// Diagnose one branch: open the chain to an item and report where its
// sub-menu and items are drawn, and whether anything clips them.
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || "playwright");
(async () => {
  const [, , url, menuId, ...chain] = process.argv;
  const b = await chromium.launch({ headless: true });
  const page = await (await b.newContext({ viewport: { width: 1440, height: 900 } })).newPage();
  await page.goto(url, { waitUntil: "networkidle", timeout: 90000 });
  const menu = page.locator(`[id="${menuId}"]`).first();
  for (const id of chain) {
    const li = menu.locator(`li.menu-item-${id}`).first();
    const sub = li.locator(":scope > ul.sub-menu");
    if (!(await sub.isVisible())) {
      await li.locator(":scope > .eael-advanced-menu-indicator, :scope > a > .eael-advanced-menu-indicator, :scope > span").first().click();
      await page.waitForTimeout(1200);
    }
  }
  const info = await page.evaluate(([id, last]) => {
    const m = document.querySelectorAll(`[id="${id}"]`)[0];
    const li = m.querySelector(`li.menu-item-${last}`);
    const sub = li.querySelector(":scope > ul");
    const rect = (e) => { const r = e.getBoundingClientRect(); return [Math.round(r.x), Math.round(r.y), Math.round(r.width), Math.round(r.height)]; };
    const clip = [];
    for (let e = sub; e && e !== document.body; e = e.parentElement) {
      const cs = getComputedStyle(e);
      if (cs.overflow !== "visible" || cs.position !== "static" || cs.transform !== "none" || parseFloat(cs.opacity) < 1)
        clip.push({ tag: e.tagName, cls: (e.className || "").toString().slice(0, 60), overflow: cs.overflow, position: cs.position, opacity: cs.opacity, color: cs.color, rect: rect(e) });
    }
    const items = [...sub.querySelectorAll(":scope > li > a")].slice(0, 4).map((a) => {
      const cs = getComputedStyle(a); const r = rect(a);
      const hit = document.elementFromPoint(r[0] + 5, r[1] + r[3] / 2);
      return { text: a.textContent.trim(), rect: r, color: cs.color, bg: cs.backgroundColor, opacity: cs.opacity, visibility: cs.visibility, topmost_at_point: hit ? hit.tagName + "." + (hit.className || "").toString().slice(0, 40) + " '" + (hit.textContent || "").trim().slice(0, 30) + "'" : null };
    });
    return { menu: rect(m), sub: rect(sub), sub_display: getComputedStyle(sub).display, scrollY: window.scrollY, items, clip };
  }, [menuId, chain[chain.length - 1]]);
  console.log(JSON.stringify(info, null, 2));
  await page.screenshot({ path: "/work/debug-branch.png", fullPage: true });
  await b.close();
})();
