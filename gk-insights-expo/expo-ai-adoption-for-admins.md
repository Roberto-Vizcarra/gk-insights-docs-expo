---
title: AI Adoption for Admins
description: Setup checklist, data freshness chain, roster hygiene, and security reference for GitKraken Insights admins.
product: GitKraken Insights
content_type: how-to
audience: admin
plan_required: GitKraken Insights
status: GA
page_type: content
nav_category: getting-started
nav_order: 50
nav_label: For Admins
card_icon: shield-lock
card_color: purple
card_description: Settings, roster, integrations, and data hygiene
taxonomy:
    category: insights-expo
---
<kbd>Last updated: September 2026</kbd>

## For admins

Setup, integrations, and the small list of things that keep the data clean. If you are the person who installed Insights or who is the org's go-to for "why does this number look weird?", this is for you.

### First-week setup checklist

New organizations get a **Setup** checklist in the sidebar that tracks the setup steps still outstanding. Each step's **Open step** button takes you to the Settings page where you complete it. The checklist is on by default the first time you open Insights, and if you close it you can switch it back on in [Settings → General](https://gitkraken.dev/ai-adoption/settings/general).

In order:

- [ ] **Roster is loaded.** Settings → Developers shows every active developer with a display name, git-provider login, and email. Inactive developers from your HRIS are marked `is_active = false`.
- [ ] **Teams are defined.** Settings → Teams has at least one team per organizational unit. Departments are populated for grouping.
- [ ] **Default Department is set.** Settings → General → "Default Department". This pre-selects the right view for first-time visitors.
- [ ] **PTO sync is on.** `WHOS_OUT_ICAL_URL` is set to your BambooHR iCal feed. The sync runs every 6 hours.
- [ ] **CFR sync is on (optional).** `JIRA_CUSTOMER_BUG_FIELD_ID` is set if you want to track customer bugs on /ai-adoption/ai-impact.
- [ ] **AI provider data is flowing.** Open `/ai-adoption/data-explorer`, filter to `event_name = user_prompt`, confirm rows for the last 24 hours.
- [ ] **Maturity Factor is sized for your org.** Default 0.75 works for most. If you are early in rollout, consider 0.6. If you are mature, 0.85.

### The data freshness chain

Every metric on the dashboard is downstream of one of these syncs. If a number looks wrong, the first thing to check is which sync is stale.

| Sync | Source | Interval | Owns |
| --- | --- | --- | --- |
| **Git provider sync** | GitHub, Bitbucket, Azure DevOps, or GitLab token — including the self-hosted GitHub Enterprise Server, Azure DevOps Server, and GitLab Self-Managed connections | Every few minutes | PRs, direct commits, reviews |
| **AI events sync** | Snowflake OTEL export (Claude / Codex) | Every 5 min, with 12 h safety lag | Adoption, agentic, AI-assisted detection |
| **Cursor sync** | Cursor API | Every 5 min, with 12 h safety lag | Cursor adoption |
| **CFR sync** | Jira | Every hour | Customer bugs, CFR %, MTTR |
| **Release sync** | GitHub Releases / configured release event / releases pushed with the manual releases API | Every few minutes | Deployment Frequency, Lead Time |
| **PTO sync** | BambooHR iCal | Every 6 hours | On-PTO tier, effective weekdays |
| **PR classifier** | Internal LLM | Continuous worker | Category, auto-category, CapEx / OpEx, Effort Score |
| **AI-assisted classifier** | Internal worker | Continuous, with 24 h refresh sweep | `is_ai_assisted` materialization |

The 12-hour **safety lag** on AI events sync exists because the upstream OTEL export sometimes backfills events into already-synced time ranges. The lag absorbs that overlap so data is not missed. Don't be alarmed if you see counts shift slightly within a 24-hour-old window.

### The unmatched lists

Two places in Settings → Developers surface "we couldn't auto-match this":

#### Unmatched PTO names

When the BambooHR feed has a name like "Jeffrey Schinella" but your roster has "Jeff Schinella," the sync drops the name into the unmatched list. Use the combobox to map it. Once mapped, the alias persists.

#### Unmatched Jira assignees

Same pattern: Jira `accountId`s that don't tie to a known developer email. Map them once and future bugs from that assignee will auto-route.

**Treat both lists like an inbox.** Empty them weekly. An unmatched PTO name means you are miscounting effective weekdays for that developer; an unmatched Jira assignee means CFR attribution is incomplete.

### Roster hygiene

The single biggest cause of "this number looks weird" tickets is roster drift. Check these monthly:

* `is_active = true` **should be a current employee.** Offboarded developers should be flipped to `false`, not deleted. Deletion loses historical attribution.
* **git-provider login is set and matches their actual handle.** This is the authoritative join key for PR and commit data. If someone has two accounts on the same provider, merge both identities into one person in Settings → Developers.
* **Team membership is current.** Reorgs happen quietly; your developer table will reflect the old structure until you update it.
* **Email aliases are mapped.** If a developer has multiple work emails (e.g. from an acquisition), add aliases in their profile so P90 cohorts don't double-count.

### Settings worth a quarterly check

| Setting | Default | Where | When to revisit |
| --- | --- | --- | --- |
| **Maturity Factor** | 0.75 | Settings → General (slider) | When AI Adoption % plateaus or when your org's actual maturity has clearly outgrown 0.75 |
| **Developer Hourly Rate** | $75 | Settings → General (input) | Annually, when your finance team updates the loaded rate |
| **Baseline Period** | Nov 1 last year | Settings → General (month picker) | When you launch a new AI tool and want uplift relative to a specific pre-launch month |
| **Default Department** | None | Settings → General (dropdown) | When your org structure shifts |
| **Tier Weights** | 0.5 / 0.2 / 0.3 | Settings → General | When you move from "rolling out" to "extracting value" — see the [Set tier weights playbook](/gk-insights/ai-adoption-playbook-tier-weights) |
| **Direct Commit Weight** | 0.5 | Settings → General | When your team's direct-commit workflow changes (e.g. moving to TBD) |
| **Review Weight** | 0.5 | Settings → General | When you want to emphasize or de-emphasize review work in Output Score |
| **Exclude Chore from Output Score** | On | Settings → General | Rarely. Default reflects most orgs' definition of "real output" |

### What to do when a number looks wrong

A short triage flow:

1. **Is the date range right?** Most "this is missing" tickets are sub-7-day windows where rate-normalized metrics widen the benchmark scope or fall back to defaults.
2. **Is the user being filtered?** Inactive developers, alias-only developers, and on-PTO developers don't show in all views.
3. **Is the sync stale?** Open /ai-adoption/data-explorer, filter by event type, check the most recent timestamp.
4. **Is the developer mapped?** Settings → Developers → check `is_active` and git-provider login.
5. **Are aliases set?** A developer with multiple emails will appear split across rows in P90 cohorts until aliases are mapped.

If none of those resolve it, file a support ticket with: the page URL, the date range, the team filter, and a screenshot.

### Demo mode

Use Settings → Demo Mode to switch the dashboard into a fake-data profile (Stellar / Launchpad / Atlas) for prospect demos or internal walkthroughs. Demo mode is per-session and respects browser storage — your real data is still there when you toggle off.

For self-contained marketing builds (where there is no real backend at all), set `DEMO_MODE=stellar|launchpad|atlas` as an environment variable. That short-circuits all API calls and forces a credentials-only login. Talk to your account team for the marketing-build recipe.

### Security and access

* **Authentication:** users sign in through GitKraken's identity, restricted to your configured organization.
* **Role-based access.** Org Settings writes are gated by the `org_settings:write` permission. Users without that permission see a banner explaining read-only access and a prompt to ask an org admin or owner. Permissions follow the standard GitKraken role model (Owner, Admin, Member).
* **Row-level filtering for sensitive views is not yet implemented.** All users with read access to the dashboard see all teams and developers. If you need row-level access controls, ask your account manager — it is on the roadmap.
* **Secrets.** The deployment loads credentials from your secret store of choice (typically AWS SSM Parameter Store or equivalent). Never commit `.env.local` or any file containing tokens.
