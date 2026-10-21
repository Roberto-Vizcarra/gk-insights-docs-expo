---
title: AI Adoption for Admins
description: Setup checklist, data freshness chain, roster hygiene, and security reference for GitKraken Insights admins.
product: GitKraken Insights
content_type: how-to
audience: admin
plan_required: GitKraken Insights
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: purple
    card_description: Settings, roster, integrations, and data hygiene
    card_icon: shield-lock
    nav_category: getting-started
    nav_label: For Admins
    nav_order: 50
    page_type: content
---
<kbd>Last updated: October 2026</kbd>

## For admins

Setup, integrations, and the small list of things that keep the data clean. If you are the person who installed Insights or who is the org's go-to for "why does this number look weird?", this is for you.

### First-week setup checklist

New organizations get a **Setup** checklist in the sidebar that tracks the setup steps still outstanding. Each step's **Open step** button takes you to the Settings page where you complete it. The checklist is on by default the first time you open Insights, and if you close it you can switch it back on in [Settings → General](https://gitkraken.dev/ai-adoption/settings/general).

The checklist's steps, in order. The first five are required; the last two are optional and can be dismissed:

- [ ] **Connect a Git provider.** Connect a Git provider such as GitHub, Bitbucket, or Azure DevOps in Settings → Data Connections. Pull-request, commit, and contributor metrics all come from it.
- [ ] **Connect AI tools.** Connect at least one AI coding tool, such as Claude Code, Cursor, Codex, or OpenCode (available on request).
- [ ] **Set organization settings.** Set a **Developer Hourly Rate** and a **Baseline Period** in Settings → General.
- [ ] **Identify team members.** In Settings → Developers, give every tracked contributor an email, and review any records that look like duplicates of someone already on your roster. While duplicates are waiting, the step shows how many suggested merges are left, and it stays open until you've reviewed them. See [Suggested merges](/insights-expo/expo-ai-adoption-settings#suggested-merges).
- [ ] **Organize teams.** Assign at least one developer to a team in Settings → Teams.
- [ ] **Connect Jira (optional).** Jira gives AI Impact the cycle-time start signals it uses to compare like-for-like work.
- [ ] **Connect time off calendar (optional).** Connect a time-off source, such as BambooHR, so adoption scores don't penalize developers for time off.

Once the checklist is done, check these too:

- [ ] **Teams are grouped.** Settings → Departments places teams in departments, and departments in business units, so the Team filter groups them. See [Departments settings](/insights-expo/expo-ai-adoption-settings#departments-settings).
- [ ] **Default Department is set.** Settings → General → "Default Department". This pre-selects the right view for first-time visitors.
- [ ] **AI provider data is flowing.** Open `/ai-adoption/data-explorer`, filter to `event_name = user_prompt`, confirm rows for the last 24 hours.
- [ ] **Maturity Factor is sized for your org.** Set as **Company AI Readiness (%)** in Settings → General; the default is 100% (no scaling). If you are early in rollout, consider 60%. If you are in active rollout, consider 75–85%.

### The data freshness chain

Every metric on the dashboard is downstream of one of these syncs. If a number looks wrong, the first thing to check is which sync is stale.

| Sync | Source | Interval | Owns |
| --- | --- | --- | --- |
| **Git provider sync** | GitHub, Bitbucket, Azure DevOps, or GitLab token — including the self-hosted GitHub Enterprise Server, Azure DevOps Server, and GitLab Self-Managed connections | Every few minutes | PRs, direct commits, reviews |
| **AI events sync** | [Agent OTel Data](/insights-expo/expo-ai-adoption-connect-otel) — OpenTelemetry export from your AI tools (Claude Code / Codex / GitHub Copilot / OpenCode) | Every 5 min, with 12 h safety lag | Adoption, agentic, AI-assisted detection |
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
* **git-provider login is set and matches their actual handle.** This is the authoritative join key for PR and commit data. If someone has two accounts on the same provider, merge both identities into one person in Settings → Developers. Insights suggests likely duplicates for you to review — see [Suggested merges](/insights-expo/expo-ai-adoption-settings#suggested-merges). Merges can't be undone.
* **Team membership is current.** Reorgs happen quietly; your developer table will reflect the old structure until you update it. A developer can belong to more than one team: in Settings → Developers, edit the developer's **Teams**, or select several developers and use **Add to team** or **Remove from team**. When a developer is on two or more teams, pick a **Primary team**: the team their work counts toward for delivery metrics. The roster's **Team** column marks it **Primary**.
* **Email aliases are mapped.** If a developer has multiple work emails (e.g. from an acquisition), add aliases in their profile so P90 cohorts don't double-count.

### Settings worth a quarterly check

| Setting | Default | Where | When to revisit |
| --- | --- | --- | --- |
| **Maturity Factor** | 100% | Settings → General → **Company AI Readiness (%)** (input) | When AI Adoption % plateaus or when your org's actual maturity has clearly outgrown the value you set |
| **Developer Hourly Rate** | $75 | Settings → General (input) | Annually, when your finance team updates the loaded rate. Developers with their own rate in Settings → Developers use that instead. |
| **Working Hours Per Week** | 40 | Settings → General (input, 1–80) | When your org's standard work week changes |
| **Baseline Period** | Detected automatically where possible (about 90 days before your first repository connection) | Settings → General (month picker) | When you launch a new AI tool and want uplift relative to a specific pre-launch month |
| **Cycle Time Max Days** | 30 | Settings → General (input, 1–365) | When long-running PRs are being dropped from Cycle Time that you want counted, or outliers still dominate |
| **AI cost rates** | 1× per provider | Settings → General → **AI cost rates** | When your negotiated AI pricing changes |
| **Default Department** | None | Settings → General (dropdown) | When your org structure shifts |
| **GitKraken Insights for Developers** | Off | Settings → General → **Access to Insights** (checkbox; owners and admins only) | When you want members without org-wide access to see their own [personal page](/insights-expo/expo-ai-adoption-insights-for-developers) |
| **Tier Weights** | 0.5 / 0.2 / 0.3 | Settings → General | When you move from "rolling out" to "extracting value" — see the [Set tier weights playbook](/insights-expo/expo-ai-adoption-playbook-tier-weights) |
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
* **Who can view Insights.** The org-wide Insights pages are available to Owners, Admins, and Managers of your GitKraken organization. Other members can't open them; when [GitKraken Insights for Developers](/insights-expo/expo-ai-adoption-settings#gitkraken-insights-for-developers) is on, they see only their own personal page instead. See [Who can view Insights](/insights-expo/expo-ai-adoption-security-permissions#who-can-view-insights).
* **Role-based access.** Org Settings writes are gated by the `org_settings:write` permission. Users without that permission see a banner explaining read-only access and a prompt to ask an org admin or owner. Permissions follow the standard GitKraken role model (Owner, Admin, Manager, Member).
* **Row-level filtering for sensitive views is not yet implemented.** All users who can view the org-wide pages see all teams and developers. If you need row-level access controls, ask your account manager — it is on the roadmap.
* **Secrets.** The deployment loads credentials from your secret store of choice (typically AWS SSM Parameter Store or equivalent). Never commit `.env.local` or any file containing tokens.
