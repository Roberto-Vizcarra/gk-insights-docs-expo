---
name: gki-docs-authoring
description: Write, add, move, or edit pages in the GitKraken Insights AI Adoption Help Center (gk-insights-expo/*.md, help.gitkraken.com/insights-expo/) so they work with the GKI Docs Helper plugin's Pass C design. Covers the custom_fields frontmatter that builds the nav, breadcrumbs, card grids, Home metric map and search; page types; the Markdown patterns the plugin turns into spec panels, formula panels, step rails, tier scales, callouts, FAQs and checklists; and the lint and preview checks to run before committing. Use for any content change in gk-insights-expo/, including new metric pages, new connect or playbook pages, renaming or re-parenting pages, and nav order changes. For changes to the plugin's PHP, CSS or JS, use the gki-docs-helper skill instead.
---

# Authoring Insights Help Center pages

Pages are plain Markdown. The plugin gives them their design, so you never
write layout HTML. There are two levers:

1. **Frontmatter `custom_fields`** become WordPress post meta. The plugin builds
   the left nav, breadcrumbs, index-page card grids, the Home metric map and the
   command-palette search index from them.
2. **Recognised Markdown patterns.** After Parsedown renders the page, the
   `the_content` filters in `gki-docs-helper/includes/gki-passc.php` find known
   patterns and replace them with components: spec panel, formula panel, step
   rail, tier scale, callouts and so on. If you write the pattern, the page gets
   the component. If you don't, it renders as plain prose. Nothing breaks either
   way.

How the pipeline runs: Markdown in `gk-insights-expo/` → Git It Write
(Parsedown v1.7.4 + ParsedownExtra) → a WP `post` in category `insights-expo` →
the plugin's filters at priorities 15/16/17 → `templates/single-gki.php`. The
slug is the file name. The URL is `/insights-expo/<slug>/`.

## Before you start

- Read the **Content Integrity** and **Technical Writing Standards** sections of
  `CLAUDE.md`. Don't invent product behaviour, don't add, rename or remove
  images, and don't rewrite text you were only asked to move.
- Look at two or three sibling pages in the same section. Copy their frontmatter
  values and structure, not this skill's examples. The siblings are the source
  of truth for icon, colour and voice.

## Workflow

1. **Place the page.** Pick its section (`nav_category`), its parent if it has
   one (`nav_parent`), and its `page_type`. Pick a `nav_order` that slots it
   between its siblings: orders go up in steps of 10 (sections, families) or 1
   (metrics in a family). See [references/frontmatter.md](references/frontmatter.md).
2. **Start from a skeleton** in [references/templates.md](references/templates.md):
   metric page, family sub-index, section index, how-to/connect page, or
   playbook.
3. **Write the body using the patterns** in
   [references/patterns.md](references/patterns.md). On a metric page the H3
   names matter: the plugin reorders sections by heading text.
4. **Wire it in.** Card grids, nav, breadcrumbs and the Home metric map update
   on their own. Hand-written cross-references do not. Check for each of these:
   - the parent index's overview tables and lists, such as "The four DORA
     metrics" on `expo-ai-adoption-dora-metrics.md`
   - **Related metrics** tables on sibling metric pages
   - "Related pages" tables on playbooks
   - any page that links to a slug you renamed

   Internal links are root-relative: `/insights-expo/expo-ai-adoption-<name>`,
   plus `#anchor` if needed (the heading text, lowercased and hyphenated).
5. **Lint:**
   `python .claude/skills/gki-docs-authoring/scripts/check_pages.py [files…]`.
   With no arguments it checks every page and the whole hierarchy. Fix every
   `ERROR`. Read each `WARN` and either fix it or know why it's fine.
6. **Preview:** `python preview/build.py`. It renders every page with the
   plugin's own PHP and opens Home (see `preview/README.md`). It exits non-zero
   on a broken `/insights-expo/` link or a missing image. Open the page you
   changed and check:
   - it sits in the right place in the nav
   - the breadcrumb is correct
   - its card shows on the parent index
   - the components you expected rendered
   - the right-hand TOC lists the sections
7. **Commit.** Git It Write syncs the branch it watches to WordPress. Tell the
   user about any WordPress-side cleanup the change needs (next section).

## Changes that need more than a content edit

| Change | What else is needed |
|---|---|
| Delete or rename a file | Git It Write never deletes posts. The old post stays live at the old URL until someone deletes it in WP Admin. A rename also changes the slug, so update every link to it and every `nav_parent` that names it. Flag this to the user. |
| New `nav_category` (a new top-level section) | It renders after Admin & API unless it's added to `$section_order` in `gki_docs_get_nav_structure()` (plugin change). It also needs exactly one un-parented `page_type: index` page. |
| New metric that needs an interactive instrument | Instruments are mapped by slug tail in `gki_passc_instruments()` and drawn by `js/gki-passc.js`. That's plugin work, so use the gki-docs-helper skill. |
| Home hero headline, the four role cards, or the "derived" (dashed) chips on the metric map | Hard-coded in `templates/single-gki.php` and `gki_passc_role_paths()` / `gki_passc_metric_map()`. That's plugin work. |
| A page needs a component no pattern produces | Don't add HTML to the Markdown. Teach the transform in `gki-passc.php` to recognise a pattern (CLAUDE.md, Design Rules), which is plugin work. |
| New screenshot | Out of scope for this skill. CLAUDE.md forbids adding images during content edits. Screenshots come from the docs-audit capture flow. |

## Rules that are easy to miss

- **Everything the plugin reads goes under `custom_fields:`.** If you put
  `nav_order:` at the top level, Git It Write silently drops it and the page
  falls out of the nav.
- **`nav_parent` is the parent's slug** (its file name without `.md`), not its
  title. The parent must be a `page_type: index` page in the same
  `nav_category`. The nav is three levels deep at most: section index →
  sub-index → page.
- **`nav_label` is what readers see**: the H1, the nav entry, the breadcrumb and
  the card title. `title` is the SEO/post title. On metric pages they're usually
  the same, and in quotes.
- **Don't write an H1.** The template prints `nav_label` as the H1. A leading H2
  that repeats the title or `nav_label` gets removed, so you can keep one (all
  metric pages do), but it never shows.
- **Index pages split at the first `---`.** Intro → landing search + card grid →
  everything after the rule. Put exactly the intro above the first rule. The
  `---` before each later H2 is hidden by CSS, so the existing convention of a
  rule before every H2 is fine.
- **On metric pages, only the first blockquote and the first fenced code block
  get special treatment.** They become the definition lede and the formula
  panel. Later blockquotes are not turned into callouts on metric pages, and
  later code blocks get no labelled panel.
- **Parsedown v1 won't parse Markdown inside an HTML block.** Inside `<figure>`,
  write links and emphasis as HTML.
- **Images**: `<figure>` + `<img src="/wp-content/uploads/<file>"
  class="help-center-img img-bordered" alt="…">` + `<figcaption>`. The file must
  exist in `_images/`. Keep `src` and `alt` exactly as they are when you move a
  figure.
- **Open each page with `<kbd>Last updated: <Month YYYY></kbd>`**, and update it
  when the content changes.
- **Drafts**: a file name starting with `_` is skipped by Git It Write and by
  the preview. A page that has to exist but stay out of the nav uses
  `nav_category: hidden`.
