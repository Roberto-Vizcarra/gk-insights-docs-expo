# Per-Product Decisions (Deferred)

> **Status:** Not decided. These decisions were deliberately deferred on
> 2026-10-14. Settle each product's rows before its rollout phase starts.
> The "Proposed" values come from the exploration and the mockups. They are
> starting points, not agreements.

## Cross-product decisions

- [ ] **Accent colour.** Do all products share the Insights purple, or does each
      get its own accent token on the shared base?
- [ ] **Auth gate.** Is any product besides Insights gated? (Proposed: no.)
- [ ] **Search.** The command palette is scoped to one product. Should it offer
      "search all help centers", or hand off to SearchWP?
- [ ] **Release notes.** Should every product use one shared pattern (a version
      picker plus a collapsed H4 TOC)?
- [ ] **Callout prefixes.** Do we standardize on `> **Note:**` / `**Tip:**` /
      `**Warning:**` for new content in every repo?
- [ ] **Frontmatter CI.** Should every repo fail its build when a page is
      missing `nav_category`?

## Decisions for each product

Each product's table has three columns. **Decide** is the open question.
**Proposed** is the starting point from the exploration and the mockups.
**Decision** stays blank until someone settles it.

### Kepler (`kepler`)

| Decide | Proposed | Decision |
| --- | --- | --- |
| Category slug | `kepler` (unchanged) | |
| Nav sections and order | The 6 sections in `preview/nav.json` | |
| Is `nav.json` the agreed IA? | Yes, after review | |
| Home layout | Generic hero, 4 entry paths from "Where to go next", 6 section cards, an agent chip row | |
| Section index pages to write | One per section (5; Getting Started is a single page) | |
| Special components | Settings spec panels; agent and tracker card grids | |
| Retired pages | Exclude where `status: retired` | |

### GitKraken MCP (`MCP`)

| Decide | Proposed | Decision |
| --- | --- | --- |
| Category slug | Keep `MCP`, or normalize to `mcp` (URL is already `/mcp/`) | |
| Nav sections | Getting Started · Clients · Tools (6 categories) · Other Resources | |
| Split per-client setup into pages? | Yes: one page or anchor card per client (11 to 14) | |
| Generate a page for each tool from `tools.json`? | Yes, after the generator is fixed and the 22-vs-18 tool gap is reconciled | |
| Home layout | 2 install paths, tools-by-category cards, client chips | |

### GitKraken CLI (`cli`)

| Decide | Proposed | Decision |
| --- | --- | --- |
| Nav | Flat list of 10, no sections | |
| Home layout | Hero with 4 install cards (Homebrew, Winget, Snap or binary, npm); no section cards | |
| Default code panel label | "Terminal" | |
| Command reference | Link out to `gitkraken.github.io/gk-cli`; don't rebuild it | |
| Copy-button scripts | Remove from the Markdown (preferred), or strip in the content filter | |

### Git Integration for Jira Cloud (`git-integration-for-jira-cloud`)

| Decide | Proposed | Decision |
| --- | --- | --- |
| Nav sections | Getting Started · Connect a Git host · Features · Webhooks · Administration · Troubleshooting & FAQ · Plans & Release Notes | |
| Home layout | 3 role paths (Jira admin, developer, troubleshoot) and a 9-host card grid | |
| Host cards | Each card links to its setup, webhooks, events and JMESPath pages (via `git_host_support`) | |
| Metadata fixes | Home `content_type`/`status`; GitLab deprecations; remove drafts | |
| "On this page" lists | Remove (38 pages) | |
| Share a section map with Data Center? | Yes, for the 65 shared pages | |

### GitKraken Desktop (`gitkraken-desktop`)

| Decide | Proposed | Decision |
| --- | --- | --- |
| Nav sections | The 8 groups based on `llms.txt` (Security & Policy folds into Troubleshooting & Reference) | |
| Home layout | 4 role paths (developer, team lead, enterprise admin, troubleshoot) and 8 section cards | |
| "Requirements and limits" | Render as a spec panel on 79 pages | |
| Badges | Plan (`plan_required`), lifecycle (`status`), hosting (`hosted_variant`) | |
| Release notes | Version-picker cards, with the TOC collapsed to H2 | |
| Copy-button scripts | Remove from 12 pages | |

### gitkraken.dev (`gk-dev`)

| Decide | Proposed | Decision |
| --- | --- | --- |
| Nav sections | The 7 `llms.txt` groups | |
| Home layout | 4 role paths (developer, admin, billing owner, FAQ) and section cards | |
| Metric catalog overlap | Replace `gk-dev-dashboard-management` definitions with links to the Insights metric pages | |
| SSO | Identity-provider anchor cards | |
| Callouts | Normalize the three syntaxes to one | |

### GitLens (`gitlens`)

| Decide | Proposed | Decision |
| --- | --- | --- |
| Nav sections | Get Started · Workflows · Features · Reference · Release Notes | |
| Home layout | Install CTA, 4 workflow paths, feature cards from a hand-curated config | |
| Hubs | Merge Home, Start Here and the 3 theme hubs | |
| `gitlens-features` mega-page | Retire or redirect, because the feature pages and cards replace it | |
| Tier badges | Transform `` `PRO` `` / `` `PREVIEW` `` / `` `ADVANCED` `` code spans | |
| Settings | Mini spec panel for each `gitlens.*` key | |
| Unpublish | `GL-Test.md`, `GL-New-Page-Template.md`, possibly `home-view.md` | |

### Git Integration for Jira Data Center (`git-integration-for-jira-data-center`)

| Decide | Proposed | Decision |
| --- | --- | --- |
| Treatment | Styling and nav first; cards only after the metadata backfill | |
| Nav sections | Getting Started · Git hosts · Features · Settings · REST API & ScriptRunner · Troubleshooting · FAQ | |
| Description backfill | Reuse the Cloud schema; seed from the Documentation hub's descriptions | |
| TOC depth | H3 and H4 until headings are normalized | |
| Troubleshooting layout | Issue layout for the 29 Problem/Diagnosis/Cause/Solution pages | |
| Bug fixes | Wrong-category page; wrong-slug home link; filename typo | |
| Duplicates | Consolidate the smart-commits family and the discard-cloned-files pairs | |
