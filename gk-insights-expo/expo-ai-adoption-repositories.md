---
title: By Repository
description: Read the By Repository page in GitKraken Insights to see which repositories are set up for AI-assisted work, which teams they belong to, and how much of their activity is AI-assisted.
product: GitKraken Insights
content_type: reference
audience: team-lead
plan_required: GitKraken Insights
integrations: [GitHub, GitHub Enterprise Server, GitLab, GitLab Self-Managed, Bitbucket, Bitbucket Data Center, Azure DevOps, Azure DevOps Server]
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: purple
    card_description: Repository AI readiness, teams, and AI activity
    card_icon: git-branch
    nav_category: getting-started
    nav_label: By Repository
    nav_order: 65
    page_type: content
---
<kbd>Last updated: October 2026</kbd>

The **By Repository** page shows how ready each repository is for AI-assisted work, with its actual AI activity laid over that readiness, across your whole organization. This page explains each part of the page and the readiness checks behind each repository's tier. It assumes your git provider is connected. To connect one, see [Connect Your Data](/insights-expo/expo-ai-adoption-connect-your-data). To change which teams a repository belongs to, or to turn checks off, see [Repositories settings](/insights-expo/expo-ai-adoption-settings#repositories-settings).

> **Who can see it:** By Repository is available to **Owners**, **Admins**, and **Managers**.

---

## Open By Repository

In the Insights sidebar, under **Agent Leaderboard**, click **By Repository**. The page opens with a filter bar, four KPI cards, and two tabs: **Repositories** and **Needs Attention**.

## Narrow the page with filters

The filter bar holds **Timeframe**, **Filters**, and **Teams**, plus these repository filters:

* **Active**, **Idle**, or **All**, to keep repositories with or without activity in the window.
* **Tier**, to keep repositories at the readiness tiers you choose. **Not scored** keeps repositories whose readiness checks are all turned off.
* **Language**, to keep repositories by their primary language.
* **Team**, to keep repositories that belong to the teams you choose. A repository that belongs to several teams appears under each of them.
* **AI activity**, to keep only repositories **With AI activity**.

**Export CSV** downloads the repositories you're viewing. A repository that belongs to several teams lists every team in the export.

## Read the KPI cards

Four cards summarize the repositories in the current scope:

* **Repos tracked** — repositories with a readiness assessment. With filters active, the card reads **Showing N of M (filtered)**.
* **Avg readiness score** — the mean readiness score, from 0 to 100.
* **% Exemplary or Good** — the share of repositories at the Exemplary or Good tier.
* **% with AI activity** — the share of repositories where at least 20% of pull requests in the window were AI-assisted.

## Read the Repositories tab

The **Repositories** tab opens with the **Readiness × AI activity** chart. Each dot is a repository: its readiness score runs along the horizontal axis, and the AI-assisted share of its activity up the vertical axis. Dot size grows with activity volume, and the color shows the primary team. Repositories with no activity in the window, and repositories with every check turned off, are left out. The chart has four quadrants:

* **WORKING** — high readiness and high AI activity.
* **AT RISK** — high AI activity in a repository that isn't set up for it.
* **WASTED PREP** — a ready repository that sees little AI activity.
* **COLD** — low readiness and little AI activity.

Below the chart, the **All repos** table lists every repository. Use **Filter by repo** to find one by name, and **Columns** to show, hide, or resize columns. The table has these columns:

| Column | What it shows |
| --- | --- |
| **Repo** | The repository's owner and name, which opens the repository on its host where available. Archived repositories are flagged but still included. |
| **Provider** | Where the repository is hosted: GitHub, GitLab, Bitbucket, or Azure DevOps, cloud or self-hosted. A dash means the provider isn't known. |
| **Lang** | The primary programming language. |
| **Team** | Every team the repository belongs to. See [Which teams a repository belongs to](#which-teams-a-repository-belongs-to). |
| **Repo Tier** | The readiness tier: **Exemplary**, **Good**, **Basic**, or **Not Ready**. Hover the cell for the score of each check group. |
| **Assessment** | One badge per readiness check, passing or missing. Hover a badge for what the check verifies and how to fix it. |
| **AI activity** | The share of the repository's pull requests and direct commits in the window that were AI-assisted, excluding bot authors, with the volume underneath. Hover for the split by pull requests and direct commits. |
| **Last active** | The most recent pull request or direct commit on the default branch in the window. |

<figure>
  <img src="/wp-content/uploads/gki-repositories-table-01-v3@2x.png" class="help-center-img img-bordered" alt="The All Repos table on the By Repository page in GitKraken Insights, under Timeframe, Filters, Teams, Active / Idle / All, Tier, Language, Team and AI activity filters and Export CSV, listing example-org repositories by readiness with their Provider (GitHub), Lang, Team, Repo Tier (three Exemplary, then Good), Assessment check badges with passing checks in green and missing ones in red, AI Activity and Last Active" />
  <figcaption>The All repos table on By Repository.</figcaption>
</figure>

The readiness tier comes from a readiness score from 0 to 100: Exemplary at 80 and above, Good at 55 and above, Basic at 25 and above, and Not Ready below 25. The score weights AI instructions at 40%, dev hygiene at 30%, and content quality at 30%.

### Which teams a repository belongs to

A repository can belong to several teams. The **Team** column lists every one. Teams that an organization admin assigns in [Settings → Repositories](/insights-expo/expo-ai-adoption-settings#team-assignment) are authoritative. With no team assigned, the repository falls back to the team whose developers contributed the most pull requests and direct commits over the past 90 days. That team is worked out at sync time and doesn't follow the dashboard's date picker. A repository with no team shows a dash.

The **Team** filter and the page's **Teams** filter match every team a repository belongs to, so a shared repository appears under each of its teams.

## Check a repository's readiness

Click a repository's row to expand its **Readiness breakdown**. The header shows the repository's team or teams, its tier, and its score. Below it, the checks are grouped in three sections, each with a count of passing checks:

* **AI Instructions** — CLAUDE.md, AGENTS.md, .cursor/rules, or Copilot instructions. Quality matters more than quantity.
* **Dev Hygiene** — CI/CD, README, CODEOWNERS, and branch protection: the safety net for merging AI-assisted changes quickly.
* **Content Quality** — test config, lint config, and custom skills or commands: what AI tools read to validate their output.

Click a check to see why it matters and an **Action item** for fixing it. For an AI instruction file, the breakdown also shows its best quality score.

The **Closest to next tier** card suggests the checks that would move the repository to the next tier. A repository already at the top tier reads **Already at the top tier — keep it up**.

<figure>
  <img src="/wp-content/uploads/gki-repositories-readiness-checks-01-v3@2x.png" class="help-center-img img-bordered" alt="An expanded repository row on the By Repository page in GitKraken Insights: example-org/refunds-service, team Payments, Good at 56.8/100, with its Readiness breakdown of AI Instructions (AI Instructions passing), Dev Hygiene (CI/CD and README passing, CODEOWNERS and Branch Protection failing) and Content Quality (Test Config and Skills / Commands passing, Lint Config failing), and a Closest to next tier card listing Lint Config (+12), CODEOWNERS (+10) and Branch Protection (+5), 23.2 points short of Exemplary" />
  <figcaption>A repository's Readiness breakdown, with one check missing.</figcaption>
</figure>

### What each readiness check looks for

| Check | Group | What it looks for |
| --- | --- | --- |
| **AI Instructions** | AI Instructions | At least one AI instruction file: CLAUDE.md, agents.md (any case), .cursor/rules, .cursorrules, or .github/copilot-instructions.md. |
| **CI/CD** | Dev Hygiene | A CI workflow, such as .github/workflows/*.yml. |
| **README** | Dev Hygiene | A README.md. |
| **CODEOWNERS** | Dev Hygiene | A CODEOWNERS file, at the root or in .github. |
| **Branch Protection** | Dev Hygiene | Branch protection on the repository. |
| **Test Config** | Content Quality | A test framework config, such as vitest.config.*, jest.config.*, pytest.ini, a Go test setup, or Elixir's test_helper.exs. |
| **Lint Config** | Content Quality | A linter or formatter config, such as eslint.config.*, .prettierrc, biome.json, golangci-lint, or Elixir's .formatter.exs. |
| **Skills / Commands** | Content Quality | Skill or command definitions for the agent tool your team uses, in .claude/commands, .claude/skills, .claude/agents, .cursor/commands, .cursor/skills, or .agents. |

Skills managed outside the repository, such as in dev containers or user-level config, can't be detected. For those repositories, an organization admin can turn the **Skills / Commands** check off under **Settings → Repositories → Edit checks**. A check that's turned off no longer counts toward the repository's tier. A repository with every check turned off shows as **Not scored**. See [Readiness checks](/insights-expo/expo-ai-adoption-settings#readiness-checks).

## Find repositories that need attention

The **Needs Attention** tab lists the repositories missing the basics, in two panels:

* **Missing AI Instructions** — repositories without CLAUDE.md, AGENTS.md, .cursor/rules, or Copilot instructions.
* **Missing Dev Hygiene** — repositories missing branch protection, CODEOWNERS, CI, or a README.

Each entry says what's missing, so you can hand the fix to the repository's owners.

## Related pages

| Page | What it covers |
| --- | --- |
| [Repositories settings](/insights-expo/expo-ai-adoption-settings#repositories-settings) | Assign teams to repositories and turn readiness checks on or off |
| [Reading Your First Dashboard](/insights-expo/expo-ai-adoption-first-dashboard) | The Repos tab on each team's row |
| [AI-Assisted Percentage](/insights-expo/expo-ai-adoption-impact-cost-ai-assisted-percentage) | How Insights decides that work is AI-assisted |
