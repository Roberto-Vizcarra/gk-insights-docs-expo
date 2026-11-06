#!/usr/bin/env python3
"""Render gk-insights-expo/*.md into a browsable local copy of the Help Center.

    python preview/build.py            build preview/out/, then open Home in the browser
    python preview/build.py --no-open  build only
    python preview/build.py --serve    build, then serve the repo on localhost and open it

The HTML comes from the plugin's own PHP (gki-docs-helper/) running under a
small WordPress shim (preview/render.php) with Git It Write's Markdown parser
and WordPress core's formatting filters. Nothing is ported to Python; this
script only finds PHP, gathers the pages and writes the output. See README.md.

Python: standard library only. PHP >= 8.2: found or installed automatically.
"""
import argparse
import glob
import hashlib
import http.server
import json
import os
import platform
import re
import shutil
import subprocess
import sys
import urllib.request
import webbrowser
from functools import partial

HERE = os.path.dirname(os.path.abspath(__file__))
REPO = os.path.dirname(HERE)
PAGES_DIR = os.path.join(REPO, 'gk-insights-expo')
IMAGES_DIR = os.path.join(REPO, '_images')
PLUGIN_DIR = os.path.join(REPO, 'gki-docs-helper')
OUT = os.path.join(HERE, 'out')
CACHE = os.path.join(OUT, '.cache')
HOME_SLUG = 'expo-ai-adoption-home'
SITE = 'https://help.gitkraken.com'   # production origin; links to it become local files
CATEGORY = 'insights-expo'
PLUGIN_URL_PATH = '/wp-content/plugins/gki-docs-helper/'

# WordPress core files the renderer loads, pinned to production's release.
WP_VERSION = '6.8.3'
WP_FILES = {
    'plugin.php': '87a819241f339b4702b8e986dd2bd9f817755bae61d4631a1db11fdc34149c31',
    'class-wp-hook.php': '711375e96ed5b31d2435e079602168c4d15032e4b7bd96271c89eac039600766',
    'formatting.php': '13af9f31642a1e5fe34360a969ccec348f62d25ab7d6bd9c02d1c8251e01bd76',
    'kses.php': 'b32aa80b0dc024589473d157f2b166d30740f3714b2093184cee735260fc0788',
    'shortcodes.php': 'a6f755a2dab1f6a5169b95692515c74abd0b2c50682644e879151ef486ad1ac4',
}
WP_SOURCE = 'https://raw.githubusercontent.com/WordPress/WordPress/{}/wp-includes/{}'

# Extensions the plugin, Parsedown(Extra) and WordPress' formatting.php call.
PHP_EXTENSIONS = ['mbstring', 'dom', 'libxml', 'json', 'pcre', 'ctype', 'iconv']
MIN_PHP = (8, 2)
WINGET_ID = 'PHP.PHP.NTS.8.2'


def say(msg):
    print(msg, flush=True)


def die(msg, code=1):
    print('\nERROR: ' + msg, file=sys.stderr, flush=True)
    sys.exit(code)


# --------------------------------------------------------------------------- PHP

def php_version(exe):
    try:
        out = subprocess.run([exe, '-n', '-r', 'echo PHP_VERSION;'], capture_output=True, text=True, timeout=30)
    except (OSError, subprocess.SubprocessError):
        return None
    m = re.match(r'(\d+)\.(\d+)', out.stdout.strip())
    return (int(m.group(1)), int(m.group(2))) if out.returncode == 0 and m else None


def php_candidates(explicit):
    """Where a usable php may be, in order of preference."""
    yield explicit
    yield os.environ.get('GKI_PHP')
    yield shutil.which('php')
    local = os.environ.get('LOCALAPPDATA')
    if local:  # winget installs don't refresh PATH in this process
        pkgs = glob.glob(os.path.join(local, 'Microsoft', 'WinGet', 'Packages', 'PHP.PHP.*', 'php.exe'))
        yield from sorted(pkgs, reverse=True)
        yield os.path.join(local, 'Microsoft', 'WinGet', 'Links', 'php.exe')
    yield from ('/opt/homebrew/bin/php', '/usr/local/bin/php')


def find_php(explicit):
    for exe in php_candidates(explicit):
        if exe and os.path.isfile(exe):
            v = php_version(exe)
            if v and v >= MIN_PHP:
                return exe, v
            if v:
                say(f'  skipping {exe}: PHP {v[0]}.{v[1]} is older than {MIN_PHP[0]}.{MIN_PHP[1]}')
    return None, None


def install_command():
    system = platform.system()
    if system == 'Windows' and shutil.which('winget'):
        return ['winget', 'install', '--id', WINGET_ID, '--source', 'winget', '--scope', 'user', '--silent',
                '--accept-package-agreements', '--accept-source-agreements']
    if system == 'Darwin' and shutil.which('brew'):
        return ['brew', 'install', 'php']
    return None


def ensure_php(explicit, allow_install):
    say('Looking for PHP >= %d.%d ...' % MIN_PHP)
    exe, v = find_php(explicit)
    if exe:
        say(f'  using PHP {v[0]}.{v[1]}: {exe}')
        return exe
    cmd = install_command()
    manual = {
        'Windows': 'winget install --id %s --source winget --scope user' % WINGET_ID,
        'Darwin': 'brew install php   (install Homebrew first: https://brew.sh)',
    }.get(platform.system(), 'sudo apt install php-cli php-mbstring php-xml   (or your distro\'s PHP 8.2+ packages)')
    if not cmd or not allow_install:
        die('PHP %d.%d or newer was not found. Install it, then re-run:\n    %s\n'
            'or point at an existing php with --php PATH or the GKI_PHP environment variable.' % (MIN_PHP + (manual,)))
    say('  not found; installing it: ' + ' '.join(cmd))
    if subprocess.run(cmd).returncode != 0:
        say('  the installer reported a failure; checking whether PHP is there anyway')
    exe, v = find_php(explicit)
    if not exe:
        die('PHP still not found after installing. Install it by hand:\n    ' + manual)
    say(f'  installed PHP {v[0]}.{v[1]}: {exe}')
    return exe


def php_ext_flags(exe):
    """-d flags that load the extensions this build needs and php has but hasn't enabled.
    (winget's PHP ships no php.ini, so even bundled mbstring is off.)"""
    probe = 'echo json_encode([get_loaded_extensions(), PHP_EXTENSION_DIR]);'
    out = subprocess.run([exe, '-r', probe], capture_output=True, text=True)
    loaded, ext_dir = json.loads(out.stdout)
    loaded = {e.lower() for e in loaded}
    missing = [e for e in PHP_EXTENSIONS if e not in loaded]
    if not missing:
        return []
    local_ext = os.path.join(os.path.dirname(exe), 'ext')
    ext_dir = local_ext if os.path.isdir(local_ext) else ext_dir
    flags, absent = ['-d', 'extension_dir=' + ext_dir], []
    for e in missing:
        if glob.glob(os.path.join(ext_dir, f'php_{e}.dll')) or glob.glob(os.path.join(ext_dir, f'{e}.so')):
            flags += ['-d', 'extension=' + e]
        else:
            absent.append(e)
    if absent:
        die('PHP at %s lacks the extension(s) %s and they are not in %s.\n'
            'Install them (e.g. php-mbstring / php-xml packages) and re-run.' % (exe, ', '.join(absent), ext_dir))
    say('  enabling for this run: ' + ', '.join(e for e in missing))
    return flags


# ------------------------------------------------------------------ WordPress core

def ensure_wp_core():
    """Fetch WordPress core's hook and formatting files once, verified by hash."""
    d = os.path.join(CACHE, 'wordpress-' + WP_VERSION)
    os.makedirs(d, exist_ok=True)
    for name, sha in WP_FILES.items():
        path = os.path.join(d, name)
        if os.path.isfile(path) and hashlib.sha256(open(path, 'rb').read()).hexdigest() == sha:
            continue
        url = WP_SOURCE.format(WP_VERSION, name)
        say(f'  fetching WordPress {WP_VERSION} {name}')
        try:
            data = urllib.request.urlopen(url, timeout=60).read()
        except OSError as e:
            die(f'Could not download {url}: {e}\nThe first build needs network access; later builds use the cache.')
        if hashlib.sha256(data).hexdigest() != sha:
            die(f'{url} does not match its pinned hash; refusing to run it.')
        with open(path, 'wb') as f:
            f.write(data)
    return d


# ------------------------------------------------------------------------ pages

def yaml_scalar(v):
    v = v.strip()
    if v.startswith('"') and v.endswith('"'):
        return json.loads(v)
    if v.startswith("'") and v.endswith("'"):
        return v[1:-1].replace("''", "'")
    if v.startswith('[') and v.endswith(']'):
        return [yaml_scalar(x) for x in v[1:-1].split(',') if x.strip()]
    v = re.sub(r'\s+#.*$', '', v)
    if re.fullmatch(r'-?\d+', v):
        return int(v)
    return {'true': True, 'false': False, 'null': None, '~': None}.get(v.lower(), v)


def parse_front_matter(text):
    """The block-mapping subset of YAML the content uses (Git It Write runs Symfony YAML)."""
    root, stack = {}, [(-1, None, None)]  # (indent, parent, key) for open containers
    stack[0] = (-1, {'': root}, '')
    for n, raw in enumerate(text.split('\n'), 1):
        if not raw.strip() or raw.lstrip().startswith('#'):
            continue
        indent = len(raw) - len(raw.lstrip(' '))
        line = raw.strip()
        while stack[-1][0] >= indent:
            stack.pop()
        parent, key = stack[-1][1], stack[-1][2]
        container = parent[key]
        if line.startswith('- '):
            if not isinstance(container, list):
                container = parent[key] = [] if container in ({}, None) else container
            container.append(yaml_scalar(line[2:]))
            continue
        m = re.match(r'([^:#]+?):(?:\s+(.*))?$', line)
        if not m or not isinstance(container, dict):
            raise ValueError(f'front matter line {n}: {raw!r}')
        k, v = m.group(1).strip(), m.group(2)
        if v is None or not v.strip():
            container[k] = {}
            stack.append((indent, container, k))
        else:
            container[k] = yaml_scalar(v)
    return root


def meta_string(v):
    """update_post_meta() stores scalars as strings."""
    if isinstance(v, bool):
        return '1' if v else ''
    if v is None:
        return ''
    return json.dumps(v) if isinstance(v, (list, dict)) else str(v)


def read_pages():
    pages = []
    for path in sorted(glob.glob(os.path.join(PAGES_DIR, '*.md'))):
        slug = os.path.splitext(os.path.basename(path))[0]
        if slug[0] in '_.':
            continue  # Git It Write skips these
        with open(path, encoding='utf-8') as f:
            text = f.read().replace('\r\n', '\n')  # GitHub serves LF; Windows checkouts may be CRLF
        parts = re.split(r'^[\s]?---[\s]?$', '\n' + text.lstrip(), maxsplit=2, flags=re.M)
        try:
            fm = parse_front_matter(parts[1].strip()) if len(parts) >= 3 else {}
        except ValueError as e:
            die(f'{os.path.relpath(path, REPO)}: cannot read {e}')
        cats = (fm.get('taxonomy') or {}).get('category') or []
        pages.append({
            'slug': slug,
            'title': fm.get('title') or slug,
            'categories': [cats] if isinstance(cats, str) else list(cats),
            'meta': {k: meta_string(v) for k, v in (fm.get('custom_fields') or {}).items()},
            'text': text,
        })
    if not pages:
        die(f'No pages found in {PAGES_DIR}')
    return pages


def make_shadow():
    """GKI_DOCS_PATH for the preview: stubs that include the real plugin files,
    plus an empty includes/gki-auth.php so the auth gate is never loaded."""
    shadow = os.path.join(CACHE, 'plugin-shadow')
    shutil.rmtree(shadow, ignore_errors=True)
    for path in glob.glob(os.path.join(PLUGIN_DIR, '**', '*.php'), recursive=True):
        rel = os.path.relpath(path, PLUGIN_DIR)
        if os.path.dirname(rel) == '':
            continue  # the main file is loaded directly
        dest = os.path.join(shadow, rel)
        os.makedirs(os.path.dirname(dest), exist_ok=True)
        with open(dest, 'w', encoding='utf-8') as f:
            if rel.replace('\\', '/') == 'includes/gki-auth.php':
                f.write('<?php // Local preview: the auth gate is excluded; every page renders as an entitled reader sees it.\n')
            else:
                f.write("<?php return include %s;\n" % json.dumps(path.replace('\\', '/')).replace('$', '\\$'))
    return shadow


# ----------------------------------------------------------------------- render

def render(php, flags, pages):
    os.makedirs(CACHE, exist_ok=True)
    job_path, out_path = os.path.join(CACHE, 'job.json'), os.path.join(CACHE, 'result.json')
    fwd = lambda p: p.replace('\\', '/')
    job = {
        'plugin_dir': fwd(PLUGIN_DIR), 'shadow_dir': fwd(make_shadow()), 'wp_core_dir': fwd(ensure_wp_core()),
        'vendor_dir': fwd(os.path.join(HERE, 'vendor')), 'out': fwd(out_path),
        'home': SITE, 'plugin_url': SITE + PLUGIN_URL_PATH, 'pages': pages,
    }
    with open(job_path, 'w', encoding='utf-8') as f:
        json.dump(job, f)
    if os.path.exists(out_path):
        os.remove(out_path)
    say(f'Rendering {len(pages)} pages with the plugin\'s PHP ...')
    proc = subprocess.run([php, '-d', 'display_errors=stderr', '-d', 'memory_limit=512M', *flags,
                           os.path.join(HERE, 'render.php'), job_path], capture_output=True, text=True, encoding='utf-8')
    if proc.returncode != 0 or not os.path.exists(out_path):
        die('render.php failed (exit %d):\n%s%s' % (proc.returncode, proc.stdout[-4000:], proc.stderr[-4000:]))
    with open(out_path, encoding='utf-8') as f:
        result = json.load(f)
    for scratch in (job_path, out_path):  # they hold page text; keep out/ to generated HTML
        os.remove(scratch)
    result['warnings'] += [l for l in proc.stderr.splitlines() if l.strip()]
    return result


LINK = re.compile(r'''(?P<pre>(?:href|src|action)=["']|"url":")(?P<url>[^"'\s]+)''')


def localise(html, slugs, broken, missing, page):
    """Production URLs -> files in this checkout, relative to preview/out/."""
    def fix(m):
        url = m.group('url')
        path = url[len(SITE):] if url.startswith(SITE) else url
        if path.startswith('/' + CATEGORY):
            mm = re.fullmatch(r'/%s/?([^/#?]*)/?(\?[^#]*)?(#.*)?' % CATEGORY, path)
            if mm:
                slug = mm.group(1) or HOME_SLUG
                if slug in slugs:
                    return m.group('pre') + slug + '.html' + (mm.group(3) or '')
            broken.append((page, url))
        elif path.startswith(PLUGIN_URL_PATH):
            # The plugin's CSS/JS, in place. The file's mtime joins ?ver= so a
            # reload picks up edits made under an unchanged plugin version.
            rel, _, query = path[len(PLUGIN_URL_PATH):].partition('?')
            local = os.path.join(PLUGIN_DIR, *rel.split('/'))
            if not os.path.isfile(local):
                missing.append((page, url))
                return m.group(0)
            ver = '?%s.%d' % (query, os.path.getmtime(local)) if query else ''
            return m.group('pre') + '../../gki-docs-helper/' + rel + ver
        elif path.startswith('/wp-content/uploads/'):
            name = path.rsplit('/', 1)[1].split('?')[0]
            if not os.path.isfile(os.path.join(IMAGES_DIR, name)):
                missing.append((page, url))
            return m.group('pre') + '../../_images/' + name
        return m.group(0)
    return LINK.sub(fix, html)


def write_site(result, pages):
    slugs = {p['slug'] for p in pages}
    for old in glob.glob(os.path.join(OUT, '*.html')) + glob.glob(os.path.join(OUT, '*.js')):
        os.remove(old)  # pages deleted on this branch must not linger
    broken, missing = [], []
    data = ''.join('var %s = %s;\n' % (var, localise(js, slugs, broken, missing, 'search index'))
                   for var, js in result['search_data'].items())
    data_src = 'search-data.js?v=' + hashlib.sha256(data.encode()).hexdigest()[:10]
    for slug, html in result['pages'].items():
        html = re.sub(r'PREVIEW-L10N:\w+', data_src, localise(html, slugs, broken, missing, slug))
        with open(os.path.join(OUT, slug + '.html'), 'w', encoding='utf-8', newline='\n') as f:
            f.write(html)
    with open(os.path.join(OUT, 'search-data.js'), 'w', encoding='utf-8', newline='\n') as f:
        f.write('/* The plugin\'s wp_localize_script() data, shared by every preview page. */\n' + data)
    with open(os.path.join(OUT, 'index.html'), 'w', encoding='utf-8') as f:
        f.write(f'<!doctype html><meta charset="utf-8"><meta http-equiv="refresh" content="0; url={HOME_SLUG}.html">'
                f'<a href="{HOME_SLUG}.html">GitKraken Insights Help Center (local preview)</a>\n')
    return sorted(set(broken)), sorted(set(missing))


def group_warnings(warnings):
    """One line per distinct warning, with the pages it came from."""
    groups = {}
    for w in warnings:
        m = re.match(r'(.*) \[page (.*)\]$', w)
        msg, page = (m.group(1), m.group(2)) if m else (w, '-')
        groups.setdefault(msg.replace(REPO + os.sep, '').replace(REPO.replace('\\', '/') + '/', ''), []).append(page)
    return ['%s  (x%d; %s)' % (msg, len(pages), ', '.join(sorted(set(pages))[:4]) + (' ...' if len(set(pages)) > 4 else ''))
            for msg, pages in groups.items()]


def report(warnings, broken, missing, strict):
    for title, items in (('PHP warnings/notices (WordPress would log these to debug.log)', group_warnings(warnings)),
                         ('Broken internal links (page -> target)', [f'{p} -> {u}' for p, u in broken]),
                         ('Missing images and plugin assets (page -> src)', [f'{p} -> {u}' for p, u in missing])):
        if items:
            say(f'\n{title}: {len(items)}')
            for i in items:
                say('  ' + i)
    failed = bool(broken or missing or (strict and warnings))
    if failed:
        print('\nBUILD FAILED: fix the items above%s.' % (' (--strict treats PHP warnings as failures)' if strict and warnings else ''),
              file=sys.stderr)
    return failed


def size_of(path):
    return sum(os.path.getsize(f) for f in glob.glob(os.path.join(path, '*')) if os.path.isfile(f))


def serve(port, open_browser):
    handler = partial(http.server.SimpleHTTPRequestHandler, directory=REPO)
    handler.log_message = lambda *a: None
    with http.server.ThreadingHTTPServer(('127.0.0.1', port), handler) as httpd:
        url = f'http://localhost:{httpd.server_address[1]}/preview/out/{HOME_SLUG}.html'
        say(f'\nServing the repo root at {url}\n(Ctrl+C to stop; re-run the build in another terminal and reload.)')
        if open_browser:
            webbrowser.open(url)
        try:
            httpd.serve_forever()
        except KeyboardInterrupt:
            pass


def main():
    ap = argparse.ArgumentParser(description=__doc__.split('\n\n')[0])
    ap.add_argument('--serve', action='store_true', help='serve the repo root over HTTP after building')
    ap.add_argument('--port', type=int, default=8765, help='port for --serve (default 8765)')
    ap.add_argument('--no-open', dest='open', action='store_false',
                    help='do not open Home in the browser after building (it opens by default)')
    ap.add_argument('--open', dest='open', action='store_true', help=argparse.SUPPRESS)  # old flag, now the default
    ap.set_defaults(open=True)
    ap.add_argument('--php', help='path to php (otherwise GKI_PHP, PATH, then known install locations)')
    ap.add_argument('--strict', action='store_true', help='treat PHP warnings and notices as failures')
    ap.add_argument('--no-install', action='store_true', help='do not install PHP if it is missing; just say how')
    args = ap.parse_args()

    php = ensure_php(args.php, not args.no_install)
    flags = php_ext_flags(php)
    pages = read_pages()
    result = render(php, flags, pages)
    broken, missing = write_site(result, pages)
    say(f'Wrote {len(result["pages"])} pages to {os.path.relpath(OUT, REPO)}/ '
        f'({size_of(OUT) / 1024:.0f} KB; images and assets are referenced in place).')
    failed = report(result['warnings'], broken, missing, args.strict)
    home = os.path.join(OUT, HOME_SLUG + '.html')
    if args.serve:
        serve(args.port, args.open)
    else:
        say(f'Home: {home}')
        if args.open:
            webbrowser.open('file:///' + home.replace('\\', '/').lstrip('/'))
    sys.exit(1 if failed else 0)


if __name__ == '__main__':
    main()
