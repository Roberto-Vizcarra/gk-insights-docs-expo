---
title: "Cycle Time"
description: "How GitKraken Insights calculates and displays Cycle Time."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for Cycle Time"
    card_icon: clock
    nav_category: metrics
    nav_label: "Cycle Time"
    nav_order: 31
    nav_parent: expo-ai-adoption-flow-metrics
    page_type: content
---
<kbd>Last updated: November 2026</kbd>

## Cycle Time

> _Total hours from when work on a PR starts to its merge (and optionally to deploy). The headline operational health metric — how fast work flows from idea to shipped._

**Family:** Flow & Cycle Time · **Cadence:** Per PR, aggregated for views · **Where it appears:** /insights/throughput-quality, /insights/flow-delivery, /insights/executive, /insights/board-metrics, /insights/ai-impact, /insights/teams, /insights/developers

### At a glance

Cycle Time is the single most informative operational metric in the dashboard. It answers "how long does it take a piece of work to go from first keystroke to merged?" Lower is generally better — but the _composition_ of the time (which phase dominates) matters more than the total. A 5-day Cycle Time made of 1 day Coding + 4 days Pickup tells a very different story than one made of 4 days Coding + 1 day Pickup. The phase breakdown is the part to read first.

### Formula

```
Cycle Time = Coding + Pickup + Review  (+ Deploy, when available)

  Coding  = work start → PR open
  Pickup  = PR open → first review
  Review  = first review → merge
  Deploy  = merge → production deploy (if release detection is configured)
```

For a team or org view, Cycle Time is shown as the median across PRs merged in the window, with phase contributions visualized as a stacked bar or area. A single PR's cycle time — the **Total** column in the PR drill-down — runs from when work started until the PR merged. Deploy is an optional extra phase that charts show only when release detection is available.

### How GitKraken Insights calculates it

**The four phases.** Cycle Time is decomposed into four phases. Each PR's timestamps are computed and the phase durations are summed:

1. **Coding** — from when work started to when the PR was opened. The [Cycle Time Start Signal](/insights-expo/expo-ai-adoption-settings#cycle-time-start-signal) setting decides when work starts. Captures "writing the code before showing anyone."
2. **Pickup** — from PR open to its first review: an approval, a changes-requested review, or a review comment. Captures "PR is waiting for someone to look at it." On GitHub, only a submitted review ends Pickup; plain comments on the pull request do not. On GitLab, Bitbucket, and Azure DevOps, a review comment also counts.
3. **Review** — from the first review to merge. Captures the back-and-forth time.
4. **Deploy** — from `merged_at` to the production deploy timestamp. Only populated when release detection is configured for the repo. When release detection is not set up, the Deploy phase is omitted from the total rather than substituted with anything else. A PR's **Total** in the drill-down ends at merge and does not include Deploy.

**PRs merged without a qualifying review.** A PR that merges without a qualifying review has no Pickup or Review time. In the PR drill-down, those two columns show a dash, and the time from PR open to merge appears under **Unattributed** instead. That way Coding + Pickup + Review + Unattributed still adds up to the PR's Total when work started before the PR was opened. Sort by Unattributed to find PRs that merged without review.

<figure>
  <img src="/wp-content/uploads/gki-cycle-time-pr-drilldown-01-v2@2x.png" class="help-center-img img-bordered" alt="The All PRs Cycle Time drill-down in GitKraken Insights with a 22.6h median, Search, State and Category filters, the Slower, Average, Fast and Elite scatterplot, and the PR table sorted by Unattributed: merged PRs without a qualifying review show a dash under Pickup and Review and their time under Unattributed, above reviewed PRs with Coding, Pickup, Review and Total times, with Columns and Export CSV" />
  <figcaption>The PR drill-down sorted by Unattributed: PRs merged without a qualifying review show a dash under Pickup and Review.</figcaption>
</figure>

**When the clock starts.** An admin sets the [Cycle Time Start Signal](/insights-expo/expo-ai-adoption-settings#cycle-time-start-signal) in Settings → General, and it applies to every view:

* **In progress** — the earlier of the first commit and the linked issue moving to in progress. This measures "time from team-committed to shipped," but it relies on a consistent in-progress workflow in your issue tracker.
* **First commit** — the first commit on the PR.
* **Created** — when the PR was created.

While the setting is not set, Cycle Time starts at in progress. The headline Cycle Time card reads **Median work start to merge**.

**What the headline times.** The [Cycle Time Unit of Work](/insights-expo/expo-ai-adoption-settings#cycle-time-unit-of-work) setting decides whether the headline Cycle Time times each pull request (the default) or each resolved issue across its linked PRs. With **Issue**, Cycle Time ends when the issue is resolved, and the headline card reads **Median work start to issue resolved**. Breakdowns, drill-downs, and scores always use pull requests.

**Aggregation.** For a team or org row, GitKraken Insights takes the median across all PRs merged in the window. Drafts and PRs by excluded developers are left out (most bot accounts are excluded automatically).

**Outlier handling.** PRs whose work-start-to-merge duration exceeds the org's [Cycle Time Max Days](/insights-expo/expo-ai-adoption-settings#cycle-time-max-days) setting (default 30 days) are left out of Cycle Time. Within that cap, the metric does not trim outliers, but because it reports the median, one PR that sat open for 25 days moves it far less than it would move an average. The phase-breakdown views and the PR drill-down show the distribution so you can spot the long tail.

**Bot reviews.** By default, a review from a bot account counts as review activity, so it can end the Pickup phase. Turn on [Ignore reviews from bot accounts](/insights-expo/expo-ai-adoption-settings#ignore-reviews-from-bot-accounts) to stop bot reviews from stopping the clock on pickup, review, and cycle time.

### Why it matters

Cycle Time is the dashboard's most reliable operational health signal because it integrates many smaller signals into one number:

* Slow Cycle Time usually means slow review (Pickup phase) — the fix with the biggest payoff.
* Sometimes it means PRs are too big (Review phase) — fix with size discipline.
* Occasionally it means stalled work (Coding phase) — fix with WIP discipline.

Cycle Time also correlates strongly with team morale. Developers who watch their PR sit open for three days disengage from the work and from the review process. Tight Cycle Time correlates with higher engagement, less work in flight, and more shipped iterations.

For AI adoption analysis, Cycle Time is the metric most likely to show **adoption paying off in delivery**. AI tools reduce Coding phase first; they then enable smaller PRs which reduces Review phase. Watching Cycle Time trend down alongside Adoption Score trending up is the canonical "AI is delivering value" story.

### How to read it

Median industry numbers for typical web and services engineering teams:

| Total Cycle Time | Read it as |
| --- | --- |
| **< 24 hours** | Elite — fast, lean teams with strong review discipline |
| **1 – 2 days** | Strong — typical for high-performing engineering orgs |
| **2 – 4 days** | Fair — common in larger orgs; usually has Pickup or Review opportunity |
| **4 – 7 days** | Needs attention — investigate phase breakdown |
| **> 7 days** | Stuck — review process broken, or PRs structurally too big |

**Always read the phase breakdown** before drawing conclusions. The most common pattern in teams over three days is **Pickup dominates** — PRs sit waiting for review. The fix is almost always tighter review queue discipline, not faster coding.

### Where it appears

* **/insights/throughput-quality** — two side-by-side Cycle Time cards: left = trend stacked by phase, right = breakdown. The Issue Cycle Time card sits below them.
* **/insights/flow-delivery** — Team Lead Insights: Cycle Time as a KPI card, and the Cycle Time chart by phase on the Flow tab.
* **/insights/executive** — Cycle Time as a hero KPI and the Cycle Time breakdown card.
* **/insights/board-metrics** — the Cycle Time trend card. Its header shows the median and the PR count; select the PR count to open every PR behind the chart.
* **/insights/ai-impact** — the Cycle-Time-by-AI-Tier view.
* **/insights/teams** — expanded team rows show the trend with phase breakdown on the System Metrics tab. The team table itself has no Cycle Time column.
* **/insights/developers** — per-developer cycle time in the expanded developer detail.
* **PR drill-down** — click a point on a Cycle Time chart to open the PRs behind it: a scatterplot of each PR's cycle time, and a table with Coding, Pickup, Review, Unattributed, and Total columns. Use **Columns** to show or hide the rest. When the chart shows the previous period, a previous-period point opens that period's PRs.

On the breakdown cards, the **Total / Trend** switch in the card header toggles between totals and a trend over time. To change what the card breaks Cycle Time down by, open the card's **⋯** menu and choose **View**. Besides Team, Department, Business Unit, Change Size, Author, Repo, and Category, the picker offers **By Day Opened**, **By AI Tier**, **By Agent Adoption Score**, **By Agent Autonomy Score**, and **By AI Provider**, so you can compare Cycle Time across AI adoption levels.

<figure>
  <img src="/wp-content/uploads/gki-team-lead-flow-01-v5@2x.png" class="help-center-img img-bordered" alt="Team Lead Insights in GitKraken Insights with Timeframe, Granularity, Filters and Teams filters and Export CSV, KPI cards for Throughput (6.1, a count of merged PRs and direct commits per dev per week), Cycle Time (median work start to merge), Review Speed, PR First Pass, WIP, CFR (7.8%, 5 failed / 64 releases) and AI Adoption, an AI Insight banner summarizing cycle time, throughput and change failure rate, and on the Flow tab the Cycle Time chart by Coding, Pickup and Review beside the Changes Volume by Dimension chart in Total view: Opened, Merged and Direct commits bars for each team (Platform, Payments, Growth, Mobile) under Avg Delivered 6.1 /dev/wk, 195 opened, 155 merged and 90 direct, each card with a Total / Trend switch and a more- options menu" />
  <figcaption>Team Lead Insights — the Cycle Time chart by phase beside Changes Volume by Dimension, each with a Total / Trend switch.</figcaption>
</figure>

### Settings that affect it

* [**Cycle Time Start Signal**](/insights-expo/expo-ai-adoption-settings#cycle-time-start-signal) (Settings → General) — when the clock starts: In progress, First commit, or Created. Not set by default, which starts Cycle Time at in progress.
* [**Cycle Time Unit of Work**](/insights-expo/expo-ai-adoption-settings#cycle-time-unit-of-work) (Settings → General) — whether the headline Cycle Time times each pull request or each resolved issue. Default: Pull request.
* **Issue tracker integration** — the In progress start signal reads when a PR's linked issue moved to in progress. Without linked issues, work starts at the first commit.
* **Release detection** — without configured releases, the Deploy phase is omitted.
* [**Cycle Time Max Days**](/insights-expo/expo-ai-adoption-settings#cycle-time-max-days) (Settings → General) — PRs whose work-start-to-merge duration exceeds this many days are excluded. Default 30, range 1–365.
* [**Ignore reviews from bot accounts**](/insights-expo/expo-ai-adoption-settings#ignore-reviews-from-bot-accounts) (Settings → General) — when on, reviews from bot accounts do not stop the clock on pickup, review, or cycle time. Default off.

No other admin settings affect Cycle Time. It is a measurement.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Review Cycles](/insights-expo/expo-ai-adoption-flow-review-cycles) | The cause of long Review phase. Read together. |
| [First-Pass Rate](/insights-expo/expo-ai-adoption-flow-first-pass-rate) | Inversely correlated with Review phase length. |
| [WIP](/insights-expo/expo-ai-adoption-flow-wip) | High WIP often causes high Pickup (reviewers overloaded). |
| [Throughput](/insights-expo/expo-ai-adoption-output-throughput) | Outflow rate vs. how-long-each-takes. |
| [Lead Time for Changes](/insights-expo/expo-ai-adoption-dora-lead-time) | DORA's version, which runs on to the release that delivered the change. Similar but production-scoped. |

### How to improve it

Listed by impact, highest first:

* **Establish a weekly review-clearing ritual.** Fifteen minutes per week to walk through the oldest open PRs. Decide: ship, give a deadline, or close. This single habit fixes the Pickup phase on most teams within 2–4 weeks.
* **Right-size PRs.** Teams with high Cycle Time and high Review Cycles are over-batching. Aim for PRs that can be reviewed in 15 minutes.
* **Reduce reviewer load.** A team with 6 developers and 25 open PRs has a reviewer problem, not a coder problem.
* **Use AI to clean up small PRs.** Routine small PRs (formatting fixes, tests, docs) lend themselves to AI-assisted shipping. Moving the long tail of small work to AI-assisted flow can free reviewer time for substantive PRs.
* **Set a Cycle Time target as a team.** A target like "median Cycle Time under 36 hours" gives the team a shared goal and makes the metric actionable rather than diagnostic.

### Limitations and gotchas

* **The median can hide a long tail.** PRs longer than Cycle Time Max Days (default 30 days) are excluded, and the median keeps any one long-running PR from dominating the number. A handful of stuck PRs can still sit behind a healthy median, so open the PR drill-down to see them.
* **PRs merged without review have no Pickup or Review time.** Their open-to-merge time shows under Unattributed in the PR drill-down instead. Pair Cycle Time with [Review Cycles](/insights-expo/expo-ai-adoption-flow-review-cycles) and the Merged Without Review card on Throughput & Quality before you read a short Pickup phase as fast review.
* **Cycle Time cannot see commits before the PR is opened that are not on a tracked branch.** If a developer codes for three days on a local branch with no pushes, their Coding phase reads as \~0.
* **The In progress start signal requires discipline.** If your team marks issues "In Progress" inconsistently, the Coding phase becomes noisy. In that case, ask your admin to set the start signal to **First commit**.
* **Deploy phase is binary — present or absent.** Without release detection configured, the Deploy phase is just omitted.
* **Drafts and PRs by excluded developers are left out.** Most bot accounts are excluded automatically, so auto-merged dependency upgrades from bots do not pull down the median.

### Issue Cycle Time

The **Issue Cycle Time** card on Throughput & Quality measures the same journey from your issue tracker's side. It shows the median time from an issue's creation to its resolution, split into two phases:

* **Backlog** — from issue creation to its first In Progress transition.
* **Active** — from the first In Progress transition to resolution.

The card covers issues resolved in the window, grouped by the date they were resolved, across every connected tracker. Use the pickers to narrow it to one tracker (Jira, Linear, Azure Boards, or Azure DevOps Server) or one issue type (Bugs, Stories, Tasks, or Epics). The header shows the median and how many of the window's issues had an In Progress phase. Lower is better.

**Org-scoped.** The team and repository filters do not apply to Issue Cycle Time. It always covers the whole organization's issues.

<figure>
  <img src="/wp-content/uploads/gki-issue-cycle-time-card-01-v3@2x.png" class="help-center-img img-bordered" alt="The Issue Cycle Time card in GitKraken Insights showing a 55h median and 79 of 86 issues with an in-progress phase, All trackers and All issue types pickers, a chart of Backlog and Active time from Jun 1 to Jun 22 with a Claude Code rolled out to Mobile annotation, and when issues were last synced" />
  <figcaption>The Issue Cycle Time card, split into Backlog and Active time.</figcaption>
</figure>

### FAQ

**Q: Should I optimize toward "shortest possible Cycle Time"?**
A: No. Push toward "predictable, low-variance Cycle Time." A team with a tight 36–48 hour distribution is healthier than a team with a wild range from 4 hours to 4 days, even if the second team's average is lower.

**Q: Why does our team's Cycle Time look terrible the week before a release?**
A: Pre-release weeks accumulate PRs that were held back for the release. They merge in a burst, but their Coding and Pickup phases stretched across the embargo. This is a normal artifact.

**Q: Why does Cycle Time use the median rather than the average?**
A: The median tells you what is typical. A few PRs that sat open for weeks would pull an average far from what most PRs experience. To see those long-running PRs, open the PR drill-down from any Cycle Time chart point.

**Q: How is Cycle Time different from Lead Time?**
A: Cycle Time runs from work start to merge. [Lead Time](/insights-expo/expo-ai-adoption-dora-lead-time) (DORA) runs from work start to the release that delivered the change. Both follow the Cycle Time Start Signal setting. While it is not set, Cycle Time starts at in progress and Lead Time at first commit.
