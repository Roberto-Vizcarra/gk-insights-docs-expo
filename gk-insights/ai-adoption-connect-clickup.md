---
title: Connect ClickUp
description: Connect ClickUp to GitKraken Insights to pull task activity and detect defects for Change Failure Rate and MTTR.
product: GitKraken Insights
content_type: how-to
audience: admin
plan_required: GitKraken Insights
integrations: [ClickUp]
status: GA
taxonomy:
    category: gk-insights
custom_fields:
    card_color: blue
    card_description: Task activity from ClickUp
    card_icon: checklist
    nav_category: connect-your-data
    nav_label: ClickUp
    nav_order: 67
    page_type: content
---
<kbd>Last updated: November 2026</kbd>

Connect **ClickUp** to pull task activity from the spaces you list, and to flag which tasks count as defects for Change Failure Rate and MTTR. ClickUp is not required to see data. For other issue trackers, see [Connect Jira & BambooHR](/gk-insights/ai-adoption-connect-jira-bamboohr) and [Connect Linear & YouTrack](/gk-insights/ai-adoption-connect-linear-youtrack).

> **Before you start:** make sure you have the right access. Only organization Owners and Admins can add data connections. See the [prerequisites table](/gk-insights/ai-adoption-connect-your-data#before-you-start-gather-access) on the Connect Your Data overview.

Each connection covers one ClickUp workspace. To sync another workspace, add another ClickUp connection.

---

## Create a ClickUp API token

Insights connects with a **personal API token**. The token belongs to a ClickUp user and sees what that user can see, so create it with an account that can see every space you want to sync.

1. Sign in to ClickUp with the account you want to use.
2. Click your avatar and select **Settings**.
3. In the sidebar, select **Apps**.
4. Under **API Token**, click **Generate**. If a token already exists, you can reuse it. Clicking **Regenerate** replaces it and breaks any other integration that uses the current token.
5. Copy the token. ClickUp personal API tokens start with `pk_`.

> **Keep the token private.** It grants access to everything its account can see. ClickUp personal tokens do not expire; if you regenerate one in ClickUp, syncing stops until you enter the new token in the connection.

## Connect ClickUp

Before you connect, gather these:

* The **ClickUp API token** from the previous section.
* The **workspace** name, exactly as it appears in ClickUp.
* The names of the **spaces** whose tasks Insights should pull, for example `Engineering`.

To connect ClickUp:

1. In **Data Connections**, under **Add data source**, click **+** next to **ClickUp**.
2. Optionally, enter a **Name** for the connection.
3. Paste the token into **ClickUp API token**.
4. In **Workspace**, enter the workspace name. Insights matches it exactly, including case.
5. In **Spaces**, list the spaces to pull, separated by commas, for example `Engineering, Design`. Insights pulls only these spaces and matches their names exactly, including case.
6. Optionally, in **Skip lists**, list the lists to leave out, even inside the spaces above, for example `Archive, Scratch`.
7. Optionally, set up [defect detection](#detect-defects-for-change-failure-rate).
8. Click **Connect**. GitKraken checks the token and confirms that it can see the workspace and every listed space. If it cannot, the form names the workspace or space so you can fix the spelling or remove it.

The connection sees only the spaces, lists, and tasks that the token's account can access.

## Detect defects for Change Failure Rate

Defect detection tells Insights which ClickUp tasks count as defects, for [Change Failure Rate (CFR)](/gk-insights/ai-adoption-dora-change-failure-rate) and [Mean Time to Recovery (MTTR)](/gk-insights/ai-adoption-dora-mttr). Without it, the connection still syncs tasks but does not feed those two metrics. CFR also needs releases tracked in **Settings → Releases**; see [Configure Change Failure Rate](/gk-insights/ai-adoption-connect-jira-bamboohr#configure-change-failure-rate-cfr).

To set it up on a connection:

1. Select **Enable defect detection**.
2. In **Match by**, choose how Insights recognizes a defect.
3. Enter the values to match, separated by commas. Insights matches values exactly, including case.

Each **Match by** option takes these values:

| Match by | What you enter |
| --- | --- |
| **Task type** | The task types that mark a defect, for example `Bug` |
| **Tag** | The tags that mark a defect, for example `bug, regression` |
| **List** | The lists whose tasks are defects, for example `Bugs` |
| **Custom field value** | The **Custom field** name as it appears in ClickUp, for example `Type`, and the values that mark a defect, for example `Bug, Defect` |

To recognize defects more than one way, click **Add defect rule**. Each connection takes up to 5 rules, and the counter beside the button shows how many you've added. With two or more rules, give each one a **Name**, for example `Customer bug`, so you can tell them apart in the **Definition** picker on bug charts. Two rules cannot match exactly the same tasks.

## Verify the connection

After you connect, open the connection's **Status** in **Data Connections**. The **ClickUp Tasks** pipeline shows how many tasks have synced. When defect detection is on, the **Change Failure Rate** pipeline shows how many incidents it found.

To change the connection later, open it and click **Save changes** after you edit. To keep the current token, leave **ClickUp API token** blank.

---

## Troubleshooting

If the connection fails or CFR stays empty, find the symptom below:

| Symptom | What's happening | Fix |
| --- | --- | --- |
| **"ClickUp didn't accept this token."** | The token is wrong, or it was regenerated in ClickUp | Check the token, or create a new one and paste it in |
| **"This token can't see a workspace named …"** | The workspace name does not match ClickUp exactly, or the token's account is not in that workspace | Check the spelling and case of the workspace name |
| **"This token can't see a space named …"** | The space name does not match ClickUp exactly, or the token's account cannot see that space | Check the spelling and case, or remove the space from **Spaces** |
| **CFR shows zeros or "Not configured"** | Defect detection is off, the match values do not match any task, or no releases are tracked | Turn on **Enable defect detection**, check the values' exact spelling and case, and set a release **Signal** per repository in **Settings → Releases** |

For general connection troubleshooting, see [Troubleshooting setup](/gk-insights/ai-adoption-connect-your-data#troubleshooting-setup) on the Connect Your Data overview.

---

## After connecting

After your connection is active, continue with the remaining setup steps on the [Connect Your Data](/gk-insights/ai-adoption-connect-your-data) overview:

- [Set your benchmarks](/gk-insights/ai-adoption-connect-your-data#set-your-benchmarks)
- [Map developer identities](/gk-insights/ai-adoption-connect-your-data#map-developer-identities)
- [Invite your team](/gk-insights/ai-adoption-connect-your-data#invite-your-team)
