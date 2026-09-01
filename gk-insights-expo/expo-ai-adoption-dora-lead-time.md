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

## Lead Time for Changes

> _Hours from a developer's first commit to the release that delivered the change to production. The other DORA velocity metric._

**Family:** DORA & Quality · **Cadence:** Per PR, aggregated for views · **Where it appears:** /ai-adoption/board-metrics, /ai-adoption/ai-impact

### At a glance

Lead Time for Changes is "how long does it take a change to go from first keystroke to live in production?" It is the end-to-end sibling of [Cycle Time](/insights-expo/expo-ai-adoption-flow-metrics#cycle-time). Cycle Time stops at merge; Lead Time keeps counting until the change is actually in production.

It is the DORA metric most often confused with Cycle Time. The difference matters: shipping fast (low Cycle Time) without actually deploying (high Lead Time) is the classic "engineering ships, ops sits on it" anti-pattern.

### Formula

```
Lead Time (hours) = released_at − first_commit_at  (clamped at 0)

  released_at = timestamp of the earliest non-prerelease release
                whose released_at is at or after the PR's merged_at,
                on the same repo (with branch- and SHA-aware matching)
```

### How GitKraken Insights calculates it

**The PR-to-release join.** For each merged PR, the backend uses a LATERAL nearest-release lookup: it finds the earliest non-prerelease release on the same repo whose `released_at` is at or after the PR's `merged_at`. Branch- and SHA-aware matching prefers the release whose target commit matches the PR's merge SHA; it falls back to base-branch matching when SHA data isn't available.

**Lead Time per PR.** Once the delivering release is identified, Lead Time is the hours between the PR's first commit and the release's `released_at`, clamped at 0.

**Aggregation.** Mean across PRs that delivered in the window. Drafts excluded.

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

For most product engineering teams, High is the target. Elite requires fully-automated CI/CD with no human deploy gates.

### Where it appears

* **/ai-adoption/board-metrics** — Lead Time as one of the four DORA cards.
* **/ai-adoption/ai-impact** — implicit in the Cycle Time breakdown when a delivering release is present.

### Settings that affect it

* **Release detection** (repo-level). Required for Lead Time to compute. Without it the metric shows an empty state.
* **JIRA integration** — if you enable "Include JIRA start time in Coding phase," Lead Time extends backward to include the time from the issue's in-progress transition.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Cycle Time](/insights-expo/expo-ai-adoption-flow-metrics#cycle-time) | The first-commit-to-merge portion. Lead Time extends to the delivering release. |
| [Deployment Frequency](#deployment-frequency) | The other DORA velocity metric. |

### How to improve it

* **Eliminate the deploy gate.** If your team has a manual release step between merge and deploy, automate it. Lead Time will halve or better immediately.
* **Smaller PRs.** Same root-cause fix as Cycle Time. Less to test, less to coordinate, less to QA-block.
* **Decouple deploy from release.** Feature flags let you deploy code without releasing the feature. Lead Time becomes deploy time, not release time.

### Limitations and gotchas

* **Release detection required.** Repos without release events contribute nothing to Lead Time. The fix is configuration, not data.
* **Monorepo over-attribution.** When one repo holds multiple services and one service's release ships, that release is attributed to PRs touching _any_ service in the repo. Median aggregation mitigates this somewhat; per-service attribution would require commit-in-release data we don't have today.
* **Release branch and hotfix attribution.** A release cut from a commit older than the merge can attach to the PR retroactively. Documented behavior, not a bug.
* **Long-tail outliers skew the mean.** Same caveat as Cycle Time — a single PR that sat for a quarter can drag the average noticeably.

### FAQ

**Q: Is Lead Time the same as the time from a JIRA issue being opened to a deploy?**
A: Not unless you enable the JIRA-extended Coding phase. By default, Lead Time starts at the first commit, not at issue creation.

**Q: We deploy on merge. Is Lead Time useless for us?**
A: It will be close to Cycle Time plus a small deploy-pipeline interval. The two are still meaningfully different — Lead Time captures any non-zero deploy time that Cycle Time misses.
