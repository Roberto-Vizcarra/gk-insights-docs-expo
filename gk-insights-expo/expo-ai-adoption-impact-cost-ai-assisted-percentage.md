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
<kbd>Last updated: November 2026</kbd>

## AI-Assisted Percentage

> _What share of your team's shipped changes had AI assistance — measured as a percentage of lines changed in AI-assisted PRs and commits._

**Family:** AI Impact & Cost · **Cadence:** Per window · **Where it appears:** AI Impact, Executive View, drill-downs

### At a glance

AI-Assisted % is the behavioral counterpart to [Agent Adoption Score](/insights-expo/expo-ai-adoption-agentic-adoption-score). Where Adoption asks "is this developer using AI?", AI-Assisted % asks "did this developer use AI _on this specific change_?" The two often diverge. A developer can be a heavy daily Claude user (high Adoption) and ship most of this week's PRs without touching it (low AI-Assisted on those changes).

The metric is line-weighted, so a 2,000-line AI-assisted refactor counts more than a 30-line AI-assisted typo fix.

### Formula

```
AI-Assisted % = (lines changed in AI-assisted PRs + commits) / (total lines changed) × 100%

  where AI-assisted = has AI co-author trailer OR
                      the commit's author had AI events or Cursor activity
                      within the correlation window of a commit
                      (default 30 minutes; a PR counts when any of
                      its commits qualifies)
```

### How GitKraken Insights calculates it

**Per-item detection.** GitKraken Insights marks each merged PR and direct commit as AI-assisted when any of these is true:

1. **The commit has an AI co-author trailer**, or
2. **The commit's author had AI events within the AI-to-commit correlation window of the commit.** The window defaults to 30 minutes and is configurable per org from 1 to 60 minutes — see [AI-to-Commit Correlation Window](/insights-expo/expo-ai-adoption-settings#ai-to-commit-correlation-window). The window applies on either side of the commit. A PR is AI-assisted when this is true for any of its commits, each checked against that commit's author. AI activity elsewhere in the PR's life, away from its commits, does not count. AI events are prompts, tool results, and API requests (`user_prompt`, `tool_result`, and `api_request` in Data Explorer) from any connected AI tool that reports them, such as Claude Code or Codex.
3. **The commit's author had Cursor activity in that same window.** Cursor activity near the commit, or near one of the pull request's commits, counts as its own reason, so teams that use only Cursor get AI-assisted changes too.

**Aggregation.** AI-Assisted % weights each change by its lines changed instead of counting changes. So a team that ships 100 small AI-assisted PRs and one big non-AI-assisted PR can show 50% AI-Assisted (by lines) even though 99% of count is AI-assisted.

This weighting reflects "how much of the shipped _work_ had AI help" rather than "how many of the shipped _items_."

### Why it matters

AI-Assisted % is the closest thing the dashboard has to an _attribution_ metric. Adoption Score says "developers are using AI"; AI-Assisted % says "AI is touching the shipped work."

It is also a sanity check on the Adoption Score. A team with 90% Adoption Score and 10% AI-Assisted % has a problem — devs are using AI but not on their actual work. A team with 60% Adoption and 60% AI-Assisted has clean alignment.

For executives, AI-Assisted % is the metric to cite when answering "how integrated is AI into the actual code we ship?" The answer is more concrete than Adoption (which can include "I asked Claude a question once").

### How to read it

| AI-Assisted % | Read it as |
| --- | --- |
| **70%+** | Deep integration — AI is on most of the team's substantive work |
| **40% to 69%** | Solid — AI is integrated into about half of substantive shipping |
| **15% to 39%** | Emerging — AI is used on a subset of work, often the easier subset |
| **< 15%** | Limited — AI is being used adjacent to work but not on the work itself |

The right target for your team depends on what you ship. Heavy infrastructure and migration work resists AI assistance — even at 100% adoption, AI-Assisted % may cap at 50%. Greenfield product work can reach 80%+.

### Where it appears

* **AI Impact** — AI-Assisted Changes % card in the Productivity hero, which shows AI-assisted changed lines alongside total changed lines so you can see the ratio the percentage comes from. Also shows up in the AI Insight banner narrative.
* **Executive View** — AI-Assisted % is one of the headline KPIs.
* **PR drill-down tables** — each AI-assisted PR shows an **AI** badge. In Data Explorer's PR data, hover the **AI** badge to see the reason: an AI co-author in a commit message, AI tool activity near the commit or one of the PR's commits, or Cursor activity near the commit or one of the PR's commits. PRs that a coding agent created also carry an **Agent** badge next to the AI badge; hover it to see which agent, for example "Created by the Claude Code coding agent." A PR counts as agent-created when Copilot or Devin opened it on GitHub, or when its branch starts with `cursor/`, `claude/`, or `codex/` — then the author is the developer who ran the agent. The open pull requests table shows the same badge, and Data Explorer's PR data shows which agent created each PR. Devin is available on request — contact your GitKraken account team.

### Settings that affect it

* [**AI-to-Commit Correlation Window**](/insights-expo/expo-ai-adoption-settings#ai-to-commit-correlation-window) — how close an AI coding session must be to a commit for that work to count as AI-assisted. Labeled **AI Correlation Window (minutes)** in Settings → General. Default 30 minutes; range 1 to 60. Changing it recalculates AI-assisted metrics for past periods, so past percentages shift with it.
* The AI co-author trailer detection is fixed.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Agent Adoption Score](/insights-expo/expo-ai-adoption-agentic-adoption-score) | The user-level adoption measure. AI-Assisted % is the work-level counterpart. |
| [Productivity Uplift](/insights-expo/expo-ai-adoption-impact-cost-productivity-uplift) | AI-Assisted % is a confirming signal — higher AI-Assisted → stronger uplift narrative. |
| [Output Score](/insights-expo/expo-ai-adoption-output-output-score) | Independent — Output Score does not filter by AI-assisted status. |

### How to improve it

* **Run a team session on "show me how you actually use AI."** Many teams have devs using AI for _adjacent_ work (research, learning) but not for the work they ship. Demos help.
* **Pair Power Users with low-AI-Assisted devs.** Same intervention as for Adoption, but specifically targeting work-level integration.
* **Audit the gap.** Identify devs with high Adoption but low AI-Assisted %. Those are the ones to coach — they have the habit but are not applying it to their shipped work.
* **Do not conflate AI-Assisted with "using AI co-author tags."** Many devs use AI without tagging. The temporal heuristic catches them; teams that primarily rely on tagging may show low AI-Assisted artificially.

### Limitations and gotchas

* **The temporal heuristic produces false positives and false negatives.** At the default 30-minute window, a dev who left Claude open in another window while writing non-AI-assisted code still gets the AI-assisted flag, and a dev who used AI 2 hours before committing does not. The heuristic is the best practical balance, not a measurement. If it over-attributes for your team, tighten the [correlation window](/insights-expo/expo-ai-adoption-settings#ai-to-commit-correlation-window).
* **Very recent changes can take minutes to hours to be classified.** GitKraken Insights catches up continuously.
* **Co-author trailer detection requires the trailer.** Devs using AI without the `Co-authored-by` trailer rely entirely on the temporal heuristic.
* **Work authored by Devin itself is not counted.** Pull requests and commits that Devin authors are flagged as AI-authored, but they are not counted in AI-Assisted %, which measures work by your developers. The Devin connection is available on request — contact your GitKraken account team.
* **Line-weighted means one big PR can dominate the metric.** A team's 50% AI-Assisted week might be one 3,000-line non-AI-assisted migration plus many smaller AI-assisted PRs. Look at distribution, not just the headline.

### FAQ

**Q: How is "AI-assisted" detected if the dev does not tag the commit?**
A: GitKraken Insights looks for AI events (prompts, tool results, and API requests from any connected AI tool) or Cursor activity by the commit's author within the correlation window of the commit — 30 minutes by default. If they prompted Claude within that window of committing, the commit is marked AI-assisted, with AI tool activity as the reason; Cursor activity in the window marks it AI-assisted with Cursor activity as the reason. A PR is marked AI-assisted when any of its commits qualifies.

**Q: A PR has no AI badge, but I know the dev used Claude. Why?**
A: Three usual causes: (1) the AI events happened further from every one of the PR's commits than the correlation window allows, for example while the PR was in review, (2) the developer's email or login does not match between the AI events and the PR, (3) the PR is very recent and GitKraken Insights has not classified it yet.

**Q: Why line-weighted rather than count-weighted?**
A: Line weighting tracks "how much of the work shipped with AI help." Count weighting can be skewed by a flood of trivial PRs. Line weighting is the more honest "share of substantive output" answer.

**Q: Will lines from the AI's auto-generated comments count?**
A: Yes — all lines changed in the PR count, including ones the AI wrote that the human accepted. That is by design: the work was AI-assisted regardless of which lines came from whom.
