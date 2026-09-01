# Phase 4 — UI/UX Refinement Handoff

**Created:** 2026-09-01
**For:** Next session continuing the GKI Help Center redesign
**Plugin version at handoff:** 1.7.0

---

## What Was Just Deployed

Plugin v1.7.0 was pushed and is ready for upload. The user deleted all WP posts and will reimport fresh via Git It Write. Changes in v1.7.0 vs v1.6.0:

1. **TOC spacing fix** — Index pages keep the 3-column grid but hide TOC via `visibility: hidden` instead of removing the column entirely. This keeps the content area width consistent between index and content pages.

2. **Card filter fix** — Removed `display: flex !important` from `.gki-card` that was preventing the JS filter from hiding cards. Now uses `.gki-hidden` class toggle. Added "no results" message.

3. **Table styling** — Added proper CSS for Markdown-rendered tables: padding, header backgrounds, hover rows, Elementor color overrides.

4. **Home page content order** — Template now splits `main-index` page content at the first `<hr>`: renders intro blurb, then card grid, then remaining content below in a `.gki-below-cards` wrapper.

5. **`gki_docs_render_card_grid()`** — Extracted card grid HTML into a reusable PHP function (was inline in the template).

---

## User's Reported Issues Still Needing Attention

The user flagged these in their last message. Items 1, 3, 4, and 5 were addressed in v1.7.0 but have NOT been verified live:

1. ~~TOC spacing mismatch~~ → Fixed (visibility: hidden)
2. **Weird table spacing on many pages** → CSS added but needs live verification
3. ~~Home page layout~~ → Fixed (cards after intro)
4. ~~Card filter does nothing~~ → Fixed (class toggle)
5. **"The design layout/philosophy for the entire flow of the site needs to be that the visual cards drive the navigation"** → This is a broader UX principle that should guide all Phase 4 work. Cards are the primary wayfinding mechanism; traditional nav is secondary.

---

## Phase 4 Punch List

### Priority 1 — Verify v1.7.0 Live

Before making more changes, the user needs to:
1. Upload plugin v1.7.0 zip to WP Admin
2. Trigger Git It Write sync (all posts were deleted for clean reimport)
3. Verify on live site:
   - Home page: blurb → cards → requirements (cards between intro and details)
   - Card filter works (type in search box, cards hide/show)
   - Table spacing is clean
   - TOC spacing matches between index and content pages
   - Headings are dark (#1C1C1C)
   - Nav is hierarchically nested

### Priority 2 — Visual Polish (CSS)

These are CSS-only fixes that don't require template or content changes:

- **Card aspect ratio tuning** — Current `aspect-ratio: 1.15 / 1` may be too tall on desktop. Test on real screen and adjust. Consider removing aspect-ratio and relying on min-height alone.
- **Card hover refinement** — The `translateY(-3px)` lift + purple border works but may feel too aggressive. Test and tune.
- **Responsive card grid** — Verify 3→2→1 column breakpoints on real mobile/tablet. Current: 3-col > 768px, 2-col 500–768px, 1-col < 500px.
- **Mobile nav** — Currently collapses to horizontal scroll. May need hamburger menu or collapsible accordion instead.
- **Blockquote styling** — The `>` blockquote on the home page renders as a purple-left-bordered box. The requirements info should probably render as a structured card (like the `.gki-requirements` component) instead of a blockquote.
- **Empty paragraph cleanup** — Parsedown may inject empty `<p>` tags that create unwanted spacing. The CSS hides `.gki-page p:empty` but check for other cases.
- **Heading anchor links** — H2/H3 headings get auto-IDs from the TOC JS, but there are no visible anchor links (hover-to-show `#` or link icon) for easy sharing.
- **Print styles** — No print stylesheet exists. Consider adding basic print rules (hide nav, TOC, back-to-top).
- **kbd tag styling** — The "Last updated: September 2026" `<kbd>` tag renders as a small grey pill. Confirm this looks intentional and consistent across pages.

### Priority 3 — Template / JS Enhancements

- **Index page intro text consistency** — Some index pages (Getting Started, Connect Your Data) have substantial content above their card grids. Others (Metrics, Playbooks, Admin) have just a one-liner. Decide whether index pages should be content-light (just intro sentence + cards) or content-rich (like Getting Started).
- **TOC depth control** — The TOC collects H2 and H3. Some pages have many H3s, making the TOC very long. Consider collapsing H3s under their parent H2.
- **Search scope indicator** — The card filter says "Filter pages..." but doesn't indicate whether it searches titles only or titles + descriptions. The `data-search` attribute includes both.
- **Back-to-top button** — Position (fixed bottom-right) may overlap content on narrow screens. Test.
- **Card grid animation** — Cards appear all at once. Consider a staggered fade-in on page load for polish.
- **Nav active state on section indexes** — When viewing a section index page (e.g., Metrics), the section heading in the nav should be visually active (bold + left border). Verify this works correctly.

### Priority 4 — Content Quality

- **Cross-page link audit** — After reimport, verify every internal link resolves. Key pattern to check: links from Getting Started to role-specific pages, from Playbooks to metric pages, from Home to section indexes.
- **Image rendering** — Verify all 10 images render correctly with the lightbox. Check alt text for accessibility.
- **Table of contents accuracy** — On long content pages, verify the right-sidebar TOC includes all headings and scroll spy highlights correctly.
- **Breadcrumb accuracy** — Verify breadcrumbs show `Home > Section > Page` for content pages, `Home > Section` for index pages, and nothing for the home page.

### Priority 5 — Design System Extension

- **Dark mode** — The CSS uses custom properties, so a dark mode variant would be a second set of token values under `@media (prefers-color-scheme: dark)`. Not started.
- **Code block styling** — Some pages may have inline `code` or code blocks. Currently styled with purple text on grey background. Verify this is readable.
- **Nested list rendering** — Markdown nested lists inside content pages may not render correctly through Parsedown. Test and fix CSS as needed.

---

## Files to Read First in Next Session

1. `CLAUDE.md` — Standing rules (content integrity, design rules, Git It Write requirements)
2. `PHASE-4-HANDOFF.md` — This file (the punch list)
3. `gki-docs-helper/gki-docs-helper.php` — Plugin functions and template logic
4. `gki-docs-helper/css/gki-docs.css` — All styles
5. `gki-docs-helper/js/gki-docs.js` — Interactive features
6. `gki-docs-helper/templates/single-gki.php` — Page template

---

## Key Functions Reference

| Function | File | Purpose |
|---|---|---|
| `gki_docs_is_target_post()` | gki-docs-helper.php | Checks `is_single() && has_category('insights-expo')` |
| `gki_docs_get_page_type()` | gki-docs-helper.php | Reads `page_type` post meta |
| `gki_docs_is_index_page()` | gki-docs-helper.php | Returns true for `index` or `main-index` |
| `gki_docs_get_nav_structure()` | gki-docs-helper.php | Builds hierarchical nav from `nav_category`/`nav_order`/`nav_label` post meta |
| `gki_docs_get_child_pages()` | gki-docs-helper.php | Queries child pages for card grid (main-index → section indexes; index → content pages) |
| `gki_docs_render_card_grid()` | gki-docs-helper.php | Outputs card grid HTML for an array of child cards |
| `gki_docs_get_breadcrumb_data()` | gki-docs-helper.php | Builds breadcrumb trail from nav structure |
| `gki_docs_body_class()` | gki-docs-helper.php | Adds `gki-docs-page` and `gki-index-page` body classes |
| `buildTOC()` | js/gki-docs.js | Auto-generates right-sidebar TOC from H2/H3 headings |
| `buildSearch()` | js/gki-docs.js | Creates filter input above card grid, toggles `.gki-hidden` class |
| `buildLightbox()` | js/gki-docs.js | Click-to-expand image overlay |

---

## CSS Architecture Notes

The stylesheet has four layers:

1. **Custom properties** (`:root`) — All brand tokens. Change colors here, they cascade everywhere.
2. **Component styles** (`.gki-*`) — Breadcrumb, cards, callouts, steps, figures, tables, etc.
3. **Layout** (`.gki-layout`) — 3-column grid: `240px minmax(0, 860px) 220px`.
4. **Elementor overrides** (`body.gki-docs-page .gki-*`) — High-specificity `!important` rules that beat the dark Elementor theme. These are fragile and must be checked after any theme update.

Index pages use `body.gki-index-page` to hide the TOC sidebar content (`visibility: hidden`) while keeping the column space.

---

## Template Content Flow

### Content pages
```
breadcrumb → the_content() → [right sidebar: TOC]
```

### Regular index pages (page_type: index)
```
breadcrumb → the_content() → card grid → [right sidebar: hidden]
```

### Main index / home page (page_type: main-index)
```
breadcrumb → intro (content before first <hr>) → card grid → remaining content in .gki-below-cards → [right sidebar: hidden]
```
