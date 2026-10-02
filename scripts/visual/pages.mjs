// Screenshots arbitrary pages at the 4 standard viewports; reports overflow, console errors, failed requests.
//   node pages.mjs <baseUrl> <outDir> <path> [<path>…]
import { chromium } from 'playwright';
import fs from 'node:fs';

const [base, out, ...paths] = process.argv.slice(2);
const viewports = { desktop: [1440, 900], tablet: [820, 1180], phone: [390, 844], phoneL: [844, 390] };
fs.mkdirSync(out, { recursive: true });
const browser = await chromium.launch();
const rows = [];
for (const path of paths) {
  for (const [vp, [width, height]] of Object.entries(viewports)) {
    const context = await browser.newContext({ viewport: { width, height }, hasTouch: width < 900 });
    const page = await context.newPage();
    const errors = [], failed = [];
    page.on('console', (m) => m.type() === 'error' && errors.push(m.text().slice(0, 140)));
    page.on('pageerror', (e) => errors.push(String(e).slice(0, 140)));
    page.on('response', (r) => r.status() >= 400 && failed.push(`${r.status()} ${r.url().slice(0, 100)}`));
    await page.goto(base + path, { waitUntil: 'load' });
    await page.waitForTimeout(1500);
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth - innerWidth);
    const name = `${path.replace(/[^a-z0-9]+/gi, '_') || 'home'}-${vp}`;
    await page.screenshot({ path: `${out}/${name}.png`, fullPage: vp !== 'phoneL' });
    rows.push(`${path} ${vp}: overflow=${overflow} errors=${errors.length} failed=${failed.length}` + (errors.length || failed.length ? ' ' + JSON.stringify([...errors, ...failed]) : ''));
    await context.close();
  }
}
await browser.close();
console.log(rows.join('\n'));
