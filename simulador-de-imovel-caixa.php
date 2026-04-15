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
  <title>Simulador de Compra de Imóvel CAIXA | Calcule Custos Totais | Arremate Imóveis Online</title>
  <meta name="keywords" content="simulador imóvel CAIXA, calcular custo leilão CAIXA, custos compra imóvel CAIXA, ITBI leilão, cartório imóvel CAIXA, simulador arremate CAIXA, custo total imóvel CAIXA">
  <meta name="description" content="Simule o custo total de compra de um imóvel da CAIXA: arremate, ITBI, cartório, reforma e financiamento. Descubra se o investimento vale a pena antes de dar o lance.">
  <meta property="og:title" content="Simulador de Compra de Imóvel CAIXA | Calcule Custos Totais | Arremate Imóveis Online">
  <meta property="og:description" content="Calcule arremate, ITBI, cartório, reforma e financiamento de imóvel CAIXA em um só lugar. Simulação gratuita com dados reais da CAIXA.">
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

/* HERO */
.hero{background:linear-gradient(135deg,#0b1a33 0%,#001634 50%,var(--azul) 100%);color:#fff;padding:40px 20px 36px}
.hero-inner{max-width:900px;margin:0 auto;text-align:center}
.hero-title{font-size:2rem;font-weight:900;line-height:1.22;margin-bottom:14px}
.hero-title em{color:var(--laranja);font-style:normal}
.hero-sub{font-size:1rem;opacity:.92;margin-bottom:20px;max-width:660px;margin-left:auto;margin-right:auto}
.hero-badges{display:flex;flex-wrap:wrap;gap:10px;justify-content:center}
.badge{display:inline-flex;align-items:center;gap:6px;border:1px solid rgba(255,255,255,.28);background:rgba(0,0,0,.18);border-radius:999px;padding:6px 14px;font-size:.82rem}

/* BREADCRUMB */
.breadcrumb{max-width:1200px;margin:0 auto;padding:10px 20px 0;font-size:.8rem;color:var(--muted)}
.breadcrumb a{color:var(--azul);text-decoration:none}
.breadcrumb a:hover{text-decoration:underline}
.breadcrumb span{color:var(--muted)}

/* BOTÕES */
.btn-primary{background:linear-gradient(120deg,var(--laranja),#ffb347);border:none;border-radius:999px;padding:10px 22px;font-weight:900;font-size:.87rem;color:#3b2200;cursor:pointer;display:inline-flex;align-items:center;gap:7px;white-space:nowrap;box-shadow:0 4px 12px rgba(0,0,0,.18);font-family:inherit}
.btn-primary:hover{filter:brightness(1.05)}
.btn-ghost{background:rgba(0,83,166,.08);border:1.5px solid rgba(0,83,166,.3);border-radius:999px;padding:9px 18px;font-weight:900;font-size:.84rem;color:var(--azul-esc);cursor:pointer;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;font-family:inherit}
.btn-ghost:hover{background:rgba(0,83,166,.15)}

/* SEÇÕES */
.sec-outer{width:100%}
.sec-outer.alt{background:var(--azul-card)}
.sec-inner{max-width:1200px;margin:0 auto;padding:36px 20px 32px}
.sec-title{font-size:1.15rem;font-weight:900;color:var(--azul-esc);margin-bottom:6px}
.sec-sub{font-size:.9rem;color:var(--muted);margin-bottom:20px}

/* STEPS */
.steps-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.step-card{background:#fff;border:1px solid var(--borda);border-radius:var(--radius);padding:16px;box-shadow:var(--sombra);display:grid;grid-template-columns:44px 1fr;column-gap:12px;row-gap:6px;align-items:center}
.step-icon{width:44px;height:44px;border-radius:50%;background:var(--azul-card);border:1px solid var(--borda);display:flex;align-items:center;justify-content:center;font-size:1.35rem;flex-shrink:0;grid-column:1;grid-row:1}
.step-card h3{font-size:.95rem;font-weight:900;color:#111827;margin:0;grid-column:2;grid-row:1;line-height:1.15}
.step-card p{font-size:.83rem;color:var(--muted);margin:0;grid-column:1 / -1;grid-row:2;line-height:1.35}

/* FAQ */
.faq-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
.faq-card{background:#fff;border:1px solid var(--borda);border-radius:var(--radius);padding:16px;box-shadow:var(--sombra)}
.faq-card h3{font-size:.95rem;font-weight:900;margin-bottom:6px;color:#111827}
.faq-card p{font-size:.83rem;color:var(--muted);line-height:1.6}

/* FOOTER */
.barra-contato{background:#0b1220;color:#e2e8f0;padding:24px 20px 0}
.barra-inner{max-width:1400px;margin:0 auto}
.footer-cols{display:grid;grid-template-columns:repeat(3,1fr);gap:32px;padding-bottom:28px;border-bottom:1px solid rgba(255,255,255,.08)}
@media(max-width:700px){.footer-cols{grid-template-columns:1fr;gap:20px}}
.footer-col h4{font-size:.78rem;font-weight:900;text-transform:uppercase;letter-spacing:.08em;color:#fff;margin-bottom:12px}
.footer-col a,.footer-col span{display:block;font-size:.84rem;color:#94a3b8;margin-bottom:8px;line-height:1.4}
.footer-col a:hover{color:#fff;text-decoration:underline}
.footer-social{display:flex;gap:12px;margin-top:8px}
.footer-social a{display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,.08);font-size:1rem;margin:0}
.footer-social a:hover{background:rgba(255,255,255,.18)}
.footer-copy{padding:16px 0;font-size:.76rem;color:#475569;text-align:center;line-height:1.5}

/* HEADER */
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
.btn-nav-cta{background:var(--laranja)!important;color:#3b1f00!important;padding:7px 14px!important;border-radius:999px!important;font-weight:900!important;opacity:1!important;box-shadow:0 3px 8px rgba(0,0,0,.22)}
.btn-nav-cta:hover{filter:brightness(1.08)}
.nav-links a.active{opacity:1;background:rgba(255,255,255,.18);border-radius:8px;padding:4px 10px;font-weight:900}
.nav-mobile a.active{background:#c0d8f8;color:var(--azul-esc);font-weight:900}
.hamburger{display:none;align-items:center;justify-content:center;width:44px;height:44px;flex-shrink:0;background:rgba(255,255,255,.12);border:1.5px solid rgba(255,255,255,.3);border-radius:10px;cursor:pointer;color:#fff;transition:background .15s}
.hamburger svg{width:20px;height:20px;display:block}
    .hamburger:hover,.hamburger:focus-visible{background:rgba(255,255,255,.22);outline:none}
.nav-mobile{display:none;flex-direction:column;width:100%;background:#dceeff;border-top:2px solid #a8cfee}
.menu-chk:checked~.nav-mobile{display:flex!important}
.nav-mobile a{display:block;padding:14px 20px;font-size:.97rem;font-weight:700;color:#0b1a33;background:#e8f3ff;border-bottom:1px solid #b8d8f5;text-decoration:none;transition:background .15s}
.nav-mobile a:hover{background:#cde5ff}
.nav-mob-cta{background:#e97500!important;color:#fff!important;font-weight:900!important;border-bottom:none!important}
.nav-mob-close{display:flex;align-items:center;padding:12px 20px;font-size:.9rem;font-weight:700;color:#01468d;background:#c8e0f8;border-bottom:2px solid #a8cfee;cursor:pointer}

/* SIMULADOR PRINCIPAL */
.sim-main-wrap{max-width:1200px;margin:0 auto;padding:28px 20px 36px}
.sim-layout{display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start}

/* FORM */
.sim-form-card{background:#fff;border:1px solid var(--borda);border-radius:var(--radius);box-shadow:var(--sombra);overflow:hidden}
.sim-form-header{background:var(--azul-esc);color:#fff;padding:16px 20px}
.sim-form-header h2{font-size:1rem;font-weight:900;margin:0}
.sim-form-header p{font-size:.78rem;opacity:.8;margin-top:3px}
.sim-form-body{padding:20px}
.sim-section{margin-bottom:20px}
.sim-section-title{font-size:.8rem;font-weight:900;text-transform:uppercase;letter-spacing:.07em;color:var(--azul);border-bottom:2px solid var(--borda);padding-bottom:6px;margin-bottom:14px}
.sim-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:10px 14px}
.fgroup{display:flex;flex-direction:column;gap:4px}
.fgroup label{font-size:.74rem;font-weight:700;color:#475569}
.fgroup input,.fgroup select,.fgroup textarea{border:1px solid #cbd5e1;border-radius:10px;padding:9px 13px;font-size:.84rem;outline:none;background:#fff;width:100%;font-family:inherit;color:#0f172a}
.fgroup input:focus,.fgroup select:focus{border-color:var(--azul);box-shadow:0 0 0 2px rgba(0,83,166,.15)}
.fgroup .helper{font-size:.72rem;color:var(--muted);margin-top:3px}
.fgroup .helper.highlight{color:#059669;font-weight:700}
.fgroup-full{grid-column:1/-1}

/* CHECKBOX TOGGLE SECTIONS */
.chk-toggle{display:flex;align-items:center;gap:10px;padding:10px 13px;background:#f8faff;border:1px solid #e2e8f0;border-radius:10px;cursor:pointer;margin-bottom:8px;transition:background .15s}
.chk-toggle:hover{background:#eef5ff}
.chk-toggle input[type=checkbox]{width:16px;height:16px;accent-color:var(--azul);cursor:pointer;flex-shrink:0}
.chk-toggle label{font-size:.86rem;font-weight:700;color:#334155;cursor:pointer;flex:1}
.chk-sub{display:none;background:#f0f6ff;border:1px solid #cfe2ff;border-radius:10px;padding:12px 14px;margin-bottom:8px}
.chk-sub.open{display:block}
.chk-sub .fgroup{margin-bottom:6px}
.chk-sub .fgroup:last-child{margin-bottom:0}

/* FINANCING TOGGLE */
.fin-toggle-wrap{display:flex;gap:10px;margin-bottom:12px}
.fin-toggle-btn{flex:1;border:2px solid #cbd5e1;background:#fff;border-radius:10px;padding:10px;font-size:.86rem;font-weight:700;cursor:pointer;color:#64748b;font-family:inherit;transition:all .15s}
.fin-toggle-btn.active{border-color:var(--azul);background:var(--azul-card);color:var(--azul-esc)}
.fin-section{display:none}
.fin-section.open{display:block}

/* SIMULATE BUTTON */
.btn-simular{width:100%;background:linear-gradient(120deg,var(--laranja),#ffb347);border:none;border-radius:12px;padding:14px 22px;font-weight:900;font-size:1rem;color:#3b2200;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:9px;box-shadow:0 4px 16px rgba(243,146,0,.35);font-family:inherit;margin-top:6px;transition:filter .15s,transform .1s}
.form-disclaimer{font-size:.73rem;color:#64748b;text-align:center;margin-top:10px;line-height:1.5;padding:0 4px}
.btn-simular:hover{filter:brightness(1.06);transform:translateY(-1px)}
.btn-simular:active{transform:translateY(0)}

/* RESULTS PANEL */
.sim-results-panel{display:flex;flex-direction:column;gap:16px}

.results-empty{background:#fff;border:1px solid var(--borda);border-radius:var(--radius);box-shadow:var(--sombra);padding:40px 20px;text-align:center;color:var(--muted)}
.results-empty .empty-icon{font-size:3rem;margin-bottom:12px;opacity:.5}
.results-empty p{font-size:.9rem;line-height:1.6}

/* Result Cards */
.res-card{border-radius:var(--radius);overflow:hidden;box-shadow:var(--sombra)}
.res-card-dark{background:linear-gradient(135deg,#0f172a,#1d4ed8);color:#fff;padding:18px 20px}
.res-card-dark .res-card-title{font-size:.75rem;font-weight:900;text-transform:uppercase;letter-spacing:.07em;opacity:.7;margin-bottom:12px}
.res-dark-row{display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.1)}
.res-dark-row:last-child{border-bottom:none;padding-top:10px;margin-top:4px}
.res-dark-label{font-size:.83rem;opacity:.8}
.res-dark-value{font-size:.9rem;font-weight:700}
.badge-desconto{background:#10b981;color:#fff;border-radius:999px;padding:3px 10px;font-size:.75rem;font-weight:900}
.badge-desconto.low{background:#94a3b8}
.badge-desconto.mod{background:#f59e0b}
.badge-desconto.good{background:#3b82f6}

.res-card-white{background:#fff;border:1px solid var(--borda);padding:18px 20px}
.res-card-title{font-size:.75rem;font-weight:900;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:12px}
.res-white-row{display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f1f5f9;font-size:.87rem}
.res-white-row:last-child{border-bottom:none}
.res-white-row .label{color:var(--muted)}
.res-white-row .value{font-weight:600;color:#334155}
.res-total-row{background:var(--azul-card);border-radius:10px;padding:10px 14px;margin-top:10px;display:flex;justify-content:space-between;align-items:center}
.res-total-row .label{font-size:.88rem;font-weight:900;color:var(--azul-esc)}
.res-total-row .value{font-size:1.05rem;font-weight:900;color:var(--azul-esc)}

.res-card-blue{background:var(--azul-card);border:1px solid var(--borda);padding:18px 20px}
.viab-row{display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid rgba(0,83,166,.1);font-size:.87rem}
.viab-row:last-child{border-bottom:none}
.viab-row .label{color:#334155}
.viab-row .value{font-weight:700;color:var(--azul-esc)}
.status-badge{display:inline-block;border-radius:999px;padding:5px 14px;font-size:.78rem;font-weight:900;margin-top:10px}
.status-excelente{background:#dcfce7;color:#166534;border:1px solid #bbf7d0}
.status-boa{background:#dbeafe;color:#1d4ed8;border:1px solid #bfdbfe}
.status-moderado{background:#fef3c7;color:#92400e;border:1px solid #fde68a}
.status-pouco{background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0}

.res-card-fin{background:#fff;border:1px solid var(--borda);padding:18px 20px}
.fin-row{display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f1f5f9;font-size:.87rem}
.fin-row:last-child{border-bottom:none}
.fin-row .label{color:var(--muted)}
.fin-row .value{font-weight:700;color:#111827}
.fin-link{display:block;margin-top:12px;text-align:center;font-size:.82rem;color:var(--azul);font-weight:700;text-decoration:underline}

.res-card-inv{background:#fff3e0;border:1px solid #fde0a0;padding:18px 20px}
.inv-row{display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #fde0a0;font-size:.87rem}
.inv-row:last-child{border-bottom:none}
.inv-row .label{color:#92400e}
.inv-row .value{font-weight:700;color:#b45309}
.inv-disclaimer{font-size:.74rem;color:#a16207;margin-top:8px;line-height:1.45}

.res-cta{background:var(--azul-esc);border-radius:var(--radius);padding:18px 20px;text-align:center;box-shadow:var(--sombra)}
.res-cta p{color:rgba(255,255,255,.8);font-size:.85rem;margin-bottom:12px}
.btn-wpp{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:var(--laranja);color:#3b1f00;border-radius:999px;padding:12px 24px;font-weight:900;font-size:.95rem;box-shadow:0 4px 14px rgba(243,146,0,.35);transition:filter .15s}
.btn-wpp:hover{filter:brightness(1.07)}

/* RESPONSIVO */
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
  .sim-layout{grid-template-columns:1fr}
  .faq-grid{grid-template-columns:1fr}
  .steps-grid{grid-template-columns:1fr;gap:10px}
  .step-card{padding:12px 14px;grid-template-columns:40px 1fr;column-gap:12px;row-gap:4px}
  .step-icon{width:36px;height:36px;font-size:1.1rem}
  .step-card h3{font-size:.92rem}
  .step-card p{font-size:.82rem}
  .hero-badges{display:none!important}
  .hero-title{font-size:1.5rem}
}
@media(max-width:700px){
  .hero-title{font-size:1.3rem}
  .sim-grid-2{grid-template-columns:1fr}
  .faq-grid{grid-template-columns:1fr}
  .step-card{background:#eaf2ff;border-color:#cfe2ff}
  .step-icon{background:#fff}
}
@media(max-width:420px){.hero-title{font-size:1.15rem}}
@media(min-width:901px){.nav-mobile{display:none!important}}
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
    <a href="index.php#busque-imoveis" onclick="document.getElementById('menu-toggle').checked=false">🔎 Buscar imóvel</a>
    <a href="index.php#oportunidades" onclick="document.getElementById('menu-toggle').checked=false">🏡 Oportunidades</a>
    <a href="resultados.html" onclick="document.getElementById('menu-toggle').checked=false">🔍 Buscar Imóveis</a>
    <a href="favoritos.html" onclick="document.getElementById('menu-toggle').checked=false">❤️ Favoritos</a>
    <a href="simulador-de-financiamento.php" class="active" onclick="document.getElementById('menu-toggle').checked=false">📊 Simulador</a>
    <a href="index.php#duvidas" onclick="document.getElementById('menu-toggle').checked=false">❓ Dúvidas</a>
    <a href="blog.html" onclick="document.getElementById('menu-toggle').checked=false">📝 Blog</a>
  </nav>
</header>

<!-- ===== HERO ===== -->
<section class="hero">
  <div class="hero-inner">
    <h1 class="hero-title">Simule o <em>Custo Total</em> de um Imóvel da CAIXA</h1>
    <p class="hero-sub">Calcule arremate, ITBI, cartório, reforma e descubra se o investimento vale a pena — antes de dar o lance.</p>
    <div class="hero-badges">
      <span class="badge">📊 Simulação gratuita</span>
      <span class="badge">🏦 Dados reais CAIXA</span>
      <span class="badge">💡 Decisão segura</span>
    </div>
  </div>
</section>

<!-- BREADCRUMB -->
<div class="breadcrumb">
  <a href="index.php">Início</a> &rsaquo;
  <a href="simulador-de-financiamento.php">Simulador</a> &rsaquo;
  <span>Simulador de Imóvel CAIXA</span>
</div>

<!-- ===== CALCULADORA PRINCIPAL ===== -->
<div class="sim-main-wrap">
  <div class="sim-layout">

    <!-- COLUNA ESQUERDA: FORMULÁRIO -->
    <div class="sim-form-card">
      <div class="sim-form-header">
        <h2>🏠 Dados do Imóvel</h2>
        <p>Preencha as informações para calcular o custo total da compra</p>
      </div>
      <div class="sim-form-body">

        <!-- SEÇÃO A: IMÓVEL -->
        <div class="sim-section">
          <div class="sim-section-title">A — Imóvel</div>
          <div class="sim-grid-2">
            <div class="fgroup">
              <label for="f_avaliacao">Valor de avaliação</label>
              <input type="text" id="f_avaliacao" placeholder="R$ 0" inputmode="numeric" oninput="mascaraPreco(this);atualizarPct()">
            </div>
            <div class="fgroup">
              <label for="f_arremate">Valor de arremate / compra</label>
              <input type="text" id="f_arremate" placeholder="R$ 0" inputmode="numeric" oninput="mascaraPreco(this);atualizarPct()">
              <div class="helper" id="pct_avaliacao" style="display:none"></div>
            </div>
            <div class="fgroup">
              <label for="f_modalidade">Modalidade</label>
              <select id="f_modalidade">
                <option value="leilao">Leilão SFI</option>
                <option value="licitacao">Licitação Aberta</option>
                <option value="venda_online">Venda Online</option>
                <option value="venda_direta">Venda Direta Online</option>
              </select>
            </div>
            <div class="fgroup">
              <label for="f_tipo_imovel">Tipo de imóvel</label>
              <select id="f_tipo_imovel">
                <option value="residencial">Residencial</option>
                <option value="comercial">Comercial</option>
              </select>
            </div>
            <div class="fgroup">
              <label for="f_estado">Estado</label>
              <select id="f_estado">
                <option value="">Selecione o estado</option>
                <option value="AC">AC - Acre</option>
                <option value="AL">AL - Alagoas</option>
                <option value="AM">AM - Amazonas</option>
                <option value="AP">AP - Amapá</option>
                <option value="BA">BA - Bahia</option>
                <option value="CE">CE - Ceará</option>
                <option value="DF">DF - Distrito Federal</option>
                <option value="ES">ES - Espírito Santo</option>
                <option value="GO">GO - Goiás</option>
                <option value="MA">MA - Maranhão</option>
                <option value="MG">MG - Minas Gerais</option>
                <option value="MS">MS - Mato Grosso do Sul</option>
                <option value="MT">MT - Mato Grosso</option>
                <option value="PA">PA - Pará</option>
                <option value="PB">PB - Paraíba</option>
                <option value="PE">PE - Pernambuco</option>
                <option value="PI">PI - Piauí</option>
                <option value="PR">PR - Paraná</option>
                <option value="RJ">RJ - Rio de Janeiro</option>
                <option value="RN">RN - Rio Grande do Norte</option>
                <option value="RO">RO - Rondônia</option>
                <option value="RR">RR - Roraima</option>
                <option value="RS">RS - Rio Grande do Sul</option>
                <option value="SC">SC - Santa Catarina</option>
                <option value="SE">SE - Sergipe</option>
                <option value="SP">SP - São Paulo</option>
                <option value="TO">TO - Tocantins</option>
              </select>
            </div>
            <div class="fgroup">
              <label for="f_cidade">Cidade</label>
              <input type="text" id="f_cidade" placeholder="Nome da cidade">
            </div>
            <div class="fgroup fgroup-full">
              <label for="f_area">Área do imóvel (m²) <span style="font-weight:400;color:var(--muted)">(opcional)</span></label>
              <input type="text" id="f_area" placeholder="Ex: 80" inputmode="numeric">
            </div>
          </div>
        </div>

        <!-- SEÇÃO B: CUSTOS ADICIONAIS -->
        <div class="sim-section">
          <div class="sim-section-title">B — Custos Adicionais</div>

          <!-- ITBI -->
          <div class="chk-toggle" onclick="toggleChk('chk_itbi','sub_itbi')">
            <input type="checkbox" id="chk_itbi">
            <label for="chk_itbi">Incluir ITBI (Imposto de Transmissão)</label>
          </div>
          <div class="chk-sub" id="sub_itbi">
            <div class="fgroup">
              <label for="f_itbi_aliq">Alíquota ITBI (%)</label>
              <input type="text" id="f_itbi_aliq" value="2" inputmode="decimal">
              <div class="helper">A alíquota varia por município. Em SP capital é 3%, na maioria das cidades é 2%. Consulte a prefeitura local.</div>
            </div>
          </div>

          <!-- Cartório -->
          <div class="chk-toggle" onclick="toggleChk('chk_cartorio','sub_cartorio')">
            <input type="checkbox" id="chk_cartorio">
            <label for="chk_cartorio">Incluir Registro em Cartório</label>
          </div>
          <div class="chk-sub" id="sub_cartorio">
            <div class="fgroup">
              <label for="f_cartorio_val">Valor estimado (R$)</label>
              <input type="text" id="f_cartorio_val" value="R$ 3.000" inputmode="numeric" oninput="mascaraPreco(this)">
              <div class="helper">Custos de cartório variam conforme o valor do imóvel e o estado. Valor médio estimado: R$ 2.000 a R$ 5.000.</div>
            </div>
          </div>

          <!-- Reforma -->
          <div class="chk-toggle" onclick="toggleChk('chk_reforma','sub_reforma')">
            <input type="checkbox" id="chk_reforma">
            <label for="chk_reforma">Incluir Reforma / Adequação</label>
          </div>
          <div class="chk-sub" id="sub_reforma">
            <div class="fgroup">
              <label for="f_reforma_val">Valor estimado (R$)</label>
              <input type="text" id="f_reforma_val" placeholder="R$ 0" inputmode="numeric" oninput="mascaraPreco(this)">
            </div>
          </div>

          <!-- Assessoria -->
          <div class="chk-toggle" onclick="toggleChk('chk_assessoria','sub_assessoria')">
            <input type="checkbox" id="chk_assessoria">
            <label for="chk_assessoria">Incluir Assessoria / Intermediação</label>
          </div>
          <div class="chk-sub" id="sub_assessoria">
            <div class="fgroup">
              <label for="f_assessoria_pct">Percentual sobre o arremate (%)</label>
              <input type="text" id="f_assessoria_pct" value="5" inputmode="decimal">
              <div class="helper">Honorários de assessoria jurídica e imobiliária. Valor usual: 5% sobre o valor de arremate.</div>
            </div>
          </div>
        </div>

        <!-- SEÇÃO C: FINANCIAMENTO -->
        <div class="sim-section">
          <div class="sim-section-title">C — Financiamento (opcional)</div>
          <div style="font-size:.83rem;color:var(--muted);margin-bottom:10px">Vai financiar parte do imóvel?</div>
          <div class="fin-toggle-wrap">
            <button class="fin-toggle-btn active" id="btn_fin_nao" onclick="setFin(false)" type="button">Não, à vista</button>
            <button class="fin-toggle-btn" id="btn_fin_sim" onclick="setFin(true)" type="button">Sim, quero financiar</button>
          </div>
          <div class="fin-section" id="fin_section">
            <div class="sim-grid-2">
              <div class="fgroup fgroup-full">
                <label for="f_fin_valor">Valor a financiar</label>
                <input type="text" id="f_fin_valor" placeholder="R$ 0" inputmode="numeric" oninput="mascaraPreco(this)">
              </div>
              <div class="fgroup">
                <label for="f_fin_prazo">Prazo (meses)</label>
                <select id="f_fin_prazo">
                  <option value="120">120 meses (10 anos)</option>
                  <option value="180">180 meses (15 anos)</option>
                  <option value="240" selected>240 meses (20 anos)</option>
                  <option value="300">300 meses (25 anos)</option>
                  <option value="360">360 meses (30 anos)</option>
                </select>
              </div>
              <div class="fgroup">
                <label for="f_fin_juros">Taxa de juros anual (%)</label>
                <input type="text" id="f_fin_juros" value="10.5" inputmode="decimal">
              </div>
            </div>
          </div>
        </div>

        <button class="btn-simular" onclick="simular()" type="button">
          📊 Simular agora
        </button>
        <p class="form-disclaimer">📋 Simulação estimada para referência. Não inclui seguros, taxas administrativas nem aprovação de crédito pelo banco. Para condições reais, consulte uma agência CAIXA ou correspondente bancário.</p>
      </div>
    </div>

    <!-- COLUNA DIREITA: RESULTADOS -->
    <div class="sim-results-panel" id="resultsPanel">
      <div class="results-empty" id="resultsEmpty">
        <div class="empty-icon">🏠</div>
        <p><strong>Preencha os dados do imóvel</strong> e clique em <strong>"Simular agora"</strong> para ver o custo total da compra, análise de viabilidade e muito mais.</p>
      </div>
    </div>

  </div>
</div>

<!-- ===== COMO FUNCIONA ===== -->
<div class="sec-outer alt">
  <div class="sec-inner">
    <h2 class="sec-title">Como funciona a compra de imóvel CAIXA</h2>
    <p class="sec-sub">Entenda o passo a passo para adquirir um imóvel da CAIXA com segurança</p>
    <div class="steps-grid">
      <div class="step-card">
        <div class="step-icon">🔍</div>
        <h3>Busque o imóvel na plataforma</h3>
        <p>Explore mais de 30.000 imóveis da CAIXA em todo o Brasil filtrando por estado, cidade, tipo e modalidade.</p>
      </div>
      <div class="step-card">
        <div class="step-icon">📊</div>
        <h3>Simule os custos totais</h3>
        <p>Use este simulador para calcular o custo total da compra: arremate, ITBI, cartório, reforma e financiamento.</p>
      </div>
      <div class="step-card">
        <div class="step-icon">🏦</div>
        <h3>Solicite habilitação na CAIXA</h3>
        <p>Faça sua habilitação no portal da CAIXA ou em uma agência. Você precisará de documentação pessoal e comprovação de renda.</p>
      </div>
      <div class="step-card">
        <div class="step-icon">🏠</div>
        <h3>Arremate e faça a transferência</h3>
        <p>Dê seu lance, finalize a compra e registre o imóvel em cartório. Conte com nossa equipe especializada em todo o processo.</p>
      </div>
    </div>
  </div>
</div>

<!-- ===== FAQ ===== -->
<div class="sec-outer">
  <div class="sec-inner">
    <h2 class="sec-title">Perguntas Frequentes</h2>
    <p class="sec-sub">Tire suas dúvidas sobre custos na compra de imóvel da CAIXA</p>
    <div class="faq-grid">
      <div class="faq-card">
        <h3>Quais são os custos além do lance?</h3>
        <p>Além do valor de arremate, o comprador deve considerar: ITBI (imposto municipal de 2% a 3% sobre o valor do imóvel), registro em cartório (R$ 2.000 a R$ 5.000 dependendo do estado e valor), eventuais dívidas de IPTU e condomínio, e custos de reforma ou adequação. Use este simulador para estimar todos esses custos antes de dar o lance.</p>
      </div>
      <div class="faq-card">
        <h3>Como funciona o ITBI na compra de imóvel de leilão?</h3>
        <p>O ITBI (Imposto sobre Transmissão de Bens Imóveis) é cobrado pela prefeitura municipal sempre que há transferência de propriedade, inclusive em leilões. A alíquota varia por município: na maioria das cidades é de 2%, mas em São Paulo capital é 3%. O valor é calculado sobre o maior entre o valor de arremate e a avaliação do imóvel pela prefeitura (valor venal).</p>
      </div>
      <div class="faq-card">
        <h3>Posso usar FGTS para comprar imóvel da CAIXA em leilão?</h3>
        <p>O uso do FGTS em leilões da CAIXA é limitado. Na modalidade Licitação Aberta e Venda Direta, geralmente é possível usar o FGTS para abater o valor ou amortizar financiamento — desde que o imóvel e o comprador atendam às regras do programa. Em leilões SFI, o uso do FGTS para pagamento à vista não é permitido, mas pode ser usado no financiamento pós-arremate.</p>
      </div>
      <div class="faq-card">
        <h3>Qual é o desconto médio nos imóveis da CAIXA?</h3>
        <p>Os imóveis da CAIXA costumam ter descontos entre 20% e 50% sobre o valor de avaliação, mas já foram registrados descontos de até 90% em casos extremos. O desconto médio varia por estado e tipo de imóvel: imóveis em regiões menos valorizadas tendem a ter descontos maiores. Descontos acima de 30% são considerados excelentes oportunidades de investimento.</p>
      </div>
    </div>
  </div>
</div>

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
          <a href="#" title="Facebook" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>        </span></div>
      </div>
    </div>
  
    </div>
        <div class="footer-copy">
      © <?= $ano ?> Arremate Imóveis Online — A plataforma de busca de imóveis da CAIXA em todo o Brasil.<br>
      <span style="color:#4b5563;font-size:13px">Este não é um site oficial da Caixa Econômica Federal. Plataforma independente de busca e comparação.</span>
    </div>
  </div>
</footer>

<script>
/* =============================================
   MÁSCARAS E FORMATAÇÃO
   ============================================= */
function mascaraPreco(el){
  var raw = el.value.replace(/[^\d]/g,'');
  if(!raw){el.value='';return;}
  var n = parseInt(raw,10);
  el.value = 'R$ ' + n.toLocaleString('pt-BR',{maximumFractionDigits:0});
}

function limpaNumeroBR(v){
  if(!v) return 0;
  return parseInt(String(v).replace(/[R$\s.]/g,'').replace(',','.'),10)||0;
}

function limpaDecimal(v){
  if(!v) return 0;
  return parseFloat(String(v).replace(',','.'))||0;
}

function fmtBRL(n){
  return (n||0).toLocaleString('pt-BR',{style:'currency',currency:'BRL',maximumFractionDigits:0});
}

function fmtPct(n){
  return (n||0).toFixed(1).replace('.',',') + '%';
}

/* =============================================
   ATUALIZAR PERCENTUAL DA AVALIAÇÃO
   ============================================= */
function atualizarPct(){
  var av = limpaNumeroBR(document.getElementById('f_avaliacao').value);
  var arr = limpaNumeroBR(document.getElementById('f_arremate').value);
  var el = document.getElementById('pct_avaliacao');
  if(av > 0 && arr > 0){
    var pct = (arr / av * 100).toFixed(1).replace('.',',');
    var desc = ((av - arr) / av * 100).toFixed(1).replace('.',',');
    el.style.display = 'block';
    if(arr <= av){
      el.className = 'helper highlight';
      el.textContent = pct + '% da avaliação — desconto de ' + desc + '%';
    } else {
      el.className = 'helper';
      el.style.color = '#dc2626';
      el.textContent = pct + '% da avaliação (acima do valor avaliado)';
    }
  } else {
    el.style.display = 'none';
  }
}

/* =============================================
   TOGGLE CHECKBOXES
   ============================================= */
function toggleChk(chkId, subId){
  var chk = document.getElementById(chkId);
  var sub = document.getElementById(subId);
  chk.checked = !chk.checked;
  sub.classList.toggle('open', chk.checked);
}

/* =============================================
   TOGGLE FINANCIAMENTO
   ============================================= */
var finAtivo = false;
function setFin(sim){
  finAtivo = sim;
  document.getElementById('btn_fin_nao').className = 'fin-toggle-btn' + (sim ? '' : ' active');
  document.getElementById('btn_fin_sim').className = 'fin-toggle-btn' + (sim ? ' active' : '');
  document.getElementById('fin_section').className = 'fin-section' + (sim ? ' open' : '');
}

/* =============================================
   CÁLCULO PRICE E SAC
   ============================================= */
function calcPrice(pv, n, iAnual){
  var i = iAnual / 100 / 12;
  if(i === 0) return pv / n;
  return pv * i / (1 - Math.pow(1 + i, -n));
}

function calcSACPrimeira(pv, n, iAnual){
  var i = iAnual / 100 / 12;
  var amort = pv / n;
  var juros = pv * i;
  return amort + juros;
}

/* =============================================
   BADGE DE STATUS
   ============================================= */
function getBadgeDesconto(desc){
  if(desc >= 30) return {cls:'badge-desconto', label:'Excelente oportunidade'};
  if(desc >= 15) return {cls:'badge-desconto good', label:'Boa oportunidade'};
  if(desc >= 5)  return {cls:'badge-desconto mod', label:'Desconto moderado'};
  return {cls:'badge-desconto low', label:'Pouco desconto'};
}

function getStatusBadge(desc){
  if(desc >= 30) return {cls:'status-badge status-excelente', label:'✅ Excelente oportunidade'};
  if(desc >= 15) return {cls:'status-badge status-boa', label:'🔵 Boa oportunidade'};
  if(desc >= 5)  return {cls:'status-badge status-moderado', label:'🟡 Desconto moderado'};
  return {cls:'status-badge status-pouco', label:'⚪ Pouco desconto'};
}

/* =============================================
   SIMULAÇÃO PRINCIPAL
   ============================================= */
function simular(){
  var avaliacao   = limpaNumeroBR(document.getElementById('f_avaliacao').value);
  var arremate    = limpaNumeroBR(document.getElementById('f_arremate').value);
  var tipoImovel  = document.getElementById('f_tipo_imovel').value;
  var area        = limpaDecimal(document.getElementById('f_area').value) || 0;

  if(!avaliacao || !arremate){
    alert('Por favor, informe o valor de avaliação e o valor de arremate/compra.');
    return;
  }

  // Desconto
  var desconto = avaliacao > 0 ? (avaliacao - arremate) / avaliacao * 100 : 0;

  // ITBI
  var itbi = 0;
  if(document.getElementById('chk_itbi').checked){
    var aliq = limpaDecimal(document.getElementById('f_itbi_aliq').value) || 2;
    itbi = arremate * (aliq / 100);
  }

  // Cartório
  var cartorio = 0;
  if(document.getElementById('chk_cartorio').checked){
    cartorio = limpaNumeroBR(document.getElementById('f_cartorio_val').value) || 3000;
  }

  // Reforma
  var reforma = 0;
  if(document.getElementById('chk_reforma').checked){
    reforma = limpaNumeroBR(document.getElementById('f_reforma_val').value) || 0;
  }

  // Assessoria
  var assessoria = 0;
  if(document.getElementById('chk_assessoria').checked){
    var pctAssess = limpaDecimal(document.getElementById('f_assessoria_pct').value) || 5;
    assessoria = arremate * (pctAssess / 100);
  }

  var total = arremate + itbi + cartorio + reforma + assessoria;

  // Financiamento
  var parcelaPRICE = 0, parcelaSAC = 0, totalPago = 0;
  var finValor = 0, finPrazo = 0, finJuros = 0;
  if(finAtivo){
    finValor = limpaNumeroBR(document.getElementById('f_fin_valor').value);
    finPrazo = parseInt(document.getElementById('f_fin_prazo').value);
    finJuros = limpaDecimal(document.getElementById('f_fin_juros').value) || 10.5;
    if(finValor > 0 && finPrazo > 0){
      parcelaPRICE = calcPrice(finValor, finPrazo, finJuros);
      parcelaSAC   = calcSACPrimeira(finValor, finPrazo, finJuros);
      totalPago    = parcelaPRICE * finPrazo;
    }
  }

  // Badge de desconto
  var badge = getBadgeDesconto(desconto);
  var statusB = getStatusBadge(desconto);

  // Rentabilidade
  var rentaMensal = tipoImovel === 'comercial' ? arremate * 0.008 : arremate * 0.005;
  var mesesBreakeven = rentaMensal > 0 ? Math.round(total / rentaMensal) : 0;
  var rentaAnual = rentaMensal * 12;
  var roiAnual = total > 0 ? (rentaAnual / total * 100) : 0;
  var mostrarInv = (tipoImovel === 'comercial' || desconto >= 20);

  // Montar painel
  var panel = document.getElementById('resultsPanel');
  var html = '';

  // Card 1: Resumo do Arremate
  html += '<div class="res-card">';
  html += '<div class="res-card-dark">';
  html += '<div class="res-card-title">📋 Resumo do Arremate</div>';
  html += '<div class="res-dark-row"><span class="res-dark-label">Valor de avaliação</span><span class="res-dark-value">' + fmtBRL(avaliacao) + '</span></div>';
  html += '<div class="res-dark-row"><span class="res-dark-label">Valor de arremate</span><span class="res-dark-value">' + fmtBRL(arremate) + '</span></div>';
  html += '<div class="res-dark-row"><span class="res-dark-label">Desconto obtido</span><span class="res-dark-value"><span class="' + badge.cls + '">' + fmtPct(desconto) + ' OFF</span></span></div>';
  html += '</div></div>';

  // Card 2: Custos Totais
  html += '<div class="res-card">';
  html += '<div class="res-card-white">';
  html += '<div class="res-card-title">💰 Custos Totais da Compra</div>';
  html += '<div class="res-white-row"><span class="label">Arremate</span><span class="value">' + fmtBRL(arremate) + '</span></div>';
  if(itbi > 0)       html += '<div class="res-white-row"><span class="label">ITBI</span><span class="value">' + fmtBRL(itbi) + '</span></div>';
  if(cartorio > 0)   html += '<div class="res-white-row"><span class="label">Registro em Cartório</span><span class="value">' + fmtBRL(cartorio) + '</span></div>';
  if(reforma > 0)    html += '<div class="res-white-row"><span class="label">Reforma / Adequação</span><span class="value">' + fmtBRL(reforma) + '</span></div>';
  if(assessoria > 0) html += '<div class="res-white-row"><span class="label">Assessoria / Intermediação</span><span class="value">' + fmtBRL(assessoria) + '</span></div>';
  html += '<div class="res-total-row"><span class="label">Total necessário</span><span class="value">' + fmtBRL(total) + '</span></div>';
  html += '</div></div>';

  // Card 3: Viabilidade
  html += '<div class="res-card">';
  html += '<div class="res-card-blue">';
  html += '<div class="res-card-title" style="color:var(--azul-esc)">📈 Análise de Viabilidade</div>';
  html += '<div class="viab-row"><span class="label">Desconto sobre avaliação</span><span class="value">' + fmtPct(desconto) + '</span></div>';
  if(area > 0){
    var custoPorM2 = total / area;
    html += '<div class="viab-row"><span class="label">Custo total por m²</span><span class="value">' + fmtBRL(custoPorM2) + '/m²</span></div>';
    html += '<div class="viab-row"><span class="label">Arremate por m²</span><span class="value">' + fmtBRL(arremate / area) + '/m²</span></div>';
  }
  html += '<div><span class="' + statusB.cls + '">' + statusB.label + '</span></div>';
  html += '</div></div>';

  // Card 4: Financiamento (se ativo)
  if(finAtivo && finValor > 0 && parcelaPRICE > 0){
    html += '<div class="res-card">';
    html += '<div class="res-card-fin">';
    html += '<div class="res-card-title">🏦 Simulação de Financiamento</div>';
    html += '<div class="fin-row"><span class="label">Valor financiado</span><span class="value">' + fmtBRL(finValor) + '</span></div>';
    html += '<div class="fin-row"><span class="label">Prazo</span><span class="value">' + finPrazo + ' meses</span></div>';
    html += '<div class="fin-row"><span class="label">Taxa de juros</span><span class="value">' + finJuros.toFixed(1).replace('.',',') + '% a.a.</span></div>';
    html += '<div class="fin-row"><span class="label">Parcela estimada PRICE</span><span class="value">' + fmtBRL(Math.round(parcelaPRICE)) + '/mês</span></div>';
    html += '<div class="fin-row"><span class="label">Parcela SAC (1ª parcela)</span><span class="value">' + fmtBRL(Math.round(parcelaSAC)) + '/mês</span></div>';
    html += '<div class="fin-row"><span class="label">Total pago (PRICE)</span><span class="value">' + fmtBRL(Math.round(totalPago)) + '</span></div>';
    html += '<a href="simulador-de-financiamento.php" class="fin-link">📊 Simulação completa de financiamento →</a>';
    html += '</div></div>';
  }

  // Card 5: Retorno para Investidores
  if(mostrarInv){
    html += '<div class="res-card">';
    html += '<div class="res-card-inv">';
    html += '<div class="res-card-title" style="color:#92400e">💼 Retorno para Investidores</div>';
    html += '<div class="inv-row"><span class="label">Renda potencial / mês</span><span class="value">' + fmtBRL(Math.round(rentaMensal)) + '</span></div>';
    html += '<div class="inv-row"><span class="label">Rentabilidade bruta anual</span><span class="value">' + roiAnual.toFixed(1).replace('.',',') + '%</span></div>';
    html += '<div class="inv-row"><span class="label">Break-even estimado</span><span class="value">' + mesesBreakeven + ' meses</span></div>';
    html += '<div class="inv-disclaimer">* Estimativa baseada em 0,5% (residencial) ou 0,8% (comercial) do valor de arremate ao mês. Valores reais dependem de localização, estado do imóvel e mercado local.</div>';
    html += '</div></div>';
  }

  // CTA WhatsApp
  html += '<div class="res-cta">';
  html += '<p>Gostou da simulação? Fale com um especialista e tire todas as dúvidas antes de dar o lance.</p>';
  html += '<a href="https://wa.me/5512997651740?text=Olá! Fiz uma simulação no Arremate Imóveis Online e quero saber mais sobre o imóvel." class="btn-wpp" target="_blank" rel="noopener">💬 Falar com Especialista</a>';
  html += '</div>';

  panel.innerHTML = html;

  // Scroll para resultados no mobile
  if(window.innerWidth < 900){
    panel.scrollIntoView({behavior:'smooth', block:'start'});
  }
}
</script>
<script src="logo-fit.js"></script>
</body>
</html>
