---
title: Connect Jira & BambooHR
description: Connect Jira Cloud for Change Failure Rate tracking and BambooHR for PTO sync in GitKraken Insights.
product: GitKraken Insights
content_type: how-to
audience: admin
plan_required: GitKraken Insights
integrations: [Jira Cloud, BambooHR]
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
<kbd>Last updated: October 2026</kbd>

Neither of these is required to see data, but both sharpen it. You can add them now or any time later.

> **Before you start:** make sure you have the right access. See the [prerequisites table](/insights-expo/expo-ai-adoption-connect-your-data#before-you-start-gather-access) on the Connect Your Data overview.

---

## Jira

Jira gives Insights cycle-time start signals and customer-bug data, so AI Impact compares like-for-like work across tools.

1. Go to [**id.atlassian.com → Security → API tokens**](https://id.atlassian.com/manage-profile/security/api-tokens).
2. Click **Create API token** — **not** "Create API token with scopes."
3. In **Data Connections**, click **+** next to **Jira** and enter:
   - the **API token**,
   - the **account email** the token was created for,
   - your **Jira instance URL**.
4. Optionally expand **Advanced** and enter project key prefixes in **Only include these projects** (for example, `PROJ, ENG`) or **Exclude these projects**.

> **You can add more than one Jira connection.** Each connection claims the projects listed in its **Only include these projects** field. Leaving that field blank claims every project on the instance, which blocks a second Jira connection from being added. To split one Jira site across connections, list the projects on each one.

> **Scoped tokens aren't supported yet.** Jira's newer "API token with scopes" option won't work — use the plain **Create API token** option. (The Atlassian token page is easy to miss; the link above goes straight to it.)

### Configure Change Failure Rate (CFR)

Change Failure Rate (CFR) is a DORA stability metric — see [Change Failure Rate (CFR)](/insights-expo/expo-ai-adoption-dora-change-failure-rate) for what it measures and how to read it. Setting it up here is what powers the CFR cards on Throughput & Quality, Board Metrics, Executive, and AI Impact.

CFR can read customer bugs from any connected issue tracker with defect detection turned on: Jira, Linear, YouTrack, or Azure Boards (an Azure DevOps connection with work-item sync). One configured tracker is enough. For the other trackers, see [Connect Linear and YouTrack](/insights-expo/expo-ai-adoption-connect-linear-youtrack) and [Sync work items from Azure Boards](/insights-expo/expo-ai-adoption-connect-azure-devops#sync-work-items-from-azure-boards).

CFR needs two pieces of configuration. Until both are set, those cards show zeros even when Jira is connected and healthy.

**1. Tell Insights which Jira issues are customer bugs.**

**Where the setting is:** Insights → **Settings → Data Connections** → the **Jira** connection → **Edit** → **Enable defect detection**, under each Jira instance URL.

1. Select **Enable defect detection**.
2. Under **Match by**, choose how GitKraken identifies Jira issues that count as customer-impacting defects:
   - **Custom field** — enter the **Custom field ID** of the field your team uses to flag customer-reported defects, for example `customfield_10042`.
   - **Jira labels** — one or more labels.
   - **Issue types** — one or more issue-type names.
   - **Field + value** — a standard or custom Jira field and the values that mark a defect.

If you have more than one Jira instance, each one has its own defect detection setting. Changing **Enable defect detection** or **Match by** recalculates that connection's Change Failure Rate history, so past figures can change.

*[Screenshot needed: Jira connection modal → Enable defect detection → Match by.]*

> **Finding the field ID:** In Jira, go to **Settings → Issues → Custom fields**, locate your "Customer Bug" field, and open **⋯ → Edit details** — the ID appears as `customfield_NNNNN` in the page URL. Admins can also list every field at `https://<your-site>.atlassian.net/rest/api/3/field` and match by name.

> **This is the most common reason CFR shows zeros.** If defect detection is off, no Jira issues are attributed as customer bugs, so CFR can't be calculated — even with Jira fully connected.

**2. Make sure releases are being tracked.** CFR is *failing releases ÷ total releases*, so Insights needs to know what counts as a release. Go to **Settings → Releases** and set the **Signal** for each repository. The options depend on the repo's git provider:

- **Auto-detect** (default) — picks the release source the repo's provider offers. The **Detected** column shows what it found on the last sync.
- **GitHub** repos: **GitHub releases**, **Git tags**, **Workflow file** (a GitHub Actions workflow, e.g. `cd.yaml`), or **Release branch merge**.
- **Azure DevOps** repos: **Git tags**, **Azure pipeline** (a pipeline definition), or **Release branch merge**.
- **GitLab** repos: **GitLab releases**, **Git tags**, **GitLab pipeline** (a pipeline name), or **Release branch merge**.
- **Bitbucket** repos: **Git tags**, **Bitbucket Pipelines** (Bitbucket Cloud only), or **Release branch merge**.
- **Skip** — don't track releases for that repo.

> **Release branch merge** needs a **base branch** per repository, so it isn't offered in the bulk action — set it one repo at a time.

Once syncing completes, confirm the **# Releases** column shows a non-zero count.

**Push releases from your own tooling.** If your deployments don't produce a signal Insights can detect — an external CI/CD system, a platform GitKraken doesn't read, or a pipeline that doesn't tag releases — you can send releases to Insights yourself with the [Manual Releases API](/insights-expo/expo-ai-adoption-manual-releases-api). Create an API key in the **Security** tab of your [gitkraken.dev account](https://gitkraken.dev/account), then POST each release. Manual releases are tracked alongside detected ones, and you can backfill historical releases the same way.

**What counts as a failure:** a release fails when a customer bug (matching your defect detection setting), of any priority, is created after the release ships and before the next release. Severity comes directly from the Jira **priority** field, so keep priorities consistent. Priority names other than the standard ones, such as `Critical (P1)`, are placed by their order in your Jira priority scheme, most severe first.

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
6. Click **Connect**. GitKraken fetches the URL and verifies it serves a live iCalendar feed before saving. If it doesn't, the two usual causes are a feed URL that was reset or deleted in BambooHR, or a URL that points at a BambooHR page instead of the iCal feed itself.

> **Generate the feed with an account that sees everyone.** A feed only contains what its generating user can see in their own Who's Out widget — typically that means an HR admin or someone with company-wide calendar visibility. Otherwise part of the team's PTO will be missing.

> **Treat the feed URL as a secret.** BambooHR iCal feeds are not password protected — anyone with the URL can read the calendar, and the embedded token is the only protection.

> **Any user can reset or delete their feed URL** in BambooHR at any time, which invalidates it and stops the sync. If that happens, generate a new feed and update the connection.

BambooHR notes that changes can take up to 24 hours to appear in external calendar feeds, so very recent PTO entries may lag. If you don't see the **iCal Feeds** option at all, your BambooHR administrator controls that access — ask them to enable it or generate the feed for you. Reference: [BambooHR — Create an iCalendar Feed](https://help.bamboohr.com/s/article/587318).

Once connected, time off feeds into the metrics described in [For Admins](/insights-expo/expo-ai-adoption-for-admins).

### Mark time off by hand

Admins can also mark or clear a single day of time off, with or without BambooHR. On the [Developers page](/insights-expo/expo-ai-adoption-developers), expand a developer and click a day on the activity heatmap. The tooltip reads **Mark as time off** or **Clear time off**. A day you mark shows as time off and counts the same way as time off synced from BambooHR. The personal GitKraken Insights for Developers page shows time off but can't change it.

---

## Troubleshooting

| Symptom | What's happening | Fix |
| --- | --- | --- |
| **Jira token rejected** | A *scoped* token was created | Recreate with plain **Create API token** (no scopes) |
| **BambooHR feed fails to validate, or some people's PTO is missing** | The feed was reset/deleted, the URL points at a BambooHR page rather than the iCal feed, or the feed was generated by an account with narrow calendar visibility | Copy the feed URL from **iCal Feeds** (it contains `/feeds/ical/`), regenerate it with an account that sees the whole company if needed, and update the connection |
| **CFR cards show zeros or "Change Failure Rate isn't measured yet"** | Defect detection isn't set up on any connected issue tracker, or no releases are tracked | Click **Configure defect detection** on the card, or edit your tracker connection (Jira, Linear, YouTrack, or Azure DevOps with work-item sync) and select **Enable defect detection**. Then set a release **Signal** per repo in Settings → Releases — see [Configure CFR](#configure-change-failure-rate-cfr) |

For general connection troubleshooting, see [Troubleshooting setup](/insights-expo/expo-ai-adoption-connect-your-data#troubleshooting-setup) on the Connect Your Data overview.

---

## After connecting

Once your connection is active, continue with the remaining setup steps on the [Connect Your Data](/insights-expo/expo-ai-adoption-connect-your-data) overview:

- [Set your benchmarks](/insights-expo/expo-ai-adoption-connect-your-data#set-your-benchmarks)
- [Map developer identities](/insights-expo/expo-ai-adoption-connect-your-data#map-developer-identities)
- [Invite your team](/insights-expo/expo-ai-adoption-connect-your-data#invite-your-team)
