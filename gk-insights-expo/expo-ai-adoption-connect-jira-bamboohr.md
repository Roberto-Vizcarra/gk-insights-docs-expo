---
title: Connect Jira & BambooHR
description: Connect Jira Cloud or Jira Data Center for Change Failure Rate tracking and BambooHR for PTO sync in GitKraken Insights.
product: GitKraken Insights
content_type: how-to
audience: admin
plan_required: GitKraken Insights
integrations: [Jira Cloud, Jira Data Center, BambooHR]
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: blue
    card_description: Issue tracking and PTO sync
    card_icon: ticket
    nav_category: connect-your-data
    nav_label: Jira & BambooHR
    nav_order: 60
    page_type: content
---
<kbd>Last updated: November 2026</kbd>

Jira and BambooHR are optional. Insights shows data without them, and each one adds detail: Jira adds cycle-time start signals and customer-bug data, and BambooHR adds time off. You can connect them now or any time later.

> **Before you start:** make sure you have the right access. See the [prerequisites table](/insights-expo/expo-ai-adoption-connect-your-data#before-you-start-gather-access) on the Connect Your Data overview.

---

## Jira

Jira gives Insights cycle-time start signals and customer-bug data, so AI Impact compares like-for-like work across tools.

Use the **Jira** tile for Jira Cloud and the **Jira Data Center** tile for a self-hosted Jira server. For Jira Data Center, see [Connect Jira Data Center](#connect-jira-data-center).

To connect Jira Cloud:

1. Go to [**id.atlassian.com → Security → API tokens**](https://id.atlassian.com/manage-profile/security/api-tokens).
2. Click **Create API token** — **not** "Create API token with scopes."
3. In **Data Connections**, click **+** next to **Jira** and enter:
   - the **API token**,
   - the **account email** the token was created for,
   - your **Jira Cloud instance URL**: the `https` address of your Atlassian site, for example `https://myorg.atlassian.net`.
4. Optionally expand **Advanced** and enter project key prefixes in **Only include these projects** (for example, `PROJ, ENG`) or **Exclude these projects**.
5. To sync another Atlassian site with the same token, click **Add Jira instance** and enter its URL. Each instance has its own **Advanced** project filters and defect detection.
6. Click **Connect**. GitKraken checks the credentials against each site, confirms that the listed projects are visible, and test-runs any defect rules before it saves the connection.

> **Each Jira instance can belong to only one Jira connection.** Add a second Jira connection for a different site, not to split one site's projects. Leaving **Only include these projects** blank includes every project on that instance.

> **Scoped tokens are not supported yet.** Jira's newer "API token with scopes" option does not work. Use the plain **Create API token** option. The link in step 1 opens the Atlassian token page directly.

### Connect Jira Data Center

Jira Data Center connects with a **personal access token** instead of an API token and account email. Each connection covers one server; to sync another server, add another **Jira Data Center** connection.

The server must be reachable from GitKraken at an `https` URL. For a Jira Cloud site on `atlassian.net`, use the **Jira** tile instead.

1. In Jira Data Center, open your profile → **Personal Access Tokens** and create a token.
2. In **Data Connections**, click **+** next to **Jira Data Center**.
3. Optionally, enter a **Name** for the connection.
4. Paste the token into **Personal access token**.
5. Enter the **Server URL**: the `https` address of your Jira Data Center server, including its context path if it uses one, for example `https://jira.example.com`. You cannot change the server URL after you save the connection.
6. Optionally expand **Advanced** and fill in **Only include these projects** or **Exclude these projects**, as for Jira Cloud.
7. Optionally, set up defect detection, as described in [Configure Change Failure Rate (CFR)](#configure-change-failure-rate-cfr).
8. Click **Connect**.

To change the connection later, open it and click **Save changes**. To keep the current token, leave **Personal access token** blank.

### Link issues to pull requests

Insights links a Jira issue to a pull request when the issue key, for example `PROJ-123`, appears in the pull request's title or branch name. Write the key in uppercase, as Jira shows it. You do not need to set anything up in Jira. The [AI Sessions Issues tab](/insights-expo/expo-ai-adoption-ai-sessions#see-cost-by-issue-on-the-issues-tab) uses these links to roll sessions up into issues.

### Configure Change Failure Rate (CFR)

Change Failure Rate (CFR) is a DORA stability metric — see [Change Failure Rate (CFR)](/insights-expo/expo-ai-adoption-dora-change-failure-rate) for what it measures and how to read it. The setup in this section fills the CFR cards on Throughput & Quality, Board Metrics, Executive, and AI Impact.

CFR can read customer bugs from any connected issue tracker with defect detection turned on: Jira, Jira Data Center, Linear, YouTrack, ClickUp, or Azure Boards (an Azure DevOps connection with work-item sync). One configured tracker is enough. For the other trackers, see [Connect Linear and YouTrack](/insights-expo/expo-ai-adoption-connect-linear-youtrack), [Connect ClickUp](/insights-expo/expo-ai-adoption-connect-clickup), and [Sync work items from Azure Boards](/insights-expo/expo-ai-adoption-connect-azure-devops#sync-work-items-from-azure-boards).

CFR needs two pieces of configuration. Until both are set, those cards show zeros even when Jira is connected and healthy.

**1. Tell Insights which Jira issues are customer bugs.**

**Where the setting is:** Insights → **Settings → Data Connections** → the **Jira** or **Jira Data Center** connection → **Edit** → **Enable defect detection**, under each Jira instance URL.

1. Select **Enable defect detection**.
2. Under **Match by**, choose how GitKraken identifies Jira issues that count as customer-impacting defects:
   - **Custom field** — enter the **Custom field ID** of the field your team uses to flag customer-reported defects, for example `customfield_10042`.
   - **Jira labels** — one or more labels.
   - **Issue types** — one or more issue-type names.
   - **Field + value** — a standard or custom Jira field and the values that mark a defect.

To recognize defects more than one way, click **Add defect rule**. Each Jira instance takes up to 5 rules, and the counter beside the button shows how many you've added. With two or more rules, give each one a **Name**, for example `Customer bug`, so you can tell them apart in the **Definition** picker on bug charts. Two rules cannot match exactly the same issues.

If you have more than one Jira instance, each one has its own defect detection setting. Changing **Enable defect detection** or **Match by** recalculates that connection's Change Failure Rate history, so past figures can change.

> **Finding the field ID:** In Jira, go to **Settings → Issues → Custom fields**, locate your "Customer Bug" field, and open **⋯ → Edit details** — the ID appears as `customfield_NNNNN` in the page URL. Admins can also list every field at `https://<your-site>.atlassian.net/rest/api/3/field` and match by name.

> **Defect detection that is turned off is the most common reason CFR shows zeros.** Without it, Insights counts no Jira issues as customer bugs, so it cannot calculate CFR, even with Jira fully connected.

**2. Make sure releases are being tracked.** CFR is *failing releases ÷ total releases*, so Insights needs to know what counts as a release. Go to **Settings → Releases** and set the **Signal** for each repository. The options depend on the repo's git provider:

- **Auto-detect** (default) — picks the release source the repo's provider offers. The **Detected** column shows what it found on the last sync.
- **GitHub** repos: **GitHub releases**, **Git tags**, **Workflow file** (a GitHub Actions workflow, for example `cd.yaml`), or **Release branch merge**.
- **Azure DevOps** repos: **Git tags**, **Azure pipeline** (a pipeline definition), or **Release branch merge**.
- **GitLab** repos: **GitLab releases**, **Git tags**, **GitLab job** (the job that deploys, for example `deploy`), **GitLab pipeline** (a pipeline name; a GitLab pipeline only has a name when `.gitlab-ci.yml` sets `workflow:name`), or **Release branch merge**.
- **Bitbucket** repos: **Git tags**, **Bitbucket Pipelines** (Bitbucket Cloud only), or **Release branch merge**.
- **Skip** — do not track releases for that repo.

> **Release branch merge** needs a **base branch** per repository, so the bulk action does not offer it. Set it one repo at a time.

To count only some merges, turn on **Only certain branches** and enter a **Branch pattern (regex)**. The pattern is a case-sensitive regular expression on the source branch, not a wildcard. It matches anywhere in the branch name unless you anchor it with `^` and `$`, for example `^release/[0-9]+\.[0-9]+\.[0-9]+$`. When a pattern looks like a wildcard or is not anchored, the field shows a warning and a **Use** button that applies a suggested pattern.

Changes to release detection apply from the next sync. After syncing completes, confirm the **# Releases** column shows a non-zero count.

**Push releases from your own tooling.** If your deployments do not produce a signal Insights can detect, you can send releases to Insights yourself with the [Manual Releases API](/insights-expo/expo-ai-adoption-manual-releases-api). For example, use it for an external CI/CD system, a platform GitKraken does not read, or a pipeline that does not tag releases. Create an API key in the **Security** tab of your [gitkraken.dev account](https://gitkraken.dev/account), then POST each release. API keys are available on request; see the Manual Releases API page. Insights tracks manual releases alongside detected ones, and you can backfill historical releases the same way.

**What counts as a failure:** a release fails when a customer bug (matching your defect detection setting), of any priority, is created after the release ships and before the next release. Severity comes directly from the Jira **priority** field, so keep priorities consistent. Insights ranks priority names other than the standard ones, such as `Critical (P1)`, by their order in your Jira priority scheme, most severe first.

**Verify it's working:** on the Jira connection, open **Status** and confirm the **Change Failure Rate** pipeline is healthy. Then check the **Customer Bugs & Change Failure Rate** card on **Throughput & Quality**, or the CFR tile on **Board Metrics**. It should read `N failed / M releases` rather than "Change Failure Rate isn't measured yet."

> **CFR is a lagging metric and syncs hourly.** New customer bugs take time to appear, and a bug reported weeks after a release lands in the week it's reported — not the week it shipped.

---

## BambooHR

BambooHR syncs your team's paid time off so analytics exclude vacation days. Insights reads it from BambooHR's **Who's Out iCal feed** — a read-only calendar URL. No API key is involved: the feed URL itself is the credential.

1. Log in to BambooHR (`https://yourcompany.bamboohr.com`) with an account that can see the whole company's Who's Out calendar.
2. On the **Home** page, find the **Who's Out** widget and click **Full Calendar**.
3. In the calendar view, open the **action menu (gears)** in the top-right corner and select **iCal Feeds**.
4. Copy the feed URL — it looks like `https://yourcompany.bamboohr.com/feeds/ical/...`.
5. In **Data Connections**, click **+** next to **BambooHR**, optionally name the connection, and paste the URL into **BambooHR Who's Out iCal URL**. The URL must use `https`.
6. Click **Connect**. GitKraken fetches the URL and verifies it serves a live iCalendar feed before saving. If the check fails, the 2 usual causes are a feed URL that was reset or deleted in BambooHR, or a URL that points at a BambooHR page instead of the iCal feed itself.

> **Generate the feed with an account that sees everyone.** A feed only contains what its generating user can see in their own Who's Out widget — typically that means an HR admin or someone with company-wide calendar visibility. Otherwise part of the team's PTO will be missing.

> **Treat the feed URL as a secret.** BambooHR iCal feeds are not password protected — anyone with the URL can read the calendar, and the embedded token is the only protection.

> **Any user can reset or delete their feed URL** in BambooHR at any time, which invalidates it and stops the sync. If that happens, generate a new feed and update the connection.

BambooHR notes that changes can take up to 24 hours to appear in external calendar feeds, so the newest PTO entries may lag. If you do not see the **iCal Feeds** option at all, your BambooHR administrator controls that access — ask them to enable it or generate the feed for you. Reference: [BambooHR — Create an iCalendar Feed](https://help.bamboohr.com/s/article/587318).

After you connect, time off feeds into the metrics described in [For Admins](/insights-expo/expo-ai-adoption-for-admins).

### Mark time off by hand

Admins can also mark or clear a single day of time off, with or without BambooHR. On the [Developers page](/insights-expo/expo-ai-adoption-developers), expand a developer and click a day on the activity heatmap. The tooltip reads **Mark as time off** or **Clear time off**. A day you mark shows as time off and counts the same way as time off synced from BambooHR. The personal GitKraken Insights for Developers page shows time off but cannot change it.

---

## Troubleshooting

If a connection fails or CFR stays empty, find the symptom below:

| Symptom | What's happening | Fix |
| --- | --- | --- |
| **Jira token rejected** | A *scoped* token was created | Recreate with plain **Create API token** (no scopes) |
| **"The saved credentials for the Jira connection are no longer accepted."** | You edited the connection and kept the saved token, but Jira no longer accepts it, for example because it expired or was revoked | Enter a new API token and account email (for Jira Data Center, a new personal access token), then save again |
| **"One or more projects under Advanced › Only include these projects … aren't visible to this account."** | A listed project key is wrong, or the connecting account cannot see that project | Check the project keys, and that the account can browse those projects |
| **"This account can't browse one or more projects under Advanced › Only include these projects …"** | The connecting account lacks the **Browse Projects** permission on a listed project | Give the account **Browse Projects** on those projects, or remove them from the list |
| **BambooHR feed fails to validate, or some people's PTO is missing** | The feed was reset or deleted, the URL points at a BambooHR page rather than the iCal feed, or the feed was generated by an account with narrow calendar visibility | Copy the feed URL from **iCal Feeds** (it contains `/feeds/ical/`), regenerate it with an account that sees the whole company if needed, and update the connection |
| **CFR cards show zeros or "Change Failure Rate isn't measured yet"** | Defect detection is not set up on any connected issue tracker, or no releases are tracked | Click **Configure defect detection** on the card, or edit your tracker connection (Jira, Jira Data Center, Linear, YouTrack, ClickUp, or Azure DevOps with work-item sync) and select **Enable defect detection**. Then set a release **Signal** per repo in Settings → Releases — see [Configure CFR](#configure-change-failure-rate-cfr) |

For general connection troubleshooting, see [Troubleshooting setup](/insights-expo/expo-ai-adoption-connect-your-data#troubleshooting-setup) on the Connect Your Data overview.

---

## After connecting

After your connection is active, continue with the remaining setup steps on the [Connect Your Data](/insights-expo/expo-ai-adoption-connect-your-data) overview:

- [Set your benchmarks](/insights-expo/expo-ai-adoption-connect-your-data#set-your-benchmarks)
- [Map developer identities](/insights-expo/expo-ai-adoption-connect-your-data#map-developer-identities)
- [Invite your team](/insights-expo/expo-ai-adoption-connect-your-data#invite-your-team)
