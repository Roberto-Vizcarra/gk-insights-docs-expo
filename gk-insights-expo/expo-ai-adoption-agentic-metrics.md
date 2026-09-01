---
title: Adoption & Agentic Metrics in GitKraken Insights
description: Learn about Adoption & Agentic metrics in GitKraken Insights, including Agent Adoption Score, Agent Autonomy Score, AI Tier, Maturity Factor, and Cursor Boost.
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
integrations: [Claude Code, Codex, Cursor, Devin, GitHub Copilot]
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: Adoption Score, Autonomy, AI Tier, Maturity Factor, Cursor Boost
    card_icon: user-check
    nav_category: metrics
    nav_label: Adoption & Agentic
    nav_order: 10
    nav_parent: expo-ai-adoption-metrics
    page_type: index
---
<kbd>Last updated: September 2026</kbd>


This family answers a single question: **how much is your team actually using AI, and how deeply?**

It's the most-read group of metrics in the dashboard because it surfaces the rollout story — are people picking up the tools, are they integrating them, and where are the gaps?

---

## The metrics

| Metric | What it captures |
| --- | --- |
| [**Agent Adoption Score**](#agent-adoption-score) | 0–100 per developer. Combines daily consistency, hourly spread, prompt volume, and token output across Claude Code and Codex, with optional Cursor boost. The headline number on /ai-adoption/developers. |
| [**Agent Autonomy Score**](#agent-autonomy-score) | 0–100 per developer. Measures depth of _autonomous_ AI use — sessions with 10+ tool calls across Claude Code and Codex (Cursor not included). Separates "asking AI a question" from "letting AI do real work." |
| [**AI Tier**](#ai-tier) | Power User / Regular / Explorer / Emerging / On PTO. The composite category. Computed by weighting Adoption, Agentic, and Output Norm together (defaults 0.5 / 0.2 / 0.3). |
| [**Maturity Factor**](#maturity-factor) | 0.01–1.00 org-wide ceiling knob (default 0.75). Adjusts all P90 scores so the tier ceiling fits your org's actual maturity. |
| [**Cursor Boost**](#cursor-boost) | 25% by default. How much Cursor adoption layers on top of Claude / Codex in the headline Adoption Score. |

---

## How the metrics fit together

Adoption Score is built from per-provider scoring (Claude, Codex, Cursor) using a four-factor blend:

1. **Daily Use** — consistency across the window
2. **Hourly Spread** — diversity within days
3. **Prompts** — volume of interactions
4. **Output** — token production depth

Claude Code and Codex events are _unioned_ at the factor level (days, hours, prompts, tokens merged together — not averaged) and scored as one **primary** score. Cursor is scored independently and added as a **secondary boost** (default 25% of the Cursor score).

Then everything scales by **Maturity Factor**, which is your org-wide ceiling knob.

```
Primary score   = blend(Claude ∪ Codex)
Final headline  = min(Primary + 0.25 × Cursor, 100) × Maturity Factor
```

Agentic is a separate, parallel measurement that runs against **Claude Code and Codex sessions only** — Cursor doesn't currently feed Autonomy because Cursor's event stream doesn't expose per-session tool calls in a way we can score. Agentic does not roll into Adoption. Both feed into the **AI Tier** composite alongside Output Norm.

### Tier composite (default; org-configurable)

```
tierScore = (0.5 × Agent Adoption) + (0.2 × Agent Autonomy) + (0.3 × Output Norm)
```

Each weight is read from `analytics.app_settings` per organization (keys: `tier_weight_adoption`, `tier_weight_agentic`, `tier_weight_output`) and renormalized to sum to 1.0. The tier-badge tooltip in the app renders the live values for every developer so the breakdown is always transparent.

**What admins can change in Settings → General:** Maturity Factor, Developer Hourly Rate, Baseline Period, and Default Department, plus the tier composite weights (`tier_weight_*`) and the Output Score sub-weights (`direct_commit_weight`, `review_weight`, `output_score_exclude_chore`). All are per-org settings stored in `app_settings` and editable directly in the settings form.

---

## Read this family responsibly

Three things to remember:

1. **The Adoption Score is about behavior, not skill.** A developer can be brilliant and Emerging (their work this quarter doesn't suit AI). The score is descriptive, not evaluative.
2. **Org P90 is the ceiling, not 100.** Because we normalize against the org's 90th percentile, a single developer can't game the system by spamming prompts. The ceiling moves with the org.
3. **Maturity Factor sets the headroom.** At 0.75, even a perfect (P90) developer scores 75. That's intentional — it leaves room for the _org_ to grow into higher numbers without retiering everyone overnight.

See [How to think about developer scores](/insights-expo/expo-ai-adoption-getting-started#how-to-think-about-developer-scores) before drawing individual conclusions.

---

## Where this family shows up

* **/ai-adoption/developers** — primary surface. Each row has Adoption, Agentic, Tier, and a heatmap.
* **/ai-adoption/teams** — team averages and tier mix bars.
* **/ai-adoption/ai-tools-comparison** — cohort comparisons (e.g. team A vs. team B, or Claude vs. Codex users). **Devin** is its own cohort here, scored on adoption and autonomy alongside Copilot, Cursor, and Claude rather than blended into another tool's cohort.
* **/ai-adoption/executive** — hero KPI ("AI Adoption %") and trend lines.
* **/ai-adoption/ai-impact** — autonomy deep dive and Business Impact / ROI.
* Adjacent surfaces in the same nav family: **/ai-adoption/board-metrics**, **/ai-adoption/capex**, **/ai-adoption/data-connections**, **/ai-adoption/data-explorer**, **/ai-adoption/settings/\***.
