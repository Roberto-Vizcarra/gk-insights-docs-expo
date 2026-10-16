---
title: Adoption & Agentic Metrics in GitKraken Insights
description: Learn about Adoption & Agentic metrics in GitKraken Insights, including Agent Adoption Score, Agent Autonomy Score, AI Tier, Maturity Factor, and Provider Weights.
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
integrations: [Claude Code, Codex, Cursor, GitHub Copilot]
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: Adoption Score, Autonomy, AI Tier, Maturity Factor, Provider Weights
    card_icon: user-check
    nav_category: metrics
    nav_label: Adoption & Agentic
    nav_order: 10
    nav_parent: expo-ai-adoption-metrics
    page_type: index
---
<kbd>Last updated: October 2026</kbd>


This family answers a single question: **how much is your team actually using AI, and how deeply?**

It's the most-read group of metrics in the dashboard because it surfaces the rollout story — are people picking up the tools, are they integrating them, and where are the gaps?

---

## The metrics

| Metric | What it captures |
| --- | --- |
| [**Agent Adoption Score**](/insights-expo/expo-ai-adoption-agentic-adoption-score) | 0–100 per developer. Combines daily consistency, hourly spread, prompt volume, and token output across Claude Code, Codex, Cursor, GitHub Copilot, and OpenCode, each tool at its Provider Weight. The headline number on /ai-adoption/developers. |
| [**Agent Autonomy Score**](/insights-expo/expo-ai-adoption-agentic-autonomy-score) | 0–100 per developer. Measures depth of _autonomous_ AI use — sessions with 10+ tool calls in Claude Code, Codex, and OpenCode, plus Cursor Agent-mode usage and Copilot autonomy. Separates "asking AI a question" from "letting AI do real work." |
| [**AI Tier**](/insights-expo/expo-ai-adoption-agentic-ai-tier) | Power User / Regular / Explorer / Emerging / On PTO. The composite category. Computed by weighting Adoption, Agentic, and Output Norm together (defaults 0.5 / 0.2 / 0.3). |
| [**Maturity Factor**](/insights-expo/expo-ai-adoption-agentic-maturity-factor) | Org-wide ceiling knob, set in Settings as **Company AI Readiness (%)** (1–100%, default 100%). Adjusts all P90 scores so the tier ceiling fits your org's actual maturity. |
| [**Provider Weights**](/insights-expo/expo-ai-adoption-agentic-cursor-boost) | 0–1 per AI tool, default 1.0. How much each tool's score counts toward the headline Adoption and Autonomy Scores. Replaces the earlier Cursor Boost. |

---

## How the metrics fit together

Adoption Score is built from per-provider scoring (Claude Code, Codex, Cursor, OpenCode) using a four-factor blend, with these default weights:

1. **Daily Regularity (35%)** — how consistently AI shows up across the working days in the window
2. **Hourly Spread (25%)** — how much of the workday AI is woven into
3. **Prompt Depth (25%)** — volume of interactions per active day
4. **Token Output (15%)** — depth of AI output per active day

GitHub Copilot is scored from one source per developer: through Agent OTel Data on the same four factors, or from the GitHub Copilot connection's own signals (active days, interactions, and lines added), each measured against your org's 90th percentile. The score hover labels the source **OTEL** or **PAT**. Each tool's score is multiplied by its **Provider Weight** (default 1.0), and the weighted scores are added and capped at 100 — tools are not averaged, so using several tools isn't penalized.

Then everything scales by **Maturity Factor** (Company AI Readiness), which is your org-wide ceiling knob.

```
Tool score      = four-factor score per tool (Copilot: four factors via
                  Agent OTel Data, or its connection signals vs org P90)
Final headline  = min(Σ(tool score × tool weight), 100) × Company AI readiness
```

> **Note — OpenCode is available on request.** To enable OpenCode telemetry for your organization, contact your GitKraken account team. See [OpenCode](/insights-expo/expo-ai-adoption-connect-otel#opencode).

Agentic is a separate, parallel measurement: Claude Code, Codex, and OpenCode are scored from agentic sessions (10+ tool calls), Cursor from its Agent-mode usage, and Copilot from agentic sessions when its activity comes through Agent OTel Data or otherwise from its connection's agent activity against the org P90, combined with the same Provider Weights. Agentic does not roll into Adoption. Both feed into the **AI Tier** composite alongside Output Norm.

### Tier composite (default; org-configurable)

```
tierScore = (0.5 × Agent Adoption) + (0.2 × Agent Autonomy) + (0.3 × Output Norm)
```

Each weight is read from `analytics.app_settings` per organization (keys: `tier_weight_adoption`, `tier_weight_agentic`, `tier_weight_output`) and renormalized to sum to 1.0. The tier-badge tooltip in the app renders the live values for every developer so the breakdown is always transparent.

**What admins can change in Settings → General:** Maturity Factor (Company AI Readiness), Developer Hourly Rate, Baseline Period, and Default Department, plus the tier composite weights (`tier_weight_*`), Provider Weights, Benchmarks targets, and the Output Score sub-weights (Merged PR Weight, `direct_commit_weight`, `review_weight`, `output_score_exclude_chore`). All are per-org settings stored in `app_settings` and editable directly in the settings form. See the [Settings reference](/insights-expo/expo-ai-adoption-settings) for every setting, including the ones that affect cost, ROI, and cycle-time metrics.

---

## Read this family responsibly

Three things to remember:

1. **The Adoption Score is about behavior, not skill.** A developer can be brilliant and Emerging (their work this quarter doesn't suit AI). The score is descriptive, not evaluative.
2. **Org P90 is the ceiling, not 100.** Because we normalize against the org's 90th percentile, a single developer can't game the system by spamming prompts. The ceiling moves with the org.
3. **Maturity Factor sets the headroom.** At the default 100%, a perfect (P90) developer scores 100. Lower it — at 75%, the same developer scores 75 — to leave room for the _org_ to grow into higher numbers without retiering everyone overnight.

See [How to read scores fairly](/insights-expo/expo-ai-adoption-for-engineering-leaders#how-to-read-scores-fairly) before drawing individual conclusions.

---

## Where this family shows up

* **/ai-adoption/developers** — primary surface. Each row on the [Developers](/insights-expo/expo-ai-adoption-developers) table shows Tier, Agent Adoption Score, Agent Autonomy Score, Output Score /wk, Token Usage Value, and $ / Output. **$ / Output** is AI API spend per point of output the developer shipped in the window: lower means more shipped effort per AI dollar, $0.00 means the output shipped without billed AI usage, and a dash means there's no scored output. Expand a developer for the scorecard, the activity heatmap, and one activity-trend card per AI tool on the **Agent Adoption** tab.
* **/ai-adoption/teams** — team averages for each score, plus a team **Tier** from the average of its members' composite tier scores (members on PTO left out).
* **/ai-adoption/ai-tools-comparison** — cohort comparisons (e.g. team A vs. team B, or Claude vs. Codex users).
* **/ai-adoption/executive** — hero KPI ("AI Adoption %") and trend lines.
* **/ai-adoption/ai-impact** — autonomy deep dive and Business Impact / ROI.
* Adjacent surfaces in the same nav family: **/ai-adoption/board-metrics**, **/ai-adoption/capex**, **/ai-adoption/data-connections**, **/ai-adoption/data-explorer**, **/ai-adoption/settings/\***.
