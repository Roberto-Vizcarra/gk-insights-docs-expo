#!/usr/bin/env python3
"""Apply the archive rules (see ../ARCHIVE-PLAN.md) to every repo file in one
audit run and write the per-file dispositions. Planning only: nothing is moved.

Rules, applied in this order (the first that matches decides):
  1. KEEP: exemption        page template files
  2. DECIDE: slug collision  the file shares its base slug with another live
                             post and is not itself proven in or linked from a
                             nav, so which post it updates must be confirmed
  3. KEEP: in nav            the file's post is a sidebar item (any help center)
  4. KEEP: linked from nav   linked directly from a page that is in a nav
  5. DECIDE: redirected      the file's URL redirects; its content is not served
  6. DECIDE: test page       format/test utility pages
     DECIDE: other category  publishes under a category outside the eight
                             audited help centers, so its nav is unknown
  7. DECIDE: two clicks      linked only from pages that are themselves KEEP
                             (reachable, but not directly from a nav page)
  8. ARCHIVE                 not in any nav and not linked from any nav page
  Anything else -> DECIDE (unclassified), so nothing falls through silently.

Usage: python -I dispositions.py <audit output dir>
"""
import csv
import os
import re
import sys

sys.path.insert(0, os.path.dirname(__file__))
import nav_audit as N  # noqa: E402

ORDER = ["desktop", "gitlens", "kepler", "mcp", "cli", "gkdev", "gijc", "gijdc"]
TEMPLATE = re.compile(r"(new-page-template|-new-doc)\.md$", re.I)
TEST_PAGE = {"gitlens/GL-Test.md", "git-integration-for-jira-cloud/Testing.md"}


def rows(path):
    return list(csv.DictReader(open(path, encoding="utf8"))) if os.path.exists(path) else []


def main(out):
    files, by_url = [], {}
    for k in ORDER:
        d = os.path.join(out, k)
        collide = {}
        for w in rows(os.path.join(d, "wp-posts.csv")):
            if w["status"].startswith("SLUG"):
                for f in w["repo_file_with_this_slug"].split(" | "):
                    if f:
                        collide[f] = w["collision_group"]
        for r in rows(os.path.join(d, "repo-pages.csv")):
            r["product"] = k
            r["collision_group"] = collide.get(r["repo_file"], "")
            files.append(r)
            by_url[r["expected_url"]] = r

    # Pass 1: rules that do not depend on other files' dispositions.
    for r in files:
        c = r["classification"]
        if TEMPLATE.search(r["repo_file"]):
            r["disposition"], r["reason"] = "KEEP", "exemption: page template"
        elif r["collision_group"] and c not in ("IN NAV", "IN ANOTHER HELP CENTER'S NAV", "LIVE, NOT IN NAV, LINKED FROM A NAV PAGE"):
            r["disposition"], r["reason"] = "DECIDE", "slug collision: confirm in WP Admin which post this file updates"
        elif c in ("IN NAV", "IN ANOTHER HELP CENTER'S NAV"):
            r["disposition"], r["reason"] = "KEEP", "in nav" + (f" ({r['in_nav_of']})" if r["in_nav_of"] else "")
        elif c == "LIVE, NOT IN NAV, LINKED FROM A NAV PAGE":
            r["disposition"], r["reason"] = "KEEP", "linked directly from a nav page"
        elif c.startswith("CHECK: expected URL redirects"):
            r["disposition"], r["reason"] = "DECIDE", f"URL redirects to {r['redirected_to']}; this file's content is not served"
        elif r["repo_file"] in TEST_PAGE:
            r["disposition"], r["reason"] = "DECIDE", "format/test page: keep as tooling or archive"
        elif r["frontmatter_category"] and r["frontmatter_category"].lower() not in {p["url_cat"] for p in N.PRODUCTS.values()}:
            r["disposition"], r["reason"] = "DECIDE", (f"publishes under category '{r['frontmatter_category']}', "
                                                       "which this audit does not cover; its reachability is unknown")
        else:
            r["disposition"], r["reason"] = None, ""

    # Pass 2: pages linked only from non-nav pages.
    for r in files:
        if r["disposition"]:
            continue
        c = r["classification"]
        if c == "LIVE, NOT IN NAV, LINKED ONLY FROM NON-NAV PAGES":
            linkers = [by_url.get(u) for u in r["inbound_links_from_other_pages"].split()]
            kept = [x["repo_file"] for x in linkers if x and x["disposition"] == "KEEP"]
            if kept:
                r["disposition"], r["reason"] = "DECIDE", "two clicks from the nav: linked only from " + ", ".join(kept)
            else:
                r["disposition"], r["reason"] = "ARCHIVE", "not in a nav; linked only from pages that are themselves not reachable"
        elif c == "LIVE, NOT IN NAV, NO INBOUND LINKS FOUND":
            r["disposition"], r["reason"] = "ARCHIVE", "not in any nav and not linked from any audited page"
        else:
            r["disposition"], r["reason"] = "DECIDE", f"unclassified: {c}"

    for r in files:
        notes = []
        if r["collision_group"]:
            notes.append("slug collision group: " + r["collision_group"][:200])
        if r["frontmatter_status"] and r["frontmatter_status"].split()[0].strip('"') not in ("GA", ""):
            notes.append("front matter status: " + r["frontmatter_status"].split()[0].strip('"'))
        cat_dir = r["repo_file"].split("/")[0]
        if r["frontmatter_category"] and N.PRODUCTS[r["product"]]["url_cat"] != r["frontmatter_category"].lower():
            notes.append(f"front matter category is {r['frontmatter_category']}, not this help center's")
        r["notes"] = "; ".join(notes)
        r["archive_path"] = (f"archive/{cat_dir}/ARCHIVED-{os.path.basename(r['repo_file'])}"
                             if r["disposition"] == "ARCHIVE" else "")
        r["repo"] = os.path.basename(N.PRODUCTS[r["product"]]["repo"])

    dd = os.path.join(out, "dispositions")
    os.makedirs(dd, exist_ok=True)
    fields = ["product", "disposition", "reason", "repo_file", "archive_path", "frontmatter_title", "frontmatter_status",
              "expected_url", "post_id", "classification", "inbound_links_from_nav_pages", "inbound_links_from_other_pages", "notes"]
    N.write_csv(os.path.join(dd, "dispositions.csv"), sorted(files, key=lambda r: (ORDER.index(r["product"]), r["disposition"], r["repo_file"])), fields)

    names = {k: N.PRODUCTS[k]["name"] for k in ORDER}
    L = ["# Page dispositions", "",
         "Generated by `dispositions.py` from this audit run, applying the rules in `redesign-expansion/ARCHIVE-PLAN.md`. "
         "Planning only: nothing has been moved. KEEP files are omitted below; every file is in `dispositions.csv`.", "",
         "| Help center | Repo files | KEEP | DECIDE | ARCHIVE |", "| --- | --- | --- | --- | --- |"]
    for k in ORDER:
        fs_ = [r for r in files if r["product"] == k]
        n = lambda d: sum(r["disposition"] == d for r in fs_)  # noqa: E731
        L.append(f"| {names[k]} | {len(fs_)} | {n('KEEP')} | {n('DECIDE')} | {n('ARCHIVE')} |")
    tot = lambda d: sum(r["disposition"] == d for r in files)  # noqa: E731
    L.append(f"| **All** | **{len(files)}** | **{tot('KEEP')}** | **{tot('DECIDE')}** | **{tot('ARCHIVE')}** |")
    for title, disp in (("DECIDE: needs a person", "DECIDE"), ("ARCHIVE: proposed", "ARCHIVE")):
        L += ["", f"## {title}", ""]
        for k in ORDER:
            fs_ = [r for r in files if r["product"] == k and r["disposition"] == disp]
            if not fs_:
                continue
            L += [f"### {names[k]}", "", "| Repo file | Live post | Reason | Notes |" + (" Archive path |" if disp == "ARCHIVE" else ""),
                  "| --- | --- | --- | --- |" + (" --- |" if disp == "ARCHIVE" else "")]
            for r in fs_:
                L.append(f"| `{r['repo_file']}` | {r['post_id']} {r['expected_url']} | {r['reason']} | {r['notes'] or '—'} |"
                         + (f" `{r['archive_path']}` |" if disp == "ARCHIVE" else ""))
            L.append("")
    keep_ex = [r for r in files if r["reason"].startswith("exemption")]
    L += ["## KEEP by exemption", "", "| Repo file | Live post |", "| --- | --- |"]
    L += [f"| `{r['repo_file']}` | {r['post_id']} {r['expected_url']} |" for r in keep_ex]
    open(os.path.join(dd, "DISPOSITIONS.md"), "w", encoding="utf8").write("\n".join(L) + "\n")
    print({d: tot(d) for d in ("KEEP", "DECIDE", "ARCHIVE")}, "of", len(files))


if __name__ == "__main__":
    main(sys.argv[1])
