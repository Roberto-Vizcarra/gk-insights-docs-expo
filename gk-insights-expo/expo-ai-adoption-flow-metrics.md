---
title: Flow & Cycle Time Metrics in GitKraken Insights
description: Learn about Flow & Cycle Time metrics in GitKraken Insights, including Cycle Time, Review Cycles, First-Pass Rate, and Work In Progress (WIP).
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
integrations: [GitHub, GitHub Enterprise Server, GitLab, Bitbucket, Azure DevOps, Azure DevOps Server, Jira Cloud]
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: Cycle Time, Review Cycles, First-Pass Rate, WIP
    card_icon: clock
    nav_category: metrics
    nav_label: Flow & Cycle Time
    nav_order: 30
    nav_parent: expo-ai-adoption-metrics
    page_type: index
---
<kbd>Last updated: October 2026</kbd>

This family answers: **how fast does work move through your system, and where does it get stuck?**

Where the Output family asks "how much shipped?", Flow asks "how smooth was the journey from idea to merge?" These are the operational metrics — the ones team leads watch weekly and engineering leaders read for systemic patterns.

---

## The metrics

| Metric | What it captures |
| --- | --- |
| [**Cycle Time**](/insights-expo/expo-ai-adoption-flow-cycle-time) | Median hours from first commit to merge — the headline flow metric. Broken into phases: Coding, Pickup, and Review, plus Deploy when release detection is available. |
| [**Review Cycles**](/insights-expo/expo-ai-adoption-flow-review-cycles) | Average rounds of "changes requested" per PR before merge. A signal of PR size and review fit. |
| [**First-Pass Rate**](/insights-expo/expo-ai-adoption-flow-first-pass-rate) | Percent of PRs merged with zero "changes requested" reviews. The clean-merge rate. |
| [**WIP**](/insights-expo/expo-ai-adoption-flow-wip) | Number of currently open PRs. The work-in-progress backlog. |

---

## The four cycle phases

When you see Cycle Time broken down, these are the phases:

| Phase | What it measures |
| --- | --- |
| **Coding** | First commit to PR open. The "writing the code" time. |
| **Pickup** | PR open to first review. The "PR sitting unattended" time. |
| **Review** | First review to merge. The "back and forth" time. |
| **Deploy** | Merge to production deploy (only populated when release detection is configured for the repo). |

Reading Cycle Time _by phase_ is almost always more useful than reading the total. A 5-day Cycle Time that is 80% Pickup tells you something very different than a 5-day Cycle Time that is 80% Coding.

---

## Read this family

Three common patterns:

1. **Long Pickup phase** → review queue is over-committed. Symptoms: WIP rising, fewer reviewers active. Fix: review-clearing ritual, reviewer rotation.
2. **Long Review phase** → PRs too big, or reviewers asking for too many changes. Symptoms: high Review Cycles, low First-Pass Rate. Fix: split PRs, write better PR descriptions.
3. **Long Coding phase** → could be deep work (fine) or stalled work (not fine). Cross-check with WIP and developer-level activity to tell which.

---

## Throughput & Quality page

**Throughput & Quality** (`/ai-adoption/throughput-quality`, under **Reporting** in the sidebar) brings this family's charts together with PR volume and change-failure trends. The filter bar at the top sets the timeframe, granularity, and scope for every card, and **Export CSV** exports the page.

<figure>
  <img src="/wp-content/uploads/gki-throughput-quality-page-01-v2@2x.png" class="help-center-img img-bordered" alt="The Throughput & Quality page in GitKraken Insights with Timeframe, Granularity, Filters and Teams filters and Export CSV, a Cycle Time chart stacked by Coding, Pickup and Review beside a Cycle Time trend against the previous period with a Total / Trend switch, and the Issue Cycle Time chart of Backlog and Active time with All trackers and All issue types pickers" />
  <figcaption>The top of Throughput & Quality: the two Cycle Time cards and Issue Cycle Time.</figcaption>
</figure>

From top to bottom, the page shows these cards:

| Card | What it shows |
| --- | --- |
| **Cycle Time** (trend) | Median PR [Cycle Time](/insights-expo/expo-ai-adoption-flow-cycle-time) per bucket, stacked into Coding, Pickup, Review, and Deploy when available. |
| **Cycle Time** (breakdown) | Cycle Time split by a dimension you choose from the card's **⋯** menu → **View**, with a **Total / Trend** switch. |
| [**Issue Cycle Time**](/insights-expo/expo-ai-adoption-flow-cycle-time#issue-cycle-time) | Median time from issue creation to resolution, split into Backlog and Active phases. Org-scoped: the team, repository, and author filters don't apply. |
| **First Response Time** | Median hours from PR open to first review. Lower means faster review pickup. |
| **Avg Changes / Developer** | Average PRs opened and merged per active developer per bucket, with direct commits as a separate series. |
| **PR Volume** (breakdown) | PR volume split by a dimension you choose, with a **Total / Trend** switch. |
| **Customer Bugs & Change Failure Rate** | The [Change Failure Rate](/insights-expo/expo-ai-adoption-dora-change-failure-rate) trend with the customer bugs behind it. |
| **Customer Bugs by dimension** | Customer bug counts grouped by a dimension you pick on the card, with a **Total / Trend** switch. |
| **PR Review Count** | Average reviews per PR per bucket, over non-draft PRs that people authored. Percentile bands show how the trend compares to the full window. |
| **Merged Without Review** | Share of PRs merged in the window with zero reviews. Higher is worse. |

Click a point on a Cycle Time or PR volume chart to open the PRs behind it. Every chart card has a **⋯** menu for exporting its data and adding annotations; see [Working with charts](/insights-expo/expo-ai-adoption-metrics#working-with-charts).

---

## Where this family shows up

* **/ai-adoption/throughput-quality** — the Throughput & Quality page described above.
* **/ai-adoption/flow-delivery** — Team Lead Insights: flow KPI cards and, on the Flow tab, the Cycle Time chart by phase.
* **/ai-adoption/ai-impact** — the Cycle-Time-by-AI-Tier view.
* **/ai-adoption/teams** — expanded team rows show the Cycle Time trend and phase breakdown on the System Metrics tab. The team table itself has no Cycle Time column.
* **/ai-adoption/executive** — Cycle Time as a hero KPI and the Cycle Time breakdown card, with its **Total / Trend** switch and **⋯** → **View** picker.
* **/ai-adoption/developers** — per-developer cycle time in the expanded developer detail.
* **PR drill-down tables** — clicking into a Cycle Time or PR Volume chart point opens a table with per-PR Cycle Time, Review Cycles, and Effort columns. A Cycle Time drill-down adds a scatterplot of each PR's cycle time, and a PR Volume drill-down adds a Lead time column. PRs a coding agent created carry an **Agent** badge; hover it to see which agent created the PR.

---

## A note on the "Include JIRA start time" toggle

When the "Include JIRA start time in Coding phase" toggle is on, the Coding phase extends backward to include the time between the JIRA issue's in-progress transition and the first commit. This gives a more complete picture of "time from commitment to ship" — but it requires JIRA integration and a consistent in-progress workflow.

The toggle is on by default in the app. If your team doesn't use JIRA, or your in-progress workflow is inconsistent, turn it off so Cycle Time starts at first commit instead.
