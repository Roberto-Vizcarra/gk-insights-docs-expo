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
<kbd>Last updated: November 2026</kbd>

Connect **Linear**, **YouTrack**, or **YouTrack Server** to Insights. Each connection pulls issue activity for the teams or projects you list, and can flag which issues count as defects for Change Failure Rate and MTTR. Neither tracker is required to see data. To connect Jira instead, see [Connect Jira & BambooHR](/insights-expo/expo-ai-adoption-connect-jira-bamboohr). For ClickUp, see [Connect ClickUp](/insights-expo/expo-ai-adoption-connect-clickup).

> **Before you start:** make sure you have the right access. Only organization Owners and Admins can add data connections. See the [prerequisites table](/insights-expo/expo-ai-adoption-connect-your-data#before-you-start-gather-access) on the Connect Your Data overview.

You can add more than one connection for each tracker, for example one per Linear workspace or per YouTrack instance.

---

## Linear

Before you connect, gather these:

* A **personal API key**. Create one in Linear under **Settings → Security & access**. A key with **Read** access is enough, and you can limit it to the teams you sync. Linear API keys start with `lin_api_`.
* Your **workspace URL**: the `linear.app` address of the workspace to sync, for example `https://linear.app/acme`.
* The **team keys** of the teams whose issues Insights should pull, for example `ENG` and `DES`. A team key is 1 to 5 uppercase characters.

To connect Linear:

1. In **Data Connections**, under **Add data source**, click **+** next to **Linear**.
2. Optionally, enter a **Name** for the connection.
3. Paste the key into **Linear API key** and enter the **Workspace URL**.
4. In **Team keys**, list the teams to pull, separated by commas. Insights pulls only these teams. List at least 1 team and at most 50.
5. Optionally, in **Skip team keys**, list teams to exclude even if they're listed above.
6. Optionally, set up [defect detection](#detect-defects-for-change-failure-rate).
7. Click **Connect**. GitKraken checks the key and confirms that it can see every listed team. If the key cannot see a team, GitKraken does not save the connection.

The connection sees only the teams that the key's account can access. If the key is revoked in Linear, syncing stops until you enter a new key.

Insights counts a Linear issue as started when it first moves into a **Started**-type state, and as resolved when it reaches a **Completed**-type state. A canceled issue does not count as resolved.

## YouTrack and YouTrack Server

Use **YouTrack** for a `youtrack.cloud` instance, and **YouTrack Server** for a self-hosted instance. The two forms are the same except for the instance URL.

Before you connect, gather these:

* A **permanent token**. Create one from your YouTrack profile under **Account Security → Authentication**, and under **Scope** select **YouTrack**. Permanent tokens start with `perm:`. They do not expire, but syncing stops if the token is deleted.
* Your **instance URL**:
    * For **YouTrack**, the `youtrack.cloud` address of the instance, for example `https://acme.youtrack.cloud`. Older `myjetbrains.com` addresses also work.
    * For **YouTrack Server**, the `https` address of your server, including its context path if it uses one, for example `https://youtrack.example.com/youtrack`. Insights must be able to reach the server.
* The **short names** of the projects whose issues Insights should pull, for example `ENG` and `DES`. A short name is 2 to 10 uppercase characters.

To connect YouTrack or YouTrack Server:

1. In **Data Connections**, under **Add data source**, click **+** next to **YouTrack** or **YouTrack Server**.
2. Optionally, enter a **Name** for the connection.
3. Paste the token into **Permanent token** and enter the **Instance URL**.
4. In **Projects**, list the project short names to pull, separated by commas. Insights pulls only these projects. List at least 1 project and at most 50.
5. Optionally, in **Skip projects**, list projects to exclude even if they're listed above.
6. Optionally, in **In-progress states**, list the state names that mean work has started, for example `In Progress`. If you leave the field blank, Insights uses the default states, `In Progress` and `In Development`.
7. Optionally, set up [defect detection](#detect-defects-for-change-failure-rate).
8. Click **Connect**. GitKraken checks the token, confirms that the listed projects are visible, and test-runs any defect rules before it saves the connection.

## Detect defects for Change Failure Rate

Defect detection tells Insights which issues count as defects, for [Change Failure Rate (CFR)](/insights-expo/expo-ai-adoption-dora-change-failure-rate) and [Mean Time to Recovery (MTTR)](/insights-expo/expo-ai-adoption-dora-mttr). CFR also needs releases tracked in **Settings → Releases**; see [Configure Change Failure Rate](/insights-expo/expo-ai-adoption-connect-jira-bamboohr#configure-change-failure-rate-cfr).

To set it up on a connection:

1. Select **Enable defect detection**.
2. In **Match by**, choose how Insights recognizes a defect.
3. Enter the values to match, separated by commas. Insights matches values exactly, including case.

To recognize defects more than one way, click **Add defect rule**. Each connection takes up to 5 rules, and the counter beside the button shows how many you've added. With two or more rules, give each one a **Name**, for example `Customer bug`, so you can tell them apart in the **Definition** picker on bug charts. Two rules cannot match exactly the same issues.

The **Match by** options depend on the tracker:

| Tracker | Match by | What you enter |
| --- | --- | --- |
| Linear | **Label**, **Project**, or **Team** | The labels, projects, or teams whose issues are defects, for example `Bug, Defect` |
| YouTrack and YouTrack Server | **Tag**, **Issue type**, **Project**, or **Custom field value** | The tags, issue types, projects, or values that mark a defect, for example `Bug` |

For **Custom field value**, also enter the **Field name** of the YouTrack custom field to match on, for example `Root Cause`.

## Link issues to pull requests

Insights links an issue to a pull request when the issue's ID, for example `ENG-123`, appears in the pull request's title or branch name. Insights links only IDs from the teams or projects the connection pulls. Write the ID in uppercase. A lowercase ID, such as `eng-123` in a branch name, does not link. The [AI Sessions Issues tab](/insights-expo/expo-ai-adoption-ai-sessions#see-cost-by-issue-on-the-issues-tab) uses these links to roll sessions up into issues.

## Verify the connection

After you connect, open the connection's **Status** in **Data Connections**. The **Linear Issues** or **YouTrack Issues** pipeline shows how many issues have synced. When defect detection is on, the **Change Failure Rate** pipeline shows how many incidents it found.

To change a connection later, open it and click **Save changes** after you edit. To keep the current key or token, leave its field blank.

---

## Troubleshooting

If CFR stays empty, check the following:

| Symptom | What's happening | Fix |
| --- | --- | --- |
| **CFR shows zeros or "Not configured"** | Defect detection is off, the match values do not match any issue, or no releases are tracked | Turn on **Enable defect detection**, check the values' exact spelling and case, and set a release **Signal** per repository in **Settings → Releases** |

For general connection troubleshooting, see [Troubleshooting setup](/insights-expo/expo-ai-adoption-connect-your-data#troubleshooting-setup) on the Connect Your Data overview.

---

## After connecting

After your connection is active, continue with the remaining setup steps on the [Connect Your Data](/insights-expo/expo-ai-adoption-connect-your-data) overview:

- [Set your benchmarks](/insights-expo/expo-ai-adoption-connect-your-data#set-your-benchmarks)
- [Map developer identities](/insights-expo/expo-ai-adoption-connect-your-data#map-developer-identities)
- [Invite your team](/insights-expo/expo-ai-adoption-connect-your-data#invite-your-team)
