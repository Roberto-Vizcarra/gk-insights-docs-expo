---
title: Manual Releases API for GitKraken Insights
description: Record releases in GitKraken Insights that your git provider doesn't capture — generate an API key, authenticate, and create or delete manual releases with the Manual Releases API.
product: GitKraken Insights
content_type: reference
audience: admin
plan_required: GitKraken Insights
integrations: [GitHub, GitHub Enterprise Server, Bitbucket, Azure DevOps, Azure DevOps Server, GitLab, GitLab Self-Managed]
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: red
    card_description: Record releases via API when your git provider does not capture them
    card_icon: terminal-2
    nav_category: admin
    nav_label: Releases API
    nav_order: 20
    page_type: content
---
<kbd>Last updated: September 2026</kbd>

The Manual Releases API lets you record releases in GitKraken Insights that aren't captured automatically from your git provider — for example, releases cut by an external CI/CD system, deploys to a platform GitKraken doesn't read, or historical releases you want to backfill.

Manual releases appear alongside automatically detected releases in Insights reporting, and contribute to release-based metrics such as [Deployment Frequency](/insights-expo/expo-ai-adoption-dora-metrics#deployment-frequency).

> **Plan:** GitKraken Insights
> **Role:** An account that can create API keys for your organization
> **Base URL:** `https://api.gitkraken.dev`

---

## Generate an API key

1. Go to your account on gitkraken.dev: [https://gitkraken.dev/account](https://gitkraken.dev/account).
2. Open the **Security** settings tab.
3. Under **API keys**, click **Create API key**.
4. Give the key a name, choose the organization it applies to, and set an expiration.
5. Click **Create API key**.
6. Copy the key and store it somewhere secure — GitKraken shows it only once.

Your key looks like `gk_tkn_…`.

> **Treat the key as a password.** Never commit it to source control, paste it into a shared document, or send it over chat. If a key is exposed, delete it in the Security tab and create a replacement.

---

## Authenticate

Send the key as a bearer token on every request:

```
Authorization: Bearer YOUR_API_KEY
```

In the examples below, set the key as an environment variable so it never appears in your shell history or scripts:

```bash
export GK_API_KEY="YOUR_API_KEY"
```

---

## Create a manual release

```
POST /v1/insights/analytics/releases
```

Records a manual release. The `id` must be unique within the repository — reusing an existing `id` returns `400`.

### Request body

Content type: `application/json`

| Field | Type | Required | Description |
| --- | --- | --- | --- |
| `id` | string | Yes | Release identifier, unique within the repository. |
| `repo` | string | Yes | Repository name in `owner/name` form. |
| `gitProvider` | string | Yes | Git provider, e.g. `github`. |
| `releasedAt` | string (date-time) | Yes | Release time in RFC 3339 format, e.g. `2026-07-09T14:30:00Z`. |
| `tagName` | string | No | Release tag. |
| `headBranch` | string | No | Branch the release was cut from. |

### Example request

```bash
curl -i -X POST https://api.gitkraken.dev/v1/insights/analytics/releases \
  -H "Authorization: Bearer $GK_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "id": "release-2026-07-09-01",
    "repo": "your-org/your-repo",
    "gitProvider": "github",
    "releasedAt": "2026-07-09T14:30:00Z",
    "tagName": "v1.4.0",
    "headBranch": "main"
  }'
```

### Responses

| Status | Meaning |
| --- | --- |
| `201` | Release created. The response body is empty. |
| `400` | Bad request — a required field is missing or malformed, or the `id` already exists in this repository. |
| `401` | Unauthenticated — the API key is missing, malformed, or expired. |
| `403` | Unauthorized — the key lacks Insights write permission for this organization. |
| `500` | Internal server error. |

---

## Delete a manual release

```
DELETE /v1/insights/analytics/releases/{id}
```

Deletes a manual release. Only manual releases can be deleted — releases detected automatically from your git provider are not removable through this endpoint.

### Path parameters

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| `id` | string | Yes | The release identifier. |

### Query parameters

Both are required to identify the release, since `id` is only unique within a repository.

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| `repo` | string | Yes | Repository name in `owner/name` form. URL-encode the `/` as `%2F`. |
| `gitProvider` | string | Yes | Git provider, e.g. `github`. |

### Example request

```bash
curl -i -X DELETE \
  "https://api.gitkraken.dev/v1/insights/analytics/releases/release-2026-07-09-01?gitProvider=github&repo=your-org%2Fyour-repo" \
  -H "Authorization: Bearer $GK_API_KEY"
```

### Responses

| Status | Meaning |
| --- | --- |
| `204` | Release deleted. The response body is empty. |
| `400` | Bad request — `repo` or `gitProvider` is missing or malformed. |
| `401` | Unauthenticated — the API key is missing, malformed, or expired. |
| `403` | Unauthorized — the key lacks Insights write permission for this organization. |
| `404` | Not found — no manual release with that `id` exists in the given repository. |
| `500` | Internal server error. |

---

## Error format

Error responses (`400`, `401`, `403`, `404`, `500`) return a JSON body:

```json
{
  "error": {
    "message": "A description of what went wrong"
  }
}
```

---

## Related pages

- [Connect Jira & BambooHR — Configure Change Failure Rate](/insights-expo/expo-ai-adoption-connect-jira-bamboohr#configure-change-failure-rate-cfr) — where release tracking is configured for repositories Insights reads directly.
- [DORA & Quality Metrics](/insights-expo/expo-ai-adoption-dora-metrics) — the metrics releases feed: Deployment Frequency, Lead Time for Changes, and Change Failure Rate.
