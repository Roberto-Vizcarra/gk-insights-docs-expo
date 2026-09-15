#!/usr/bin/env python3
"""Build gki-docs-helper.zip as a WordPress-installable archive.

Why this exists: the zip has twice been generated incorrectly, and both
failures produced a fatal error on upload rather than a clear message.

  1. Built on Windows with Python's zipfile using os.path.join(), which wrote
     entries as "css\\gki-docs.css". A ZIP path separator is ALWAYS "/", so
     PHP's unzip created a single file literally named "css\\gki-docs.css"
     instead of a css/ directory. includes/ never existed, so the plugin's
     require_once of includes/gki-auth.php fataled and took the site down.

  2. Built by cd-ing into the plugin folder and zipping ".", which produced an
     archive with no root directory. WordPress expects exactly one top-level
     folder and mis-installs a flat archive.

This script guarantees: one root folder, forward slashes, no junk files,
deterministic ordering, and a post-build verification pass that fails loudly.

Usage:  python build-plugin.py
"""

import sys
import zipfile
from pathlib import Path

REPO = Path(__file__).resolve().parent
SRC = REPO / "gki-docs-helper"
OUT = SRC / "gki-docs-helper.zip"
ROOT = "gki-docs-helper"

# Files that must be present in the built archive, or the plugin fatals.
REQUIRED = [
    "gki-docs-helper.php",
    "includes/gki-auth.php",
    "includes/gki-passc.php",
    "templates/single-gki.php",
    "templates/gki-gate.php",
    "css/gki-docs.css",
    "css/gki-passc.css",
    "js/gki-docs.js",
    "js/gki-passc.js",
]

EXCLUDE_SUFFIX = {".zip"}
EXCLUDE_NAMES = {".DS_Store", "Thumbs.db"}


def included(path: Path) -> bool:
    rel = path.relative_to(SRC)
    if any(part.startswith(".") for part in rel.parts):
        return False
    if "__MACOSX" in rel.parts or "node_modules" in rel.parts:
        return False
    if path.suffix.lower() in EXCLUDE_SUFFIX:
        return False
    if path.name in EXCLUDE_NAMES:
        return False
    return path.is_file()


def main() -> int:
    if not SRC.is_dir():
        print(f"ERROR: plugin source not found at {SRC}", file=sys.stderr)
        return 1

    files = sorted((p for p in SRC.rglob("*") if included(p)),
                   key=lambda p: p.relative_to(SRC).as_posix())
    if not files:
        print("ERROR: no files matched", file=sys.stderr)
        return 1

    # Build into memory, then overwrite in place. Deleting files on a mounted
    # folder is not always permitted, so never unlink — truncate and rewrite.
    import io
    buf = io.BytesIO()
    with zipfile.ZipFile(buf, "w", zipfile.ZIP_DEFLATED) as z:
        for f in files:
            # as_posix() is the load-bearing call: forward slashes, always.
            arcname = f"{ROOT}/{f.relative_to(SRC).as_posix()}"
            z.write(f, arcname)
    payload = buf.getvalue()

    # --- Verify BEFORE writing. A build that cannot be verified is not a build. ---
    problems = []
    with zipfile.ZipFile(io.BytesIO(payload)) as z:
        names = z.namelist()
        bad = z.testzip()
        if bad:
            problems.append(f"corrupt entry: {bad}")
        for n in names:
            if "\\" in n:
                problems.append(f"backslash in entry name: {n!r}")
            if not n.startswith(ROOT + "/"):
                problems.append(f"entry outside root folder: {n!r}")
        roots = {n.split("/")[0] for n in names}
        if roots != {ROOT}:
            problems.append(f"expected a single root folder, found: {sorted(roots)}")
        for req in REQUIRED:
            if f"{ROOT}/{req}" not in names:
                problems.append(f"MISSING required file: {req}")

    # Version header and constant must agree, or WordPress shows a stale version
    # and the cache-busting query string never changes.
    php = (SRC / "gki-docs-helper.php").read_text(encoding="utf-8", errors="replace")
    header = next((l.split("Version:")[1].strip() for l in php.splitlines()
                   if "Version:" in l), "?")
    const = next((l.split("'")[3] for l in php.splitlines()
                  if "GKI_DOCS_VERSION" in l and "define" in l), "?")
    if header != const:
        problems.append(f"header version {header!r} != GKI_DOCS_VERSION {const!r}")

    if problems:
        print("BUILD FAILED (nothing written):", file=sys.stderr)
        for p in problems:
            print(f"  - {p}", file=sys.stderr)
        return 1

    OUT.write_bytes(payload)
    print(f"OK  {OUT.name}  v{header}  {len(files)} files  {len(payload):,} bytes")
    for req in REQUIRED:
        print(f"    verified {ROOT}/{req}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
