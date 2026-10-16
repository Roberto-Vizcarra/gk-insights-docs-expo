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

**Family:** DORA & Quality · **Cadence:** Per window, per team or org · **Where it appears:** /ai-adoption/throughput-quality, /ai-adoption/board-metrics, /ai-adoption/flow-delivery, /ai-adoption/executive, /ai-adoption/ai-impact

### At a glance

CFR is "what percent of our deploys cause customer pain?" High CFR means shipping is causing more problems than it's solving. Low CFR means you are shipping reliably. It is the stability counterweight to Deployment Frequency — together they tell the most credible story about whether your engineering organization is healthy.

The dashboard counts the customer bugs in your connected issue tracker — Jira, Linear, YouTrack, or Azure Boards — that the connection's defect detection identifies (for Jira, for example, a "Customer Bug = Yes" field). Internal bugs caught in QA don't count toward CFR. This is deliberate — CFR focuses on what hit customers, not what your QA caught.

### Formula

```
CFR = count(releases with ≥1 customer bug) / count(releases in window) × 100%

  where customer bug = a tracker issue that defect detection
                       identifies as a customer bug
```

### How GitKraken Insights calculates it

**Customer bugs.** A background worker (the CFR syncer) reads issues from each connected tracker that has defect detection turned on — Jira, Linear, YouTrack, or Azure Boards — and keeps the ones that match the connection's defect detection. For Jira it queries hourly, and the bugs are stored in the `jira_incidents` table along with priority, assignee, and create/resolve timestamps. One tracker with defect detection is enough for CFR.

**The integration requires defect detection.** On the tracker connection in **Settings → Data Connections**, select **Enable defect detection** and choose how to **Match by**. Without it, CFR isn't measured: the CFR cards show "Change Failure Rate isn't measured yet" and a **Configure defect detection** button that opens that connection's defect-detection settings.

**Matching bugs to releases.** A release counts as failed when a customer bug is created after it ships and before the next release, within the selected period.

**Priority.** Customer bugs are grouped by priority as Highest, High, Medium, and Low, plus Other for priorities that don't map to those four. Priority names other than the standard ones (for example, "Critical (P1)") are placed in these bands by their order in your tracker's priority scheme, most severe first. The **Customer Bugs & Change Failure Rate** card counts the same bugs that make releases fail, so its priority counts add up to the bugs behind the failed releases.

**Aggregation.** CFR is `failing releases / total releases` over the window, expressed as a percent. Teams share releases, so per-team release counts don't add up to the organization total.

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

* **/ai-adoption/throughput-quality** — primary surface. The **Customer Bugs & Change Failure Rate** card shows the rate with "N failed / M releases", then **Customer Bugs by Priority**: Highest, High, Medium, Low, and Other counts you can turn on or off, above a trend stacked by priority with the previous period alongside. The **Customer Bugs by** breakdown card groups bugs by Team, Department, Business Unit, Priority, or Assignee, as bars or trend lines.
* **/ai-adoption/board-metrics** — the CFR tile, plus the same Customer Bugs & Change Failure Rate card, always for all definitions.
* **/ai-adoption/flow-delivery** — CFR in the Team Lead Insights KPI strip.
* **/ai-adoption/executive** — the Customer Bugs chart with its CFR readout, as a stability indicator.
* **/ai-adoption/ai-impact** — Customer Bugs by AI Tier.

When more than one defect source is connected, a **Definition** picker on the Throughput & Quality bug cards and the Customer Bugs chart narrows them to one source; **All definitions** is the default. Board Metrics always shows all definitions.

<figure>
  <img src="/wp-content/uploads/gki-cfr-customer-bugs-card-01.png" class="help-center-img img-bordered" alt="The Customer Bugs & Change Failure Rate card in GitKraken Insights: a Change Failure Rate section reading 7.8% with 5 failed / 64 releases, and a Customer Bugs by Priority section with Highest 2, High 9, Medium 9, Low 3 and Other 0 pills above weekly stacked bars of bugs by priority from Jun 1 to Jun 22, each beside a grey previous-period bar, crossed by a Claude Code rolled out to Mobile annotation" />
  <figcaption>The Customer Bugs & Change Failure Rate card — the rate with failed and total releases, then the same bugs by priority.</figcaption>
</figure>

### Settings that affect it

* **Enable defect detection** and **Match by** (Settings → Data Connections → the tracker connection) — how the connection identifies customer bugs; for Jira, for example, a custom field such as "Customer Bug = Yes." Required for CFR to populate at all. Changing **Enable defect detection** or **Match by** recalculates that connection's Change Failure Rate history, so past figures can change.
* **More than one defect source** — each tracker connection, and each Jira instance, has its own defect detection.
* **Release tracking** (Settings → Releases, or the manual releases API) — CFR is failing releases ÷ total releases, so it needs releases to divide by.
* **Unmatched Jira assignees** (Settings → Developers) — assignees the sync couldn't match to a roster developer. CFR by-assignee dimension will under-count until these are resolved.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Deployment Frequency](/insights-expo/expo-ai-adoption-dora-deployment-frequency) | The DORA velocity pair. CFR is the stability counterweight. |
| [MTTR](/insights-expo/expo-ai-adoption-dora-mttr) | The other stability metric — how fast you recover when CFR strikes. |
| [First-Pass Rate](/insights-expo/expo-ai-adoption-flow-first-pass-rate) | High First-Pass + rising CFR = rubber-stamping risk. |
| [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier) | The CFR-by-Tier breakdown on /ai-adoption/ai-impact tells you whether AI adoption changes stability. |

### How to improve it

* **Increase test coverage on high-risk code paths.** Code Climate, coverage tools, and other quality tools live alongside Insights, not inside it — but the rising CFR signal is what should send you there.
* **Slow down on high-risk changes.** A team whose CFR is rising should not be sprinting — they should be tightening review on architecturally-sensitive PRs.
* **Investigate the bug → PR mapping.** The Customer Bugs by Team, Priority, or Assignee breakdown on /ai-adoption/throughput-quality shows where the bugs cluster; open those bugs in your tracker and walk through them. Are they hitting one repo? One team? One developer's PRs? Patterns tell you where to intervene.
* **Pair CFR with First-Pass Rate.** If both are high, your reviews aren't catching real problems. Calibrate reviewer expectations.
* **Don't trade velocity for stability blindly.** A team with elite Deployment Frequency and elite CFR is the goal — not high stability through low velocity.

### Limitations and gotchas

* **Supported trackers.** CFR reads customer bugs from Jira, Linear, YouTrack, or Azure Boards (Azure DevOps with work-item sync on), whichever you have connected and set up to identify defects. Organizations that track customer bugs elsewhere can't populate CFR.
* **Depends on tracker workflow discipline.** If your team marks customer bugs inconsistently, CFR is noisy. The fix is upstream — get your tracker workflow consistent.
* **No automatic severity assessment.** Priority comes from your tracker directly. If your assignees set priority inconsistently (everyone's bug is "High"), the priority breakdown is misleading.
* **Customer-only.** Internal bugs caught in QA don't count. This is deliberate — but it means CFR doesn't measure all quality, just user-visible quality.
* **Lagging by issue-creation latency.** A bug that ships today but isn't reported by a customer for six weeks doesn't appear in this week's CFR. CFR is genuinely lagging in a way Deployment Frequency is not.
* **Sync runs every hour.** Bugs created in the last hour may not be in CFR yet.

### FAQ

**Q: We have a customer bug that wasn't caused by a code change. Does it count?**
A: If your tracker's defect detection identifies it as a customer bug, yes. The CFR metric does not try to distinguish code-caused bugs from infrastructure / data / config issues. If you need that distinction, mark code-caused bugs with their own field, label, or issue type and set defect detection's **Match by** to it.

**Q: Our CFR keeps spiking on Monday mornings. Why?**
A: Probably weekend-released bugs that don't get reported until the work week starts. Look at the create-timestamp of the bugs, not the merge-timestamp. The spike is artifact, not a real Monday quality problem.

**Q: Can I see CFR for just one team?**
A: Yes. Apply a team filter on the page; the breakdown chart also supports team as a dimension. A release can count for more than one team, so team-level CFR figures don't sum to the org figure.

**Q: Why does CFR by AI Tier matter?**
A: It tells you whether AI adoption is changing your stability profile. The hopeful answer is "Power Users have similar or lower CFR than Emerging devs." If Power Users have higher CFR, that's a flag — AI is enabling faster shipping at a quality cost.
