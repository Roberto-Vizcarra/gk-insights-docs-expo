---
title: "Lead Time for Changes"
description: "How GitKraken Insights calculates and displays Lead Time for Changes."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for Lead Time for Changes"
    card_icon: shield-check
    nav_category: metrics
    nav_label: "Lead Time for Changes"
    nav_order: 42
    nav_parent: expo-ai-adoption-dora-metrics
    page_type: content
---
<kbd>Last updated: November 2026</kbd>

## Lead Time for Changes

> _Hours from when work on a change starts to the release that delivered it to production. The other DORA velocity metric._

**Family:** DORA & Quality · **Cadence:** Per PR, aggregated for views · **Where it appears:** /insights/board-metrics, /insights/ai-impact

### At a glance

Lead Time for Changes is "how long does it take a change to go from first keystroke to live in production?" It is the end-to-end sibling of [Cycle Time](/insights-expo/expo-ai-adoption-flow-cycle-time). Cycle Time stops at merge; Lead Time keeps counting until the change is actually in production.

It is the DORA metric most often confused with Cycle Time. The difference matters: shipping fast (low Cycle Time) without actually deploying (high Lead Time) is the classic "engineering ships, ops sits on it" anti-pattern.

### Formula

```
Lead Time (hours) = release time − work start  (clamped at 0)

  release    = the earliest non-prerelease release on the same repo
               at or after the PR's merge (branch- and SHA-aware matching)
  work start = set by the Cycle Time Start Signal setting
               (the first commit while the setting is not set)
```

### How GitKraken Insights calculates it

**The PR-to-release match.** For each merged PR, GitKraken Insights finds the earliest non-prerelease release on the same repo at or after the PR's merge. Branch- and SHA-aware matching prefers the release whose target commit matches the PR's merge SHA; it falls back to base-branch matching when SHA data is not available.

**Lead Time per PR.** Once the delivering release is identified, Lead Time is the hours between work start and the release, clamped at 0. The [Cycle Time Start Signal](/insights-expo/expo-ai-adoption-settings#cycle-time-start-signal) setting decides when work starts, for Lead Time and Cycle Time alike. While the setting is not set, Lead Time starts at the PR's first commit.

**Issues instead of PRs.** When an admin sets [Cycle Time Unit of Work](/insights-expo/expo-ai-adoption-settings#cycle-time-unit-of-work) to **Issue**, the headline Lead Time times each resolved issue across its linked PRs, ending when the last of those PRs is released. Breakdowns and drill-downs still use pull requests.

**Aggregation.** Median across merged PRs delivered in the window, leaving out drafts and PRs by excluded developers (most bot accounts are excluded automatically).

**Release detection required.** If a repo has no release events, its PRs have no delivering release and therefore no Lead Time. The metric shows an empty state for those repos. Lead Time is not silently substituted with Cycle Time.

### Why it matters

Lead Time is the truer end-to-end measure of "how fast can my team get a fix or a feature to customers?" Cycle Time is what your engineering team feels (PR → merge); Lead Time is what your customers feel (idea → in their hands).

A team that is fast on Cycle Time but slow on Lead Time usually has one of three blockers:

1. **Release embargo.** Engineering merges continuously; ops releases weekly.
2. **Staging gate.** PRs sit in staging waiting for QA sign-off.
3. **Compliance review.** Changes need to pass a security or compliance review before deployment.

Each of those has its own fix. Lead Time surfaces which one is your dominant constraint.

### How to read it

DORA bands for Lead Time:

| Band | Pattern |
| --- | --- |
| **Elite** | Less than 1 day |
| **High** | 1 day to 1 week |
| **Medium** | 1 week to 1 month |
| **Low** | More than 1 month |

For most product engineering teams, High is the target. Elite requires fully automated CI/CD with no human deploy gates.

### Where it appears

* **/insights/board-metrics** — the Change Lead Time tile and chart. Select a point on the chart to see the changes behind that period's median, with each change's title and PR, repo, first commit, merge and release dates, and lead time. A change merged in the period but not yet released carries a **Not released** badge and is not part of the median. When Cycle Time Unit of Work is set to **Issue**, a point opens the releases delivered in that period instead.
* **/insights/ai-impact** — implicit in the Cycle Time breakdown when a delivering release is present.

### Settings that affect it

* **Release detection** (repo-level). Required for Lead Time to compute. Without it the metric shows an empty state.
* [**Cycle Time Start Signal**](/insights-expo/expo-ai-adoption-settings#cycle-time-start-signal) (Settings → General) — when the clock starts for Lead Time and Cycle Time: In progress, First commit, or Created. While it is not set, Lead Time starts at the first commit.
* [**Cycle Time Unit of Work**](/insights-expo/expo-ai-adoption-settings#cycle-time-unit-of-work) (Settings → General) — whether the headline Lead Time times each pull request (the default) or each resolved issue.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Cycle Time](/insights-expo/expo-ai-adoption-flow-cycle-time) | The work-start-to-merge portion. Lead Time extends to the delivering release. |
| [Deployment Frequency](/insights-expo/expo-ai-adoption-dora-deployment-frequency) | The other DORA velocity metric. |

### How to improve it

* **Eliminate the deploy gate.** If your team has a manual release step between merge and deploy, automate it. Lead Time will halve or better immediately.
* **Smaller PRs.** Same root-cause fix as Cycle Time. Less to test, less to coordinate, less to QA-block.
* **Decouple deploy from release.** Feature flags let you deploy code without releasing the feature. Lead Time becomes deploy time, not release time.

### Limitations and gotchas

* **Release detection required.** Repos without release events contribute nothing to Lead Time. The fix is configuration, not data.
* **Monorepo over-attribution.** When one repo holds multiple services and one service's release ships, that release is attributed to PRs touching _any_ service in the repo. Median aggregation mitigates this somewhat; per-service attribution would require commit-in-release data that Insights does not have.
* **Release branch and hotfix attribution.** A release cut from a commit older than the merge can attach to the PR retroactively. This is expected behavior, not a bug.
* **Recent periods can read low.** Changes that are slow to release have not been delivered yet, so the most recent periods may understate the median until those releases happen.

### FAQ

**Q: Is Lead Time the same as the time from a Jira issue being opened to a deploy?**
A: Not by default. While the Cycle Time Start Signal setting is not set, Lead Time starts at the first commit. An admin can set the start signal to **In progress** (the earlier of the first commit and the linked issue moving to in progress) or **Created** (when the PR or issue was created).

**Q: We deploy on merge. Is Lead Time useless for us?**
A: It is close to Cycle Time plus a small deploy-pipeline interval. The two are still meaningfully different — Lead Time captures any non-zero deploy time that Cycle Time misses.
