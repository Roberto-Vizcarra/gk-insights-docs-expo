# CLAUDE.md

## Project Overview

This repo contains the **GitKraken Insights AI Adoption Help Center** — ~50 Markdown content pages and a custom WordPress plugin (GKI Docs Helper) that provides the template, CSS, JS, and Elementor overrides for the Help Center at `help.gitkraken.com/insights-expo/`.

## Current State (September 16, 2026)

Phases 1–6 are complete. The active work is the **Pass C redesign** on branch
`pass-c-structural` (plugin **v1.17.1**), which turns metric pages into
reference objects and carries one component language through every page.
`main` holds the last stable pre-redesign build (**v1.14.2**, tag
`v1.14.2-stable`, archived in `plugin-archive/`). Pass C has been verified on
the local import only; it has not been deployed.

### Branches
| Branch | Plugin | What it is |
|---|---|---|
| `main` | 1.14.2 | Stable build. Rollback point. |
| `Taste-design-expo` | 1.15.x | Pass A/B exploration with the three-way design switch. Superseded; kept for reference. |
| `pass-c-structural` | 1.17.1 | Pass A+B folded into the baseline, Pass C on top, plus the C.2 page-body pass and gate fixes. **Current.** |

### What the plugin does today (pass-c-structural)
- ~50 Markdown files in `gk-insights-expo/` synced to WordPress via Git It Write
- 3-column layout: collapsible left nav (nested section → sub-index → page), center content, right TOC
- Command palette (Ctrl/Cmd-K, `/`) over the server-built search index; legacy search inputs kept hidden
- Card grid index pages with tinted icons (auto-rendered from frontmatter); Intro → Cards → Overview, split at the first `<hr>`
- Home: hero, role-based entry paths, metric map — all from frontmatter
- **Metric pages are restructured server-side** (`includes/gki-passc.php`): definition lede, spec panel, canonical section order, formula panel + sandbox inside the Formula section, numbered step rail, tier scale, FAQ pairs, path-token "Where it appears" at the foot, hero screenshot under At a glance. No content files were changed to achieve this.
- **General pages get the same components** via a second filter: typed callouts from blockquotes, playbook step headings on the rail with time badges, settings mini spec panels, labelled code panels, checklists, run-in labels, duplicate-H2 removal.
- Interactive instruments on 11 metric pages (sandboxes, proportion bar, timelines, precedence table)
- Light/dark theme toggle, image lightbox, progress bar, back-to-top, entry reveal
- Auth gate (built-in OAuth2 against gitkraken.dev + Insights entitlement), off by default

### Plugin Location
```
gki-docs-helper/
  gki-docs-helper.php       # Main plugin: hooks, enqueue, template override, nav/cards/breadcrumbs (v1.17.1)
  includes/gki-auth.php     # Auth gate: OAuth2 login endpoint + callback, entitlement check, settings page
  includes/gki-passc.php    # Pass C: metric-page pipeline + general-page components (the_content filters, priority 16/17)
  css/gki-docs.css          # Baseline: tokens, layout, Elementor overrides, Pass A craft + Pass B ambition rules (CRLF file)
  css/gki-passc.css         # Pass C: spec/formula/instruments/palette/Home + all C.2 components + gate fixes (LF file)
  js/gki-docs.js            # TOC, nav collapse, theme toggle, lightbox, progress bar, legacy search
  js/gki-passc.js           # Command palette, instruments, entry reveal
  templates/single-gki.php  # 3-column page template
  templates/gki-gate.php    # Auth gate page (login / no-access variants)
  gki-docs-helper.zip       # Installable archive — built ONLY by build-plugin.py

build-plugin.py             # The only sanctioned zip builder (see Plugin Workflow)
archive-and-build.ps1       # Archives the current zip by version, then runs build-plugin.py
plugin-archive/             # Rollback zips, one per version, plus ARCHIVE.md ledger
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
- **Purple (#7900C9) is accent only** — links, active borders, step numbers, hover effects. Never a main/heading color. Tier colours (`--gki-tier-*`) are semantic, not accent, and only appear where the content names a product tier.
- Heading text: #1C1C1C. Body text: #414141. Formulas, figures, paths and step numbers use JetBrains Mono (`--gki-font-data`); Inter stays the brand face.
- Cards drive navigation on index pages. Index pages: Intro → Cards → Overview.
- All Elementor overrides require `body.gki-docs-page` prefix + `!important`. **This includes anything rendered outside `.gki-page`** — the auth gate's H1 and links were being painted near-white and hot pink by the kit until 1.17.1 because the overrides were scoped to `.gki-page` only.
- **One step style, site-wide.** Outlined circle, accent number, hairline rail joining the circles (`.gki-seq` and every plain Markdown `<ol>`). The filled-purple disc with dashed dividers was retired in 1.17.0; do not reintroduce it.
- **Structure is applied in PHP, not in Markdown.** Section reordering, callout typing, step rails, spec panels and hero figures are all `the_content` transforms in `includes/gki-passc.php`. If a page needs a component, teach the transform to recognise the existing pattern; do not add HTML to content files.
- Every metric page has the same 12 H3s. The canonical order is in `gki_passc_section_order()`; unknown headings keep their position relative to the known section before them, so nothing is ever dropped.
- Screenshots: on metric pages the one figure is the hero (under At a glance). On other content pages a figure before the first heading is the hero unless it follows a step list. Figures that illustrate steps stay with their steps.

### CSS Traps (measured, do not re-introduce)
- **`.gki-related` is a Pass B entry-reveal target.** Anything given that class fades in on scroll. Tables use `.gki-related-table`.
- **`gki-docs.css` is CRLF; `gki-passc.css` is LF.** Editing the CRLF file with a tool that normalises line endings produces a 5,600-line phantom diff. Check `git diff --stat` before committing; if the whole file shows as changed, the line endings were flipped.
- **`hr` elements are hidden, not removed.** `hr:has(+ h2)` hides the redundant `---` rules; the elements stay in the DOM because `single-gki.php` splits index-page content on the first `<hr>` to place the card grid.
- **PowerShell scripts in this repo are plain ASCII.** Windows PowerShell 5.1 reads a BOM-less `.ps1` as ANSI; an em dash in a string mangles into a quote and the whole file fails to parse.

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
- **Before building, archive.** `.\archive-and-build.ps1` copies the current zip to `plugin-archive/gki-docs-helper-v<version>.zip` (version read from the PHP header *inside* the zip), appends a row to `plugin-archive/ARCHIVE.md`, then runs `build-plugin.py`. `-FromRef main` archives a committed build instead. Roll back by uploading an archived zip through WP Admin → Plugins → Add New → Upload (replace).
- Upload zip to WP Admin → Plugins after each version bump.
- The sandbox shell used by AI sessions cannot reach `localhost:8420` (network allowlist) and can wedge mid-session; the build then has to be run from a real PowerShell. Verify the version inside the zip afterwards.

### Auth Gate (v1.12.0+, fixes in v1.17.1)
- All `/insights-expo/` pages are gated behind GitKraken OAuth2 + Insights subscription entitlement, enforced on `template_redirect` at priority 5.
- **The OAuth flow is built into the plugin** (`includes/gki-auth.php`). The OpenID Connect Generic Client plugin was replaced in v1.12.0 and is no longer required or installed. Login endpoint `/insights-expo/login/` redirects to `https://gitkraken.dev/login`; callback is `admin-ajax.php?action=gki-oauth-callback`; token exchange is `POST api.gitkraken.dev/oauth/access_token`; userinfo is `GET api.gitkraken.dev/user`. The callback URL must be registered as the client's `redirect_uri` — only production's is.
- Off by default. Options: `gki_auth_enabled`, `gki_auth_client_id`, `gki_auth_licensing_endpoint`, `gki_auth_cache_ttl` (hours, default 24), `gki_auth_upgrade_url`. Settings → GKI Auth Gate in WP Admin, with a debug log.
- WP admins (`manage_options`) always bypass the gate. Test as a subscriber.
- Entitlement: `GET /user/organizations` with the user's Bearer token; any org with `totalInsightsLicenses > 0` grants access. Org-level, not per-user. Cached in user meta (`gki_insights_access`, `gki_insights_checked_at`). No configured endpoint fails **closed**.
- Gate template (`templates/gki-gate.php`) has two variants: `login` (401) and `no-access` (403). It reuses `<main class="gki-layout">`, so `.gki-layout > .gki-gate` must span all grid tracks (fixed 1.17.1 — the card had been rendering 176px wide inside the nav column since the gate shipped).
- **The gate page enqueues stylesheets only.** `gki_auth_show_gate()` defines `GKI_GATE_ACTIVE`; `gki_docs_enqueue_assets()` returns before the scripts when it is set. Before 1.17.1 the gate shipped `gkiSearchData` (title, URL and excerpt of every gated page) and a working Ctrl-K palette to signed-out visitors.
- Local testing: the real sign-in cannot complete against localhost (unregistered redirect URI), and the team has chosen not to register one. The three gate states can be driven from the database instead — recipe in `LOCAL-DEV.md`. `setup.ps1` forces `gki_auth_enabled` off (it set the wrong option name until 1.17.1).
- See `AUTH-GATE-PLAN.md` for the original design and the WordPress hardening checklist (noindex, sitemap, page cache, feeds).

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
- `main-index` — the home page; Pass C hero + role paths + metric map, then remaining content
- `index` — section or sub-section landing; card grid of child pages rendered between intro and overview content
- `content` — regular article with TOC sidebar. Metric pages (`nav_category: metrics`) go through the metric pipeline; everything else through the general-page pipeline.

### Content Transforms (includes/gki-passc.php)

Both run on `the_content`, after `gki_docs_clean_parsedown` (15).

**Metric pages** — `gki_passc_restructure` (16). Input is the H3 skeleton every metric page shares. Output order: definition lede → spec panel (Range, Family, Cadence) → At a glance (+ hero figure) → Formula (formula panel, legend, instrument) → How it's calculated → Why it matters → How to read it → Settings → How to improve/use → Limitations → FAQ → Related metrics → Where it appears (path tokens + descriptions). Patterns recognised: `**Step N — Title.**` / `**Step N.**` paragraphs → `.gki-seq`; other `**Label.**` paragraphs → `.gki-runin`; 3–6 row bold-first-cell table under How to read it → `.gki-tiers`; `**Q:**<br>A:` → `.gki-faq`; `<li><strong>/path</strong> — desc` → `.gki-surfaces`; first fenced code block → `.gki-formula`.

**All other pages** — `gki_passc_restructure_general` (17). Duplicate leading H2 dropped (matches post title or `nav_label`); italic-only lede lifted; blockquotes → `.gki-callout--warn|info|tip|note` by lead phrase (depth-counted, italic inner quote kept as `.gki-quote`); `### Step N — Title (3 min)` / `### Month N —` headings → `.gki-seq--headed` with time badge or kicker; `### Pattern:` / `### If …` → `.gki-branch`; settings `Default/Range/Type` tables + `In Settings UI:` → `.gki-spec--mini`; `→ Full section:` → `.gki-fullsection`; fenced code → `.gki-code`; tables whose first cells are all links → `.gki-related-table`; `[ ]`/`[x]` bullets → `.gki-checklist`; first figure before any heading → `.gki-hero-figure` unless preceded by an `<ol>`.

Instruments: `gki_passc_instruments()` maps slug tails to instrument kinds; `js/gki-passc.js` renders them into `.gki-instrument[data-instrument]`. A failing instrument removes itself.

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
| `--gki-font-data` | `JetBrains Mono` + mono stack | Formulas, figures, paths, step numbers (gki-passc.css) |
| `--gki-tier-power/regular/explorer/emerging` | teal / indigo / amber / rose | Tier bands only, never accent (gki-passc.css; dark variants under `[data-theme="dark"]`) |
| `--gki-fs-*`, `--gki-lh-*` | type scale | Pass A nine-step scale; use these, not raw rem |

Dark mode is a second set of token values under `[data-theme="dark"]`, toggled by the slider in the left nav.
