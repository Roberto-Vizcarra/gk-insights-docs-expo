---
title: Connect Linear & YouTrack
description: Connect Linear, YouTrack, or YouTrack Server to GitKraken Insights to pull issue activity and detect defects for Change Failure Rate.
product: GitKraken Insights
content_type: how-to
audience: admin
plan_required: GitKraken Insights
integrations: [Linear, YouTrack, YouTrack Server]
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: blue
    card_description: Issue tracking from Linear and YouTrack
    card_icon: list-check
    nav_category: connect-your-data
    nav_label: Linear & YouTrack
    nav_order: 65
    page_type: content
---
<kbd>Last updated: October 2026</kbd>

This page explains how to connect **Linear**, **YouTrack**, and **YouTrack Server** to Insights. Each connection pulls issue activity for the teams or projects you list, and can flag which issues count as defects for Change Failure Rate and MTTR. Neither tracker is required to see data. To connect Jira instead, see [Connect Jira & BambooHR](/insights-expo/expo-ai-adoption-connect-jira-bamboohr).

> **Before you start:** make sure you have the right access. Only organization Owners and Admins can add data connections. See the [prerequisites table](/insights-expo/expo-ai-adoption-connect-your-data#before-you-start-gather-access) on the Connect Your Data overview.

You can add more than one connection for each tracker, for example one per Linear workspace or per YouTrack instance.

---

## Linear

Before you connect, gather these:

* A **personal API key**. Create one in Linear under **Settings → Security & access**.
* Your **workspace URL**: the `linear.app` address of the workspace to sync, for example `https://linear.app/acme`.
* The **team keys** of the teams whose issues Insights should pull, for example `ENG` and `DES`.

To connect Linear:

1. In **Data Connections**, under **Add data source**, click **+** next to **Linear**.
2. Optionally, enter a **Name** for the connection.
3. Paste the key into **Linear API key** and enter the **Workspace URL**.
4. In **Team keys**, list the teams to pull, separated by commas. Insights pulls only these teams.
5. Optionally, in **Skip team keys**, list teams to exclude even if they're listed above.
6. Optionally, set up [defect detection](#detect-defects-for-change-failure-rate).
7. Click **Connect**.

## YouTrack and YouTrack Server

Use **YouTrack** for a `youtrack.cloud` instance, and **YouTrack Server** for a self-hosted instance. The two forms are the same except for the instance URL.

Before you connect, gather these:

* A **permanent token**. Create one from your YouTrack profile under **Account Security → Authentication**.
* Your **instance URL**:
    * For **YouTrack**, the `youtrack.cloud` address of the instance, for example `https://acme.youtrack.cloud`.
    * For **YouTrack Server**, the `https` address of your server, including its context path if it uses one, for example `https://youtrack.example.com/youtrack`. Insights must be able to reach the server.
* The **short names** of the projects whose issues Insights should pull, for example `ENG` and `DES`.

To connect YouTrack or YouTrack Server:

1. In **Data Connections**, under **Add data source**, click **+** next to **YouTrack** or **YouTrack Server**.
2. Optionally, enter a **Name** for the connection.
3. Paste the token into **Permanent token** and enter the **Instance URL**.
4. In **Projects**, list the project short names to pull, separated by commas. Insights pulls only these projects.
5. Optionally, in **Skip projects**, list projects to exclude even if they're listed above.
6. Optionally, in **In-progress states**, list the state names that mean work has started, for example `In Progress`. If you leave the field blank, Insights uses the default states.
7. Optionally, set up [defect detection](#detect-defects-for-change-failure-rate).
8. Click **Connect**.

## Detect defects for Change Failure Rate

Defect detection tells Insights which issues count as defects, for [Change Failure Rate (CFR)](/insights-expo/expo-ai-adoption-dora-change-failure-rate) and [Mean Time to Recovery (MTTR)](/insights-expo/expo-ai-adoption-dora-mttr). CFR also needs releases tracked in **Settings → Releases**; see [Configure Change Failure Rate](/insights-expo/expo-ai-adoption-connect-jira-bamboohr#configure-change-failure-rate-cfr).

To set it up on a connection:

1. Select **Enable defect detection**.
2. In **Match by**, choose how Insights recognizes a defect.
3. Enter the values to match, separated by commas.

The **Match by** options depend on the tracker:

| Tracker | Match by | What you enter |
| --- | --- | --- |
| Linear | **Label**, **Project**, or **Team** | The labels, projects, or teams whose issues are defects, for example `Bug, Defect` |
| YouTrack and YouTrack Server | **Tag**, **Issue type**, **Project**, or **Custom field value** | The tags, issue types, projects, or values that mark a defect, for example `Bug` |

For **Custom field value**, also enter the **Field name** of the YouTrack custom field to match on, for example `Root Cause`. Values are matched exactly, including case.

## Verify the connection

After you connect, open the connection's **Status** in **Data Connections**. The **Linear Issues** or **YouTrack Issues** pipeline shows how many issues have synced. When defect detection is on, the **Change Failure Rate** pipeline shows how many incidents it found.

To change a connection later, open it and click **Save changes** after you edit. To keep the current key or token, leave its field blank.

---

## Troubleshooting

| Symptom | What's happening | Fix |
| --- | --- | --- |
| **CFR shows zeros or "Not configured"** | Defect detection is off, the match values don't match any issue, or no releases are tracked | Turn on **Enable defect detection**, check the values' exact spelling and case, and set a release **Signal** per repository in **Settings → Releases** |

For general connection troubleshooting, see [Troubleshooting setup](/insights-expo/expo-ai-adoption-connect-your-data#troubleshooting-setup) on the Connect Your Data overview.

---

## After connecting

Once your connection is active, continue with the remaining setup steps on the [Connect Your Data](/insights-expo/expo-ai-adoption-connect-your-data) overview:

- [Set your benchmarks](/insights-expo/expo-ai-adoption-connect-your-data#set-your-benchmarks)
- [Map developer identities](/insights-expo/expo-ai-adoption-connect-your-data#map-developer-identities)
- [Invite your team](/insights-expo/expo-ai-adoption-connect-your-data#invite-your-team)
