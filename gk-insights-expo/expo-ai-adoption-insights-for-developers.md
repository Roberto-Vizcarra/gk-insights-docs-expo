---
title: GitKraken Insights for Developers
description: Each developer's own page in GitKraken Insights, showing where their AI spend goes, the sessions and models they use, and how ready their repositories are for agents.
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
integrations: [Claude Code, Codex, Cursor, GitHub Copilot, Devin, OpenCode]
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: purple
    card_description: Each developer's own AI spend, sessions, and repository readiness
    card_icon: user-circle
    nav_category: getting-started
    nav_label: Insights for Developers
    nav_order: 90
    page_type: content
---
<kbd>Last updated: October 2026</kbd>

**GitKraken Insights for Developers** is a personal page for each member of your organization. It shows where your AI spend goes, the AI sessions and models you use, and how ready the repositories you work in are for agents. This page explains who gets the page, how to turn it on, and what each view shows. It doesn't cover the org-wide pages that Owners, Admins, and Managers use; for those, start with [Reading Your First Dashboard](/insights-expo/expo-ai-adoption-first-dashboard).

---

## Who gets the page

The page is for members of your organization who don't have org-wide access to Insights. Owners, Admins, and Managers already see everyone's data on the org-wide pages, so the personal page isn't offered to them.

The page is available only when an Owner or Admin turns on **GitKraken Insights for Developers** for the organization. When the setting is off, the page isn't available to anyone. Turning it on lets each person see their own data; it doesn't give anyone access to another person's data.

### Turn the page on for your organization

To turn on the page, you must be an organization **Owner** or **Admin**:

1. In Insights, open **Settings → General**.
2. Under **Access to Insights**, find **GitKraken Insights for Developers** and select **Turn on for everyone in the organization**.
3. Click **Save changes**.

For the setting's details, see [GitKraken Insights for Developers](/insights-expo/expo-ai-adoption-settings#gitkraken-insights-for-developers) in Settings.

## Open your page

Once the setting is on, opening Insights takes you to your personal page. The sidebar has two groups:

* **Insights:** **Overview**, **Agent Adoption**, **Repositories**, **Delivery**, and **AI Sessions**.
* **Level Up:** **Coaching** and **About the framework**. **Goals & Streaks** and **Benchmarks** are marked **Coming soon** and aren't available yet.

Use the filters at the top of the page to change the time window.

If none of your AI tool activity has synced yet, the page shows **No adoption data to show**. Your usage and delivery metrics appear once your activity syncs. If you expected to see data, ask an organization admin which AI tools are connected. The **Repositories** and **AI Sessions** views open even before your activity syncs.

## Read the Overview

The **Overview** shows where your AI spend goes, and how ready the repositories you work in are for agents. It has these parts:

* **Your header.** Your name, the AI tools you use, your team, and your tier. On the right, **Token Usage** shows your token spend for the window and your cost per output point. A lower cost per output point means you get more shipped output for the same spend.
* **Agent Adoption Insight.** Click the bulb for an AI read on your tier, what's driving it, and where to nudge next. The card appears only when your organization's plan includes GitKraken AI features and the organization has AI features turned on.
* **About the framework.** Opens an explanation of the tier shown in your header.
* **Model choices.** Where your token spend goes, by model.
* **Top skills by token value.** Your five highest-spending skills for the window. A skill that calls other skills counts only its own turns. The card is hidden when the window has no skill activity.
* **Latest sessions.** Your most recent AI sessions, each with its top skill and cost. To see where a session's token value went, by skill and by model, click the session. To open the full list, click **View all sessions**.
* **Repositories.** The average AI readiness of the repositories in your organization, with a **Needs attention** list of active, low-scoring repositories. To open the full list, click **View all repositories**.

**Top skills by token value** and **Latest sessions** are measured from Claude Code, GitHub Copilot, and OpenCode sessions. **Model choices** covers every connected AI tool, so the two don't always add up to the same total.

GitHub Copilot models appear in **Model choices** only when your organization has a **GitHub Copilot** or **Agent OTel Data** connection. Per-model Copilot spend comes from Copilot telemetry. A GitHub Copilot connection through a personal access token reports only an estimated seat-level cost, so it has no per-model breakdown.

> **Note: Devin and OpenCode are available on request.** These connections aren't turned on for every organization. To enable them, your organization's admin can contact your GitKraken account team.

## Use the other views

Each sidebar item opens a focused view of your own data:

| View | What it shows |
| --- | --- |
| **Agent Adoption** | How you use AI agents: your scores, autonomy, and cost, benchmarked against your organization. |
| **Repositories** | Whether the repositories you work in are set up to work well with agents: readiness, instructions, and safeguards. |
| **Delivery** | Your pull request cycle time and throughput, scoped to your own contributions. |
| **AI Sessions** | Your AI coding sessions, and how they roll up into the pull requests and issues you delivered. |
| **Coaching** | AI-generated guidance to help you get more from your agents. |

### Read your delivery charts

The **Delivery** view counts your pull requests, but it doesn't open lists of pull requests. Those lists draw on organization-wide data that only Owners, Admins, and Managers can open. If a list is refused, it reads **These pull requests are not available**.

### Read your AI sessions

The **AI Sessions** view shows the same metric strip and the same **Overview**, **Sessions**, **Pull Requests**, and **Issues** tabs as the org-wide [AI Sessions](/insights-expo/expo-ai-adoption-ai-sessions) page, scoped to your own sessions. A notice explains which AI tools report sessions:

* Sessions cover Claude Code, Codex, Devin, OpenCode, and GitHub Copilot through OpenTelemetry.
* Cursor, and GitHub Copilot connected without OpenTelemetry, report usage that isn't grouped into sessions. Their activity appears in **Agent Adoption** instead.
* Pull requests, commits, and issues are linked to a session from the point you connect your environment onward. A session that ran before that point shows no delivery activity.

For what each card, tab, and column means, see [AI Sessions](/insights-expo/expo-ai-adoption-ai-sessions).

## Related pages

* [AI Adoption Settings](/insights-expo/expo-ai-adoption-settings#gitkraken-insights-for-developers): the setting that turns the page on.
* [AI Sessions](/insights-expo/expo-ai-adoption-ai-sessions): the org-wide view of every session.
* [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier): what the tier in your header means.
