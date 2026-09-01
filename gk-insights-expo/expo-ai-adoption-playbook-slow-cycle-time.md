---
title: Investigate a Slow Cycle Time
description: Step-by-step triage guide for diagnosing and resolving slow cycle time in GitKraken Insights.
product: GitKraken Insights
content_type: how-to
audience: team-lead
plan_required: GitKraken Insights
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: amber
    card_description: Triage guide for cycle time bottlenecks
    card_icon: clock-pause
    nav_category: playbooks
    nav_label: Slow Cycle Time
    nav_order: 30
    page_type: content
---
<kbd>Last updated: September 2026</kbd>

> _Your team's [Cycle Time](/insights-expo/expo-ai-adoption-flow-metrics#cycle-time) just crept past your tolerance. This playbook walks you from "something's wrong" to "I know which lever to pull" in about 30 minutes._

## The problem

You opened /ai-adoption/teams or /ai-adoption/ai-impact, saw Cycle Time at 5+ days, and felt the familiar sinking feeling. The number is the symptom — the cause is something specific you can intervene on. This playbook is how to find it.

## Where to look

Three views, in order:

### Step 1 — Phase breakdown (5 min)

Open `/ai-adoption/ai-impact`. Set the date range to the period that triggered the concern (typically the last 14 days).

The Cycle Time card has a dimension dropdown. Set it to **Phase** (the default). You will see Cycle Time decomposed into Coding, Pickup, Review, and Deploy (if release detection is configured for your repos).

**Find the dominant phase.** Whichever phase is the biggest chunk of the stack is your starting point.

| Dominant phase | What it usually means |
| --- | --- |
| **Coding** | Either deep work (legit) or stalled work (problem). Cross-check with WIP and the developer-level System Metrics tab. |
| **Pickup** | Review queue is backed up. Reviewers overloaded or absent. Almost always the cause for teams over 3 days. |
| **Review** | PRs too big, or specs too thin. Check Review Cycles. |
| **Deploy** | Manual deploy gate or release embargo. Talk to ops. |

If two phases are roughly tied, both need attention — but tackle the biggest one first.

### Step 2 — Trend (3 min)

Switch the same chart to **Trends** mode. Pick **By Phase** as the trend type. Each phase now shows as a separate line over time.

**The question is: when did this start?**

* If one phase has been climbing for 4+ weeks, you have a slow-drift problem (process drift, attrition, gradual scope creep). Address it as a process issue.
* If one phase spiked in the last 1–2 weeks, you have an acute problem (someone on PTO, a release embargo, a specific stalled PR). Address it tactically.

### Step 3 — Drill into the PRs (15 min)

Click a high point on the trend chart. The drill-down table opens below with every PR in that time bucket. Sort by **Cycle Time descending**.

**Look at the top 5–10 PRs.** Patterns to spot:

* **Same author repeatedly?** That developer either has a quality problem causing many cycles, or they are working on hard problems that take longer. Talk to them.
* **Same repo repeatedly?** That repo has a process problem — maybe a slow reviewer, maybe brittle CI, maybe an over-zealous required-reviewer policy.
* **High Review Cycles on the slow PRs?** The PRs are too big or the spec was unclear. Look at PR descriptions and effort scores.
* **All slow PRs opened around the same time?** A reviewer was out or a release embargo hit. Often resolves itself.
* **One huge outlier PR that has been open for weeks?** That PR is the metric. Decide: merge it, split it, or close it.

## What to do

Pick **one** intervention from the most likely cause. Don't try to fix everything at once — you can't tell what worked.

### If Pickup dominates

* **Establish a weekly review-clearing ritual.** 15 minutes on Monday morning. Walk through the oldest open PRs in your repo or PR queue. Decide: ship, give a deadline, or close each one. This single habit fixes Pickup on most teams within 2–4 weeks.
* **Add a reviewer rotation.** If one developer is doing most of the reviewing, distribute it.
* **Set a Pickup SLO.** "PRs get first review within four business hours." Make it visible.

### If Review dominates

* **Right-size PRs.** Aim for PRs reviewable in 15 minutes. Check the Effort Score distribution on /ai-adoption/data-explorer: a team consistently at 0.7+ effort medians is over-batching.
* **Improve PR descriptions.** A clear "what / why / how to test" cuts review cycles. AI-assisted PR descriptions are a low-effort win.
* **Calibrate reviewers.** If one reviewer's PRs consistently get 2+ cycles, have the conversation — it is usually a habit, not a quality issue.

### If Coding dominates

* **Check WIP.** A team with a high Coding phase and high WIP is stalled, not deep-working. Help unstick or descope.
* **Cross-check with developer activity.** /ai-adoption/developers expanded detail shows recent activity. A developer with the high-Coding PR who hasn't committed in 4 days is stuck. Talk to them.
* **Consider AI-assisted scaffolding.** If devs are taking days on the boilerplate-heavy part of new work, AI can compress it.

### If Deploy dominates

* **Talk to ops or release engineering.** Insights surfaces the symptom; the fix is process-side.
* **Consider continuous delivery.** If your team batches deploys, moving to merge-deploys can cut the Deploy phase to near zero.

## What to expect

A reasonable expectation, having intervened on the dominant phase:

* **Week 1:** Numbers may still be noisy. Don't read week 1 as a verdict.
* **Week 2–3:** The intervention either took or it didn't. Pickup interventions show up fastest — usually 2 weeks before the average comes down. Review interventions take 4–6 weeks because they require behavior change.
* **Week 4:** If you don't see improvement, your hypothesis was wrong. Go back to Step 1 — what does the breakdown look like _now_? Sometimes the underlying problem has shifted.

## Common mistakes

* **Trying to fix all four phases at once.** You won't know what worked. One lever at a time.
* **Reading a single week.** Cycle Time has 2–3 week natural cycles. A bad week could be Christmas-week noise; a bad month is a signal.
* **Blaming individual developers.** The cycle time of a team is mostly the team's process, not its individuals. Process interventions work; finger-pointing doesn't.
* **Focusing on Total Cycle Time when one phase tells the story.** Always look at the breakdown.

## Related metric pages

* [Cycle Time](/insights-expo/expo-ai-adoption-flow-metrics#cycle-time)
* [Review Cycles](/insights-expo/expo-ai-adoption-flow-metrics#review-cycles)
* [WIP](/insights-expo/expo-ai-adoption-flow-metrics#work-in-progress-wip)
* [First-Pass Rate](/insights-expo/expo-ai-adoption-flow-metrics#first-pass-rate)
