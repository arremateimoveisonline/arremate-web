/**
 * scrape-datas-leilao.js — Scraping de datas de leilão/licitação da CAIXA
 * Executado pelo GitHub Actions (IP não bloqueado pelo Radware).
 *
 * Uso: node scrape-datas-leilao.js <hdnimovel1> <hdnimovel2> ...
 * Saída: JSON com { hdnimovel: "YYYY-MM-DD HH:MM" } para atualizar o banco
 */

const https = require('https');
const fs    = require('fs');

const DETALHE_URL = 'https://venda-imoveis.caixa.gov.br/sistema/detalhe-imovel.asp?hdnimovel=';
const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';
const DELAY_MS = 1200; // 1.2s entre requests — respeitoso com o servidor
const OUTPUT_FILE = '/tmp/datas_leilao.json';

const hdns = process.argv.slice(2).filter(Boolean);

if (hdns.length === 0) {
  console.error('Uso: node scrape-datas-leilao.js <hdnimovel1> <hdnimovel2> ...');
  process.exit(1);
}

function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }

function fetchUrl(url) {
  return new Promise((resolve, reject) => {
    const req = https.get(url, {
      headers: {
        'User-Agent': UA,
        'Accept': 'text/html,application/xhtml+xml,*/*;q=0.8',
        'Accept-Language': 'pt-BR,pt;q=0.9,en;q=0.8',
        'Referer': 'https://venda-imoveis.caixa.gov.br/',
      },
      timeout: 20000,
    }, (res) => {
      // Segue redirecionamentos simples (302)
      if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
        res.resume();
        return fetchUrl(res.headers.location).then(resolve).catch(reject);
      }
      const chunks = [];
      res.on('data', c => chunks.push(c));
      res.on('end', () => resolve({ status: res.statusCode, body: Buffer.concat(chunks).toString('latin1') }));
      res.on('error', reject);
    });
    req.on('error', reject);
    req.on('timeout', () => { req.destroy(); reject(new Error('timeout')); });
  });
}

function isBlocked(html) {
  return html.includes('Radware') || html.includes('perfdrive') ||
         html.includes('Bot Manager') || html.includes('hcaptcha') ||
         html.includes('shieldsquare');
}

/**
 * Extrai data de encerramento do HTML da página de detalhe da CAIXA.
 * Formatos suportados (conforme caixa-scrape-detalhe.php):
 *   "1º Leilão: 30/04/2026 às 09:30"
 *   "Encerramento: 30/04/2026"
 *   "Prazo: 30/04/2026 09:30"
 */
function extrairData(html) {
  // Padrão 1: "às HH:MM" após data DD/MM/YYYY
  let m = html.match(/(?:Encerramento|Prazo|1[ºo°]\s*Leil[aã]o|2[ºo°]\s*Leil[aã]o|Data)[^\d]*(\d{2})\/(\d{2})\/(\d{4})\s+(?:às\s+)?(\d{2}:\d{2})/i);
  if (m) return m[3] + '-' + m[2] + '-' + m[1] + ' ' + m[4];

  // Padrão 2: sem horário
  m = html.match(/(?:Encerramento|Prazo|1[ºo°]\s*Leil[aã]o|2[ºo°]\s*Leil[aã]o|Data)[^\d]*(\d{2})\/(\d{2})\/(\d{4})/i);
  if (m) return m[3] + '-' + m[2] + '-' + m[1];

  // Padrão 3: qualquer data DD/MM/YYYY com horário próximo ao texto "leilão"
  m = html.match(/(\d{2})\/(\d{2})\/(\d{4})\s+às\s+(\d{2}:\d{2})/i);
  if (m) return m[3] + '-' + m[2] + '-' + m[1] + ' ' + m[4];

  return null;
}

async function main() {
  const resultados = {};
  let ok = 0, sem_data = 0, bloqueados = 0, erros = 0;

  console.log(`Scraping datas de ${hdns.length} imóveis...`);

  for (let i = 0; i < hdns.length; i++) {
    const hdn = hdns[i];
    const url = DETALHE_URL + hdn;

    try {
      const { status, body } = await fetchUrl(url);

      if (isBlocked(body)) {
        bloqueados++;
        console.error(`[${i+1}/${hdns.length}] ${hdn}: BLOQUEADO por Radware`);
        // Se bloqueou, parar para não queimar o IP do GitHub Actions
        if (bloqueados >= 5) {
          console.error('5 bloqueios — abortando para preservar o IP.');
          break;
        }
      } else if (status >= 400) {
        erros++;
        console.log(`[${i+1}/${hdns.length}] ${hdn}: HTTP ${status}`);
      } else {
        const data = extrairData(body);
        if (data) {
          resultados[hdn] = data;
          ok++;
          console.log(`[${i+1}/${hdns.length}] ${hdn}: ${data}`);
        } else {
          sem_data++;
          console.log(`[${i+1}/${hdns.length}] ${hdn}: sem data encontrada`);
        }
      }
    } catch (e) {
      erros++;
      console.log(`[${i+1}/${hdns.length}] ${hdn}: ERRO — ${e.message}`);
    }

    if (i < hdns.length - 1) await sleep(DELAY_MS);
  }

  console.log(`\nResultado: ${ok} com data | ${sem_data} sem data | ${bloqueados} bloqueados | ${erros} erros`);
  fs.writeFileSync(OUTPUT_FILE, JSON.stringify(resultados, null, 2));
  console.log(`Salvo em ${OUTPUT_FILE}`);

  if (bloqueados >= 5) process.exit(2);
  process.exit(0);
}

main().catch(e => { console.error('FATAL:', e.message); process.exit(1); });
