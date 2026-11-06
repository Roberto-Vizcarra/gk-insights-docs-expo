# GKI Help Center — Auth Gate Implementation Plan

> **Status (2026-09-16): built and shipped; this document is the original plan.**
> The architecture below changed in v1.12.0: the **OpenID Connect Generic Client
> plugin was removed** and the OAuth2 flow is now implemented inside
> `includes/gki-auth.php` (login endpoint `/insights-expo/login/`, callback
> `admin-ajax.php?action=gki-oauth-callback`, token exchange against
> `api.gitkraken.dev/oauth/access_token`, userinfo `api.gitkraken.dev/user`).
> The entitlement endpoint and token details in section 3–4 are confirmed and
> unchanged. Default cache TTL is 24 hours, not 30 minutes. v1.17.1 fixed the
> gate page's layout (card was 176px wide in the nav column), Elementor colour
> leaks, and an information leak (the search index and Ctrl-K palette were
> served to signed-out visitors). The **Hardening Checklist → WordPress Config**
> section is still the pre-enable checklist and still applies. For current
> behaviour see `CLAUDE.md` → Auth Gate; for local testing see `LOCAL-DEV.md`.

## Goal

Restrict all GKI Help Center pages (`/insights-expo/*`) to authenticated GitKraken users whose subscription includes Insights access.

## Architecture

Two components work together:

1. **OpenID Connect Generic Client** (free WP plugin) — handles the OAuth login flow against `gitkraken.dev`, creates WordPress sessions
2. **GKI Docs Helper auth module** (custom code in our plugin) — gates pages, checks Insights entitlement via licensing API, caches results

### User Flow

1. User visits any `/insights-expo/` page
2. Plugin checks for a WordPress session → if none, redirects to WP login
3. WP login shows "Sign in with GitKraken" → redirects to `gitkraken.dev` OAuth endpoint
4. User authenticates → redirected back to WordPress with auth code
5. OIDC plugin exchanges code for access token, creates WP user session
6. Plugin calls GK licensing API with the stored access token → checks Insights entitlement
7. Result cached in WP user meta (default TTL: 30 minutes)
8. **Entitled** → page renders normally
9. **Not entitled** → gate page shown with upgrade link

### Blocked User Experience

- **Not logged in**: "Sign in with GitKraken to access Insights documentation" + login button
- **Logged in, no Insights**: "Your subscription doesn't include Insights" + upgrade/contact link
- **WP admins**: bypass the gate entirely (prevents lockout)

---

## Backend Team Requirements

The following is needed from the GitKraken backend/platform team:

### 1. OAuth Client Registration

Register the help center as an OAuth client on `gitkraken.dev`:

- **Redirect/callback URI**: `https://help.gitkraken.com/wp-admin/admin-ajax.php?action=openid-connect-authorize`
- **Deliverables**: Client ID, Client Secret

### 2. Endpoint URLs

- **Authorize URL** — where users are redirected to log in (e.g., `https://gitkraken.dev/oauth/authorize`)
- **Token URL** — where WP exchanges the auth code for tokens (e.g., `https://gitkraken.dev/oauth/token`)
- **Userinfo URL** — where WP fetches user profile after login (e.g., `https://gitkraken.dev/api/v1/user`)
- **Required scopes** — what scopes the help center should request (e.g., `openid email profile`)

### 3. Insights Entitlement Endpoint ✅ CONFIRMED

**Endpoint:** `GET https://api.gitkraken.dev/user/organizations`
**Auth:** Bearer token from the OAuth flow
**Response:** Array of organizations, each containing `totalInsightsLicenses` (int)

**Gate logic:** If any org in the response has `totalInsightsLicenses > 0`, the user is granted access.

**Caveat:** This is org-level, not per-user. It confirms the user belongs to an org that owns Insights seats, but not that this specific user has a seat assigned. The actual Insights member roster is an admin-only endpoint the plugin can't use as the logged-in user. This is an acceptable trade-off for help center docs access — worst case, someone in an Insights org without a personal seat can still read the documentation.

### 4. Token Details ✅ CONFIRMED

- **Format:** Opaque Bearer token (not JWT)
- **Lifetime:** ~60 days
- **Refresh tokens:** None issued

---

## WordPress Setup Steps

### Step 1: Install OIDC Plugin

Install "OpenID Connect Generic Client" from the WP plugin directory.

### Step 2: Configure OIDC Plugin

Settings → OpenID Connect Client:

- Client ID: `gk_help`
- Client Secret: *(leave empty — not validated on this flow)*
- OpenID Scope: `email profile`
- Login Endpoint URL: `https://api.gitkraken.dev/oauth/authorize`
- Userinfo Endpoint URL: `https://api.gitkraken.dev/user`
- Token Validation Endpoint URL: `https://api.gitkraken.dev/oauth/access_token`
- End Session Endpoint URL: *(leave empty)*
- Login Button Text: `Sign in with GitKraken`
- Link Existing Users: Yes (match by email)
- Create user if does not exist: Yes
- Redirect Back to Origin Page: Yes
- New user default role: Subscriber

**Note:** Token is opaque (not JWT), so you may need to enable "Disable Token Verification" in the OIDC plugin if it errors on ID token validation.

### Step 3: Deploy GKI Docs Helper Update

Upload the updated plugin (v1.9.0) which includes:

- `includes/gki-auth.php` — auth gate logic, entitlement check, caching
- `templates/gki-gate.php` — blocked-user page template
- Admin settings: licensing API URL, cache TTL, gate enable/disable toggle

### Step 4: Configure Auth Settings

GKI Docs Helper → Settings (WP Admin):

- Licensing API Endpoint: `[from backend team]`
- Entitlement Cache TTL: 30 minutes (adjustable)
- Enable Auth Gate: toggle on when ready

---

## Hardening Checklist

### Handled in Plugin Code (already done)

- [x] WP admin users bypass the gate (prevent lockout) — `current_user_can('manage_options')` check
- [x] Token expiry → clear cached entitlement, force re-auth — 401 handler in `gki_auth_call_licensing_api()`
- [x] Subscription downgrade → caught within cache TTL window (default 30 min, configurable)
- [x] Return user to the page they originally requested after login — current URL passed to `wp_login_url()`
- [x] Licensing API downtime → fails closed (denies access) — change to fail-open if preferred
- [x] Gate pages send `nocache_headers()` so the gate itself is never cached

### Handled in Plugin Code since 1.17.3 — the lockdown

Audit of production on 2026-09-23, with the gate enabled, from an anonymous
client. The template gate only covers `is_single()`; every other route
WordPress uses to serve a post was open:

| Route | Found | Fixed by |
|---|---|---|
| `GET /wp-json/wp/v2/posts/<id>`, `?slug=`, `?categories=` | Full rendered content of all 50 posts | `rest_prepare_post` → 401; `rest_post_query` excludes the category |
| `GET /wp-json/wp/v2/search` | Titles + URLs | `rest_post_search_query` excludes the category |
| `GET /wp-json/oembed/1.0/embed?url=` | Title + embed HTML | `oembed_response_data` → false (404) |
| Feeds, `?s=`, date/author/tag archives, blog index | Empty on prod (host quirk), open in WordPress itself | `pre_get_posts` on the main query excludes the category |
| `/category/insights-expo/` archive | Excerpts of every page | Now a gated page — shows the gate |
| `/wp-sitemap-posts-post-N.xml` | All 50 URLs listed; `robots.txt` points crawlers at it | `wp_sitemaps_posts_query_args` + taxonomy exclusion; Yoast (`wpseo_exclude_from_sitemap_by_post_ids`) and Rank Math (`rank_math/sitemap/posts_to_exclude`) fed the same IDs |
| No robots directive anywhere | Pages fully indexable; 10+ already in search results with content snippets | `noindex, nofollow, noarchive` via `wp_robots` / `wpseo_robots` / `rank_math/frontend/robots` **and** an `X-Robots-Tag` header, on gated posts, the category archive and the gate page |
| Cloudflare cache | `/expo-ai-adoption-home/` served the full pre-gate article from cache (`cf-cache-status: HIT`, age 42 h) | `nocache_headers()` at `template_redirect` priority 4 on every gated request, before the entitlement checks; REST/oEmbed/shortlink head links removed |

All of it is active only while `gki_auth_enabled` is on and only for viewers
who are not entitled (admins and entitled subscribers see normal behaviour);
robots directives apply to everyone, since a crawler is never entitled.

### Still outside the plugin (do these by hand)

- [ ] **Purge Cloudflare for `/insights-expo/*` once** after 1.17.3 is live, and add a
      Cache Rule that bypasses cache for that path. The plugin now prevents new
      copies; it cannot evict the old ones.
- [ ] **Make the content repos private — before the first restricted page is committed.**
      `Roberto-Vizcarra/gk-insights-docs-expo` and its upstream `gitkraken/gk-insights-docs`
      are public on GitHub by choice today: nothing in them is confidential yet, so this is
      not urgent. It becomes a hard prerequisite the moment restricted content is written,
      because a public repo bypasses every control on the site. Git It Write syncs from a
      private repo with a token. Also purge the git history if anything restricted was ever
      committed while public.
- [ ] **Search Console / Bing Webmaster:** request removal of the `/insights-expo/`
      prefix. `noindex` takes weeks to propagate on its own and the snippets are
      already cached by search and AI answer engines.
- [ ] **SearchWP:** it runs its own index and its live-search AJAX endpoint bypasses
      `pre_get_posts`. Exclude the `insights-expo` category in the SearchWP engine
      settings, or gated titles/excerpts will still surface in site search.
- [ ] **Media:** images under `wp-content/uploads/` are public URLs that WordPress
      cannot gate. Do not put restricted material in screenshots, or front the uploads
      path with a Cloudflare Access rule.
- [ ] **`/gk-insights/`** (the main Insights section) is ungated. The expo build is
      expected to fold back into that category eventually; the gate's scope there is a
      design decision (whole category vs. sub-category vs. per-page flag) — see
      `MERGE-PLAN.md` §1 before adding restricted content to either section.
- [ ] Verify each row of the table above on prod with the commands in `LOCAL-DEV.md`
      → "Verifying the lockdown".

---

## Files Changed

```
gki-docs-helper/
  gki-docs-helper.php          # v1.9.0 — includes auth module, adds settings
  includes/gki-auth.php        # NEW — auth gate, entitlement check, caching
  templates/gki-gate.php       # NEW — blocked-user page
  css/gki-docs.css             # Gate page styles added
  gki-docs-helper.zip          # Rebuilt
```

## Status

- [x] Implementation plan documented
- [x] Auth module scaffolded with placeholder endpoints (v1.9.0)
- [x] Backend team provided OAuth client + endpoint details
- [x] ~~OIDC plugin installed and configured on WP~~ — replaced by the built-in OAuth2 handler (v1.12.0)
- [x] Gate page redesigned with GK branding (v1.10.7)
- [x] Cloudflare caching of gated pages fixed with no-store headers (v1.10.7)
- [x] Production deployment (gate available, enable via Settings → GKI Auth Gate)
- [x] Gate page layout, Elementor leaks and search-index leak fixed (v1.17.1, on `pass-c-structural`, not yet deployed)
- [x] Hardening audit run on prod 2026-09-23 — REST, oEmbed, sitemap, robots and CDN cache all leaked; closed in plugin code (v1.17.3)
- [ ] Manual items in "Still outside the plugin" done (CF purge + rule, private repos, Search Console removal, SearchWP, `/gk-insights/`)
- [ ] Gate visually confirmed on production after 1.17.1 upload (the local site cannot complete the sign-in round-trip)
