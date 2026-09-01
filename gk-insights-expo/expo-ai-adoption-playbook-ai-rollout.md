---
title: Roll Out AI Tooling with the Adoption Score
description: A phased plan for rolling out AI coding tools using GitKraken Insights metrics to track progress.
product: GitKraken Insights
content_type: how-to
audience: engineering-leader
plan_required: GitKraken Insights
status: GA
page_type: content
nav_category: playbooks
nav_order: 20
nav_label: AI Rollout
card_icon: rocket
card_color: amber
card_description: Phased rollout plan tracked by Adoption Score
taxonomy:
    category: insights-expo
---
<kbd>Last updated: September 2026</kbd>

> _You're launching (or accelerating) Claude Code, Codex, or Cursor across your org. This playbook is how to use the dashboard as the operational backbone of that rollout._

## The problem

Most AI tooling rollouts fail not at the install step but at the adoption-depth step. Developers get licenses, install the tool once, use it for a week, and drift away. The dashboard's job is to surface that drift early and give you a structured way to intervene.

This is the playbook for the first 6 months of an active rollout.

## Where to look

Three operational views — each has a different cadence:

| Cadence | View | What you're checking |
| --- | --- | --- |
| **Weekly** | /developers, sorted by Adoption Score ascending | The Emerging cohort. Who's stuck? Who's ramping? |
| **Bi-weekly** | /teams tier mix bars | Team-level momentum. Which teams are pulling ahead, which are stalled? |
| **Monthly** | /executive trend lines | The headline rollout health story. What goes in the leadership update? |

## What to do — the 6-month rollout playbook

### Month 1 — Set the baseline

**Goal:** Establish where you are _before_ the rollout intervention starts.

1. **Set Maturity Factor to 0.65 in Settings → General.** Lower than the 0.75 default. In the first 3 months, you want the tier ladder to feel achievable. A dev who picks up Claude this month and uses it consistently for 4 weeks should land in Regular, not get stuck in Explorer because the org P90 hasn't shifted yet. Raise back to 0.75 in month 4.
2. **Keep Tier Weights at defaults** (0.5 / 0.2 / 0.3). Adoption-heavy is right for early rollouts.
3. **Take a baseline snapshot.** Note the org's Adoption %, Power User %, AI Adoption %. Write them in your rollout doc. You'll cite these in month 3 and month 6.
4. **Roll out licenses to your launch cohort.** A pilot team or two is usually the right scope. Don't try org-wide on day one.

### Month 2 — Activate the pilot

**Goal:** Get the pilot cohort from Emerging to Explorer+.

1. **Set up the weekly Emerging review.** Every Friday, open /developers filtered to the pilot teams, sorted by Adoption ascending. Walk the bottom 10. For each: do they need a tooling fix, a pairing session, or a usage-pattern intro?
2. **Run an AI office hour.** 30 minutes weekly. Open invitation. Show patterns. The single highest-leverage intervention in week 2–4.
3. **Pair a Power User with an Emerging developer.** One hour of paired work. Watch the Emerging developer's score climb in the next 2-week window.

### Month 3 — Pilot review, expand the cohort

**Goal:** Pilot retro + expand to next cohort.

1. **Pull the month 1 baseline.** Compare to today's numbers. Honest read: did the pilot work? Pilot team's Adoption % (target: 60%+ at Explorer+), pilot team's tier mix (target: ≥20% in Regular), pilot team's Output Score trend (target: flat or up — Output usually lags Adoption by 1-2 months).
2. **Document what worked.** Office hours, pairing, internal Slack channel — whatever the pilot proved. Codify it as the rollout SOP.
3. **Expand to the next cohort.** Apply the same playbook to 2–3 more teams.

### Month 4 — Watch the Power User cohort

**Goal:** Make sure the pilot cohort doesn't regress.

1. **Raise Maturity Factor to 0.75** (the default). The pilot cohort has earned the standard ceiling.
2. **Watch the pilot cohort weekly.** Some developers will regress (the tool stops being novel; they fall back to old habits). Catch the regression in /developers — the trend sparkline shows it before the score does.
3. **Re-engage regressors.** Same playbook: pairing, office hours, audit their toolchain.

### Month 5 — Org-wide expansion

**Goal:** Make AI tools the default, not the opt-in.

1. **All new developers get licenses on day 1.** Built into onboarding. New hires landing in Emerging after 8 weeks is a process flag.
2. **Watch the Maturing-team cohort.** Some teams will plateau at "everyone's an Explorer, no one's a Power User." That's the "broad but shallow" pattern — start working on the [Agent Autonomy Score](/gk-insights/ai-adoption-agentic-metrics#agent-autonomy-score) by running agentic-pattern workshops.

### Month 6 — Strategic review

**Goal:** Decide what's next.

1. **Pull the 6-month report.** Org Adoption %, Power User %, Productivity Uplift, AI-Assisted %. Compare to month 1 baseline.
2. **If the rollout worked:** Consider shifting Tier Weights toward Output (e.g. 0.4 / 0.2 / 0.4). You're now in the Maturing phase. (Tier Weights are configured in `app_settings` per-org and editable in Settings → General.)
3. **If parts didn't work:** Identify the laggard teams. They usually share a root cause (one team lead who's skeptical, one repo with bad CI, one process gap). Address structurally rather than per-developer.
4. **Plan the next quarter:** Is the focus broadening adoption (still teams to onboard), deepening adoption (Power User % to grow), or extracting value (Output Score / Productivity Uplift to push)?

## What to expect

A typical "successful" rollout trajectory across 6 months:

| Month | Adoption % | Power User % | Productivity Uplift |
| --- | --- | --- | --- |
| 1 (baseline) | 5–15% | 0–2% | 0% |
| 2 | 15–30% | 1–5% | 0–5% |
| 3 | 30–50% | 5–10% | 5–10% |
| 4 | 45–65% | 10–18% | 10–15% |
| 5 | 60–80% | 15–25% | 15–25% |
| 6 | 70–90% | 20–35% | 20–35% |

These are typical ranges, not guarantees. Some orgs move faster (greenfield product teams), some slower (enterprise with platform gating). The shape is what matters: Adoption rises first, Power User % follows with a 1–2 month lag, Uplift follows Power User by another 1–2 months.

If your trajectory has _Adoption climbing but Uplift flat at month 4+_, your rollout is broad but shallow. Pivot to agentic workshops and pairing.

## Common mistakes

* **Org-wide on day 1.** Spreads adoption too thin to track. Pilot, prove, then expand.
* **Ignoring the trajectory in favor of the snapshot.** Adoption % at 30% is a great number if it was 5% three months ago. It's a bad number if it's been 30% for six months.
* **Treating tier as a performance metric.** Read [How to think about developer scores](/gk-insights/ai-adoption-getting-started#how-to-think-about-developer-scores) before any 1:1 about a developer's tier.
* **Not adjusting Maturity Factor with the rollout.** Default 0.75 is calibrated for active rollout. In month 1, lower it. In month 12, raise it. It's a knob for a reason.
* **Picking AI adoption as the only metric to watch.** Pair it with Cycle Time and CFR. If you ship faster but break more things, you haven't won.

## Related pages

* [Agent Adoption Score](/gk-insights/ai-adoption-agentic-metrics#agent-adoption-score)
* [Agent Autonomy Score](/gk-insights/ai-adoption-agentic-metrics#agent-autonomy-score)
* [Maturity Factor](/gk-insights/ai-adoption-agentic-metrics#maturity-factor)
* [Playbook — Set tier weights for your org's maturity](/gk-insights/ai-adoption-playbook-tier-weights)
* [For Engineering Leaders](/gk-insights/ai-adoption-for-engineering-leaders)
