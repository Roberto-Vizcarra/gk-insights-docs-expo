---
title: AI Adoption for Engineering Leaders
description: Weekly scan, quarterly review, and settings checklist for engineering leaders using GitKraken Insights.
product: GitKraken Insights
content_type: how-to
audience: engineering-leader
plan_required: GitKraken Insights
status: GA
page_type: content
nav_category: getting-started
nav_order: 30
nav_label: For Engineering Leaders
card_icon: code
card_color: purple
card_description: Weekly scans, quarterly reviews, and settings checks
taxonomy:
    category: insights-expo
---
<kbd>Last updated: September 2026</kbd>

## For engineering leaders

You run multiple teams. You have a board meeting next week and a 1:1 with your VP the week after. This guide is how to extract the right answers in 30 minutes flat.

### Your weekly 10-minute scan

Every Monday morning, open `/teams` with the date range set to the last 14 days. Look at three things:

1. **Tier mix bars.** Are any teams disproportionately Emerging or Explorer? That's a leading indicator of an adoption problem you can intervene on this week.
2. **Cycle Time outliers.** Any team over 4 days? Click in — almost always it's a review bottleneck (high review cycles) or a WIP problem (too many open PRs), not coding speed.
3. **Output Score swings.** A team's Output Score down 30% week-over-week deserves a check-in. Usually it's a quarter-end shift in priorities, but sometimes it's a process breakage.

If nothing flagged, you're done. If something flagged, drill into the team's expanded row → System Metrics tab.

### Your quarterly review

A 45-minute structured review using four pages. Do this once per quarter with your engineering leadership group.

#### Step 1 — Org-level AI rollout health (5 min)

Open `/executive`. Note: AI Adoption % current vs. last quarter, Power User % current vs. last quarter, AI-Assisted % current vs. last quarter.

**Question to answer:** Did we move the rollout forward this quarter? If yes, by how much and on which teams? If no, what's blocking us?

#### Step 2 — Delivery health (10 min)

Stay on `/executive`. Look at Cycle Time, Throughput, Deploy Frequency, and CFR trends.

**Question to answer:** Did delivery health improve, hold, or regress alongside AI adoption? You're testing the thesis "AI use should compound into better flow."

If Cycle Time worsened despite adoption climbing — flag for investigation. It usually means the team has _adopted_ AI without the flow practices (small PRs, tight reviews) to take advantage of it.

#### Step 3 — Team-by-team review (20 min)

Open `/teams`. Walk through each team's expanded row.

For each team, write down: their adoption trajectory (improving / steady / declining), their delivery trajectory (improving / steady / declining), one specific question they should answer at their next team retro.

The dashboard's job is to surface the questions. The answers come from the team.

#### Step 4 — Investment review (10 min)

Open `/ai-impact`. Look at: Productivity Uplift % vs. baseline, estimated $ saved per week, AI-Assisted % of lines changed.

**Question to answer:** Is the AI tooling spend still returning more value than it costs? For most orgs running Claude Code + Cursor, breakeven happens in single-digit weeks. If yours hasn't, look at adoption depth — the math fails when adoption is shallow even if it's broad.

### How to read scores fairly

The most common mistake leaders make with this dashboard is reading individual developer scores as a performance review proxy. Read [How to think about developer scores](/gk-insights/ai-adoption-getting-started#how-to-think-about-developer-scores) before you go anywhere near a 1:1 talking about a score.

What scores _are_ good for: **Cohort comparison** ("Our backend team has 80% Power Users, our mobile team has 20%. Why?"), **Onboarding signal** ("New hires are landing in Emerging after 8 weeks instead of Explorer. Something's wrong with our onboarding."), **Tooling signal** ("Three teams using Cursor + Claude Code together hit Power User faster than teams on Claude Code alone.").

What scores are _not_ good for: "Why is Alex at Explorer when Sam is at Power User?" Without context (project, role, week), that question is unanswerable from the dashboard.

### Settings to verify quarterly

These three settings drift over time and need a quarterly check:

| Setting | What to check |
| --- | --- |
| **Maturity Factor** | Still 0.75? If your org has matured, raise it. If your adoption % has plateaued for 2+ quarters, consider whether the ceiling is the constraint. |
| **Tier Weights** | Default 0.5 / 0.2 / 0.3. If your org has moved from "rolling out" to "extracting value", consider shifting weight toward Output. _(Editable in Settings → General.)_ |
| **Baseline Period** | Default Nov 1 last year. If you launched a new AI tool mid-year, anchor the baseline to a month _before_ that launch so uplift math is meaningful. |

→ [Playbook — Set tier weights for your org's maturity](/gk-insights/ai-adoption-playbook-tier-weights)

### Where to drill further

* **Investigating a slow team** → [Playbook — Investigate a slow cycle time](/gk-insights/ai-adoption-playbook-slow-cycle-time)
* **Planning a new tool rollout** → [Playbook — Roll out AI tooling with the Adoption Score](/gk-insights/ai-adoption-playbook-ai-rollout)
* **Quality regression** → [Playbook — Interpret a high CFR week](/gk-insights/ai-adoption-playbook-high-cfr)
