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
Spend per Tier = SUM(AI tool cost per developer) over developers in that tier

  AI tool cost per developer = each connected AI tool's cost for that developer
                               × that tool's AI cost rate (default 1×)
```

### How GitKraken Insights calculates it

**Step 1 — Total each developer's AI tool cost.** For each developer, GitKraken Insights adds up the cost of every connected AI tool in the window. Cursor's cost is reported by Cursor. The other tools' costs are GitKraken estimates based on each tool's usage. GitHub Copilot cost is counted once per developer, even when Copilot is connected more than one way.

**Step 2 — Apply your AI cost rates.** Each tool's cost is multiplied by that provider's [AI cost rate](/insights-expo/expo-ai-adoption-settings#ai-cost-rates) from Settings → General. The default rate is 1×, so until an admin sets a rate, the estimated costs are used as they are.

**Step 3 — Group by tier.** Once each developer's spend is computed, GitKraken Insights groups developers by their AI Tier for the window. The result is the total dollars allocated to each tier.

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

* **/ai-impact** — the **AI Spend by Tier** card alongside the cycle time and CFR by-tier charts, with the **AI Spend by Developer** card below it.

The **AI Spend by Developer** card lists every developer with recorded AI spend in the period, sorted by spend, ten per page. Its columns are **Developer**, **Team**, **AI Tier**, **Spend**, **Merged PRs**, and **Cost / PR**; use **Columns** to hide the ones you don't need. Hover the info icon next to the title to see the total spend and how many developers it covers. Developers with no AI spend in the period aren't listed, and **Cost / PR** shows a dash for a developer with no merged PRs. The AI Impact CSV export includes the same list as a **Spend by developer** section.

To scope the whole AI Impact report — both spend cards included — to specific people, open **Filters** and choose **Developer**. If none of the selected developers are in the current roster, the page says so; clear the filter or pick someone else.

<figure>
  <img src="/wp-content/uploads/gki-ai-spend-by-developer-card-01-v2@2x.png" class="help-center-img img-bordered" alt="The AI Spend by Developer card in GitKraken Insights, with an Adjusted badge beside its title and a Columns button, listing ten developers with their team, AI tier, spend, merged PRs and cost per PR, sorted by spend from $262.00 down to $143.00" />
  <figcaption>AI Spend by Developer — each developer's AI spend, merged PRs, and cost per merged PR for the period.</figcaption>
</figure>

### Settings that affect it

* [**AI cost rates**](/insights-expo/expo-ai-adoption-settings#ai-cost-rates) (Settings → General) — a multiplier per provider that adjusts estimated AI costs to match what you pay, for example after a negotiated discount (0.8 for a 20% discount, 1 for no adjustment). Each rate has an effective-from date, so a past date recalculates that provider's cost figures from then on. Default 1× for every provider.
* Cursor's cost is reported by Cursor; the other tools' costs are GitKraken estimates from their usage. Every tool's cost is then multiplied by its provider's AI cost rate.

When a cost figure includes a custom AI cost rate for the selected period, it shows an **Adjusted** badge — next to the **AI Spend by Tier** and **AI Spend by Developer** card titles, and as a small icon in table column headers such as **Token Usage Value** and **$ / Output** on the Developers table. Hover the badge to see which providers' custom rates are included, for example "Includes your organization's custom cost rate for Claude Code." The badge never shows the rate itself; see [AI cost rates](/insights-expo/expo-ai-adoption-settings#ai-cost-rates) for that.

<figure>
  <img src="/wp-content/uploads/gki-cost-adjusted-badge-01-v3@2x.png" class="help-center-img img-bordered" alt="The AI Spend by Developer card on AI Impact in GitKraken Insights with its Adjusted badge hovered beside the card title, showing the tooltip Includes your organization's custom cost rate for Claude Code, above the first four developers' team, AI tier, spend, merged PRs and cost per PR" />
  <figcaption>The Adjusted badge names the providers whose custom cost rates are included in a figure.</figcaption>
</figure>

### Related metrics

| Metric | Relationship |
| --- | --- |
| [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier) | The grouping dimension. |
| [Productivity Uplift](/insights-expo/expo-ai-adoption-impact-cost-productivity-uplift) | Spend / Uplift = ROI. Most healthy rollouts have uplift >> spend by an order of magnitude. |

### How to use it

* **Annual or quarterly license review.** Pull this chart before your AI tooling renewal conversation. Identify the Emerging cohort. Decide: re-engage them or remove their seats.
* **Re-allocate seats from Emerging to Explorer.** If you're capacity-constrained on licenses, the highest-leverage move is shifting them from devs who aren't using them to devs who are starting to.
* **ROI framing for the CFO.** "We spend $X on AI tooling and our estimated productivity uplift is $Y, with Z% of spend on developers in Regular+ tiers." The framing alone makes most renewal conversations easy.

### Limitations and gotchas

* **Most costs are estimates until you adjust them.** Only Cursor's cost is reported by the provider. We don't know your actual contract terms. If you're paying a different rate, the absolute dollar values will be off until you set [AI cost rates](/insights-expo/expo-ai-adoption-settings#ai-cost-rates) to match — but the _distribution across tiers_ is still accurate.
* **Costs depend on which tools are connected.** A tool's spend appears only for developers whose activity that tool reports, so a tool you haven't connected adds nothing to any tier. GitHub Copilot cost is counted once per developer, even when Copilot is connected more than one way.
* **Emerging doesn't mean "didn't use AI ever."** A developer can be Emerging in this window but a Regular last quarter. License seats are usually paid annually — even if their utilization is bad this window, they have the seat. The decision is about future renewal, not retroactive credit.

### FAQ

**Q: A team has high Emerging % and high spend on Emerging. Should I cut their licenses?**
A: Probably not as a first move. Most Emerging cohorts respond to re-engagement (training, pairing, office hours) faster and cheaper than license churn. Cut only after re-engagement has been tried.

**Q: How do I see the cost behind each AI event?**
A: /data → AI Events tab → filter by event type. `api_request` events carry `cost_usd`, which includes your organization's custom cost rate for the provider when one is set. To see spend per person rather than per event, use the **AI Spend by Developer** card on /ai-adoption/ai-impact.

**Q: We don't pay per-developer for Claude — we pay enterprise. Does this work?**
A: Yes, but you'll need to manually allocate the enterprise cost back to developers (e.g. divide by headcount). If your effective price differs from the estimated costs, set that provider's [AI cost rate](/insights-expo/expo-ai-adoption-settings#ai-cost-rates) multiplier in Settings → General so the dollar figures match what you pay.
