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

- [ ] WP admin users bypass the gate (prevent lockout)
- [ ] Token expiry → clear cache, re-authenticate
- [ ] Subscription downgrade → caught within cache TTL window
- [ ] Add `noindex` meta tag or `robots.txt` block for gated pages
- [ ] Exclude GKI pages from WP page cache (WP Super Cache, W3TC, etc.)
- [ ] Return user to the page they originally requested after login
- [ ] Handle licensing API downtime gracefully (fail open vs fail closed — TBD)

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
