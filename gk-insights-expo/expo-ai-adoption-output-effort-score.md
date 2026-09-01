---
title: "Effort Score"
description: "How GitKraken Insights calculates and displays Effort Score."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for Effort Score"
    card_icon: package
    nav_category: metrics
    nav_label: "Effort Score"
    nav_order: 24
    nav_parent: expo-ai-adoption-output-metrics
    page_type: content
---
## Effort Score (Complexity)

> _A 0.1–0.9 estimate of how complex each PR and direct commit is. Five tiers, LLM-generated, manually overrideable. Powers Output Score._

**Family:** Output & Throughput · **Cadence:** Per PR / per commit, continuously classified · **Where it appears:** /ai-adoption/data-explorer, drill-down tables, /ai-adoption/developers (via Output Score)

### At a glance

Every merged PR and every direct commit in GitKraken Insights carries an Effort Score — a number between 0.1 and 0.9 representing the change's apparent complexity. There are five discrete tiers (Trivial / Light / Moderate / Substantial / Deep). The score is assigned by an LLM classifier that reads the diff, the title, and the PR description; it ignores raw line count.

Effort Scores are the per-item weights that feed [Output Score](#output-score). They are also exposed directly in tables and drill-downs as a sortable "Complexity" pill.

### The rubric

| Score | Tier | Examples |
| --- | --- | --- |
| **0.1** | Trivial | Typo fix, formatting-only changes, dependency version bump, lint config tweak |
| **0.3** | Light | Small bug fix, simple test addition, copy update with no logic, isolated config change |
| **0.5** | Moderate | Standard feature work, contained refactor, new endpoint, focused integration |
| **0.7** | Substantial | Multi-component feature, non-trivial design decision, cross-module refactor, schema migration |
| **0.9** | Deep | Architectural change, major migration, deep algorithmic work, new system bootstrap |

The rubric is intentionally **LOC-blind**. Two PRs of identical line count can have very different Effort Scores if one is a copy-paste config update and the other is a careful algorithmic change. A 50-line PR can be 0.9 (deep algorithmic) while a 2,000-line PR can be 0.1 (auto-generated dep bump).

### How GitKraken Insights calculates it

**Step 1 — auto_effort_score (LLM).** A background worker reads each PR or commit through the classifier. The classifier looks at the diff (what files, what changed), the PR or commit title, the PR description (if any), and the branch name (signals like `feat/`, `fix/`, `chore/`).

It outputs one of the five tier values. The classifier is rubric-versioned (`EffortScoreRubricVersion`), so when the rubric is updated, the system knows to reclassify.

**Step 2 — effort_score (manual override).** Admins can override `auto_effort_score` with their own assessment from /ai-adoption/data-explorer. Manual overrides win the COALESCE.

**Step 3 — the displayed value.** Every read uses `COALESCE(effort_score, auto_effort_score, 0)`. So manual > LLM > zero.

**The effort pass is independent of categorization.** Effort runs on its own state gate, decoupled from `classification_status`. Category overrides (e.g. marking a PR as Chore) don't blank out effort, and rubric bumps don't blank out the category.

**Retry behavior.** The effort classifier retries up to three times per PR on transient failures. If all three fail, the PR is left without an effort score until the next refresh sweep.

### Why it matters

Effort Score is the single mechanic that lets Output Score work. Without it, the dashboard would be back to raw PR counts and the well-known "Velocity is misleading" problem. With it: a senior IC shipping one 0.9-effort PR matches a junior shipping nine 0.1-effort PRs in Output Score; cross-team comparison stops penalizing teams whose work is structurally larger-grained; AI-enabled "one developer shipping 3× more substantive work" patterns show up in Output Score where they wouldn't in raw counts.

Effort Score is also a useful standalone metric. Look at the distribution across a team's PRs — a team that ships only 0.1–0.3 effort is probably over-fragmenting. A team that ships only 0.7–0.9 effort is probably not breaking work down enough for clean review.

### Where it appears

* **/ai-adoption/data-explorer** — PRs tab and Direct Commits tab. Sortable "Effort" column with a five-tier pill (Trivial → Deep).
* **PR drill-down tables** — when you click into a cycle-time or PR-volume chart point, the resulting table has an Effort column.
* **/ai-adoption/developers** — feeds into Output Score; not displayed per-PR in the developer table.
* **/ai-adoption/teams** — feeds into Output Score; not displayed per-PR in the team table.

### Settings that affect it

There are no admin-configurable settings for Effort Score directly. The rubric is versioned in code; rubric changes are rolled out via a coordinated reclassification pass. Manual override exists per item from /ai-adoption/data-explorer — that is effectively the per-PR setting.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Output Score](#output-score) | Sums Effort Score over a developer's merged PRs, direct commits, and formal reviews. |
| [Direct Commits](#direct-commits) | Each direct commit gets an Effort Score. |
| [Throughput](#throughput) | The count-only sibling. Throughput doesn't use Effort. |

### How to use Effort Scores

* **Audit miscategorized PRs.** Once a quarter, open /ai-adoption/data-explorer sorted by Effort descending. Skim the 0.9s. Are they actually deep work, or is the LLM over-scoring something? Same in reverse with 0.1s — anything that looks substantive at 0.1 deserves an override.
* **Watch the team distribution.** /ai-adoption/data-explorer filtered to a team shows the Effort histogram. Heavy left-skew (everything 0.1–0.3) suggests over-fragmentation. Heavy right-skew (everything 0.7+) suggests undersized review batches.
* **Don't game it.** Tempting as it is to manually mark every PR as 0.9 to inflate Output Score, this defeats the cross-team comparison. The defaults are calibrated; if your team's actual work is mostly 0.5, the score will reflect that, and that is fine.

### Limitations and gotchas

* **It is an LLM estimate, not a measurement.** Expect 70–80% directional accuracy; the rest is rubric-edge cases.
* **The classifier doesn't see review feedback.** A PR that _looked_ like 0.3 work but actually exposed an architectural issue during review still scores 0.3. The effort is the work as merged, not the discussion that produced it.
* **Bot PRs aren't classified.** They are excluded from Output Score anyway.
* **Rubric versioning means old PRs may rescore.** When the rubric is bumped (rare), a refresh sweep reclassifies recent PRs.

### FAQ

**Q: How do I override a wrong score?**
A: /ai-adoption/data-explorer → PRs tab → click the row → edit the Effort field. Saves immediately; takes effect on the next Output Score recomputation.

**Q: Why discrete tiers instead of a continuous 0–1 score?**
A: Discrete tiers reduce noise (the LLM can pick between 5 buckets more consistently than along a continuous slider) and they make manual overrides intuitive.

**Q: Is 0.9 the maximum? Can a really gnarly PR be 1.0?**
A: 0.9 is the maximum tier. We cap there because the linear sum (Output Score) becomes too sensitive to individual high-effort outliers above that.

**Q: Why ignore LOC?**
A: Lines changed correlates poorly with effort. A 5,000-line PR can be a near-trivial mechanical refactor; a 30-line PR can be a deep algorithmic change. Including LOC in the rubric made the score worse, not better, in our calibration.
