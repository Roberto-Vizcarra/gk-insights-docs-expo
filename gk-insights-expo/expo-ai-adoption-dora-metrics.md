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
<kbd>Last updated: October 2026</kbd>

> **Note:** This page covers the **DORA & Quality** family of the AI Adoption dashboard. For DORA metrics on the classic Insights dashboards — Deploy Frequency, Change Lead Time, Mean Time to Repair/Recover, and Defect Rate, which use different definitions and calculation logic — see [DORA Metrics](/insights-expo/expo-ai-adoption-dora-metrics).

This family covers the industry-standard four DORA metrics — Deployment Frequency, Lead Time for Changes, Change Failure Rate, and Mean Time to Recovery — plus the customer-bug volume that powers CFR and MTTR.

---

DORA emerged from the DevOps Research & Assessment program at Google and is the common language for engineering delivery health at the leadership level. When you present to a CTO, a board, or an external stakeholder, these are the metrics they already know.

---

## The four DORA metrics

| Metric | Bucket | What it captures |
| --- | --- | --- |
| [**Deployment Frequency**](/insights-expo/expo-ai-adoption-dora-deployment-frequency) | Velocity | How often you ship to production. |
| [**Lead Time for Changes**](/insights-expo/expo-ai-adoption-dora-lead-time) | Velocity | How long it takes from a developer's first commit to that change reaching production. |
| [**Change Failure Rate (CFR)**](/insights-expo/expo-ai-adoption-dora-change-failure-rate) | Stability | What percent of releases produce a customer-reported bug. |
| [**Mean Time to Recovery (MTTR)**](/insights-expo/expo-ai-adoption-dora-mttr) | Stability | How long it takes to recover from a production incident. MTTR isn't currently shown on the standard Insights pages. |

DORA splits these into two velocity metrics and two stability metrics. A high-performing team scores well on **both** sides — fast and reliable. A team that is fast but unreliable, or reliable but slow, has unfinished work to do.

---

## How DORA in this dashboard differs from the original framing

A few practical adaptations worth knowing:

* **Deployment Frequency** counts release events per window, using each repo's configured [release signal](/insights-expo/expo-ai-adoption-dora-deployment-frequency#release-signals-by-provider). You need release detection configured for each repo — without it, the metric shows an empty state.
* **Lead Time** measures from _first commit_ to _delivering release_. It does not start at issue creation. If you enable JIRA integration with the "Include JIRA start time" option, Lead Time extends backward to include time from the issue's in-progress transition.
* **CFR** is the rate of _releases that produced a customer-reported bug_, not the rate of all bugs. The signal is the customer bugs from your connected issue tracker — Jira, Linear, YouTrack, or Azure Boards — identified by that connection's defect detection. Internal bugs caught in QA don't count — a deliberate choice so CFR focuses on bugs that actually shipped and reached customers.
* **MTTR** measures median hours from a customer bug being opened to resolved. It uses the same customer bugs from your connected issue tracker as CFR. MTTR isn't currently shown on the standard Insights pages.

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

* **/ai-adoption/board-metrics** — Deployment Frequency, Change Lead Time, and CFR in one leadership view. See [Board Metrics page](#board-metrics-page).
* **/ai-adoption/throughput-quality** — the Customer Bugs & Change Failure Rate card and the Customer Bugs breakdown card.
* **/ai-adoption/ai-impact** — Customer Bugs by AI Tier.
* **/ai-adoption/executive** — Deployment Frequency and CFR as hero trend lines.

---

## Board Metrics page

Board Metrics (**Reporting → Board Metrics** in the sidebar, `/ai-adoption/board-metrics`) collects engineering health indicators for leadership reporting on one page. Set **Timeframe**, **Granularity**, **Filters**, and **Teams** at the top, or select **Export CSV** to download the figures.

The KPI tiles across the top show **Cycle Time**, **Change Lead Time**, **Deployment Frequency**, **CFR** (with "N failed / M releases"), **Agent Adoption**, and **PRs / Dev / Week**, each against the previous period.

The charts below show:

* **Cycle Time** — median cycle time, summing the coding, pickup, and review phases.
* **Change Lead Time** — median time from a change's first commit to the release that delivered it. Select a point to see the releases behind it.
* **Deployment Frequency** — production releases per week, against the previous period.
* **Customer Bugs & Change Failure Rate** — the change failure rate with failed and total releases, then Customer Bugs by Priority (Highest, High, Medium, Low, and Other). On Board Metrics this card always shows all defect definitions, so it matches the CFR tile.
* **Avg Changes / Developer** — average merged PRs plus direct commits per active developer per week, compared against the previous period.
* **Agent Adoption** — how many developers fall in each AI tier.

MTTR isn't on Board Metrics.

<figure>
  <img src="/wp-content/uploads/gki-board-metrics-page-01.png" class="help-center-img img-bordered" alt="The Board Metrics page in GitKraken Insights with Timeframe, Granularity, Filters and Teams filters and Export CSV, KPI tiles for Cycle Time, Change Lead Time, Deployment Frequency, CFR (7.8%, 5 failed / 64 releases), Agent Adoption and PRs / Dev / Week, charts for Cycle Time, Change Lead Time and Deployment Frequency against the previous period, the Customer Bugs & Change Failure Rate card with its Change Failure Rate section and its Customer Bugs by Priority section of Highest, High, Medium, Low and Other pills above weekly bars, the Avg Changes / Developer chart, and an Agent Adoption card splitting developers into 5 Power User and 5 Regular" />
  <figcaption>Board Metrics — KPI tiles, then the Cycle Time, Change Lead Time, Deployment Frequency, Customer Bugs & Change Failure Rate, Avg Changes / Developer, and Agent Adoption charts.</figcaption>
</figure>

---

## Required integrations

* **CFR & MTTR** require defect detection on a connected issue tracker (Jira, Linear, YouTrack, or Azure Boards): select **Enable defect detection** on the connection and choose how to **Match by**. Without it, both metrics show an empty state. See [Configure Change Failure Rate (CFR)](/insights-expo/expo-ai-adoption-connect-jira-bamboohr#configure-change-failure-rate-cfr).
* **Lead Time and Deployment Frequency** require releases to be tracked per repo — through release detection (a release signal set per repo in **Settings → Releases** — see [Release signals by provider](/insights-expo/expo-ai-adoption-dora-deployment-frequency#release-signals-by-provider)) or through releases you push with the [Manual Releases API](/insights-expo/expo-ai-adoption-manual-releases-api). Without either, both metrics show an empty state.
