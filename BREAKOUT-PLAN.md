# Help Center Page Breakout Plan

> **Status:** Approved — pending visual design discussion before execution  
> **Date:** September 1, 2026  
> **Scope:** Break dense pages into standalone pages; add frontmatter tags for auto-built side nav

---

## Summary

Split 3 source pages into ~24 total pages (from 11). Keep metric pages, settings, and the API reference as-is.

| Action | Source page | New pages created |
|--------|------------|-------------------|
| Break out by audience | Getting Started | 5 new + reworked overview |
| Break out by provider | Connect Your Data | 6 new + reworked overview |
| Break out by playbook | Playbooks | 4 new + reworked index |
| Keep as-is | 5 metric family pages, Settings, Manual Releases API, Hub | 0 |

---

## New Page Inventory

### Getting Started (6 pages)

| File | Slug | Title | nav_order |
|------|------|-------|-----------|
| `expo-ai-adoption-getting-started.md` | `ai-adoption-getting-started` | Getting Started with AI Adoption | 10 |
| `expo-ai-adoption-first-dashboard.md` | `ai-adoption-first-dashboard` | Reading Your First Dashboard | 20 |
| `expo-ai-adoption-for-executives.md` | `ai-adoption-for-executives` | AI Adoption for Executives | 30 |
| `expo-ai-adoption-for-engineering-leaders.md` | `ai-adoption-for-engineering-leaders` | AI Adoption for Engineering Leaders | 40 |
| `expo-ai-adoption-for-team-leads.md` | `ai-adoption-for-team-leads` | AI Adoption for Team Leads | 50 |
| `expo-ai-adoption-for-admins.md` | `ai-adoption-for-admins` | AI Adoption for Admins | 60 |

### Connect Your Data (7 pages)

| File | Slug | Title | nav_order |
|------|------|-------|-----------|
| `expo-ai-adoption-connect-your-data.md` | `ai-adoption-connect-your-data` | Connect Your Data — Overview | 10 |
| `expo-ai-adoption-connect-github.md` | `ai-adoption-connect-github` | Connect GitHub | 20 |
| `expo-ai-adoption-connect-bitbucket.md` | `ai-adoption-connect-bitbucket` | Connect Bitbucket | 30 |
| `expo-ai-adoption-connect-azure-devops.md` | `ai-adoption-connect-azure-devops` | Connect Azure DevOps | 40 |
| `expo-ai-adoption-connect-gitlab.md` | `ai-adoption-connect-gitlab` | Connect GitLab | 50 |
| `expo-ai-adoption-connect-ai-tools.md` | `ai-adoption-connect-ai-tools` | Connect AI Coding Tools | 60 |
| `expo-ai-adoption-connect-jira-bamboohr.md` | `ai-adoption-connect-jira-bamboohr` | Connect Jira & BambooHR | 70 |

### Metrics (5 pages — unchanged)

| File | Slug | Title | nav_order |
|------|------|-------|-----------|
| `expo-ai-adoption-agentic-metrics.md` | `ai-adoption-agentic-metrics` | Adoption & Agentic Metrics | 10 |
| `expo-ai-adoption-output-metrics.md` | `ai-adoption-output-metrics` | Output & Throughput Metrics | 20 |
| `expo-ai-adoption-flow-metrics.md` | `ai-adoption-flow-metrics` | Flow & Cycle Time Metrics | 30 |
| `expo-ai-adoption-dora-metrics.md` | `ai-adoption-dora-metrics` | DORA & Quality Metrics | 40 |
| `expo-ai-adoption-impact-cost-metrics.md` | `ai-adoption-impact-cost-metrics` | AI Impact & Cost Metrics | 50 |

### Playbooks (4 pages + index)

| File | Slug | Title | nav_order |
|------|------|-------|-----------|
| `expo-ai-adoption-playbooks.md` | `ai-adoption-playbooks` | AI Adoption Playbooks | 10 |
| `expo-ai-adoption-playbook-tier-weights.md` | `ai-adoption-playbook-tier-weights` | Set Tier Weights for Your Org's Maturity | 20 |
| `expo-ai-adoption-playbook-ai-rollout.md` | `ai-adoption-playbook-ai-rollout` | Roll Out AI Tooling with the Adoption Score | 30 |
| `expo-ai-adoption-playbook-slow-cycle-time.md` | `ai-adoption-playbook-slow-cycle-time` | Investigate a Slow Cycle Time | 40 |
| `expo-ai-adoption-playbook-high-cfr.md` | `ai-adoption-playbook-high-cfr` | Interpret a High CFR Week | 50 |

### Admin & API (2 pages — unchanged)

| File | Slug | Title | nav_order |
|------|------|-------|-----------|
| `expo-ai-adoption-settings.md` | `ai-adoption-settings` | AI Adoption Settings | 10 |
| `expo-ai-adoption-manual-releases-api.md` | `ai-adoption-manual-releases-api` | Manual Releases API | 20 |

---

## Content Mapping

### Getting Started → 6 pages

**Overview page keeps:**
- Intro paragraph + requirements callout
- "What this product does, in one sentence"
- "What this product does _not_ do"
- "Three pages that matter most"
- "Pick your starting point" (rewrite links to point to new child pages)
- "How to think about developer scores" (heavily cross-linked; stays here)
- "A note on benchmarks"
- "Next" section

**Reading Your First Dashboard (`first-dashboard`) gets:**
- Everything from the H2 "Reading your first dashboard" through "Glossary refresher"
- Sections: Set the scope first, The team table (6 H4 columns), Expand a row, What to look at first, Where to go next, What the dashboard won't show you, Glossary refresher
- Image: `ai-adoption-team-lead-flow.png` (the one at line 100)

**For Executives (`for-executives`) gets:**
- Everything from the H2 "For executives" through "Where to drill further"
- Sections: The four numbers (4 H4s), How to read the trend lines, What to put in your monthly report, What _not_ to do, Where to drill further
- Image: `ai-adoption-executive-view.png`

**For Engineering Leaders (`for-engineering-leaders`) gets:**
- Everything from the H2 "For engineering leaders" through "Where to drill further"
- Sections: Weekly 10-minute scan, Quarterly review (4 H4 steps), How to read scores fairly, Settings to verify quarterly, Where to drill further

**For Team Leads (`for-team-leads`) gets:**
- Everything from the H2 "For team leads" through "Where to drill further"
- Sections: Weekly home (/teams), What to look at when, Weekly ritual, How to interpret fairly, What the dashboard won't tell you, Where to drill further
- Image: `ai-adoption-team-lead-flow.png` (the one at line 354)

**For Admins (`for-admins`) gets:**
- Everything from the H2 "For admins" through "Security and access"
- Sections: First-week setup checklist, Data freshness chain, Unmatched lists, Roster hygiene, Settings worth a quarterly check, What to do when a number looks wrong, Demo mode, Security and access

---

### Connect Your Data → 7 pages

**Overview page keeps:**
- Intro paragraph + requirements callout + Next-Gen note
- "Before you start — gather access" (the prerequisites table)
- "How the Data Connections page works"
- Image: `settings-data-connections-aug-2026.png`
- "Step 4 — Set your benchmarks" (rename to just "Set your benchmarks")
- "Step 5 — Map developer identities" + "Excluding review bots"
- "Step 6 — Invite your team"
- "What to expect after setup"
- "Troubleshooting setup" (the 19-row table)
- "Related pages"

**Connect GitHub (`connect-github`) gets:**
- H3 "GitHub" content (fine-grained vs classic token table, required permissions, editing tokens)
- H3 "GitHub Enterprise Server" content (HTTPS requirement, PAT scopes, 3-step connection)
- Provider-specific troubleshooting rows from the overview's troubleshooting table: "Missing orgs with fine-grained tokens", "GitHub token UI hiccup"

**Connect Bitbucket (`connect-bitbucket`) gets:**
- H3 "Bitbucket" content (Atlassian API token, required scopes, 4-step connection)
- H3 "Bitbucket Data Center" content (self-hosted requirements, HTTP access token)
- Image: `connect-bitbucket-modal.png`
- Provider-specific troubleshooting row: "Missing Bitbucket workspaces"

**Connect Azure DevOps (`connect-azure-devops`) gets:**
- H3 "Azure DevOps" content (PAT creation, Code(Read) scope, host domain, project filter, legacy URL note, Entra ID caveat)
- H3 "Azure DevOps Server" content (Server 2022+ requirement, 4-step connection)
- Image: `azure-devops-pat-tokens-menu.png`
- Provider-specific troubleshooting rows: "Azure DevOps sync stopped", "Self-hosted server validation failure"

**Connect GitLab (`connect-gitlab`) gets:**
- H3 "GitLab" content (PAT with read_api, 3-step connection)
- H3 "GitLab Self-Managed" content (self-hosted requirements, same PAT scope, 3-step connection)

**Connect AI Coding Tools (`connect-ai-tools`) gets:**
- H3 "Claude Code and Codex (OpenTelemetry)" (full section including org-managed settings, JSON snippet, file-based deploy, per-OS paths, Codex config.toml)
- H3 "Cursor (API key)"
- H3 "Devin"
- H3 "GitHub Copilot" (org vs enterprise table, usage metrics policy, classic PAT, SAML SSO)
- Images: `claude-code-codex-setup.png`, `connect-copilot-modal-aug-2026.png`
- Provider-specific troubleshooting rows: "Cursor key issues", "Claude Code org settings", "Missing Claude Code telemetry", "Missing Codex telemetry", "Copilot no metrics", "Copilot token rejected"

**Connect Jira & BambooHR (`connect-jira-bamboohr`) gets:**
- H3 "Jira" content (API token creation, no scoped tokens)
- H4 "Configure Change Failure Rate (CFR)" (customer bug field ID, release signal types, manual releases API link, verification)
- H3 "BambooHR" content (iCal feed URL, 6-step connection, visibility, security)
- Provider-specific troubleshooting rows: "Jira token rejected", "BambooHR feed failure", "CFR zeros"

---

### Playbooks → 5 pages

**Index page keeps:**
- Intro paragraph
- Summary table (rewrite links to point to new child pages)

**Each playbook page gets its full H2 section as-is:**
- `playbook-tier-weights`: "Set tier weights for your org's maturity" (all sub-sections)
- `playbook-ai-rollout`: "Roll out AI tooling with the Adoption Score" (all sub-sections)
- `playbook-slow-cycle-time`: "Investigate a slow cycle time" (all sub-sections)
- `playbook-high-cfr`: "Interpret a high CFR week" (all sub-sections)

---

## Image Assignment

Every image and its destination after breakout. Total count stays at 10.

| Image file | Current page(s) | After breakout |
|-----------|-----------------|----------------|
| `ai-adoption-team-lead-flow.png` | getting-started (×2), flow-metrics (×1) | `first-dashboard` (×1), `for-team-leads` (×1), flow-metrics (×1) |
| `ai-adoption-executive-view.png` | getting-started (×1), agentic-metrics (×1) | `for-executives` (×1), agentic-metrics (×1) |
| `ai-adoption-developers.png` | agentic-metrics (×3), output-metrics (×1) | No change |
| `ai-adoption-settings-general.png` | agentic-metrics (×1), settings (×1) | No change |
| `settings-data-connections-aug-2026.png` | connect-your-data (×1) | connect-your-data overview (×1) |
| `connect-bitbucket-modal.png` | connect-your-data (×1) | `connect-bitbucket` (×1) |
| `azure-devops-pat-tokens-menu.png` | connect-your-data (×1) | `connect-azure-devops` (×1) |
| `claude-code-codex-setup.png` | connect-your-data (×1) | `connect-ai-tools` (×1) |
| `connect-copilot-modal-aug-2026.png` | connect-your-data (×1) | `connect-ai-tools` (×1) |
| `ai-adoption-wip-trend.png` | flow-metrics (×1) | No change |

---

## Link Rewrite Table

Every cross-page link that changes because an anchor becomes a standalone page.

### Getting Started anchors → new pages

| Old link | New link | Used in |
|----------|----------|---------|
| `/gk-insights/ai-adoption-getting-started#for-executives` | `/gk-insights/ai-adoption-for-executives` | getting-started overview (pick your starting point) |
| `/gk-insights/ai-adoption-getting-started#for-engineering-leaders` | `/gk-insights/ai-adoption-for-engineering-leaders` | getting-started overview, playbooks (line 241) |
| `/gk-insights/ai-adoption-getting-started#for-team-leads` | `/gk-insights/ai-adoption-for-team-leads` | getting-started overview |
| `/gk-insights/ai-adoption-getting-started#for-admins` | `/gk-insights/ai-adoption-for-admins` | getting-started overview, connect-your-data (lines 510, 535, 598), settings (line 17) |
| `/gk-insights/ai-adoption-getting-started#reading-your-first-dashboard` | `/gk-insights/ai-adoption-first-dashboard` | getting-started overview |
| `/gk-insights/ai-adoption-getting-started#how-to-think-about-developer-scores` | No change — stays on getting-started overview | agentic-metrics (line 74), playbooks (line 231) |

### Connect Your Data anchors → new pages

| Old link | New link | Used in |
|----------|----------|---------|
| `/gk-insights/ai-adoption-connect-your-data#configure-change-failure-rate-cfr` | `/gk-insights/ai-adoption-connect-jira-bamboohr#configure-change-failure-rate-cfr` | dora-metrics (line 71), manual-releases-api (line 165) |
| `/gk-insights/ai-adoption-connect-your-data#step-5--map-developer-identities` | `/gk-insights/ai-adoption-connect-your-data#map-developer-identities` | connect-your-data internal (stays, anchor rename only) |

### Playbook anchors → new pages

| Old link | New link | Used in |
|----------|----------|---------|
| `/gk-insights/ai-adoption-playbooks#set-tier-weights-for-your-orgs-maturity` | `/gk-insights/ai-adoption-playbook-tier-weights` | settings (lines 74, 224), getting-started (line 339, 492) |
| `/gk-insights/ai-adoption-playbooks#roll-out-ai-tooling-with-the-adoption-score` | `/gk-insights/ai-adoption-playbook-ai-rollout` | agentic-metrics (line 428), impact-cost-metrics (line 150), settings (line 225) |
| `/gk-insights/ai-adoption-playbooks#investigate-a-slow-cycle-time` | `/gk-insights/ai-adoption-playbook-slow-cycle-time` | getting-started (lines 343, 420) |
| `/gk-insights/ai-adoption-playbooks#interpret-a-high-cfr-week` | `/gk-insights/ai-adoption-playbook-high-cfr` | getting-started (lines 345, 421) |

### Links that stay unchanged

All metric-to-metric cross-references (e.g., `/gk-insights/ai-adoption-flow-metrics#cycle-time`) and metric-to-settings references (e.g., `/gk-insights/ai-adoption-settings#maturity-factor`) are unaffected — those pages are not being split.

---

## Hub Page Update

The hub page (`expo-ai-adoption.md`) summary table needs to expand to reflect the new page structure. Currently lists 10 pages; after breakout it should either:

- **Option A:** Keep the current 10-row structure (each row covers a category, linking to the overview/index page for that category). Child pages are discoverable through the side nav and the overview pages.
- **Option B:** Expand to show all 24 pages grouped by category.

**Recommendation:** Option A. The hub page is already clean and concise. Let the side nav handle discoverability of child pages.

---

## Frontmatter Scheme

### New fields to add

```yaml
nav_category: getting-started    # Top-level nav group
nav_order: 10                    # Sort order within category (use 10s)
nav_label: Overview              # Display name in nav (falls back to title)
```

### Category definitions

| Slug | Display label | Sort order | Category overview page |
|------|--------------|------------|----------------------|
| `getting-started` | Getting Started | 1 | `ai-adoption-getting-started` |
| `connect-your-data` | Connect Your Data | 2 | `ai-adoption-connect-your-data` |
| `metrics` | Metrics | 3 | (none — flat list) |
| `playbooks` | Playbooks | 4 | `ai-adoption-playbooks` |
| `admin` | Admin & API | 5 | (none — flat list) |

### Plugin changes needed

The `single-gki.php` template auto-nav query currently sorts alphabetically by title. It needs to change to:

1. Read `nav_category` and `nav_order` from post meta (mapped from frontmatter during Markdown → WordPress import).
2. Group posts by `nav_category`.
3. Sort categories by hardcoded order (or a category-order lookup).
4. Sort posts within each category by `nav_order`.
5. Render as collapsible groups with the category label as the group header.

The hub page (`expo-ai-adoption.md`) sits above all categories as the section entry point. It can either be outside any category or be its own single-page category at nav_order 0.

---

## Issues to Resolve During Breakout

### Missing screenshots (add before or during breakout)

- [ ] Jira connection modal → Advanced → "Customer bug field ID" field
- [ ] Settings → Developers identity merge / exclude flow
- [ ] DORA & Quality page has zero screenshots (add at least one)
- [ ] AI Impact & Cost page has zero screenshots (add at least one)

### Screenshot review flags

- [ ] `claude-code-codex-setup.png` shows dev collector hostname (`otel-devex-dev.gitkraken.com`) — needs retake against production

### Content fixes

- [ ] Agentic Metrics: remove GitHub Copilot and Devin from `integrations` frontmatter, or add body coverage explaining their role (or non-role) in scoring
- [ ] Impact & Cost Metrics: replace internal ticket reference (NG-152) with user-facing description
- [ ] Playbooks: update last-modified from June 2026 to current date
- [ ] Connect Your Data: deduplicate CFR metric definition — keep config steps, replace "What is CFR?" paragraph with a link to the DORA metrics page
- [ ] Connect Your Data: deduplicate "Set your benchmarks" — keep the checklist, link to Settings for full reference

### Content overlaps to clean up

- [ ] Admin setup checklists: Getting Started → For Admins has a condensed version; Connect Your Data has the full walkthrough. After breakout, the For Admins page should link to the Connect Your Data pages instead of duplicating checklist items.
- [ ] Maturity Factor: appears in For Admins, For Engineering Leaders, Agentic Metrics, and Settings. Canonical definition stays in Agentic Metrics. Others should cross-link, not duplicate.
- [ ] Tier definitions: canonical reference stays in Agentic Metrics (AI Tier section). Getting Started overview ("How to think about developer scores") is the introductory version — keep it lighter and link to the full reference.

---

## Execution Order

> **Do not execute until visual design discussion is complete.**

When ready:

1. Create all new page files with frontmatter (including `nav_category`, `nav_order`, `nav_label`).
2. Move content from source pages to new pages per the content mapping above.
3. Rework source pages into overview/index pages with links to child pages.
4. Execute link rewrites across all pages per the rewrite table.
5. Verify image references on every new page.
6. Add `nav_category`, `nav_order`, `nav_label` to all existing pages that aren't being split.
7. Update the plugin (`single-gki.php`) to read frontmatter nav fields and render grouped nav.
8. Verify total image count across all pages equals original total (10 unique images).
9. Verify no broken cross-references (grep for all `/gk-insights/` links, confirm each target slug exists).
