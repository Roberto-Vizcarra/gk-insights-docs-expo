---
title: Manual Critical Defects API for GitKraken Insights
description: Record critical defects in GitKraken Insights that your issue tracker doesn't capture — generate an API key, authenticate, and create or delete manual critical defects with the Manual Critical Defects API.
product: GitKraken Insights
content_type: reference
audience: admin
plan_required: GitKraken Insights
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    nav_category: hidden
    nav_order: 99
    nav_label: Critical Defects API
    page_type: content
    card_icon: ""
    card_color: ""
    card_description: ""
---
<!-- FLAG FOR HUMAN REVIEW: API key creation (gkDotDevApiKeys) is not confirmed released to all customers; unhide by moving into the admin nav once confirmed. -->
<kbd>Last updated: October 2026</kbd>

The Manual Critical Defects API lets you record critical defects in GitKraken Insights that aren't captured automatically from your issue tracker — for example, incidents tracked in a system GitKraken doesn't read, defects reported directly by customers, or historical defects you want to backfill.

Manual critical defects appear alongside defects synced from your issue tracker, and contribute to quality metrics such as [Change Failure Rate](/insights-expo/expo-ai-adoption-dora-change-failure-rate) and [Mean Time to Recovery](/insights-expo/expo-ai-adoption-dora-mttr).

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

## Create a manual critical defect

```
POST /v1/insights/analytics/critical-defects
```

Records a manual critical defect. The `defectKey` must be unique within your organization — reusing an existing key returns `400`.

### Request body

Content type: `application/json`

| Field | Type | Required | Description |
| --- | --- | --- | --- |
| `defectKey` | string | Yes | Defect identifier, unique among manual defects in the organization. 1–128 characters; letters, digits, dots, underscores, and hyphens only, starting with a letter or digit. |
| `repo` | string | Yes | Repository the defect is attributed to, in `owner/name` form. Must already exist in your organization for the given `gitProvider`. |
| `gitProvider` | string | Yes | Git provider. One of: `github`, `githubEnterprise`, `gitlab`, `gitlabSelfHosted`, `bitbucket`, `bitbucketServer`, `azure`, `azureDevopsServer`. |
| `severity` | string | Yes | Defect severity. `Highest`, `Critical`, `High`, `Medium`, `Low`, and `Lowest` map onto the standard priority bands. Any other value is matched against your organization's configured priority scale, and is grouped under "other" only if it isn't found there. |
| `detectedAt` | string (date-time) | Yes | When the defect was detected, in RFC 3339 format, e.g. `2026-07-09T14:30:00Z`. |
| `description` | string | No | Short summary of the defect. |
| `category` | string | No | Free-form category label, e.g. `regression`. |
| `fixAppliedAt` | string (date-time) | No | When the fix was applied, in RFC 3339 format. Must be on or after `detectedAt`. |
| `fixReleasedAt` | string (date-time) | No | When the fix reached production, in RFC 3339 format. Must be on or after `detectedAt`, and on or after `fixAppliedAt` when that's supplied. This is the defect's resolution time — omit it and the defect is treated as unresolved and doesn't contribute to MTTR. |
| `fixReleasedSha` | string | No | Commit SHA of the fix, 7–40 hexadecimal characters. |
| `fixReleasedRepoPath` | string | No | Repository the fix landed in, in `owner/name` form, if different from `repo`. |

### Example request

```bash
curl -i -X POST https://api.gitkraken.dev/v1/insights/analytics/critical-defects \
  -H "Authorization: Bearer $GK_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "defectKey": "INC-2026-07-09-01",
    "repo": "your-org/your-repo",
    "gitProvider": "github",
    "severity": "Critical",
    "detectedAt": "2026-07-09T14:30:00Z",
    "description": "Checkout fails on expired coupon",
    "category": "regression",
    "fixAppliedAt": "2026-07-10T09:00:00Z",
    "fixReleasedAt": "2026-07-10T17:00:00Z"
  }'
```

### Responses

| Status | Meaning |
| --- | --- |
| `201` | Defect created. The response body is an empty JSON object. |
| `400` | Bad request — a required field is missing or malformed, the `defectKey` already exists in this organization, the `repo` isn't known for the given `gitProvider`, or the repository is assigned to several teams with no designated attribution team. |
| `401` | Unauthenticated — the API key is missing, malformed, or expired. |
| `403` | Unauthorized — the key lacks Insights write permission for this organization. |
| `500` | Internal server error. |

---

## Delete a manual critical defect

```
DELETE /v1/insights/analytics/critical-defects/{defectKey}
```

Deletes a manual critical defect. Only manual defects can be deleted — defects synced automatically from your issue tracker aren't removable through this endpoint.

Unlike manual releases, no `repo` or `gitProvider` query parameters are needed: `defectKey` is unique across your whole organization, so the key alone identifies the defect.

### Path parameters

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| `defectKey` | string | Yes | The defect identifier used when the defect was created. |

### Example request

```bash
curl -i -X DELETE \
  "https://api.gitkraken.dev/v1/insights/analytics/critical-defects/INC-2026-07-09-01" \
  -H "Authorization: Bearer $GK_API_KEY"
```

### Responses

| Status | Meaning |
| --- | --- |
| `204` | Defect deleted. The response body is empty. |
| `401` | Unauthenticated — the API key is missing, malformed, or expired. |
| `403` | Unauthorized — the key lacks Insights write permission for this organization. |
| `404` | Not found — no manual defect with that key exists in this organization. |
| `500` | Internal server error. |

---

## How manual defects affect your metrics

Manual defects are counted the same way as defects synced from your issue tracker.

**Change Failure Rate.** A defect is attributed to the releases that were current when it was detected — releases whose window contains `detectedAt` — for the team that owns the repository you named. Because attribution runs through the team rather than the repository alone, a defect can mark releases across several of that team's repositories as failed.

**Mean Time to Recovery.** A defect contributes only when `fixReleasedAt` is set. The resolution time is measured from `detectedAt` to `fixReleasedAt`.

**Repository team assignment matters.** Attribution depends on the repository resolving to a team. If the repository you name has no team, the defect is still recorded and still appears in defect counts, but it isn't attributed to any release and doesn't affect Change Failure Rate. Assigning the team afterwards doesn't update existing defects — delete the defect and create it again so the team is resolved.

**Reporting windows.** Because a defect attaches to the release that was current when it was detected, a report window that starts after that release began won't show the attribution. Widen the date range to include the release that was live at `detectedAt`.

---

## Error format

Validation and not-found errors (`400`, `404`, `500`) return a JSON body of this shape:

```json
{
  "data": null,
  "error": {
    "message": "A description of what went wrong",
    "code": "400.1"
  }
}
```

Authentication and authorization errors (`401`, `403`) are returned before the request reaches Insights, and use a simpler shape:

```json
{
  "error": "invalid API key"
}
```

---

## Related pages

- [Manual Releases API](/insights-expo/expo-ai-adoption-manual-releases-api) — record the releases that critical defects are attributed to.
- [Connect Jira & BambooHR — Configure Change Failure Rate](/insights-expo/expo-ai-adoption-connect-jira-bamboohr#configure-change-failure-rate-cfr) — where defect detection is configured for issues Insights reads directly.
- [DORA & Quality Metrics](/insights-expo/expo-ai-adoption-dora-metrics) — the metrics critical defects feed.
