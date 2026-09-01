---
title: Output & Throughput Metrics in GitKraken Insights
description: Learn about Output & Throughput metrics in GitKraken Insights, including Output Score, Throughput, Direct Commits, and Effort Score (Complexity).
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
integrations: [GitHub, GitHub Enterprise Server, GitLab, Bitbucket, Azure DevOps, Azure DevOps Server]
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: Output Score, Throughput, Direct Commits, Effort Score
    card_icon: package
    nav_category: metrics
    nav_label: Output & Throughput
    nav_order: 20
    nav_parent: expo-ai-adoption-metrics
    page_type: index
---
<kbd>Last updated: September 2026</kbd>


This family answers: **what is your team shipping, and how much of it is substantive work?**

It is the dashboard's "delivered output" lens — distinct from Flow (how fast?) and DORA (how reliably?). Output asks: of all the work that came out of this team this window, how does it stack up against itself over time and against the org?

---

## The metrics

| Metric | What it captures |
| --- | --- |
| [**Output Score**](#output-score) | The headline. Effort-weighted shipping rate per developer or per team. Replaces the older raw "Velocity" count. |
| [**Throughput**](#throughput) | Raw PR count — merged PRs per week, per developer or per team. |
| [**Direct Commits**](#direct-commits) | Commits pushed straight to a default branch without a PR. Counted as output with a configurable weight. |
| [**Effort Score (Complexity)**](#effort-score-complexity) | The 0.1–0.9 per-PR / per-commit complexity estimate that powers Output Score. |

---

## Why "Output Score" replaced raw "Velocity"

The traditional metric on the developer table used to be "Velocity" — a count of merged PRs per week. It treated a one-line documentation fix the same as a 2,000-line refactor.

That worked when teams were small and the work was uniform. It broke down as soon as:

* A senior IC was working on a quarter-long feature (one giant PR = velocity of 0).
* A junior was iterating on lots of small UI changes (lots of PRs = high velocity, low impact).
* AI tools started enabling developers to ship dramatically larger changes in dramatically less time.

Output Score fixes this by **weighting every PR, every direct commit, and every formal review by its estimated complexity** (Effort Score, 0.1 to 0.9). A 0.9-effort PR contributes 9× the score of a 0.1-effort PR. The result is a metric that scales with the _work_, not the _count_.

---

## How effort gets assigned

Every merged PR and every direct commit gets an Effort Score. There are two ways it can land:

1. `auto_effort_score` — an LLM-generated estimate based on the diff, title, and apparent intent. Computed by a background classifier worker. Locked to the discrete tier values {0.1, 0.3, 0.5, 0.7, 0.9}.
2. `effort_score` — an optional manual override by an admin, using the same five tier values.

The displayed effort is `COALESCE(effort_score, auto_effort_score, 0)` — manual override wins, then LLM, then zero.

The LLM rubric is intentionally LOC-blind. Two PRs of the same line count can have very different Effort Scores if one is a copy-paste config update and the other is a careful algorithmic change.

---

## How GitKraken Insights computes Output Score

```
Output Score (per developer)
  = SUM(PR effort)
  + DCWeight     × SUM(DC effort)
  + ReviewWeight × SUM(reviewed-PR effort)

Output Score (per team)
  = team total / active team developers
```

The three terms are:

* **PR effort** — the developer's own merged PRs in the window. Each PR's effort is `COALESCE(effort_score, auto_effort_score, 0)`.
* **DC effort** — the developer's direct commits in the window, weighted by `DCWeight` (default 0.5). Settable per-org as `direct_commit_weight`.
* **Reviewed-PR effort** — the effort of PRs the developer formally reviewed (approved or requested changes), weighted by `ReviewWeight` (default 0.5). Settable per-org as `review_weight`. Set to 0 to disable the review-credit term entirely.

When "Exclude Chore from Output Score" is on (the default), Chore-category items are subtracted from the effort sums — but **PR and commit counts shown in the cell stay raw**, always reflecting true shipping volume.

When the score is rate-normalized for AI Tier (Output Norm), the per-dev sum is divided by working weeks in the window and compared to the org P90 rate. The cohort and benchmark scope widen automatically for sub-week windows so the rate still has meaningful resolution.

---

## Read this family

Three habits:

1. **Read Output Score alongside Adoption Score.** High Output + low Adoption = the team ships well without AI (a baseline). High Output + high Adoption = AI is paying off. Low Output + high Adoption = adoption is happening but value isn't.
2. **Read team Output Score per active developer, not in absolute totals.** A team of 12 will always out-ship a team of 4. The per-dev rate is the apples-to-apples comparison.
3. **Don't read raw PR count when effort varies.** Use Output Score when comparing developers or teams; use Throughput when comparing _the same team over time_ (where effort distribution is roughly constant).

---

## Where this family shows up

* **/ai-adoption/developers** — Output Score column, with a clickable opened/merged/direct breakdown that drills into the PR panel.
* **/ai-adoption/teams** — Output Score per active developer column.
* **/ai-adoption/ai-tools-comparison** — cohort comparisons use Output Score as a key metric.
* **/ai-adoption/capex** — Output Score with CapEx / OpEx categorization for the capitalization split.
* **/ai-adoption/data-explorer** — per-PR and per-commit Effort Score visible in the underlying data.
