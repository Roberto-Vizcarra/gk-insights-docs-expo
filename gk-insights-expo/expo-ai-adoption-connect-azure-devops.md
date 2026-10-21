---
title: Connect Azure DevOps
description: Connect Azure DevOps Cloud or Azure DevOps Server to GitKraken Insights — PAT creation, required scopes, and setup steps.
product: GitKraken Insights
content_type: how-to
audience: admin
plan_required: GitKraken Insights
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: blue
    card_description: Cloud and Server
    card_icon: brand-azure
    nav_category: connect-your-data
    nav_label: Azure DevOps
    nav_order: 30
    page_type: content
---
<kbd>Last updated: October 2026</kbd>

Connect Azure DevOps Cloud or Azure DevOps Server to sync repository, pull request, and contributor activity into GitKraken Insights. Your git provider powers every PR, commit, contributor, and cycle-time metric.

> **Before you start:** make sure you have the right access. See the [prerequisites table](/insights-expo/expo-ai-adoption-connect-your-data#before-you-start-gather-access) on the Connect Your Data overview.

---

## Azure DevOps

Connect Azure DevOps if your repositories live in an Azure DevOps organization. It connects with a **Personal Access Token (PAT)** with **Code (Read)** and **Build (Read)** — plus **Work Items (Read)** if you sync work items.

1. Sign in at `https://dev.azure.com/{yourOrgName}`, open **user settings** (top right) → **Personal access tokens**, and select **+ New Token**. Direct link: `https://dev.azure.com/{yourOrgName}/_usersSettings/tokens`.
2. Name the token, select the **organization** it applies to, set an expiration, and select the **Code → Read** and **Build → Read** scopes (add **Work Items → Read** if you sync work items). If you don't see a section, click **Show all scopes**.
3. Select **Create**, then copy the token immediately — Azure DevOps shows it only once.
4. In gitkraken.dev, open **Insights → Settings → Data Connections** and, under **Add data source**, click **+** next to **Azure DevOps**.
5. Optionally give the connection a **Name**, enter the **Host domain** — your organization URL, for example `https://dev.azure.com/yourOrgName` — paste the **Azure API token**, and click **Validate**. The **View all your organizations** link next to Host domain lists the organizations your Azure DevOps account belongs to.
6. Optionally choose **Projects (optional)**. Leave it empty to include every project.
7. Optionally add **Repositories to skip** — type a full `<org>/<project>/<repo>` path or pick from the list. Skipped repositories are excluded from sync and PR classification.
8. Optionally turn on work-item sync — see [Sync work items from Azure Boards](#sync-work-items-from-azure-boards) — then click **Connect**.

<figure>
  <img src="/wp-content/uploads/azure-devops-pat-tokens-menu.png" class="help-center-img img-bordered" alt="Azure DevOps user settings menu open in the top-right corner, listing Preview features, Profile, Time and Locale, Permissions, Notifications, Theme, Usage, Personal access tokens, and SSH public keys" />
  <figcaption>The Azure DevOps user settings menu — Personal access tokens is near the bottom.</figcaption>
</figure>

> Legacy `https://yourOrgName.visualstudio.com` URLs are also accepted as the host domain.

> **Note — Projects (optional) doesn't change what syncs.** Leave it empty to include every project. Projects you add there filter the repository list you browse under **Repositories to skip**; if you sync work items, they scope the work-item sync instead. Either way, they don't change which repositories are synced — use **Repositories to skip** for that.

> **Tip — change the project scope later.** Edit the connection to add or remove projects. If you don't validate a new token, type each project name and press Enter. The connection's row on **Data Connections** lists its projects after the organization.

> **Keep the PAT private.** It authenticates as your user account — treat it like a password.

> **PATs expire**, and org administrators can enforce a maximum lifetime (commonly 90 days). When the PAT expires, syncing stops — create a new one and update the connection.

> **Microsoft Entra ID organizations:** a PAT becomes inactive if its owner doesn't sign in to Azure DevOps within 90 days. Use an account that logs in regularly.

The connection only sees the projects and repositories the PAT's account can access. Reference: [Microsoft — Use personal access tokens](https://learn.microsoft.com/en-us/azure/devops/organizations/accounts/use-personal-access-tokens-to-authenticate).

---

## Azure DevOps Server

Connect Azure DevOps Server to pull activity from a self-hosted collection.

**Requirements:**

- **Azure DevOps Server 2022 or newer**, reachable from GitKraken over `https` with a **publicly-trusted certificate**.
- A personal access token with **Code (Read)** and **Build (Read)** — plus **Work Items (Read)** if you sync work items. Create it the same way as for Azure DevOps above.

1. In **Data Connections**, click **+** next to **Azure DevOps Server**.
2. Optionally name the connection, then enter the **Server URL** — for example `https://azuredevops.yourcompany.com`.
3. Enter the **Collection** — `DefaultCollection` unless your server uses a different one.
4. Paste the **Azure API token** and click **Validate**.
5. Optionally choose **Projects (optional)** and add **Repositories to skip**. They work the same way as for Azure DevOps above, and skipped repositories are excluded from sync and PR classification.
6. Optionally turn on work-item sync — see [Sync work items from Azure Boards](#sync-work-items-from-azure-boards) — then click **Connect**.

---

## Sync work items from Azure Boards

Azure DevOps and Azure DevOps Server connections can also sync work items from Azure Boards. Work-item sync links work items to pull requests for cycle time, planned work, and Change Failure Rate. The token needs the **Work Items (Read)** scope.

1. On the connection form, select **Sync work items (Azure Boards)**.
2. Optionally add **Projects to skip (optional)**. These projects are left out of the work-item sync; their repositories still sync.
3. To measure Change Failure Rate, select **Enable defect detection**, then choose how GitKraken identifies work items that count as customer-impacting defects under **Match by**:
   - **Work item type** — for example, `Bug`.
   - **Tag** — one or more work item tags.
   - **Area path** — one or more area paths, including their child paths.
   - **Field + value** — a work item field reference name, such as `Custom.CustomerBug`, and the values that mark a defect.
4. Click **Connect**, or save the connection if you're editing it.

If **Enable defect detection** is cleared, the connection doesn't count toward Change Failure Rate. See [Change Failure Rate](/insights-expo/expo-ai-adoption-dora-change-failure-rate).

---

## Troubleshooting

| Symptom | What's happening | Fix |
| --- | --- | --- |
| **An Azure DevOps project is missing after Validate** | The token's account can't access it | Check that account's permissions in Azure DevOps |
| **"We couldn't find the project" on the Projects field** | A project name you typed doesn't match a project in the organization | Check the spelling, or remove the project |
| **The token is valid, but a Microsoft Entra Conditional Access Policy is blocking GitKraken** | A Conditional Access policy in your tenant, including an IP-based policy, refuses the connection | Ask your Azure DevOps admin to exempt personal access tokens from the policy, or to allow GitKraken's IP address `34.199.117.219` |
| **Azure DevOps sync stopped** | The PAT expired, or (Entra ID orgs) its owner hasn't signed in for 90 days | Create a new PAT with the same scopes and use an account that logs in regularly |
| **Azure DevOps Server won't validate** | GitKraken can't reach it, or its certificate isn't publicly trusted | Confirm the **Server URL** is reachable over `https` from outside your network with a publicly-trusted certificate — self-signed and internal CAs won't work. Azure DevOps Server must be 2022 or newer |

For general connection troubleshooting, see [Troubleshooting setup](/insights-expo/expo-ai-adoption-connect-your-data#troubleshooting-setup) on the Connect Your Data overview.

---

## After connecting

Once your connection is active, continue with the remaining setup steps on the [Connect Your Data](/insights-expo/expo-ai-adoption-connect-your-data) overview:

- [Set your benchmarks](/insights-expo/expo-ai-adoption-connect-your-data#set-your-benchmarks)
- [Map developer identities](/insights-expo/expo-ai-adoption-connect-your-data#map-developer-identities)
- [Invite your team](/insights-expo/expo-ai-adoption-connect-your-data#invite-your-team)
