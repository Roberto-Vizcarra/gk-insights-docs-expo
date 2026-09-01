---
title: Connect Your Data — Setting Up AI Adoption
description: A step-by-step setup guide for AI Adoption in GitKraken Insights — gather the right access, connect your git provider (GitHub, Bitbucket, Azure DevOps, GitLab, or a self-hosted server), your AI coding tools (Claude Code, Codex, Cursor, GitHub Copilot, Devin), Jira, and BambooHR, map developer identities, and invite your team.
product: GitKraken Insights
content_type: how-to
audience: admin
plan_required: GitKraken Insights
integrations: [GitHub, GitHub Enterprise Server, Bitbucket, Bitbucket Data Center, Azure DevOps, Azure DevOps Server, GitLab, GitLab Self-Managed, Claude Code, Codex, Cursor, GitHub Copilot, Devin, Jira Cloud, BambooHR]
status: GA
page_type: index
nav_category: connect-your-data
nav_order: 0
nav_label: Connect Your Data
card_icon: plug
card_color: blue
card_description: Link git providers, AI coding tools, and project trackers
taxonomy:
    category: insights-expo
---
<kbd>Last updated: September 2026</kbd>

This is the hands-on setup guide for AI Adoption in GitKraken Insights. By the end, your organization's git-provider activity and AI coding-tool telemetry will be flowing into the dashboard, your developers will be mapped to a single identity, and your team will have access.

Plan on **15–20 minutes of active work**, plus up to a day for the first full data sync to complete in the background.

> **Plan:** GitKraken Insights
> **Platform:** Browser only via [gitkraken.dev](https://gitkraken.dev)
> **Role:** Owner or Admin (to manage data connections)
> **Where you'll work:** **Insights → Settings → Data Connections**

> **Note — this is the Next-Gen (AI Adoption) connection flow.** AI Adoption connects through **org-level access tokens** in Settings → Data Connections. This is different from the classic Insights repository connection (the OAuth "Authorize GitHub" flow described in [Getting Started with GitKraken Insights](/gk-insights/gk-insights#connecting-your-data)). If you're setting up AI Adoption, follow the steps on this page.

---

## Before you start — gather access

The single biggest cause of stalled setups is discovering mid-stream that the person doing the setup doesn't have the right permissions. Setup almost always touches **two or three different people** across your org. Line them up *before* you begin.

| You'll need… | …who has this access | What they'll do |
| --- | --- | --- |
| **GitKraken organization** | Owner or Admin of your gk.dev org | Manage data connections, invite teammates |
| **GitHub** *(if you use GitHub)* | An org admin (to create an org-level token) | Generate the GitHub access token |
| **Bitbucket** *(if you use Bitbucket)* | An admin of your Bitbucket workspace | Generate a Bitbucket-specific Atlassian API token |
| **Azure DevOps** *(if you use Azure DevOps)* | An account that can see every project you want to sync | Create a PAT with **Code (Read)** |
| **GitLab** *(if you use GitLab, cloud or self-managed)* | An account with access to the groups you want to sync | Create a PAT with the `read_api` scope |
| **A self-hosted server** *(GitHub Enterprise Server, Bitbucket Data Center, Azure DevOps Server, or GitLab Self-Managed)* | Whoever runs it | Confirm it's reachable over `https` with a publicly-trusted certificate |
| **Claude Code / Codex** | The **Owner** of your Anthropic (Claude Code) organization — *admins cannot do this* | Paste the telemetry snippet into org-managed settings |
| **Cursor** | A Cursor **team admin** | Create a team-level admin API key |
| **GitHub Copilot** (optional) | A GitHub organization or enterprise admin | Enable the Copilot usage metrics policy, create a classic PAT |
| **Devin** (optional) | An admin of your Devin organization | Provision a service-user API key and note the Organization ID |
| **Jira** (optional) | A Jira admin | Create an API token |
| **BambooHR** (optional) | Someone who sees the whole company's Who's Out calendar (usually HR) | Generate the Who's Out iCal feed URL |

> **Start your git-provider token request now.** In larger orgs, getting approval to create a git-provider token with the right scope can take days — sometimes weeks. It's the most common bottleneck, so kick it off before anything else.

> **Only Owners and Admins can connect data.** If you open Settings → Data Connections and see a read-only banner, you'll need an org Owner or Admin to either make the connections or grant you access.

---

## How the Data Connections page works

Every step below happens on the same page: **Insights → Settings → Data Connections**. It has two parts.

- **Connected** lists what's already wired up, with each connection's data source, connection date, and sync status. Use **Status** to inspect a connection's pipelines, **Edit** to change its credentials or advanced settings, and **Disconnect** to remove it. The Claude Code & Codex row offers **View setup** instead of Edit, because its configuration lives in your AI tools rather than in GitKraken.
- **Add data source** lists everything you can connect. Click the **+** next to a source to open its connection modal.

<figure>
  <img src="/wp-content/uploads/settings-data-connections-aug-2026.png" class="help-center-img img-bordered" alt="The Data Connections tab of Insights Settings, showing a Connected table with Claude Code and Codex, GitHub, and Jira rows alongside their sync status, and an Add data source grid listing Cursor, Devin, BambooHR, GitHub, Bitbucket, Azure DevOps, GitLab, Azure DevOps Server, GitHub Enterprise Server, GitLab Self-Managed, Bitbucket Data Center, and GitHub Copilot" />
  <figcaption style="text-align: center; color: #888">Settings → Data Connections — existing connections on top, available data sources below.</figcaption>
</figure>

> **These are org-level credentials.** GitHub and Jira are also available per-user under **Integrations**, but AI adoption analytics need their own org-level connections made here.

---

## Connect your data sources

Each data source has its own setup page with provider-specific instructions, token scopes, and troubleshooting. Choose the pages that match your stack:

- **Git providers:** [GitHub](/gk-insights/ai-adoption-connect-github), [Bitbucket](/gk-insights/ai-adoption-connect-bitbucket), [Azure DevOps](/gk-insights/ai-adoption-connect-azure-devops), [GitLab](/gk-insights/ai-adoption-connect-gitlab)
- **AI coding tools:** [Claude Code, Codex, Cursor, Copilot, and Devin](/gk-insights/ai-adoption-connect-ai-tools)
- **Project tracking and PTO:** [Jira & BambooHR](/gk-insights/ai-adoption-connect-jira-bamboohr)

Your git provider is the foundation — it powers every PR, commit, contributor, and cycle-time metric, so connect that first. Then connect at least one AI coding tool so Insights can measure adoption. Jira and BambooHR are optional but sharpen the data.

---

## Set your benchmarks

A few business inputs let Insights translate engineering activity into ROI and tier your developers correctly. Sensible defaults are pre-filled — confirm or adjust in **Settings → General**:

- **Developer Hourly Rate** — used for ROI / time-saved-to-dollars on AI Impact.
- **Baseline Period** — the "before" date AI-adoption lift is measured against.
- **Maturity Factor** (Company AI Readiness %) — an org-wide scaling knob; the 0.75 default suits most orgs.
- **Default Department** — pre-selects the right view for first-time visitors.

You can change all of these at any time. For what each setting affects, see the [AI Adoption Settings reference](/gk-insights/ai-adoption-settings).

---

## Map developer identities

This is the step that makes or breaks clean data. The same person often shows up under several identities — a git-provider login, one or more commit emails, a Jira account. Until those are merged, your leaderboards and adoption metrics double-count, and you end up with "parallel universes" of the same developer.

1. After your git provider has been processing for a bit (allow ~12 hours), open **Settings → Developers**.
2. Review the detected identities. Where you recognize duplicates of the same person, use **Merge** to combine them — including two accounts on the same git provider, such as a developer with two GitHub logins.
3. Where an identity is missing an email, add it — this helps tie commit data back to the right person.

Insights auto-suggests matches using email, git handle, and name, but you should confirm them and clean up anything it couldn't resolve. **Treat this like an inbox and keep it empty** — see the [For Admins](/gk-insights/ai-adoption-for-admins) page for ongoing roster hygiene.

### Excluding review bots

AI code-review bots — such as **GitHub Copilot review** or Atlassian **Rovo** — leave activity on pull requests, so Insights detects them as contributors. If you'd rather they not appear as developers in your metrics, you can exclude them from the roster in **Settings → Developers**. Excluded developers also stay out of the developer dropdowns across the dashboards.

<!-- FLAG FOR HUMAN REVIEW: screenshot of Settings → Developers identity merge / exclude flow needed. -->

---

## Invite your team

Give the rest of your stakeholders access so they can read the dashboards.

1. From the gk.dev sidebar, go back to the **main menu** and open **Users**.
2. Click **Add users** and invite people by email, or share the invite link.
3. Give each person at least an **Admin** or **Lead** role so they can view Insights.

> **If you invite by link:** new users land with a default role, so you'll need to adjust their role to **Admin** or **Lead** after they create their account.

---

## What to expect after setup

- **First data:** the last month of git-provider activity typically appears within a few hours; a full year usually lands within one to two days.
- **AI tool data:** starts flowing on each developer's next Claude Code / Codex / Cursor session — there's no backfill before the connection was made. Devin data arrives through its API once the connection is live.
- **Time off:** BambooHR changes can take up to 24 hours to reach the iCal feed, so recent PTO may lag.
- **Sync status:** each connection on the Data Connections page shows a health status. If a connection looks degraded or errored, that's the first place to check — and let your account team know.
- **A degraded status during the first sync is usually normal.** Large organizations hit their git provider's API rate limits while the initial backfill pulls a year of history, and the connection reports degraded while it waits. The credentials are fine and the data is still coming. Contact your account team before disconnecting and reconnecting.

---

## Troubleshooting setup

| Symptom | What's happening | Fix |
| --- | --- | --- |
| **Read-only banner** on Data Connections or Settings | Your gk.dev role can't manage connections | Ask an org Owner or Admin to connect, or to grant you access |
| **A connection shows "degraded" during the first sync** | Usually your git provider's API rate limits throttling the initial backfill, not a broken connection | Let it finish. If it doesn't clear, contact your account team before disconnecting and reconnecting |
| **A connection sits at "Not yet synced"** | Expected right after connecting — and for Claude Code / Codex it stays that way until the first developer session sends telemetry | Wait for the first sync; for Claude Code / Codex, confirm the snippet is applied and have someone start a fresh session |
| **A developer appears twice** | Multiple identities not yet merged | Merge them in Settings → Developers — see [Map developer identities](#map-developer-identities) |
| **Setup email flagged by your IT as suspicious** | Some corporate filters flag new domains | Navigate directly to [gitkraken.dev](https://gitkraken.dev) instead of clicking the email link; tell your account team |

For provider-specific troubleshooting, see the individual connection pages: [GitHub](/gk-insights/ai-adoption-connect-github#troubleshooting), [Bitbucket](/gk-insights/ai-adoption-connect-bitbucket#troubleshooting), [Azure DevOps](/gk-insights/ai-adoption-connect-azure-devops#troubleshooting), [GitLab](/gk-insights/ai-adoption-connect-gitlab#troubleshooting), [AI Coding Tools](/gk-insights/ai-adoption-connect-ai-tools#troubleshooting), [Jira & BambooHR](/gk-insights/ai-adoption-connect-jira-bamboohr#troubleshooting).

If a problem persists, contact your GitKraken account team with the page URL, what you were connecting, and a screenshot of any error.

---

## Related pages

- [Getting Started with AI Adoption](/gk-insights/ai-adoption-getting-started) — orient yourself in the dashboards once data is flowing.
- [For Admins](/gk-insights/ai-adoption-for-admins) — ongoing roster hygiene, data freshness, and troubleshooting.
- [Manual Releases API](/gk-insights/ai-adoption-manual-releases-api) — push releases to Insights from deployment tooling it can't read.
- [AI Adoption Settings reference](/gk-insights/ai-adoption-settings) — what each setting changes.
- [Getting Started with GitKraken Insights](/gk-insights/gk-insights) — request access and the classic repository connection flow.
