---
title: AI Adoption Playbooks for GitKraken Insights
description: Action-first guides for AI Adoption in GitKraken Insights, including setting tier weights, rolling out AI tooling, investigating a slow cycle time, and interpreting a high CFR week.
product: GitKraken Insights
content_type: how-to
audience: admin
plan_required: GitKraken Insights
integrations: [Claude Code, Codex, Cursor]
status: GA
taxonomy:
    category: gk-insights
---
<kbd>Last updated: June 2026</kbd>

Action-first guides. Use these when you have a question like "how do I roll out AI tooling?" or "my cycle time is high — what now?"

| Playbook | Use it when |
| --- | --- |
| [Set tier weights for your org's maturity](#set-tier-weights-for-your-orgs-maturity) | You are setting up Insights or reviewing your config and wondering whether the default Tier Weights are right for your org. |
| [Roll out AI tooling with the Adoption Score](#roll-out-ai-tooling-with-the-adoption-score) | You're launching (or accelerating) Claude Code, Codex, or Cursor across your org. |
| [Investigate a slow cycle time](#investigate-a-slow-cycle-time) | Your team's Cycle Time just crept past your tolerance. |
| [Interpret a high CFR week](#interpret-a-high-cfr-week) | Your Change Failure Rate spiked and you need to know if it's real. |

---

## Set tier weights for your org's maturity

> _The default Tier Weights (0.5 Adoption / 0.2 Agentic / 0.3 Output) are tuned for orgs in active AI rollout. This playbook helps you decide whether to keep them or change them._

**How to change Tier Weights.** Tier Weights are stored per-org in `analytics.app_settings` as the keys `tier_weight_adoption`, `tier_weight_agentic`, and `tier_weight_output`. The Settings → General form exposes them as user-editable fields. Set the new values there; the change takes effect immediately for the current window.

### The problem

You are setting up Insights (or you are six months in and reviewing your config) and you are wondering whether the default Tier Weights are right for your org. The defaults assume "we are rolling out AI and we care that everyone is trying it." That is right for most orgs in the first year. It is wrong for orgs further along.

This playbook helps you pick a weighting that reflects where your org actually is in its AI journey.

### Where to look

#### Step 1 — Diagnose your current state (10 min)

Open `/ai-adoption/executive`. Look at three numbers:

| Number | What it tells you |
| --- | --- |
| **AI Adoption %** (devs at Explorer+) | How broad your rollout is |
| **Power User %** | How deep your rollout is |
| **Productivity Uplift %** | Whether breadth and depth are converting to value |

Also open `/ai-adoption/ai-impact` and look at **AI-Assisted % of changes**.

The four numbers together place your org in one of four states:

| Adoption % | Power User % | AI-Assisted % | Uplift % | State |
| --- | --- | --- | --- | --- |
| Low (< 40%) | Very low (< 10%) | Low | Low | **Early rollout** |
| Medium (40–70%) | Low (10–20%) | Medium | Low–medium | **Active rollout** |
| High (70–90%) | Medium (20–35%) | Medium–high | Medium | **Maturing** |
| Very high (90%+) | High (35%+) | High | High | **Mature** |

Don't agonize over the state. Most orgs are clearly in one. The interesting question is which way to _tune_ from there.

### What to do

#### If you are in Early or Active rollout

**Keep the defaults: 0.5 / 0.2 / 0.3.**

The defaults emphasize Adoption (50%), which is right for early rollouts. The signal you want from the dashboard is "are people picking up the tools?" — and Adoption is what answers that. Output matters but follows adoption with a 4–12 week lag, so weighting it heavily early biases the tier composite against the right early signal.

If anything, consider lowering the **Maturity Factor** to 0.6 or 0.65 (this _is_ settable in Settings → General) to leave more headroom for developers to feel like they are progressing. Don't change tier weights yet.

#### If you are Maturing

**Consider setting 0.4 / 0.2 / 0.4 in Settings → General.**

You have established broad adoption. The next question is whether adoption is converting to output. Balancing Adoption and Output equally surfaces the developers who are doing both. The 0.2 Agentic stays unchanged — agentic depth is still emerging.

Also consider raising the **Maturity Factor** to 0.85 (settable in Settings → General) to reflect that your org's P90 should now mean something close to "Power User territory."

#### If you are Mature

**Consider setting 0.3 / 0.2 / 0.5 in Settings → General.**

In a mature org, adoption is the baseline assumption. The differentiator between developers is output and depth. Weighting Output most heavily emphasizes "who is actually delivering with this." The 0.2 Agentic stays unchanged — agentic adoption is harder to push deep and most orgs cap out around 0.3 agentic mean.

Maturity Factor at 0.95–1.00 reflects that you have earned the top of the scale.

#### If you have a specific goal

| Goal | Suggested weights |
| --- | --- |
| "Drive AI adoption — this is a strategic priority" | 0.7 / 0.2 / 0.1 |
| "Encourage autonomous AI workflows specifically" | 0.3 / 0.5 / 0.2 |
| "Reward shippers" | 0.3 / 0.1 / 0.6 |
| "Balanced" | 0.4 / 0.3 / 0.3 |

Don't optimize for one of these unless you have actually decided that is the org's strategic focus this quarter. Most orgs benefit from the defaults plus minor tweaks.

### Things to know before you change weights

1. **Weights are renormalized to sum to 1.0 on every read.** So setting 1.0 / 0.5 / 0.5 yields effective 0.5 / 0.25 / 0.25. Most people find it clearer to choose values that already sum to 1.
2. **All three weights must be strictly positive.** Zero or negative values are rejected; the default for that key is used instead. There is never a "100% output, 0% everything else" state.
3. **Changes affect every developer immediately.** No retroactive recomputation of historical snapshots, but the _current_ window updates as soon as the keys are set. Plan an announcement.
4. **The tier thresholds (25 / 55 / 80) don't move.** Changing weights _will_ re-tier some developers. Some will move up, some down.

### How to roll out a weight change

A practical sequence:

1. **Note the current weights.** Hover any tier badge on /ai-adoption/developers — the tier-score tooltip shows the live weights for that developer's composite. Write the values down.
2. **Set the three new values** in Settings → General. Note the reason for the change for your own records ("we are Maturing and want to balance Adoption and Output equally").
3. **Sanity check the new distribution.** After saving the change, open /ai-adoption/developers and verify the tier distribution looks roughly as expected. If half your Regulars dropped to Explorer, the change was bigger than intended.
4. **Announce the change** to your team via the channel you would use for any process change. Frame it as "we are shifting emphasis toward \[X\] to reflect \[Y\]." Reassure people that tier movement is by design, not a personal regression.
5. **Don't change them again for at least a quarter.** Tier weights are strategic settings, not tactical knobs. Frequent changes erode trust in the dashboard.

### What to expect

After a weight change:

* **Day 1:** The change is live. /ai-adoption/developers and /ai-adoption/teams reflect the new tier distribution immediately. The tier-badge tooltips render the new weights.
* **Week 1–2:** Some confusion. People notice their tier moved. Be prepared with a one-line explanation: "We rebalanced toward \[X\] this quarter to reflect \[Y\]."
* **Week 4+:** The tier distribution stabilizes as people's underlying behavior catches up to the new emphasis. If you weighted Output more heavily, developers will (consciously or not) emphasize shipping more.

### Common mistakes

* **Changing weights to make a specific number look better.** Tier weights aren't there to inflate Power User %. They are there to express what your org cares about. Tuning for a number erodes the metric's value.
* **Setting Adoption near zero in a mature org.** Even mature orgs care that everyone is still using AI consistently. Don't drop Adoption below 0.2.
* **Changing both weights _and_ Maturity Factor at the same time.** Hard to attribute what moved the distribution. Change one at a time, observe for a quarter, then the other if needed.

### Related

* [AI Tier](/gk-insights/ai-adoption-agentic-metrics#ai-tier)
* [Maturity Factor](/gk-insights/ai-adoption-agentic-metrics#maturity-factor)
* [Settings reference](/gk-insights/ai-adoption-settings)

---

## Roll out AI tooling with the Adoption Score

> _You're launching (or accelerating) Claude Code, Codex, or Cursor across your org. This playbook is how to use the dashboard as the operational backbone of that rollout._

### The problem

Most AI tooling rollouts fail not at the install step but at the adoption-depth step. Developers get licenses, install the tool once, use it for a week, and drift away. The dashboard's job is to surface that drift early and give you a structured way to intervene.

This is the playbook for the first 6 months of an active rollout.

### Where to look

Three operational views — each has a different cadence:

| Cadence | View | What you're checking |
| --- | --- | --- |
| **Weekly** | /developers, sorted by Adoption Score ascending | The Emerging cohort. Who's stuck? Who's ramping? |
| **Bi-weekly** | /teams tier mix bars | Team-level momentum. Which teams are pulling ahead, which are stalled? |
| **Monthly** | /executive trend lines | The headline rollout health story. What goes in the leadership update? |

### What to do — the 6-month rollout playbook

#### Month 1 — Set the baseline

**Goal:** Establish where you are _before_ the rollout intervention starts.

1. **Set Maturity Factor to 0.65 in Settings → General.** Lower than the 0.75 default. In the first 3 months, you want the tier ladder to feel achievable. A dev who picks up Claude this month and uses it consistently for 4 weeks should land in Regular, not get stuck in Explorer because the org P90 hasn't shifted yet. Raise back to 0.75 in month 4.
2. **Keep Tier Weights at defaults** (0.5 / 0.2 / 0.3). Adoption-heavy is right for early rollouts.
3. **Take a baseline snapshot.** Note the org's Adoption %, Power User %, AI Adoption %. Write them in your rollout doc. You'll cite these in month 3 and month 6.
4. **Roll out licenses to your launch cohort.** A pilot team or two is usually the right scope. Don't try org-wide on day one.

#### Month 2 — Activate the pilot

**Goal:** Get the pilot cohort from Emerging to Explorer+.

1. **Set up the weekly Emerging review.** Every Friday, open /developers filtered to the pilot teams, sorted by Adoption ascending. Walk the bottom 10. For each: do they need a tooling fix, a pairing session, or a usage-pattern intro?
2. **Run an AI office hour.** 30 minutes weekly. Open invitation. Show patterns. The single highest-leverage intervention in week 2–4.
3. **Pair a Power User with an Emerging developer.** One hour of paired work. Watch the Emerging developer's score climb in the next 2-week window.

#### Month 3 — Pilot review, expand the cohort

**Goal:** Pilot retro + expand to next cohort.

1. **Pull the month 1 baseline.** Compare to today's numbers. Honest read: did the pilot work? Pilot team's Adoption % (target: 60%+ at Explorer+), pilot team's tier mix (target: ≥20% in Regular), pilot team's Output Score trend (target: flat or up — Output usually lags Adoption by 1-2 months).
2. **Document what worked.** Office hours, pairing, internal Slack channel — whatever the pilot proved. Codify it as the rollout SOP.
3. **Expand to the next cohort.** Apply the same playbook to 2–3 more teams.

#### Month 4 — Watch the Power User cohort

**Goal:** Make sure the pilot cohort doesn't regress.

1. **Raise Maturity Factor to 0.75** (the default). The pilot cohort has earned the standard ceiling.
2. **Watch the pilot cohort weekly.** Some developers will regress (the tool stops being novel; they fall back to old habits). Catch the regression in /developers — the trend sparkline shows it before the score does.
3. **Re-engage regressors.** Same playbook: pairing, office hours, audit their toolchain.

#### Month 5 — Org-wide expansion

**Goal:** Make AI tools the default, not the opt-in.

1. **All new developers get licenses on day 1.** Built into onboarding. New hires landing in Emerging after 8 weeks is a process flag.
2. **Watch the Maturing-team cohort.** Some teams will plateau at "everyone's an Explorer, no one's a Power User." That's the "broad but shallow" pattern — start working on the [Agent Autonomy Score](/gk-insights/ai-adoption-agentic-metrics#agent-autonomy-score) by running agentic-pattern workshops.

#### Month 6 — Strategic review

**Goal:** Decide what's next.

1. **Pull the 6-month report.** Org Adoption %, Power User %, Productivity Uplift, AI-Assisted %. Compare to month 1 baseline.
2. **If the rollout worked:** Consider shifting Tier Weights toward Output (e.g. 0.4 / 0.2 / 0.4). You're now in the Maturing phase. (Tier Weights are configured in `app_settings` per-org and editable in Settings → General.)
3. **If parts didn't work:** Identify the laggard teams. They usually share a root cause (one team lead who's skeptical, one repo with bad CI, one process gap). Address structurally rather than per-developer.
4. **Plan the next quarter:** Is the focus broadening adoption (still teams to onboard), deepening adoption (Power User % to grow), or extracting value (Output Score / Productivity Uplift to push)?

### What to expect

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

### Common mistakes

* **Org-wide on day 1.** Spreads adoption too thin to track. Pilot, prove, then expand.
* **Ignoring the trajectory in favor of the snapshot.** Adoption % at 30% is a great number if it was 5% three months ago. It's a bad number if it's been 30% for six months.
* **Treating tier as a performance metric.** Read [How to think about developer scores](/gk-insights/ai-adoption-getting-started#how-to-think-about-developer-scores) before any 1:1 about a developer's tier.
* **Not adjusting Maturity Factor with the rollout.** Default 0.75 is calibrated for active rollout. In month 1, lower it. In month 12, raise it. It's a knob for a reason.
* **Picking AI adoption as the only metric to watch.** Pair it with Cycle Time and CFR. If you ship faster but break more things, you haven't won.

### Related

* [Agent Adoption Score](/gk-insights/ai-adoption-agentic-metrics#agent-adoption-score)
* [Agent Autonomy Score](/gk-insights/ai-adoption-agentic-metrics#agent-autonomy-score)
* [Maturity Factor](/gk-insights/ai-adoption-agentic-metrics#maturity-factor)
* [Playbook — Set tier weights for your org's maturity](#set-tier-weights-for-your-orgs-maturity)
* [For engineering leaders](/gk-insights/ai-adoption-getting-started#for-engineering-leaders)

---

## Investigate a slow cycle time

> _Your team's [Cycle Time](/gk-insights/ai-adoption-flow-metrics#cycle-time) just crept past your tolerance. This playbook walks you from "something's wrong" to "I know which lever to pull" in about 30 minutes._

### The problem

You opened /ai-adoption/teams or /ai-adoption/ai-impact, saw Cycle Time at 5+ days, and felt the familiar sinking feeling. The number is the symptom — the cause is something specific you can intervene on. This playbook is how to find it.

### Where to look

Three views, in order:

#### Step 1 — Phase breakdown (5 min)

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

#### Step 2 — Trend (3 min)

Switch the same chart to **Trends** mode. Pick **By Phase** as the trend type. Each phase now shows as a separate line over time.

**The question is: when did this start?**

* If one phase has been climbing for 4+ weeks, you have a slow-drift problem (process drift, attrition, gradual scope creep). Address it as a process issue.
* If one phase spiked in the last 1–2 weeks, you have an acute problem (someone on PTO, a release embargo, a specific stalled PR). Address it tactically.

#### Step 3 — Drill into the PRs (15 min)

Click a high point on the trend chart. The drill-down table opens below with every PR in that time bucket. Sort by **Cycle Time descending**.

**Look at the top 5–10 PRs.** Patterns to spot:

* **Same author repeatedly?** That developer either has a quality problem causing many cycles, or they are working on hard problems that take longer. Talk to them.
* **Same repo repeatedly?** That repo has a process problem — maybe a slow reviewer, maybe brittle CI, maybe an over-zealous required-reviewer policy.
* **High Review Cycles on the slow PRs?** The PRs are too big or the spec was unclear. Look at PR descriptions and effort scores.
* **All slow PRs opened around the same time?** A reviewer was out or a release embargo hit. Often resolves itself.
* **One huge outlier PR that has been open for weeks?** That PR is the metric. Decide: merge it, split it, or close it.

### What to do

Pick **one** intervention from the most likely cause. Don't try to fix everything at once — you can't tell what worked.

#### If Pickup dominates

* **Establish a weekly review-clearing ritual.** 15 minutes on Monday morning. Walk through the oldest open PRs in your repo or PR queue. Decide: ship, give a deadline, or close each one. This single habit fixes Pickup on most teams within 2–4 weeks.
* **Add a reviewer rotation.** If one developer is doing most of the reviewing, distribute it.
* **Set a Pickup SLO.** "PRs get first review within four business hours." Make it visible.

#### If Review dominates

* **Right-size PRs.** Aim for PRs reviewable in 15 minutes. Check the Effort Score distribution on /ai-adoption/data-explorer: a team consistently at 0.7+ effort medians is over-batching.
* **Improve PR descriptions.** A clear "what / why / how to test" cuts review cycles. AI-assisted PR descriptions are a low-effort win.
* **Calibrate reviewers.** If one reviewer's PRs consistently get 2+ cycles, have the conversation — it is usually a habit, not a quality issue.

#### If Coding dominates

* **Check WIP.** A team with a high Coding phase and high WIP is stalled, not deep-working. Help unstick or descope.
* **Cross-check with developer activity.** /ai-adoption/developers expanded detail shows recent activity. A developer with the high-Coding PR who hasn't committed in 4 days is stuck. Talk to them.
* **Consider AI-assisted scaffolding.** If devs are taking days on the boilerplate-heavy part of new work, AI can compress it.

#### If Deploy dominates

* **Talk to ops or release engineering.** Insights surfaces the symptom; the fix is process-side.
* **Consider continuous delivery.** If your team batches deploys, moving to merge-deploys can cut the Deploy phase to near zero.

### What to expect

A reasonable expectation, having intervened on the dominant phase:

* **Week 1:** Numbers may still be noisy. Don't read week 1 as a verdict.
* **Week 2–3:** The intervention either took or it didn't. Pickup interventions show up fastest — usually 2 weeks before the average comes down. Review interventions take 4–6 weeks because they require behavior change.
* **Week 4:** If you don't see improvement, your hypothesis was wrong. Go back to Step 1 — what does the breakdown look like _now_? Sometimes the underlying problem has shifted.

### Common mistakes

* **Trying to fix all four phases at once.** You won't know what worked. One lever at a time.
* **Reading a single week.** Cycle Time has 2–3 week natural cycles. A bad week could be Christmas-week noise; a bad month is a signal.
* **Blaming individual developers.** The cycle time of a team is mostly the team's process, not its individuals. Process interventions work; finger-pointing doesn't.
* **Focusing on Total Cycle Time when one phase tells the story.** Always look at the breakdown.

### Related metric pages

* [Cycle Time](/gk-insights/ai-adoption-flow-metrics#cycle-time)
* [Review Cycles](/gk-insights/ai-adoption-flow-metrics#review-cycles)
* [WIP](/gk-insights/ai-adoption-flow-metrics#work-in-progress-wip)
* [First-Pass Rate](/gk-insights/ai-adoption-flow-metrics#first-pass-rate)

---

## Interpret a high CFR week

> _Your Change Failure Rate spiked. Before you sound the alarm, walk this playbook to figure out whether it's a real quality problem or an artifact._

### The problem

CFR spikes are alarming and often misleading. A high CFR week can mean any of: an actual quality regression, a backlog of customer bugs got triaged this week, one bad release dominates the average, your sync caught up after a stale period, a reporting threshold change in Jira.

The dashboard's CFR is honest about what it's measuring (customer-reported bugs as a percentage of releases), but the surrounding context matters a lot. This playbook is the 20-minute triage.

### Where to look

#### Step 1 — Look at the trend, not the snapshot (3 min)

Open `/impact`. Look at the **CFR trend chart** (left side, severity-stacked).

* **If the spike is one week and CFR is back to baseline before and after**, you have a localized incident, not a trend.
* **If CFR has been climbing for 3+ weeks**, you have a real quality drift.
* **If CFR has been flat then suddenly stepped up**, you have a regression at a specific point.

Note which pattern you're seeing.

#### Step 2 — Check the severity breakdown (3 min)

Same chart, look at the severity stack. The headline CFR % includes all severities, but **High and Critical bugs are what actually hurt**.

* If the spike is in Low / Medium severity, you may have a triage influx (someone categorized old bugs this week). Less concerning.
* If the spike is in High / Critical, treat it as urgent.

The **CFR KPI card** (left of the trend) shows High & Critical specifically — that's the number that matters.

#### Step 3 — Pull the customer bugs themselves (10 min)

Scroll down to the drill-down table below the CFR charts. Sort by **Created date descending**. Look at the bugs that drove the spike.

Patterns to spot:

* **Same repo repeatedly?** That repo had a bad release. Investigate the release, not the org.
* **Same root cause repeatedly?** A specific bug class (e.g. "null pointer in date parser") shipped multiple times. Process / test coverage issue.
* **All bugs filed by one customer?** May be one customer's escalated reports rather than a real quality spike. Look at the cluster.
* **Bugs created this week but referencing changes from weeks ago?** Reporting lag. Your quality wasn't suddenly worse — customers just noticed something old.
* **A backlog of bugs that got categorized this week?** Look at the create-dates carefully. If many bugs were filed weeks ago but only categorized as "Customer Bug = Yes" today, the spike is bookkeeping, not quality.

#### Step 4 — Look at AI Tier breakdown (5 min)

This is where the dashboard does something unique. Click into `/ai-impact` and look at **CFR by AI Tier**.

The question: **does CFR scale with AI tier?**

* If Power Users have _lower_ CFR than Emerging devs, AI adoption is a quality boost. Good news.
* If Power Users have _higher_ CFR, AI is enabling faster shipping at a quality cost. Investigate AI-assisted work specifically.
* If CFR is uncorrelated with tier, the spike isn't an AI issue — it's a general quality issue.

### What to do

Pick the intervention that fits the pattern you found.

#### Pattern: One localized incident

A single spike that's already resolved. Often a bad release that's been hot-patched.

**Action:** Run the team's standard post-mortem on the responsible release. CFR will return to baseline naturally. No structural change needed unless the post-mortem reveals a class of issue worth fixing.

#### Pattern: 3+ week climb

Genuine quality drift.

**Action:** Pause non-critical feature work for the affected team for one sprint. Sample the recent bug reports — look for a common root cause. Strengthen pre-merge review on the affected repo(s). Consider mandatory reviewers if you've been relaxed about review. Look at [First-Pass Rate](/gk-insights/ai-adoption-flow-metrics#first-pass-rate) over the same window. A First-Pass climbing alongside CFR is the rubber-stamping pattern — your reviewers aren't catching things.

#### Pattern: Suddenly stepped up

Something specific changed. Possibilities: new release process introduced a gap, a senior developer left and quality oversight dropped, a new team or repo joined the dataset, a Jira workflow change started catching previously-uncategorized bugs.

**Action:** Find the discontinuity. Cross-reference with your team's known events (releases, hires, departures, process changes). The step is almost always a specific event.

#### Pattern: AI Tier scaling problem

Power Users have higher CFR than Emerging.

**Action:** This is the most interesting and rarest case. AI is helping with speed but at a quality cost. Look at: Are Power Users shipping bigger PRs because AI made it easy? Check Effort Score distribution. Is the review process catching AI-generated bugs? Check First-Pass Rate among Power Users. Is there a specific class of bug that's AI-correlated? (E.g. wrong API usage, hallucinated function calls.) Drill into the bug list.

**Don't conclude "AI is bad" yet** — investigate. The fix is usually "review AI-assisted code more carefully," not "stop using AI."

### What to expect

A reasonable expectation after intervening on a real CFR climb:

* **Week 1 after intervention:** Numbers still noisy. The bugs reported this week are from changes that shipped weeks ago. Don't read week 1.
* **Week 2–4:** CFR should start trending down. If you intervened on review process, expect 3–4 weeks before the data reflects it.
* **Week 6–8:** Verdict. If CFR is back to baseline or below, the intervention worked. If not, your hypothesis was wrong — revisit step 3.

### Common mistakes

* **Reading one week as a trend.** CFR has natural variance week to week. A single spike doesn't constitute drift.
* **Slowing the team without diagnosing.** "Pause everything" is sometimes the right call, but usually the right call is "fix the specific thing." Diagnose first.
* **Blaming AI without checking the tier breakdown.** If CFR is high but uncorrelated with AI tier, it's a general quality issue, not an AI issue.
* **Ignoring severity.** A 20% CFR week of all-Low-severity bugs is much less urgent than an 8% CFR week of Critical bugs.

### Related metric pages

* [Change Failure Rate (CFR)](/gk-insights/ai-adoption-dora-metrics#change-failure-rate-cfr)
* [MTTR](/gk-insights/ai-adoption-dora-metrics#mean-time-to-recovery-mttr)
* [First-Pass Rate](/gk-insights/ai-adoption-flow-metrics#first-pass-rate)
* [AI Tier](/gk-insights/ai-adoption-agentic-metrics#ai-tier)
