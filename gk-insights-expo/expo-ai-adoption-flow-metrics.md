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
<kbd>Last updated: September 2026</kbd>

This family answers: **how fast does work move through your system, and where does it get stuck?**

Where the Output family asks "how much shipped?", Flow asks "how smooth was the journey from idea to merge?" These are the operational metrics — the ones team leads watch weekly and engineering leaders read for systemic patterns.

---

## The metrics

| Metric | What it captures |
| --- | --- |
| [**Cycle Time**](#cycle-time) | Total hours from first commit to merge (or deploy). The headline flow metric. Broken into four phases: Coding, Pickup, Review, Deploy. |
| [**Review Cycles**](#review-cycles) | Average rounds of "changes requested" per PR before merge. A signal of PR size and review fit. |
| [**First-Pass Rate**](#first-pass-rate) | Percent of PRs merged with zero "changes requested" reviews. The clean-merge rate. |
| [**WIP**](#work-in-progress-wip) | Number of currently open PRs. The work-in-progress backlog. |

---

## The four cycle phases

When you see Cycle Time broken down, these are the phases:

| Phase | What it measures |
| --- | --- |
| **Coding** | First commit to PR open. The "writing the code" time. |
| **Pickup** | PR open to first review activity. The "PR sitting unattended" time. |
| **Review** | First review to last review-related event. The "back and forth" time. |
| **Deploy** | Merge to production deploy (only populated when release detection is configured for the repo). |

Reading Cycle Time _by phase_ is almost always more useful than reading the total. A 5-day Cycle Time that is 80% Pickup tells you something very different than a 5-day Cycle Time that is 80% Coding.

---

## Read this family

Three common patterns:

1. **Long Pickup phase** → review queue is over-committed. Symptoms: WIP rising, fewer reviewers active. Fix: review-clearing ritual, reviewer rotation.
2. **Long Review phase** → PRs too big, or reviewers asking for too many changes. Symptoms: high Review Cycles, low First-Pass Rate. Fix: split PRs, write better PR descriptions.
3. **Long Coding phase** → could be deep work (fine) or stalled work (not fine). Cross-check with WIP and developer-level activity to tell which.

---

## Where this family shows up

* **/ai-adoption/ai-impact** — Cycle Time chart with phase breakdown, Totals/Trends toggle, and the Cycle-Time-by-AI-Tier view.
* **/ai-adoption/teams** — Cycle Time column on the team table; expanded rows show the trend and phase breakdown.
* **/ai-adoption/executive** — Cycle Time as a hero KPI and trend line.
* **/ai-adoption/developers** — per-developer cycle time in the expanded developer detail.
* **PR drill-down tables** — clicking into a Cycle Time or PR Volume chart point opens a table with per-PR Cycle Time, Review Cycles, and Effort columns.

---

## A note on the "Include JIRA start time" toggle

When the "Include JIRA start time in Coding phase" toggle is on, the Coding phase extends backward to include the time between the JIRA issue's in-progress transition and the first commit. This gives a more complete picture of "time from commitment to ship" — but it requires JIRA integration and a consistent in-progress workflow.

The toggle is on by default in the app. If your team doesn't use JIRA, or your in-progress workflow is inconsistent, turn it off so Cycle Time starts at first commit instead.
