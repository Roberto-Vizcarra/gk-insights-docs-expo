---
title: Connect GitHub
description: Connect GitHub Cloud, GitHub Enterprise Server, or GitHub Enterprise Cloud to GitKraken Insights — the GitHub App or a personal access token, required permissions, and setup steps.
product: GitKraken Insights
content_type: how-to
audience: admin
plan_required: GitKraken Insights
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: blue
    card_description: Cloud, Enterprise Server, and Enterprise Cloud
    card_icon: brand-github
    nav_category: connect-your-data
    nav_label: GitHub
    nav_order: 10
    page_type: content
---
<kbd>Last updated: October 2026</kbd>

Connect GitHub Cloud or GitHub Enterprise Server to sync repository, pull request, and contributor activity into GitKraken Insights. Your git provider powers every PR, commit, contributor, and cycle-time metric.

> **Before you start:** make sure you have the right access. See the [prerequisites table](/insights-expo/expo-ai-adoption-connect-your-data#before-you-start-gather-access) on the Connect Your Data overview.

---

## GitHub

1. In gitkraken.dev, open **Insights → Settings → Data Connections**.
2. Under **Add data source**, click **+** next to **GitHub**.
3. In the **Connect GitHub** dialog, choose how to connect:
   - **GitHub App** (Recommended) — install the GitKraken Insights GitHub App on your organization. There's no token to create or rotate, and you manage repository access on GitHub. See [Connect with the GitHub App](#connect-with-the-github-app).
   - **Personal access token** — paste a GitHub token for the organizations you want to sync. See [Connect with a personal access token](#connect-with-a-personal-access-token).

You can connect more than one GitHub organization, and a connected organization appears as **GitHub App** or **GitHub PAT** depending on how it was connected.

---

## Connect with the GitHub App

The App is installed on the GitHub organization itself, and it only ever requests **read** access — to actions, administration, code, metadata, and pull requests. Installing a GitHub App on an organization requires an **organization owner**. If you're a member but not an owner, GitHub turns your install into a request that an owner approves.

### If you're an owner of the GitHub organization

1. In the **Connect GitHub** dialog, choose **GitHub App**.
2. GitHub asks you to authorize GitKraken Insights on your own GitHub account. This only verifies who you are and which organizations you can access — it doesn't grant repository access. Click **Authorize**.
3. If the App isn't installed on any of your organizations yet, GitKraken shows **Install the GitKraken Insights app** — click **Install on an organization**. (If it's already installed somewhere, pick the organization under **Choose an organization**, or click **Install on another organization**.)
4. On GitHub, select the organization whose activity you want in Insights.
5. Choose **All repositories** (recommended — it covers future repositories automatically) or **Only select repositories**, review the read-only permissions, and click **Install & Authorize**.
6. Back in GitKraken, select the organization under **Choose an organization** if you're asked to. GitKraken confirms **GitHub App connected** and starts ingesting data.

### If you're not an owner of the GitHub organization

1. Follow steps 1–5 above. Instead of **Install & Authorize**, GitHub shows **Authorize & Request** — click it. GitHub records a request rather than installing the App.
2. GitKraken tells you GitHub is waiting for an organization owner to approve the installation. This is expected — click **Close** and let an owner know.
3. **Owner:** GitHub emails the organization's owners about the request. Open the review link in that email, adjust the repository selection if needed, and click **Approve, Install, & Authorize**. GitHub returns you to GitKraken, which confirms **GitHub App installed**.
4. **Requester:** back in **Data Connections**, click **+** next to **GitHub** again, choose **GitHub App**, and select your organization under **Choose an organization**.

> **If an owner rejects the request,** the App can't be connected for that organization. Ask an owner to install it, or use the [personal access token](#connect-with-a-personal-access-token) option instead.

Reference: [GitHub — Requesting a GitHub App from your organization owner](https://docs.github.com/en/apps/using-github-apps/requesting-a-github-app-from-your-organization-owner).

### Manage a GitHub App connection

The organization appears in your connections as **GitHub App**, with the status **Not yet synced** until the first sync completes. Your dashboards fill in automatically once it's ready.

- **Exclude repositories:** click **Edit** on the connection. **Skip repositories** excludes repositories from sync; **Skip PR classification** excludes them from PR classification only, while still syncing them. The organization is fixed by the installation and can't be changed here.
- **Change which repositories the App can access:** do this on GitHub, not in GitKraken — **Organization settings → Third-party Access → GitHub Apps → GitKraken Insights → Configure**.
- **Nothing to rotate:** the App holds its own credentials, so nothing expires the way a personal access token does.
- **Uninstalling the App on GitHub stops syncing.** To resume, install it again and reconnect the organization.

Reference: [GitHub — Reviewing and modifying installed GitHub Apps](https://docs.github.com/en/apps/using-github-apps/reviewing-and-modifying-installed-github-apps).

---

## Connect with a personal access token

1. In the **Connect GitHub** dialog, choose **Personal access token**.
2. Create a token (see the permissions below). The **Create a token** link above the token field opens GitHub's token form with the required permissions already filled in, for whichever token type is selected under **Required scopes**.
3. Paste the token into **GitHub personal access token** and click **Validate**.
4. Under **Organizations to sync**, choose the organizations to include. With a fine-grained token, type each organization name exactly as it appears on GitHub and press Enter.
5. Optionally, expand **Advanced** and add **Skip repositories** — they're excluded from sync and PR classification. Then click **Connect**.

### Fine-grained vs. classic token

Either works. The tradeoffs:

| | Fine-grained PAT | Classic PAT |
| --- | --- | --- |
| **Org selection** | We can't auto-fetch your orgs — you'll type the org name in manually | We can list your orgs in a dropdown |
| **Best for** | Tightly-scoped, single-org setups | Multi-org setups, or excluding specific orgs |

We generally recommend a **fine-grained token scoped to your organization**.

### Required permissions (read-only)

Create the token at **GitHub → Settings → Developer settings → Personal access tokens**. Set the **Resource owner** to your organization (not your personal account) so the token can see org repositories.

**Fine-grained token — Repository permissions (Read-only):**

- **Metadata** — *Read* (selected automatically; required)
- **Contents** — *Read*
- **Pull requests** — *Read*
- **Actions** — *Read* (optional) — for deployments detected from workflow runs
- **Administration** — *Read* (optional) — for the branch-protection signal. Without it, Insights still works, but branch-protection signals may be incomplete.

If your organization requires approval for fine-grained tokens, an owner must approve the token before it can read private repositories.

<!-- FLAG FOR HUMAN REVIEW: the GitHub PAT form's scopes panel does not list the Members organization permission below; neither does the credential-security source doc. Kept from the existing page — confirm it is still recommended. -->
**Recommended — Organization permissions (Read-only):**

- **Members** — *Read* — lets us match GitHub identities to people on your roster, which makes [identity mapping](/insights-expo/expo-ai-adoption-connect-your-data#map-developer-identities) far smoother.

> If you use a **classic** token instead, select the **`repo`** and **`read:org`** scopes.

> **You can edit a token's scopes after creating it** — you don't need to regenerate it if you missed one. (Note: GitHub does *not* let you change a token's expiration after creation, so set a comfortably long expiry up front.)

Once connected, GitHub data begins syncing in the background and continues over the next several hours.

---

## GitHub Enterprise Server

Connect GitHub Enterprise Server to sync activity from a self-hosted GitHub instance.

**Requirements:**

- The server must be reachable from GitKraken over `https` with a **publicly-trusted certificate**.
- A personal access token with the **`repo`** and **`read:org`** scopes.

1. In **Data Connections**, click **+** next to **GitHub Enterprise Server**.
2. Optionally name the connection, then enter the **Server URL** — for example `https://github.yourcompany.com`.
3. Paste the **GitHub Enterprise token** and click **Validate**, then **Connect**.

---

## Troubleshooting

| Symptom | What's happening | Fix |
| --- | --- | --- |
| **GitHub token validates, then "Connect" won't enable** | Occasional UI hiccup where re-validating clears the token | Refresh the browser, paste the token once, then Validate → Connect |
| **No orgs to select after connecting GitHub** | Expected with a **fine-grained** token — we can't auto-fetch orgs | Type your GitHub org name in manually |
| **GitHub App: "GitHub is waiting for an organization owner to approve this installation"** | You aren't an owner of the GitHub organization, so your install became a request | Ask an owner to approve it from GitHub's email, then connect again — see [Connect with the GitHub App](#connect-with-the-github-app) |
| **GitHub App connection stopped syncing** | The App was uninstalled from the organization on GitHub | Install the App again and reconnect the organization |
| **GitHub App doesn't see a repository** | The installation is limited to selected repositories | On GitHub, open **Organization settings → Third-party Access → GitHub Apps → GitKraken Insights → Configure** and add the repository |
| **GitHub Enterprise Server won't validate** | GitKraken can't reach it, or its certificate isn't publicly trusted | Confirm the **Server URL** is reachable over `https` from outside your network with a publicly-trusted certificate — self-signed and internal CAs won't work |

For general connection troubleshooting, see [Troubleshooting setup](/insights-expo/expo-ai-adoption-connect-your-data#troubleshooting-setup) on the Connect Your Data overview.

---

## After connecting

Once your connection is active, continue with the remaining setup steps on the [Connect Your Data](/insights-expo/expo-ai-adoption-connect-your-data) overview:

- [Set your benchmarks](/insights-expo/expo-ai-adoption-connect-your-data#set-your-benchmarks)
- [Map developer identities](/insights-expo/expo-ai-adoption-connect-your-data#map-developer-identities)
- [Invite your team](/insights-expo/expo-ai-adoption-connect-your-data#invite-your-team)
