/**
 * caixa-csv-downloader.js — Baixa CSVs da CAIXA usando Puppeteer (resolve Radware JS challenge)
 * Estratégia:
 *   1. Abre homepage da CAIXA com Puppeteer para resolver o desafio JS do Radware
 *   2. Para cada estado, abre uma nova aba que herda os cookies da sessão
 *   3. Faz fetch do CSV dentro do contexto do browser (passa pelo Radware)
 *   4. Grava o arquivo CSV no diretório de destino
 * Uso: node caixa-csv-downloader.js [SP RJ GO ...]   (sem args = todos os estados)
 */
const puppeteer = require('puppeteer-core');
const fs = require('fs');
const path = require('path');

const BASE_URL  = 'https://venda-imoveis.caixa.gov.br/';
const CSV_DIR   = process.env.CSV_DIR || '/var/www/dados/csv';
const LOG_FILE  = '/var/log/arremate_scraper.log';

/* Detecta o caminho do Chrome conforme o ambiente:
 * - GitHub Actions (ubuntu-latest): google-chrome instalado em /usr/bin/google-chrome
 * - VPS Kinghost: /snap/bin/chromium
 * - Pode ser sobrescrito pela variável CHROME_PATH */
const CHROME_PATH = process.env.CHROME_PATH ||
  (fs.existsSync('/usr/bin/google-chrome')    ? '/usr/bin/google-chrome'    :
   fs.existsSync('/usr/bin/chromium-browser') ? '/usr/bin/chromium-browser' :
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

function isBlocked(text) {
  const sample = text.slice(0, 4096);
  return sample.includes('Radware') || sample.includes('captcha') ||
         sample.includes('perfdrive') || sample.includes('shieldsquare') ||
         sample.includes('Bot Manager') || sample.includes('hcaptcha') ||
         sample.includes('<!DOCTYPE') || sample.includes('<html');
}

/**
 * Baixa um CSV abrindo uma nova aba no mesmo browser context.
 * Novas abas herdam os cookies da sessão estabelecida na aba principal.
 */
async function baixarCsv(browser, uf) {
  const url = `${BASE_URL}listaweb/Lista_imoveis_${uf}.csv`;
  const tabela = await browser.newPage();

  try {
    await tabela.setUserAgent(UA);
    await tabela.setExtraHTTPHeaders({ 'Accept-Language': 'pt-BR,pt;q=0.9,en;q=0.8' });

    // Navega para uma página do mesmo domínio para ter contexto válido
    await tabela.goto(`${BASE_URL}sistema/site/acesso.aspx?hdnimovel=&tipo=imovel&estados=${uf}`, {
      waitUntil: 'domcontentloaded', timeout: 15000
    }).catch(() => {});

    // Faz o fetch do CSV dentro do contexto do browser usando os cookies da sessão
    const result = await tabela.evaluate(async (csvUrl) => {
      try {
        const resp = await fetch(csvUrl, {
          credentials: 'include',
          headers: { 'Accept': 'text/csv,text/plain,*/*;q=0.9' },
        });
        if (!resp.ok) return { error: `HTTP ${resp.status}`, content: null };
        const text = await resp.text();
        return { error: null, content: text };
      } catch(e) {
        return { error: e.message, content: null };
      }
    }, url);

    return result;
  } finally {
    await tabela.close().catch(() => {});
  }
}

async function main() {
  if (!fs.existsSync(CSV_DIR)) fs.mkdirSync(CSV_DIR, { recursive: true });

  log(`=== CSV DOWNLOADER (puppeteer + fetch em nova aba) ===`);
  log(`Chrome: ${CHROME_PATH}`);
  log(`Estados: ${UFS.join(',')}`);

  const browser = await puppeteer.launch({
    executablePath: CHROME_PATH,
    headless: 'new',
    args: [
      '--no-sandbox', '--disable-setuid-sandbox',
      '--disable-dev-shm-usage', '--disable-gpu',
      '--disable-extensions',
    ],
  });

  // Aba principal: resolve o desafio JS do Radware na homepage
  const paginaPrincipal = await browser.newPage();
  await paginaPrincipal.setUserAgent(UA);
  await paginaPrincipal.setExtraHTTPHeaders({ 'Accept-Language': 'pt-BR,pt;q=0.9,en;q=0.8' });

  log('Iniciando sessão na CAIXA...');
  try {
    await paginaPrincipal.goto(BASE_URL, { waitUntil: 'networkidle2', timeout: 30000 });
  } catch(e) { log(`  (aviso homepage: ${e.message})`); }
  await new Promise(r => setTimeout(r, 4000));
  log('Sessão iniciada.');

  let ok = 0, bloqueados = 0, falhou = 0;

  for (const uf of UFS) {
    log(`CSV ${uf}...`);

    try {
      const result = await baixarCsv(browser, uf);

      if (result.error || !result.content) {
        falhou++;
        log(`  ✗ ERRO: ${result.error || 'sem conteúdo'}`);
      } else if (isBlocked(result.content)) {
        bloqueados++;
        log(`  ✗ BLOQUEADO por Radware/captcha`);
      } else if (result.content.length < 500) {
        falhou++;
        log(`  ✗ FALHA: conteúdo muito pequeno (${result.content.length} bytes)`);
      } else {
        const dest = path.join(CSV_DIR, `Lista_imoveis_${uf}.csv`);
        // Grava como binário para preservar encoding ISO-8859-1 da CAIXA
        fs.writeFileSync(dest, Buffer.from(result.content, 'binary'));
        const linhas = result.content.split('\n').length;
        ok++;
        log(`  ✓ OK: ${linhas} linhas (${(result.content.length / 1024).toFixed(1)} KB)`);
      }
    } catch(e) {
      falhou++;
      log(`  ✗ ERRO: ${e.message}`);
    }

    await new Promise(r => setTimeout(r, 800));
  }

  await browser.close();

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
