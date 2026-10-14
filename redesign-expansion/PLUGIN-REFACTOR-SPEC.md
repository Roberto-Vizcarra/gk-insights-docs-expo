# Spec: GKI Docs Helper Multi-Help-Center Refactor

> **Status:** Draft spec. Not started; deferred with the [proposal](PROPOSAL.md).
> **Written against:** plugin v1.17.2 (`main` at `de5ef02`). Line references
> point to that version. Re-check them before starting.

## Goal

Let the plugin serve any number of WordPress categories. Each category gets its
own title, nav, search scope, home layout and feature set, and Insights stays
byte-for-byte the same in behaviour.

## Non-goals

- No changes to Markdown content in this phase.
- No renaming of the `gki-` prefix for classes, tokens, cookie or options. It is
  cosmetic, and renaming it would break stored preferences.
- No change to the auth gate's behaviour for Insights.

## 1. Product registry

Add `includes/gki-registry.php`, loaded first and defensively like the other
modules:

```php
function gki_docs_registry() {
    return apply_filters( 'gki_docs_registry', array(
        'insights-expo' => array(
            'title'          => 'GitKraken Insights',
            'section_order'  => array( 'getting-started', 'connect-your-data', 'metrics', 'playbooks', 'admin' ),
            'palette_hint'   => 'Search metrics, playbooks, settings…',
            'accent'         => null,            // null = shared purple tokens
            'home'           => 'insights',      // home module: 'insights' | 'generic'
            'toc_levels'     => array( 2, 3 ),
            'enabled'        => true,
            'features'       => array(
                'auth_gate'       => true,       // still off unless enabled in Settings
                'metric_pipeline' => true,
                'instruments'     => true,
                'settings_specs'  => true,
            ),
        ),
        // Future entries, disabled until their phase ships:
        // 'kepler' => array( 'title' => 'Kepler', 'enabled' => false, 'home' => 'generic', ... ),
    ) );
}
```

Add `gki_docs_current_hc()`:

- It returns the registry entry plus `slug` for the current single post, or
  `null`.
- It resolves the post's categories against the enabled registry keys once per
  request, using a static cache.
- If a post belongs to two registered categories, the first registry key wins.
  Log a notice when that happens.

## 2. Replace the single-category coupling

| Today (v1.17.2) | Change |
| --- | --- |
| `define( 'GKI_DOCS_CATEGORY', 'insights-expo' )` (`gki-docs-helper.php:43`) | Keep it as a deprecated fallback for one release; nothing reads it |
| `gki_docs_is_target_post()` uses `has_category( GKI_DOCS_CATEGORY )` | `return (bool) gki_docs_current_hc();` |
| `get_posts( 'category_name' => GKI_DOCS_CATEGORY )` in the enqueue, nav, breadcrumbs, child cards, article count, `gki_passc_metric_map()` and `gki_passc_role_paths()` | Use `gki_docs_current_hc()['slug']` |
| `$section_order` hardcoded in `gki_docs_get_nav_structure()` | `section_order` from the registry |
| "GitKraken Insights" in `single-gki.php:35-41, 270` | `title` from the registry |
| Menu location `gki-insights-nav` | Register `gki-nav-{slug}` for each enabled product. Keep `gki-insights-nav` as an alias for Insights |
| Palette placeholder hardcoded in `gki-passc.js:885` | Pass `palette_hint` through `wp_localize_script` |
| `gki_auth_is_gated_page()` uses `has_category( GKI_DOCS_CATEGORY )` (`gki-auth.php:826`) | Gate only when `features.auth_gate` is true for the current product |
| `/insights-expo/login/` endpoint | Unchanged; Insights-only |

## 3. Feature flags

| Flag | Gates |
| --- | --- |
| `metric_pipeline` | `gki_passc_restructure()` (filter priority 16) |
| `instruments` | Instrument registry and JS. Move the instrument code (`gki-passc.js:188-690`) into `js/gki-instruments.js` and enqueue it only when this flag is set |
| `auth_gate` | `gki_auth_gate_check()` |
| `settings_specs` | `gki_passc_settings_specs()`. Today it triggers on slugs ending in `-settings`; it should also trigger on `layout: settings` |

## 4. Home page

- `home: insights` keeps the current hero, role paths and metric map.
- `home: generic` builds the same hero, then adds:
  - **Entry paths** from posts carrying `entry_role`, `entry_title` and
    `entry_desc` custom fields, ordered by `entry_order`. This replaces the
    hardcoded copy in `gki_passc_role_paths()`.
  - **Section cards** from the `page_type: index` posts, which is the existing
    behaviour.
  - **An optional product module** chosen by a registry callback:
    - MCP: a tools catalog
    - Kepler: an agent chip row

## 5. Content adapters

These are new `the_content` transforms at priority 17. They run for every
registered product and are safe for Insights, which uses none of these patterns.

| Pattern in source | Becomes | Repos |
| --- | --- | --- |
| `<div class="callout callout--basic\|info">` | `gki-callout--note` / `--info` | Desktop, GitLens, gk-dev |
| `<div class="callout callout--warning\|danger">` | `gki-callout--warn` | Desktop, GitLens, gk-dev |
| `<div class="callout callout--success">` | `gki-callout--tip` | Desktop |
| `<div class="bbb-callout bbb--info\|note\|tip\|alert\|error">…<div class="imsgbox">` | `--info` / `--note` / `--tip` / `--warn` / `--danger`, with only the `imsgbox` body kept | GIJ Cloud, GIJ Data Center, gk-dev |
| `<div class="note" markdown="1">` | `gki-callout--note` | Kepler |
| `> **Note:**`, `**Tip:**`, `**Warning:**`, `**Important:**`, `**Deprecated:**`, `**Requirements:**` | Typed by explicit prefix before the keyword fallback | CLI, MCP, GitLens |
| `<style>` + `<script>` copy-button blocks at the end of a page | Removed; the plugin's code panels already provide copy | Desktop (12 pages), CLI (8 pages) |
| `` `PRO` `` / `` `PREVIEW` `` / `` `ADVANCED` `` code spans in headings | Tier badges, excluded from the TOC text | GitLens, gk-dev |
| "**Requirements and limits**" bullet block | Spec panel | Desktop (79 pages) |
| `### Problem` / `### Diagnosis` / `### Cause` / `### Solution` | Issue layout (label column plus body) | GIJ Data Center (29 pages) |
| Leading `<kbd>Last updated: …</kbd>` | Page-meta line under the H1 | Most repos |
| Manual "**On this page:**" anchor lists | Removed, because the right TOC replaces them | GIJ Cloud (38 pages) |
| Runs of `&nbsp;` / `<br>` between blocks | Collapsed | GIJ Cloud, GIJ Data Center |
| Unlabelled fenced code | Code panel labelled "Terminal" for each product (`code_default_label`) | CLI |

TOC depth comes from `toc_levels`. GIJ Data Center needs `array( 3, 4 )` until
its headings are normalized.

## 6. Caching

- **Nav, breadcrumb and child-card data:** compute once per category and store
  in a transient `gki_nav_{slug}`. Flush it on `save_post`, `deleted_post` and
  `set_object_terms` for posts in that category.
- **Search index:** move it out of `wp_localize_script` into
  `/?gki-search={slug}`. That endpoint returns JSON from transient
  `gki_search_{slug}` with an ETag, and the palette fetches it the first time it
  opens. Keep the inline index for categories under 100 pages if that is
  simpler.
- **Gated products:** the search endpoint must enforce the same gate as the
  pages. This covers the existing 1.17.1 rule that the search index never
  reaches unauthenticated visitors.

## 7. Frontmatter contract for new products

Each page needs a `custom_fields` block. Git It Write maps it to post meta.

```yaml
custom_fields:
    nav_category: core-concepts     # section key; must exist in the registry's section_order
    nav_order: 20                   # sort within section (gaps of 10)
    nav_label: Task View            # optional short label
    nav_parent: kepler-concepts     # optional; slug of a sub-index
    page_type: content              # main-index | index | content
    card_icon: layout-board         # Tabler icon name (index pages and card children)
    card_color: blue                # purple | blue | green | amber | red
    card_description: One line shown on the card
    layout: settings                # optional: steps | settings | reference | issue
    entry_role: Developer           # optional: home entry path
    entry_title: Work inside a Task
    entry_desc: The Task View: everything one piece of work holds.
    entry_order: 10
```

Each product repo gets one script that writes these fields from its section map
(`nav.json`, `llms.txt` groups or a hand-written YAML map), plus a check that
fails CI when a page has no `nav_category`.

## 8. Acceptance criteria for phase 1

- [ ] With only `insights-expo` registered, every Insights page renders the same
      HTML as v1.17.2. Exceptions: asset version strings, and the instrument JS
      moving into its own file. Verify by diffing saved HTML from the local
      import for Home, one metric page, one playbook, Settings, and the gate page.
- [ ] Posts outside registered categories get no plugin classes, assets,
      template or filters.
- [ ] Registering a disabled product has no effect.
- [ ] Registering an enabled test category on the local import gives that
      category:
      - its own title, nav, search scope and palette hint
      - no auth gate
      - no metric pipeline
      - no instrument JS
- [ ] Nav and search data come from transients after the first request, and
      saving a post flushes them.
- [ ] `build-plugin.py` packages the new `includes/` and `js/` files; the zip
      layout check passes.

## 9. Traps to carry over

The standing rules in the repo `CLAUDE.md` apply unchanged:

- No `grid-template-columns` in a transition on `.gki-layout`.
- No fixed `height` on `.gki-sidebar--left`.
- Every Elementor override is prefixed with `body.gki-docs-page` and marked
  `!important`. That applies to anything rendered outside `.gki-page` too.
- Test on the local Docker import, never in production.
