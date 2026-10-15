---
title: Credential Security and Integration Permissions
description: How GitKraken Insights protects the credentials you enter in Data Connections, who can manage connections, what each integration stores, and the permissions each one needs.
product: GitKraken Insights
content_type: reference
audience: admin
plan_required: GitKraken Insights
integrations: [GitHub, GitHub Enterprise Server, GitHub Copilot, GitLab, GitLab Self-Managed, Bitbucket, Bitbucket Data Center, Azure DevOps, Azure DevOps Server, Jira Cloud, Cursor, Devin, BambooHR, Claude Code, Codex, OpenCode]
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: red
    card_description: How credentials are protected and what each integration can access
    card_icon: shield-lock
    nav_category: admin
    nav_label: Security & Permissions
    nav_order: 30
    page_type: content
---
<kbd>Last updated: October 2026</kbd>

When you connect a data source in **Settings → Data Connections**, GitKraken Insights stores the credential it needs to sync that source. This page explains how those credentials are protected, who can manage them, and the access each integration needs. For setup steps, see [Connect Your Data](/insights-expo/expo-ai-adoption-connect-your-data).

---

## How credentials are protected

Insights encrypts every connection's credential before storing it, using industry-standard encryption with managed key protection. In practice:

- **Credentials are never stored in plain text.** They're encrypted before they're saved and kept only as ciphertext.
- **Credentials are decrypted only when a sync needs them,** for that specific sync job.
- **Stored secrets aren't shown back to you.** Connection views return masked values instead — when you edit a connection, leave the credential blank to keep the current one, or paste a new one to replace it.
- **Secret-bearing fields are redacted from application logs** to reduce accidental exposure.
- **Non-secret settings are kept apart from the credential where possible** — for example, which organizations, workspaces, or projects to sync.

---

## Who can manage connections

Only **Owners** and **Admins** of your GitKraken organization can create, update, view, or delete data connections. The same applies to the **GitKraken Insights for Developers** access toggle in **Settings → General**; the **AI cost rates** section and the **Categorization** tab appear only to people who can edit settings. Other members see a read-only notice on Data Connections asking them to contact an organization admin or owner.

---

## What each integration stores

Insights stores the minimum it needs to run each integration:

| Integration | Credential | Non-secret settings |
| --- | --- | --- |
| **GitHub — GitHub App** | None you provide — the App holds its own credentials on GitHub | The organization, and any repositories to skip |
| **GitHub — personal access token** | Personal access token | Organizations to sync, repositories to skip |
| **GitHub Enterprise Server** | Personal access token | Server URL, organizations to sync, repositories to skip |
| **GitHub Copilot** | Personal access token (classic, or fine-grained for an organization) | The organization name or enterprise slug |
| **GitLab / GitLab Self-Managed** | Personal access token | Server URL (Self-Managed), groups to sync, repositories to skip |
| **Bitbucket** | Atlassian API token and account email | Workspaces to sync, repositories to skip |
| **Bitbucket Data Center** | HTTP access token | Server URL, projects to sync, repositories to skip |
| **Azure DevOps / Azure DevOps Server** | Personal access token | Host domain or server URL and collection, projects, repositories to skip |
| **Jira Cloud** | API token and account email | Jira instance and project settings |
| **Cursor** | Team API key | — |
| **Devin** (available on request) | Service-user API key | Devin organization IDs |
| **BambooHR** | The authenticated Who's Out iCal feed URL | — |
| **Agent OTel Data** (Claude Code, OpenAI Codex, GitHub Copilot, and OpenCode telemetry) | None you provide — GitKraken generates your organization's auth token | — |

---

## Permissions each integration needs

Each integration needs read access to the data Insights uses for analytics, and no more than the scopes below. The connection only sees what the credential's account can see, so create each credential with an account that can access everything you want included.

### GitHub

**GitHub App.** Installing the App on an organization requires an organization owner; members can request it for an owner to approve. The App requests **read** access only — to actions, administration, code, metadata, and pull requests. See [Connect with the GitHub App](/insights-expo/expo-ai-adoption-connect-github#connect-with-the-github-app).

**Personal access token.** Either token type works:

- **Classic token:** `repo` and `read:org`.
- **Fine-grained token:** set the **Resource owner** to your organization and grant these repository permissions, all **Read**: **Metadata**, **Contents**, **Pull requests**, plus **Actions** (optional, for deployments detected from workflow runs) and **Administration** (optional, for the branch-protection signal). A fine-grained token can't list your organizations, so you type their names during setup.

GitHub Enterprise Server uses a personal access token with `repo` and `read:org`.

With this access, Insights reads repository metadata, pull requests and reviews, commit history, file and repository tree contents, release history, and GitHub Actions workflow run history. It also checks branch protection status as a read operation — if that permission isn't granted, Insights still works, but branch-protection signals may be incomplete.

### GitHub Copilot

A personal access token:

- **Organization:** a classic token with `read:org`, or a fine-grained token with the **Organization Copilot metrics: Read-only** permission. The token owner must be an organization owner, or a member with permission to view organization Copilot metrics.
- **Enterprise:** a classic token with `read:enterprise` (`manage_billing:copilot` also works). Use a classic token here — no fine-grained permission is documented for enterprise Copilot metrics. The token owner must be an enterprise owner, or a member with permission to view enterprise Copilot metrics.

The **Copilot usage metrics** policy must also be enabled on the organization or enterprise. See [GitHub Copilot](/insights-expo/expo-ai-adoption-connect-ai-tools#github-copilot).

### GitLab and GitLab Self-Managed

A personal access token with the `read_api` scope. See [Connect GitLab](/insights-expo/expo-ai-adoption-connect-gitlab).

### Bitbucket and Bitbucket Data Center

- **Bitbucket:** a scoped Atlassian API token for the **Bitbucket** app with `read:account`, `read:pipeline:bitbucket`, `read:pullrequest:bitbucket`, `read:repository:bitbucket`, `admin:repository:bitbucket`, and `read:workspace:bitbucket`.
- **Bitbucket Data Center:** an HTTP access token with read access to the projects and repositories you want to sync.

See [Connect Bitbucket](/insights-expo/expo-ai-adoption-connect-bitbucket).

### Azure DevOps and Azure DevOps Server

A personal access token with **Code (Read)** and **Build (Read)**, plus **Work Items (Read)** only if you sync work items. See [Connect Azure DevOps](/insights-expo/expo-ai-adoption-connect-azure-devops).

### Jira Cloud

A Jira Cloud API token — created with **Create API token**, not the scoped option — and the email of the Atlassian account that owns it. Insights reads:

- Issues referenced by engineering work
- Issue fields such as summary, type, and status
- Issue change history and status transitions
- Search results returned through JQL
- Assignee profile information needed for reporting
- Customer bug fields, when you choose to include them

Insights doesn't need Jira write access. See [Connect Jira & BambooHR](/insights-expo/expo-ai-adoption-connect-jira-bamboohr).

### Cursor

A Cursor **team** API key. Insights reads team usage events, Cursor's daily usage data, and your team member directory, and doesn't need write access in Cursor. See [Cursor](/insights-expo/expo-ai-adoption-connect-ai-tools#cursor-api-key).

### Devin

A Devin API key for a **service user** provisioned with the **Admin** role. Insights only reads data from the Devin API — it doesn't make changes in Devin. The Devin connection is available on request; contact your GitKraken account team to enable it. See [Devin](/insights-expo/expo-ai-adoption-connect-ai-tools#devin).

### BambooHR

The Who's Out iCal feed URL, generated by an account that can see the whole company's time off. Insights reads only the time-off entries the feed exposes — the integration is feed-based and doesn't use the BambooHR API. See [BambooHR](/insights-expo/expo-ai-adoption-connect-jira-bamboohr#bamboohr).

### Agent OTel Data

Claude Code, OpenAI Codex, GitHub Copilot, and OpenCode (available on request) send telemetry to GitKraken using the auth token GitKraken generates for your organization — you don't grant Insights access to anything. For exactly what the telemetry contains, see [What GitKraken collects from agent telemetry](/insights-expo/expo-ai-adoption-connect-otel#what-gitkraken-collects-from-agent-telemetry).

---

## Read-only by design

Insights is designed to read the source data it needs for analytics. It doesn't use your credentials to make changes in GitHub, Jira, Cursor, Devin, or BambooHR as part of syncing.

---

## Related pages

- [Connect Your Data](/insights-expo/expo-ai-adoption-connect-your-data) — the setup guide, including who needs which access.
- [Connect Agent OTel Data](/insights-expo/expo-ai-adoption-connect-otel) — telemetry setup and what it collects.
- [AI Adoption Settings](/insights-expo/expo-ai-adoption-settings) — the admin settings that affect AI Adoption scores.
