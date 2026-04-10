(() => {
const HDN_ATTR = 'data-hdnimovel';

function normalizeHdn(value) {
const digits = String(value || '').replace(/\D/g, '');
return digits.length >= 6 ? digits : '';
}

function findHdnFromHref(href) {
if (!href) return '';
try {
const u = new URL(href, window.location.origin);
const q = u.searchParams.get('hdnimovel');
return normalizeHdn(q);
} catch {
const m = String(href).match(/hdnimovel=([0-9-]+)/i);
return m ? normalizeHdn(m[1]) : '';
}
}

function pickCardContainer(el) {
return el.closest('article, [class*="card"], li, .item, .property, .result, .grid-item, div') || el.parentElement;
}

function setHdnOnCard(card, hdn) {
if (!card || !hdn) return false;
if (card.getAttribute(HDN_ATTR) === hdn) return false;
card.setAttribute(HDN_ATTR, hdn);
return true;
}

function scan() {
let updated = 0;

// 1) Preferencial: extrair hdnimovel de links (se existirem)
const links = Array.from(document.querySelectorAll('a[href*="hdnimovel="], a[href*="detalhe-imovel.asp"]'));
for (const a of links) {
  const hdn = findHdnFromHref(a.getAttribute('href') || a.href);
  if (!hdn) continue;

  const card = pickCardContainer(a);
  if (setHdnOnCard(card, hdn)) updated++;
}

// 2) Fallback: procurar padrão do ID no texto do card (ex: 878770193426-7)
const maybeCards = Array.from(document.querySelectorAll('article, [class*="card"], li, .item, .property, .result, .grid-item'));
for (const card of maybeCards) {
  if (card.hasAttribute(HDN_ATTR)) continue;

  const text = (card.textContent || '').trim();
  if (!text) continue;

  const m = text.match(/\b(\d{12,13}-\d)\b/);
  if (!m) continue;

  const hdn = normalizeHdn(m[1]);
  if (setHdnOnCard(card, hdn)) updated++;
}

const total = document.querySelectorAll(`[${HDN_ATTR}]`).length;
console.log(`[caixa-hdnimovel] atualizados=${updated} totalComHdn=${total}`);

}

// roda já e depois observa DOM (React adiciona conteúdo depois)
scan();
const obs = new MutationObserver(() => scan());
obs.observe(document.documentElement, { childList: true, subtree: true });

// deixa disponível pra você testar manualmente no console
window.__scanHdn = scan;
})();
