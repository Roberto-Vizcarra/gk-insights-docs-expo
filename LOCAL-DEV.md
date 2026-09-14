# Local Development Environment

A Dockerized WordPress that mirrors production, so plugin changes get tested
before they reach `help.gitkraken.com`.

Everything lives in `local-dev/`. Nothing in this setup can write to production.

- **Site:** http://localhost:8420
- **Docs home:** http://localhost:8420/insights-expo/expo-ai-adoption-home/
- **Admin:** http://localhost:8420/wp-admin — `local` / `local`
- **Adminer:** http://localhost:8421 (opt-in, see below)

---

## Why this exists

Two site-down fatals shipped from testing against the live site. A local copy
catches them for free: `WP_DEBUG_LOG` turns a white screen into a stack trace,
and the blast radius is a container you can delete.

The catch is fidelity. A vanilla WordPress will **not** reproduce this project's
bugs, because nearly all of them come from Elementor kit specificity
(`.elementor-kit-5`), the Elementor Theme Builder template, and the Git It Write
post structure. So this setup imports the production database rather than
starting clean.

### Verified against production

Confirmed on the first successful import (2026-09-14):

| Fact | Value | Why it matters |
|---|---|---|
| Elementor active kit ID | `5` | `.elementor-kit-5` overrides apply locally exactly as in prod |
| Permalink structure | `/%category%/%postname%/` | Where the `/insights-expo/` prefix comes from |
| Docs post type | `post` (category `insights-expo`) | Not a custom post type |
| Table prefix | `wp_` | |
| Posts collation | `utf8mb4_unicode_520_ci` | 4-byte safe; emoji and curly quotes survive |
| Posts in database | 3,622 published (whole help center) | 50 of them are `insights-expo` |
| Themes | `hello-elementor` + `hello-elementor-child` | |
| Plugins | 27 | Includes Elementor, RankMath, SearchWP, Git It Write |

---

## First-time setup

### Prerequisites

- Docker Desktop, running
- Windows PowerShell 5.1 or PowerShell 7
- ~5 GB free disk

### 1. Export from production with WP Migrate Lite

Install **WP Migrate Lite** on prod (free, wp.org). It is read-only against the
live site — it only reads and writes a download.

Go to **Tools → WP Migrate → Migrate → Export file.**

Fill in the Find & Replace section. It pre-fills the Find column:

| Find (pre-filled) | Replace with |
|---|---|
| `https://help.gitkraken.com` | `http://localhost:8420` |
| prod document root, e.g. `/home/.../public_html` | `/var/www/html` |

The path row matters. Elementor stores absolute filesystem paths in postmeta,
and leaving prod's path in place produces CSS-generation failures that look
like styling bugs.

> If you get the port wrong here, it is recoverable — `setup.ps1` detects a
> localhost URL on the wrong port and rewrites it.

Under **Advanced Options**: exclude post revisions, spam comments, and
transients. Leave every *table* selected. Excluding `wp_options` defeats the
whole exercise — that is where the Elementor kit pointer, the permalink
structure, and all plugin settings live.

For file inclusion:

| Option | Take it? | Why |
|---|---|---|
| Media Uploads | Yes — limit to the last month or two | Full history is gigabytes of other products' screenshots |
| Themes | **Yes** | Without the real active theme, nothing you see is meaningful |
| Plugins | **Yes** | Elementor at the exact prod version is the point |
| Must-use plugins | Optional | Host-specific ones fatal outside their hosting environment |
| Others | **No** | Caches, backups, logs — pure bloat |

Check sizes first at **Tools → Site Health → Info → Directories and Sizes.**

> Never copy `wp-config.php` across. The container generates its own, with the
> debug and localhost constants already set.

### 2. Run setup

```powershell
cd C:\Users\<you>\Documents\Git\gk-insights-docs-expo\local-dev
.\setup.ps1 -Archive ~\Downloads\<export>.zip -Fresh
```

That is the whole install — roughly 5 minutes, most of it the database import.

If PowerShell blocks the script ("running scripts is disabled"), bypass for
that one invocation without changing any system setting:

```powershell
powershell -ExecutionPolicy Bypass -File .\setup.ps1 -Archive ~\Downloads\<export>.zip -Fresh
```

### 3. Read the verification output

The last section is the part to actually read:

- **Elementor active kit ID** must be `5`. Every override in the plugin is keyed
  to that number. If it differs, your CSS will not apply locally and the
  environment is lying to you — the export was partial.
- **Permalink structure** should be `/%category%/%postname%/`.
- **`.gki-layout` present** confirms the plugin template actually took over.
- **PHP fatals in debug.log** should be zero.

---

## What setup.ps1 does

Nine phases, each reporting `[ok]` / `[warn]` / `[FAIL]`.

| Phase | What it does |
|---|---|
| 1 Preflight | Docker daemon, Compose v2, port free (names the process holding it), `.env` created, disk space |
| 2 Stack up | `docker compose up`, waits for MariaDB health and an HTTP response |
| 3 Unpack | Expands the zip, locates the SQL dump anywhere inside it |
| 4 Import | Loads the dump with charset normalization, auto-detects table prefix, patches `wp-config.php`, asserts collation |
| 5 Files | Uploads to the bind mount; themes and plugins via `docker compose cp` |
| 6 Rewrite | Prod URL → localhost (auto-detected from the dump), optional path rewrite |
| 7 Clean | Deletes WP Migrate, deactivates plugins that must not run locally, forces the auth gate off |
| 8 Activate | GKI Docs Helper, local admin, rewrite flush, Elementor CSS flush |
| 9 Verify | Kit ID, page count, real permalink via `get_permalink()`, HTTP fetch, template check, fatal scan |

### Flags

| Flag | Effect |
|---|---|
| `-Fresh` | Destroy the local database first. Use for every full re-import. |
| `-SkipImport` | Reuse the loaded database. For re-running after a late-stage failure. |
| `-RebuildConfig` | Regenerate `wp-config.php` after changing `WORDPRESS_*` env. Database untouched. |
| `-ProdPath /home/.../public_html` | Rewrite filesystem paths, if you skipped that at export. |
| `-KeepMuPlugins` | Stage must-use plugins (skipped by default). |
| `-SkipFiles` | Database only; ignore themes/plugins/uploads in the archive. |
| `-AdminUser` / `-AdminPassword` | Defaults are `local` / `local`. |

`-Fresh` and `-SkipImport` are mutually exclusive and the script refuses both.

A bare `.sql` or `.sql.gz` works as `-Archive` too — it just skips file staging.

---

## Daily loop

```powershell
cd local-dev
docker compose up -d     # start (instant after the first time)
docker compose down      # stop, keeps the database
docker compose down -v   # destroy the database too
```

`gki-docs-helper/` is bind-mounted into the container, so the repo directory
*is* the running plugin. Edit CSS, PHP or JS and refresh — no zip rebuild while
iterating. Two caveats:

- **Bump `GKI_DOCS_VERSION`** when testing CSS or JS, or the browser serves the
  cached file. The version is the cache buster locally exactly as in prod.
- **Hard refresh** (`Ctrl+Shift+R`) after CSS changes anyway.

### Watching for errors

```powershell
docker compose exec wordpress tail -f /var/www/html/wp-content/debug.log
```

This is the point of the exercise. Read it before every zip build.

Errors are logged, never displayed. Printing notices to the page causes
"headers already sent" and breaks redirects site-wide — see Environment Traps.

### Poking at the database

```powershell
docker compose --profile tools up -d adminer        # http://localhost:8421
docker compose run --rm wpcli plugin list
docker compose run --rm wpcli option get permalink_structure
docker compose run --rm wpcli post list --category_name=insights-expo --fields=ID,post_name
```

Adminer credentials: server `db`, user `wordpress`, password `wordpress`,
database `wordpress`.

---

## What this does NOT test

**The OAuth auth gate.** `gitkraken.dev` will not redirect back to
`http://localhost:8420`, so the sign-in flow cannot complete. `setup.ps1`
deactivates OpenID Connect Generic and forces `gki_auth_gate_enabled` off.

You can still test the gate *template* by forcing the variant in
`includes/gki-auth.php`. To exercise the real flow you need either a staging
instance with a registered redirect URI, or a tunnel (`cloudflared`) on a stable
hostname that the backend team whitelists. That request belongs in the same
conversation as asking for a real staging environment.

**Git It Write sync.** Deactivated locally, since it clones from the remote
repo. Posts arrive via the database import instead. To test the sync itself,
reactivate it and point it at a throwaway branch.

**CDN, caching, production traffic.** Not reproduced. A bug that only appears
behind Cloudflare will not show up here.

**Zip packaging.** See below — this is important.

---

## Before shipping to prod

The local site proves the plugin *renders*. It does not prove the *zip* is
valid — and both historical outages were zip-packaging failures, not code
failures. The release path is unchanged:

1. Verify locally, with a clean `debug.log`.
2. Bump the version in the header comment **and** `GKI_DOCS_VERSION`.
3. `python build-plugin.py` from the repo root. Nothing else.
4. Upload the zip to WP Admin → Plugins.

---

## Environment traps (measured, do not re-introduce)

These cost real time to diagnose. They are in the config with comments; this is
the explanation.

### Never define `WP_DEBUG` in `WORDPRESS_CONFIG_EXTRA`

The official `wordpress` image already writes its own `define('WP_DEBUG', ...)`
into `wp-config.php`. Defining it again raises `Constant WP_DEBUG already
defined`, that warning is output sent before headers, and every redirect,
canonical URL and template load on the site breaks. The symptom is a 200
response of about 0.5 KB with no content.

Set `WORDPRESS_DEBUG: 1` as an environment variable instead. `WP_DEBUG_LOG`,
`WP_DEBUG_DISPLAY` and `SCRIPT_DEBUG` are *not* pre-defined and are safe in
`CONFIG_EXTRA`.

### Never turn on `display_errors`

Same failure, different source. Any notice from any plugin printed before a
`header()` call produces "headers already sent." Both `php.ini` and
`wp-config` keep display off and logging on.

### The import must be forced to `utf8mb4`

MariaDB still defaults to `latin1`, and WP Migrate dumps carry `utf8mb3`
declarations. Without normalization, 4-byte characters — emoji, curly quotes —
become `?`. That corrupts post content silently, and collides on unique keys
(SearchWP's token table is where it surfaces first, as
`Duplicate entry '????' for key 'token'`).

`setup.ps1` rewrites charset declarations in-stream. The rewrite is anchored to
the places a charset is actually *declared* (`DEFAULT CHARSET=`,
`CHARACTER SET `, `SET NAMES `, `character_set_client = `, `COLLATE`), so a docs
page that discusses `utf8` in its prose is never touched. A blind
`s/utf8/utf8mb4/g` both rewrites content and turns `utf8mb3` into `utf8mb4mb3`.

### Never set the permalink structure

Production uses `/%category%/%postname%/`, and the `/insights-expo/` prefix
depends on it. Setting a structure "helpfully" silently changes every URL on the
site. `setup.ps1` only flushes rewrite rules and reports what prod's structure
is.

### `wp-config.php` is only generated once

The image writes it only when absent, so changing `WORDPRESS_*` env vars on an
existing volume does nothing. Use `-RebuildConfig` (deletes and regenerates it;
database untouched) or `-Fresh`.

### `docker compose` writes progress to stderr

In Windows PowerShell 5.1, merging that with `2>&1` under
`$ErrorActionPreference = 'Stop'` throws `NativeCommandError` on a completely
successful command. The helper functions discard stderr and judge success by
exit code.

### `grep -c` exits non-zero on zero matches

It prints `0` *and* exits 1, so `grep -c ... || echo 0` emits `"0\n0"`. Branch
on the file existing instead.

---

## Troubleshooting

**Port already in use.** The preflight names the process holding it. Change
`WP_PORT` in `.env` to anything free — the URL rewrite handles whatever you
pick. Defaults are 8420/8421 rather than 8080/8081, which tend to be taken on a
working dev machine.

**Site shows the WordPress install screen after import.** The table prefix in
`wp-config.php` does not match the imported tables. `setup.ps1` detects and
fixes this — if you still see it, the dump is missing its `*_options` table.

**Import fails partway.** The database is left partial. Re-run with `-Fresh`;
re-running without it imports on top of existing rows and produces duplicate-key
errors that mask the original problem.

**Page loads but `.gki-layout` is missing.** Something printed output before
headers. Check `debug.log`, and confirm nothing re-defines `WP_DEBUG`.

**Layout unstyled or wrong.** Check the reported Elementor kit ID against `5`,
and confirm Elementor and `hello-elementor` came across:
`docker compose run --rm wpcli plugin list`.

**White screen.** Host mu-plugins are the usual cause if you passed
`-KeepMuPlugins`:
`docker compose exec wordpress rm -rf /var/www/html/wp-content/mu-plugins`

**CSS changes not appearing.** Bump `GKI_DOCS_VERSION` and hard-refresh.

**Images 404.** Expected if you limited the uploads export by date. Harmless for
layout work — the CSS sizes those containers regardless.

---

## Files

```
local-dev/
  setup.ps1            # WP Migrate export -> verified running local site
  docker-compose.yml   # mariadb + wordpress + wp-cli + adminer
  .env.example         # copied to .env on first run
  .env                 # local config (gitignored)
  php.ini              # upload/memory limits; errors logged, not displayed
  import.ps1           # deprecated, superseded by setup.ps1
  db-dump/             # .sql files land here (gitignored)
  uploads/             # prod wp-content/uploads (gitignored)
  .staging/            # transient extract dir, removed on success
```

Services: `db` (MariaDB 10.11), `wordpress` (6.8 / PHP 8.2 / Apache), plus
`wpcli` and `adminer` behind Compose profiles so they do not start with `up`.
