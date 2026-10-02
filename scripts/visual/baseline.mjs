// Regression baseline for the restructuring: for every route it stores the server-rendered HTML, the list of requests
// the browser made (with status), console errors, and a box dump of the shared chrome at the four standard viewports.
//   node baseline.mjs <baseUrl> <outDir>
// Compare two runs with compare-baseline.py (it normalises cache-busting values and the asset URL prefix).
import { chromium } from 'playwright';
import fs from 'node:fs';

const [base = 'http://localhost:8088', out = 'baseline'] = process.argv.slice(2);
const routes = ['/', '/careers', '/careers/frontend-developer', '/careers/team', '/contact', '/downloads', '/home/auth', '/home/auth/reset',
  '/home/_api/UI/', '/home/_api/UI/terminal/', '/home/_api/Docs/', '/home/_api/UI/terminal/Tools/domain/', '/home/_api/UI/terminal/Tools/math/',
  '/home/_api/UI/terminal/Tools/qr/', '/home/_api/UI/terminal/Tools/editor/', '/home/_api/UI/terminal/Tools/currency/', '/home/_api/UI/terminal/Tools/crypto/',
  '/home/_api/UI/terminal/Page/4ukraine/', '/home/_api/UI/terminal/Page/donate/', '/home/_api/UI/terminal/Page/valentine/', '/downloads/wallpapers/create/', '/nope'];
const viewports = { desktop: [1440, 900], tablet: [820, 1180], phone: [390, 844], phoneL: [844, 390] };
const selectors = ['.Navbar', '.Navbar-toggle', '.Navbar-toggle i', '.Navbar-brand', '.Navbar-quickLinks', '.aev-apps-menu', '.aev-user-badge', 'main', 'h1'];
fs.mkdirSync(out + '/html', { recursive: true });
fs.mkdirSync(out + '/shots', { recursive: true });
const browser = await chromium.launch();
const report = {};
const slug = (r) => (r.replace(/[^a-z0-9]+/gi, '_').replace(/^_|_$/g, '') || 'home');

for (const route of routes) {
  const entry = (report[route] = { status: null, requests: [], errors: [], boxes: {} });
  const ctx0 = await browser.newContext();
  const res = await ctx0.request.get(base + route, { maxRedirects: 0 });
  entry.status = res.status();
  fs.writeFileSync(`${out}/html/${slug(route)}.html`, await res.text());
  await ctx0.close();

  for (const [vp, [width, height]] of Object.entries(viewports)) {
    const context = await browser.newContext({ viewport: { width, height }, hasTouch: width < 900 });
    const page = await context.newPage();
    const requests = new Set();
    page.on('response', (r) => requests.add(`${r.status()} ${r.url().replace(base, '')}`));
    page.on('console', (m) => m.type() === 'error' && vp === 'desktop' && entry.errors.push(m.text().slice(0, 140)));
    page.on('pageerror', (e) => vp === 'desktop' && entry.errors.push('PAGEERROR ' + String(e).slice(0, 140)));
    await page.goto(base + route, { waitUntil: 'load' });
    await page.waitForTimeout(route === '/' ? 3800 : 1500);
    entry.boxes[vp] = await page.evaluate((sels) => Object.fromEntries(sels.map((s) => {
      const e = document.querySelector(s);
      if (!e) return [s, null];
      const b = e.getBoundingClientRect();
      return [s, [b.x, b.y, b.width, b.height].map(Math.round)];
    })), selectors);
    entry.boxes[vp].overflow = await page.evaluate(() => document.documentElement.scrollWidth - innerWidth);
    if (vp === 'desktop') entry.requests = [...requests].filter((u) => !/ \/(api\/prices)/.test(u)).sort();
    await page.screenshot({ path: `${out}/shots/${slug(route)}-${vp}.png` });
    await context.close();
  }
}
await browser.close();
fs.writeFileSync(out + '/report.json', JSON.stringify(report, null, 1));
console.log(`baseline: ${routes.length} routes → ${out}`);
