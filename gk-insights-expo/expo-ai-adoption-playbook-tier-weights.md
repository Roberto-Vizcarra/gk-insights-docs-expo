---
title: "Set Tier Weights for Your Org’s Maturity"
description: How to calibrate Adoption, Output, and Agentic weights as your organization moves through AI maturity phases.
product: GitKraken Insights
content_type: how-to
audience: engineering-leader
plan_required: GitKraken Insights
status: GA
page_type: content
nav_category: playbooks
nav_order: 10
nav_label: Set Tier Weights
card_icon: adjustments-horizontal
card_color: amber
card_description: Calibrate weights as your org matures
taxonomy:
    category: insights-expo
---
<kbd>Last updated: September 2026</kbd>

> _The default Tier Weights (0.5 Adoption / 0.2 Agentic / 0.3 Output) are tuned for orgs in active AI rollout. This playbook helps you decide whether to keep them or change them._

**How to change Tier Weights.** Tier Weights are stored per-org in `analytics.app_settings` as the keys `tier_weight_adoption`, `tier_weight_agentic`, and `tier_weight_output`. The Settings → General form exposes them as user-editable fields. Set the new values there; the change takes effect immediately for the current window.

## The problem

You are setting up Insights (or you are six months in and reviewing your config) and you are wondering whether the default Tier Weights are right for your org. The defaults assume "we are rolling out AI and we care that everyone is trying it." That is right for most orgs in the first year. It is wrong for orgs further along.

This playbook helps you pick a weighting that reflects where your org actually is in its AI journey.

## Where to look

### Step 1 — Diagnose your current state (10 min)

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

## What to do

### If you are in Early or Active rollout

**Keep the defaults: 0.5 / 0.2 / 0.3.**

The defaults emphasize Adoption (50%), which is right for early rollouts. The signal you want from the dashboard is "are people picking up the tools?" — and Adoption is what answers that. Output matters but follows adoption with a 4–12 week lag, so weighting it heavily early biases the tier composite against the right early signal.

If anything, consider lowering the **Maturity Factor** to 0.6 or 0.65 (this _is_ settable in Settings → General) to leave more headroom for developers to feel like they are progressing. Don't change tier weights yet.

### If you are Maturing

**Consider setting 0.4 / 0.2 / 0.4 in Settings → General.**

You have established broad adoption. The next question is whether adoption is converting to output. Balancing Adoption and Output equally surfaces the developers who are doing both. The 0.2 Agentic stays unchanged — agentic depth is still emerging.

Also consider raising the **Maturity Factor** to 0.85 (settable in Settings → General) to reflect that your org's P90 should now mean something close to "Power User territory."

### If you are Mature

**Consider setting 0.3 / 0.2 / 0.5 in Settings → General.**

In a mature org, adoption is the baseline assumption. The differentiator between developers is output and depth. Weighting Output most heavily emphasizes "who is actually delivering with this." The 0.2 Agentic stays unchanged — agentic adoption is harder to push deep and most orgs cap out around 0.3 agentic mean.

Maturity Factor at 0.95–1.00 reflects that you have earned the top of the scale.

### If you have a specific goal

| Goal | Suggested weights |
| --- | --- |
| "Drive AI adoption — this is a strategic priority" | 0.7 / 0.2 / 0.1 |
| "Encourage autonomous AI workflows specifically" | 0.3 / 0.5 / 0.2 |
| "Reward shippers" | 0.3 / 0.1 / 0.6 |
| "Balanced" | 0.4 / 0.3 / 0.3 |

Don't optimize for one of these unless you have actually decided that is the org's strategic focus this quarter. Most orgs benefit from the defaults plus minor tweaks.

## Things to know before you change weights

1. **Weights are renormalized to sum to 1.0 on every read.** So setting 1.0 / 0.5 / 0.5 yields effective 0.5 / 0.25 / 0.25. Most people find it clearer to choose values that already sum to 1.
2. **All three weights must be strictly positive.** Zero or negative values are rejected; the default for that key is used instead. There is never a "100% output, 0% everything else" state.
3. **Changes affect every developer immediately.** No retroactive recomputation of historical snapshots, but the _current_ window updates as soon as the keys are set. Plan an announcement.
4. **The tier thresholds (25 / 55 / 80) don't move.** Changing weights _will_ re-tier some developers. Some will move up, some down.

## How to roll out a weight change

A practical sequence:

1. **Note the current weights.** Hover any tier badge on /ai-adoption/developers — the tier-score tooltip shows the live weights for that developer's composite. Write the values down.
2. **Set the three new values** in Settings → General. Note the reason for the change for your own records ("we are Maturing and want to balance Adoption and Output equally").
3. **Sanity check the new distribution.** After saving the change, open /ai-adoption/developers and verify the tier distribution looks roughly as expected. If half your Regulars dropped to Explorer, the change was bigger than intended.
4. **Announce the change** to your team via the channel you would use for any process change. Frame it as "we are shifting emphasis toward \[X\] to reflect \[Y\]." Reassure people that tier movement is by design, not a personal regression.
5. **Don't change them again for at least a quarter.** Tier weights are strategic settings, not tactical knobs. Frequent changes erode trust in the dashboard.

## What to expect

After a weight change:

* **Day 1:** The change is live. /ai-adoption/developers and /ai-adoption/teams reflect the new tier distribution immediately. The tier-badge tooltips render the new weights.
* **Week 1–2:** Some confusion. People notice their tier moved. Be prepared with a one-line explanation: "We rebalanced toward \[X\] this quarter to reflect \[Y\]."
* **Week 4+:** The tier distribution stabilizes as people's underlying behavior catches up to the new emphasis. If you weighted Output more heavily, developers will (consciously or not) emphasize shipping more.

## Common mistakes

* **Changing weights to make a specific number look better.** Tier weights aren't there to inflate Power User %. They are there to express what your org cares about. Tuning for a number erodes the metric's value.
* **Setting Adoption near zero in a mature org.** Even mature orgs care that everyone is still using AI consistently. Don't drop Adoption below 0.2.
* **Changing both weights _and_ Maturity Factor at the same time.** Hard to attribute what moved the distribution. Change one at a time, observe for a quarter, then the other if needed.

## Related pages

* [AI Tier](/gk-insights/ai-adoption-agentic-metrics#ai-tier)
* [Maturity Factor](/gk-insights/ai-adoption-agentic-metrics#maturity-factor)
* [Settings reference](/gk-insights/ai-adoption-settings)
