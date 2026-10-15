# gk-insights-docs-expo

The **GitKraken Insights AI Adoption Help Center** — ~50 Markdown content pages
and the custom WordPress plugin that renders them at
`help.gitkraken.com/insights-expo/`.

## What's in here

| Path | What it is |
|---|---|
| `gk-insights-expo/` | ~50 Markdown pages, synced to WordPress by Git It Write |
| `gki-docs-helper/` | The WordPress plugin: template, CSS, JS, Elementor overrides, auth gate |
| `local-dev/` | Dockerized WordPress for testing plugin changes off production |
| `_images/` | Screenshots referenced by the Markdown pages |
| `prototypes/` | `pass-c-mockup.html` — the Pass C design mockup (also the "Insights Docs Pass C" Artifact) |
| `build-plugin.py` | The only supported way to build the plugin zip |
| `archive-and-build.ps1` | Archives the current zip by version into `plugin-archive/`, then runs `build-plugin.py` |
| `plugin-archive/` | Rollback zips, one per version, with an `ARCHIVE.md` ledger |

## Branches

| Branch | Plugin | |
|---|---|---|
| `main` | 1.17.3 | Current. Pass C merged (PR #2); 1.17.2 is live on production, 1.17.3 adds the auth-gate lockdown. `v1.14.2-stable` is the pre-redesign rollback tag. |
| `pass-c-structural` | 1.17.2 | Merged into `main`; kept for history. |
| `Taste-design-expo` | 1.15.x | Superseded exploration branch, kept for reference. |

## Documentation

| Document | Read it when |
|---|---|
| [`CLAUDE.md`](CLAUDE.md) | Standing rules, site structure, design rules, content transforms, known traps. Start here. |
| [`LOCAL-DEV.md`](LOCAL-DEV.md) | Setting up or using the local test environment; testing the auth gate states |
| [`PROJECT-STATUS.md`](PROJECT-STATUS.md) | Architecture, phase history, content map, known issues, open items |
| [`AUTH-GATE-PLAN.md`](AUTH-GATE-PLAN.md) | Gate design, the 2026-09-23 leak audit, what the plugin now closes and the manual hardening items that remain |
| [`MERGE-PLAN.md`](MERGE-PLAN.md) | Inventory for the eventual fold-back from `insights-expo` into the main `/gk-insights/` category — read before planning that merge |
| [`BREAKOUT-PLAN.md`](BREAKOUT-PLAN.md) | Historical: the Phase 3 content breakout plan |
| [`PHASE-4-HANDOFF.md`](PHASE-4-HANDOFF.md) | Historical: the Phase 4 punch list (closed) |

## Quick start — testing a plugin change

Never test plugin changes against the live site. Two site-down incidents have
come from that. Use the local environment instead:

```powershell
cd local-dev
docker compose up -d
# http://localhost:8420/insights-expo/expo-ai-adoption-home/
```

`gki-docs-helper/` is bind-mounted into the container, so this repo directory is
the running plugin — edit CSS or PHP and refresh. Watch for errors with:

```powershell
docker compose exec wordpress tail -f /var/www/html/wp-content/debug.log
```

First-time setup requires a production export. See [`LOCAL-DEV.md`](LOCAL-DEV.md).

## Shipping a plugin change

1. Verify locally, with a clean `debug.log`.
2. Bump the version in **both** the header comment and the `GKI_DOCS_VERSION`
   constant in `gki-docs-helper/gki-docs-helper.php`.
3. Archive and build: `.\archive-and-build.ps1` from the repo root. It copies
   the current zip to `plugin-archive/` under its own version, then runs
   `python build-plugin.py`. Never build the zip any other way — hand-rolled
   zip commands have taken the site down twice.
4. Commit (the zip is tracked), upload to WP Admin → Plugins.

To roll back: upload `plugin-archive/gki-docs-helper-v<version>.zip` the same
way. `.\archive-and-build.ps1 -FromRef <ref>` archives a committed build.

## Content changes

Markdown in `gk-insights-expo/` syncs to WordPress via Git It Write. Custom
fields must be nested under `custom_fields:` in the YAML frontmatter or they are
not stored as post meta. Git It Write does not delete WordPress posts when
source files are removed — those must be deleted manually in WP Admin.
