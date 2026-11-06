---
title: Getting Started with AI Adoption in GitKraken Insights
description: Get oriented in the AI Adoption dashboard in GitKraken Insights, with quickstarts for executives, engineering leaders, team leads, and admins, plus a guided walkthrough of your first dashboard.
product: GitKraken Insights
content_type: how-to
audience: all
plan_required: GitKraken Insights
integrations: [GitHub, GitHub Enterprise Server, GitLab, GitLab Self-Managed, Bitbucket, Bitbucket Data Center, Azure DevOps, Azure DevOps Server, Claude Code, Codex, Cursor, GitHub Copilot, Devin, OpenCode, Jira Cloud, BambooHR]
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: purple
    card_description: Orientation, first dashboard, and role-specific quickstarts
    card_icon: compass
    nav_category: getting-started
    nav_label: Getting Started
    nav_order: 10
    page_type: index
---
<kbd>Last updated: October 2026</kbd>

GitKraken Insights is the dashboard your engineering organization uses to see — in one place — how AI tools, code delivery, and team capacity actually interact. This section gets you oriented in about ten minutes.

---

## What this product does

It connects your git provider, your AI coding tool telemetry, your Jira customer bugs, and your BambooHR PTO calendar, and produces a small set of trustworthy numbers for engineering leadership.

## What this product does _not_ do

* It does not rank or compare individual developers against each other for performance reviews. The scoring framework is designed for _trend reading_ and _cohort comparison_, not for slotting people on a curve.
* It does not replace your standup, your retro, or your one-on-ones. It surfaces patterns that should become conversations.
* It does not produce real-time alerts. The numbers refresh on a window (default 14 days) and are designed for weekly / monthly / quarterly review.

## Three pages that matter most

| Page | When to use it |
| --- | --- |
| **/teams** _(the default landing)_ | Weekly leadership review. Team-by-team adoption and delivery, side by side. |
| **/developers** | When you want to drill into individuals: who's a Power User, who's struggling to adopt, who needs onboarding. |
| **/executive** | Monthly or quarterly reports up the chain. Pre-shaped for execs with hero KPIs and trend lines. |

## Explore by developer, repository, tool, and session

The **Agent Leaderboard** pages in the sidebar each look at AI adoption from one angle:

| Page | What it shows |
| --- | --- |
| [By Developer](/insights-expo/expo-ai-adoption-developers) | Every developer's scores, spend, and AI sessions, with a detail view for each person. |
| [By Repository](/insights-expo/expo-ai-adoption-repositories) | How ready each repository is for agents, and which teams it belongs to. |
| [By AI Tool & Model](/insights-expo/expo-ai-adoption-ai-tools-comparison) | Side-by-side cohorts of developers by AI tool and by model. |
| [By AI Sessions](/insights-expo/expo-ai-adoption-ai-sessions) | Every AI coding session, and how sessions roll up into pull requests and issues. |

[Surveys](/insights-expo/expo-ai-adoption-surveys) lists your organization's surveys and their results. Members without org-wide access get their own page, [GitKraken Insights for Developers](/insights-expo/expo-ai-adoption-insights-for-developers), once an Owner or Admin turns it on.

## Adjust table columns

Most AI Adoption tables have a **Columns** menu. To choose which columns appear, open **Columns** and select or clear each column under **Visible columns**. To bring every column back, click **Show all**. To resize a column, drag the edge of its header, or focus the edge and use the arrow keys. To undo your resizing, click **Reset widths** in the **Columns** menu. Insights remembers your column choices in this browser. On narrow screens, columns can't be hidden or resized.

## A note on benchmarks

You'll see ranges throughout the help center labeled "Strong / Fair / Needs attention." These reflect our defaults and what we see across the orgs running Insights. They are not industry benchmarks — every team's context matters too much — so treat the ranges as a starting frame, not a verdict.
