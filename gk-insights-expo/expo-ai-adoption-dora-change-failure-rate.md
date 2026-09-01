---
title: "Change Failure Rate (CFR)"
description: "How GitKraken Insights calculates and displays Change Failure Rate (CFR)."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for Change Failure Rate (CFR)"
    card_icon: shield-check
    nav_category: metrics
    nav_label: "Change Failure Rate (CFR)"
    nav_order: 43
    nav_parent: expo-ai-adoption-dora-metrics
    page_type: content
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

**The integration requires the Customer bug field ID.** Set it on the Jira connection in Settings → Data Connections. Without it, CFR sync is skipped and the UI shows an empty state.

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

* **Customer bug field ID** (Settings → Data Connections → the Jira connection → Edit → Advanced) — the Jira custom field for "Customer Bug = Yes." Required for CFR to populate at all.
* **Multiple CFR configurations** — you can keep more than one Change Failure Rate configuration, one per Jira instance or per definition of a failed change.
* **Release tracking** (Settings → Releases, or the manual releases API) — CFR is failing releases ÷ total releases, so it needs releases to divide by.
* **Unmatched Jira assignees** (Settings → Developers) — assignees the sync couldn't match to a roster developer. CFR by-assignee dimension will under-count until these are resolved.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Deployment Frequency](#deployment-frequency) | The DORA velocity pair. CFR is the stability counterweight. |
| [MTTR](#mean-time-to-recovery-mttr) | The other stability metric — how fast you recover when CFR strikes. |
| [First-Pass Rate](/insights-expo/expo-ai-adoption-flow-metrics#first-pass-rate) | High First-Pass + rising CFR = rubber-stamping risk. |
| [AI Tier](/insights-expo/expo-ai-adoption-agentic-metrics#ai-tier) | The CFR-by-Tier breakdown on /ai-adoption/ai-impact tells you whether AI adoption changes stability. |

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
