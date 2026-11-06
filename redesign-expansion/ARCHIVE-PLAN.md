# Archive Plan: Retired Help Center Pages

> **Status:** Mapping only (2026-11-06). Nothing has been moved, renamed or
> unpublished. This must be executed before frontmatter is added for the
> redesign, but not until the redesign expansion starts.
>
> **Evidence:** the live nav audit in [nav-audit/](nav-audit/), run
> 2026-11-06. Before executing, re-run the audit and regenerate the
> dispositions; the site and repos will have changed.

## Why

Pages on the help centers have been retired by unlinking them from the sidebar
nav, not by removing them. Every docs repo therefore contains pages that are
still published but that readers can no longer find. The redesign builds its
nav from frontmatter, so each repo must first contain only the pages that are
meant to be live. Otherwise retired pages would reappear in the generated nav.

A page wrongly archived is lost content. Every rule below errs toward KEEP or
DECIDE.

## Rules

A page is **live** if a reader can reach it directly from the help center:

1. **It is a sidebar nav entry** in any of the eight help centers.
2. **It is linked directly from a page that is a sidebar nav entry** (one
   click from a nav page).

| Disposition | When | Action when executed |
| --- | --- | --- |
| **KEEP** | Live by rule 1 or 2 | None. Gets redesign frontmatter later |
| **KEEP (exemption)** | Page template files (`*-New-Page-Template.md`, `GIJ-Cloud-New-Doc.md`) | None |
| **ARCHIVE** | Not in any nav and not linked from any nav page | Rename to `ARCHIVED-<name>.md` and move to `archive/<content folder>/` at the repo root |
| **DECIDE** | The rules cannot settle it safely (see below) | A person decides; then KEEP or ARCHIVE |

A file is sent to **DECIDE**, never guessed, when:

- **Slug collision.** It shares its base slug with another live post (WordPress
  added `-2`, `-15`, …), so which post it updates must be confirmed in WP Admin.
- **Redirect.** Its URL redirects elsewhere, so its own content isn't served.
- **Test page.** It is a format or test page (`GL-Test.md`, `Testing.md`).
- **Unaudited category.** It publishes under a category outside the eight
  audited help centers, such as `general`.
- **Two clicks away.** It is linked only from pages that are themselves KEEP.
  Readers can reach it, but not directly from a nav page.

## Result (2026-11-06)

| Help center | Repo | Files | KEEP | DECIDE | ARCHIVE |
| --- | --- | --- | --- | --- | --- |
| GitKraken Desktop | gitkraken-desktop-docs | 94 | 89 | 1 | 4 |
| GitLens | gitlens-docs | 37 | 35 | 1 | 1 |
| Kepler | kepler-docs | 32 | 30 | 0 | 2 |
| GitKraken MCP | MCP-Docs | 6 | 6 | 0 | 0 |
| GitKraken CLI | gk-cli-docs | 10 | 9 | 1 | 0 |
| gitkraken.dev | gk-dev-docs | 17 | 17 | 0 | 0 |
| Git Integration for Jira Cloud | git-integration-for-jira-cloud-docs | 145 | 130 | 4 | 11 |
| Git Integration for Jira Data Center | git-integration-for-jira-self-managed-docs | 338 | 335 | 0 | 3 |
| **All** | | **679** | **651** | **7** | **21** |

The per-file list, with each file's reason, live post ID, URL, inbound links
and proposed archive path, is in
[nav-audit/output/2026-11-06/dispositions/DISPOSITIONS.md](nav-audit/output/2026-11-06/dispositions/DISPOSITIONS.md)
(and `dispositions.csv` beside it).

### The 7 DECIDE files

| File | Why it can't be decided by rule |
| --- | --- |
| `gitkraken-desktop/GK-soc2-request.md` | Publishes under `general`, a category this audit doesn't cover |
| `gitlens/GL-Test.md` | Test page: titled "GitLens Release Notes", opens with a "Test Page" heading, 3,512 lines (a release-notes copy) |
| `cli/GK-CLI-mcp.md` | Its URL redirects to the MCP help center. The CLI nav item "How to Use GitKraken CLI with AI Coding Agents" points at that redirect, so the file's content isn't served |
| `git-integration-for-jira-cloud/connected-apps-gij-cloud.md` | Two clicks from the nav (via `default-branch-feature`, `user-settings`) |
| `git-integration-for-jira-cloud/default-repository-feature-gij-cloud.md` | Two clicks from the nav (via `default-branch-feature`, `user-settings`) |
| `git-integration-for-jira-cloud/personal-access-token-feature-gij-cloud.md` | Two clicks from the nav (via `user-settings`) |
| `git-integration-for-jira-cloud/Testing.md` | Format test page, `status: draft` |

## Procedure (when executed)

Do this per repo, one repo at a time.

1. **Re-run the audit and regenerate the dispositions** (see
   [nav-audit/README.md](nav-audit/README.md)). Work only from a fresh run.
2. **Resolve every DECIDE file**, and record each decision in
   [PER-PRODUCT-DECISIONS.md](PER-PRODUCT-DECISIONS.md).
3. **Confirm the Git It Write settings for the repo** in WP Admin:
   - which branch it publishes from (the audit assumed `main`)
   - which folder it syncs
   `archive/` must be outside the synced folder, or archived files would be
   published again.
4. **Confirm the six slug-collision files** in WP Admin (listed under "Slug
   collisions" in the run's `REPORT.md`), so the post each one updates is
   known.
5. **Move each ARCHIVE file** with `git mv <folder>/<name>.md
   archive/<folder>/ARCHIVED-<name>.md`. Keep its images and frontmatter
   unchanged.
6. **Handle the live post.** Moving a file out of the synced folder does not
   remove its WordPress post. Assume Git It Write never deletes posts, and
   verify that before relying on it. For each archived file's post ID, set the
   post to draft (or private) and add a 301 redirect from its URL to the most
   relevant live page. Links from outside the help centers (in-app help,
   gitkraken.com, blog, email) can't be seen by the audit, and the redirect
   keeps them working.
7. **Regenerate the repo's index files** (`llms.txt`, `llms-full.txt`,
   `sitemap.xml`) so they no longer list archived pages.
8. **Re-run the audit.** Every KEEP page must still be live and every nav item
   unchanged. Then open the PR.

## Outside the repos (found by the audit, not part of the archive)

These are live WordPress posts with no repo file. Archiving repo files does
not touch them, but the redesign has to deal with them.

- **Nav items pointing to posts with no repo file: 7.**
  - Desktop: "Review and Undo Agent Changes" (post 20312, the `-2` copy).
  - Data Center: the Javadoc classes "ChangeStr" and "CommitIssueChange", the
    `-15` copies of "Class Branch" and "Class PullRequest", "Git roll up issue
    tab", and "Git Services Support Page".

  The plugin builds the nav from frontmatter, so each of these needs a repo
  source, or a decision to drop it from the nav.
- **WordPress-only published posts.**

  | Help center | Posts |
  | --- | --- |
  | GIJ Cloud | 115 (none in the nav; most dated 2025-07-02) |
  | GIJ Data Center | 32 |
  | Desktop | 5 |
  | CLI | 3 |
  | GitLens | 2 |
  | Kepler | 1 |
  | gitkraken.dev | 1 |

  Lists are in each product's `wp-posts.csv`. They need their own cleanup
  decision.
- **1,626 duplicate posts in GIJ Data Center.** "Class Branch" and "Class
  PullRequest" each have 813 published copies, created from July 2022 to
  March 2026. Git It Write appears to create a new copy on each sync. The two
  repo files have upper-case letters in their names, which is a likely
  trigger. Fix the cause, then unpublish the copies.
- **Template pages are publicly published** (for example
  `/gitkraken-desktop/gkd-new-page-template/`). They are KEEP by exemption.
  Consider moving templates out of the synced folders, or keeping their posts
  as drafts.
- **Nav items that cross help centers: 2.**
  - The GIJ Cloud nav links to a Data Center page ("Removing integration or
    repository configuration").
  - The Data Center nav links to a Cloud page ("How to Create Reindex Triggers
    for a Single Repository").
- **A file filed under the wrong category.**
  `when-a-license-expires-a-session-error-is-shown-to-the-user-gij-self-managed.md`
  sits in the Data Center repo but publishes under the Cloud category. It is
  KEEP, because it is linked from a nav page.

## Open questions

- [ ] Do the archive rules also apply to the WordPress-only posts, or only to
      repo files?
- [ ] Should "two clicks from the nav" pages be KEEP in general?
- [ ] Should the archive naming be `ARCHIVED-<name>.md`, or should the
      original name be kept inside `archive/`?
- [ ] Which page should each archived URL redirect to?
