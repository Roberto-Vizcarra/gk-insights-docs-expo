<?php
/**
 * GKI Docs Helper — Custom Single Post Template (v1.8.1)
 *
 * 3-column layout for insights-expo posts:
 *   Left:   Site search + collapsible category navigation (frontmatter-based)
 *   Center: Post content — Intro → Cards → Overview on index pages
 *   Right:  "On this page" TOC (JS-populated, hidden on index pages)
 *
 * Nav supports nested sub-groups via nav_parent (e.g. Metrics > DORA > individual metrics).
 * Active section, sub-group, and page are all visually highlighted.
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
$current_id    = get_the_ID();

// SVG chevron used in collapsible nav sections
$chevron_svg = '<svg class="gki-nav-chevron" width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true"><path d="M4 2l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

get_header();
?>

<main class="gki-layout" id="gki-layout">

  <!-- Left sidebar: search + navigation -->
  <aside class="gki-sidebar gki-sidebar--left" role="navigation" aria-label="<?php esc_attr_e( 'Insights documentation', 'gki-docs-helper' ); ?>">
    <nav class="gki-nav">
      <!-- Site search -->
      <div class="gki-site-search">
        <input type="text" class="gki-site-search-input" placeholder="Search docs…" aria-label="Search documentation">
        <div class="gki-site-search-results" hidden></div>
      </div>

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

          // "Home" link
          if ( $nav_structure['main_index'] ) {
              $home_active = ( $current_id === $nav_structure['main_index']['post']->ID );
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
              // --- Determine active states ---
              $section_active = false;
              $index_active   = false;

              if ( $section['index'] && $current_id === $section['index']['post']->ID ) {
                  $section_active = true;
                  $index_active   = true;
              }
              foreach ( $section['children'] as $child ) {
                  if ( $current_id === $child['post']->ID ) {
                      $section_active = true;
                      break;
                  }
                  if ( ! empty( $child['children'] ) ) {
                      foreach ( $child['children'] as $gc ) {
                          if ( $current_id === $gc['post']->ID ) {
                              $section_active = true;
                              break 2;
                          }
                      }
                  }
              }

              $section_classes = 'gki-nav-section';
              if ( $section_active ) {
                  $section_classes .= ' gki-nav-section--active gki-nav-section--open';
              }

              echo '<li class="' . esc_attr( $section_classes ) . '">';

              // Section heading with chevron
              if ( $section['index'] ) {
                  printf(
                      '<a href="%s" class="gki-nav-section-link"%s>%s%s</a>',
                      esc_url( get_permalink( $section['index']['post'] ) ),
                      $index_active ? ' aria-current="page"' : '',
                      esc_html( $section['index']['label'] ),
                      $chevron_svg
                  );
              }

              // Children (may include sub-groups)
              if ( ! empty( $section['children'] ) ) {
                  echo '<ul class="gki-nav-children">';
                  foreach ( $section['children'] as $child ) {
                      $child_active      = ( $current_id === $child['post']->ID );
                      $has_subchildren   = ! empty( $child['children'] );

                      if ( $has_subchildren ) {
                          // --- Sub-group: collapsible with its own children ---
                          $subgroup_active = $child_active;
                          foreach ( $child['children'] as $gc ) {
                              if ( $current_id === $gc['post']->ID ) {
                                  $subgroup_active = true;
                                  break;
                              }
                          }

                          $sg_classes = 'gki-nav-subgroup';
                          if ( $subgroup_active ) {
                              $sg_classes .= ' gki-nav-subgroup--active gki-nav-subgroup--open';
                          }

                          echo '<li class="' . esc_attr( $sg_classes ) . '">';
                          printf(
                              '<a href="%s" class="gki-nav-subgroup-link"%s>%s%s</a>',
                              esc_url( get_permalink( $child['post'] ) ),
                              $child_active ? ' aria-current="page"' : '',
                              esc_html( $child['label'] ),
                              $chevron_svg
                          );

                          echo '<ul class="gki-nav-subchildren">';
                          foreach ( $child['children'] as $gc ) {
                              $gc_active = ( $current_id === $gc['post']->ID );
                              printf(
                                  '<li class="gki-nav-child%s"><a href="%s"%s>%s</a></li>',
                                  $gc_active ? ' gki-nav-child--active' : '',
                                  esc_url( get_permalink( $gc['post'] ) ),
                                  $gc_active ? ' aria-current="page"' : '',
                                  esc_html( $gc['label'] )
                              );
                          }
                          echo '</ul></li>';
                      } else {
                          // --- Flat child ---
                          printf(
                              '<li class="gki-nav-child%s"><a href="%s"%s>%s</a></li>',
                              $child_active ? ' gki-nav-child--active' : '',
                              esc_url( get_permalink( $child['post'] ) ),
                              $child_active ? ' aria-current="page"' : '',
                              esc_html( $child['label'] )
                          );
                      }
                  }
                  echo '</ul>';
              }

              echo '</li>';
          }

          echo '</ul>';
          wp_reset_postdata();
      } else {
          // Priority 3: Alphabetical fallback
          $nav_posts = get_posts( array(
              'category_name'  => GKI_DOCS_CATEGORY,
              'posts_per_page' => -1,
              'orderby'        => 'title',
              'order'          => 'ASC',
          ) );
          if ( $nav_posts ) {
              echo '<ul class="gki-nav-list">';
              foreach ( $nav_posts as $nav_post ) {
                  $is_active = ( $current_id === $nav_post->ID );
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
    // Breadcrumb — shown on all pages except main-index
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
    // Page title <h1>
    if ( $page_type === 'main-index' ) {
        echo '<h1 class="gki-page-title">GitKraken Insights Documentation</h1>';
    } else {
        $title_text = get_post_meta( get_the_ID(), 'nav_label', true ) ?: get_the_title();
        printf( '<h1 class="gki-page-title">%s</h1>', esc_html( $title_text ) );
    }
    ?>

    <?php
    // Post content — all index pages use Intro → Cards → Overview pattern
    while ( have_posts() ) {
        the_post();

        if ( $is_index && ! empty( $child_cards ) ) {
            // Buffer content so we can split at first <hr>
            ob_start();
            the_content();
            $full_content = ob_get_clean();

            // Split at first <hr> — intro above, overview below
            $hr_pos = strpos( $full_content, '<hr' );
            if ( $hr_pos !== false ) {
                $intro = substr( $full_content, 0, $hr_pos );
                $rest  = substr( $full_content, $hr_pos );
            } else {
                $intro = $full_content;
                $rest  = '';
            }

            // Intro
            echo $intro;
            // Cards (front and center)
            gki_docs_render_card_grid( $child_cards );
            // Overview content below cards
            if ( trim( $rest ) ) {
                echo '<div class="gki-below-cards">' . $rest . '</div>';
            }
        } else {
            the_content();
        }
    }
    ?>
  </article>

  <!-- Right sidebar: on-this-page TOC (JS-populated, hidden on index pages via CSS) -->
  <aside class="gki-sidebar gki-sidebar--right" role="complementary" aria-label="<?php esc_attr_e( 'On this page', 'gki-docs-helper' ); ?>">
    <div class="gki-sidebar-toc" id="gki-sidebar-toc"></div>
  </aside>

</main>

<?php get_footer(); ?>
