---
title: Connect Your Data — Setting Up AI Adoption
description: A step-by-step setup guide for AI Adoption in GitKraken Insights — gather the right access, connect GitHub or Bitbucket, your AI coding tools (Claude Code, Codex, Cursor), and Jira, map developer identities, and invite your team.
product: GitKraken Insights
content_type: how-to
audience: admin
plan_required: GitKraken Insights
integrations: [GitHub, Bitbucket, Claude Code, Codex, Cursor, Jira Cloud]
status: GA
taxonomy:
    category: gk-insights
---
<kbd>Last updated: July 2026</kbd>

This is the hands-on setup guide for AI Adoption in GitKraken Insights. By the end, your organization's GitHub or Bitbucket activity and AI coding-tool telemetry will be flowing into the dashboard, your developers will be mapped to a single identity, and your team will have access.

Plan on **15–20 minutes of active work**, plus up to a day for the first full data sync to complete in the background.

> **Plan:** GitKraken Insights
> **Platform:** Browser only via [gitkraken.dev](https://gitkraken.dev)
> **Role:** Owner or Admin (to manage data connections)
> **Where you'll work:** **Insights → Settings → Data Connections**

> **Note — this is the Next-Gen (AI Adoption) connection flow.** AI Adoption connects through **org-level access tokens** in Settings → Data Connections. This is different from the classic Insights repository connection (the OAuth "Authorize GitHub" flow described in [Getting Started with GitKraken Insights](/gk-insights/gk-insights#connecting-your-data)). If you're setting up AI Adoption, follow the steps on this page.

---

## Before you start — gather access

The single biggest cause of stalled setups is discovering mid-stream that the person doing the setup doesn't have the right permissions. Setup almost always touches **two or three different people** across your org. Line them up *before* you begin.

| You'll need… | …who has this access | What they'll do |
| --- | --- | --- |
| **GitKraken organization** | Owner or Admin of your gk.dev org | Manage data connections, invite teammates |
| **GitHub** *(if you use GitHub)* | An org admin (to create an org-level token) | Generate the GitHub access token |
| **Bitbucket** *(if you use Bitbucket)* | An admin of your Bitbucket workspace | Generate a Bitbucket-specific Atlassian API token |
| **Claude Code / Codex** | The **Owner** of your Anthropic (Claude Code) organization — *admins cannot do this* | Paste the telemetry snippet into org-managed settings |
| **Cursor** | A Cursor **team admin** | Create a team-level admin API key |
| **Jira** (optional) | A Jira admin | Create an API token |

> **Start your git-provider token request now.** In larger orgs, getting approval to create a GitHub or Bitbucket token with the right scope can take days — sometimes weeks. It's the most common bottleneck, so kick it off before anything else.

> **Only Owners and Admins can connect data.** If you open Settings → Data Connections and see a read-only banner, you'll need an org Owner or Admin to either make the connections or grant you access.

---

## Step 1 — Connect your git provider

Your git provider is the foundation. It powers every PR, commit, contributor, and cycle-time metric — without it, the dashboards stay empty. Connect **GitHub or Bitbucket**, whichever hosts your repositories.

### GitHub

1. In gitkraken.dev, open **Insights → Settings → Data Connections**.
2. On the **GitHub** card, click **Connect**.
3. In GitHub, create a token (see scopes below), paste it into the field, click **Validate**, then **Connect**.

<!-- FLAG FOR HUMAN REVIEW: screenshot of the Data Connections page with the GitHub card needed. -->

#### Fine-grained vs. classic token

Either works. The tradeoffs:

| | Fine-grained PAT | Classic PAT |
| --- | --- | --- |
| **Org selection** | We can't auto-fetch your orgs — you'll type the org name in manually | We can list your orgs in a dropdown |
| **Best for** | Tightly-scoped, single-org setups | Multi-org setups, or excluding specific orgs |

We generally recommend a **fine-grained token scoped to your organization**.

#### Required permissions (read-only)

Create the token at **GitHub → Settings → Developer settings → Personal access tokens**. Set the **Resource owner** to your organization (not your personal account) so the token can see org repositories.

**Fine-grained token — Repository permissions (Read-only):**

- **Metadata** — *Read* (selected automatically; required)
- **Contents** — *Read*
- **Pull requests** — *Read*

**Recommended — Organization permissions (Read-only):**

- **Members** — *Read* — lets us match GitHub identities to people on your roster, which makes [identity mapping](#step-5--map-developer-identities) far smoother.

> If you use a **classic** token instead, select the **`repo`** (read) and **`read:org`** scopes.

> **You can edit a token's scopes after creating it** — you don't need to regenerate it if you missed one. (Note: GitHub does *not* let you change a token's expiration after creation, so set a comfortably long expiry up front.)

Once connected, GitHub data begins syncing in the background and continues over the next several hours.

### Bitbucket

Connect Bitbucket instead if your repositories live in a Bitbucket workspace. Like GitHub, it powers every PR, commit, contributor, and cycle-time metric.

1. In gitkraken.dev, open **Insights → Settings → Data Connections**.
2. On the **Bitbucket** card, click **Connect**.
3. In the **Connect Bitbucket** modal, optionally give the connection a **Name**, then enter your **Atlassian account email** and a **Bitbucket API token** (see scopes below), click **Validate**, then **Connect**.

<figure>
  <img src="/wp-content/uploads/connect-bitbucket-modal.png" class="help-center-img img-bordered" alt="Connect Bitbucket modal in GitKraken Insights showing an optional connection name field, the list of scoped Atlassian API token scopes required, and the Atlassian account email and Bitbucket API token fields" />
  <figcaption style="text-align: center; color: #888">The Connect Bitbucket modal — an optional connection name, the required token scopes, and the Atlassian account email and Bitbucket API token fields.</figcaption>
</figure>

#### Required token scopes

Bitbucket connects with a **scoped Atlassian API token**. Create it at [**id.atlassian.com → Security → API tokens**](https://id.atlassian.com/manage-profile/security/api-tokens) using **Create API token with scopes**, and include at least these scopes:

- `read:account`
- `read:pipeline:bitbucket`
- `read:pullrequest:bitbucket`
- `read:repository:bitbucket`
- `admin:repository:bitbucket`
- `read:workspace:bitbucket`

Once connected, Bitbucket data begins syncing in the background and continues over the next several hours.

---

## Step 2 — Connect your AI coding tools

Connect **at least one** AI provider — this is what lets Insights *measure* adoption instead of guessing at it. Connect as many as your team uses.

### Claude Code and Codex (OpenTelemetry)

Claude Code and Codex report usage through OpenTelemetry (OTel). You'll paste a small configuration snippet into your AI tool's **organization-managed settings**.

1. In **Data Connections**, click **Connect** on the **Claude Code / Codex** card, then **View setup**.
2. Copy the configuration snippet (use the copy icon). GitKraken has already generated your organization's telemetry token and endpoint inside it.
3. Have your Anthropic **organization Owner** paste the snippet into your org-managed Claude Code settings.

> **This requires the Claude Code organization Owner.** Admins do not have permission to change org-managed settings.

> **Heads-up for your developers — warn them first.** The moment org telemetry settings change, Anthropic notifies *every developer* on their next Claude Code session that organization settings were modified, and the notice uses deliberately cautious wording (it warns that changed settings *could* run code). This is Anthropic's standard behavior, not GitKraken's. Send your team a quick message ahead of time so no one is surprised. Suggested note:
>
> > *"Heads up — we're turning on Claude Code usage telemetry for the team so we can measure AI adoption. On your next Claude Code session you'll see a notice that org settings changed; that's expected — go ahead and accept it. We are not collecting your prompts."*

> **Privacy:** the default snippet collects usage and session telemetry (tokens used, duration, tool calls) — **not prompt content**. Anthropic publishes documentation on this telemetry, and there is a separate opt-in flag some orgs add to capture prompts; the snippet we generate does **not** enable it.

> **Data appears on the next fresh session.** There's no backfill — usage starts flowing the next time each developer runs Claude Code or Codex after the settings are applied. (Claude Code OTel instrumentation began March 5, 2026; activity before that date isn't available.)

### Cursor (API key)

1. In Cursor, go to **Settings → API Keys → Create New Key**.
2. Create a **team-level** key with **admin scope** (this grants access to *team usage events* and the *member directory*). Name it something recognizable, e.g. "GitKraken Insights."
3. Copy the key immediately — Cursor only shows it once.
4. Back in **Data Connections**, click **Connect** on the **Cursor** card, paste the key, and connect.

> A **personal** key, or a key from a non-admin account, won't have access to team usage data. It must be a team-level admin key.

### GitHub Copilot

Copilot support is on the way. Copilot returns a narrower set of data than Claude Code, Codex, or Cursor, so some metrics will be partial. Your account team will let you know when it's available to connect.

---

## Step 3 — Connect Jira (optional, recommended)

Jira gives Insights cycle-time start signals and customer-bug data, so AI Impact compares like-for-like work across tools. You can add it now or any time later.

1. Go to [**id.atlassian.com → Security → API tokens**](https://id.atlassian.com/manage-profile/security/api-tokens).
2. Click **Create API token** — **not** "Create API token with scopes."
3. In **Data Connections**, click **Connect** on the **Jira** card and enter:
   - the **API token**,
   - the **account email** the token was created for,
   - your **Jira instance URL**.

> **Scoped tokens aren't supported yet.** Jira's newer "API token with scopes" option won't work — use the plain **Create API token** option. (The Atlassian token page is easy to miss; the link above goes straight to it.)

### Configure Change Failure Rate (CFR)

**What is CFR?** Change Failure Rate is the DORA stability metric — the percentage of your releases that produce a customer-reported bug. Setting it up is what powers the CFR cards on /ai-adoption/ai-impact, board-metrics, and executive. (See the [Change Failure Rate (CFR)](/gk-insights/ai-adoption-dora-metrics#change-failure-rate-cfr) metric page for how to read it.)

CFR needs two pieces of configuration. Until both are set, those cards show zeros even when Jira is connected and healthy.

**1. Point Insights at your Jira "customer bug" field.**

**Where the setting is:** Insights → **Settings → Data Connections** → the **Jira** card → open its **connection modal** → expand the **Advanced** section → **Customer bug field ID**.

Set it to the custom Jira field your team uses to flag customer-reported defects — for example `customfield_10042`. If you have more than one Jira instance, set it on each one.

*[Screenshot needed: Jira connection modal → Advanced → "Customer bug field ID" field.]*

> **Finding the field ID:** In Jira, go to **Settings → Issues → Custom fields**, locate your "Customer Bug" field, and open **⋯ → Edit details** — the ID appears as `customfield_NNNNN` in the page URL. Admins can also list every field at `https://<your-site>.atlassian.net/rest/api/3/field` and match by name.

> **This is the most common reason CFR shows zeros.** If the field ID is blank, no Jira issues are attributed as customer bugs, so CFR can't be calculated — even with Jira fully connected.

**2. Make sure releases are being tracked.** CFR is *failing releases ÷ total releases*, so Insights needs to know what counts as a release. Go to **Settings → Releases** and set the **Signal** for each repository:

- **Auto-detect** (default) — tries GitHub Releases first, then falls back to your CD workflow.
- **GitHub Releases** — use the GitHub Releases API explicitly.
- **Workflow file** — watch a specific GitHub Actions workflow (e.g. `cd.yaml`).
- **Skip** — don't track releases for that repo.

Once syncing completes, confirm the **# Releases** column shows a non-zero count.

**What counts as a failure:** a customer bug (matching the field above) at **High** or **Highest / Critical** priority that's attributed to a release. Severity comes directly from the Jira **priority** field, so keep priorities consistent.

**Verify it's working:** on the Jira connection, open **Status** and confirm the **Change Failure Rate** pipeline is healthy. Then check the CFR card on **/ai-adoption/ai-impact** — it should read `N bugs / M releases` rather than "Not configured."

> **CFR is a lagging metric and syncs hourly.** New customer bugs take time to appear, and a bug reported weeks after a release lands in the week it's reported — not the week it shipped.

---

## Step 4 — Set your benchmarks

A few business inputs let Insights translate engineering activity into ROI and tier your developers correctly. Sensible defaults are pre-filled — confirm or adjust in **Settings → General**:

- **Developer Hourly Rate** — used for ROI / time-saved-to-dollars on AI Impact.
- **Baseline Period** — the "before" date AI-adoption lift is measured against.
- **Maturity Factor** (Company AI Readiness %) — an org-wide scaling knob; the 0.75 default suits most orgs.
- **Default Department** — pre-selects the right view for first-time visitors.

You can change all of these at any time. For what each setting affects, see the [AI Adoption Settings reference](/gk-insights/ai-adoption-settings).

---

## Step 5 — Map developer identities

This is the step that makes or breaks clean data. The same person often shows up under several identities — a GitHub or Bitbucket login, one or more commit emails, a Jira account. Until those are merged, your leaderboards and adoption metrics double-count, and you end up with "parallel universes" of the same developer.

1. After your git provider has been processing for a bit (allow ~12 hours), open **Settings → Developers**.
2. Review the detected identities. Where you recognize duplicates of the same person, use **Merge** to combine them.
3. Where an identity is missing an email, add it — this helps tie commit data back to the right person.

Insights auto-suggests matches using email, git handle, and name, but you should confirm them and clean up anything it couldn't resolve. **Treat this like an inbox and keep it empty** — see the [For admins](/gk-insights/ai-adoption-getting-started#for-admins) page for ongoing roster hygiene.

### Excluding review bots

AI code-review bots — such as **GitHub Copilot review** or Atlassian **Rovo** — leave activity on pull requests, so Insights detects them as contributors. If you'd rather they not appear as developers in your metrics, you can exclude them from the roster in **Settings → Developers**.

<!-- FLAG FOR HUMAN REVIEW: screenshot of Settings → Developers identity merge / exclude flow needed. -->

---

## Step 6 — Invite your team

Give the rest of your stakeholders access so they can read the dashboards.

1. From the gk.dev sidebar, go back to the **main menu** and open **Users**.
2. Click **Add users** and invite people by email, or share the invite link.
3. Give each person at least an **Admin** or **Lead** role so they can view Insights.

> **If you invite by link:** new users land with a default role, so you'll need to adjust their role to **Admin** or **Lead** after they create their account.

---

## What to expect after setup

- **First data:** the last month of GitHub activity typically appears within a few hours; a full year usually lands within one to two days.
- **AI tool data:** starts flowing on each developer's next Claude Code / Codex / Cursor session — there's no backfill before the connection was made.
- **Sync status:** each connection on the Data Connections page shows a health status. If a connection looks degraded or errored, that's the first place to check — and let your account team know.
- **Coming soon:** **GitHub Copilot** telemetry and **Bitbucket** support (with aggregated multi-provider dashboards, so GitHub and Bitbucket activity show side by side). Your account team will confirm availability.

---

## Troubleshooting setup

| Symptom | What's happening | Fix |
| --- | --- | --- |
| **Read-only banner** on Data Connections or Settings | Your gk.dev role can't manage connections | Ask an org Owner or Admin to connect, or to grant you access |
| **GitHub token validates, then "Connect" won't enable** | Occasional UI hiccup where re-validating clears the token | Refresh the browser, paste the token once, then Validate → Connect |
| **No orgs to select after connecting GitHub** | Expected with a **fine-grained** token — we can't auto-fetch orgs | Type your GitHub org name in manually |
| **Can't create the Cursor key / no usage data** | Key isn't team-level admin, or your Cursor role is too low | Have a Cursor team admin create a **team-level admin** key |
| **Can't change Claude Code org settings** | You're an admin, not the org Owner | Only the Anthropic org **Owner** can apply the telemetry snippet |
| **Jira token rejected** | A *scoped* token was created | Recreate with plain **Create API token** (no scopes) |
| **CFR cards show zeros or "Not configured"** | The Customer bug field ID isn't set, or no releases are tracked | Set **Customer bug field ID** on the Jira connection (Advanced), and set a release **Signal** per repo in Settings → Releases — see [Configure CFR](#configure-change-failure-rate-cfr) |
| **A developer appears twice** | Multiple identities not yet merged | Merge them in Settings → Developers (Step 5) |
| **Setup email flagged by your IT as suspicious** | Some corporate filters flag new domains | Navigate directly to [gitkraken.dev](https://gitkraken.dev) instead of clicking the email link; tell your account team |

If a problem persists, contact your GitKraken account team with the page URL, what you were connecting, and a screenshot of any error.

---

## Related pages

- [Getting Started with AI Adoption](/gk-insights/ai-adoption-getting-started) — orient yourself in the dashboards once data is flowing.
- [For admins](/gk-insights/ai-adoption-getting-started#for-admins) — ongoing roster hygiene, data freshness, and troubleshooting.
- [AI Adoption Settings reference](/gk-insights/ai-adoption-settings) — what each setting changes.
- [Getting Started with GitKraken Insights](/gk-insights/gk-insights) — request access and the classic repository connection flow.
