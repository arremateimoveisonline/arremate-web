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

const DB_PATH   = process.env.DB_PATH  || '/var/www/dados/imoveis.db';
const LOG_FILE  = process.env.LOG_FILE || (require('os').tmpdir() + '/arremate_detail_scraper.log');
const LOCK_FILE = require('os').tmpdir() + '/arremate_detail_scraper.lock';
const BASE_URL = 'https://venda-imoveis.caixa.gov.br/';
const DETAIL_BASE = 'https://venda-imoveis.caixa.gov.br/sistema/detalhe-imovel.asp?hdnimovel=';

const CHROME_PATH = process.env.CHROME_PATH ||
  (fs.existsSync('C:/Program Files/Google/Chrome/Application/chrome.exe') ? 'C:/Program Files/Google/Chrome/Application/chrome.exe' :
   fs.existsSync('/usr/bin/google-chrome')    ? '/usr/bin/google-chrome'    :
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

/* ── SQLite via better-sqlite3 (funciona em Windows e Linux) ── */
const os  = require('os');
const TMP = os.tmpdir();
let _db = null;
function getDb() {
  if (!_db) {
    try {
      const Database = require('better-sqlite3');
      _db = new Database(DB_PATH);
    } catch(e) {
      // Fallback para CLI (VPS Linux)
      return null;
    }
  }
  return _db;
}

function sqlQuery(sql) {
  const db = getDb();
  if (db) {
    try {
      const rows = db.prepare(sql.replace(/;$/, '')).all();
      return rows.map(r => Object.values(r).join('|'));
    } catch(e) { log(`SQL ERROR: ${e.message.split('\n')[0]}`); return []; }
  }
  // Fallback CLI (Linux/VPS)
  const f = `${TMP}/arremate_q_${Date.now()}.sql`;
  try {
    fs.writeFileSync(f, sql, 'utf8');
    const out = execSync(`sqlite3 "${DB_PATH}" < "${f}"`, { encoding: 'utf8', shell: true });
    return out.trim().split('\n').filter(Boolean);
  } catch(e) { log(`SQL ERROR: ${e.message.split('\n')[0]}`); return []; }
  finally { try { fs.unlinkSync(f); } catch(e) {} }
}

function sqlExec(sql) {
  const db = getDb();
  if (db) {
    try {
      db.exec(sql);
      return true;
    } catch(e) { log(`SQL EXEC ERROR: ${e.message.split('\n')[0]}`); return false; }
  }
  // Fallback CLI (Linux/VPS)
  const f = `${TMP}/arremate_u_${Date.now()}.sql`;
  try {
    fs.writeFileSync(f, sql, 'utf8');
    execSync(`sqlite3 "${DB_PATH}" < "${f}"`, { shell: true });
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

/* Decodifica entidades HTML comuns e strip tags → texto limpo para regexes */
function htmlParaTexto(html) {
  return html
    .replace(/&agrave;/gi, 'à').replace(/&aacute;/gi, 'á').replace(/&atilde;/gi, 'ã')
    .replace(/&eacute;/gi, 'é').replace(/&ecirc;/gi, 'ê').replace(/&iacute;/gi, 'í')
    .replace(/&oacute;/gi, 'ó').replace(/&otilde;/gi, 'õ').replace(/&uacute;/gi, 'ú')
    .replace(/&ccedil;/gi, 'ç').replace(/&ordm;/gi, 'º').replace(/&ordf;/gi, 'ª')
    .replace(/&amp;/gi, '&').replace(/&lt;/gi, '<').replace(/&gt;/gi, '>').replace(/&nbsp;/gi, ' ')
    .replace(/&#(\d+);/g, (_, n) => String.fromCharCode(parseInt(n, 10)))
    .replace(/&#x([0-9a-f]+);/gi, (_, h) => String.fromCharCode(parseInt(h, 16)))
    .replace(/<[^>]+>/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();
}

function extrairDatas(txt) {
  // txt já deve chegar como texto limpo (sem tags, entidades decodificadas)
  let d1 = '', d2 = '';

  // Formato CAIXA: "DD/MM/YYYY - HHhMM"  ou  "DD/MM/YYYY às HH:MM"
  // (?:\s*[-–]\s*|\s+(?:[àa]s\s+)?) cobre ambos os separadores
  // [h:] cobre "10h00" e "10:00"
  const RE_DATA_HORA = /(\d{2})\/(\d{2})\/(\d{4})(?:\s*[-–]\s*|\s+(?:[àa]s\s+)?)(\d{2})[h:](\d{2})/gi;
  const RE_LEILAO1   = /(?:Data\s+d[oa]\s+)?1[°º]?\s*Leil[aã]o[^\d]{0,80}(\d{2})\/(\d{2})\/(\d{4})(?:\s*[-–]\s*|\s+(?:[àa]s\s+)?)(\d{2})[h:](\d{2})/iu;
  const RE_LEILAO2   = /(?:Data\s+d[oa]\s+)?2[°º]?\s*Leil[aã]o[^\d]{0,80}(\d{2})\/(\d{2})\/(\d{4})(?:\s*[-–]\s*|\s+(?:[àa]s\s+)?)(\d{2})[h:](\d{2})/iu;

  // 1º e 2º Leilão explícitos (SFI)
  const r1 = txt.match(RE_LEILAO1);
  if (r1) d1 = validarData(r1[3], r1[2], r1[1], r1[4], r1[5]);
  const r2 = txt.match(RE_LEILAO2);
  if (r2) d2 = validarData(r2[3], r2[2], r2[1], r2[4], r2[5]);

  // Se não achou "Nº Leilão" explícito, captura todas as datas com hora e ordena
  if (!d1 && !d2) {
    const all = [...txt.matchAll(RE_DATA_HORA)];
    const datas = [...new Set(
      all.map(m => validarData(m[3], m[2], m[1], m[4], m[5])).filter(Boolean)
    )].sort();
    if (datas.length >= 2) { d1 = datas[0]; d2 = datas[datas.length - 1]; }
    else if (datas.length === 1) { d2 = datas[0]; }
  }

  // Fallback: DD/MM/YYYY sem hora — log de aviso, não usa 23:59 fictício
  if (!d1 && !d2) {
    const fb = txt.match(/(?:Encerramento|Prazo|Data\s+de\s+Encerramento)[^\d]{0,20}(\d{2})\/(\d{2})\/(20\d{2})/i);
    if (fb) {
      log(`  ⚠ Data sem hora encontrada (${fb[1]}/${fb[2]}/${fb[3]}) — verificar página manual`);
      d2 = validarData(fb[3], fb[2], fb[1], '23', '59');
    } else {
      const fb2 = txt.match(/(\d{2})\/(\d{2})\/(20\d{2})/);
      if (fb2) {
        log(`  ⚠ Data genérica sem hora (${fb2[1]}/${fb2[2]}/${fb2[3]}) — verificar página manual`);
        d2 = validarData(fb2[3], fb2[2], fb2[1], '23', '59');
      }
    }
  }

  // Promove d1 para encerramento se só tiver um
  if (d1 && !d2) { d2 = d1; d1 = ''; }

  return { data_leilao_1: d1, data_encerramento: d2 };
}

function extrairCampos(html, pageText) {
  // pageText = innerText da página (JS já renderizado, sem tags, sem entidades)
  // Se não disponível, gera a partir do HTML
  const txt = pageText || htmlParaTexto(html);

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

  // IPTU/Tributos — regex ampla: cobre "Tributos:" e "IPTU:", com ou sem "Sob"
  let iptu = '';
  const iptuM = txt.match(/(?:Tributos|IPTU):?\s*(?:Sob\s+)?[Rr]esponsabilidade\s+d[oa]\s*(comprador|arrematante|caixa)/i);
  if (iptuM) {
    iptu = /caixa/i.test(iptuM[1]) ? 'caixa' : 'comprador';
  } else {
    // Fallback: busca "caixa" ou "arrematante/comprador" próximo à palavra Tributos/IPTU
    const tribM = txt.match(/(?:Tributos|IPTU)[^\n]{0,120}/i);
    if (tribM) {
      if (/\bcaixa\b/i.test(tribM[0])) iptu = 'caixa';
      else if (/arrematante|comprador/i.test(tribM[0])) iptu = 'comprador';
    }
  }

  // Foto (precisa do HTML com tags para encontrar o src)
  let foto_url = '';
  const fotoM = html.match(/src=['"][^'"]*?(\/fotos\/F[^'"]+\.jpg)['"]/i);
  if (fotoM) foto_url = 'https://venda-imoveis.caixa.gov.br' + fotoM[1];

  // Edital PDF (precisa do HTML com tags)
  let edital_url = '';
  const editalM = html.match(/editais\/E[^"'<\s]+\.PDF/i);
  if (editalM) edital_url = 'https://venda-imoveis.caixa.gov.br/' + editalM[0];

  // Datas — usa txt (texto limpo, JS já renderizado) para garantir match correto
  const datas = extrairDatas(txt);

  // Modalidade — aparece como linha solo no innerText (sem prefixo "Modalidade:")
  // Variantes vistas: "Compra Direta", "Venda Online", "Licitação Aberta",
  //                   "Leilão Único", "Leilão SFI - Edital Único", "1º Leilão", "2º Leilão"
  let modalidade = '';
  let modalidade_raw = '';
  const linhas = txt.split('\n').map(l => l.trim());
  for (const l of linhas) {
    if (!l || l.length > 40) continue;
    if (l.includes(':')) continue; // pula "Leiloeiro: X", "Valor: R$ ...", "Averbação dos leilões: ..."
    if (/^Compra\s+Direta\b/i.test(l))                                       { modalidade_raw = l; modalidade = 'Compra Direta'; break; }
    if (/^Venda\s+Direta\s+Online\b/i.test(l))                              { modalidade_raw = l; modalidade = 'Compra Direta'; break; }
    if (/^Venda\s+Online\b/i.test(l) && !/regras|formas|pagamento/i.test(l)){ modalidade_raw = l; modalidade = 'Venda Online'; break; }
    if (/^Licita[cç][aã]o\s+Aberta\b/i.test(l))                             { modalidade_raw = l; modalidade = 'Licitação Aberta'; break; }
    if (/^[12]?\s*[ºoO\.]?\s*Leil[aã]o\b/i.test(l))                         { modalidade_raw = l; modalidade = 'Leilão SFI - Edital Único'; break; }
  }

  // Status da CAIXA — mensagens especiais exibidas na página
  // "Venda online encerrada em DD/MM/YYYY HH:MM:SS."
  let status_caixa = '';
  const mEnc = txt.match(/Venda\s+online\s+encerrada\s+em\s+([\d]{2}\/[\d]{2}\/[\d]{4}\s+[\d]{2}:[\d]{2}:[\d]{2})/i);
  if (mEnc) {
    status_caixa = 'encerrada:' + mEnc[1];
  } else if (
    /n[aã]o\s+est[aá]\s+mais\s+dispon[ií]vel\s+para\s+venda/i.test(txt) ||
    /im[oó]vel\s+que\s+voc[eê]\s+procura\s+n[aã]o\s+est[aá]/i.test(txt) ||
    /ocorreu\s+um\s+erro\s+ao\s+tentar\s+recuperar\s+os\s+dados/i.test(txt)
  ) {
    status_caixa = 'removido';
  }

  return { fgts, financiamento, condominio, caixa_paga_excedente: caixaPagaExcedente, iptu, foto_url, edital_url, status_caixa, modalidade, modalidade_raw, ...datas };
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
  // Lock: evita duas instâncias simultâneas
  if (fs.existsSync(LOCK_FILE)) {
    const pid = parseInt(fs.readFileSync(LOCK_FILE, 'utf8').trim(), 10);
    let rodando = false;
    if (pid) {
      try { process.kill(pid, 0); rodando = true; } catch(e) { rodando = false; }
    }
    if (rodando) {
      log('Outra instância em execução (lock ativo). Saindo.');
      return;
    }
    log(`Lock órfão (PID ${pid} não existe) — removendo e continuando.`);
    fs.unlinkSync(LOCK_FILE);
  }
  fs.writeFileSync(LOCK_FILE, String(process.pid));

  log('=== DETAIL SCRAPER (Puppeteer) ===');
  log(`DB: ${DB_PATH} | Limite: ${LIMIT} | UF: ${UF || 'todos'} | Force: ${FORCE} | Batch: ${BATCH_SIZE}`);

  // Busca imóveis pendentes:
  // - sem scraped_at (nunca scraped), OU
  // - com data 23:59 (fallback antigo — precisa re-scrape com hora real), OU
  // - scraped há mais de 12 horas (leilões mudam status/datas em horas)
  // --force reprocessa todos independente do estado
  const ufFilter = UF ? `AND uf = '${UF}'` : '';
  const dateFilter = FORCE ? '' :
    `AND (scraped_at IS NULL OR scraped_at = ''
          OR data_encerramento LIKE '%23:59%'
          OR csv_updated_at > scraped_at
          OR scraped_at < datetime('now', '-7 days'))`;
  const query = `
    SELECT hdnimovel FROM imoveis WHERE 1=1 ${ufFilter} ${dateFilter}
    ORDER BY
      CASE
        WHEN scraped_at IS NULL OR scraped_at = '' THEN 0
        WHEN csv_updated_at > scraped_at THEN 1
        ELSE 2
      END ASC,
      scraped_at ASC
    LIMIT ${LIMIT};`;

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
    let pageText = '';
    const tab = await browser.newPage();
    try {
      await tab.setUserAgent(UA);
      await tab.setExtraHTTPHeaders({ 'Accept-Language': 'pt-BR,pt;q=0.9,en;q=0.8' });
      // networkidle2: aguarda JS da CAIXA renderizar datas dinamicamente
      await tab.goto(url, { waitUntil: 'networkidle2', timeout: 30000 });
      html = await tab.content();
      // innerText: texto limpo já renderizado pelo browser (datas, horas, etc.)
      pageText = await tab.evaluate(() => (document.body ? document.body.innerText : '')).catch(() => '');
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
    const d = extrairCampos(html, pageText);
    log(`  data_enc=${d.data_encerramento || '—'} | d1=${d.data_leilao_1 || '—'} | fgts=${d.fgts} | fin=${d.financiamento} | cond=${d.condominio} | iptu=${d.iptu} | mod=${d.modalidade || '—'} | status=${d.status_caixa || 'ok'}`);

    const sql = `UPDATE imoveis SET
      data_leilao_1='${esc(d.data_leilao_1)}',
      data_encerramento='${esc(d.data_encerramento)}',
      fgts=${d.fgts},
      financiamento=${d.financiamento},
      condominio='${esc(d.condominio)}',
      caixa_paga_excedente=${d.caixa_paga_excedente},
      iptu='${esc(d.iptu)}',
      foto_url=CASE WHEN '${esc(d.foto_url)}' != '' THEN '${esc(d.foto_url)}' ELSE foto_url END,
      edital_url=CASE WHEN '${esc(d.edital_url)}' != '' THEN '${esc(d.edital_url)}' ELSE edital_url END,
      modalidade=CASE WHEN '${esc(d.modalidade)}' != '' THEN '${esc(d.modalidade)}' ELSE modalidade END,
      modalidade_raw=CASE WHEN '${esc(d.modalidade_raw)}' != '' THEN '${esc(d.modalidade_raw)}' ELSE modalidade_raw END,
      status_caixa='${esc(d.status_caixa)}',
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

  try { fs.unlinkSync(LOCK_FILE); } catch(e) {}

  if (bloqueados === rows.length) {
    log('ATENÇÃO: 100% bloqueados — IP pode estar na lista negra do Radware.');
    process.exit(2);
  }
  process.exit(0);
}

main().catch(e => {
  log(`ERRO FATAL: ${e.message}\n${e.stack}`);
  try { fs.unlinkSync(LOCK_FILE); } catch(_) {}
  process.exit(1);
});
