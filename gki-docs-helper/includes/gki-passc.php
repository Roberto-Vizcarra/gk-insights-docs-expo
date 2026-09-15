<?php
/**
 * GKI Docs Helper — Pass C structural module
 * =========================================================================
 * Turns a metric page from an article into a reference object, using only
 * what the Markdown already contains. No content files are modified.
 *
 * Every metric page today opens like this:
 *
 *     ## Agent Adoption Score              <- duplicates the post title
 *     > _A 0-100 measure of how ..._       <- the definition
 *     **Family:** X · **Cadence:** Y · **Where it appears:** Z
 *     ### At a glance
 *     ...
 *     ### Formula
 *     ```
 *     Adoption Score = ...
 *     ```
 *
 * This module restructures the rendered HTML into:
 *
 *     definition  ->  spec panel  ->  formula panel  ->  instrument  ->  prose
 *
 * It is deliberately forgiving: any page missing a piece simply renders
 * without it. Nothing here throws, and nothing depends on frontmatter that
 * does not already exist.
 *
 * @package gki-docs-helper
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================================================================
   INSTRUMENT REGISTRY
   =========================================================================
   Which metric pages get an interactive instrument, and which kind.

   The rule: a sandbox earns its place only when the formula has a tunable
   input whose effect is non-obvious. Metrics whose difficulty lives
   somewhere else get the instrument that matches that difficulty instead —
   a proportion bar, a sequence timeline, or a precedence table. Metrics
   that are genuinely simple get nothing, which is the correct answer.

   Keys are matched against the tail of the post slug.
   ========================================================================= */
function gki_passc_instruments() {
    return array(
        // --- Sandboxes: a tunable setting with a non-obvious result -------
        'agentic-maturity-factor'           => 'maturity',
        'agentic-adoption-score'            => 'adoption',
        'agentic-ai-tier'                   => 'aitier',
        'agentic-cursor-boost'              => 'cursorboost',
        'output-output-score'               => 'outputscore',
        'output-direct-commits'             => 'directcommits',
        'impact-cost-productivity-uplift'   => 'uplift',

        // --- Proportion: the insight is which part dominates --------------
        'flow-cycle-time'                   => 'cycletime',

        // --- Sequence: the insight is the matching / attribution rule -----
        'dora-lead-time'                    => 'leadtime',
        'impact-cost-ai-assisted-percentage' => 'aiassisted',

        // --- Precedence: the insight is the COALESCE chain ----------------
        'impact-cost-capex-opex'            => 'capexopex',
    );
}

/**
 * Resolve the instrument for the current post, or '' if it has none.
 */
function gki_passc_instrument_for( $post_id ) {
    $slug = get_post_field( 'post_name', $post_id );
    if ( ! $slug ) {
        return '';
    }
    foreach ( gki_passc_instruments() as $needle => $kind ) {
        if ( substr( $slug, -strlen( $needle ) ) === $needle ) {
            return $kind;
        }
    }
    return '';
}

/* =========================================================================
   CONTENT RESTRUCTURING
   ========================================================================= */

add_filter( 'the_content', 'gki_passc_restructure', 16 );

/**
 * Restructure a metric page's rendered HTML.
 *
 * Runs at priority 16, immediately after gki_docs_clean_parsedown (15), so
 * the Parsedown artifacts are already gone and the markup is predictable.
 */
function gki_passc_restructure( $content ) {
    if ( ! function_exists( 'gki_docs_is_target_post' ) || ! gki_docs_is_target_post() ) {
        return $content;
    }

    $post_id = get_the_ID();
    $type    = get_post_meta( $post_id, 'nav_category', true );

    // Metric pages only. Index pages, playbooks and admin pages are untouched.
    if ( $type !== 'metrics' || gki_docs_get_page_type() !== 'content' ) {
        return $content;
    }

    $title = get_the_title( $post_id );

    /* --- 1. Drop the leading H2 that repeats the page title -------------
       Every metric page opens with `## <Metric Name>`, which renders as a
       second copy of the H1 directly beneath it.

       The match has to survive other plugins: the anchor-link plugin on
       this site injects an <a class="aal_anchor"> with an inline SVG
       inside every heading, so the title text is not the first thing
       after the opening tag. Compare stripped text instead of matching
       the tag contents literally. */
    if ( preg_match( '/<h2\b[^>]*>(.*?)<\/h2>/is', $content, $h2 ) ) {
        $heading_text = trim( html_entity_decode( wp_strip_all_tags( $h2[1] ), ENT_QUOTES, 'UTF-8' ) );
        if ( strcasecmp( $heading_text, trim( $title ) ) === 0 ) {
            $content = str_replace( $h2[0], '', $content );
        }
    }

    /* --- 2. Lift the definition blockquote ------------------------------ */
    $definition = '';
    if ( preg_match( '/<blockquote>(.*?)<\/blockquote>/is', $content, $m ) ) {
        $inner = trim( wp_strip_all_tags( $m[1] ) );
        if ( $inner !== '' && strlen( $inner ) < 400 ) {
            $definition = $inner;
            $content    = str_replace( $m[0], '', $content );
        }
    }

    /* --- 3. Parse the metadata line into spec fields --------------------- */
    $spec = array();
    if ( preg_match( '/<p>\s*<strong>\s*Family:\s*<\/strong>(.*?)<\/p>/is', $content, $m ) ) {
        $spec    = gki_passc_parse_spec_line( $m[1] );
        $content = str_replace( $m[0], '', $content );
    }

    // Range is shown only when the page states it outright — never inferred.
    $range = gki_passc_detect_range( $definition );
    if ( $range ) {
        $spec = array_merge( array( 'Range' => $range ), $spec );
    }

    /* --- 4. Lift the first code block into a formula panel --------------- */
    $formula = '';
    if ( preg_match( '/<pre[^>]*>\s*<code[^>]*>(.*?)<\/code>\s*<\/pre>/is', $content, $m ) ) {
        $formula = $m[1];
        $content = str_replace( $m[0], '', $content );

        // The heading that introduced it is now orphaned.
        $content = preg_replace( '/<h[23][^>]*>\s*Formula\s*<\/h[23]>\s*/i', '', $content, 1 );
    }

    /* --- 5. Assemble ------------------------------------------------------ */
    $head = '';

    if ( $definition !== '' ) {
        $head .= '<p class="gki-definition">' . esc_html( $definition ) . '</p>';
    }

    if ( $spec ) {
        $head .= gki_passc_render_spec( $spec );
    }

    if ( $formula !== '' ) {
        $head .= gki_passc_render_formula( $formula );
    }

    $instrument = gki_passc_instrument_for( $post_id );
    if ( $instrument ) {
        $head .= '<div class="gki-instrument" data-instrument="' . esc_attr( $instrument ) . '"></div>';
    }

    return $head . $content;
}

/**
 * Split "Adoption &amp; Agentic · <strong>Cadence:</strong> Window-based · ..."
 * into an ordered label => value map.
 *
 * The first value belongs to the "Family:" label that the caller already
 * matched and stripped, so it is seeded explicitly.
 */
function gki_passc_parse_spec_line( $tail ) {
    $spec = array();

    // Normalise the separator Parsedown emits, then split on <strong>Label:</strong>
    $parts = preg_split(
        '/<strong>\s*([^<:]+):\s*<\/strong>/i',
        'Family:' . '<strong>Family:</strong>' . $tail,
        -1,
        PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
    );

    // parts alternates: [junk], label, value, label, value, ...
    $count = count( $parts );
    for ( $i = 0; $i < $count - 1; $i++ ) {
        $label = trim( wp_strip_all_tags( $parts[ $i ] ) );
        if ( $label === '' || strpos( $label, ':' ) !== false ) {
            continue;
        }
        if ( ! isset( $parts[ $i + 1 ] ) ) {
            continue;
        }
        $value = trim( wp_strip_all_tags( $parts[ $i + 1 ] ) );
        $value = trim( $value, " \t\n\r\0\x0B·-–" );
        if ( $value === '' || strlen( $value ) > 300 ) {
            continue;
        }
        if ( ! isset( $spec[ $label ] ) ) {
            $spec[ $label ] = $value;
        }
        $i++; // consume the value
    }

    return $spec;
}

/**
 * Pull an explicitly stated range out of the definition sentence.
 * Returns '' when the page does not state one — never guesses.
 */
function gki_passc_detect_range( $definition ) {
    if ( $definition === '' ) {
        return '';
    }
    if ( preg_match( '/\b0\s*[-–—]\s*100\b/u', $definition ) ) {
        return '0 – 100';
    }
    if ( preg_match( '/\bpercentage\b|\bpercent\b|\b%\b/iu', $definition ) ) {
        return '0 – 100%';
    }
    return '';
}

/**
 * Render the spec panel: a hairline field grid, not a card.
 */
function gki_passc_render_spec( $spec ) {
    $out = '<section class="gki-spec" aria-label="' . esc_attr__( 'Specification', 'gki-docs-helper' ) . '"><dl>';

    foreach ( $spec as $label => $value ) {
        // Long path lists read better as separate tokens than as prose.
        $is_paths = ( stripos( $label, 'appears' ) !== false );
        $rendered = $is_paths
            ? gki_passc_render_paths( $value )
            : '<span>' . esc_html( $value ) . '</span>';

        $out .= '<div class="gki-spec-field' . ( $is_paths ? ' gki-spec-field--wide' : '' ) . '">';
        $out .= '<dt>' . esc_html( $label ) . '</dt>';
        $out .= '<dd>' . $rendered . '</dd>';
        $out .= '</div>';
    }

    return $out . '</dl></section>';
}

/**
 * Turn "/developers, /teams, /comparison" into individual path tokens.
 * Anything that is not a path is left as plain text.
 */
function gki_passc_render_paths( $value ) {
    $chunks = preg_split( '/\s*,\s*/', $value );
    $out    = '';
    $any    = false;

    foreach ( $chunks as $chunk ) {
        $chunk = trim( $chunk );
        if ( $chunk === '' ) {
            continue;
        }
        if ( $chunk[0] === '/' ) {
            $out .= '<code class="gki-path">' . esc_html( $chunk ) . '</code>';
            $any  = true;
        } else {
            $out .= '<span class="gki-spec-prose">' . esc_html( $chunk ) . '</span>';
        }
    }

    return $any ? $out : '<span>' . esc_html( $value ) . '</span>';
}

/**
 * Render the formula panel. Variables on the left of an "=" and known
 * operators are tinted; everything else is left alone.
 */
function gki_passc_render_formula( $raw ) {
    $text = html_entity_decode( wp_strip_all_tags( $raw ), ENT_QUOTES, 'UTF-8' );
    $text = rtrim( $text );

    return '<section class="gki-formula" aria-label="' . esc_attr__( 'Formula', 'gki-docs-helper' ) . '">'
         . '<span class="gki-formula-label">Formula</span>'
         . '<pre>' . esc_html( $text ) . '</pre>'
         . '</section>';
}

/* =========================================================================
   METRIC MAP DATA (Home)
   =========================================================================
   Built entirely from frontmatter that already exists: nav_category,
   nav_label, nav_parent, card_description.
   ========================================================================= */

/**
 * Group every metric page under its family sub-index.
 *
 * @return array [ [ 'family' => label, 'count' => int, 'metrics' => [ [label, url, derived] ] ] ]
 */
function gki_passc_metric_map() {
    $posts = get_posts( array(
        'category_name'  => GKI_DOCS_CATEGORY,
        'posts_per_page' => -1,
        'orderby'        => 'meta_value_num',
        'meta_key'       => 'nav_order',
        'order'          => 'ASC',
    ) );

    if ( ! $posts ) {
        return array();
    }

    // First pass: find the family sub-indexes (page_type=index, nav_category=metrics).
    $families = array();
    $by_slug  = array();

    foreach ( $posts as $p ) {
        $by_slug[ $p->post_name ] = $p;

        $cat  = get_post_meta( $p->ID, 'nav_category', true );
        $type = get_post_meta( $p->ID, 'page_type', true );

        if ( $cat !== 'metrics' || $type !== 'index' ) {
            continue;
        }
        // The section index itself has no nav_parent pointing at another index.
        $parent = get_post_meta( $p->ID, 'nav_parent', true );
        if ( ! $parent ) {
            continue; // this is the Metrics section index, not a family
        }

        $families[ $p->post_name ] = array(
            'family'  => get_post_meta( $p->ID, 'nav_label', true ) ?: $p->post_title,
            'url'     => get_permalink( $p ),
            'metrics' => array(),
        );
    }

    // Second pass: attach each metric page to its family.
    foreach ( $posts as $p ) {
        $cat  = get_post_meta( $p->ID, 'nav_category', true );
        $type = get_post_meta( $p->ID, 'page_type', true );

        if ( $cat !== 'metrics' || $type !== 'content' ) {
            continue;
        }

        $parent = get_post_meta( $p->ID, 'nav_parent', true );
        if ( ! $parent || ! isset( $families[ $parent ] ) ) {
            continue;
        }

        $label = get_post_meta( $p->ID, 'nav_label', true ) ?: $p->post_title;

        /* A "modifier" changes other scores rather than being a score of its
           own. Both are named as such in their own documentation, so this is
           a read of the content, not a judgement about it. */
        $derived = (bool) preg_match( '/maturity factor|cursor boost/i', $label );

        $families[ $parent ]['metrics'][] = array(
            'label'   => $label,
            'url'     => get_permalink( $p ),
            'derived' => $derived,
        );
    }

    // Drop empty families and add counts.
    $out = array();
    foreach ( $families as $f ) {
        if ( empty( $f['metrics'] ) ) {
            continue;
        }
        $f['count'] = count( $f['metrics'] );
        $out[]      = $f;
    }

    return $out;
}

/**
 * Role-based entry paths for Home, resolved from the Getting Started pages
 * that already exist. Any role whose page is missing is simply skipped.
 */
function gki_passc_role_paths() {
    $roles = array(
        'for-executives'          => array( 'Executive',           'Prove the investment',   'Adoption, productivity uplift, and the CapEx/OpEx split.' ),
        'for-engineering-leaders' => array( 'Engineering leader',  'Find the bottleneck',    'DORA, cycle time, and review load across your teams.' ),
        'for-team-leads'          => array( 'Team lead',           'Coach the team',         'Per-developer adoption, without it becoming a performance review.' ),
        'for-admins'              => array( 'Admin',               'Connect the data',       'Git providers, AI tools, trackers, and the settings that move scores.' ),
    );

    $posts = get_posts( array(
        'category_name'  => GKI_DOCS_CATEGORY,
        'posts_per_page' => -1,
    ) );

    $out = array();
    foreach ( $roles as $needle => $meta ) {
        foreach ( $posts as $p ) {
            if ( substr( $p->post_name, -strlen( $needle ) ) === $needle ) {
                $out[] = array(
                    'role'  => $meta[0],
                    'title' => $meta[1],
                    'desc'  => $meta[2],
                    'url'   => get_permalink( $p ),
                );
                break;
            }
        }
    }

    return $out;
}
