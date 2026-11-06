#!/usr/bin/env python3
"""Live help center nav audit.

Reads every product help center's sidebar menu from help.gitkraken.com exactly
as rendered, verifies it, and maps every docs-repo page against it.

Read-only against the live site (plain GET requests, one at a time, with a
delay) and against the repos (git show on a ref; working trees are not used).

Nothing is inferred:
  * The nav tree is the rendered <ul id="menu-..."> with its WordPress menu-item
    IDs, labels, hrefs and nesting, saved verbatim as evidence.
  * The sidebar is re-extracted from every page it links to and compared, so a
    page with a different menu is reported, not assumed away.
  * A repo file is mapped to a live URL only when that URL answers 200 at the
    file's expected path AND is the same URL the nav links to. Anything less is
    reported as unconfirmed with the evidence, never matched by similarity.
  * Any request that fails after retries is listed, and the run is marked
    INCOMPLETE.

Usage:
  python -I nav_audit.py --out <dir> [--products gitlens,cli] [--delay 0.5]
"""

import argparse
import csv
import datetime as dt
import gzip
import hashlib
import html
import json
import os
import re
import subprocess
import sys
import time
import unicodedata
import urllib.error
import urllib.parse
import urllib.request
from html.parser import HTMLParser

SITE = "https://help.gitkraken.com"
UA = "Mozilla/5.0 (compatible; GitKraken-docs-nav-audit/1.0)"
GLOBAL_HEADER_WIDGET = "13b1f87"  # site-wide header menu, same on every page
WT = r"c:\Users\robertov\kepler\worktrees"

PRODUCTS = {
    "desktop": dict(name="GitKraken Desktop", url_cat="gitkraken-desktop", home="gitkraken-desktop-home",
                    sidebar="menu-gitkraken-desktop-menu", repo=WT + r"\gitkr-audit-5f16f", dir="gitkraken-desktop"),
    "gitlens": dict(name="GitLens", url_cat="gitlens", home="gitlens-home",
                    sidebar="menu-gitlens-menu", repo=WT + r"\gitle-docs-0a938", dir="gitlens"),
    "kepler": dict(name="Kepler", url_cat="kepler", home="kepler-getting-started",
                   sidebar="menu-kepler", repo=WT + r"\keple-keple-b20d4", dir="kepler"),
    "mcp": dict(name="GitKraken MCP", url_cat="mcp", home="mcp-getting-started",
                sidebar="menu-mcp-menu", repo=WT + r"\MCP-D-explo-aa4bf", dir="MCP"),
    "cli": dict(name="GitKraken CLI", url_cat="cli", home="cli-home",
                sidebar="menu-gitkraken-cli", repo=WT + r"\gk-cl-help-ceb6a", dir="cli"),
    "gkdev": dict(name="gitkraken.dev", url_cat="gk-dev", home="gk-dev-home",
                  sidebar="menu-gk-dev-menu", repo=WT + r"\gk-de-help-c4fa3", dir="gk-dev"),
    "gijc": dict(name="Git Integration for Jira Cloud", url_cat="git-integration-for-jira-cloud",
                 home="git-integration-for-jira-home-gij-cloud",
                 sidebar="menu-git-integration-for-jira-cloud-menu", repo=WT + r"\git-i-gk-he-8802f",
                 dir="git-integration-for-jira-cloud"),
    "gijdc": dict(name="Git Integration for Jira Data Center", url_cat="git-integration-for-jira-data-center",
                  home="git-integration-for-jira-home-self-manged",
                  sidebar="menu-git-integration-for-jira-server-data-center-menu", repo=WT + r"\git-i-explo-35bad",
                  dir="git-integration-for-jira-self-managed"),
}

VOID = {"area", "base", "br", "col", "embed", "hr", "img", "input", "link", "meta", "param", "source", "track", "wbr"}


# --------------------------------------------------------------------------- HTML tree

class Node:
    __slots__ = ("tag", "attrs", "children", "parent", "start", "end")

    def __init__(self, tag, attrs, parent, start):
        self.tag, self.attrs, self.children, self.parent = tag, attrs, [], parent
        self.start, self.end = start, None

    def classes(self):
        return (self.attrs.get("class") or "").split()

    def text(self):
        out = []
        for c in self.children:
            out.append(c if isinstance(c, str) else c.text())
        return "".join(out)

    def elements(self):
        return [c for c in self.children if isinstance(c, Node)]

    def walk(self):
        for c in self.elements():
            yield c
            yield from c.walk()


class TreeBuilder(HTMLParser):
    """Stack-based tree builder. Records source offsets so a menu's exact HTML
    can be saved as evidence. Reports any end tag that had to close other open
    elements, so malformed markup around a menu is visible rather than silent."""

    def __init__(self, src):
        super().__init__(convert_charrefs=True)
        self.src = src
        self.root = Node("#root", {}, None, 0)
        self.cur = self.root
        self.irregular = []
        # line/col -> absolute offset
        self.line_starts = [0]
        for m in re.finditer("\n", src):
            self.line_starts.append(m.end())

    def _pos(self):
        line, col = self.getpos()
        return self.line_starts[line - 1] + col

    def handle_starttag(self, tag, attrs):
        n = Node(tag, dict(attrs), self.cur, self._pos())
        self.cur.children.append(n)
        if tag not in VOID:
            self.cur = n
        else:
            n.end = self._pos()

    def handle_startendtag(self, tag, attrs):
        n = Node(tag, dict(attrs), self.cur, self._pos())
        n.end = self._pos()
        self.cur.children.append(n)

    def handle_endtag(self, tag):
        if tag in VOID:
            return
        node, skipped = self.cur, []
        while node is not self.root and node.tag != tag:
            skipped.append(node.tag)
            node = node.parent
        if node is self.root:
            self.irregular.append(("stray-end", tag, self._pos()))
            return
        if skipped:
            self.irregular.append(("implicit-close", tag, skipped, self._pos()))
        end = self.src.find(">", self._pos()) + 1
        n = self.cur
        while n is not node:
            n.end = end
            n = n.parent
        node.end = end
        self.cur = node.parent

    def handle_data(self, data):
        self.cur.children.append(data)


def parse(src):
    b = TreeBuilder(src)
    b.feed(src)
    b.close()
    return b


def norm(s):
    return re.sub(r"\s+", " ", html.unescape(s or "")).strip()


# --------------------------------------------------------------------------- menus

ITEM_ID = re.compile(r"^menu-item-(\d+)$")


def menu_items(ul, depth=0, parent=None):
    """Direct <li> children of a menu <ul>, recursively. Every anomaly (a non-li
    child, an li without an item id, more than one link or sub-menu) is kept on
    the item rather than dropped."""
    items = []
    for pos, li in enumerate([e for e in ul.elements()], start=1):
        anomalies = []
        if li.tag != "li":
            anomalies.append(f"non-li child <{li.tag}> in menu list")
        cls = li.classes()
        ids = [ITEM_ID.match(c).group(1) for c in cls if ITEM_ID.match(c)]
        if len(ids) != 1:
            anomalies.append(f"menu-item id classes found: {ids}")
        links = [e for e in li.elements() if e.tag == "a"]
        subs = [e for e in li.elements() if e.tag == "ul"]
        others = [e.tag for e in li.elements() if e.tag not in ("a", "ul")]
        if len(links) != 1:
            anomalies.append(f"{len(links)} direct <a> elements")
        if len(subs) > 1:
            anomalies.append(f"{len(subs)} direct sub-menus")
        if others:
            anomalies.append(f"other direct children: {others}")
        a = links[0] if links else None
        item = dict(
            item_id=ids[0] if len(ids) == 1 else None,
            depth=depth,
            position=pos,
            parent_item_id=parent,
            label=norm(a.text()) if a else "",
            href=(a.attrs.get("href") if a else None),
            target=(a.attrs.get("target") if a else None),
            type=next((c[len("menu-item-type-"):] for c in cls if c.startswith("menu-item-type-")), None),
            object=next((c[len("menu-item-object-"):] for c in cls if c.startswith("menu-item-object-")), None),
            has_children_class="menu-item-has-children" in cls,
            anomalies=anomalies,
            children=[],
        )
        if subs:
            item["children"] = menu_items(subs[0], depth + 1, item["item_id"])
        if item["has_children_class"] != bool(item["children"]):
            anomalies.append("menu-item-has-children class disagrees with rendered sub-menu")
        items.append(item)
    return items


def flatten(items):
    for it in items:
        yield it
        yield from flatten(it["children"])


def signature(items):
    """Order-sensitive fingerprint of a menu: id, depth, parent, label, href."""
    rows = [(i["item_id"], i["depth"], i["parent_item_id"], i["label"], i["href"]) for i in flatten(items)]
    return hashlib.sha256(json.dumps(rows, ensure_ascii=False).encode()).hexdigest(), rows


def find_menus(tree):
    return {n.attrs["id"]: n for n in tree.root.walk() if n.tag == "ul" and (n.attrs.get("id") or "").startswith("menu-")}


def page_facts(src, tree):
    body = next((n for n in tree.root.walk() if n.tag == "body"), None)
    bcls = body.classes() if body else []
    postid = next((c[7:] for c in bcls if c.startswith("postid-")), None)
    canon = next((n.attrs.get("href") for n in tree.root.walk() if n.tag == "link" and n.attrs.get("rel") == "canonical"), None)
    robots = next((n.attrs.get("content") for n in tree.root.walk() if n.tag == "meta" and n.attrs.get("name") == "robots"), None)
    title_el = next((n for n in tree.root.walk() if n.tag == "title"), None)
    h1 = None
    for n in tree.root.walk():
        if n.attrs.get("data-widget_type", "").startswith("theme-post-title"):
            h = next((e for e in n.walk() if e.tag in ("h1", "h2")), None)
            h1 = norm(h.text()) if h else None
            break
    content = next((n for n in tree.root.walk() if n.attrs.get("data-widget_type", "").startswith("theme-post-content")), None)
    links = []
    if content is not None:
        links = [a.attrs.get("href") for a in content.walk() if a.tag == "a" and a.attrs.get("href")]
    return dict(post_id=postid, body_classes=" ".join(bcls), canonical=canon, robots=robots,
                title=norm(title_el.text()) if title_el else None, h1=h1,
                has_content_widget=content is not None, content_links=links)


# --------------------------------------------------------------------------- fetching

class Fetcher:
    def __init__(self, cache_dir, delay):
        self.cache_dir, self.delay, self.failures, self.count = cache_dir, delay, [], 0
        os.makedirs(cache_dir, exist_ok=True)
        self.mem = {}

    def get(self, url):
        if url in self.mem:
            return self.mem[url]
        key = hashlib.sha1(url.encode()).hexdigest()
        path = os.path.join(self.cache_dir, key + ".json.gz")
        if os.path.exists(path):
            with gzip.open(path, "rt", encoding="utf8") as f:
                res = json.load(f)
            self.mem[url] = res
            return res
        res, err = None, None
        for attempt in range(4):
            time.sleep(self.delay * (1 if attempt == 0 else 4 * attempt))
            self.count += 1
            try:
                req = urllib.request.Request(url, headers={"User-Agent": UA})
                with urllib.request.urlopen(req, timeout=45) as r:
                    res = dict(url=url, status=r.status, final_url=r.geturl(), body=r.read().decode("utf8", "replace"))
                break
            except urllib.error.HTTPError as e:
                if e.code in (429, 500, 502, 503, 504):
                    err = f"HTTP {e.code}"
                    continue
                res = dict(url=url, status=e.code, final_url=e.geturl() or url, body="")
                break
            except Exception as e:  # network error: retry
                err = repr(e)
        if res is None:
            self.failures.append((url, err))
            res = dict(url=url, status=None, final_url=None, body="", error=err)
            self.mem[url] = res
            return res  # failures are not cached, so a re-run retries them
        with gzip.open(path, "wt", encoding="utf8") as f:
            json.dump(res, f)
        self.mem[url] = res
        return res


# --------------------------------------------------------------------------- repo

def sanitize_title(s):
    """WordPress sanitize_title_with_dashes() for ASCII filenames: the rule
    WordPress applies to a post_name. Used only to build the URL to *test*;
    a mapping is accepted only if that URL answers 200 at itself."""
    s = unicodedata.normalize("NFKD", s).encode("ascii", "ignore").decode()
    s = re.sub(r"<[^>]*>", "", s)
    s = re.sub(r"%(?![0-9a-fA-F]{2})", "", s)
    s = s.lower()
    s = re.sub(r"&.+?;", "", s)
    s = s.replace(".", "-")
    s = re.sub(r"[^%a-z0-9 _-]", "", s)
    s = re.sub(r"\s+", "-", s)
    s = re.sub(r"-+", "-", s)
    return s.strip("-")


def git(repo, *args):
    return subprocess.run(["git", "-C", repo, *args], capture_output=True, text=True, encoding="utf8", check=True).stdout


def repo_pages(p, ref):
    sha = git(p["repo"], "rev-parse", ref).strip()
    names = [n for n in git(p["repo"], "ls-tree", "-r", "--name-only", ref, "--", p["dir"]).splitlines() if n.lower().endswith(".md")]
    out = []
    for n in names:
        src = git(p["repo"], "show", f"{ref}:{n}")
        fm = re.match(r"^\ufeff?\s*---\s*\n(.*?)\n---", src, re.S)
        fm = fm.group(1) if fm else ""
        t = re.search(r"^title:\s*(.*)$", fm, re.M)
        c = re.search(r"^\s+category:\s*(\S.*)$", fm, re.M)
        st = re.search(r"^status:\s*(.*)$", fm, re.M)
        stem = os.path.splitext(os.path.basename(n))[0]
        out.append(dict(path=n, stem=stem,
                        title=t.group(1).strip().strip("\"'") if t else None,
                        category=c.group(1).strip().strip("\"'") if c else None,
                        status=st.group(1).strip().strip("\"'") if st else None,
                        has_frontmatter=bool(fm)))
    return sha, out


# --------------------------------------------------------------------------- URL helpers

def canon_url(href, base):
    if not href:
        return None
    u = urllib.parse.urljoin(base, html.unescape(href.strip()))
    s = urllib.parse.urlsplit(u)
    if s.scheme not in ("http", "https"):
        return None
    path = s.path or "/"
    if not path.endswith("/") and "." not in path.rsplit("/", 1)[-1]:
        path += "/"
    return urllib.parse.urlunsplit(("https", s.netloc.lower(), path, "", ""))


def same_site(u):
    return u and urllib.parse.urlsplit(u).netloc in ("help.gitkraken.com",)


def write_csv(path, rows, fields):
    with open(path, "w", newline="", encoding="utf8") as f:
        w = csv.DictWriter(f, fieldnames=fields, extrasaction="ignore")
        w.writeheader()
        for r in rows:
            w.writerow(r)


def tree_md(items, out, pages):
    for it in items:
        flag = "" if not it["anomalies"] else "  ⚠ " + "; ".join(it["anomalies"])
        pg = pages.get(it.get("url")) or {}
        if pg.get("status") not in (None, 200):
            flag += f"  ⚠ HTTP {pg.get('status')}"
        elif pg.get("redirected"):
            flag += f"  ⚠ redirects to {pg.get('final_url')}"
        href = it["href"] or "(no href)"
        out.append(f"{'    ' * it['depth']}- **{it['label'] or '(empty label)'}** — `{href}` · item {it['item_id']} · {it['type']}/{it['object']}{flag}")
        tree_md(it["children"], out, pages)


# --------------------------------------------------------------------------- audit

def audit(key, p, F, ref, out_dir, run_meta):
    pdir = os.path.join(out_dir, key)
    os.makedirs(pdir, exist_ok=True)
    home_url = f"{SITE}/{p['url_cat']}/{p['home']}/"
    home = F.get(home_url)
    problems = []
    if home["status"] != 200:
        problems.append(f"home page {home_url} returned {home['status']}")
        json.dump(dict(problems=problems), open(os.path.join(pdir, "problems.json"), "w"), indent=2)
        return dict(key=key, problems=problems)

    tree = parse(home["body"])
    menus = find_menus(tree)
    if p["sidebar"] not in menus:
        problems.append(f"sidebar {p['sidebar']} not found on home; menus present: {sorted(menus)}")
        return dict(key=key, problems=problems)
    side = menus[p["sidebar"]]
    items = menu_items(side)
    sig, rows = signature(items)

    # Evidence: the sidebar's exact rendered HTML.
    with open(os.path.join(pdir, "sidebar-rendered.html"), "w", encoding="utf8") as f:
        f.write(home["body"][side.start:side.end])

    # Cross-check the other renders of a product menu on the same page.
    copies = {}
    for mid, ul in menus.items():
        if mid == p["sidebar"] or GLOBAL_HEADER_WIDGET in mid:
            continue
        s2, r2 = signature(menu_items(ul))
        copies[mid] = dict(identical=(s2 == sig), items=len(r2),
                           diff=None if s2 == sig else [x for x in r2 if x not in rows][:20] + [("MISSING", x) for x in rows if x not in r2][:20])

    flat = list(flatten(items))
    nav_urls = {}
    for it in flat:
        href = (it["href"] or "").strip()
        it["url"] = None if (not href or href.startswith("#")) else canon_url(href, home_url)
        it["anomalies_text"] = "; ".join(it["anomalies"])
        if it["url"] and same_site(it["url"]):
            nav_urls.setdefault(it["url"], []).append(it["item_id"])

    # Visit every page the nav links to; re-extract the sidebar on each.
    pages = {}
    for u in nav_urls:
        r = F.get(u)
        rec = dict(url=u, status=r["status"], final_url=r.get("final_url"), redirected=bool(r.get("final_url")) and canon_url(r["final_url"], u) != u,
                   error=r.get("error"))
        if r["body"]:
            t = parse(r["body"])
            rec.update(page_facts(r["body"], t))
            m = find_menus(t)
            if p["sidebar"] in m:
                s3, _ = signature(menu_items(m[p["sidebar"]]))
                rec["sidebar_on_page"] = "identical" if s3 == sig else "DIFFERENT"
            else:
                others = [k for k in m if GLOBAL_HEADER_WIDGET not in k and not re.match(r"menu-[12]-", k)]
                rec["sidebar_on_page"] = "MISSING" + (f" (has {others})" if others else "")
            path = urllib.parse.urlsplit(canon_url(r["final_url"], u) or u).path.strip("/").split("/")
            rec["url_category"] = path[0] if path else None
        pages[u] = rec

    # Repo inventory and exact mapping.
    sha, files = repo_pages(p, ref)
    run_meta["repos"][key] = dict(repo=p["repo"], ref=ref, sha=sha, dir=p["dir"], files=len(files))
    by_url = {}
    for fdesc in files:
        cat = (fdesc["category"] or "").lower()
        fdesc["expected_url"] = f"{SITE}/{cat}/{sanitize_title(fdesc['stem'])}/" if cat else None
        u = fdesc["expected_url"]
        if not u:
            fdesc["live_status"] = None
            continue
        r = F.get(u)
        fdesc["live_status"] = r["status"]
        fdesc["live_final_url"] = r.get("final_url")
        fdesc["live_redirected"] = bool(r.get("final_url")) and canon_url(r["final_url"], u) != u
        if r["body"]:
            fa = page_facts(r["body"], parse(r["body"]))
            fdesc.update({f"live_{k}": v for k, v in fa.items() if k != "content_links"})
            pages.setdefault(u, dict(url=u, status=r["status"], final_url=r.get("final_url"), **fa))
            pages[u].setdefault("content_links", fa["content_links"])
        by_url.setdefault(u, []).append(fdesc)

    # Post IDs: the exact identity used for every match below.
    nav_pids = {}
    for u, ids in nav_urls.items():
        pg = pages.get(u, {})
        if pg.get("status") == 200 and pg.get("post_id"):
            nav_pids.setdefault(pg["post_id"], []).append(u)
    by_pid = {}
    for fdesc in files:
        if fdesc.get("live_status") == 200 and not fdesc.get("live_redirected") and fdesc.get("live_post_id"):
            by_pid.setdefault(fdesc["live_post_id"], []).append(fdesc)

    return dict(nav_pids=nav_pids, by_pid=by_pid,key=key, p=p, pdir=pdir, home_url=home_url, items=items, flat=flat, sig=sig, copies=copies,
                nav_urls=nav_urls, pages=pages, files=files, by_url=by_url, problems=problems,
                irregular=tree.irregular)


def resolve_pid(url, known, F):
    """Post ID a same-site link lands on. Known pages answer from what was
    already fetched; anything else inside the audited help centers is fetched.
    Returns None for links outside them or that do not resolve to a post."""
    if url in known:
        return known[url]
    first = urllib.parse.urlsplit(url).path.strip("/").split("/")[0].lower()
    if first not in {p["url_cat"] for p in PRODUCTS.values()}:
        return None
    r = F.get(url)
    pid = None
    if r.get("status") == 200 and r["body"]:
        m = re.search(r'<body[^>]*class="([^"]*)"', r["body"])
        pid = next((c[7:] for c in (m.group(1).split() if m else []) if c.startswith("postid-")), None)
    known[url] = pid
    return pid


def classify_and_write(results, out_dir, run_meta, F):
    known = {}
    for R in results:
        for u, rec in (R.get("pages") or {}).items():
            if rec.get("status") == 200 and rec.get("post_id"):
                known[u] = rec["post_id"]

    # Nav membership by post ID, across all help centers.
    all_nav = {}
    for R in results:
        for pid in (R.get("nav_pids") or {}):
            all_nav.setdefault(pid, []).append(R["key"])
    pid_url = {}
    for R in results:
        for u, rec in (R.get("pages") or {}).items():
            if rec.get("post_id"):
                pid_url.setdefault(rec["post_id"], u)

    # Inbound content links from every audited page, resolved to post IDs.
    inbound = {}
    for R in results:
        for u, rec in (R.get("pages") or {}).items():
            if rec.get("status") != 200 or not rec.get("post_id"):
                continue
            for h in rec.get("content_links") or []:
                if h.strip().startswith("#"):
                    continue
                t = canon_url(h, u)
                if not t or not same_site(t):
                    continue
                pid = resolve_pid(t, known, F)
                if pid and pid != rec["post_id"]:
                    inbound.setdefault(pid, set()).add(rec["post_id"])

    summary_rows = []
    for R in results:
        key, pdir = R["key"], os.path.join(out_dir, R["key"])
        os.makedirs(pdir, exist_ok=True)
        if "items" not in R:
            summary_rows.append(dict(key=key, problems="; ".join(R["problems"])))
            continue
        p = R["p"]

        # nav-tree.json / nav-tree.md
        json.dump(dict(product=p["name"], sidebar_menu=p["sidebar"], home=R["home_url"], fingerprint=R["sig"],
                       captured_utc=run_meta["started_utc"], items=R["items"]),
                  open(os.path.join(pdir, "nav-tree.json"), "w", encoding="utf8"), indent=2, ensure_ascii=False)
        md = [f"# {p['name']} — live sidebar nav", "",
              f"Menu `{p['sidebar']}` as rendered on {R['home_url']} at {run_meta['started_utc']} UTC.",
              f"{len(R['flat'])} items, max depth {max(i['depth'] for i in R['flat']) + 1 if R['flat'] else 0}. Fingerprint `{R['sig'][:16]}`.", ""]
        tree_md(R["items"], md, R["pages"])
        open(os.path.join(pdir, "nav-tree.md"), "w", encoding="utf8").write("\n".join(md) + "\n")

        # nav-items.csv: one row per menu item, with the page facts and the repo file it maps to.
        nav_rows = []
        for it in R["flat"]:
            pg = R["pages"].get(it["url"], {}) if it["url"] else {}
            matches = R["by_pid"].get(pg.get("post_id"), []) if pg.get("status") == 200 and pg.get("post_id") else []
            if not it["url"]:
                h = (it["href"] or "").strip()
                repo_map = "n/a (in-page anchor)" if len(h) > 1 and h.startswith("#") else "n/a (label only, no link)"
            elif not same_site(it["url"]):
                repo_map = "n/a (external link)"
            elif pg.get("status") != 200:
                repo_map = f"n/a (link answers HTTP {pg.get('status')})"
            elif pg.get("redirected"):
                rm = R["by_pid"].get(pg.get("post_id"), [])
                repo_map = f"REDIRECTS to {pg.get('final_url')}" + (f" (served by {rm[0]['path']})" if len(rm) == 1 else "")
            elif pg.get("url_category") and pg.get("url_category") != p["url_cat"]:
                repo_map = f"n/a (page in another help center: {pg.get('url_category')})"
            elif len(matches) == 1:
                repo_map = matches[0]["path"]
            elif len(matches) > 1:
                repo_map = "AMBIGUOUS: " + " | ".join(m["path"] for m in matches)
            else:
                repo_map = "NO REPO FILE for this post"
            nav_rows.append(dict(item_id=it["item_id"], depth=it["depth"], position=it["position"], parent_item_id=it["parent_item_id"],
                                 label=it["label"], href=it["href"], type=it["type"], object=it["object"],
                                 http_status=pg.get("status"), redirected_to=pg.get("final_url") if pg.get("redirected") else "",
                                 post_id=pg.get("post_id"), page_h1=pg.get("h1"), robots=pg.get("robots"),
                                 sidebar_on_page=pg.get("sidebar_on_page"), repo_file=repo_map, anomalies=it["anomalies_text"]))
        write_csv(os.path.join(pdir, "nav-items.csv"), nav_rows, list(nav_rows[0].keys()) if nav_rows else ["item_id"])

        # repo-pages.csv: every repo file, classified.
        repo_rows = []
        for f in R["files"]:
            u = f.get("expected_url")
            pid = f.get("live_post_id") if f.get("live_status") == 200 and not f.get("live_redirected") else None
            in_nav_here = bool(pid) and pid in R["nav_pids"]
            in_nav_any = all_nav.get(pid, []) if pid else []
            links_in = sorted(inbound.get(pid, set())) if pid else []
            linkers_in_nav = [x for x in links_in if x in all_nav]
            if not f["has_frontmatter"] or not f["category"]:
                cls = "CHECK: no frontmatter category (cannot build URL)"
            elif f["live_status"] is None:
                cls = "CHECK: request failed"
            elif f["live_status"] != 200:
                cls = f"NOT LIVE at expected URL (HTTP {f['live_status']})"
            elif f.get("live_redirected"):
                cls = "CHECK: expected URL redirects"
            elif in_nav_here:
                cls = "IN NAV"
            elif in_nav_any:
                cls = "IN ANOTHER HELP CENTER'S NAV"
            elif linkers_in_nav:
                cls = "LIVE, NOT IN NAV, LINKED FROM A NAV PAGE"
            elif links_in:
                cls = "LIVE, NOT IN NAV, LINKED ONLY FROM NON-NAV PAGES"
            else:
                cls = "LIVE, NOT IN NAV, NO INBOUND LINKS FOUND"
            title_check = ""
            if f.get("live_h1") is not None and f["title"] is not None:
                title_check = "same" if norm(f["title"]) == norm(f["live_h1"]) else "differs"
            repo_rows.append(dict(classification=cls, repo_file=f["path"], frontmatter_title=f["title"],
                                  frontmatter_category=f["category"], frontmatter_status=f["status"],
                                  expected_url=u, http_status=f["live_status"],
                                  redirected_to=f.get("live_final_url") if f.get("live_redirected") else "",
                                  post_id=f.get("live_post_id"), live_h1=f.get("live_h1"), title_vs_h1=title_check,
                                  robots=f.get("live_robots"), in_nav_of=",".join(in_nav_any),
                                  inbound_links_from_nav_pages=" ".join(pid_url.get(x, "post " + x) for x in linkers_in_nav),
                                  inbound_links_from_other_pages=" ".join(pid_url.get(x, "post " + x) for x in links_in if x not in linkers_in_nav)))
        repo_rows.sort(key=lambda r: (r["classification"], r["repo_file"]))
        write_csv(os.path.join(pdir, "repo-pages.csv"), repo_rows, list(repo_rows[0].keys()) if repo_rows else ["repo_file"])

        # Per-product summary.
        counts = {}
        for r in repo_rows:
            counts[r["classification"]] = counts.get(r["classification"], 0) + 1
        nav_no_repo = [r for r in nav_rows if r["repo_file"] == "NO REPO FILE for this post"]
        sidebar_diff = [r for r in nav_rows if r["sidebar_on_page"] not in (None, "identical")]
        anomalies = [r for r in nav_rows if r["anomalies"]]
        bad_status = [r for r in nav_rows if r["http_status"] not in (None, 200)]
        s = [f"# {p['name']} — nav audit", "",
             f"- Captured: {run_meta['started_utc']} UTC from {R['home_url']}",
             f"- Sidebar menu: `{p['sidebar']}`; {len(R['flat'])} items; fingerprint `{R['sig'][:16]}`",
             f"- Repo: `{run_meta['repos'][R['key']]['repo']}` at `{run_meta['repos'][R['key']]['ref']}` ({run_meta['repos'][R['key']]['sha'][:7]}), folder `{p['dir']}/`, {len(R['files'])} .md files",
             "", "## Checks", "",
             f"- Other renders of this menu on the home page: " + (", ".join(f"`{k}` {'identical' if v['identical'] else 'DIFFERENT'}" for k, v in R["copies"].items()) or "none"),
             f"- Sidebar re-extracted on {sum(1 for r in nav_rows if r['sidebar_on_page'])} linked pages: {len(sidebar_diff)} not identical",
             f"- Nav links not answering 200: {len(bad_status)}",
             f"- Menu items with structural anomalies: {len(anomalies)}",
             f"- HTML irregularities on the home page: {len(R.get('irregular') or [])}",
             f"- Nav items with no repo file at their URL: {len(nav_no_repo)}",
             "", "## Repo pages by classification", "", "| Classification | Files |", "| --- | --- |"]
        s += [f"| {k} | {v} |" for k, v in sorted(counts.items())]
        if nav_no_repo:
            s += ["", "## Nav items with no repo file", "", "| Item | Label | URL | Post ID |", "| --- | --- | --- | --- |"]
            s += [f"| {r['item_id']} | {r['label']} | {r['href']} | {r['post_id']} |" for r in nav_no_repo]
        if sidebar_diff or bad_status:
            s += ["", "## Pages that need a look", ""]
            s += [f"- item {r['item_id']} {r['label']}: HTTP {r['http_status']}, sidebar {r['sidebar_on_page']} — {r['href']}" for r in sidebar_diff + bad_status]
        open(os.path.join(pdir, "SUMMARY.md"), "w", encoding="utf8").write("\n".join(s) + "\n")
        summary_rows.append(dict(key=key, name=p["name"], items=len(R["flat"]), repo_files=len(R["files"]), counts=counts,
                                 nav_no_repo=len(nav_no_repo), sidebar_diff=len(sidebar_diff), bad_status=len(bad_status),
                                 anomalies=len(anomalies), copies_ok=all(v["identical"] for v in R["copies"].values())))
    return summary_rows


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--out", required=True)
    ap.add_argument("--products", default=",".join(PRODUCTS))
    ap.add_argument("--ref", default="origin/main")
    ap.add_argument("--delay", type=float, default=0.5)
    ap.add_argument("--cache", default=os.path.join(os.path.expanduser("~"), ".cache", "gk-nav-audit"))
    a = ap.parse_args()
    started = dt.datetime.now(dt.timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ")
    out_dir = a.out
    os.makedirs(out_dir, exist_ok=True)
    F = Fetcher(os.path.join(a.cache, started.replace(":", "")), a.delay)
    meta = dict(started_utc=started, site=SITE, ref=a.ref, repos={})
    results = []
    for k in a.products.split(","):
        print(f"[{k}] auditing…", flush=True)
        results.append(audit(k, PRODUCTS[k], F, a.ref, out_dir, meta))
        print(f"[{k}] done ({F.count} requests so far, {len(F.failures)} failures)", flush=True)
    rows = classify_and_write(results, out_dir, meta, F)
    meta.update(finished_utc=dt.datetime.now(dt.timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ"),
                requests=F.count, failures=F.failures, complete=not F.failures, summary=rows)
    json.dump(meta, open(os.path.join(out_dir, "run.json"), "w", encoding="utf8"), indent=2, default=list)
    print(json.dumps(dict(complete=not F.failures, failures=F.failures, summary=rows), indent=2, default=list))


if __name__ == "__main__":
    main()
