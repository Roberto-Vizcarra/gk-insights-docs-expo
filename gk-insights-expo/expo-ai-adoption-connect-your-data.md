---
title: Connect Your Data — Setting Up AI Adoption
description: A step-by-step setup guide for AI Adoption in GitKraken Insights — gather the right access, connect your git provider (GitHub, Bitbucket, Azure DevOps, GitLab, or a self-hosted server), your AI coding tools (Claude Code, Codex, Cursor, GitHub Copilot, Devin), Jira, and BambooHR, map developer identities, and invite your team.
product: GitKraken Insights
content_type: how-to
audience: admin
plan_required: GitKraken Insights
integrations: [GitHub, GitHub Enterprise Server, Bitbucket, Bitbucket Data Center, Azure DevOps, Azure DevOps Server, GitLab, GitLab Self-Managed, Claude Code, Codex, Cursor, GitHub Copilot, Devin, Jira Cloud, BambooHR]
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: blue
    card_description: Link git providers, AI coding tools, and project trackers
    card_icon: plug
    nav_category: connect-your-data
    nav_label: Connect Your Data
    nav_order: 20
    page_type: index
---
<kbd>Last updated: September 2026</kbd>

This is the hands-on setup guide for AI Adoption in GitKraken Insights. By the end, your organization's git-provider activity and AI coding-tool telemetry will be flowing into the dashboard, your developers will be mapped to a single identity, and your team will have access.

Plan on **15–20 minutes of active work**, plus up to a day for the first full data sync to complete in the background.

> **Note — this is the Next-Gen (AI Adoption) connection flow.** AI Adoption connects through **org-level access tokens** in Settings → Data Connections. This is different from the classic Insights repository connection (the OAuth "Authorize GitHub" flow described in [Getting Started with GitKraken Insights](/gk-insights/gk-insights#connecting-your-data)). If you're setting up AI Adoption, follow the steps on this page.

---

## Before you start — gather access

The single biggest cause of stalled setups is discovering mid-stream that the person doing the setup doesn't have the right permissions. Line up the right people *before* you begin.

| You'll need… | …who has this access |
| --- | --- |
| **GitKraken organization** | Owner or Admin of your gk.dev org |
| **GitHub** | An org admin (to create an org-level token) |
| **Bitbucket** | An admin of your Bitbucket workspace |
| **Azure DevOps** | An account that can see every project you want to sync |
| **GitLab** | An account with access to the groups you want to sync |
| **A self-hosted server** | Whoever runs it — confirm it's reachable over `https` with a publicly-trusted certificate |
| **Claude Code / Codex** | The **Owner** of your Anthropic organization — *admins cannot do this* |
| **Cursor** | A Cursor **team admin** |
| **Jira** (optional) | A Jira admin |
| **BambooHR** (optional) | Someone who sees the whole company's Who's Out calendar |

> **Start your git-provider token request now.** In larger orgs, getting approval can take days. Kick it off before anything else.

## After connecting — general setup steps

These apply regardless of which data sources you connected. Your git provider is the foundation — connect that first, then at least one AI coding tool.

### Set your benchmarks

A few business inputs let Insights translate engineering activity into ROI. Confirm or adjust in **Settings → General**: Developer Hourly Rate, Baseline Period, Maturity Factor, and Default Department. See the [Settings reference](/insights-expo/expo-ai-adoption-settings) for details.

### Map developer identities

After your git provider has been processing (~12 hours), open **Settings → Developers**. Review detected identities, merge duplicates, and add missing emails. AI code-review bots (GitHub Copilot review, Rovo) can be excluded from the roster here.

### Invite your team

From the gk.dev sidebar, open **Users** → **Add users**. Give each person at least an **Admin** or **Lead** role so they can view Insights.

## What to expect after setup

* **Git data:** the last month typically appears within a few hours; a full year within one to two days.
* **AI tool data:** starts flowing on each developer's next session — no backfill before the connection.
* **Sync status:** each connection on Data Connections shows health. A degraded status during the first sync is usually normal (API rate limits during backfill).

## Troubleshooting setup

| Symptom | Fix |
| --- | --- |
| **Read-only banner** on Data Connections | Ask an org Owner or Admin to connect, or grant you access |
| **"Degraded" during first sync** | Usually rate-limit throttling during backfill — let it finish |
| **"Not yet synced"** | Expected right after connecting; for Claude Code/Codex, confirm the snippet is applied |
| **Developer appears twice** | Merge identities in Settings → Developers |

For provider-specific troubleshooting, see the individual connection pages. If a problem persists, contact your GitKraken account team.
