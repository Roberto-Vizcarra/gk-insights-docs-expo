---
title: Connect GitHub
description: Connect GitHub Cloud, GitHub Enterprise Server, or GitHub Enterprise Cloud to GitKraken Insights — token types, required permissions, and setup steps.
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
<kbd>Last updated: September 2026</kbd>

Connect GitHub Cloud or GitHub Enterprise Server to sync repository, pull request, and contributor activity into GitKraken Insights. Your git provider powers every PR, commit, contributor, and cycle-time metric.

> **Before you start:** make sure you have the right access. See the [prerequisites table](/insights-expo/expo-ai-adoption-connect-your-data#before-you-start--gather-access) on the Connect Your Data overview.

---

## GitHub

1. In gitkraken.dev, open **Insights → Settings → Data Connections**.
2. Under **Add data source**, click **+** next to **GitHub**.
3. In GitHub, create a token (see scopes below), paste it into the field, click **Validate**, then **Connect**.

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

**Recommended — Organization permissions (Read-only):**

- **Members** — *Read* — lets us match GitHub identities to people on your roster, which makes [identity mapping](/insights-expo/expo-ai-adoption-connect-your-data#map-developer-identities) far smoother.

> If you use a **classic** token instead, select the **`repo`** (read) and **`read:org`** scopes.

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
| **GitHub Enterprise Server won't validate** | GitKraken can't reach it, or its certificate isn't publicly trusted | Confirm the **Server URL** is reachable over `https` from outside your network with a publicly-trusted certificate — self-signed and internal CAs won't work |

For general connection troubleshooting, see [Troubleshooting setup](/insights-expo/expo-ai-adoption-connect-your-data#troubleshooting-setup) on the Connect Your Data overview.

---

## After connecting

Once your connection is active, continue with the remaining setup steps on the [Connect Your Data](/insights-expo/expo-ai-adoption-connect-your-data) overview:

- [Set your benchmarks](/insights-expo/expo-ai-adoption-connect-your-data#set-your-benchmarks)
- [Map developer identities](/insights-expo/expo-ai-adoption-connect-your-data#map-developer-identities)
- [Invite your team](/insights-expo/expo-ai-adoption-connect-your-data#invite-your-team)
