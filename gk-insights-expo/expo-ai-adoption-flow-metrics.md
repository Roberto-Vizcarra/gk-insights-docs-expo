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
<kbd>Last updated: November 2026</kbd>

This family answers: **how fast does work move through your system, and where does it get stuck?**

Where the Output family asks "how much shipped?", Flow asks "how smooth was the journey from idea to merge?" These are the operational metrics — the ones team leads watch weekly and engineering leaders read for systemic patterns.

---

## The metrics

| Metric | What it captures |
| --- | --- |
| [**Cycle Time**](/insights-expo/expo-ai-adoption-flow-cycle-time) | Median hours from work start to merge — the headline flow metric. Broken into phases: Coding, Pickup, and Review, plus Deploy when release detection is available. |
| [**Review Cycles**](/insights-expo/expo-ai-adoption-flow-review-cycles) | Average rounds of "changes requested" per PR before merge. A signal of PR size and review fit. |
| [**First-Pass Rate**](/insights-expo/expo-ai-adoption-flow-first-pass-rate) | Percent of PRs merged with zero "changes requested" reviews. The clean-merge rate. |
| [**WIP**](/insights-expo/expo-ai-adoption-flow-wip) | Number of currently open PRs. The work-in-progress backlog. |

---

## The four cycle phases

When you see Cycle Time broken down, these are the phases:

| Phase | What it measures |
| --- | --- |
| **Coding** | Work start to PR open. The "writing the code" time. |
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

**Throughput & Quality** (`/insights/throughput-quality`, under **Reporting** in the sidebar) brings this family's charts together with PR volume and change-failure trends. The filter bar at the top sets the timeframe, granularity, and scope for every card, and **Export CSV** exports the page. To count only pull requests linked to an issue, choose **Filters** → **Linked to an issue**; see [Set the scope first](/insights-expo/expo-ai-adoption-first-dashboard#set-the-scope-first).

<figure>
  <img src="/wp-content/uploads/gki-throughput-quality-page-01-v3@2x.png" class="help-center-img img-bordered" alt="The Throughput & Quality page in GitKraken Insights with Timeframe, Granularity, Filters and Teams filters and Export CSV, a Cycle Time chart stacked by Coding, Pickup and Review beside a Cycle Time trend against the previous period with a Total / Trend switch, and the Issue Cycle Time chart of Backlog and Active time with All trackers and All issue types pickers" />
  <figcaption>The top of Throughput & Quality: the two Cycle Time cards and Issue Cycle Time.</figcaption>
</figure>

From top to bottom, the page shows these cards:

| Card | What it shows |
| --- | --- |
| **Cycle Time** (trend) | Median PR [Cycle Time](/insights-expo/expo-ai-adoption-flow-cycle-time) per bucket, stacked into Coding, Pickup, Review, and Deploy when available. |
| **Cycle Time** (breakdown) | Cycle Time split by a dimension you choose from the card's **⋯** menu → **View**, with a **Total / Trend** switch. |
| [**Issue Cycle Time**](/insights-expo/expo-ai-adoption-flow-cycle-time#issue-cycle-time) | Median time from issue creation to resolution, split into Backlog and Active phases; click a point or the issue count to list the issues behind it. Org-scoped: the team and repository filters do not apply. |
| **First Response Time** | Median hours from PR open to its first review (a comment, approval, or change request), leaving out PRs not yet reviewed. Lower means faster review pickup. |
| **Avg Changes / Developer** | Average PRs opened and merged per active developer per bucket, with direct commits as a separate series. With **Linked to an issue** on, only linked PRs count and the direct commits series is hidden. |
| **PR Volume** (breakdown) | PR volume split by a dimension you choose, with a **Total / Trend** switch. |
| **Customer Bugs & Change Failure Rate** | The [Change Failure Rate](/insights-expo/expo-ai-adoption-dora-change-failure-rate) trend with the customer bugs behind it. |
| **Customer Bugs by dimension** | Customer bug counts grouped by a dimension you pick on the card, with a **Total / Trend** switch. |
| **PR Review Count** | Average reviews per PR per bucket, over non-draft PRs, leaving out PRs by excluded developers (most bot accounts are excluded automatically). Percentile bands show how the trend compares to the full window. |
| **Merged Without Review** | Share of PRs merged in the window without an approval or a request for changes (comments alone do not count as a review). Higher is worse. |

Click a point on a Cycle Time or PR volume chart to open the PRs behind it, or a point on the Issue Cycle Time chart to open the issues behind it. On **First Response Time**, click a point or the **PRs reviewed** count in the header to list the PRs opened in that period, on **Reviewed** and **Not reviewed** tabs, with each PR's first response and response time.

<figure>
  <img src="/wp-content/uploads/gki-first-response-drilldown-01.png" class="help-center-img img-bordered" alt="The All PRs First Response Time drill-down with a 3.2h median for Jun 1 - Jun 28, 2026, a note that first response time runs from when a PR is opened to its first review (a comment, approval, or change request), the Reviewed (189) and Not reviewed (6) tabs, a search box, and a table of Repo, PR, Author, Opened, First response and Response time with pagination" />
  <figcaption>The First Response Time drill-down, with reviewed and not-reviewed PRs on separate tabs.</figcaption>
</figure>

Every chart card has a **⋯** menu for exporting its data and adding annotations; see [Working with charts](/insights-expo/expo-ai-adoption-metrics#working-with-charts).

---

## Where this family shows up

* **/insights/throughput-quality** — the Throughput & Quality page described above.
* **/insights/flow-delivery** — Team Lead Insights: flow KPI cards and, on the Flow tab, the Cycle Time chart by phase. When GitKraken AI features are on for your organization, an **AI Insight** banner with a generated summary sits above the page content. The **Linked to an issue** filter narrows the PR-based cards to linked PRs and hides the banner.
* **/insights/ai-impact** — the Cycle-Time-by-AI-Tier view.
* **/insights/teams** — expanded team rows show the Cycle Time trend and phase breakdown on the System Metrics tab. The team table itself has no Cycle Time column.
* **/insights/executive** — Cycle Time as a hero KPI and the Cycle Time breakdown card, with its **Total / Trend** switch and **⋯** → **View** picker. The picker includes AI dimensions: **By AI Tier**, **By Agent Adoption Score**, **By Agent Autonomy Score**, and **By AI Provider**, plus **By Day Opened**.
* **/insights/developers** — per-developer cycle time in the expanded developer detail.
* **PR drill-down tables** — selecting a Cycle Time or PR Volume chart point opens a table with per-PR Cycle Time, Review Cycles, and Effort columns. A Cycle Time drill-down adds a scatterplot of each PR's cycle time, and a PR Volume drill-down adds a Lead time column. PRs a coding agent created carry an **Agent** badge; hover it to see which agent created the PR.

---

## Choose what Cycle Time measures

Two settings in **Settings → General** decide what the headline Cycle Time measures on every page:

* [**Cycle Time Start Signal**](/insights-expo/expo-ai-adoption-settings#cycle-time-start-signal) sets when the clock starts. **In progress** is the earlier of the first commit and the linked issue moving to in progress, which gives a fuller picture of "time from commitment to ship" but needs a consistent in-progress workflow in your tracker. **First commit** starts at the PR's first commit, and **Created** at the PR's creation. While the setting is not set, Cycle Time starts at in progress.
* [**Cycle Time Unit of Work**](/insights-expo/expo-ai-adoption-settings#cycle-time-unit-of-work) sets whether the headline Cycle Time times each pull request (the default) or each resolved issue across its linked PRs. Breakdowns, drill-downs, and scores always use pull requests.

If your team does not use an issue tracker, or your in-progress workflow is inconsistent, ask your admin to choose **First commit**.
