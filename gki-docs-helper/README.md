# GKI Docs Helper

WordPress plugin that provides a custom page template, shared CSS/JS, and Parsedown cleanup for GitKraken Insights Help Center pages.

**Version:** 1.4.0
**Requires:** WordPress 5.8+, [Git It Write](https://developer.wordpress.org/plugins/git-it-write/) plugin
**Category target:** `insights-expo`

## What It Does

- **Custom 3-column template** for `insights-expo` posts: left nav, center content, right TOC
- **Shared CSS** with GitKraken "Celestial" brand tokens (light mode, Inter font, Major Third type scale)
- **Parsedown cleanup** via `the_content` filter (removes empty `<p>`, stray `<br>`, block-wrapping `<p>`)
- **JS features:** auto-generated TOC with scroll spy, card search/filter, image lightbox, back-to-top, reading progress bar, smooth-scroll anchors
- **Elementor overrides** to force light background, dark text, and non-purple nav links (the WP site uses Elementor Pro with a dark theme kit)

## File Structure

```
gki-docs-helper/
  gki-docs-helper.php    # Main plugin file — hooks, filters, template override
  css/gki-docs.css       # All styles: brand tokens, component styles, Elementor overrides, layout
  js/gki-docs.js         # TOC builder, search, lightbox, back-to-top, progress bar
  templates/
    single-gki.php       # 3-column page template (replaces Elementor's single-post template)
  gki-docs-helper.zip    # Installable plugin archive (rebuild after changes)
```

## Installation

1. Zip the plugin directory (exclude `.zip` files inside it):
   ```bash
   cd gki-docs-helper
   zip -r gki-docs-helper.zip gki-docs-helper.php css/ js/ templates/ -x "*.DS_Store"
   ```
2. In WP Admin: Plugins > Add New > Upload Plugin > select the zip > Install > Activate.

Or copy the `gki-docs-helper/` folder directly into `wp-content/plugins/`.

## Configuration

**Category slug** — change `GKI_DOCS_CATEGORY` in `gki-docs-helper.php` (default: `insights-expo`). The plugin only activates on posts in this category.

**Sidebar navigation** — two modes:
1. **Auto-generated** (default): lists all posts in the target category, sorted alphabetically.
2. **Manual menu**: assign a WP menu to the "GKI Insights Sidebar Navigation" location in Appearance > Menus.

## Cache Busting

CSS and JS versions use `filemtime()` so WordPress cache-busts automatically when files change on disk. No version bump needed for style-only changes — just upload the updated files.

## Elementor Compatibility

The plugin fights Elementor Pro's Theme Builder for control of the single-post template. Key mechanisms:

| Problem | Solution |
|---|---|
| Elementor overrides `template_include` | Plugin hooks at priority **9999** (Elementor uses ~999) |
| Elementor re-applies its template via Theme Builder | `elementor/theme/get_location_templates/template_id` filter returns `0` for target posts |
| Elementor Kit sets `h1 { color: rgb(241,241,241) }` (near-white) | CSS uses `.gki-docs-page .gki-page h1 { color: #1C1C1C !important; }` |
| Kit sets body background dark | `body.gki-docs-page { background: #FFFFFF !important; }` |
| Kit sets link color to pink `rgb(248,171,255)` | Sidebar links use `body.gki-docs-page .gki-nav-item a { color: #414141 !important; }` with `:link`/`:visited` states |

## Design Decisions

- **Purple is accent-only.** Used for: link text, active-state left borders, step-number circles, hover card borders, progress bar gradient, back-to-top button. Never used for heading text, nav text, or large surface areas.
- **Light mode only.** The WP site runs a dark Elementor theme, but Help Center pages force a white background.
- **No external dependencies.** All CSS/JS is self-contained. No CDN imports.
- **Parsedown workarounds.** Git It Write uses Parsedown v1, which does not process Markdown inside HTML block elements. Content files use raw HTML for structured components (cards, callouts, steps) and Markdown only for plain prose sections.

## Known Issues

1. **Parsedown v1 limitation** — Markdown inside `<div class="gki-page">` is not parsed. All structured content must be raw HTML.
2. **Elementor specificity wars** — if the theme updates its kit CSS, our `!important` overrides may need updating. Check heading/link colors after theme updates.
3. **Card aspect ratio** — `aspect-ratio: 1.2 / 1` makes cards near-square; adjust if content varies widely in length.

## Content Authoring

Content files live in the `gk-insights-expo/` directory and are synced to WordPress via Git It Write. Each `.md` file maps to a WP post in the `insights-expo` category.

Since Parsedown v1 doesn't parse Markdown inside HTML blocks, content files follow a hybrid approach:
- Use `<div class="gki-page">` as the outer wrapper
- Use HTML for structured components: `.gki-card-grid`, `.gki-callout`, `.gki-steps`, `.gki-figure`, etc.
- Use standard Markdown for plain text sections (only works outside HTML block elements)

## Rebuilding the Zip

After any change to `.php`, `.css`, or `.js` files:

```bash
cd gki-docs-helper
zip -r gki-docs-helper.zip gki-docs-helper.php css/ js/ templates/ -x "*.DS_Store"
```

Then upload via WP Admin > Plugins > Add New > Upload Plugin (deactivate/delete old version first, or use the "Replace current with uploaded" option).
