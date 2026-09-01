---
title: "Cursor Boost"
description: "How the Cursor Boost rate affects Agent Adoption Score in GitKraken Insights."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: "Configuration, interpretation, and FAQ for the Cursor Boost rate"
    card_icon: user-check
    nav_category: metrics
    nav_label: "Cursor Boost"
    nav_order: 15
    nav_parent: expo-ai-adoption-agentic-metrics
    page_type: content
---
## Cursor Boost

> _The 25% (default) multiplier applied to a developer's Cursor adoption score before it layers into the headline Agent Adoption Score._

**Family:** Adoption & Agentic · **Where it appears:** Behind the scenes in the Adoption Score on /developers, /teams, /executive

### At a glance

GitKraken Insights treats Claude Code and Codex as "primary" AI tools and Cursor as a "secondary" one. Primary tools have their events unioned at the factor level into one combined score. Cursor is scored independently and _added_ to that primary score at a discount — the Cursor Boost rate — before the final score is capped at 100. The default discount is 25%, configurable via an environment variable.

This page exists so admins understand why a developer's Cursor activity contributes meaningfully to their Adoption Score but doesn't dominate it.

### Formula

```
Final Adoption (pre-maturity) = min(Primary + secondaryBoost × Cursor, 100)
  Primary = blend(Claude ∪ Codex)
  Cursor  = blend(Cursor events alone)
  secondaryBoost = 0.25 (default)
```

If Cursor data is absent (`nil`), the boost term is dropped entirely — no Cursor activity, no boost.

### How GitKraken Insights calculates it

**Step 1.** Per-provider scoring for Cursor runs the same four-factor blend (Daily Use, Hourly Spread, Prompts, Output) used for Claude and Codex — see [Agent Adoption Score](#agent-adoption-score) for the mechanics.

**Step 2.** The resulting Cursor score (0–100) is multiplied by the `secondaryBoost` rate (default 0.25 = 25%). So a developer at Cursor=80 contributes 80 × 0.25 = 20 points of additional adoption.

**Step 3.** The boosted value is added to the Primary score (Claude ∪ Codex) and the sum is capped at 100 before Maturity Factor scaling.

**Edge case: primary is zero.** If a developer has _no_ Claude/Codex activity but has Cursor activity, they still get the Cursor boost as their entire pre-maturity score. So a dev with Primary=0, Cursor=80 gets 0 + 20 = 20 pre-maturity, which becomes 15 at the default Maturity Factor of 0.75. They land in Emerging but at the high end — visible as "trying Cursor only."

### Why the asymmetric treatment

Claude Code and Codex export OpenTelemetry traces with detailed prompt/response/token/tool data. Cursor's API exports a less complete event stream — fewer fields, sparser timing, no token counts in some cases. We use Cursor data as **confirmatory signal** rather than primary evidence:

* If a developer is using Cursor consistently, we want their Adoption Score to reflect that.
* But we don't trust Cursor data enough to make it a 1:1 substitute for Claude / Codex.

The 25% boost rate is the empirical middle ground — high enough that Cursor power-users score meaningfully, low enough that incomplete data doesn't mask gaps in primary-tool adoption.

### How to read it

You don't typically read the Cursor Boost in isolation. What you read is the breakdown on a developer's expanded detail panel:

* A developer with Claude=70, Cursor=80 has Primary=70 + boost=20 → pre-maturity 90 (capped at 100), final 67.5 at MF=0.75.
* A developer with Claude=70, no Cursor data has Primary=70, no boost → pre-maturity 70, final 52.5.

The difference (15 points) is what Cursor adoption is contributing in that developer's case.

### Where it appears

* **/developers** — expanded panel breaks down "Claude contribution" / "Codex contribution" / "Cursor contribution" so you can see what each tool is doing for a developer's score.
* **Behind the scenes** in every Adoption Score on the dashboard.

<figure>
  <img src="/wp-content/uploads/ai-adoption-developers.png" class="help-center-img img-bordered" alt="Developers page in GitKraken Insights showing the Top 10 developers widget, score trend chart, and the developer table with Adoption, Agentic, Providers, and Output Score columns" />
  <figcaption>Developers page — Top 10 developers, score trend, and the full developer table with Adoption, Agentic, Providers, and Output Score columns.</figcaption>
</figure>

### Settings that affect it

* `SCORE_SECONDARY_BOOST` environment variable. Default 0.25. Range 0.0 to 1.0+. Changing it requires a backend restart.

The Cursor Boost rate is not currently exposed in the Settings UI. Ask your account manager if you need it tunable.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Agent Adoption Score](#agent-adoption-score) | Cursor Boost is one of two terms in the Adoption Score formula. |
| [AI Tier](#ai-tier) | Indirectly — Cursor adoption shows up here via Adoption Score. |

### How to use it

Cases where you might want to change the boost rate:

* **You've standardized on Cursor as a primary tool.** If your org has gone "Cursor-first" rather than "Claude-first," consider raising the boost to 0.5 or higher so Cursor adoption contributes proportionally.
* **You want Cursor to count equally with Claude / Codex.** Set the boost to 1.0. Now a Cursor-only developer can score the same as a Claude-only developer.
* **You want to phase Cursor out.** Set the boost to 0. Cursor data is ignored in adoption math (it still appears in /data and other detail views).

In most orgs, the default 0.25 is the right answer. Touch it only when your provider mix has genuinely shifted.

### Limitations and gotchas

* **No agentic score from Cursor.** Cursor's events don't expose tool-call data in a way we can score for the [Agent Autonomy Score](#agent-autonomy-score). So a developer doing all their autonomous AI work in Cursor will look low-Autonomy in the dashboard regardless of their actual practice.
* **Boost is additive, not blended.** We don't union Cursor events with Claude/Codex at the factor level (the way we do for Claude+Codex). Cursor is scored separately and then layered on.
* **Pre-cap addition.** Because we cap at 100 _after_ the boost is added, a developer at Primary=95 + Cursor=80 caps at 100 — the last 15 points of potential Cursor contribution are wasted.

### FAQ

**Q: Why isn't Cursor unioned with Claude / Codex?**
A: Their event streams aren't comparable enough at the field level. We'd need consistent prompt counts, token counts, and tool counts across all three providers to union them; Cursor doesn't currently export that consistently. As Cursor's API matures, this could change.

**Q: A developer uses Cursor exclusively and shows score 15. Is that right?**
A: At default settings, yes. Cursor=80 × 0.25 boost × 0.75 Maturity Factor = 15. If your org is Cursor-first, raise `SCORE_SECONDARY_BOOST` to 0.75 or 1.0 to give Cursor users a fair share of the score.

**Q: Why default 25% and not 50%?**
A: Empirically, the data we get from Cursor today is roughly a quarter as rich as what we get from Claude / Codex OTEL traces. 25% reflects "Cursor counts about as much as the data we have on it." If Cursor's event coverage improves, we'd raise the default.
