---
title: "Provider Weights (formerly Cursor Boost)"
description: "How Provider Weights combine scores from multiple AI tools into the Agent Adoption and Agent Autonomy Scores in GitKraken Insights. Replaces the earlier Cursor Boost."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: "How multiple AI tools combine into one score, and how to weight each tool"
    card_icon: user-check
    nav_category: metrics
    nav_label: "Provider Weights"
    nav_order: 15
    nav_parent: expo-ai-adoption-agentic-metrics
    page_type: content
---
## Provider Weights

> _A per-tool weight (0–1, default 1.0) that sets how much each AI tool's score counts toward a developer's headline Agent Adoption and Agent Autonomy Scores. Replaces the fixed 25% Cursor Boost._

**Family:** Adoption & Agentic · **Where it appears:** Settings → General → Tier and provider weights; behind the scenes in the Adoption and Autonomy Scores on /developers, /teams, /executive

### At a glance

GitKraken Insights scores each AI tool a developer uses on its own, then adds the tool scores together to produce the headline score. Provider Weights decide how much each tool's score counts in that sum. Every tool starts at a weight of 1.0 (full weight); an admin can lower a tool's weight to de-emphasize it, or set it to 0 to leave that tool out of the score entirely.

This replaces the earlier **Cursor Boost** model, where Claude Code and Codex were merged into one "primary" score and Cursor was added on top at a fixed 25% rate. Today no tool is treated as secondary by default: Claude Code, Codex, Cursor, Copilot, Devin, and OpenCode each contribute their own score at their own weight.

One weight per tool applies to both the [Agent Adoption Score](/insights-expo/expo-ai-adoption-agentic-adoption-score) and the [Agent Autonomy Score](/insights-expo/expo-ai-adoption-agentic-autonomy-score).

### Formula

```
headline = min(Σ(tool score × tool weight), 100) × Company AI readiness
  tool score  = that tool's own 0–100 score
  tool weight = Provider Weight for that tool (0–1, default 1.0)
```

If a developer has no activity in a tool, that tool's score is 0 and it adds nothing — no activity, no contribution.

### How GitKraken Insights calculates it

**Step 1 — Score each tool on its own.** For the Adoption Score, Claude Code, Codex, Cursor, and OpenCode are each scored on the four factors (Daily Regularity, Hourly Spread, Prompt Depth, Token Output). Copilot is scored from one source per developer: through Agent OTel Data on the same four factors, or from the GitHub Copilot connection's own signals (active days, interactions, and lines added), each measured against your org's 90th percentile. See [Agent Adoption Score](/insights-expo/expo-ai-adoption-agentic-adoption-score) for the mechanics. For the Autonomy Score, each tool contributes its own 0–100 autonomy score — see [Agent Autonomy Score](/insights-expo/expo-ai-adoption-agentic-autonomy-score).

**Step 2 — Weight each tool score.** Each tool score is multiplied by that tool's Provider Weight. At the default 1.0, a Cursor score of 60 contributes 60 points; at 0.5 it contributes 30.

**Step 3 — Add and cap.** The weighted tool scores are added together and the sum is capped at 100.

**Step 4 — Scale by Company AI readiness.** The capped sum is multiplied by the org's [Maturity Factor](/insights-expo/expo-ai-adoption-agentic-maturity-factor) (labeled **Company AI Readiness (%)** in Settings, default 100%).

**Edge case: one tool only.** A developer who uses only Cursor gets their Cursor score at Cursor's weight as their whole score. At default settings, a Cursor-only developer with a Cursor score of 60 scores 60 — the same as a Claude Code-only developer with a Claude Code score of 60.

### Why it matters

Different orgs standardize on different tools. Because every tool is scored on its own and starts at the same weight, a developer gets the same credit for the same level of use whichever supported tool they use. Provider Weights then let an admin de-emphasize a tool the org is phasing out or doesn't want to count as heavily.

### How to read it

You don't typically read Provider Weights in isolation. What you read is the breakdown on a developer's expanded detail panel, which lists each tool's contribution. At default settings (every weight 1.0, Company AI Readiness 100%):

* A developer with Claude Code = 50 and Cursor = 30 scores 50 + 30 = 80.
* The same developer with Cursor's weight set to 0.5 scores 50 + 15 = 65.
* A developer with Claude Code = 70 and Cursor = 50 reaches 120 before the cap, so they score 100.

The difference between the first two cases (15 points) is what the Cursor weight change is doing in that developer's case.

### Where it appears

* **/developers** — hover a developer's Agent Adoption or Agent Autonomy Score on the [Developers](/insights-expo/expo-ai-adoption-developers) table to see each tool's contribution, and expand the developer for the full breakdown. The "How is the Agent Adoption Score calculated?" card lists the current weight for each tool.
* **Behind the scenes** in every Adoption and Autonomy Score on the dashboard.

<figure>
  <img src="/wp-content/uploads/ai-adoption-developers.png" class="help-center-img img-bordered" alt="Developers page in GitKraken Insights showing the Top 10 developers widget, score trend chart, and the developer table with Adoption, Agentic, Providers, and Output Score columns" />
  <figcaption>Developers page — KPI cards and the All Developers table with Tier, Agent Adoption Score, Agent Autonomy Score, Output Score /wk, Token Usage Value, and $ / Output columns.</figcaption>
</figure>

### Settings that affect it

* [**Provider Weights**](/insights-expo/expo-ai-adoption-settings#provider-weights) — Settings → General → **Tier and provider weights**. One weight per tool, range 0–1, default 1.0. Applies to both the Adoption and Autonomy Scores. The form lists a row for each of **Claude**, **Codex**, **Cursor**, **Copilot**, **Devin**, and **OpenCode** for every organization. The [Devin](/insights-expo/expo-ai-adoption-connect-ai-tools#devin) and [OpenCode](/insights-expo/expo-ai-adoption-connect-otel#opencode) connections are available on request — contact your GitKraken account team. Until a tool is connected and has activity, its weight has no effect.
* [**Maturity Factor**](/insights-expo/expo-ai-adoption-settings#maturity-factor) — labeled **Company AI Readiness (%)**. Scales the weighted, capped sum.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Agent Adoption Score](/insights-expo/expo-ai-adoption-agentic-adoption-score) | Provider Weights set how each tool's adoption score combines into the headline Adoption Score. |
| [Agent Autonomy Score](/insights-expo/expo-ai-adoption-agentic-autonomy-score) | The same weights set how each tool's autonomy score combines into the headline Autonomy Score. |
| [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier) | Indirectly — through Adoption and Autonomy. |

### How to use it

Cases where you might want to change a tool's weight:

* **You want to phase a tool out.** Set its weight to 0. That tool no longer counts toward Adoption or Autonomy (its data still appears in other detail views).
* **You trust one tool's signal less.** Lower its weight (for example, to 0.5) so heavy use of that tool alone can't carry a developer to the top of the scale.
* **Your developers use several tools side by side and scores are hitting the cap.** Because weighted tool scores are added together, a developer active in two tools can reach 100 quickly. Lowering the weights of overlapping tools spreads the scores back out.

In most orgs, the default 1.0 for every tool is the right answer. Change a weight only when your provider mix has genuinely shifted.

### Limitations and gotchas

* **Scores add, they don't average.** A developer active in two tools gets credit from both. This avoids penalizing multi-tool developers, but it means multi-tool developers reach the 100 cap sooner.
* **Cap before scaling.** The sum is capped at 100 _before_ Company AI readiness is applied, so contributions above 100 are absorbed by the cap.
* **One weight covers both scores.** You can't weight a tool differently for Adoption and for Autonomy.
* **A weight of 0 removes the tool entirely** from both scores, not just from the headline display.

### FAQ

**Q: What happened to the Cursor Boost?**
A: It was replaced by Provider Weights. Cursor is no longer added on top of a combined Claude Code + Codex score at a fixed 25% rate. Every tool — Cursor included — is scored on its own and counts at its own weight, which defaults to 1.0.

**Q: A developer uses Cursor exclusively. Why did their score go up?**
A: Under the old Cursor Boost, a Cursor-only developer's score was discounted to 25% of their Cursor score. With Provider Weights at the default 1.0, Cursor counts at full weight, so the same activity now produces a higher score. Lower Cursor's weight if you want the old discount back.

**Q: Where do I see the current weights?**
A: In Settings → General → **Tier and provider weights**, and on the "How is the Agent Adoption Score calculated?" card on /developers, which lists each tool's weight.
