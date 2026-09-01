---
title: "Deployment Frequency"
description: "How GitKraken Insights calculates and displays Deployment Frequency."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for Deployment Frequency"
    card_icon: shield-check
    nav_category: metrics
    nav_label: "Deployment Frequency"
    nav_order: 41
    nav_parent: expo-ai-adoption-dora-metrics
    page_type: content
---

## Deployment Frequency

> _How often your team ships to production. One of the two DORA velocity metrics._

**Family:** DORA & Quality · **Cadence:** Per window, per team or org · **Where it appears:** /ai-adoption/board-metrics, /ai-adoption/executive, /ai-adoption/ai-impact

### At a glance

Deployment Frequency is "how often does new code reach production?" It is the simplest of the DORA metrics and the one most teams already track informally. The dashboard counts deployments as _tagged releases_ (or configured release events) per window.

The metric is most useful as a trend line and as a cohort comparison (team A vs. team B), not as an absolute target. "Three deploys per week" doesn't mean much without context.

### Formula

```
Deployment Frequency = count(releases in window) / time-unit

  where a release = a tagged release, GitHub Release, or
                    configured release event on a tracked repo
```

Expressed as deploys per day, week, or month depending on cadence and team activity.

### How GitKraken Insights calculates it

**Source.** The backend reads release events from `analytics.github_releases` (the canonical metric name is `release_count`). A repo must have release detection configured for its deployments to appear; without it the metric shows an empty state for that repo.

**What counts as a release.** A tagged release, a GitHub Release, any other release event the writer captures for the repo, or a release pushed to Insights with the manual releases API. Pre-releases are excluded.

**Aggregation.** For a team, we count all releases across the team's repos. For an org, all releases across all repos in the filter.

### Why it matters

Industry research (DORA's annual State of DevOps report) consistently shows that **higher deployment frequency correlates with better outcomes** — including lower CFR and faster MTTR. Counter-intuitively: teams that deploy more often have fewer outages, because each deploy is smaller and easier to debug.

For AI adoption analysis, Deployment Frequency is the headline velocity metric. AI tools enable smaller, more frequent PRs; teams successfully integrating AI typically see Deployment Frequency climb 30–80% within 6 months of rollout.

### How to read it

DORA's reference bands for Deployment Frequency:

| Band | Pattern |
| --- | --- |
| **Elite** | Multiple deploys per day |
| **High** | Between once per day and once per week |
| **Medium** | Between once per week and once per month |
| **Low** | Less than once per month |

These bands are industry benchmarks, not GitKraken Insights defaults. They translate roughly to: mature SaaS / web teams usually run High or Elite; enterprise teams with release embargoes usually run Medium; regulated or compliance-heavy teams (finance, healthcare) often run Low by design.

Read the trend, not the band. A team that moved from Medium to High in six months is a success story even if they're not Elite.

### Where it appears

* **/ai-adoption/board-metrics** — primary surface for DORA. Deployment Frequency is one of the four headline cards.
* **/ai-adoption/executive** — Deployment Frequency as a hero trend line.
* **/ai-adoption/ai-impact** — can be broken down by repo, team, or AI Tier.

### Settings that affect it

* **Release detection** — repo-level configuration. Your admin needs to wire up tagged releases or a configured release event for each repo you want to track.
* **[Manual Releases API](/insights-expo/expo-ai-adoption-manual-releases-api)** — for deployments Insights can't detect, your admin can push releases directly with an API key. Manual releases count toward Deployment Frequency alongside detected ones.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Lead Time for Changes](#lead-time-for-changes) | The other DORA velocity metric. Velocity = "fast and often" needs both. |
| [CFR](#change-failure-rate-cfr) | Stability counterweight. High Deployment Frequency with low CFR is the goal. |
| [Throughput](/insights-expo/expo-ai-adoption-output-metrics#throughput) | The merged-PR count metric. Often correlated with Deployment Frequency but measures merges, not releases. |

### How to improve it

* **Smaller PRs, more often.** The single most effective intervention. Cuts Cycle Time, raises Deployment Frequency, and usually drops CFR all at once.
* **Continuous delivery, not weekly releases.** If your team batches deploys to a weekly cadence, you're capping Deployment Frequency artificially. Moving to continuous delivery (merge → deploy automatically) raises Deployment Frequency 5–10×.
* **Automate the deploy.** Manual deploy gates are the most common Deployment Frequency limiter. Automated CI/CD with merge-gated tests is the standard answer.
* **Embed AI into the small-PR workflow.** AI-assisted shipping for routine work (lint fixes, docs, test additions) frees reviewer bandwidth for substantive PRs and raises overall flow.

### Limitations and gotchas

* **Counts all releases equally.** A 0.1-effort dependency-bump release and a 0.9-effort feature release both count as 1. The count tells you flow cadence, not impact — pair with Output Score.
* **Empty if releases aren't configured.** A repo without release detection contributes nothing to Deployment Frequency. The fix is configuration, not data.
* **Release cadence reflects your release workflow.** Some teams tag releases monthly even though they deploy daily — that will under-count the real deploy cadence. The metric reflects what the data says is happening, not what you informally think of as a "deploy."
* **DORA bands don't mean "must reach Elite."** Elite requires architectural and process investments that may not be cost-justified for your team. High is a strong target for most product engineering orgs.

### FAQ

**Q: We deploy on merge automatically. Will every merge show as a release?**
A: Only if each deploy also produces a release artifact the backend can read. If your pipeline auto-tags every deploy, yes. If it deploys without tagging, no — work with your admin to wire up release detection, or to push each deploy to Insights with the [Manual Releases API](/insights-expo/expo-ai-adoption-manual-releases-api).

**Q: A revert is a deploy. Does that count?**
A: Yes — the revert ships and is captured as a release event. Some teams find this over-counts; the metric keeps it because reverts genuinely are deploys (they go through the same pipeline).

**Q: How do I see deploys per team?**
A: /ai-adoption/board-metrics with team filter applied, or /ai-adoption/executive with team scope set.
