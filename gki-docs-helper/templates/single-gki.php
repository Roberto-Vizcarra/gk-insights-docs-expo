<?php
/**
 * GKI Docs Helper — Custom Single Post Template
 *
 * 3-column layout for insights-expo posts:
 *   Left:   Category navigation (WP menu or auto-generated)
 *   Center: Post content (the_content)
 *   Right:  "On this page" TOC (JS-populated)
 *
 * The left nav can be managed two ways:
 *   1. Assign a WP menu to the "GKI Insights Sidebar Navigation" location
 *      (Appearance → Menus → Manage Locations) — full manual control.
 *   2. If no menu is assigned, the nav auto-generates from all posts
 *      in the insights-expo category, sorted alphabetically.
 *
 * The right TOC is built by gki-docs.js from headings in the content.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<main class="gki-layout" id="gki-layout">

  <!-- Left sidebar: navigation -->
  <aside class="gki-sidebar gki-sidebar--left" role="navigation" aria-label="<?php esc_attr_e( 'Insights documentation', 'gki-docs-helper' ); ?>">
    <nav class="gki-nav">
      <a href="/<?php echo esc_attr( GKI_DOCS_CATEGORY ); ?>/" class="gki-nav-title">GitKraken Insights</a>
      <?php
      if ( has_nav_menu( 'gki-insights-nav' ) ) {
          wp_nav_menu( array(
              'theme_location' => 'gki-insights-nav',
              'container'      => false,
              'menu_class'     => 'gki-nav-list',
              'depth'          => 2,
          ) );
      } else {
          // Auto-generate nav from category posts
          $nav_posts = get_posts( array(
              'category_name'  => GKI_DOCS_CATEGORY,
              'posts_per_page' => -1,
              'orderby'        => 'title',
              'order'          => 'ASC',
          ) );
          if ( $nav_posts ) {
              echo '<ul class="gki-nav-list">';
              foreach ( $nav_posts as $nav_post ) {
                  $is_active = ( get_the_ID() === $nav_post->ID );
                  printf(
                      '<li class="gki-nav-item%s"><a href="%s"%s>%s</a></li>',
                      $is_active ? ' gki-nav-item--active' : '',
                      esc_url( get_permalink( $nav_post ) ),
                      $is_active ? ' aria-current="page"' : '',
                      esc_html( $nav_post->post_title )
                  );
              }
              echo '</ul>';
              wp_reset_postdata();
          }
      }
      ?>
    </nav>
  </aside>

  <!-- Center: post content -->
  <article class="gki-content-main">
    <?php
    while ( have_posts() ) :
        the_post();
        the_content();
    endwhile;
    ?>
  </article>

  <!-- Right sidebar: on-this-page TOC (JS-populated) -->
  <aside class="gki-sidebar gki-sidebar--right" role="complementary" aria-label="<?php esc_attr_e( 'On this page', 'gki-docs-helper' ); ?>">
    <div class="gki-sidebar-toc" id="gki-sidebar-toc"></div>
  </aside>

</main>

<?php get_footer(); ?>
