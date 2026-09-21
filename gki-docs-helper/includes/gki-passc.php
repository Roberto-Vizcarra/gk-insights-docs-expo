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

    // Metric pages only. Every other page goes through gki_passc_restructure_general.
    if ( $type !== 'metrics' || gki_docs_get_page_type() !== 'content' ) {
        return $content;
    }

    /* --- 1. Drop the leading H2 that repeats the page title ------------- */
    $content = gki_passc_drop_duplicate_h2( $content, get_the_title( $post_id ) );

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

    /* "Where it appears" belongs at the foot of the page as a full section
       with descriptions, not as a spec field. The spec value is kept only
       as a fallback for a page that has no such section. */
    $spec_surfaces = '';
    foreach ( array_keys( $spec ) as $label ) {
        if ( stripos( $label, 'appears' ) !== false ) {
            $spec_surfaces = $spec[ $label ];
            unset( $spec[ $label ] );
        }
    }

    /* --- 4. Lift the first code block into a formula panel ---------------
       The panel is re-inserted at the top of the Formula section below, so
       the formula, its legend and the sandbox read as one unit. */
    $formula = '';
    if ( preg_match( '/<pre[^>]*>\s*<code[^>]*>(.*?)<\/code>\s*<\/pre>/is', $content, $m ) ) {
        $formula = $m[1];
        $content = str_replace( $m[0], '', $content );
    }

    $instrument = gki_passc_instrument_for( $post_id );

    /* --- 5. Split on H3, transform each section, reorder ----------------- */
    $sections = gki_passc_split_sections( $content );
    $sections = gki_passc_transform_sections( $sections, $formula, $instrument, $spec_surfaces );
    $sections = gki_passc_order_sections( $sections );

    /* --- 6. Assemble ------------------------------------------------------ */
    $head = '';

    if ( $definition !== '' ) {
        $head .= '<p class="gki-definition">' . esc_html( $definition ) . '</p>';
    }

    if ( $spec ) {
        $head .= gki_passc_render_spec( $spec );
    }

    $out = $head;
    foreach ( $sections as $s ) {
        $out .= $s['heading'] . $s['body'];
    }

    return $out;
}

/* =========================================================================
   GENERAL PAGES (connect, playbooks, getting started, admin, indexes)
   =========================================================================
   The same component language as the metric pages, applied to the
   patterns those pages actually use. Nothing is re-ordered here — the
   page authors chose these orders deliberately — and the first <hr> on an
   index page is left untouched because the template splits on it.
   ========================================================================= */

add_filter( 'the_content', 'gki_passc_restructure_general', 17 );

function gki_passc_restructure_general( $content ) {
    if ( ! function_exists( 'gki_docs_is_target_post' ) || ! gki_docs_is_target_post() ) {
        return $content;
    }

    $post_id = get_the_ID();
    $cat     = get_post_meta( $post_id, 'nav_category', true );
    $ptype   = gki_docs_get_page_type();
    $slug    = get_post_field( 'post_name', $post_id );

    // Metric pages have their own pipeline.
    if ( $cat === 'metrics' && $ptype === 'content' ) {
        return $content;
    }

    if ( $ptype === 'content' ) {
        // The H1 shows nav_label when one is set, so a duplicate H2 may
        // match either that or the post title.
        $content = gki_passc_drop_duplicate_h2( $content, get_the_title( $post_id ), get_post_meta( $post_id, 'nav_label', true ) );
        $content = gki_passc_lift_lede( $content );
    }

    $content = gki_passc_callouts( $content );

    if ( substr( $slug, -9 ) === '-settings' ) {
        $content = gki_passc_settings_specs( $content );
    }

    if ( $cat === 'playbooks' && $ptype === 'content' ) {
        $content = gki_passc_headed_steps( $content );
        $content = gki_passc_branch_headings( $content );
    }

    if ( $ptype === 'content' ) {
        $content = gki_passc_hero_figure( $content );
    }

    $content = gki_passc_code_panels( $content );
    $content = gki_passc_link_tables( $content );
    $content = gki_passc_checklists( $content );
    $content = gki_passc_runins( $content );

    return $content;
}

/**
 * A figure that appears before the first section heading is the page's
 * hero screenshot (getting-started role pages, settings). Tag it so it
 * gets the hero treatment rather than the in-flow one.
 */
function gki_passc_hero_figure( $content ) {
    $first_head = preg_match( '/<h[23]\b/i', $content, $m, PREG_OFFSET_CAPTURE ) ? $m[0][1] : strlen( $content );
    $head       = substr( $content, 0, $first_head );

    if ( preg_match( '/<figure\b(?![^>]*gki-hero-figure)([^>]*)>/i', $head, $fig, PREG_OFFSET_CAPTURE ) ) {
        $pos = $fig[0][1];
        // A figure that follows a step list is illustrating those steps
        // (connect pages whose duplicate H2 was dropped), not opening the page.
        if ( preg_match( '/<ol\b/i', substr( $head, 0, $pos ) ) ) {
            return $content;
        }
        $content = substr_replace( $content, '<figure' . $fig[1][0] . ' class="gki-hero-figure">', $pos, strlen( $fig[0][0] ) );
    }
    return $content;
}

/**
 * `* [ ] item` / `* [x] item` written as literal brackets (Parsedown v1 has
 * no task-list syntax) become checklist rows with a drawn box.
 */
function gki_passc_checklists( $html ) {
    $html = preg_replace_callback(
        '/<ul>((?:\s*<li>\s*\[( |x|X)\].*?<\/li>\s*)+)<\/ul>/is',
        function ( $m ) {
            $items = preg_replace_callback(
                '/<li>\s*\[( |x|X)\]\s*(.*?)<\/li>/is',
                function ( $li ) {
                    $done = strtolower( $li[1] ) === 'x';
                    return '<li class="gki-check' . ( $done ? ' gki-check--done' : '' ) . '"><span class="gki-check-box" aria-hidden="true"></span><span class="gki-check-body">' . $li[2] . '</span></li>';
                },
                $m[1]
            );
            return '<ul class="gki-checklist">' . $items . '</ul>';
        },
        $html
    );
    return $html;
}

/**
 * Drop a leading H2 whose stripped text equals the post title.
 *
 * The anchor-link plugin injects an <a class="aal_anchor"> with an SVG
 * inside every heading, so compare stripped text, not tag contents.
 */
function gki_passc_drop_duplicate_h2( $content, $title, $alt = '' ) {
    if ( preg_match( '/<h2\b[^>]*>(.*?)<\/h2>/is', $content, $h2 ) ) {
        $heading_text = trim( html_entity_decode( wp_strip_all_tags( $h2[1] ), ENT_QUOTES, 'UTF-8' ) );
        foreach ( array( $title, $alt ) as $candidate ) {
            $candidate = trim( (string) $candidate );
            if ( $candidate !== '' && strcasecmp( $heading_text, $candidate ) === 0 ) {
                $content = str_replace( $h2[0], '', $content );
                break;
            }
        }
    }
    return $content;
}

/**
 * An italic-only blockquote before the first H2 is the page's lede
 * (playbooks open this way). Lift it exactly as metric pages do.
 */
function gki_passc_lift_lede( $content ) {
    $first_h2 = stripos( $content, '<h2' );
    $head     = $first_h2 === false ? $content : substr( $content, 0, $first_h2 );

    if ( preg_match( '/<blockquote>\s*<p>\s*<em>(.*?)<\/em>\s*<\/p>\s*<\/blockquote>/is', $head, $m ) ) {
        $inner = trim( wp_strip_all_tags( $m[1] ) );
        if ( $inner !== '' && strlen( $inner ) < 500 ) {
            $content = str_replace( $m[0], '<p class="gki-definition">' . esc_html( $inner ) . '</p>', $content );
        }
    }
    return $content;
}

/**
 * Blockquotes → typed callouts, decided by the bold lead phrase.
 *
 * Handles nesting by depth-counting rather than regex, since one page
 * quotes a suggested message inside a warning. An italic-only inner
 * quote stays a quotation.
 */
function gki_passc_callouts( $html ) {
    $offset = 0;
    while ( ( $start = strpos( $html, '<blockquote>', $offset ) ) !== false ) {
        // Find the matching close.
        $depth = 0;
        $pos   = $start;
        $end   = false;
        while ( preg_match( '/<(\/?)blockquote\b[^>]*>/i', $html, $t, PREG_OFFSET_CAPTURE, $pos ) ) {
            $tag_pos = $t[0][1];
            $depth  += ( $t[1][0] === '/' ) ? -1 : 1;
            $pos     = $tag_pos + strlen( $t[0][0] );
            if ( $depth === 0 ) {
                $end = $pos;
                break;
            }
        }
        if ( $end === false ) {
            break; // unbalanced; leave the rest alone
        }

        $inner = substr( $html, $start + strlen( '<blockquote>' ), $end - $start - strlen( '<blockquote>' ) - strlen( '</blockquote>' ) );
        $inner = gki_passc_callouts( $inner ); // nested first

        $replacement = gki_passc_render_callout( $inner );
        $html        = substr_replace( $html, $replacement, $start, $end - $start );
        $offset      = $start + strlen( $replacement );
    }
    return $html;
}

function gki_passc_render_callout( $inner ) {
    $trimmed = trim( $inner );

    // Italic-only quotation → keep as a quote, restyled.
    if ( preg_match( '/^<p>\s*<em>.*<\/em>\s*<\/p>$/is', $trimmed ) ) {
        return '<blockquote class="gki-quote">' . $trimmed . '</blockquote>';
    }

    $lead = '';
    if ( preg_match( '/^<p>\s*<strong>(.*?)<\/strong>/is', $trimmed, $m ) ) {
        $lead = strtolower( wp_strip_all_tags( $m[1] ) );
    }
    $probe = $lead !== '' ? $lead : strtolower( substr( wp_strip_all_tags( $trimmed ), 0, 80 ) );

    if ( preg_match( '/private|owner|heads.?up|don.t merge|warn|restart|precedence|caution|do not|don.t|won.t|never|requires|watch|must/u', $probe ) ) {
        $type = 'warn';
    } elseif ( preg_match( '/before you start|privacy|data appears|backfill|already send|note|suppress|only returns/u', $probe ) ) {
        $type = 'info';
    } elseif ( preg_match( '/tip|recommended|you can edit|shortcut/u', $probe ) ) {
        $type = 'tip';
    } else {
        $type = 'note';
    }

    $icons = array(
        'warn' => '<svg class="gki-callout-icon" viewBox="0 0 20 20" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 3.2 2.6 16h14.8z"/><path d="M10 8v4M10 14.2h.01"/></svg>',
        'info' => '<svg class="gki-callout-icon" viewBox="0 0 20 20" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><circle cx="10" cy="10" r="7.2"/><path d="M10 9v4.5M10 6.6h.01"/></svg>',
        'tip'  => '<svg class="gki-callout-icon" viewBox="0 0 20 20" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7.5 14.5h5M8.3 17h3.4M10 2.8a5 5 0 0 1 3 9c-.6.5-1 1.2-1 2H8c0-.8-.4-1.5-1-2a5 5 0 0 1 3-9z"/></svg>',
        'note' => '<svg class="gki-callout-icon" viewBox="0 0 20 20" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><path d="M5 4.5h10M5 8.5h10M5 12.5h6"/></svg>',
    );

    return '<div class="gki-callout gki-callout--' . $type . '" role="note">' . $icons[ $type ]
         . '<div class="gki-callout-body">' . $trimmed . '</div></div>';
}

/**
 * Settings page: the `<em>In Settings UI: yes.</em>` sentence plus the
 * headerless Default / Range / Type table under each setting become one
 * mini spec panel. The "→ Full section" pointer becomes a link row.
 */
function gki_passc_settings_specs( $html ) {
    $html = preg_replace_callback(
        '/(<p>(.*?)<em>\s*In Settings UI:\s*(yes|no)\.?\s*<\/em>(.*?)<\/p>)?\s*<table>\s*<thead>\s*<tr>\s*<th>\s*<\/th>\s*<th>\s*<\/th>\s*<\/tr>\s*<\/thead>\s*<tbody>(.*?)<\/tbody>\s*<\/table>/is',
        function ( $m ) {
            $fields = array();
            if ( preg_match_all( '/<tr>\s*<td>\s*<strong>(.*?)<\/strong>\s*<\/td>\s*<td>(.*?)<\/td>\s*<\/tr>/is', $m[5], $rows, PREG_SET_ORDER ) ) {
                foreach ( $rows as $r ) {
                    $fields[ trim( wp_strip_all_tags( $r[1] ) ) ] = trim( $r[2] );
                }
            }
            if ( ! $fields ) {
                return $m[0];
            }
            if ( ! empty( $m[3] ) ) {
                $fields['In Settings UI'] = ucfirst( strtolower( $m[3] ) );
            }

            $out = '';
            $lead = trim( ( isset( $m[2] ) ? $m[2] : '' ) . ' ' . ( isset( $m[4] ) ? $m[4] : '' ) );
            if ( $lead !== '' ) {
                $out .= '<p>' . $lead . '</p>';
            }
            $out .= '<section class="gki-spec gki-spec--mini" aria-label="Setting"><dl>';
            foreach ( $fields as $k => $v ) {
                $out .= '<div class="gki-spec-field"><dt>' . esc_html( $k ) . '</dt><dd><span>' . $v . '</span></dd></div>';
            }
            $out .= '</dl></section>';
            return $out;
        },
        $html
    );

    $html = preg_replace( '/<p>\s*(?:→|&rarr;|&#8594;|-&gt;|->)?\s*(Full section|See also|See):\s*(<a\b[^>]*>.*?<\/a>)\s*\.?\s*<\/p>/isu', '<p class="gki-fullsection"><span class="gki-fullsection-label">$1</span> $2</p>', $html );

    return $html;
}

/**
 * Playbooks: runs of `### Step N — Title (3 min)` (or `### Month N — …`)
 * headings become the same numbered rail used on metric pages, with the
 * heading kept for the TOC and the time estimate set as a badge.
 */
function gki_passc_headed_steps( $html ) {
    $re_head = '/<h3\b[^>]*>(.*?)<\/h3>/is';
    $re_step = '/^(.*?)(Step|Month|Week|Phase)\s+(\d+)\s*[—–-]+\s*(.*?)\s*(?:\(([^()]*)\))?\s*$/isu';

    $parts = preg_split( '/(<h[23]\b[^>]*>.*?<\/h[23]>)/is', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
    $out   = '';
    $open  = false;

    $count = count( $parts );
    for ( $i = 0; $i < $count; $i++ ) {
        $chunk = $parts[ $i ];
        $is_h3 = (bool) preg_match( $re_head, $chunk, $h );

        if ( $is_h3 && preg_match( $re_step, $h[1], $s ) ) {
            $prefix = $s[1]; // anchor-plugin markup, if any
            $unit   = $s[2];
            $num    = $s[3];
            $title  = trim( $s[4] );
            $time   = isset( $s[5] ) ? trim( $s[5] ) : '';

            if ( ! $open ) {
                $out .= '<ol class="gki-seq gki-seq--headed">';
                $open = true;
            } else {
                $out .= '</li>';
            }

            $kicker = strcasecmp( $unit, 'Step' ) === 0 ? '' : '<span class="gki-seq-kicker">' . esc_html( $unit . ' ' . $num ) . '</span>';
            $badge  = $time !== '' ? ' <span class="gki-seq-time">' . esc_html( $time ) . '</span>' : '';

            $out .= '<li class="gki-seq-step">'
                  . preg_replace( '/^<h3\b([^>]*)>.*<\/h3>$/is', '<h3$1 class="gki-seq-title">' . $prefix . $kicker . $title . $badge . '</h3>', $chunk );
            continue;
        }

        // Any other heading (h2, or a non-step h3) closes an open sequence.
        if ( $open && preg_match( '/^<h[23]\b/i', $chunk ) ) {
            $out .= '</li></ol>';
            $open = false;
        }

        $out .= $chunk;
    }

    if ( $open ) {
        $out .= '</li></ol>';
    }

    return $out;
}

/**
 * Playbooks: the "What to do" branches (`### Pattern: …`, `### If … dominates`)
 * are alternatives, not a sequence. Tag them so they read as branches.
 */
function gki_passc_branch_headings( $html ) {
    return preg_replace_callback(
        '/<h3\b([^>]*)>(.*?)<\/h3>/is',
        function ( $m ) {
            $text = trim( wp_strip_all_tags( $m[2] ) );
            if ( preg_match( '/^(pattern:|if\s|when\s)/i', $text ) && strpos( $m[1], 'class=' ) === false ) {
                return '<h3' . $m[1] . ' class="gki-branch">' . $m[2] . '</h3>';
            }
            return $m[0];
        },
        $html
    );
}

/**
 * Fenced code blocks → the formula panel, labelled with the language.
 */
function gki_passc_code_panels( $html ) {
    return preg_replace_callback(
        '/<pre[^>]*>\s*<code([^>]*)>(.*?)<\/code>\s*<\/pre>/is',
        function ( $m ) {
            $label = 'Code';
            if ( preg_match( '/language-([a-z0-9+#-]+)/i', $m[1], $l ) ) {
                $label = strtoupper( $l[1] );
                if ( $label === 'SH' || $label === 'SHELL' || $label === 'BASH' ) { $label = 'Shell'; }
                if ( $label === 'JSON' ) { $label = 'JSON'; }
                if ( $label === 'TOML' || $label === 'YAML' || $label === 'HTTP' ) { /* keep */ }
                if ( $label === 'TEXT' || $label === 'TXT' || $label === 'PLAINTEXT' ) { $label = 'Code'; }
            }
            return '<section class="gki-formula gki-code" aria-label="' . esc_attr( $label ) . '">'
                 . '<span class="gki-formula-label">' . esc_html( $label ) . '</span>'
                 . '<pre><code' . $m[1] . '>' . $m[2] . '</code></pre></section>';
        },
        $html
    );
}

/**
 * A table whose every first cell is a link is a list of relationships —
 * give it the related-metrics treatment.
 */
function gki_passc_link_tables( $html ) {
    return preg_replace_callback(
        '/<table>(\s*<thead>.*?<\/thead>)?\s*<tbody>(.*?)<\/tbody>\s*<\/table>/is',
        function ( $m ) {
            if ( ! preg_match_all( '/<tr>\s*<td>(.*?)<\/td>/is', $m[2], $cells ) || count( $cells[1] ) < 2 ) {
                return $m[0];
            }
            foreach ( $cells[1] as $c ) {
                if ( stripos( $c, '<a ' ) === false ) {
                    return $m[0];
                }
            }
            return '<table class="gki-related-table">' . $m[1] . '<tbody>' . $m[2] . '</tbody></table>';
        },
        $html
    );
}

/* =========================================================================
   SECTION PIPELINE
   =========================================================================
   Every metric page is written to the same H3 skeleton. Splitting on H3
   lets each section receive the treatment its content pattern calls for,
   and lets the page be re-ordered so that reference material (Related
   metrics, Where it appears) closes the page rather than interrupting it.

   Unknown headings are never dropped: they keep their position relative
   to the known section that preceded them.
   ========================================================================= */

/**
 * Canonical section order. Each entry is a regex matched against the
 * heading's stripped text. The first match wins.
 */
function gki_passc_section_order() {
    return array(
        'glance'    => '/^at a glance$/i',
        'formula'   => '/^(formulas?|the rubric)$/i',
        'calc'      => '/^how .* (calculates|applies|computes) it$/i',
        'why'       => '/^why /i',
        'read'      => '/^how to read it$/i',
        'settings'  => '/^settings that affect it$/i',
        'improve'   => '/^how to (improve|use) /i',
        'limits'    => '/^limitations/i',
        'faq'       => '/^faq$/i',
        'related'   => '/^related metrics$/i',
        'surfaces'  => '/^where it appears$/i',
    );
}

/**
 * Split rendered HTML into a preamble plus one entry per <h3>.
 *
 * @return array [ [ 'key' => string|'', 'heading' => html, 'text' => string, 'body' => html ] ]
 */
function gki_passc_split_sections( $content ) {
    $parts    = preg_split( '/(<h3\b[^>]*>.*?<\/h3>)/is', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
    $sections = array();

    // parts[0] is anything before the first H3.
    if ( isset( $parts[0] ) && trim( $parts[0] ) !== '' ) {
        $sections[] = array( 'key' => '', 'heading' => '', 'text' => '', 'body' => $parts[0] );
    }

    $count = count( $parts );
    for ( $i = 1; $i < $count; $i += 2 ) {
        $heading = $parts[ $i ];
        $body    = isset( $parts[ $i + 1 ] ) ? $parts[ $i + 1 ] : '';
        $text    = trim( html_entity_decode( wp_strip_all_tags( $heading ), ENT_QUOTES, 'UTF-8' ) );

        $key = '';
        foreach ( gki_passc_section_order() as $k => $re ) {
            if ( preg_match( $re, $text ) ) {
                $key = $k;
                break;
            }
        }

        $sections[] = array( 'key' => $key, 'heading' => $heading, 'text' => $text, 'body' => $body );
    }

    return $sections;
}

/**
 * Apply per-section treatments. Order of operations matters: the formula
 * and instrument are placed before the generic run-in pass so their
 * markup is never re-parsed.
 */
function gki_passc_transform_sections( $sections, $formula, $instrument, $spec_surfaces ) {
    $has_formula_section  = false;
    $has_surfaces_section = false;
    $hero                 = '';

    foreach ( $sections as $s ) {
        if ( $s['key'] === 'formula' )  { $has_formula_section  = true; }
        if ( $s['key'] === 'surfaces' ) { $has_surfaces_section = true; }
    }

    foreach ( $sections as &$s ) {
        switch ( $s['key'] ) {

            case 'formula':
                $lead = '';
                if ( $formula !== '' ) {
                    $lead .= gki_passc_render_formula( $formula );
                }
                if ( $instrument ) {
                    $lead .= '<div class="gki-instrument" data-instrument="' . esc_attr( $instrument ) . '"></div>';
                }
                // The factor legend (a ul directly under the formula) reads as
                // a key to the formula, so it is tagged for tighter styling.
                $s['body'] = preg_replace( '/^(\s*(?:<p>.*?<\/p>\s*)?)<ul>/is', '$1<ul class="gki-legend">', $s['body'], 1 );
                $s['body'] = $lead . gki_passc_runins( $s['body'] );
                break;

            case 'calc':
                $s['body'] = gki_passc_steps( $s['body'] );
                $s['body'] = gki_passc_runins( $s['body'] );
                break;

            case 'read':
                $s['body'] = gki_passc_tier_scale( $s['body'] );
                $s['body'] = gki_passc_runins( $s['body'] );
                break;

            case 'faq':
                $s['body'] = gki_passc_faq( $s['body'] );
                break;

            case 'related':
                $s['body'] = preg_replace( '/<table>/i', '<table class="gki-related-table">', $s['body'], 1 );
                break;

            case 'surfaces':
                /* The page's one screenshot was authored here because it
                   shows the surface. As the only visual on a reference page
                   it earns the hero slot instead — lifted out now, placed
                   after At a glance below. Path, alt and caption untouched. */
                if ( preg_match( '/<figure\b[^>]*>.*?<\/figure>/is', $s['body'], $fig ) ) {
                    $hero      = preg_replace( '/^<figure\b([^>]*)>/i', '<figure$1 class="gki-hero-figure">', $fig[0], 1 );
                    $s['body'] = str_replace( $fig[0], '', $s['body'] );
                }
                $s['body'] = gki_passc_surfaces( $s['body'] );
                break;

            default:
                $s['body'] = gki_passc_runins( $s['body'] );
        }
    }
    unset( $s );

    /* Hero screenshot: after At a glance, or at the very top if there is none. */
    if ( $hero !== '' ) {
        $placed = false;
        foreach ( $sections as &$s ) {
            if ( $s['key'] === 'glance' ) {
                $s['body'] .= $hero;
                $placed     = true;
                break;
            }
        }
        unset( $s );
        if ( ! $placed ) {
            array_unshift( $sections, array( 'key' => '', 'heading' => '', 'text' => '', 'body' => $hero ) );
        }
    }

    /* A page with a formula but no Formula heading (or a sandbox with no
       formula at all) still gets the panel, directly after At a glance. */
    if ( ! $has_formula_section && ( $formula !== '' || $instrument ) ) {
        $lead = $formula !== '' ? gki_passc_render_formula( $formula ) : '';
        if ( $instrument ) {
            $lead .= '<div class="gki-instrument" data-instrument="' . esc_attr( $instrument ) . '"></div>';
        }
        $inserted = false;
        foreach ( $sections as $i => $s ) {
            if ( $s['key'] === 'glance' ) {
                array_splice( $sections, $i + 1, 0, array( array( 'key' => 'formula', 'heading' => '', 'text' => '', 'body' => $lead ) ) );
                $inserted = true;
                break;
            }
        }
        if ( ! $inserted ) {
            array_unshift( $sections, array( 'key' => 'formula', 'heading' => '', 'text' => '', 'body' => $lead ) );
        }
    }

    /* A page whose only surface list was the spec line still gets the
       closing section, built from that value. */
    if ( ! $has_surfaces_section && $spec_surfaces !== '' ) {
        $sections[] = array(
            'key'     => 'surfaces',
            'heading' => '<h3>Where it appears</h3>',
            'text'    => 'Where it appears',
            'body'    => '<ul class="gki-surfaces">' . gki_passc_surface_items_from_spec( $spec_surfaces ) . '</ul>',
        );
    }

    return $sections;
}

/**
 * Stable reorder into canonical order. Unknown sections inherit the rank
 * of the known section before them, so they travel with their neighbour.
 */
function gki_passc_order_sections( $sections ) {
    $order = array_keys( gki_passc_section_order() );
    $rank  = array_flip( $order );

    $last = -1;
    foreach ( $sections as $i => &$s ) {
        if ( $s['key'] !== '' && isset( $rank[ $s['key'] ] ) ) {
            $last = $rank[ $s['key'] ];
            $s['_rank'] = $last;
        } else {
            $s['_rank'] = $last + 0.5;
        }
        $s['_i'] = $i;
    }
    unset( $s );

    usort( $sections, function ( $a, $b ) {
        if ( $a['_rank'] == $b['_rank'] ) {
            return $a['_i'] - $b['_i'];
        }
        return ( $a['_rank'] < $b['_rank'] ) ? -1 : 1;
    } );

    return $sections;
}

/* =========================================================================
   SECTION TREATMENTS
   ========================================================================= */

/**
 * Run-in paragraphs: `<p><strong>Label.</strong> body…</p>`.
 *
 * Every metric page uses these as sub-headings inside a section. Tagging
 * them lets CSS give the label weight and a hanging rhythm without
 * touching the words. Q:/A: pairs are excluded (FAQ has its own pass),
 * as are labels that are really the start of a sentence.
 */
function gki_passc_runins( $html ) {
    return preg_replace_callback(
        '/<p>\s*<strong>\s*([^<]{2,80}?[.:])\s*<\/strong>\s*(?!<br)(.*?)<\/p>/is',
        function ( $m ) {
            $label = trim( $m[1] );
            if ( preg_match( '/^(Q|A):/i', $label ) ) {
                return $m[0];
            }
            return '<p class="gki-runin"><strong class="gki-runin-label">' . $label . '</strong> ' . ltrim( $m[2] ) . '</p>';
        },
        $html
    );
}

/**
 * Consecutive `<p><strong>Step N — Title.</strong> body</p>` paragraphs
 * become an ordered step list. Everything between one step and the next
 * (lists, follow-on paragraphs) belongs to the step it follows. The list
 * closes at the first run-in paragraph that is not itself a step.
 */
function gki_passc_steps( $html ) {
    // Both `**Step 1 — Title.**` and the bare `**Step 1.**` form.
    $re_step = '/<p>\s*<strong>\s*Step\s+(\d+)\s*(?:[—–-]+\s*(.*?)|\.)\s*<\/strong>\s*(.*?)<\/p>/is';

    if ( ! preg_match( $re_step, $html ) ) {
        return $html;
    }

    // Tokenise into top-level chunks: each <p>, <ul>, <ol>, <table>, <pre>, <figure> …
    $chunks = preg_split(
        '/(?=<(?:p|ul|ol|table|pre|figure|blockquote|div|h4)\b)/i',
        $html,
        -1,
        PREG_SPLIT_NO_EMPTY
    );

    $out      = '';
    $in_list  = false;
    $open_li  = false;

    foreach ( $chunks as $chunk ) {
        if ( preg_match( $re_step, $chunk, $m ) ) {
            if ( ! $in_list ) {
                $out    .= '<ol class="gki-seq">';
                $in_list = true;
            }
            if ( $open_li ) {
                $out .= '</li>';
            }
            $title = isset( $m[2] ) ? rtrim( trim( $m[2] ), '.' ) : '';
            $body  = trim( $m[3] );
            $out  .= '<li class="gki-seq-step' . ( $title === '' ? ' gki-seq-step--untitled' : '' ) . '">';
            if ( $title !== '' ) {
                $out .= '<h4 class="gki-seq-title">' . $title . '</h4>';
            }
            if ( $body !== '' ) {
                $out .= '<p>' . $body . '</p>';
            }
            $open_li = true;
            continue;
        }

        // A non-step run-in ends the sequence.
        $is_runin = (bool) preg_match( '/^<p>\s*<strong>[^<]{2,80}?[.:]\s*<\/strong>/is', $chunk );

        if ( $in_list && $is_runin ) {
            $out    .= '</li></ol>';
            $in_list = false;
            $open_li = false;
        }

        $out .= $chunk;
    }

    if ( $in_list ) {
        $out .= ( $open_li ? '</li>' : '' ) . '</ol>';
    }

    return $out;
}

/**
 * The first 3–6 row table whose first cell is bold becomes a tier scale:
 * a segmented track plus a list, in the table's own row order. Colour is
 * semantic only when the rows name the product's own tiers; otherwise a
 * neutral ramp is used so the picture never claims a ranking the words
 * do not.
 */
function gki_passc_tier_scale( $html ) {
    if ( ! preg_match( '/<table>\s*(?:<thead>.*?<\/thead>)?\s*<tbody>(.*?)<\/tbody>\s*<\/table>/is', $html, $t ) ) {
        return $html;
    }

    if ( ! preg_match_all( '/<tr>\s*(.*?)\s*<\/tr>/is', $t[1], $rows ) ) {
        return $html;
    }

    $n = count( $rows[1] );
    if ( $n < 3 || $n > 6 ) {
        return $html;
    }

    $items = array();
    foreach ( $rows[1] as $row ) {
        if ( ! preg_match_all( '/<td>\s*(.*?)\s*<\/td>/is', $row, $cells ) ) {
            return $html;
        }
        $c = $cells[1];
        if ( count( $c ) < 2 || ! preg_match( '/^<strong>(.*?)<\/strong>$/is', trim( $c[0] ), $lead ) ) {
            return $html; // not the pattern — leave the table alone
        }
        $items[] = array(
            'lead' => trim( $lead[1] ),
            'rest' => array_slice( $c, 1 ),
        );
    }

    /* ---- Classify every row -------------------------------------------
       name    : the band's label (lead if it is a word, else the phrase
                 before the first dash in the description)
       range   : parsed numeric interval, or null
       special : PTO / empty-state rows — listed, never drawn           */
    $product_tiers = array( 'power user' => 'power', 'regular' => 'regular', 'explorer' => 'explorer', 'emerging' => 'emerging' );
    $top_words     = '/^(power user|elite|strong|high|deep|lean|excellent)\b/';
    $special_words = '/\b(pto|empty state|n\/a|not applicable)\b/';

    $any_product = false;
    foreach ( $items as $i => &$it ) {
        // Parsedown emits &lt; / &gt; for the comparison signs; decode first.
        $lead_plain = html_entity_decode( trim( wp_strip_all_tags( $it['lead'] ) ), ENT_QUOTES, 'UTF-8' );
        $rest_plain = html_entity_decode( trim( wp_strip_all_tags( $it['rest'][0] ) ), ENT_QUOTES, 'UTF-8' );
        $lead_txt   = strtolower( $lead_plain );
        $rest_txt   = strtolower( $rest_plain );

        $lead_is_numeric = (bool) preg_match( '/^[<>≥≤]?=?\s*[0-9]/u', $lead_txt );

        if ( $lead_is_numeric ) {
            $it['range'] = gki_passc_parse_range( $lead_txt );
            $name        = preg_split( '/\s+[—–-]+\s+/u', $rest_plain, 2 );
            $it['name']  = trim( $name[0] );
        } else {
            $it['range'] = gki_passc_parse_range( $rest_txt );
            $it['name']  = $lead_plain;
        }
        $it['name_key'] = strtolower( $it['name'] );
        $it['special']  = (bool) preg_match( $special_words, $lead_txt . ' ' . $it['name_key'] );
        $it['product']  = isset( $product_tiers[ $it['name_key'] ] ) ? $product_tiers[ $it['name_key'] ] : '';
        if ( $it['product'] ) {
            $any_product = true;
        }
    }
    unset( $it );

    $drawn = array_values( array_filter( $items, function ( $it ) { return ! $it['special']; } ) );
    $count = count( $drawn );

    // Map by tier name only when the whole scale is the product's tiers;
    // one row that happens to be called "Emerging" does not make it one.
    $all_product = $count > 0;
    foreach ( $drawn as $it ) {
        if ( ! $it['product'] ) { $all_product = false; break; }
    }
    $any_product = $all_product;

    /* ---- Colour ----------------------------------------------------------
       quality  : the first band names a top level -> rank palette
       product  : rows are the product's own tiers -> by name
       ordinal  : numeric but not a judgement -> sequential ramp            */
    $first_name = $count ? $drawn[0]['name_key'] : '';
    $quality    = (bool) preg_match( $top_words, $first_name );
    $rank_pal   = array(
        3 => array( 'power', 'explorer', 'emerging' ),
        4 => array( 'power', 'regular', 'explorer', 'emerging' ),
        5 => array( 'power', 'regular', 'explorer', 'emerging', 'critical' ),
        6 => array( 'power', 'regular', 'explorer', 'emerging', 'critical', 'critical' ),
    );

    $rank = 0;
    foreach ( $items as $i => &$it ) {
        if ( $it['special'] ) {
            $it['cls'] = 'gki-tier--special';
            continue;
        }
        if ( $any_product && $it['product'] ) {
            $it['cls'] = 'gki-tier--' . $it['product'];
        } elseif ( $quality && isset( $rank_pal[ $count ] ) ) {
            $it['cls'] = 'gki-tier--' . $rank_pal[ $count ][ $rank ];
        } else {
            $it['cls'] = 'gki-tier--seq' . min( $rank + 1, 6 );
        }
        $rank++;
    }
    unset( $it );

    // Rebuild now that every row carries its class.
    $drawn = array_values( array_filter( $items, function ( $it ) { return ! $it['special']; } ) );

    /* ---- Geometry --------------------------------------------------------
       Scale only when every drawn band parses, the bands do not overlap
       (shares of a population, like the AI Tier mix, are not bands on an
       axis) and the bands are within 20x of each other; otherwise equal
       widths. Segments are laid on an ascending axis so the picture is a
       number line, not a list.                                           */
    $numeric = $count > 0;
    foreach ( $drawn as $it ) {
        if ( ! $it['range'] ) { $numeric = false; break; }
    }
    if ( $numeric ) {
        $sorted = $drawn;
        usort( $sorted, function ( $a, $b ) { return $a['range']['lo'] <=> $b['range']['lo']; } );
        for ( $i = 1; $i < $count; $i++ ) {
            $prev_hi = $sorted[ $i - 1 ]['range']['hi'];
            if ( $prev_hi === null || $sorted[ $i ]['range']['lo'] < $prev_hi - 0.0001 ) {
                // An open-ended band that is not the top, or overlapping bands.
                $numeric = false;
                break;
            }
        }
    }

    $segments = array();
    $ticks    = array();
    $unit     = $numeric ? $drawn[0]['range']['unit'] : '';

    if ( $numeric ) {
        usort( $drawn, function ( $a, $b ) {
            return $a['range']['lo'] <=> $b['range']['lo'];
        } );

        // Snap to contiguous: each band runs to the next band's lower bound.
        $bounds = array();
        $widest = 0;
        for ( $i = 0; $i < $count; $i++ ) {
            $lo = $drawn[ $i ]['range']['lo'];
            $hi = ( $i + 1 < $count ) ? $drawn[ $i + 1 ]['range']['lo'] : $drawn[ $i ]['range']['hi'];
            $open = ( $i + 1 === $count ) && $drawn[ $i ]['range']['open_hi'];
            if ( ! $open ) {
                $widest = max( $widest, $hi - $lo );
            }
            $bounds[] = array( 'lo' => $lo, 'hi' => $hi, 'open' => $open );
        }
        // An open-ended top band is drawn as wide as the widest closed band.
        foreach ( $bounds as &$b ) {
            if ( $b['open'] || $b['hi'] === null ) {
                $b['hi']   = $b['lo'] + ( $widest > 0 ? $widest : 1 );
                $b['open'] = true;
            }
        }
        unset( $b );

        $min = $bounds[0]['lo'];
        $max = $bounds[ $count - 1 ]['hi'];
        $span = $max - $min;

        $narrowest = INF;
        foreach ( $bounds as $b ) {
            $narrowest = min( $narrowest, $b['hi'] - $b['lo'] );
        }
        if ( $span <= 0 || $narrowest <= 0 || ( $widest > 0 && $widest / $narrowest > 20 ) ) {
            $numeric = false; // too lopsided to read — fall back to equal bands
        } else {
            foreach ( $bounds as $i => $b ) {
                $segments[] = array(
                    'pct'  => ( $b['hi'] - $b['lo'] ) / $span * 100,
                    'cls'  => $drawn[ $i ]['cls'],
                    'name' => $drawn[ $i ]['name'],
                    'open' => $b['open'],
                );
                $ticks[] = array( 'pct' => ( $b['lo'] - $min ) / $span * 100, 'label' => gki_passc_format_value( $b['lo'], $unit ) );
            }
            $last = $bounds[ $count - 1 ];
            $ticks[] = array(
                'pct'   => 100,
                'label' => $last['open'] ? gki_passc_format_value( $last['lo'], $unit ) . '+' : gki_passc_format_value( $last['hi'], $unit ),
                'end'   => true,
            );
            // The first tick of an open top band duplicates the "+" label; drop it.
            if ( $last['open'] && $count > 1 ) {
                array_splice( $ticks, $count - 1, 1 );
            }
        }
    }

    if ( ! $numeric && $count ) {
        // Equal bands in table order. Categorical tables (nothing parses,
        // no quality words) get no bar at all — a bar would be decoration.
        $categorical = ! $quality && ! $any_product;
        if ( ! $categorical ) {
            foreach ( $drawn as $it ) {
                $segments[] = array( 'pct' => 100 / $count, 'cls' => $it['cls'], 'name' => $it['name'], 'open' => false );
            }
        }
    }

    /* ---- Render ------------------------------------------------------------ */
    $out = '<div class="gki-tiers' . ( $numeric ? ' gki-tiers--scaled' : '' ) . '" data-count="' . (int) $n . '">';

    if ( $segments ) {
        $out .= '<div class="gki-tier-bar" aria-hidden="true">';
        foreach ( $segments as $s ) {
            // A label only when the band is wide enough and the name is a
            // name, not a sentence (Maturity Factor's rows are quotations).
            $label = ( $s['pct'] >= 13 && mb_strlen( $s['name'] ) <= 22 && ! preg_match( '/[.!?"“”]/u', $s['name'] ) )
                ? '<span class="gki-tier-bar-label">' . esc_html( $s['name'] ) . '</span>'
                : '';
            $out  .= '<span class="gki-tier-seg ' . $s['cls'] . ( $s['open'] ? ' gki-tier-seg--open' : '' ) . '" style="flex-basis:' . round( $s['pct'], 2 ) . '%">' . $label . '</span>';
        }
        $out .= '</div>';
        if ( $ticks ) {
            $out .= '<div class="gki-tier-ticks" aria-hidden="true">';
            foreach ( $ticks as $tk ) {
                $out .= '<span class="gki-tier-tick' . ( ! empty( $tk['end'] ) ? ' gki-tier-tick--end' : '' ) . '" style="left:' . round( $tk['pct'], 2 ) . '%">' . esc_html( $tk['label'] ) . '</span>';
            }
            $out .= '</div>';
        }
    }

    $out .= '<ul class="gki-tier-list">';
    foreach ( $items as $it ) {
        $out .= '<li><b class="gki-tier-lead">' . $it['lead'] . '</b>'
              . '<span class="gki-tier-desc"><i class="gki-tier-swatch ' . $it['cls'] . '"></i>'
              . implode( ' <span class="gki-tier-sep">·</span> ', $it['rest'] )
              . '</span></li>';
    }
    $out .= '</ul></div>';

    return str_replace( $t[0], $out, $html );
}

/**
 * Parse a band expression into a numeric interval.
 *
 *   "80–100"  "55 – 79"  "0.50 – 0.65"  "2 – 3.9"        closed
 *   "≥ 10"  "70%+"  "> 7 days"  "More than 1 month"       open top
 *   "< 1"  "< 24 hours"  "Less than 1 day"                [0, a]
 *   "1 day to 1 week"  "1 hour – 1 day"                   closed, mixed units
 *
 * Durations normalise to days. Returns null when the text is not a range.
 *
 * @return array|null [ lo, hi, open_hi, unit ]
 */
function gki_passc_parse_range( $text ) {
    $t = strtolower( trim( $text ) );
    $t = str_replace( array( '≥', '≤', '&gt;', '&lt;', '×', 'x ' ), array( '>=', '<=', '>', '<', '×', '× ' ), $t );
    $t = preg_replace( '/\s*\(.*?\)\s*/', ' ', $t ); // drop parentheticals
    $t = preg_replace( '/^(less than|under|below)\s+/', '< ', $t );
    $t = preg_replace( '/^(more than|over|above|at least)\s+/', '> ', $t );
    $t = preg_replace( '/\s+(to|and)\s+/', ' – ', $t );
    $t = trim( preg_replace( '/\s+/', ' ', $t ) );

    $num  = '([0-9]+(?:\.[0-9]+)?)';
    $unit = '\s*(%|×|hours?|hrs?|h|days?|d|weeks?|wks?|w|months?|mo)?';

    $conv = function ( $v, $u ) {
        $u = (string) $u;
        if ( preg_match( '/^(hours?|hrs?|h)$/', $u ) )   { return array( $v / 24, 'd' ); }
        if ( preg_match( '/^(days?|d)$/', $u ) )         { return array( $v, 'd' ); }
        if ( preg_match( '/^(weeks?|wks?|w)$/', $u ) )   { return array( $v * 7, 'd' ); }
        if ( preg_match( '/^(months?|mo)$/', $u ) )      { return array( $v * 30, 'd' ); }
        if ( $u === '%' )                                 { return array( $v, '%' ); }
        if ( $u === '×' )                                 { return array( $v, '×' ); }
        return array( $v, '' );
    };

    // a – b   (units may sit on either or both numbers)
    if ( preg_match( '/^' . $num . $unit . '\s*[–—-]\s*' . $num . $unit . '$/u', $t, $m ) ) {
        $ua = $m[2] !== '' ? $m[2] : $m[4];
        $ub = $m[4] !== '' ? $m[4] : $m[2];
        list( $lo, $u ) = $conv( (float) $m[1], $ua );
        list( $hi )     = $conv( (float) $m[3], $ub );
        if ( $hi < $lo ) { return null; }
        return array( 'lo' => $lo, 'hi' => $hi, 'open_hi' => false, 'unit' => $u );
    }
    // >= a   > a   a+
    if ( preg_match( '/^(?:>=?\s*' . $num . $unit . '|' . $num . $unit . '\s*\+)$/u', $t, $m ) ) {
        $v = $m[1] !== '' ? $m[1] : $m[3];
        $u = $m[1] !== '' ? $m[2] : $m[4];
        list( $lo, $unit_out ) = $conv( (float) $v, $u );
        return array( 'lo' => $lo, 'hi' => null, 'open_hi' => true, 'unit' => $unit_out );
    }
    // <= a   < a
    if ( preg_match( '/^<=?\s*' . $num . $unit . '$/u', $t, $m ) ) {
        list( $hi, $u ) = $conv( (float) $m[1], $m[2] );
        return array( 'lo' => 0, 'hi' => $hi, 'open_hi' => false, 'unit' => $u );
    }
    return null;
}

/**
 * Tick label for a value in the scale's unit. Integers stay integers.
 */
function gki_passc_format_value( $v, $unit ) {
    $s = ( abs( $v - round( $v ) ) < 0.001 ) ? (string) (int) round( $v ) : rtrim( rtrim( number_format( $v, 2, '.', '' ), '0' ), '.' );
    if ( $s === '0' )    { return '0'; } // "0×" and "0d" read as noise
    if ( $unit === '%' ) { return $s . '%'; }
    if ( $unit === '×' ) { return $s . '×'; }
    if ( $unit === 'd' ) { return $s . 'd'; }
    return $s;
}

/**
 * `<p><strong>Q: …</strong><br>A: …</p>` pairs become a Q/A list.
 */
function gki_passc_faq( $html ) {
    $count = 0;
    $html  = preg_replace_callback(
        '/<p>\s*<strong>\s*Q:\s*(.*?)<\/strong>\s*<br\s*\/?>\s*A:\s*(.*?)<\/p>/is',
        function ( $m ) use ( &$count ) {
            $count++;
            return '<div class="gki-faq-item"><p class="gki-faq-q">' . trim( $m[1] ) . '</p>'
                 . '<p class="gki-faq-a">' . trim( $m[2] ) . '</p></div>';
        },
        $html
    );

    if ( $count ) {
        // Wrap each contiguous run of items; items contain only <p>, so
        // the non-greedy match cannot swallow a neighbouring block.
        $html = preg_replace(
            '/((?:<div class="gki-faq-item">.*?<\/p><\/div>\s*)+)/is',
            '<div class="gki-faq">$1</div>',
            $html
        );
    }

    return $html;
}

/**
 * `<li><strong>/path</strong> — description</li>` becomes a surface row
 * with the path as a token. Items that are not paths are left as-is.
 */
function gki_passc_surfaces( $html ) {
    return preg_replace_callback(
        '/<ul>((?:\s*<li>\s*<strong>\s*\/[^<]*<\/strong>.*?<\/li>\s*)+)<\/ul>/is',
        function ( $m ) {
            $items = preg_replace_callback(
                '/<li>\s*<strong>\s*(\/[^<]*?)\s*<\/strong>\s*(?:[—–-]+\s*)?(.*?)<\/li>/is',
                function ( $li ) {
                    return '<li><code class="gki-path">' . esc_html( trim( $li[1] ) ) . '</code><span>' . trim( $li[2] ) . '</span></li>';
                },
                $m[1]
            );
            return '<ul class="gki-surfaces">' . $items . '</ul>';
        },
        $html,
        1
    );
}

/**
 * Fallback surface rows from the spec-line value, for pages without a
 * "Where it appears" section of their own.
 */
function gki_passc_surface_items_from_spec( $value ) {
    $out = '';
    foreach ( preg_split( '/\s*,\s*/', $value ) as $chunk ) {
        $chunk = trim( $chunk );
        if ( $chunk === '' ) {
            continue;
        }
        $out .= $chunk[0] === '/'
            ? '<li><code class="gki-path">' . esc_html( $chunk ) . '</code><span></span></li>'
            : '<li><span>' . esc_html( $chunk ) . '</span></li>';
    }
    return $out;
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
