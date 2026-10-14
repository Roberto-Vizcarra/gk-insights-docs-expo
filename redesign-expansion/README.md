# Redesign Expansion: Every GitKraken Help Center

> **Status:** Deferred (2026-10-14). Do not start until the Insights redesign is
> live on `/insights-expo/` and stable.
> **Decision so far:** extend GKI Docs Helper as **one plugin with a per-product
> registry**. Do not fork a second plugin.

This folder catalogs the exploration into applying the Insights help center
redesign to the other GitKraken help centers. Nothing here is built, and nothing
in this folder is read by the plugin build (`build-plugin.py` packages only
`gki-docs-helper/`) or by Git It Write, which syncs only `gk-insights-expo/`.

## Contents

| File | What it is |
| --- | --- |
| [PROPOSAL.md](PROPOSAL.md) | The proposal: decision, scope, treatment for each product, phases, risks |
| [PLUGIN-REFACTOR-SPEC.md](PLUGIN-REFACTOR-SPEC.md) | Technical spec for the registry refactor, content adapters, caching, frontmatter contract and acceptance criteria |
| [CONTENT-SURVEY.md](CONTENT-SURVEY.md) | Detailed survey of each repo: slugs, frontmatter, structure, markup patterns, largest pages, conflicts |
| [PER-PRODUCT-DECISIONS.md](PER-PRODUCT-DECISIONS.md) | Deferred decisions for each product (slugs, nav sections, home layout, components), with the proposed values |
| [artifacts/redesign-rollout-exploration.md](artifacts/redesign-rollout-exploration.md) | Local copy of the exploration summary doc |
| [artifacts/help-center-mockups.html](artifacts/help-center-mockups.html) | Local copy of the interactive mockups (open in a browser): a landing page and a content page for each product |

## Online originals

- Exploration summary (Claude Doc): https://claude.ai/code/artifact/c41a9f41-9e32-44d1-b0a5-88c14de703c8
- Mockups (Artifact): https://claude.ai/artifact/5vHi2weX6UQgwPKSN8c9nq

Both are private to their owner until shared. The local copies in `artifacts/`
are the snapshot of record from 2026-10-14.

## Repos surveyed

| Help center | Repo | Category slug |
| --- | --- | --- |
| GitKraken Desktop | gitkraken-desktop-docs | `gitkraken-desktop` |
| GitLens | gitlens-docs | `gitlens` |
| Kepler | kepler-docs | `kepler` |
| GitKraken MCP | MCP-Docs | `MCP` |
| GitKraken CLI | gk-cli-docs | `cli` |
| gitkraken.dev | gk-dev-docs | `gk-dev` |
| Git Integration for Jira Cloud | git-integration-for-jira-cloud-docs | `git-integration-for-jira-cloud` |
| Git Integration for Jira Data Center | git-integration-for-jira-self-managed-docs | `git-integration-for-jira-data-center` |

## When this resumes

1. Re-run the counts in `CONTENT-SURVEY.md`, because the repos will have moved.
2. Re-check the line references in `PLUGIN-REFACTOR-SPEC.md` against the
   current plugin version.
3. Work through `PER-PRODUCT-DECISIONS.md` for the pilot product (Kepler is the
   proposed pilot).
4. Follow the phases in `PROPOSAL.md`.
