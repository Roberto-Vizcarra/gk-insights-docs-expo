---
title: "Throughput"
description: "How GitKraken Insights calculates and displays Throughput."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for Throughput"
    card_icon: package
    nav_category: metrics
    nav_label: "Throughput"
    nav_order: 22
    nav_parent: expo-ai-adoption-output-metrics
    page_type: content
---
## Throughput

> _Number of merged PRs in the window, expressed per week per developer for cross-team comparison._

**Family:** Output & Throughput · **Cadence:** Window-based · **Where it appears:** /ai-adoption/executive, /ai-adoption/ai-tools-comparison, /ai-adoption/ai-impact

### At a glance

Throughput is the raw count metric — how many PRs your team merged. It is the simpler, older sibling of [Output Score](#output-score). Output Score is generally a better metric for cross-team or cross-window comparison because it weights for effort. Throughput is better when you want a clean count over time _for the same team_ (where effort distribution is roughly constant) or when you need a quick gut check.

### Formula

```
Throughput (per active dev per week) = merged PRs / active developers / weeks in window
Throughput (team total)              = SUM(merged PRs in window)
```

Drafts and bot-authored PRs are excluded.

### How GitKraken Insights calculates it

**Step 1.** Count merged PRs in the window. A PR is "in the window" if its `merged_at` timestamp falls within `[from, to)`. Drafts are excluded. PRs whose author git-provider login matches a bot pattern (e.g. `*[bot]`) are excluded.

**Step 2.** For team-level views, divide by the count of active developers and by the number of weeks in the window. The result is PRs per active dev per week — the comparison-ready rate.

**Step 3.** Per-developer Throughput is the raw count of that developer's merged PRs in the window. The /ai-adoption/developers cell shows it as a clickable breakdown (X opened · Y merged · Z direct); clicking opens the drill-down sheet.

**Who counts as "active."** An active developer for the window has `is_active = true`, was not fully on PTO across the window, and contributed at least one merged PR, direct commit, or formal review.

### Why it matters

Two things Throughput tells you that Output Score doesn't:

1. **How many distinct things shipped.** If your team's count drops while Output Score stays steady, you are shipping fewer-but-bigger PRs. That can be a good thing (right-sizing) or a bad thing (PRs growing because review queues are slow). Throughput is the signal.
2. **A quick gut check.** Output Score requires Effort Score to be assigned, which requires the LLM classifier to have run. Throughput is computable instantly from any window of PR data.

### How to read it

| Per active dev per week | Read it as |
| --- | --- |
| **≥ 4** | High — typical for teams shipping many small focused PRs |
| **2 – 3.9** | Solid — typical mainstream pattern |
| **1 – 1.9** | Low — investigation worthwhile (big PRs in flight? review bottleneck? infra work?) |
| **< 1** | Minimal — probably either a stalled team or a deep-work team that is mid-feature |

The "right" number depends on PR size discipline. A team shipping 0.5-effort PRs comfortably runs at 4 per dev per week. A team shipping 0.9-effort PRs may live at 1 per dev per week and be just as effective.

### Where it appears

* **/ai-adoption/executive** — Throughput as one of the headline trend lines.
* **/ai-adoption/ai-tools-comparison** — cohort comparisons display Throughput alongside Output Score.
* **/ai-adoption/ai-impact** — PR Volume trends in the System Metrics view.

### Settings that affect it

None. Throughput is a raw count — no admin knobs change it. If you want effort-weighted shipping with knobs, see [Output Score](#output-score).

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Output Score](#output-score) | The effort-weighted version of the same shipping activity. |
| [Cycle Time](/insights-expo/expo-ai-adoption-flow-metrics#cycle-time) | The "how fast" pair to Throughput's "how many." Read them together. |
| [WIP](/insights-expo/expo-ai-adoption-flow-metrics#work-in-progress-wip) | Number of currently-open PRs. Throughput is the _outflow_; WIP is the _backlog_. |
| [Deployment Frequency](/insights-expo/expo-ai-adoption-dora-metrics#deployment-frequency) | DORA's release-based sibling — same concept, counted from release events instead of merges. |

### How to improve it

* **Right-size PRs.** Teams shipping fewer-but-huge PRs often have low Throughput. The fix is usually upstream: smaller, more frequent merges. Track via Cycle Time alongside Throughput.
* **Clear stale open PRs.** A high WIP with low Throughput is the classic review-bottleneck pattern. Walk the oldest open PRs each week.
* **Reduce review cycles.** If every PR averages 2+ "changes requested" rounds, Throughput stalls. See [Review Cycles](/insights-expo/expo-ai-adoption-flow-metrics#review-cycles).

### Limitations and gotchas

* **Raw counts are misleading for cross-team comparison.** A team merging 20 0.1-effort docs PRs per week looks more productive than a team merging 3 0.9-effort migration PRs per week. Use Output Score instead when comparing.
* **Throughput drops during long features.** Teams working on multi-week initiatives may legitimately show low Throughput. Pair with WIP to distinguish "stuck" from "deep-work."
* **Bots are excluded.** Dependabot, security bots, and similar tools don't inflate Throughput.

### FAQ

**Q: Why two metrics for shipping (Throughput + Output Score)?**
A: They answer different questions. Throughput is "how many?" Output Score is "how much real work?" Both are useful; neither alone is sufficient.

**Q: A team has high Throughput but low Output Score. What does that mean?**
A: They are shipping many small things. That is fine for some work (UI iteration, bug fixes) and a warning sign for others (a backend team should usually have substantial PRs in the mix).

**Q: Does the count include PRs that were merged but reverted?**
A: Yes. Reverts are tracked as a separate signal but don't subtract from Throughput in the standard view.
