---
title: DORA & Quality Metrics in the AI Adoption Dashboard
description: Learn about DORA & Quality metrics in the GitKraken Insights AI Adoption dashboard, including Deployment Frequency, Lead Time for Changes, Change Failure Rate (CFR), and Mean Time to Recovery (MTTR).
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
integrations: [GitHub, GitHub Enterprise Server, GitLab, Bitbucket, Azure DevOps, Azure DevOps Server, Jira Cloud]
status: GA
taxonomy:
    category: gk-insights
---
<kbd>Last updated: June 2026</kbd>

> **Note:** This page covers the **DORA & Quality** family of the AI Adoption dashboard. For DORA metrics on the classic Insights dashboards — Deploy Frequency, Change Lead Time, Mean Time to Repair/Recover, and Defect Rate, which use different definitions and calculation logic — see [DORA Metrics](/gk-insights/gk-insights-dora-metrics).

This family covers the industry-standard four DORA metrics — Deployment Frequency, Lead Time for Changes, Change Failure Rate, and Mean Time to Recovery — plus the customer-bug volume that powers CFR and MTTR.

DORA emerged from the DevOps Research & Assessment program at Google and is the common language for engineering delivery health at the leadership level. When you present to a CTO, a board, or an external stakeholder, these are the metrics they already know.

---

## The four DORA metrics

| Metric | Bucket | What it captures |
| --- | --- | --- |
| [**Deployment Frequency**](#deployment-frequency) | Velocity | How often you ship to production. |
| [**Lead Time for Changes**](#lead-time-for-changes) | Velocity | How long it takes from a developer's first commit to that change reaching production. |
| [**Change Failure Rate (CFR)**](#change-failure-rate-cfr) | Stability | What percent of releases produce a customer-reported bug. |
| [**Mean Time to Recovery (MTTR)**](#mean-time-to-recovery-mttr) | Stability | How long it takes to recover from a production incident. |

DORA splits these into two velocity metrics and two stability metrics. A high-performing team scores well on **both** sides — fast and reliable. A team that is fast but unreliable, or reliable but slow, has unfinished work to do.

---

## How DORA in this dashboard differs from the original framing

A few practical adaptations worth knowing:

* **Deployment Frequency** counts tagged releases (or configured release events) per window. You need release detection configured for each repo — without it, the metric shows an empty state.
* **Lead Time** measures from _first commit_ to _delivering release_. It does not start at issue creation. If you enable JIRA integration with the "Include JIRA start time" option, Lead Time extends backward to include time from the issue's in-progress transition.
* **CFR** is the rate of _releases that produced a customer-reported bug_, not the rate of all bugs. The signal is your Jira "Customer Bug = Yes" field. Internal bugs caught in QA don't count — a deliberate choice so CFR focuses on bugs that actually shipped and reached customers.
* **MTTR** measures mean hours from Jira incident open to resolved. It uses the same Jira customer-bug stream as CFR.

---

## Read this family

The classic DORA reading pattern:

1. **Start with the velocity pair.** Are you shipping often enough? Is Lead Time stable or drifting?
2. **Then the stability pair.** Is CFR creeping? Are recovery times stable?
3. **The interesting question is usually how they trade off.** A team where Deployment Frequency just doubled but CFR also doubled has not actually improved delivery. A team where Deployment Frequency doubled while CFR stayed flat has — that's the real win.

For AI adoption analysis, DORA is the metric set most likely to show "AI is paying off" at a board-level credible scale. Healthy AI rollouts trend Deployment Frequency ↑, Lead Time ↓, CFR flat or ↓, MTTR flat or ↓.

If Deployment Frequency rises while CFR also rises, AI is enabling faster but worse shipping — a known anti-pattern that needs investigation.

---

## Where this family shows up

* **/ai-adoption/board-metrics** — all four DORA metrics in one canonical view.
* **/ai-adoption/ai-impact** — CFR is the prominent stability metric. CFR KPI card, trend chart, severity-stacked view, and the CFR-by-AI-Tier breakdown.
* **/ai-adoption/executive** — Deployment Frequency and CFR as hero trend lines.

---

## Required integrations

* **CFR & MTTR** require the Jira integration with the Customer Bug custom field configured (`JIRA_CUSTOMER_BUG_FIELD_ID` env var). Without it, both metrics show an empty state.
* **Lead Time and Deployment Frequency** require release detection configured per repo (tagged releases, GitHub Releases, or a configured release event). Without it, both metrics show an empty state.

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

**What counts as a release.** A tagged release, a GitHub Release, or any other release event the writer captures for the repo. Pre-releases are excluded.

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

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Lead Time for Changes](#lead-time-for-changes) | The other DORA velocity metric. Velocity = "fast and often" needs both. |
| [CFR](#change-failure-rate-cfr) | Stability counterweight. High Deployment Frequency with low CFR is the goal. |
| [Throughput](/gk-insights/ai-adoption-output-metrics#throughput) | The merged-PR count metric. Often correlated with Deployment Frequency but measures merges, not releases. |

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
A: Only if each deploy also produces a release artifact the backend can read. If your pipeline auto-tags every deploy, yes. If it deploys without tagging, no — work with your admin to wire up release detection.

**Q: A revert is a deploy. Does that count?**
A: Yes — the revert ships and is captured as a release event. Some teams find this over-counts; the metric keeps it because reverts genuinely are deploys (they go through the same pipeline).

**Q: How do I see deploys per team?**
A: /ai-adoption/board-metrics with team filter applied, or /ai-adoption/executive with team scope set.

---

## Lead Time for Changes

> _Hours from a developer's first commit to the release that delivered the change to production. The other DORA velocity metric._

**Family:** DORA & Quality · **Cadence:** Per PR, aggregated for views · **Where it appears:** /ai-adoption/board-metrics, /ai-adoption/ai-impact

### At a glance

Lead Time for Changes is "how long does it take a change to go from first keystroke to live in production?" It is the end-to-end sibling of [Cycle Time](/gk-insights/ai-adoption-flow-metrics#cycle-time). Cycle Time stops at merge; Lead Time keeps counting until the change is actually in production.

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
| [Cycle Time](/gk-insights/ai-adoption-flow-metrics#cycle-time) | The first-commit-to-merge portion. Lead Time extends to the delivering release. |
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

---

## Change Failure Rate (CFR)

> _The percent of releases that produce a customer-reported bug. The DORA stability metric._

**Family:** DORA & Quality · **Cadence:** Per window, per team or org · **Where it appears:** /ai-adoption/ai-impact, /ai-adoption/board-metrics, /ai-adoption/executive

### At a glance

CFR is "what percent of our deploys cause customer pain?" High CFR means shipping is causing more problems than it's solving. Low CFR means you are shipping reliably. It is the stability counterweight to Deployment Frequency — together they tell the most credible story about whether your engineering organization is healthy.

The dashboard uses your Jira "Customer Bug = Yes" field as the canonical signal for what counts. Internal bugs caught in QA don't count toward CFR. This is deliberate — CFR focuses on what hit customers, not what your QA caught.

### Formula

```
CFR = count(releases with ≥1 customer bug) / count(releases in window) × 100%

  where customer bug = Jira issue with Customer Bug field = Yes
```

### How GitKraken Insights calculates it

**The Customer Bug field.** A background worker (the CFR syncer) queries Jira hourly for issues where your configured Customer Bug field is set to "Yes." The bugs are stored in the `jira_incidents` table along with severity, assignee, and create/resolve timestamps.

**The integration requires** `JIRA_CUSTOMER_BUG_FIELD_ID` **env var.** Without it, CFR sync is skipped and the UI shows an empty state.

**Matching bugs to releases.** Each customer bug is matched to the release it shipped in. A release "fails" if it had at least one customer bug attributed to it.

**Severity.** Bugs are tracked with severity — Critical, High, Medium, Low. The CFR KPI card on /ai-adoption/ai-impact specifically shows **Critical & High Customer Bugs** — the bugs that actually hurt. The trend chart can show all severities stacked.

**Aggregation.** CFR is `failing releases / total releases` over the window, expressed as a percent.

### Why it matters

CFR is the metric most likely to _contradict_ a happy story you are telling about velocity. A team that just doubled Deployment Frequency without watching CFR may be celebrating something that is actively making customers angry.

For AI adoption specifically, CFR is the metric to watch alongside Deployment Frequency. AI enables faster shipping; the question is whether it enables faster _and reliable_ shipping or just faster shipping. The dashboard's job is to answer that question honestly.

### How to read it

DORA bands for CFR:

| Band | CFR | Pattern |
| --- | --- | --- |
| **Elite** | 0–5% | Very few deploys produce customer-reported bugs |
| **High** | 5–10% | Solid stability, occasional incidents |
| **Medium** | 10–15% | Routine bugs — investigate why |
| **Low** | > 15% | Quality problem — slow down or strengthen review |

A rising CFR trend is more concerning than a high baseline. Some teams have a legitimately higher baseline CFR (new product areas, experimental features) and that is fine — the question is whether it is trending up or down.

### Where it appears

* **/ai-adoption/ai-impact** — primary surface. CFR KPI card (Critical & High bugs), CFR trend chart (severity-stacked), CFR breakdown chart (dimension dropdown: None / Team / Priority / Assignee, with bars or trend lines). Drill-down table below. The CFR-by-AI-Tier breakdown also lives here.
* **/ai-adoption/board-metrics** — CFR as one of the four DORA cards.
* **/ai-adoption/executive** — CFR trend line as a stability indicator.

### Settings that affect it

* `JIRA_CUSTOMER_BUG_FIELD_ID` env var — the Jira custom field ID for "Customer Bug = Yes." Required for CFR to populate at all.
* `JIRA_INSTANCE_N_CUSTOMER_BUG_FIELD_ID` — per-instance override if you have multiple Jira instances.
* **Unmatched Jira assignees** (Settings → Developers) — assignees the sync couldn't match to a roster developer. CFR by-assignee dimension will under-count until these are resolved.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Deployment Frequency](#deployment-frequency) | The DORA velocity pair. CFR is the stability counterweight. |
| [MTTR](#mean-time-to-recovery-mttr) | The other stability metric — how fast you recover when CFR strikes. |
| [First-Pass Rate](/gk-insights/ai-adoption-flow-metrics#first-pass-rate) | High First-Pass + rising CFR = rubber-stamping risk. |
| [AI Tier](/gk-insights/ai-adoption-agentic-metrics#ai-tier) | The CFR-by-Tier breakdown on /ai-adoption/ai-impact tells you whether AI adoption changes stability. |

### How to improve it

* **Increase test coverage on high-risk code paths.** Code Climate, coverage tools, and other quality tools live alongside Insights, not inside it — but the rising CFR signal is what should send you there.
* **Slow down on high-risk changes.** A team whose CFR is rising should not be sprinting — they should be tightening review on architecturally-sensitive PRs.
* **Investigate the bug → PR mapping.** The /ai-adoption/ai-impact drill-down table shows the recent customer bugs. Walk through them. Are they hitting one repo? One team? One developer's PRs? Patterns tell you where to intervene.
* **Pair CFR with First-Pass Rate.** If both are high, your reviews aren't catching real problems. Calibrate reviewer expectations.
* **Don't trade velocity for stability blindly.** A team with elite Deployment Frequency and elite CFR is the goal — not high stability through low velocity.

### Limitations and gotchas

* **Jira only.** CFR reads customer bugs from Jira. Organizations that track customer bugs in Azure DevOps work items instead can't populate CFR.
* **Depends on Jira workflow discipline.** If your team marks customer bugs inconsistently, CFR is noisy. The fix is upstream — get Jira workflow consistent.
* **No automatic severity assessment.** Severity comes from the Jira field directly. If your assignees set severity inconsistently (everyone's bug is "High"), the Critical & High view is misleading.
* **Customer-only.** Internal bugs caught in QA don't count. This is deliberate — but it means CFR doesn't measure all quality, just user-visible quality.
* **Lagging by issue-creation latency.** A bug that ships today but isn't reported by a customer for six weeks doesn't appear in this week's CFR. CFR is genuinely lagging in a way Deployment Frequency is not.
* **Sync runs every hour.** Bugs created in the last hour may not be in CFR yet.

### FAQ

**Q: We have a customer bug that wasn't caused by a code change. Does it count?**
A: If it is marked Customer Bug = Yes in Jira, yes. The CFR metric does not try to distinguish code-caused bugs from infrastructure / data / config issues. If you need that distinction, use a separate Jira field and filter the dashboard view accordingly.

**Q: Our CFR keeps spiking on Monday mornings. Why?**
A: Probably weekend-released bugs that don't get reported until the work week starts. Look at the create-timestamp of the bugs, not the merge-timestamp. The spike is artifact, not a real Monday quality problem.

**Q: Can I see CFR for just one team?**
A: Yes. Apply a team filter on the page; the breakdown chart also supports team as a dimension.

**Q: Why does CFR by AI Tier matter?**
A: It tells you whether AI adoption is changing your stability profile. The hopeful answer is "Power Users have similar or lower CFR than Emerging devs." If Power Users have higher CFR, that's a flag — AI is enabling faster shipping at a quality cost.

---

## Mean Time to Recovery (MTTR)

> _Mean hours from incident open to incident close. The DORA recovery metric._

**Family:** DORA & Quality · **Cadence:** Per window, per team or org · **Where it appears:** /ai-adoption/board-metrics

### At a glance

MTTR is "when things break, how fast do you fix them?" It is the second DORA stability metric, alongside [CFR](#change-failure-rate-cfr). Where CFR asks "how often does shipping cause problems?", MTTR asks "when shipping does cause problems, how fast do you recover?" Together they describe your stability profile.

A team with high CFR but low MTTR is shipping bugs but fixing them fast — uncomfortable, but recoverable. A team with low CFR but high MTTR ships clean most of the time but struggles when something does go wrong — unusual but possible. The healthiest profile is low on both.

### Formula

```
MTTR (hours) = mean(resolved_at − opened_at) for customer-bug
                incidents resolved in the window
```

### How GitKraken Insights calculates it

**Source data.** The same Jira customer-bug stream that powers CFR. Each Jira incident has a creation timestamp (`opened_at`) and a resolution timestamp (`resolved_at`), stored in the `jira_incidents` table.

**Computation.** For each resolved incident in the window, compute the duration in hours. Take the mean across incidents. (Median is more robust to outliers and is shown on hover for most charts.)

**Open incidents are excluded.** Only resolved incidents count toward MTTR — an open incident doesn't have a resolution time yet, so it cannot contribute to a mean recovery time.

### Why it matters

MTTR is the metric that distinguishes "we ship bugs" from "we ship bugs and they bleed into our quarter." A team with elite MTTR can take risks on shipping speed because they know they can recover fast. A team with poor MTTR has to be more conservative because every bug becomes a slow drag.

For AI adoption: AI tools sometimes help MTTR (faster debugging with Claude / Codex), and sometimes hurt (autonomous AI-assisted commits introducing subtle bugs that take longer to debug). Worth watching alongside CFR by AI Tier.

### How to read it

DORA bands for MTTR:

| Band | MTTR | Pattern |
| --- | --- | --- |
| **Elite** | < 1 hour | Production hotfixes within the hour |
| **High** | 1 hour – 1 day | Same-day recovery |
| **Medium** | 1 day – 1 week | Recovery within a week |
| **Low** | > 1 week | Slow recovery — a risk amplifier |

For most product engineering teams, **High** is the realistic target. Elite requires investment in observability, runbooks, and on-call practices that not every team needs.

### Where it appears

* **/ai-adoption/board-metrics** — MTTR as one of the four DORA cards.

### Settings that affect it

* `JIRA_CUSTOMER_BUG_FIELD_ID` — required for MTTR to populate (same as CFR).

### Related metrics

| Metric | Relationship |
| --- | --- |
| [CFR](#change-failure-rate-cfr) | The "how often" stability metric. MTTR is the "how fast to recover" sibling. |
| [Lead Time](#lead-time-for-changes) | The velocity equivalent. Lead Time and MTTR are sometimes confused — Lead Time covers shipping changes; MTTR covers recovering from incidents. |

### How to improve it

* **Invest in observability.** Most MTTR is dominated by "how long it took to find the problem." Better dashboards, alerts, and tracing usually move MTTR more than faster fixes do.
* **Write runbooks.** A team that knows what to do when a specific alert fires recovers faster than one that has to figure it out each time.
* **Practice incident response.** Game days, recovery drills, and post-mortem rituals all reduce real-incident MTTR.
* **Use AI-assisted debugging.** A real win — Claude and Codex can walk through stack traces and recent changes faster than humans on the first pass. Practice using AI as the first debugging step.

### Limitations and gotchas

* **Means are skewed by outliers.** One incident that took a week to fix can dominate a monthly MTTR. Check median alongside.
* **Open incidents aren't counted.** A team with many open old incidents can have artificially good MTTR (only the resolved ones contribute). Watch open-incident count alongside.
* **Severity is not stratified in the MTTR metric today.** CFR tracks bug counts by severity (Critical / High / Medium / Low), but MTTR is a single mean across all customer-bug incidents. If you need severity-aware recovery times, drill into the CFR breakdown table where severity is captured.
* **No automatic incident detection.** The metric uses Jira customer bugs as the proxy. If your team uses a separate incident management system (PagerDuty, Statuspage), MTTR in this dashboard won't reflect those events.

### FAQ

**Q: Should MTTR include the time to acknowledge?**
A: It currently includes everything from Jira open to Jira close. If your Jira workflow distinguishes "acknowledged" from "open," you would need a custom view — not currently in the dashboard.

**Q: Why doesn't MTTR show on /ai-adoption/ai-impact alongside CFR?**
A: They are related but different views. CFR is the broader stability metric most leaders watch, so it is featured on the AI Impact page. MTTR is the operational recovery metric and lives in /ai-adoption/board-metrics alongside the rest of DORA.
