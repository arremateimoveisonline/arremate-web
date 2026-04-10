/**
 * caixa-csv-downloader.js — Baixa CSVs da CAIXA usando Puppeteer (resolve Radware JS challenge)
 * Estratégia: Puppeteer resolve Radware, usa Browser.setDownloadBehavior para capturar
 *             downloads nativos do Chrome em diretório temporário.
 * Uso: node caixa-csv-downloader.js [SP RJ GO ...]   (sem args = todos os estados)
 */
const puppeteer = require('puppeteer-core');
const fs = require('fs');
const path = require('path');

const BASE_URL  = 'https://venda-imoveis.caixa.gov.br/';
const CSV_DIR   = process.env.CSV_DIR || '/var/www/dados/csv';
const DL_TMP    = '/tmp/arremate-csv-dl';
const LOG_FILE  = '/var/log/arremate_scraper.log';

/* Detecta o caminho do Chrome conforme o ambiente:
 * - GitHub Actions (ubuntu-latest): google-chrome instalado
 * - VPS: /snap/bin/chromium
 * - Pode ser sobrescrito pela variável CHROME_PATH */
const CHROME_PATH = process.env.CHROME_PATH ||
  (require('fs').existsSync('/usr/bin/google-chrome') ? '/usr/bin/google-chrome' :
   require('fs').existsSync('/usr/bin/chromium-browser') ? '/usr/bin/chromium-browser' :
   '/snap/bin/chromium');
const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';

const ALL_UFS = ['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT',
                 'PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO'];

const args = process.argv.slice(2);
const UFS = args.length > 0 ? args.map(s => s.toUpperCase()) : ALL_UFS;

function log(msg) {
  const line = `[${new Date().toISOString().replace('T',' ').slice(0,19)}] ${msg}`;
  console.log(line);
  try { fs.appendFileSync(LOG_FILE, line + '\n'); } catch(e) {}
}

function isBlocked(buf) {
  const sample = buf.slice(0, 4096).toString('utf8');
  return sample.includes('Radware') || sample.includes('captcha') ||
         sample.includes('perfdrive') || sample.includes('shieldsquare') ||
         sample.includes('Bot Manager') || sample.includes('hcaptcha');
}

/** Remove arquivos do dir de download temporário */
function limparDlTmp() {
  try {
    for (const f of fs.readdirSync(DL_TMP)) {
      fs.unlinkSync(path.join(DL_TMP, f));
    }
  } catch(e) {}
}

/**
 * Aguarda um arquivo aparecer em DL_TMP que corresponda a `uf`.
 * Chrome nomeia o download como Lista_imoveis_XX.csv (ou .crdownload enquanto baixa).
 * Retorna o path do arquivo quando completo, ou lança erro em timeout.
 */
function aguardarDownload(uf, timeoutMs = 30000) {
  return new Promise((resolve, reject) => {
    const esperado = `Lista_imoveis_${uf}.csv`;
    const inicio = Date.now();

    const check = setInterval(() => {
      try {
        const files = fs.readdirSync(DL_TMP);
        // Arquivo completo: sem .crdownload
        if (files.includes(esperado)) {
          clearInterval(check);
          resolve(path.join(DL_TMP, esperado));
          return;
        }
        // Ainda baixando?
        const parcial = files.find(f => f.startsWith(`Lista_imoveis_${uf}`) && f.endsWith('.crdownload'));
        if (!parcial && Date.now() - inicio > 5000) {
          // Nenhum arquivo parcial nem completo após 5s — provavelmente não disparou download
          clearInterval(check);
          reject(new Error('download nao iniciado'));
          return;
        }
      } catch(e) {}

      if (Date.now() - inicio > timeoutMs) {
        clearInterval(check);
        reject(new Error('timeout aguardando download'));
      }
    }, 300);
  });
}

async function main() {
  if (!fs.existsSync(CSV_DIR)) fs.mkdirSync(CSV_DIR, { recursive: true });
  if (!fs.existsSync(DL_TMP)) fs.mkdirSync(DL_TMP, { recursive: true });
  limparDlTmp();

  log(`=== CSV DOWNLOADER (puppeteer + download nativo) ===`);
  log(`Estados: ${UFS.join(',')}`);

  const browser = await puppeteer.launch({
    executablePath: CHROME_PATH,
    headless: 'new',
    args: [
      '--no-sandbox', '--disable-setuid-sandbox',
      '--disable-dev-shm-usage', '--disable-gpu',
      '--disable-extensions', '--single-process',
    ],
  });

  // Configura download nativo via CDP no nível do browser
  const browserClient = await browser.target().createCDPSession();
  await browserClient.send('Browser.setDownloadBehavior', {
    behavior: 'allow',
    downloadPath: DL_TMP,
    eventsEnabled: true,
  });

  const page = await browser.newPage();
  await page.setUserAgent(UA);
  await page.setExtraHTTPHeaders({ 'Accept-Language': 'pt-BR,pt;q=0.9,en;q=0.8' });

  // Resolve Radware JS challenge na homepage
  log('Iniciando sessão na CAIXA...');
  try {
    await page.goto(BASE_URL, { waitUntil: 'networkidle2', timeout: 30000 });
  } catch(e) {}
  await new Promise(r => setTimeout(r, 3000));

  // Visita listagem para consolidar sessão
  try {
    await page.goto(`${BASE_URL}sistema/site/acesso.aspx?hdnimovel=&tipo=imovel&estados=SP`, {
      waitUntil: 'networkidle2', timeout: 20000
    });
  } catch(e) {}
  await new Promise(r => setTimeout(r, 2000));

  log('Sessão iniciada.');

  let ok = 0, bloqueados = 0, falhou = 0;

  for (const uf of UFS) {
    const url = `${BASE_URL}listaweb/Lista_imoveis_${uf}.csv`;
    log(`CSV ${uf}...`);
    limparDlTmp();

    try {
      // Navega para o CSV — Chrome vai disparar o download automaticamente
      page.goto(url).catch(() => {});

      // Aguarda o arquivo aparecer no dir de download
      const dlPath = await aguardarDownload(uf, 30000);

      const body = fs.readFileSync(dlPath);

      if (isBlocked(body)) {
        bloqueados++;
        log(`  ✗ BLOQUEADO por Radware/captcha`);
      } else if (body.length < 500) {
        falhou++;
        log(`  ✗ FALHA: arquivo muito pequeno (${body.length} bytes)`);
      } else {
        const dest = path.join(CSV_DIR, `Lista_imoveis_${uf}.csv`);
        fs.copyFileSync(dlPath, dest);
        const linhas = body.toString('latin1').split('\n').length;
        ok++;
        log(`  ✓ OK: ${linhas} linhas (${(body.length / 1024).toFixed(1)} KB)`);
      }
    } catch(e) {
      falhou++;
      log(`  ✗ ERRO: ${e.message}`);
    }

    await new Promise(r => setTimeout(r, 800));
  }

  await browser.close();
  limparDlTmp();

  log(`CSVs válidos: ${ok} | Bloqueados: ${bloqueados} | Falhou: ${falhou}`);

  if (ok === 0) {
    log('⚠️  0 CSVs válidos — saindo com exit 2');
    process.exit(2);
  }
  if (UFS.length >= 15 && ok < 15) {
    log(`⚠️  Apenas ${ok} CSVs válidos (mínimo 15) — saindo com exit 2`);
    process.exit(2);
  }

  process.exit(0);
}

main().catch(e => {
  log(`ERRO FATAL: ${e.message}`);
  process.exit(1);
});
