---
title: "Change Failure Rate (CFR)"
description: "How GitKraken Insights calculates and displays Change Failure Rate (CFR)."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: gk-insights
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for Change Failure Rate (CFR)"
    card_icon: shield-check
    nav_category: metrics
    nav_label: "Change Failure Rate (CFR)"
    nav_order: 43
    nav_parent: ai-adoption-dora-metrics
    page_type: content
---
<kbd>Last updated: November 2026</kbd>

## Change Failure Rate (CFR)

> _The percent of releases that produce a customer-reported bug. The DORA stability metric._

**Family:** DORA & Quality · **Cadence:** Per window, per team or org · **Where it appears:** Throughput & Quality, Board Metrics, Team Lead Insights, Executive View, AI Impact

### At a glance

CFR is "what percent of our deploys cause customer pain?" High CFR means shipping is causing more problems than it's solving. Low CFR means you are shipping reliably. It is the stability counterweight to Deployment Frequency — together they tell the most credible story about whether your engineering organization is healthy.

The dashboard counts the customer bugs in your connected issue tracker — Jira, Linear, YouTrack, Azure Boards, or [ClickUp](/gk-insights/ai-adoption-connect-clickup) — that the connection's defect detection identifies (for Jira, for example, a "Customer Bug = Yes" field). Internal bugs caught in QA do not count toward CFR. This is deliberate — CFR focuses on what hit customers, not what your QA caught.

### Formula

```
CFR = count(releases with ≥1 customer bug) / count(releases in window) × 100%

  where customer bug = a tracker issue that defect detection
                       identifies as a customer bug
```

### How GitKraken Insights calculates it

**Customer bugs.** GitKraken Insights reads issues from each connected tracker that has defect detection turned on — Jira, Linear, YouTrack, Azure Boards, or ClickUp — and keeps the ones that match the connection's defect rules. For Jira it checks hourly, and it keeps each bug's priority, assignee, and created and resolved times. One tracker with defect detection is enough for CFR.

**The integration requires defect detection.** On the tracker connection in **Settings → Data Connections**, select **Enable defect detection** and choose how to **Match by**. Without it, CFR is not measured: the CFR cards show "Change Failure Rate isn't measured yet" and a **Configure defect detection** button that opens that connection's defect-detection settings.

**Matching bugs to releases.** A release counts as failed when a customer bug is created after it ships and before the next release, within the selected period.

**Priority.** Customer bugs are grouped by priority as Highest, High, Medium, and Low, plus Other for priorities that do not map to those four. Priority names other than the standard ones (for example, "Critical (P1)") are placed in these bands by their order in your tracker's priority scheme, most severe first. The **Customer Bugs & Change Failure Rate** card counts the same bugs that make releases fail, so its priority counts add up to the bugs behind the failed releases.

**Aggregation.** CFR is failing releases ÷ total releases over the window, expressed as a percent. Teams share releases, so per-team release counts do not add up to the organization total.

### Why it matters

CFR is the metric most likely to _contradict_ a happy story you are telling about velocity. A team that just doubled Deployment Frequency without watching CFR may be celebrating something that is actively making customers angry.

For AI adoption specifically, CFR is the metric to watch alongside Deployment Frequency. AI enables faster shipping; the question is whether it enables faster _and reliable_ shipping or just faster shipping.

### How to read it

DORA bands for CFR:

| Band | CFR | Pattern |
| --- | --- | --- |
| **Elite** | 0% to 5% | Very few deploys produce customer-reported bugs |
| **High** | 5% to 10% | Solid stability, occasional incidents |
| **Medium** | 10% to 15% | Routine bugs — investigate why |
| **Low** | > 15% | Quality problem — slow down or strengthen review |

A rising CFR trend is more concerning than a high baseline. Some teams have a legitimately higher baseline CFR (new product areas, experimental features) and that is fine — the question is whether it is trending up or down.

### Where it appears

* **Throughput & Quality** — primary surface. The **Customer Bugs & Change Failure Rate** card shows the rate with "N failed / M releases", then **Customer Bugs by Priority**: Highest, High, Medium, Low, and Other counts above a trend stacked by priority, with the previous period alongside. Use the legend under the counts to turn priorities on or off in the trend. The **Customer Bugs by** breakdown card groups bugs by Team, Department, Business Unit, Priority, or Assignee, as bars or trend lines.
* **Board Metrics** — the CFR tile, plus the same Customer Bugs & Change Failure Rate card, always for all definitions.
* **Team Lead Insights** — CFR in the KPI strip.
* **Executive View** — the Customer Bugs chart with its CFR readout, as a stability indicator. When a bug tracker is configured, the team rankings add a **CFR Bugs** column: the distinct customer bugs attributed to each team's releases in the period, with a dash for teams that had no releases.
* **AI Impact** — Customer Bugs by AI Tier.
* **Failed releases drill-down** — select the CFR value on the Customer Bugs & Change Failure Rate card, the Board Metrics CFR tile, the Executive View readout, or the Team Lead Insights KPI strip to open **Change Failure Rate — Failed releases**: each failed release's repo, team, release date, and bug count. Expand a release to see the customer bugs that failed it. Select a priority count to see the bugs in that priority instead.
* **Customer bug list** — on the **Customer Bugs by** card and the Customer Bugs chart, select a bar or a trend point to list the bugs behind it, with each bug's key, title, severity, team, created date, and release. Bars open a list when the card groups by Team, Department, Business Unit, or Priority, not Assignee. Bugs that could not be matched to a team appear under **Unknown**.

When some customer bugs are assigned to someone who does not match a developer, the CFR readouts (the Customer Bugs & Change Failure Rate card, the Board Metrics tile, the Executive View readout, and the Team Lead Insights KPI strip) show a note such as "3 bugs not attributed to a team". Those bugs cannot be tied to a release, so they are not counted in CFR.

When more than one defect rule is set up, a **Definition** picker on the Throughput & Quality bug cards and the Customer Bugs chart narrows them to one rule; **All definitions** is the default. Board Metrics always shows all definitions.

<figure>
  <img src="/wp-content/uploads/gki-cfr-customer-bugs-card-01-v4@2x.png" class="help-center-img img-bordered" alt="The Customer Bugs & Change Failure Rate card in GitKraken Insights: a Change Failure Rate section reading 7.8% with 5 failed / 64 releases, and a Customer Bugs by Priority section with Highest 2, High 9, Medium 9, Low 3 and Other 0 pills above weekly stacked bars of bugs by priority from Jun 1 to Jun 22, each beside a grey previous-period bar, crossed by a Claude Code rolled out to Mobile annotation, with a Highest, High, Medium, Low and Other legend below" />
  <figcaption>The Customer Bugs & Change Failure Rate card — the rate with failed and total releases, then the same bugs by priority. The gray bar to the left of each week's stack is the previous period.</figcaption>
</figure>

### Settings that affect it

* **Enable defect detection** and **Match by** (Settings → Data Connections → the tracker connection) — how the connection identifies customer bugs; for Jira, for example, a custom field such as "Customer Bug = Yes." Required for CFR to populate at all. Select **Add defect rule** to add up to five named rules per connection, each with its own **Match by**. Name each rule so you can tell them apart in the **Definition** picker; two rules cannot match exactly the same issues. Changing **Enable defect detection** or **Match by** recalculates that connection's Change Failure Rate history, so past figures can change.
* **More than one defect source** — each tracker connection, and each Jira instance, has its own defect rules.
* **Release tracking** (Settings → Releases, or the Manual Releases API) — CFR is failing releases ÷ total releases, so it needs releases to divide by.
* **Bug assignees who do not match a developer** — customer bugs assigned to someone GitKraken Insights cannot match to a roster developer cannot be tied to a release, so CFR leaves them out. The CFR readouts show how many with "N bugs not attributed to a team", and **Status** on the Jira connection (Settings → Data Connections) shows the number of unmatched assignees.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Deployment Frequency](/gk-insights/ai-adoption-dora-deployment-frequency) | The DORA velocity pair. CFR is the stability counterweight. |
| [MTTR](/gk-insights/ai-adoption-dora-mttr) | The other stability metric — how fast you recover when CFR strikes. |
| [First-Pass Rate](/gk-insights/ai-adoption-flow-first-pass-rate) | High First-Pass + rising CFR = rubber-stamping risk. |
| [AI Tier](/gk-insights/ai-adoption-agentic-ai-tier) | The CFR-by-Tier breakdown on AI Impact tells you whether AI adoption changes stability. |

### How to improve it

* **Increase test coverage on high-risk code paths.** Code Climate, coverage tools, and other quality tools live alongside Insights, not inside it — but the rising CFR signal is what should send you there.
* **Slow down on high-risk changes.** A team whose CFR is rising should not be sprinting — they should be tightening review on architecturally sensitive PRs.
* **Investigate the bug → PR mapping.** The Customer Bugs by Team, Priority, or Assignee breakdown on Throughput & Quality shows where the bugs cluster. Select a Team or Priority bar to list its bugs and the release each one followed, then open them in your tracker and walk through them. Are they hitting one repo? One team? One developer's PRs? Patterns tell you where to intervene.
* **Pair CFR with First-Pass Rate.** If both are high, your reviews are not catching real problems. Calibrate reviewer expectations.
* **Do not trade velocity for stability blindly.** A team with elite Deployment Frequency and elite CFR is the goal — not high stability through low velocity.

### Limitations and gotchas

* **Supported trackers.** CFR reads customer bugs from Jira, Linear, YouTrack, Azure Boards (Azure DevOps with work-item sync on), or [ClickUp](/gk-insights/ai-adoption-connect-clickup), whichever you have connected and set up to identify defects. Organizations that track customer bugs elsewhere cannot populate CFR.
* **Depends on tracker workflow discipline.** If your team marks customer bugs inconsistently, CFR is noisy. The fix is upstream — get your tracker workflow consistent.
* **No automatic severity assessment.** Priority comes from your tracker directly. If your assignees set priority inconsistently (everyone's bug is "High"), the priority breakdown is misleading.
* **Customer-only.** Internal bugs caught in QA do not count. This is deliberate — but it means CFR does not measure all quality, just user-visible quality.
* **Lagging by issue-creation latency.** A bug that ships today but is not reported by a customer for six weeks does not appear in this week's CFR. CFR lags in a way Deployment Frequency does not.
* **Sync runs every hour.** Bugs created in the last hour may not be in CFR yet.

### FAQ

**Q: We have a customer bug that was not caused by a code change. Does it count?**
A: If your tracker's defect detection identifies it as a customer bug, yes. The CFR metric does not try to distinguish code-caused bugs from infrastructure / data / config issues. If you need that distinction, mark code-caused bugs with their own field, label, or issue type and set defect detection's **Match by** to it.

**Q: Our CFR keeps spiking on Monday mornings. Why?**
A: The likely cause is bugs released over the weekend that customers do not report until the work week starts. Look at the bugs' created dates, not the merge dates. If that is the pattern, the spike is an artifact, not a real Monday quality problem.

**Q: Can I see CFR for just one team?**
A: Yes. Apply a team filter on the page; the breakdown chart also supports team as a dimension. A release can count for more than one team, so team-level CFR figures do not sum to the org figure.

**Q: Why does CFR by AI Tier matter?**
A: It tells you whether AI adoption is changing your stability profile. The hopeful answer is "Power Users have similar or lower CFR than Emerging devs." If Power Users have higher CFR, that's a flag — AI is enabling faster shipping at a quality cost.
