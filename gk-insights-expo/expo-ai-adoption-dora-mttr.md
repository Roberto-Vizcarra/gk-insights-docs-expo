---
title: "Mean Time to Recovery (MTTR)"
description: "How GitKraken Insights calculates and displays Mean Time to Recovery (MTTR)."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for Mean Time to Recovery (MTTR)"
    card_icon: shield-check
    nav_category: metrics
    nav_label: "Mean Time to Recovery (MTTR)"
    nav_order: 44
    nav_parent: expo-ai-adoption-dora-metrics
    page_type: content
---

## Mean Time to Recovery (MTTR)

> _Mean hours from incident open to incident close. The DORA recovery metric._

**Family:** DORA & Quality · **Cadence:** Per window, per team or org · **Where it appears:** /ai-adoption/board-metrics

### At a glance

MTTR is "when things break, how fast do you fix them?" It is the second DORA stability metric, alongside [CFR](#change-failure-rate-cfr). Where CFR asks "how often does shipping cause problems?", MTTR asks "when shipping does cause problems, how fast do you recover?" Together they describe your stability profile.

A team with high CFR but low MTTR is shipping bugs but fixing them fast — uncomfortable, but recoverable. A team with low CFR but high MTTR ships clean most of the time but struggles when something does go wrong — unusual but possible. The healthiest profile is low on both.

### Formula

```
MTTR (hours) = mean(resolved_at − opened_at) for customer-bug
                incidents resolved in the window
```

### How GitKraken Insights calculates it

**Source data.** The same Jira customer-bug stream that powers CFR. Each Jira incident has a creation timestamp (`opened_at`) and a resolution timestamp (`resolved_at`), stored in the `jira_incidents` table.

**Computation.** For each resolved incident in the window, compute the duration in hours. Take the mean across incidents. (Median is more robust to outliers and is shown on hover for most charts.)

**Open incidents are excluded.** Only resolved incidents count toward MTTR — an open incident doesn't have a resolution time yet, so it cannot contribute to a mean recovery time.

### Why it matters

MTTR is the metric that distinguishes "we ship bugs" from "we ship bugs and they bleed into our quarter." A team with elite MTTR can take risks on shipping speed because they know they can recover fast. A team with poor MTTR has to be more conservative because every bug becomes a slow drag.

For AI adoption: AI tools sometimes help MTTR (faster debugging with Claude / Codex), and sometimes hurt (autonomous AI-assisted commits introducing subtle bugs that take longer to debug). Worth watching alongside CFR by AI Tier.

### How to read it

DORA bands for MTTR:

| Band | MTTR | Pattern |
| --- | --- | --- |
| **Elite** | < 1 hour | Production hotfixes within the hour |
| **High** | 1 hour – 1 day | Same-day recovery |
| **Medium** | 1 day – 1 week | Recovery within a week |
| **Low** | > 1 week | Slow recovery — a risk amplifier |

For most product engineering teams, **High** is the realistic target. Elite requires investment in observability, runbooks, and on-call practices that not every team needs.

### Where it appears

* **/ai-adoption/board-metrics** — MTTR as one of the four DORA cards.

### Settings that affect it

* `JIRA_CUSTOMER_BUG_FIELD_ID` — required for MTTR to populate (same as CFR).

### Related metrics

| Metric | Relationship |
| --- | --- |
| [CFR](#change-failure-rate-cfr) | The "how often" stability metric. MTTR is the "how fast to recover" sibling. |
| [Lead Time](#lead-time-for-changes) | The velocity equivalent. Lead Time and MTTR are sometimes confused — Lead Time covers shipping changes; MTTR covers recovering from incidents. |

### How to improve it

* **Invest in observability.** Most MTTR is dominated by "how long it took to find the problem." Better dashboards, alerts, and tracing usually move MTTR more than faster fixes do.
* **Write runbooks.** A team that knows what to do when a specific alert fires recovers faster than one that has to figure it out each time.
* **Practice incident response.** Game days, recovery drills, and post-mortem rituals all reduce real-incident MTTR.
* **Use AI-assisted debugging.** A real win — Claude and Codex can walk through stack traces and recent changes faster than humans on the first pass. Practice using AI as the first debugging step.

### Limitations and gotchas

* **Means are skewed by outliers.** One incident that took a week to fix can dominate a monthly MTTR. Check median alongside.
* **Open incidents aren't counted.** A team with many open old incidents can have artificially good MTTR (only the resolved ones contribute). Watch open-incident count alongside.
* **Severity is not stratified in the MTTR metric today.** CFR tracks bug counts by severity (Critical / High / Medium / Low), but MTTR is a single mean across all customer-bug incidents. If you need severity-aware recovery times, drill into the CFR breakdown table where severity is captured.
* **No automatic incident detection.** The metric uses Jira customer bugs as the proxy. If your team uses a separate incident management system (PagerDuty, Statuspage), MTTR in this dashboard won't reflect those events.

### FAQ

**Q: Should MTTR include the time to acknowledge?**
A: It currently includes everything from Jira open to Jira close. If your Jira workflow distinguishes "acknowledged" from "open," you would need a custom view — not currently in the dashboard.

**Q: Why doesn't MTTR show on /ai-adoption/ai-impact alongside CFR?**
A: They are related but different views. CFR is the broader stability metric most leaders watch, so it is featured on the AI Impact page. MTTR is the operational recovery metric and lives in /ai-adoption/board-metrics alongside the rest of DORA.
