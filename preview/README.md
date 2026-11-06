# Local preview

Renders every page in `gk-insights-expo/*.md` into a browsable copy of the Help
Center (`help.gitkraken.com/insights-expo/`), using the plugin's own PHP. It
renders whatever branch is checked out, so you can preview a branch before it
syncs to WordPress.

The Docker WordPress in `local-dev/` is still the full-fidelity check (real
theme, Elementor kit, database, auth gate). This is the fast one: one command,
a few seconds, no Docker.

## Use it

```
python preview/build.py            # build preview/out/, then open Home in your browser (file://)
python preview/build.py --no-open  # build only
python preview/build.py --serve    # build, serve the repo root on http://localhost:8765 and open it
python preview/build.py --serve --port 9000
```

Edit a page or the plugin, re-run the build, reload. With `--serve` the server
keeps running; build again from another terminal.

| Flag | What it does |
|---|---|
| `--serve` | Serves the **repo root** (so `../../_images/` and `../../gki-docs-helper/` resolve) and prints the URL. |
| `--port N` | Port for `--serve` (default 8765). |
| `--no-open` | Don't open Home in the browser (it opens by default). |
| `--php PATH` | Use this `php`. Also: the `GKI_PHP` environment variable. |
| `--strict` | Treat PHP warnings and notices as failures. |
| `--no-install` | If PHP is missing, print the install command instead of running it. |

The build exits non-zero if a page links to an `/insights-expo/` page that does
not exist, if a page references an image that is not in `_images/`, or (with
`--strict`) if the plugin raised a PHP warning. The pages are still written;
the problems are listed at the end, one line each.

## Requirements (set up automatically)

- **Python 3.8+**, standard library only. Nothing to `pip install`.
- **PHP 8.2 or newer** (production runs 8.2). The build looks for it in this
  order: `--php`, `GKI_PHP`, `php` on `PATH`, then known install locations
  (WinGet's package folder, which a fresh install does not add to the current
  shell's `PATH`; Homebrew's). If none is found it installs one:
  - Windows: `winget install --id PHP.PHP.NTS.8.2 --source winget --scope user --silent ...`
  - macOS: `brew install php` (needs Homebrew)
  - Linux / anything else: prints the command to run and stops.
- **PHP extensions**: `mbstring`, `dom`, `libxml`, `json`, `pcre`, `ctype`,
  `iconv` (Parsedown, ParsedownExtra, WordPress' formatting functions and the
  plugin call these). WinGet's PHP has no `php.ini`, so `mbstring` is off; the
  build enables missing ones per run with `-d extension=...`. Nothing is
  written to your PHP install.
- **Network, first build only**: five WordPress 6.8.3 core files are fetched
  from the WordPress GitHub mirror, checked against pinned SHA-256 hashes, and
  cached in `preview/out/.cache/`.

## What it produces

`preview/out/` (gitignored), about 2 MB:

- one `<slug>.html` per page. Each page has its own nav state, breadcrumbs,
  card grid and TOC, which is why every page is its own file, as on the site.
- `search-data.js`: the command-palette index (`gkiSearchData`). WordPress
  inlines it into every page; the preview writes it once and every page loads
  it with `<script src>`.
- `index.html`, which redirects to Home.
- `.cache/`: the WordPress core sources and the plugin shadow folder (see
  below). The job and result files `render.php` exchanges with `build.py` are
  deleted after each successful run; after a failed one they stay for
  debugging.

Nothing in it copies repo content. Images are referenced in place:
`/wp-content/uploads/<name>` becomes `../../_images/<name>`. The plugin's CSS
and JS are referenced in place under `gki-docs-helper/`, with the file's mtime
added to `?ver=` so a reload always picks up your edits. Links to
`/insights-expo/<slug>/` (and to `https://help.gitkraken.com/insights-expo/...`)
become `<slug>.html`. The pages work from `file://` and from `--serve`.

## How it renders

Nothing is ported to Python. `build.py` finds PHP, reads the front matter and
hands the pages to `render.php`, which runs the same code production runs:

1. **Markdown to `post_content`**: Git It Write's own `GIW_Parsedown`
   (`vendor/git-it-write/parsedown.php`) on Parsedown 1.7.4 and ParsedownExtra
   0.8.1, the versions on production. Front-matter splitting, the
   `%%content%%` template, and `wp_unslash()` from `wp_insert_post()` follow
   Git It Write's `publisher.php`. Checked against the production database
   export: for the 9 published pages in it, the preview's `post_content` is
   byte-identical to what Git It Write stored.
2. **`post_content` to the page**: WordPress 6.8.3's own `wptexturize`,
   `wpautop`, `shortcode_unautop`, `do_shortcode` and `capital_P_dangit` on
   `the_content` (from `formatting.php`, `shortcodes.php`, `plugin.php`), then
   the plugin's filters at 15/16/17, then `templates/single-gki.php`, all
   unmodified and loaded from `gki-docs-helper/`.
3. Front matter: `title`, `taxonomy.category` and every `custom_fields` key
   become the post's title, category and post meta (strings, as WordPress
   stores them). The slug is the file name. Files starting with `_` or `.` are
   skipped, as Git It Write skips them.

`render.php` implements only what WordPress would answer from the database or
the request: `get_posts()` for the arguments the plugin passes, post meta,
permalinks (`/%category%/%postname%/`), the loop, enqueue/localize, `wp_head`
and `wp_footer` at core's priorities, and translation passthroughs.

**The auth gate is excluded.** `gki-docs-helper.php` loads
`includes/gki-auth.php` from `GKI_DOCS_PATH`. In the preview that path is a
shadow folder of one-line stubs that `include` the real plugin files, and an
empty `includes/gki-auth.php`. So the gate never loads, every page renders as an
entitled reader sees it, and no plugin file is edited. Core's `plugin.php` is
loaded as-is except that its `plugin_dir_path()`/`plugin_dir_url()` give way to
the preview's, which point at the shadow folder.

## What matches the live site, and what doesn't

Matches: the Markdown rendering, every plugin transform (metric pipeline,
general-page components, tier scales, instruments), the template (nav with
sections, sub-indexes and pages, collapsible; breadcrumbs; card grids; Home
hero, role paths and metric map; landing search), the plugin's CSS and JS
(TOC, command palette, theme toggle, lightbox, progress bar, instruments),
and the CDN assets the plugin enqueues (Tabler icons, JetBrains Mono).

Does not match:

- **Theme and Elementor kit styles are not loaded.** The live page also gets
  hello-elementor's CSS and the Elementor kit (`.elementor-kit-5`). The plugin
  overrides most of it. Where it doesn't, the live site differs: the role-path
  headings on Home are larger, and the `Last updated` `<kbd>` badge has a dark
  border. The preview's header is a bare document shell plus Inter from Google
  Fonts (Elementor loads Inter on the live site), with the body face and margin
  set so elements appended to `<body>`, such as the palette, use Inter.
- **WordPress features the plugin doesn't call**: emoji/smiley conversion,
  `wp_filter_content_tags` (lazy-loading and `srcset` attributes on images),
  SEO plugin `<head>` tags, SearchWP, and the `?s=` search results page (the
  landing search's no-JS fallback submits to production).
- **Post order for ties**: WordPress returns posts newest first. The preview
  uses file order as the date, which only matters when two siblings share a
  `nav_order`.
- **Offline**: Inter, JetBrains Mono and Tabler icons come from their CDNs.
  Offline, text falls back to system fonts and card icons are blank.

## Licences

`vendor/parsedown/` and `vendor/parsedown-extra/` are MIT (licence files
alongside). `vendor/git-it-write/parsedown.php` is copied unmodified from the Git
It Write plugin (GPLv2 or later). `render.php` includes
`GIW_Utils::remove_extension_relative_url()` verbatim from the same plugin.
The WordPress core files fetched into the cache are GPLv2 or later.
