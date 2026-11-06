#!/usr/bin/env python3
"""Complete inventory of published posts per help center, from the public
WordPress REST API, joined to the nav audit and the repo files.

Why: nav_audit.py maps a repo file to the post at the URL its filename
produces. When two posts share a base slug (WordPress appends -2, -3, ... on a
collision), that rule can attach a file to the wrong post. This script lists
every published post in each category so every such collision is visible, and
every post with no repo file at all (content that exists only in WordPress)
is listed too.

Read-only: GET requests to /wp-json/wp/v2/categories and /wp-json/wp/v2/posts.

Usage: python -I wp_inventory.py <audit output dir>
"""

import csv
import json
import os
import re
import sys
import time
import urllib.request

sys.path.insert(0, os.path.dirname(__file__))
import nav_audit as N  # noqa: E402

API = N.SITE + "/wp-json/wp/v2"


def get(url):
    for attempt in range(4):
        time.sleep(0.5 * (1 + 3 * attempt))
        try:
            req = urllib.request.Request(url, headers={"User-Agent": N.UA})
            with urllib.request.urlopen(req, timeout=60) as r:
                return json.loads(r.read()), dict(r.headers)
        except Exception as e:  # noqa: BLE001
            err = e
    raise RuntimeError(f"{url}: {err!r}")


def all_posts(cat_id):
    posts, page = [], 1
    while True:
        data, hdr = get(f"{API}/posts?categories={cat_id}&per_page=100&page={page}&orderby=id&order=asc"
                        "&_fields=id,slug,link,date,modified,title,status,categories")
        posts += data
        total_pages = int(hdr.get("X-WP-TotalPages") or hdr.get("x-wp-totalpages") or 1)
        if page >= total_pages:
            return posts, int(hdr.get("X-WP-Total") or hdr.get("x-wp-total") or len(posts))
        page += 1


def main(out_dir):
    report = []
    for key, p in N.PRODUCTS.items():
        d = os.path.join(out_dir, key)
        if not os.path.isdir(d):
            continue
        cats, _ = get(f"{API}/categories?slug={p['url_cat']}&_fields=id,slug,name,count")
        if len(cats) != 1:
            report.append(dict(key=key, error=f"category lookup returned {len(cats)} results"))
            continue
        cat = cats[0]
        posts, total = all_posts(cat["id"])

        nav = {r["post_id"]: r for r in csv.DictReader(open(os.path.join(d, "nav-items.csv"), encoding="utf8")) if r["post_id"]}
        repo = list(csv.DictReader(open(os.path.join(d, "repo-pages.csv"), encoding="utf8")))
        expected = {}
        for r in repo:
            if r["expected_url"]:
                slug = r["expected_url"].rstrip("/").rsplit("/", 1)[-1]
                expected.setdefault(slug, []).append(r["repo_file"])

        # Group posts by base slug: "foo", "foo-2", "foo-15" share base "foo" when
        # "foo" itself is a repo file's expected slug or another post's slug.
        slugs = {x["slug"] for x in posts}
        def base(s):
            m = re.match(r"^(.*)-(\d+)$", s)
            return m.group(1) if m and (m.group(1) in slugs or m.group(1) in expected) else s
        groups = {}
        for x in posts:
            groups.setdefault(base(x["slug"]), []).append(x)

        rows = []
        for x in posts:
            b = base(x["slug"])
            files_same_slug = expected.get(x["slug"], [])
            group = groups[b]
            collision = len(group) > 1 or (b != x["slug"])
            if collision:
                status = "SLUG COLLISION — mapping to a repo file needs confirmation"
            elif len(files_same_slug) == 1:
                status = "matches one repo file"
            elif len(files_same_slug) > 1:
                status = "AMBIGUOUS — several repo files produce this slug"
            else:
                status = "NO REPO FILE — exists only in WordPress"
            rows.append(dict(post_id=x["id"], slug=x["slug"], link=x["link"], title=N.norm(x["title"]["rendered"]),
                             date=x["date"], modified=x["modified"], in_nav=("yes (item " + nav[str(x["id"])]["item_id"] + ")") if str(x["id"]) in nav else "no",
                             repo_file_with_this_slug=" | ".join(files_same_slug),
                             collision_base=b if collision else "",
                             collision_size=len(group) if collision else "",
                             # Members listed only for small groups; a group of hundreds is identified by base + size.
                             collision_group=(" ".join(f"{g['slug']}#{g['id']}" for g in group) if len(group) <= 10
                                              else f"{len(group)} posts sharing base slug '{b}'") if collision else "",
                             status=status))
        rows.sort(key=lambda r: (r["status"], r["slug"]))
        N.write_csv(os.path.join(d, "wp-posts.csv"), rows, list(rows[0].keys()) if rows else ["post_id"])
        counts = {}
        for r in rows:
            counts[r["status"]] = counts.get(r["status"], 0) + 1
        report.append(dict(key=key, category_id=cat["id"], category_count=cat["count"], posts_listed=len(posts),
                           rest_total=total, counts=counts))
        print(key, json.dumps(report[-1]), flush=True)
    json.dump(report, open(os.path.join(out_dir, "wp-inventory.json"), "w", encoding="utf8"), indent=2)


if __name__ == "__main__":
    main(sys.argv[1])
