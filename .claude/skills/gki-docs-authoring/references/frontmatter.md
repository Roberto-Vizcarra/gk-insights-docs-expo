# Frontmatter reference

Every page opens with a YAML block. Git It Write maps `title` to the post title
and `taxonomy.category` to the WP category, and stores each key under
`custom_fields:` as post meta. Other top-level keys (`description`, `product`,
`content_type`, `audience`, …) are kept for consistency and tooling, but the
plugin doesn't read them.

```yaml
---
title: "Lead Time for Changes"
description: "How GitKraken Insights calculates and displays Lead Time for Changes."
product: GitKraken Insights
content_type: reference          # reference | how-to | … (match siblings)
audience: all                    # all | admin | … (match siblings)
plan_required: GitKraken Insights
status: GA                       # optional
integrations: [GitHub, GitLab]   # optional, flow list
taxonomy:
    category: insights-expo      # REQUIRED, exactly this
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for Lead Time for Changes"
    card_icon: shield-check
    nav_category: metrics
    nav_label: "Lead Time for Changes"
    nav_order: 42
    nav_parent: expo-ai-adoption-dora-metrics
    page_type: content
---
```

Indent with four spaces (house style). Keys under `custom_fields` are listed
alphabetically on existing pages. Quote any value that contains `:`, `#`, `&`
at the start, or leading/trailing spaces.

## The `custom_fields` the plugin reads

| Field | Required | Read by | Meaning |
|---|---|---|---|
| `page_type` | yes | template, both transforms, nav | `main-index` (Home only, exactly one), `index` (a section or sub-section landing page with a card grid), `content` (everything else). Missing means `content`. |
| `nav_category` | yes | nav, breadcrumbs, card fallback, metric pipeline | Section key: `getting-started`, `connect-your-data`, `metrics`, `playbooks`, `admin`, plus `home` (Home) and `hidden` (out of the nav). **`metrics` + `content` routes the page through the metric pipeline**; every other combination goes through the general pipeline. |
| `nav_order` | yes | nav, card grids, metric map | Integer. Sorts siblings ascending. Ties have no defined order. Top-level sections are ordered by a fixed list in PHP, not by this field, but their Home cards are sorted by it. |
| `nav_label` | yes | H1, nav, breadcrumbs, card title, search index, duplicate-H2 check | What readers see. Falls back to `title`. |
| `nav_parent` | for nested pages | nav nesting, breadcrumbs, card grid, metric map | Parent's **slug** (file name without `.md`). The parent must be `page_type: index` in the same `nav_category`. Omit it for pages that hang directly off a section index. |
| `card_icon` | yes (non-hidden) | card grid | A [Tabler icon](https://tabler.io/icons) name **without** `ti-` (the plugin loads Tabler webfont 3.6.0). Children of a metric family reuse the family's icon. |
| `card_color` | yes (non-hidden) | card grid | `purple`, `blue`, `green`, `amber` or `red`. The colour is per section (see below). Any other value renders an unstyled icon chip. |
| `card_description` | yes (non-hidden) | card subtitle, command-palette search index | One short line with no final period. It's searched, so name the things a reader would type. |

`hidden` pages still need `page_type`, `nav_order` (99 by convention) and
`nav_label`. Set the three `card_*` fields to `""`.

## How the hierarchy is built

- **Nav** (`gki_docs_get_nav_structure()`): Home first, then one collapsible
  group per `nav_category`, in the fixed order getting-started →
  connect-your-data → metrics → playbooks → admin. Any other category comes
  after those. The group heading is the section's un-parented `index` page.
  Children are the section's un-parented `content` pages, plus anything whose
  `nav_parent` is the section index, all sorted by `nav_order`. A child that has
  its own children becomes a collapsible sub-group (the metric families).
- **Breadcrumbs**: Home → each ancestor up the `nav_parent` chain → this page.
  A page with no `nav_parent` gets its section index as the middle crumb.
- **Card grid on an `index` page**: pages whose `nav_parent` is this index. If
  there are none, it uses the `content` pages in the same `nav_category` that
  have no `nav_parent`. Home's grid is every un-parented `index` page. (Home
  currently shows the Pass C hero, role paths and metric map instead.)
- **Home metric map** (`gki_passc_metric_map()`): every `metrics` `index` page
  that has a `nav_parent` is a family. Every `metrics` `content` page is a chip
  under its `nav_parent` family. **A metric page without `nav_parent` is left
  off the map.**

## Current map (November 2026)

Check this against the files before you rely on it. Running
`check_pages.py` confirms the hierarchy is consistent.

| Section (`nav_category`) | Index page (`nav_order`) | Colour | Children |
|---|---|---|---|
| home | `expo-ai-adoption-home` (0, `main-index`) | purple | — |
| getting-started | `expo-ai-adoption-getting-started` (10) | purple | first-dashboard 10, for-executives 20, for-engineering-leaders 30, for-team-leads 40, for-admins 50, developers 60, repositories 65, ai-sessions 70, ai-tools-comparison 75, surveys 80, insights-for-developers 90. Each has its own `card_icon`. |
| connect-your-data | `expo-ai-adoption-connect-your-data` (20) | blue | connect-github 10, -bitbucket 20, -azure-devops 30, -gitlab 40, -ai-tools 50, -otel 55, -jira-bamboohr 60, -linear-youtrack 65, -clickup 67 |
| metrics | `expo-ai-adoption-metrics` (30) | green | five family sub-indexes, each `nav_parent: expo-ai-adoption-metrics` (below) |
| playbooks | `expo-ai-adoption-playbooks` (40) | amber | playbook-tier-weights 10, -ai-rollout 20, -slow-cycle-time 30, -high-cfr 40 |
| admin | `expo-ai-adoption-admin` (50) | red | settings 10, manual-releases-api 20, security-permissions 30 |
| hidden | — | — | `expo-ai-adoption` (redirect), `expo-ai-adoption-manual-critical-defects-api` (draft) |

Metric families (`page_type: index`, `nav_category: metrics`,
`nav_parent: expo-ai-adoption-metrics`). Each child metric page uses the
family's icon and `nav_order` = family order + 1, 2, 3 …

| Family sub-index | `nav_order` | `card_icon` | Metric pages (slug tail → order) |
|---|---|---|---|
| `expo-ai-adoption-agentic-metrics` — Adoption & Agentic | 10 | `user-check` | agentic-adoption-score 11, agentic-autonomy-score 12, agentic-ai-tier 13, agentic-maturity-factor 14, agentic-cursor-boost 15 |
| `expo-ai-adoption-output-metrics` — Output & Throughput | 20 | `package` | output-output-score 21, output-throughput 22, output-direct-commits 23, output-effort-score 24 |
| `expo-ai-adoption-flow-metrics` — Flow & Cycle Time | 30 | `clock` | flow-cycle-time 31, flow-review-cycles 32, flow-first-pass-rate 33, flow-wip 34 |
| `expo-ai-adoption-dora-metrics` — DORA & Quality | 40 | `shield-check` | dora-deployment-frequency 41, dora-lead-time 42, dora-change-failure-rate 43, dora-mttr 44 |
| `expo-ai-adoption-impact-cost-metrics` — AI Impact & Cost | 50 | `currency-dollar` | impact-cost-productivity-uplift 51, impact-cost-ai-assisted-percentage 52, impact-cost-capex-opex 53, impact-cost-spend-by-tier 54 |

## Naming

- File name: `expo-ai-adoption-<section-or-family>-<topic>.md`, lowercase and
  hyphenated. Metric pages carry their family prefix (`dora-`, `flow-`,
  `output-`, `agentic-`, `impact-cost-`). Two things match on the slug tail:
  the instrument registry (`gki_passc_instruments()`) and Home's role paths
  (`for-executives` etc.). So don't rename those pages casually.
- The settings mini-spec-panel transform only runs on a slug ending in
  `-settings`.
