<?php
/**
 * Local preview renderer — a minimal WordPress for the GKI Docs Helper plugin.
 *
 *   php render.php <job.json>
 *
 * Driven by preview/build.py; not meant to be run by hand. The job file names
 * the plugin, the WordPress core sources and the pages (front matter + raw
 * Markdown). Every page is rendered the way production renders it:
 *
 *   Markdown --Git It Write's GIW_Parsedown--> post_content          (stored)
 *   post_content --the_content filters--> the plugin's single-gki.php (served)
 *
 * The_content runs WordPress core's own wptexturize/wpautop/shortcode filters
 * (formatting.php, kses.php, plugin.php, shortcodes.php from the pinned
 * WordPress release) and then the plugin's filters at 15/16/17, unmodified.
 * Only what WordPress would read from the database or the request is shimmed
 * below. includes/gki-auth.php is never loaded: every page renders as an
 * entitled reader sees it.
 *
 * Writes a JSON result to the path in job.out:
 *   { pages: { slug: html }, search_data: { var: json }, warnings: [ ... ] }
 */

$job = json_decode( file_get_contents( $argv[1] ), true );

/* --- Collect PHP warnings/notices instead of printing them into a page. --- */
$GLOBALS['preview_warnings'] = array();
set_error_handler( function ( $no, $msg, $file, $line ) {
    if ( ! ( error_reporting() & $no ) ) {
        return false; // @-suppressed, as in WordPress.
    }
    $GLOBALS['preview_warnings'][] = sprintf( '%s: %s in %s:%d [page %s]', $no & ( E_WARNING | E_USER_WARNING ) ? 'Warning' : ( $no & ( E_DEPRECATED | E_USER_DEPRECATED ) ? 'Deprecated' : 'Notice' ), $msg, $file, $line, $GLOBALS['preview_slug'] ?? '-' );
    return true;
} );
$GLOBALS['preview_slug'] = null;

define( 'ABSPATH', rtrim( $job['plugin_dir'], '/' ) . '/../' );
define( 'WPINC', 'wp-includes' );

/* --- WordPress core (pinned release, fetched by build.py). ---------------- */
require $job['wp_core_dir'] . '/class-wp-hook.php';
// plugin.php as-is, except that its plugin_dir_path()/plugin_dir_url() step
// aside for the preview's own (below), which point the plugin at the shadow
// folder and at its CSS/JS relative to preview/out/.
eval( '?>' . strtr( file_get_contents( $job['wp_core_dir'] . '/plugin.php' ), array(
    "require __DIR__ . '/class-wp-hook.php';" => '',
    'function plugin_dir_path(' => 'function wp_core_plugin_dir_path(',
    'function plugin_dir_url('  => 'function wp_core_plugin_dir_url(',
) ) );
foreach ( array( 'formatting.php', 'kses.php', 'shortcodes.php' ) as $core ) {
    require $job['wp_core_dir'] . '/' . $core;
}

/* --- The WordPress API surface the plugin, template and core files use. -- */
function __( $t, $d = 'default' ) { return $t; }
function _x( $t, $c, $d = 'default' ) { return $t; }
function _e( $t, $d = 'default' ) { echo $t; }
function esc_html__( $t, $d = 'default' ) { return esc_html( $t ); }
function esc_html_e( $t, $d = 'default' ) { echo esc_html( $t ); }
function esc_attr__( $t, $d = 'default' ) { return esc_attr( $t ); }
function esc_attr_e( $t, $d = 'default' ) { echo esc_attr( $t ); }
function is_admin() { return false; }
function is_single() { return true; }   // Every preview page is a single post.
function is_singular() { return true; }
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function wp_allowed_protocols() {
    return array( 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'irc6', 'ircs', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'sms', 'svn', 'tel', 'fax', 'xmpp', 'webcal', 'urn' );
}
function get_option( $name, $default = false ) {
    $o = array( 'blog_charset' => 'UTF-8', 'use_smilies' => '0', 'home' => PREVIEW_HOME, 'siteurl' => PREVIEW_HOME );
    return $o[ $name ] ?? $default;
}
function is_utf8_charset( $charset = null ) { return in_array( strtolower( $charset ?? get_option( 'blog_charset' ) ), array( 'utf-8', 'utf8' ), true ); }
function _canonical_charset( $charset ) { return is_utf8_charset( $charset ) ? 'UTF-8' : $charset; }
function wp_get_word_count_type() { return 'words'; }  // en_US
function home_url( $path = '' ) { return PREVIEW_HOME . '/' . ltrim( $path, '/' ); }
function plugin_dir_path( $file ) { return PREVIEW_SHADOW_DIR; }  // see load_plugin()
function plugin_dir_url( $file ) { return PREVIEW_PLUGIN_URL; }
function register_nav_menus( $locations = array() ) {}
function has_nav_menu( $location ) { return false; }
function wp_nav_menu( $args = array() ) {}
function wp_reset_postdata() {}
function current_user_can( $cap ) { return false; }

/* Posts ------------------------------------------------------------------- */
function get_post( $p = null ) {
    if ( $p instanceof WP_Post ) return $p;
    if ( $p === null ) return $GLOBALS['post'] ?? null;
    return $GLOBALS['preview_posts'][ (int) $p ] ?? null;
}
function get_the_ID() { return $GLOBALS['post']->ID; }
function get_post_field( $field, $p = null ) { $p = get_post( $p ); return $p ? $p->$field : ''; }
function get_post_meta( $id, $key = '', $single = false ) {
    $meta = $GLOBALS['preview_posts'][ (int) $id ]->meta ?? array();
    if ( $key === '' ) return array_map( function ( $v ) { return array( $v ); }, $meta );
    if ( ! array_key_exists( $key, $meta ) ) return $single ? '' : array();
    return $single ? $meta[ $key ] : array( $meta[ $key ] );
}
function get_the_title( $p = 0 ) {
    $p = get_post( $p ?: null );
    return $p ? apply_filters( 'the_title', $p->post_title, $p->ID ) : '';
}
function get_permalink( $p = 0 ) {
    $p = get_post( $p ?: null );
    // Production: /%category%/%postname%/ with every page in insights-expo.
    return $p ? home_url( '/' . $p->category . '/' . $p->post_name . '/' ) : false;
}
function has_category( $cat, $p = null ) { $p = get_post( $p ); return $p && in_array( $cat, $p->categories, true ); }

/** The WP_Query arguments the plugin passes to get_posts(), nothing more. */
function get_posts( $args = array() ) {
    $posts = array_values( $GLOBALS['preview_posts'] );
    // WordPress default: newest first. Ties in nav_order then fall out the same way.
    usort( $posts, function ( $a, $b ) { return $b->ID - $a->ID; } );
    if ( ! empty( $args['category_name'] ) ) {
        $posts = array_filter( $posts, function ( $p ) use ( $args ) { return in_array( $args['category_name'], $p->categories, true ); } );
    }
    if ( ! empty( $args['name'] ) ) {
        $posts = array_filter( $posts, function ( $p ) use ( $args ) { return $p->post_name === $args['name']; } );
    }
    foreach ( $args['meta_query'] ?? array() as $q ) {
        $posts = array_filter( $posts, function ( $p ) use ( $q ) { return ( $p->meta[ $q['key'] ] ?? null ) === (string) $q['value']; } );
    }
    if ( ! empty( $args['meta_key'] ) ) {  // An INNER JOIN on the key: posts without it drop out.
        $posts = array_filter( $posts, function ( $p ) use ( $args ) { return isset( $p->meta[ $args['meta_key'] ] ); } );
    }
    $posts   = array_values( $posts );
    $orderby = $args['orderby'] ?? 'date';
    $dir     = strtoupper( $args['order'] ?? ( $orderby === 'title' ? 'DESC' : 'DESC' ) ) === 'ASC' ? 1 : -1;
    if ( $orderby === 'meta_value_num' ) {
        usort( $posts, function ( $a, $b ) use ( $args, $dir ) { return $dir * ( (float) $a->meta[ $args['meta_key'] ] <=> (float) $b->meta[ $args['meta_key'] ] ); } );
    } elseif ( $orderby === 'title' ) {
        usort( $posts, function ( $a, $b ) use ( $dir ) { return $dir * strcasecmp( $a->post_title, $b->post_title ); } );
    } elseif ( strtoupper( $args['order'] ?? 'DESC' ) === 'ASC' ) {
        $posts = array_reverse( $posts );
    }
    $n = $args['posts_per_page'] ?? $args['numberposts'] ?? 5;
    if ( $n > 0 ) $posts = array_slice( $posts, 0, $n );
    if ( ( $args['fields'] ?? '' ) === 'ids' ) return array_map( function ( $p ) { return $p->ID; }, $posts );
    return $posts;
}

class WP_Post {
    public $ID, $post_name, $post_title, $post_content, $post_type = 'post', $post_status = 'publish';
    public $meta = array(), $categories = array(), $category = '';
}

/* The loop: one post per request, as on a single-post URL. */
function have_posts() { return ! $GLOBALS['preview_loop_done']; }
function the_post() { $GLOBALS['preview_loop_done'] = true; }
function the_content() {
    $content = apply_filters( 'the_content', get_post()->post_content );
    echo str_replace( ']]>', ']]&gt;', $content );
}

/* Assets ------------------------------------------------------------------- */
function wp_enqueue_style( $h, $src = '', $deps = array(), $ver = false, $media = 'all' ) { $GLOBALS['preview_styles'][ $h ] = array( $src, $ver ); }
function wp_enqueue_script( $h, $src = '', $deps = array(), $ver = false, $footer = false ) {
    $GLOBALS['preview_scripts'][ $h ] = array( $src, $ver, is_array( $footer ) ? ! empty( $footer['in_footer'] ) : $footer );
}
function wp_localize_script( $h, $var, $data ) { $GLOBALS['preview_l10n'][ $h ][ $var ] = $data; return true; }
function preview_asset_url( $src, $ver ) {
    if ( $ver === false ) $ver = '6.8.3';
    return $ver === null ? $src : $src . ( strpos( $src, '?' ) === false ? '?' : '&' ) . 'ver=' . $ver;
}
function preview_print_styles() {
    foreach ( $GLOBALS['preview_styles'] as $h => list( $src, $ver ) ) {
        printf( "<link rel='stylesheet' id='%s-css' href='%s' media='all' />\n", esc_attr( $h ), esc_url( preview_asset_url( $src, $ver ) ) );
    }
}
function preview_print_scripts( $in_footer ) {
    foreach ( $GLOBALS['preview_scripts'] as $h => list( $src, $ver, $footer ) ) {
        if ( $footer !== $in_footer ) continue;
        foreach ( $GLOBALS['preview_l10n'][ $h ] ?? array() as $var => $data ) {
            // WordPress inlines this per page; the preview shares one file (build.py writes it).
            $GLOBALS['preview_search_data'][ $var ] = wp_json_encode_preview( $data );
            printf( "<script src=\"%s\" id=\"%s-js-extra\"></script>\n", 'PREVIEW-L10N:' . $var, esc_attr( $h ) );
        }
        printf( "<script src=\"%s\" id=\"%s-js\"></script>\n", esc_url( preview_asset_url( $src, $ver ) ), esc_attr( $h ) );
    }
}
function wp_json_encode_preview( $data ) { return json_encode( $data, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES ); }

/* Theme chrome --------------------------------------------------------------
   The live site wraps the template in hello-elementor's header and footer.
   On these pages the plugin suppresses Elementor's templates, so what is left
   is the document shell; Inter comes from Elementor's Google Fonts loader. */
function language_attributes() { echo apply_filters( 'language_attributes', 'lang="en-US"', 'html' ); }
function get_body_class() { return apply_filters( 'body_class', array( 'post-template-default', 'single', 'single-post', 'postid-' . get_the_ID(), 'single-format-standard', 'elementor-kit-5' ), array() ); }
function body_class() { echo 'class="' . esc_attr( implode( ' ', get_body_class() ) ) . '"'; }
function wp_head() { do_action( 'wp_head' ); }
function wp_footer() { do_action( 'wp_footer' ); }
function wp_body_open() { do_action( 'wp_body_open' ); }
function get_header() {
    echo "<!doctype html>\n<html "; language_attributes(); echo ">\n<head>\n<meta charset=\"UTF-8\">\n";
    echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n";
    echo '<title>' . esc_html( wp_strip_all_tags( get_the_title() ) ) . " &#8211; GitKraken Help Center (local preview)</title>\n";
    echo "<link rel='stylesheet' id='elementor-gf-inter-css' href='https://fonts.googleapis.com/css?family=Inter:100,200,300,400,500,600,700,800,900&#038;display=swap' media='all' />\n";
    // Stand-in for the theme + Elementor kit base styles, which are not loaded:
    // only the body face and margin, so elements the plugin appends to <body>
    // (the command palette) get Inter as on the live site.
    echo "<style id='preview-theme-standin'>body{margin:0;font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif}</style>\n";
    wp_head();
    echo "</head>\n<body "; body_class(); echo ">\n"; wp_body_open();
}
function get_footer() { wp_footer(); echo "</body>\n</html>\n"; }

/* --- Git It Write: Markdown -> post_content ------------------------------ */
final class Git_It_Write { public static function allowed_file_types() { return array( 'md' ); } }
final class GIW_Utils {
    public static function log( $message ) {}
    // Verbatim from git-it-write/includes/utilities.php (GPLv2+).
    public static function remove_extension_relative_url( $url ) {
        $allowed_file_types = Git_It_Write::allowed_file_types();
        $parts = parse_url( $url );
        if ( ! isset( $parts['path'] ) ) return $url;
        $path_parts = pathinfo( $parts['path'] );
        if ( ! isset( $path_parts['extension'] ) ) return $url;
        if ( ! in_array( strtolower( $path_parts['extension'] ), $allowed_file_types ) ) return $url;
        $final_url = array( $path_parts['dirname'] . '/' . $path_parts['filename'] . '/' );
        if ( isset( $parts['query'] ) ) $final_url[] = '?' . $parts['query'];
        if ( isset( $parts['fragment'] ) ) $final_url[] = '#' . $parts['fragment'];
        return implode( '', $final_url );
    }
}
require $job['vendor_dir'] . '/parsedown/Parsedown.php';
require $job['vendor_dir'] . '/parsedown-extra/ParsedownExtra.php';
require $job['vendor_dir'] . '/git-it-write/parsedown.php';

/** GIW_Publisher::create_post(), minus the database: what lands in post_content. */
function preview_post_content( $text ) {
    $pd = new GIW_Parsedown();
    $pd->setUrlsLinked( false );
    $pd->uploaded_images = array();  // Pages reference /wp-content/uploads/ directly.
    // GIW_Parsedown::parse_content() without the YAML (build.py parsed it).
    $parts    = preg_split( '/^[\s\r\n]?---[\s\r\n]?$/sm', PHP_EOL . ltrim( $text ) );
    $markdown = count( $parts ) < 3 ? $text : implode( PHP_EOL . '---' . PHP_EOL, array_slice( $parts, 2 ) );
    $html     = str_replace( '%%content%%', $pd->text( $markdown ), '%%content%%' );
    // wp_insert_post(): sanitize_post() + wp_unslash(). Kses does not apply on
    // production's import path (see README), so content_save_pre is empty here.
    return wp_unslash( apply_filters( 'content_save_pre', $html ) );
}

/* --- Load posts, core default filters, then the plugin. ------------------ */
define( 'PREVIEW_HOME', rtrim( $job['home'], '/' ) );
define( 'PREVIEW_PLUGIN_URL', $job['plugin_url'] );
define( 'PREVIEW_SHADOW_DIR', $job['shadow_dir'] . '/' );

$GLOBALS['preview_posts'] = array();
foreach ( $job['pages'] as $i => $pg ) {
    $GLOBALS['preview_slug'] = $pg['slug'];
    $p               = new WP_Post();
    $p->ID           = 1000 + $i;
    $p->post_name    = $pg['slug'];
    $p->post_title   = wp_unslash( $pg['title'] );
    $p->post_content = preview_post_content( $pg['text'] );
    $p->categories   = $pg['categories'];
    $p->category     = $pg['categories'][0] ?? 'uncategorized';
    $p->meta         = wp_unslash( $pg['meta'] );  // custom_fields -> post meta (strings)
    $GLOBALS['preview_posts'][ $p->ID ] = $p;
}

// wp-includes/default-filters.php, the_content and the_title.
add_filter( 'the_content', 'wptexturize' );
add_filter( 'the_content', 'wpautop' );
add_filter( 'the_content', 'shortcode_unautop' );
add_filter( 'the_content', 'do_shortcode', 11 );
add_filter( 'the_content', 'capital_P_dangit', 11 );
add_filter( 'the_title', 'wptexturize' );
add_filter( 'the_title', 'convert_chars' );
add_filter( 'the_title', 'trim' );
add_filter( 'the_title', 'capital_P_dangit', 11 );
// wp_head/wp_footer printing, at core's priorities.
add_action( 'wp_head', function () { do_action( 'wp_enqueue_scripts' ); }, 1 );
add_action( 'wp_head', 'preview_print_styles', 8 );
add_action( 'wp_head', function () { preview_print_scripts( false ); }, 9 );
add_action( 'wp_footer', function () { preview_print_scripts( true ); }, 20 );

/* The plugin's main file requires includes/*.php and resolves its template
   through GKI_DOCS_PATH = plugin_dir_path(). That points at a shadow folder
   (build.py creates it) holding one-line stubs that require the real files,
   and an empty includes/gki-auth.php, so the auth gate is never loaded and
   the plugin source is never modified. */
require $job['plugin_dir'] . '/gki-docs-helper.php';
do_action( 'plugins_loaded' );
do_action( 'after_setup_theme' );
do_action( 'init' );

$result = array( 'pages' => array(), 'search_data' => array() );
foreach ( $GLOBALS['preview_posts'] as $p ) {
    $GLOBALS['preview_slug']      = $p->post_name;
    $GLOBALS['post']              = $p;
    $GLOBALS['preview_loop_done'] = false;
    $GLOBALS['preview_styles']    = $GLOBALS['preview_scripts'] = $GLOBALS['preview_l10n'] = array();
    $template = apply_filters( 'template_include', '' );
    if ( ! $template || ! file_exists( $template ) ) {
        fwrite( STDERR, "The plugin did not supply a template for {$p->post_name}\n" );
        exit( 2 );
    }
    ob_start();
    include $template;
    $result['pages'][ $p->post_name ] = ob_get_clean();
}
$result['search_data'] = $GLOBALS['preview_search_data'] ?? array();
$result['warnings']    = $GLOBALS['preview_warnings'];
file_put_contents( $job['out'], json_encode( $result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE ) );
