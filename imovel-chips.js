/**
 * imovel-chips.js
 * Extrai ícones de quartos, banheiros, vagas e área da descrição do CSV da CAIXA.
 * Usado em index.html e resultados.html
 */
(function(){
'use strict';

function extrairChips(desc){
  if(!desc) return [];
  var chips = [];
  var m;

  // Quartos / dormitórios
  m = desc.match(/(\d+)\s*qto/i);
  if(m){
    var q = parseInt(m[1]);
    chips.push({ icon:'🛏️', texto: q + (q>1?' quartos':' quarto') });
  }

  // Banheiros / WC
  m = desc.match(/(\d+)\s*(?:banheiro|wc|ban\.)/i);
  if(!m) m = desc.match(/wc/i) ? ['','1'] : null;
  if(m){
    var b = parseInt(m[1]) || 1;
    chips.push({ icon:'🚿', texto: b + (b>1?' banheiros':' banheiro') });
  }

  // Vagas de garagem
  m = desc.match(/(\d+)\s*vaga/i);
  if(m){
    var v = parseInt(m[1]);
    chips.push({ icon:'🚗', texto: v + (v>1?' vagas':' vaga') });
  }

  // Área privativa — suporta: "45,16M2 DE AREA PRIVATIVA" e "Área Privativa: 45,16 m²"
  m = desc.match(/([\d,\.]+)\s*(?:m[²2])?\s*de\s*[aá]rea\s*privativa/i) ||
      desc.match(/[aá]rea\s*privativa[:\s]+([\d,\.]+)/i);
  if(m){
    var ap = parseFloat((m[1]||m[2]||'0').replace(',','.'));
    if(ap>0) chips.push({ icon:'📐', texto: ap.toLocaleString('pt-BR',{maximumFractionDigits:1})+'m²' });
  }

  // Área total (só se não tiver privativa)
  if(!desc.match(/[aá]rea\s*privativa/i)){
    m = desc.match(/([\d,\.]+)\s*(?:m[²2])?\s*de\s*[aá]rea\s*total/i) ||
        desc.match(/[aá]rea\s*total[:\s]+([\d,\.]+)/i);
    if(m){
      var at = parseFloat((m[1]||m[2]||'0').replace(',','.'));
      if(at>0) chips.push({ icon:'📐', texto: at.toLocaleString('pt-BR',{maximumFractionDigits:1})+'m²' });
    }
  }

  // Área do terreno
  m = desc.match(/([\d,\.]+)\s*(?:m[²2])?\s*de\s*[aá]rea\s*do\s*terreno/i) ||
      desc.match(/[aá]rea\s*(?:do\s*)?terreno[:\s]+([\d,\.]+)/i);
  if(m){
    var tr = parseFloat((m[1]||m[2]||'0').replace(',','.'));
    if(tr>0) chips.push({ icon:'🌳', texto: tr.toLocaleString('pt-BR',{maximumFractionDigits:1})+'m² terreno' });
  }

  return chips;
}

function buildChipsRow(desc){
  var chips = extrairChips(desc);
  if(!chips.length) return null;

  var row = document.createElement('div');
  row.className = 'imovel-atributos';
  row.style.cssText = 'display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;';

  chips.forEach(function(c){
    var span = document.createElement('span');
    span.style.cssText = 'display:inline-flex;align-items:center;gap:4px;background:#f0f7ff;border:1px solid #dbeafe;border-radius:999px;padding:3px 10px;font-size:.76rem;font-weight:700;color:#1e3a8a;white-space:nowrap;';
    span.textContent = c.icon + ' ' + c.texto;
    row.appendChild(span);
  });

  return row;
}

window.extrairChips   = extrairChips;
window.buildChipsRow  = buildChipsRow;

})();
