---
title: "Spend by Tier"
description: "How GitKraken Insights calculates and displays Spend by Tier."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for Spend by Tier"
    card_icon: currency-dollar
    nav_category: metrics
    nav_label: "Spend by Tier"
    nav_order: 54
    nav_parent: expo-ai-adoption-impact-cost-metrics
    page_type: content
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
| [AI Tier](/insights-expo/expo-ai-adoption-agentic-metrics#ai-tier) | The grouping dimension. |
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
