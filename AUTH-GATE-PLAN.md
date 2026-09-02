# GKI Help Center — Auth Gate Implementation Plan

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

### 3. Insights Entitlement Endpoint

An API endpoint that, given a bearer token, returns whether the user's account includes Insights access.

- **URL**: e.g., `https://gitkraken.dev/api/v1/subscription` or similar
- **Auth**: Bearer token from the OAuth flow
- **Response format**: needs to indicate Insights entitlement (exact field TBD)

### 4. Token Details

- Access token lifetime
- Whether refresh tokens are issued
- Token format (JWT vs opaque)

---

## WordPress Setup Steps

### Step 1: Install OIDC Plugin

Install "OpenID Connect Generic Client" from the WP plugin directory.

### Step 2: Configure OIDC Plugin

Settings → OpenID Connect Client:

- Client ID: `[from backend team]`
- Client Secret: `[from backend team]`
- OpenID Scope: `[from backend team]`
- Login Endpoint URL: `[from backend team]`
- Userinfo Endpoint URL: `[from backend team]`
- Token Validation Endpoint URL: `[from backend team]`
- Login Button Text: `Sign in with GitKraken`
- Link Existing Users: Yes (match by email)
- Create user if does not exist: Yes
- New user default role: Subscriber

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

### WordPress Config (do these before enabling the gate)

These are done in WP Admin, not in plugin code. The WordPress posts still exist in the database regardless of the gate, so search engines and sitemaps could expose them through other paths.

#### 1. Block search engine indexing of gated pages

**Option A — robots.txt (simplest)**

1. Go to WP Admin → Settings → Reading
2. Make sure "Discourage search engines from indexing this site" is **unchecked** (you don't want to block the whole site, just the gated pages)
3. Install the **Yoast SEO** plugin if not already installed (or Rank Math — either works)
4. In Yoast: go to Yoast SEO → Settings → Content types → Posts
5. Under "Show Posts in search results?" — this is site-wide, so leave it on
6. Instead, go to Yoast SEO → Settings → Categories (under Taxonomies)
7. Find the `insights-expo` category → set "Show insights-expo in search results" to **No**
8. This adds `noindex` to all posts in the category

**If you don't have an SEO plugin and don't want one:**

1. Edit the `robots.txt` file on the server (or use a robots.txt plugin)
2. Add these lines:
   ```
   User-agent: *
   Disallow: /insights-expo/
   ```
3. This tells search engines not to crawl any `/insights-expo/` URLs

**Verify it worked:**
- Visit `https://help.gitkraken.com/robots.txt` and confirm the disallow rule appears
- Or for the SEO plugin approach: view source on any gated page (as an admin) and look for `<meta name="robots" content="noindex` in the `<head>`

#### 2. Remove gated pages from the XML sitemap

Search engines discover pages through sitemaps even if robots.txt blocks them.

**If using Yoast SEO:**
1. The noindex setting from step 1 automatically excludes those pages from the Yoast sitemap — nothing extra to do

**If using another sitemap plugin (e.g., XML Sitemaps, Rank Math):**
1. Go to that plugin's settings
2. Find the exclusion or category filter option
3. Exclude the `insights-expo` category from the sitemap

**If using the default WordPress sitemap (`/wp-sitemap.xml`):**
1. Add this to your theme's `functions.php` or a custom plugin:
   ```php
   add_filter( 'wp_sitemaps_posts_query_args', function( $args ) {
       $args['category__not_in'] = array( get_cat_ID( 'insights-expo' ) );
       return $args;
   } );
   ```
2. Or just install Yoast — it handles this automatically with the noindex setting

**Verify it worked:**
- Visit `https://help.gitkraken.com/wp-sitemap.xml` (or `/sitemap_index.xml` for Yoast)
- Search for "insights-expo" — no gated URLs should appear

#### 3. Exclude gated pages from WordPress page cache

If the site uses a caching plugin, authenticated pages could be cached and served to unauthenticated users, bypassing the gate entirely.

**If using WP Super Cache:**
1. Go to WP Admin → Settings → WP Super Cache → Advanced
2. Under "Accepted Filenames & Rejected URIs", add: `/insights-expo/`
3. Save

**If using W3 Total Cache:**
1. Go to WP Admin → Performance → Page Cache
2. Under "Never cache the following pages" (in the Advanced section), add: `/insights-expo/`
3. Save all settings

**If using LiteSpeed Cache:**
1. Go to LiteSpeed Cache → Cache → Excludes
2. Under "Do Not Cache URIs", add: `/insights-expo/`
3. Save

**If using a managed host with built-in caching (WP Engine, Flywheel, Kinsta, etc.):**
1. Check the host's documentation for URL-based cache exclusion
2. Add `/insights-expo/` (or the equivalent wildcard pattern) to the exclusion list
3. Some hosts (like WP Engine) also have a "Cache Exclusions" panel in their dashboard

**If you're not sure which caching is in use:**
1. Go to WP Admin → Plugins → Installed Plugins
2. Look for anything with "Cache" in the name
3. If nothing is there, your host may handle caching at the server level — check their dashboard

**Verify it worked:**
1. Log in as an admin, visit a gated page — should load normally
2. Open the same URL in a private/incognito browser window (not logged in)
3. You should see the gate page, not the cached authenticated version
4. Repeat after clicking through a few pages to build up cache

#### 4. Disable RSS/Atom feeds for gated content (optional)

WordPress exposes post content through RSS feeds by default. If the `insights-expo` posts appear in feeds, their content would be readable without auth.

1. Check if feeds include gated content: visit `https://help.gitkraken.com/category/insights-expo/feed/`
2. If content appears, add this to your theme's `functions.php` or a custom plugin:
   ```php
   add_action( 'pre_get_posts', function( $query ) {
       if ( $query->is_feed() ) {
           $excluded = get_cat_ID( 'insights-expo' );
           $query->set( 'category__not_in', array( $excluded ) );
       }
   } );
   ```
3. Or disable feeds entirely if the site doesn't use them — Yoast has a toggle for this under Yoast SEO → Settings → Advanced → RSS

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
- [x] Auth module scaffolded with placeholder endpoints
- [ ] Backend team provides OAuth client + endpoint details
- [ ] OIDC plugin installed and configured on WP
- [ ] End-to-end testing
- [ ] Production deployment
