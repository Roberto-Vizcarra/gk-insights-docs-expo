<?php
/**
 * Plugin Name: GKI Docs Helper
 * Plugin URI:  https://gitkraken.com
 * Description: Custom styling, Parsedown cleanup, and JS support for GitKraken Insights Help Center pages in the "insights-expo" category.
 * Version:     1.8.6
 * Author:      GitKraken
 * Author URI:  https://gitkraken.com
 * License:     GPL-2.0-or-later
 * Text Domain: gki-docs-helper
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GKI_DOCS_VERSION', '1.8.6' );
define( 'GKI_DOCS_PATH', plugin_dir_path( __FILE__ ) );
define( 'GKI_DOCS_URL', plugin_dir_url( __FILE__ ) );

/**
 * The category slug this plugin targets.
 * Change this value to target a different category.
 */
define( 'GKI_DOCS_CATEGORY', 'insights-expo' );

/* =========================================================================
   1. CONDITIONAL ASSET LOADING
   ========================================================================= */

add_action( 'wp_enqueue_scripts', 'gki_docs_enqueue_assets' );

/**
 * Enqueue the shared docs stylesheet and optional JS only on posts
 * that belong to the target category.
 */
function gki_docs_enqueue_assets() {
    if ( ! gki_docs_is_target_post() ) {
        return;
    }

    // Use file modification time for cache busting — auto-updates on deploy
    $css_ver = GKI_DOCS_VERSION;
    $js_ver  = GKI_DOCS_VERSION;

    // Tabler Icons webfont — used for card icons on index pages
    wp_enqueue_style(
        'tabler-icons',
        'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.6.0/dist/tabler-icons.min.css',
        array(),
        '3.6.0'
    );

    // Shared docs stylesheet — replaces per-file <style> blocks
    wp_enqueue_style(
        'gki-docs-styles',
        GKI_DOCS_URL . 'css/gki-docs.css',
        array(),
        $css_ver
    );

    // Optional JS for interactive features (TOC, search, collapsible, etc.)
    wp_enqueue_script(
        'gki-docs-scripts',
        GKI_DOCS_URL . 'js/gki-docs.js',
        array(),
        $js_ver,
        true // load in footer
    );

    // Build site-wide search index for JS
    $search_posts = get_posts( array(
        'category_name'  => GKI_DOCS_CATEGORY,
        'posts_per_page' => -1,
    ) );
    $search_index = array();
    foreach ( $search_posts as $sp ) {
        $sp_type = get_post_meta( $sp->ID, 'page_type', true );
        $sp_cat  = get_post_meta( $sp->ID, 'nav_category', true );
        if ( $sp_type === 'main-index' || $sp_cat === 'hidden' ) {
            continue;
        }
        $search_index[] = array(
            'title'   => get_post_meta( $sp->ID, 'nav_label', true ) ?: $sp->post_title,
            'desc'    => get_post_meta( $sp->ID, 'card_description', true ) ?: '',
            'url'     => get_permalink( $sp ),
            'type'    => $sp_type ?: 'content',
            'cat'     => $sp_cat ?: '',
            'excerpt' => wp_strip_all_tags( wp_trim_words( $sp->post_content, 30, '' ) ),
        );
    }
    wp_localize_script( 'gki-docs-scripts', 'gkiSearchData', array(
        'pages' => $search_index,
    ) );
}

/* =========================================================================
   2. PARSEDOWN CLEANUP — the_content FILTER
   ========================================================================= */

add_filter( 'the_content', 'gki_docs_clean_parsedown', 15 );

/**
 * Clean up Parsedown artifacts in the rendered HTML:
 * - Remove empty <p> tags (including those with only whitespace)
 * - Remove stray <br> tags injected between block elements
 * - Strip empty paragraphs inside grid containers
 *
 * Runs only on target-category posts.
 */
function gki_docs_clean_parsedown( $content ) {
    if ( ! gki_docs_is_target_post() ) {
        return $content;
    }

    // Remove <p> tags that are empty or contain only whitespace/&nbsp;
    $content = preg_replace( '/<p>\s*(&nbsp;)?\s*<\/p>/i', '', $content );

    // Remove <br> tags that sit between block-level elements
    // (Parsedown injects <br> between <div>, <a>, <section>, <figure>, etc.)
    $content = preg_replace(
        '/(<\/(?:div|a|section|figure|details|nav|ul|ol|li|h[1-6])>)\s*<br\s*\/?>\s*(<(?:div|a|section|figure|details|nav|ul|ol|li|h[1-6])[\s>])/i',
        '$1$2',
        $content
    );

    // Second pass: catch <br> at the start of containers (after opening tags)
    $content = preg_replace(
        '/(<(?:div|section|nav)[^>]*>)\s*<br\s*\/?>/i',
        '$1',
        $content
    );

    // Remove <p> tags wrapping block elements (Parsedown sometimes wraps divs in <p>)
    $content = preg_replace(
        '/<p>\s*(<(?:div|section|figure|details|nav|a\s+[^>]*class)[^>]*>)/i',
        '$1',
        $content
    );
    $content = preg_replace(
        '/(<\/(?:div|section|figure|details|nav|a)>)\s*<\/p>/i',
        '$1',
        $content
    );

    return $content;
}

/* =========================================================================
   3. ALLOW SCRIPT TAGS IN TARGET POSTS
   ========================================================================= */

add_filter( 'wp_kses_allowed_html', 'gki_docs_allow_scripts', 10, 2 );

/**
 * Expand the allowed HTML tags to include <script> for posts in the
 * target category. This lets Git It Write content include inline JS.
 *
 * Note: This only affects wp_kses filtering. If Git It Write or another
 * plugin strips scripts before wp_kses runs, a separate filter on that
 * plugin's content pipeline may be needed.
 */
function gki_docs_allow_scripts( $allowed_tags, $context ) {
    if ( $context !== 'post' ) {
        return $allowed_tags;
    }

    // Only widen for target posts (check is safe inside this filter)
    if ( ! gki_docs_is_target_post() ) {
        return $allowed_tags;
    }

    $allowed_tags['script'] = array(
        'type' => true,
        'src'  => true,
    );

    return $allowed_tags;
}

/**
 * Additionally, try to preserve <script> tags during the Git It Write
 * save/import process by filtering content before sanitization.
 */
add_filter( 'content_save_pre', 'gki_docs_preserve_scripts', 5 );

function gki_docs_preserve_scripts( $content ) {
    // Only act if the post is being saved to the target category
    // Since we're in a save context, check via $_POST or the global $post
    if ( ! gki_docs_is_saving_target_post() ) {
        return $content;
    }

    // Store scripts as base64 comments that survive sanitization,
    // then restore them on render via the_content filter
    $content = preg_replace_callback(
        '/<script\b([^>]*)>(.*?)<\/script>/is',
        function ( $matches ) {
            $attrs   = $matches[1];
            $body    = $matches[2];
            $encoded = base64_encode( '<script' . $attrs . '>' . $body . '</script>' );
            return '<!--gki-script:' . $encoded . '-->';
        },
        $content
    );

    return $content;
}

/**
 * Restore base64-encoded scripts on render.
 */
add_filter( 'the_content', 'gki_docs_restore_scripts', 5 );

function gki_docs_restore_scripts( $content ) {
    if ( ! gki_docs_is_target_post() ) {
        return $content;
    }

    $content = preg_replace_callback(
        '/<!--gki-script:([\w+\/=]+)-->/',
        function ( $matches ) {
            return base64_decode( $matches[1] );
        },
        $content
    );

    return $content;
}

/* =========================================================================
   4. ADD BODY CLASS FOR CSS SCOPING
   ========================================================================= */

add_filter( 'body_class', 'gki_docs_body_class' );

/**
 * Add a `gki-docs-page` class to the <body> on target posts so CSS
 * can scope rules without relying on inline classes in the content.
 */
function gki_docs_body_class( $classes ) {
    if ( gki_docs_is_target_post() ) {
        $classes[] = 'gki-docs-page';
        if ( gki_docs_is_index_page() ) {
            $classes[] = 'gki-index-page';
        }
    }
    return $classes;
}

/* =========================================================================
   5. CUSTOM PAGE TEMPLATE FOR INSIGHTS PAGES
   ========================================================================= */

/**
 * Override the single post template for insights-expo posts.
 * Provides a 3-column layout: left nav, content, right TOC.
 *
 * Priority 9999 ensures we run AFTER Elementor Pro's Theme Builder
 * (which hooks at various priorities up to ~999). This guarantees
 * our template wins over any Elementor-assigned single-post template.
 */
add_filter( 'template_include', 'gki_docs_custom_template', 9999 );

function gki_docs_custom_template( $template ) {
    if ( ! gki_docs_is_target_post() ) {
        return $template;
    }

    $custom = GKI_DOCS_PATH . 'templates/single-gki.php';
    if ( file_exists( $custom ) ) {
        return $custom;
    }

    return $template;
}

/**
 * Tell Elementor not to apply its Theme Builder template on our posts.
 * Without this, Elementor may re-override even a high-priority template_include.
 */
add_action( 'elementor/theme/register_conditions', 'gki_docs_elementor_exclude', 999 );

function gki_docs_elementor_exclude() {
    // If Elementor's theme module isn't available, nothing to do
    if ( ! class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' ) ) {
        return;
    }
}

/**
 * Alternative approach: filter the Elementor Pro template location
 * to return empty for our posts, so Elementor falls back to WP default
 * (which is our high-priority template_include).
 */
add_filter( 'elementor/theme/get_location_templates/template_id', 'gki_docs_block_elementor_template', 999 );

function gki_docs_block_elementor_template( $template_id ) {
    if ( gki_docs_is_target_post() ) {
        return 0; // Return 0 = no Elementor template for this post
    }
    return $template_id;
}

/**
 * Prevent Elementor from overriding our canvas on target posts.
 * Hooks into the Elementor template inclusion filter used by the Theme Builder.
 */
add_filter( 'elementor/document/wrapper_attributes', 'gki_docs_elementor_wrapper', 10, 2 );

function gki_docs_elementor_wrapper( $attributes, $document ) {
    // We don't modify wrapper attributes, but this ensures our hooks are loaded
    return $attributes;
}

/* =========================================================================
   6. REGISTER OPTIONAL NAV MENU LOCATION
   ========================================================================= */

add_action( 'after_setup_theme', 'gki_docs_register_menus' );

/**
 * Register a menu location for the Insights sidebar nav.
 * If a menu is assigned here, it replaces the auto-generated nav.
 * Set up via Appearance → Menus → Manage Locations.
 */
function gki_docs_register_menus() {
    register_nav_menus( array(
        'gki-insights-nav' => __( 'GKI Insights Sidebar Navigation', 'gki-docs-helper' ),
    ) );
}

/* =========================================================================
   7. HELPER FUNCTIONS
   ========================================================================= */

/**
 * Check whether the current request is for a single post in the target category.
 */
function gki_docs_is_target_post() {
    if ( ! is_single() ) {
        return false;
    }

    // Cache the result for the current request
    static $result = null;
    if ( $result !== null ) {
        return $result;
    }

    $result = has_category( GKI_DOCS_CATEGORY );
    return $result;
}

/**
 * Check whether a post being saved belongs to the target category.
 */
function gki_docs_is_saving_target_post() {
    global $post;

    if ( ! $post ) {
        return false;
    }

    return has_category( GKI_DOCS_CATEGORY, $post );
}

/* =========================================================================
   8. FRONTMATTER-BASED NAV & INDEX PAGE HELPERS
   ========================================================================= */

/**
 * Check whether the current post is an index page (main-index or section index).
 * Uses the `page_type` custom field set via frontmatter.
 */
function gki_docs_is_index_page() {
    if ( ! gki_docs_is_target_post() ) {
        return false;
    }
    $type = get_post_meta( get_the_ID(), 'page_type', true );
    return in_array( $type, array( 'index', 'main-index' ), true );
}

/**
 * Get the page type from frontmatter. Returns 'content' as default.
 */
function gki_docs_get_page_type() {
    $type = get_post_meta( get_the_ID(), 'page_type', true );
    return $type ? $type : 'content';
}

/**
 * Build the hierarchical navigation structure from frontmatter fields.
 *
 * Supports nested hierarchy via nav_parent custom field:
 *   - Pages with nav_parent are nested under their parent entry
 *   - This enables metrics sub-indexes (e.g. DORA → individual metrics)
 *
 * Returns null if no posts have nav_category set (triggers alphabetical fallback).
 * Otherwise returns:
 *   array(
 *     'main_index' => array|null,
 *     'sections'   => array( 'category-slug' => array( 'index' => ..., 'children' => array(...) ) )
 *   )
 *
 * Each child entry may have its own 'children' array for sub-group nesting.
 */
function gki_docs_get_nav_structure() {
    $section_order = array(
        'getting-started',
        'connect-your-data',
        'metrics',
        'playbooks',
        'admin',
    );

    $all_posts = get_posts( array(
        'category_name'  => GKI_DOCS_CATEGORY,
        'posts_per_page' => -1,
    ) );

    $has_frontmatter = false;
    foreach ( $all_posts as $p ) {
        if ( get_post_meta( $p->ID, 'nav_category', true ) ) {
            $has_frontmatter = true;
            break;
        }
    }
    if ( ! $has_frontmatter ) {
        return null;
    }

    $main_index      = null;
    $entries_by_slug = array();
    $parented_slugs  = array();

    // First pass: build all entries indexed by slug
    foreach ( $all_posts as $p ) {
        $cat        = get_post_meta( $p->ID, 'nav_category', true );
        $type       = get_post_meta( $p->ID, 'page_type', true );
        $order      = (int) get_post_meta( $p->ID, 'nav_order', true );
        $label      = get_post_meta( $p->ID, 'nav_label', true ) ?: $p->post_title;
        $nav_parent = get_post_meta( $p->ID, 'nav_parent', true );

        $entry = array(
            'post'       => $p,
            'label'      => $label,
            'order'      => $order,
            'type'       => $type,
            'cat'        => $cat,
            'icon'       => get_post_meta( $p->ID, 'card_icon', true ),
            'color'      => get_post_meta( $p->ID, 'card_color', true ),
            'desc'       => get_post_meta( $p->ID, 'card_description', true ),
            'nav_parent' => $nav_parent,
            'children'   => array(),
        );

        $entries_by_slug[ $p->post_name ] = $entry;

        if ( $type === 'main-index' ) {
            $main_index = $entry;
        }
    }

    // Second pass: nest children under their nav_parent
    foreach ( $entries_by_slug as $slug => &$entry ) {
        if ( $entry['type'] === 'main-index' ) {
            continue;
        }
        $np = $entry['nav_parent'];
        if ( $np && isset( $entries_by_slug[ $np ] ) ) {
            $entries_by_slug[ $np ]['children'][] = &$entry;
            $parented_slugs[ $slug ] = true;
        }
    }
    unset( $entry );

    // Sort children of each entry by nav_order
    foreach ( $entries_by_slug as &$entry ) {
        if ( ! empty( $entry['children'] ) ) {
            usort( $entry['children'], function ( $a, $b ) {
                return $a['order'] - $b['order'];
            } );
        }
    }
    unset( $entry );

    // Third pass: build sections from un-parented entries
    $sections = array();
    foreach ( $entries_by_slug as $slug => $entry ) {
        if ( $entry['type'] === 'main-index' ) {
            continue;
        }
        if ( isset( $parented_slugs[ $slug ] ) ) {
            continue;
        }

        $cat = $entry['cat'];
        if ( ! $cat || $cat === 'hidden' ) {
            continue;
        }

        if ( ! isset( $sections[ $cat ] ) ) {
            $sections[ $cat ] = array( 'index' => null, 'children' => array() );
        }

        if ( $entry['type'] === 'index' ) {
            $sections[ $cat ]['index'] = $entry;
        } else {
            $sections[ $cat ]['children'][] = $entry;
        }
    }

    // Merge index's nav_parent children into section children
    foreach ( $sections as $cat => &$sec ) {
        if ( $sec['index'] && ! empty( $sec['index']['children'] ) ) {
            $sec['children'] = array_merge( $sec['index']['children'], $sec['children'] );
            usort( $sec['children'], function ( $a, $b ) {
                return $a['order'] - $b['order'];
            } );
        }
    }
    unset( $sec );

    // Sort flat children within each section
    foreach ( $sections as &$sec ) {
        usort( $sec['children'], function ( $a, $b ) {
            return $a['order'] - $b['order'];
        } );
    }
    unset( $sec );

    // Order sections by predefined order
    $ordered = array();
    foreach ( $section_order as $key ) {
        if ( isset( $sections[ $key ] ) ) {
            $ordered[ $key ] = $sections[ $key ];
        }
    }
    foreach ( $sections as $key => $sec ) {
        if ( ! isset( $ordered[ $key ] ) && $key !== 'hidden' ) {
            $ordered[ $key ] = $sec;
        }
    }

    return array(
        'main_index' => $main_index,
        'sections'   => $ordered,
    );
}

/**
 * Build breadcrumb data for the current page.
 *
 * Follows nav_parent chain to build multi-level breadcrumbs:
 *   Home > Metrics > DORA & Quality > Deployment Frequency
 *
 * Falls back to section index by nav_category when no nav_parent is set.
 *
 * Returns an array of crumbs: array( array( 'label' => '...', 'url' => '...'|null ) )
 * The last crumb has url=null (current page).
 */
function gki_docs_get_breadcrumb_data() {
    $type = gki_docs_get_page_type();

    if ( $type === 'main-index' ) {
        return array();
    }

    $crumbs = array();

    // Home crumb
    $main = get_posts( array(
        'category_name'  => GKI_DOCS_CATEGORY,
        'posts_per_page' => 1,
        'meta_query'     => array(
            array( 'key' => 'page_type', 'value' => 'main-index' ),
        ),
    ) );
    if ( $main ) {
        $crumbs[] = array(
            'label' => get_post_meta( $main[0]->ID, 'nav_label', true ) ?: 'Home',
            'url'   => get_permalink( $main[0] ),
        );
    }

    // Build ancestor chain via nav_parent
    $nav_parent = get_post_meta( get_the_ID(), 'nav_parent', true );
    $chain      = array();
    $visited    = array();

    while ( $nav_parent && ! isset( $visited[ $nav_parent ] ) ) {
        $visited[ $nav_parent ] = true;
        $parent_posts = get_posts( array(
            'category_name'  => GKI_DOCS_CATEGORY,
            'name'           => $nav_parent,
            'posts_per_page' => 1,
        ) );
        if ( $parent_posts ) {
            $pp = $parent_posts[0];
            array_unshift( $chain, array(
                'label' => get_post_meta( $pp->ID, 'nav_label', true ) ?: $pp->post_title,
                'url'   => get_permalink( $pp ),
            ) );
            $nav_parent = get_post_meta( $pp->ID, 'nav_parent', true );
        } else {
            break;
        }
    }

    // If no nav_parent chain and this is a content page, fall back to section index
    if ( empty( $chain ) && $type !== 'index' ) {
        $cat = get_post_meta( get_the_ID(), 'nav_category', true );
        if ( $cat ) {
            $indexes = get_posts( array(
                'category_name'  => GKI_DOCS_CATEGORY,
                'posts_per_page' => -1,
                'meta_query'     => array(
                    array( 'key' => 'page_type', 'value' => 'index' ),
                ),
            ) );
            foreach ( $indexes as $idx ) {
                if ( get_post_meta( $idx->ID, 'nav_category', true ) === $cat
                    && ! get_post_meta( $idx->ID, 'nav_parent', true ) ) {
                    $chain[] = array(
                        'label' => get_post_meta( $idx->ID, 'nav_label', true ) ?: $idx->post_title,
                        'url'   => get_permalink( $idx ),
                    );
                    break;
                }
            }
        }
    }

    $crumbs = array_merge( $crumbs, $chain );

    // Current page (no link)
    $crumbs[] = array(
        'label' => get_post_meta( get_the_ID(), 'nav_label', true ) ?: get_the_title(),
        'url'   => null,
    );

    return $crumbs;
}

/**
 * Get the child pages for the current index page, formatted as card data.
 *
 * For main-index: returns section index pages (those without nav_parent).
 * For index pages: returns pages whose nav_parent matches this page's slug,
 *   or falls back to content pages in the same nav_category.
 * For content pages: returns empty array.
 */
function gki_docs_get_child_pages() {
    $type         = gki_docs_get_page_type();
    $cat          = get_post_meta( get_the_ID(), 'nav_category', true );
    $current_slug = get_post_field( 'post_name', get_the_ID() );

    if ( $type === 'main-index' ) {
        $posts = get_posts( array(
            'category_name'  => GKI_DOCS_CATEGORY,
            'posts_per_page' => -1,
            'meta_query'     => array(
                array( 'key' => 'page_type', 'value' => 'index' ),
            ),
        ) );
        // Exclude sub-indexes (those with nav_parent)
        $posts = array_filter( $posts, function ( $p ) {
            return ! get_post_meta( $p->ID, 'nav_parent', true );
        } );
    } elseif ( $type === 'index' ) {
        // Check for pages that reference this page as nav_parent
        $all_posts = get_posts( array(
            'category_name'  => GKI_DOCS_CATEGORY,
            'posts_per_page' => -1,
        ) );
        $nav_parent_children = array_filter( $all_posts, function ( $p ) use ( $current_slug ) {
            return get_post_meta( $p->ID, 'nav_parent', true ) === $current_slug;
        } );

        if ( ! empty( $nav_parent_children ) ) {
            $posts = $nav_parent_children;
        } else {
            // Fallback: content pages in same nav_category without nav_parent
            $posts = array_filter( $all_posts, function ( $p ) use ( $cat ) {
                return get_post_meta( $p->ID, 'nav_category', true ) === $cat
                    && get_post_meta( $p->ID, 'page_type', true ) === 'content'
                    && ! get_post_meta( $p->ID, 'nav_parent', true );
            } );
        }
    } else {
        return array();
    }

    $cards = array();
    foreach ( $posts as $p ) {
        $cards[] = array(
            'url'   => get_permalink( $p ),
            'title' => get_post_meta( $p->ID, 'nav_label', true ) ?: $p->post_title,
            'desc'  => get_post_meta( $p->ID, 'card_description', true ) ?: '',
            'icon'  => get_post_meta( $p->ID, 'card_icon', true ) ?: '',
            'color' => get_post_meta( $p->ID, 'card_color', true ) ?: 'purple',
            'order' => (int) get_post_meta( $p->ID, 'nav_order', true ),
        );
    }

    usort( $cards, function ( $a, $b ) {
        return $a['order'] - $b['order'];
    } );

    return $cards;
}

/**
 * Render the card grid HTML for a set of child page cards.
 * Extracted so it can be called from the template in multiple places.
 */
function gki_docs_render_card_grid( $child_cards ) {
    if ( empty( $child_cards ) ) return;
    ?>
    <div class="gki-card-grid">
      <?php foreach ( $child_cards as $card ) :
          $color_class = 'gki-card-icon--' . esc_attr( $card['color'] );
      ?>
      <a href="<?php echo esc_url( $card['url'] ); ?>" class="gki-card" data-search="<?php echo esc_attr( $card['title'] . ' ' . $card['desc'] ); ?>">
        <?php if ( $card['icon'] ) : ?>
          <div class="gki-card-icon <?php echo esc_attr( $color_class ); ?>">
            <i class="ti ti-<?php echo esc_attr( $card['icon'] ); ?>" aria-hidden="true"></i>
          </div>
        <?php endif; ?>
        <div class="gki-card-title">
          <?php echo esc_html( $card['title'] ); ?>
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
            <path d="M6 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <?php if ( $card['desc'] ) : ?>
          <div class="gki-card-desc"><?php echo esc_html( $card['desc'] ); ?></div>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>
    <?php
}

/**
 * Count articles under a nav category (content pages only, excludes index pages).
 */
function gki_docs_count_category_articles( $nav_category ) {
    $posts = get_posts( array(
        'category_name'  => GKI_DOCS_CATEGORY,
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ) );

    $count = 0;
    foreach ( $posts as $pid ) {
        if ( get_post_meta( $pid, 'nav_category', true ) === $nav_category
            && get_post_meta( $pid, 'page_type', true ) === 'content' ) {
            $count++;
        }
    }
    return $count;
}
