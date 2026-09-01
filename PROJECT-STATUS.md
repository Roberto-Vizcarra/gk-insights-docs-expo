# GKI Help Center Redesign — Project Status

**Last updated:** 2026-09-01
**Owner:** roberto.vizcarra@gitkraken.com
**Plugin version:** 1.7.0
**Live URL:** `help.gitkraken.com/insights-expo/expo-ai-adoption-home`

## Architecture

```
WordPress (help.gitkraken.com)
  ├── Elementor Pro Theme Builder (dark kit — fights us)
  ├── Git It Write plugin (syncs repo Markdown → WP posts)
  │     └── Category: insights-expo → gk-insights-expo/ directory
  │     └── YAML custom_fields: → stored as WP post meta
  └── GKI Docs Helper plugin v1.7.0 (our custom plugin)
        ├── template override (priority 9999)
        ├── Parsedown cleanup (the_content filter)
        ├── gki_docs_render_card_grid() — reusable card grid renderer
        ├── gki_docs_get_nav_structure() — frontmatter-based nav builder
        ├── gki_docs_get_child_pages() — child page query for index cards
        ├── CSS (brand tokens + Elementor overrides + table styles)
        └── JS (TOC, card filter, lightbox, back-to-top, progress bar)
```

## Phase History

### Phase 1 — Audit (Complete)
Audited the original monolithic Dashboard Management page. Identified 21 metrics across 5 families, plus config/settings content. Recommended breakout into standalone pages.

### Phase 2 — Plugin & Visual Design (Complete, v1.5.0)
Built GKI Docs Helper plugin: 3-column template, CSS design system (Inter, Major Third scale, brand tokens), Elementor overrides, card grid design, hierarchical nav, TOC, lightbox, progress bar. Exploration v3 defined the visual language: tinted icon cards, 3-wide grid, purple as accent only.

### Phase 3 — Content Breakout (Complete, v1.7.0)
Split content into 29 standalone pages. Key fixes along the way:
- Moved frontmatter plugin fields under `custom_fields:` (Git It Write requirement)
- Rewrote all internal links from `/gk-insights/` to `/insights-expo/`
- Created clean landing page (`expo-ai-adoption-home.md`) separate from Getting Started
- Converted old hub page to hidden redirect
- Removed static HTML tables from index pages (card grids auto-render)
- Fixed heading color CSS specificity (added `gki-page` class to template article)
- Fixed card filter (removed `display: flex !important` that blocked JS hide)
- Added table styling for Markdown tables
- Template splits main-index content at first `<hr>` to insert cards after intro
- Added `gki_docs_render_card_grid()` helper function

### Phase 4 — UI/UX Refinement (Next)
See `PHASE-4-HANDOFF.md` for the complete punch list.

## Content Map (29 files)

| File | Page Type | Nav Category | Nav Label |
|---|---|---|---|
| expo-ai-adoption-home.md | main-index | home | Home |
| expo-ai-adoption-getting-started.md | index | getting-started | Getting Started |
| expo-ai-adoption-first-dashboard.md | content | getting-started | First Dashboard |
| expo-ai-adoption-for-executives.md | content | getting-started | For Executives |
| expo-ai-adoption-for-engineering-leaders.md | content | getting-started | For Engineering Leaders |
| expo-ai-adoption-for-team-leads.md | content | getting-started | For Team Leads |
| expo-ai-adoption-for-admins.md | content | getting-started | For Admins |
| expo-ai-adoption-connect-your-data.md | index | connect-your-data | Connect Your Data |
| expo-ai-adoption-connect-github.md | content | connect-your-data | GitHub |
| expo-ai-adoption-connect-bitbucket.md | content | connect-your-data | Bitbucket |
| expo-ai-adoption-connect-azure-devops.md | content | connect-your-data | Azure DevOps |
| expo-ai-adoption-connect-gitlab.md | content | connect-your-data | GitLab |
| expo-ai-adoption-connect-ai-tools.md | content | connect-your-data | AI Coding Tools |
| expo-ai-adoption-connect-jira-bamboohr.md | content | connect-your-data | Jira & BambooHR |
| expo-ai-adoption-metrics.md | index | metrics | Metrics |
| expo-ai-adoption-dora-metrics.md | content | metrics | DORA & Quality |
| expo-ai-adoption-flow-metrics.md | content | metrics | Flow & Cycle Time |
| expo-ai-adoption-output-metrics.md | content | metrics | Output & Throughput |
| expo-ai-adoption-agentic-metrics.md | content | metrics | Adoption & Agentic |
| expo-ai-adoption-impact-cost-metrics.md | content | metrics | AI Impact & Cost |
| expo-ai-adoption-playbooks.md | index | playbooks | Playbooks |
| expo-ai-adoption-playbook-tier-weights.md | content | playbooks | Set Tier Weights |
| expo-ai-adoption-playbook-ai-rollout.md | content | playbooks | AI Rollout |
| expo-ai-adoption-playbook-slow-cycle-time.md | content | playbooks | Slow Cycle Time |
| expo-ai-adoption-playbook-high-cfr.md | content | playbooks | High CFR |
| expo-ai-adoption-admin.md | index | admin | Admin & API |
| expo-ai-adoption-settings.md | content | admin | Settings |
| expo-ai-adoption-manual-releases-api.md | content | admin | Releases API |
| expo-ai-adoption.md | content | hidden | (Redirect) |

## Known Issues

1. **Parsedown v1** doesn't parse Markdown inside HTML blocks — fundamental limitation.
2. **Elementor specificity is fragile** — theme updates can break overrides. After any theme update, verify heading colors, link colors, body background.
3. **No auto-update** — plugin zip must be manually uploaded after each version.
4. **Git It Write doesn't delete posts** — when source files are removed, WP posts persist and must be manually deleted.
5. **Tabler Icons CDN** — icons load from `cdn.jsdelivr.net`. If CDN is blocked by WAF/CSP, card icons will be invisible.

## Long-term Considerations

- Dark mode support (second set of token values)
- Auto-update mechanism (webhook or GitHub Actions → WP REST API)
- Replace Git It Write + Parsedown with a better Markdown pipeline
- Consider dedicated docs platform if WP/Elementor friction continues
