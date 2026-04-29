/**
 * generate-pdf.js
 * Usage: node generate-pdf.js <embedUrl> <outputPath>
 *
 * Renders a Looker Studio (or any) embed URL in headless Chrome and saves
 * the page as a PDF. Requires @puppeteer/puppeteer (installed in this folder).
 */
'use strict';

const puppeteer = require('puppeteer');

const [, , embedUrl, outputPath] = process.argv;

if (!embedUrl || !outputPath) {
  console.error('Usage: node generate-pdf.js <embedUrl> <outputPath>');
  process.exit(1);
}

(async () => {
  let browser;
  try {
    const PDF_WIDTH  = 2000;
    const PDF_HEIGHT = 3000;

    browser = await puppeteer.launch({
      headless: true,
      args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--disable-gpu',
        '--disable-web-security',   // allow cross-origin iframes
        `--window-size=${PDF_WIDTH},${PDF_HEIGHT}`,
      ],
    });

    const page = await browser.newPage();

    // Fixed viewport: 2000px wide, full height for rendering
    await page.setViewport({ width: PDF_WIDTH, height: PDF_HEIGHT, deviceScaleFactor: 1 });

    // Mimic a real browser so Looker Studio doesn't reject the request
    await page.setUserAgent(
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64) ' +
      'AppleWebKit/537.36 (KHTML, like Gecko) ' +
      'Chrome/124.0.0.0 Safari/537.36'
    );

    // Navigate and wait until network is mostly idle
    await page.goto(embedUrl, {
      waitUntil: 'networkidle2',
      timeout: 90_000,
    });

    // Scroll through the full page to trigger lazy-rendered charts/filters
    await page.evaluate(async (pageHeight) => {
      await new Promise((resolve) => {
        const step = 400;
        let current = 0;
        const timer = setInterval(() => {
          window.scrollBy(0, step);
          current += step;
          if (current >= pageHeight) {
            window.scrollTo(0, 0);
            clearInterval(timer);
            resolve();
          }
        }, 100);
      });
    }, PDF_HEIGHT);

    // Wait for network to settle again after scroll-triggered requests
    await page.waitForNetworkIdle({ idleTime: 2000, timeout: 30_000 }).catch(() => {});

    // Extra settle time for chart paint / JS animations to finish
    await new Promise((r) => setTimeout(r, 10_000));

    await page.pdf({
      path: outputPath,
      width: PDF_WIDTH + 'px',
      height: PDF_HEIGHT + 'px',
      printBackground: true,
      margin: { top: '0px', right: '0px', bottom: '0px', left: '0px' },
    });

    process.exit(0);
  } catch (err) {
    // Write the error to stderr so PHP can capture it
    process.stderr.write(err.message + '\n');
    process.exit(1);
  } finally {
    if (browser) {
      await browser.close().catch(() => {});
    }
  }
})();
