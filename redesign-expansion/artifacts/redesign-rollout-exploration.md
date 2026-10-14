# GK Help Centers — Redesign Rollout Exploration

> Local copy of the Claude Doc at
> https://claude.ai/code/artifact/c41a9f41-9e32-44d1-b0a5-88c14de703c8
> Exported 2026-10-14 · Roberto Vizcarra. The online doc may receive later edits; this file is the snapshot of record.

## Summary

Keep a single plugin, but refactor it before adding slugs: GKI Docs Helper is generic at its core, yet adding the other categories to its one constant would show every product as "GitKraken Insights", merge their navs and search, and put every help center behind the Insights auth gate. A product registry (title, section order, feature flags per category slug) is about 2–3 days of work and keeps one design system.

- **Plugin coupling.** About a dozen hardcoded points: the category constant, section order, brand strings, home role paths and metric map, metric pipeline, 11 instruments, and the auth gate. The metric pipeline, instruments and auth gate stay Insights-only behind flags.
- **Content fit.** Every help center gets the full design language. Kepler, MCP and GIJ Cloud also suit the full card hierarchy; Desktop, gitkraken.dev and GitLens suit a hybrid (card home and section landings, styled pages); CLI gets a hero home only; GIJ Data Center gets styling and nav until its metadata is backfilled.
- **The real cost is content prep, not the plugin.** No repo has the nav frontmatter the plugin reads, and six of eight write callouts as HTML `div`s the current transforms ignore.

Nothing in any repository was changed during this review.

## How the Insights redesign works today

The redesign is one WordPress plugin, GKI Docs Helper v1.17.2 (`gk-insights-docs-expo/gki-docs-helper`, about 9,400 lines), that takes over any single post in one WordPress category. Content is plain Markdown synced by Git It Write; every structural component is added at render time in PHP, CSS and JS. All help centers live in the same WordPress site (3,622 posts; 50 are Insights), so other products are just other categories on the same install.

**Scoping.** One constant, `GKI_DOCS_CATEGORY = 'insights-expo'`, and one check, `gki_docs_is_target_post()` (`is_single() && has_category(...)`), gate every hook. Pages outside the category are untouched.

**What the plugin does on a target page:**

| Layer | Mechanism | Where |
| --- | --- | --- |
| Template | `template_include` at priority 9999 swaps in `templates/single-gki.php` (3 columns: left nav, content, right TOC) and blocks the Elementor Theme Builder template | `gki-docs-helper.php` §5 |
| Navigation and breadcrumbs | Built per request from frontmatter custom fields: `nav_category`, `nav_parent`, `nav_order`, `nav_label`, `page_type` (`main-index` / `index` / `content`) | `gki_docs_get_nav_structure()` |
| Card grids | Index pages render child cards from `card_icon` (Tabler icon name), `card_color`, `card_description`; template splits content at the first `<hr>` into Intro → Cards → Overview | `gki_docs_get_child_pages()` |
| Search | Every page in the category is serialized into `gkiSearchData` and searched client-side by the Ctrl/Cmd-K command palette | §1 enqueue, `gki-passc.js` |
| Theme switcher | Cookie + localStorage, injected as `data-theme` on `<html>` server-side so there is no flash | §1 |
| Content transforms | `the_content` filters: Parsedown cleanup (p15), metric-page pipeline (p16), general-page components (p17): typed callouts, step rails, code panels, checklists, settings spec panels, hero figure | `includes/gki-passc.php` |
| Styling | Design tokens (`--gki-*`), Elementor overrides scoped to `body.gki-docs-page` with `!important` | `css/gki-docs.css`, `css/gki-passc.css` |
| Auth gate | Optional OAuth2 against gitkraken.dev plus an Insights-license check, off by default | `includes/gki-auth.php` |

The content contract is the frontmatter. Insights pages carry a `custom_fields:` block (`nav_category: metrics`, `nav_order: 30`, `card_icon: chart-dots-3`, …) beside `taxonomy: category: insights-expo`. Without those fields the nav falls back to a flat alphabetical list and no cards render.

## How Insights-specific the plugin is

The engine is generic; the Insights coupling sits in about a dozen identifiable places, and most of it is labels, lists and one content pipeline rather than architecture. Roughly 70% of the code (layout, nav builder, cards, breadcrumbs, palette, theme, callouts, step rails, code panels, base CSS) works for any category that adopts the frontmatter.

| Coupling | Where | Effort to generalize |
| --- | --- | --- |
| Single category constant `insights-expo` used in every `get_posts` and in `has_category` | `gki-docs-helper.php:43`, `gki-auth.php:826`, `gki-passc.php` | Low: replace with a per-request "active help center" resolved from a registry of slugs |
| Hardcoded section order `getting-started, connect-your-data, metrics, playbooks, admin` | `gki_docs_get_nav_structure()` | Low: move into the registry (unknown sections already append after) |
| Brand strings "GitKraken Insights" (nav title, home label), aria label, menu location `gki-insights-nav` | `single-gki.php:35-52, 270` | Low: registry field `title` |
| Home page: role paths (`for-executives`, `for-admins`…) with hardcoded copy, and the "metric map" | `gki_passc_role_paths()`, `gki_passc_metric_map()`, `single-gki.php:251-330` | Medium: role paths become frontmatter (`entry_role`, `entry_title`); the metric map becomes an optional per-product module |
| Metric-page pipeline: 12 canonical H3 sections, spec panel, formula panel, tier scales, FAQ pairs | `gki_passc_restructure()` (runs only when `nav_category: metrics`) | None needed: it is already inert for other content. Keep it Insights-only |
| 11 interactive instruments keyed to Insights slugs | `gki_passc_instruments()`, `gki-passc.js:188-690` | None: inert elsewhere, but ships about 500 lines of JS to every page; split into its own file |
| Callout typing uses an English keyword list tuned to Insights prose (`precedence`, `backfill`, `suppress`…) | `gki_passc_render_callout()` | Low: add explicit `> **Note:**` / `Tip` / `Warning` prefix detection first, keep keywords as fallback |
| Playbook step rails trigger on `nav_category: playbooks`; settings specs on slugs ending `-settings` | `gki_passc_restructure_general()` | Low: trigger on a `layout:` frontmatter value instead |
| Palette placeholder "Search metrics, playbooks, settings…" | `gki-passc.js:885` | Low: localize from the registry |
| Auth gate: OAuth + `totalInsightsLicenses` check, `/insights-expo/login/` endpoint | `includes/gki-auth.php` | Keep Insights-only; other help centers are public |
| `gki-` prefix on every class, token, cookie and option | everywhere | Cosmetic; no need to rename |

**Two non-obvious risks for bigger help centers.** The nav, breadcrumbs and card builders each run `get_posts( posts_per_page => -1 )` over the whole category on every request, several times. That is fine at 50 pages but should be cached (transient keyed by category, flushed on save) before applying it to a category with several hundred pages. The search index is also inlined into every page; for a large help center it should move to a cached JSON endpoint.

The theme cookie (`gki_theme`) and localStorage key are site-wide on the same domain, so a reader's dark-mode choice would carry across every help center. That is a feature, not a bug.

## One plugin or a second plugin?

Keep one plugin and refactor it into a shared core plus per-product configuration; do not fork a second plugin. Simply adding slugs to `GKI_DOCS_CATEGORY` would not work as-is, and a fork would duplicate about 7,000 lines of CSS/JS that must stay visually identical.

| Option | What it takes | Verdict |
| --- | --- | --- |
| A. Just add the other slugs | Change the constant to an array and `has_category( array(...) )` | Not enough. Every product would show "GitKraken Insights" in the nav, share one merged nav and search index across products, inherit the Insights section order and home page, and the auth gate would lock every help center |
| B. Copy into a second plugin for the other products | Duplicate the plugin, rename prefixes, strip Insights bits | Works fast, but two copies of the design system drift; every fix (theme, palette, Elementor traps in CLAUDE.md) lands twice. Both plugins also hook the same `template_include` and `the_content` and must not collide |
| **C. One plugin, product registry (recommended)** | A small registry maps each category slug to its title, section order, palette placeholder, accent, home layout and feature flags (`auth_gate`, `metric_pipeline`, `instruments`). All queries use the resolved current category. Insights-only modules load only when flagged | About 2–3 days of refactor plus testing on the local Docker import; one design system, one release |

**Refactor outline for option C**

1. Add `gki_docs_registry()` returning an array keyed by category slug; `gki_docs_current_hc()` resolves the post's category once per request. Replace every `GKI_DOCS_CATEGORY` use.
2. Move strings (title, section order, aria label, palette placeholder) into the registry; register one nav menu location per product.
3. Make the home layout data-driven: role paths from frontmatter (`entry_role`, `entry_title`, `entry_desc`), the metric map becomes an Insights-only module.
4. Gate `gki-auth.php`, the metric pipeline and the instruments JS behind registry flags; split instruments into `gki-instruments.js` loaded only where flagged.
5. Add explicit callout prefixes (`Note`, `Tip`, `Warning`, `Important`) and a `layout:` frontmatter key (`steps`, `settings`, `reference`) to replace the playbooks/`-settings` triggers.
6. Cache nav and search data per category (transient, flushed on `save_post`); serve search JSON from an endpoint for large help centers.
7. Optional per-product accent token (e.g. GitLens, Desktop) while keeping the shared purple base.

The real cost of rollout is not the plugin; it is adding `custom_fields` frontmatter (`nav_category`, `nav_order`, `page_type`, card fields) to every page of each help center and writing index pages where none exist. That is scripted work per repo, sized in the compatibility section below.

## Page content overview

Every other help center is a single flat folder in one WordPress category, and none of them has the nav fields (`nav_category`, `nav_order`, `page_type`, card fields) the plugin builds menus and cards from. Most also write callouts as HTML `div`s rather than the blockquotes the Insights transforms look for.

| Help center | Category slug | Pages | Metadata today | Callout syntax | Existing hubs and IA |
| --- | --- | --- | --- | --- | --- |
| Git Integration for Jira (Data Center) | `git-integration-for-jira-data-center` | 338 | `title` only; `description` empty on 337 | `bbb-callout` divs (563) | Home, Documentation (32 linked cards), Integration Guides (13 hosts), FAQ, Troubleshooting, REST API hubs |
| Git Integration for Jira Cloud | `git-integration-for-jira-cloud` | 145 | Rich: `content_type`, `audience`, `git_host_support`, `plan_required`; some mislabels | `bbb-callout` divs (330) | Home, Integration Guide (9 hosts with logos), Features, Getting Started Guide + 3 subpages, End-User Guide |
| GitKraken Desktop | `gitkraken-desktop` | 90 | Rich: `content_type`, `audience`, `plan_required`, `hosted_variant`, `status` | `callout--*` divs (193) | Home with link lists; `integrations` hub; 9 groups in `llms.txt` |
| GitLens | `gitlens` | 36 | `title`, `description` only | `callout--*` divs (35) + mixed blockquotes | Home + Start Here + 3 workflow-theme hubs (overlapping) |
| Kepler | `kepler` | 18 live | Rich (same schema as Desktop) | `div.note` (6) | **Written IA** in `preview/nav.json`: 6 sections |
| gitkraken.dev | `gk-dev` | 17 | Medium: `content_type`, `role_required` | three syntaxes (divs, `bbb-callout`, blockquotes) | Home with feature table; 7 groups in `llms.txt` |
| GitKraken CLI | `cli` | 10 | Medium | blockquotes `> **Note:**` (7) | Home only; command reference is external |
| GitKraken MCP | `MCP` (URL `/mcp/`) | 5 | Medium; `tools.json` holds every tool's schema | blockquotes (19) | README defines home sections |

**What carries over cleanly everywhere:** the 3-column layout, theme switcher, right-hand TOC, command-palette search, breadcrumbs, step rails from numbered lists, image lightbox and the token-based styling. Pages already share conventions the plugin can use: a `<kbd>Last updated</kbd>` stamp on most pages, `<figure>` images from `/wp-content/uploads/`, and `***` section rules.

**What each repo needs before cards work:**

- **Callout adapters.** Map `callout--basic|warning|success|danger|info`, `bbb-callout bbb--info|tip|alert|note|error` and `div.note` onto the typed callout component. This is a CSS-plus-regex change in the plugin, not a content change.
- **Inline copy-button `<script>`/`<style>` blocks** on 12 Desktop pages and 8 of 10 CLI pages restyle every `pre` globally and will clash with the plugin's code panels. Strip them in the content filter or remove them from the Markdown.
- **Nav frontmatter.** Add `custom_fields` to every page. For Kepler, generate them from `nav.json`; for Desktop and gk-dev, from the `llms.txt` groups; for GIJ Cloud, from `content_type` + `audience` + the existing hubs.
- **Heading depth.** GIJ Data Center pages are mostly H3-first (1,104 H3 vs 513 H2), so the TOC must include H3, or headings need normalizing.
- **Clean-up in the publish folders:** test and template pages (`GL-Test.md`, `GL-New-Page-Template.md`, `GKD-New-Page-Template.md`, `Testing.md`), retired Kepler pages, a GIJ Data Center page filed under the Cloud category, and a GIJ home link using the wrong slug.

**Product notes**

- **Git Integration for Jira (both).** 65 pages exist in both repos, and both home pages share the same layout, so one design covers both. The Integration Guide pages are already provider card grids in disguise (logo, link, one-line description). Data Center is the largest help center (338 pages, 36 REST API pages, 15 Javadoc pages) and has no usable metadata.
- **GitKraken Desktop.** Best metadata of the large sites. 79 pages open with a "Requirements and limits" bullet block, which maps directly to the Insights spec panel. Release notes run up to 881 lines.
- **GitLens.** Small but dominated by mega-pages: `gitlens-settings` (2,249 lines, 34 HTML tables of `gitlens.*` keys), `gitlens-features` (1,716 lines, duplicates the feature pages) and three release-notes pages over 1,100 lines. Tier badges are written as inline code in headings (`` `PRO` `` 87 times).
- **Kepler.** Closest to Insights already: a written 3-level IA, rich frontmatter, a 1,049-line Settings reference built from "Control | What it does" tables.
- **gitkraken.dev.** Admin and billing heavy. `gk-dev-dashboard-management` repeats about 30 Insights metric definitions; decide whether it should link to the Insights metric pages instead.
- **CLI.** Ten pages; no command reference to restructure.
- **MCP.** Five pages, but catalog-shaped: 14 clients, 22 tools in 6 categories. `tools.json` already has each tool's description, parameters and read-only flag, enough to generate Insights-style reference objects. The generator (`render-tools.ts`) writes to a filename that no longer exists and would produce a duplicate page.

## Compatibility and recommendation per product

Three help centers earn the full card hierarchy, three suit a hybrid (a card home and section landings over styled pages), CLI needs only styling plus a hero home, and GIJ Data Center should get styling and nav only until its metadata is fixed. Every product gets the shared design language: theme switcher, colors and branding, command-palette search, generated left nav, right TOC, breadcrumbs, typed callouts and step rails.

| Help center | Treatment | Card home and entry paths | Section landing cards | Special components | Prep work |
| --- | --- | --- | --- | --- | --- |
| Kepler | Full card hierarchy | Yes: 8 "Where to go next" rows become paths | Yes: 6 sections from `nav.json` | Settings spec panels (16 Settings pages), agent and tracker card grids | Low: generate frontmatter from `nav.json`; map `div.note` |
| GitKraken MCP | Full card hierarchy (catalog) | Yes: Install via GitLens / via CLI | Clients (14), tools by category (6) | Tool reference objects generated from `tools.json` | Medium: split per-client setup; fix the generator; repair restarted numbered lists |
| Git Integration for Jira Cloud | Full card hierarchy | Yes: Jira admin / developer / troubleshoot (from `audience`) | Git hosts (9 with logos), Features, Signals | Settings spec panels; host card with setup, webhooks, events sub-links | Medium: section map; `bbb-callout` adapter; strip `&nbsp;` spacers and manual "On this page" lists |
| GitKraken Desktop | Hybrid, leaning full | Yes: developer / team lead / enterprise admin; Cloud vs Self-Hosted | 8 sections from the `llms.txt` groups | "Requirements and limits" becomes a spec panel; plan and status badges | Medium: section map + order for 90 pages; `callout--*` adapter; remove copy-button scripts |
| gitkraken.dev | Hybrid | Yes: developer / admin / billing | 7 groups from `llms.txt` | SSO identity-provider cards; plan comparison | Low to medium: three callout syntaxes; decide the metrics-page overlap |
| GitLens | Hybrid | Yes, hand-curated: install + 4 workflow themes | Features, Workflows, Reference | Settings spec panels for `gitlens.*` keys; `PRO` / `PREVIEW` tier badges | Medium: thin frontmatter; merge overlapping hubs; exclude test pages |
| GitKraken CLI | Styling + nav, with a hero home | Yes: install by OS, Use AI, Connect agents | No (10 pages, nothing to nest) | Labelled code panels ("Terminal" default for 82 unlabeled fences) | Low: remove copy-button scripts |
| Git Integration for Jira Data Center | Styling + nav now; cards later | Not until metadata exists | Later: FAQ, Troubleshooting, REST API, Git hosts | H3-aware TOC; `bbb-callout` adapter | High: backfill `description` on 337 pages, add types; consolidate duplicates |

**Suggested rollout order:** Kepler first (cheapest and already structured), then MCP and CLI (small), then GIJ Cloud and Desktop (largest gain for readers), then gitkraken.dev and GitLens, and GIJ Data Center last after its metadata backfill. Ship the plugin refactor before any of them, verified on the local Docker import with Insights unchanged.

## Mockups

[Open the GK Help Center Redesign Mockups](https://claude.ai/artifact/5vHi2weX6UQgwPKSN8c9nq) (local copy: `help-center-mockups.html` in this folder): one tab per help center, each with a landing page and a representative content page drawn with the plugin's own tokens and components. The theme switch in each mock's left nav works, and a toggle marks which source pattern each component is built from (for example `bbb-callout bbb--error` becoming a typed callout).

| Help center | Landing mockup | Content mockup |
| --- | --- | --- |
| GitKraken Desktop | Role paths + 8 section cards | Stage, Unstage, and Ignore Files: "Requirements and limits" as a spec panel |
| GitLens | Workflow paths + curated feature cards | Settings Overview: each `gitlens.*` key as a mini spec panel |
| Kepler | Paths from "Where to go next" + 6 section cards | Settings → Agents: control table as a spec panel |
| GitKraken MCP | Install paths + tools-by-category cards + client chips | `issues_get_detail` as a reference object generated from `tools.json` |
| GitKraken CLI | Install-by-OS cards, flat nav, no section cards | AI Features: labelled "Terminal" code panels |
| gitkraken.dev | Role paths + section cards | Configure SSO: callout, step rail, identity-provider cards |
| GIJ Cloud | Role paths + Git host card grid | GitHub.com: `bbb-callout` as a typed callout, sibling-page cards |
| GIJ Data Center | Styled link list, no cards | Troubleshooting page as a Problem / Diagnosis / Cause / Solution layout |

Page titles, steps, table rows and counts come from each repo. Nav groupings for Desktop, gitkraken.dev and both GIJ sites are proposals, not existing structure.

## Risks, open questions and next steps

**Risks**

- **Template takeover reaches every page in a category at once.** The plugin swaps the template for every post in a target category; a page with odd markup (HubSpot form in `GIJ-Advanced-Support-form`, Wistia embeds, copy-button scripts) breaks visibly on day one. Roll out one category at a time behind the registry flag.
- **Performance at scale.** Uncached `get_posts( -1 )` calls and the inlined search index are fine for 50 pages, not for 338. Cache both before enabling GIJ Data Center or Desktop.
- **Two search systems.** SearchWP already indexes the whole site; the palette would sit beside it on more pages.
- **Production safety.** Both past site-down incidents came from testing in production; every category should be verified on the local Docker import first.

**Open questions**

- [ ] Should all products share the Insights purple, or does each get its own accent token?
- [ ] Does any other help center need the auth gate? (Assumed no: all are public.)
- [ ] Should `gk-dev-dashboard-management` keep its own metric definitions or link to the Insights metric pages?
- [ ] Who owns the frontmatter backfill for GIJ Data Center (337 empty descriptions)?
- [ ] Is the Kepler `nav.json` the agreed IA, or still a proposal?

**Next steps**

1. Refactor the plugin into core + registry (option C), with Insights as the only registered category; confirm Insights renders identically on the local import.
2. Add callout adapters (`callout--*`, `bbb-callout`, `div.note`), the copy-script stripper and an H3-aware TOC.
3. Pilot on Kepler: generate frontmatter from `nav.json`, register `kepler`, verify locally, then ship.
4. Repeat per product in the rollout order above, starting each with a frontmatter script and a section map.
