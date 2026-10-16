---
title: "Agent Adoption Score"
description: "How GitKraken Insights calculates and displays the Agent Adoption Score metric."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for Agent Adoption Score"
    card_icon: user-check
    nav_category: metrics
    nav_label: "Agent Adoption Score"
    nav_order: 11
    nav_parent: expo-ai-adoption-agentic-metrics
    page_type: content
---
## Agent Adoption Score

> _A 0–100 measure of how consistently and how deeply a developer is using AI coding tools (Claude Code, Codex, Cursor, GitHub Copilot, OpenCode) over the selected window._

**Family:** Adoption & Agentic · **Cadence:** Window-based (refreshes with date range) · **Where it appears:** /developers, /teams, /comparison, /executive, /ai-impact

### At a glance

The Agent Adoption Score is the dashboard's headline answer to "is this developer using AI?" It's a single number from 0 to 100 that combines four dimensions of usage — daily consistency, time-of-day spread, prompt volume, and output tokens — across all the AI tools the developer has access to. Higher means more consistent, deeper use; lower means sporadic or shallow use. It's normalized against your organization's own 90th-percentile usage, so the bar is your team, not some industry average.

### Formula

```
Adoption Score = min(Σ(tool score × tool weight), 100) × Company AI readiness

  where tool score  = four-factor score for Claude Code, Codex, Cursor,
                      and OpenCode; for Copilot, the four factors (Agent
                      OTel Data) or its connection signals vs org P90
        tool weight = Provider Weight for that tool (default 1.0)
        Company AI readiness = Maturity Factor (default 100%)
```

The four factors per provider, and their default weights in the 100-point scale, are:

* **Daily Regularity (35%)** — weekdays with ≥1 prompt ÷ effective weekdays. This factor is a straight share of your working days — it is _not_ normalized to the org P90.
* **Hourly Spread (25%)** — avg distinct hours per active day with ≥2 prompts ÷ org P90 spread
* **Prompt Depth (25%)** — prompts per active weekday ÷ org P90 prompts
* **Token Output (15%)** — output tokens per active weekday ÷ org P90 tokens

### How GitKraken Insights calculates it

**Step 1 — Per-provider scoring.** For each provider (Claude Code, Codex, Cursor, and OpenCode), we compute four normalized factors. GitHub Copilot is scored from one source per developer. When its activity comes through [Agent OTel Data](/insights-expo/expo-ai-adoption-connect-otel#github-copilot-opentelemetry), it gets the same four factors as the other tools. Otherwise it's scored from the [GitHub Copilot connection's](/insights-expo/expo-ai-adoption-connect-ai-tools#github-copilot) own signals (active days, interactions, and lines added), each measured against your org's 90th percentile for that signal. The score hover and breakdown label which Copilot source was used: **OTEL** or **PAT**. OpenCode is available on request — contact your GitKraken account team.

* **Daily Regularity:** the fraction of effective weekdays in the window where the developer sent at least one prompt. Effective weekdays subtracts weekdays before the provider was instrumented (Claude Code data starts March 5, 2026) and subtracts PTO weekdays. Unlike the other three factors, this is a raw fraction of working days — it is not benchmarked against the org P90.
* **Hourly Spread:** the average number of distinct hours per active weekday where the developer ran ≥2 prompts. Captures "AI is integrated into their workday" vs. "AI is one batch at the end of the day."
* **Prompt Depth:** total prompts divided by active weekdays.
* **Token Output:** total output tokens divided by active weekdays. A proxy for the _depth_ of each AI interaction.

The last three factors are each divided by the corresponding **org P90 cap** (the 90th-percentile value across all active developers in the window) and capped at 1.0, so a developer at or above the org's 90th percentile on a factor scores the max contribution for it. If an admin has set a target for that factor under [Benchmarks](/insights-expo/expo-ai-adoption-settings#benchmarks), the target replaces the org P90. (Daily Regularity is already a 0–1 share of working days, so it isn't P90-normalized.)

The four normalized factors are then weighted — Daily Regularity 35%, Hourly Spread 25%, Prompt Depth 25%, Token Output 15% — and summed to a 0–100 provider score.

**Step 2 — Weight each provider score.** Each provider's score is multiplied by that provider's [Provider Weight](/insights-expo/expo-ai-adoption-agentic-cursor-boost) (default 1.0). We don't average providers: a developer using Claude Code and Codex gets credit from both, so using multiple tools isn't penalized.

**Step 3 — Add and cap.** The weighted provider scores are added together and the total is capped at 100 before maturity scaling.

**Step 4 — Scale by Maturity Factor.** The final value is multiplied by the org's Maturity Factor, labeled **Company AI Readiness (%)** in Settings (default 100%). At the default setting, a developer at the org's P90 on every factor scores 100. Lower the setting to leave headroom: at 75%, the same developer scores 75, below the Power User band (≥80).

**Window scope.** Everything is scoped to the date range selected at the top of the page. The org P90 caps are recomputed _for the same window_, so the ceiling moves with the cohort.

### Why it matters

Adoption is the leading indicator of an AI rollout. Output follows adoption with a 4–12 week lag — first developers have to integrate the tool, then they have to get good at it, _then_ they ship faster. Reading the Adoption Score in isolation tells you whether your rollout is moving. Reading it alongside Output Score over time tells you whether the rollout is _paying off_.

The score is **descriptive, not evaluative**. A senior developer working on a quarter-long migration may show as Emerging because the work doesn't suit AI tooling. A junior on UI changes may be a Power User. Neither is a judgment of skill. The aggregated score across a team or a cohort is the more useful read.

### How to read it

| Range | Read it as |
| --- | --- |
| **80–100** | Power User — AI is integrated into daily work, used across multiple sessions per day |
| **55–79** | Regular — solid, consistent adoption; AI is a routine part of their workflow |
| **25–54** | Explorer — using AI but not yet daily, or shallow integration |
| **0–24** | Emerging — minimal or no AI usage in the window |

These tier bands are fixed reference points — they do **not** move with the Maturity Factor. What moves is the _score_: the Maturity Factor sets the ceiling (Maturity Factor × 100). At the default 100% the highest achievable score is 100. Set it below 80% and the highest achievable score falls below the Power User band (≥ 80) — at 75% the ceiling is 75, the top of Regular — until you raise it again. See [Maturity Factor](/insights-expo/expo-ai-adoption-agentic-maturity-factor) for how to size it.

A team average of 50–65 means a healthy mix with most developers in Explorer/Regular. A team average approaching the ceiling (100 at the default, or Maturity Factor × 100 if you've lowered it) means broad, deep adoption across the team. A team average below 30 is your "rollout has stalled" signal.

### Where it appears

* **/developers** — primary surface. The Agent Adoption Score column on the [Developers](/insights-expo/expo-ai-adoption-developers) table; hover a score to see each tool's contribution. Expand a developer to see the four-factor breakdown and the per-provider contribution. The **Agent Adoption** tab shows one activity-trend card per AI tool, benchmarked against the org's developers who use that same tool.
* **/teams** — the Agent Adoption Score column shows the team average; expand a team to see per-developer scores. The team **Tier** blends Adoption with Autonomy and Output — see [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier).
* **/comparison** — distribution by cohort (e.g. by team, by AI tool, by primary model).
* **/executive** — hero KPI card "AI Adoption %" (percentage of devs at score ≥ 25) and a trend line.
* **/ai-impact** — used as the cohort dimension for the productivity uplift analysis.

<figure>
  <img src="/wp-content/uploads/gki-developers-table-01-v2@2x.png" class="help-center-img img-bordered" alt="The Developers page in GitKraken Insights with Timeframe, Granularity, Filters and Teams filters, KPI cards for Avg Adoption, Avg Autonomy, Avg Output and % Power Users, an Opportunity, Risers and Fallers banner, and the All Developers table listing ten developers with their Team, Tier, Agent Adoption Score, Agent Autonomy Score, Output Score /wk, Token Usage Value and $ / Output" />
  <figcaption>Developers page — KPI cards and the All Developers table with Tier, Agent Adoption Score, Agent Autonomy Score, Output Score /wk, Token Usage Value, and $ / Output columns.</figcaption>
</figure>

### Settings that affect it

* [**Maturity Factor**](/insights-expo/expo-ai-adoption-settings#maturity-factor) — labeled **Company AI Readiness (%)** in Settings → General (default 100%). Multiplies the final score. Lowering it lowers the tier ceiling for everyone.
* [**Provider Weights**](/insights-expo/expo-ai-adoption-settings#provider-weights) — how much each connected provider counts relative to the others (0–1, default 1.0). Editable in Settings → General.
* [**Benchmarks**](/insights-expo/expo-ai-adoption-settings#benchmarks) — optional admin-set targets for Hourly Spread, Prompt Depth, and Token Output that replace the org P90 for those factors.

The four-factor weights (Daily Regularity 35%, Hourly Spread 25%, Prompt Depth 25%, Token Output 15%) are fixed defaults and aren't editable in Settings. The "How is the Agent Adoption Score calculated?" card on /developers shows them.

### Related metrics

| Metric | Relationship |
| --- | --- |
| [Agent Autonomy Score](/insights-expo/expo-ai-adoption-agentic-autonomy-score) | Parallel metric. Adoption captures _consistency_; Autonomy captures _depth of autonomous use_. |
| [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier) | Adoption is one of three inputs into the composite Tier. |
| [Maturity Factor](/insights-expo/expo-ai-adoption-agentic-maturity-factor) | The scalar that ceilings every Adoption Score in your org. |
| [Provider Weights](/insights-expo/expo-ai-adoption-agentic-cursor-boost) | Define how much each tool's score contributes to the headline Adoption Score. |
| [Output Score](/insights-expo/expo-ai-adoption-output-output-score) | The shipping-side metric. High Adoption with flat Output is a common pattern in early rollouts. |

### How to improve it

* **Run scheduled "AI office hours."** Pair a Power User with an Emerging developer for an hour. Watch the Emerging developer's Adoption climb in the next two-week window.
* **Set provider defaults.** Make sure new developers have Claude Code installed and configured on day one. Emerging numbers among recent hires often trace to "no one told me to install it."
* **Audit the Emerging cohort.** Open /developers, sort by Adoption ascending. Walk the list. Many "Emerging" devs are actually working on infrastructure or platform work where AI isn't helpful — those are not a problem. Some are stuck on tooling friction. Those are the wins.
* **Track adoption _trajectory_, not snapshot.** A developer who moved from Emerging → Explorer in 6 weeks is a win even if they're still below 50.
* **Size the Maturity Factor to your rollout.** It defaults to 100%. If developers reach Power User before your org's adoption has really matured, lowering it (for example to 75%) keeps the top tier aspirational; raise it again as your org matures.

### Limitations and gotchas

* **The score lags reality by hours, not days.** Events sync every few minutes but with a 12-hour safety lag to absorb late-arriving OTEL backfill.
* **Pre-2026-03-05 Claude Code data doesn't exist.** Effective Weekdays adjusts for this automatically.
* **Cursor data depends on the Cursor API being reachable.** If your Cursor sync is broken, devs who only use Cursor will show as Emerging.
* **The org P90 ceiling moves with the cohort.** Hire a wave of Power Users and the bar rises for everyone. This is intentional but counter-intuitive.
* **Direct provider events only.** We don't infer AI usage from PR co-author tags or other heuristics for the Adoption Score (that's [AI-Assisted Percentage](/insights-expo/expo-ai-adoption-impact-cost-ai-assisted-percentage)).

### FAQ

**Q: Why don't Claude and Codex scores get averaged together?**
A: Because averaging penalizes developers who use multiple tools. Each tool is scored on its own and the weighted scores are added (capped at 100), so a developer who uses Claude in the morning and Codex in the afternoon gets credit from both.

**Q: A developer shows score 0 but I know they're using Claude. What happened?**
A: Check (1) the developer's `is_active` flag, (2) whether their email aliases are mapped if they have multiple work emails, and (3) whether the date range pre-dates the provider's data start (March 5, 2026 for Claude). One of those almost always explains it.

**Q: Does Cursor count for less than Claude Code or Codex?**
A: Not by default. Every tool's Provider Weight defaults to 1.0, so Cursor counts at full weight. An admin can lower any tool's weight in Settings → General → **Tier and provider weights**. See [Provider Weights](/insights-expo/expo-ai-adoption-agentic-cursor-boost).

**Q: Can I see the four-factor breakdown for a single developer?**
A: Yes. Click any developer on /developers to expand them. The agentic panel shows Daily Regularity, Hourly Spread, Prompt Depth, and Token Output as bars with the developer's value and, for the three P90-normalized factors, the org P90 cap.
