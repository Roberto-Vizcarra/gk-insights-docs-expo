# GKI Help Center Redesign — Project Status

**Last updated:** 2026-09-01
**Owner:** roberto.vizcarra@gitkraken.com
**Repos:**
- `gk-insights-docs-expo` — plugin + exploration content (this repo)
- `gk-insights-docs` — production Help Center content (restructured pages per CLAUDE.md)

## Architecture

```
WordPress (gitkraken.com/…)
  ├── Elementor Pro Theme Builder (dark kit — fights us)
  ├── Git It Write plugin (syncs repo Markdown → WP posts)
  │     └── Category: insights-expo → gk-insights-expo/ directory
  └── GKI Docs Helper plugin (our custom plugin)
        ├── template override (priority 9999)
        ├── Parsedown cleanup (the_content filter)
        ├── CSS (brand tokens + Elementor overrides)
        └── JS (TOC, search, lightbox, etc.)
```

### How Content Gets to WordPress

1. Markdown files in `gk-insights-expo/` are committed to the repo.
2. Git It Write reads the repo, converts Markdown via Parsedown v1, and creates/updates WP posts in the `insights-expo` category.
3. GKI Docs Helper detects posts in that category, applies the custom template, enqueues CSS/JS, and cleans up Parsedown artifacts.

## Current State (v1.5.0)

### Working
- Custom 3-column template overrides Elementor
- Light background forced over dark Elementor kit
- Heading colors forced to dark (#1C1C1C)
- Body text readable (#414141)
- Purple limited to accent role (borders, links, step circles)
- Image lightbox (click to expand, Escape to close)
- **Frontmatter-based hierarchical nav** (nav_category, nav_order, nav_label)
- Alphabetical nav fallback when no frontmatter is present
- Right sidebar TOC with scroll spy (hidden on index pages)
- Reading progress bar (hidden on index pages), back-to-top button
- Smooth-scroll anchor links
- **3-wide card grid** with tinted icon backgrounds and hover effects
- **Index page detection** via `page_type` frontmatter (main-index, index, content)
- **Auto-rendered card grids** on index pages from child page frontmatter
- **Breadcrumb navigation** (Home > Section > Page)
- **Tabler Icons webfont** loaded from jsDelivr CDN for card icons
- Responsive: 3-col → 2-col → 1-col at breakpoints
- `filemtime()` cache busting (no manual version bumps for CSS/JS)

### Needs Verification After Upload
- **v1.4.0 items still pending** — Nav link colors, card aspect-ratio tuning, search filter threshold.
- **Tabler Icons CDN** — Confirm `cdn.jsdelivr.net` is not blocked by any WAF or CSP on gitkraken.com.
- **Frontmatter nav** — Requires content pages to have `nav_category`, `nav_order`, `nav_label`, `page_type` custom fields. Git It Write must store these from YAML frontmatter as post meta.
- **Index card grid** — Card icons, colors, and descriptions all read from frontmatter (`card_icon`, `card_color`, `card_description`). Verify rendering once content pages have frontmatter.
- **Breadcrumb** — Requires `page_type: main-index` on exactly one page. Falls back gracefully (no breadcrumb shown) if missing.

### Known Issues
1. **Parsedown v1 doesn't parse Markdown inside HTML blocks.** This is a fundamental Parsedown limitation. Content must use raw HTML for anything inside `<div class="gki-page">`. No fix possible without replacing Parsedown or the entire Git It Write pipeline.
2. **Elementor specificity is fragile.** Our CSS uses `!important` with `body.gki-docs-page` selectors. If Elementor kit updates change selectors or add inline styles, overrides may break. After any theme update, check: heading colors, link colors in nav/TOC, body background.
3. **No auto-update mechanism.** Plugin must be manually uploaded via WP Admin after each change. Webhook-based auto-update was discussed but tabled because the repo is public.

## Elementor Override Reference

These are the specific Elementor/kit rules that cause problems:

| Elementor Rule | Effect | Our Override |
|---|---|---|
| `.elementor-kit-5 h1 { color: rgb(241,241,241) }` | Headings near-white | `.gki-docs-page .gki-page h1 { color: #1C1C1C !important }` |
| `.elementor-kit-5 a { color: rgb(248,171,255) }` | Links hot pink | `body.gki-docs-page .gki-nav-item a:link { color: #414141 !important }` |
| Kit body background (dark) | Dark page background | `body.gki-docs-page { background: #FFFFFF !important }` |
| Elementor Theme Builder `template_include` | Overrides WP template | Plugin hooks at priority 9999 + `template_id` filter returns 0 |

## CSS Custom Properties

All brand tokens are defined in `:root` in `gki-docs.css`:

| Token | Value | Usage |
|---|---|---|
| `--gki-purple` | `#7900C9` | Accent borders, links, step circles |
| `--gki-blue` | `#3F36BA` | Info callout, progress gradient |
| `--gki-teal` | `#15777E` | Tip callouts |
| `--gki-text` | `#1C1C1C` | Primary text, headings |
| `--gki-text-secondary` | `#414141` | Body text, descriptions |
| `--gki-text-muted` | `#636568` | Captions, timestamps |
| `--gki-bg` | `#FFFFFF` | Page background |
| `--gki-bg-subtle` | `#F5F6F8` | Card backgrounds, hover states |
| `--gki-border-light` | `#E0E1E3` | Most borders |
| `--gki-font` | `Inter` + system fallback | All text |

## Content Structure (gk-insights-docs repo)

The `gk-insights-docs` repo follows the CLAUDE.md directive to split the monolithic Dashboard Management page into category-based pages:

1. **DORA Metrics** — Deploy Frequency, Change Lead Time, MTTR, Defect Rate
2. **Pull Request Metrics** — First Response, Cycle Time, Lead Time, Reviews, Open Time, Abandoned, Merged Without Review, Comments, Size/Effort, Code Review Hours
3. **AI Impact Metrics** — Copy/paste %, Duplicated code, Rework %, Post-PR work, Active Users, Suggestions, Prompt Acceptance, Tab Acceptance
4. **Code Quality Metrics** — Bug Work %, Doc & Test %, Code Change Rate, Code Change by Operation
5. **Velocity / Delivery Consistency** — Commit Count, Estimated Coding Hours
6. **Dashboard Configuration** — filter/config UI
7. **Metric Settings** — metric-level settings

## Exploration Files

Three design explorations in `gk-insights-expo/` demonstrate different approaches:

| File | Approach | Notes |
|---|---|---|
| `exploration-v1-css-only.md` | Pure CSS enhancement of Markdown | Minimal HTML, relies on Markdown rendering |
| `exploration-v2-html-css-cards.md` | Full HTML with card grid | Better visual design, no JS needed |
| `exploration-v3-interactive.md` | HTML + CSS + embedded JS | TOC, search, collapsible sections |

The plugin's shared CSS supports all three approaches. The custom template (single-gki.php) provides the 3-column layout for any of them.

## Remaining Work

### Short-term
- Verify v1.5.0 plugin upload (Tabler CDN, card rendering, nav hierarchy)
- Tune card aspect-ratio and responsive breakpoints on real devices
- Verify all 28 content pages render correctly after Git It Write sync

### Completed (Phase 3 — Content Breakout, 2026-09-01)
- Frontmatter added to all 28 content pages (nav_category, nav_order, nav_label, page_type, card_icon, card_color, card_description)
- Getting Started split into 6 pages (overview + first-dashboard, for-executives, for-engineering-leaders, for-team-leads, for-admins)
- Connect Your Data split into 7 pages (overview + github, bitbucket, azure-devops, gitlab, ai-tools, jira-bamboohr)
- Playbooks split into 5 pages (index + tier-weights, ai-rollout, slow-cycle-time, high-cfr)
- Metrics index page and Admin & API index page created
- All cross-file links rewritten to point to new standalone pages (12 link rewrites across 6 files)
- Technical writing standards pass (heading clarity fixes)
- All kbd tags updated to September 2026
- Final verification: 10 unique images, zero stale links, complete frontmatter on all 28 pages

### Medium-term
- Add dark mode support (currently light-only; would need a second set of token values)

### Long-term
- Auto-update mechanism (webhook or GitHub Actions → WP REST API)
- Replace Git It Write + Parsedown with a pipeline that handles Markdown-in-HTML-blocks
- Consider migrating to a dedicated docs platform if WP/Elementor friction continues
