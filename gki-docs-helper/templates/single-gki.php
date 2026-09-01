<?php
/**
 * GKI Docs Helper — Custom Single Post Template
 *
 * 3-column layout for insights-expo posts:
 *   Left:   Category navigation (frontmatter-based, WP menu, or auto-generated)
 *   Center: Post content — card grid on index pages, long-form on content pages
 *   Right:  "On this page" TOC (JS-populated, hidden on index pages)
 *
 * The left nav builds from frontmatter fields (nav_category, nav_order, nav_label).
 * Falls back to alphabetical listing when no frontmatter is present.
 *
 * Index pages (page_type: main-index or index) auto-render a card grid of child
 * pages below the post content. Content pages render the_content() with a sidebar TOC.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$page_type     = gki_docs_get_page_type();
$is_index      = in_array( $page_type, array( 'index', 'main-index' ), true );
$nav_structure = gki_docs_get_nav_structure();
$crumbs        = gki_docs_get_breadcrumb_data();
$child_cards   = $is_index ? gki_docs_get_child_pages() : array();
$current_cat   = get_post_meta( get_the_ID(), 'nav_category', true );

get_header();
?>

<main class="gki-layout" id="gki-layout">

  <!-- Left sidebar: navigation -->
  <aside class="gki-sidebar gki-sidebar--left" role="navigation" aria-label="<?php esc_attr_e( 'Insights documentation', 'gki-docs-helper' ); ?>">
    <nav class="gki-nav">
      <?php if ( $nav_structure && $nav_structure['main_index'] ) : ?>
        <a href="<?php echo esc_url( get_permalink( $nav_structure['main_index']['post'] ) ); ?>" class="gki-nav-title">GitKraken Insights</a>
      <?php else : ?>
        <a href="/<?php echo esc_attr( GKI_DOCS_CATEGORY ); ?>/" class="gki-nav-title">GitKraken Insights</a>
      <?php endif; ?>

      <?php
      if ( has_nav_menu( 'gki-insights-nav' ) ) {
          // Priority 1: Manual WP menu
          wp_nav_menu( array(
              'theme_location' => 'gki-insights-nav',
              'container'      => false,
              'menu_class'     => 'gki-nav-list',
              'depth'          => 2,
          ) );
      } elseif ( $nav_structure ) {
          // Priority 2: Frontmatter-based hierarchical nav
          echo '<ul class="gki-nav-list">';

          // "Home" link (main index)
          if ( $nav_structure['main_index'] ) {
              $home_active = ( get_the_ID() === $nav_structure['main_index']['post']->ID );
              printf(
                  '<li class="gki-nav-item%s"><a href="%s"%s>%s</a></li>',
                  $home_active ? ' gki-nav-item--active' : '',
                  esc_url( get_permalink( $nav_structure['main_index']['post'] ) ),
                  $home_active ? ' aria-current="page"' : '',
                  esc_html( $nav_structure['main_index']['label'] )
              );
          }

          // Section groups
          foreach ( $nav_structure['sections'] as $cat_slug => $section ) {
              // Determine if this section is active
              $section_active = false;
              $index_active   = false;

              if ( $section['index'] && get_the_ID() === $section['index']['post']->ID ) {
                  $section_active = true;
                  $index_active   = true;
              }
              foreach ( $section['children'] as $child ) {
                  if ( get_the_ID() === $child['post']->ID ) {
                      $section_active = true;
                      break;
                  }
              }

              $section_classes = 'gki-nav-section';
              if ( $section_active ) {
                  $section_classes .= ' gki-nav-section--active';
              }

              echo '<li class="' . esc_attr( $section_classes ) . '">';

              // Section heading (links to section index)
              if ( $section['index'] ) {
                  printf(
                      '<a href="%s" class="gki-nav-section-link"%s>%s</a>',
                      esc_url( get_permalink( $section['index']['post'] ) ),
                      $index_active ? ' aria-current="page"' : '',
                      esc_html( $section['index']['label'] )
                  );
              }

              // Child pages
              if ( ! empty( $section['children'] ) ) {
                  echo '<ul class="gki-nav-children">';
                  foreach ( $section['children'] as $child ) {
                      $child_active = ( get_the_ID() === $child['post']->ID );
                      printf(
                          '<li class="gki-nav-child%s"><a href="%s"%s>%s</a></li>',
                          $child_active ? ' gki-nav-child--active' : '',
                          esc_url( get_permalink( $child['post'] ) ),
                          $child_active ? ' aria-current="page"' : '',
                          esc_html( $child['label'] )
                      );
                  }
                  echo '</ul>';
              }

              echo '</li>';
          }

          echo '</ul>';
          wp_reset_postdata();
      } else {
          // Priority 3: Alphabetical fallback (no frontmatter)
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
  <article class="gki-content-main gki-page">
    <?php
    // Breadcrumb (not shown on main index)
    if ( ! empty( $crumbs ) && count( $crumbs ) > 1 ) :
    ?>
    <nav class="gki-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'gki-docs-helper' ); ?>">
      <?php foreach ( $crumbs as $i => $crumb ) :
          if ( $i > 0 ) : ?>
            <span class="gki-crumb-sep" aria-hidden="true">/</span>
          <?php endif;

          if ( $crumb['url'] ) : ?>
            <a href="<?php echo esc_url( $crumb['url'] ); ?>"><?php echo esc_html( $crumb['label'] ); ?></a>
          <?php else : ?>
            <span class="gki-crumb-current" aria-current="page"><?php echo esc_html( $crumb['label'] ); ?></span>
          <?php endif;
      endforeach; ?>
    </nav>
    <?php endif; ?>

    <?php
    // Post content — on main-index, insert card grid after the intro (first <hr>)
    while ( have_posts() ) {
        the_post();

        if ( 'main-index' === $page_type && ! empty( $child_cards ) ) {
            // Buffer content so we can split it
            ob_start();
            the_content();
            $full_content = ob_get_clean();

            // Split at first <hr> — intro above, details below
            $hr_pos = strpos( $full_content, '<hr' );
            if ( $hr_pos !== false ) {
                $intro = substr( $full_content, 0, $hr_pos );
                $rest  = substr( $full_content, $hr_pos );
            } else {
                $intro = $full_content;
                $rest  = '';
            }

            echo $intro;
            // Card grid right after intro
            gki_docs_render_card_grid( $child_cards );
            // Remaining content below cards
            if ( $rest ) {
                echo '<div class="gki-below-cards">' . $rest . '</div>';
            }
        } else {
            the_content();
        }
    }

    // Card grid for regular index pages (not main-index)
    if ( 'main-index' !== $page_type && $is_index && ! empty( $child_cards ) ) {
        gki_docs_render_card_grid( $child_cards );
    } ?>
  </article>

  <!-- Right sidebar: on-this-page TOC (JS-populated, hidden on index pages via CSS) -->
  <aside class="gki-sidebar gki-sidebar--right" role="complementary" aria-label="<?php esc_attr_e( 'On this page', 'gki-docs-helper' ); ?>">
    <div class="gki-sidebar-toc" id="gki-sidebar-toc"></div>
  </aside>

</main>

<?php get_footer(); ?>
