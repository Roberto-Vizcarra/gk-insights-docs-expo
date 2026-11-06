---
title: By Developer
description: Read the Developers page in GitKraken Insights, from the filter bar and the All Developers table to the Overview, Agent Adoption, AI Sessions, Developer Environment, and System Metrics tabs of each developer.
product: GitKraken Insights
content_type: reference
audience: team-lead
plan_required: GitKraken Insights
integrations: [Claude Code, Codex, Cursor, GitHub Copilot, Devin, OpenCode]
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: purple
    card_description: Every developer's adoption, autonomy, output, and AI spend
    card_icon: user
    nav_category: getting-started
    nav_label: By Developer
    nav_order: 60
    page_type: content
---
<kbd>Last updated: October 2026</kbd>

The **Developers** page shows how each developer is adopting AI agents: daily use, autonomy in agentic workflows, delivered output, and AI spend. This page explains each part of the Developers page and how to read it, from the table to the detail you see when you expand a developer. It assumes your git provider and AI tools already report to Insights. To connect them, see [Connect Your Data](/insights-expo/expo-ai-adoption-connect-your-data). For how each score is calculated, see the metric pages linked from each section.

> **Who can see it:** The Developers page is available to **Owners**, **Admins**, and **Managers**. Other members see their own figures on [GitKraken Insights for Developers](/insights-expo/expo-ai-adoption-insights-for-developers).

The Developers page is a coaching view, not a ranking. Use it to find who needs help forming an AI habit and who is ready for bigger agent tasks, not to slot people on a curve.

---

## Open the Developers page

In the Insights sidebar, under **Agent Leaderboard**, click **By Developer**. The page opens with a filter bar, four KPI cards, an insight banner, and the **All Developers** table. Below the table, three explainer cards describe the scores: **Score Tiers**, **How is the Agent Adoption Score calculated?**, and **What is the Agent Autonomy Score?**

<figure>
  <img src="/wp-content/uploads/gki-developers-table-01-v3@2x.png" class="help-center-img img-bordered" alt="The Developers page in GitKraken Insights with Timeframe, Granularity, Filters and Teams filters, KPI cards for Avg Adoption, Avg Autonomy, Avg Output and % Power Users, an Opportunity, Risers and Fallers banner, and the All Developers table listing ten developers with their Team, Tier, Agent Adoption Score, Agent Autonomy Score, Output Score /wk, Token Usage Value and $ / Output" />
  <figcaption>The Developers page: filter bar, KPI cards, insight banner, and the All Developers table.</figcaption>
</figure>

## Narrow the page with filters

The filter bar sets the scope for everything on the page. It holds **Timeframe**, **Granularity**, **Filters**, and **Teams**, plus **Export CSV**, which downloads the developer rows you're viewing. When any filter is active, **Clear all** resets them.

Open **Filters** to add a filter. The menu lists these filters:

* **Team**, **Org**, **Workspace**, and **Repositories**, which narrow the page to part of your organization.
* **Developer**, which picks individual developers.
* **AI Tier**, which keeps developers in the tiers you choose.
* **AI Tool**, which keeps developers by the combination of AI tools they used in the window, such as **Claude Code** on its own or **Claude + Cursor**. The cohorts follow the same tool combinations as [By AI Tool & Model](/insights-expo/expo-ai-adoption-ai-tools-comparison). Cohorts that include Devin or OpenCode appear only for organizations where those tools are enabled. Devin and OpenCode are available on request — contact your GitKraken account team.
* **AI Model**, which keeps a developer when they used any of the models you select in the window, not only their main model. **None** keeps developers with no model data.

<figure>
  <img src="/wp-content/uploads/gki-developers-filters-01-v3@2x.png" class="help-center-img img-bordered" alt="The Filters dropdown on the Developers page open below the filter bar, listing Team (checked), Org, Workspace, Repositories, Developer, AI Tier, AI Tool and AI Model, beside the Timeframe, Granularity and Teams filters" />
  <figcaption>The Filters menu on the Developers page.</figcaption>
</figure>

If your organization's Claude Code telemetry includes Cowork activity ([connect Cowork](/insights-expo/expo-ai-adoption-connect-otel#claude-cowork)), a **Surface** control also appears, with **All**, **Claude Code**, and **Cowork**. Choosing a surface narrows only the detail you see when you expand a developer. Scores and tables still include activity from every surface. Detail panels that can't be split by surface say **Includes activity from all surfaces**, and the Agent Adoption Insight card is hidden while a surface is selected.

## Read the KPI cards and the insight banner

Four KPI cards summarize the developers in the current scope:

* **Avg Adoption** — the mean [Agent Adoption Score](/insights-expo/expo-ai-adoption-agentic-adoption-score).
* **Avg Autonomy** — the mean [Agent Autonomy Score](/insights-expo/expo-ai-adoption-agentic-autonomy-score).
* **Avg Output** — the mean [Output Score](/insights-expo/expo-ai-adoption-output-output-score) per working week, with its normalized value, out of 100, beside it.
* **% Power Users** — the share of active developers in the Power User [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier).

The insight banner below the cards has three blocks. Click a developer's name in any block to jump to that developer's row in the table:

* **Opportunity** estimates the AI-assisted capacity your sub-Regular developers leave unused, in hours per week, if they reached Regular adoption. The estimate uses the hours-saved figure from [Productivity Uplift](/insights-expo/expo-ai-adoption-impact-cost-productivity-uplift).
* **Risers** lists the developers whose tier score improved most against the previous window, plus **New adopters**.
* **Fallers** lists the developers whose tier score dropped, under **Check in with**.

When **Opportunity** has no estimate, the block says why:

* Everyone in the view is on time off for the period.
* Everyone in the view is at Regular adoption or above, so no untapped capacity remains.
* The baseline month has no delivery activity yet. If you just connected, the estimate fills in once the first sync finishes. Otherwise, an organization admin can pick a different [Baseline Period](/insights-expo/expo-ai-adoption-settings#baseline-period) in AI Adoption settings.
* Delivery pace in the period is at or below your baseline month, so no measurable capacity is left to unlock.

Without previous-window data, **Risers** and **Fallers** read **No prior-period data to compare**.

## Read the developers table

The **All Developers** table has one row per developer. Use **Filter by name** to find someone by name or email. When a filter or search is active, a count shows how many developers match. The table has these columns:

| Column | What it shows |
| --- | --- |
| **Developer** | The developer's name. A row labelled **Automation** collects activity from automated accounts; it has no tier or scores and doesn't expand. |
| **Team** | The developer's team. |
| **Tier** | The developer's [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier), from a weighted blend of Agent Adoption, Agent Autonomy, and Output. |
| **Agent Adoption Score** | The [Agent Adoption Score](/insights-expo/expo-ai-adoption-agentic-adoption-score), from 0 to 100. |
| **Agent Autonomy Score** | The [Agent Autonomy Score](/insights-expo/expo-ai-adoption-agentic-autonomy-score), from 0 to 100. |
| **Output Score /wk** | The [Output Score](/insights-expo/expo-ai-adoption-output-output-score) per working week. Click it to open the breakdown of the pull requests and commits behind it. |
| **Token Usage Value** | AI API spend across your connected AI coding tools in the selected window. |
| **AI Credits Used** | Copilot premium-model credits in the window, as reported by GitHub, with input and output token counts below when GitHub reports them. The column appears only when your organization uses GitHub Copilot and has credit data. |
| **$ / Output** | AI API spend per point of output the developer shipped in the window. |

Hover a score for its breakdown by AI tool. For GitHub Copilot, the breakdown labels which source scored it: **OTEL** or **PAT**.

Read **$ / Output** as cost efficiency: a lower figure means more shipped effort per AI dollar. **$0.00** means the developer shipped output without billed AI usage. A dash means no scored output for the window, for example a developer on time off for the whole window or the Automation row. Very small ratios read **<$0.01**. **$ / Output** is also in the CSV export.

### Show, hide, and resize columns

Click **Columns** above the table to choose which columns to show. The menu reads **Visible columns** with a count, and a checkbox for each column you can hide. After you hide a column, **Show all** brings every column back, and **Reset widths** restores the default widths. To resize a column, drag the edge of its header. Your browser remembers the visible columns and widths for this table. On narrow screens, columns can't be hidden or resized.

<figure>
  <img src="/wp-content/uploads/gki-developers-columns-01-v3@2x.png" class="help-center-img img-bordered" alt="The Columns menu open over the All Developers table, showing Visible columns 7 of 7 with checked boxes for Team, Tier, Agent Adoption Score, Agent Autonomy Score, Output Score /wk, Token Usage Value and $ / Output (the longer names truncated), above the table header and the first three developer rows" />
  <figcaption>The Columns menu above the All Developers table.</figcaption>
</figure>

## Expand a developer

Click a developer's row to expand it. The expanded detail has up to five tabs:

1. **Overview** — the developer's scorecard. Always shown.
2. **Agent Adoption** — activity trends for each AI tool. Shown when at least one tool has trend data in the window.
3. **AI Sessions** — the developer's AI coding sessions and skill use. Always shown.
4. **Developer Environment** — how each AI tool is set up on the developer's machines. Shown when environment data exists.
5. **System Metrics** — pull request cycle time and volume. Always shown.

The detail opens on **Overview**. If the tab you're on disappears after you change the timeframe, the detail returns to **Overview**. The same expanded detail opens when you expand a developer from a team's **Developers** tab on the [Teams page](/insights-expo/expo-ai-adoption-first-dashboard).

### Overview tab

The **Overview** tab is a coaching scorecard. It opens with the **Agent Adoption Insight** card. Click the bulb to generate an AI-written read of the developer's tier, what drives it, and where to nudge next. Insights generates the read only when you click. When the developer's activity is too sparse, the card says there isn't enough adoption signal to summarize.

> **Note:** AI-generated insight cards appear only when your organization has AI features enabled. If an organization admin has turned AI features off, the card is hidden.

Below the insight card, the tab shows these panels:

* **Activity heatmap.** One cell per day, shaded by activity. The heading names the span the data covers: **Activity (last N days)** when the data reaches today, or a date range when the latest sync trails behind. Days off show as **Day off**.
* **Cost Breakdown.** The developer's total spend for the window, split by model under **By Model**. The total matches the developer's **Token Usage Value** in the table. Spend that a tool reports without a per-model split appears as **Other**. Only tools with spend in the window appear. GitHub Copilot connected through a personal access token reports only an estimated seat-level cost, so it adds to the Copilot total without a per-model split. Copilot cost appears only when your organization has a GitHub Copilot or Agent OTel Data connection.
* **Agent Adoption**, **Agent Autonomy**, and **Output** cards, each with its share of the tier score and a short explanation of what it measures.
* **Tier** and the score list. The **Tier** row shows the developer's tier and score out of 100. Below it, **Agent Adoption**, **Agent Autonomy**, and **Output /wk** each show a short verdict and a tick for the org's 90th percentile (P90). Click any score to see the method behind it.

The detail pane changes with the score you click:

* **Tier** shows the **Composite Tier** math: each score, its weight, and the points it adds to the tier score. For Output, it also shows the developer's output rate, the org benchmark, their ratio, and the resulting output norm. The weights are set in [Settings → General](/insights-expo/expo-ai-adoption-settings#tier-weights).
* **Agent Adoption** and **Agent Autonomy** show the breakdown of each score, tool by tool.
* **Output** shows the effort from authored pull requests, direct commits, and reviews, the working weeks after time off, and the resulting score per week. A note says whether chores are excluded, which you set in Settings.

A developer on time off for the whole window shows **Fully on PTO for the selected window — scores are paused** instead of scores. **Key terms in the math** explains the terms the panels use, such as tool weight, org P90, Company AI readiness, deep session, and working weeks.

<figure>
  <img src="/wp-content/uploads/gki-developer-overview-01-v3@2x.png" class="help-center-img img-bordered" alt="Sarah Chen's row expanded on the Developers page with the Overview tab selected: an Activity (Jun 1 - Jun 28) heatmap with 22 active days, a Cost Breakdown of $262.00 total spend, marked Adjusted, split between claude-sonnet-5 and claude-haiku-5, Agent Adoption, Agent Autonomy and Output cards with their share of the tier, a Tier panel showing Power User 96/100, and the Composite Tier breakdown of each score times its weight with the output rate scaled against the org-wide benchmark" />
  <figcaption>The Overview tab of an expanded developer.</figcaption>
</figure>

#### Mark a day off

If you can manage Insights data, for example as an organization admin, you can mark or clear a developer's day off on the activity heatmap. Click a day, or move to it with the keyboard and press Enter or Space. The tooltip reads **Mark as time off** or **Clear time off**. A day you mark shows as **Day off** and counts the same way as time off synced from [BambooHR](/insights-expo/expo-ai-adoption-connect-jira-bamboohr#mark-time-off-by-hand): it can move the developer to the On PTO tier, and it leaves the day out of per-week rates. If the change doesn't save, Insights says so and you can try again.

### Agent Adoption tab

The **Agent Adoption** tab shows the developer's activity over time, with one card per AI tool they used: **Claude Code**, **Codex**, **OpenCode**, **Cursor**, **Copilot (OTEL)**, **Devin**, and **Copilot (PAT)**. **Copilot (OTEL)** comes from GitHub Copilot through [Agent OTel Data](/insights-expo/expo-ai-adoption-connect-otel#github-copilot-opentelemetry), and **Copilot (PAT)** from the [GitHub Copilot connection](/insights-expo/expo-ai-adoption-connect-ai-tools#github-copilot). Devin and OpenCode are available on request — contact your GitKraken account team.

Each card groups its charts into up to five sections:

* **Engagement** — how often the developer engages with the tool, whether or not the interaction produced code.
* **Activity & Volume** — how much the developer leans on AI day to day, such as sessions, prompts, requests, and output tokens.
* **Agentic Intensity** — how autonomously they work: tools per prompt, subagent calls, and context compactions.
* **Quality & Impact** — per-use effectiveness, such as completion and chat accept rates and lines per agent edit.
* **Reliability & Health** — diagnostic signals: cache hit rate, tool failures, rate-limit episodes, and provider outages.

Where a tool has benchmarks, each line is drawn against the org's developers who use the same tool. **Median (p50)** marks the middle of that group, **Top 10% (p90)** the level only 10% exceed, and the **Middle 50%** band (p25 to p75) where the middle half sit. Landing inside the band is typical. Charts without enough org data to benchmark, such as the Copilot (PAT) charts, show the developer's line alone. The charts follow the page's **Granularity**.

<figure>
  <img src="/wp-content/uploads/gki-developer-agent-adoption-01-v3@2x.png" class="help-center-img img-bordered" alt="Aisha Patel's row expanded on the Developers page with the Agent Adoption tab selected: a Claude Code card with weekly Sessions, Prompts and Output Tokens, Tools / Prompt, Subagent Calls and Context Compactions, and Cache Hit % and Tool Failures % charts, and a Cursor card with Active Hours, Requests and Output Tokens per week, each line drawn against the org's Median (p50), Top 10% (p90) and Middle 50% (p25-p75) bands" />
  <figcaption>The Agent Adoption tab, with one card per AI tool.</figcaption>
</figure>

### AI Sessions tab

The **AI Sessions** tab shows the developer's AI coding sessions and the skills they used. A note at the top reads **Measured from Claude Code, GitHub Copilot and OpenCode sessions**, which names the tools that report skill activity. GitHub Copilot counts here when it reports through Agent OTel Data. OpenCode is available on request — contact your GitKraken account team. For every session across your organization, see [AI Sessions](/insights-expo/expo-ai-adoption-ai-sessions).

The top of the tab shows four panels:

* **Token value trend.** The developer's token value over time, **Overall** and for **Skills**, the share spent while a skill was active.
* **Cost per Delivery.** Token value divided by the effort the developer delivered in the same period, with a dashed trend line. Periods with no delivered work have no bar. Delivered effort weights work by estimated complexity but isn't the Output Score, so this figure won't match the **$ / Output** column.
* **Selected period.** Token value spent on skills, **Skill uses**, **Distinct skills**, and **Token value / run**, then **Merged PRs**, **Direct commits**, **Reviewed PRs**, and **Delivered effort** in points.
* **Breakdown by model.** How the developer's token value or token volume splits across models, for skill and non-skill work. Use the toggle to switch between the two.

The trends follow the page's **Granularity**.

<figure>
  <img src="/wp-content/uploads/gki-developer-ai-sessions-01-v3@2x.png" class="help-center-img img-bordered" alt="Sarah Chen's row expanded on the Developers page with the AI Sessions tab selected, under the note &quot;Measured from Claude Code, GitHub Copilot and OpenCode sessions&quot;: a Token Value Trend chart ($262.00 overall, $166.60 skills), a Cost per Delivery bar chart with its trend line, the Selected Period panel ($166.60 on skills, 157 skill uses, 8 distinct skills, $0.81 token value per run, 20 merged PRs, 10 direct commits, 8 reviewed PRs, 10.2 pts delivered effort) and a Breakdown by Model donut of $262.00 split 87% claude-sonnet-5 and 13% claude-haiku-5" />
  <figcaption>The top of the AI Sessions tab for one developer.</figcaption>
</figure>

Below the panels, the tab shows how skills were used:

* **Top skills by token value.** Each skill's own token value, with its install source, its model mix, the share that ran inside subagents, and how many runs and sessions used it. Toggle between **Total** and **Per run**. A **No skill** row shows token value spent with no skill active, for context.
* **Invocation trigger.** How each skill started: the developer invoked it directly, another skill called it, the agent chose it, or it loaded with an agent's context.
* **Skill source.** Where the skills came from: user (personal), project (shared in the repository), bundled or built-in (included with the coding agent), plugin, custom directory, or remote (organization or enterprise).

#### Open a session

The **All sessions** table lists every session the developer ran in the window, with **Session**, **Top skill**, **Skills**, **Models**, **Runs**, **Started**, and **Token value**. Filter by top skill or search by session ID to narrow it. Click a session to expand it.

An expanded session shows its time span and token value, and any pull requests it worked on. Below that, the **Session timeline** lays the session out in order, in two lanes side by side:

* The **work** lane shows what each prompt turn did, as bars whose length is proportional to token value. Each bar names its skill, or **No skill**, with its cost and model.
* The **git** lane shows the git activity each turn produced: pull requests, direct commits, and branch changes, in time order. Markers show where the context window was compacted.

When a session's git activity wasn't recorded, the timeline says so and shows the work lane alone. Click a skill on the timeline to open its **Segment detail**: token value, API requests, prompts sent (count and length only, never prompt text), how hands-on the work was, cache hit rate, models, tools used, where the work ran, and any failed tools or API errors.

Sessions that spawned subagents list them under **Subagents**, grouped by agent type, with each type's cost and main model. The segment detail also shows **Subagents spawned**, with the share of requests they made. Click a subagent type to open its **Agent detail**: token value, requests, tool calls, tokens, cache hit rate, and models. A subagent always reads as **Autonomous**, because it takes no prompts of its own.

<figure>
  <img src="/wp-content/uploads/gki-developer-session-timeline-01-v2@2x.png" class="help-center-img img-bordered" alt="The first session in Sarah Chen's All sessions table (Session, Top Skill, Skills, Models, Runs, Started and Token Value columns) expanded on the Developers page AI Sessions tab: Jun 5, 1:45 PM - 2:46 PM, $11.88 session token value, a banner for merged, authored PR #552 &quot;Add CODEOWNERS for mobile&quot; git-linked via its feature/add-codeowners-for-mobile branch, and the Session Timeline by Token Value with the work lane (No skill and /schema-diff bars with their cost and model) beside the git lane (branch switch, two commits and PR #552 opened, by time), a per-skill cost legend, and the Explore and test-runner subagents" />
  <figcaption>An expanded session with its two-lane timeline.</figcaption>
</figure>

### Developer Environment tab

The **Developer Environment** tab shows one card per AI tool the developer used, with the number of events it reported:

* **OS / Arch** — the operating system and architecture.
* **Editor** — the editor the tool ran in.
* **Top tools** — the tools the developer used most, ranked by event volume. Up to 10 are shown.
* **Models** — the models the developer ran most. Up to 10 are shown.

GitHub Copilot can have two cards. The card marked **OTEL** comes from Agent OTel Data and follows the same layout. The card marked **PAT** comes from the GitHub Copilot connection and shows **Clients**, **Models**, **Languages**, **Latest plugin version**, and **AI Credits Used**. **Clients** names the editors the developer launched Copilot from, such as VS Code, Visual Studio, JetBrains, or Xcode. Usage GitHub reports without a named editor is grouped under **Unknown client**.

Click the bulb on the **Environment Insight** card to generate an AI-written read of the developer's environment: the pattern, anything worth flagging, and one suggestion. Like the Agent Adoption Insight, it appears only when your organization has AI features enabled.

<figure>
  <img src="/wp-content/uploads/gki-developer-environment-01-v3@2x.png" class="help-center-img img-bordered" alt="Aisha Patel's row expanded on the Developers page with the Developer Environment tab selected: a Claude Code card with 7,280 events showing OS / arch Darwin 25.1.0 arm64, the ghostty editor, Top Tools from Read (1,007) to TodoWrite (203) and the claude-sonnet-5 and claude-haiku-5 models, and a Cursor card with 2,511 events showing an Unknown OS, the cursor editor, no top tools and the claude-sonnet-5 and cursor-small models" />
  <figcaption>The Developer Environment tab, with one card per AI tool.</figcaption>
</figure>

### System Metrics tab

The **System Metrics** tab shows the developer's pull request activity from your connected git providers:

* **Cycle Time** — median cycle time broken into coding, pickup, and review. See [Cycle Time](/insights-expo/expo-ai-adoption-flow-cycle-time).
* **PR Volume** — pull requests the developer opened and merged.

Dashed lines show the org-wide P50 and P90 for context. Use the dimension picker to break either chart down. The picker offers only the dimensions that make sense for one developer: **Trend**, **By Repo**, **By Size**, **By Day Opened**, **By Category**, **By AI Tier**, **By Agent Adoption Score**, **By Agent Autonomy Score**, and **By AI Provider**.

## Related pages

| Page | What it covers |
| --- | --- |
| [Agent Adoption Score](/insights-expo/expo-ai-adoption-agentic-adoption-score) | How the adoption score is calculated |
| [Agent Autonomy Score](/insights-expo/expo-ai-adoption-agentic-autonomy-score) | How the autonomy score is calculated |
| [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier) | How the composite tier combines the scores |
| [Output Score](/insights-expo/expo-ai-adoption-output-output-score) | How output is weighted and the drill-down behind it |
| [AI Sessions](/insights-expo/expo-ai-adoption-ai-sessions) | Every AI session across your organization |
| [By AI Tool & Model](/insights-expo/expo-ai-adoption-ai-tools-comparison) | Productivity compared across AI tool and model cohorts |
