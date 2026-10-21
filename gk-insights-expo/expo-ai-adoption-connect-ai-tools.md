---
title: Connect AI Coding Tools
description: Connect Cursor, GitHub Copilot, and Devin to GitKraken Insights through their APIs — setup instructions for each tool, plus where to set up Claude Code, Codex, and Copilot telemetry.
product: GitKraken Insights
content_type: how-to
audience: admin
plan_required: GitKraken Insights
integrations: [Cursor, Devin, GitHub Copilot]
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: blue
    card_description: Cursor, GitHub Copilot, and Devin
    card_icon: robot
    nav_category: connect-your-data
    nav_label: AI Coding Tools
    nav_order: 50
    page_type: content
---
<kbd>Last updated: October 2026</kbd>

Connect **at least one** AI provider — this is what lets Insights *measure* adoption instead of guessing at it. Connect as many as your team uses.

This page covers the tools GitKraken reads through their APIs — Cursor, GitHub Copilot, and Devin. Claude Code, OpenAI Codex, and GitHub Copilot telemetry are set up through **Agent OTel Data** — see [Connect Agent OTel Data](/insights-expo/expo-ai-adoption-connect-otel).

> **Before you start:** make sure you have the right access. See the [prerequisites table](/insights-expo/expo-ai-adoption-connect-your-data#before-you-start-gather-access) on the Connect Your Data overview.

---

## Claude Code and Codex (OpenTelemetry)

Claude Code, OpenAI Codex, and GitHub Copilot telemetry now share one connection, **Agent OTel Data**. Setup for each agent — the org auth token, each agent's config, and what the telemetry contains — is on its own page: [Connect Agent OTel Data](/insights-expo/expo-ai-adoption-connect-otel).

- [Claude Code](/insights-expo/expo-ai-adoption-connect-otel#claude-code) — team config or Claude apps gateway
- [Claude Cowork](/insights-expo/expo-ai-adoption-connect-otel#claude-cowork) — four fields in the Claude admin console, set once by an organization Admin
- [OpenAI Codex](/insights-expo/expo-ai-adoption-connect-otel#openai-codex) — `config.toml` via MDM or on each machine
- [GitHub Copilot (OpenTelemetry)](/insights-expo/expo-ai-adoption-connect-otel#github-copilot-opentelemetry) — managed settings, CLI, or VS Code
- [OpenCode](/insights-expo/expo-ai-adoption-connect-otel#opencode) — available on request; a plugin config each developer adds

<!-- FLAG FOR HUMAN REVIEW: claude-code-codex-setup.png predates the Agent OTel Data setup view (it shows the older "Claude Code & Codex" page with one config field per tool, not the Choose your agent cards). It was also captured against the dev collector, so its Collector endpoint reads otel-devex-dev.gitkraken.com. Retake against production. -->
<figure>
  <img src="/wp-content/uploads/claude-code-codex-setup.png" class="help-center-img img-bordered" alt="The Claude Code and Codex setup page in GitKraken Insights, showing the shared org auth token with its Collector endpoint and masked Authentication token, followed by a masked Claude Code config field and a masked OpenAI Codex config field, each with reveal and copy buttons" />
  <figcaption>The setup page — one org auth token, plus a ready-to-copy config for each agent.</figcaption>
</figure>

---

## Cursor (API key)

GitKraken pulls Cursor usage centrally through the Cursor Teams API, using a **team API key**. There's nothing for individual developers to install or configure, and the first sync includes usage from before you connected.

1. Go to your Cursor dashboard's API section: [cursor.com/dashboard/api → Team API keys](https://cursor.com/dashboard/api?section=team-keys#team-api-keys). The **Get your API key** link in GitKraken's connection dialog opens the same page.
2. Create a new **team** API key and give it a name that describes its purpose, for example "GitKraken Insights."
3. Copy the key immediately and store it somewhere secure, such as a password manager — Cursor shows it only once. Team API keys start with `sk-…`.
4. In **Data Connections**, click **+** next to **Cursor**, optionally give the connection a **Name**, paste the **Cursor API key**, and click **Connect**.

> **It must be a team key, not a personal one.** GitKraken reads team-scoped endpoints, and a personal key can't see them.

> **Keep the API key private.** It grants read access to your Cursor team's usage data — treat it like a password.

**What GitKraken reads.** The integration is read-only — GitKraken never writes anything back to Cursor. It uses the key for:

- **Usage events** — per-request activity, including model, token counts, and cost where Cursor reports it.
- **Daily usage data** — Cursor's per-day rollup of team activity.
- **Team members** — your Cursor team directory, used to match Cursor usage to the right developer.

**Matching usage to developers.** Cursor usage is matched to developers by email. A teammate whose Cursor email differs from their git-provider email may appear as a separate person — review the suggested merges in **Settings → Developers**, or merge the identities yourself. See [Map developer identities](/insights-expo/expo-ai-adoption-connect-your-data#map-developer-identities).

**Rotating or revoking the key.** If the key is exposed or due for rotation, create a new team API key in Cursor and paste it into the existing connection with **Edit** — don't create a second connection. Revoking the key in Cursor stops the sync; existing data stays in your dashboards, but no new usage arrives until a valid key is supplied.

For when Cursor data first appears and how far back it goes, see [When your AI tool data appears](/insights-expo/expo-ai-adoption-connect-your-data#when-your-ai-tool-data-appears).

---

## Devin

> **Note — available on request.** The Devin connection isn't turned on for every organization. To enable it, contact your GitKraken account team. Once it's enabled, **Devin** appears in the **AI Coding Agents** group under **Add data source**.

Devin usage comes through the Devin API, authenticated with a **service-user API key**.

1. Log in at [devin.ai](https://devin.ai) and open your **organization's settings** in the top left.
2. Go to the **Devin API** section and click **Provision Service User**. Provision a user with the **Admin** role and set an expiration — pick the longest expiry your policy allows, and set a reminder to rotate it.
3. Copy the generated token and store it securely. While you're on this page, note the **Organization ID** shown at the top.
4. In **Data Connections**, click **+** next to **Devin**, give the connection a name, enter the **Devin API key** and your **Devin organization ID** — type each ID and press **Enter** to add it — then click **Connect**.

> **Keep the API key private.** It grants API access to your Devin organization — treat it like a password.

> The key belongs to the provisioned service user rather than a personal account, so it won't break when an individual teammate leaves. To rotate it, provision a new service-user key in Devin and update the connection.

**When Devin data appears.** The connection starts syncing as soon as you click **Connect**:

- **First data** usually appears within **4 hours**.
- **The first sync** pulls the last 90 days of Devin session activity and finishes within **24 hours**. The 90 days are counted by last activity, so a session that started earlier but was worked on inside the window is included — Devin history can reach back further than 90 days.
- **No reporting lag.** Devin data stays current to the present, and the connection keeps refreshing in the background.

If nothing appears after 24 hours, re-check the API key, confirm it belongs to a service user with the **Admin** role, and confirm the organization ID. See [When your AI tool data appears](/insights-expo/expo-ai-adoption-connect-your-data#when-your-ai-tool-data-appears).

---

## GitHub Copilot

Connect GitHub Copilot to pull Copilot usage metrics for your organization or enterprise from GitHub's Copilot metrics API. Copilot returns a narrower set of data than Claude Code, Codex, or Cursor, so some metrics will be partial.

> **Note — Copilot telemetry is a separate connection.** To also capture per-session telemetry from developers' Copilot CLI and VS Code, set up GitHub Copilot under **Agent OTel Data**. You can use either or both — see [Copilot telemetry and the GitHub Copilot connection](/insights-expo/expo-ai-adoption-connect-otel#copilot-telemetry-and-the-github-copilot-connection).

You can connect at **organization** or **enterprise** level. The connection modal's **Required scopes** panel has a tab for each, and your choice changes both the token scope and the last field you fill in:

| | Organization | Enterprise |
| --- | --- | --- |
| **Classic token scope** | `read:org` | `read:enterprise` (`manage_billing:copilot` also works) |
| **Fine-grained token permission** | **Organization Copilot metrics: Read-only** | Not documented — use a classic token |
| **Token owner must be** | An organization owner, or a member with permission to view organization Copilot metrics | An enterprise owner, or a member with permission to view enterprise Copilot metrics |
| **Policy enabled on** | The organization | The enterprise |
| **Last field** | **GitHub Organization name** | **GitHub Enterprise slug** |

Two prerequisites matter more than the token itself: the **Copilot usage metrics** policy must be enabled, and the token owner must have permission to view Copilot metrics. Everything else is the same for both levels.

**1. Enable the Copilot usage metrics policy** (organization or enterprise admin):

1. On GitHub, open your organization's or enterprise's **Settings** tab.
2. In the left sidebar, select **Copilot → Policies**.
3. Under **Features**, set **Copilot usage metrics** to **Enabled**.

Without this policy, the metrics API returns no data even with a valid token.

**2. Create a personal access token.** A classic token works at both levels; an organization connection can also use a fine-grained token.

**Classic token.** The fastest route is the **Create a classic token** link in the connection modal — it opens GitHub's token form with the description and your level's scope already filled in. To do it by hand:

1. On GitHub, go to **Settings → Developer settings → Personal access tokens → Tokens (classic)**.
2. Select **Generate new token → Generate new token (classic)**, name it in the **Note** field, and set an **Expiration**.
3. Select the scope for your level — **`read:org`** (under `admin:org`) for an organization, or **`read:enterprise`** for an enterprise. Either grants read access to membership and Copilot metrics, and no other scopes are needed.
4. Click **Generate token** and copy it immediately — GitHub won't show it again.
5. **If your organization uses SAML single sign-on:** click **Configure SSO** next to the token and authorize it for your organization. Without this, the classic token can't read org data.

Classic tokens start with `ghp_…`.

**Fine-grained token (organization connections).** On GitHub, go to **Settings → Developer settings → Personal access tokens → Fine-grained tokens** and generate a new token. Set the **Resource owner** to your organization, set an expiration, and under organization permissions grant **Organization Copilot metrics: Read-only**. The token owner needs the same permission to view organization Copilot metrics as for a classic token. For an **enterprise** connection, use a classic token — the connection modal lists classic scopes only, and no fine-grained permission is documented for enterprise Copilot metrics.

> **Using SAML single sign-on?** Whichever token type you create, authorize it for the GitHub organization you're connecting before you paste it into GitKraken. Until it's authorized for that organization, the token can't read its Copilot metrics.

**3. Connect in GitKraken:**

1. In **Data Connections**, click **+** next to **GitHub Copilot**.
2. In **Required scopes**, select the **Organization** or **Enterprise** tab to match your token.
3. Optionally give the connection a **Name**, then enter the **GitHub personal access token**.
4. Enter the last field for your level:
   - **GitHub Organization name** — the org slug as it appears in URLs (`my-org` for `github.com/my-org`), not the display name.
   - **GitHub Enterprise slug** — your enterprise slug, for example `my-enterprise`.
5. Click **Connect**.

Each connection covers one organization or one enterprise; add another GitHub Copilot connection for each additional target. An enterprise connection covers every organization in the enterprise, so connecting one of its member organizations separately adds nothing — each developer is counted once per day either way.

<figure>
  <img src="/wp-content/uploads/connect-copilot-modal-aug-2026.png" class="help-center-img img-bordered" alt="Connect GitHub Copilot modal in GitKraken Insights showing an optional connection name field, a Required scopes panel with Organization and Enterprise tabs listing the read:org scope and the token-owner and usage-metrics-policy prerequisites, a GitHub personal access token field with a Create a classic token link, and a GitHub Organization name field" />
  <figcaption>The Connect GitHub Copilot modal — the required scope and prerequisites, a shortcut for creating the classic token, and the organization name.</figcaption>
</figure>

> **GitHub suppresses data for small teams.** GitHub only returns Copilot usage for a given day if the team had **five or more members with active Copilot licenses** on that day, evaluated at the end of the day. Teams below that threshold will see gaps regardless of how the connection is configured.

> **Copilot data runs two to three days behind.** GitKraken waits for GitHub's daily usage report to settle before pulling it, so neither today nor yesterday appears — a new connection showing nothing for today is working correctly. The first sync pulls up to the last 90 days. See [When your AI tool data appears](/insights-expo/expo-ai-adoption-connect-your-data#when-your-ai-tool-data-appears).

**Where Copilot AI credits appear.** When Copilot is connected and GitHub reports credit data, the **By Developer** and **By Team** tables add an **AI Credits Used** column: the premium-model credits consumed through Copilot in the selected window, as reported by GitHub, with input and output token counts when GitHub provides them. A team's value is the sum of its members. The same figure appears under **AI Credits Used** on an expanded developer's **Developer Environment** tab. See [By Developer](/insights-expo/expo-ai-adoption-developers).

> **Keep the token private.** A personal access token grants access to everything its owner can see within the granted scopes or permissions — treat it like a password.

References: [GitHub — REST API endpoints for Copilot metrics](https://docs.github.com/en/rest/copilot/copilot-metrics) and [GitHub — Managing your personal access tokens](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/managing-your-personal-access-tokens).

---

## Troubleshooting

| Symptom | What's happening | Fix |
| --- | --- | --- |
| **No Cursor usage data after 24 hours** | The key is a personal key rather than a team key, or it was revoked in Cursor | Create a **team** API key in the Cursor dashboard and paste it into the existing connection |
| **A developer appears twice after connecting Cursor** | Their Cursor email differs from their git-provider email | Review the suggested merges in **Settings → Developers**, or merge the identities yourself — see [Map developer identities](/insights-expo/expo-ai-adoption-connect-your-data#map-developer-identities) |
| **Copilot connects but no metrics appear** | The **Copilot usage metrics** policy is off, the token owner can't view Copilot metrics, the team had fewer than five active Copilot licenses, or the data is still inside GitHub's two-to-three-day reporting lag | Enable the policy in org **Settings → Copilot → Policies**, use an org owner or a role with **View Organization Copilot Metrics**, check the license count and the organization slug, and allow for the lag |
| **Copilot token rejected** | The token lacks the Copilot metrics permission for your level, a fine-grained token was used for an enterprise connection, or SAML SSO isn't authorized | Use a **classic** token with `read:org` (organization) or `read:enterprise` (enterprise), or a fine-grained token with **Organization Copilot metrics: Read-only** for an organization. For a classic token, also **Configure SSO** on it |

For Claude Code, Codex, and Copilot telemetry, see [Troubleshooting](/insights-expo/expo-ai-adoption-connect-otel#troubleshooting) on the Agent OTel Data page. For general connection troubleshooting, see [Troubleshooting setup](/insights-expo/expo-ai-adoption-connect-your-data#troubleshooting-setup) on the Connect Your Data overview.

---

## After connecting

Once your connection is active, continue with the remaining setup steps on the [Connect Your Data](/insights-expo/expo-ai-adoption-connect-your-data) overview:

- [Set your benchmarks](/insights-expo/expo-ai-adoption-connect-your-data#set-your-benchmarks)
- [Map developer identities](/insights-expo/expo-ai-adoption-connect-your-data#map-developer-identities)
- [Invite your team](/insights-expo/expo-ai-adoption-connect-your-data#invite-your-team)
