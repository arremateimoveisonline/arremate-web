const puppeteer = require('puppeteer-core');
(async () => {
  console.log('Lançando Chromium...');
  const br = await puppeteer.launch({
    executablePath: '/snap/bin/chromium',
    headless: 'new',
    args: ['--no-sandbox','--disable-setuid-sandbox','--disable-dev-shm-usage','--disable-gpu','--single-process']
  });
  console.log('Chromium OK');
  const pg = await br.newPage();
  console.log('Navegando para example.com...');
  await pg.goto('https://example.com', { waitUntil: 'domcontentloaded', timeout: 15000 });
  const title = await pg.title();
  console.log('Title:', title);
  await br.close();
  console.log('OK - puppeteer funciona!');
})().catch(e => { console.error('ERR:', e.message); process.exit(1); });
