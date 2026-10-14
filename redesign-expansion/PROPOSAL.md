# Proposal: Extend the Help Center Redesign to Every GitKraken Help Center

> **Status:** Deferred. Approved in principle on 2026-10-14; not scheduled.
> **Start condition:** the Insights redesign (plugin v1.17.x, Pass C) is live on
> `help.gitkraken.com/insights-expo/` and stable.
> **Owner:** Roberto Vizcarra
> **Supporting docs:** see [README.md](README.md) in this folder.

## Decision

Extend **GKI Docs Helper** to the other help centers as **one plugin with a
per-product registry**. Do not fork a second plugin, and do not simply add slugs
to `GKI_DOCS_CATEGORY`.

Why:

- The engine is already generic. Layout, generated nav, cards, breadcrumbs,
  command palette, theme switcher, callouts, step rails, code panels and the base
  CSS work for any category that adopts the frontmatter (roughly 70% of the code).
- Adding slugs to the constant breaks things. Every product would be titled
  "GitKraken Insights", the navs and search indexes would merge across products,
  the Insights section order and home would apply everywhere, and the auth gate
  would lock every help center.
- A fork means two copies of the design system drifting apart. Both copies would
  also hook the same `template_include` and `the_content` filters.

## Scope

**In scope**

1. A plugin refactor into a shared core plus a product registry, with Insights
   as the only registered product at first. See
   [PLUGIN-REFACTOR-SPEC.md](PLUGIN-REFACTOR-SPEC.md).
2. Plugin adapters for the markup the other repos already use:
   - `callout--*`, `bbb-callout` and `div.note` callouts
   - inline copy-button scripts
   - H3-first headings
3. Per product:
   - a section map
   - a frontmatter backfill script
   - a registry entry
   - verification on the local Docker import
4. A treatment for each help center, following the compatibility matrix below.

**Out of scope**

- Rewriting content. Structure stays a PHP render-time concern, the same rule
  Insights follows.
- The auth gate for anything but Insights.
- Moving any help center off WordPress or Git It Write.

## Treatment per help center

Every product gets the shared design language:

- theme switcher
- tokens and branding
- command-palette search
- generated left nav
- right TOC
- breadcrumbs
- typed callouts
- step rails
- code panels

The treatments differ in how much card hierarchy sits on top.

| Help center | Category slug | Pages | Treatment |
| --- | --- | --- | --- |
| Kepler | `kepler` | 18 | Full card hierarchy |
| GitKraken MCP | `MCP` (`/mcp/`) | 5 | Full card hierarchy (catalog; tool pages generated from `tools.json`) |
| Git Integration for Jira Cloud | `git-integration-for-jira-cloud` | 145 | Full card hierarchy |
| GitKraken Desktop | `gitkraken-desktop` | 90 | Hybrid, leaning full |
| gitkraken.dev | `gk-dev` | 17 | Hybrid |
| GitLens | `gitlens` | 36 | Hybrid (hand-curated home) |
| GitKraken CLI | `cli` | 10 | Styling and nav, with a hero home |
| Git Integration for Jira Data Center | `git-integration-for-jira-data-center` | 338 | Styling and nav now; cards after a metadata backfill |

What the treatment names mean:

- **Full card hierarchy:** a card home with entry paths, card landing pages for
  each section, and product-specific components.
- **Hybrid:** a card home and card landing pages for each section, over pages
  that are styled but not restructured.
- **Styling and nav only:** no generated cards.

The evidence for each row is in
[artifacts/redesign-rollout-exploration.md](artifacts/redesign-rollout-exploration.md)
and [CONTENT-SURVEY.md](CONTENT-SURVEY.md). The mockups are in
[artifacts/help-center-mockups.html](artifacts/help-center-mockups.html).

## Phases

| Phase | Work | Exit criteria |
| --- | --- | --- |
| 0. Gate | Insights redesign live and stable | No open Insights regressions; plugin version tagged and archived |
| 1. Refactor | Registry, flags, caching, callout adapters, script stripper, H3-aware TOC | Insights renders identically on the local import (visual diff of Home, a metric page, a playbook, Settings); no behaviour change in production |
| 2. Per-product decisions | Settle slugs, nav sections, home layout and accent for each product ([PER-PRODUCT-DECISIONS.md](PER-PRODUCT-DECISIONS.md)) | Every row in that file signed off for the pilot product |
| 3. Pilot: Kepler | Generate frontmatter from `preview/nav.json`; register `kepler`; verify locally; ship | Kepler live in the new layout; no change to other categories |
| 4. Rollout | MCP and CLI, then GIJ Cloud and Desktop, then gitkraken.dev and GitLens | Each product verified locally before its registry flag is turned on in production |
| 5. GIJ Data Center | Metadata backfill (descriptions, types), duplicate consolidation, then registry entry | Styling and nav first; cards once descriptions exist |

## Estimates

These are rough judgements from the exploration, not commitments.

- **Plugin refactor (phase 1):** about 2–3 days, plus local verification.
- **Each product:** mostly frontmatter scripting and a section map.
  - Kepler: low.
  - MCP, CLI, gitkraken.dev: low to medium.
  - Desktop, GitLens, GIJ Cloud: medium.
  - GIJ Data Center: high, because 337 of its 338 pages have no `description`.

## Risks

- **A whole category changes at once.** Ship each category behind its own
  registry flag, and verify on the local Docker import first. Both previous
  site-down incidents came from testing in production.
- **Performance.** Nav, breadcrumbs and cards run uncached `get_posts( -1 )`
  queries, and the search index is inlined into every page. Cache both before
  enabling any category over 100 pages.
- **Odd markup.**
  - the HubSpot form page in GIJ Cloud
  - 49 Wistia iframes in GIJ Data Center
  - copy-button scripts in Desktop and CLI
  - mislabelled frontmatter in GIJ Cloud
- **Two search systems.** SearchWP already covers the whole site.

## Open questions

Tracked in [PER-PRODUCT-DECISIONS.md](PER-PRODUCT-DECISIONS.md) under
"Cross-product decisions".
