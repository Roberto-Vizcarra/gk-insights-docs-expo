<?php
/**
 * Plugin Name: GKI Docs Helper
 * Plugin URI:  https://gitkraken.com
 * Description: Custom styling, Parsedown cleanup, and JS support for GitKraken Insights Help Center pages in the "insights-expo" category.
 * Version:     1.0.0
 * Author:      GitKraken
 * Author URI:  https://gitkraken.com
 * License:     GPL-2.0-or-later
 * Text Domain: gki-docs-helper
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GKI_DOCS_VERSION', '1.0.0' );
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

    // Shared docs stylesheet — replaces per-file <style> blocks
    wp_enqueue_style(
        'gki-docs-styles',
        GKI_DOCS_URL . 'css/gki-docs.css',
        array(),
        GKI_DOCS_VERSION
    );

    // Optional JS for interactive features (TOC, search, collapsible, etc.)
    wp_enqueue_script(
        'gki-docs-scripts',
        GKI_DOCS_URL . 'js/gki-docs.js',
        array(),
        GKI_DOCS_VERSION,
        true // load in footer
    );
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
    }
    return $classes;
}

/* =========================================================================
   5. HELPER FUNCTIONS
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
