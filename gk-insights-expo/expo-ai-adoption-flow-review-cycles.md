---
title: "Review Cycles"
description: "How GitKraken Insights calculates and displays Review Cycles."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for Review Cycles"
    card_icon: clock
    nav_category: metrics
    nav_label: "Review Cycles"
    nav_order: 32
    nav_parent: expo-ai-adoption-flow-metrics
    page_type: content
---
## Review Cycles

> _The average number of "changes requested" reviews a PR receives before it gets merged. Bucketed as 0 / 1 / 2 / 3+._

**Family:** Flow & Cycle Time · **Cadence:** Per PR, aggregated · **Where it appears:** /ai-adoption/ai-impact, PR drill-down tables

### At a glance

Review Cycles counts how many rounds of "changes requested" each PR went through. A PR merged with zero changes-requested reviews has 0 cycles (a clean first-pass). One round of "please fix X" before approval is 1 cycle. Two rounds is 2. Three or more is 3+.

It is one of the dashboard's better diagnostic metrics — a high cycle count is almost always either too-big PRs, unclear specs, or mismatched author and reviewer expectations. Each of those has a different fix.

### Formula

```
Review Cycles per PR = number of CHANGES_REQUESTED reviews before merge

Bucketed as 0 / 1 / 2 / 3+ for aggregate views
```

The PR record stores `approved_at` (the first APPROVED-review timestamp) and `review_cycles` (the count of CHANGES_REQUESTED reviews).

### How GitKraken Insights calculates it

For each merged PR, the backend counts reviews with state `CHANGES_REQUESTED` that occurred before the PR was merged. That count is stored on the PR record as `review_cycles`.

For aggregate views (team-level, org-level), the metric is shown either as the mean number of cycles, or as the distribution across the four buckets (0 / 1 / 2 / 3+) as stacked bars or breakdown lines.

The buckets are the more useful presentation — they show the _shape_ of the problem rather than just the average. A team averaging 1.0 cycles could be evenly distributed across the buckets or could be "everyone gets exactly 1 cycle." Those need different fixes.

### Why it matters

Review Cycles tells you about a few different problems at once:

* **PR size.** Big PRs accumulate more reviewable surface area, more review feedback, more cycles. High cycles usually correlate with high effort scores.
* **Spec clarity.** When developers don't have a clear spec, the review _becomes_ the spec. Cycles spike.
* **Reviewer-author calibration.** A reviewer who reflexively requests changes on every PR will inflate cycles regardless of PR quality.
* **Style guide drift.** PRs failing for inconsistent style or lint should not show up here — but if your team's automation isn't catching those, they will.

Compared to Cycle Time, Review Cycles is more _causal_. Cycle Time tells you it took 5 days; Review Cycles tells you it took 5 days _because_ of three rounds of back-and-forth.

### How to read it

| Team average | Read it as |
| --- | --- |
| **< 0.5** | Strong — most PRs go through clean. Code quality at submission is high. |
| **0.5 – 1.0** | Healthy — typical for orgs with good but not great PR discipline. |
| **1.0 – 2.0** | Fair — every PR averages a round of revisions. Look at PR size and spec clarity. |
| **> 2.0** | Needs attention — chronic back-and-forth. PRs are likely too big, or specs are too thin. |

The distribution matters as much as the mean. A team with 60% at 0 cycles, 30% at 1, and 10% at 3+ has a small problem cohort. A team with 50% at 0, 30% at 1, 15% at 2, 5% at 3+ has the same mean but a worse tail.

### Where it appears

* **/ai-adoption/ai-impact** — Review Cycles bucketed breakdown is available as a dimension on Cycle Time and PR Volume charts.
* **PR drill-down tables** — every PR drill-down has a Cycles column.

### Settings that affect it

None. Review Cycles is a measurement.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [First-Pass Rate](#first-pass-rate) | The "0 cycles" percentage, expressed as a positive metric. |
| [Cycle Time](#cycle-time) | Long Review phase is usually high Review Cycles. |
| [Effort Score](/insights-expo/expo-ai-adoption-output-metrics#effort-score-complexity) | High-effort PRs tend to have more review cycles. Track together. |

### How to improve it

* **Split big PRs.** The single most effective intervention. PRs that take 15 minutes to review get 0 cycles. PRs that take 90 minutes to review get 3.
* **Write better PR descriptions.** A PR with a clear "what / why / how to test" description gets approved faster than one with title only. AI-assisted PR descriptions are a low-effort win here.
* **Tighten the spec before coding.** PRs that change scope during review are PRs that get cycles. Get spec alignment in the design stage, not the review stage.
* **Calibrate reviewers.** If one reviewer's PRs always get more cycles than peers, have a quick conversation. Reviewer perfectionism is a fixable habit.
* **Automate style enforcement.** Lint failures, formatting nits, and type errors should never be in a human review. If they are, your CI isn't catching them.

### Limitations and gotchas

* **Doesn't measure the _quality_ of feedback.** A PR with three cycles that resulted in genuinely better code is a healthy outcome; a PR with 0 cycles that shipped a bug is not. Use Review Cycles alongside CFR.
* **Doesn't count dismissed reviews.** A "changes requested" that gets dismissed without action still counts as a cycle.
* **A PR that bypasses formal review** (e.g. small fix from a senior IC, merged after a comment) shows as 0 cycles.

### FAQ

**Q: A team has high Review Cycles but low CFR. Are we just being too picky?**
A: Possibly, but probably not. Thorough review catches real problems before they ship — high cycles + low CFR is the trade. The interesting question is whether the _time cost_ of those cycles outweighs the bug-prevention value.

**Q: One developer's PRs have 4× the cycles of the team average. Is that a performance issue?**
A: Maybe, but check the work first. PRs in unfamiliar domains, big refactors, or risky changes naturally accumulate review feedback.

**Q: Does this count "approving but with suggestions" reviews?**
A: No. Only formal CHANGES_REQUESTED reviews count.
