# Merging back into `/gk-insights/`

**Status:** not started — reference for when the expo build is folded into the
main Insights Help Center.
**Written:** 2026-09-23, against plugin v1.17.3.

The expo site lives under its own WordPress category, `insights-expo`
(term ID 83), with page slugs prefixed `expo-ai-adoption-`. That was a
deliberate sandbox: it let the redesign and the auth gate ship without touching
the live `/gk-insights/` section. When this build replaces the main Insights
docs, **everything keyed on `insights-expo` or `expo-ai-adoption` has to move
to the `gk-insights` category and its slugs.** This document is the inventory
of what that means, so the merge can be planned rather than discovered.

Nothing here needs doing now.

---

## 1. Decide the gate's scope first

Today the gate, the template, the nav, the search index and the lockdown all
key on one thing: membership in the `GKI_DOCS_CATEGORY` category. That is
fine for the sandbox, where every page is ours. It is **not** fine for
`gk-insights`, which already holds the classic Insights documentation that
customers without an Insights subscription — and search engines — are meant
to read.

Moving `GKI_DOCS_CATEGORY` to `gk-insights` as-is would:

- gate the entire Insights section, classic pages included;
- `noindex` all of it and pull it from the sitemap;
- hide all of it from the REST API, feeds and site search.

So before any slug work, choose one of:

| Option | How | Trade-off |
|---|---|---|
| **A. Sub-category** | Restricted pages go in `gk-insights/ai-adoption` (a child category). Gate + lockdown key on the child; template + nav key on the parent. | Cleanest URL story (`/gk-insights/ai-adoption/...`) but the permalink structure `/%category%/%postname%/` puts the *deepest* category in the URL, so check what WordPress actually emits. |
| **B. Post meta flag** | A `custom_fields: gki_restricted: true` frontmatter key. Gate + lockdown test the meta; template + nav key on the category. | Per-page control, no taxonomy change, works with Git It Write today. Needs a `meta_query` in every exclusion filter instead of `category__not_in`. |
| **C. Whole category** | Gate all of `gk-insights`. | Only if the business decision is that all Insights docs become subscriber-only. |

The plugin currently has one function per concern — `gki_auth_is_gated_page()`,
`gki_auth_post_is_gated()`, `gki_auth_exclude_gated_category()` — so the
change is contained, but it is a design decision, not a find-and-replace.

---

## 2. Inventory: everything that references the sandbox

### Plugin (`gki-docs-helper/`)

| Where | What | Action |
|---|---|---|
| `gki-docs-helper.php:43` | `define( 'GKI_DOCS_CATEGORY', 'insights-expo' )` | Change to `gk-insights` (or the child slug per §1). This one constant drives the template override, asset enqueue, nav query, card grid, breadcrumbs, search index, the gate and the lockdown. |
| `gki-docs-helper.php:5, 352` | Plugin description and a docblock name `insights-expo` | Cosmetic. |
| `includes/gki-auth.php:97` | `if ( $path !== 'insights-expo/login' )` — the login endpoint is a **hard-coded path**, not derived from the constant | Change to `gk-insights/login`, or better, build it from `GKI_DOCS_CATEGORY` so this cannot drift again. |
| `includes/gki-auth.php:103, 183, 325, 1171, 1173` | `home_url( '/insights-expo/' )` fallbacks for redirect-after-login, the login-error page and `gki_auth_get_login_url()` | Same: derive from the constant. |
| `includes/gki-auth.php` §lockdown | `gki_auth_gated_category_id()` resolves the constant by slug at runtime | No change if §1 stays category-based; rewrite to a `meta_query` if option B. |
| `includes/gki-auth.php` §lockdown | `LOCAL-DEV.md` recipe cites category **83** and post **18987** | Both change with the category. |
| `js/gki-docs.js:3`, `templates/single-gki.php:5` | Comments | Cosmetic. |
| `includes/gki-passc.php` | Instruments are mapped by **slug tail** (`gki_passc_instruments()`), e.g. `…-autonomy-score` | Verify the mapping still matches once the `expo-ai-adoption-` prefix is dropped — the tails should survive, but check every one of the 11 instrumented pages. |

The **OAuth client does not need re-registering.** The registered
`redirect_uri` is `admin-ajax.php?action=gki-oauth-callback`, which has no
category in it. Only the plugin's own `/…/login/` entry point moves.

### Content (`gk-insights-expo/`)

| What | Count | Action |
|---|---|---|
| Filenames `expo-ai-adoption-*.md` → post slugs | 50 | Rename. Decide the new slug scheme (`ai-adoption-*`? bare?) once, then rename every file. Git It Write creates **new** posts for new filenames and does not delete the old ones — the 50 `insights-expo` posts must be deleted from WP Admin by hand afterwards, or the sandbox left in place behind 301s. |
| Internal links `/insights-expo/expo-ai-adoption-*` | 166 across 40 files | Rewrite to the new prefix and slugs. Script it; verify with a link check on the local site. |
| `nav_parent` custom fields | every non-index page | Values are **slugs**, so they change with the filenames. |
| `expo-ai-adoption.md` | 1 | Hidden redirect stub (`nav_category: hidden`). Probably retired. |
| Cross-links into classic pages, e.g. `/gk-insights/gk-insights#connecting-your-data` on Connect Your Data | a few | Already point at the destination section; re-check anchors after the merge. |
| Existing `/gk-insights/` posts that overlap: `ai-adoption`, `ai-adoption-settings`, `ai-adoption-playbooks`, `ai-adoption-agentic-metrics` | 4 known (from the REST search, 2026-09-23) | Decide replace vs. redirect. Slug collisions here will make Git It Write create `…-2` posts. |
| `_images/` paths and alt text | — | Standing rule: do not rename images. Paths are category-independent. |

### Git It Write

The plugin maps a repo folder to a WordPress category. Today:
`gk-insights-expo/` → `insights-expo`. Either repoint the folder to
`gk-insights`, or rename the folder and re-create the mapping. Do this **after**
the file renames and link rewrites are committed, or the first sync will publish
the half-migrated state.

### WordPress / hosting

| What | Action |
|---|---|
| 301 redirects `/insights-expo/*` → new URLs | For every one of the 50 pages, once the new posts exist. Cloudflare Bulk Redirects or a WP redirect plugin. |
| Cloudflare Cache Rule (bypass) on `/insights-expo/*` | Add the same rule for the new path; purge both. |
| Search Console / Bing removal request for `/insights-expo/` | Repeat for the new prefix if it is `noindex`; **do not** submit a removal for `/gk-insights/` if classic pages must stay indexed (see §1). |
| SearchWP engine exclusion on `insights-expo` | Move to whichever category/meta the gate keys on. |
| Legacy `/gk-insights/` section | Becomes the destination. Anything there that is superseded should be unpublished or redirected, not left as a duplicate. |

### Local environment and scripts

| Where | What |
|---|---|
| `local-dev/setup.ps1:524-533` | Verifies the import by counting `post_name LIKE 'expo-ai-adoption%'` and fetching `/insights-expo/expo-ai-adoption-home`. Update both probes. |
| `LOCAL-DEV.md` → "Verifying the lockdown" | Category 83, post 18987 and every URL in the recipe. |
| `AUTH-GATE-PLAN.md`, `PROJECT-STATUS.md`, `CLAUDE.md`, `README.md` | URLs, category names, the "Live URL" line. |
| `llms.txt`, `llms-full.txt`, `sitemap.xml` in the repo root | Generated for the Desktop help center; the repo copies mention `insights-expo`. Regenerate or drop. |
| `local-dev/.staging/gate-screenshots/` | Gate captures; regenerate for the new URL if they are used anywhere. |

---

## 3. Suggested sequence

1. Settle §1 (gate scope) and the new slug scheme. Write both down here.
2. Make the content repos private if restricted content is landing at the same time (see `AUTH-GATE-PLAN.md`).
3. On a branch: rename the 50 files, rewrite the 166 links and every `nav_parent`, retire `expo-ai-adoption.md`.
4. Plugin: change `GKI_DOCS_CATEGORY`, derive the login/fallback paths from it, adjust the lockdown per §1, bump the version, build.
5. Local: import a fresh prod copy, repoint Git It Write (or seed posts by hand), run the plugin, walk all 50 pages, run the lockdown recipe with the gate on.
6. Prod, in this order: upload the plugin (the constant change is inert until posts exist in the new category) → repoint Git It Write and sync → verify → add 301s → delete the old `insights-expo` posts → Cloudflare rule + purge → SearchWP → Search Console.
7. Update every document in the inventory above.

---

## 4. Things the sandbox taught us that must carry over

- **The template gate is not the only door.** REST, oEmbed, feeds, search,
  archives, sitemaps and the CDN all served content around the gate until
  1.17.3. Whatever the gate keys on after the merge, every filter in the
  lockdown section of `gki-auth.php` must key on the same thing. Re-run the
  verification recipe on prod after the merge.
- **The CDN keeps what it cached before the gate.** Purge on the day the gate
  scope changes, not later.
- **Search engines had already indexed the sandbox** with content snippets
  before it was gated. Restricted content must be published *behind* the gate
  from its first sync, never gated after the fact.
- **Git It Write does not delete.** Every rename leaves an orphan post.
