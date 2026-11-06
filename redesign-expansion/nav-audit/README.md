# Live Nav Audit

This folder records every live sidebar nav item on each GitKraken help center,
exactly as it appears now, and maps every docs-repo page against that nav.

It exists so that pages can be archived safely before the redesign.

- **The problem.** Pages were retired by unlinking them from the nav, so the
  repos contain pages that are no longer live in the nav.
- **The rule.** Nothing in this audit is inferred from patterns. Anything the
  tools cannot prove is reported for a person to decide.

## Tools

| Script | What it does | Reads |
| --- | --- | --- |
| `nav_audit.py` | Captures each product's sidebar menu, follows every nav link, and maps each repo file. Writes the nav tree, `nav-items.csv`, `repo-pages.csv` and a summary | help.gitkraken.com (GET); each repo at `origin/main` via `git show` |
| `verify_nav.py` | Independent re-parse of the saved sidebar HTML with a different method, compared item by item with the audit | the saved `sidebar-rendered.html` |
| `visual_check.js` | Opens each home page in a headless browser and reads what is really rendered and visible. Opens each branch as a reader would, then screenshots every expanded branch | help.gitkraken.com, in Docker |
| `wp_inventory.py` | Lists every published post in each category from the public REST API, joined to the nav and repo results. Exposes WordPress-only posts and slug collisions | `/wp-json/wp/v2/categories`, `/wp-json/wp/v2/posts` |
| `make_report.py` | Writes the run's `REPORT.md`; every list is generated from the data files | the run's output |
| `dispositions.py` | Applies the archive rules in [../ARCHIVE-PLAN.md](../ARCHIVE-PLAN.md) to every repo file and writes `dispositions/DISPOSITIONS.md` and `dispositions.csv` | the run's output |
| `debug_branch.js` | Diagnostic: opens one branch and reports where it is drawn and what clips it | help.gitkraken.com, in Docker |

## Output of a run (`output/<date>/`)

| File | Contents |
| --- | --- |
| `REPORT.md` | Findings: verification table, repo pages by classification, nav items needing a decision, slug collisions, WordPress-only posts |
| `dispositions/` | KEEP / DECIDE / ARCHIVE for every repo file |
| `run.json`, `visual-check.json`, `wp-inventory.json` | Run metadata and per-tool results |
| `<product>/nav-tree.md`, `nav-tree.json` | The live sidebar, exactly as rendered: labels, links, menu-item IDs, nesting |
| `<product>/sidebar-rendered.html` | The sidebar's rendered HTML, saved verbatim as evidence |
| `<product>/nav-items.csv` | One row per nav item: HTTP status, post ID, whether the sidebar is identical on that page, the repo file it maps to |
| `<product>/repo-pages.csv` | One row per repo file: live URL, post ID, classification, inbound links |
| `<product>/wp-posts.csv` | Every published post in the category, with its repo file, nav membership and any slug collision |
| `<product>/visual-check.json`, `branches/*.png`, `sidebar-all-forced-open.png` | Browser verification and one screenshot per opened branch. The forced-open image is an overview only; readers can't reach that state |

Every tool is read-only:

- Live site: GET requests only, one at a time, with a 0.5 s delay.
- Repos: read at a git ref; working trees are never touched.

## How each claim is proven

**What counts as a nav item.** An item is a `<li class="menu-item menu-item-N">`
inside the product's sidebar `<ul id="menu-…">`. For each item the audit
records:

- the WordPress menu-item ID
- the label as rendered
- the href
- depth and parent
- the item type

The exact rendered HTML is saved as evidence in `sidebar-rendered.html`.

**The nav is the same on every page.** The sidebar is re-extracted on every
page the nav links to and compared with the home page's copy.

**The menus were read correctly.** Three independent readings must agree on
every item:

1. the audit's tree parser
2. `verify_nav.py`'s tag tokenizer
3. the headless browser's live DOM after scripts run

**A reader can see each item.** `visual_check.js` opens only the item's
ancestor branches, top down. The sidebars are accordions, so opening one branch
can close its siblings. It then waits for the open animation to finish and
checks the item is laid out, not hidden, and fully opaque, including every
ancestor. Each branch screenshot in `branches/` shows exactly what a reader
sees when that branch is open.

The capture window is 7,000 px tall on purpose. Scrolling or resizing the page
makes the site's menu script re-initialise and collapse open branches, which
produced blank screenshots in early runs. `debug_branch.js` is the diagnostic
used to find that.

**Which repo file is which live page.** Every match is made by WordPress post
ID, read from the page's `<body class="postid-N">`. A repo file maps to the
post served at the URL its filename produces, `/<category>/<sanitized
filename>/`, when that URL answers 200 without a redirect. Nav items map to the
post their link resolves to.

**Where this mapping can be wrong.** When two or more live posts share a base
slug, WordPress appends `-2`, `-3` and so on, and a repo file may publish to
the suffixed post. `wp_inventory.py` flags every such slug collision. Those
files need a person to confirm in WP Admin which post Git It Write updates.
Outside collisions, exactly one live post carries the file's slug.

**Whether a page is still linked.** Content links on every audited page are
resolved to post IDs across all eight help centers. A page that isn't in a nav
but is linked from a nav page is not unlinked.

## Classifications in `repo-pages.csv`

| Classification | Meaning | Safe to archive? |
| --- | --- | --- |
| IN NAV | The file's post is a sidebar item | No |
| IN ANOTHER HELP CENTER'S NAV | Linked from a different product's sidebar | No |
| LIVE, NOT IN NAV, LINKED FROM A NAV PAGE | Readers reach it through a link on a nav page | No, it is still in use |
| LIVE, NOT IN NAV, LINKED ONLY FROM NON-NAV PAGES | Linked only from other unlinked pages | Decide together with the pages that link to it |
| LIVE, NOT IN NAV, NO INBOUND LINKS FOUND | No nav item and no link from any audited page | Candidate, after the checks below |
| CHECK: … / NOT LIVE … | Redirect, error or missing frontmatter | Investigate first |

## What this audit cannot see

- **Links from outside the eight help centers.** That includes the products
  themselves: in-app help links, links on gitkraken.com, and links in blog
  posts and emails. An unlinked page may still be opened from inside an app.
- **Git It Write's file-to-post record.** The public API doesn't expose it.
  Slug-collision cases need confirming in WP Admin.
- **Which branch Git It Write publishes from.** Assumed `main`; confirm this in
  each repository's Git It Write settings.
- **Nav menus that differ by device.** The audit uses the desktop-width sidebar.

Before deleting anything, archive it (move it to an archive folder) and add a
301 redirect for its URL, so any outside link still lands somewhere useful.

## Re-running

```
python -I nav_audit.py --out output/<date>
python -I verify_nav.py output/<date>
python -I wp_inventory.py output/<date>
python -I make_report.py output/<date>     # after visual_check.js
python -I dispositions.py output/<date>
docker run --rm --ipc=host -v "<this folder>:/work" -v "<a node_modules with playwright 1.62.1>:/deps/node_modules:ro" \
  -e PLAYWRIGHT_MODULE=/deps/node_modules/playwright -w /work \
  mcr.microsoft.com/playwright:v1.62.1-noble node visual_check.js output/<date>
```

Network access is required. Fetched pages are cached under
`~/.cache/gk-nav-audit/<run>`, so a re-analysis doesn't hit the site again.
