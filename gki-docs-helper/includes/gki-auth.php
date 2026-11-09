<?php
/**
 * GKI Docs Helper — Auth Gate Module
 *
 * Gates all insights-expo pages behind GitKraken authentication + Insights
 * subscription entitlement. Implements a standalone OAuth2 authorization-code
 * flow against gitkraken.dev.
 *
 * Flow:
 *   1. Check if current page is a GKI docs page
 *   2. Check if user has a WordPress session
 *   3. Check cached Insights entitlement in user meta
 *   4. If cache expired, call licensing API to re-validate
 *   5. Allow or block based on entitlement
 *
 * OAuth endpoints (gitkraken.dev):
 *   - Authorize: https://gitkraken.dev/login?client_id=&redirect_uri=&state=
 *   - Token:     POST https://api.gitkraken.dev/oauth/access_token
 *   - Userinfo:  GET  https://api.gitkraken.dev/user (Bearer token)
 *
 * @since 1.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================================================================
   AUTH GATE — runs on template_redirect, before any output
   ========================================================================= */

add_action( 'template_redirect', 'gki_auth_gate_check', 5 );

/**
 * Main auth gate. Blocks unauthenticated or unentitled users from GKI pages.
 * Runs at priority 5 on template_redirect — before any template loads.
 */
function gki_auth_gate_check() {
    // Only gate GKI docs pages
    if ( ! gki_auth_is_gated_page() ) {
        return;
    }

    // Check if the gate is enabled in settings
    if ( ! gki_auth_is_enabled() ) {
        return;
    }

    // Prevent CDN/page-cache from serving gated pages without auth checks.
    // Without this, Cloudflare (or similar) may cache the page once for an
    // admin and then serve that cached copy to unauthenticated visitors.
    header( 'Cache-Control: no-cache, no-store, must-revalidate, private' );
    header( 'Pragma: no-cache' );

    // WP admins always bypass the gate
    if ( current_user_can( 'manage_options' ) ) {
        return;
    }

    // Check 1: Is the user logged in?
    if ( ! is_user_logged_in() ) {
        gki_auth_show_gate( 'login' );
        exit;
    }

    // Check 2: Does the user have Insights entitlement?
    $user_id  = get_current_user_id();
    $entitled = gki_auth_check_entitlement( $user_id );

    if ( ! $entitled ) {
        // The stored GitKraken token was rejected or is missing, so the
        // licence could not be checked. Sign the user out and ask them to
        // sign in again (fresh token, fresh check) instead of reporting
        // "no access" — this is how a trial customer who has since bought
        // Insights gets in once their trial ends. Since 1.17.7.
        if ( get_user_meta( $user_id, 'gki_reauth_required', true ) === '1' ) {
            wp_destroy_current_session();
            wp_clear_auth_cookie();
            wp_set_current_user( 0 );
            gki_auth_show_gate( 'login' );
            exit;
        }
        gki_auth_show_gate( 'no-access' );
        exit;
    }

    // User is authenticated and entitled — allow page to render
}

/* =========================================================================
   CUSTOM LOGIN ENDPOINT — /insights-expo/login/
   Redirects to gitkraken.dev with OAuth2 params.
   ========================================================================= */

add_action( 'init', 'gki_auth_intercept_login_endpoint' );

/**
 * Handle the custom login endpoint at /insights-expo/login/.
 *
 * Generates a cryptographic state token, stores it in a transient,
 * and redirects the user to gitkraken.dev/login with client_id,
 * redirect_uri, and state. After authentication, gitkraken.dev
 * redirects back to our own callback handler.
 *
 * @since 1.10.1
 */
function gki_auth_intercept_login_endpoint() {
    $path = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );

    if ( $path !== 'insights-expo/login' ) {
        return;
    }

    $redirect_to = isset( $_GET['redirect_to'] )
        ? esc_url_raw( $_GET['redirect_to'] )
        : home_url( '/insights-expo/' );

    // Already logged in — send them to the page (gate will check entitlement).
    if ( is_user_logged_in() ) {
        wp_safe_redirect( $redirect_to );
        exit;
    }

    $client_id = gki_auth_get_client_id();

    if ( empty( $client_id ) ) {
        error_log( 'GKI Auth: client_id not configured — cannot build login URL.' );
        wp_die(
            'Single sign-on is not configured yet. Please contact your administrator.',
            'Login Unavailable',
            array( 'response' => 503 )
        );
    }

    // Generate cryptographic state for CSRF protection.
    $state = wp_generate_password( 32, false );
    set_transient( 'gki_auth_state--' . $state, array(
        'redirect_to' => $redirect_to,
    ), 180 ); // 3-minute window to complete login

    // Callback URL — our ajax handler receives the auth code from gitkraken.dev.
    $redirect_uri = admin_url( 'admin-ajax.php?action=gki-oauth-callback' );

    // Redirect to gitkraken.dev login page.
    $auth_url = 'https://gitkraken.dev/login?'
        . http_build_query( array(
            'client_id'    => $client_id,
            'redirect_uri' => $redirect_uri,
            'state'        => $state,
        ), '', '&' );

    wp_redirect( $auth_url );
    exit;
}

/* =========================================================================
   OAUTH2 CALLBACK — handles the redirect from gitkraken.dev
   ========================================================================= */

// Register for both logged-in and logged-out users.
add_action( 'wp_ajax_gki-oauth-callback', 'gki_auth_oauth_callback' );
add_action( 'wp_ajax_nopriv_gki-oauth-callback', 'gki_auth_oauth_callback' );

/**
 * OAuth2 callback handler.
 *
 * 1. Validates the state parameter (CSRF protection)
 * 2. Exchanges the authorization code for an access token
 * 3. Fetches user profile from /user endpoint
 * 4. Creates or matches a WordPress user
 * 5. Stores the access token in user meta
 * 6. Logs the user in and redirects to the original page
 *
 * @since 1.12.0
 */
function gki_auth_oauth_callback() {
    // --- Validate state (CSRF protection) ---
    if ( empty( $_GET['state'] ) ) {
        gki_auth_callback_error( 'missing-state', 'Missing state parameter.' );
        return;
    }

    $state      = sanitize_text_field( $_GET['state'] );
    $state_data = get_transient( 'gki_auth_state--' . $state );

    if ( ! $state_data ) {
        gki_auth_callback_error( 'invalid-state', 'Invalid or expired state. Please try signing in again.' );
        return;
    }

    // State is single-use — delete immediately.
    delete_transient( 'gki_auth_state--' . $state );

    $redirect_to = ! empty( $state_data['redirect_to'] )
        ? $state_data['redirect_to']
        : home_url( '/insights-expo/' );

    // --- Check for errors from the provider ---
    if ( ! empty( $_GET['error'] ) ) {
        $desc = ! empty( $_GET['error_description'] )
            ? sanitize_text_field( $_GET['error_description'] )
            : sanitize_text_field( $_GET['error'] );
        gki_auth_callback_error( 'provider-error', $desc );
        return;
    }

    // --- Get the authorization code ---
    if ( empty( $_GET['code'] ) ) {
        gki_auth_callback_error( 'missing-code', 'No authorization code received.' );
        return;
    }

    $code      = sanitize_text_field( $_GET['code'] );
    $client_id = gki_auth_get_client_id();

    // --- Exchange code for access token ---
    $token_url = 'https://api.gitkraken.dev/oauth/access_token';
    $token_response = wp_remote_post( $token_url, array(
        'timeout' => 15,
        'body'    => array(
            'code'          => $code,
            'client_id'     => $client_id,
            'client_secret' => '', // Not required per backend team
            'redirect_uri'  => admin_url( 'admin-ajax.php?action=gki-oauth-callback' ),
            'grant_type'    => 'authorization_code',
        ),
    ) );

    if ( is_wp_error( $token_response ) ) {
        error_log( 'GKI Auth: Token exchange failed — ' . $token_response->get_error_message() );
        gki_auth_callback_error( 'token-request-failed', 'Could not connect to authentication server.' );
        return;
    }

    $token_status = wp_remote_retrieve_response_code( $token_response );
    $token_body   = json_decode( wp_remote_retrieve_body( $token_response ), true );

    if ( $token_status !== 200 || empty( $token_body['access_token'] ) ) {
        $msg = isset( $token_body['error'] ) ? $token_body['error'] : 'HTTP ' . $token_status;
        error_log( 'GKI Auth: Token exchange returned ' . $msg );
        gki_auth_callback_error( 'token-exchange-failed', 'Authentication failed: ' . $msg );
        return;
    }

    $access_token = $token_body['access_token'];

    // --- Fetch user profile ---
    $userinfo_url = 'https://api.gitkraken.dev/user';
    $userinfo_response = wp_remote_get( $userinfo_url, array(
        'timeout' => 10,
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
            'Accept'        => 'application/json',
        ),
    ) );

    if ( is_wp_error( $userinfo_response ) ) {
        error_log( 'GKI Auth: Userinfo request failed — ' . $userinfo_response->get_error_message() );
        gki_auth_callback_error( 'userinfo-failed', 'Could not retrieve user profile.' );
        return;
    }

    $userinfo_status = wp_remote_retrieve_response_code( $userinfo_response );
    $user_profile    = json_decode( wp_remote_retrieve_body( $userinfo_response ), true );

    if ( $userinfo_status !== 200 || empty( $user_profile['email'] ) ) {
        error_log( 'GKI Auth: Userinfo returned HTTP ' . $userinfo_status );
        gki_auth_callback_error( 'userinfo-invalid', 'Could not retrieve a valid user profile.' );
        return;
    }

    // --- Find or create WordPress user ---
    $email    = sanitize_email( $user_profile['email'] );
    $name     = isset( $user_profile['name'] ) ? sanitize_text_field( $user_profile['name'] ) : '';
    $username = isset( $user_profile['username'] ) ? sanitize_user( $user_profile['username'] ) : '';
    $gk_id    = isset( $user_profile['id'] ) ? sanitize_text_field( $user_profile['id'] ) : '';

    $wp_user = get_user_by( 'email', $email );

    if ( ! $wp_user ) {
        // Create a new WordPress user for this GitKraken account.
        $user_login = ! empty( $username ) ? $username : $email;

        // Ensure username is unique.
        if ( username_exists( $user_login ) ) {
            $user_login = $email;
        }
        if ( username_exists( $user_login ) ) {
            $user_login = 'gk_' . substr( md5( $email ), 0, 10 );
        }

        $user_id = wp_insert_user( array(
            'user_login'   => $user_login,
            'user_email'   => $email,
            'display_name' => ! empty( $name ) ? $name : $user_login,
            'user_pass'    => wp_generate_password( 32, true, true ), // Random, unused
            'role'         => 'subscriber',
        ) );

        if ( is_wp_error( $user_id ) ) {
            error_log( 'GKI Auth: Failed to create user — ' . $user_id->get_error_message() );
            gki_auth_callback_error( 'user-creation-failed', 'Could not create your account. Please try again.' );
            return;
        }

        $wp_user = get_user_by( 'id', $user_id );
    }

    // --- Store token and profile in user meta ---
    update_user_meta( $wp_user->ID, 'gki_access_token', $access_token );
    update_user_meta( $wp_user->ID, 'gki_gitkraken_id', $gk_id );

    // Fresh licence check on every sign-in (since 1.17.7): a customer who
    // converted from a trial is recognised as licensed immediately, and the
    // session length below can depend on the result.
    delete_user_meta( $wp_user->ID, 'gki_reauth_required' );
    delete_user_meta( $wp_user->ID, 'gki_insights_access' );
    delete_user_meta( $wp_user->ID, 'gki_insights_checked_at' );
    gki_auth_check_licensed( $wp_user->ID );

    // Update display name if it changed.
    if ( ! empty( $name ) && $wp_user->display_name !== $name ) {
        wp_update_user( array(
            'ID'           => $wp_user->ID,
            'display_name' => $name,
        ) );
    }

    // --- Log the user in ---
    wp_set_current_user( $wp_user->ID );
    wp_set_auth_cookie( $wp_user->ID, true ); // "Remember me" = true

    // --- Redirect to the original page ---
    wp_safe_redirect( $redirect_to );
    exit;
}

/**
 * Redirect to the login error page with a message.
 *
 * @since 1.12.0
 * @param string $code    Error code slug.
 * @param string $message Human-readable error message.
 */
function gki_auth_callback_error( $code, $message ) {
    $url = home_url( '/insights-expo/?login-error=' . urlencode( $code )
        . '&message=' . urlencode( $message ) );
    wp_redirect( $url );
    exit;
}

/* =========================================================================
   ENTITLEMENT CHECK — cached, with API fallback
   ========================================================================= */

/**
 * Check whether a user has Insights entitlement.
 * Uses cached result from user meta; falls back to API call when expired.
 *
 * @param int $user_id WordPress user ID.
 * @return bool True if user has Insights access.
 */
function gki_auth_check_entitlement( $user_id ) {
    // The real licence comes first, so a trial customer who converts is on
    // paid access as soon as the (cached) check sees it, whatever their trial
    // dates. A redeemed, unexpired trial code is the fallback. Since 1.17.7;
    // 1.17.5 checked the trial first and skipped the licence entirely.
    if ( gki_auth_check_licensed( $user_id ) ) {
        return true;
    }
    return gki_auth_has_active_trial( $user_id );
}

/**
 * Whether the user's GitKraken organizations hold Insights licences.
 * Cached in user meta for the Cache TTL; calls the organizations API when
 * the cache is missing or stale.
 *
 * @param int $user_id WordPress user ID.
 * @return bool
 */
function gki_auth_check_licensed( $user_id ) {
    // Check cached result
    $cached_access = get_user_meta( $user_id, 'gki_insights_access', true );
    $cached_time   = (int) get_user_meta( $user_id, 'gki_insights_checked_at', true );
    $cache_ttl     = gki_auth_get_cache_ttl();

    if ( $cached_access !== '' && $cached_time > 0 ) {
        $age = time() - $cached_time;
        if ( $age < $cache_ttl ) {
            return $cached_access === '1';
        }
    }

    // Cache expired or missing — call the licensing API
    $entitled = gki_auth_call_licensing_api( $user_id );

    // Cache the result
    update_user_meta( $user_id, 'gki_insights_access', $entitled ? '1' : '0' );
    update_user_meta( $user_id, 'gki_insights_checked_at', time() );

    return $entitled;
}

/**
 * Check Insights entitlement via the GitKraken organizations API.
 *
 * Calls GET /user/organizations with the user's OAuth Bearer token.
 * The response is an array of orgs, each containing the user's role
 * and a totalInsightsLicenses count. If ANY org has
 * totalInsightsLicenses > 0, the user is considered entitled.
 *
 * Caveat: this is org-level, not per-user. It confirms the user
 * belongs to an org that owns Insights seats, not that this specific
 * user has a seat assigned. This is an acceptable gate for help
 * center documentation access.
 *
 * @param int $user_id WordPress user ID.
 * @return bool True if the user belongs to an org with Insights licenses.
 */
function gki_auth_call_licensing_api( $user_id ) {
    $endpoint = gki_auth_get_licensing_endpoint();
    if ( empty( $endpoint ) ) {
        // No endpoint configured — fail closed (deny access)
        error_log( 'GKI Auth: Organizations API endpoint not configured.' );
        return false;
    }

    // Get the OAuth access token stored by our custom OAuth handler.
    $access_token = get_user_meta( $user_id, 'gki_access_token', true );

    if ( empty( $access_token ) ) {
        error_log( 'GKI Auth: No OAuth access token found for user ' . $user_id );
        update_user_meta( $user_id, 'gki_reauth_required', '1' );
        return false;
    }

    // Call the organizations endpoint
    $response = wp_remote_get( $endpoint, array(
        'timeout' => 10,
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
            'Accept'        => 'application/json',
        ),
    ) );

    // Handle request failure
    if ( is_wp_error( $response ) ) {
        error_log( 'GKI Auth: Organizations API request failed — ' . $response->get_error_message() );
        // Fail closed on API error — deny access
        return false;
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body        = wp_remote_retrieve_body( $response );

    // Handle 401 — token may be expired
    if ( $status_code === 401 ) {
        error_log( 'GKI Auth: Organizations API returned 401 — token may be expired for user ' . $user_id );
        // Ask the gate to sign the user in again (fresh token).
        update_user_meta( $user_id, 'gki_reauth_required', '1' );
        delete_user_meta( $user_id, 'gki_insights_access' );
        delete_user_meta( $user_id, 'gki_insights_checked_at' );
        return false;
    }

    // Handle non-200 responses
    if ( $status_code !== 200 ) {
        error_log( 'GKI Auth: Organizations API returned HTTP ' . $status_code );
        return false;
    }

    $data = json_decode( $body, true );

    // Response should be an array of organizations
    if ( ! is_array( $data ) ) {
        error_log( 'GKI Auth: Organizations API returned invalid JSON.' );
        return false;
    }

    // Check if any org has Insights licenses
    // Each org object includes totalInsightsLicenses (int).
    // If any org has totalInsightsLicenses > 0, user is entitled.
    foreach ( $data as $org ) {
        if ( ! is_array( $org ) ) {
            continue;
        }
        $licenses = isset( $org['totalInsightsLicenses'] ) ? (int) $org['totalInsightsLicenses'] : 0;
        if ( $licenses > 0 ) {
            return true;
        }
    }

    // No orgs with Insights licenses found
    return false;
}

/* =========================================================================
   GATE DISPLAY — render the blocked-user template
   ========================================================================= */

/**
 * Render the auth gate page and stop execution.
 *
 * @param string $reason 'login' or 'no-access'.
 */
function gki_auth_show_gate( $reason ) {
    // Set appropriate HTTP status
    if ( $reason === 'login' ) {
        status_header( 401 );
    } else {
        status_header( 403 );
    }

    // Prevent caching of gate pages
    nocache_headers();

    // Tell the asset enqueue (which runs inside get_header) that this is the
    // gate: stylesheet only, no scripts and no search index.
    if ( ! defined( 'GKI_GATE_ACTIVE' ) ) {
        define( 'GKI_GATE_ACTIVE', true );
    }

    // Build the login URL using our custom endpoint (bypasses WP admin login).
    $login_url = gki_auth_get_login_url( gki_auth_get_current_url() );

    // Load the gate template
    $template = GKI_DOCS_PATH . 'templates/gki-gate.php';
    if ( file_exists( $template ) ) {
        // Make variables available to the template
        set_query_var( 'gki_gate_reason', $reason );
        set_query_var( 'gki_gate_login_url', $login_url );
        include $template;
    } else {
        // Fallback if template is missing
        wp_die(
            'You must sign in with your GitKraken account to access this page.',
            'Access Required',
            array( 'response' => 403 )
        );
    }
}

/* =========================================================================
   SETTINGS — admin page for auth configuration
   ========================================================================= */

add_action( 'admin_menu', 'gki_auth_add_settings_page' );
add_action( 'admin_init', 'gki_auth_register_settings' );

/**
 * Add the auth settings page under the GKI Docs Helper menu.
 */
function gki_auth_add_settings_page() {
    add_options_page(
        'GKI Auth Gate Settings',
        'GKI Auth Gate',
        'manage_options',
        'gki-auth-settings',
        'gki_auth_render_settings_page'
    );
}

/**
 * Register auth settings.
 */
function gki_auth_register_settings() {
    register_setting( 'gki_auth_settings', 'gki_auth_enabled', array(
        'type'              => 'boolean',
        'default'           => false,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ) );

    register_setting( 'gki_auth_settings', 'gki_auth_licensing_endpoint', array(
        'type'              => 'string',
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    register_setting( 'gki_auth_settings', 'gki_auth_cache_ttl', array(
        'type'              => 'integer',
        'default'           => 24, // 24 hours
        'sanitize_callback' => 'absint',
    ) );

    register_setting( 'gki_auth_settings', 'gki_auth_upgrade_url', array(
        'type'              => 'string',
        'default'           => 'https://www.gitkraken.com/insights',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    register_setting( 'gki_auth_settings', 'gki_auth_client_id', array(
        'type'              => 'string',
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    register_setting( 'gki_auth_settings', 'gki_auth_trial_code', array(
        'type'              => 'string',
        'default'           => '',
        'sanitize_callback' => function ( $value ) {
            return trim( sanitize_text_field( (string) $value ) );
        },
    ) );

    register_setting( 'gki_auth_settings', 'gki_auth_trial_days', array(
        'type'              => 'integer',
        'default'           => 14,
        'sanitize_callback' => function ( $value ) {
            $days = absint( $value );
            return ( $days >= 1 && $days <= 90 ) ? $days : 14;
        },
    ) );

    // Settings section
    add_settings_section(
        'gki_auth_main',
        'Auth Gate Configuration',
        function () {
            echo '<p>Configure the authentication gate for GKI Help Center pages. '
               . 'Uses a built-in OAuth2 flow against gitkraken.dev.</p>';
        },
        'gki-auth-settings'
    );

    // Fields
    add_settings_field( 'gki_auth_enabled', 'Enable Auth Gate', function () {
        $val = get_option( 'gki_auth_enabled', false );
        printf(
            '<label><input type="checkbox" name="gki_auth_enabled" value="1" %s /> '
            . 'Require authentication to view Help Center pages</label>',
            checked( $val, true, false )
        );
    }, 'gki-auth-settings', 'gki_auth_main' );

    add_settings_field( 'gki_auth_client_id', 'OAuth Client ID', function () {
        $val = get_option( 'gki_auth_client_id', '' );
        printf(
            '<input type="text" name="gki_auth_client_id" value="%s" class="regular-text" '
            . 'placeholder="gk_help" />'
            . '<p class="description">The OAuth client_id registered with gitkraken.dev.</p>',
            esc_attr( $val )
        );
    }, 'gki-auth-settings', 'gki_auth_main' );

    add_settings_field( 'gki_auth_licensing_endpoint', 'Organizations API Endpoint', function () {
        $val = get_option( 'gki_auth_licensing_endpoint', '' );
        printf(
            '<input type="url" name="gki_auth_licensing_endpoint" value="%s" class="regular-text" '
            . 'placeholder="https://api.gitkraken.dev/user/organizations" />'
            . '<p class="description">The API endpoint that returns the user\'s organizations with Insights license counts.</p>',
            esc_attr( $val )
        );
    }, 'gki-auth-settings', 'gki_auth_main' );

    add_settings_field( 'gki_auth_cache_ttl', 'Cache TTL (hours)', function () {
        $val = get_option( 'gki_auth_cache_ttl', 24 );
        printf(
            '<input type="number" name="gki_auth_cache_ttl" value="%d" min="1" max="168" /> '
            . '<p class="description">How long to cache entitlement results per user. Default: 24 hours.</p>',
            $val
        );
    }, 'gki-auth-settings', 'gki_auth_main' );

    add_settings_field( 'gki_auth_upgrade_url', 'Upgrade URL', function () {
        $val = get_option( 'gki_auth_upgrade_url', 'https://www.gitkraken.com/insights' );
        printf(
            '<input type="url" name="gki_auth_upgrade_url" value="%s" class="regular-text" '
            . 'placeholder="https://www.gitkraken.com/insights" />'
            . '<p class="description">Linked from the sign-in screen ("Learn about GitKraken Insights"). The no-access screen links to gitkraken.com/insights and the book-a-demo page directly.</p>',
            esc_attr( $val )
        );
    }, 'gki-auth-settings', 'gki_auth_main' );

    add_settings_field( 'gki_auth_trial_code', 'Trial code', function () {
        $val = get_option( 'gki_auth_trial_code', '' );
        printf(
            '<input type="text" name="gki_auth_trial_code" value="%s" class="regular-text" autocomplete="off" /> '
            . '<p class="description">Give this code to Insights trial customers. A signed-in GitKraken account without Insights access can enter it on the no-access screen to read the Help Center for the trial length below. '
            . 'Each account can redeem one trial. Matching ignores case. Leave empty to turn trial codes off. Changing the code does not end trials already redeemed.</p>',
            esc_attr( $val )
        );
    }, 'gki-auth-settings', 'gki_auth_main' );

    add_settings_field( 'gki_auth_trial_days', 'Trial length (days)', function () {
        $val = (int) get_option( 'gki_auth_trial_days', 14 );
        printf(
            '<input type="number" name="gki_auth_trial_days" value="%d" min="1" max="90" /> '
            . '<p class="description">How long access lasts, counted from the moment each account redeems the code. Default: 14 days.</p>',
            $val
        );
    }, 'gki-auth-settings', 'gki_auth_main' );
}

/**
 * Render the settings page.
 */
function gki_auth_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>GKI Auth Gate Settings</h1>
        <?php settings_errors(); ?>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'gki_auth_settings' );
            do_settings_sections( 'gki-auth-settings' );
            submit_button();
            ?>
        </form>

        <?php gki_auth_render_trial_extensions_section(); ?>

        <hr />
        <h2>Status</h2>
        <?php
        $client_id = gki_auth_get_client_id();
        $endpoint  = get_option( 'gki_auth_licensing_endpoint', '' );
        $enabled   = get_option( 'gki_auth_enabled', false );
        $callback  = admin_url( 'admin-ajax.php?action=gki-oauth-callback' );

        echo '<table class="widefat" style="max-width:600px">';
        echo '<tr><td>OAuth Client ID</td><td>' . ( $client_id ? esc_html( $client_id ) : '⚠️ Not configured' ) . '</td></tr>';
        echo '<tr><td>Callback URL</td><td><code style="font-size:12px;">' . esc_html( $callback ) . '</code><br><em style="font-size:11px;">Register this as the redirect_uri in gitkraken.dev</em></td></tr>';
        echo '<tr><td>Licensing Endpoint</td><td>' . ( $endpoint ? esc_html( $endpoint ) : '⚠️ Not configured' ) . '</td></tr>';
        echo '<tr><td>Auth Gate</td><td>' . ( $enabled ? '🔒 Enabled' : '🔓 Disabled' ) . '</td></tr>';
        echo '<tr><td>Trial codes</td><td>' . ( gki_auth_get_trial_code() !== '' ? 'On — ' . (int) gki_auth_get_trial_days() . ' days per account' : 'Off' ) . '</td></tr>';
        echo '</table>';

        $trial_users = get_users( array(
            'meta_key' => 'gki_trial_expires_at',
            'orderby'  => 'meta_value_num',
            'order'    => 'DESC',
            'number'   => 200,
        ) );
        echo '<h2>Trial redemptions</h2>';
        if ( empty( $trial_users ) ) {
            echo '<p><em>No trial codes redeemed yet.</em></p>';
        } else {
            $fmt = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
            echo '<table class="widefat striped" style="max-width:800px"><thead><tr><th>Account</th><th>Redeemed</th><th>Expires</th><th>Status</th></tr></thead><tbody>';
            foreach ( $trial_users as $u ) {
                $started  = (int) get_user_meta( $u->ID, 'gki_trial_started_at', true );
                $own      = (int) get_user_meta( $u->ID, 'gki_trial_expires_at', true );
                $expires  = gki_auth_trial_expires_at( $u->ID );
                $extended = $expires > $own ? ' (extended for ' . gki_auth_user_email_domain( $u->ID ) . ')' : '';
                printf(
                    '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                    esc_html( $u->user_email ),
                    $started ? esc_html( wp_date( $fmt, $started ) ) : '—',
                    $expires ? esc_html( wp_date( $fmt, $expires ) . $extended ) : '—',
                    ( $expires > time() ) ? 'Active' : 'Ended'
                );
            }
            echo '</tbody></table>';
            echo '<p class="description">To end a trial early, set that account\'s <code>gki_trial_expires_at</code> user meta to a past time. Deleting it instead lets the account redeem again.</p>';
        }
        ?>

        <hr />
        <h2>OAuth Debug Log</h2>
        <p>Captures HTTP requests/responses between WordPress and the GitKraken API (token exchange, userinfo, etc.). Trigger a login attempt to populate.</p>
        <?php
        $debug_log = get_option( 'gki_auth_debug_log', array() );
        if ( empty( $debug_log ) ) {
            echo '<p><em>No entries yet. Try signing in to capture the OAuth token exchange.</em></p>';
        } else {
            // Clear log button
            if ( isset( $_POST['gki_clear_debug_log'] ) && check_admin_referer( 'gki_clear_debug_log' ) ) {
                delete_option( 'gki_auth_debug_log' );
                echo '<div class="notice notice-success"><p>Debug log cleared.</p></div>';
                $debug_log = array();
            }

            if ( ! empty( $debug_log ) ) {
                echo '<form method="post">';
                wp_nonce_field( 'gki_clear_debug_log' );
                echo '<p><button type="submit" name="gki_clear_debug_log" value="1" class="button">Clear Log</button></p>';
                echo '</form>';

                // Show entries newest first
                $debug_log = array_reverse( $debug_log );
                foreach ( $debug_log as $i => $entry ) {
                    $time   = isset( $entry['time'] ) ? esc_html( $entry['time'] ) : '?';
                    $method = isset( $entry['method'] ) ? esc_html( $entry['method'] ) : '?';
                    $url    = isset( $entry['url'] ) ? esc_html( $entry['url'] ) : '?';
                    $code   = isset( $entry['response_code'] ) ? (int) $entry['response_code'] : '—';

                    echo '<details style="margin-bottom:8px;border:1px solid #ccd0d4;border-radius:4px;padding:8px 12px;">';
                    echo '<summary><strong>' . $method . '</strong> ' . $url . ' &mdash; <code>' . $code . '</code> &mdash; ' . $time . '</summary>';
                    echo '<div style="margin-top:8px;">';

                    if ( ! empty( $entry['request_headers'] ) ) {
                        echo '<h4 style="margin:4px 0;">Request Headers</h4>';
                        echo '<pre style="background:#f0f0f1;padding:8px;overflow-x:auto;max-height:200px;font-size:12px;">'
                            . esc_html( print_r( $entry['request_headers'], true ) ) . '</pre>';
                    }
                    if ( ! empty( $entry['request_body'] ) ) {
                        echo '<h4 style="margin:4px 0;">Request Body</h4>';
                        echo '<pre style="background:#f0f0f1;padding:8px;overflow-x:auto;max-height:200px;font-size:12px;">'
                            . esc_html( is_array( $entry['request_body'] ) ? print_r( $entry['request_body'], true ) : $entry['request_body'] ) . '</pre>';
                    }
                    if ( ! empty( $entry['response_error'] ) ) {
                        echo '<h4 style="margin:4px 0;color:#d63638;">Response Error</h4>';
                        echo '<pre style="background:#fcf0f1;padding:8px;font-size:12px;">' . esc_html( $entry['response_error'] ) . '</pre>';
                    }
                    if ( ! empty( $entry['response_headers'] ) ) {
                        echo '<h4 style="margin:4px 0;">Response Headers</h4>';
                        echo '<pre style="background:#f0f0f1;padding:8px;overflow-x:auto;max-height:200px;font-size:12px;">'
                            . esc_html( print_r( $entry['response_headers'], true ) ) . '</pre>';
                    }
                    if ( ! empty( $entry['response_body'] ) ) {
                        echo '<h4 style="margin:4px 0;">Response Body</h4>';
                        // Try to pretty-print JSON
                        $pretty = json_decode( $entry['response_body'] );
                        $body_display = ( $pretty !== null )
                            ? json_encode( $pretty, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
                            : $entry['response_body'];
                        echo '<pre style="background:#f0f0f1;padding:8px;overflow-x:auto;max-height:400px;font-size:12px;">'
                            . esc_html( $body_display ) . '</pre>';
                    }

                    echo '</div></details>';
                }
            }
        }
        ?>
    </div>
    <?php
}

/* =========================================================================
   OAUTH DEBUG LOGGING — captures token exchange request/response
   ========================================================================= */

/**
 * Intercept all WordPress HTTP API calls to api.gitkraken.dev and log the
 * request + response details to a WP option. Viewable from the GKI Auth
 * settings page — no server/FTP access required.
 *
 * @since 1.11.2
 */
add_action( 'http_api_debug', 'gki_auth_debug_http', 10, 5 );

function gki_auth_debug_http( $response, $context, $class, $parsed_args, $url ) {
    // Only log requests to gitkraken API endpoints
    if ( strpos( $url, 'gitkraken.dev' ) === false ) {
        return;
    }

    $entry = array(
        'time'   => current_time( 'mysql' ),
        'url'    => $url,
        'method' => isset( $parsed_args['method'] ) ? $parsed_args['method'] : 'GET',
    );

    // Log request body (the POST data sent to the token endpoint)
    if ( ! empty( $parsed_args['body'] ) ) {
        $body = $parsed_args['body'];
        if ( is_array( $body ) ) {
            // Redact client_secret if present
            if ( isset( $body['client_secret'] ) && strlen( $body['client_secret'] ) > 4 ) {
                $body['client_secret'] = substr( $body['client_secret'], 0, 4 ) . '***REDACTED***';
            }
            // Redact auth code (keep first 8 chars for identification)
            if ( isset( $body['code'] ) && strlen( $body['code'] ) > 8 ) {
                $body['code'] = substr( $body['code'], 0, 8 ) . '***';
            }
        }
        $entry['request_body'] = $body;
    }

    // Log request headers
    if ( ! empty( $parsed_args['headers'] ) ) {
        $headers = $parsed_args['headers'];
        // Redact Authorization header value
        if ( isset( $headers['Authorization'] ) ) {
            $headers['Authorization'] = substr( $headers['Authorization'], 0, 15 ) . '***REDACTED***';
        }
        $entry['request_headers'] = $headers;
    }

    // Log response
    if ( is_wp_error( $response ) ) {
        $entry['response_error'] = $response->get_error_message();
    } else {
        $entry['response_code'] = wp_remote_retrieve_response_code( $response );

        // wp_remote_retrieve_headers() returns a CaseInsensitiveDictionary object
        // on success but a plain array() when headers are absent. Calling
        // ->getAll() on the array is a fatal error, so branch on the type.
        $resp_headers = wp_remote_retrieve_headers( $response );
        if ( is_object( $resp_headers ) && method_exists( $resp_headers, 'getAll' ) ) {
            $resp_headers = $resp_headers->getAll();
        }
        $entry['response_headers'] = is_array( $resp_headers ) ? $resp_headers : array();

        $resp_body = wp_remote_retrieve_body( $response );

        // Redact bearer/refresh tokens before they are written to wp_options.
        // The token endpoint returns them in plain text in the response body.
        $resp_body = preg_replace(
            '/("(?:access|refresh|id)_token"\s*:\s*")([^"]{8})[^"]*(")/i',
            '$1$2***REDACTED***$3',
            $resp_body
        );

        // Truncate very long responses but keep enough to diagnose
        if ( strlen( $resp_body ) > 4000 ) {
            $resp_body = substr( $resp_body, 0, 4000 ) . '...[TRUNCATED]';
        }
        $entry['response_body'] = $resp_body;
    }

    // Append to the debug log (keep last 20 entries)
    $log = get_option( 'gki_auth_debug_log', array() );
    $log[] = $entry;
    $log = array_slice( $log, -20 );
    update_option( 'gki_auth_debug_log', $log, false );
}

/* =========================================================================
   LOCKDOWN — every route other than the page template that can expose a
   gated post. The template gate above only covers is_single(); WordPress
   also serves post content through the REST API, oEmbed, feeds, site
   search, archives and sitemaps, and tells crawlers about all of it.
   Everything in this section is active only while the gate is enabled and
   only for viewers who are not entitled (admins and entitled subscribers
   see the normal behaviour). Verified against production 2026-09-23: the
   REST API alone returned the full content of all 50 posts anonymously.

   @since 1.17.3
   ========================================================================= */

/**
 * Whether the current viewer may see gated content. Cached per request.
 * Mirrors the order of checks in gki_auth_gate_check().
 */
function gki_auth_viewer_is_entitled() {
    static $result = null;
    if ( $result !== null ) {
        return $result;
    }
    if ( ! is_user_logged_in() ) {
        return $result = false;
    }
    if ( current_user_can( 'manage_options' ) ) {
        return $result = true;
    }
    return $result = (bool) gki_auth_check_entitlement( get_current_user_id() );
}

/**
 * True when gated content must be hidden from the current viewer.
 */
function gki_auth_should_hide() {
    return gki_auth_is_enabled() && ! gki_auth_viewer_is_entitled();
}

/**
 * Whether a post belongs to the gated category.
 *
 * @param int|WP_Post|null $post
 */
function gki_auth_post_is_gated( $post ) {
    $post = get_post( $post );
    if ( ! $post || $post->post_type !== 'post' ) {
        return false;
    }
    return has_category( GKI_DOCS_CATEGORY, $post );
}

/**
 * Term ID of the gated category, or 0 if it does not exist.
 */
function gki_auth_gated_category_id() {
    static $id = null;
    if ( $id === null ) {
        $term = get_category_by_slug( GKI_DOCS_CATEGORY );
        $id   = $term ? (int) $term->term_id : 0;
    }
    return $id;
}

/**
 * Add the gated category to a query-args array's category__not_in.
 *
 * @param array $args WP_Query-style args.
 * @return array
 */
function gki_auth_exclude_gated_category( $args ) {
    $cat = gki_auth_gated_category_id();
    if ( ! $cat ) {
        return $args;
    }
    $not_in = isset( $args['category__not_in'] ) ? (array) $args['category__not_in'] : array();
    $not_in[] = $cat;
    $args['category__not_in'] = array_values( array_unique( array_map( 'intval', $not_in ) ) );
    return $args;
}

/* --- REST API: /wp-json/wp/v2/posts and /wp-json/wp/v2/search ------------ */

add_filter( 'rest_post_query', 'gki_auth_rest_exclude_gated', 10, 2 );
add_filter( 'rest_post_search_query', 'gki_auth_rest_exclude_gated', 10, 2 );

/**
 * Drop gated posts from REST collection and search queries.
 */
function gki_auth_rest_exclude_gated( $args, $request ) {
    if ( gki_auth_should_hide() ) {
        $args = gki_auth_exclude_gated_category( $args );
    }
    return $args;
}

add_filter( 'rest_request_before_callbacks', 'gki_auth_rest_block_single', 10, 3 );

/**
 * Refuse a single gated post (/wp/v2/posts/<id> and its sub-routes, and the
 * same route when reached as an _embed of another response).
 *
 * This runs before the controller, which is the documented place to return a
 * WP_Error. Until 1.17.8 the check lived on rest_prepare_post; WP 6.8's
 * get_item() calls ->link_header() on whatever prepare returns, so the
 * WP_Error fataled and the endpoint answered 500 instead of 401. ?slug= and
 * ?include= go through the collection query, which rest_post_query already
 * filters.
 *
 * @param WP_REST_Response|WP_HTTP_Response|WP_Error|mixed $response
 * @param array                                          $handler
 * @param WP_REST_Request                                $request
 */
function gki_auth_rest_block_single( $response, $handler, $request ) {
    if ( is_wp_error( $response ) || ! gki_auth_should_hide() ) {
        return $response;
    }
    if ( ! preg_match( '#^/wp/v2/posts/(\d+)(?:/|$)#', $request->get_route(), $m ) ) {
        return $response;
    }
    if ( gki_auth_post_is_gated( (int) $m[1] ) ) {
        return new WP_Error(
            'gki_auth_required',
            'Sign in with a GitKraken account that has an Insights subscription to read this page.',
            array( 'status' => 401 )
        );
    }
    return $response;
}

/**
 * IDs of every post in the gated category, any status. Cached per request.
 *
 * @return int[]
 */
function gki_auth_gated_post_ids() {
    static $ids = null;
    if ( $ids === null ) {
        $cat = gki_auth_gated_category_id();
        $ids = $cat ? array_map( 'intval', get_posts( array(
            'post_type'        => 'post',
            'post_status'      => 'any',
            'category'         => $cat,
            'fields'           => 'ids',
            'posts_per_page'   => -1,
            'no_found_rows'    => true,
            'suppress_filters' => true,
        ) ) ) : array();
    }
    return $ids;
}

add_filter( 'searchwp\rest\args', 'gki_auth_searchwp_rest_exclude_gated', 10, 2 );

/**
 * SearchWP replaces every /wp/v2/search handler with its own (SearchWP\Rest),
 * which runs SWP_Query against its own index and never fires
 * rest_post_search_query. Exclude the gated posts at its query so results
 * and totals stay right.
 */
function gki_auth_searchwp_rest_exclude_gated( $args, $context = array() ) {
    if ( gki_auth_should_hide() ) {
        $ids = gki_auth_gated_post_ids();
        if ( $ids ) {
            $not_in = isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array();
            $args['post__not_in'] = array_values( array_unique( array_map( 'intval', array_merge( $not_in, $ids ) ) ) );
        }
    }
    return $args;
}

add_filter( 'rest_post_dispatch', 'gki_auth_rest_search_strip_gated', 10, 3 );

/**
 * Last line for /wp/v2/search, whichever handler answered it (core, SearchWP,
 * or anything registered later through wp_rest_search_handlers): drop every
 * result that is a gated post. The query-level filters above normally leave
 * nothing for this to remove.
 */
function gki_auth_rest_search_strip_gated( $result, $server, $request ) {
    if ( ! gki_auth_should_hide() || $request->get_route() !== '/wp/v2/search' ) {
        return $result;
    }
    if ( ! ( $result instanceof WP_REST_Response ) || $result->is_error() ) {
        return $result;
    }
    $data = $result->get_data();
    if ( ! is_array( $data ) ) {
        return $result;
    }
    $gated = array_flip( gki_auth_gated_post_ids() );
    $kept  = array();
    foreach ( $data as $item ) {
        $id   = is_array( $item ) && isset( $item['id'] ) ? (int) $item['id'] : 0;
        $type = is_array( $item ) && isset( $item['type'] ) ? $item['type'] : '';
        if ( $id && $type === 'post' && isset( $gated[ $id ] ) ) {
            continue;
        }
        $kept[] = $item;
    }
    if ( count( $kept ) !== count( $data ) ) {
        $result->set_data( $kept );
        $removed = count( $data ) - count( $kept );
        $total   = $result->get_headers()['X-WP-Total'] ?? null;
        if ( $total !== null ) {
            $result->header( 'X-WP-Total', (string) max( 0, (int) $total - $removed ) );
        }
    }
    return $result;
}

/* --- oEmbed ---------------------------------------------------------------- */

add_filter( 'oembed_response_data', 'gki_auth_oembed_block', 10, 2 );

/**
 * Return no oEmbed data for gated posts. The oEmbed REST controller turns a
 * falsy result into a 404 (oembed_invalid_url).
 */
function gki_auth_oembed_block( $data, $post ) {
    if ( gki_auth_should_hide() && gki_auth_post_is_gated( $post ) ) {
        return false;
    }
    return $data;
}

/* --- Feeds, site search, archives (front-end main query) ------------------ */

add_action( 'pre_get_posts', 'gki_auth_main_query_exclude_gated' );

/**
 * Keep gated posts out of every listing an anonymous visitor can reach:
 * RSS/Atom feeds (site, category, per-post comment feeds), ?s= search
 * results, and date/author/tag archives and the blog index. The gated
 * category's own archive is handled by gki_auth_gate_check() instead, so it
 * shows the gate rather than an empty page.
 */
function gki_auth_main_query_exclude_gated( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( ! gki_auth_should_hide() ) {
        return;
    }
    if ( $query->is_feed() || $query->is_search() || $query->is_home() || $query->is_archive() ) {
        $cat = gki_auth_gated_category_id();
        if ( $cat ) {
            $not_in   = (array) $query->get( 'category__not_in' );
            $not_in[] = $cat;
            $query->set( 'category__not_in', array_values( array_unique( array_map( 'intval', $not_in ) ) ) );
        }
    }
}

/* --- Sitemaps -------------------------------------------------------------- */

add_filter( 'wp_sitemaps_posts_query_args', 'gki_auth_sitemap_exclude_posts', 10, 2 );

/**
 * Core sitemaps (/wp-sitemap-posts-post-N.xml): leave gated posts out.
 */
function gki_auth_sitemap_exclude_posts( $args, $post_type ) {
    if ( $post_type === 'post' && gki_auth_is_enabled() ) {
        $args = gki_auth_exclude_gated_category( $args );
    }
    return $args;
}

add_filter( 'wp_sitemaps_taxonomies_query_args', 'gki_auth_sitemap_exclude_term', 10, 2 );

/**
 * Core sitemaps: leave the gated category term out of the taxonomy sitemap.
 */
function gki_auth_sitemap_exclude_term( $args, $taxonomy ) {
    if ( $taxonomy === 'category' && gki_auth_is_enabled() ) {
        $cat = gki_auth_gated_category_id();
        if ( $cat ) {
            $exclude   = isset( $args['exclude'] ) ? (array) $args['exclude'] : array();
            $exclude[] = $cat;
            $args['exclude'] = array_values( array_unique( array_map( 'intval', $exclude ) ) );
        }
    }
    return $args;
}

// Yoast SEO and Rank Math build their own sitemaps; feed them the same list.
add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', 'gki_auth_sitemap_exclude_ids' );
add_filter( 'rank_math/sitemap/posts_to_exclude', 'gki_auth_sitemap_exclude_ids' );

/**
 * IDs of every gated post, for SEO plugins' exclusion filters.
 *
 * @param array $ids
 * @return array
 */
function gki_auth_sitemap_exclude_ids( $ids ) {
    if ( ! gki_auth_is_enabled() ) {
        return $ids;
    }
    $cat = gki_auth_gated_category_id();
    if ( ! $cat ) {
        return $ids;
    }
    $gated = get_posts( array(
        'post_type'        => 'post',
        'post_status'      => 'any',
        'cat'              => $cat,
        'posts_per_page'   => -1,
        'fields'           => 'ids',
        'no_found_rows'    => true,
        'suppress_filters' => true,
    ) );
    return array_values( array_unique( array_merge( (array) $ids, array_map( 'intval', $gated ) ) ) );
}

/* --- Crawler directives ---------------------------------------------------- */

add_filter( 'wp_robots', 'gki_auth_robots_noindex', 20 );
add_filter( 'wpseo_robots', 'gki_auth_robots_noindex_string', 20 );
add_filter( 'rank_math/frontend/robots', 'gki_auth_robots_noindex_array', 20 );

/**
 * Whether the current request should carry noindex: any gated post, the
 * gated category archive, and the gate page itself. Applies to entitled
 * viewers too — a crawler is never entitled, and a signed-in admin's view
 * must not differ in what it tells search engines.
 */
function gki_auth_request_is_noindex() {
    if ( ! gki_auth_is_enabled() ) {
        return false;
    }
    if ( defined( 'GKI_GATE_ACTIVE' ) ) {
        return true;
    }
    return gki_auth_is_gated_page();
}

/** Core `wp_robots` (array of directive => value). */
function gki_auth_robots_noindex( $robots ) {
    if ( gki_auth_request_is_noindex() ) {
        unset( $robots['index'], $robots['follow'], $robots['max-image-preview'], $robots['max-snippet'], $robots['max-video-preview'] );
        $robots['noindex']   = true;
        $robots['nofollow']  = true;
        $robots['noarchive'] = true;
    }
    return $robots;
}

/** Yoast `wpseo_robots` (comma-separated string). */
function gki_auth_robots_noindex_string( $robots ) {
    if ( gki_auth_request_is_noindex() ) {
        return 'noindex, nofollow, noarchive';
    }
    return $robots;
}

/** Rank Math `rank_math/frontend/robots` (array of directive => directive). */
function gki_auth_robots_noindex_array( $robots ) {
    if ( gki_auth_request_is_noindex() ) {
        return array( 'noindex' => 'noindex', 'nofollow' => 'nofollow', 'noarchive' => 'noarchive' );
    }
    return $robots;
}

add_action( 'template_redirect', 'gki_auth_gated_page_headers', 4 );

/**
 * Runs just before the gate. On any gated request: an X-Robots-Tag header
 * (search engines honour it even when the HTML never renders), no-store so
 * neither Cloudflare nor a page-cache plugin keeps a copy, and no head
 * links that advertise the REST/oEmbed routes for this post.
 */
function gki_auth_gated_page_headers() {
    if ( ! gki_auth_is_enabled() || ! gki_auth_is_gated_page() ) {
        return;
    }
    header( 'X-Robots-Tag: noindex, nofollow, noarchive', true );
    nocache_headers();
    remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
    remove_action( 'wp_head', 'rest_output_link_wp_head' );
    remove_action( 'wp_head', 'wp_shortlink_wp_head' );
    remove_action( 'template_redirect', 'rest_output_link_header', 11 );
    remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
}

/* =========================================================================
   HELPER FUNCTIONS
   ========================================================================= */

/**
 * Check whether the current request is for a gated GKI docs page: a post in
 * the gated category (including its /embed/ and comment-feed variants, which
 * are still is_single()), or the gated category's own archive, whose
 * excerpts would otherwise be readable by anyone.
 */
function gki_auth_is_gated_page() {
    if ( is_single() ) {
        return has_category( GKI_DOCS_CATEGORY );
    }
    return is_category( GKI_DOCS_CATEGORY );
}

/* =========================================================================
   TRIAL CODES — time-boxed Help Center access for Insights trial customers
   whose trial is not visible to the organizations API. The code lives in
   Settings → GKI Auth Gate (never in this public repo). Redeeming it stores
   gki_trial_started_at / gki_trial_expires_at on the WP user; an unexpired
   trial counts as entitlement everywhere gki_auth_check_entitlement() runs
   (the page gate and the lockdown filters).

   @since 1.17.5
   ========================================================================= */

/**
 * The configured trial code, or '' when trial codes are off.
 */
function gki_auth_get_trial_code() {
    return trim( (string) get_option( 'gki_auth_trial_code', '' ) );
}

/**
 * Trial length in days (1–90, default 14).
 */
function gki_auth_get_trial_days() {
    $days = (int) get_option( 'gki_auth_trial_days', 14 );
    return ( $days >= 1 && $days <= 90 ) ? $days : 14;
}

/**
 * Unix time the user's trial ends, or 0 if they never redeemed one.
 * An active domain extension (Settings → Trial extensions by email domain) wins when
 * it ends later than the account's own trial; it never applies to accounts
 * that have not redeemed a code.
 */
function gki_auth_trial_expires_at( $user_id ) {
    $own = (int) get_user_meta( $user_id, 'gki_trial_expires_at', true );
    if ( $own <= 0 ) {
        return 0;
    }
    $extensions = gki_auth_trial_domain_extensions();
    $domain     = gki_auth_user_email_domain( $user_id );
    if ( $domain !== '' && isset( $extensions[ $domain ] ) ) {
        return max( $own, $extensions[ $domain ] );
    }
    return $own;
}

/**
 * Lowercased domain of the user's email (the GitKraken account email), or ''.
 */
function gki_auth_user_email_domain( $user_id ) {
    $user = get_userdata( $user_id );
    if ( ! $user || strpos( $user->user_email, '@' ) === false ) {
        return '';
    }
    return strtolower( substr( strrchr( $user->user_email, '@' ), 1 ) );
}

/* -------------------------------------------------------------------------
   Trial extensions by email domain (1.17.7). Stored as tracked records in the
   gki_auth_trial_domain_ext_log option, never deleted, so the settings page
   keeps a full history:
     id, domain, until (Y-m-d), added_at, added_by,
     revoked_at, revoked_by, replaced (bool: superseded by a newer extension)
   A record extends trials while it is neither revoked nor past its date.
   ------------------------------------------------------------------------- */

/**
 * All extension records, newest first.
 */
function gki_auth_trial_ext_records() {
    $records = get_option( 'gki_auth_trial_domain_ext_log', array() );
    if ( ! is_array( $records ) ) {
        return array();
    }
    // Records are appended in the order they were added; newest first.
    return array_reverse( array_values( $records ) );
}

/**
 * Unix time at the end of a Y-m-d date in the site timezone, or 0.
 */
function gki_auth_trial_ext_end_ts( $date ) {
    $end = DateTimeImmutable::createFromFormat( '!Y-m-d H:i:s', $date . ' 23:59:59', wp_timezone() );
    return $end ? $end->getTimestamp() : 0;
}

/**
 * 'active', 'expired', 'revoked' or 'replaced'.
 */
function gki_auth_trial_ext_status( $record ) {
    if ( ! empty( $record['revoked_at'] ) ) {
        return ! empty( $record['replaced'] ) ? 'replaced' : 'revoked';
    }
    return gki_auth_trial_ext_end_ts( $record['until'] ) >= time() ? 'active' : 'expired';
}

/**
 * Active extensions as domain => Unix time (end of that day, site timezone).
 */
function gki_auth_trial_domain_extensions() {
    $map = array();
    foreach ( gki_auth_trial_ext_records() as $record ) {
        if ( gki_auth_trial_ext_status( $record ) !== 'active' ) {
            continue;
        }
        $end = gki_auth_trial_ext_end_ts( $record['until'] );
        if ( ! isset( $map[ $record['domain'] ] ) || $end > $map[ $record['domain'] ] ) {
            $map[ $record['domain'] ] = $end;
        }
    }
    return $map;
}

/**
 * Normalize and validate a domain ("@Acme.COM " -> "acme.com"), or ''.
 */
function gki_auth_normalize_domain( $domain ) {
    $domain = strtolower( trim( ltrim( trim( (string) $domain ), '@' ) ) );
    return preg_match( '/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)+$/', $domain ) ? $domain : '';
}

/**
 * Number of accounts on a domain that have redeemed a trial.
 */
function gki_auth_trial_ext_account_count( $domain ) {
    $ids = get_users( array(
        'search'         => '*@' . $domain,
        'search_columns' => array( 'user_email' ),
        'meta_key'       => 'gki_trial_expires_at',
        'fields'         => 'ID',
        'number'         => 1000,
    ) );
    return count( $ids );
}

add_action( 'admin_post_gki_trial_ext_add', 'gki_auth_trial_ext_add' );
add_action( 'admin_post_gki_trial_ext_revoke', 'gki_auth_trial_ext_revoke' );

/**
 * Add an extension. Any extension still in force for the same domain is
 * closed as "replaced", so each domain has at most one active record.
 */
function gki_auth_trial_ext_add() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Not allowed.', 403 );
    }
    check_admin_referer( 'gki_trial_ext_add' );

    $domain = gki_auth_normalize_domain( isset( $_POST['gki_ext_domain'] ) ? wp_unslash( $_POST['gki_ext_domain'] ) : '' );
    $until  = isset( $_POST['gki_ext_until'] ) ? sanitize_text_field( wp_unslash( $_POST['gki_ext_until'] ) ) : '';
    $d      = DateTimeImmutable::createFromFormat( '!Y-m-d', $until, wp_timezone() );

    if ( $domain === '' ) {
        gki_auth_trial_ext_back( 'bad-domain' );
    }
    if ( ! $d || $d->format( 'Y-m-d' ) !== $until ) {
        gki_auth_trial_ext_back( 'bad-date' );
    }
    if ( gki_auth_trial_ext_end_ts( $until ) < time() ) {
        gki_auth_trial_ext_back( 'past-date' );
    }

    $who     = wp_get_current_user()->user_login;
    $now     = time();
    $records = get_option( 'gki_auth_trial_domain_ext_log', array() );
    $records = is_array( $records ) ? $records : array();
    foreach ( $records as &$record ) {
        if ( $record['domain'] === $domain && empty( $record['revoked_at'] ) ) {
            $record['revoked_at'] = $now;
            $record['revoked_by'] = $who;
            $record['replaced']   = true;
        }
    }
    unset( $record );
    $records[] = array(
        'id'         => wp_generate_uuid4(),
        'domain'     => $domain,
        'until'      => $until,
        'added_at'   => $now,
        'added_by'   => $who,
        'revoked_at' => 0,
        'revoked_by' => '',
        'replaced'   => false,
    );
    update_option( 'gki_auth_trial_domain_ext_log', $records, false );
    gki_auth_trial_ext_back( 'added' );
}

/**
 * Revoke an extension early. The record stays in the history.
 */
function gki_auth_trial_ext_revoke() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Not allowed.', 403 );
    }
    $id = isset( $_POST['gki_ext_id'] ) ? sanitize_key( wp_unslash( $_POST['gki_ext_id'] ) ) : '';
    check_admin_referer( 'gki_trial_ext_revoke_' . $id );

    $records = get_option( 'gki_auth_trial_domain_ext_log', array() );
    $records = is_array( $records ) ? $records : array();
    $found   = false;
    foreach ( $records as &$record ) {
        if ( $record['id'] === $id && empty( $record['revoked_at'] ) ) {
            $record['revoked_at'] = time();
            $record['revoked_by'] = wp_get_current_user()->user_login;
            $found                = true;
        }
    }
    unset( $record );
    if ( $found ) {
        update_option( 'gki_auth_trial_domain_ext_log', $records, false );
    }
    gki_auth_trial_ext_back( $found ? 'revoked' : 'not-found' );
}

/**
 * Back to the settings page with a result notice.
 */
function gki_auth_trial_ext_back( $result ) {
    wp_safe_redirect( add_query_arg(
        array( 'page' => 'gki-auth-settings', 'gki_ext' => $result ),
        admin_url( 'options-general.php' )
    ) . '#gki-trial-extensions' );
    exit;
}

/**
 * Settings page section: add form, notice, and the full extension history.
 */
function gki_auth_render_trial_extensions_section() {
    $notices = array(
        'added'      => array( 'success', 'Extension added.' ),
        'revoked'    => array( 'success', 'Extension revoked. Affected accounts are back on their own trial dates.' ),
        'bad-domain' => array( 'error', 'Enter a domain like acme.com.' ),
        'bad-date'   => array( 'error', 'Pick a valid end date.' ),
        'past-date'  => array( 'error', 'The end date is in the past.' ),
        'not-found'  => array( 'warning', 'That extension was already revoked or no longer exists.' ),
    );
    $result = isset( $_GET['gki_ext'] ) ? sanitize_key( wp_unslash( $_GET['gki_ext'] ) ) : '';
    $fmt    = get_option( 'date_format' );
    $dtfmt  = $fmt . ' ' . get_option( 'time_format' );
    ?>
    <hr />
    <h2 id="gki-trial-extensions">Trial extensions by email domain</h2>
    <p class="description" style="max-width:800px">
        Every account on the domain that has redeemed a trial keeps access through the end of the chosen date (site timezone), even if its own trial already ended; whichever date is later wins.
        Domains match exactly (<code>acme.com</code> does not cover <code>eu.acme.com</code>). Accounts that never redeemed a code are not affected.
        Adding a new date for a domain replaces its current extension. Records are kept after they end.
    </p>
    <?php if ( isset( $notices[ $result ] ) ) : ?>
        <div class="notice notice-<?php echo esc_attr( $notices[ $result ][0] ); ?> inline"><p><?php echo esc_html( $notices[ $result ][1] ); ?></p></div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:12px 0 16px">
        <input type="hidden" name="action" value="gki_trial_ext_add" />
        <?php wp_nonce_field( 'gki_trial_ext_add' ); ?>
        <label for="gki-ext-domain">Domain</label>
        <input type="text" id="gki-ext-domain" name="gki_ext_domain" placeholder="acme.com" class="regular-text" style="width:220px" required />
        <label for="gki-ext-until" style="margin-left:8px">Extend through</label>
        <input type="date" id="gki-ext-until" name="gki_ext_until" min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" required />
        <?php submit_button( 'Add extension', 'secondary', 'submit', false ); ?>
    </form>

    <?php
    $records = gki_auth_trial_ext_records();
    if ( empty( $records ) ) {
        echo '<p><em>No trial extensions yet.</em></p>';
        return;
    }
    $labels = array( 'active' => 'Active', 'expired' => 'Expired', 'revoked' => 'Revoked', 'replaced' => 'Replaced' );
    echo '<table class="widefat striped" style="max-width:1000px"><thead><tr>'
        . '<th>Domain</th><th>Extended through</th><th>Accounts with a trial</th><th>Added</th><th>Status</th><th></th>'
        . '</tr></thead><tbody>';
    foreach ( $records as $record ) {
        $status = gki_auth_trial_ext_status( $record );
        $state  = $labels[ $status ];
        if ( $status === 'revoked' || $status === 'replaced' ) {
            $state .= ' ' . wp_date( $dtfmt, (int) $record['revoked_at'] ) . ( $record['revoked_by'] !== '' ? ' by ' . $record['revoked_by'] : '' );
        }
        echo '<tr>';
        echo '<td><strong>' . esc_html( $record['domain'] ) . '</strong></td>';
        echo '<td>' . esc_html( wp_date( $fmt, gki_auth_trial_ext_end_ts( $record['until'] ) ) ) . '</td>';
        echo '<td>' . (int) gki_auth_trial_ext_account_count( $record['domain'] ) . '</td>';
        echo '<td>' . esc_html( wp_date( $dtfmt, (int) $record['added_at'] ) . ' by ' . $record['added_by'] ) . '</td>';
        echo '<td>' . esc_html( $state ) . '</td>';
        echo '<td>';
        if ( $status === 'active' ) {
            echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" onsubmit="return confirm(\'Revoke the extension for ' . esc_attr( $record['domain'] ) . '? Accounts on this domain go back to their own trial dates now.\');">';
            echo '<input type="hidden" name="action" value="gki_trial_ext_revoke" />';
            echo '<input type="hidden" name="gki_ext_id" value="' . esc_attr( $record['id'] ) . '" />';
            wp_nonce_field( 'gki_trial_ext_revoke_' . $record['id'] );
            echo '<button type="submit" class="button button-link-delete">Revoke</button>';
            echo '</form>';
        }
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}

/**
 * Whether the user has a redeemed, unexpired trial.
 */
function gki_auth_has_active_trial( $user_id ) {
    $expires = gki_auth_trial_expires_at( $user_id );
    return $expires > 0 && time() < $expires;
}

/**
 * Session length for trial accounts (since 1.17.7). An account with a trial
 * record whose licence check did not succeed signs in again every day, so the
 * plugin always holds a fresh GitKraken token and notices a conversion to a
 * paid plan right away. Licensed accounts keep WordPress's normal length.
 */
define( 'GKI_TRIAL_SESSION_SECONDS', DAY_IN_SECONDS );

add_filter( 'auth_cookie_expiration', 'gki_auth_trial_session_length', 10, 3 );

function gki_auth_trial_session_length( $length, $user_id, $remember ) {
    if ( (int) get_user_meta( $user_id, 'gki_trial_expires_at', true ) <= 0 ) {
        return $length;
    }
    if ( get_user_meta( $user_id, 'gki_insights_access', true ) === '1' ) {
        return $length;
    }
    return min( (int) $length, GKI_TRIAL_SESSION_SECONDS );
}

add_action( 'init', 'gki_auth_handle_trial_redemption' );

/**
 * Handle a trial-code POST from the no-access screen. Runs on init, before
 * the template gate, so the form can post back to the page it is on (no
 * wp-admin round trip, which some hosts block for subscribers). Always ends
 * in a redirect: back to the page (now readable) on success, or back with
 * ?gki_trial=<status> for the template to explain.
 */
function gki_auth_handle_trial_redemption() {
    if ( empty( $_POST['gki_trial_action'] ) || $_POST['gki_trial_action'] !== 'redeem' ) {
        return;
    }

    $back = remove_query_arg( 'gki_trial', gki_auth_get_current_url() );
    $back = wp_validate_redirect( $back, home_url( '/insights-expo/' ) );

    $nonce = isset( $_POST['_gki_trial_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_gki_trial_nonce'] ) ) : '';
    if ( ! is_user_logged_in() || ! wp_verify_nonce( $nonce, 'gki_redeem_trial' ) ) {
        gki_auth_trial_redirect( $back, 'error' );
    }

    $code = gki_auth_get_trial_code();
    if ( $code === '' ) {
        gki_auth_trial_redirect( $back, 'off' );
    }

    $user_id = get_current_user_id();

    // One trial per account: an ended trial cannot be renewed with the code
    // (a domain extension is the way to give an account more time).
    if ( (int) get_user_meta( $user_id, 'gki_trial_expires_at', true ) > 0 ) {
        gki_auth_trial_redirect( $back, gki_auth_has_active_trial( $user_id ) ? '' : 'used' );
    }

    // Throttle guessing: 5 wrong attempts per account locks the form for 15 minutes.
    $attempts_key = 'gki_trial_attempts_' . $user_id;
    $attempts     = (int) get_transient( $attempts_key );
    if ( $attempts >= 5 ) {
        gki_auth_trial_redirect( $back, 'locked' );
    }

    $entered = isset( $_POST['gki_trial_code'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['gki_trial_code'] ) ) ) : '';
    if ( $entered === '' || ! hash_equals( strtolower( $code ), strtolower( $entered ) ) ) {
        set_transient( $attempts_key, $attempts + 1, 15 * MINUTE_IN_SECONDS );
        gki_auth_trial_redirect( $back, 'invalid' );
    }

    delete_transient( $attempts_key );
    $now = time();
    update_user_meta( $user_id, 'gki_trial_started_at', $now );
    update_user_meta( $user_id, 'gki_trial_expires_at', $now + gki_auth_get_trial_days() * DAY_IN_SECONDS );

    // Reissue the session so the trial session length applies from now on.
    wp_set_auth_cookie( $user_id, true );

    gki_auth_trial_redirect( $back, '' );
}

/**
 * Redirect after a redemption attempt and stop.
 *
 * @param string $url    Page to return to.
 * @param string $status '' on success, otherwise a ?gki_trial= status.
 */
function gki_auth_trial_redirect( $url, $status ) {
    if ( $status !== '' ) {
        $url = add_query_arg( 'gki_trial', $status, $url );
    }
    nocache_headers();
    wp_safe_redirect( $url, 303 );
    exit;
}

/**
 * Message for a ?gki_trial= status on the no-access screen, or ''.
 */
function gki_auth_trial_message( $status ) {
    $messages = array(
        'invalid' => "That code isn't valid. Check it and try again.",
        'locked'  => 'Too many attempts. Try again in 15 minutes.',
        'error'   => 'Your session expired. Reload the page and try again.',
        'off'     => 'Trial codes are not available right now.',
    );
    return isset( $messages[ $status ] ) ? $messages[ $status ] : '';
}

/**
 * Get the configured OAuth client ID.
 */
function gki_auth_get_client_id() {
    return get_option( 'gki_auth_client_id', '' );
}

/**
 * Whether the auth gate is enabled in settings.
 */
function gki_auth_is_enabled() {
    return (bool) get_option( 'gki_auth_enabled', false );
}

/**
 * Get the configured licensing API endpoint.
 */
function gki_auth_get_licensing_endpoint() {
    return get_option( 'gki_auth_licensing_endpoint', '' );
}

/**
 * Get the cache TTL in seconds (stored as hours in settings).
 */
function gki_auth_get_cache_ttl() {
    $hours = (int) get_option( 'gki_auth_cache_ttl', 24 );
    return $hours * 3600;
}

/**
 * Get the upgrade URL for users without Insights access.
 */
function gki_auth_get_upgrade_url() {
    return get_option( 'gki_auth_upgrade_url', 'https://www.gitkraken.com/insights' );
}

/**
 * Get the current request URL (for redirect-after-login).
 */
function gki_auth_get_current_url() {
    $protocol = is_ssl() ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Build the custom login URL for help center visitors.
 * Points to /insights-expo/login/ which triggers the OAuth flow
 * without exposing the WP admin login page.
 *
 * @since 1.10.1
 * @param string $redirect_to URL to return to after login.
 * @return string Login URL.
 */
function gki_auth_get_login_url( $redirect_to = '' ) {
    if ( empty( $redirect_to ) ) {
        $redirect_to = home_url( '/insights-expo/' );
    }
    return home_url( '/insights-expo/login/?redirect_to=' . rawurlencode( $redirect_to ) );
}

/**
 * Build the switch-account URL.
 * Logs the user out of WP and redirects to the custom login endpoint.
 *
 * @since 1.10.1
 * @param string $redirect_to URL to return to after re-login.
 * @return string Logout-then-login URL.
 */
function gki_auth_get_switch_account_url( $redirect_to = '' ) {
    $login_url = gki_auth_get_login_url( $redirect_to );
    return wp_logout_url( $login_url );
}
