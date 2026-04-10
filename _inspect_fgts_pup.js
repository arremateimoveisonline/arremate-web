/**
 * Inspeciona HTML real da CAIXA via Puppeteer — roda direto na VPS
 */
const puppeteer = require('puppeteer-core');
const { execSync } = require('child_process');

// Pegar HDNs do banco SQLite via sqlite3 CLI
function sqlQuery(sql) {
  try {
    return execSync(`sqlite3 /var/www/dados/imoveis.db "${sql}"`).toString().trim();
  } catch(e) { return ''; }
}

const comFin  = sqlQuery("SELECT hdnimovel,tipo FROM imoveis WHERE financiamento=1 AND tipo IN ('casa','apartamento') LIMIT 3;");
const semFin  = sqlQuery("SELECT hdnimovel,tipo FROM imoveis WHERE financiamento=0 AND tipo='terreno' LIMIT 2;");

console.log('financiamento=1 (casa/apt):', comFin);
console.log('financiamento=0 (terreno):', semFin);

async function main() {
  const browser = await puppeteer.launch({
    executablePath: '/snap/bin/chromium',
    headless: 'new',
    args: ['--no-sandbox','--disable-setuid-sandbox','--disable-dev-shm-usage','--disable-gpu','--single-process'],
  });
  const page = await browser.newPage();
  await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36');

  // Sessão Radware
  await page.goto('https://venda-imoveis.caixa.gov.br/', {waitUntil:'networkidle2',timeout:20000}).catch(()=>{});
  await new Promise(r=>setTimeout(r,2000));

  const hdns = [];
  comFin.split('\n').slice(0,2).forEach(l => {
    const p=l.split('|'); if(p[0]) hdns.push({hdn:p[0].trim(),tipo:p[1]||'?',fin:1});
  });
  semFin.split('\n').slice(0,1).forEach(l => {
    const p=l.split('|'); if(p[0]) hdns.push({hdn:p[0].trim(),tipo:p[1]||'?',fin:0});
  });

  for (const item of hdns) {
    const url = `https://venda-imoveis.caixa.gov.br/sistema/detalhe-imovel.asp?hdnimovel=${item.hdn}`;
    console.log(`\n${'='.repeat(60)}`);
    console.log(`HDN=${item.hdn} tipo=${item.tipo} fin_csv=${item.fin}`);

    try {
      await page.goto(url, {waitUntil:'networkidle2',timeout:25000});
      await new Promise(r=>setTimeout(r,1500));

      const info = await page.evaluate(() => {
        // Texto completo da página
        const body = document.body.innerText;
        // Linhas relevantes
        const relevantes = body.split('\n')
          .map(l=>l.trim()).filter(l=>l.length>2)
          .filter(l => /fgts|financiamento|sbpe|permite|condi[çc]|venda/i.test(l));

        // HTML bruto das condições
        const blocos = new Set();
        document.querySelectorAll('*').forEach(el => {
          const t = (el.innerText||el.textContent||'').trim();
          if (t.length < 200 && t.length > 3 && /fgts|financiamento|sbpe|permite/i.test(t)) {
            blocos.add(t);
          }
        });

        // Pegar HTML de uma área maior ao redor de "FGTS"
        let htmlFgts = '';
        const html = document.documentElement.innerHTML;
        const pos = html.search(/FGTS/i);
        if (pos >= 0) htmlFgts = html.slice(Math.max(0,pos-300), pos+500).replace(/<[^>]+>/g,' ').replace(/\s+/g,' ');

        return { relevantes, blocos: [...blocos], htmlFgts };
      });

      console.log('\n=== Linhas com financ/fgts/sbpe ===');
      info.relevantes.forEach(l => console.log(' ', l));
      console.log('\n=== Elementos HTML ===');
      info.blocos.slice(0,20).forEach(b => console.log(' |', b));
      if (info.htmlFgts) {
        console.log('\n=== Contexto HTML ao redor de FGTS ===');
        console.log(info.htmlFgts.slice(0,600));
      }
    } catch(e) {
      console.log('ERRO:', e.message);
    }
    await new Promise(r=>setTimeout(r,3000));
  }

  await browser.close();
}

main().catch(e => { console.error('FATAL:', e.message); process.exit(1); });
