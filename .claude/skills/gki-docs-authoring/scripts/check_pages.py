#!/usr/bin/env python3
"""Lint the Insights Help Center Markdown pages against what the GKI Docs
Helper plugin expects.

Checks frontmatter (custom_fields nesting, allowed values, nav_parent
resolution), the metric-page skeleton, index-page layout, internal links and
image references. Standard library only.

    python .claude/skills/gki-docs-authoring/scripts/check_pages.py            # every page
    python .claude/skills/gki-docs-authoring/scripts/check_pages.py FILE ...   # report on these pages only

Exit status 1 if any ERROR was reported. WARN lines are judgement calls.
"""
import os
import re
import sys
from collections import defaultdict

REPO = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..', '..', '..'))
CONTENT = os.path.join(REPO, 'gk-insights')
# Classic Insights pages the AI Adoption build superseded. They are no longer
# synced, but their /gk-insights/ posts are still published, so links to them
# resolve.
LEGACY = os.path.join(REPO, 'Legacy-Insights-Docs-Archive')
IMAGES = os.path.join(REPO, '_images')

PAGE_TYPES = {'content', 'index', 'main-index'}
# Sections the template orders explicitly (gki_docs_get_nav_structure), plus
# the two special values. A new value renders, but after every known section.
NAV_CATEGORIES = {'home', 'getting-started', 'connect-your-data', 'metrics', 'playbooks', 'admin', 'hidden'}
CARD_COLORS = {'purple', 'blue', 'green', 'amber', 'red'}
CUSTOM_FIELDS = {'page_type', 'nav_category', 'nav_order', 'nav_label', 'nav_parent',
                 'card_icon', 'card_color', 'card_description'}
NAV_KEYS = CUSTOM_FIELDS

# gki_passc_section_order() in gki-docs-helper/includes/gki-passc.php
METRIC_SECTIONS = [
    ('At a glance', r'^at a glance$'),
    ('Formula', r'^(formulas?|the rubric)$'),
    ('How GitKraken Insights calculates it', r'^how .* (calculates|applies|computes) it$'),
    ('Why it matters', r'^why '),
    ('How to read it', r'^how to read it$'),
    ('Settings that affect it', r'^settings that affect it$'),
    ('How to improve/use ...', r'^how to (improve|use) '),
    ('Limitations and gotchas', r'^limitations'),
    ('FAQ', r'^faq$'),
    ('Related metrics', r'^related metrics$'),
    ('Where it appears', r'^where it appears$'),
]

LEGACY_SLUGS = {os.path.splitext(n)[0] for n in os.listdir(LEGACY) if n.endswith('.md')} if os.path.isdir(LEGACY) else set()

problems = []


def report(level, slug, msg):
    problems.append((level, slug, msg))


def parse_front_matter(text):
    """Return (dict, body). Handles the flat two-level YAML these pages use."""
    m = re.match(r'^---\r?\n(.*?)\r?\n---\r?\n?(.*)$', text, re.S)
    if not m:
        return None, text
    data, parent = {}, None
    for raw in m.group(1).splitlines():
        if not raw.strip() or raw.lstrip().startswith('#'):
            continue
        indent = len(raw) - len(raw.lstrip())
        km = re.match(r'^\s*([A-Za-z0-9_]+):\s*(.*)$', raw)
        if not km:
            continue
        key, val = km.group(1), km.group(2).strip()
        if len(val) >= 2 and val[0] == val[-1] and val[0] in '"\'':
            val = val[1:-1]
        if indent == 0:
            if val == '':
                data[key] = {}
                parent = key
            else:
                data[key] = val
                parent = None
        elif parent is not None:
            data[parent][key] = val
    return data, m.group(2)


def load_pages():
    pages = {}
    for name in sorted(os.listdir(CONTENT)):
        if not name.endswith('.md') or name[0] in '_.':
            continue  # Git It Write skips _ and . files
        with open(os.path.join(CONTENT, name), encoding='utf-8') as fh:
            text = fh.read()
        fm, body = parse_front_matter(text)
        pages[name[:-3]] = {'file': name, 'fm': fm, 'body': body}
    return pages


def cf(page):
    fields = (page['fm'] or {}).get('custom_fields')
    return fields if isinstance(fields, dict) else {}


def strip_code(body):
    return re.sub(r'```.*?```', '', body, flags=re.S)


def check_front_matter(slug, page):
    fm = page['fm']
    if fm is None:
        report('ERROR', slug, 'no YAML frontmatter (--- block) at the top of the file')
        return False
    if not slug.startswith('ai-adoption'):
        report('WARN', slug, 'file name does not start with ai-adoption-; the slug comes from the file name')
    if not fm.get('title'):
        report('ERROR', slug, 'missing title')
    if not fm.get('description'):
        report('WARN', slug, 'missing description (used for SEO and the page summary)')
    tax = fm.get('taxonomy')
    if not isinstance(tax, dict) or tax.get('category') != 'gk-insights':
        report('ERROR', slug, 'taxonomy.category must be gk-insights, or the post lands outside the Help Center')
    for key in NAV_KEYS:
        if key in fm:
            report('ERROR', slug, f'{key} is a top-level key; Git It Write only stores fields nested under custom_fields:')
    fields = fm.get('custom_fields')
    if not isinstance(fields, dict):
        report('ERROR', slug, 'no custom_fields: block; the page will not appear in the nav or card grids')
        return False
    for key in fields:
        if key not in CUSTOM_FIELDS:
            report('WARN', slug, f'custom_fields.{key} is not read by the plugin')
    for key in ('page_type', 'nav_category', 'nav_order', 'nav_label'):
        if not fields.get(key):
            report('ERROR', slug, f'custom_fields.{key} is missing or empty')
    ptype = fields.get('page_type', '')
    if ptype and ptype not in PAGE_TYPES:
        report('ERROR', slug, f'page_type "{ptype}" is not one of {sorted(PAGE_TYPES)}')
    cat = fields.get('nav_category', '')
    if cat and cat not in NAV_CATEGORIES:
        report('WARN', slug, f'nav_category "{cat}" is new: it renders after Admin & API unless added to $section_order in gki_docs_get_nav_structure()')
    order = fields.get('nav_order', '')
    if order and not re.fullmatch(r'-?\d+', order):
        report('ERROR', slug, f'nav_order "{order}" must be an integer (the plugin casts it with (int))')
    if cat != 'hidden':
        color = fields.get('card_color', '')
        if color not in CARD_COLORS:
            report('ERROR', slug, f'card_color "{color}" must be one of {sorted(CARD_COLORS)}')
        if not fields.get('card_icon'):
            report('WARN', slug, 'card_icon is empty; the card renders with no icon (Tabler icon name without the ti- prefix)')
        elif fields['card_icon'].startswith('ti-'):
            report('ERROR', slug, 'card_icon must not include the ti- prefix')
        if not fields.get('card_description'):
            report('WARN', slug, 'card_description is empty; it is the card subtitle and part of the search index')
    return True


def check_hierarchy(pages):
    siblings = defaultdict(list)
    for slug, page in pages.items():
        fields = cf(page)
        if not fields:
            continue
        parent = fields.get('nav_parent', '')
        ptype = fields.get('page_type', '')
        cat = fields.get('nav_category', '')
        if parent:
            if parent not in pages:
                report('ERROR', slug, f'nav_parent "{parent}" is not a page slug (file name without .md); the page drops out of the nav')
            else:
                pf = cf(pages[parent])
                if pf.get('page_type') != 'index':
                    report('ERROR', slug, f'nav_parent "{parent}" is not an index page')
                if pf.get('nav_category') != cat:
                    report('ERROR', slug, f'nav_category "{cat}" differs from its parent\'s "{pf.get("nav_category")}"')
                if pf.get('nav_parent') and pages.get(pf['nav_parent']) and cf(pages[pf['nav_parent']]).get('nav_parent'):
                    report('ERROR', slug, 'nested deeper than section > sub-index > page; the nav renders three levels only')
            # walk for cycles
            seen, cur = {slug}, parent
            while cur and cur in pages:
                if cur in seen:
                    report('ERROR', slug, 'nav_parent chain loops back on itself')
                    break
                seen.add(cur)
                cur = cf(pages[cur]).get('nav_parent', '')
        if ptype == 'main-index' or cat == 'hidden':
            continue
        key = parent or ('section-index' if ptype == 'index' else f'section:{cat}')
        if key == f'section:{cat}':
            # un-parented children of a section sit beside the index's nav_parent children
            idx = [s for s, p in pages.items() if cf(p).get('nav_category') == cat and cf(p).get('page_type') == 'index' and not cf(p).get('nav_parent')]
            if idx:
                key = idx[0]
        siblings[key].append((fields.get('nav_order', ''), slug))
    mains = [s for s, p in pages.items() if cf(p).get('page_type') == 'main-index']
    if len(mains) != 1:
        report('ERROR', '(site)', f'expected exactly one main-index page, found {len(mains)}: {mains}')
    for cat in {cf(p).get('nav_category') for p in pages.values()} - {'home', 'hidden', None, ''}:
        idx = [s for s, p in pages.items() if cf(p).get('nav_category') == cat and cf(p).get('page_type') == 'index' and not cf(p).get('nav_parent')]
        if len(idx) != 1:
            report('ERROR', '(site)', f'nav_category "{cat}" needs exactly one un-parented index page, found {len(idx)}: {idx}')
    for key, items in siblings.items():
        by_order = defaultdict(list)
        for order, slug in items:
            by_order[order].append(slug)
        for order, slugs in by_order.items():
            if len(slugs) > 1:
                for s in slugs:
                    report('WARN', s, f'shares nav_order {order} with {[x for x in slugs if x != s]} under the same parent; order between them is not defined')


def check_body(slug, page, pages):
    fields = cf(page)
    body = page['body']
    ptype = fields.get('page_type', 'content')
    cat = fields.get('nav_category', '')
    prose = strip_code(body)

    if (ptype in ('index', 'main-index') and re.search(r'^##\s', prose, re.M)
            and not re.search(r'^(---|\*\*\*|___)\s*$', prose, re.M)):
        report('WARN', slug, 'index page has sections but no --- rule; the card grid renders after all of them instead of after the intro')

    if cat == 'metrics' and ptype == 'content':
        check_metric(slug, prose, pages, fields)

    if re.search(r'^#\s', prose, re.M):
            report('WARN', slug, 'contains an H1 (#); the template already prints the page title as the H1')

    if re.search(r'<script\b', body, re.I):
        report('ERROR', slug, 'contains <script>; scripts in content are not supported')

    # internal links
    for m in re.finditer(r'(?:https://help\.gitkraken\.com)?/gk-insights/([a-z0-9-]+)/?(#[^)\s"]*)?', prose):
        target = m.group(1)
        if target in ('login',):
            continue
        if target in LEGACY_SLUGS:
            report('INFO', slug, f'links to classic page /gk-insights/{target} (archived in Legacy-Insights-Docs-Archive/, still published)')
        elif target not in pages:
            report('ERROR', slug, f'links to /gk-insights/{target}, which is not a page in gk-insights/')
    # images
    # Root-relative uploads map to _images/ (as in preview/build.py); absolute
    # URLs on other hosts (Home's marketing graphics) are not checked.
    for m in re.finditer(r'(?<![\w.])/wp-content/uploads/([^\s"\')]+)', prose):
        name = m.group(1)
        if not os.path.exists(os.path.join(IMAGES, name)):
            report('ERROR', slug, f'image {name} is not in _images/')
    for m in re.finditer(r'!\[[^\]]*\]\((/[^)]+)\)', prose):
        report('WARN', slug, f'Markdown image ![...]({m.group(1)}); the site uses <figure><img class="help-center-img ..."> with a <figcaption>')


def check_metric(slug, prose, pages, fields):
    parent = fields.get('nav_parent', '')
    if not parent or cf(pages.get(parent, {'fm': {}})).get('page_type') != 'index':
        report('ERROR', slug, 'metric page needs nav_parent set to its family sub-index (e.g. ai-adoption-dora-metrics), or it is missing from the Home metric map')
    lines = prose.splitlines()
    first_h3 = next((i for i, l in enumerate(lines) if l.startswith('### ')), len(lines))
    head = '\n'.join(lines[:first_h3])
    bq = re.search(r'^>\s*(.+)$', head, re.M)
    if not bq:
        report('WARN', slug, 'no > _definition_ blockquote before the first H3; the definition lede will be missing')
    elif len(re.sub(r'[_*>]', '', bq.group(1)).strip()) >= 400:
        report('WARN', slug, 'definition blockquote is 400+ characters; the plugin will not lift it as the lede')
    if not re.search(r'^\*\*Family:\*\*', head, re.M):
        report('WARN', slug, 'no **Family:** ... line before the first H3; the spec panel will be missing')
    h3s = [l[4:].strip() for l in lines if l.startswith('### ')]
    for label, rx in METRIC_SECTIONS:
        if not any(re.search(rx, h, re.I) for h in h3s):
            report('WARN', slug, f'no "{label}" H3 (canonical metric section)')
    for h in h3s:
        if not any(re.search(rx, h, re.I) for _, rx in METRIC_SECTIONS):
            report('INFO', slug, f'H3 "{h}" is not a canonical section; it travels with the section before it')


def main(argv):
    pages = load_pages()
    if not pages:
        print(f'No pages found in {CONTENT}')
        return 1
    only = None
    if argv:
        only = {os.path.splitext(os.path.basename(a))[0] for a in argv}
        unknown = only - set(pages)
        for u in sorted(unknown):
            report('ERROR', u, 'not found in gk-insights/ (or its name starts with _ and Git It Write skips it)')
    for slug, page in pages.items():
        if check_front_matter(slug, page):
            check_body(slug, page, pages)
    check_hierarchy(pages)

    shown = [p for p in problems if only is None or p[1] in only or p[1] == '(site)']
    order = {'ERROR': 0, 'WARN': 1, 'INFO': 2}
    for level, slug, msg in sorted(shown, key=lambda p: (order[p[0]], p[1])):
        print(f'{level:5} {slug}: {msg}')
    errors = sum(1 for p in shown if p[0] == 'ERROR')
    warns = sum(1 for p in shown if p[0] == 'WARN')
    scope = f'{len(only)} page(s)' if only else f'{len(pages)} pages'
    print(f'\n{scope} checked: {errors} error(s), {warns} warning(s).')
    return 1 if errors else 0


if __name__ == '__main__':
    sys.exit(main(sys.argv[1:]))
