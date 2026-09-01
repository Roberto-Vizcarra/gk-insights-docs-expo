---
title: Connect Bitbucket
description: Connect Bitbucket Cloud or Bitbucket Data Center to GitKraken Insights — API tokens, required scopes, and setup steps.
product: GitKraken Insights
content_type: how-to
audience: admin
plan_required: GitKraken Insights
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: blue
    card_description: Cloud and Data Center
    card_icon: brand-bitbucket
    nav_category: connect-your-data
    nav_label: Bitbucket
    nav_order: 20
    page_type: content
---
<kbd>Last updated: September 2026</kbd>

Connect Bitbucket Cloud or Bitbucket Data Center to sync repository, pull request, and contributor activity into GitKraken Insights. Your git provider powers every PR, commit, contributor, and cycle-time metric.

> **Before you start:** make sure you have the right access. See the [prerequisites table](/insights-expo/expo-ai-adoption-connect-your-data#before-you-start--gather-access) on the Connect Your Data overview.

---

## Bitbucket

Connect Bitbucket if your repositories live in a Bitbucket workspace. Like GitHub, it powers every PR, commit, contributor, and cycle-time metric.

1. In gitkraken.dev, open **Insights → Settings → Data Connections**.
2. Under **Add data source**, click **+** next to **Bitbucket**.
3. In the **Connect Bitbucket** modal, optionally give the connection a **Name**, then enter your **Atlassian account email** and a **Bitbucket API token** (see scopes below).
4. Click **Validate**. Insights lists the workspaces the token can see — select the ones to sync, then click **Connect**.

<figure>
  <img src="/wp-content/uploads/connect-bitbucket-modal.png" class="help-center-img img-bordered" alt="Connect Bitbucket modal in GitKraken Insights showing an optional connection name field, the list of scoped Atlassian API token scopes required, and the Atlassian account email and Bitbucket API token fields" />
  <figcaption>The Connect Bitbucket modal — an optional connection name, the required token scopes, and the Atlassian account email and Bitbucket API token fields.</figcaption>
</figure>

### Required token scopes

Bitbucket connects with a **scoped Atlassian API token**. Create it at [**id.atlassian.com → Security → API tokens**](https://id.atlassian.com/manage-profile/security/api-tokens) using **Create API token with scopes**. Name it, set an expiration (Atlassian allows 1–365 days), choose **Bitbucket** as the app, and include at least these scopes:

- `read:account`
- `read:pipeline:bitbucket`
- `read:pullrequest:bitbucket`
- `read:repository:bitbucket`
- `admin:repository:bitbucket`
- `read:workspace:bitbucket`

Copy the token immediately — Atlassian shows it only once. Scoped Bitbucket tokens start with `ATATT…`.

> **Tokens expire.** When the token reaches its expiration date, syncing stops. Create a new token and update the connection before that happens.

> **A workspace missing after Validate** means the token's Atlassian account can't access it. Check that account's Bitbucket permissions.

Once connected, Bitbucket data begins syncing in the background and continues over the next several hours. Reference: [Atlassian — Manage API tokens](https://support.atlassian.com/atlassian-account/docs/manage-api-tokens-for-your-atlassian-account/).

---

## Bitbucket Data Center

Connect Bitbucket Data Center to sync activity from a Bitbucket instance you run yourself.

**Requirements:**

- The server must be reachable from GitKraken over `https` with a **publicly-trusted certificate**.
- An HTTP access token with read access to the projects and repositories you want to sync. 

1. In **Data Connections**, click **+** next to **Bitbucket Data Center**.
2. Optionally name the connection, then enter the **Server URL** — for example `https://bitbucket.yourcompany.com`.
3. Paste the **Bitbucket access token** and click **Validate**, then **Connect**.

As with Bitbucket cloud, the connection only sees the projects and repositories the token's account can access.

---

## Troubleshooting

| Symptom | What's happening | Fix |
| --- | --- | --- |
| **A Bitbucket workspace is missing after Validate** | The token's account can't access it | Check that account's permissions in Bitbucket |
| **Bitbucket Data Center won't validate** | GitKraken can't reach it, or its certificate isn't publicly trusted | Confirm the **Server URL** is reachable over `https` from outside your network with a publicly-trusted certificate — self-signed and internal CAs won't work |

For general connection troubleshooting, see [Troubleshooting setup](/insights-expo/expo-ai-adoption-connect-your-data#troubleshooting-setup) on the Connect Your Data overview.

---

## After connecting

Once your connection is active, continue with the remaining setup steps on the [Connect Your Data](/insights-expo/expo-ai-adoption-connect-your-data) overview:

- [Set your benchmarks](/insights-expo/expo-ai-adoption-connect-your-data#set-your-benchmarks)
- [Map developer identities](/insights-expo/expo-ai-adoption-connect-your-data#map-developer-identities)
- [Invite your team](/insights-expo/expo-ai-adoption-connect-your-data#invite-your-team)
