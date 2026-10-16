---
title: By AI Tool & Model
description: Compare productivity across AI tool and AI model cohorts on the AI Tools Comparison page in GitKraken Insights, from the cohort cards to the trend charts and the productivity table.
product: GitKraken Insights
content_type: reference
audience: engineering-leader
plan_required: GitKraken Insights
integrations: [Claude Code, Codex, Cursor, GitHub Copilot, Devin, OpenCode]
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: purple
    card_description: Productivity compared across AI tool and model cohorts
    card_icon: tools
    nav_category: getting-started
    nav_label: By AI Tool & Model
    nav_order: 75
    page_type: content
---
<kbd>Last updated: October 2026</kbd>

The **AI Tools Comparison** page groups your developers into cohorts by the AI tools or models they used, and compares adoption, autonomy, and delivery across those cohorts. This page explains how cohorts are formed and how to read each part of the page. It assumes your AI tools already report to Insights. To connect them, see [Connect AI Coding Tools](/insights-expo/expo-ai-adoption-connect-ai-tools) and [Connect Agent OTel Data](/insights-expo/expo-ai-adoption-connect-otel).

> **Who can see it:** The AI Tools Comparison page is available to **Owners**, **Admins**, and **Managers**.

---

## Open AI Tools Comparison

In the Insights sidebar, under **Agent Leaderboard**, click **By AI Tool & Model**. The page opens with a filter bar and two tabs:

* **AI Tools** compares cohorts of single tools and tool combinations. The page opens on this tab.
* **AI Models** compares cohorts by the AI model developers used.

The filter bar holds **Timeframe**, **Granularity**, **Filters**, and **Teams**. The filters apply to every card, chart, and table on the page.

## Understand the cohorts

On the **AI Tools** tab, a cohort is the combination of AI tools a developer used in the selected window. A developer who used only Claude Code is in the **Claude Code** cohort. A developer who used Claude Code and Cursor is in **Claude + Cursor**. Cohorts can combine any of Claude Code, Cursor, Codex, GitHub Copilot, Devin, and OpenCode. Developers with no AI tool activity in the window form the **None** cohort.

Devin and OpenCode are available on request — contact your GitKraken account team. Cohorts that include them appear only for organizations where those tools are enabled. The **AI Tool** filter on the [Developers page](/insights-expo/expo-ai-adoption-developers) uses the same cohorts.

On the **AI Models** tab, each card is one model, and counts every developer who used that model in the window.

Only cohorts with developers in the window get a card, so the set of cards changes with the timeframe and filters.

## Read the cohort cards

Each cohort card lists these figures for its developers:

* **Developers** — active developers in the cohort during the selected period.
* **Adoption** — the average [Agent Adoption Score](/insights-expo/expo-ai-adoption-agentic-adoption-score).
* **Autonomy** — the average [Agent Autonomy Score](/insights-expo/expo-ai-adoption-agentic-autonomy-score).
* **Median Cycle Time** — the median time from start of work to pull request merge, for pull requests of 30 days or less. Lower is better.
* **PRs / Dev / Week** — merged pull requests per developer per week.
* **Changes / Dev / Week** — merged pull requests plus direct commits per developer per week.

<figure>
  <img src="/wp-content/uploads/gki-ai-tools-comparison-by-tool-01.png" class="help-center-img img-bordered" alt="The AI Tools Comparison page on its AI Tools tab, below the Timeframe, Granularity, Filters and Teams controls: cohort cards for Claude Code, Claude + Cursor and Claude + Codex, each listing Developers, Adoption, Autonomy, Median Cycle Time, PRs / Dev / Week and Changes / Dev / Week." />
  <figcaption>Cohort cards on the AI Tools tab.</figcaption>
</figure>

### Tokens and Spend on model cards

Model cards on the **AI Models** tab add two rows:

* **Tokens** — total output tokens from the model, across every developer who used it.
* **Spend** — total cost of the model in the selected period, across every developer who used it.

Both count every developer who used the model, not only developers whose main model it is. A dash means the tool doesn't report tokens or cost per model.

<figure>
  <img src="/wp-content/uploads/gki-ai-tools-comparison-by-model-01.png" class="help-center-img img-bordered" alt="The AI Tools Comparison page on its AI Models tab, below the Timeframe, Granularity, Filters and Teams controls: cards for Claude Sonnet 5, Claude Fable 5 and GPT-5.4, each listing Developers, Adoption, Autonomy, Median Cycle Time, PRs / Dev / Week, Changes / Dev / Week, Tokens and Spend." />
  <figcaption>Model cards on the AI Models tab, with Tokens and Spend.</figcaption>
</figure>

## Compare cohorts over time

Below the cards, four charts compare the cohorts:

* **Merged PRs / Developer** — merged pull requests per developer, by cohort over time.
* **Total Changes / Developer** — merged pull requests plus direct commits per developer, by cohort over time.
* **Median Cycle Time** — median cycle time by cohort over time, for pull requests of 30 days or less.
* **Cycle Time by Category** — median cycle time by pull request category and cohort.

The trend charts follow the page's **Granularity**.

## Read the Productivity Metrics table

The **Productivity Metrics** table puts the cohorts side by side, one column per cohort, with these rows:

* **PRs Merged / Dev / Week**
* **Total Changes / Dev / Week**
* **Avg PR Size**, in lines changed per pull request
* **Median Cycle Time**, from start of work to merge
* **Median Coding Time / PR**, from start of work to pull request open
* **Median Review Time / PR**, from pull request open to merge

Start of work is the earlier of the first commit or the Jira issue moving to In Progress. The best value in each row is highlighted.

Read the comparison as a starting point for a conversation, not a verdict on a tool. Cohorts differ in size, and developers who adopt several tools often differ from those who use one. See [Cycle Time](/insights-expo/expo-ai-adoption-flow-cycle-time) and [Throughput](/insights-expo/expo-ai-adoption-output-throughput) for how the delivery figures are measured.

## Related pages

| Page | What it covers |
| --- | --- |
| [By Developer](/insights-expo/expo-ai-adoption-developers) | Each developer's scores, spend, and AI tool activity |
| [Adoption & Agentic Metrics](/insights-expo/expo-ai-adoption-agentic-metrics) | The adoption and autonomy scores the cards average |
| [Roll Out AI Tooling with the Adoption Score](/insights-expo/expo-ai-adoption-playbook-ai-rollout) | Using cohort comparisons during a rollout |
