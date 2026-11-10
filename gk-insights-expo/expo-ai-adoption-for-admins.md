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
<kbd>Last updated: November 2026</kbd>

## For admins

Set up Insights, connect its integrations, and keep the data clean. Start here if you installed Insights, or if your org asks you "why does this number look weird?"

### First-week setup checklist

New organizations get a **Setup** checklist in the sidebar that tracks the setup steps still outstanding. Each step's **Open step** button takes you to the Settings page where you complete it. The checklist is on by default the first time you open Insights, and if you close it you can switch it back on in [Settings → General](https://gitkraken.dev/insights/settings/general).

The checklist has these steps, in order. The first 5 are required; you can dismiss the last 2, which are optional:

- [ ] **Connect a Git provider.** Connect a Git provider such as GitHub, Bitbucket, or Azure DevOps in Settings → Data Connections. Pull-request, commit, and contributor metrics all come from it.
- [ ] **Connect AI tools.** Connect at least one AI coding tool, such as Claude Code, Cursor, Codex, or OpenCode (available on request).
- [ ] **Set organization settings.** Set a **Developer Hourly Rate** and a **Baseline Period** in Settings → General.
- [ ] **Identify team members.** In Settings → Developers, give every tracked contributor an email, and review any records that look like duplicates of someone already on your roster. While duplicates are waiting, the step shows how many suggested merges are left, and it stays open until you've reviewed them. See [Suggested merges](/insights-expo/expo-ai-adoption-settings#suggested-merges).
- [ ] **Organize teams.** Assign at least one developer to a team in Settings → Teams.
- [ ] **Connect Jira (optional).** Jira gives AI Impact the cycle-time start signals it uses to compare like-for-like work.
- [ ] **Connect time off calendar (optional).** Connect a time-off source, such as BambooHR, so adoption scores do not penalize developers for time off.

After you finish the checklist, check these too:

- [ ] **Teams are grouped.** Settings → Departments places teams in departments, and departments in business units, so the Team filter groups them. See [Departments settings](/insights-expo/expo-ai-adoption-settings#departments-settings).
- [ ] **Default Department is set.** Settings → General → "Default Department". This pre-selects the right view for first-time visitors.
- [ ] **AI provider data is flowing.** Open **Data Explorer**, select **AI Events**, filter the event name to `user_prompt`, and confirm rows for the last 24 hours.
- [ ] **Maturity Factor is sized for your org.** Set as **Company AI Readiness (%)** in Settings → General; the default is 100% (no scaling). If you are early in rollout, consider 60%. If you are in active rollout, consider 75% to 85%.

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
| **PR classification** | AI classification of your PRs and commits | Continuous | Category, auto-category, CapEx / OpEx, Effort Score |
| **AI-assisted detection** | Your git and AI tool data | Continuous, rechecked every 24 h | AI-assisted status of PRs and commits |

The 12-hour **safety lag** on AI events sync catches events that your AI tools report late, into time ranges that were already synced, so no data is missed. Expect counts to shift slightly within a 24-hour-old window.

### Bugs assigned to unmatched Jira assignees

Customer bugs assigned to someone GitKraken Insights cannot match to a roster developer cannot be tied to a release, so they are not counted in Change Failure Rate. The CFR readouts show how many, with a note such as "3 bugs not attributed to a team", and **Status** on the Jira connection (Settings → Data Connections) shows the number of unmatched assignees.

### Roster hygiene

Roster drift causes more "this number looks weird" tickets than anything else. Check these monthly:

* **Every Included developer is a current employee.** In Settings → Developers, set offboarded developers to **Hidden** in the **Visibility** column instead of deleting them. Deletion loses historical attribution.
* **git-provider login is set and matches their actual handle.** GitKraken Insights uses this login to match PR and commit data to the developer. If someone has two accounts on the same provider, merge both identities into one person in Settings → Developers. Insights suggests likely duplicates for you to review — see [Suggested merges](/insights-expo/expo-ai-adoption-settings#suggested-merges). You cannot undo a merge, and you cannot merge a bot account with a person.
* **Team membership is current.** Reorgs happen quietly; your developer table will reflect the old structure until you update it. A developer can belong to more than one team: in Settings → Developers, edit the developer's **Teams**, or select several developers and use **Add to team** or **Remove from team**. When a developer is on two or more teams, pick a **Primary team**: the team their work counts toward for delivery metrics. The roster's **Team** column marks it **Primary**.
* **Email aliases are mapped.** If a developer has multiple work emails (for example, from an acquisition), add aliases in their profile so P90 cohorts do not double-count.

### Settings worth a quarterly check

| Setting | Default | Where | When to revisit |
| --- | --- | --- | --- |
| **Maturity Factor** | 100% | Settings → General → **Company AI Readiness (%)** (input) | When AI Adoption % plateaus or when your org's actual maturity has clearly outgrown the value you set |
| **Developer Hourly Rate** | $75 | Settings → General (input) | Annually, when your finance team updates the loaded rate. Developers with their own rate in Settings → Developers use that instead. |
| **Working Hours Per Week** | 40 | Settings → General (input, 1 to 80) | When your org's standard work week changes |
| **Baseline Period** | Detected automatically where possible (about 90 days before your first repository connection) | Settings → General (month picker) | When you launch a new AI tool and want uplift relative to a specific pre-launch month |
| **Cycle Time Max Days** | 30 | Settings → General (input, 1 to 365) | When long-running PRs are being dropped from Cycle Time that you want counted, or outliers still dominate |
| **AI cost rates** | 1× per provider | Settings → General → **AI cost rates** | When your negotiated AI pricing changes |
| **Default Department** | None | Settings → General (dropdown) | When your org structure shifts |
| **GitKraken Insights for Developers** | Off | Settings → General → **Access to Insights** (checkbox; owners and admins only) | When you want members without org-wide access to see their own [personal page](/insights-expo/expo-ai-adoption-insights-for-developers) |
| **Tier Weights** | 0.5 / 0.2 / 0.3 | Settings → General | When you move from "rolling out" to "extracting value" — see the [Set tier weights playbook](/insights-expo/expo-ai-adoption-playbook-tier-weights) |
| **Direct Commit Weight** | 0.5 | Settings → General | When your team's direct-commit workflow changes (for example, moving to trunk-based development) |
| **Review Weight** | 0.5 | Settings → General | When you want to emphasize or de-emphasize review work in Output Score |
| **Exclude Chore from Output Score** | On | Settings → General | Rarely. Default reflects most orgs' definition of "real output" |

### What to do when a number looks wrong

Work through these questions in order:

1. **Is the date range right?** Most "this is missing" tickets are sub-7-day windows where rate-normalized metrics widen the benchmark scope or fall back to defaults.
2. **Is the user being filtered?** Inactive developers, alias-only developers, and on-PTO developers do not show in all views.
3. **Is the sync stale?** Open **Data Explorer**, filter by event type, and check the most recent timestamp.
4. **Is the developer mapped?** In Settings → Developers, check the developer's **Visibility** and git-provider login.
5. **Are aliases set?** A developer with multiple emails will appear split across rows in P90 cohorts until aliases are mapped.

If none of those resolve it, file a support ticket with: the page URL, the date range, the team filter, and a screenshot.

### Security and access

* **Authentication:** users sign in through GitKraken's identity, restricted to your configured organization.
* **Who can view Insights.** The org-wide Insights pages are available to Owners, Admins, and Leads of your GitKraken organization. Other members cannot open them; when [GitKraken Insights for Developers](/insights-expo/expo-ai-adoption-settings#gitkraken-insights-for-developers) is on, they see only their own personal page instead. See [Who can view Insights](/insights-expo/expo-ai-adoption-security-permissions#who-can-view-insights).
* **Role-based access.** Changing settings requires permission to manage organization settings (organization owners and admins have it). Users without that permission see a banner explaining read-only access and a prompt to ask an org admin or owner. Permissions follow your GitKraken organization roles, such as Owner, Admin, Lead, and User.
* **Row-level filtering for sensitive views is not yet available.** All users who can view the org-wide pages see all teams and developers. If you need row-level access controls, ask your account manager — it is on the roadmap.
