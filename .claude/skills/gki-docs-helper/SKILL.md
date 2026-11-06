---
name: "gki-docs-helper"
description: "Continue work on the GKI Docs Helper WordPress plugin — the template, CSS, JS, PHP content transforms, auth gate and Elementor overrides for GitKraken Insights Help Center pages. Use when the user mentions the GKI plugin, Help Center redesign, Pass C, insights-expo pages, metric page layout, the auth gate, or the gk-insights-docs-expo repo."
---

# GKI Docs Helper — Project Continuation Skill

You are picking up an ongoing project: a custom WordPress plugin called **GKI Docs Helper** that renders the GitKraken Insights Help Center at `help.gitkraken.com/insights-expo/`. The repo is `gk-insights-docs-expo` (ask for the folder if it is not connected; it lives under the user's `Documents\Git`).

## Before starting work

1. Read `CLAUDE.md` — standing rules, design rules, content transforms, measured traps. It is the source of truth; if this skill and `CLAUDE.md` disagree, `CLAUDE.md` wins.
2. Read `PROJECT-STATUS.md` — phase history, content map, known issues, open items.
3. Read the plan that matches the task: `AUTH-GATE-PLAN.md` (gate design + WordPress hardening checklist), `MERGE-PLAN.md` (the eventual fold-back into `/gk-insights/` — do not start that merge without reading it), `BREAKOUT-PLAN.md`, `LOCAL-DEV.md` (Docker runbook, gate-state recipes, lockdown verification).
4. Check the plugin version in `gki-docs-helper/gki-docs-helper.php` (header AND `GKI_DOCS_VERSION` must agree) and compare with `plugin-archive/ARCHIVE.md` to see what has shipped.
5. Check the branch and `git status`. The repo often carries uncommitted content edits from docs audits; do not sweep them into a plugin commit.

## Shape of the system (November 2026)

- `main` is current: Pass C is merged (PR #2) and the plugin is at 1.17.x. `pass-c-structural` and `Taste-design-expo` are kept for history only. `v1.14.2-stable` is the pre-redesign rollback tag; every shipped zip is in `plugin-archive/`.
- ~60 Markdown files in `gk-insights-expo/` sync to WP posts via Git It Write (Parsedown v1 — no Markdown inside HTML blocks). Custom fields must be nested under `custom_fields:` in frontmatter. Posts are `post` type in category `insights-expo`, slug from filename, links use `/insights-expo/expo-ai-adoption-*`. Git It Write never deletes posts — remove stale ones in WP Admin.
- The plugin overrides the template for `insights-expo` posts and fights the Elementor dark kit (`.elementor-kit-5`) with `body.gki-docs-page … !important` rules. That prefix is required for anything rendered outside `.gki-page` too — the gate page was painted by the kit until 1.17.1.
- **Structure is applied in PHP, not Markdown.** `includes/gki-passc.php` has two `the_content` filters after the Parsedown cleanup (15): the metric-page pipeline (16: split on H3, reorder to `gki_passc_section_order()`, definition lede, spec panel, formula panel + sandbox, step rail, tier scale, FAQ, path-token "Where it appears", hero figure) and the general-page pipeline (17: typed callouts from blockquotes, playbook step headings on the rail, branch headings, settings mini spec panels, code panels, related tables, checklists, run-in labels, duplicate-H2 removal, hero figure). To change how a page looks, teach the transform to recognise the existing content pattern. Do not add HTML to content files.
- Layout: collapsible left nav (3 levels via `nav_parent`), centre content, right TOC. Command palette (Ctrl/Cmd-K, `/`) over the server-built search index; Home and every index page carry a landing search field that opens the palette seeded with the text and degrades to a real `?s=` form without JS. Index pages: Intro → Cards → Overview, split at the first `<hr>` (so `hr` is hidden, never removed). Home: hero, role paths, metric map from frontmatter. Instruments on 11 metric pages via `data-instrument`; a failing instrument removes itself.
- CSS: `css/gki-docs.css` (baseline, **CRLF**) and `css/gki-passc.css` (Pass C components, **LF**). JS: `js/gki-docs.js` (TOC, nav, theme, lightbox, progress) and `js/gki-passc.js` (palette, landing search, instruments, reveal).
- **Auth gate is live on production** (since 2026-09-23), in `includes/gki-auth.php`: built-in OAuth2 against gitkraken.dev (login `/insights-expo/login/`, callback `admin-ajax.php?action=gki-oauth-callback`), entitlement = any org with `totalInsightsLicenses > 0`, cached in user meta, fails closed without an endpoint. WP admins bypass — test as a subscriber. 1.17.3 added the **lockdown** (REST, oEmbed, feeds, search, archives, sitemaps, robots/noindex, `nocache_headers`) keyed on `gki_auth_should_hide()`; any new route that can render a post must be added there. 1.17.5–1.17.7 added trial codes, the learn-more / book-a-demo no-access screen, per-email-domain trial extensions with tracking and revoke, and trial-to-paid conversion handling. The gate page enqueues stylesheets only (`GKI_GATE_ACTIVE`). The real sign-in cannot complete on localhost (unregistered redirect URI, by choice); drive the three gate states from the database — recipe in `LOCAL-DEV.md`.
- Nothing published so far is confidential and the content repos are public by choice; they must go private before the first restricted page is committed.

## Non-negotiables

- Purple `#7900C9` is accent only. Headings `#1C1C1C`, body `#414141`. Tier colours are semantic, only where content names a tier; "How to read it" scales follow the colour rules in `CLAUDE.md`. Formulas, figures, paths and step numbers use JetBrains Mono (`--gki-font-data`); Inter is the brand face. Use the `--gki-fs-*` / `--gki-lh-*` scale, not raw rem.
- One step style site-wide: outlined circle, accent number, hairline rail. Never the filled purple disc.
- Content integrity: move, don't rewrite; don't invent product behaviour; never add, remove or rename images; preserve alt text and paths. Flag unclear behaviour for human review.
- Every metric page keeps the same 12 H3s; unknown headings keep their position relative to the known section before them so nothing is dropped.
- `.gki-related` is a reveal-animation target — tables use `.gki-related-table`.
- Never put `grid-template-columns` in a `transition` on `.gki-layout`; never give `.gki-sidebar--left` a fixed `height` (use `max-height`). Both measured; see `CLAUDE.md`.
- Keep `gki-docs.css` CRLF. Check `git diff --stat` before committing; a whole-file diff means line endings flipped.
- PowerShell scripts in the repo are plain ASCII (PS 5.1 parses BOM-less files as ANSI; an em dash breaks the parse).

## Workflow

- **Fast preview** (no Docker): `python preview/build.py` renders every page through the plugin's own PHP into `preview/out/` and opens Home; `--serve` serves the repo root on `localhost:8765`; `--strict` fails on PHP warnings. Use it for transform and CSS iteration on any branch.
- **Full-fidelity check**: the Docker site, `cd local-dev; docker compose up -d` → `http://localhost:8420` (real theme, Elementor kit, database, gate). The plugin dir is bind-mounted; edit and refresh. With `WP_DEBUG` on, the enqueue appends the asset mtime so edits bust the cache; if the old build still shows, hard-refresh. Read `wp-content/debug.log` before building any zip. Both historical outages came from testing in prod — never skip this.
- The AI sandbox shell cannot reach `localhost:8420`. Use the browser tools against it; fetch pages with `fetch()` in page context and parse with `DOMParser` to sweep all pages for PHP errors and component counts. Under viewport emulation the built-in browser mis-renders after scrolling — shift the page with `document.body.style.transform` instead of scrolling before screenshots.
- **Ship**: bump the version in both places → `.\archive-and-build.ps1` (archives the current zip to `plugin-archive/` with an `ARCHIVE.md` row, then runs `build-plugin.py`, which forces `/` separators and a single `gki-docs-helper/` root and refuses to write if any check fails) → commit (zip is tracked) → upload through WP Admin → Plugins. **Never** build the zip any other way; hand-rolled zips took the site down twice. Roll back by uploading an archived zip. Every upload bumps the asset version, so prod never serves stale CSS/JS.
- If the sandbox shell wedges, run the build in the user's PowerShell and verify the version inside the zip; commits can go through the GitKraken MCP tools. A stale `.git/index.lock` may need deleting first (the sandbox cannot remove it).

## Verification pass, every time

Light and dark; 1280 and 375 widths; no horizontal overflow; TOC still lists every H2/H3; no PHP warnings in any page (`preview/build.py --strict`, then the Docker site); nav collapse still resolves; `wp-content/debug.log` clean; all three gate states (signed out, signed in without entitlement, entitled) still render inside `.gki-layout` spanning all tracks; after gate changes, re-run the lockdown recipe in `LOCAL-DEV.md`. Remaining 404s for `/wp-content/uploads/*.png` on the local site are missing uploads in the import, not plugin errors.
