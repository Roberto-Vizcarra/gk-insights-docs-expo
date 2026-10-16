---
title: "Agent Autonomy Score"
description: "How GitKraken Insights calculates and displays the Agent Autonomy Score metric."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for Agent Autonomy Score"
    card_icon: user-check
    nav_category: metrics
    nav_label: "Agent Autonomy Score"
    nav_order: 12
    nav_parent: expo-ai-adoption-agentic-metrics
    page_type: content
---
## Agent Autonomy Score

> _A 0–100 measure of how often a developer runs AI through multi-step, tool-using sessions — sessions with 10+ tool calls._

**Family:** Adoption & Agentic · **Cadence:** Window-based · **Where it appears:** /ai-adoption/developers, /ai-adoption/ai-impact, /ai-adoption/executive

### At a glance

Where the [Agent Adoption Score](/insights-expo/expo-ai-adoption-agentic-adoption-score) measures _how consistently_ a developer uses AI, the Autonomy Score measures _how autonomously_. High autonomy means the developer is running AI in agentic mode — multi-step sessions where AI is calling tools, reading files, editing code, running commands. Low autonomy means AI is being used as a Q&A interface ("how do I X?"). Both are valid uses; tracking them separately surfaces a different kind of adoption depth.

### Formula

```
Agentic Score = min(Σ(tool autonomy score × tool weight), 100) × Company AI readiness

  Claude Code, Codex, OpenCode: min(intensity / OrgP90Intensity, 1.0) × 100
  Cursor:             Agent-mode usage score (0–100)
  Copilot:            agentic sessions (Agent OTel Data), or its
                      connection's agent signals vs org P90 (0–100)
```

For Claude Code, Codex, and OpenCode, `intensity` is a developer-level count of that tool's executions from sessions that ran 10 or more tools; each tool is normalized on its own. A tool's term is 0 when the org has no P90 intensity (no data). Each tool score is multiplied by that tool's [Provider Weight](/insights-expo/expo-ai-adoption-agentic-cursor-boost) (default 1.0).

### How GitKraken Insights calculates it

**What counts as an "agentic session."** We define an agentic session as one where the AI ran at least 10 tool executions (tool calls such as reading a file, editing code, running a command, or searching the web) within a single session. It's the total number of tool calls, not the number of _distinct_ tool types — ten uses of the same tool still qualifies. The 10-tool threshold is the heuristic that separates "I asked Claude a question and it called a tool to answer" from "Claude is doing real multi-step work."

**Providers included.** Claude Code, Codex, OpenCode, Cursor, and GitHub Copilot each contribute their own autonomy score. Claude Code, Codex, and OpenCode are scored from agentic sessions, as described below. OpenCode is available on request — contact your GitKraken account team. Cursor doesn't expose per-session tool calls, so it is scored from its Agent-mode usage instead: how many days the developer made Agent requests, and how many accepted AI-written lines landed per agent-active day. Copilot is scored from one source per developer, the same one that drives its Adoption Score. When its activity comes through [Agent OTel Data](/insights-expo/expo-ai-adoption-connect-otel#github-copilot-opentelemetry), it's scored from agentic sessions like Claude Code. Otherwise its [GitHub Copilot connection's](/insights-expo/expo-ai-adoption-connect-ai-tools#github-copilot) agent activity signals are each measured against your org's 90th percentile. The score hover labels the Copilot source **OTEL** or **PAT**.

**Intensity.** For each developer and each of Claude Code, Codex, and OpenCode, we total the tool executions from their agentic sessions over the window. The result is an intensity number per tool — higher means more agentic activity.

**Normalization.** We compute the **org-wide P90 intensity** across all active developers in the window. The developer's intensity is divided by the org P90 and capped at 1.0. If an admin has set an **Agentic Intensity Target** under [Benchmarks](/insights-expo/expo-ai-adoption-settings#benchmarks), the target replaces the org P90. This is the same normalization pattern as Adoption — the bar is your team, not an industry average. Developers with time off in part of the window are measured against a benchmark scaled to their working days.

**Combining tools.** Each tool's autonomy score is multiplied by its [Provider Weight](/insights-expo/expo-ai-adoption-agentic-cursor-boost) (default 1.0), and the weighted scores are added and capped at 100.

**Critical:** the org P90 is **always org-wide**, never team-filtered. A developer's Autonomy Score does not change when an admin toggles team filters.

**Maturity scaling.** The capped sum × Maturity Factor (labeled **Company AI Readiness (%)** in Settings) produces the final 0–100 score. At the default 100%, a developer at the org's P90 on agentic intensity in one tool scores 100; at 75%, the same developer scores 75.

### Why it matters

Agentic adoption is the deeper rollout signal. A team can have high Adoption (everyone using Claude as a Q&A interface) but low Autonomy (no one letting it run multi-step). Or the reverse — a small group running highly agentic sessions while the broader team hasn't started.

The most valuable AI productivity gains come from autonomous sessions. A developer asking Claude one question saves 5 minutes; a developer running Claude through a 30-minute agentic refactor saves 2 hours. Tracking autonomy separately lets you see whether your team is moving from the first pattern to the second — which is usually where ROI inflects.

### How to read it

| Range | Read it as |
| --- | --- |
| **60–100** | Strong — developer regularly runs AI through multi-step autonomous work |
| **25–59** | Fair — occasional agentic use; AI is partly an autonomous collaborator |
| **0–24** | Low — AI is being used mostly for Q&A, not autonomous tasks |

A team average above 40 is a strong signal that agentic workflows have taken root. Below 20 is "your team is using AI mostly to ask questions" — which is still useful but a leading indicator that you can extract more value.

### Where it appears

* **/ai-adoption/developers** — the Agent Autonomy Score column on the [Developers](/insights-expo/expo-ai-adoption-developers) table, next to Adoption; hover a score to see each tool's share. The expanded developer detail breaks down agentic activity by tool, session count, and tool diversity.
* **/ai-adoption/ai-impact** — deep-dive autonomy analysis: tools-per-session distribution, top tools by usage, ranking of high-autonomy developers.
* **/ai-adoption/executive** — trend line showing org agentic adoption over time.

<figure>
  <img src="/wp-content/uploads/gki-developers-table-01-v2@2x.png" class="help-center-img img-bordered" alt="The Developers page in GitKraken Insights with Timeframe, Granularity, Filters and Teams filters, KPI cards for Avg Adoption, Avg Autonomy, Avg Output and % Power Users, an Opportunity, Risers and Fallers banner, and the All Developers table listing ten developers with their Team, Tier, Agent Adoption Score, Agent Autonomy Score, Output Score /wk, Token Usage Value and $ / Output" />
  <figcaption>Developers page — KPI cards and the All Developers table with Tier, Agent Adoption Score, Agent Autonomy Score, Output Score /wk, Token Usage Value, and $ / Output columns.</figcaption>
</figure>

### Settings that affect it

* [**Maturity Factor**](/insights-expo/expo-ai-adoption-settings#maturity-factor) — labeled **Company AI Readiness (%)**; multiplies the final score (default 100%).
* [**Provider Weights**](/insights-expo/expo-ai-adoption-settings#provider-weights) — how much each tool's autonomy score counts (0–1, default 1.0). The same weights apply to the Adoption Score.
* [**Benchmarks**](/insights-expo/expo-ai-adoption-settings#benchmarks) — an optional **Agentic Intensity Target (per week)** that replaces the org P90 intensity.
* The **agentic threshold** (10 tools per session) is not currently configurable. Ask your account manager if you need it tunable.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Agent Adoption Score](/insights-expo/expo-ai-adoption-agentic-adoption-score) | Parallel adoption measure. Adoption = consistency. Agentic = depth of autonomous use. |
| [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier) | Agentic is one of three inputs into the composite Tier (default weight 0.2). See [How the metrics fit together](/insights-expo/expo-ai-adoption-agentic-metrics#how-the-metrics-fit-together) for the canonical composite formula and per-org configuration story. |
| [Productivity Uplift](/insights-expo/expo-ai-adoption-impact-cost-productivity-uplift) | Most productivity gains correlate with rising Autonomy, not Adoption. |

### How to improve it

* **Train the team on agentic prompts.** A 30-minute internal session on "give Claude a goal, let it run" can lift Autonomy faster than any other intervention.
* **Establish patterns for safe autonomous work.** "When can I let Claude refactor unattended?" If your team doesn't have an answer, Autonomy will stay low. Publishing a one-page playbook usually unblocks it.
* **Audit the Emerging Autonomy cohort.** Some developers are happy using AI as a search engine forever. That's fine — but flag the gap. If 70% of your team has Autonomy < 20, you're not extracting deep value from the tooling spend.
* **Pair an Autonomy Power User with a low-Autonomy developer.** Same intervention as for Adoption, but specifically targeting agentic workflows.

### Limitations and gotchas

* **The 10-tool threshold is heuristic.** A developer running 9-tool sessions all day will score lower than one running occasional 10-tool sessions.
* **Tool count ≠ value.** A 50-tool session that achieved nothing scores higher than a 10-tool session that shipped a feature. Use Autonomy alongside Output Score for the full picture.
* **Cursor and Copilot are scored differently.** Cursor's API doesn't expose per-session tool-call events, so the 10-tool session rule applies to Claude Code, Codex, OpenCode, and to Copilot only when its activity comes through Agent OTel Data. Cursor contributes through its Agent-mode usage score, and Copilot from the GitHub Copilot connection contributes through its agent activity measured against the org P90, each at its Provider Weight.
* **Org P90 moves with the cohort.** As your team matures, the bar rises.

### FAQ

**Q: Why 10 tools as the agentic threshold? Why not 5 or 20?**
A: 10 is the empirical knee in the distribution — at 10+ tools, sessions are reliably "AI doing real multi-step work" rather than "AI calling one tool to answer a question."

**Q: A developer has high Adoption but low Autonomy. Is that a problem?**
A: Not necessarily. They're using AI consistently but mostly as a Q&A tool. Whether that's a problem depends on the work.

**Q: Why isn't this counted toward Adoption?**
A: We want to separate "is the developer using AI?" (Adoption) from "is the developer using AI as an autonomous collaborator?" (Agentic). Conflating them loses information.
