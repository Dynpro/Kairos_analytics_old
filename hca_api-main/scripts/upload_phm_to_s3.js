'use strict';

/**
 * Screenshot each PHM chart via Puppeteer and upload to S3 at phm_look/{id}.png
 * Bucket: kairos-next-gen-storage  Region: us-east-2
 */

const puppeteer = require('puppeteer');
const { S3Client, PutObjectCommand } = require('@aws-sdk/client-s3');
const fs   = require('fs');
const path = require('path');

// ── Load chart list ─────────────────────────────────────────────────────────
const chartIds = JSON.parse(
  fs.readFileSync(path.join(__dirname, '../../phm_chart_ids.json'), 'utf8')
);

// Load .env manually
const envPath = path.join(__dirname, '../.env');
const env = {};
fs.readFileSync(envPath, 'utf8').split('\n').forEach(line => {
  const m = line.match(/^([^#=]+)=(.*)$/);
  if (m) env[m[1].trim()] = m[2].trim().replace(/^["']|["']$/g, '');
});

const s3 = new S3Client({
  region: env.AWS_DEFAULT_REGION || 'us-east-2',
  credentials: {
    accessKeyId:     env.AWS_ACCESS_KEY_ID,
    secretAccessKey: env.AWS_SECRET_ACCESS_KEY,
  },
});
const BUCKET = env.AWS_BUCKET || 'kairos-next-gen-storage';

// Full chart list with studio_dashboards IDs
const charts = [
  { name: 'Chart_10_1', url: 'https://lookerstudio.google.com/embed/reporting/31738470-761d-480c-9bba-a3a5b1b35b80/page/oZPuF' },
  { name: 'Chart_10_2', url: 'https://lookerstudio.google.com/embed/reporting/af834555-865e-49c8-af53-a039858d9d63/page/oZPuF' },
  { name: 'Chart_11_1', url: 'https://lookerstudio.google.com/embed/reporting/2eaf7ca2-d51b-478f-8133-4d26b3f33df7/page/qgPuF' },
  { name: 'Chart_11_2', url: 'https://lookerstudio.google.com/embed/reporting/9b8763c8-29ca-4d7e-bd34-1eb97e9e8bf3/page/oZPuF' },
  { name: 'Chart_11_3', url: 'https://lookerstudio.google.com/embed/reporting/3e43b623-736a-47d5-b978-e0af726af020/page/oZPuF' },
  { name: 'Chart_12_1', url: 'https://lookerstudio.google.com/embed/reporting/13a7690e-1fcc-4108-8bad-265c781a8f8e/page/oZPuF' },
  { name: 'Chart_12_2', url: 'https://lookerstudio.google.com/embed/reporting/3c8ff342-7bcd-4815-903d-93d95e7624cb/page/TjWuF' },
  { name: 'Chart_13_1', url: 'https://lookerstudio.google.com/embed/reporting/f5250072-c7d2-404a-a7f5-74687f5dbb1b/page/1BYuF' },
  { name: 'Chart_13_2', url: 'https://lookerstudio.google.com/embed/reporting/15d58d24-92e3-4d3e-896a-ae8e966ae93a/page/odYuF' },
  { name: 'Chart_15_1', url: 'https://lookerstudio.google.com/embed/reporting/67e5a82e-1406-4670-8f54-43b47dd9d9ba/page/hnYuF' },
  { name: 'Chart_15_2', url: 'https://lookerstudio.google.com/embed/reporting/0ae44b17-674a-4bf2-b069-c0bcb91dbb2b/page/C1YuF' },
  { name: 'Chart_15_3', url: 'https://lookerstudio.google.com/embed/reporting/951866d9-6486-4623-b318-5bcb91f859e0/page/Y8YuF' },
  { name: 'Chart_16_1', url: 'https://lookerstudio.google.com/embed/reporting/b4ec2d96-3470-40ee-9cff-3b20d59d0713/page/vDIuF' },
  { name: 'Chart_16_2', url: 'https://lookerstudio.google.com/embed/reporting/f89642bb-eed0-47e1-902e-89cb295f51b9/page/vDIuF' },
  { name: 'Chart_16_3', url: 'https://lookerstudio.google.com/embed/reporting/1caed473-6e92-4415-af62-e43aa58c7638/page/vDIuF' },
  { name: 'Chart_16_4', url: 'https://lookerstudio.google.com/embed/reporting/a3fec924-165b-4de1-83e2-b71974676692/page/vDIuF' },
  { name: 'Chart_17_1', url: 'https://lookerstudio.google.com/embed/reporting/41212954-bb4f-4d87-902f-9d2cefe6c51a/page/vDIuF' },
  { name: 'Chart_17_2', url: 'https://lookerstudio.google.com/embed/reporting/023ffedf-7452-4c52-84d9-6fc65af1d411/page/vDIuF' },
  { name: 'Chart_18_1', url: 'https://lookerstudio.google.com/embed/reporting/f78c06ab-ea3f-48ea-9da3-7bf7f9faec7d/page/GKIuF' },
  { name: 'Chart_18_2', url: 'https://lookerstudio.google.com/embed/reporting/17cea54b-65e1-4250-910c-9ae96583fd92/page/EhKuF' },
  { name: 'Chart_19_1', url: 'https://lookerstudio.google.com/embed/reporting/454b96b2-6a29-4c7e-8dd2-e034498efcc4/page/vLQuF' },
  { name: 'Chart_19_2', url: 'https://lookerstudio.google.com/embed/reporting/65fb7fcd-138c-41c8-90e9-5263eeb77d47/page/fbQuF' },
  { name: 'Chart_1_1',  url: 'https://lookerstudio.google.com/embed/reporting/9a88eef8-ec3d-4bdf-9441-fe14156efde6/page/TAWuF' },
  { name: 'Chart_1_10', url: 'https://lookerstudio.google.com/embed/reporting/cf19093b-7baf-4919-af92-f748e582c885/page/yUWuF' },
  { name: 'Chart_1_12', url: 'https://lookerstudio.google.com/embed/reporting/15fbf5f7-abfc-4de1-b005-3b6cb75e7d97/page/VcIuF' },
  { name: 'Chart_1_13', url: 'https://lookerstudio.google.com/embed/reporting/93813e58-3fd7-4111-a3c1-5b0affe1b42a/page/HBJuF' },
  { name: 'Chart_1_14', url: 'https://lookerstudio.google.com/embed/reporting/0e79a4bd-cb0e-4b6a-88eb-12bce7cd9ef0/page/VqNuF' },
  { name: 'Chart_1_16', url: 'https://lookerstudio.google.com/embed/reporting/50f3a15b-9499-4050-988d-fb10dfbbc7ae/page/nwNuF' },
  { name: 'Chart_1_17', url: 'https://lookerstudio.google.com/embed/reporting/0bd44f76-d47e-438c-bec4-84da61f66443/page/kfOuF' },
  { name: 'Chart_1_2',  url: 'https://lookerstudio.google.com/embed/reporting/573d24ca-351f-4b3f-840f-df6ef6c91f0f/page/L9PuF' },
  { name: 'Chart_1_3',  url: 'https://lookerstudio.google.com/embed/reporting/9abc7648-a955-4515-a381-d12fbc4e49c0/page/2IIuF' },
  { name: 'Chart_1_5',  url: 'https://lookerstudio.google.com/embed/reporting/f90adcc6-e1a1-417b-8535-592bfcdf5ab0/page/X0IuF' },
  { name: 'Chart_1_7',  url: 'https://lookerstudio.google.com/embed/reporting/a7e74be4-e80f-4b14-930b-64daa72ff5f7/page/U4IuF' },
  { name: 'Chart_1_9',  url: 'https://lookerstudio.google.com/embed/reporting/11ded633-430f-4bcd-a6b2-c5d9629f21de/page/76IuF' },
  { name: 'Chart_20_1', url: 'https://lookerstudio.google.com/embed/reporting/ad7e8b7f-2273-44d9-bca9-b7d78a62fb16/page/76IuF' },
  { name: 'Chart_20_2', url: 'https://lookerstudio.google.com/embed/reporting/95f2a007-4299-4390-8aa2-563fa2856bfd/page/TDIuF' },
  { name: 'Chart_21_1', url: 'https://lookerstudio.google.com/embed/reporting/88d99758-28bc-466d-97c1-dd821604fc6f/page/vlNuF' },
  { name: 'Chart_21_2', url: 'https://lookerstudio.google.com/embed/reporting/de0b3f62-fc11-4d02-bb78-4a84b32d6cc8/page/2YNuF' },
  { name: 'Chart_22_1', url: 'https://lookerstudio.google.com/embed/reporting/d228e4c9-e482-4b94-a016-bf152d880dce/page/oZPuF' },
  { name: 'Chart_22_2', url: 'https://lookerstudio.google.com/embed/reporting/c2a67109-2ed4-49a3-bfa3-5ce4434f7a9a/page/6SNuF' },
  { name: 'Chart_22_3', url: 'https://lookerstudio.google.com/embed/reporting/a34479bf-1fed-4c69-93dd-25ade12d824b/page/6SNuF' },
  { name: 'Chart_23_1', url: 'https://lookerstudio.google.com/embed/reporting/06141e46-0f44-4e65-a4b5-ff8496f9912d/page/6SNuF' },
  { name: 'Chart_23_2', url: 'https://lookerstudio.google.com/embed/reporting/cc9c634f-cf93-43c1-bf2b-41ffc45a7414/page/rVIuF' },
  { name: 'Chart_24_1', url: 'https://lookerstudio.google.com/embed/reporting/26faab3d-7bfb-4cdc-b0fa-f29a13af2af7/page/LhIuF' },
  { name: 'Chart_24_2', url: 'https://lookerstudio.google.com/embed/reporting/335b310a-7871-4648-b45b-d4fea153850c/page/LhIuF' },
  { name: 'Chart_25_1', url: 'https://lookerstudio.google.com/embed/reporting/57c87d0a-9ff3-4c6e-b66f-9d6dd5ac308e/page/LhIuF' },
  { name: 'Chart_25_2', url: 'https://lookerstudio.google.com/embed/reporting/3cd58535-0b51-489b-804e-cfc79f5a4739/page/LhIuF' },
  { name: 'Chart_25_3', url: 'https://lookerstudio.google.com/embed/reporting/c89fdbd7-e93a-423d-a47e-293e60467155/page/LhIuF' },
  { name: 'Chart_26_1', url: 'https://lookerstudio.google.com/embed/reporting/04c53a42-b3ed-4aec-8240-5464efa5c44a/page/nLHuF' },
  { name: 'Chart_26_2', url: 'https://lookerstudio.google.com/embed/reporting/16eeb30c-0173-430b-8654-a78a99c217ab/page/pDIuF' },
  { name: 'Chart_2_1',  url: 'https://lookerstudio.google.com/embed/reporting/e4f48e2b-d154-496a-b200-5da9db9f976b/page/GwNuF' },
  { name: 'Chart_2_2',  url: 'https://lookerstudio.google.com/embed/reporting/52ab42d5-d989-4b65-ba8c-cf209ec1df3d/page/C3IuF' },
  { name: 'Chart_2_3',  url: 'https://lookerstudio.google.com/embed/reporting/a8dd0c4e-89a9-45b6-a0e3-7cdd752c87af/page/GwNuF' },
  { name: 'Chart_2_4',  url: 'https://lookerstudio.google.com/embed/reporting/ec16f810-5e04-4544-8bac-7fced8eec2b6/page/osNuF' },
  { name: 'Chart_3_1',  url: 'https://lookerstudio.google.com/embed/reporting/374b1d27-0c67-4e1a-b8e2-7309e98ff0e5/page/tGHuF' },
  { name: 'Chart_3_2',  url: 'https://lookerstudio.google.com/embed/reporting/d71d2e28-b794-470d-affe-b87c55220c08/page/EEIuF' },
  { name: 'Chart_3_3',  url: 'https://lookerstudio.google.com/embed/reporting/63131be2-7feb-4d0f-956e-9e51cd800718/page/QXIuF' },
  { name: 'Chart_4_1',  url: 'https://lookerstudio.google.com/embed/reporting/aad17bae-d7b7-4cde-a87e-f8e6105b926a/page/TlIuF' },
  { name: 'Chart_4_2',  url: 'https://lookerstudio.google.com/embed/reporting/f20aaeb4-9042-425d-a3a3-d5c51e02ded1/page/40IuF' },
  { name: 'Chart_5_1',  url: 'https://lookerstudio.google.com/embed/reporting/ac40166b-c6d9-4de5-8b0d-ebb6c0728788/page/0TNuF' },
  { name: 'Chart_5_2',  url: 'https://lookerstudio.google.com/embed/reporting/b83f7793-cb97-4f97-8242-65eb464d566a/page/KhHuF' },
  { name: 'Chart_6_1',  url: 'https://lookerstudio.google.com/embed/reporting/ea0c58ef-95ee-444d-b1b9-ea04e1fd97f8/page/nBIuF' },
  { name: 'Chart_6_2',  url: 'https://lookerstudio.google.com/embed/reporting/dd1a3b11-3fcf-46c9-9ecf-bed82d2f0256/page/rNIuF' },
  { name: 'Chart_7_1',  url: 'https://lookerstudio.google.com/embed/reporting/a2164aa4-53a9-4cdd-bad0-e56a5c09c525/page/HaIuF' },
  { name: 'Chart_7_2',  url: 'https://lookerstudio.google.com/embed/reporting/2fffe580-a13a-49e0-997e-ee02db71b022/page/ShIuF' },
  { name: 'Chart_8_1',  url: 'https://lookerstudio.google.com/embed/reporting/29c448c1-0e14-459e-8e18-eaddfb326c83/page/lONuF' },
  { name: 'Chart_8_2',  url: 'https://lookerstudio.google.com/embed/reporting/36738d0e-f487-4ed2-97a5-31879c809746/page/WGHuF' },
  { name: 'Chart_8_3',  url: 'https://lookerstudio.google.com/embed/reporting/7623e44d-8578-4b0c-88ad-32dd486a1987/page/yVHuF' },
  { name: 'Chart_9_1',  url: 'https://lookerstudio.google.com/embed/reporting/8fa7d134-ec03-4f89-b3de-7a34c9e42f5e/page/meHuF' },
  { name: 'Chart_9_2',  url: 'https://lookerstudio.google.com/embed/reporting/2893ba54-f919-472e-abfc-88f377ccf394/page/meHuF' },
  { name: 'Chart_9_3',  url: 'https://lookerstudio.google.com/embed/reporting/722c5180-d4e6-4a29-b60e-b983bc3f8aff/page/meHuF' },
];

async function screenshotAndUpload(browser, chart, studioId) {
  const page = await browser.newPage();
  try {
    await page.setViewport({ width: 1280, height: 800, deviceScaleFactor: 1 });
    await page.setUserAgent(
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
    );
    await page.goto(chart.url, { waitUntil: 'networkidle2', timeout: 90_000 });
    await new Promise(r => setTimeout(r, 8000)); // wait for chart render

    const screenshot = await page.screenshot({ type: 'png', fullPage: false });

    // Upload to S3 as phm_look/{studio_dashboards_id}.png
    const s3Key = `phm_look/${studioId}.png`;
    await s3.send(new PutObjectCommand({
      Bucket:      BUCKET,
      Key:         s3Key,
      Body:        screenshot,
      ContentType: 'image/png',
    }));

    console.log(`[OK]    ${chart.name} (id=${studioId}) → s3://${BUCKET}/${s3Key}`);
    return true;
  } catch (err) {
    console.error(`[ERROR] ${chart.name}: ${err.message}`);
    return false;
  } finally {
    await page.close();
  }
}

(async () => {
  console.log(`Starting upload of ${charts.length} PHM charts to S3 bucket: ${BUCKET}\n`);

  const browser = await puppeteer.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage', '--disable-gpu'],
  });

  let ok = 0, fail = 0;

  for (const chart of charts) {
    const studioId = chartIds[chart.name];
    if (!studioId) {
      console.warn(`[SKIP]  ${chart.name} — no studio_dashboards id found`);
      fail++;
      continue;
    }
    const success = await screenshotAndUpload(browser, chart, studioId);
    success ? ok++ : fail++;
  }

  await browser.close();

  console.log(`\n===================================`);
  console.log(`Uploaded : ${ok}`);
  console.log(`Failed   : ${fail}`);
  console.log(`S3 path  : s3://${BUCKET}/phm_look/{id}.png`);
})();
