<?php
$ano = date('Y');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="color-scheme" content="light">
  <title>Simulador de Financiamento CAIXA 2026 | Arremate Imóveis Online</title>
  <meta name="description" content="Simule seu financiamento imobiliário CAIXA com taxas atualizadas para 2026. No Estado de SP, conte com assessoria gratuita via imobiliária parceira credenciada (CRECI-SP 043342). Acesse e confira!">
  <meta name="keywords" content="simulador financiamento CAIXA 2026, calcular parcela financiamento, financiamento imóvel CAIXA, PRICE SAC simulação, FGTS financiamento CAIXA">
  <meta property="og:title" content="Simulador de Financiamento CAIXA 2026 | Arremate Imóveis Online">
  <meta property="og:description" content="Simule seu financiamento imobiliário CAIXA com taxas atualizadas para 2026. No Estado de SP, conte com assessoria gratuita via imobiliária parceira credenciada (CRECI-SP 043342). Acesse e confira!">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="pt_BR">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
 --azul:#0053a6;
 --azul-esc:#00366f;
 --laranja:#f39200;
 --azul-bg:#f1f7ff;
 --azul-card:#e8f2ff;
 --borda:#cfe2ff;
 --texto:#1e293b;
 --muted:#64748b;
 --radius:14px;
 --sombra:0 4px 18px rgba(0,83,166,.10);
}
html{scroll-behavior:smooth;color-scheme:light;overflow-x:hidden}
body{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--azul-bg)!important;color:var(--texto);line-height:1.55;font-size:15px}
a{color:inherit;text-decoration:none}
[id]{scroll-margin-top:80px}
input,select,textarea,button{color-scheme:light}
input,select,textarea{background-color:#fff;color:#0f172a}

/* ── HEADER ── */
.menu-chk{display:none!important;position:absolute;left:-9999px}
.site-header{position:sticky;top:0;z-index:200;background:#01468d;color:#fff;box-shadow:0 3px 12px rgba(0,0,0,.25)}
.hdr{max-width:1400px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:0 20px;min-height:84px}
.logo{display:flex;align-items:center;gap:12px;flex-shrink:0;flex-grow:0}
.logo-icon{width:70px;height:70px;flex-shrink:0;border-radius:14px;overflow:hidden}
.logo-icon-img{width:70px;height:70px;display:block;object-fit:contain;border-radius:14px}
.logo-txt{display:flex;flex-direction:column;min-width:0}
.logo-aio{font-size:1.38rem;font-weight:900;color:#fff;line-height:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-shadow:0 2px 6px rgba(0,0,0,.25)}
.logo-sub{font-size:.72rem;color:rgba(255,255,255,.88);line-height:1.2;margin-top:4px;text-align:justify;text-align-last:justify}
.logo-sub-full{display:block;width:100%}
.logo-sub-mobile{display:none}
.nav-links{display:flex;align-items:center;gap:10px;flex-wrap:nowrap;font-size:.82rem;margin-left:auto;flex-shrink:0}
.nav-links a{color:#fff;opacity:.9;font-weight:600;white-space:nowrap;transition:opacity .2s;text-decoration:none;padding:4px 2px}
.nav-links a:hover{opacity:1;text-decoration:underline;text-underline-offset:3px}
.nav-links a.active{opacity:1;background:rgba(255,255,255,.18);border-radius:8px;padding:4px 10px;font-weight:900}
.btn-nav-cta{background:var(--laranja)!important;color:#3b1f00!important;padding:7px 14px!important;border-radius:999px!important;font-weight:900!important;opacity:1!important;box-shadow:0 3px 8px rgba(0,0,0,.22)}
.btn-nav-cta:hover{filter:brightness(1.08)}
.hamburger{display:none;align-items:center;justify-content:center;width:44px;height:44px;flex-shrink:0;background:rgba(255,255,255,.12);border:1.5px solid rgba(255,255,255,.3);border-radius:10px;cursor:pointer;color:#fff;transition:background .15s}
.hamburger svg{width:20px;height:20px;display:block}
    .hamburger:hover,.hamburger:focus-visible{background:rgba(255,255,255,.22);outline:none}
.nav-mobile{display:none;flex-direction:column;width:100%;background:#dceeff;border-top:2px solid #a8cfee}
.menu-chk:checked~.nav-mobile{display:flex!important}
.nav-mobile a{display:block;padding:14px 20px;font-size:.97rem;font-weight:700;color:#0b1a33;background:#e8f3ff;border-bottom:1px solid #b8d8f5;text-decoration:none;transition:background .15s}
.nav-mobile a:hover{background:#cde5ff}
.nav-mobile a.active{background:#c0d8f8;color:var(--azul-esc);font-weight:900}
.nav-mob-cta{background:#e97500!important;color:#fff!important;font-weight:900!important;border-bottom:none!important}
.nav-mob-close{display:flex;align-items:center;padding:12px 20px;font-size:.9rem;font-weight:700;color:#01468d;background:#c8e0f8;border-bottom:2px solid #a8cfee;cursor:pointer}

/* ── PAGE HERO ── */
.page-hero{background:linear-gradient(135deg,#0b1a33 0%,#001634 50%,var(--azul) 100%);color:#fff;padding:22px 20px 20px;text-align:center}
.page-hero-inner{max-width:860px;margin:0 auto}
.page-hero h1{font-size:1.55rem;font-weight:900;line-height:1.2;margin-bottom:8px}
.page-hero h1 em{color:var(--laranja);font-style:normal}
.page-hero h2{font-size:.88rem;font-weight:400;opacity:.88;max-width:780px;margin:0 auto;line-height:1.5}

/* ── MAIN LAYOUT ── */
.main-wrap{max-width:1200px;margin:0 auto;padding:32px 20px 48px}
.sim-cols{display:grid;grid-template-columns:480px 1fr;gap:24px;align-items:start}

/* ── FORM PANEL ── */
.form-panel{background:#fff;border:1px solid var(--borda);border-radius:var(--radius);box-shadow:var(--sombra);overflow:visible}
.form-section{padding:20px 22px;border-bottom:1px solid #e8f0fb}
.form-section:last-of-type{border-bottom:none}
.form-section-title{font-size:.82rem;font-weight:900;text-transform:uppercase;letter-spacing:.07em;color:var(--azul-esc);margin-bottom:14px;display:flex;align-items:center;gap:7px}
.form-section-title span{background:var(--azul-card);border:1px solid var(--borda);border-radius:6px;padding:2px 8px;font-size:.75rem}
.fg{display:flex;flex-direction:column;gap:4px;margin-bottom:12px}
.fg:last-child{margin-bottom:0}
.fg label{font-size:.76rem;font-weight:700;color:#475569}
.fg input[type=text],.fg input[type=number],.fg select{border:1.5px solid #cbd5e1;border-radius:10px;padding:10px 14px;font-size:.88rem;outline:none;background:#fff;width:100%;font-family:inherit;color:#0f172a;transition:border-color .18s,box-shadow .18s}
.fg input:focus,.fg select:focus{border-color:var(--azul);box-shadow:0 0 0 3px rgba(0,83,166,.12)}
.fg .helper{font-size:.73rem;color:var(--muted);margin-top:3px}
.fg .helper.warn{color:#d97706}
.fg .helper.good{color:#16a34a}
.fg .helper.danger{color:#dc2626}
.frow{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.radio-group{display:flex;gap:10px;flex-wrap:wrap}
.radio-opt{display:flex;align-items:center;gap:7px;background:#f8faff;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 14px;font-size:.84rem;cursor:pointer;transition:border-color .18s,background .18s;flex:1;min-width:120px}
.radio-opt:hover{background:var(--azul-card);border-color:var(--borda)}
.radio-opt input[type=radio]{accent-color:var(--azul);width:16px;height:16px;cursor:pointer}
.radio-opt.selected{background:var(--azul-card);border-color:var(--azul)}
.radio-opt-desc{font-size:.71rem;color:var(--muted);display:block;margin-top:2px}

/* Prazo slider */
.slider-wrap{display:flex;flex-direction:column;gap:6px}
.slider-label{display:flex;justify-content:space-between;align-items:center}
.slider-label span{font-size:.78rem;color:var(--muted)}
.slider-label strong{font-size:.95rem;font-weight:900;color:var(--azul-esc)}
input[type=range]{-webkit-appearance:none;appearance:none;width:100%;height:6px;border-radius:999px;background:linear-gradient(to right,var(--azul) 0%,var(--azul) var(--pct,43%),#e2e8f0 var(--pct,43%),#e2e8f0 100%);outline:none;cursor:pointer}
input[type=range]::-webkit-slider-thumb{-webkit-appearance:none;appearance:none;width:20px;height:20px;border-radius:50%;background:var(--azul);border:3px solid #fff;box-shadow:0 2px 6px rgba(0,83,166,.35);cursor:pointer}
input[type=range]::-moz-range-thumb{width:20px;height:20px;border-radius:50%;background:var(--azul);border:3px solid #fff;box-shadow:0 2px 6px rgba(0,83,166,.35);cursor:pointer;border:none}

/* Checkbox extras */
.check-row{display:flex;align-items:flex-start;gap:10px;padding:10px 0;border-bottom:1px solid #f1f5f9}
.check-row:last-child{border-bottom:none;padding-bottom:0}
.check-row input[type=checkbox]{width:17px;height:17px;accent-color:var(--azul);cursor:pointer;flex-shrink:0;margin-top:2px}
.check-row-body{flex:1}
.check-row-label{font-size:.86rem;font-weight:700;color:#1e293b;cursor:pointer}
.check-row-sub{font-size:.74rem;color:var(--muted);margin-top:2px}
.check-extra{margin-top:8px;display:none}
.check-extra.open{display:block}

/* Btn calculate */
.btn-calc{width:auto;min-width:220px;background:linear-gradient(120deg,var(--laranja),#ffb347);border:none;border-radius:12px;padding:14px 32px;font-weight:900;font-size:1rem;color:#3b2200;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:9px;box-shadow:0 4px 16px rgba(243,146,0,.35);font-family:inherit;transition:filter .18s,box-shadow .18s;margin:20px auto 22px}
.btn-calc:hover{filter:brightness(1.06);box-shadow:0 6px 22px rgba(243,146,0,.45)}
.btn-calc:active{filter:brightness(.97)}
.form-disclaimer{font-size:.73rem;color:var(--muted);text-align:center;padding:0 22px 18px;line-height:1.5}

/* ── RESULTS PANEL ── */
.results-panel{display:flex;flex-direction:column;gap:16px}

/* Empty state */
.result-empty{background:#fff;border:1.5px dashed var(--borda);border-radius:var(--radius);padding:40px 24px;text-align:center;box-shadow:var(--sombra)}
.result-empty .empty-icon{font-size:3.5rem;display:block;margin-bottom:14px;opacity:.7}
.result-empty h3{font-size:1.05rem;font-weight:900;color:var(--azul-esc);margin-bottom:8px}
.result-empty p{font-size:.88rem;color:var(--muted)}

/* Result cards */
.result-card{background:#fff;border:1px solid var(--borda);border-radius:var(--radius);box-shadow:var(--sombra);overflow:hidden}
.result-card-hdr{background:linear-gradient(135deg,#0f172a,#1d4ed8);color:#fff;padding:13px 18px;display:flex;align-items:center;gap:9px;font-weight:900;font-size:.9rem}
.result-card-hdr span{font-size:1.1rem}
.result-card-body{padding:16px 18px}
.result-row{display:flex;justify-content:space-between;align-items:flex-start;padding:7px 0;border-bottom:1px solid #f1f5f9;gap:12px}
.result-row:last-child{border-bottom:none;padding-bottom:0}
.result-row.highlight{background:linear-gradient(90deg,#fffbeb,#fff8f0);margin:-1px -18px;padding:8px 18px;border-radius:0}
.result-row.highlight .result-val{color:var(--laranja);font-size:1.05rem;font-weight:900}
.result-label{font-size:.83rem;color:var(--muted);flex-shrink:0}
.result-val{font-size:.88rem;font-weight:700;color:#0f172a;text-align:right}
.result-val.green{color:#16a34a}
.result-val.yellow{color:#d97706}
.result-val.red{color:#dc2626}

/* renda bar */
.renda-bar-wrap{margin-top:10px}
.renda-bar-bg{height:10px;border-radius:999px;background:#e2e8f0;overflow:hidden;margin-bottom:4px}
.renda-bar-fill{height:100%;border-radius:999px;transition:width .5s ease}
.renda-bar-fill.green{background:#16a34a}
.renda-bar-fill.yellow{background:#d97706}
.renda-bar-fill.red{background:#dc2626}
.renda-bar-labels{display:flex;justify-content:space-between;font-size:.7rem;color:var(--muted)}

/* amort table */
.amort-table{width:100%;border-collapse:collapse;font-size:.78rem;margin-top:4px}
.amort-table th{background:#f1f5f9;color:#475569;font-weight:700;padding:7px 10px;text-align:right;border-bottom:2px solid #e2e8f0}
.amort-table th:first-child{text-align:left}
.amort-table td{padding:6px 10px;text-align:right;border-bottom:1px solid #f1f5f9;color:#1e293b}
.amort-table td:first-child{text-align:left;color:var(--muted);font-weight:600}
.amort-table tr:last-child td{border-bottom:none;background:#fffbeb;font-weight:700}
.amort-sep td{background:#f8fafc;color:var(--muted)!important;font-style:italic;font-size:.72rem;text-align:center!important;padding:4px 10px}

/* CTA card */
.cta-card{background:linear-gradient(135deg,#0b1a33,#01468d);color:#fff;border-radius:var(--radius);padding:22px 22px;box-shadow:0 4px 18px rgba(0,53,106,.3)}
.cta-card h3{font-size:1rem;font-weight:900;margin-bottom:8px;line-height:1.3}
.cta-card p{font-size:.84rem;opacity:.9;margin-bottom:16px;line-height:1.5}
.btn-whats{display:inline-flex;align-items:center;gap:8px;background:#25d366;color:#fff;font-weight:900;font-size:.9rem;padding:11px 22px;border-radius:999px;box-shadow:0 4px 12px rgba(0,0,0,.25);transition:filter .18s;white-space:nowrap;flex-shrink:0}
.btn-whats:hover{filter:brightness(1.08)}
/* CTA full-width */
.cta-full{border-radius:var(--radius);margin-top:24px}
.cta-full-inner{display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap}
.cta-full-text{flex:1;min-width:0}
.cta-full h3{font-size:1.05rem;font-weight:900;margin-bottom:8px;line-height:1.3}
.cta-full p{font-size:.88rem;opacity:.9;line-height:1.6;margin-bottom:0}

/* ── INFO CARDS ── */
.info-section-wrap{background:#fff;border-top:1px solid var(--borda);border-bottom:1px solid var(--borda)}
.info-section{max-width:1200px;margin:0 auto;padding:36px 20px 40px}
.info-block-hdr{margin-bottom:20px}
.info-block-title{font-size:1.15rem;font-weight:900;color:var(--azul-esc);margin-bottom:6px}
.info-block-sub{font-size:.9rem;color:var(--muted)}
.info-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
.info-card{background:#fff;border:1px solid var(--borda);border-radius:var(--radius);padding:20px;box-shadow:var(--sombra)}
.info-card-icon{font-size:2rem;margin-bottom:12px;display:block}
.info-card h3{font-size:.97rem;font-weight:900;color:var(--azul-esc);margin-bottom:8px}
.info-card p{font-size:.83rem;color:var(--muted);line-height:1.55}
.info-card a{display:inline-flex;align-items:center;gap:5px;margin-top:10px;font-size:.8rem;font-weight:700;color:var(--azul);text-decoration:underline;text-underline-offset:3px}

/* ── FAQ ── */
.faq-section{background:var(--azul-bg);border-top:1px solid var(--borda)}
.faq-inner{max-width:1200px;margin:0 auto;padding:36px 20px 40px}
.faq-title{font-size:1.15rem;font-weight:900;color:var(--azul-esc);margin-bottom:6px}
.faq-sub{font-size:.9rem;color:var(--muted);margin-bottom:24px}
.faq-list{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}
.faq-item{background:var(--azul-bg);border:1px solid var(--borda);border-radius:var(--radius);padding:18px 20px}
.faq-item h3{font-size:.95rem;font-weight:900;color:#111827;margin-bottom:7px}
.faq-item p{font-size:.83rem;color:var(--muted);line-height:1.55}

/* ── FOOTER ── */
.barra-contato{background:#0b1220;color:#e2e8f0;padding:24px 20px 0}
.barra-inner{max-width:1400px;margin:0 auto}
.footer-cols{display:grid;grid-template-columns:repeat(3,1fr);gap:32px;padding-bottom:28px;border-bottom:1px solid rgba(255,255,255,.08)}
.footer-col h4{font-size:.78rem;font-weight:900;text-transform:uppercase;letter-spacing:.08em;color:#fff;margin-bottom:12px}
.footer-col a,.footer-col span{display:block;font-size:.84rem;color:#94a3b8;margin-bottom:8px;line-height:1.4}
.footer-col a:hover{color:#fff;text-decoration:underline}
.footer-social{display:flex;gap:12px;margin-top:8px}
.footer-social a{display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,.08);font-size:1rem;margin:0}
.footer-social a:hover{background:rgba(255,255,255,.18)}
.footer-copy{padding:16px 0;font-size:.76rem;color:#475569;text-align:center;line-height:1.5}

/* ── RESPONSIVE ── */
@media(max-width:1100px){
  .sim-cols{grid-template-columns:1fr}
  .info-grid{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:900px){
  .hdr{padding:0 8px 0 6px;min-height:76px;gap:6px}
  .nav-links{display:none!important}
  .hamburger{display:flex}
  .logo-icon{width:44px;height:44px}
  .logo-icon-img{width:44px;height:44px}
  .logo-aio{font-size:1.2rem}
  .logo{flex-shrink:1;min-width:0;gap:5px}
      .logo-txt{max-width:calc(100vw - 112px)}
  .logo-sub-full{display:none}
  .logo-sub-mobile{display:block}
  .logo-sub{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:.71rem;text-align:left;text-align-last:left}
  .page-hero h1{font-size:1.4rem}
  .faq-list{grid-template-columns:1fr}
}
@media(max-width:700px){
  .footer-cols{grid-template-columns:1fr;gap:20px}
  .info-grid{grid-template-columns:1fr}
  .frow{grid-template-columns:1fr}
  .page-hero h1{font-size:1.2rem}
  .main-wrap{padding:20px 14px 36px}
  .form-section{padding:16px 16px}
  .btn-calc{margin:16px auto 18px;min-width:200px}
  .form-disclaimer{padding:0 16px 14px}
  .result-card-body{padding:14px 14px}
  .result-card-hdr{padding:12px 14px}
}
@media(max-width:420px){
  .radio-group{flex-direction:column}
}
@media(min-width:901px){.nav-mobile{display:none!important}}
label{display:flex;align-items:center;gap:5px}
.sim-tip{position:relative;display:inline-flex;align-items:center;cursor:pointer}
.sim-tip-icon{width:14px;height:14px;border-radius:50%;background:#0053a6;color:#fff;font-size:.6rem;font-weight:900;display:flex;align-items:center;justify-content:center;flex-shrink:0;line-height:1}
.sim-tip-box{display:none;position:absolute;left:0;top:calc(100% + 6px);background:#1e293b;color:#f1f5f9;font-size:.73rem;font-weight:400;line-height:1.45;padding:9px 12px;border-radius:8px;width:260px;z-index:300;box-shadow:0 6px 18px rgba(0,0,0,.28);pointer-events:none}
.sim-tip-box::before{content:'';position:absolute;top:-5px;left:10px;border-left:5px solid transparent;border-right:5px solid transparent;border-bottom:5px solid #1e293b}
.sim-tip:hover .sim-tip-box{display:block}
@media(max-width:600px){.sim-tip-box{width:200px}}
</style>
</head>
<body>

<!-- ===== HEADER ===== -->
<header class="site-header">
  <input type="checkbox" id="menu-toggle" class="menu-chk" aria-hidden="true">
  <div class="hdr">
    <a href="index.php" class="logo">
      <div class="logo-icon">
        <img src="https://cdn.tess.im/assets/uploads/0e90758d-2354-4677-b743-9724498c3976.jpg"
             class="logo-icon-img" alt="Arremate Imóveis Online" loading="eager" decoding="async">
      </div>
      <div class="logo-txt">
        <div class="logo-aio">Arremate Imóveis Online</div>
        <div class="logo-sub">
          <span class="logo-sub-full">Onde a busca termina e a sua conquista começa.</span>
          <span class="logo-sub-mobile">Onde a busca termina e a sua conquista começa.</span>
        </div>
      </div>
    </a>
    <nav class="nav-links" id="navDesktop">
      <a href="index.php">Início</a>
      <a href="index.php#oportunidades">Oportunidades</a>
      <a href="resultados.html">Buscar Imóveis</a>
      <a href="favoritos.html">❤️ Favoritos</a>
      <a href="simulador-de-financiamento.php" class="active">Simulador</a>
      <a href="index.php#duvidas">Dúvidas</a>
      <a href="blog.html">Blog</a>
    </nav>
    <label for="menu-toggle" class="hamburger" aria-label="Abrir menu">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
        <line x1="3" y1="6" x2="21" y2="6"/>
        <line x1="3" y1="12" x2="21" y2="12"/>
        <line x1="3" y1="18" x2="21" y2="18"/>
      </svg>
    </label>
  </div>
  <nav class="nav-mobile">
    <label for="menu-toggle" class="nav-mob-close">✕ Fechar</label>
    <a href="index.php" onclick="document.getElementById('menu-toggle').checked=false">🏠 Início</a>
    <a href="index.php#oportunidades" onclick="document.getElementById('menu-toggle').checked=false">🏡 Oportunidades</a>
    <a href="resultados.html" onclick="document.getElementById('menu-toggle').checked=false">🔍 Buscar Imóveis</a>
    <a href="favoritos.html" onclick="document.getElementById('menu-toggle').checked=false">❤️ Favoritos</a>
    <a href="simulador-de-financiamento.php" class="active" onclick="document.getElementById('menu-toggle').checked=false">📊 Simulador</a>
    <a href="index.php#duvidas" onclick="document.getElementById('menu-toggle').checked=false">❓ Dúvidas</a>
    <a href="blog.html" onclick="document.getElementById('menu-toggle').checked=false">📝 Blog</a>
  </nav>
</header>

<!-- ===== PAGE HERO ===== -->
<section class="page-hero">
  <div class="page-hero-inner">
    <h1>Simulador de Financiamento Imobiliário <em>CAIXA 2026</em></h1>
    <h2>Planeje seu crédito, calcule parcelas e compare taxas em todo o Brasil através do Arremate Imóveis Online. Para imóveis no Estado de São Paulo, garanta suporte técnico gratuito ao indicar a imobiliária parceira credenciada (CRECI-SP 043342) em sua proposta junto à CAIXA.</h2>
  </div>
</section>

<!-- ===== MAIN SIMULATOR ===== -->
<div class="main-wrap" id="simulador">
  <div class="sim-cols">

    <!-- ── LEFT: INPUT FORM ── -->
    <div class="form-panel">

      <!-- Section A: Dados do Imóvel -->
      <div class="form-section">
        <div class="form-section-title">🏠 Dados do Imóvel</div>
        <div class="frow">
          <div class="fg">
            <label for="sim_val">Valor do imóvel (R$) <span class="sim-tip"><span class="sim-tip-icon">i</span><span class="sim-tip-box">Informe o Valor de Avaliação do edital se desejar liberar a cota máxima de crédito. Esse valor é a base para reduzir sua entrada e aumentar o financiamento.</span></span></label>
            <input type="text" id="sim_val" inputmode="numeric" placeholder="Ex.: R$ 350.000,00" autocomplete="off" oninput="mascaraMoeda(this);atualizarPctEntrada();calcDebounced()">
          </div>
          <div class="fg">
            <label for="sim_ent">Valor de entrada (R$) <span class="sim-tip"><span class="sim-tip-icon">i</span><span class="sim-tip-box">Para imóveis de leilão, o uso do Valor de Avaliação permite reduzir a entrada para 5%. Caso utilize o valor de venda, a entrada padrão sobe para 20%.</span></span></label>
            <input type="text" id="sim_ent" inputmode="numeric" placeholder="Ex.: R$ 70.000,00" autocomplete="off" oninput="mascaraMoeda(this);atualizarPctEntrada();calcDebounced()">
            <div class="helper" id="pct_entrada_helper">% de entrada: —</div>
          </div>
        </div>
        <div class="fg">
          <label>Tipo de imóvel</label>
          <div class="radio-group" id="tipo_imovel_group">
            <label class="radio-opt selected" id="opt_residencial">
              <input type="radio" name="tipo_imovel" value="residencial" checked onchange="selecionarRadio('tipo_imovel_group',this)"> Residencial
            </label>
            <label class="radio-opt" id="opt_comercial">
              <input type="radio" name="tipo_imovel" value="comercial" onchange="selecionarRadio('tipo_imovel_group',this)"> Comercial
            </label>
          </div>
        </div>
      </div>

      <!-- Section B: Financiamento -->
      <div class="form-section">
        <div class="form-section-title">📋 Financiamento</div>

        <div class="fg">
          <label>Prazo do financiamento <span class="sim-tip"><span class="sim-tip-icon">i</span><span class="sim-tip-box">Número de meses para quitar o financiamento. O prazo máximo na CAIXA é de 35 anos (420 meses).</span></span></label>
          <div class="slider-wrap">
            <div class="slider-label">
              <span>5 anos</span>
              <strong id="prazo_label">30 anos (360 meses)</strong>
              <span>35 anos</span>
            </div>
            <input type="range" id="sim_prazo_slider" min="5" max="35" value="30" step="1"
                   oninput="atualizarPrazoLabel(this.value);calcDebounced()" style="--pct:50%">
          </div>
        </div>

        <div class="fg">
          <label>Sistema de amortização <span class="sim-tip"><span class="sim-tip-icon">i</span><span class="sim-tip-box">PRICE: parcelas fixas, ideal para quem prefere previsibilidade no orçamento. SAC: parcelas que diminuem ao longo do tempo.</span></span></label>
          <div class="radio-group" id="sis_group">
            <label class="radio-opt selected" id="opt_price">
              <input type="radio" name="sim_sis" value="PRICE" checked onchange="selecionarRadio('sis_group',this);calcDebounced()">
              <div>
                PRICE
                <span class="radio-opt-desc">Parcela fixa durante todo o prazo</span>
              </div>
            </label>
            <label class="radio-opt" id="opt_sac">
              <input type="radio" name="sim_sis" value="SAC" onchange="selecionarRadio('sis_group',this);calcDebounced()">
              <div>
                SAC
                <span class="radio-opt-desc">Parcela inicial maior, decresce ao longo do prazo</span>
              </div>
            </label>
          </div>
        </div>

        <div class="frow">
          <div class="fg">
            <label for="sim_juros">Taxa de juros (% a.a.) <span class="sim-tip"><span class="sim-tip-icon">i</span><span class="sim-tip-box">Taxa anual aplicada ao financiamento, definida pela CAIXA conforme seu perfil e relacionamento com o banco.</span></span></label>
            <input type="number" id="sim_juros" inputmode="decimal" value="10.5" min="0" max="30" step="0.1" autocomplete="off" oninput="calcDebounced()">
            <div class="helper">Taxa média CAIXA: ~10,5% a.a. (varia conforme perfil e modalidade)</div>
          </div>
          <div class="fg">
            <label for="sim_renda">Renda mensal bruta (R$) <span class="sim-tip"><span class="sim-tip-icon">i</span><span class="sim-tip-box">A CAIXA limita o comprometimento a 30% da renda bruta familiar. Some as rendas de todos os compradores para aumentar sua capacidade de financiamento.</span></span></label>
            <input type="text" id="sim_renda" inputmode="numeric" placeholder="Ex.: R$ 8.000,00" autocomplete="off" oninput="mascaraMoeda(this);calcDebounced()">
            <div class="helper">Para calcular comprometimento de renda (regra dos 30%)</div>
          </div>
        </div>
      </div>

      <!-- Section C: Custos Adicionais -->
      <div class="form-section">
        <div class="form-section-title">💰 Custos Adicionais (estimativa)</div>

        <div class="check-row">
          <input type="checkbox" id="chk_itbi" onchange="toggleExtra('itbi_extra',this);calcDebounced()">
          <div class="check-row-body">
            <label class="check-row-label" for="chk_itbi">Incluir ITBI</label>
            <div class="check-row-sub">Imposto de Transmissão de Bens Imóveis — pago na compra (geralmente 2% do valor venal)</div>
            <div class="check-extra" id="itbi_extra">
              <div class="fg" style="margin-top:8px;margin-bottom:0">
                <label for="itbi_pct">Alíquota ITBI (%)</label>
                <input type="number" id="itbi_pct" value="2" min="0" max="10" step="0.1" autocomplete="off" oninput="calcDebounced()">
              </div>
            </div>
          </div>
        </div>

        <div class="check-row">
          <input type="checkbox" id="chk_reg" onchange="toggleExtra('reg_extra',this);calcDebounced()">
          <div class="check-row-body">
            <label class="check-row-label" for="chk_reg">Incluir Registro/Cartório</label>
            <div class="check-row-sub">Custo estimado para registro do imóvel e escritura no cartório</div>
            <div class="check-extra" id="reg_extra">
              <div class="fg" style="margin-top:8px;margin-bottom:0">
                <label for="reg_val">Valor estimado (R$)</label>
                <input type="text" id="reg_val" inputmode="numeric" placeholder="Ex.: R$ 4.000,00" autocomplete="off" oninput="mascaraMoeda(this);calcDebounced()">
              </div>
            </div>
          </div>
        </div>

        <div class="check-row">
          <input type="checkbox" id="chk_reforma" onchange="toggleExtra('reforma_extra',this);calcDebounced()">
          <div class="check-row-body">
            <label class="check-row-label" for="chk_reforma">Incluir Reforma / Mobília</label>
            <div class="check-row-sub">Estimativa de obras, reformas ou mobiliário após a compra</div>
            <div class="check-extra" id="reforma_extra">
              <div class="fg" style="margin-top:8px;margin-bottom:0">
                <label for="reforma_val">Valor estimado (R$)</label>
                <input type="text" id="reforma_val" inputmode="numeric" placeholder="Ex.: R$ 20.000,00" autocomplete="off" oninput="mascaraMoeda(this);calcDebounced()">
              </div>
            </div>
          </div>
        </div>
      </div>

      <button class="btn-calc" type="button" onclick="calcular()">
        📊 Calcular Financiamento
      </button>
      <p class="form-disclaimer">📋 Simulação estimada para referência. Não inclui seguros, taxas administrativas nem aprovação de crédito pelo banco. Para condições reais, consulte uma agência CAIXA ou correspondente bancário.</p>

    </div><!-- /form-panel -->

    <!-- ── RIGHT: RESULTS ── -->
    <div class="results-panel" id="results_panel">

      <!-- Empty state (shown until first calculation) -->
      <div class="result-empty" id="result_empty">
        <span class="empty-icon">🏦</span>
        <h3>Aguardando dados do financiamento</h3>
        <p>Preencha os dados ao lado para ver o resultado detalhado com parcelas, análise de renda, custos totais e tabela de amortização.</p>
      </div>

      <!-- Resumo do Financiamento -->
      <div class="result-card" id="card_resumo" style="display:none">
        <div class="result-card-hdr"><span>📊</span> Resumo do Financiamento</div>
        <div class="result-card-body" id="body_resumo"></div>
      </div>

      <!-- Análise de Renda -->
      <div class="result-card" id="card_renda" style="display:none">
        <div class="result-card-hdr"><span>💼</span> Análise de Renda</div>
        <div class="result-card-body" id="body_renda"></div>
      </div>

      <!-- Custos Totais -->
      <div class="result-card" id="card_custos" style="display:none">
        <div class="result-card-hdr"><span>🧾</span> Custos Totais da Compra</div>
        <div class="result-card-body" id="body_custos"></div>
      </div>

    </div><!-- /results-panel -->

  </div><!-- /sim-cols -->

  <!-- CTA full-width -->
  <div class="cta-card cta-full" id="card_cta" style="display:none">
    <div class="cta-full-inner">
      <div class="cta-full-text">
        <h3>📍 Imóveis da CAIXA em SP? Conte com assessoria especializada.</h3>
        <p>O suporte em todo o processo de financiamento e registro para imóveis no estado de São Paulo é realizado por uma imobiliária parceira credenciada (<strong>CRECI-SP 043342</strong>), garantindo segurança técnica e jurídica para sua arrematação.</p>
      </div>
      <a href="https://wa.me/5512997651740?text=Ol%C3%A1%21+Fiz+uma+simula%C3%A7%C3%A3o+de+financiamento+e+quero+ajuda+com+im%C3%B3veis+da+CAIXA+em+SP."
         class="btn-whats" target="_blank" rel="noopener">
        💬 Falar com Especialista
      </a>
    </div>
  </div>

</div><!-- /main-wrap -->

<!-- ===== INFO CARDS ===== -->
<section class="info-section-wrap">
<div class="info-section">
  <div class="info-block-hdr">
    <h2 class="info-block-title">📚 Entenda os sistemas de financiamento</h2>
    <p class="info-block-sub">Compare as modalidades disponíveis pela CAIXA e descubra qual é a mais adequada ao seu perfil.</p>
  </div>
  <div class="info-grid">

    <div class="info-card">
      <span class="info-card-icon">📐</span>
      <h3>O que é o sistema PRICE?</h3>
      <p>No sistema PRICE (também chamado de Tabela Price), as parcelas mensais são <strong>fixas</strong> durante todo o prazo do financiamento. No início, a maior parte da parcela é composta por juros; ao longo do tempo, a proporção de amortização do saldo devedor aumenta. É a opção mais comum para quem prefere previsibilidade no orçamento mensal.</p>
    </div>

    <div class="info-card">
      <span class="info-card-icon">📉</span>
      <h3>O que é o sistema SAC?</h3>
      <p>No SAC (Sistema de Amortização Constante), a parcela de amortização do principal é sempre a mesma, mas os juros diminuem mês a mês à medida que o saldo devedor cai. Isso resulta em parcelas <strong>decrescentes</strong> ao longo do prazo. A primeira parcela é maior que no PRICE, mas o total de juros pago é menor. Ideal para quem tem renda atual compatível e quer economizar no longo prazo.</p>
    </div>

    <div class="info-card">
      <span class="info-card-icon">💚</span>
      <h3>Como usar o FGTS no financiamento?</h3>
      <p>O FGTS pode ser usado como <strong>entrada</strong>, para <strong>amortizar o saldo devedor</strong> ou para <strong>abater parcelas</strong> em financiamentos pelo Sistema Financeiro de Habitação (SFH). Para imóveis da CAIXA, a aplicação do FGTS é facilitada, especialmente em imóveis residenciais com valor de até R$ 1,5 milhão. Consulte as condições de elegibilidade diretamente na CAIXA.</p>
      <a href="resultados.html?fgts=1">🔎 Ver imóveis que aceitam FGTS →</a>
    </div>

  </div>
</div>
</section>

<!-- ===== FAQ ===== -->
<section class="faq-section" id="duvidas">
  <div class="faq-inner">
    <h2 class="faq-title">❓ Dúvidas frequentes sobre financiamento CAIXA</h2>
    <p class="faq-sub">Respostas para as perguntas mais comuns sobre financiamento imobiliário pela CAIXA.</p>
    <div class="faq-list">

      <div class="faq-item">
        <h3>Qual a taxa de juros atual da CAIXA?</h3>
        <p>A CAIXA pratica taxas a partir de aproximadamente <strong>8,99% a.a.</strong> no programa Minha Casa Minha Vida (para imóveis até R$ 350 mil) e entre <strong>10% e 12% a.a.</strong> para outros financiamentos habitacionais pelo SFH. A taxa final depende do seu perfil de crédito, relacionamento com o banco, renda e o tipo de imóvel. Use 10,5% a.a. como referência para simulação geral.</p>
      </div>

      <div class="faq-item">
        <h3>Quanto de entrada preciso para financiar imóvel da CAIXA?</h3>
        <p>A CAIXA financia em geral até <strong>80% do valor do imóvel</strong> pelo SFH, exigindo entrada mínima de 20%. Em algumas linhas (como MCMV) o percentual pode ser menor. O valor mínimo de entrada deve ser suficiente para cobrir o que o banco não financia — além de reservar capital para ITBI, cartório e outras despesas de aquisição.</p>
      </div>

      <div class="faq-item">
        <h3>Posso usar FGTS como entrada no financiamento?</h3>
        <p>Sim! O FGTS pode ser usado para compor a entrada no financiamento habitacional, desde que o imóvel seja residencial urbano, o comprador não tenha outro imóvel no mesmo município, e o financiamento seja pelo SFH. O saldo do FGTS pode cobrir parcialmente ou totalmente o valor de entrada exigido. Imóveis da CAIXA leiloados também podem aceitar FGTS — verifique na ficha do imóvel.</p>
      </div>

      <div class="faq-item">
        <h3>Qual sistema é melhor para mim: PRICE ou SAC?</h3>
        <p>Depende do seu perfil financeiro. O <strong>PRICE</strong> é melhor se você precisa de parcelas menores no início e previsibilidade total — mas paga mais juros no total. O <strong>SAC</strong> cobra mais no início, mas você paga menos juros ao longo do prazo e quita mais rápido o saldo devedor. Se sua renda permite pagar a primeira parcela do SAC (que é a maior), prefira o SAC para economizar. Use nosso simulador para comparar os dois cenários.</p>
      </div>

    </div>
  </div>
</section>

<!-- ===== FOOTER ===== -->
<footer class="barra-contato" id="contato">
  <div class="barra-inner">
    <div class="footer-cols">
      <div class="footer-col">
        <h4>Navegação</h4>
        <a href="index.php">Início</a>
        <a href="resultados.html">Buscar Imóveis</a>
        <a href="index.php#oportunidades">Oportunidades</a>
        <a href="simulador-de-financiamento.php">Simulador de Financiamento</a>
        <a href="favoritos.html">❤️ Favoritos</a>
        <a href="blog.html">Blog do Arremate</a>
      </div>
      <div class="footer-col">
        <h4>Suporte</h4>
        <a href="index.php#duvidas">Perguntas Frequentes</a>
        <a href="index.php#contato">Fale Conosco</a>
        <a href="privacidade.html">Política de Privacidade</a>
        <span style="margin-top:10px;color:#cbd5e1">🏦 Imobiliária parceira credenciada</span>
        <span style="color:#cbd5e1">para imóveis da Caixa em SP</span>
        <div style="display:flex;align-items:center;gap:7px;flex-wrap:wrap;margin-top:0"><span style="color:#f39200;font-weight:700">CRECI-SP 043342</span><a href="https://wa.me/5512997651740" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:4px;background:#22c55e;color:#fff;padding:2px 7px;border-radius:999px;font-weight:700;font-size:.65rem;text-decoration:none;box-shadow:0 1px 4px rgba(34,197,94,.3)"><svg viewBox="0 0 24 24" fill="currentColor" width="10" height="10"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg> WhatsApp</a></div>
      </div>
      <div class="footer-col">
        <h4>Contato</h4>
        <a href="mailto:contato@arremateimoveisonline.com.br">contato@arremateimoveisonline.com.br</a>
        <span>&nbsp;</span>
        <div class="footer-social"><span style="display:none">
          <a href="#" title="Instagram" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg></a>
          <a href="#" title="Facebook" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>
        </span></div>
      </div>
    </div>
    <div class="footer-copy">
      © <?= $ano ?> Arremate Imóveis Online — A plataforma de busca de imóveis da CAIXA em todo o Brasil.<br>
      <span style="color:#4b5563;font-size:13px">Este não é um site oficial da Caixa Econômica Federal. Plataforma independente de busca e comparação.</span>
    </div>
  </div>
</footer>

<!-- ===== JAVASCRIPT ===== -->
<script>
/* ──────────────────────────────────────────────────────────
   UTILITÁRIOS
────────────────────────────────────────────────────────── */

function limpaNumeroBR(s) {
  if (!s) return 0;
  // Remove tudo exceto dígitos e vírgula
  var limpo = String(s).replace(/[^\d,]/g, '').replace(',', '.');
  return parseFloat(limpo) || 0;
}

function formataMilharBR(n) {
  if (isNaN(n) || n === null) return '0,00';
  return n.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function brl(n) {
  return 'R$ ' + formataMilharBR(n);
}

function mascaraMoeda(el) {
  var raw = el.value.replace(/\D/g, '');
  if (raw === '') { el.value = ''; return; }
  var num = parseInt(raw, 10) / 100;
  el.value = 'R$ ' + formataMilharBR(num);
}

/* ──────────────────────────────────────────────────────────
   HELPERS DE UI
────────────────────────────────────────────────────────── */

function selecionarRadio(groupId, radioEl) {
  var group = document.getElementById(groupId);
  if (!group) return;
  group.querySelectorAll('.radio-opt').forEach(function(el) {
    el.classList.remove('selected');
  });
  var label = radioEl.closest('.radio-opt');
  if (label) label.classList.add('selected');
}

function toggleExtra(extraId, chkEl) {
  var el = document.getElementById(extraId);
  if (!el) return;
  if (chkEl.checked) {
    el.classList.add('open');
  } else {
    el.classList.remove('open');
  }
}

function atualizarPrazoLabel(val) {
  val = parseInt(val, 10);
  var anos = val;
  var meses = anos * 12;
  document.getElementById('prazo_label').textContent = anos + ' anos (' + meses + ' meses)';
  // Atualizar gradiente do slider
  var slider = document.getElementById('sim_prazo_slider');
  var pct = ((val - 5) / (35 - 5)) * 100;
  slider.style.setProperty('--pct', pct.toFixed(1) + '%');
}

function atualizarPctEntrada() {
  var val = limpaNumeroBR(document.getElementById('sim_val').value);
  var ent = limpaNumeroBR(document.getElementById('sim_ent').value);
  var helper = document.getElementById('pct_entrada_helper');
  if (!helper) return;
  if (val <= 0 || ent <= 0) {
    helper.textContent = '% de entrada: —';
    helper.className = 'helper';
    return;
  }
  var pct = (ent / val) * 100;
  helper.textContent = '% de entrada: ' + pct.toFixed(1) + '%';
  if (pct < 20) {
    helper.className = 'helper danger';
    helper.textContent += ' ⚠️ CAIXA exige mínimo ~20%';
  } else if (pct < 30) {
    helper.className = 'helper warn';
  } else {
    helper.className = 'helper good';
  }
}

/* ──────────────────────────────────────────────────────────
   DEBOUNCE
────────────────────────────────────────────────────────── */
var _calcTimer = null;
function calcDebounced() {
  if (_calcTimer) clearTimeout(_calcTimer);
  _calcTimer = setTimeout(function() { calcular(); }, 520);
}

/* ──────────────────────────────────────────────────────────
   LEITURA DOS INPUTS
────────────────────────────────────────────────────────── */
function lerInputs() {
  var val     = limpaNumeroBR(document.getElementById('sim_val').value)   || 0;
  var ent     = limpaNumeroBR(document.getElementById('sim_ent').value)   || 0;
  var prazoA  = parseInt(document.getElementById('sim_prazo_slider').value, 10) || 30;
  var prazo   = prazoA * 12;
  var jaa     = parseFloat(document.getElementById('sim_juros').value)    || 10.5;
  var sis     = document.querySelector('input[name="sim_sis"]:checked').value;
  var renda   = limpaNumeroBR(document.getElementById('sim_renda').value) || 0;
  var tipo    = document.querySelector('input[name="tipo_imovel"]:checked').value;

  var usaItbi   = document.getElementById('chk_itbi').checked;
  var itbiPct   = parseFloat(document.getElementById('itbi_pct').value) || 2;
  var usaReg    = document.getElementById('chk_reg').checked;
  var regVal    = limpaNumeroBR(document.getElementById('reg_val').value) || 0;
  var usaReforma= document.getElementById('chk_reforma').checked;
  var reformaVal= limpaNumeroBR(document.getElementById('reforma_val').value) || 0;

  return { val, ent, prazo, prazoA, jaa, sis, renda, tipo,
           usaItbi, itbiPct, usaReg, regVal, usaReforma, reformaVal };
}

/* ──────────────────────────────────────────────────────────
   FUNÇÃO PRINCIPAL DE CÁLCULO
────────────────────────────────────────────────────────── */
function calcular() {
  var d = lerInputs();

  if (d.val <= 0 || d.prazo <= 0) {
    // Não mostrar erro se campos vazios (ainda digitando)
    return;
  }

  var fin = Math.max(0, d.val - d.ent);
  var im  = (d.jaa / 100) / 12;

  // Esconder empty state
  document.getElementById('result_empty').style.display = 'none';

  // ── RESUMO ──
  var resumoHTML = '';

  resumoHTML += rowResult('Valor do imóvel', brl(d.val), false);
  resumoHTML += rowResult('Entrada', brl(d.ent), false);
  resumoHTML += rowResult('Valor financiado', brl(fin), false);
  resumoHTML += rowResult('Prazo', d.prazoA + ' anos (' + d.prazo + ' meses)', false);
  resumoHTML += rowResult('Taxa de juros', d.jaa.toFixed(2) + '% a.a. (' + ((d.jaa / 12).toFixed(4)) + '% a.m.)', false);
  resumoHTML += rowResult('Sistema', d.sis, false);

  var parcelaInicial = 0;
  var parcelaFinal   = 0;
  var totalPago      = 0;
  var totalJuros     = 0;

  if (fin === 0) {
    resumoHTML += rowResult('Pagamento', '✅ À vista — sem parcelas', true);
    totalPago  = d.val;
    totalJuros = 0;
  } else if (d.sis === 'PRICE') {
    if (im === 0) {
      parcelaInicial = fin / d.prazo;
    } else {
      parcelaInicial = fin * im / (1 - Math.pow(1 + im, -d.prazo));
    }
    parcelaFinal = parcelaInicial;
    totalPago    = parcelaInicial * d.prazo;
    totalJuros   = totalPago - fin;

    resumoHTML += '<div style="height:6px"></div>';
    resumoHTML += rowResult('Parcela mensal (fixa)', brl(parcelaInicial), true);

  } else { // SAC
    var amort         = fin / d.prazo;
    parcelaInicial    = amort + fin * im;
    parcelaFinal      = amort + amort * im;

    // Total pago = soma de todas as parcelas SAC
    // parcela_k = amort + (fin - (k-1)*amort) * im
    // Total = prazo * amort + im * sum_{k=1}^{prazo} [fin - (k-1)*amort]
    // sum_{k=1}^{N} [fin - (k-1)*amort] = N*fin - amort * N*(N-1)/2
    totalPago = d.prazo * amort + im * (d.prazo * fin - amort * d.prazo * (d.prazo - 1) / 2);
    totalJuros = totalPago - fin;

    resumoHTML += '<div style="height:6px"></div>';
    resumoHTML += rowResult('1ª parcela (mais alta)', brl(parcelaInicial), true);
    resumoHTML += rowResult('Última parcela', brl(parcelaFinal), false);

    // Economia vs PRICE
    var parcelaPrice = im === 0 ? fin / d.prazo : fin * im / (1 - Math.pow(1 + im, -d.prazo));
    var totalPrice   = parcelaPrice * d.prazo;
    var economia     = totalPrice - totalPago;
    if (economia > 0) {
      resumoHTML += rowResult('Economia vs PRICE', '✅ ' + brl(economia), false);
    }
  }

  document.getElementById('body_resumo').innerHTML = resumoHTML;
  mostrarCard('card_resumo');

  // ── ANÁLISE DE RENDA ──
  var rendaHTML = '';
  var rendaMin30 = parcelaInicial / 0.30;
  rendaHTML += rowResult('Renda mínima sugerida (30%)', brl(rendaMin30), true);

  if (d.renda > 0) {
    var comprPct = (parcelaInicial / d.renda) * 100;
    var comprClass = comprPct <= 25 ? 'green' : (comprPct <= 30 ? 'yellow' : 'red');
    var comprLabel = comprPct <= 25 ? '✅ Excelente' : (comprPct <= 30 ? '⚠️ Atenção' : '🔴 Acima do recomendado');
    rendaHTML += rowResult('Sua renda informada', brl(d.renda), false);
    rendaHTML += rowResultValClass('Comprometimento da renda', comprPct.toFixed(1) + '% — ' + comprLabel, comprClass);
    // Barra visual
    var barW = Math.min(comprPct, 100).toFixed(1);
    rendaHTML += '<div class="renda-bar-wrap">';
    rendaHTML += '<div class="renda-bar-bg"><div class="renda-bar-fill ' + comprClass + '" style="width:' + barW + '%"></div></div>';
    rendaHTML += '<div class="renda-bar-labels"><span>0%</span><span style="color:#16a34a;font-weight:700">25%</span><span style="color:#d97706;font-weight:700">30%</span><span>100%</span></div>';
    rendaHTML += '</div>';

    if (comprPct > 30) {
      rendaHTML += '<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 12px;margin-top:10px;font-size:.79rem;color:#b91c1c;line-height:1.5">A CAIXA geralmente não aprova financiamentos com comprometimento de renda acima de 30%. Considere aumentar a entrada ou reduzir o prazo.</div>';
    }
  } else {
    rendaHTML += '<div style="font-size:.78rem;color:var(--muted);margin-top:6px">Informe sua renda mensal bruta no formulário para ver análise de comprometimento.</div>';
  }

  document.getElementById('body_renda').innerHTML = rendaHTML;
  mostrarCard('card_renda');

  // ── CUSTOS TOTAIS ──
  var custosHTML = '';
  var totalExtras = 0;

  custosHTML += rowResult('Entrada (capital próprio)', brl(d.ent), false);

  if (d.usaItbi) {
    var itbiVal = d.val * (d.itbiPct / 100);
    totalExtras += itbiVal;
    custosHTML += rowResult('ITBI (' + d.itbiPct.toFixed(1) + '% sobre valor do imóvel)', brl(itbiVal), false);
  }

  if (d.usaReg && d.regVal > 0) {
    totalExtras += d.regVal;
    custosHTML += rowResult('Registro / Cartório (estimativa)', brl(d.regVal), false);
  }

  if (d.usaReforma && d.reformaVal > 0) {
    totalExtras += d.reformaVal;
    custosHTML += rowResult('Reforma / Mobília (estimativa)', brl(d.reformaVal), false);
  }

  var totalNecessario = d.ent + totalExtras;
  custosHTML += '<div style="height:4px"></div>';
  custosHTML += rowResult('Total necessário para fechar o negócio', brl(totalNecessario), true);

  if (d.val > 0 && totalNecessario > 0) {
    var pctCapital = (totalNecessario / d.val) * 100;
    custosHTML += rowResult('% do valor do imóvel em capital próprio', pctCapital.toFixed(1) + '%', false);
  }

  document.getElementById('body_custos').innerHTML = custosHTML;
  mostrarCard('card_custos');

  // Mostrar CTA
  mostrarCard('card_cta');

  // Scroll suave ao resultado no mobile
  if (window.innerWidth < 1100) {
    var resultsEl = document.getElementById('results_panel');
    if (resultsEl) {
      setTimeout(function() {
        resultsEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }, 100);
    }
  }
}

/* ──────────────────────────────────────────────────────────
   HELPERS DE RENDERIZAÇÃO
────────────────────────────────────────────────────────── */

function rowResult(label, val, highlight) {
  var cls = highlight ? 'result-row highlight' : 'result-row';
  return '<div class="' + cls + '"><span class="result-label">' + label + '</span><span class="result-val">' + val + '</span></div>';
}

function rowResultValClass(label, val, valClass) {
  return '<div class="result-row"><span class="result-label">' + label + '</span><span class="result-val ' + valClass + '">' + val + '</span></div>';
}

function mostrarCard(id) {
  var el = document.getElementById(id);
  if (el) el.style.display = 'block';
}

/* ──────────────────────────────────────────────────────────
   INIT
────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function() {
  // Inicializar label do prazo
  atualizarPrazoLabel(document.getElementById('sim_prazo_slider').value);

  // Inicializar gradiente do slider
  var slider = document.getElementById('sim_prazo_slider');
  slider.addEventListener('input', function() {
    var pct = ((this.value - 5) / (35 - 5)) * 100;
    this.style.setProperty('--pct', pct.toFixed(1) + '%');
  });

  // Estilizar radio opts ao clicar diretamente no label
  document.querySelectorAll('.radio-opt input[type=radio]').forEach(function(radio) {
    radio.addEventListener('change', function() {
      var group = this.closest('.radio-group');
      if (!group) return;
      group.querySelectorAll('.radio-opt').forEach(function(opt) {
        opt.classList.remove('selected');
      });
      var parentOpt = this.closest('.radio-opt');
      if (parentOpt) parentOpt.classList.add('selected');
    });
  });
});
</script>
<script src="logo-fit.js"></script>
</body>
</html>
