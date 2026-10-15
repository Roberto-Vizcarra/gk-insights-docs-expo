---
title: "Productivity Uplift"
description: "How GitKraken Insights calculates and displays Productivity Uplift."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for Productivity Uplift"
    card_icon: currency-dollar
    nav_category: metrics
    nav_label: "Productivity Uplift"
    nav_order: 51
    nav_parent: expo-ai-adoption-impact-cost-metrics
    page_type: content
---
## Productivity Uplift

> _Estimated productivity gain attributable to AI adoption. Computed three ways: volume speedup vs. a baseline period, per-developer uplift comparing active to other developers, and Power-User uplift comparing top-tier developers to the rest._

**Family:** AI Impact & Cost · **Cadence:** Window vs. baseline comparison · **Where it appears:** /ai-adoption/ai-impact, /ai-adoption/executive

### At a glance

Productivity Uplift is the dashboard's attempt to answer the question every CFO asks: "what is AI actually doing for us?" The dashboard computes three related comparisons and surfaces them on the Productivity hero cards:

1. **Volume speedup** — current changes-per-dev-per-week vs. baseline period.
2. **Per-developer uplift** — active developers vs. other developers, within the current window.
3. **Power-User uplift** — top-tier developers vs. the rest, within the current window.

All three are **estimates**, built from rate comparisons that each carry uncertainty. Use these numbers for storytelling and order-of-magnitude framing — not for finance reconciliation.

### Formulas

```
VolumeSpeedupFactor          = TreatmentChangesPerDevPerWeek / ControlChangesPerDevPerWeek
EstimatedHoursSavedPerDevWeek = (VolumeSpeedupFactor − 1) × Working Hours Per Week (default 40)
EstimatedValueSaved          = EstimatedHoursSavedPerDevWeek × sum of developer hourly rates

PerDevUpliftPct        = (ActiveAvgChangesPerDev − OtherAvgChangesPerDev) / OtherAvgChangesPerDev × 100
PowerUserPerDevUpliftPct = (PowerUserAvgChangesPerDev − BelowPowerAvgChangesPerDev) / BelowPowerAvgChangesPerDev × 100
PotentialUpliftPct     = additional org-wide changes if all developers moved to Active tier, as % of current total
```

### How GitKraken Insights calculates it

**Step 1 — Establish the baseline.** The baseline is a single reference month, set in Settings → General as [Baseline Period](/insights-expo/expo-ai-adoption-settings#baseline-period). Where it can be detected, it's set automatically to a month about 90 days before your organization first connected a repository; an admin can pick a different month. Rates are compared per developer per week, so the current window and the baseline month compare directly.

**Step 2 — Compute the volume speedup.** We measure changes-per-developer-per-week in both windows. If the baseline rate is at least 1 change per dev per week (a guard against divide-by-near-zero — see the minimum-baseline guard below), the ratio gives the volume speedup factor. A factor of 1.5 means the team is shipping 50% more changes per dev per week than baseline.

**Step 3 — Estimate hours and dollars saved.** Multiply `(VolumeSpeedupFactor − 1)` by your org's [Working Hours Per Week](/insights-expo/expo-ai-adoption-settings#working-hours-per-week) (default 40) to get hours saved per dev per week. Multiply that by the sum of the developers' hourly rates to get the dollar figure — each developer counts at their own [hourly rate](/insights-expo/expo-ai-adoption-settings#per-developer-hourly-rate) if one is set, and at the org's [Developer Hourly Rate](/insights-expo/expo-ai-adoption-settings#developer-hourly-rate) otherwise.

**Step 4 — Compute the within-window comparisons.** Independently of the baseline comparison, we compute uplift percentages within the current window: active developers vs. other developers, and Power Users vs. the rest. These don't require baseline data and answer slightly different questions.

**The minimum-baseline guard.** When the baseline rate is below 1 change per dev per week, the dashboard refuses to compute a speedup factor. This guard exists because tiny baseline denominators (sparse teams, new connections, or many seeded developers with few historical changes) used to produce nonsense headline numbers like "+12,665% productivity, $5.3M/week saved." Below the floor, we leave the volume-speedup and hours-saved fields at zero, and the AI Impact view shows an empty state instead of an inflated number.

### Why it matters

Productivity Uplift is the metric you use when an executive asks "should we keep paying for Claude?" The dashboard can't give you a definitive answer, but it can give you a defensible directional one.

It is also useful for tracking rollout maturity. A team where uplift is climbing alongside AI Adoption Score is a team where the rollout is genuinely working. A team where Adoption climbs but uplift stays flat is a team where adoption is shallow — they have the tools but aren't extracting value.

### How to read it

For the volume-speedup percentage:

| Speedup | Read it as |
| --- | --- |
| **30%+** | Strong return — AI is clearly delivering. Sustainable if it stays roughly here. |
| **15–29%** | Solid — typical for orgs 6–12 months into active rollout. |
| **5–14%** | Early — adoption exists but value is still ramping. Expect this to grow. |
| **< 5%** | Limited — either rollout is shallow or measurement is masking real gains. |
| **Empty state** | Baseline rate is below the minimum-baseline floor (1 change per dev per week). Pick a different baseline period or wait for more data. |

Don't quote the dollar value to two decimal places. "About $50K per week in annualized productivity gain" is defensible. "$2.43M per year" is not — the math doesn't support that precision.

### Where it appears

* **/ai-adoption/ai-impact** — the Productivity hero (4 cards: Increase in Productivity %, Additional Hours/Dev/Week, Additional $/Week, AI-Assisted Changes %), plus the AI Insight banner narrating the org-level uplift.
* **/ai-adoption/executive** — Productivity Uplift is one of the executive view's headline indicators and shows up in the LLM-generated executive insight banner.

### Settings that affect it

* [**Baseline Period**](/insights-expo/expo-ai-adoption-settings#baseline-period) — sets the reference month for the comparison. Move it when you have launched a new tool and want to anchor uplift to a specific pre-launch month.
* [**Developer Hourly Rate**](/insights-expo/expo-ai-adoption-settings#developer-hourly-rate) — translates additional hours into additional dollars for developers without their own rate.
* [**Per-developer hourly rate**](/insights-expo/expo-ai-adoption-settings#per-developer-hourly-rate) (Settings → Developers) — overrides the org rate for individual developers.
* [**Working Hours Per Week**](/insights-expo/expo-ai-adoption-settings#working-hours-per-week) — the work week used to turn the speedup into hours saved. Default 40.
* [**AI cost rates**](/insights-expo/expo-ai-adoption-settings#ai-cost-rates) — scale the AI spend that the ROI figure divides by.
* [**Maturity Factor**](/insights-expo/expo-ai-adoption-settings#maturity-factor) — indirectly, by shifting the underlying Adoption / Output baselines that feed the active vs. other comparison.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Output Score](/insights-expo/expo-ai-adoption-output-output-score) | The shipping-rate signal that underpins changes-per-dev-per-week. |
| [AI-Assisted Percentage](/insights-expo/expo-ai-adoption-impact-cost-ai-assisted-percentage) | Confirms that the output gain came from AI-touched work. |
| [Cycle Time](/insights-expo/expo-ai-adoption-flow-cycle-time) | Lower Cycle Time generally accompanies the speedup story. |
| [Spend by Tier](/insights-expo/expo-ai-adoption-impact-cost-spend-by-tier) | Pairs with uplift for ROI math. |

### How to improve it

The honest answer: improve the inputs.

* **Raise changes per dev per week** by following the [Output Score improvement guidance](/insights-expo/expo-ai-adoption-output-output-score).
* **Raise AI-Assisted %** by deepening adoption in the cohort that is already using AI lightly — see the [Agent Adoption Score playbook](/insights-expo/expo-ai-adoption-playbook-ai-rollout).
* **Lower Cycle Time** via review process and PR-size discipline ([Cycle Time](/insights-expo/expo-ai-adoption-flow-cycle-time)).
* **Don't try to improve uplift directly.** It is a derived metric. Optimizing the inputs is the only way to move it honestly.

### Limitations and gotchas

* **Estimate, not measurement.** Built from rate comparisons each with uncertainty. Round generously.
* **Baseline period choice matters a lot.** Comparing against a baseline that itself had unusual circumstances can produce misleading uplift numbers in either direction.
* **Doesn't isolate AI from other process changes.** If you adopted AI _and_ started doing weekly retros _and_ hired three senior ICs, all of those contribute to your speedup. The number doesn't surgically attribute.
* **Dollar figure depends on hourly rate.** A team with $50/hour rate sees half the dollar figure of a team with $100/hour rate, same productivity gain. The percentage is the more invariant number.
* **Below-floor baselines produce an empty state, not a number.** See the minimum-baseline guard above.

### FAQ

**Q: How precise is the dollar figure?**
A: Order-of-magnitude. The percentage speedup is more trustworthy than the absolute dollars — dollars depend on the hourly rates × the Working Hours Per Week setting (default 40). Cite the percentage and let the audience do the rough math if they want a dollar feel.

**Q: A team has 5% volume speedup but high AI adoption. Is something wrong?**
A: Possibly. Common causes: (1) adoption is broad but shallow, (2) the baseline period was anomalously productive, (3) the team works in a domain where AI helps less. Investigate before drawing conclusions.

**Q: Can I see uplift by team?**
A: Yes — filter the /ai-adoption/ai-impact page by team, and the Productivity hero cards recompute for that scope.

**Q: Will these metrics work if my org just installed Insights?**
A: Only weakly. The baseline is a single month that, where it can be detected, is set automatically to about 90 days before you first connected a repository — so the comparison depends on how much history your first sync brought in. If the baseline rate is below the minimum-baseline floor, the dashboard shows an empty state rather than an inflated number.

**Q: How do I explain this to my CFO?**
A: "This is our directional estimate of productivity gain attributable to AI adoption. The percentage is reliable for trend reading. The dollar figure is order-of-magnitude — don't book it as financial guidance, but it is defensible for narrative."
