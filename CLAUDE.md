# CLAUDE.md

## Project Overview

This repo contains the **GitKraken Insights AI Adoption Help Center** — 29 Markdown content pages and a custom WordPress plugin (GKI Docs Helper) that provides the template, CSS, JS, and Elementor overrides for the Help Center at `help.gitkraken.com/insights-expo/`.

## Current State (September 2026)

Phases 1–3 are complete. The content breakout, frontmatter, plugin template, and visual design foundations are live. **Phase 4 (UI/UX refinement)** is the active workstream — see `PHASE-4-HANDOFF.md` for the full punch list.

### What's Live
- 29 Markdown files in `gk-insights-expo/` synced to WordPress via Git It Write
- GKI Docs Helper plugin v1.7.0 installed on WordPress
- 3-column layout: left nav, center content, right TOC
- Card grid index pages with tinted icons (auto-rendered from frontmatter)
- Hierarchical left nav grouped by section
- Card filter/search on index pages
- Image lightbox, progress bar, back-to-top, smooth scroll

### Plugin Location
```
gki-docs-helper/
  gki-docs-helper.php      # Main plugin file (v1.7.0)
  css/gki-docs.css          # All styles
  js/gki-docs.js            # Interactive features
  templates/single-gki.php  # 3-column page template
  gki-docs-helper.zip       # Installable archive — REBUILD AFTER EVERY CHANGE
```

### Content Location
```
gk-insights-expo/           # 29 Markdown files → WP posts via Git It Write
```

---

## Standing Rules

These rules apply to ALL sessions working on this project.

### Content Integrity
- Do not invent product behavior or expand metric definitions.
- Do not add new images. Do not remove or rename existing images.
- Preserve all image paths and alt text exactly.
- Move content; do not rewrite unless necessary for clarity.
- If information does not exist in source, do not create it.
- If behavior is unclear, leave unchanged and flag for human review.

### Technical Writing Standards
- Active voice preferred ("GitKraken calculates…" not "Is calculated by…").
- Clear, task-based headings. No generic "Overview" / "Details".
- One topic per section. Cross-link instead of duplicating.
- Bulleted lists for non-sequential info; numbered lists for steps.
- Headings semantically ordered (H2 → H3). No ALL CAPS.
- No keyword-stuffed headings. Metric names must stay consistent.

### Design Rules
- **Purple (#7900C9) is accent only** — links, active borders, step circles, hover effects. Never a main/heading color.
- Heading text: #1C1C1C. Body text: #414141.
- Cards drive navigation on index pages.
- All Elementor overrides require `body.gki-docs-page` prefix + `!important`.

### Git It Write Requirements
- Custom fields MUST be nested under `custom_fields:` in YAML frontmatter to be stored as WordPress post meta. Top-level YAML keys are NOT stored.
- WordPress URLs: `help.gitkraken.com/insights-expo/{slug}` — slug comes from filename.
- Internal links use `/insights-expo/expo-ai-adoption-*` format.
- Git It Write does NOT auto-delete WP posts when source files are removed. Old posts must be manually deleted from WP Admin.

### Plugin Workflow
- After ANY plugin file change: bump version in both the header comment AND `GKI_DOCS_VERSION` constant, then rebuild the zip.
- Zip rebuild: `cd gki-docs-helper && zip -r /tmp/gki-new.zip . -x '*.zip' -x '.*' && cp /tmp/gki-new.zip ./gki-docs-helper.zip`
- Upload zip to WP Admin → Plugins after each version bump.

---

## Site Structure

### Nav Sections (hardcoded order in PHP)
1. **Home** — `expo-ai-adoption-home.md` (main-index, card grid of section indexes)
2. **Getting Started** — 6 pages (index + first-dashboard, for-executives, for-engineering-leaders, for-team-leads, for-admins)
3. **Connect Your Data** — 7 pages (index + github, bitbucket, azure-devops, gitlab, ai-tools, jira-bamboohr)
4. **Metrics** — 6 pages (index + dora, flow, output, agentic, impact-cost)
5. **Playbooks** — 5 pages (index + tier-weights, ai-rollout, slow-cycle-time, high-cfr)
6. **Admin & API** — 3 pages (index + settings, manual-releases-api)

Plus 1 hidden redirect: `expo-ai-adoption.md` (nav_category: hidden)

### Page Types
- `main-index` — the home page; card grid of section indexes inserted after intro paragraph
- `index` — section landing; card grid of child content pages rendered after all content
- `content` — regular article with TOC sidebar

---

## Key Technical Context

### Parsedown v1 Limitation
Git It Write uses Parsedown v1, which does NOT parse Markdown inside HTML block elements. Content that mixes HTML and Markdown must account for this.

### Elementor Override Table

| Elementor Rule | Our Override |
|---|---|
| `.elementor-kit-5 h1 { color: rgb(241,241,241) }` | `.gki-docs-page .gki-page h1 { color: #1C1C1C !important }` |
| `.elementor-kit-5 a { color: rgb(248,171,255) }` | `body.gki-docs-page .gki-nav-item a:link { color: #414141 !important }` |
| Kit dark body background | `body.gki-docs-page { background: #FFFFFF !important }` |
| Elementor Theme Builder template | Plugin hooks at priority 9999 + `template_id` filter returns 0 |

### CSS Custom Properties (`:root`)

| Token | Value | Usage |
|---|---|---|
| `--gki-purple` | `#7900C9` | Accent only |
| `--gki-text` | `#1C1C1C` | Headings |
| `--gki-text-secondary` | `#414141` | Body text |
| `--gki-text-muted` | `#636568` | Captions |
| `--gki-bg` | `#FFFFFF` | Page background |
| `--gki-bg-subtle` | `#F5F6F8` | Card/hover backgrounds |
| `--gki-border-light` | `#E0E1E3` | Borders |
| `--gki-font` | `Inter` + system stack | All text |
