---
title: "First-Pass Rate"
description: "How GitKraken Insights calculates and displays First-Pass Rate."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for First-Pass Rate"
    card_icon: clock
    nav_category: metrics
    nav_label: "First-Pass Rate"
    nav_order: 33
    nav_parent: expo-ai-adoption-flow-metrics
    page_type: content
---
## First-Pass Rate

> _Percent of PRs merged with zero "changes requested" reviews. The clean-merge rate._

**Family:** Flow & Cycle Time · **Cadence:** Per window, percent across all merged PRs · **Where it appears:** /ai-adoption/ai-impact

### At a glance

First-Pass Rate is the inverse of [Review Cycles](#review-cycles), expressed as the percentage of PRs that get merged without a single round of "please fix X." It is the cleanest signal in the dashboard for "is our code quality at submission high?" A high rate means developers are submitting work that is ready to ship. A low rate means either submissions are sloppy or reviews are nitpicky — the next step is to drill into Review Cycles to find out which.

### Formula

```
First-Pass Rate = count(merged PRs with 0 review cycles) / count(merged PRs in window) × 100%
```

### How GitKraken Insights calculates it

The backend counts merged PRs in the window. For each, it checks whether `review_cycles = 0` (no CHANGES_REQUESTED reviews before merge). The count of zero-cycle PRs is divided by the total and expressed as a percentage.

Drafts and bot-authored PRs are excluded. Direct commits are excluded because they don't go through formal review.

### Why it matters

First-Pass Rate is one of the few metrics where the _direction_ is interpretable on its own without context. A higher rate is almost always better, with one exception (see "Limitations" below).

It is especially valuable as a leading indicator for AI adoption ROI. When teams successfully integrate AI into their workflow, First-Pass Rate climbs — AI helps catch the small issues that would have triggered a review cycle (missing tests, naming inconsistencies, edge cases). Watching First-Pass Rate trend up alongside Adoption Score is one of the cleanest "AI is paying off" stories you can tell.

### How to read it

| Rate | Read it as |
| --- | --- |
| **75 – 100%** | Strong — most PRs ship clean. Code quality and review fit are aligned. |
| **55 – 74%** | Healthy — typical for high-functioning engineering teams. |
| **35 – 54%** | Fair — every other PR needs a revision round. Investigate PR size and spec clarity. |
| **< 35%** | Needs attention — review is doing the heavy lifting of design or spec work. |

**One nuance:** rates above \~85% can also signal _rubber-stamping_. If a team's First-Pass Rate is 95% _and_ CFR is rising, your reviewers may not be looking hard enough. Read First-Pass Rate alongside [CFR](/insights-expo/expo-ai-adoption-dora-metrics#change-failure-rate-cfr).

### Where it appears

* **/ai-adoption/ai-impact** — available as a breakdown dimension on PR Volume charts.

### Settings that affect it

None. First-Pass Rate is a measurement.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Review Cycles](#review-cycles) | First-Pass Rate is "the % at 0 cycles" — same data, different framing. |
| [Cycle Time](#cycle-time) | High First-Pass Rate tightens the Review phase. |
| [CFR](/insights-expo/expo-ai-adoption-dora-metrics#change-failure-rate-cfr) | Always read together. High First-Pass + high CFR = rubber-stamping. |

### How to improve it

* **Shrink PR scope.** Smaller PRs ship clean more often. This is the same root-cause fix as for Review Cycles.
* **Strengthen the pre-review checklist.** A simple PR template with "tests added? docs updated? changelog?" catches the trivial revisions before they reach a reviewer.
* **Adopt AI-assisted self-review.** Have AI review your own PR before requesting human review. It catches the small stuff (typos, naming, missing tests) reliably.
* **Calibrate reviewers as a team.** If half your team has 80% First-Pass Rate and half has 40%, the variance is the reviewer, not the code.

### Limitations and gotchas

* **High rates can mean rubber-stamping.** Always cross-check with CFR. If both are healthy, you have a great team. If First-Pass is high but CFR is rising, your reviewers are letting things through.
* **Single-reviewer dynamics.** Teams where one or two developers do all the reviewing will have rates dominated by those reviewers' personalities, not by the broader team's quality.
* **Trivial PRs inflate the rate.** A team that ships lots of formatting or dep-bump PRs will have high First-Pass Rate that doesn't reflect substantive work. Filter to Effort Score 0.3+ for a more meaningful read.

### FAQ

**Q: Is 100% First-Pass Rate a good thing?**
A: Only if your CFR is also low. A team that _never_ requests changes is a team that is either preternaturally good or not reviewing. Probably the latter, statistically.

**Q: A developer has 30% First-Pass Rate. Is that a performance issue?**
A: Not on its own. They may be working on harder problems, more contested code areas, or under tighter spec ambiguity. Use it as a conversation starter, not a verdict.

**Q: Does this include PRs where someone leaves a comment but doesn't formally request changes?**
A: No. Comments without a CHANGES_REQUESTED state don't reduce First-Pass Rate. Only formal change requests count.
