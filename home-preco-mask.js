(function () {
  function keepDigitsComma(raw) {
    return String(raw || "")
      .replace(/[^\d,]/g, "")
      .replace(/,+/g, ",")
      .replace(/^,/, "");
  }

  function formatMoneyPtBR(raw, maxDecimals) {
    maxDecimals = typeof maxDecimals === "number" ? maxDecimals : 2;

    var cleaned = keepDigitsComma(raw);
    if (!cleaned) return "";

    var parts = cleaned.split(",");
    var intPart = (parts[0] || "").replace(/\D+/g, "");
    var decPart = (parts[1] || "").replace(/\D+/g, "").slice(0, maxDecimals);

    if (!intPart) intPart = "0";

    var intFmt = Number(intPart).toLocaleString("pt-BR", { maximumFractionDigits: 0 });
    return decPart ? intFmt + "," + decPart : intFmt;
  }

  function setCaretByDigitIndex(el, digitIndex) {
    var s = el.value;
    var count = 0;

    for (var i = 0; i < s.length; i++) {
      if (/\d/.test(s[i])) count++;
      if (count >= digitIndex) {
        el.setSelectionRange(i + 1, i + 1);
        return;
      }
    }
    el.setSelectionRange(s.length, s.length);
  }

  function parseMoneyBR(raw) {
    // "1.234,56" -> 1234.56
    // "1.234"    -> 1234
    var s = String(raw || "").trim();
    if (!s) return null;

    s = s.replace(/[^\d,\.]/g, "");
    s = s.replace(/\./g, "");
    s = s.replace(/,+/g, ",");

    if (!s) return null;

    var i = s.indexOf(",");
    if (i !== -1) {
      var left = s.slice(0, i).replace(/\D+/g, "") || "0";
      var right = s.slice(i + 1).replace(/\D+/g, "").slice(0, 2) || "0";
      var n = Number(left + "." + right);
      return isFinite(n) ? n : null;
    }

    var n2 = Number(s.replace(/\D+/g, ""));
    return isFinite(n2) ? n2 : null;
  }

  function brlInt(n) {
    return "R$ " + Number(n || 0).toLocaleString("pt-BR", { maximumFractionDigits: 0 });
  }

  function syncPriceLabels() {
    var inMin = document.getElementById("preco_min");
    var inMax = document.getElementById("preco_max");

    var lblMinA = document.getElementById("preco_min_lbl");
    var lblMaxA = document.getElementById("preco_max_lbl");

    var lblMinB = document.getElementById("slider-preco-label-min");
    var lblMaxB = document.getElementById("slider-preco-label-max");

    var nMin = inMin ? parseMoneyBR(inMin.value) : null;
    var nMax = inMax ? parseMoneyBR(inMax.value) : null;

    var tMin = nMin === null ? "" : brlInt(Math.round(nMin));
    var tMax = nMax === null ? "" : brlInt(Math.round(nMax));

    if (lblMinA) lblMinA.textContent = tMin;
    if (lblMaxA) lblMaxA.textContent = tMax;
    if (lblMinB) lblMinB.textContent = tMin;
    if (lblMaxB) lblMaxB.textContent = tMax;
  }

  function wireMoneyMask(inputId, maxDecimals) {
    var el = document.getElementById(inputId);
    if (!el) return;

    el.addEventListener("input", function () {
      var pos = el.selectionStart || 0;
      var before = el.value.slice(0, pos);
      var digitIndex = (before.match(/\d/g) || []).length;

      el.value = formatMoneyPtBR(el.value, maxDecimals);
      setCaretByDigitIndex(el, digitIndex);

      syncPriceLabels();
    });

    el.addEventListener("blur", syncPriceLabels);
  }

  document.addEventListener("DOMContentLoaded", function () {
    var pmin = document.getElementById("preco_min");
    var pmax = document.getElementById("preco_max");
    if (pmin) pmin.value = "";
    if (pmax) pmax.value = "";

    // 2 casas caso usuário digite vírgula (centavos). Se não digitar vírgula, fica inteiro.
    wireMoneyMask("preco_min", 2);
    wireMoneyMask("preco_max", 2);

    syncPriceLabels();

    // exporta o parser se você quiser usar no getFiltros depois
    window.parseMoneyBR = parseMoneyBR;
  });
})();
