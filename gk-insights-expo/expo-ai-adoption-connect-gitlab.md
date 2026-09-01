---
title: Connect GitLab
description: Connect GitLab Cloud or GitLab Self-Managed to GitKraken Insights — PAT creation, required scopes, and setup steps.
product: GitKraken Insights
content_type: how-to
audience: admin
plan_required: GitKraken Insights
status: GA
page_type: content
nav_category: connect-your-data
nav_order: 40
nav_label: GitLab
card_icon: brand-gitlab
card_color: blue
card_description: Cloud and Self-Managed
taxonomy:
    category: insights-expo
---
<kbd>Last updated: September 2026</kbd>

Connect GitLab Cloud or GitLab Self-Managed to sync repository, pull request, and contributor activity into GitKraken Insights. Your git provider powers every PR, commit, contributor, and cycle-time metric.

> **Before you start:** make sure you have the right access. See the [prerequisites table](/gk-insights/ai-adoption-connect-your-data#before-you-start--gather-access) on the Connect Your Data overview.

---

## GitLab

Connect GitLab to pull activity for your GitLab groups. It connects with a **personal access token** carrying the **`read_api`** scope.

1. In **Data Connections**, click **+** next to **GitLab**.
2. Optionally name the connection, then create the token — the modal's **Create a token** link opens GitLab's token form for you.
3. Paste the **GitLab personal access token** and click **Validate**, then **Connect**.

GitLab personal access tokens start with `glpat-…`. The connection only sees the groups and projects the token's account can access.

---

## GitLab Self-Managed

Connect GitLab Self-Managed to sync activity from a GitLab instance you run yourself.

**Requirements:**

- The server must be reachable from GitKraken over `https` with a **publicly-trusted certificate**.
- A personal access token with the **`read_api`** scope.

1. In **Data Connections**, click **+** next to **GitLab Self-Managed**.
2. Optionally name the connection, then enter the **Server URL** — for example `https://gitlab.yourcompany.com`.
3. Paste the **GitLab personal access token** and click **Validate**, then **Connect**.

As with GitLab cloud, the connection only sees the groups and projects the token's account can access.

---

## Troubleshooting

| Symptom | What's happening | Fix |
| --- | --- | --- |
| **GitLab Self-Managed won't validate** | GitKraken can't reach it, or its certificate isn't publicly trusted | Confirm the **Server URL** is reachable over `https` from outside your network with a publicly-trusted certificate — self-signed and internal CAs won't work |

For general connection troubleshooting, see [Troubleshooting setup](/gk-insights/ai-adoption-connect-your-data#troubleshooting-setup) on the Connect Your Data overview.

---

## After connecting

Once your connection is active, continue with the remaining setup steps on the [Connect Your Data](/gk-insights/ai-adoption-connect-your-data) overview:

- [Set your benchmarks](/gk-insights/ai-adoption-connect-your-data#set-your-benchmarks)
- [Map developer identities](/gk-insights/ai-adoption-connect-your-data#map-developer-identities)
- [Invite your team](/gk-insights/ai-adoption-connect-your-data#invite-your-team)
