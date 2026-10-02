// Captures the interactive states of the main page at the project's standard viewports and reports
// horizontal overflow, console errors and failed requests.
//   node states.mjs <baseUrl> <path> <outDir>
// States: closed · menu · settings · launcher · profile (launcher/profile exist only ≥768px, as in the original).
import { chromium } from 'playwright';
import fs from 'node:fs';

const [base = 'http://localhost:8088', path = '/', out = 'shots'] = process.argv.slice(2);
const viewports = { desktop: [1440, 900], tablet: [820, 1180], phone: [390, 844], phoneL: [844, 390] };
fs.mkdirSync(out, { recursive: true });
const browser = await chromium.launch();
const report = {};

for (const [vp, [width, height]] of Object.entries(viewports)) {
  const context = await browser.newContext({ viewport: { width, height }, colorScheme: 'dark', hasTouch: width < 900 });
  const page = await context.newPage();
  const log = { overflow: null, console: [], failed: [], boxes: {} };
  report[vp] = log;
  page.on('console', (m) => ['error', 'warning'].includes(m.type()) && log.console.push(`${m.type()}: ${m.text().slice(0, 140)}`));
  page.on('response', (r) => r.status() >= 400 && log.failed.push(`${r.status()} ${r.url().slice(0, 100)}`));
  await page.goto(base + path, { waitUntil: 'load' });
  await page.waitForTimeout(4500);
  const shot = (name) => page.screenshot({ path: `${out}/${vp}-${name}.png` });
  const box = async (sel) => (await page.locator(sel).first().boundingBox().catch(() => null));
  const visible = async (sel) => page.locator(sel).first().isVisible().catch(() => false);

  log.overflow = await page.evaluate(() => document.documentElement.scrollWidth - innerWidth);
  await shot('closed');
  for (const sel of ['.Navbar-toggle', '.Navbar-brand', '.aev-apps-menu', '.aev-user-badge']) log.boxes[sel] = await box(sel);

  await page.locator('#toggle').click();
  await page.waitForTimeout(700);
  for (const sel of ['.m-spotlight-search', '.mod-buttons', '#aDHieSVT', '.Navbar-menu-minor']) log.boxes[sel] = await box(sel);
  await shot('menu');
  try {
    await page.locator('.mod-buttons button').first().click({ timeout: 3000 });
    await page.waitForTimeout(1800);
    await shot('settings');
  } catch (error) { log.console.push(`settings state skipped: ${String(error).slice(0, 80)}`); }
  await page.locator('#toggle').click({ timeout: 3000 }).catch(() => {});
  await page.waitForTimeout(500);

  if (await visible('.aev-apps-menu')) {
    await page.locator('.aev-apps-menu').click();
    await page.waitForTimeout(700);
    await shot('launcher');
    log.boxes['.aev-app-launcher'] = await box('.aev-app-launcher');
    await page.evaluate(() => document.body.click());
    await page.locator('.aev-user-badge').click();
    await page.waitForTimeout(700);
    await shot('profile');
    log.boxes['.aev-profile-options'] = await box('.aev-profile-options');
  }
  await context.close();
}
await browser.close();
fs.writeFileSync(`${out}/report.json`, JSON.stringify(report, null, 1));
console.log(Object.entries(report).map(([vp, l]) => `${vp}: overflow=${l.overflow} errors=${l.console.length} failed=${l.failed.length}`).join('\n'));
