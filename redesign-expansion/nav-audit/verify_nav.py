#!/usr/bin/env python3
"""Independent cross-check of nav_audit.py's nav trees.

Re-reads each product's saved sidebar-rendered.html with a second, unrelated
method (a flat tag tokenizer that tracks <ul>/<li> depth) and compares every
item's id, depth, parent, label and href with nav-tree.json. Any disagreement
is printed and the script exits non-zero.

Usage: python -I verify_nav.py <audit output dir>
"""

import html
import json
import os
import re
import sys

TOKEN = re.compile(r"<(/?)(ul|li|a)\b([^>]*)>|([^<]+)|<[^>]*>", re.I | re.S)


def norm(s):
    return re.sub(r"\s+", " ", html.unescape(s)).strip()


def tokenize_items(src):
    items, stack, depth = [], [], -1  # stack of item ids for open <li>
    cur_a = None
    for m in TOKEN.finditer(src):
        close, tag, attrs, text = m.group(1), (m.group(2) or "").lower(), m.group(3) or "", m.group(4)
        if text is not None:
            if cur_a is not None:
                cur_a["label"] += text
            continue
        if not tag:
            continue
        if tag == "ul":
            depth += -1 if close else 1
        elif tag == "li" and not close:
            mid = re.search(r"\bmenu-item-(\d+)\b", attrs)
            item = dict(item_id=mid.group(1) if mid else None, depth=depth,
                        parent_item_id=stack[-1] if stack else None, label="", href=None)
            items.append(item)
            stack.append(item["item_id"])
            item["_a_seen"] = False
        elif tag == "li" and close:
            stack.pop()
        elif tag == "a" and not close:
            owner = items[-1] if items else None
            # The item's own link is the first <a> after its <li> opens, at its depth.
            if owner is not None and not owner["_a_seen"] and owner["item_id"] == (stack[-1] if stack else None):
                h = re.search(r'\bhref="([^"]*)"', attrs)
                owner["href"] = html.unescape(h.group(1)) if h else None
                owner["_a_seen"] = True
                cur_a = owner
        elif tag == "a" and close:
            cur_a = None
    for it in items:
        it["label"] = norm(it["label"])
        del it["_a_seen"]
    return items


def flatten(items):
    for it in items:
        yield it
        yield from flatten(it["children"])


def main(out_dir):
    bad = 0
    for key in sorted(os.listdir(out_dir)):
        d = os.path.join(out_dir, key)
        side, tree = os.path.join(d, "sidebar-rendered.html"), os.path.join(d, "nav-tree.json")
        if not (os.path.isfile(side) and os.path.isfile(tree)):
            continue
        a = tokenize_items(open(side, encoding="utf8").read())
        b = list(flatten(json.load(open(tree, encoding="utf8"))["items"]))
        fields = ("item_id", "depth", "parent_item_id", "label", "href")
        diffs = []
        if len(a) != len(b):
            diffs.append(f"item count: tokenizer {len(a)} vs audit {len(b)}")
        for i, (x, y) in enumerate(zip(a, b)):
            for f in fields:
                if x[f] != y[f]:
                    diffs.append(f"#{i + 1} {f}: tokenizer {x[f]!r} vs audit {y[f]!r}")
        raw_ids = [m for cls in re.findall(r'<li\b[^>]*\bclass="([^"]*)"', open(side, encoding="utf8").read())
                   for m in re.findall(r"(?:^|\s)menu-item-(\d+)(?=\s|$)", cls)]
        if raw_ids != [it["item_id"] for it in b]:
            diffs.append("menu-item ids in raw HTML order differ from audit order")
        status = "OK" if not diffs else "MISMATCH"
        print(f"{key:8} {status}  {len(b)} items, max depth {max((it['depth'] for it in b), default=-1) + 1}")
        for x in diffs[:30]:
            print("   ", x)
        bad += bool(diffs)
    sys.exit(1 if bad else 0)


if __name__ == "__main__":
    main(sys.argv[1])
