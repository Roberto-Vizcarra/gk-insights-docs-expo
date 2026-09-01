---
title: "Direct Commits"
description: "How GitKraken Insights calculates and displays Direct Commits."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for Direct Commits"
    card_icon: package
    nav_category: metrics
    nav_label: "Direct Commits"
    nav_order: 23
    nav_parent: expo-ai-adoption-output-metrics
    page_type: content
---
## Direct Commits

> _Commits pushed straight to a default branch without a PR. Counted alongside merged PRs as shipped work, with a configurable weight._

**Family:** Output & Throughput · **Cadence:** Window-based · **Where it appears:** /ai-adoption/developers, /ai-adoption/teams, /ai-adoption/capex, /ai-adoption/data-explorer

### At a glance

Not all shipped work goes through a pull request. Some teams (especially those running Trunk-Based Development) push directly to `main` or `master` for hotfixes, dependency upgrades, or any change small enough to bypass review. The Direct Commits column captures that activity so developers who do real work this way still appear in the dashboard's output metrics.

Each direct commit carries an Effort Score (0.1 to 0.9) just like a PR. It is added to Output Score, scaled down by the **Direct Commit Weight** setting (default 0.5).

### Formula

```
Direct Commit contribution to Output Score = DCWeight × SUM(DC effort)

  DC effort = COALESCE(effort_score, auto_effort_score, 0) per commit
  DCWeight  = Direct Commit Weight setting (default 0.5)
```

### How GitKraken Insights calculates it

**What counts as a direct commit.** A commit pushed to a default branch (`main`, `master`, or the repo's configured default) whose **author** is one of your roster developers and whose author is not a bot (login matching `*[bot]`). Empty commits are excluded.

**Effort Score.** Every direct commit gets an Effort Score from the LLM classifier (`auto_effort_score`) using the same five-tier rubric as PRs. Manual overrides are supported (`effort_score`). The displayed value is `COALESCE(effort_score, auto_effort_score, 0)`.

**Aggregation.** Per-developer direct-commit effort sums separately from PR effort. The two sums are then combined in the Output Score formula with DCWeight applied to the DC sum.

**Chore handling.** If the "Exclude Chore from Output Score" toggle is on (default), Chore-category direct commits are removed from the effort sum but their count is preserved in the displayed breakdown.

### Why it matters

Direct commits used to disappear from productivity dashboards entirely because the dashboards assumed every shipped change went through a PR. That made developers running TBD workflows look low-output even when they were shipping meaningfully. By counting direct commits — weighted appropriately — we close that gap.

The 0.5 default reflects empirical reality: direct commits skew smaller and less-reviewed than PRs. Treating them at 1:1 with PRs would over-credit pure direct-commit workflows; treating them at 0 would erase a legitimate work pattern.

### How to read it

Don't read direct commits in isolation — read them as part of the Output Score breakdown. The /ai-adoption/developers cell shows the count as part of "X opened · Y merged · Z direct."

| Pattern | What it suggests |
| --- | --- |
| **Most output is PRs** | Standard PR-based workflow |
| **Roughly equal PR and direct work** | Mixed workflow, often platform or SRE teams |
| **Mostly direct commits** | Trunk-Based Development, or a hotfix-heavy week |
| **Direct commits rising sharply** | Investigate — could be legit TBD adoption or could be PRs being bypassed |

### Where it appears

* **/ai-adoption/developers** — Direct Commits count in the Output Score breakdown cell.
* **/ai-adoption/teams** — per-team direct-commit counts inside the developer sub-table.
* **/ai-adoption/capex** — direct commits carry CapEx vs OpEx categorization the same as PRs.
* **/ai-adoption/data-explorer** — Direct Commits tab shows every commit with full metadata.

### Settings that affect it

* [**Direct Commit Weight**](/insights-expo/expo-ai-adoption-settings#direct-commit-weight) — the multiplier. 0 = ignore direct commits in Output. 1 = treat equally to PRs.
* [**Exclude Chore from Output Score**](/insights-expo/expo-ai-adoption-settings#exclude-chore-from-output-score) — affects whether Chore-category direct commits contribute to the effort sum.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Output Score](#output-score) | Direct commits are the DC term of the formula. |
| [Effort Score](#effort-score-complexity) | Each direct commit gets one. |
| [Throughput](#throughput) | Throughput counts PRs only; direct commits live in this metric. |

### How to use the weight

* **Lower it (toward 0)** if your team uses direct commits primarily for trivial maintenance and you don't want them inflating Output Score.
* **Raise it (toward 1)** if your team runs TBD and most substantive work goes via direct commit.
* **Leave at 0.5** if you have a mixed workflow — this is the empirically-tuned default.

### Limitations and gotchas

* **Bot commits are excluded.** Dependabot's auto-merged dependency commits, security-bot patches, and similar don't contribute.
* **Force-pushed history is whatever GitHub reports last.** If a developer rewrites direct-commit history via force-push, the displayed count reflects post-rewrite history.
* **Co-authored commits attribute to the primary author.** GitHub's Co-authored-by trailer doesn't fan out into multiple developer scores.
* **The classifier may lag.** A direct commit pushed in the last hour may not have an Effort Score yet, so its contribution to Output Score is temporarily 0.

### FAQ

**Q: Why are direct commits weighted lower than PRs?**
A: Most direct commits are smaller and less-reviewed than typical PRs. The 0.5 default reflects that. If your team's direct-commit work is substantive, raise the weight.

**Q: A developer's commits aren't showing up. Why?**
A: Most likely their `is_active` is false, or their git-provider login on the roster doesn't match the actual commit author. Check Settings → Developers.

**Q: Do reverts count as direct commits?**
A: Yes — they are commits pushed to the default branch, so they appear in the count. The Effort Score classifier usually labels them low-effort.
