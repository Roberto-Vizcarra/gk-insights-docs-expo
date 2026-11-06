---
title: DORA & Quality Metrics in the AI Adoption Dashboard
description: Learn about DORA & Quality metrics in the GitKraken Insights AI Adoption dashboard, including Deployment Frequency, Lead Time for Changes, Change Failure Rate (CFR), and Mean Time to Recovery (MTTR).
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
integrations: [GitHub, GitHub Enterprise Server, GitLab, GitLab Self-Managed, Bitbucket, Azure DevOps, Azure DevOps Server, Jira Cloud]
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: Deploy Frequency, Lead Time, CFR, and MTTR
    card_icon: shield-check
    nav_category: metrics
    nav_label: DORA & Quality
    nav_order: 40
    nav_parent: expo-ai-adoption-metrics
    page_type: index
---
<kbd>Last updated: November 2026</kbd>

This family covers the industry-standard four DORA metrics — Deployment Frequency, Lead Time for Changes, Change Failure Rate, and Mean Time to Recovery — plus the customer-bug volume that powers CFR and MTTR.

---

DORA emerged from the DevOps Research & Assessment program at Google and is the common language for engineering delivery health at the leadership level. When you present to a CTO, a board, or an external stakeholder, these are the metrics they already know.

---

## The four DORA metrics

| Metric | Bucket | What it captures |
| --- | --- | --- |
| [**Deployment Frequency**](/insights-expo/expo-ai-adoption-dora-deployment-frequency) | Velocity | How often you ship to production. |
| [**Lead Time for Changes**](/insights-expo/expo-ai-adoption-dora-lead-time) | Velocity | How long it takes from when work on a change starts to that change reaching production. |
| [**Change Failure Rate (CFR)**](/insights-expo/expo-ai-adoption-dora-change-failure-rate) | Stability | What percent of releases produce a customer-reported bug. |
| [**Mean Time to Recovery (MTTR)**](/insights-expo/expo-ai-adoption-dora-mttr) | Stability | How long it takes to recover from a production incident. MTTR is not shown on the standard Insights pages. |

DORA splits these into two velocity metrics and two stability metrics. A high-performing team scores well on **both** sides — fast and reliable. A team that is fast but unreliable, or reliable but slow, has unfinished work to do.

---

## How DORA in this dashboard differs from the original framing

GitKraken Insights adapts the original DORA definitions in these ways:

* **Deployment Frequency** counts release events per window, using each repo's configured [release signal](/insights-expo/expo-ai-adoption-dora-deployment-frequency#release-signals-by-provider). You need release detection configured for each repo — without it, the metric shows an empty state.
* **Lead Time** measures from _work start_ to _delivering release_. The [Cycle Time Start Signal](/insights-expo/expo-ai-adoption-settings#cycle-time-start-signal) setting decides when work starts, for Lead Time and Cycle Time alike. While it is not set, Lead Time starts at the first commit, not at issue creation. An admin can choose **In progress** to start the clock when the linked issue moves to in progress, if that comes before the first commit.
* **CFR** is the rate of _releases that produced a customer-reported bug_, not the rate of all bugs. The signal is the customer bugs from your connected issue tracker — Jira, Linear, YouTrack, Azure Boards, or [ClickUp](/insights-expo/expo-ai-adoption-connect-clickup) — identified by that connection's defect detection. Internal bugs caught in QA do not count — a deliberate choice so CFR focuses on bugs that actually shipped and reached customers.
* **MTTR** measures median hours from a customer bug being opened to resolved. It uses the same customer bugs from your connected issue tracker as CFR. MTTR is not shown on the standard Insights pages.

---

## Read this family

The classic DORA reading pattern:

1. **Start with the velocity pair.** Are you shipping often enough? Is Lead Time stable or drifting?
2. **Then the stability pair.** Is CFR creeping? Are recovery times stable?
3. **The interesting question is usually how they trade off.** A team where Deployment Frequency just doubled but CFR also doubled has not actually improved delivery. A team where Deployment Frequency doubled while CFR stayed flat has — that's the real win.

For AI adoption analysis, DORA is the metric set most likely to show "AI is paying off" at a board-level credible scale. Healthy AI rollouts trend Deployment Frequency ↑, Lead Time ↓, CFR flat or ↓, MTTR flat or ↓.

If Deployment Frequency rises while CFR also rises, AI is enabling faster but worse shipping — a known anti-pattern that needs investigation.

---

## Where this family shows up

* **/insights/board-metrics** — Deployment Frequency, Change Lead Time, and CFR in one leadership view. See [Board Metrics page](#board-metrics-page).
* **/insights/throughput-quality** — the Customer Bugs & Change Failure Rate card and the Customer Bugs breakdown card.
* **/insights/ai-impact** — Customer Bugs by AI Tier.
* **/insights/executive** — Deployment Frequency and CFR as hero trend lines.

---

## Board Metrics page

Board Metrics (**Reporting → Board Metrics** in the sidebar, `/insights/board-metrics`) collects engineering health indicators for leadership reporting on one page. Set **Timeframe**, **Granularity**, **Filters**, and **Teams** at the top, or select **Export CSV** to download the figures.

The KPI tiles across the top show **Cycle Time**, **Change Lead Time**, **Deployment Frequency**, **CFR** (with "N failed / M releases"), **Agent Adoption**, and **PRs / Dev / Week**, each against the previous period. Several tiles open the data behind them: select the **Deployment Frequency** value to list every release in the period, the **CFR** value to list the failed releases, or the **Agent Adoption** subtitle to list the developers actively using AI.

The charts below show:

* **Cycle Time** — median cycle time, summing the coding, pickup, and review phases. The header shows the median and the PR count; select the PR count, or a point on the chart, to open the PRs behind it. A previous-period point opens that period's PRs.
* **Change Lead Time** — median time from a change's work start to the release that delivered it. Select a point to see the changes behind it, with each change's merge and release dates and lead time. When Cycle Time Unit of Work is set to **Issue**, a point opens the releases behind it instead.
* **Deployment Frequency** — production releases per week, against the previous period. To list a week's releases, hide **Previous Period** in the legend, then select that week's bar.
* **Customer Bugs & Change Failure Rate** — the change failure rate with failed and total releases, then Customer Bugs by Priority (Highest, High, Medium, Low, and Other). On Board Metrics this card always shows all defect definitions, so it matches the CFR tile. Select the rate to see the failed releases and the customer bugs behind each, or a priority count to see those bugs.
* **Avg Changes / Developer** — average merged PRs plus direct commits per active developer per week, compared against the previous period. Select the merged and direct count in the header, or a bar, to open the changes behind it.
* **Agent Adoption** — how many developers fall in each AI tier. Select a tier to open **Developers by adoption tier**, which lists the developers in that tier and links on to By Developer.

MTTR is not on Board Metrics.

<figure>
  <img src="/wp-content/uploads/gki-board-metrics-page-01-v4@2x.png" class="help-center-img img-bordered" alt="The Board Metrics page in GitKraken Insights with Timeframe, Granularity, Filters and Teams filters and Export CSV, KPI tiles for Cycle Time, Change Lead Time, Deployment Frequency, CFR (7.8%, 5 failed / 64 releases), Agent Adoption and PRs / Dev / Week, charts for Cycle Time and Change Lead Time and Deployment Frequency against the previous period, the Customer Bugs & Change Failure Rate card with its Customer Bugs by Priority section of Highest, High, Medium, Low and Other pills above weekly bars and a priority legend, the Avg Changes / Developer chart, and an Agent Adoption card splitting developers into 5 Power User and 5 Regular; the Cycle Time and Avg Changes / Developer cards read Click to drill down" />
  <figcaption>Board Metrics — KPI tiles, then the Cycle Time, Change Lead Time, Deployment Frequency, Customer Bugs & Change Failure Rate, Avg Changes / Developer, and Agent Adoption charts.</figcaption>
</figure>

---

## Required integrations

* **CFR & MTTR** require defect detection on a connected issue tracker (Jira, Linear, YouTrack, Azure Boards, or [ClickUp](/insights-expo/expo-ai-adoption-connect-clickup)): select **Enable defect detection** on the connection and choose how to **Match by**. Without it, both metrics show an empty state. See [Configure Change Failure Rate (CFR)](/insights-expo/expo-ai-adoption-connect-jira-bamboohr#configure-change-failure-rate-cfr).
* **Lead Time and Deployment Frequency** require tracked releases for each repo. Without them, both metrics show an empty state. Releases come from either source:
  * release detection, a release signal set per repo in **Settings → Releases** (see [Release signals by provider](/insights-expo/expo-ai-adoption-dora-deployment-frequency#release-signals-by-provider))
  * releases you push with the [Manual Releases API](/insights-expo/expo-ai-adoption-manual-releases-api)
