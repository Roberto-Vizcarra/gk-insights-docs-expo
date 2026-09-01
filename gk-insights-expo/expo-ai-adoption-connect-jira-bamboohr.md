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
<kbd>Last updated: September 2026</kbd>

Neither of these is required to see data, but both sharpen it. You can add them now or any time later.

> **Before you start:** make sure you have the right access. See the [prerequisites table](/insights-expo/expo-ai-adoption-connect-your-data#before-you-start--gather-access) on the Connect Your Data overview.

---

## Jira

Jira gives Insights cycle-time start signals and customer-bug data, so AI Impact compares like-for-like work across tools.

1. Go to [**id.atlassian.com → Security → API tokens**](https://id.atlassian.com/manage-profile/security/api-tokens).
2. Click **Create API token** — **not** "Create API token with scopes."
3. In **Data Connections**, click **+** next to **Jira** and enter:
   - the **API token**,
   - the **account email** the token was created for,
   - your **Jira instance URL**.

> **Scoped tokens aren't supported yet.** Jira's newer "API token with scopes" option won't work — use the plain **Create API token** option. (The Atlassian token page is easy to miss; the link above goes straight to it.)

### Configure Change Failure Rate (CFR)

Change Failure Rate (CFR) is a DORA stability metric — see [Change Failure Rate (CFR)](/insights-expo/expo-ai-adoption-dora-metrics#change-failure-rate-cfr) for what it measures and how to read it. Setting it up here is what powers the CFR cards on /ai-adoption/ai-impact, board-metrics, and executive.

CFR needs two pieces of configuration. Until both are set, those cards show zeros even when Jira is connected and healthy.

**1. Point Insights at your Jira "customer bug" field.**

**Where the setting is:** Insights → **Settings → Data Connections** → the **Jira** connection → **Edit** → expand the **Advanced** section → **Customer bug field ID**.

Set it to the custom Jira field your team uses to flag customer-reported defects — for example `customfield_10042`. If you have more than one Jira instance, set it on each one.

> **You can keep more than one CFR configuration.** Set up a separate Change Failure Rate configuration for each Jira instance you run, or for each definition of a failed change your org uses.

*[Screenshot needed: Jira connection modal → Advanced → "Customer bug field ID" field.]*

> **Finding the field ID:** In Jira, go to **Settings → Issues → Custom fields**, locate your "Customer Bug" field, and open **⋯ → Edit details** — the ID appears as `customfield_NNNNN` in the page URL. Admins can also list every field at `https://<your-site>.atlassian.net/rest/api/3/field` and match by name.

> **This is the most common reason CFR shows zeros.** If the field ID is blank, no Jira issues are attributed as customer bugs, so CFR can't be calculated — even with Jira fully connected.

**2. Make sure releases are being tracked.** CFR is *failing releases ÷ total releases*, so Insights needs to know what counts as a release. Go to **Settings → Releases** and set the **Signal** for each repository:

- **Auto-detect** (default) — tries GitHub Releases first, then falls back to your CD workflow.
- **GitHub Releases** — use the GitHub Releases API explicitly.
- **Workflow file** — watch a specific GitHub Actions workflow (e.g. `cd.yaml`).
- **Skip** — don't track releases for that repo.

Once syncing completes, confirm the **# Releases** column shows a non-zero count.

**Push releases from your own tooling.** If your deployments don't produce a signal Insights can detect — an external CI/CD system, a platform GitKraken doesn't read, or a pipeline that doesn't tag releases — you can send releases to Insights yourself with the [Manual Releases API](/insights-expo/expo-ai-adoption-manual-releases-api). Create an API key in the **Security** tab of your [gitkraken.dev account](https://gitkraken.dev/account), then POST each release. Manual releases are tracked alongside detected ones, and you can backfill historical releases the same way.

**What counts as a failure:** a customer bug (matching the field above) at **High** or **Highest / Critical** priority that's attributed to a release. Severity comes directly from the Jira **priority** field, so keep priorities consistent.

**Verify it's working:** on the Jira connection, open **Status** and confirm the **Change Failure Rate** pipeline is healthy. Then check the CFR card on **/ai-adoption/ai-impact** — it should read `N bugs / M releases` rather than "Not configured."

> **CFR is a lagging metric and syncs hourly.** New customer bugs take time to appear, and a bug reported weeks after a release lands in the week it's reported — not the week it shipped.

---

## BambooHR

BambooHR syncs your team's paid time off so analytics exclude vacation days. Insights reads it from BambooHR's **Who's Out iCal feed** — a read-only calendar URL. No API key is involved: the feed URL itself is the credential.

1. Log in to BambooHR (`https://yourcompany.bamboohr.com`) with an account that can see the whole company's Who's Out calendar.
2. On the **Home** page, find the **Who's Out** widget and click **Full Calendar**.
3. In the calendar view, open the **action menu (gears)** in the top-right corner and select **iCal Feeds**.
4. Copy the feed URL — it looks like `https://yourcompany.bamboohr.com/feeds/ical/...`.
5. In **Data Connections**, click **+** next to **BambooHR**, optionally name the connection, and paste the URL into **BambooHR Who's Out iCal URL**. The URL must use `https`.
6. Click **Connect**. GitKraken fetches the URL and verifies it serves a live iCalendar feed before saving.

> **Generate the feed with an account that sees everyone.** A feed only contains what its generating user can see in their own Who's Out widget — typically that means an HR admin or someone with company-wide calendar visibility. Otherwise part of the team's PTO will be missing.

> **Treat the feed URL as a secret.** BambooHR iCal feeds are not password protected — anyone with the URL can read the calendar, and the embedded token is the only protection.

> **Any user can reset or delete their feed URL** in BambooHR at any time, which invalidates it and stops the sync. If that happens, generate a new feed and update the connection.

BambooHR notes that changes can take up to 24 hours to appear in external calendar feeds, so very recent PTO entries may lag. If you don't see the **iCal Feeds** option at all, your BambooHR administrator controls that access — ask them to enable it or generate the feed for you. Reference: [BambooHR — Create an iCalendar Feed](https://help.bamboohr.com/s/article/587318).

Once connected, time off feeds into the metrics described in [For Admins](/insights-expo/expo-ai-adoption-for-admins).

---

## Troubleshooting

| Symptom | What's happening | Fix |
| --- | --- | --- |
| **Jira token rejected** | A *scoped* token was created | Recreate with plain **Create API token** (no scopes) |
| **BambooHR feed fails to validate, or some people's PTO is missing** | The feed was reset/deleted, or it was generated by an account with narrow calendar visibility | Regenerate the feed with an account that sees the whole company, and update the connection |
| **CFR cards show zeros or "Not configured"** | The Customer bug field ID isn't set, or no releases are tracked | Set **Customer bug field ID** on the Jira connection (Advanced), and set a release **Signal** per repo in Settings → Releases — see [Configure CFR](#configure-change-failure-rate-cfr) |

For general connection troubleshooting, see [Troubleshooting setup](/insights-expo/expo-ai-adoption-connect-your-data#troubleshooting-setup) on the Connect Your Data overview.

---

## After connecting

Once your connection is active, continue with the remaining setup steps on the [Connect Your Data](/insights-expo/expo-ai-adoption-connect-your-data) overview:

- [Set your benchmarks](/insights-expo/expo-ai-adoption-connect-your-data#set-your-benchmarks)
- [Map developer identities](/insights-expo/expo-ai-adoption-connect-your-data#map-developer-identities)
- [Invite your team](/insights-expo/expo-ai-adoption-connect-your-data#invite-your-team)
