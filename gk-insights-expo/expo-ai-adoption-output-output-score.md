---
title: "Output Score"
description: "How GitKraken Insights calculates and displays Output Score."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for Output Score"
    card_icon: package
    nav_category: metrics
    nav_label: "Output Score"
    nav_order: 21
    nav_parent: expo-ai-adoption-output-metrics
    page_type: content
---
<kbd>Last updated: November 2026</kbd>

## Output Score

> _Effort-weighted shipping rate per developer or per team, per working week. Adds up the complexity of every merged PR, direct commit, and formally reviewed PR in the window, weighting commits and reviews at half the rate of authored PRs by default, then divides by working weeks with PTO excluded._

**Family:** Output & Throughput · **Cadence:** Window-based · **Where it appears:** /insights/developers, /insights/teams, /insights/ai-tools-comparison, /insights/capex

### At a glance

Output Score replaces the older "Velocity" count on the developer table with a metric that scales with the _work_, not the _count_. Every merged PR and every direct commit carries an Effort Score (0.1 to 0.9, five discrete tiers). Output Score adds up the effort across the window, expresses it per working week, and credits reviewers for the PRs they formally reviewed — so a developer shipping one big architectural PR can match a developer shipping ten small fixes, and a senior who spent the window unblocking reviews still appears as a contributor.

### Formula

```
Output Score (per developer, per working week)
  = ( MergedPRWeight × SUM(PR effort)
    + DCWeight       × SUM(DC effort)
    + ReviewWeight   × SUM(reviewed-PR effort) )
    ÷ working weeks

Output Score (per team, per working week)
  = team effort ÷ members' working weeks, pooled
```

Where:

* **Working weeks** = the weeks in the window minus the developer's PTO weeks.

* **PR effort** = the effort score of each PR the developer authored and merged in the window, summed (a manual override if one is set, otherwise the automatic score, otherwise 0). A merged PR's effort is shared among the developers who authored its commits.
* **DC effort** = the same effort score, summed over direct commits the developer authored in the window.
* **Reviewed-PR effort** = the same effort score, summed over PRs where the developer left a formal review (**Approve** or **Request changes**). Each PR counts once per reviewer.
* **MergedPRWeight** = Merged PR Weight setting (default 1; range 0 to 1). Set to 0 to score on direct commits and reviews only.
* **DCWeight** = Direct Commit Weight setting (default 0.5; range 0 to 1).
* **ReviewWeight** = Review Weight setting (default 0.5; range 0 to 1). Set to 0 to turn off the review-credit term entirely.
* **Chore filtering** — when "Exclude Chore from Output Score" is on (default), Chore-category items drop out of the effort sums (but not the displayed counts).

### How GitKraken Insights calculates it

**Effort scores.** Every merged PR and every direct commit gets an Effort Score between 0.1 and 0.9 in five discrete tiers:

| Score | Tier | Roughly |
| --- | --- | --- |
| 0.1 | Trivial | Typo, formatting, dependency bump |
| 0.3 | Light | Small bug fix, simple test addition |
| 0.5 | Moderate | Standard feature work, focused refactor |
| 0.7 | Substantial | Multi-component feature, non-trivial design |
| 0.9 | Deep | Architectural change, major migration |

An AI model assigns each effort score automatically, and the score can be overridden manually. When an item has a manual override, Insights shows and uses that value.

**Per-developer rate.** For each developer in the window:

1. Sum effort across their authored merged PRs (drafts and bots excluded; optionally Chore-excluded). A merged PR's effort is shared among the developers who authored its commits.
2. Sum effort across their authored direct commits (bots and empty commits excluded; optionally Chore-excluded).
3. Sum effort across PRs they formally reviewed (**Approve** or **Request changes**; the same PR can credit multiple reviewers).
4. Multiply the PR sum by **Merged PR Weight**, the direct-commit sum by **Direct Commit Weight**, and the review sum by **Review Weight**.
5. Add the three terms.
6. Divide by the developer's working weeks in the window (PTO excluded) to get the per-week rate, shown in effort points as **N pts/wk**. It is not a PR count, so it is not expected to match [Throughput](/insights-expo/expo-ai-adoption-output-throughput), which counts merged PRs and direct commits.

Hover a developer's Output Score on the Developers table to see this calculation for that developer: each effort term, the working weeks minus PTO, the per-week rate, and how it's normalized to a 0 to 100 scale for Output Norm.

> **Why "Reviewed" here does not match GitHub's review count.** Insights credits a review only when a developer left a formal **Approve** or **Request changes** (plain comments do not count), and credits each PR once per reviewer no matter how many reviews they left, excluding self-reviews and bots. GitHub's own "reviews" number is broader, so the two will legitimately differ.

**Team aggregate.** For team rows on the Teams page, the score is the team's effort per working week, pooled across its members' PTO-adjusted working weeks. Members who only reviewed count too. This per-week rate is what makes team scores comparable across team sizes.

**The Chore asymmetry.** When the "Exclude Chore" toggle is on (default), Chore-category items are excluded from the **effort sums** — but the **counts shown in the cell stay raw**. So a developer who shipped 5 PRs and 12 direct commits will always show "5 merged · 12 direct" in the breakdown, even if half were chores. Because the counts stay raw, a developer whose window was mostly chores still appears in the developer table.

**Sub-week windows.** For windows shorter than 7 days, each developer's working weeks have a minimum, and the org P90 benchmark widens to the trailing 7 days so the rate keeps meaningful resolution. Output Score is already a per-week rate; the widened benchmark applies to its normalized variant (**Output Norm**, used by AI Tier). The benchmark is never narrowed by the team filter, but filtering by Repositories or Organizations computes it for just that scope.

### Why it matters

Raw PR counts mislead. A team that shipped one 0.9-effort architectural PR and a team that shipped nine 0.1-effort lint fixes should not score the same on shipping. Output Score puts them on a like-for-like footing.

The review-credit term matters for the same reason. A senior engineer who spent the window doing high-quality reviews on substantive PRs has produced real value; the older Velocity metric ignored that work entirely.

Output Score also feeds the [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier) composite. Normalized to org P90 and scaled by Maturity Factor (a derived value called **Output Norm**), it becomes the "shipping" leg of the composite alongside Agent Adoption and Agent Autonomy.

For leaders, the most useful reading is **Output Score over time alongside Adoption Score over time**. AI rollouts almost always show Adoption rising first, then Output rising 4 to 12 weeks later.

### How to read it

Output Score is a rate per working week, so values are comparable across windows of different lengths and across team sizes. Some rough reads at the per-developer level:

| Value per week | Read it as |
| --- | --- |
| **≥ 5** | High — multiple substantial-or-deeper PRs/commits/reviews each week |
| **2 to 4.9** | Solid — typical shipping pattern for an engaged developer |
| **0.5 to 1.9** | Light — small contributions, possibly maintenance work |
| **< 0.5** | Minimal — investigation worthwhile (big WIP PR not yet merged? infrastructure work?) |

For teams, compare the per-week rate directly. A team averaging an Output Score of 3.5 per week is in good shape.

### Where it appears

* **/insights/developers** — main **Output Score (pts/wk)** column on the [Developers](/insights-expo/expo-ai-adoption-developers) table. Hover the score to see the calculation. The breakdown under it reads "X opened · Y merged · Z direct · W reviewed"; click it to open the Output Score drill-down.
* **/insights/teams** — **Output Score (pts/wk)** column, sortable. The breakdown under each score reads "from X merged · Y direct · Z review credits". Hover a team's score to see its merged PRs, direct commits, review credits, members, and working weeks. Each reviewer earns one review credit per PR, so a PR reviewed by two teammates counts twice and review credits can exceed the number of merged PRs. Click the breakdown to open the team's Output Score drill-down.
* **/insights/ai-tools-comparison** — cohort comparison uses Output Score as a key metric.
* **/insights/capex** — Output Score with CapEx vs OpEx classification for the capitalization grid.

<figure>
  <img src="/wp-content/uploads/gki-developers-table-01-v6@2x.png" class="help-center-img img-bordered" alt="The Developers page in GitKraken Insights with Timeframe, Granularity, Filters and Teams filters, KPI cards for Avg Adoption, Avg Autonomy, Avg Output (2.02 pts/wk) and % Power Users (+10.0 pp from 40.0%), each with its change from the previous period and the first three with their Org P90 scoring thresholds, a banner whose Opportunity block reads that everyone in view is at Regular adoption or above, beside Risers and Fallers, and the first five rows of the All Developers table, each name with a pts vs prev trend chip, with their Team, Tier, Agent Adoption Score, Agent Autonomy Score, Output Score (pts/wk) with an opened, merged, direct and reviewed breakdown, Token Usage Value and $ / Output" />
  <figcaption>Developers page — KPI cards and the All Developers table with Tier, Agent Adoption Score, Agent Autonomy Score, Output Score (pts/wk), Token Usage Value, and $ / Output columns.</figcaption>
</figure>

**The Output Score drill-down.** Click a developer's Output Score breakdown on the Developers table to open the work behind the score, for any connected Git provider. The drill-down has three tabs, each with search, filters, a **Columns** menu, and **Export CSV**:

* **Authored PRs** — the pull requests the developer opened and merged in the period, with a summary of how many were opened and merged.
* **Direct Commits** — the commits the developer pushed without a pull request.
* **Reviewed** — the merged pull requests the developer formally reviewed, with an **Outcome** of **Approved** or **Changes requested** for each. Only approvals and change requests on pull requests merged in the range count toward Output Score.

If a developer has no authored changes in the period, the drill-down says the score comes from reviews.

<figure>
  <img src="/wp-content/uploads/gki-output-score-drilldown-01-v3@2x.png" class="help-center-img img-bordered" alt="The Output Score drill-down for Sarah Chen (Jun 1 - Jun 28, 2026) on the Authored PRs (24) tab, beside Direct Commits (10) and Reviewed (8), with a search box, State and Category filters, a Columns button, the summary &quot;24 opened - 20 merged in this period&quot;, a table of her pull requests with Title, Author, State, Category, Size, Effort, Merged, Coding and Pickup columns, AI badges on AI-assisted PRs, pagination and Export CSV" />
  <figcaption>Output Score drill-down — the Authored PRs tab.</figcaption>
</figure>

<figure>
  <img src="/wp-content/uploads/gki-output-score-drilldown-commits-01-v3@2x.png" class="help-center-img img-bordered" alt="The Output Score drill-down for Sarah Chen on the Direct Commits (10) tab: a search box, a Category filter, Columns, and a table of ten direct commits, each with its repository and short SHA, Author, Category, Size, Effort and lines added and removed (that column cut at the drawer's edge), AI badges on AI-assisted commits, and Export CSV" />
  <figcaption>Output Score drill-down — the Direct Commits tab.</figcaption>
</figure>

<figure>
  <img src="/wp-content/uploads/gki-output-score-drilldown-reviewed-01-v3@2x.png" class="help-center-img img-bordered" alt="The Output Score drill-down for Sarah Chen on the Reviewed (8) tab: a search box, a Category filter, Columns, and a table of the merged pull requests she reviewed, authored by alexjohnson and davidkim, with State, Category, Size, Effort (higher for the larger L changes, lowest for the XS one), Merged and an Outcome column of Approved or Changes requested, and Export CSV" />
  <figcaption>Output Score drill-down — the Reviewed tab, with each review's outcome.</figcaption>
</figure>

**A team's Output Score drill-down.** On the Teams table, click the "from X merged · Y direct · Z review credits" breakdown under a team's score to open **Output Score —** _team name_. It has the same **Authored PRs**, **Direct Commits**, and **Reviewed** tabs, listing the team's work in the period. The **Effort** column shows the part of each change's effort credited to the team: the authors' share of an authored PR and each reviewer's share of a review. Expand a pull request to see who earned that effort, for example "Sarah Chen: 0.70 credited effort". Work by developers whose visibility is **Hidden** still counts toward the team's score. The drill-down totals it in a **Not listed for privacy** line instead of listing it.

<figure>
  <img src="/wp-content/uploads/gki-team-output-score-drilldown-01.png" class="help-center-img img-bordered" alt="The Output Score drill-down for the Platform team (Jun 1 - Jun 28, 2026) on the Authored PRs tab, beside Direct Commits and Reviewed, with search, State and Category filters and Columns; the first pull request is expanded to show its timeline and Sarah Chen: 0.70 credited effort, above a table of Title, Author, State, Category, Size, Effort, Merged, Coding and Pickup, pagination and Export CSV" />
  <figcaption>A team's Output Score drill-down, with a pull request expanded to show the effort credited to its author.</figcaption>
</figure>

### Settings that affect it

* [**Merged PR Weight**](/insights-expo/expo-ai-adoption-settings#merged-pr-weight) — scales authored-PR contribution (default 1). Set to 0 to score on direct commits and reviews only.
* [**Direct Commit Weight**](/insights-expo/expo-ai-adoption-settings#direct-commit-weight) — scales direct-commit contribution (default 0.5).
* [**Review Weight**](/insights-expo/expo-ai-adoption-settings#review-weight) — scales review-credit contribution (default 0.5). Set to 0 to disable the review-credit term.
* [**Exclude Chore from Output Score**](/insights-expo/expo-ai-adoption-settings#exclude-chore-from-output-score) — strips Chore effort from the sums (default on). Which categories count as Chore is set in [work categories](/insights-expo/expo-ai-adoption-settings#work-categories).
* [**Exclusion rules**](/insights-expo/expo-ai-adoption-settings#exclusion-rules) — leave PRs that match a branch pattern (for example, release merges into `master`) out of Output Score entirely. Matching PRs add no effort, are left out of the org benchmark, and earn their reviewers no review credit; they still count toward the author's merged pull requests. No rules by default.
* [**Maturity Factor**](/insights-expo/expo-ai-adoption-settings#maturity-factor) — does _not_ affect raw Output Score, but does affect **Output Norm** (the variant that feeds into AI Tier).

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Effort Score](/insights-expo/expo-ai-adoption-output-effort-score) | The per-PR / per-commit weights that Output Score sums over. |
| [Throughput](/insights-expo/expo-ai-adoption-output-throughput) | Raw count of merged PRs and direct commits, for same-team trend reading. Output Score is for cross-team comparison. |
| [Direct Commits](/insights-expo/expo-ai-adoption-output-direct-commits) | The direct-commit term of the formula. |
| [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier) | Output Norm is the Output leg of the composite. |
| [CapEx / OpEx Split](/insights-expo/expo-ai-adoption-impact-cost-capex-opex) | Categorizes the same effort sums for capitalization accounting. |

### How to improve it

* **Right-size PRs.** A team consistently shipping 0.1 to 0.3 effort PRs is over-fragmenting.
* **Calibrate effort scores.** If the automatic effort scores are consistently wrong for your team's domain, have a senior engineer override the most miscategorized PRs.
* **Audit Chore exclusion behavior.** On most teams the default reflects the right definition of "real output." For an SRE team where dependency upgrades _are_ the job, turn it off.
* **Track Output trajectory, not the absolute number.** Output Score that climbed from 4 → 8 per dev over a quarter is a stronger signal than a static 9 from a team that has been at 9 for a year.
* **Pair Output review with WIP review.** A team with low Output may have a lot of work _in progress_ that has not merged.

### Limitations and gotchas

* **Effort is an LLM estimate, not a measurement.** Manual overrides exist for the most miscategorized cases.
* **The score is a weekly rate, so short windows are noisy.** A 14-day and a 90-day Output Score are on the same per-week scale, but a short window rests on fewer weeks of work, so one large PR moves it more.
* **Chore filtering changes the score, not the counts.** This is intentional but trips people up.
* **Merged PR Weight, Direct Commit Weight, or Review Weight at 0 hides that term entirely from the score.**
* **Drafts and most bots are excluded.**

### FAQ

**Q: Why was Velocity replaced with Output Score?**
A: Velocity treated all PRs as equal-weight and ignored review work. That worked when PRs were uniform in size and scope. It stopped working as AI tooling enabled developers to ship dramatically larger changes. Output Score scales with effort, not count, and credits reviewers for substantive review work.

**Q: How is Effort Score assigned?**
A: An AI model reads each PR or commit diff and assigns one of five effort tiers. It ignores lines of code and focuses on the change's apparent scope, intent, and complexity. See [Effort Score](/insights-expo/expo-ai-adoption-output-effort-score).

**Q: Why is my Output Score 0 even though I shipped a big PR?**
A: Three common causes: (1) the PR has not been given an effort score yet, (2) the PR's category is Chore and the "Exclude Chore" toggle is on, or (3) the PR merged outside the window you selected.

**Q: Why are direct commits and reviews weighted lower than authored PRs?**
A: Empirically, direct commits trend toward smaller, less-reviewed work, and a single review takes less effort than authoring the same PR. The 0.5 defaults reflect those averages. To change them, adjust [Direct Commit Weight](/insights-expo/expo-ai-adoption-settings#direct-commit-weight) and [Review Weight](/insights-expo/expo-ai-adoption-settings#review-weight).

**Q: Can I see what effort score a specific PR got?**
A: Yes. In **Data Explorer** (`/insights/data-explorer`), the PRs tab shows the Effort column for every PR, sortable.
