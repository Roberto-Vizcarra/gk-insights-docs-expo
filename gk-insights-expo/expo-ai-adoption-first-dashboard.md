---
title: Reading Your First Dashboard
description: A guided walkthrough of the /teams page in GitKraken Insights — what every column means, how to read team tiers and scores, and where to click next.
product: GitKraken Insights
content_type: how-to
audience: all
plan_required: GitKraken Insights
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: purple
    card_description: Guided walkthrough of the /teams page
    card_icon: layout-dashboard
    nav_category: getting-started
    nav_label: First Dashboard
    nav_order: 10
    page_type: content
---
<kbd>Last updated: October 2026</kbd>

<!-- NOTE: The Confluence Glossary page was intentionally not migrated. All terms it defines are documented in their dedicated metric pages; the cross-cutting terms (active developer, window, P90, Maturity Factor) are summarized in the "Glossary refresher" section below. -->

## Reading your first dashboard

A guided 10-minute walkthrough of `/teams`, the page you land on when you log in. By the end you'll know what every column means and where to click next.

### Set the scope first

Before you read anything, set two filters at the top of the page:

1. **Date range.** Default is the last 7 days. For a two-week review, switch to 14 days. For a quarter-end review, switch to 90 days. Sub-7-day windows force some rates to zero, so don't pick "yesterday" expecting full data. Rolling ranges such as **Last 14 days** end on the last day your data has fully synced and keep their length, so a range can end a day or two before today. When that happens, an info icon beside the date picker shows the **Data through** date.
2. **Department or team.** Open **Filters** → **Team** and pick the slice of the org you care about. A filter shows on the bar as a chip only once it's set. If you have a `Default Department` set in Settings, this comes pre-filled.

Everything below this point will reflect those two filters.

**Surface toggle (Claude Cowork orgs).** Once your organization's Claude Code telemetry includes Claude Cowork activity, the Teams and Developers pages also show a **Surface** toggle: **All**, **Claude Code**, or **Cowork**. It narrows only the expanded developer detail; scores and tables always include all activity.

### The team table — column by column

The main view is one row per team, under a strip of four KPI cards:

* **Avg Adoption** — the mean Agent Adoption Score across teams, where each team is the average of its members' scores.
* **Avg Autonomy** — the same for Agent Autonomy Score.
* **Avg Output** — the mean Output Score across teams, as a per-working-week rate.
* **% Power Teams** — the share of teams whose tier is Power User. Teams with every member on PTO are left out.

<figure>
  <img src="/wp-content/uploads/gki-teams-table-01-v3@2x.png" class="help-center-img img-bordered" alt="The Teams page in GitKraken Insights with Timeframe, Granularity, Filters and Teams filters, KPI cards for Avg Adoption, Avg Autonomy, Avg Output and % Power Teams, and the All Teams table listing Platform, Payments, Growth and Mobile with their Tier, Agent Adoption Score, Agent Autonomy Score, Output Score /wk and Token Usage Value" />
  <figcaption>The Teams page: the KPI strip above the team table.</figcaption>
</figure>

Use **Filter by name** above the table to find a team. Here's what each column tells you:

#### Team

The team name and its developer count. Every score in the row is aggregated from these members.

#### Tier

The team's tier, from the average of its members' composite tier scores (Agent Adoption, Agent Autonomy, and Output, weighted as configured in Settings → General). Members on PTO are left out. The bands are the same as for developers: Power User (80 and above), Regular (55 to 79), Explorer (25 to 54), and Emerging (below 25).

→ [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier)

#### Agent Adoption Score

The average Agent Adoption Score of the team's members. A team average of 50 means a healthy mix; 80+ means a high-adoption team.

→ [Agent Adoption Score](/insights-expo/expo-ai-adoption-agentic-adoption-score)

#### Agent Autonomy Score

The average Agent Autonomy Score of the team's members: how autonomously they work with AI agents.

→ [Agent Autonomy Score](/insights-expo/expo-ai-adoption-agentic-autonomy-score)

#### Output Score /wk

Effort-weighted shipping rate per working week. A team that ships fewer but bigger PRs can have the same Output Score as a team that ships many small ones.

→ [Output Score](/insights-expo/expo-ai-adoption-output-output-score)

#### Token Usage Value

AI API spend across your connected AI coding tools over the selected window.

#### AI Credits Used

Shown when your organization uses GitHub Copilot and has credit data. The premium-model credits the team's members consumed through Copilot in the window, as reported by GitHub, with input and output token counts below when GitHub reports them.

**Choose and size columns.** Open **Columns** above the table to show or hide columns; **Show all** brings every column back. Drag a column edge to resize it, and use **Reset widths** to undo. Your browser remembers the choices for each table. The same **Columns** menu appears on the other Insights tables, such as Developers and Repositories, and in drill-downs. Hiding and resizing aren't available on narrow screens.

Below the table, the **Score Tiers** card and the score explainers show how the tiers and scores are calculated.

### Expand a row to drill in

Click any team row to expand it.

**Team Adoption Insight.** Above the tabs, the **Team Adoption Insight** card writes a short AI-generated read of the team's adoption profile when you click its lightbulb. It appears only when GitKraken AI features are turned on for your organization and the team has enough developers for a meaningful summary.

Below the card, the row has four tabs:

#### Repos

Per-repo readiness, with each repository's score, readiness tier, and assessment checks.

**Which repositories belong to a team.** A repository belongs to the teams an organization admin assigned it to. With no team assigned, it falls back to the team whose developers contributed the most pull requests and direct commits to it over the past 90 days. One repository can belong to several teams. When a shared repository's releases, change failure rate, and bug escape rate count toward another team, the Repos tab marks it **Attributed to** that team; it still counts as one of this team's repositories for ownership, readiness, and coverage. Repositories assigned to the team that have never been scanned for readiness appear separately under **Assigned, not yet assessed**.

#### Developers

The full developer roster for that team with individual Adoption Scores, Output Scores, AI Tier, and a Direct Commits column. Click any developer to drill further into their profile (this navigates to `/developers`).

#### System Metrics

Cycle Time + PR Volume trends for the team. Use the dimension dropdown to break either chart down by author, AI tier, or PR category.

#### Releases

The team's releases.

### What to look at first

In order, every time:

1. **Tier column and % Power Teams.** Are any teams sitting at Explorer or Emerging? That's the leading edge of an adoption problem. Expand the row and check the Developers tab to see who is pulling the average down.
2. **Agent Adoption Score vs Output Score /wk.** A team with high adoption and low output (or vice versa) deserves a conversation.
3. **Cycle Time outliers.** Expand a team and open System Metrics. Anyone over 4 days? That's almost always a review-bottleneck or WIP problem, not a coding problem.
4. **Repos tab gaps.** Repositories under **Assigned, not yet assessed** have no readiness score yet, and repositories with failing checks usually mean a missing integration or config — quick to fix.

### Where to go next

Now that you've read /teams, here are the obvious follow-up pages depending on what you saw:

| If you saw … | Go to … |
| --- | --- |
| A team with low adoption | /developers, filtered to that team — find the Emerging cohort |
| A team with slow cycle time | /flow-delivery → Flow tab → switch dimension to "phase" |
| A team with high output but low AI | /comparison → compare that team to a high-AI peer |
| A team you don't recognize | /settings → Teams — check the roster |
| An exec asking "what's the ROI?" | /ai-impact and /executive |

### What the dashboard won't show you (and why)

* **Code quality at the line level.** We don't run static analysis or measure code health beyond cycle metrics. Use your existing tools (SonarQube, Code Climate, etc.) for that.
* **Sentiment.** A team can have great numbers and miserable morale. The dashboard is one input — your one-on-ones are another.
* **Pre-2026-03-05 AI usage.** Claude Code OTEL instrumentation started March 5, 2026. Anything earlier is undercounted.

### Glossary refresher

* **Active developer:** is_active = true _and_ shipped at least one PR or direct commit in the window _and_ wasn't fully on PTO.
* **Window:** the date range you selected at the top.
* **P90:** 90th-percentile cap computed across your whole active org. Sets the ceiling for normalized scores.
* **Maturity Factor:** an org-wide scaling knob, set as **Company AI Readiness (%)** in Settings (default 100%). At 100%, a P90 developer scores 100; at 75%, they score 75. Adjusts everyone's tier ceiling.
