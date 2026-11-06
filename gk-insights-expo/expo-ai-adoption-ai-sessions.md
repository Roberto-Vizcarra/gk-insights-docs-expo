---
title: AI Sessions
description: See every AI coding session across your organization in GitKraken Insights, and how those sessions roll up into pull requests and issues.
product: GitKraken Insights
content_type: reference
audience: engineering-leader
plan_required: GitKraken Insights
integrations: [Claude Code, Codex, GitHub Copilot, Devin, OpenCode]
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: purple
    card_description: Every AI coding session, by session, pull request, and issue
    card_icon: stack-2
    nav_category: getting-started
    nav_label: By AI Sessions
    nav_order: 70
    page_type: content
---
<kbd>Last updated: October 2026</kbd>

The **AI Sessions** page shows every AI coding session across your organization, and how those sessions roll up into the pull requests and issues they delivered. This page explains each part of AI Sessions and how to read it. It assumes your AI tools already report to Insights. To connect them, see [Connect Agent OTel Data](/insights-expo/expo-ai-adoption-connect-otel) and [Connect AI Coding Tools](/insights-expo/expo-ai-adoption-connect-ai-tools).

> **Who can see it:** AI Sessions is available to **Owners**, **Admins**, and **Managers**. Other members see their own sessions in the AI Sessions view of [GitKraken Insights for Developers](/insights-expo/expo-ai-adoption-insights-for-developers).

---

## Open AI Sessions

In the Insights sidebar, under **Agent Leaderboard**, click **By AI Sessions**. The page opens with a filter bar, a metric strip, a coverage notice, and four tabs: **Overview**, **Sessions**, **Pull Requests**, and **Issues**. **Overview** is selected each time the page opens.

Use the filter bar to narrow the page by time, team, developer, AI tool, and AI tier. The filters apply to the metric strip and to every tab. The **Granularity** control appears only on the **Overview** tab, because only its trend charts use it. On AI Sessions, the finest granularity is daily.

## Read the metric strip

Six cards summarize the sessions in the selected window. Each card shows its change from the previous period, with the previous value beside it:

| Card | What it shows |
| --- | --- |
| **Sessions** | Every AI coding session in the window, and how many active developers ran them. |
| **Avg parallel sessions** | How many sessions a developer typically runs at the same time, with the peak. Insights weights overlap by time: the total duration of every session divided by the span those sessions covered together. |
| **Autonomous run** | How long the agent works between prompts, shown as the 90th percentile (P90), with the median and the number of runs. Each run lasts from a prompt to the agent's last activity before the next prompt. |
| **Skills / session** | The average number of distinct skills used in a session, and the share of sessions that use at least one skill. |
| **Cost / session** | The mean cost of a session, with the median and P90 beside it so a few expensive sessions don't hide the typical one. |
| **Total usage value** | The total usage value of every session in the window, divided by merged pull requests and by issues. |

Usage value and token value are the estimated dollar value of the tokens a session consumed.

Not every AI tool reports every metric. When a card covers fewer tools than the page does, it names the tools it covers, for example **Covers Claude Code only**. When run durations aren't recorded for every day in the range, **Autonomous run** reads as unavailable.

## Understand which sessions are covered

A notice below the metric strip explains two things the figures can't show on their own:

* **Which AI tools report sessions.** Sessions cover Claude Code, Codex, Devin, OpenCode, and GitHub Copilot through OpenTelemetry. Cursor, and GitHub Copilot connected without OpenTelemetry, report usage that isn't grouped into sessions. Their activity appears on the other AI Adoption pages instead.
* **When delivery is linked.** Pull requests, commits, and issues are linked to a session from the point each developer connects their environment onward. A session that ran before that point shows no delivery activity.

> **Note: Devin and OpenCode are available on request.** These connections aren't turned on for every organization. To enable them, contact your GitKraken account team.

To hide the notice, click its close button. Your browser remembers the choice.

<figure>
  <img src="/wp-content/uploads/gki-ai-sessions-metric-strip-01-v2@2x.png" class="help-center-img img-bordered" alt="The AI Sessions metric strip: six cards for Sessions (599), Avg parallel sessions (1.4 concurrent), Autonomous run (37m), Skills / session (1.8), Cost / session ($3.20) and Total usage value ($1.9k), each with its change from the previous period. Below it, the coverage notice lists the AI tools that report sessions and says pull requests, commits and issues are linked from the point each developer connects their environment." />
  <figcaption>The metric strip and the coverage notice on AI Sessions.</figcaption>
</figure>

## Read the Overview tab

The **Overview** tab shows where session spend goes across the filtered cohort. A note at the top names the AI tools the figures cover. Only tools that report skill activity contribute to this tab, so it can cover fewer tools than the metric strip. GitHub Copilot through OpenTelemetry and OpenCode report skill activity alongside Claude Code.

The tab holds these cards:

* **Token value trend.** Token value over time, overall and for the share that ran under a skill.
* **Cost per output score.** Token value for each period divided by what that period shipped: merged pull requests, direct commits, and reviews, scored together. A dashed line shows the trend across the window. A period that shipped nothing has no bar.
* **Breakdown by model.** A donut of how the window's token value and token volume split across models.
* **Skills invocation trigger.** How each skill started: a developer invoked it directly, another skill called it, the agent chose it, or it loaded with an agent's context.
* **Skill source.** Where the skills came from: user (personal), project (shared in the repository), bundled or built-in, plugin, custom directory, or remote (organization or enterprise).
* **Top skills by token value.** The skills with the highest token value, beside the token value spent with no skill active.
* **Autonomous run distribution.** How long autonomous runs lasted, grouped into duration bands, with the P90 band highlighted.

If the cohort has no session activity in the period, the tab says so and keeps only the run distribution.

<figure>
  <img src="/wp-content/uploads/gki-ai-sessions-overview-01-v4@2x.png" class="help-center-img img-bordered" alt="The Overview tab of AI Sessions, beside the Sessions, Pull Requests and Issues tabs. A note says the figures cover only the AI tools that report skill activity: Claude Code. Cards show the Token Value Trend, Cost per Output Score, a Breakdown by Model donut, Skills Invocation Trigger and Skill Source bars, the Top Skills by Token Value table and the Autonomous Run Distribution histogram." />
  <figcaption>The Overview tab of AI Sessions.</figcaption>
</figure>

## Browse sessions on the Sessions tab

The **Sessions** tab lists every session in the period in the **All sessions** table, sorted by token value. To narrow the list, pick skills from the **Skills** filter or type part of a session ID in **Search session id**. To choose which columns appear, use the **Columns** menu.

The table has these columns:

| Column | What it shows |
| --- | --- |
| **Developer** | The session owner. |
| **Session** | The session's ID from the AI tool that ran it. |
| **Top skill** | The skill with the highest token value in the session. |
| **Skills**, **Models**, **Runs** | How many distinct skills, models, and top-level runs the session used. |
| **PRs** | Pull requests the session worked on, from its captured git activity. |
| **Direct** | Commits the developer pushed straight to a branch, with no pull request. |
| **Commits** | Every commit the session's captured activity resolved to, including commits someone else authored. This count can be higher than **Direct**. |
| **Started** | When the session started. |
| **Token value** | The session's total token value. The bar splits skill work (purple) from work with no skill active (grey). |

Some cells show a status instead of a value:

* **Not applicable:** the AI tool doesn't report skills.
* **Not tracked:** the session's git activity was never captured. This status doesn't mean zero.
* **Couldn't load:** the page's delivery details failed to load.

### Open a session

To see a session's detail, click its row. One session opens at a time. The detail shows the session's time span and token value, then its token usage value by model and by skill, and the pull requests it delivered.

Below that, the **Session timeline by token value** lays the session out in time order in two lanes:

* **Work · skills** shows the work each prompt turn did. Bar length is proportional to token value. To open a segment's detail, click a skill. The detail covers the segment's requests, prompts, models, and tools, and any subagents it spawned, grouped by agent type.
* **Git · commits, by time** shows the git activity each turn produced: pull requests, direct commits, and branch changes. Each pull request shows how the session relates to it, such as **Authored** or **Reviewed**, and any review verdict: **Approved**, **Changes requested**, or **Commented**.

A **context compaction** marker shows when the session's context window filled and older turns were summarized. When git activity wasn't recorded for a session, the git lane says so. Sessions from AI tools that don't report skills have no timeline to open.

<figure>
  <img src="/wp-content/uploads/gki-ai-sessions-session-detail-01-v2@2x.png" class="help-center-img img-bordered" alt="The Sessions tab of AI Sessions: the All sessions table, with its Skills filter, session search and Columns menu, and David Kim's session expanded. The detail shows the session's time span and token value, token usage value by model and by skill, three linked merged pull requests marked Authored, and the session timeline: a Work lane of skill and no-skill runs with their token value beside a Git lane of commits and pull requests in time order. Two collapsed session rows and the pager follow." />
  <figcaption>An expanded session on the Sessions tab, with its two-lane timeline.</figcaption>
</figure>

## See cost by pull request on the Pull Requests tab

The **Pull Requests** tab rolls sessions up into the pull requests they built. The table lists each pull request with its repository, contributors, state, sessions, commits, usage value, and merge date, sorted by usage value. To filter by state, use **All states**. The options are **Merged**, **Open**, **Closed**, and **Draft**.

A pull request can be built by several sessions and developers, and a session can span several pull requests. Insights allocates a session's usage value to each pull request by that session's share of the commits, so a shared session is never double-counted.

To see the sessions behind a pull request, click its row. Each session shows its developer, top skill, start time, and the usage value allocated to this pull request. A **shared** badge marks a session that also worked on other pull requests.

<figure>
  <img src="/wp-content/uploads/gki-ai-sessions-pull-requests-01-v2@2x.png" class="help-center-img img-bordered" alt="The Pull Requests tab of AI Sessions. A Many-to-many note explains that a pull request can be built by several sessions and that usage value is allocated per pull request by each session's commit share. The table lists pull requests with repository, contributors, state, sessions, commits, usage value and merge date, sorted by usage value, with an All states filter and a Columns menu." />
  <figcaption>The Pull Requests tab of AI Sessions.</figcaption>
</figure>

## See cost by issue on the Issues tab

The **Issues** tab rolls sessions up into issues through the pull requests linked to each issue. The table lists each issue with its type, status, linked pull requests, sessions, developers, and usage value. To filter by status, use **All statuses**. The list uses the statuses from your issue tracker.

An issue's usage value is the sum of its linked pull requests' allocated values. A pull request linked to several issues contributes only its share to each, so nothing is counted twice. **Sessions** and **Developers** count each session and developer once across all of the issue's linked pull requests.

To see the pull requests and sessions behind an issue, click its row.

<figure>
  <img src="/wp-content/uploads/gki-ai-sessions-issues-01-v2@2x.png" class="help-center-img img-bordered" alt="The Issues tab of AI Sessions. A Two hops, still no double-counting note explains that an issue's usage value is the sum of its linked pull requests' allocated values. The table lists issues with key, title, type, status, linked pull requests, sessions, developers and usage value, with an All statuses filter." />
  <figcaption>The Issues tab of AI Sessions.</figcaption>
</figure>

### Open a session from a pull request or issue

On both roll-up tabs, you can expand a contributing session into the same timeline as on the **Sessions** tab. To open it, click the session's row. A session that isn't linked to a developer can't be opened.

---

## See one developer's sessions

To see the sessions of a single developer, open the developer on the [By Developer](/insights-expo/expo-ai-adoption-developers) page and select the **AI Sessions** tab.

## Related pages

* [Connect Agent OTel Data](/insights-expo/expo-ai-adoption-connect-otel): set up the AI tools that report sessions.
* [GitKraken Insights for Developers](/insights-expo/expo-ai-adoption-insights-for-developers): each developer's own AI Sessions view.
* [Agent Autonomy Score](/insights-expo/expo-ai-adoption-agentic-autonomy-score): how session depth feeds autonomy.
