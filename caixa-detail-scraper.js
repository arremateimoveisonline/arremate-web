/**
 * caixa-detail-scraper.js — Scraping em lote de páginas de detalhe via Puppeteer
 *
 * Problema: curl/libcurl do PHP é bloqueado pelo Radware Bot Manager da CAIXA.
 * Solução: Chromium real via Puppeteer resolve o desafio JS e acessa normalmente.
 *
 * O que extrai de cada página:
 *   data_leilao_1 / data_encerramento  — datas em YYYY-MM-DD HH:MM:SS (BRT)
 *   fgts                               — 1 se "Permite utilização de FGTS"
 *   financiamento                      — 1 se "Permite financiamento"
 *   condominio                         — 'comprador' | 'limitada' | ''
 *   caixa_paga_excedente               — 1 se CAIXA assume excedente de 10%
 *   iptu                               — 'comprador' | 'caixa' | ''
 *   foto_url                           — URL da foto principal
 *   scraped_at                         — timestamp do scraping
 *
 * Uso:
 *   node caixa-detail-scraper.js [--limit N] [--uf SP] [--force]
 *   --limit N    processa no máximo N imóveis por execução (padrão: 300)
 *   --uf UF      restringe a um estado (ex: SP)
 *   --force      reprocessa mesmo imóveis já scraped
 */

const puppeteer   = require('puppeteer-core');
const fs          = require('fs');
const { execSync } = require('child_process');

const DB_PATH  = process.env.DB_PATH  || '/var/www/dados/imoveis.db';
const LOG_FILE = process.env.LOG_FILE || '/var/log/arremate_detail_scraper.log';
const BASE_URL = 'https://venda-imoveis.caixa.gov.br/';
const DETAIL_BASE = 'https://venda-imoveis.caixa.gov.br/sistema/detalhe-imovel.asp?hdnimovel=';

const CHROME_PATH = process.env.CHROME_PATH ||
  (fs.existsSync('/usr/bin/google-chrome')    ? '/usr/bin/google-chrome'    :
   fs.existsSync('/usr/bin/chromium-browser') ? '/usr/bin/chromium-browser' :
   '/snap/bin/chromium');

const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';

/* ── CLI args ── */
const args  = process.argv.slice(2);
const limitIdx = args.indexOf('--limit');
const LIMIT = limitIdx !== -1 && args[limitIdx + 1] ? parseInt(args[limitIdx + 1], 10) : 300;
const ufIdx = args.indexOf('--uf');
const UF    = ufIdx !== -1 && args[ufIdx + 1] && !args[ufIdx + 1].startsWith('--') ? args[ufIdx + 1].toUpperCase() : null;
const FORCE = args.includes('--force');

/* ── Logging ── */
function log(msg) {
  const line = `[${new Date().toISOString().replace('T',' ').slice(0,19)}] ${msg}`;
  console.log(line);
  try { fs.appendFileSync(LOG_FILE, line + '\n'); } catch(e) {}
}

/* ── SQLite via CLI (usa arquivo temporário para evitar problemas de escaping) ── */
const os  = require('os');
const TMP = os.tmpdir();

function sqlQuery(sql) {
  const f = `${TMP}/arremate_q_${Date.now()}.sql`;
  try {
    fs.writeFileSync(f, sql, 'utf8');
    const out = execSync(`sqlite3 "${DB_PATH}" < "${f}"`, { encoding: 'utf8', shell: '/bin/bash' });
    return out.trim().split('\n').filter(Boolean);
  } catch(e) { log(`SQL ERROR: ${e.message.split('\n')[0]}`); return []; }
  finally { try { fs.unlinkSync(f); } catch(e) {} }
}

function sqlExec(sql) {
  const f = `${TMP}/arremate_u_${Date.now()}.sql`;
  try {
    fs.writeFileSync(f, sql, 'utf8');
    execSync(`sqlite3 "${DB_PATH}" < "${f}"`, { shell: '/bin/bash' });
    return true;
  } catch(e) { log(`SQL EXEC ERROR: ${e.message.split('\n')[0]}`); return false; }
  finally { try { fs.unlinkSync(f); } catch(e) {} }
}

/* ── Parsing do HTML (replica lógica do PHP) ── */

function validarData(Y, M, D, h, min) {
  const y = parseInt(Y), mo = parseInt(M), d = parseInt(D);
  const ho = parseInt(h), mi = parseInt(min);
  if (mo < 1 || mo > 12 || d < 1 || d > 31) return '';
  if (ho > 23 || mi > 59) return '';
  if (y < 2024 || y > 2035) return '';
  const pad = n => String(n).padStart(2, '0');
  return `${y}-${pad(mo)}-${pad(d)} ${pad(ho)}:${pad(mi)}:00`;
}

function extrairDatas(html) {
  let d1 = '', d2 = '';

  // 1º Leilão explícito (SFI)
  const r1 = html.match(/1[°º]?\s*Leil[aã]o[^\d]{0,60}(\d{2})\/(\d{2})\/(\d{4})\s+[àa]s\s+(\d{2}):(\d{2})/iu);
  if (r1) d1 = validarData(r1[3], r1[2], r1[1], r1[4], r1[5]);

  // 2º Leilão explícito (SFI)
  const r2 = html.match(/2[°º]?\s*Leil[aã]o[^\d]{0,60}(\d{2})\/(\d{2})\/(\d{4})\s+[àa]s\s+(\d{2}):(\d{2})/iu);
  if (r2) d2 = validarData(r2[3], r2[2], r2[1], r2[4], r2[5]);

  // Se não achou explícito, captura todas as datas "DD/MM/YYYY às HH:MM"
  if (!d1 && !d2) {
    const all = [...html.matchAll(/(\d{2})\/(\d{2})\/(\d{4})\s+[àa]s\s+(\d{2}):(\d{2})/gi)];
    const datas = [...new Set(
      all.map(m => validarData(m[3], m[2], m[1], m[4], m[5])).filter(Boolean)
    )].sort();
    if (datas.length >= 2) { d1 = datas[0]; d2 = datas[datas.length - 1]; }
    else if (datas.length === 1) { d2 = datas[0]; }
  }

  // Fallback: DD/MM/YYYY sem hora (usa 23:59)
  if (!d1 && !d2) {
    const fb = html.match(/(?:Encerramento|Prazo|Data)[^\d]{0,20}(\d{2})\/(\d{2})\/(20\d{2})/i);
    if (fb) d2 = validarData(fb[3], fb[2], fb[1], '23', '59');
    else {
      const fb2 = html.match(/(\d{2})\/(\d{2})\/(20\d{2})/);
      if (fb2) d2 = validarData(fb2[3], fb2[2], fb2[1], '23', '59');
    }
  }

  // Promove d1 para encerramento se só tiver um
  if (d1 && !d2) { d2 = d1; d1 = ''; }

  return { data_leilao_1: d1, data_encerramento: d2 };
}

function extrairCampos(html) {
  const txt = html.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ');

  const fgts = (/Permite utiliza/i.test(txt) && /FGTS/i.test(txt)) ? 1 : 0;
  const financiamento = /Permite financiamento/i.test(txt) ? 1 : 0;

  // Condomínio — frase exata da CAIXA
  const caixaPagaExcedente =
    /Sob responsabilidade do comprador/i.test(txt) &&
    /limite de 10%/i.test(txt) &&
    /CAIXA realizar/i.test(txt) &&
    /exceder/i.test(txt) ? 1 : 0;

  let condominio = '';
  if (caixaPagaExcedente) condominio = 'limitada';
  else if (/responsabilidade do comprador/i.test(txt) && /condom/i.test(txt)) condominio = 'comprador';

  // IPTU/Tributos
  let iptu = '';
  const iptuM = txt.match(/Tributos:\s*Sob responsabilidade d[oa]\s*(comprador|arrematante|CAIXA)/i);
  if (iptuM) iptu = /caixa/i.test(iptuM[1]) ? 'caixa' : 'comprador';

  // Foto
  let foto_url = '';
  const fotoM = html.match(/src=['"][^'"]*?(\/fotos\/F[^'"]+\.jpg)['"]/i);
  if (fotoM) foto_url = 'https://venda-imoveis.caixa.gov.br' + fotoM[1];

  // Datas
  const datas = extrairDatas(html);

  return { fgts, financiamento, condominio, caixa_paga_excedente: caixaPagaExcedente, iptu, foto_url, ...datas };
}

function isBlocked(html) {
  const s = html.slice(0, 3000).toLowerCase();
  return s.includes('radware') || s.includes('captcha') || s.includes('bot manager') ||
         s.includes('perfdrive') || s.includes('hcaptcha') || s.includes('shieldsquare');
}

function esc(s) { return String(s || '').replace(/'/g, "''"); }

/* ── Inicia/renova sessão do browser (resolve desafio Radware) ── */
const BATCH_SIZE = 50; // renova sessão a cada N imóveis

async function iniciarBrowser() {
  const browser = await puppeteer.launch({
    executablePath: CHROME_PATH,
    headless: 'new',
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage', '--disable-gpu', '--disable-extensions'],
  });
  const pg = await browser.newPage();
  await pg.setUserAgent(UA);
  await pg.setExtraHTTPHeaders({ 'Accept-Language': 'pt-BR,pt;q=0.9,en;q=0.8' });
  try {
    await pg.goto(BASE_URL, { waitUntil: 'networkidle2', timeout: 30000 });
  } catch(e) { log(`(aviso homepage: ${e.message})`); }
  await new Promise(r => setTimeout(r, 4000));
  log('Sessão CAIXA pronta.');
  return browser;
}

/* ── Main ── */
async function main() {
  log('=== DETAIL SCRAPER (Puppeteer) ===');
  log(`DB: ${DB_PATH} | Limite: ${LIMIT} | UF: ${UF || 'todos'} | Force: ${FORCE} | Batch: ${BATCH_SIZE}`);

  // Busca imóveis sem data de encerramento (ou todos se --force)
  const ufFilter = UF ? `AND uf = '${UF}'` : '';
  const dateFilter = FORCE ? '' : `AND (data_encerramento IS NULL OR data_encerramento = '')`;
  const query = `SELECT hdnimovel FROM imoveis WHERE 1=1 ${ufFilter} ${dateFilter} ORDER BY CASE WHEN uf='SP' THEN 0 ELSE 1 END, RANDOM() LIMIT ${LIMIT};`;

  const rows = sqlQuery(query);
  if (rows.length === 0) { log('Nenhum imóvel pendente.'); return; }
  log(`${rows.length} imóveis para processar.`);

  let ok = 0, bloqueados = 0, erros = 0;
  const now = new Date().toISOString().replace('T', ' ').slice(0, 19);

  let browser = null;
  let blockedConsec = 0; // bloqueios consecutivos

  for (let i = 0; i < rows.length; i++) {
    // Inicia/renova browser a cada BATCH_SIZE imóveis
    if (i % BATCH_SIZE === 0) {
      if (browser) {
        log(`Renovando sessão (batch ${Math.floor(i/BATCH_SIZE)})...`);
        await browser.close().catch(() => {});
        await new Promise(r => setTimeout(r, 3000));
      }
      browser = await iniciarBrowser();
      blockedConsec = 0;
    }

    const hdn = rows[i].trim();
    if (!hdn) continue;

    const url = DETAIL_BASE + hdn;
    log(`[${i+1}/${rows.length}] ${hdn}...`);

    let html = '';
    const tab = await browser.newPage();
    try {
      await tab.setUserAgent(UA);
      await tab.setExtraHTTPHeaders({ 'Accept-Language': 'pt-BR,pt;q=0.9,en;q=0.8' });
      await tab.goto(url, { waitUntil: 'domcontentloaded', timeout: 20000 });
      html = await tab.content();
    } catch(e) {
      log(`  ✗ ERRO goto: ${e.message.split('\n')[0]}`);
      erros++;
    } finally {
      await tab.close().catch(() => {});
    }

    if (!html || html.length < 500) {
      log(`  ✗ HTML vazio ou pequeno`);
      erros++; blockedConsec++;
      await new Promise(r => setTimeout(r, 2000));
      continue;
    }

    if (isBlocked(html)) {
      log(`  ✗ BLOQUEADO`);
      bloqueados++; blockedConsec++;
      // Se 5 consecutivos bloqueados, força renovação imediata
      if (blockedConsec >= 5) {
        log('  5 bloqueios consecutivos — renovando sessão...');
        await browser.close().catch(() => {});
        await new Promise(r => setTimeout(r, 5000));
        browser = await iniciarBrowser();
        blockedConsec = 0;
      } else {
        await new Promise(r => setTimeout(r, 3000));
      }
      continue;
    }

    blockedConsec = 0;
    const d = extrairCampos(html);
    log(`  data_enc=${d.data_encerramento || '—'} | d1=${d.data_leilao_1 || '—'} | fgts=${d.fgts} | fin=${d.financiamento} | cond=${d.condominio} | iptu=${d.iptu}`);

    const sql = `UPDATE imoveis SET
      data_leilao_1='${esc(d.data_leilao_1)}',
      data_encerramento='${esc(d.data_encerramento)}',
      fgts=${d.fgts},
      financiamento=${d.financiamento},
      condominio='${esc(d.condominio)}',
      caixa_paga_excedente=${d.caixa_paga_excedente},
      iptu='${esc(d.iptu)}',
      foto_url=CASE WHEN '${esc(d.foto_url)}' != '' THEN '${esc(d.foto_url)}' ELSE foto_url END,
      scraped_at='${now}'
      WHERE hdnimovel='${esc(hdn)}';`;

    if (sqlExec(sql)) ok++;
    else erros++;

    await new Promise(r => setTimeout(r, 2000));
  }

  if (browser) await browser.close().catch(() => {});

  log(`=== FIM: OK=${ok} | Bloqueados=${bloqueados} | Erros=${erros} ===`);

  // Resumo do banco após execução
  const [total, comData] = [
    sqlQuery(`SELECT COUNT(*) FROM imoveis;`)[0] || '0',
    sqlQuery(`SELECT COUNT(*) FROM imoveis WHERE data_encerramento != '';`)[0] || '0',
  ];
  log(`Banco: ${total} total | ${comData} com data_encerramento`);

  if (bloqueados === rows.length) {
    log('ATENÇÃO: 100% bloqueados — IP pode estar na lista negra do Radware.');
    process.exit(2);
  }
  process.exit(0);
}

main().catch(e => {
  log(`ERRO FATAL: ${e.message}\n${e.stack}`);
  process.exit(1);
});
