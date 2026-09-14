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
| `build-plugin.py` | The only supported way to build the plugin zip |

## Documentation

| Document | Read it when |
|---|---|
| [`CLAUDE.md`](CLAUDE.md) | Standing rules, site structure, design tokens, known traps. Start here. |
| [`LOCAL-DEV.md`](LOCAL-DEV.md) | Setting up or using the local test environment |
| [`PROJECT-STATUS.md`](PROJECT-STATUS.md) | Architecture, phase history, content map |
| [`AUTH-GATE-PLAN.md`](AUTH-GATE-PLAN.md) | OAuth + entitlement gate design and backend requirements |
| [`PHASE-4-HANDOFF.md`](PHASE-4-HANDOFF.md) | UI/UX punch list |

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
3. Build the zip: `python build-plugin.py` from the repo root. **Nothing else** —
   hand-rolled zip commands have taken the site down twice.
4. Upload to WP Admin → Plugins.

## Content changes

Markdown in `gk-insights-expo/` syncs to WordPress via Git It Write. Custom
fields must be nested under `custom_fields:` in the YAML frontmatter or they are
not stored as post meta. Git It Write does not delete WordPress posts when
source files are removed — those must be deleted manually in WP Admin.
