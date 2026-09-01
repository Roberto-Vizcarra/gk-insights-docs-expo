---
title: "AI-Assisted Percentage"
description: "How GitKraken Insights calculates and displays AI-Assisted Percentage."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for AI-Assisted Percentage"
    card_icon: currency-dollar
    nav_category: metrics
    nav_label: "AI-Assisted Percentage"
    nav_order: 52
    nav_parent: expo-ai-adoption-impact-cost-metrics
    page_type: content
---
## AI-Assisted Percentage

> _What share of your team's shipped changes had AI assistance — measured as a percentage of lines changed in AI-assisted PRs and commits._

**Family:** AI Impact & Cost · **Cadence:** Per window · **Where it appears:** /ai-adoption/ai-impact, /ai-adoption/executive, drill-downs

### At a glance

AI-Assisted % is the behavioral counterpart to [Agent Adoption Score](/insights-expo/expo-ai-adoption-agentic-metrics#agent-adoption-score). Where Adoption asks "is this developer using AI?", AI-Assisted % asks "did this developer use AI _on this specific change_?" The two diverge surprisingly often. A developer can be a heavy daily Claude user (high Adoption) and ship most of this week's PRs without touching it (low AI-Assisted on those changes).

The metric is line-weighted, so a 2,000-line AI-assisted refactor counts more than a 30-line AI-assisted typo fix.

### Formula

```
AI-Assisted % = (lines changed in AI-assisted PRs + commits) / (total lines changed) × 100%

  where AI-assisted = has AI co-author trailer OR
                      developer had AI events within the correlation window
                      of the change's lifecycle (default 60 minutes)
```

### How GitKraken Insights calculates it

**Per-item detection.** For each merged PR and direct commit, the backend computes `is_ai_assisted = true` if either:

1. **The commit has an AI co-author trailer** (reason: `co_author`), or
2. **The developer had AI events within the AI-to-commit correlation window of the change's lifecycle window** (reason: `ai_events`). The window defaults to 60 minutes and is configurable per org — see [AI-to-Commit Correlation Window](/insights-expo/expo-ai-adoption-settings#ai-to-commit-correlation-window). For PRs, the lifecycle window is `(first_commit_at, merged_at OR closed_at OR now)`. For direct commits, the correlation window applies on either side of the commit. AI events include `user_prompt`, `tool_result`, and `api_request` from any connected provider (Claude Code, Codex, Cursor).

The materialized flag (`pull_requests.is_ai_assisted` / `direct_commits.is_ai_assisted`) is computed by a continuous classifier worker. A live `EXISTS` fallback fires when the materialized flag is still NULL (typically for very recent items).

**Aggregation.** AI-Assisted % is computed as lines-changed-weighted, not as a simple count. So a team that ships 100 small AI-assisted PRs and one big non-AI-assisted PR can show 50% AI-Assisted (by lines) even though 99% of count is AI-assisted.

This weighting reflects "how much of the shipped _work_ had AI help" rather than "how many of the shipped _items_."

### Why it matters

AI-Assisted % is the closest thing the dashboard has to an _attribution_ metric. Adoption Score says "developers are using AI"; AI-Assisted % says "AI is touching the shipped work."

It is also a sanity check on the Adoption Score. A team with 90% Adoption Score and 10% AI-Assisted % has a problem — devs are using AI but not on their actual work. A team with 60% Adoption and 60% AI-Assisted has clean alignment.

For executives, AI-Assisted % is the metric to cite when answering "how integrated is AI into the actual code we ship?" The answer is more concrete than Adoption (which can include "I asked Claude a question once").

### How to read it

| AI-Assisted % | Read it as |
| --- | --- |
| **70%+** | Deep integration — AI is on most of the team's substantive work |
| **40–69%** | Solid — AI is integrated into about half of substantive shipping |
| **15–39%** | Emerging — AI is used on a subset of work, often the easier subset |
| **< 15%** | Limited — AI is being used adjacent to work but not on the work itself |

The right target for your team depends on what you ship. Heavy infrastructure and migration work resists AI assistance — even at 100% adoption, AI-Assisted % may cap at 50%. Greenfield product work can reach 80%+.

### Where it appears

* **/ai-adoption/ai-impact** — AI-Assisted Changes % card in the Productivity hero, which shows AI-assisted changed lines alongside total changed lines so you can see the ratio the percentage comes from. Also shows up in the AI Insight banner narrative.
* **/ai-adoption/executive** — AI-Assisted % is one of the headline KPIs.
* **PR drill-down tables** — each PR shows its AI-Assisted status with the reason (`co_author` or `ai_events`).

### Settings that affect it

* [**AI-to-Commit Correlation Window**](/insights-expo/expo-ai-adoption-settings#ai-to-commit-correlation-window) — how close AI activity must be to a change for that change to count as AI-assisted. Default 60 minutes. Changing it re-runs the association on existing data, so past percentages shift with it.
* The AI co-author trailer detection is fixed.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Agent Adoption Score](/insights-expo/expo-ai-adoption-agentic-metrics#agent-adoption-score) | The user-level adoption measure. AI-Assisted % is the work-level counterpart. |
| [Productivity Uplift](#productivity-uplift) | AI-Assisted % is a confirming signal — higher AI-Assisted → stronger uplift narrative. |
| [Output Score](/insights-expo/expo-ai-adoption-output-metrics#output-score) | Independent — Output Score doesn't filter by AI-assisted status. |

### How to improve it

* **Run a brown-bag on "show me how you actually use AI."** Many teams have devs using AI for _adjacent_ work (research, learning) but not for the work they ship. Demos help.
* **Pair Power Users with low-AI-Assisted devs.** Same intervention as for Adoption, but specifically targeting work-level integration.
* **Audit the gap.** Identify devs with high Adoption but low AI-Assisted %. Those are the ones to coach — they have the habit but aren't translating it.
* **Don't conflate AI-Assisted with "using AI co-author tags."** Many devs use AI without tagging. The temporal heuristic catches them; teams that primarily rely on tagging may show low AI-Assisted artificially.

### Limitations and gotchas

* **The temporal heuristic produces false positives and false negatives.** At the default 60-minute window, a dev who left Claude open in another window while writing non-AI-assisted code still gets the AI-assisted flag, and a dev who used AI two hours before committing doesn't. The heuristic is the best practical balance, not a measurement. If it over-attributes for your team, tighten the [correlation window](/insights-expo/expo-ai-adoption-settings#ai-to-commit-correlation-window).
* **Materialized flag lags by minutes to hours.** The classifier worker catches up continuously.
* **Co-author trailer detection requires the trailer.** Devs using AI without the `Co-authored-by` trailer rely entirely on the temporal heuristic.
* **Line-weighted means one big PR can dominate the metric.** A team's 50% AI-Assisted week might be one 3,000-line non-AI-assisted migration plus many smaller AI-assisted PRs. Look at distribution, not just the headline.

### FAQ

**Q: How is "AI-assisted" detected if the dev doesn't tag the commit?**
A: The backend looks for AI events (Claude / Codex / Cursor prompts, tool results, API requests) by that developer within the correlation window of the change's lifecycle — 60 minutes by default. If they prompted Claude within that window of committing, the commit is marked AI-assisted with reason `ai_events`.

**Q: A PR shows AI-Assisted = false but I know the dev used Claude. Why?**
A: Three usual causes: (1) the AI events happened outside the PR's lifecycle window by more than the correlation window allows, (2) the developer's email or login doesn't match between the AI events and the PR, (3) the materialized flag hasn't been computed yet.

**Q: Why line-weighted rather than count-weighted?**
A: Line weighting tracks "how much of the work shipped with AI help." Count weighting can be skewed by a flood of trivial PRs. Line weighting is the more honest "share of substantive output" answer.

**Q: Will lines from the AI's auto-generated comments count?**
A: Yes — all lines changed in the PR count, including ones the AI wrote that the human accepted. That is by design: the work was AI-assisted regardless of which lines came from whom.
