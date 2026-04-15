/* logo-fit.js — alinha título e subtítulo no mobile:
   ambos começam e terminam no mesmo ponto. */
(function () {
  var TITLE_MAX = 32;
  var TITLE_MIN = 12;
  var SUB_MAX   = 12;
  var SUB_MIN   =  7;

  function fit() {
    if (window.innerWidth > 900) {
      /* desktop: remove todos os overrides inline */
      document.querySelectorAll('.logo-txt').forEach(function (el) {
        el.style.width = '';
      });
      document.querySelectorAll('.logo-aio').forEach(function (el) {
        el.style.fontSize = '';
      });
      document.querySelectorAll('.logo-sub-mobile').forEach(function (el) {
        el.style.fontSize = '';
      });
      return;
    }

    document.querySelectorAll('.logo-txt').forEach(function (txt) {
      var aio = txt.querySelector('.logo-aio');
      if (!aio) return;

      /* 0. Limpa width anterior para medir o espaço real disponível */
      txt.style.width = '';
      var containerW = txt.clientWidth;
      if (!containerW) return;

      /* ── 1. Escala o TÍTULO para preencher o container ── */
      var s = aio.style;
      var savedOv = s.overflow;
      var savedWs = s.whiteSpace;

      s.fontSize   = '16px';
      s.overflow   = 'visible';
      s.whiteSpace = 'nowrap';
      var titleNatW = aio.getBoundingClientRect().width;
      s.overflow   = savedOv;
      s.whiteSpace = savedWs;

      if (!titleNatW) return;

      var titleIdeal = 16 * containerW / titleNatW;
      s.fontSize = Math.min(Math.max(titleIdeal, TITLE_MIN), TITLE_MAX) + 'px';

      /* ── 2. Mede a largura REAL do título após aplicar o font-size ──
         (pode ser menor que containerW se atingiu o teto TITLE_MAX)   */
      s.overflow   = 'visible';
      s.whiteSpace = 'nowrap';
      var titleActualW = aio.getBoundingClientRect().width;
      s.overflow   = savedOv;
      s.whiteSpace = savedWs;

      /* ── 3. Fixa o container na largura real do título ──
         Isso garante que subtítulo e título compartilhem
         exatamente o mesmo início e fim.                   */
      txt.style.width = titleActualW + 'px';

      /* ── 4. Escala o SUBTÍTULO para a largura real do título ── */
      var subSpan = txt.querySelector('.logo-sub-mobile');
      if (!subSpan) return;

      var subParent     = subSpan.parentElement;
      var savedParentOv = subParent.style.overflow;
      var savedSubWs    = subSpan.style.whiteSpace;

      subParent.style.overflow = 'visible';
      subSpan.style.fontSize   = '11px';
      subSpan.style.whiteSpace = 'nowrap';
      var subNatW = subSpan.getBoundingClientRect().width;
      subParent.style.overflow = savedParentOv;
      subSpan.style.whiteSpace = savedSubWs;

      if (!subNatW) return;

      /* escala para a largura real do título;
         se o texto do subtítulo for muito longo e ficar abaixo de
         SUB_MIN, o overflow:hidden do .logo-sub já o recorta na
         borda certa — alinhamento garantido de qualquer forma. */
      var subIdeal = 11 * titleActualW / subNatW;
      subSpan.style.fontSize = Math.min(Math.max(subIdeal, SUB_MIN), SUB_MAX) + 'px';
    });
  }

  document.addEventListener('DOMContentLoaded', fit);
  window.addEventListener('resize', fit);
})();
