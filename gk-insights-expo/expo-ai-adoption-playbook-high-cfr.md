---
title: Interpret a High CFR Week
description: How to interpret a spike in Change Failure Rate and determine whether action is needed.
product: GitKraken Insights
content_type: how-to
audience: team-lead
plan_required: GitKraken Insights
status: GA
page_type: content
nav_category: playbooks
nav_order: 40
nav_label: High CFR
card_icon: alert-triangle
card_color: amber
card_description: Interpret a spike in Change Failure Rate
taxonomy:
    category: insights-expo
---
<kbd>Last updated: September 2026</kbd>

> _Your Change Failure Rate spiked. Before you sound the alarm, walk this playbook to figure out whether it's a real quality problem or an artifact._

## The problem

CFR spikes are alarming and often misleading. A high CFR week can mean any of: an actual quality regression, a backlog of customer bugs got triaged this week, one bad release dominates the average, your sync caught up after a stale period, a reporting threshold change in Jira.

The dashboard's CFR is honest about what it's measuring (customer-reported bugs as a percentage of releases), but the surrounding context matters a lot. This playbook is the 20-minute triage.

## Where to look

### Step 1 — Look at the trend, not the snapshot (3 min)

Open `/impact`. Look at the **CFR trend chart** (left side, severity-stacked).

* **If the spike is one week and CFR is back to baseline before and after**, you have a localized incident, not a trend.
* **If CFR has been climbing for 3+ weeks**, you have a real quality drift.
* **If CFR has been flat then suddenly stepped up**, you have a regression at a specific point.

Note which pattern you're seeing.

### Step 2 — Check the severity breakdown (3 min)

Same chart, look at the severity stack. The headline CFR % includes all severities, but **High and Critical bugs are what actually hurt**.

* If the spike is in Low / Medium severity, you may have a triage influx (someone categorized old bugs this week). Less concerning.
* If the spike is in High / Critical, treat it as urgent.

The **CFR KPI card** (left of the trend) shows High & Critical specifically — that's the number that matters.

### Step 3 — Pull the customer bugs themselves (10 min)

Scroll down to the drill-down table below the CFR charts. Sort by **Created date descending**. Look at the bugs that drove the spike.

Patterns to spot:

* **Same repo repeatedly?** That repo had a bad release. Investigate the release, not the org.
* **Same root cause repeatedly?** A specific bug class (e.g. "null pointer in date parser") shipped multiple times. Process / test coverage issue.
* **All bugs filed by one customer?** May be one customer's escalated reports rather than a real quality spike. Look at the cluster.
* **Bugs created this week but referencing changes from weeks ago?** Reporting lag. Your quality wasn't suddenly worse — customers just noticed something old.
* **A backlog of bugs that got categorized this week?** Look at the create-dates carefully. If many bugs were filed weeks ago but only categorized as "Customer Bug = Yes" today, the spike is bookkeeping, not quality.

### Step 4 — Look at AI Tier breakdown (5 min)

This is where the dashboard does something unique. Click into `/ai-impact` and look at **CFR by AI Tier**.

The question: **does CFR scale with AI tier?**

* If Power Users have _lower_ CFR than Emerging devs, AI adoption is a quality boost. Good news.
* If Power Users have _higher_ CFR, AI is enabling faster shipping at a quality cost. Investigate AI-assisted work specifically.
* If CFR is uncorrelated with tier, the spike isn't an AI issue — it's a general quality issue.

## What to do

Pick the intervention that fits the pattern you found.

### Pattern: One localized incident

A single spike that's already resolved. Often a bad release that's been hot-patched.

**Action:** Run the team's standard post-mortem on the responsible release. CFR will return to baseline naturally. No structural change needed unless the post-mortem reveals a class of issue worth fixing.

### Pattern: 3+ week climb

Genuine quality drift.

**Action:** Pause non-critical feature work for the affected team for one sprint. Sample the recent bug reports — look for a common root cause. Strengthen pre-merge review on the affected repo(s). Consider mandatory reviewers if you've been relaxed about review. Look at [First-Pass Rate](/gk-insights/ai-adoption-flow-metrics#first-pass-rate) over the same window. A First-Pass climbing alongside CFR is the rubber-stamping pattern — your reviewers aren't catching things.

### Pattern: Suddenly stepped up

Something specific changed. Possibilities: new release process introduced a gap, a senior developer left and quality oversight dropped, a new team or repo joined the dataset, a Jira workflow change started catching previously-uncategorized bugs.

**Action:** Find the discontinuity. Cross-reference with your team's known events (releases, hires, departures, process changes). The step is almost always a specific event.

### Pattern: AI Tier scaling problem

Power Users have higher CFR than Emerging.

**Action:** This is the most interesting and rarest case. AI is helping with speed but at a quality cost. Look at: Are Power Users shipping bigger PRs because AI made it easy? Check Effort Score distribution. Is the review process catching AI-generated bugs? Check First-Pass Rate among Power Users. Is there a specific class of bug that's AI-correlated? (E.g. wrong API usage, hallucinated function calls.) Drill into the bug list.

**Don't conclude "AI is bad" yet** — investigate. The fix is usually "review AI-assisted code more carefully," not "stop using AI."

## What to expect

A reasonable expectation after intervening on a real CFR climb:

* **Week 1 after intervention:** Numbers still noisy. The bugs reported this week are from changes that shipped weeks ago. Don't read week 1.
* **Week 2–4:** CFR should start trending down. If you intervened on review process, expect 3–4 weeks before the data reflects it.
* **Week 6–8:** Verdict. If CFR is back to baseline or below, the intervention worked. If not, your hypothesis was wrong — revisit step 3.

## Common mistakes

* **Reading one week as a trend.** CFR has natural variance week to week. A single spike doesn't constitute drift.
* **Slowing the team without diagnosing.** "Pause everything" is sometimes the right call, but usually the right call is "fix the specific thing." Diagnose first.
* **Blaming AI without checking the tier breakdown.** If CFR is high but uncorrelated with AI tier, it's a general quality issue, not an AI issue.
* **Ignoring severity.** A 20% CFR week of all-Low-severity bugs is much less urgent than an 8% CFR week of Critical bugs.

## Related metric pages

* [Change Failure Rate (CFR)](/gk-insights/ai-adoption-dora-metrics#change-failure-rate-cfr)
* [MTTR](/gk-insights/ai-adoption-dora-metrics#mean-time-to-recovery-mttr)
* [First-Pass Rate](/gk-insights/ai-adoption-flow-metrics#first-pass-rate)
* [AI Tier](/gk-insights/ai-adoption-agentic-metrics#ai-tier)
