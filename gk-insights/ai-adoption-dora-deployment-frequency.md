---
title: "Deployment Frequency"
description: "How GitKraken Insights calculates and displays Deployment Frequency."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: gk-insights
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for Deployment Frequency"
    card_icon: shield-check
    nav_category: metrics
    nav_label: "Deployment Frequency"
    nav_order: 41
    nav_parent: ai-adoption-dora-metrics
    page_type: content
---
<kbd>Last updated: November 2026</kbd>

## Deployment Frequency

> _How often your team ships to production. One of the two DORA velocity metrics._

**Family:** DORA & Quality · **Cadence:** Per window, per team or org · **Where it appears:** /insights/board-metrics, /insights/executive, /insights/ai-impact

### At a glance

Deployment Frequency is "how often does new code reach production?" It is the simplest of the DORA metrics and the one most teams already track informally. The dashboard counts deployments as _release events_ per window — whatever [release signal](#release-signals-by-provider) is set for each repo (a published release, a Git tag, a CI/pipeline run, or a release-branch merge), plus any releases pushed with the Manual Releases API.

The metric is most useful as a trend line and as a cohort comparison (team A vs. team B), not as an absolute target. "Three deploys per week" does not mean much without context.

### Formula

```
Deployment Frequency = count(releases in window) / time-unit

  where a release = the configured release signal for the repo
                    (published release, Git tag, CI/pipeline run,
                    or release-branch merge)
```

Expressed as deploys per day, week, or month depending on cadence and team activity.

### How GitKraken Insights calculates it

**Source.** The backend reads release events from `analytics.github_releases` (the canonical metric name is `release_count`). A repo must have release detection configured for its deployments to appear; without it the metric shows an empty state for that repo.

**What counts as a release.** A tagged release, a GitHub Release, any other release event Insights captures for the repo, or a release pushed to Insights with the Manual Releases API. Pre-releases are excluded.

**Aggregation.** For a team, Insights counts every release credited to that team. A release can be credited to more than one team, so per-team counts can add up to more than the organization total. Repository counts do not overlap. For an org, Insights counts each release across all repos in the filter once.

### Release signals by provider

Release detection is set per repository in **Settings → Releases**. Leave it on **Auto-detect** or pick a signal:

| Provider | Signals |
| --- | --- |
| **GitHub** | GitHub releases · Git tags · Workflow file (e.g. `cd.yaml`) · Release branch merge |
| **Azure DevOps** | Git tags · Azure pipeline (a pipeline definition) · Release branch merge |
| **GitLab** | GitLab releases · Git tags · GitLab job (the job that deploys) · GitLab pipeline (a pipeline name) · Release branch merge |
| **Bitbucket Cloud** | Git tags · Bitbucket Pipelines · Release branch merge |
| **Bitbucket Data Center** | Git tags · Release branch merge |

- For **Workflow file**, **Azure pipeline**, **GitLab job**, and **GitLab pipeline**, pick one of the repository's CI workflows, jobs, or pipelines from the list, or type a name. A GitLab pipeline only has a name when `.gitlab-ci.yml` sets `workflow:name`. If yours does not, choose **GitLab job** and enter the name of the job that deploys.
- **Git tags** can be narrowed with an optional tag pattern. **Release branch merge** needs a base branch and is set one repo at a time.
- **Branch pattern (regex)** optionally narrows **Release branch merge** to merges whose source branch matches a case-sensitive regular expression, not a wildcard. The pattern matches anywhere in the branch name unless you anchor it with `^` and `$`, for example `^release/[0-9]+\.[0-9]+\.[0-9]+$`. If a pattern looks like a wildcard or is not anchored, the field warns you and offers a corrected pattern.
- After you change release detection, new matches are counted on the next sync.
- **Skip** turns off release detection for a repo — its deployments do not count toward Deployment Frequency, Lead Time, or CFR.

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

Read the trend, not the band. A team that moved from Medium to High in six months is a success story even if it is not Elite.

### Where it appears

* **/insights/board-metrics** — primary surface for DORA. The Deployment Frequency tile and chart, against the previous period. Select the tile's value to list every release in the period. To list one week's releases from the chart, hide **Previous Period** in the legend, then select that week's bar.
* **/insights/executive** — the Deploy Frequency card, by team or repository as bars, or as a trend line. Select the card's value to list every release in the period, or select a bar or a point on the overall trend line to see the releases behind it. While the previous period is showing, hide **Previous Period** first.
* **/insights/ai-impact** — can be broken down by repo, team, or AI Tier.
* **Release list** — the drill-down shows each release's **Repo**, **Triggering rule** (the release type, with the source and workflow name that detected it), **Deployed at** time, and **Tag / Branch**.

### Settings that affect it

* **Release detection** — repo-level configuration in **Settings → Releases**. Your admin sets a [release signal](#release-signals-by-provider) (or leaves Auto-detect) for each repo you want to track; the options depend on the git provider.
* **[Manual Releases API](/gk-insights/ai-adoption-manual-releases-api)** — for deployments Insights cannot detect, your admin can push releases directly with an API key. Manual releases count toward Deployment Frequency alongside detected ones.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Lead Time for Changes](/gk-insights/ai-adoption-dora-lead-time) | The other DORA velocity metric. Velocity = "fast and often" needs both. |
| [CFR](/gk-insights/ai-adoption-dora-change-failure-rate) | Stability counterweight. High Deployment Frequency with low CFR is the goal. |
| [Throughput](/gk-insights/ai-adoption-output-throughput) | The merged-PR count metric. Often correlated with Deployment Frequency but measures merges, not releases. |

### How to improve it

* **Smaller PRs, more often.** The single most effective intervention. Cuts Cycle Time, raises Deployment Frequency, and usually drops CFR all at once.
* **Continuous delivery, not weekly releases.** If your team batches deploys to a weekly cadence, you're capping Deployment Frequency artificially. Moving to continuous delivery (merge → deploy automatically) raises Deployment Frequency 5–10×.
* **Automate the deploy.** Manual deploy gates are the most common Deployment Frequency limiter. Automated CI/CD with merge-gated tests is the standard answer.
* **Embed AI into the small-PR workflow.** AI-assisted shipping for routine work (lint fixes, docs, test additions) frees reviewer bandwidth for substantive PRs and raises overall flow.

### Limitations and gotchas

* **Counts all releases equally.** A 0.1-effort dependency-bump release and a 0.9-effort feature release both count as 1. The count tells you flow cadence, not impact — pair with Output Score.
* **Empty if releases are not configured.** A repo without release detection contributes nothing to Deployment Frequency. The fix is configuration, not data.
* **Release cadence reflects your release workflow.** Some teams tag releases monthly even though they deploy daily, and the metric then under-counts the real deploy cadence. The metric reflects what the data says is happening, not what you informally think of as a "deploy."
* **DORA bands do not mean "must reach Elite."** Elite requires architectural and process investments that may not be cost-justified for your team. High is a strong target for most product engineering orgs.

### FAQ

**Q: We deploy on merge automatically. Will every merge show as a release?**
A: Only if each deploy also produces a release artifact Insights can read. If your pipeline auto-tags every deploy, yes. If it deploys without tagging, no — work with your admin to wire up release detection, or to push each deploy to Insights with the [Manual Releases API](/gk-insights/ai-adoption-manual-releases-api).

**Q: A revert is a deploy. Does that count?**
A: Yes — the revert ships and is captured as a release event. Some teams find this over-counts; the metric keeps it because reverts genuinely are deploys (they go through the same pipeline).

**Q: How do I see deploys per team?**
A: Apply a team filter on /insights/board-metrics, or set the team scope on /insights/executive.
