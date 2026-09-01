---
title: AI Impact & Cost Metrics in GitKraken Insights
description: Learn about AI Impact & Cost metrics in GitKraken Insights, including Productivity Uplift, AI-Assisted Percentage, CapEx/OpEx Split, and Spend by Tier.
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
integrations: [Claude Code, Codex, Cursor, GitHub Copilot]
status: GA
taxonomy:
    category: insights-expo
page_type: content
nav_category: metrics
nav_order: 50
nav_label: AI Impact & Cost
card_icon: currency-dollar
card_color: green
card_description: Productivity Uplift, AI-Assisted %, CapEx/OpEx, Spend by Tier
---
<kbd>Last updated: September 2026</kbd>

> **Note:** This page covers the **AI Impact & Cost** family of the AI Adoption dashboard. For the code-quality and acceptance-rate metrics from connected AI providers, see [AI Impact Metrics](/gk-insights/gk-insights-ai-impact-metrics).

This family answers: **is AI actually paying off?**

Other families measure adoption (are people using it?) and output (is work shipping?). This family connects those signals to dollars and hours — the language executives and finance teams need.

These numbers are _estimates_, not measurements. The dashboard is honest about that throughout, but it is worth saying up front: when we publish "$1.2M in annualized productivity gain," we mean "directionally, this is the order of magnitude." Use these numbers for storytelling and order-of-magnitude framing, not for finance reconciliation.

---

## The metrics

| Metric | What it captures |
| --- | --- |
| [**Productivity Uplift**](#productivity-uplift) | The headline ROI numbers. Volume speedup vs. baseline period, plus per-dev and Power-User comparisons within the current window. |
| [**AI-Assisted Percentage**](#ai-assisted-percentage) | What % of shipped _changes_ (PRs + commits) had AI assistance. Weighted by lines changed. |
| [**CapEx / OpEx Split**](#capex-opex-split) | Software capitalization breakdown. Which work counts as capitalizable engineering investment? |
| [**Spend by Tier**](#spend-by-tier) | AI tooling cost allocated across AI Tiers. Where is the money going, and is it landing on Power Users? |

---

## How the metrics connect

Adoption Score → AI-Assisted % → Productivity Uplift → $ saved / week → CapEx / OpEx split → ROI math (Spend by Tier)

The chain is: adoption drives AI-assisted work, AI-assisted work drives productivity uplift, productivity uplift translates to dollar savings, and dollar savings vs. AI spend gives you ROI.

Each step adds assumption uncertainty. The Adoption Score is a clean measurement. The Productivity Uplift estimate carries the most assumption load — see its section for the honest accounting.

---

## Read this family responsibly

Three rules:

1. **Don't cite Productivity Uplift to the dollar.** It is an estimate. Round generously when reporting.
2. **Use these numbers for trend storytelling.** "Productivity uplift up 2× since rollout began" is a defensible story. "Productivity uplift is $1,234,567/year" is not.
3. **The CapEx/OpEx split is more solid than the productivity numbers.** It is based on category classification of actual shipped work, not on uplift inference. Use it for capitalization accounting with reasonable confidence.

---

## Where this family shows up

* **/ai-adoption/ai-impact** — the dedicated home for this family. Productivity hero cards, AI Insight banner, Cycle Time and PR Volume by AI Tier, Spend by Tier, CFR by AI Tier.
* **/ai-adoption/executive** — Productivity Uplift in the hero KPI strip and the executive insight banner.
* **/ai-adoption/capex** — CapEx / OpEx split as the primary surface.
* **/ai-adoption/teams**, **/ai-adoption/developers** — AI-Assisted % appears in trend lines and breakdowns.

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
EstimatedHoursSavedPerDevWeek = (VolumeSpeedupFactor − 1) × 40-hour work week
EstimatedValueSaved          = EstimatedHoursSavedPerDevWeek × Developer Hourly Rate × dev count

PerDevUpliftPct        = (ActiveAvgChangesPerDev − OtherAvgChangesPerDev) / OtherAvgChangesPerDev × 100
PowerUserPerDevUpliftPct = (PowerUserAvgChangesPerDev − BelowPowerAvgChangesPerDev) / BelowPowerAvgChangesPerDev × 100
PotentialUpliftPct     = additional org-wide changes if all developers moved to Active tier, as % of current total
```

### How GitKraken Insights calculates it

**Step 1 — Establish the baseline.** The baseline period is set in Settings → General (default November 1 of the previous year through the start of the current month). The current window is compared against a same-length window from the baseline period.

**Step 2 — Compute the volume speedup.** We measure changes-per-developer-per-week in both windows. If the baseline rate is at least 1 change per dev per week (a guard against divide-by-near-zero — see the minimum-baseline guard below), the ratio gives the volume speedup factor. A factor of 1.5 means the team is shipping 50% more changes per dev per week than baseline.

**Step 3 — Estimate hours and dollars saved.** Multiply `(VolumeSpeedupFactor − 1)` by a 40-hour standard work week to get hours saved per dev per week. Multiply that by Developer Hourly Rate and developer count to get the dollar figure.

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

* [**Baseline Period**](/gk-insights/ai-adoption-settings#baseline-period) — sets the historical comparison window. Move it when you have launched a new tool and want to anchor uplift to a specific pre-launch month.
* [**Developer Hourly Rate**](/gk-insights/ai-adoption-settings#developer-hourly-rate) — translates additional hours into additional dollars.
* [**Maturity Factor**](/gk-insights/ai-adoption-settings#maturity-factor) — indirectly, by shifting the underlying Adoption / Output baselines that feed the active vs. other comparison.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Output Score](/gk-insights/ai-adoption-output-metrics#output-score) | The shipping-rate signal that underpins changes-per-dev-per-week. |
| [AI-Assisted Percentage](#ai-assisted-percentage) | Confirms that the output gain came from AI-touched work. |
| [Cycle Time](/gk-insights/ai-adoption-flow-metrics#cycle-time) | Lower Cycle Time generally accompanies the speedup story. |
| [Spend by Tier](#spend-by-tier) | Pairs with uplift for ROI math. |

### How to improve it

The honest answer: improve the inputs.

* **Raise changes per dev per week** by following the [Output Score improvement guidance](/gk-insights/ai-adoption-output-metrics#output-score).
* **Raise AI-Assisted %** by deepening adoption in the cohort that is already using AI lightly — see the [Agent Adoption Score playbook](/gk-insights/ai-adoption-playbook-ai-rollout).
* **Lower Cycle Time** via review process and PR-size discipline ([Cycle Time](/gk-insights/ai-adoption-flow-metrics#cycle-time)).
* **Don't try to improve uplift directly.** It is a derived metric. Optimizing the inputs is the only way to move it honestly.

### Limitations and gotchas

* **Estimate, not measurement.** Built from rate comparisons each with uncertainty. Round generously.
* **Baseline period choice matters a lot.** Comparing against a baseline that itself had unusual circumstances can produce misleading uplift numbers in either direction.
* **Doesn't isolate AI from other process changes.** If you adopted AI _and_ started doing weekly retros _and_ hired three senior ICs, all of those contribute to your speedup. The number doesn't surgically attribute.
* **Dollar figure depends on hourly rate.** A team with $50/hour rate sees half the dollar figure of a team with $100/hour rate, same productivity gain. The percentage is the more invariant number.
* **Below-floor baselines produce an empty state, not a number.** See the minimum-baseline guard above.

### FAQ

**Q: How precise is the dollar figure?**
A: Order-of-magnitude. The percentage speedup is more trustworthy than the absolute dollars — dollars depend on Developer Hourly Rate × the 40-hour work-week assumption. Cite the percentage and let the audience do the rough math if they want a dollar feel.

**Q: A team has 5% volume speedup but high AI adoption. Is something wrong?**
A: Possibly. Common causes: (1) adoption is broad but shallow, (2) the baseline period was anomalously productive, (3) the team works in a domain where AI helps less. Investigate before drawing conclusions.

**Q: Can I see uplift by team?**
A: Yes — filter the /ai-adoption/ai-impact page by team, and the Productivity hero cards recompute for that scope.

**Q: Will these metrics work if my org just installed Insights?**
A: Only weakly. The baseline period defaults to November 1 last year, so you need at least a few months of historical data to compute meaningful deltas. If the baseline rate is below the minimum-baseline floor, the dashboard shows an empty state rather than an inflated number.

**Q: How do I explain this to my CFO?**
A: "This is our directional estimate of productivity gain attributable to AI adoption. The percentage is reliable for trend reading. The dollar figure is order-of-magnitude — don't book it as financial guidance, but it is defensible for narrative."

---

## AI-Assisted Percentage

> _What share of your team's shipped changes had AI assistance — measured as a percentage of lines changed in AI-assisted PRs and commits._

**Family:** AI Impact & Cost · **Cadence:** Per window · **Where it appears:** /ai-adoption/ai-impact, /ai-adoption/executive, drill-downs

### At a glance

AI-Assisted % is the behavioral counterpart to [Agent Adoption Score](/gk-insights/ai-adoption-agentic-metrics#agent-adoption-score). Where Adoption asks "is this developer using AI?", AI-Assisted % asks "did this developer use AI _on this specific change_?" The two diverge surprisingly often. A developer can be a heavy daily Claude user (high Adoption) and ship most of this week's PRs without touching it (low AI-Assisted on those changes).

The metric is line-weighted, so a 2,000-line AI-assisted refactor counts more than a 30-line AI-assisted typo fix.

### Formula

```
AI-Assisted % = (lines changed in AI-assisted PRs + commits) / (total lines changed) × 100%

  where AI-assisted = has AI co-author trailer OR
                      developer had AI events within the correlation window
                      of the change's lifecycle (default 60 minutes)
```

### How GitKraken Insights calculates it

**Per-item detection.** For each merged PR and direct commit, the backend computes `is_ai_assisted = true` if either:

1. **The commit has an AI co-author trailer** (reason: `co_author`), or
2. **The developer had AI events within the AI-to-commit correlation window of the change's lifecycle window** (reason: `ai_events`). The window defaults to 60 minutes and is configurable per org — see [AI-to-Commit Correlation Window](/gk-insights/ai-adoption-settings#ai-to-commit-correlation-window). For PRs, the lifecycle window is `(first_commit_at, merged_at OR closed_at OR now)`. For direct commits, the correlation window applies on either side of the commit. AI events include `user_prompt`, `tool_result`, and `api_request` from any connected provider (Claude Code, Codex, Cursor).

The materialized flag (`pull_requests.is_ai_assisted` / `direct_commits.is_ai_assisted`) is computed by a continuous classifier worker. A live `EXISTS` fallback fires when the materialized flag is still NULL (typically for very recent items).

**Aggregation.** AI-Assisted % is computed as lines-changed-weighted, not as a simple count. So a team that ships 100 small AI-assisted PRs and one big non-AI-assisted PR can show 50% AI-Assisted (by lines) even though 99% of count is AI-assisted.

This weighting reflects "how much of the shipped _work_ had AI help" rather than "how many of the shipped _items_."

### Why it matters

AI-Assisted % is the closest thing the dashboard has to an _attribution_ metric. Adoption Score says "developers are using AI"; AI-Assisted % says "AI is touching the shipped work."

It is also a sanity check on the Adoption Score. A team with 90% Adoption Score and 10% AI-Assisted % has a problem — devs are using AI but not on their actual work. A team with 60% Adoption and 60% AI-Assisted has clean alignment.

For executives, AI-Assisted % is the metric to cite when answering "how integrated is AI into the actual code we ship?" The answer is more concrete than Adoption (which can include "I asked Claude a question once").

### How to read it

| AI-Assisted % | Read it as |
| --- | --- |
| **70%+** | Deep integration — AI is on most of the team's substantive work |
| **40–69%** | Solid — AI is integrated into about half of substantive shipping |
| **15–39%** | Emerging — AI is used on a subset of work, often the easier subset |
| **< 15%** | Limited — AI is being used adjacent to work but not on the work itself |

The right target for your team depends on what you ship. Heavy infrastructure and migration work resists AI assistance — even at 100% adoption, AI-Assisted % may cap at 50%. Greenfield product work can reach 80%+.

### Where it appears

* **/ai-adoption/ai-impact** — AI-Assisted Changes % card in the Productivity hero, which shows AI-assisted changed lines alongside total changed lines so you can see the ratio the percentage comes from. Also shows up in the AI Insight banner narrative.
* **/ai-adoption/executive** — AI-Assisted % is one of the headline KPIs.
* **PR drill-down tables** — each PR shows its AI-Assisted status with the reason (`co_author` or `ai_events`).

### Settings that affect it

* [**AI-to-Commit Correlation Window**](/gk-insights/ai-adoption-settings#ai-to-commit-correlation-window) — how close AI activity must be to a change for that change to count as AI-assisted. Default 60 minutes. Changing it re-runs the association on existing data, so past percentages shift with it.
* The AI co-author trailer detection is fixed.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Agent Adoption Score](/gk-insights/ai-adoption-agentic-metrics#agent-adoption-score) | The user-level adoption measure. AI-Assisted % is the work-level counterpart. |
| [Productivity Uplift](#productivity-uplift) | AI-Assisted % is a confirming signal — higher AI-Assisted → stronger uplift narrative. |
| [Output Score](/gk-insights/ai-adoption-output-metrics#output-score) | Independent — Output Score doesn't filter by AI-assisted status. |

### How to improve it

* **Run a brown-bag on "show me how you actually use AI."** Many teams have devs using AI for _adjacent_ work (research, learning) but not for the work they ship. Demos help.
* **Pair Power Users with low-AI-Assisted devs.** Same intervention as for Adoption, but specifically targeting work-level integration.
* **Audit the gap.** Identify devs with high Adoption but low AI-Assisted %. Those are the ones to coach — they have the habit but aren't translating it.
* **Don't conflate AI-Assisted with "using AI co-author tags."** Many devs use AI without tagging. The temporal heuristic catches them; teams that primarily rely on tagging may show low AI-Assisted artificially.

### Limitations and gotchas

* **The temporal heuristic produces false positives and false negatives.** At the default 60-minute window, a dev who left Claude open in another window while writing non-AI-assisted code still gets the AI-assisted flag, and a dev who used AI two hours before committing doesn't. The heuristic is the best practical balance, not a measurement. If it over-attributes for your team, tighten the [correlation window](/gk-insights/ai-adoption-settings#ai-to-commit-correlation-window).
* **Materialized flag lags by minutes to hours.** The classifier worker catches up continuously.
* **Co-author trailer detection requires the trailer.** Devs using AI without the `Co-authored-by` trailer rely entirely on the temporal heuristic.
* **Line-weighted means one big PR can dominate the metric.** A team's 50% AI-Assisted week might be one 3,000-line non-AI-assisted migration plus many smaller AI-assisted PRs. Look at distribution, not just the headline.

### FAQ

**Q: How is "AI-assisted" detected if the dev doesn't tag the commit?**
A: The backend looks for AI events (Claude / Codex / Cursor prompts, tool results, API requests) by that developer within the correlation window of the change's lifecycle — 60 minutes by default. If they prompted Claude within that window of committing, the commit is marked AI-assisted with reason `ai_events`.

**Q: A PR shows AI-Assisted = false but I know the dev used Claude. Why?**
A: Three usual causes: (1) the AI events happened outside the PR's lifecycle window by more than the correlation window allows, (2) the developer's email or login doesn't match between the AI events and the PR, (3) the materialized flag hasn't been computed yet.

**Q: Why line-weighted rather than count-weighted?**
A: Line weighting tracks "how much of the work shipped with AI help." Count weighting can be skewed by a flood of trivial PRs. Line weighting is the more honest "share of substantive output" answer.

**Q: Will lines from the AI's auto-generated comments count?**
A: Yes — all lines changed in the PR count, including ones the AI wrote that the human accepted. That is by design: the work was AI-assisted regardless of which lines came from whom.

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

* [**Developer Hourly Rate**](/gk-insights/ai-adoption-settings#developer-hourly-rate) — translates hours into capitalized and expensed dollars in the cost rollup.
* Manual `capex_opex` override per PR or commit — settable from /ai-adoption/data-explorer when the auto-classification produces the wrong answer.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Effort Score](/gk-insights/ai-adoption-output-metrics#effort-score-complexity) | Effort feeds into the hours estimate. |
| [Output Score](/gk-insights/ai-adoption-output-metrics#output-score) | Same shipped work, different aggregation. |

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
A: As a starting point, yes — the per-PR audit trail is the strongest feature. The auto-categorization should be sampled and reviewed by your accounting team before booking, especially the first time. Most teams find \~85% accuracy out of the box; manual overrides on the remaining 15% take an hour or two per month.

**Q: How does the hours estimate work?**
A: Effort Score (0.1 to 0.9) maps to an internal hours-per-effort-point constant calibrated against teams that have tracked time. It is an estimate. Order-of-magnitude reliable, not down-to-the-hour.

**Q: We want to track CapEx by project, not by developer. Is that possible?**
A: Currently the grid is per-developer per-month. Project-level rollup isn't directly supported — filter by team (if your projects map to teams) or use the CSV export and re-pivot.

**Q: My PR is misclassified as CapEx but it's really maintenance. How do I fix it?**
A: /ai-adoption/data-explorer → PRs tab → click the row → set `capex_opex` to OpEx. Saves immediately. Future aggregations will reflect the override.

---

## Spend by Tier

> _AI tooling cost allocated across AI Tiers. Where is the money going, and is it landing on the developers who are actually using it?_

**Family:** AI Impact & Cost · **Cadence:** Per window · **Where it appears:** /ai-impact

### At a glance

Spend by Tier answers the awkward question: "are we paying for AI tools on behalf of developers who aren't using them?" It splits your AI tooling spend across the five AI Tiers (Power User / Regular / Explorer / Emerging / On PTO) and shows where the money is concentrated.

The healthiest pattern: spend concentrated on Power Users and Regulars. The unhealthy pattern: a meaningful chunk of spend allocated to Emerging seats — wasted licensing budget that should be reallocated or wound down.

### Formula

```
Spend per Tier = (license cost per developer) × (developers in that tier)

For per-developer usage-based spend (e.g. API costs):
Spend per Tier = SUM(api_cost) over developers in that tier
```

### How GitKraken Insights calculates it

**Two modes.**

1. **License-based.** Each developer with an active AI tool license contributes the license cost (say $20/seat/month for Claude Code Pro). Sum across developers, grouped by tier.
2. **Usage-based.** For tools billed on consumption (API tokens, e.g.), we sum the actual `cost_usd` from `api_request` events grouped by developer, then group by tier. For GitHub Copilot, the rollup includes AI credits consumed, so orgs on Copilot usage-based billing see that spend alongside their seat costs.

In practice, most orgs see both. Claude / Codex / Cursor licenses are seat-based; some API usage charges are consumption-based. The dashboard rolls them up together.

**Grouping by tier.** Once spend-per-developer is computed, we group by the developer's AI Tier for the window. The result is total $ allocated to each tier.

### Why it matters

For most orgs, AI tooling spend lands around $20–$50 per developer per month — meaningful at scale (an org of 500 developers is spending $10–25K per month). The natural question is "is that money going to developers who would feel pain if we took it away, or is half of it on Emerging seats?"

Spend by Tier surfaces this directly. It's the single most useful view for the conversation with finance about whether to renew, expand, or rightsize the AI tooling budget.

### How to read it

The healthy distribution:

| Tier | Share of spend |
| --- | --- |
| **Power User** | 25–40% (proportional to share of devs) |
| **Regular** | 35–50% |
| **Explorer** | 15–25% |
| **Emerging** | <15% — and shrinking |
| **On PTO** | minimal — usually a noise floor |

If your Emerging share is above 20%, you have either an onboarding problem or a license-allocation problem. Investigate.

The total dollar number is also informative: divide by total developers and you have your average AI spend per developer. Most orgs land in the $20–$50/dev/month range.

### Where it appears

* **/ai-impact** — Spend by Tier panel alongside the cycle time and CFR by-tier charts.

### Settings that affect it

* AI license cost configuration is not currently exposed in the Settings UI. License costs are configured at deploy time. Ask your account manager if you need to adjust them.
* API usage cost comes from the `cost_usd` field on `api_request` events — sourced directly from the provider.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [AI Tier](/gk-insights/ai-adoption-agentic-metrics#ai-tier) | The grouping dimension. |
| [Productivity Uplift](#productivity-uplift) | Spend / Uplift = ROI. Most healthy rollouts have uplift >> spend by an order of magnitude. |

### How to use it

* **Annual or quarterly license review.** Pull this chart before your AI tooling renewal conversation. Identify the Emerging cohort. Decide: re-engage them or remove their seats.
* **Re-allocate seats from Emerging to Explorer.** If you're capacity-constrained on licenses, the highest-leverage move is shifting them from devs who aren't using them to devs who are starting to.
* **ROI framing for the CFO.** "We spend $X on AI tooling and our estimated productivity uplift is $Y, with Z% of spend on developers in Regular+ tiers." The framing alone makes most renewal conversations easy.

### Limitations and gotchas

* **License cost is whatever's configured.** We don't know your actual contract terms. If you're paying a different rate, the absolute dollar values will be off — but the _distribution across tiers_ is still accurate.
* **Usage-based costs depend on event coverage.** API costs from Claude / Codex come through OTEL events, and Copilot AI-credit consumption comes from the Copilot connection. Cursor's API doesn't expose costs at the same granularity, so Cursor spend is license-based only.
* **Emerging doesn't mean "didn't use AI ever."** A developer can be Emerging in this window but a Regular last quarter. License seats are usually paid annually — even if their utilization is bad this window, they have the seat. The decision is about future renewal, not retroactive credit.

### FAQ

**Q: A team has high Emerging % and high spend on Emerging. Should I cut their licenses?**
A: Probably not as a first move. Most Emerging cohorts respond to re-engagement (training, pairing, office hours) faster and cheaper than license churn. Cut only after re-engagement has been tried.

**Q: How do I see actual API costs vs. license costs separately?**
A: /data → AI Events tab → filter by event type. `api_request` events carry `cost_usd`; license costs are computed from seat counts.

**Q: We don't pay per-developer for Claude — we pay enterprise. Does this work?**
A: Yes, but you'll need to manually allocate the enterprise cost back to developers (e.g. divide by headcount). Talk to your account manager about the configuration option.
