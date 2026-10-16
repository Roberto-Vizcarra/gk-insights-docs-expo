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
custom_fields:
    card_color: green
    card_description: Productivity Uplift, AI-Assisted %, CapEx/OpEx, Spend by Tier
    card_icon: currency-dollar
    nav_category: metrics
    nav_label: AI Impact & Cost
    nav_order: 50
    nav_parent: expo-ai-adoption-metrics
    page_type: index
---
<kbd>Last updated: October 2026</kbd>

> **Note:** This page covers the **AI Impact & Cost** family of the AI Adoption dashboard. For the code-quality and acceptance-rate metrics from connected AI providers, see [AI Impact Metrics](/insights-expo/expo-ai-adoption-impact-cost-metrics).

This family answers: **is AI actually paying off?**

---

Other families measure adoption (are people using it?) and output (is work shipping?). This family connects those signals to dollars and hours — the language executives and finance teams need.

These numbers are _estimates_, not measurements. The dashboard is honest about that throughout, but it is worth saying up front: when we publish "$1.2M in annualized productivity gain," we mean "directionally, this is the order of magnitude." Use these numbers for storytelling and order-of-magnitude framing, not for finance reconciliation.

---

## The metrics

| Metric | What it captures |
| --- | --- |
| [**Productivity Uplift**](/insights-expo/expo-ai-adoption-impact-cost-productivity-uplift) | The headline ROI numbers. Volume speedup vs. baseline period, plus per-dev and Power-User comparisons within the current window. |
| [**AI-Assisted Percentage**](/insights-expo/expo-ai-adoption-impact-cost-ai-assisted-percentage) | What % of shipped _changes_ (PRs + commits) had AI assistance. Weighted by lines changed. |
| [**CapEx / OpEx Split**](/insights-expo/expo-ai-adoption-impact-cost-capex-opex) | Software capitalization breakdown. Which work counts as capitalizable engineering investment? |
| [**Spend by Tier**](/insights-expo/expo-ai-adoption-impact-cost-spend-by-tier) | AI tooling cost allocated across AI Tiers. Where is the money going, and is it landing on Power Users? |

For AI cost per developer, the Developers table on /ai-adoption/developers adds two columns. **Token Usage Value** is a developer's AI API spend across your connected AI coding tools in the selected window. **$ / Output** is AI API spend per point of output the developer shipped in that window: lower means more shipped effort per AI dollar, and $0.00 means the output shipped without billed AI usage. A dash means there's no scored output to divide by — for example, for a developer on PTO for the whole window. The developers CSV export includes **$ / Output** too.

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

* **/ai-adoption/ai-impact** — the dedicated home for this family. Productivity hero cards, AI Insight banner, Cycle Time and PR Volume by AI Tier, AI Spend by Tier, AI Spend by Developer, CFR by AI Tier. Open **Filters** and choose **Developer** to scope the whole page to specific people.
* **/ai-adoption/executive** — Productivity Uplift in the hero KPI strip and the executive insight banner.
* **/ai-adoption/capex** — CapEx / OpEx split as the primary surface.
* **/ai-adoption/teams**, **/ai-adoption/developers** — AI-Assisted % appears in trend lines and breakdowns.

<figure>
  <img src="/wp-content/uploads/gki-ai-spend-by-developer-card-01-v2@2x.png" class="help-center-img img-bordered" alt="The AI Spend by Developer card in GitKraken Insights, with an Adjusted badge beside its title and a Columns button, listing ten developers with their team, AI tier, spend, merged PRs and cost per PR, sorted by spend from The AI Spend by Developer card in GitKraken Insights listing ten developers with their team, AI tier, spend, merged PRs and cost per PR, sorted by spend from $262.00 down to $143.0062.00 down to alt="43.00" />
  <figcaption>AI Spend by Developer on /ai-adoption/ai-impact — each developer's AI spend, merged PRs, and cost per merged PR.</figcaption>
</figure>
