---
title: "CapEx / OpEx Split"
description: "How GitKraken Insights calculates and displays CapEx / OpEx Split."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for CapEx / OpEx Split"
    card_icon: currency-dollar
    nav_category: metrics
    nav_label: "CapEx / OpEx Split"
    nav_order: 53
    nav_parent: expo-ai-adoption-impact-cost-metrics
    page_type: content
---
## CapEx / OpEx Split

> _Software capitalization breakdown — which engineering work counts as a capitalizable asset (CapEx) and which is operating expense (OpEx)._

**Family:** AI Impact & Cost · **Cadence:** Monthly grid · **Where it appears:** /ai-adoption/capex

### At a glance

In many jurisdictions, engineering work on new features and substantial enhancements can be **capitalized** — booked as an investment asset that depreciates over time, rather than expensed in the period. Maintenance, bug fixes, and operational work cannot. The CapEx / OpEx Split classifies every PR and commit into one of those two buckets so finance can produce defensible capitalization numbers without manually re-coding the engineering team's work.

It is one of the dashboard's most accountant-friendly outputs — and one of the harder pages to look at unless you are the one filing the schedule.

### Formula

```
For each PR or commit:
  effective_capex = COALESCE(capex_opex, capex_opex_auto, 'Uncategorized')

  capex_opex      — manual override stored on the PR / commit record
  capex_opex_auto — automatic classification from the PR / commit category
  'Uncategorized' — fallback when neither is set; aggregates as OpEx for reporting

Aggregated as effort-weighted hours per developer per month, split CapEx / OpEx.
```

### How GitKraken Insights calculates it

**Per-item classification.** Every PR and direct commit gets a `capex_opex_auto` value based on its category:

| Category | CapEx or OpEx |
| --- | --- |
| **Feature** | CapEx |
| **Enhancement** | CapEx |
| **Refactor** | CapEx |
| **Bug Fix** | OpEx |
| **Chore** | OpEx |
| **Docs** | OpEx (typically) |
| **Test** | Mixed (test for new feature = CapEx; test for bug fix = OpEx) |
| **Uncategorized** | Falls through the COALESCE; aggregates as OpEx in reporting. |

This is a deterministic mapping from the PR or commit category, _not_ an LLM inference at the CapEx step itself. (The category that drives it is LLM-classified.) Manual overrides (`capex_opex`) win the COALESCE.

**Aggregation.** For each developer × month, the backend sums the effort-weighted hours of CapEx work and OpEx work separately. The result is a monthly grid: each cell shows hours-of-CapEx and hours-of-OpEx for that developer in that month.

**Hours estimate.** Effort hours come from Effort Score × an internal conversion factor (calibrated empirically). It is an estimate, not a measurement.

### Why it matters

The CapEx side of engineering spend is depreciated over multiple years rather than expensed in the period. For most product engineering orgs, this materially reduces the period operating expense and changes how the business looks to finance.

Doing this classification by hand is painful — most companies don't do it well, or default to crude bucketing ("70% of engineering is capitalizable"). The dashboard's automatic per-PR classification gives you a defensible audit trail: every hour of capitalized engineering ties to a specific PR with a specific category.

For most teams using Insights, the CapEx / OpEx page is the highest-ROI single feature for finance and accounting stakeholders.

### How to read it

The grid view shows monthly columns × developer rows. Per cell, you see hours split CapEx vs. OpEx. The ratio is the interesting number:

| Engineering CapEx ratio | Read it as |
| --- | --- |
| **70%+** | Heavy product-build orgs typically land here. Strong defensibility. |
| **50–69%** | Mixed orgs — substantial product work plus meaningful maintenance. |
| **30–49%** | Maintenance-heavy or platform-heavy orgs. Defensible but lower capitalization. |
| **< 30%** | Mostly OpEx — likely SRE, support, or legacy maintenance team. |

The ratio shifts predictably with team type. SRE teams are mostly OpEx by design. New-product teams are mostly CapEx. Mature product teams sit in the middle.

### Where it appears

* **/ai-adoption/capex** — primary surface. Grid view with monthly columns and developer rows. Expandable rows show per-PR detail and a per-month trend chart. A velocity column shows the PR + commit breakdown. CSV export is available.

### Settings that affect it

* [**Developer Hourly Rate**](/insights-expo/expo-ai-adoption-settings#developer-hourly-rate) — translates hours into capitalized and expensed dollars in the cost rollup.
* Manual `capex_opex` override per PR or commit — settable from /ai-adoption/data-explorer when the auto-classification produces the wrong answer.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Effort Score](/insights-expo/expo-ai-adoption-output-metrics#effort-score-complexity) | Effort feeds into the hours estimate. |
| [Output Score](/insights-expo/expo-ai-adoption-output-metrics#output-score) | Same shipped work, different aggregation. |

### How to use it

* **Reconcile monthly with finance.** Export the CSV at the end of each month. Hand it to your accounting team. They can audit-trail back to individual PRs.
* **Adjust the category mapping if your accounting requires it.** The defaults reflect US GAAP-style "new functionality is capitalizable, maintenance is not." If your jurisdiction or accounting standard is different, talk to your account manager.
* **Use manual overrides for edge cases.** A "refactor" that is really maintenance-flavored work should be `capex_opex = OpEx`. A "bug fix" that is actually a new feature flag launch can be the reverse.
* **Don't treat the auto-classification as final without review.** For high-dollar-value capitalization decisions, sample a few PRs per month and verify the auto-category is right.

### Limitations and gotchas

* **Category accuracy depends on the LLM classifier and on PR title and description quality.** A PR titled "fix" that is actually a feature gets miscategorized. Manual overrides exist for this case.
* **Hours estimate is empirical, not timesheet-based.** Developers aren't asked to log time. The effort-to-hours conversion is a tuned constant.
* **Doesn't account for non-shipping work.** Time spent in meetings, planning, design review, or on-call doesn't land in PRs or commits.
* **Jurisdiction matters.** US GAAP, IFRS, and country-specific standards have different rules for what is capitalizable.
* **Test category is fuzzy.** Tests for new features should be CapEx with the feature; tests for bug fixes should be OpEx with the fix. The auto-classifier makes its best guess from PR scope but isn't always right.

### FAQ

**Q: Can finance trust these numbers for our audit?**
A: As a starting point, yes — the per-PR audit trail is the strongest feature. The auto-categorization should be sampled and reviewed by your accounting team before booking, especially the first time. Most teams find ~85% accuracy out of the box; manual overrides on the remaining 15% take an hour or two per month.

**Q: How does the hours estimate work?**
A: Effort Score (0.1 to 0.9) maps to an internal hours-per-effort-point constant calibrated against teams that have tracked time. It is an estimate. Order-of-magnitude reliable, not down-to-the-hour.

**Q: We want to track CapEx by project, not by developer. Is that possible?**
A: Currently the grid is per-developer per-month. Project-level rollup isn't directly supported — filter by team (if your projects map to teams) or use the CSV export and re-pivot.

**Q: My PR is misclassified as CapEx but it's really maintenance. How do I fix it?**
A: /ai-adoption/data-explorer → PRs tab → click the row → set `capex_opex` to OpEx. Saves immediately. Future aggregations will reflect the override.
