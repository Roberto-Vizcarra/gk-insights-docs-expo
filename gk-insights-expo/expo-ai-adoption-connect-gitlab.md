---
title: Connect GitLab
description: Connect GitLab Cloud or GitLab Self-Managed to GitKraken Insights — PAT creation, required scopes, and setup steps.
product: GitKraken Insights
content_type: how-to
audience: admin
plan_required: GitKraken Insights
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: blue
    card_description: Cloud and Self-Managed
    card_icon: brand-gitlab
    nav_category: connect-your-data
    nav_label: GitLab
    nav_order: 40
    page_type: content
---
<kbd>Last updated: November 2026</kbd>

Connect GitLab Cloud or GitLab Self-Managed to sync repository, pull request, and contributor activity into GitKraken Insights. Your git provider powers every PR, commit, contributor, and cycle-time metric.

> **Before you start:** make sure you have the right access. See the [prerequisites table](/insights-expo/expo-ai-adoption-connect-your-data#before-you-start-gather-access) on the Connect Your Data overview.

---

## GitLab

Connect GitLab to pull activity for your GitLab groups. It connects with a **personal access token**, either a legacy token or a fine-grained token. See [Choose a token type](#choose-a-token-type) for the permissions each one needs.

1. In **Data Connections**, click **+** next to **GitLab**.
2. Optionally name the connection, then create the token — the modal's **Create a token** link opens GitLab's token form for you.
3. Paste the **GitLab personal access token** and click **Validate**, then **Connect**.

GitLab personal access tokens start with `glpat-…`. The connection only sees the groups and projects the token's account can access.

### Choose a token type

The connection modal's **Required scopes** panel has 2 tabs, one for each kind of GitLab personal access token:

| Tab | What the token needs |
| --- | --- |
| **Legacy token** | The **`read_api`** scope |
| **Fine-grained token** | The user, group, and project permissions listed after this table |

A fine-grained token needs these permissions:

- **User permission:** **Group: Read**.
- **Group and project permissions:** **Project: Read**, **Merge Request: Read**, **Work Item: Read**, **Commit: Read**, **Repository: Read**, **Release: Read**, and **Repository Tag: Read**.
- **Optional:** **Pipeline: Read** for CI pipeline deployments, and **Protected Branch: Read** for the branch-protection signal.

For a fine-grained token, add each group you sync under **Group and project access**. The token then covers that group's subgroups too. You cannot use a token limited to individual projects.

---

## GitLab Self-Managed

Connect GitLab Self-Managed to sync activity from a GitLab instance you run yourself.

**Requirements:**

- The server must be reachable from GitKraken over `https` with a **publicly-trusted certificate**.
- A personal access token: a legacy token with the **`read_api`** scope, or a fine-grained token with the permissions in [Choose a token type](#choose-a-token-type). Fine-grained tokens require GitLab 18.10 or later.

1. In **Data Connections**, click **+** next to **GitLab Self-Managed**.
2. Optionally name the connection, then enter the **Server URL** — for example `https://gitlab.yourcompany.com`.
3. Paste the **GitLab personal access token** and click **Validate**, then **Connect**.

As with GitLab Cloud, the connection only sees the groups and projects the token's account can access.

---

## Troubleshooting

If a connection fails to validate, find the symptom below:

| Symptom | What's happening | Fix |
| --- | --- | --- |
| **GitLab Self-Managed does not validate** | GitKraken cannot reach it, or its certificate is not publicly trusted | Confirm the **Server URL** is reachable over `https` from outside your network with a publicly-trusted certificate. Self-signed certificates and internal CAs do not work |
| **"Your fine-grained GitLab token can't read projects in a group you selected."** | The token's **Group and project access** does not include a group you selected, or the token lacks **Project: Read** | Add that group to the token under **Group and project access** (its subgroups are included), and check that the token has **Project: Read** |
| **"Your fine-grained GitLab token is missing …"** | The fine-grained token lacks one or more of the permissions the connection needs | Create a token with every permission listed on the **Fine-grained token** tab, then try again |

For general connection troubleshooting, see [Troubleshooting setup](/insights-expo/expo-ai-adoption-connect-your-data#troubleshooting-setup) on the Connect Your Data overview.

---

## After connecting

After your connection is active, continue with the remaining setup steps on the [Connect Your Data](/insights-expo/expo-ai-adoption-connect-your-data) overview:

- [Set your benchmarks](/insights-expo/expo-ai-adoption-connect-your-data#set-your-benchmarks)
- [Map developer identities](/insights-expo/expo-ai-adoption-connect-your-data#map-developer-identities)
- [Invite your team](/insights-expo/expo-ai-adoption-connect-your-data#invite-your-team)
