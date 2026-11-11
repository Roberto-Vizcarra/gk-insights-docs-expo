---
title: "Mean Time to Recovery (MTTR)"
description: "How GitKraken Insights calculates and displays Mean Time to Recovery (MTTR)."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: gk-insights
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for Mean Time to Recovery (MTTR)"
    card_icon: shield-check
    nav_category: metrics
    nav_label: "Mean Time to Recovery (MTTR)"
    nav_order: 44
    nav_parent: ai-adoption-dora-metrics
    page_type: content
---
<kbd>Last updated: November 2026</kbd>

## Mean Time to Recovery (MTTR)

> _Median hours from incident open to incident close. The DORA recovery metric._

**Family:** DORA & Quality · **Cadence:** Per window, per team or org · **Where it appears:** not currently shown on the standard Insights pages

### At a glance

MTTR is "when things break, how fast do you fix them?" It is the second DORA stability metric, alongside [CFR](/gk-insights/ai-adoption-dora-change-failure-rate). Where CFR asks "how often does shipping cause problems?", MTTR asks "when shipping does cause problems, how fast do you recover?" Together they describe your stability profile.

A team with high CFR but low MTTR is shipping bugs but fixing them fast — uncomfortable, but recoverable. A team with low CFR but high MTTR ships clean most of the time but struggles when something does go wrong — unusual but possible. The healthiest profile is low on both.

### Formula

```
MTTR (hours) = median(resolved_at − opened_at) for customer-bug
                incidents resolved in the window
```

### How GitKraken Insights calculates it

**Source data.** The same customer bugs from your connected issue tracker (Jira, Linear, YouTrack, Azure Boards, or [ClickUp](/gk-insights/ai-adoption-connect-clickup)) that power CFR. Each bug has a creation time and a resolution time.

**Computation.** For each resolved incident in the window, compute the duration in hours. Take the median across incidents.

**Open incidents are excluded.** Only resolved incidents count toward MTTR — an open incident does not have a resolution time yet, so it cannot contribute to a recovery time.

### Why it matters

MTTR is the metric that distinguishes "we ship bugs" from "we ship bugs and they bleed into our quarter." A team with elite MTTR can take risks on shipping speed because they know they can recover fast. A team with poor MTTR has to be more conservative because every bug becomes a slow drag.

For AI adoption: AI tools sometimes help MTTR (faster debugging with Claude / Codex), and sometimes hurt (autonomous AI-assisted commits introducing subtle bugs that take longer to debug). Watch it alongside CFR by AI Tier.

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

MTTR is not currently shown on the standard Insights pages, including Board Metrics and Throughput & Quality.

### Settings that affect it

* **Enable defect detection** and **Match by** (Settings → Data Connections → the tracker connection) — required for MTTR to populate (same as CFR). Select **Add defect rule** to add up to five named rules per connection, each with its own **Match by**.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [CFR](/gk-insights/ai-adoption-dora-change-failure-rate) | The "how often" stability metric. MTTR is the "how fast to recover" sibling. |
| [Lead Time](/gk-insights/ai-adoption-dora-lead-time) | The velocity equivalent. Lead Time and MTTR are sometimes confused — Lead Time covers shipping changes; MTTR covers recovering from incidents. |

### How to improve it

* **Invest in observability.** Most MTTR is dominated by "how long it took to find the problem." Better dashboards, alerts, and tracing usually move MTTR more than faster fixes do.
* **Write runbooks.** A team that knows what to do when a specific alert fires recovers faster than one that has to figure it out each time.
* **Practice incident response.** Game days, recovery drills, and post-mortem rituals all reduce real-incident MTTR.
* **Use AI-assisted debugging.** A real win — Claude and Codex can walk through stack traces and recent changes faster than humans on the first pass. Practice using AI as the first debugging step.

### Limitations and gotchas

* **The median hides the long tail.** One incident that took a week to fix barely moves a monthly median MTTR. Review your longest-running incidents in your tracker as well.
* **Open incidents are not counted.** A team with many open old incidents can have artificially good MTTR (only the resolved ones contribute). Watch open-incident count alongside.
* **MTTR is not split by priority.** CFR tracks bug counts by priority (Highest / High / Medium / Low, plus Other), but MTTR is a single median across all customer-bug incidents. If you need priority-aware recovery times, compare resolution times by priority in your tracker.
* **No automatic incident detection.** The metric uses customer bugs from your connected issue tracker as the proxy. If your team uses a separate incident management system (PagerDuty, Statuspage), MTTR in this dashboard does not reflect those events.

### FAQ

**Q: Should MTTR include the time to acknowledge?**
A: MTTR includes everything from the bug being opened to closed in your tracker. If your tracker workflow distinguishes "acknowledged" from "open," you would need a custom view, and the dashboard does not offer one.

**Q: Where can I see MTTR?**
A: MTTR is not currently shown on the standard Insights pages — not on Board Metrics, Throughput & Quality, or AI Impact. CFR, the broader stability metric most leaders watch, is on all three.
