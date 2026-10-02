// Screenshots pages at the project's standard viewports in light/dark colour schemes.
//   node shoot.mjs <baseUrl> <outDir> '<json map name->path>'
// Reports console errors and failed/4xx requests per shot to <outDir>/log.json.
import { chromium } from 'playwright';
import fs from 'node:fs';

const base = process.argv[2] ?? 'http://localhost:8099';
const out = process.argv[3] ?? 'shots';
const pages = JSON.parse(process.argv[4] ?? '{"home":"/page/main/"}');
const viewports = { desktop: [1440, 900], tablet: [820, 1180], phone: [390, 844], phoneL: [844, 390] };

fs.mkdirSync(out, { recursive: true });
const browser = await chromium.launch();
const log = {};

for (const [name, path] of Object.entries(pages)) {
  for (const [vp, [width, height]] of Object.entries(viewports)) {
    for (const scheme of ['light', 'dark']) {
      const context = await browser.newContext({ viewport: { width, height }, colorScheme: scheme });
      const page = await context.newPage();
      const key = `${name}-${vp}-${scheme}`;
      log[key] = { console: [], failed: [] };
      page.on('console', (m) => ['error', 'warning'].includes(m.type()) && log[key].console.push(`${m.type()}: ${m.text().slice(0, 160)}`));
      page.on('requestfailed', (r) => log[key].failed.push(`${r.url().slice(0, 120)} ${r.failure()?.errorText ?? ''}`));
      page.on('response', (r) => r.status() >= 400 && log[key].failed.push(`${r.status()} ${r.url().slice(0, 120)}`));
      try {
        await page.goto(base + path, { waitUntil: 'load', timeout: 20000 });
        await page.waitForTimeout(4500); // legacy preloader + animations
        await page.screenshot({ path: `${out}/${key}.png` });
        if (scheme === 'light' && vp !== 'phoneL') await page.screenshot({ path: `${out}/${key}-full.png`, fullPage: true });
      } catch (error) {
        log[key].error = String(error).slice(0, 120);
      }
      await context.close();
    }
  }
}

await browser.close();
fs.writeFileSync(`${out}/log.json`, JSON.stringify(log, null, 1));
console.log(`wrote ${fs.readdirSync(out).filter((f) => f.endsWith('.png')).length} screenshots to ${out}`);
