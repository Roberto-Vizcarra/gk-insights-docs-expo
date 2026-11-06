#!/usr/bin/env python3
"""Write REPORT.md for one audit run from its CSV/JSON outputs. Every list in
the report is generated from the data files; nothing is typed by hand.

Usage: python -I make_report.py <audit output dir>
"""
import csv
import json
import os
import sys

ORDER = ["desktop", "gitlens", "kepler", "mcp", "cli", "gkdev", "gijc", "gijdc"]


def rows(d, name):
    p = os.path.join(d, name)
    return list(csv.DictReader(open(p, encoding="utf8"))) if os.path.exists(p) else []


def main(out):
    run = json.load(open(os.path.join(out, "run.json"), encoding="utf8"))
    vis = {r["key"]: r for r in json.load(open(os.path.join(out, "visual-check.json"), encoding="utf8"))}
    inv = {r["key"]: r for r in json.load(open(os.path.join(out, "wp-inventory.json"), encoding="utf8"))}
    names = {s["key"]: s["name"] for s in run["summary"]}
    L = [f"# Live nav audit — {run['started_utc'][:10]}", "",
         f"Captured {run['started_utc']} to {run['finished_utc']} UTC from help.gitkraken.com. "
         f"{run['requests']} page requests, {len(run['failures'])} failures; run {'complete' if run['complete'] else 'INCOMPLETE'}. "
         f"Repos read at `{run['ref']}`.", "",
         "## Verification", "",
         "| Help center | Nav items | Max depth | Parser vs tokenizer vs browser | Visible to a reader | Sidebar same on every linked page | Branch screenshots |",
         "| --- | --- | --- | --- | --- | --- | --- |"]
    for k in ORDER:
        d = os.path.join(out, k)
        tree = json.load(open(os.path.join(d, "nav-tree.json"), encoding="utf8"))
        def depth(items):
            return max([1 + depth(i["children"]) for i in items], default=0)
        nav = rows(d, "nav-items.csv")
        same = sum(r["sidebar_on_page"] == "identical" for r in nav)
        checked = sum(bool(r["sidebar_on_page"]) for r in nav)
        v = vis.get(k, {})
        shots = len(os.listdir(os.path.join(d, "branches"))) if os.path.isdir(os.path.join(d, "branches")) else 0
        L.append(f"| {names[k]} | {len(nav)} | {depth(tree['items'])} | {'agree' if v.get('diffs') == 0 else 'DISAGREE'} | "
                 f"{v.get('items', 0) - v.get('hidden', 0)} of {v.get('items', 0)} | {same} of {checked} | {shots} |")
    L += ["", "Pages whose sidebar is not identical are nav links into a different help center or through a redirect; they are listed below.", ""]

    L += ["## Repo pages by classification", "",
          "| Help center | Repo files | In nav | In another nav | Not in nav, linked from a nav page | Not in nav, linked only from non-nav pages | Not in nav, no inbound links | Needs a check |",
          "| --- | --- | --- | --- | --- | --- | --- | --- |"]
    for k in ORDER:
        rp = rows(os.path.join(out, k), "repo-pages.csv")
        c = lambda pre: sum(r["classification"].startswith(pre) for r in rp)  # noqa: E731
        L.append(f"| {names[k]} | {len(rp)} | {c('IN NAV')} | {c('IN ANOTHER')} | {c('LIVE, NOT IN NAV, LINKED FROM')} | "
                 f"{c('LIVE, NOT IN NAV, LINKED ONLY')} | {c('LIVE, NOT IN NAV, NO INBOUND')} | {c('CHECK') + c('NOT LIVE')} |")

    L += ["", "## Repo pages not in any nav", "",
          "Not an archive list. Each still needs the checks in the README (slug collisions, links from the apps and gitkraken.com).", ""]
    for k in ORDER:
        rp = [r for r in rows(os.path.join(out, k), "repo-pages.csv") if r["classification"] not in ("IN NAV",)]
        if not rp:
            continue
        L += [f"### {names[k]}", "", "| Classification | Repo file | Front-matter status | Live URL | Linked from |", "| --- | --- | --- | --- | --- |"]
        for r in rp:
            link = (r["inbound_links_from_nav_pages"] or r["inbound_links_from_other_pages"] or "").split()
            L.append(f"| {r['classification']} | `{r['repo_file']}` | {r['frontmatter_status'] or ''} | {r['redirected_to'] or r['expected_url']} | "
                     + ("<br>".join(link[:3]) + (f" (+{len(link) - 3})" if len(link) > 3 else "") if link else "—") + " |")
        L.append("")

    L += ["## Nav items that need a decision", ""]
    any_ = False
    for k in ORDER:
        bad = [r for r in rows(os.path.join(out, k), "nav-items.csv")
               if not r["repo_file"].endswith(".md") and not r["repo_file"].startswith("n/a (label only")]
        if bad:
            any_ = True
            L += [f"### {names[k]}", "", "| Item | Label | Link | Post | Finding |", "| --- | --- | --- | --- | --- |"]
            L += [f"| {r['item_id']} | {r['label']} | {r['href']} | {r['post_id']} | {r['repo_file']} |" for r in bad]
            L.append("")
    if not any_:
        L += ["None.", ""]

    L += ["## Slug collisions: repo-to-post mapping needs confirming in WP Admin", "",
          "Two or more live posts share the base slug, so the post a repo file updates cannot be proven from outside WordPress.", ""]
    for k in ORDER:
        wp = [r for r in rows(os.path.join(out, k), "wp-posts.csv") if r["status"].startswith("SLUG")]
        groups = {}
        for r in wp:
            groups.setdefault(r["collision_base"] or r["slug"], []).append(r)
        involved = {g: v for g, v in groups.items() if any(x["repo_file_with_this_slug"] or x["in_nav"] != "no" for x in v)}
        if not involved:
            continue
        L += [f"### {names[k]}", ""]
        for g, v in involved.items():
            if len(v) > 10:
                inv_nav = [x for x in v if x["in_nav"] != "no"]
                L.append(f"- **{v[0]['title']}** — {len(v)} published posts (created {min(x['date'] for x in v)[:10]} to {max(x['date'] for x in v)[:10]}). "
                         f"Nav links post {', '.join(x['post_id'] + ' `' + x['slug'] + '`' for x in inv_nav) or 'none'}. Repo file: `{next((x['repo_file_with_this_slug'] for x in v if x['repo_file_with_this_slug']), 'none at the base slug')}`.")
            else:
                for x in v:
                    L.append(f"- post {x['post_id']} `{x['slug']}` — {x['title']} — created {x['date'][:10]}; in nav: {x['in_nav']}; repo file at this slug: `{x['repo_file_with_this_slug'] or 'none'}`")
            L.append("")

    L += ["## Live posts with no repo file (WordPress only)", "",
          "| Help center | Published posts in category | WordPress-only posts | Of those, in the nav |", "| --- | --- | --- | --- |"]
    for k in ORDER:
        wp = rows(os.path.join(out, k), "wp-posts.csv")
        only = [r for r in wp if r["status"].startswith("NO REPO")]
        L.append(f"| {names[k]} | {inv[k]['posts_listed']} | {len(only)} | {sum(r['in_nav'] != 'no' for r in only)} |")
    L += ["", "Full lists: each product's `wp-posts.csv`. These posts are live and published but no repo file produces them, so archiving repo files does not remove them.", ""]
    open(os.path.join(out, "REPORT.md"), "w", encoding="utf8").write("\n".join(L) + "\n")
    print("wrote", os.path.join(out, "REPORT.md"))


if __name__ == "__main__":
    main(sys.argv[1])
