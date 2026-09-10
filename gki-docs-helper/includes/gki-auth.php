<?php
/**
 * GKI Docs Helper — Auth Gate Module
 *
 * Gates all insights-expo pages behind GitKraken authentication + Insights
 * subscription entitlement. Works alongside the OpenID Connect Generic Client
 * plugin which handles the OAuth login flow.
 *
 * Flow:
 *   1. Check if current page is a GKI docs page
 *   2. Check if user has a WordPress session (created by OIDC plugin after OAuth)
 *   3. Check cached Insights entitlement in user meta
 *   4. If cache expired, call licensing API to re-validate
 *   5. Allow or block based on entitlement
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
   Bypasses the WP admin login page for help center visitors.
   ========================================================================= */

add_action( 'init', 'gki_auth_intercept_login_endpoint' );

/**
 * Handle the custom login endpoint at /insights-expo/login/.
 *
 * Redirects through the OIDC plugin's authorization flow, skipping the
 * WP admin login form entirely. The ?action=openid-connect-authorize
 * parameter tells the OIDC plugin to auto-redirect to the OAuth provider
 * without rendering the login form.
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

    // Build the OAuth authorize URL directly from OIDC plugin settings.
    // This bypasses wp_login_url() entirely, avoiding conflicts with
    // login-hiding plugins that make wp-login.php return a 404.
    $oidc_settings = get_option( 'openid_connect_generic_settings', array() );
    $client_id     = isset( $oidc_settings['client_id'] ) ? $oidc_settings['client_id'] : '';
    $scope         = isset( $oidc_settings['scope'] ) ? $oidc_settings['scope'] : 'email profile';
    $endpoint      = isset( $oidc_settings['endpoint_login'] ) ? $oidc_settings['endpoint_login'] : '';

    if ( empty( $client_id ) || empty( $endpoint ) ) {
        error_log( 'GKI Auth: OIDC plugin settings not found — cannot build authorize URL.' );
        wp_die(
            'Single sign-on is not configured yet. Please contact your administrator.',
            'Login Unavailable',
            array( 'response' => 503 )
        );
    }

    // Generate state + nonce matching the OIDC plugin's transient format
    // so the callback at admin-ajax.php validates correctly.
    $state = wp_generate_password( 32, false );
    set_transient( 'openid-connect-generic-state--' . $state, array(
        'redirect_to' => $redirect_to,
        'state'       => $state,
    ), 180 );

    // Callback URL — where the provider sends the auth code.
    $redirect_uri = admin_url( 'admin-ajax.php?action=openid-connect-authorize' );

    // Redirect straight to the OAuth provider (no WP login page involved).
    $auth_url = add_query_arg( array(
        'response_type' => 'code',
        'client_id'     => $client_id,
        'scope'         => $scope,
        'redirect_uri'  => $redirect_uri,
        'state'         => $state,
    ), $endpoint );

    wp_redirect( $auth_url );
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

    // Get the OAuth access token stored by OpenID Connect Generic plugin.
    // The OIDC plugin stores tokens in user meta under these keys:
    //   openid-connect-generic-last-token-response  (full token response)
    //   openid-connect-generic-last-id-token-claim  (decoded ID token claims)
    $token_response = get_user_meta( $user_id, 'openid-connect-generic-last-token-response', true );

    if ( empty( $token_response ) || empty( $token_response['access_token'] ) ) {
        error_log( 'GKI Auth: No OAuth access token found for user ' . $user_id );
        return false;
    }

    $access_token = $token_response['access_token'];

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

    // Settings section
    add_settings_section(
        'gki_auth_main',
        'Auth Gate Configuration',
        function () {
            echo '<p>Configure the authentication gate for GKI Help Center pages. '
               . 'Requires the OpenID Connect Generic Client plugin for OAuth login.</p>';
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
        $oidc_active = is_plugin_active( 'daggerhart-openid-connect-generic/openid-connect-generic.php' );
        $endpoint    = get_option( 'gki_auth_licensing_endpoint', '' );
        $enabled     = get_option( 'gki_auth_enabled', false );

        echo '<table class="widefat" style="max-width:600px">';
        echo '<tr><td>OIDC Plugin Active</td><td>' . ( $oidc_active ? '✅ Yes' : '❌ Not detected' ) . '</td></tr>';
        echo '<tr><td>Licensing Endpoint</td><td>' . ( $endpoint ? esc_html( $endpoint ) : '⚠️ Not configured' ) . '</td></tr>';
        echo '<tr><td>Auth Gate</td><td>' . ( $enabled ? '🔒 Enabled' : '🔓 Disabled' ) . '</td></tr>';
        echo '</table>';
        ?>
    </div>
    <?php
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
 * Points to /insights-expo/login/ which triggers the OIDC flow
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
