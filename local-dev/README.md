# local-dev

Dockerized WordPress for testing the GKI Docs Helper plugin off production.

**Full documentation: [`../LOCAL-DEV.md`](../LOCAL-DEV.md)** — read that first.

## Already set up

```powershell
docker compose up -d     # http://localhost:8420
docker compose down      # stop, keeps the database
```

## Starting from scratch

```powershell
.\setup.ps1 -Archive ~\Downloads\<wp-migrate-export>.zip -Fresh
```

## Do not

- Define `WP_DEBUG` in `WORDPRESS_CONFIG_EXTRA` — the image already defines it,
  and the duplicate-define warning breaks every page on the site.
- Turn on `display_errors` — same failure, different source.
- Set the permalink structure — prod's `/%category%/%postname%/` is what puts
  `/insights-expo/` in the URL.

Each of these is commented in place. The reasoning is in `../LOCAL-DEV.md`.
