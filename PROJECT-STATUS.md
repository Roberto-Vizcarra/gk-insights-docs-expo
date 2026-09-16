# GKI Help Center Redesign — Project Status

**Last updated:** 2026-09-16
**Owner:** roberto.vizcarra@gitkraken.com
**Plugin version:** 1.17.1 on `pass-c-structural` (current work) · 1.14.2 on `main` (stable, tag `v1.14.2-stable`)
**Live URL:** `help.gitkraken.com/insights-expo/expo-ai-adoption-home`
**Local URL:** `localhost:8420/insights-expo/expo-ai-adoption-home/` — see `LOCAL-DEV.md`

> Pass C (1.16.0 → 1.17.1) is verified on the local import and **not yet deployed**.
> Deploying it means: merge `pass-c-structural` to `main`, push (Git It Write
> resyncs one content file), run `.\archive-and-build.ps1`, upload the zip.

## Architecture

```
WordPress (help.gitkraken.com)
  ├── Elementor Pro Theme Builder (dark kit — fights us)
  ├── Git It Write plugin (syncs repo Markdown → WP posts)
  │     └── Category: insights-expo → gk-insights-expo/ directory
  │     └── YAML custom_fields: → stored as WP post meta
  └── GKI Docs Helper plugin v1.17.1 (our custom plugin)
        ├── gki-docs-helper.php
        │     ├── template override (priority 9999) + Elementor template_id → 0
        │     ├── asset enqueue (stylesheets only when GKI_GATE_ACTIVE)
        │     ├── search index → gkiSearchData (nav_order sorted)
        │     ├── Parsedown cleanup (the_content, 15)
        │     └── nav / cards / breadcrumbs helpers
        ├── includes/gki-auth.php
        │     ├── auth gate (template_redirect, 5) — admins bypass
        │     ├── /insights-expo/login/ → gitkraken.dev OAuth2
        │     ├── admin-ajax gki-oauth-callback → token → userinfo → WP session
        │     ├── entitlement: GET /user/organizations, cached in user meta
        │     └── Settings → GKI Auth Gate page + debug log
        ├── includes/gki-passc.php
        │     ├── metric-page pipeline (the_content, 16): split on H3, reorder,
        │     │   spec / formula / steps / tiers / FAQ / surfaces / hero figure
        │     ├── general-page pipeline (the_content, 17): callouts, headed steps,
        │     │   settings specs, code panels, checklists, run-ins, hero figure
        │     └── Home data: metric map + role paths
        ├── templates/single-gki.php — 3-column layout, Home landing, index split at <hr>
        ├── templates/gki-gate.php — login / no-access
        ├── css/gki-docs.css  — tokens, layout, Elementor overrides, Pass A + B baseline
        ├── css/gki-passc.css — Pass C components, C.2 page-body components, gate fixes
        ├── js/gki-docs.js    — TOC, nav collapse, theme toggle, lightbox, progress, legacy search
        └── js/gki-passc.js   — command palette, 11 instruments, entry reveal
```

## Phase History

### Phase 1 — Audit (Complete)
Audited the original monolithic Dashboard Management page. Identified 21 metrics across 5 families, plus config/settings content. Recommended breakout into standalone pages.

### Phase 2 — Plugin & Visual Design (Complete, v1.5.0)
Built GKI Docs Helper plugin: 3-column template, CSS design system (Inter, Major Third scale, brand tokens), Elementor overrides, card grid design, hierarchical nav, TOC, lightbox, progress bar. Exploration v3 defined the visual language: tinted icon cards, 3-wide grid, purple as accent only.

### Phase 3 — Content Breakout (Complete, v1.7.0)
Split content into 29 standalone pages. Key fixes along the way:
- Moved frontmatter plugin fields under `custom_fields:` (Git It Write requirement)
- Rewrote all internal links from `/gk-insights/` to `/insights-expo/`
- Created clean landing page (`expo-ai-adoption-home.md`) separate from Getting Started
- Converted old hub page to hidden redirect
- Removed static HTML tables from index pages (card grids auto-render)
- Fixed heading color CSS specificity (added `gki-page` class to template article)
- Fixed card filter (removed `display: flex !important` that blocked JS hide)
- Added table styling for Markdown tables
- Template splits main-index content at first `<hr>` to insert cards after intro
- Added `gki_docs_render_card_grid()` helper function

### Phase 4 — UI/UX Refinement (Complete, v1.8.x)
See `PHASE-4-HANDOFF.md` for the original punch list.

### Phase 5 — Auth Gate (Complete, v1.9.0 → v1.12.0; hardened v1.17.1)
Authentication gate requiring GitKraken OAuth2 + Insights subscription entitlement. Started (v1.9.0) on the OpenID Connect Generic Client plugin; **v1.12.0 replaced it with a built-in OAuth2 handler** in `includes/gki-auth.php` (custom `/insights-expo/login/` endpoint, `admin-ajax` callback, token exchange, userinfo, WP user create/link), after a run of redirect-URI and Cloudflare-caching fixes (v1.10.x–1.11.x). Gate page redesigned with GK branding in v1.10.7. Entitlement via `GET /user/organizations`, cached 24 h in user meta, fails closed. v1.17.1 fixed three long-standing defects found while rendering the gate on the local import: the card was laid into the 240px nav track of the `.gki-layout` grid (176px wide), the kit painted the title near-white and links pink, and the gate page shipped the full search index plus a live Ctrl-K palette to signed-out visitors.

### Phase 6 — Local Test Environment (Complete, 2026-09-14)
See below.

### Phase 7 — Taste Passes A/B (Complete on `Taste-design-expo`, v1.15.x)
Two CSS overlays behind a three-way switch in the nav: **Pass A (craft)** — nine-step type scale, measure, tabular figures, focus and pressed states, reduced motion, tinted shadows, mobile grid fix; **Pass B (ambition)** — card hierarchy and hover physics, nav/TOC active rails, section-title hairlines, lighter callouts, entry reveal. Both were accepted and folded into `gki-docs.css` as the baseline when Pass C branched; the switcher and `taste-*.css` files were removed.

### Phase 8 — Pass C: structural redesign (Current, `pass-c-structural`, v1.16.0 → v1.17.1)
Mockup: Artifact "Insights Docs Pass C" (`prototypes/pass-c-mockup.html`).

- **v1.16.0** — metric pages become reference objects: definition lede, spec panel, formula panel, per-metric instrument (7 sandboxes, proportion bar, 2 timelines, precedence table); command palette replaces the search input; Home landing with hero, role paths and metric map. JetBrains Mono for data. All from existing content and frontmatter.
- **v1.17.0 (Pass C.2)** — the design carried through the whole page. Metric pages: split on H3, canonical section order, Formula section owns panel + legend + sandbox, "Where it appears" moves to the foot as path-token rows, screenshot becomes the hero under At a glance, step rail, run-in labels, tier scale, FAQ pairs. Every other page: typed callouts, playbook step headings on the rail with time badges, settings mini spec panels, code panels, checklists, duplicate-H2 removal, hero figures. One step style site-wide (the filled-purple disc retired). Two figures on `connect-ai-tools.md` moved next to the steps they illustrate — the only content-file change in the pass.
- **v1.17.1** — gate fixes above; `.gki-related` → `.gki-related-table` (reveal-target collision); `setup.ps1` option-name fix; `archive-and-build.ps1` and `plugin-archive/` seeded with the v1.14.2 build from `main`.

Verified: 49 pages fetched with no PHP errors, light and dark, 1280 and 375, TOC intact, nav collapse unaffected, no horizontal overflow. Remaining console 404s are missing uploads in the local import, not plugin errors.

## Content Map (50 files)

Nav labels come from `custom_fields.nav_label`; the table lists type and category, which are what the plugin branches on.

| File | Page Type | Nav Category |
|---|---|---|
| expo-ai-adoption-home.md | main-index | home |
| expo-ai-adoption-getting-started.md | index | getting-started |
| expo-ai-adoption-first-dashboard.md | content | getting-started |
| expo-ai-adoption-for-executives.md | content | getting-started |
| expo-ai-adoption-for-engineering-leaders.md | content | getting-started |
| expo-ai-adoption-for-team-leads.md | content | getting-started |
| expo-ai-adoption-for-admins.md | content | getting-started |
| expo-ai-adoption-connect-your-data.md | index | connect-your-data |
| expo-ai-adoption-connect-github.md | content | connect-your-data |
| expo-ai-adoption-connect-bitbucket.md | content | connect-your-data |
| expo-ai-adoption-connect-azure-devops.md | content | connect-your-data |
| expo-ai-adoption-connect-gitlab.md | content | connect-your-data |
| expo-ai-adoption-connect-ai-tools.md | content | connect-your-data |
| expo-ai-adoption-connect-jira-bamboohr.md | content | connect-your-data |
| expo-ai-adoption-metrics.md | index | metrics |
| expo-ai-adoption-agentic-metrics.md | index (family) | metrics |
| expo-ai-adoption-agentic-adoption-score.md | content (metric) | metrics |
| expo-ai-adoption-agentic-autonomy-score.md | content (metric) | metrics |
| expo-ai-adoption-agentic-ai-tier.md | content (metric) | metrics |
| expo-ai-adoption-agentic-maturity-factor.md | content (metric) | metrics |
| expo-ai-adoption-agentic-cursor-boost.md | content (metric) | metrics |
| expo-ai-adoption-output-metrics.md | index (family) | metrics |
| expo-ai-adoption-output-output-score.md | content (metric) | metrics |
| expo-ai-adoption-output-throughput.md | content (metric) | metrics |
| expo-ai-adoption-output-direct-commits.md | content (metric) | metrics |
| expo-ai-adoption-output-effort-score.md | content (metric) | metrics |
| expo-ai-adoption-flow-metrics.md | index (family) | metrics |
| expo-ai-adoption-flow-cycle-time.md | content (metric) | metrics |
| expo-ai-adoption-flow-review-cycles.md | content (metric) | metrics |
| expo-ai-adoption-flow-first-pass-rate.md | content (metric) | metrics |
| expo-ai-adoption-flow-wip.md | content (metric) | metrics |
| expo-ai-adoption-dora-metrics.md | index (family) | metrics |
| expo-ai-adoption-dora-deployment-frequency.md | content (metric) | metrics |
| expo-ai-adoption-dora-lead-time.md | content (metric) | metrics |
| expo-ai-adoption-dora-change-failure-rate.md | content (metric) | metrics |
| expo-ai-adoption-dora-mttr.md | content (metric) | metrics |
| expo-ai-adoption-impact-cost-metrics.md | index (family) | metrics |
| expo-ai-adoption-impact-cost-productivity-uplift.md | content (metric) | metrics |
| expo-ai-adoption-impact-cost-ai-assisted-percentage.md | content (metric) | metrics |
| expo-ai-adoption-impact-cost-capex-opex.md | content (metric) | metrics |
| expo-ai-adoption-impact-cost-spend-by-tier.md | content (metric) | metrics |
| expo-ai-adoption-playbooks.md | index | playbooks |
| expo-ai-adoption-playbook-tier-weights.md | content | playbooks |
| expo-ai-adoption-playbook-ai-rollout.md | content | playbooks |
| expo-ai-adoption-playbook-slow-cycle-time.md | content | playbooks |
| expo-ai-adoption-playbook-high-cfr.md | content | playbooks |
| expo-ai-adoption-admin.md | index | admin |
| expo-ai-adoption-settings.md | content | admin |
| expo-ai-adoption-manual-releases-api.md | content | admin |
| expo-ai-adoption.md | content | hidden (redirect) |

All 21 metric pages share the same 12 H3 sections and the same opening block (`## Title`, italic definition blockquote, `**Family:** · **Cadence:** · **Where it appears:**` line, `### Formula` with one fenced block). The metric pipeline depends on this; a new metric page should copy an existing one.

Metric pages with a screenshot (8): adoption-score, ai-tier, autonomy-score, cursor-boost, maturity-factor, cycle-time, wip, output-score. Metric pages with an instrument (11): see `gki_passc_instruments()`.

### Phase 6 — Local Test Environment (detail)
Built a Dockerized WordPress in `local-dev/` running a full production import,
ending the practice of testing plugin changes against the live site. One command
(`setup.ps1`) takes a WP Migrate export to a verified running site: import,
charset normalization, URL rewrite, file staging, cleanup, and post-install
verification. `gki-docs-helper/` is bind-mounted, so iteration needs no zip
rebuild. See `LOCAL-DEV.md`.

Confirmed by the import, and previously unrecorded:

| Fact | Value |
|---|---|
| Elementor active kit ID | `5` — `.elementor-kit-5` selectors are correct |
| Permalink structure | `/%category%/%postname%/` |
| Docs post type | `post`, category `insights-expo` (not a CPT) |
| Posts collation | `utf8mb4_unicode_520_ci` |
| Published posts (whole help center) | 3,622; 50 are insights-expo |
| Active theme | `hello-elementor` + `hello-elementor-child` |
| Active plugins | 27 |

## Known Issues

1. **Parsedown v1** doesn't parse Markdown inside HTML blocks — fundamental limitation.
2. **Elementor specificity is fragile** — theme updates can break overrides. After any theme update, verify heading colors, link colors, body background.
3. **No auto-update** — plugin zip must be manually uploaded after each version.
4. **Git It Write doesn't delete posts** — when source files are removed, WP posts persist and must be manually deleted.
5. **Tabler Icons CDN** — icons load from `cdn.jsdelivr.net`. If CDN is blocked by WAF/CSP, card icons will be invisible.
6. **Two search systems over the same content** — SearchWP is active site-wide and
   indexes `/insights-expo/` pages, alongside the plugin's own PHP-generated JSON
   search index. Discovered during the 2026-09-14 local import. Not yet
   investigated: whether they conflict, and which one users actually hit.
7. **The real OAuth sign-in cannot complete locally** — the localhost callback is
   not a registered `redirect_uri`, and the team has chosen not to register one.
   The gate *template* and all three gate states (401 / 403 / entitled) can be
   driven from the database; recipe in `LOCAL-DEV.md`. End-to-end sign-in is
   only testable on production or a staging host with a registered callback.
8. **The metric pipeline is pattern-based.** It recognises the H3 skeleton and
   the `**Label.**` / `**Step N**` / `**Q:**` conventions the 21 metric pages
   share. A page written differently renders correctly but plainly — the
   transforms are forgiving, not enforcing. Check a new page on the local site.
9. **Callout typing is keyword-based.** Blockquotes become warn / info / tip /
   note by matching the bold lead phrase. A blockquote whose bold text is not at
   the start falls back to a neutral note (one known case on Connect GitHub).
10. **Local import is missing some uploads** — `ai-adoption-developers.png`,
    `ai-adoption-executive-view.png`, `ai-adoption-settings-general.png` and a
    few others 404 locally. Figures render as alt text there. Re-export from
    prod with media, or copy the files into `local-dev/uploads/`.
11. **AI-session sandbox limits.** The sandbox shell cannot reach
    `localhost:8420` (network allowlist), and Chrome must be connected for the
    Chrome tools; the built-in browser pane mis-renders screenshots after
    scrolling under viewport emulation (shift the page with a transform instead).
    The shell can also wedge mid-session, at which point builds run from a real
    PowerShell.

## Open Items for Pass C

- Deploy: merge `pass-c-structural` → `main`, push, archive + build, upload 1.17.1, enable nothing new (the gate setting is untouched).
- Confirm the gate visually on production after upload — it's the one surface the local site can't fully exercise.
- The Pass C mockup's sticky side-rail sandbox was rejected in favour of inline-under-formula; the mockup still shows the rail.
- `PHASE-4-HANDOFF.md` is historical; its punch list is closed.

## Long-term Considerations

- Auto-update mechanism (webhook or GitHub Actions → WP REST API)
- Replace Git It Write + Parsedown with a better Markdown pipeline
- Consider dedicated docs platform if WP/Elementor friction continues
- **A real staging instance.** The local Docker environment covers plugin
  rendering, but not the OAuth gate, the CDN, or production caching. If the
  host (WP Engine / Kinsta / Pantheon) offers one-click staging, enabling it
  closes the remaining gap.
