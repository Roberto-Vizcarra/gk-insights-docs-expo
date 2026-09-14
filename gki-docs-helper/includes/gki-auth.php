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
    $entitled = gki_auth_check_entitlement( get_current_user_id() );

    if ( ! $entitled ) {
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
        // Clear cached entitlement so next visit triggers re-auth
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
            . '<p class="description">URL shown to users without Insights access. Links to upgrade/pricing page.</p>',
            esc_attr( $val )
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
        echo '</table>';
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
   HELPER FUNCTIONS
   ========================================================================= */

/**
 * Check whether the current request is for a gated GKI docs page.
 * Uses the same detection as the main plugin but works before template_include.
 */
function gki_auth_is_gated_page() {
    if ( ! is_single() ) {
        return false;
    }
    return has_category( GKI_DOCS_CATEGORY );
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
