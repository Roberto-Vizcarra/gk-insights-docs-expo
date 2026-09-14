# Taste Exploration — branch `Taste-design-expo`

A three-way comparison between the shipped build and two design passes,
switchable at runtime so they can be judged side by side rather than
one at a time.

**This branch is exploratory.** Nothing here is meant to ship as-is.
`v1.14.2-stable` is the rollback point.

---

## How to switch

Local site: <http://localhost:8420/insights-expo/expo-ai-adoption-home/>

Three ways, all equivalent:

1. **The control in the left nav** — "Design Pass: Base / A / B".
   Arrow keys work once focused.
2. **URL** — append `?taste=a`, `?taste=b`, or `?taste=stable`.
3. **Console** — `gkiTaste('b')`.

The choice persists in `localStorage` under `gki-taste` and is applied
before first paint, so switching and reloading never flash.

Both overlays load on every page but every rule inside them is scoped
to `html[data-taste="a"]` / `="b"`. With no attribute set, nothing
matches — **Base is the stable build, byte-for-byte**.
`css/gki-docs.css` has not been modified on this branch.

---

## What each variant is

### Base
Plugin v1.14.2 exactly as tagged. The comparison baseline.

### Pass A — craft (`css/taste-a.css`)
Typographic and state work only. No layout, palette or structural
change. Everything here is a defect fix or a measurable improvement,
not a matter of taste.

| Finding | Before | After |
|---|---|---|
| Type scale | 11 sizes drifted between 0.70–0.95rem | 9 steps with distinct roles |
| Body copy | 15.2px | 16px |
| Measure | 860px (~110 chars) | 68ch (~68 chars) |
| Focus states | 4 rules, `:focus` fired on mouse click | one `focus-visible` ring token everywhere |
| Pressed states | 4 rules | cards, nav, TOC, search results |
| `prefers-reduced-motion` | absent, against 23 transitions | full block |
| Tabular figures | none, on a site about 26 metrics | tables, code, counts, metric values |
| Shadows | pure black, same in both themes | tinted in light, deepened in dark |
| Mobile overflow | **page scrolls sideways by 122px at 375px** | 0px |

The mobile overflow was a real bug, not a taste question. The 768px
grid track was `1fr`, whose `auto` minimum resolves to the widest
child's max-content size, so one wide table or formula block dragged
the whole page past the viewport. The desktop tracks already used
`minmax(0, …)`; only the two mobile breakpoints were missed.

### Pass B — ambition (`css/taste-b.css`)
Builds on A. Changes how the page feels, so this is the one that
actually needs a judgement call.

- **Cards** lose `aspect-ratio: 1.15/1`, which had forced a one-line
  title and a three-line description into the same box. Counts pin to
  a hairline at the bottom so they share a baseline across the row.
  Hover gets real physics: lift, an accent edge wiping in from the
  left, icon tile scale, arrow travel.
- **Nav** active item gets a 3px accent rail plus a fill, so position
  in a 50-item tree is findable without relying on colour alone.
- **TOC** gets the matching rail and a hairline spine.
- **Section titles** replace the ragged inline-block underline — which
  reads as a link artifact — with a full-width hairline and a short
  accent lead-in.
- **Callouts** go from four-sided tinted boxes to a left rule with a
  lighter wash, so two in a row stop walling off the reading column.
- **Entry motion** on cards and section blocks: 12px, 320ms,
  IntersectionObserver, 40ms stagger capped at one row. Anything
  already in view on load skips it. Off entirely under reduced motion.

---

## What was deliberately not done

The `redesign-existing-projects` and `high-end-visual-design` skills
are written for landing pages and marketing sites. Roughly half their
advice is wrong for reference documentation, and some of it collides
head-on with the standing rules in `CLAUDE.md`:

| Skill says | Rejected because |
|---|---|
| Replace Inter, it's an AI tell | Inter is the GitKraken brand font |
| Purple/blue is the #1 AI fingerprint | #7900C9 is brand; the accent-only rule already solves this |
| Dashboards shouldn't have a left sidebar | Two documented layout traps live in that sidebar |
| Three equal cards is the most generic layout | Scannable uniformity *is* the feature on an index page |
| Double the whitespace, `py-24`–`py-40` | Docs are read, not browsed |
| "Never generate the same layout twice" | Consistency is the product in a docs system |
| Double-Bezel nested cards, squircle radii | Visual noise across 26 metric cards |

What was harvested: the performance guardrails (animate `transform`
and `opacity` only; `IntersectionObserver` over scroll listeners; no
`backdrop-filter` on scrolling content; no arbitrary z-index) and the
custom-easing point. Those are applied in Pass B.

---

## Retiring the exploration

When a winner is picked, fold the chosen overlay's rules into
`css/gki-docs.css` and delete:

- `css/taste-a.css`, `css/taste-b.css`, `css/taste-switch.css`
- `js/gki-taste.js`
- the enqueue block and `gki_docs_taste_init_script()` in
  `gki-docs-helper.php`
- the `.gki-taste-switch-wrap` block in `templates/single-gki.php`
- this file

Nothing needs to be unpicked from the base stylesheet, because nothing
was ever put in it.

---

## Known pre-existing issues (not introduced here)

- `.gki-layout` computes `transition: all` from Elementor or the theme,
  not from the plugin. Collapse still resolves correctly in all three
  variants (measured 240px → 44px), but it means the documented
  `grid-template-columns` trap is one stray rule away from firing.
- On mobile, the nested nav tree renders inside the horizontal scroll
  strip and leaves a tall empty gap below it.
- Several `/wp-content/uploads/*.png` return 404 on the local import —
  images were not part of the database import.
