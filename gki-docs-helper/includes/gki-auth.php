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
 * Call the GitKraken licensing API to check Insights entitlement.
 *
 * Retrieves the user's OAuth access token (stored by the OIDC plugin)
 * and calls the configured licensing endpoint.
 *
 * @param int $user_id WordPress user ID.
 * @return bool True if the API confirms Insights access.
 */
function gki_auth_call_licensing_api( $user_id ) {
    $endpoint = gki_auth_get_licensing_endpoint();
    if ( empty( $endpoint ) ) {
        // No endpoint configured — fail closed (deny access)
        error_log( 'GKI Auth: Licensing API endpoint not configured.' );
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

    // Call the licensing endpoint
    $response = wp_remote_get( $endpoint, array(
        'timeout' => 10,
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
            'Accept'        => 'application/json',
        ),
    ) );

    // Handle request failure
    if ( is_wp_error( $response ) ) {
        error_log( 'GKI Auth: Licensing API request failed — ' . $response->get_error_message() );
        // Fail closed on API error — deny access
        return false;
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body        = wp_remote_retrieve_body( $response );

    // Handle 401 — token may be expired
    if ( $status_code === 401 ) {
        error_log( 'GKI Auth: Licensing API returned 401 — token may be expired for user ' . $user_id );
        // Clear cached entitlement so next visit triggers re-auth
        delete_user_meta( $user_id, 'gki_insights_access' );
        delete_user_meta( $user_id, 'gki_insights_checked_at' );
        return false;
    }

    // Handle non-200 responses
    if ( $status_code !== 200 ) {
        error_log( 'GKI Auth: Licensing API returned HTTP ' . $status_code );
        return false;
    }

    $data = json_decode( $body, true );
    if ( ! is_array( $data ) ) {
        error_log( 'GKI Auth: Licensing API returned invalid JSON.' );
        return false;
    }

    // ---------------------------------------------------------------
    // TODO: Update this check to match the actual API response format.
    //
    // Examples of what this might look like depending on the API:
    //
    //   return ! empty( $data['insights'] );
    //   return ! empty( $data['entitlements']['insights'] );
    //   return in_array( 'insights', $data['products'] ?? [], true );
    //   return ( $data['subscription']['plan'] ?? '' ) === 'insights';
    //
    // For now, checks for a top-level 'insights' boolean field.
    // ---------------------------------------------------------------
    return ! empty( $data['insights'] );
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

    // Build the login URL, preserving the requested page as redirect target
    $login_url = wp_login_url( gki_auth_get_current_url() );

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
        'default'           => 1800, // 30 minutes
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

    add_settings_field( 'gki_auth_licensing_endpoint', 'Licensing API Endpoint', function () {
        $val = get_option( 'gki_auth_licensing_endpoint', '' );
        printf(
            '<input type="url" name="gki_auth_licensing_endpoint" value="%s" class="regular-text" '
            . 'placeholder="https://gitkraken.dev/api/v1/subscription" />'
            . '<p class="description">The API endpoint that returns Insights entitlement for a bearer token.</p>',
            esc_attr( $val )
        );
    }, 'gki-auth-settings', 'gki_auth_main' );

    add_settings_field( 'gki_auth_cache_ttl', 'Cache TTL (seconds)', function () {
        $val = get_option( 'gki_auth_cache_ttl', 1800 );
        printf(
            '<input type="number" name="gki_auth_cache_ttl" value="%d" min="60" max="86400" /> '
            . '<p class="description">How long to cache entitlement results per user. Default: 1800 (30 minutes).</p>',
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
 * Get the cache TTL in seconds.
 */
function gki_auth_get_cache_ttl() {
    return (int) get_option( 'gki_auth_cache_ttl', 1800 );
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
