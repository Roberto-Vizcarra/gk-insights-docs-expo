// Visual check of each help center's live sidebar.
//
// Opens the product home page in Chrome (desktop width), expands every
// sub-menu by clicking its toggle the way a reader would, screenshots the
// fully expanded sidebar, and records what the browser actually renders and
// shows: label, href, depth, menu-item id, visible or not. The result is
// compared with nav_audit.py's nav-tree.json for the same product.
//
// Read-only: page loads and clicks on menu toggles only.
//
// Usage: node visual_check.js <audit output dir> [product,...]
// Needs Playwright; set PLAYWRIGHT_MODULE to an installed copy if it is not
// resolvable from here.

const path = require("path");
const fs = require("fs");
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || "playwright");

const PRODUCTS = {
  desktop: ["gitkraken-desktop/gitkraken-desktop-home", "menu-gitkraken-desktop-menu"],
  gitlens: ["gitlens/gitlens-home", "menu-gitlens-menu"],
  kepler: ["kepler/kepler-getting-started", "menu-kepler"],
  mcp: ["mcp/mcp-getting-started", "menu-mcp-menu"],
  cli: ["cli/cli-home", "menu-gitkraken-cli"],
  gkdev: ["gk-dev/gk-dev-home", "menu-gk-dev-menu"],
  gijc: ["git-integration-for-jira-cloud/git-integration-for-jira-home-gij-cloud", "menu-git-integration-for-jira-cloud-menu"],
  gijdc: ["git-integration-for-jira-data-center/git-integration-for-jira-home-self-manged", "menu-git-integration-for-jira-server-data-center-menu"],
};

const norm = (s) => (s || "").replace(/\s+/g, " ").trim();

function flatten(items, out = []) {
  for (const it of items) { out.push(it); flatten(it.children, out); }
  return out;
}

(async () => {
  const outDir = process.argv[2];
  const keys = (process.argv[3] || Object.keys(PRODUCTS).join(",")).split(",");
  // Runs in the official Playwright Docker image (bundled Chromium, headless),
  // so no browser opens on the host desktop.
  const browser = await chromium.launch({ headless: true });
  // Desktop width, and a window tall enough that an opened menu never needs
  // scrolling or a resize to be captured. Either one makes the site's menu
  // script re-initialise and collapse the open sub-menus, which produced blank
  // branches in earlier screenshots.
  const ctx = await browser.newContext({ viewport: { width: 1440, height: 7000 }, userAgent: "Mozilla/5.0 (compatible; GitKraken-docs-nav-audit/1.0; visual-check)" });
  const report = [];
  for (const key of keys) {
    const [pagePath, menuId] = PRODUCTS[key];
    const dir = path.join(outDir, key);
    fs.mkdirSync(dir, { recursive: true });
    const page = await ctx.newPage();
    await page.goto(`https://help.gitkraken.com/${pagePath}/`, { waitUntil: "networkidle", timeout: 90000 });
    // The menu script can clone the menu (e.g. a mobile copy) after load, so
    // there may be several elements with this id. Use the one a reader sees.
    const copies = page.locator(`[id="${menuId}"]`);
    const nCopies = await copies.count();
    let idx = -1;
    for (let i = 0; i < nCopies; i++) if (await copies.nth(i).isVisible()) { idx = i; break; }
    if (idx < 0) { console.log(`${key}: no visible copy of #${menuId} (${nCopies} in DOM)`); await page.close(); continue; }
    const menu = copies.nth(idx);
    await menu.scrollIntoViewIfNeeded();

    // The sidebars are accordions: opening a sub-menu can close its siblings.
    // So each item is revealed on its own: open only its ancestor chain, top
    // down, clicking a toggle only when that sub-menu is closed, then check the
    // item is visible. One screenshot is taken per parent with its sub-menu
    // open, which is exactly what a reader sees when they open that branch.
    let clicks = 0, rounds = 0;
    const li = (id) => menu.locator(`li.menu-item-${id}`).first();
    // Open one sub-menu and wait until it is really displayed. Returns an error
    // string instead of throwing, so a failure is recorded, never skipped.
    // A sub-menu counts as open only when it is displayed, every animation on
    // it and its ancestors has finished, and it is fully opaque all the way up.
    // (Playwright's isVisible() treats an element that is still fading in at
    // opacity 0 as visible, so it is not enough on its own.)
    const settledOpen = (sub) => sub.evaluate(async (u) => {
      const anims = () => document.getAnimations().filter((a) => a.playState === "running");
      for (let i = 0; i < 40 && anims().length; i++) await new Promise((r) => setTimeout(r, 50));
      await new Promise((r) => setTimeout(r, 150)); // jQuery animations are not Web Animations; give them time too
      for (let i = 0; i < 40; i++) {
        let ok = getComputedStyle(u).display !== "none";
        for (let e = u; ok && e && e !== document.body; e = e.parentElement) {
          const cs = getComputedStyle(e);
          if (cs.display === "none" || cs.visibility === "hidden" || parseFloat(cs.opacity) < 1) ok = false;
        }
        if (ok && u.offsetHeight > 0) return true;
        await new Promise((r) => setTimeout(r, 100));
      }
      return false;
    });
    const openSub = async (id) => {
      const sub = li(id).locator(":scope > ul.sub-menu");
      if (!(await sub.count())) return null;
      for (let attempt = 1; attempt <= 3; attempt++) {
        await page.waitForTimeout(250); // let any running open/close animation settle
        if ((await sub.isVisible()) && (await settledOpen(sub))) return null;
        const toggle = li(id).locator(":scope > .eael-advanced-menu-indicator, :scope > a > .eael-advanced-menu-indicator, :scope > span").first();
        try {
          if (await toggle.count()) await toggle.click({ timeout: 5000 });
          else await li(id).locator(":scope > a").click({ timeout: 5000 });
          clicks++;
          await sub.waitFor({ state: "visible", timeout: 3000 });
          if (await settledOpen(sub)) return null;
          throw new Error("sub-menu displayed but not fully opaque after waiting");
        } catch (e) {
          if (attempt === 3) return `could not open sub-menu of item ${id}: ${String(e.message || e).split("\n")[0]}`;
        }
      }
      return null;
    };
    const tree = await page.evaluate(([id, k]) => {
      const root = document.querySelectorAll(`[id="${id}"]`)[k];
      const out = [];
      const walk = (ul, chain) => {
        for (const li of ul.children) {
          if (li.tagName !== "LI") continue;
          const m = [...li.classList].map((c) => c.match(/^menu-item-(\d+)$/)).find(Boolean);
          const myId = m ? m[1] : null;
          const sub = li.querySelector(":scope > ul");
          out.push({ id: myId, chain, hasSub: !!sub });
          if (sub) walk(sub, chain.concat([myId]));
        }
      };
      walk(root, []);
      return out;
    }, [menuId, idx]);
    const shown = {}, revealErrors = {};
    const shotsDir = path.join(dir, "branches");
    fs.rmSync(shotsDir, { recursive: true, force: true });
    fs.mkdirSync(shotsDir, { recursive: true });
    let shot = 0;
    for (const t of tree) {
      const errs = [];
      for (const anc of t.chain) { const e = await openSub(anc); if (e) errs.push(e); }
      // Visible to a reader: laid out, not hidden, and opaque including every ancestor.
      shown[t.id] = await li(t.id).locator(":scope > a").first().evaluate((a) => {
        const r = a.getBoundingClientRect();
        if (!(r.width > 0 && r.height > 0)) return false;
        for (let e = a; e && e !== document.body; e = e.parentElement) {
          const cs = getComputedStyle(e);
          if (cs.display === "none" || cs.visibility === "hidden" || parseFloat(cs.opacity) === 0) return false;
        }
        return a.checkVisibility({ checkOpacity: true, checkVisibilityCSS: true });
      });
      if (!shown[t.id]) {
        // Record why: is the item's own box empty, or is an ancestor closed?
        const why = await li(t.id).evaluate((el) => {
          const a = el.querySelector(":scope > a");
          const cs = a ? getComputedStyle(a) : null;
          const own = getComputedStyle(el);
          const closedAnc = [];
          for (let p = el.parentElement; p && !p.id?.startsWith("menu-"); p = p.parentElement)
            if (p.tagName === "UL" && getComputedStyle(p).display === "none") closedAnc.push("sub-menu closed");
          return { li_display: own.display, li_visibility: own.visibility, a_display: cs && cs.display, a_visibility: cs && cs.visibility,
                   a_box: a ? [a.offsetWidth, a.offsetHeight] : null, closed_ancestors: closedAnc.length };
        });
        errs.push(`not visible: ${JSON.stringify(why)}`);
      }
      if (errs.length) revealErrors[t.id] = errs;
      if (t.hasSub && shown[t.id]) {
        const e = await openSub(t.id);
        if (e) (revealErrors[t.id] = revealErrors[t.id] || []).push(e);
        shot++;
        await page.waitForTimeout(300);
        const box = await menu.boundingBox();
        const vp = page.viewportSize();
        if (box.y + box.height > vp.height) (revealErrors[t.id] = revealErrors[t.id] || []).push(`menu (${Math.round(box.height)}px) taller than the capture window; screenshot is cropped`);
        // Viewport clip, no fullPage, no scrolling: the page is not resized.
        await page.screenshot({ path: path.join(shotsDir, `${String(shot).padStart(3, "0")}-item-${t.id}.png`),
          clip: { x: box.x, y: box.y, width: box.width, height: Math.min(box.height, vp.height - box.y) } });
      }
    }

    // What the browser renders, after expansion.
    const rendered = await page.evaluate(([id, k]) => {
      const root = document.querySelectorAll(`[id="${id}"]`)[k];
      const out = [];
      const walk = (ul, depth, parent) => {
        for (const li of ul.children) {
          if (li.tagName !== "LI") continue;
          const a = li.querySelector(":scope > a");
          const idm = [...li.classList].map((c) => c.match(/^menu-item-(\d+)$/)).find(Boolean);
          const r = li.getBoundingClientRect();
          out.push({
            item_id: idm ? idm[1] : null, depth, parent_item_id: parent,
            label: a ? a.textContent : "", href: a ? a.getAttribute("href") : null,
          });
          const sub = li.querySelector(":scope > ul");
          if (sub) walk(sub, depth + 1, idm ? idm[1] : null);
        }
      };
      walk(root, 0, null);
      return out;
    }, [menuId, idx]);
    rendered.forEach((r) => { r.label = norm(r.label); r.visible = !!shown[r.item_id]; });

    // Overview image: every sub-menu forced open with CSS. This is not a state a
    // reader can reach in an accordion; it exists only to show the whole tree at once.
    await page.addStyleTag({ content: `[id="${menuId}"] ul.sub-menu{display:block!important;visibility:visible!important;opacity:1!important;max-height:none!important;height:auto!important}` });
    await page.waitForTimeout(300);
    const fbox = await menu.boundingBox();
    await page.setViewportSize({ width: 1440, height: Math.ceil(fbox.y + fbox.height + 20) }); // forced-open view: a resize is harmless here, CSS keeps every sub-menu open
    await page.waitForTimeout(300);
    const fb2 = await menu.boundingBox();
    await page.screenshot({ path: path.join(dir, "sidebar-all-forced-open.png"), clip: { x: fb2.x, y: fb2.y, width: fb2.width, height: fb2.height } });
    await page.close();

    // Compare with the audit's tree.
    const treePath = path.join(dir, "nav-tree.json");
    const audit = fs.existsSync(treePath) ? flatten(JSON.parse(fs.readFileSync(treePath, "utf8")).items) : null;
    const diffs = [];
    if (!audit) diffs.push("no nav-tree.json to compare with");
    else {
      if (audit.length !== rendered.length) diffs.push(`item count: browser ${rendered.length} vs audit ${audit.length}`);
      rendered.forEach((r, i) => {
        const a = audit[i];
        if (!a) return;
        for (const f of ["item_id", "depth", "parent_item_id", "label", "href"]) {
          if (String(r[f]) !== String(a[f])) diffs.push(`#${i + 1} ${f}: browser ${JSON.stringify(r[f])} vs audit ${JSON.stringify(a[f])}`);
        }
      });
    }
    const hidden = rendered.filter((r) => !r.visible);
    hidden.forEach((h) => (h.reason = revealErrors[h.item_id]));
    fs.writeFileSync(path.join(dir, "visual-check.json"), JSON.stringify({ items: rendered, menu_copies_in_dom: nCopies, copy_used: idx, clicks, hidden, reveal_errors: revealErrors, diffs }, null, 2));
    report.push({ key, items: rendered.length, hidden: hidden.length, clicks, copies: nCopies, diffs: diffs.length });
    console.log(`${key.padEnd(8)} ${diffs.length ? "MISMATCH" : "OK"}  ${rendered.length} items, ${hidden.length} not visible after expanding (${clicks} toggles clicked; ${nCopies} copies of the menu in the DOM, used #${idx + 1})`);
    diffs.slice(0, 20).forEach((d) => console.log("    " + d));
    hidden.slice(0, 20).forEach((h) => console.log(`    hidden: item ${h.item_id} depth ${h.depth} "${h.label}" — ${(h.reason || []).join(" | ")}`));
  }
  await browser.close();
  fs.writeFileSync(path.join(outDir, "visual-check.json"), JSON.stringify(report, null, 2));
})().catch((e) => { console.error(e); process.exit(1); });
