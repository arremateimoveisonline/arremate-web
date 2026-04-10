/**
 * Inspeciona HTML da CAIXA para encontrar padrões de data/encerramento
 */
const puppeteer = require('puppeteer-core');

async function main() {
  const browser = await puppeteer.launch({
    executablePath: '/snap/bin/chromium',
    headless: 'new',
    args: ['--no-sandbox','--disable-setuid-sandbox','--disable-dev-shm-usage','--disable-gpu','--single-process'],
  });
  const page = await browser.newPage();
  await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124.0.0.0 Safari/537.36');
  await page.goto('https://venda-imoveis.caixa.gov.br/', {waitUntil:'networkidle2',timeout:20000}).catch(()=>{});
  await new Promise(r=>setTimeout(r,2000));

  const hdns = ['1444420699441', '1444400380830', '10005120'];

  for (const hdn of hdns) {
    const url = 'https://venda-imoveis.caixa.gov.br/sistema/detalhe-imovel.asp?hdnimovel=' + hdn;
    console.log('\n' + '='.repeat(60));
    console.log('HDN=' + hdn);
    try {
      await page.goto(url, {waitUntil:'networkidle2',timeout:25000});
      await new Promise(r=>setTimeout(r,2000));

      const info = await page.evaluate(() => {
        const body = document.body.innerText;
        const lines = body.split('\n').map(l=>l.trim()).filter(l=>l.length>2);

        // Todas linhas com data ou hora
        const dataLines = lines.filter(l =>
          /\d{2}\/\d{2}\/\d{4}|\d{2}:\d{2}|encerramento|leil[aã]o|prazo|1[ºo]|2[ºo]/i.test(l)
        );

        // Primeiros 30 itens do body (estrutura geral)
        const primeiros = lines.slice(0, 50);

        // Contexto HTML ao redor de datas (DD/MM/YYYY)
        const html = document.documentElement.innerHTML;
        const dateMatches = [];
        const re = /\d{2}\/\d{2}\/20\d{2}/g;
        let m;
        while ((m = re.exec(html)) !== null) {
          const ctx = html.slice(Math.max(0, m.index-200), m.index+300)
            .replace(/<[^>]+>/g,' ').replace(/\s+/g,' ').trim();
          dateMatches.push(ctx);
        }

        return { dataLines, primeiros, dateMatches: dateMatches.slice(0, 8) };
      });

      console.log('\n--- Primeiras 50 linhas da página ---');
      info.primeiros.forEach((l,i) => console.log('  ['+i+'] ' + l));

      console.log('\n--- Linhas com datas/encerramento ---');
      info.dataLines.forEach(l => console.log('  ', l));

      console.log('\n--- Contexto HTML ao redor das datas ---');
      info.dateMatches.forEach((c,i) => {
        console.log('  [' + i + '] ' + c.slice(0, 400));
      });
    } catch(e) {
      console.log('ERRO:', e.message);
    }
    await new Promise(r=>setTimeout(r,3000));
  }

  await browser.close();
}

main().catch(e => { console.error('FATAL:', e.message); process.exit(1); });
