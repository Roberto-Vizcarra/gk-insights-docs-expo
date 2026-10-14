# Content Survey: GitKraken Help Center Repos

> Read-only survey taken on 2026-10-14 against these branches: `origin/main` for
> each repo, and `docs-automation/gitlens-2026-10-06` for GitLens. Counts are
> from that snapshot, so re-run them before acting on any number.
> Purpose: decide how much of the Insights redesign each help center can use.

## Cross-repo findings

- **Publishing model.** Every repo is one flat folder (depth 1) that publishes
  under one WordPress category through Git It Write. URLs are
  `help.gitkraken.com/<category>/<file-slug>/`.
- **Navigation fields.** No repo has `nav_*`, `page_type`, `card_*`, `order`,
  `parent` or `icon` fields. Navigation lives in WordPress or nowhere.
- **Images.** Images are hardcoded as
  `<figure><img src="/wp-content/uploads/…" class="help-center-img img-bordered">`
  with an inline-styled `<figcaption>`. The repo `_images/` folders are source
  copies that no page references.
- **Page furniture.** Most pages open with `<kbd>Last updated: Month YYYY</kbd>`
  and separate sections with `***`.
- **Callout markup.**
  - HTML callouts: Desktop, GitLens, gk-dev, GIJ Cloud, GIJ Data Center, Kepler.
  - Blockquote callouts: CLI and MCP.
- **Inline script blocks.** Desktop (12 pages) and CLI (8 pages) carry inline
  copy-button `<script>`/`<style>` blocks.

## Summary table

| Repo (worktree) | Category | Pages | Frontmatter | Callouts | Largest page (lines) | Verdict |
| --- | --- | --- | --- | --- | --- | --- |
| git-integration-for-jira-self-managed-docs | `git-integration-for-jira-data-center` | 338 | title only (337 empty descriptions) | `bbb-callout` ×563 | scriptrunner Javadoc Repository (883) | Styling + nav now |
| git-integration-for-jira-cloud-docs | `git-integration-for-jira-cloud` | 145 | rich | `bbb-callout` ×330 | smart-commits / general-settings (631) | Full card hierarchy |
| gitkraken-desktop-docs | `gitkraken-desktop` | 90 | rich | `callout--*` ×193 | 9x release notes (881) | Hybrid, leaning full |
| gitlens-docs | `gitlens` | 36 | title, description | `callout--*` ×35 + blockquotes | GL-Test (3,512), gitlens-settings (2,249) | Hybrid |
| kepler-docs | `kepler` | 18 live (21 files) | rich | `div.note` ×6 | release-notes-current (1,186) | Full card hierarchy |
| gk-dev-docs | `gk-dev` | 17 | medium | 3 syntaxes | dashboard-management (603) | Hybrid |
| gk-cli-docs | `cli` | 10 | medium | blockquotes ×7 | cli-home (316) | Styling + nav, hero home |
| MCP-Docs | `MCP` (`/mcp/`) | 5 (+ template) | medium | blockquotes ×19 | MCP-getting-started (508) | Full card hierarchy (catalog) |

---

## GitKraken Desktop

**Category and frontmatter**

- Category `gitkraken-desktop`. One page (`GK-soc2-request.md`) uses `general`.
- All 90 pages carry the same fields: `title`, `description`, `product`,
  `feature`, `content_type`, `audience`, `plan_required`, `os_support`,
  `git_hosts`, `integrations`, `hosted_variant`, `status`, `last_verified`,
  `llms_include`, `tags` and `taxonomy.category`.
- `og_image` appears on the 6 release-notes pages.
- Value counts:

| Field | Values |
| --- | --- |
| `content_type` | how-to 56, overview 9, reference 8, release-notes 6, troubleshooting 4, legacy 2, policy 2, faq 1, form 1, home 1 |
| `audience` | developer 69, all 10, enterprise-admin 6, buyer 3, team-lead 2 |
| `plan_required` | all 71, Pro 7, Enterprise 6, Advanced 3, mixed 3 |
| `hosted_variant` | both 68, self-hosted 12, cloud 10 |
| `status` | GA 80, legacy 5, preview 3, sunset 2 |

**Structure.** 90 pages and 19,499 lines in a flat folder. The only grouping is
in `llms.txt`, which covers 79 pages in 9 groups:

| Group | Pages |
| --- | --- |
| Getting Started | 9 |
| Core Git Workflows | 11 |
| Advanced Git & Repo Mgmt | 14 |
| Integrations & AI | 18 |
| Collaboration & Team | 5 |
| Self-Hosted & Enterprise | 8 |
| Troubleshooting & Reference | 11 |
| Security & Policy | 1 |
| Other | 2 |

**Hubs**

- `gitkraken-desktop-home.md` (72 lines) has intro prose, a YouTube iframe,
  "How to get started" (10 links), "On-premises deployment" (2) and
  "Additional resources" (3).
- `integrations.md` links to 7 Git hosts and the issue trackers.

**Content mix.** About 62% of pages are task how-tos; 62 files use numbered
steps (717 lines). The rest:

- **Reference:** keyboard-shortcuts, preferences, search, PR filter syntax,
  system requirements, authentication.
- **Release notes:** 6 pages (current, 11x–7x).
- **Troubleshooting:** 4 pages plus the FAQ.
- **Video:** 85 iframes across 36 files.

**Patterns**

- **Callouts:** `callout--basic` ×117, `--warning` ×53, `--success` ×10,
  `--danger` ×3, plain ×10. The body is `<p><strong>Tip:</strong> …</p>`.
- **Blockquotes:** 14 lines with mixed prefixes, including emoji.
- **Images:** 568 `<img>` tags, about 557 of them inside `<figure>`. No Markdown
  images.
- **Inline HTML:**
  - `<kbd>` ×1,028
  - UI-button spans (about 60)
  - Font Awesome icons
- **Code fences:** bash 91, plain 150, yaml 6, json 3, cmd 1.
- **Tables:** 408 Markdown table rows. Raw HTML `<table>` in 10 files, including
  `keyboard-shortcuts` (12 tables with colgroup).
- **Inline scripts:** `<style>`/`<script>` copy-button blocks in 12 files
  (e.g. `themes.md:150`, `commits.md:334`).
- **Rules:** `***` ×426.
- **Headings:** H2 557, H3 567, H4 28.
- **Requirements blocks:** 79 pages open with a "**Requirements and limits**"
  block, often followed by a summary table.

**Largest pages**

| Page | Lines |
| --- | --- |
| `9x.md` | 881 |
| `8x.md` | 868 |
| `7x.md` | 648 |
| `10x.md` | 543 |
| `workspaces.md` | 494 |
| `11x.md` | 471 |
| `agents.md` | 467 |

About 35 pages run over 190 lines.

**Conflicts**

- No hierarchy or order fields.
- HTML callouts.
- Inline copy scripts.
- Emoji in release-note H3s.
- HTML shortcut tables.
- Very long release-notes TOCs.

---

## GitLens

**Category and frontmatter**

- Category `gitlens` on all 36 pages.
- Only `title`, `description`, `taxonomy.category` and `last_updated` are set.
- Some descriptions are weak (`gitlens-troubleshooting.md`: "GitLens
  Troubleshooting").
- Indentation is inconsistent.

**Structure.** 36 pages, 16,978 lines, flat. File naming mixes `GL-*` and
`gitlens-*`. These files are published but are not real content:

- `GL-Test.md` (3,512 lines, a copy of the release notes)
- `GL-New-Page-Template.md`
- `home-view.md` ("Commit Graph is Home")

**Hubs**

- `gitlens-home.md` (258 lines) has an Install button, 3 workflow themes and
  "Where to Go Next".
- `gitlens-start-here.md` repeats the themes.
- Three short theme hubs: `GL-Interactive-Code-History.md`,
  `GL-accelerate-pr-reviews.md`, `GL-Streamline-Collaboration.md`.

**Content mix**

- **Feature pages:** Commit Graph, Launchpad, Worktrees, Workspaces, Agents, AI,
  Cloud Patches, Code Suggest, Visual File History, Sidebar.
- **Workflow how-tos:** 5 pages with numbered H2s.
- **Settings reference:** `gitlens-settings.md`, 2,249 lines with 34 HTML
  tables.
- **Release notes:** current (1,116), v17 (1,381), older (2,714).
- **Other:** FAQ, a troubleshooting stub (26 lines), Community vs Pro, security,
  terms.

**Patterns**

- **Callouts:** `callout--warning` ×21, `--basic` ×13, `--info` ×1, mostly tier
  warnings.
- **Blockquotes:** 18 lines with mixed prefixes (`Note:`, `**Note:**`,
  `Important:`, `Pro Tip:`, emoji, bare `>`).
- **Tier badges** as heading code spans: `` `PRO` `` ×87, `` `Pro` `` ×8,
  `` `PREVIEW` `` ×13, `` `ADVANCED` `` ×3.
- **Media:**
  - 527 upload images, 265 of them in `<figure>`
  - 7 autoplay `<video>` elements
  - 14 YouTube iframes
- **Code:** few fences, but `<code>` ×724 inside the settings tables.
- **Rules:** `***` ×100, `---` ×120.
- **Headings:** H2 333, H3 858, H4 208 (release dates).

**Conflicts**

- Thin frontmatter.
- `gitlens-features.md` (1,716 lines) duplicates the standalone feature pages.
- Giant release notes.
- Test and template pages are published.
- Tier badges show up in the TOC.
- HTML settings tables.
- Overlapping hubs.

---

## Kepler

**Category and frontmatter**

- Category `kepler`.
- 21 files: 18 live, 2 `status: retired` (`workflow-views.md`,
  `environment-and-setup.md`), 1 template.
- Same rich schema as Desktop.

**Written IA.** `preview/nav.json` defines the proposed left nav, and
`preview/build.py` fails when the nav and the pages drift apart.

| Section | Pages |
| --- | --- |
| Getting Started | `Kepler-Getting-Started` |
| Core Concepts | interface, arranging-your-work, actions, tasks-and-resources, task-view, agent-graph |
| Working in Kepler | create-task, agent-sessions, review-changes, voice-input |
| Integrations | agent-integrations, mcp-servers, issue-tracker-integrations, pull-request-integrations |
| Configuration | settings, remote-environments |
| Release Notes | release-notes-current |

**Content mix**

- 14 how-to, 1 concept, 1 reference (`settings.md`: 1,049 lines, 16 H2s, about
  351 table rows of "Control | What it does"), 1 release notes (1,186 lines).
- Getting Started has a "Where to go next" table (8 rows) and a 4-row platform
  install table.

**Patterns**

- **Code blocks:** none.
- **Tables:** 206.
- **Images:** 138 figures, each wrapped in a lightbox link (`<a href target="_blank">`).
- **Other markup:** `<kbd>` ×43; callouts are `<div class="note" markdown="1">` ×6.
- **Comments:** 52 `TODO(screenshot)` and 6 `TODO(verify)`.
- **Links:** internal links have no trailing slash (`/kepler/actions`).

**Conflicts**

- Div callouts that need Markdown Extra.
- Images are already wrapped in lightbox links.
- Headings go down to H5 in `settings.md`.
- Retired pages still sit in the folder.

---

## gitkraken.dev

**Category and frontmatter**

- Category `gk-dev`, 17 pages.
- Shared fields, plus `role_required` (5 pages), `cloud_only` (4) and
  `integrations` (3).
- `content_type` values: admin 4, how-to 4, security 2, faq 2, reference 2,
  concept, install, integration.
- `plan_required` is free text.

**Groups** (from `llms.txt`)

| Group | Pages |
| --- | --- |
| Getting Started | 4 |
| Subscription & Billing | 5 |
| Organization & Team Mgmt | 2 |
| Insights & Analytics | 2 |
| Automation & Workflows | 1 |
| Security | 2 |
| Policies | 1 |

**Content**

- Admin and billing dominate the help center.
- `gk-dev-dashboard-management.md` (603 lines) is an Insights metric catalog:
  about 30 metric H3s in 5 groups.
- `gk-dev-single-sign-on.md` covers 4 identity providers: G Suite, Azure AD,
  Okta, Ping.
- The home page has a feature/plan table and 4 "> **Use X**" decision
  blockquotes.

**Patterns**

- **Callouts:** `callout--*` ×32, legacy `bbb-callout` ×2, Note blockquotes ×8,
  plus about 13 decision blockquotes.
- **Images:** six different `<img>` class orders, plus `srcset`.
- **Tables:** 17 tables, one of them a raw HTML role-permissions table.

**Conflicts**

- Three callout syntaxes.
- Code-span badges in headings.
- Some relative links have no leading slash (`gk-dev-home.md:103`).
- Metric content duplicates Insights.

---

## GitKraken CLI

**Category and frontmatter**

- Category `cli`, 10 pages.
- Fields: `title`, `description`, `product`, `feature`, `content_type`,
  `audience`, `plan_required`, `status`, `last_verified`, `taxonomy`.

**Pages.** `cli-home` (316 lines) holds the install table, a 5-step Quick Start,
per-OS install and troubleshooting. The other pages:

- AI features (218 lines)
- MCP (158)
- work items (154)
- providers (154)
- PRs and issues (138)
- GitLens and Desktop (137)
- JSON output (119)
- terms
- security

**Content**

- How-tos follow "## Requirements → ## How to X", each with a summary table.
- The command and flag reference is external
  (`gitkraken.github.io/gk-cli/docs/`).

**Patterns**

- **Code fences:** 84, and only 2 have a language.
- **Tables:** 10.
- **Blockquote callouts:** 7.
- **Images:** 3 figures.
- **Placeholders:** 7 `Screenshot needed` comments, plus bare `<ORG_NAME>`-style
  placeholders.

**Conflicts**

- A copy-button `<style>`/`<script>` block on 8 of the 10 pages.
- Unlabelled code fences.
- Angle-bracket placeholders in prose.

---

## GitKraken MCP

**Category and frontmatter**

- Category `MCP` (URL `/mcp/`).
- The 5 pages are:

| Page | Lines | Note |
| --- | --- | --- |
| getting-started | 508 | |
| apps | 298 | status preview; missing from `sitemap.xml` |
| tools-reference | 168 | |
| example-workflows | 78 | |
| prompts-reference | 48 | |

- The README (UTF-16) defines the home sections.

**Generator.** `pnpm build` regenerates `tools.json` and `prompts.json`. Then
`render-tools.ts` writes `MCP/03-tools-reference.md`, which is a stale filename.
The hand-written reference lists 22 tools; `tools.json` has 18 (no `gitlens_*`
tools).

**Content**

- **Getting started:**
  - a 14-row client table
  - two install paths (GitLens, CLI)
  - 11 per-client H4 sections
  - 22 screenshots
- **Tools reference:** 22 tools in 6 categories (Git 9, GitKraken 1, GitLens 4,
  Issues 3, Pull Requests 4, Repository 1).
- **Prompts reference:** one prompt.

**Patterns**

- **Code fences:** 29 (bash 15, json 8).
- **Tables:** 15 Markdown tables plus 1 HTML table.
- **Blockquotes:** 19, with prefixes including `**Requirements:**` and
  `**Use this when**`.
- **Images:** 24 figures.

**Conflicts**

- Getting Started has 12 numbered lists that restart at 1.
- New callout types to map.
- Generator drift.

---

## Git Integration for Jira Cloud

**Category and frontmatter**

- Category `git-integration-for-jira-cloud`, 145 pages.
- Every page has the same rich fields, including `deployment`,
  `git_host_support`, `role_required` and `version_required`.
- Value counts:

| Field | Values |
| --- | --- |
| `content_type` | integration 51, concept 25, reference 20, install 19, security 12, troubleshooting 9, admin 5, faq 4 |
| `audience` | admin 73, all 45, developer 24, reseller 3 |
| `status` | GA 137, deprecated 6, draft 2 |

- Known mislabels:
  - The home page is tagged `troubleshooting` and `deprecated`.
  - Two GitLab host pages are marked deprecated.
  - Drafts `Testing.md` and `GIJ-Cloud-New-Doc.md` sit in the publish folder.

**Hubs**

- **Home (79 lines):** Knowledgebase, Additional Resources, and Community and
  Support link lists.
- **`integration-guide-gij-cloud.md`:** 9 hosts, each with a logo image, link
  and one-line description:
  - GitLab.com
  - GitLab CE/EE Server
  - GitHub.com
  - GitHub Enterprise Server
  - VSTS / Azure DevOps
  - TFS / Azure DevOps Server
  - AWS CodeCommit
  - Bitbucket Cloud
  - Plain Git repository
- **Other hub pages:**
  - Features (21 links)
  - Documentation (24 links)
  - How-to (13 links)
  - Getting Started Guide and its 3 subpages
  - End-User Guide

**Content mix**

- About 35% of pages are per-host guides, each with webhook, events and
  JMESPath sibling pages.
- Concepts: smart commits, repository browser, JQL, automation, Signals
  (6 pages).
- Admin pages: settings, permissions, PAT, SSH, SSL.
- Reference and commercial pages.

**Patterns**

- **Callouts:** no blockquotes. 330 `bbb-callout` divs (info 123, tip 83,
  alert 68, note 44, error 12).
- **Code fences:** 87 (java 36, json 21, powershell 11, bash 9).
- **Tables:** 714 Markdown table lines. Check and cross images sit inside table
  cells in 8 files.
- **Images:** 577 Markdown plus 410 `<img>`.
- **Spacing and inline markup:** `<div>` ×1,348, `style=` in 83 files,
  `&nbsp;` ×883, `<br>` ×256.
- **Manual TOCs:** 38 pages have a hand-written "On this page" list.
- **Embeds:** 13 iframes; a HubSpot form plus `<script>` in
  `GIJ-Advanced-Support-form.md`.
- **Headings:** H2 419, H3 502, H4 93; 17 stray H1s.

---

## Git Integration for Jira Data Center (self-managed repo)

**Category and frontmatter**

- 337 of 338 pages use `git-integration-for-jira-data-center`. The repo is named
  self-managed, but the slug is data-center.
- Bug: `when-a-license-expires-a-session-error-is-shown-to-the-user-gij-self-managed.md`
  uses the Cloud category.
- Bug: the home page links to `/git-integration-for-jira-self-managed/…`, which
  is the wrong slug.
- Only `title`, `description` and `taxonomy` are set, and `description` is empty
  on 337 pages.
- Some pages carry type markers as HTML comments: `<!-- TROUBLESHOOTING -->`
  ×22, `<!-- ADMINISTRATION -->` ×5, and others.

**Structure**

- 338 pages, flat.
- Filenames mix case and spaces; 29 files lack the `-gij-self-managed` suffix.
- The home page filename has a typo (`…-home-self-manged.md`).

**Overlap with Cloud.** 65 page slugs exist in both repos after normalizing the
filenames. Both repos share the `bbb-callout` markup and the integration-guide
logo list.

**Content mix** (counted by filename pattern)

| Kind | Pages |
| --- | --- |
| REST API endpoints | ~36 |
| Settings | ~32 |
| Troubleshooting (Problem / Diagnosis / Cause / Solution) | 29 |
| ScriptRunner examples | 16 |
| Javadoc class pages | 15 |
| Git host guides | ~13 |
| FAQ subpages | 12 |
| SSH | 12 |
| Webhooks | 11 |
| Bulk import/export | 9 |
| JMESPath | 7 |
| EOL policy | 6 |

There are near-duplicate families, for example 4 smart-commits pages and 2
Discard-cloned-files pages.

**Hubs**

- The home page uses the same layout as Cloud.
- `Documentation-gij-self-managed.md` has 32 `### [Title](url)` entries, each
  with a description.
- Other hubs: Integration Guides (13 hosts), Features (23), Troubleshooting
  (29), REST API (26), ScriptRunner (21).

**Patterns**

- **Callouts:** 563 `bbb-callout` divs (info 243, alert 124, tip 116, note 62,
  error 16, basic 2).
- **Code fences:** about 157 (json 53, java 41, powershell 32, groovy 16).
- **Tables:** 1,134 Markdown table lines; HTML tables in 6 files.
- **Video:** 49 Wistia iframes.
- **Spacing and inline styles:** `<br>` ×885, `&nbsp;` ×1,219, `style=` in 172
  files.
- **Headings start at H3:** H3 1,104 vs H2 513, plus H4 284.

**Largest pages**

| Page | Lines |
| --- | --- |
| ScriptRunner Javadoc Repository | 883 |
| IntegrationRequest | 610 |
| AWS CodeCommit | 545 |
| GitHub Enterprise Server | 543 |
| GitHub | 542 |
