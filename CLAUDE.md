# CLAUDE.md

## Project Overview

This repo contains the **GitKraken Insights AI Adoption Help Center** — ~50 Markdown content pages and a custom WordPress plugin (GKI Docs Helper) that provides the template, CSS, JS, and Elementor overrides for the Help Center at `help.gitkraken.com/insights-expo/`.

## Current State (September 2026)

Phases 1–4 are substantially complete. The content breakout (including metric-level sub-pages), collapsible nav, site-wide search, and index-page card-first layout are all in the plugin. Minor polish items may remain — see `PHASE-4-HANDOFF.md`.

### What's Live
- ~50 Markdown files in `gk-insights-expo/` synced to WordPress via Git It Write
- GKI Docs Helper plugin v1.9.0 installed on WordPress
- 3-column layout: left nav, center content, right TOC
- Collapsible nav sidebar with nested sub-groups (section → sub-index → page)
- Site-wide search (PHP-generated JSON index, JS client)
- Card grid index pages with tinted icons (auto-rendered from frontmatter)
- Index pages follow Intro → Cards → Overview order (content split at `<hr>`)
- Card filter on index pages (local, alongside global search)
- Image lightbox, progress bar, back-to-top, smooth scroll
- `nav_parent` custom field enables arbitrary nesting depth

### Plugin Location
```
gki-docs-helper/
  gki-docs-helper.php      # Main plugin file (v1.9.0)
  includes/gki-auth.php    # Auth gate module (OAuth + Insights entitlement check)
  css/gki-docs.css          # All styles
  js/gki-docs.js            # Interactive features (8 modules)
  templates/single-gki.php  # 3-column page template
  templates/gki-gate.php    # Auth gate page (login / no-access variants)
  gki-docs-helper.zip       # Installable archive — REBUILD AFTER EVERY CHANGE
```

### Local Test Environment (added 2026-09-14)
```
local-dev/
  setup.ps1                 # WP Migrate export -> verified running local site
  docker-compose.yml        # mariadb + wordpress + wp-cli + adminer
```
A Dockerized WordPress at `http://localhost:8420` running a full import of the
production database, themes and plugins. `gki-docs-helper/` is bind-mounted, so
the repo directory IS the running plugin — edit and refresh, no zip rebuild
while iterating. See `LOCAL-DEV.md` for the full runbook.

**Test plugin changes here before touching the live site.** Both historical
site-down incidents came from testing in prod.

### Content Location
```
gk-insights-expo/           # ~50 Markdown files → WP posts via Git It Write
```

---

## Standing Rules

These rules apply to ALL sessions working on this project.

### Content Integrity
- Do not invent product behavior or expand metric definitions.
- Do not add new images. Do not remove or rename existing images.
- Preserve all image paths and alt text exactly.
- Move content; do not rewrite unless necessary for clarity.
- If information does not exist in source, do not create it.
- If behavior is unclear, leave unchanged and flag for human review.

### Technical Writing Standards
- Active voice preferred ("GitKraken calculates…" not "Is calculated by…").
- Clear, task-based headings. No generic "Overview" / "Details".
- One topic per section. Cross-link instead of duplicating.
- Bulleted lists for non-sequential info; numbered lists for steps.
- Headings semantically ordered (H2 → H3). No ALL CAPS.
- No keyword-stuffed headings. Metric names must stay consistent.

### Layout Traps (measured, do not re-introduce)
- **Never put `grid-template-columns` in a `transition` on `.gki-layout`.**
  Chrome will not interpolate between the expanded and collapsed track lists and
  holds the *from* value indefinitely, so collapsing the nav silently does
  nothing. Measured 2026-09-14: with the transition on, the computed value
  stayed `240px 860px 220px`; with it off, the same class change resolved to
  `0px 1100px 220px` immediately.
- **Never give `.gki-sidebar--left` a fixed `height`.** Use `max-height` so the
  sidebar is content-sized. A full-viewport-height sidebar makes the theme
  switch's `margin-top: auto` fling it to the bottom of the screen, and
  stretches anything absolutely positioned inside it into a full-height bar.

### Local Environment Traps (measured, do not re-introduce)
Full explanations in `LOCAL-DEV.md`. The short version:

- **Never define `WP_DEBUG` in `WORDPRESS_CONFIG_EXTRA`.** The `wordpress` image
  already defines it. Redefining raises a PHP warning, that warning is output
  before headers, and every redirect and template load breaks site-wide. The
  symptom is a 200 response of ~0.5 KB. Use the `WORDPRESS_DEBUG: 1` env var.
- **Never turn on `display_errors`.** Same failure, different source. Errors go
  to `wp-content/debug.log`; nothing is printed to the page.
- **Never set the permalink structure.** Prod uses `/%category%/%postname%/` and
  the `/insights-expo/` prefix depends on it. Flush rewrite rules only.
- **Force `utf8mb4` on import.** MariaDB defaults to latin1 and WP Migrate dumps
  carry `utf8mb3`. Without normalization, emoji and curly quotes become `?` —
  corrupting post content silently. Anchor the rewrite to charset *declarations*
  only; a blind `s/utf8/utf8mb4/g` edits page content and yields `utf8mb4mb3`.
- **`wp-config.php` is generated once.** Changing `WORDPRESS_*` env on an
  existing volume does nothing. Use `-RebuildConfig` or `-Fresh`.
- **Never merge `docker compose` stderr with `2>&1` in PowerShell 5.1.** Compose
  writes progress to stderr; under `$ErrorActionPreference='Stop'` that throws
  `NativeCommandError` on a successful command.

### Verified Production Facts (confirmed by local import, 2026-09-14)
| Fact | Value |
|---|---|
| Elementor active kit ID | `5` — so `.elementor-kit-5` is correct |
| Permalink structure | `/%category%/%postname%/` |
| Docs post type | `post`, category `insights-expo` (not a CPT) |
| Table prefix | `wp_` |
| Posts collation | `utf8mb4_unicode_520_ci` |
| Total published posts | 3,622 (whole help center); 50 are insights-expo |
| Active theme | `hello-elementor` + `hello-elementor-child` |
| Other search plugin | SearchWP also indexes this content — see PROJECT-STATUS |

### Design Rules
- **Purple (#7900C9) is accent only** — links, active borders, step circles, hover effects. Never a main/heading color.
- Heading text: #1C1C1C. Body text: #414141.
- Cards drive navigation on index pages. Index pages: Intro → Cards → Overview.
- All Elementor overrides require `body.gki-docs-page` prefix + `!important`.

### Git It Write Requirements
- Custom fields MUST be nested under `custom_fields:` in YAML frontmatter to be stored as WordPress post meta. Top-level YAML keys are NOT stored.
- WordPress URLs: `help.gitkraken.com/insights-expo/{slug}` — slug comes from filename.
- Internal links use `/insights-expo/expo-ai-adoption-*` format.
- Git It Write does NOT auto-delete WP posts when source files are removed. Old posts must be manually deleted from WP Admin.

### Plugin Workflow
- **Test locally first.** `cd local-dev; docker compose up -d`, then edit and
  refresh at `http://localhost:8420`. Read `wp-content/debug.log` before
  building any zip. See `LOCAL-DEV.md`.
- The local site proves the plugin *renders*; it does not prove the *zip* is
  valid. Both historical outages were packaging failures, so `build-plugin.py`
  remains mandatory.
- After ANY plugin file change: bump version in both the header comment AND `GKI_DOCS_VERSION` constant, then rebuild the zip.
- **Zip rebuild: `python build-plugin.py` from the repo root. Nothing else.**
  Hand-rolled zip commands have produced a site-down fatal twice:
  - Python `zipfile` with `os.path.join()` on Windows wrote entries as
    `css\gki-docs.css`. A ZIP separator is always `/`, so PHP's unzip made one
    file literally named `css\gki-docs.css` and no `includes/` directory —
    `require_once includes/gki-auth.php` then fataled uncatchably.
  - `cd gki-docs-helper && zip -r … .` produced an archive with no root folder.
    WordPress expects exactly one top-level directory.
  `build-plugin.py` forces forward slashes, a single `gki-docs-helper/` root,
  and verifies every required file is present plus that the header version and
  `GKI_DOCS_VERSION` agree — it writes nothing if any check fails.
- Upload zip to WP Admin → Plugins after each version bump.

### Auth Gate (v1.9.0)
- All `/insights-expo/` pages are gated behind GitKraken OAuth + Insights subscription entitlement.
- Requires **OpenID Connect Generic Client** WP plugin for the OAuth login flow against `gitkraken.dev`.
- Auth gate is disabled by default — enable via Settings → GKI Auth Gate in WP Admin.
- WP admins always bypass the gate.
- Entitlement results are cached in WP user meta (default 30 min TTL, configurable).
- Gate template has two variants: "sign in" (unauthenticated) and "subscription required" (no Insights).
- Entitlement check uses `GET /user/organizations` — if any org has `totalInsightsLicenses > 0`, access is granted. This is org-level, not per-user (acceptable for docs access).
- See `AUTH-GATE-PLAN.md` for the full implementation plan and backend team requirements.

---

## Site Structure

### Nav Sections (frontmatter-driven, sorted by nav_order)
1. **Home** — `expo-ai-adoption-home.md` (main-index, card grid of section indexes)
2. **Getting Started** — 6 pages (index + first-dashboard, for-executives, for-engineering-leaders, for-team-leads, for-admins)
3. **Connect Your Data** — 7 pages (index + github, bitbucket, azure-devops, gitlab, ai-tools, jira-bamboohr)
4. **Metrics** — 26 pages total:
   - `expo-ai-adoption-metrics.md` (section index)
   - **Adoption & Agentic** — sub-index + 5 metric pages (adoption-score, autonomy-score, ai-tier, maturity-factor, cursor-boost)
   - **Output & Throughput** — sub-index + 4 metric pages (output-score, throughput, direct-commits, effort-score)
   - **Flow & Cycle Time** — sub-index + 4 metric pages (cycle-time, review-cycles, first-pass-rate, wip)
   - **DORA & Quality** — sub-index + 4 metric pages (deployment-frequency, lead-time, change-failure-rate, mttr)
   - **AI Impact & Cost** — sub-index + 4 metric pages (productivity-uplift, ai-assisted-percentage, capex-opex, spend-by-tier)
5. **Playbooks** — 5 pages (index + tier-weights, ai-rollout, slow-cycle-time, high-cfr)
6. **Admin & API** — 3 pages (index + settings, manual-releases-api)

Plus 1 hidden redirect: `expo-ai-adoption.md` (nav_category: hidden)

### Page Types
- `main-index` — the home page; card grid of section indexes inserted after intro
- `index` — section or sub-section landing; card grid of child pages rendered between intro and overview content
- `content` — regular article with TOC sidebar

### Nav Hierarchy (nav_parent)
Child pages reference their parent's WordPress slug via the `nav_parent` custom field. This enables nested navigation: Metrics section → metric family sub-index → individual metric page. The nav template traverses up to 3 levels. Breadcrumbs walk the `nav_parent` chain with cycle detection.

---

## Key Technical Context

### Parsedown v1 Limitation
Git It Write uses Parsedown v1, which does NOT parse Markdown inside HTML block elements. Content that mixes HTML and Markdown must account for this.

### Elementor Override Table

| Elementor Rule | Our Override |
|---|---|
| `.elementor-kit-5 h1 { color: rgb(241,241,241) }` | `.gki-docs-page .gki-page h1 { color: #1C1C1C !important }` |
| `.elementor-kit-5 a { color: rgb(248,171,255) }` | `body.gki-docs-page .gki-nav-item a:link { color: #414141 !important }` |
| Kit dark body background | `body.gki-docs-page { background: #FFFFFF !important }` |
| Elementor Theme Builder template | Plugin hooks at priority 9999 + `template_id` filter returns 0 |

### CSS Custom Properties (`:root`)

| Token | Value | Usage |
|---|---|---|
| `--gki-purple` | `#7900C9` | Accent only |
| `--gki-text` | `#1C1C1C` | Headings |
| `--gki-text-secondary` | `#414141` | Body text |
| `--gki-text-muted` | `#636568` | Captions |
| `--gki-bg` | `#FFFFFF` | Page background |
| `--gki-bg-subtle` | `#F5F6F8` | Card/hover backgrounds |
| `--gki-border-light` | `#E0E1E3` | Borders |
| `--gki-font` | `Inter` + system stack | All text |
