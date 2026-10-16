---
title: Connect Your Data — Setting Up AI Adoption
description: A step-by-step setup guide for AI Adoption in GitKraken Insights — gather the right access, connect your git provider (GitHub, Bitbucket, Azure DevOps, GitLab, or a self-hosted server), your AI coding tools (Claude Code, Codex, Cursor, GitHub Copilot, Devin, OpenCode), your issue tracker (Jira, Linear, or YouTrack), and BambooHR, map developer identities, organize teams, and invite your team.
product: GitKraken Insights
content_type: how-to
audience: admin
plan_required: GitKraken Insights
integrations: [GitHub, GitHub Enterprise Server, Bitbucket, Bitbucket Data Center, Azure DevOps, Azure DevOps Server, GitLab, GitLab Self-Managed, Claude Code, Codex, Cursor, GitHub Copilot, Devin, OpenCode, Jira Cloud, Linear, YouTrack, YouTrack Server, BambooHR]
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
<kbd>Last updated: October 2026</kbd>

This is the hands-on setup guide for AI Adoption in GitKraken Insights. By the end, your organization's git-provider activity and AI coding-tool telemetry will be flowing into the dashboard, your developers will be mapped to a single identity, and your team will have access.

Plan on **15–20 minutes of active work**, plus up to a day for the first full data sync to complete in the background.

> **Note — this is the Next-Gen (AI Adoption) connection flow.** AI Adoption connects through **org-level access tokens** in Settings → Data Connections. This is different from the classic Insights repository connection (the OAuth "Authorize GitHub" flow described in [Getting Started with GitKraken Insights](/gk-insights/gk-insights#connecting-your-data)). If you're setting up AI Adoption, follow the steps on this page.

---

## Before you start — gather access

The single biggest cause of stalled setups is discovering mid-stream that the person doing the setup doesn't have the right permissions. Line up the right people *before* you begin.

| You'll need… | …who has this access |
| --- | --- |
| **GitKraken organization** | Owner or Admin of your gk.dev org |
| **GitHub** | An organization owner to install the GitHub App (members can request it for an owner to approve), or an org admin to create an org-level token |
| **Bitbucket** | An admin of your Bitbucket workspace |
| **Azure DevOps** | An account that can see every project you want to sync |
| **GitLab** | An account with access to the groups you want to sync |
| **A self-hosted server** | Whoever runs it — confirm it's reachable over `https` with a publicly-trusted certificate |
| **Claude Code** | The **Owner** of your Anthropic organization — *admins cannot do this* — or whoever runs your Claude apps gateway |
| **OpenAI Codex** | Whoever manages your MDM, or each developer |
| **OpenCode** (available on request) | Each developer, who sets their own email in the config |
| **GitHub Copilot** | An organization or enterprise owner, or a member with permission to view Copilot metrics. For Copilot telemetry, whoever manages your GitHub Enterprise managed settings, or each developer |
| **Cursor** | A Cursor **team admin** |
| **Devin** (available on request) | An admin of your Devin organization, to provision a service user with the **Admin** role |
| **Jira** (optional) | A Jira admin |
| **Linear** (optional) | A Linear account that can see the teams you want to sync, to create a personal API key |
| **YouTrack** or **YouTrack Server** (optional) | A YouTrack account that can see the projects you want to sync, to create a permanent token |
| **BambooHR** (optional) | Someone who sees the whole company's Who's Out calendar |

> **Note — Devin and OpenCode are available on request.** To enable either one for your organization, contact your GitKraken account team.

> **Start your git-provider token request now.** In larger orgs, getting approval can take days. Kick it off before anything else.

For issue trackers, see [Connect Jira and BambooHR](/insights-expo/expo-ai-adoption-connect-jira-bamboohr) and [Connect Linear and YouTrack](/insights-expo/expo-ai-adoption-connect-linear-youtrack).

For exactly what each integration reads, the permissions it needs, and how GitKraken protects the credentials you enter, see [Credential Security and Integration Permissions](/insights-expo/expo-ai-adoption-security-permissions).

## After connecting — general setup steps

These apply regardless of which data sources you connected. Your git provider is the foundation — connect that first, then at least one AI coding tool.

### Set your benchmarks

A few business inputs let Insights translate engineering activity into ROI. Confirm or adjust in **Settings → General**: Developer Hourly Rate (you can also set [per-developer rates](/insights-expo/expo-ai-adoption-settings#per-developer-hourly-rate) in **Settings → Developers**), Working Hours Per Week, Baseline Period, Maturity Factor (labeled **Company AI Readiness (%)**), and Default Department. See the [Settings reference](/insights-expo/expo-ai-adoption-settings) for details.

### Map developer identities

After your git provider has been processing (~12 hours), open **Settings → Developers**. Review detected identities, merge duplicates, and add missing emails. AI code-review bots (GitHub Copilot review, Rovo) can be excluded from the roster here.

* **Review suggested merges.** When several records look like the same person, Settings → Developers shows **N suggested merges · Review**, and owners and admins see a suggested-merges banner on the other AI Adoption pages. Click **Review** to open the suggestions. Groups under **Ready to merge** are already selected; groups under **Needs your review** show the evidence for each match, such as the same email or a similar name. When nothing is suggested, **Find merges** uses GitKraken AI to process your developer records and find more possible merges. Merges cannot be undone.
* **Dismiss the banner.** Click **×** on the suggested-merges banner to hide it. It comes back when more records become mergeable, and the count stays on Settings → Developers.
* **Check visibility.** Each developer is **Included** (appears in lists and counts in metrics), **Hidden** (kept out of lists but still counts in metrics), or **Excluded** (dropped from both). The roster shows Included and Hidden developers by default. To find an excluded developer again, for example a bot you want to re-include, add **Excluded** to the **Visibility** filter.

For details, see [Suggested merges](/insights-expo/expo-ai-adoption-settings#suggested-merges) and [Developer visibility](/insights-expo/expo-ai-adoption-settings#developer-visibility) in the Settings reference.

### Organize your teams

Teams are how Insights groups developers for analysis. They power the **By Team** view, team cohorts in **By AI Tool & Model**, and the team filters across the dashboard. The **Default Department** setting pre-selects a department's teams in the filter bar when people first open the dashboard.

Open **Settings → Teams** and click **Add team**. Give the team a **Name** (for example, "Platform"), optionally choose or create a **Department** and add a team icon, pick its **Developers**, then click **Add team**. Repeat for each team — usually one per function or org unit. You can change membership any time from **Settings → Teams**.

A developer can belong to more than one team. Adding developers to a team keeps their other team memberships.

You can also assign teams from **Settings → Developers**:

* **One developer:** edit the developer and choose their **Teams**. With two or more teams, pick a **Primary team** — the team their work counts toward for delivery metrics. The roster's **Team** column lists the primary team first, with a **Primary** badge.
* **Several developers:** select them and use **Add to team** or **Remove from team**. Each action adds or removes one team and leaves the developers' other teams in place.

Only developers included in **Settings → Developers** can be added to a team; hidden or excluded developers aren't offered. As later syncs detect new commit authors, they become available to assign.

To group teams into departments and business units, open **Settings → Departments**. The Team filter then groups teams by business unit, then department. See [Departments settings](/insights-expo/expo-ai-adoption-settings#departments-settings).

> **Teams only need to be set up once** — they're reused across every view and filter. Start with your real org structure; you can split or merge teams later.

### Invite your team

From the gk.dev sidebar, open **Users** → **Add users**. Give each person at least an **Admin** or **Lead** role so they can view Insights.

## What to expect after setup

* **Git data:** the last month typically appears within a few hours, and older history follows within one to two days. How far back the first import reaches depends on your git provider.
* **AI tool data:** depends on the tool — telemetry starts with each developer's next session, while Cursor, GitHub Copilot, and Devin backfill recent history. See [When your AI tool data appears](#when-your-ai-tool-data-appears).
* **Sync status:** each connection on Data Connections shows health. A degraded status during the first sync is usually normal (API rate limits during backfill).
* **Data through dates:** when the data behind a card or table stops short of the range you selected, that card shows a **Data through** date with how far its data reaches. Each card reflects only the sources it reads, so one connection that falls behind flags only the cards that use it. Check that connection's **Status** on **Data Connections**.
* **Ranges ahead of the data:** if the whole range you select is later than the available data, views such as an expanded developer show **This range is ahead of the available data**, with the date the data reaches. Choose an earlier range.

### When your AI tool data appears

Telemetry-based tools report as developers work, so nothing appears until they start a session. API-based tools are pulled centrally by GitKraken, which starts syncing as soon as you click **Connect** and includes history from before you connected.

| Tool | First data | History before you connected | How current it stays |
| --- | --- | --- | --- |
| **Claude Code, OpenAI Codex, GitHub Copilot, OpenCode telemetry** (Agent OTel Data) | With each developer's next session after the config takes effect — allow up to 24 hours | None — no backfill | Usually within about half a day |
| **Cursor** | Usually within 4 hours | The last 90 days, finished within 24 hours | Up to the present — no reporting delay |
| **GitHub Copilot** (API connection) | Usually within 4 hours | Up to the last 90 days, one day at a time, finished within 24 hours | Always two to three days behind |
| **Devin** (available on request) | Usually within 4 hours | The last 90 days of session activity, counted by last activity, finished within 24 hours | Up to the present — no reporting delay |

* **Telemetry needs a fresh session.** Claude Code picks up its OpenTelemetry config on the next full restart, not on its hourly settings poll. A Codex session that was already running when the config was added won't report. Restart VS Code after adding the Copilot settings. If developers haven't started a fresh session, nothing is being sent yet — check that first. See [Connect Agent OTel Data](/insights-expo/expo-ai-adoption-connect-otel).
* **Copilot's lag is expected.** GitKraken waits for GitHub's daily usage report to settle before pulling it, so neither today nor yesterday appears. GitHub also returns no data for a day when the organization had fewer than **five members with active Copilot licenses** — those days are missing by design.
* **Give it a full 24 hours** before treating an empty dashboard as a problem — a large first backfill can legitimately take that long, and for Copilot that's on top of the reporting lag. To check sooner, open the connection's **Status** on **Data Connections**: a rate limit or a failed sync shows there right away.
* **Still nothing after 24 hours?** For telemetry, confirm developers have actively used the tool since restarting. For Cursor, confirm the key is a **team** key and hasn't been revoked. For Devin, confirm the API key belongs to a service user with the **Admin** role, and check the organization ID. For Copilot, re-check the usage-metrics policy, the token owner's permission to view Copilot metrics, SAML authorization if your organization uses SSO, and the organization slug. If everything looks right, contact your GitKraken account team.

## Troubleshooting setup

| Symptom | Fix |
| --- | --- |
| **Read-only banner** on Data Connections | Ask an org Owner or Admin to connect, or grant you access |
| **"Degraded" during first sync** | Usually rate-limit throttling during backfill — let it finish |
| **"Not yet synced"** | Expected right after connecting; for Agent OTel Data, confirm each agent's config is applied and developers have started a fresh session |
| **Developer appears twice** | Review suggested merges in Settings → Developers, or merge the records yourself — see [Map developer identities](#map-developer-identities) |

For provider-specific troubleshooting, see the individual connection pages. If a problem persists, contact your GitKraken account team.
