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
<kbd>Last updated: September 2026</kbd>

> **Note:** This page covers the **DORA & Quality** family of the AI Adoption dashboard. For DORA metrics on the classic Insights dashboards — Deploy Frequency, Change Lead Time, Mean Time to Repair/Recover, and Defect Rate, which use different definitions and calculation logic — see [DORA Metrics](/insights-expo/expo-ai-adoption-dora-metrics).

This family covers the industry-standard four DORA metrics — Deployment Frequency, Lead Time for Changes, Change Failure Rate, and Mean Time to Recovery — plus the customer-bug volume that powers CFR and MTTR.

---

DORA emerged from the DevOps Research & Assessment program at Google and is the common language for engineering delivery health at the leadership level. When you present to a CTO, a board, or an external stakeholder, these are the metrics they already know.

---

## The four DORA metrics

| Metric | Bucket | What it captures |
| --- | --- | --- |
| [**Deployment Frequency**](#deployment-frequency) | Velocity | How often you ship to production. |
| [**Lead Time for Changes**](#lead-time-for-changes) | Velocity | How long it takes from a developer's first commit to that change reaching production. |
| [**Change Failure Rate (CFR)**](#change-failure-rate-cfr) | Stability | What percent of releases produce a customer-reported bug. |
| [**Mean Time to Recovery (MTTR)**](#mean-time-to-recovery-mttr) | Stability | How long it takes to recover from a production incident. |

DORA splits these into two velocity metrics and two stability metrics. A high-performing team scores well on **both** sides — fast and reliable. A team that is fast but unreliable, or reliable but slow, has unfinished work to do.

---

## How DORA in this dashboard differs from the original framing

A few practical adaptations worth knowing:

* **Deployment Frequency** counts tagged releases (or configured release events) per window. You need release detection configured for each repo — without it, the metric shows an empty state.
* **Lead Time** measures from _first commit_ to _delivering release_. It does not start at issue creation. If you enable JIRA integration with the "Include JIRA start time" option, Lead Time extends backward to include time from the issue's in-progress transition.
* **CFR** is the rate of _releases that produced a customer-reported bug_, not the rate of all bugs. The signal is your Jira "Customer Bug = Yes" field. Internal bugs caught in QA don't count — a deliberate choice so CFR focuses on bugs that actually shipped and reached customers.
* **MTTR** measures mean hours from Jira incident open to resolved. It uses the same Jira customer-bug stream as CFR.

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

* **/ai-adoption/board-metrics** — all four DORA metrics in one canonical view.
* **/ai-adoption/ai-impact** — CFR is the prominent stability metric. CFR KPI card, trend chart, severity-stacked view, and the CFR-by-AI-Tier breakdown.
* **/ai-adoption/executive** — Deployment Frequency and CFR as hero trend lines.

---

## Required integrations

* **CFR & MTTR** require the Jira integration with the **Customer bug field ID** configured on the Jira connection. Without it, both metrics show an empty state. See [Configure Change Failure Rate (CFR)](/insights-expo/expo-ai-adoption-connect-jira-bamboohr#configure-change-failure-rate-cfr).
* **Lead Time and Deployment Frequency** require releases to be tracked per repo — through release detection (tagged releases, GitHub Releases, or a configured release event) or through releases you push with the [Manual Releases API](/insights-expo/expo-ai-adoption-manual-releases-api). Without either, both metrics show an empty state.
