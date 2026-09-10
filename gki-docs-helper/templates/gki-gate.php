<?php
/**
 * GKI Docs Helper — Auth Gate Template
 *
 * Displayed when a user is not authenticated or lacks Insights entitlement.
 * Two variants based on $gki_gate_reason:
 *   'login'     — user is not logged in
 *   'no-access' — user is logged in but subscription lacks Insights
 *
 * @since 1.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$reason    = get_query_var( 'gki_gate_reason', 'login' );
$login_url = get_query_var( 'gki_gate_login_url', wp_login_url() );
$upgrade_url = gki_auth_get_upgrade_url();

get_header();
?>

<main class="gki-layout" id="gki-layout">
  <div class="gki-gate">
    <div class="gki-gate-card">

      <!-- GitKraken logo -->
      <div class="gki-gate-logo">
        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <rect width="40" height="40" rx="8" fill="#7900C9"/>
          <text x="50%" y="55%" dominant-baseline="middle" text-anchor="middle" fill="white" font-size="18" font-weight="700" font-family="Inter, system-ui, sans-serif">GK</text>
        </svg>
      </div>

      <?php if ( $reason === 'login' ) : ?>

        <h1 class="gki-gate-title">Sign in to access Insights documentation</h1>
        <p class="gki-gate-message">
          The GitKraken Insights Help Center is available to users with an active
          Insights subscription. Sign in with your GitKraken account to continue.
        </p>
        <a href="<?php echo esc_url( $login_url ); ?>" class="gki-gate-button gki-gate-button--primary">
          Sign in with GitKraken
        </a>
        <p class="gki-gate-footer">
          Don't have an account?
          <a href="<?php echo esc_url( $upgrade_url ); ?>">Learn about GitKraken Insights</a>
        </p>

      <?php else : ?>

        <h1 class="gki-gate-title">Insights subscription required</h1>
        <p class="gki-gate-message">
          Your GitKraken account doesn't currently include access to Insights.
          Upgrade your subscription to access the full Help Center documentation.
        </p>
        <a href="<?php echo esc_url( $upgrade_url ); ?>" class="gki-gate-button gki-gate-button--primary">
          View Insights plans
        </a>
        <p class="gki-gate-footer">
          Think this is a mistake?
          <a href="https://www.gitkraken.com/contact">Contact support</a>
        </p>

        <?php
        // Provide a logout link so users can switch accounts
        $logout_url = wp_logout_url( gki_auth_get_current_url() );
        ?>
        <p class="gki-gate-switch">
          Signed in as <?php echo esc_html( wp_get_current_user()->user_email ); ?>.
          <a href="<?php echo esc_url( $logout_url ); ?>">Switch account</a>
        </p>

      <?php endif; ?>

    </div>
  </div>
</main>

<?php get_footer(); ?>
