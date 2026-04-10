<?php
// index.php — Arremate Imóveis Online (Versão Nacional)
$ano = date('Y');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="color-scheme" content="light">
  <title>Arremate Imóveis Online | Busca de Imóveis da CAIXA com Desconto em Todo o Brasil</title>
  <meta name="keywords" content="imóveis CAIXA, leilão imóveis CAIXA, buscar imóvel CAIXA, imóveis CAIXA desconto, licitação CAIXA, venda direta CAIXA, venda online CAIXA, imóvel CAIXA FGTS, imóvel CAIXA financiamento, imóveis CAIXA São Paulo, imóveis CAIXA abaixo avaliação, plataforma imóveis CAIXA Brasil, busca imóvel CAIXA online, arremate imóveis online">
  <meta name="description" content="Encontre imóveis da CAIXA com descontos de até 90% em todo o Brasil. Filtre por estado, cidade, tipo, modalidade (leilão, licitação, venda direta e online) e condições de pagamento. Imobiliária parceira credenciada CRECI-SP 043342.">
  <meta property="og:title" content="Arremate Imóveis Online | Imóveis da CAIXA com Desconto em Todo o Brasil">
  <meta property="og:description" content="A plataforma mais completa para buscar imóveis da CAIXA. Filtros por estado, cidade, tipo, modalidade e desconto. Dados atualizados diariamente. Imobiliária parceira credenciada CRECI-SP 043342.">
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
html{scroll-behavior:smooth;color-scheme:light}
body{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--azul-bg)!important;color:var(--texto);line-height:1.55;font-size:15px}
a{color:inherit;text-decoration:none}
[id]{scroll-margin-top:80px}
input,select,textarea,button{color-scheme:light}
input,select,textarea{background-color:#fff;color:#0f172a}
.modal,.modal-hdr,.mpanel{background-color:#fff}

/* HERO */
.hero{background:linear-gradient(135deg,#0b1a33 0%,#001634 50%,var(--azul) 100%);color:#fff;padding:36px 20px 32px}
.hero-inner{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:36px;align-items:start}
.hero-title{font-size:1.85rem;font-weight:900;line-height:1.22;margin-bottom:12px}
.hero-title em{color:var(--laranja);font-style:normal}
.hero-sub{font-size:.95rem;opacity:.92;margin-bottom:18px}
.hero-badges{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px}
.badge{display:inline-flex;align-items:center;gap:6px;border:1px solid rgba(255,255,255,.28);background:rgba(0,0,0,.18);border-radius:999px;padding:5px 11px;font-size:.77rem}
.hero-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:14px}
.hero-stat{background:rgba(0,0,0,.25);border:1px solid rgba(255,255,255,.18);border-radius:12px;padding:10px 12px;font-size:.78rem}
.hero-stat strong{display:block;font-size:1.05rem;margin-bottom:2px}

/* CARD FILTRO */
.filter-card{background:#fff;border-radius:var(--radius);box-shadow:var(--sombra);border:1px solid var(--borda);padding:20px}
.filter-card-title{font-size:1rem;font-weight:900;color:var(--azul-esc);margin-bottom:14px}
.filter-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px 14px}
.fgroup{display:flex;flex-direction:column;gap:4px}
.fgroup label{font-size:.74rem;font-weight:700;color:#475569}
.fgroup select,.fgroup input[type=text],.fgroup input[type=number],.fgroup input[type=date]{border:1px solid #cbd5e1;border-radius:999px;padding:9px 14px;font-size:.84rem;outline:none;background:#fff;width:100%;font-family:inherit}
.fgroup select:focus,.fgroup input:focus{border-color:var(--azul);box-shadow:0 0 0 2px rgba(0,83,166,.15)}
.filter-actions{grid-column:1/-1;display:flex;gap:10px;flex-wrap:wrap;margin-top:4px}
.filter-note{font-size:.74rem;color:#64748b;margin-top:10px;line-height:1.4}
.filter-note strong{color:#1e293b}

/* BOTÕES */
.btn-primary{background:linear-gradient(120deg,var(--laranja),#ffb347);border:none;border-radius:999px;padding:10px 22px;font-weight:900;font-size:.87rem;color:#3b2200;cursor:pointer;display:inline-flex;align-items:center;gap:7px;white-space:nowrap;box-shadow:0 4px 12px rgba(0,0,0,.18);font-family:inherit}
.btn-primary:hover{filter:brightness(1.05)}
.btn-ghost{background:rgba(0,83,166,.08);border:1.5px solid rgba(0,83,166,.3);border-radius:999px;padding:9px 18px;font-weight:900;font-size:.84rem;color:var(--azul-esc);cursor:pointer;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;font-family:inherit}
.btn-ghost:hover{background:rgba(0,83,166,.15)}

/* SEÇÕES */
.sec-outer{width:100%}
.sec-outer.alt{background:var(--azul-card)}
.sec-outer.sim-bg{background:#dce8fd;border-top:1px solid #c3d8f8;border-bottom:1px solid #c3d8f8}
.sec-inner{max-width:1200px;margin:0 auto;padding:36px 20px 32px}
.sec-title{font-size:1.15rem;font-weight:900;color:var(--azul-esc);margin-bottom:6px}
.sec-sub{font-size:.9rem;color:var(--muted);margin-bottom:20px}

/* STEPS */
.steps-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.step-card{background:#fff;border:1px solid var(--borda);border-radius:var(--radius);padding:16px;box-shadow:var(--sombra);display:grid;grid-template-columns:44px 1fr;column-gap:12px;row-gap:6px;align-items:center}
.step-icon{width:44px;height:44px;border-radius:50%;background:var(--azul-card);border:1px solid var(--borda);display:flex;align-items:center;justify-content:center;font-size:1.35rem;flex-shrink:0;grid-column:1;grid-row:1}
.step-icon.orange{background:#fff3e0;border-color:#ffd7a1}
.step-card h3{font-size:.95rem;font-weight:900;color:#111827;margin:0;grid-column:2;grid-row:1;line-height:1.15}
.step-card p{font-size:.83rem;color:var(--muted);margin:0;grid-column:1 / -1;grid-row:2;line-height:1.35}

/* IMÓVEIS */
.imoveis-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
.imovel-card{background:#fff;border:1px solid var(--borda);border-radius:var(--radius);box-shadow:var(--sombra);display:flex;flex-direction:column;overflow:hidden;position:relative;transition:box-shadow .15s,transform .15s;min-height:480px}
.imovel-card:hover{box-shadow:0 10px 34px rgba(15,23,42,.25);transform:translateY(-1px)}
.imovel-thumb{height:160px;position:relative;overflow:hidden;background:linear-gradient(135deg,#dbeafe,#eff6ff);flex-shrink:0}
.imovel-thumb-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block}
.thumb-fallback{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:2.2rem;color:#cbd5e1}
.tag-tipo{position:absolute;top:10px;left:10px;background:#064e3b;color:#f9fafb;font-size:.72rem;font-weight:900;padding:4px 12px;border-radius:999px;z-index:10}
.tag-desc{position:absolute;top:10px;right:10px;background:var(--laranja);color:#3b1f00;font-size:.72rem;font-weight:900;padding:5px 14px;border-radius:999px;z-index:10;box-shadow:0 3px 8px rgba(0,0,0,.18)}
.imovel-body{padding:12px 14px 10px;flex:1;display:flex;flex-direction:column;gap:4px;min-height:0}
.imovel-local{font-size:1rem;font-weight:700;color:#111827;margin-bottom:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.imovel-titulo{font-size:.80rem;font-weight:700;color:var(--muted);margin-bottom:1px}
.imovel-endereco{font-size:.78rem;color:#6b7280;margin-bottom:6px;min-height:2.4em;line-height:1.2em;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.imovel-precos{background:linear-gradient(135deg,#0f172a,#1d4ed8);border-radius:8px;padding:7px 11px;margin-top:6px;display:flex;flex-direction:column;gap:1px}
.preco-av{color:#94a3b8;text-decoration:line-through;font-size:.7rem;display:block}
.preco-min{color:#f9fafb;font-weight:900;font-size:.9rem}
.preco-venda-row{display:flex;align-items:center;justify-content:space-between;gap:6px;flex-wrap:nowrap}
.preco-desc-badge{background:#f39200;color:#3b1f00;border-radius:999px;padding:3px 8px;font-size:.7rem;font-weight:900;white-space:nowrap;flex-shrink:0}
.imovel-chips{margin-top:6px;display:flex;flex-direction:column;gap:8px;font-size:.8rem;color:#4b5563}
.chips-condicoes{display:flex;flex-wrap:wrap;gap:6px 10px}
.badge-cond{padding:3px 10px;border-radius:999px;font-weight:800;font-size:.72rem}
.badge-fgts{background:#dcfce7;color:#166534}
.badge-fin{background:#dbeafe;color:#1d4ed8}
.badge-disputa{background:#fee2e2;color:#b91c1c}
.imovel-footer{padding:9px 14px 11px;border-top:1px solid #e5e7eb;background:#f8fafc;display:flex;align-items:center;justify-content:space-between;gap:10px}
.status-ok{background:#d1fae5;color:#065f46;border-radius:999px;padding:4px 10px;font-weight:900;font-size:.72rem}
.status-atenc{background:#fef3c7;color:#92400e;border-radius:999px;padding:4px 10px;font-weight:900;font-size:.72rem}
.imovel-link{background:var(--laranja);color:#3b1f00;padding:8px 16px;border-radius:999px;font-weight:900;font-size:.86rem;text-decoration:none;box-shadow:0 3px 8px rgba(0,0,0,.2)}
.imovel-creci{margin-top:6px;font-size:.78rem;color:#4b5563;display:flex;align-items:center;gap:6px}
.imovel-creci-text{cursor:pointer}
.imovel-creci-copy{width:16px;height:16px;border-radius:3px;border:1px solid #cbd5e1;display:flex;align-items:center;justify-content:center;font-size:.70rem;color:#4b5563;cursor:pointer;background:#f8fafc}
.imovel-creci-text.copied,.imovel-creci-copy.copied{color:#16a34a;border-color:#16a34a}
.creci-toast{position:fixed;right:16px;bottom:16px;background:#fff;border:1px solid rgba(148,163,184,.35);box-shadow:0 10px 25px rgba(15,23,42,.18);padding:10px 14px;border-radius:10px;font-size:.82rem;color:#111827;display:flex;align-items:flex-start;gap:8px;opacity:0;transform:translateY(10px);pointer-events:none;transition:opacity .18s ease,transform .18s ease;z-index:9999}
.creci-toast.visible{opacity:1;transform:translateY(0)}
.creci-toast-icon{width:18px;height:18px;border-radius:999px;background:#dcfce7;color:#16a34a;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0}

/* SIMULADOR */
.sim-wrap{display:grid;grid-template-columns:1fr 1fr;gap:18px;align-items:start}
.sim-box{background:#fff;border:1px solid var(--borda);border-radius:var(--radius);padding:18px;box-shadow:var(--sombra)}
.sim-title{font-weight:900;font-size:.98rem;margin-bottom:14px;color:#111827}
.sim-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px 14px;margin-bottom:14px}
.sim-result{background:linear-gradient(135deg,#eff6ff,#f0f9ff);border:1px solid #bfdbfe;border-radius:12px;padding:14px;font-size:.86rem;line-height:1.8}
.sim-note{font-size:.74rem;color:var(--muted);margin-top:10px;line-height:1.45}

/* Ajuste de vão entre seções */
#oportunidades .sec-inner{padding-bottom:0!important}
#oport-atualizacao{margin-top:6px!important;margin-bottom:0!important}

/* FAQ */
.faq-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
.faq-card{background:#fff;border:1px solid var(--borda);border-radius:var(--radius);padding:16px;box-shadow:var(--sombra)}
.faq-card h3{font-size:.95rem;font-weight:900;margin-bottom:6px;color:#111827}
.faq-card p{font-size:.83rem;color:var(--muted)}

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

/* MODAL */
.overlay{position:fixed;inset:0;background:rgba(2,6,23,.65);z-index:999;display:none;align-items:flex-start;justify-content:center;padding:16px;overflow-y:auto}
.overlay.open{display:flex}
.modal{width:min(660px,100%);background:#f7fbff;border-radius:16px;border:1px solid var(--borda);box-shadow:0 20px 60px rgba(0,0,0,.35);margin:auto;position:relative}
.modal-hdr{background:#fff;padding:14px 18px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;border-radius:16px 16px 0 0;position:sticky;top:0;z-index:2}
.modal-hdr-title{font-weight:900;font-size:1rem;color:#111827;display:flex;align-items:center;gap:8px}
.modal-close{border:none;background:#f1f5f9;cursor:pointer;font-size:1.1rem;padding:8px 10px;border-radius:8px;color:#111827;line-height:1;font-family:inherit;min-width:36px;min-height:36px}
.modal-close:hover{background:#e2e8f0}
.modal-body{padding:16px 18px;display:flex;flex-direction:column;gap:14px}
.mpanel{background:#fff;border:1px solid var(--borda);border-radius:12px;padding:14px 16px}
.mpanel-title{font-weight:900;color:#111827;margin-bottom:10px;font-size:.93rem}
.panel-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px 14px}
.chk-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.chk-item{display:flex;align-items:center;gap:8px;background:#f8faff;border:1px solid #e2e8f0;border-radius:10px;padding:9px 11px;font-size:.84rem;cursor:pointer}
.chk-item input{width:16px;height:16px;accent-color:var(--azul);cursor:pointer}
.range-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:8px}
.chips-wrap{display:flex;flex-wrap:wrap;gap:7px;margin-top:8px;min-height:10px}
.chip{display:inline-flex;align-items:center;gap:6px;background:var(--azul-card);border:1px solid var(--borda);border-radius:999px;padding:4px 10px;font-size:.78rem;font-weight:900;color:var(--azul-esc)}
.chip button{border:none;background:transparent;cursor:pointer;font-size:1rem;color:var(--azul-esc);line-height:1;padding:0;font-family:inherit}
.add-btn{display:inline-flex;align-items:center;gap:5px;background:rgba(0,83,166,.07);border:1px solid rgba(0,83,166,.2);border-radius:999px;padding:6px 13px;font-size:.78rem;font-weight:900;color:var(--azul-esc);cursor:pointer;margin-top:8px;font-family:inherit}
.add-btn:hover{background:rgba(0,83,166,.14)}
.modal-footer{background:#fff;border-top:1px solid #e2e8f0;padding:12px 18px;display:flex;gap:10px;border-radius:0 0 16px 16px;position:sticky;bottom:0}
.btn-limpar{flex:1;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:12px;padding:11px;font-weight:900;font-size:.88rem;cursor:pointer;color:#111827;font-family:inherit}
.btn-aplicar{flex:1.4;background:var(--laranja);border:1px solid #e08000;border-radius:12px;padding:11px;font-weight:900;font-size:.88rem;cursor:pointer;color:#3b1f00;display:flex;align-items:center;justify-content:center;gap:8px;font-family:inherit}
.btn-limpar:hover{filter:brightness(.97)}
.btn-aplicar:hover{filter:brightness(1.04)}

/* ABAS OPORTUNIDADES */
.oport-tabs{display:flex;gap:10px;justify-content:center;margin-bottom:28px;flex-wrap:wrap}
.oport-tab{background:#fff3e0;border:2px solid var(--laranja);border-radius:999px;padding:9px 22px;font-weight:900;font-size:.88rem;color:#3b2200;cursor:pointer;font-family:inherit;transition:background .18s,color .18s,box-shadow .18s}
.oport-tab:hover{background:#ffe0a0;box-shadow:0 3px 10px rgba(243,146,0,.25)}
.oport-tab.active{background:linear-gradient(120deg,var(--laranja),#ffb347);color:#3b1f00;border-color:#e08000;box-shadow:0 4px 14px rgba(243,146,0,.35)}
.oport-more-wrap{text-align:center;margin-top:14px;display:flex;justify-content:center}
.oport-tab.oport-more{padding:7px 16px;font-size:.8rem}

/* HEADER */
.menu-chk{display:none!important;position:absolute;left:-9999px}
.site-header{position:sticky;top:0;z-index:200;background:#01468d;box-shadow:0 2px 10px rgba(0,0,0,.25)}
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
.hamburger{display:none;align-items:center;justify-content:center;width:44px;height:44px;flex-shrink:0;background:rgba(255,255,255,.1);border:2px solid rgba(255,255,255,.4);border-radius:10px;cursor:pointer;color:#fff}
.hamburger svg{width:22px;height:22px;display:block}
.nav-mobile{display:none;flex-direction:column;width:100%;background:#dceeff;border-top:2px solid #a8cfee}
.menu-chk:checked~.nav-mobile{display:flex!important}
.nav-mobile a{display:block;padding:14px 20px;font-size:.97rem;font-weight:700;color:#0b1a33;background:#e8f3ff;border-bottom:1px solid #b8d8f5;text-decoration:none;transition:background .15s}
.nav-mobile a:hover{background:#cde5ff}
.nav-mob-cta{background:#e97500!important;color:#fff!important;font-weight:900!important;border-bottom:none!important}
.nav-mob-close{display:flex;align-items:center;padding:12px 20px;font-size:.9rem;font-weight:700;color:#01468d;background:#c8e0f8;border-bottom:2px solid #a8cfee;cursor:pointer}

/* RESPONSIVO */
@media(max-width:1200px){.imoveis-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:1080px){.steps-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:900px){
  .hdr{padding:0 12px;min-height:76px;gap:8px}
  .nav-links{display:none!important}
  .hamburger{display:flex!important}
  .logo-icon{width:54px!important;height:54px!important}
  .logo-icon-img{width:54px!important;height:54px!important}
  .logo-aio{font-size:1.13rem}
  .logo-txt{max-width:calc(100vw - 112px)}
  .logo-sub-full{display:none}
  .logo-sub-mobile{display:block}
  .logo-sub{white-space:normal;font-size:.71rem;text-align:left;text-align-last:left}
  .hero-inner{grid-template-columns:1fr}
  .hero-stats{grid-template-columns:repeat(2,1fr)}
  .sim-wrap{grid-template-columns:1fr}
  .faq-grid{grid-template-columns:1fr}
  .steps-grid{grid-template-columns:1fr;gap:10px}
  .step-card{padding:12px 14px;grid-template-columns:40px 1fr;column-gap:12px;row-gap:4px}
  .step-icon{width:36px;height:36px;font-size:1.1rem}
  .step-card h3{font-size:.92rem}
  .step-card p{font-size:.82rem}
  /* Ocultar seções no mobile */
  #sobre{display:none!important}
  .hero-badges,.hero-stats{display:none!important}
}
@media(max-width:700px){
  .filter-grid{grid-template-columns:1fr}
  .filter-actions{flex-direction:column}
  .imoveis-grid{grid-template-columns:1fr}
  .hero-title{font-size:1.45rem}
  .panel-grid{grid-template-columns:1fr}
  .chk-grid{grid-template-columns:1fr}
  .range-row{grid-template-columns:1fr}
  .sim-grid{grid-template-columns:1fr}
  .hero-stats{grid-template-columns:1fr 1fr}
  .step-card{background:#eaf2ff;border-color:#cfe2ff}
  .step-icon{background:#fff}
}
@media(max-width:420px){
  .hero-stats{grid-template-columns:1fr}
  .hero-title{font-size:1.25rem}
}
@media(min-width:901px){.nav-mobile{display:none!important}}
/* Ajuste de proximidade e Favoritos */
#oportunidades { padding-bottom: 0px !important; }
.oport-more-wrap { margin-bottom: 0; margin-top: 36px; }
#simulador { margin-top: -28px; }
.card-actions{display:flex;align-items:center;gap:6px;position:absolute;bottom:8px;right:8px;z-index:20}
.btn-card-action{width:32px;height:32px;border-radius:8px;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.92);box-shadow:0 2px 6px rgba(0,0,0,.2);transition:transform .15s,background .15s;flex-shrink:0}
.btn-card-action:hover{background:#fff;transform:scale(1.08)}
.btn-card-action svg{width:16px;height:16px;display:block}
.btn-fav{color:#e11d48}
.btn-fav.ativo{background:#fee2e2!important;color:#e11d48}
.btn-fav.ativo svg path,.btn-fav.ativo svg{fill:#e11d48}
.btn-share{color:#0053a6}
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
      <a href="index.php#oportunidades" class="active">Oportunidades</a>
      <a href="resultados.html">Buscar Imóveis</a>
      <a href="favoritos.html">❤️ Favoritos</a>
      <a href="simulador-de-financiamento.php">Simulador</a>
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
    <a href="simulador-de-financiamento.php" onclick="document.getElementById('menu-toggle').checked=false">📊 Simulador</a>
    <a href="index.php#duvidas" onclick="document.getElementById('menu-toggle').checked=false">❓ Dúvidas</a>
    <a href="blog.html" onclick="document.getElementById('menu-toggle').checked=false">📝 Blog</a>
  </nav>
</header>

<!-- ===== HERO ===== -->
<section class="hero">
  <div class="hero-inner">
    <div>
      <h1 class="hero-title">Sua plataforma inteligente para encontrar <em>imóveis da CAIXA</em> em todo o Brasil.</h1>
      <p class="hero-sub">Filtre as melhores ofertas por localização e modalidade em segundos. Acesse oportunidades exclusivas com <strong style="color:var(--laranja)">até 90% de desconto</strong> e <strong style="color:var(--laranja)">antecipe-se ao mercado.</strong></p>
      <div class="hero-badges">
        <span class="badge">🏠 Conquiste seu lar com <strong>economia</strong></span>
        <span class="badge">📈 Invista seu capital com <strong>inteligência</strong></span>
        <span class="badge">🏦 Imobiliária parceira <span style="color:var(--laranja);font-weight:900">CRECI-SP 043342</span></span>
      </div>
      <div class="hero-stats">
        <div class="hero-stat"><strong>30.000+ imóveis</strong>cadastrados nas principais regiões do Brasil</div>
        <div class="hero-stat"><strong>Até 90% OFF</strong>desconto real sobre o valor de avaliação</div>
        <div class="hero-stat"><strong>Atualização diária</strong>dados direto do portal oficial da CAIXA</div>
      </div>
    </div>

    <!-- FILTRO -->
    <div class="filter-card" id="busque-imoveis">
      <div class="filter-card-title">🔎 Busque imóveis em todo Brasil</div>
      <div class="filter-grid">
        <div class="fgroup">
          <label>Estado (UF)</label>
          <select id="f_uf">
            <option value="">Todos os estados</option>
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
          <label>Cidade</label>
          <select id="f_cidade">
            <option value="">Todas as cidades</option>
          </select>
        </div>
        <div class="fgroup">
          <label>Tipo de imóvel</label>
          <select id="f_tipo">
            <option value="">Todos</option>
            <option value="Apartamento">Apartamento</option>
            <option value="Casa">Casa</option>
            <option value="Comercial">Comercial</option>
            <option value="Terreno">Terreno</option>
            <option value="Lote">Lote</option>
          </select>
        </div>
        <div class="fgroup">
          <label>Modalidade</label>
          <select id="f_modalidade">
            <option value="">Todas</option>
            <option value="Leilão SFI - Edital Único">Leilão SFI</option>
            <option value="Licitação Aberta">Licitação Aberta</option>
            <option value="Venda Online">Venda Online</option>
            <option value="Venda Direta Online">Compra Direta</option>
          </select>
        </div>
        <div class="fgroup">
          <label>Condições</label>
          <select id="f_condicao">
            <option value="">Todas as condições</option>
            <option value="fgts">Aceita FGTS</option>
            <option value="fin">Aceita Financiamento</option>
            <option value="disputa">Em disputa</option>
          </select>
        </div>
        <div class="fgroup">
          <label>Preço de venda (até)</label>
          <select id="f_valor">
            <option value="">Qualquer</option>
            <option value="150000">R$ 150 mil</option>
            <option value="250000">R$ 250 mil</option>
            <option value="400000">R$ 400 mil</option>
            <option value="700000">R$ 700 mil</option>
            <option value="1000000">R$ 1 milhão</option>
          </select>
        </div>
        <div class="filter-actions">
          <button class="btn-primary" id="btnBuscar" type="button" onclick="buscar()">🔎 Buscar</button>
          <button class="btn-ghost" id="btnFiltros" type="button" onclick="abrirModal()"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg> Mais filtros</button>
          <button class="btn-ghost" id="btnLimparHero" type="button" onclick="limparHero()">✕ Limpar</button>
        </div>
      </div>
      <p class="filter-note"><strong>Mais de 30.000 imóveis da CAIXA disponíveis.</strong> Use os filtros acima ou clique em <strong>Mais filtros</strong> para refinar por desconto, área, IPTU, condomínio e data de encerramento.</p>
    </div>
  </div>
</section>

<!-- ===== SOBRE A PLATAFORMA ===== -->
<section class="sec-outer" id="sobre">
  <div class="sec-inner">
    <h2 class="sec-title">🎯 O que é o Arremate Imóveis Online</h2>
    <p class="sec-sub">A plataforma de busca de imóveis da CAIXA mais completa do Brasil. Inteligente, rápida e estrategica.</p>
    <div style="background:#fff;border:1px solid var(--borda);border-radius:var(--radius);padding:22px 26px;box-shadow:var(--sombra);font-size:.92rem;color:#334155;line-height:1.8">
      <p>O <strong>Arremate Imóveis Online</strong> é a plataforma ideal para quem quer buscar imóveis da CAIXA com inteligência. Reunimos mais de 30.000 imóveis disponíveis em todos os estados do Brasil, apartamentos, casas, terrenos, lotes e imóveis comerciais com filtros avançados por cidade, tipo de imovel, modalidade dos leilões, desconto e condições de pagamento como FGTS e financiamento.</p>
      <p style="margin-top:14px">Aqui você encontra imóveis de leilão CAIXA, licitação aberta, venda online e venda direta em um único lugar, com dados atualizados diariamente direto do portal oficial da CAIXA Econômica Federal. Sem cadastro, sem burocracia só oportunidades.</p>
      <p style="margin-top:14px">📍 <strong>No Estado de São Paulo</strong>, contamos com uma <strong>imobiliária parceira credenciada junto à CAIXA</strong> (CRECI-SP 043342) para orientar e acompanhar você em cada etapa da compra, desde a busca até o fechamento do negócio.</p>
    </div>
  </div>
</section>

<!-- ===== COMO USAR ===== -->
<section class="sec-outer alt">
  <div class="sec-inner">
    <h2 class="sec-title">⚙️ Como usar o Arremate Imóveis Online</h2>
    <p class="sec-sub">Em segundos você encontra imóveis da CAIXA com desconto em qualquer estado do Brasil. Veja como é rápido e fácil:</p>
    <div class="steps-grid">
      <div class="step-card">
        <div class="step-icon">🔎</div>
        <h3>1. Busque com filtros inteligentes</h3>
        <p>Escolha o estado, a cidade, o tipo de imóvel, a modalidade e o intervalo de preço. Use os Filtros Avançados para refinar por desconto mínimo, área, FGTS, financiamento e data de encerramento.</p>
      </div>
      <div class="step-card">
        <div class="step-icon">📋</div>
        <h3>2. Compare as oportunidades</h3>
        <p>Visualize o preço de venda, o valor de avaliação, o desconto e as condições de cada imóvel lado a lado. Salve os favoritos e compartilhe com quem vai decidir com você.</p>
      </div>
      <div class="step-card">
        <div class="step-icon">💡</div>
        <h3>3. Analise antes de avançar</h3>
        <p>Veja modalidade, condições de IPTU e condomínio, e simule o financiamento. Entenda as regras de cada tipo de venda antes de dar o próximo passo.</p>
      </div>
      <div class="step-card">
        <div class="step-icon">🤝</div>
        <h3>4. Compre com suporte em SP</h3>
        <p>Para imóveis no Estado de São Paulo, nossa <strong>imobiliária parceira credenciada junto à CAIXA (CRECI-SP 043342)</strong> está pronta para orientar você até o fechamento com total segurança.</p>
      </div>
    </div>
  </div>
</section>

<!-- ===== OPORTUNIDADES ===== -->
<section class="sec-outer" id="oportunidades">
  <div class="sec-inner" style="padding-bottom:0">
    <h2 class="sec-title">🏡 Oportunidades em destaque</h2>
    <p class="sec-sub">Imóveis reais da CAIXA atualizados diariamente. Veja as melhores ofertas por menor preço e maior desconto — e encontre a sua antes de outra pessoa.</p>

    <div class="oport-tabs">
      <button class="oport-tab active" onclick="mostrarOportunidades('menor-preco', this)">💰 Menor Preço</button>
      <button class="oport-tab" onclick="mostrarOportunidades('maior-desconto', this)">🏷️ Maior Desconto</button>
    </div>

    <div class="imoveis-grid" id="oport-grid-menor-preco"></div>
    <div class="oport-more-wrap" id="oport-more-menor-preco">
      <a class="oport-tab oport-more" href="resultados.html">Ver mais imóveis</a>
    </div>

    <div class="imoveis-grid" id="oport-grid-maior-desconto" style="display:none"></div>
    <div class="oport-more-wrap" id="oport-more-maior-desconto" style="display:none">
      <a class="oport-tab oport-more" href="resultados.html">Ver mais imóveis</a>
    </div>

    <div id="oport-loading" style="text-align:center;padding:40px;color:#888;font-size:1rem;">⏳ Carregando oportunidades...</div>
    <p id="oport-atualizacao" style="text-align:center;font-size:.82rem;color:#aaa;"></p>
  </div>
</section>

<!-- ===== SIMULADOR ===== -->
<section class="sec-outer sim-bg" id="simulador">
  <div class="sec-inner">
    <h2 class="sec-title">📊 Simule o financiamento do seu imóvel da CAIXA</h2>
    <p class="sec-sub">Descubra quanto você paga por mês antes de tomar qualquer decisão. Simulação rápida e gratuita — PRICE ou SAC, com ou sem entrada.</p>
    <div class="sim-wrap">
      <div class="sim-box">
        <div class="sim-title">Preencha os dados</div>
        <div class="sim-grid">
          <div class="fgroup">
            <label>Valor do imóvel (R$)</label>
            <input type="text" inputmode="numeric" id="sim_val" placeholder="Ex.: 300.000" autocomplete="off">
          </div>
          <div class="fgroup">
            <label>Entrada (R$)</label>
            <input type="text" inputmode="numeric" id="sim_ent" placeholder="Ex.: 60.000" autocomplete="off">
          </div>
          <div class="fgroup">
            <label>Prazo (meses)</label>
            <input type="number" id="sim_prazo" min="12" step="12" value="360">
          </div>
          <div class="fgroup">
            <label>Juros estimado (% a.a.)</label>
            <input type="number" id="sim_juros" min="0" step="0.1" value="10.5">
          </div>
          <div class="fgroup">
            <label>Sistema de amortização</label>
            <select id="sim_sis">
              <option value="PRICE">PRICE (parcela fixa)</option>
              <option value="SAC">SAC (parcela decrescente)</option>
            </select>
          </div>
          <div class="fgroup">
            <label>Renda mensal bruta (opcional)</label>
            <input type="text" inputmode="numeric" id="sim_renda" placeholder="Ex.: 8.000" autocomplete="off">
          </div>
        </div>
        <button class="btn-primary" id="btnSimular" type="button" onclick="rodarSimulador()">📊 Simular</button>
        <p class="sim-note">Valores estimados. Não incluem seguros obrigatórios, taxas administrativas ou condições específicas do imóvel. Consulte a CAIXA para simulação oficial.</p>
      </div>
      <div class="sim-box">
        <div class="sim-title">Resultado estimado</div>
        <div class="sim-result" id="sim_result">Preencha os dados ao lado e clique em <strong>Simular</strong>.</div>
        <p class="sim-note">Gostou de algum imóvel em SP? Nossa imobiliária parceira credenciada (CRECI-SP 043342) pode orientar sobre financiamento, FGTS e todo o processo de compra junto à CAIXA.</p>
      </div>
    </div>
  </div>
</section>

<!-- ===== FAQ ===== -->
<section class="sec-outer" id="duvidas">
  <div class="sec-inner">
    <h2 class="sec-title">❓ Perguntas frequentes sobre imóveis da CAIXA</h2>
    <p class="sec-sub">Tudo o que você precisa saber para buscar, entender e comprar imóveis da CAIXA com segurança.</p>
    <div class="faq-grid">
      <div class="faq-card"><h3>O Arremate Imóveis Online é oficial da CAIXA?</h3><p><strong>Não.</strong> Somos uma plataforma independente e gratuita para busca de imóveis da CAIXA. <strong>Não somos vinculados e nem representamos oficialmente a Caixa Econômica Federal.</strong> Os dados são obtidos diretamente do portal oficial da CAIXA e atualizados diariamente.</p></div>
      <div class="faq-card"><h3>Posso comprar imóvel da CAIXA usando FGTS ou financiamento?</h3><p>Sim, em muitos casos. Depende do imóvel e da modalidade de venda. Use nosso filtro de condições para encontrar apenas imóveis que aceitam FGTS ou financiamento. Em SP, nossa imobiliária parceira (CRECI-SP 043342) orienta todo o processo.</p></div>
      <div class="faq-card"><h3>Qual a diferença entre leilão, licitação e venda direta?</h3><p><strong>Leilão SFI:</strong> disputa em data específica com lance mínimo. <strong>Licitação Aberta:</strong> propostas por período determinado, sem disputa em tempo real. <strong>Venda Direta Online:</strong> compra imediata pelo valor anunciado. <strong>Venda Online:</strong> proposta enviada pelo portal da CAIXA.</p></div>
      <div class="faq-card"><h3>Os imóveis da CAIXA têm dívidas de IPTU e condomínio?</h3><p>Depende do imóvel e da modalidade. Nossa plataforma exibe, quando disponível, se o IPTU e o condomínio são de responsabilidade da CAIXA ou do comprador. Sempre confirme diretamente na prefeitura, administradora e cartório antes de fechar.</p></div>
      <div class="faq-card"><h3>Vale a pena comprar imóvel ocupado da CAIXA?</h3><p>Pode ser uma excelente oportunidade com desconto ainda maior — mas exige atenção redobrada. Analise o custo de desocupação, prazo e riscos antes de avançar. Nossa plataforma indica quando um imóvel está em disputa para você já saber o cenário.</p></div>
      <div class="faq-card"><h3>Como encontrar imóveis da CAIXA perto de mim?</h3><p>Use os filtros de <strong>estado</strong> e <strong>cidade</strong> na busca. Selecione o seu estado, escolha a cidade e aplique os demais filtros de tipo, modalidade e preço. Em segundos você vê todos os imóveis disponíveis na sua região.</p></div>
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
        <a href="blog.html">Blog</a>
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

<!-- ===== MODAL FILTROS AVANÇADOS ===== -->
<div class="overlay" id="modalOverlay" onclick="if(event.target===this)fecharModal()">
  <div class="modal">
    <div class="modal-hdr">
      <div class="modal-hdr-title">⚙️ Filtros Avançados</div>
      <button class="modal-close" type="button" onclick="fecharModal()">✕</button>
    </div>
    <div class="modal-body">
      <div class="mpanel">
        <div class="mpanel-title">📍 Localização</div>
        <div class="fgroup" style="margin-bottom:10px">
          <label>Estado (UF)</label>
          <select id="adv_uf">
            <option value="">Todos os estados</option>
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
          <label>Cidade</label>
          <select id="adv_cidade">
            <option value="">Selecione uma cidade</option>
          </select>
          <button class="add-btn" type="button" onclick="addCidade()">+ Adicionar cidade</button>
          <div class="chips-wrap" id="chips_cidade"></div>
        </div>
      </div>
      <div class="mpanel">
        <div class="mpanel-title">🏢 Tipo de imóvel</div>
        <div class="chk-grid">
          <label class="chk-item"><input type="checkbox" value="Apartamento" class="tipo_chk"> Apartamento</label>
          <label class="chk-item"><input type="checkbox" value="Casa" class="tipo_chk"> Casa</label>
          <label class="chk-item"><input type="checkbox" value="Comercial" class="tipo_chk"> Comercial</label>
          <label class="chk-item"><input type="checkbox" value="Gleba" class="tipo_chk"> Gleba</label>
          <label class="chk-item"><input type="checkbox" value="Loja" class="tipo_chk"> Loja</label>
          <label class="chk-item"><input type="checkbox" value="Lote" class="tipo_chk"> Lote</label>
          <label class="chk-item"><input type="checkbox" value="Prédio" class="tipo_chk"> Prédio</label>
          <label class="chk-item"><input type="checkbox" value="Terreno" class="tipo_chk"> Terreno</label>
        </div>
      </div>
      <div class="mpanel">
        <div class="mpanel-title">📋 Modalidade</div>
        <div class="chk-grid">
          <label class="chk-item"><input type="checkbox" value="Leilão SFI - Edital Único" class="mod_chk"> Leilão SFI</label>
          <label class="chk-item"><input type="checkbox" value="Licitação Aberta" class="mod_chk"> Licitação Aberta</label>
          <label class="chk-item"><input type="checkbox" value="Venda Online" class="mod_chk"> Venda Online</label>
          <label class="chk-item"><input type="checkbox" value="Venda Direta Online" class="mod_chk"> Compra Direta</label>
        </div>
      </div>
      <div class="mpanel">
        <div class="mpanel-title">✅ Condições</div>
        <div class="chk-grid">
          <label class="chk-item"><input type="checkbox" id="c_fgts"> Aceita FGTS</label>
          <label class="chk-item"><input type="checkbox" id="c_fin"> Aceita Financiamento</label>
          <label class="chk-item"><input type="checkbox" id="c_disputa"> Em disputa</label>
        </div>
      </div>
      <div class="mpanel">
        <div class="mpanel-title">💰 Preço de venda</div>
        <div class="range-row">
          <div class="fgroup"><label>Mínimo (R$)</label><input type="text" id="preco_min" inputmode="decimal" autocomplete="off" placeholder="Ex.: 100.000"></div>
          <div class="fgroup"><label>Máximo (R$)</label><input type="text" id="preco_max" inputmode="decimal" autocomplete="off" placeholder="Ex.: 1.000.000"></div>
        </div>
      </div>
      <div class="mpanel">
        <div class="mpanel-title">🏷️ Desconto sobre avaliação</div>
        <div class="range-row">
          <div class="fgroup"><label>Mínimo (%)</label><input type="text" id="desc_min" inputmode="decimal" autocomplete="off" placeholder="Ex.: 10"></div>
          <div class="fgroup"><label>Máximo (%)</label><input type="text" id="desc_max" inputmode="decimal" autocomplete="off" placeholder="Ex.: 30"></div>
        </div>
      </div>
      <div class="mpanel">
        <div class="mpanel-title">🏘️ Despesas de Condomínio</div>
        <div class="chk-grid">
          <label class="chk-item"><input type="radio" name="r_cond" value="Limitada a 10%"> Limitada a 10%</label>
          <label class="chk-item"><input type="radio" name="r_cond" value="Arrematante paga"> Arrematante paga</label>
        </div>
      </div>
      <div class="mpanel">
        <div class="mpanel-title">🧾 Despesas de IPTU</div>
        <div class="chk-grid">
          <label class="chk-item"><input type="radio" name="r_iptu" value="Caixa paga"> Caixa paga</label>
          <label class="chk-item"><input type="radio" name="r_iptu" value="Arrematante paga"> Arrematante paga</label>
        </div>
      </div>
      <div class="mpanel">
        <div class="mpanel-title">📅 Data do Leilão</div>
        <div class="fgroup"><label>Encerra até</label><input type="date" id="adv_data"></div>
      </div>
      <div class="mpanel">
        <div class="mpanel-title">📐 Área</div>
        <div class="fgroup" style="margin-bottom:10px">
          <label>Tipo de área</label>
          <select id="area_tipo">
            <option value="">Selecione o tipo de área</option>
            <option value="Área Privativa">Área Privativa</option>
            <option value="Área Total">Área Total</option>
            <option value="Área do Terreno">Área do Terreno</option>
          </select>
        </div>
        <div class="range-row">
          <div class="fgroup"><label>Mínima (m²)</label><input type="text" id="area_min" inputmode="decimal" autocomplete="off" placeholder="Ex.: 45,5"></div>
          <div class="fgroup"><label>Máxima (m²)</label><input type="text" id="area_max" inputmode="decimal" autocomplete="off" placeholder="Ex.: 120"></div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-limpar" type="button" onclick="limparFiltros()">Limpar Filtros</button>
      <button class="btn-aplicar" type="button" onclick="aplicarFiltros()">Aplicar Filtros</button>
    </div>
  </div>
</div>

<!-- ===== RESULTADOS DA BUSCA ===== -->
<section class="sec-outer alt" id="resultados-busca" style="display:none">
  <div class="sec-inner">
    <h2 class="sec-title">Resultados da busca</h2>
    <p class="sec-sub" id="resultado-qtd" style="text-align:center;margin-top:-6px;color:#0053a6;font-weight:700;"></p>
    <div class="imoveis-grid" id="imoveis-grid-dinamico"></div>
  </div>
</section>

<script>
/* =========================================================
   BLOCO ÚNICO CONSOLIDADO — todas as funções em um só lugar,
   sem duplicatas, sem conflitos.
   ========================================================= */

/* ── 1. UTILITÁRIOS ── */

/** Normaliza string: sem acento, minúsculas, sem espaços extras */
function norm(v) {
  return String(v || '')
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .toLowerCase().trim();
}
window.norm = norm;

/** Formata número para BRL */
function brl(v) {
  return Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL', maximumFractionDigits: 0 });
}

/** Remove formatação e retorna float */
function limpaNumeroBR(valor) {
  if (!valor) return 0;
  var digitos = String(valor).replace(/[^\d]/g, '');
  return digitos ? parseFloat(digitos) : 0;
}

/** Formata inteiro com separador de milhar BR */
function formataMilharBR(numero) {
  var n = parseInt(numero, 10);
  if (isNaN(n)) return '';
  return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

/** Converte preço BR string para número float */
function toNumPreco(v) {
  if (!v) return null;
  var s = String(v).replace(/[R$\s]/g, '').replace(/\./g, '').replace(',', '.');
  var n = parseFloat(s);
  return isNaN(n) ? null : n;
}

/** Parseia preço para ordenação */
function parsePreco(p) {
  if (p == null) return Infinity;
  var n = toNumPreco(p);
  return (n == null) ? Infinity : n;
}

/** Parseia desconto para ordenação */
function parseDesc(d) {
  if (d == null) return 0;
  var s = String(d).replace('%', '').replace(',', '.').replace(/[^0-9.]/g, '');
  if (!s) return 0;
  var n = parseFloat(s);
  return isNaN(n) ? 0 : n;
}

/** Formata string BRL (ex.: "250.000,00" → "R$ 250.000,00") */
function fmtBRL(v) {
  if (v === null || v === undefined || v === '') return '';
  var n = parseFloat(String(v).replace(/[^0-9.,]/g, '').replace(',', '.'));
  if (isNaN(n)) return '';
  return n.toLocaleString('pt-BR', {style:'currency', currency:'BRL', minimumFractionDigits:2});
}

/* ── 2. CORRIGIR MODALIDADE ── */
function corrigirModalidade(m) {
  if (!m) return '';
  var t = norm(m);
  if (t.indexOf('direta') !== -1) return 'Venda Direta Online';
  if (t.indexOf('licit') !== -1 || t.indexOf('aberta') !== -1) return 'Licitação Aberta';
  if (t.indexOf('leil') !== -1) return 'Leilão SFI - Edital Único';
  if (t.indexOf('online') !== -1) return 'Venda Online';
  return m;
}
window.corrigirModalidade = corrigirModalidade;

/* ── COR POR TIPO E MODALIDADE ── */
function corTipo(tipo) {
  var t = (tipo || '').toLowerCase();
  if (t === 'apartamento') return {bg:'#6c757d', color:'#fff'};
  if (t === 'casa')        return {bg:'#7d5a3c', color:'#fff'};
  if (t === 'terreno' || t === 'lote' || t === 'gleba') return {bg:'#556b2f', color:'#fff'};
  return {bg:'#064e3b', color:'#f9fafb'};
}
function corMod(modLabel) {
  var m = (modLabel || '').toLowerCase();
  if (m.indexOf('leil') !== -1)   return {bg:'#003366', color:'#fff'};
  if (m.indexOf('licita') !== -1) return {bg:'#6f42c1', color:'#fff'};
  if (m === 'venda online')       return {bg:'#28a745', color:'#fff'};
  if (m.indexOf('direta') !== -1 || m === 'compra direta') return {bg:'#17A2B8', color:'#fff'};
  return {bg:'#f39200', color:'#3b1f00'};
}
window.corTipo = corTipo;
window.corMod = corMod;

/* ── 3. HDN / TIPO / PRECO ── */

function getHdnFromLink(link) {
  try {
    var u = new URL(link, location.origin);
    return (u.searchParams.get('hdnimovel') || '').replace(/\D/g, '');
  } catch (e) {
    var m = String(link || '').match(/hdnimovel=(\d+)/i);
    return m ? m[1] : '';
  }
}

function inferTipo(desc) {
  var d = norm(desc);
  if (d.indexOf('apartamento') !== -1) return 'Apartamento';
  if (d.indexOf('casa') !== -1) return 'Casa';
  if (d.indexOf('terreno') !== -1) return 'Terreno';
  if (d.indexOf('gleba') !== -1) return 'Gleba';
  if (d.indexOf('loja') !== -1) return 'Loja';
  if (d.indexOf('predio') !== -1) return 'Prédio';
  if (d.indexOf('sala') !== -1) return 'Sala';
  if (d.indexOf('lote') !== -1) return 'Lote';
  if (d.indexOf('comercial') !== -1) return 'Comercial';
  return 'Imóvel';
}

function toPct(v) {
  if (v === null || v === undefined || String(v).trim() === '') return null;
  var n = parseFloat(String(v).replace('%', '').replace(',', '.'));
  return isNaN(n) ? null : n;
}

/* ── 4. CONDIÇÕES DO IMÓVEL ── */

function aceitaFgts(item) {
  // Terrenos NUNCA aceitam FGTS
  var tipo = norm(item.descricao || '');
  if (tipo.indexOf('terreno') !== -1 || tipo.indexOf('lote') !== -1 || tipo.indexOf('gleba') !== -1) return false;
  if (item.fgts === 1 || item.fgts === '1') return true;
  var t = norm((item.descricao || '') + ' ' + corrigirModalidade(item.modalidade));
  return t.indexOf('fgts') !== -1;
}

function aceitaFinanciamento(item) {
  if (item.financiamento === 1 || item.financiamento === '1') return true;
  var t = norm((item.descricao || '') + ' ' + corrigirModalidade(item.modalidade));
  return t.indexOf('financi') !== -1 || t.indexOf('sfh') !== -1 || t.indexOf('sbpe') !== -1;
}

function estaEmDisputa(item) {
  if (item.disputa === 1 || item.disputa === '1') return true;
  var mod = norm(corrigirModalidade(item.modalidade) || '');
  return mod === 'venda online' || mod.indexOf('disputa') !== -1;
}

/* ── 5. TOAST CRECI ── */

function mostrarToastCreci() {
  var antigo = document.getElementById('creci-toast');
  if (antigo && antigo.parentNode) antigo.parentNode.removeChild(antigo);

  var toast = document.createElement('div');
  toast.id = 'creci-toast';
  toast.className = 'creci-toast';

  var ic = document.createElement('div');
  ic.className = 'creci-toast-icon';
  ic.textContent = '✓';

  var tx = document.createElement('div');
  tx.className = 'creci-toast-text';
  tx.textContent = 'Número do CRECI copiado para a área de transferência';

  toast.appendChild(ic);
  toast.appendChild(tx);
  document.body.appendChild(toast);

  requestAnimationFrame(function () { toast.classList.add('visible'); });

  setTimeout(function () {
    toast.classList.remove('visible');
    setTimeout(function () { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 250);
  }, 8000);
}

/* ── 5b. FAVORITOS (localStorage — mesmo padrão de favoritos.html) ── */
var FAV_LS_KEY = 'arremate_favoritos';
function lerFavsCard(){try{var r=localStorage.getItem(FAV_LS_KEY);if(!r)return[];var p=JSON.parse(r);if(!Array.isArray(p))return[];return p.map(function(i){return typeof i==='string'?{id:i,savedAt:0}:{id:String(i.id||''),savedAt:i.savedAt||0};}).filter(function(i){return i.id!=='';});}catch(e){return[];}}
function salvarFavsCard(l){try{localStorage.setItem(FAV_LS_KEY,JSON.stringify(l));}catch(e){}}
function isFavCard(id){return lerFavsCard().some(function(f){return f.id===String(id);});}
function addFavCard(id){if(isFavCard(id))return;var l=lerFavsCard();l.push({id:String(id),savedAt:Date.now()});salvarFavsCard(l);}
function removeFavCard(id){salvarFavsCard(lerFavsCard().filter(function(f){return f.id!==String(id);}));}

/* ── 6. BUILD CARD ── */

function buildCard(item) {
  var tipo = inferTipo(item.descricao || '');
  var card = document.createElement('div');
  card.className = 'imovel-card';

  /* THUMB */
  var thumb = document.createElement('div');
  thumb.className = 'imovel-thumb';

  var fallback = document.createElement('span');
  fallback.className = 'thumb-fallback';
  fallback.textContent = '🏡';
  thumb.appendChild(fallback);

  var hdn = getHdnFromLink(item.link || '');
  if (hdn) {
    var img = document.createElement('img');
    img.className = 'imovel-thumb-img';
    img.alt = tipo + ' - ' + (item.cidade || '') + ' ' + (item.uf || 'SP');
    img.loading = 'lazy';
    img.decoding = 'async';
    img.referrerPolicy = 'no-referrer';
    img.src = 'caixa-foto.php?hdnimovel=' + encodeURIComponent(hdn);
    img.addEventListener('load', function () { fallback.style.display = 'none'; });
    img.addEventListener('error', function () { img.remove(); fallback.style.display = 'flex'; });
    thumb.appendChild(img);
  }

  /* TAG TIPO */
  var tagTipo = document.createElement('span');
  tagTipo.className = 'tag-tipo';
  tagTipo.textContent = tipo;
  var cTipo = corTipo(tipo);
  tagTipo.style.background = cTipo.bg;
  tagTipo.style.color = cTipo.color;
  thumb.appendChild(tagTipo);

  /* TAG MODALIDADE */
  var modLabel = corrigirModalidade(item.modalidade);
  if (modLabel === 'Venda Direta Online') modLabel = 'Compra Direta';
  if (modLabel === 'Leilão SFI - Edital Único') modLabel = 'Leilão SFI';
  if (norm(modLabel).indexOf('licita') !== -1 && norm(modLabel).indexOf('abert') !== -1) modLabel = 'Licitação Aberta';
  if (modLabel) {
    var td = document.createElement('span');
    td.className = 'tag-desc';
    td.textContent = modLabel;
    var cMod = corMod(modLabel);
    td.style.background = cMod.bg;
    td.style.color = cMod.color;
    thumb.appendChild(td);
  }

  /* CORPO */
  var body = document.createElement('div');
  body.className = 'imovel-body';

  var local = document.createElement('div');
  local.className = 'imovel-local';
  local.textContent = (item.cidade || '').toUpperCase() + ' · ' + (item.uf || 'SP');

  var titulo = document.createElement('div');
  titulo.className = 'imovel-titulo';
  titulo.textContent = item.bairro ? item.bairro.trim() : (tipo + ' em ' + (item.uf || 'SP'));

  var endereco = document.createElement('div');
  endereco.className = 'imovel-endereco';
  if (item.endereco) endereco.textContent = String(item.endereco).trim();

  /* PREÇOS */
  var precos = document.createElement('div');
  precos.className = 'imovel-precos';

  var pct = toPct(item.desconto);

  if (item.avaliacao) {
    var av = document.createElement('span');
    av.className = 'preco-av';
    av.textContent = 'Avaliação: ' + fmtBRL(item.avaliacao);
    precos.appendChild(av);
  }

  var vendaRow = document.createElement('div');
  vendaRow.className = 'preco-venda-row';

  var pv = document.createElement('span');
  pv.className = 'preco-min';
  pv.textContent = 'Venda: ' + fmtBRL(item.preco);
  vendaRow.appendChild(pv);

  if (pct !== null && pct > 0) {
    var descBadge = document.createElement('span');
    descBadge.className = 'preco-desc-badge';
    descBadge.textContent = '-' + pct.toFixed(0) + '% OFF';
    vendaRow.appendChild(descBadge);
  }
  precos.appendChild(vendaRow);

  /* CHIPS CONDIÇÕES */
  var chips = document.createElement('div');
  chips.className = 'imovel-chips';

  var condRow = document.createElement('div');
  condRow.className = 'chips-condicoes';

  if (aceitaFgts(item)) {
    var fg = document.createElement('span');
    fg.className = 'badge-cond badge-fgts';
    fg.textContent = 'FGTS';
    condRow.appendChild(fg);
  }
  if (aceitaFinanciamento(item)) {
    var fn = document.createElement('span');
    fn.className = 'badge-cond badge-fin';
    fn.textContent = 'Financiamento';
    condRow.appendChild(fn);
  }
  if (estaEmDisputa(item)) {
    var dp = document.createElement('span');
    dp.className = 'badge-cond badge-disputa';
    dp.textContent = 'Em disputa';
    condRow.appendChild(dp);
  }
  if (condRow.childNodes.length > 0) chips.appendChild(condRow);

  /* CRECI */
  var creciRow = document.createElement('div');
  creciRow.className = 'imovel-creci';

  var creciText = document.createElement('span');
  creciText.className = 'imovel-creci-text';
  creciText.innerHTML = '<strong>CRECI: 043342</strong>';

  var creciIcon = document.createElement('span');
  creciIcon.className = 'imovel-creci-copy';
  creciIcon.innerHTML = '⧉';
  creciIcon.title = 'Copiar CRECI';

  function copyCreci() {
    var num = '043342';
    function marcarCopiado() {
      var oldIcon = creciIcon.innerHTML;
      creciIcon.innerHTML = '✓';
      creciText.classList.add('copied');
      creciIcon.classList.add('copied');
      creciIcon.title = 'Copiado!';
      mostrarToastCreci();
      setTimeout(function () {
        creciText.classList.remove('copied');
        creciIcon.classList.remove('copied');
        creciIcon.title = 'Copiar CRECI';
        creciIcon.innerHTML = oldIcon || '⧉';
      }, 1500);
    }
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(num).then(marcarCopiado).catch(marcarCopiado);
    } else {
      try {
        var ta = document.createElement('textarea');
        ta.value = num;
        ta.style.cssText = 'position:fixed;opacity:0';
        document.body.appendChild(ta);
        ta.focus(); ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
      } catch (e) {}
      marcarCopiado();
    }
  }

  creciText.addEventListener('click', copyCreci);
  creciIcon.addEventListener('click', copyCreci);
  creciRow.appendChild(creciText);
  creciRow.appendChild(creciIcon);

  /* ATRIBUTOS EXTRAS (chips) */
  var atribRow = (typeof buildChipsRow === 'function') ? buildChipsRow(item.descricao || '') : null;

  body.appendChild(local);
  body.appendChild(titulo);
  body.appendChild(endereco);
  body.appendChild(precos);
  /* Data do leilão */
  if (item.data_encerramento) {
    var dataEnc = item.data_encerramento;
    var dataFmt = dataEnc;
    if (/^\d{4}-\d{2}-\d{2}/.test(dataEnc)) {
      var dp = dataEnc.split(/[T ]/);
      var parts = dp[0].split('-');
      dataFmt = parts[2] + '/' + parts[1] + '/' + parts[0];
      if (dp[1]) dataFmt += ' às ' + dp[1].substring(0, 5);
    }
    var dataRow = document.createElement('div');
    dataRow.style.cssText = 'font-size:.75rem;color:#64748b;font-weight:600;margin:6px 0 0 0';
    dataRow.textContent = '📅 ' + dataFmt;
    precos.appendChild(dataRow);
  }
  if (atribRow) body.appendChild(atribRow);
  body.appendChild(chips);
  /* CRECI só para imóveis de SP */
  if ((item.uf || '').toUpperCase() === 'SP') {
    body.appendChild(creciRow);
  } else {
    var bnNac = document.createElement('span');
    bnNac.className = 'badge-nacional';
    bnNac.textContent = '🌎 Plataforma nacional de leilões CAIXA';
    var bnWrap = document.createElement('div');
    bnWrap.className = 'imovel-creci';
    bnWrap.appendChild(bnNac);
    body.appendChild(bnWrap);
  }

  /* FAVORITAR + COMPARTILHAR (SVG — mesmo padrão de resultados.html) */
  var hdnFinal = hdn || (item.num_imovel || '').replace(/\D/g, '');
  var svgHeart = '<svg viewBox="0 0 24 24" fill="#e11d48" stroke="#e11d48" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>';
  var svgHeartEmpty = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>';
  var svgShare = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>';

  var cardActions = document.createElement('div');
  cardActions.className = 'card-actions';

  var btnFavCard = document.createElement('button');
  btnFavCard.type = 'button';
  btnFavCard.className = 'btn-card-action btn-fav' + (isFavCard(hdnFinal) ? ' ativo' : '');
  btnFavCard.title = 'Favoritar';
  btnFavCard.innerHTML = isFavCard(hdnFinal) ? svgHeart : svgHeartEmpty;
  btnFavCard.addEventListener('click', function(e) {
    e.preventDefault(); e.stopPropagation();
    if (isFavCard(hdnFinal)) { removeFavCard(hdnFinal); btnFavCard.classList.remove('ativo'); btnFavCard.innerHTML = svgHeartEmpty; }
    else { addFavCard(hdnFinal); btnFavCard.classList.add('ativo'); btnFavCard.innerHTML = svgHeart; }
  });

  var btnShareCard = document.createElement('button');
  btnShareCard.type = 'button';
  btnShareCard.className = 'btn-card-action btn-share';
  btnShareCard.title = 'Compartilhar';
  btnShareCard.innerHTML = svgShare;
  btnShareCard.addEventListener('click', function(e) {
    e.preventDefault(); e.stopPropagation();
    var shareUrl = hdnFinal ? location.origin + location.pathname.replace(/[^/]*$/, '') + 'imovel.php?hdnimovel=' + hdnFinal : '#';
    var shareText = (tipo || 'Imóvel') + ' em ' + (item.cidade || '') + ' - Arremate Imóveis Online';
    if (navigator.share) { navigator.share({title: shareText, url: shareUrl}).catch(function(){}); }
    else {
      try { navigator.clipboard.writeText(shareUrl); } catch(ex) { var ta=document.createElement('textarea');ta.value=shareUrl;ta.style.cssText='position:fixed;opacity:0';document.body.appendChild(ta);ta.select();document.execCommand('copy');document.body.removeChild(ta); }
      var t=document.createElement('div');t.style.cssText='position:fixed;right:16px;bottom:60px;background:#1e293b;color:#fff;padding:8px 14px;border-radius:8px;font-size:.8rem;z-index:9999;transition:opacity .3s';t.textContent='Link copiado!';document.body.appendChild(t);setTimeout(function(){t.style.opacity='0';setTimeout(function(){t.remove();},400);},2000);
    }
  });

  cardActions.appendChild(btnFavCard);
  cardActions.appendChild(btnShareCard);
  thumb.appendChild(cardActions);

  /* RODAPÉ */
  var footer = document.createElement('div');
  footer.className = 'imovel-footer';

  var st = document.createElement('span');
  if (pct !== null && pct >= 25) {
    st.className = 'status-ok'; st.textContent = 'Ótima oportunidade!';
  } else if (pct !== null && pct > 0) {
    st.className = 'status-atenc'; st.textContent = 'Desconto ' + pct.toFixed(0) + '%';
  } else {
    st.className = 'status-atenc'; st.textContent = 'Sem desconto listado';
  }

  var a = document.createElement('a');
  a.className = 'imovel-link';
  /* ── LINK CORRIGIDO: aponta para imovel.php ── */
  a.href = hdnFinal ? 'imovel.php?hdnimovel=' + hdnFinal : (item.link || '#');
  a.target = '_blank';
  a.rel = 'noopener noreferrer';
  a.textContent = 'Saiba mais →';

  footer.appendChild(st);
  footer.appendChild(a);

  card.appendChild(thumb);
  card.appendChild(body);
  card.appendChild(footer);

  return card;
}
window.buildCard = buildCard;

/* ── 7. RENDER OPORTUNIDADES ── */

function renderizar(dados) {
  var imoveis = dados || [];
  var validos = imoveis.filter(function (im) {
    var p = parsePreco(im.preco);
    return p > 0 && p < Infinity;
  });

  var menorPreco = validos.slice().sort(function (a, b) {
    return parsePreco(a.preco) - parsePreco(b.preco);
  }).slice(0, 6);

  var maiorDesc = validos
    .filter(function (im) { return parseDesc(im.desconto) > 0; })
    .sort(function (a, b) { return parseDesc(b.desconto) - parseDesc(a.desconto); })
    .slice(0, 6);

  var gm = document.getElementById('oport-grid-menor-preco');
  var gd = document.getElementById('oport-grid-maior-desconto');

  if (gm) {
    gm.innerHTML = '';
    if (menorPreco.length) menorPreco.forEach(function (i) { gm.appendChild(buildCard(i)); });
    else gm.innerHTML = '<p style="color:#64748b;padding:16px">Nenhum imóvel encontrado.</p>';
  }
  if (gd) {
    gd.innerHTML = '';
    if (maiorDesc.length) maiorDesc.forEach(function (i) { gd.appendChild(buildCard(i)); });
    else gd.innerHTML = '<p style="color:#64748b;padding:16px">Nenhum imóvel com desconto encontrado.</p>';
  }

  var loading = document.getElementById('oport-loading');
  if (loading) loading.textContent = '';
}
window.renderizar = renderizar;

/* ── 8. MOSTRAR ABAS OPORTUNIDADES ── */

window.mostrarOportunidades = function (tipo, btn) {
  var ids = ['menor-preco', 'maior-desconto'];
  ids.forEach(function (id) {
    var g = document.getElementById('oport-grid-' + id);
    var m = document.getElementById('oport-more-' + id);
    if (g) g.style.display = 'none';
    if (m) m.style.display = 'none';
  });

  var alvo = document.getElementById('oport-grid-' + tipo);
  var alvoMore = document.getElementById('oport-more-' + tipo);
  if (alvo) alvo.style.display = '';
  if (alvoMore) alvoMore.style.display = '';

  document.querySelectorAll('.oport-tab').forEach(function (t) { t.classList.remove('active'); });
  if (btn) btn.classList.add('active');
};

/* ── 9. FETCH CSV COM ENCODING WIN-1252 ── */

async function fetchTextWin1252(url) {
  var resp = await fetch(url, { cache: 'no-store' });
  if (!resp.ok) throw new Error('Erro ao buscar lista da CAIXA: ' + resp.status);
  var buf = await resp.arrayBuffer();
  return new TextDecoder('windows-1252').decode(buf);
}
window.fetchTextWin1252 = fetchTextWin1252;

/* ── 10. PARSER CSV CAIXA ── */

function parseCSVCaixa(text) {
  var linhas = text.split(/\r?\n/);
  if (linhas.length < 4) return [];

  var cabecalho = linhas[2].split(';').map(function (c) { return c.trim(); });

  var items = [];
  for (var i = 3; i < linhas.length; i++) {
    var linha = linhas[i];
    if (!linha.trim()) continue;
    var cols = linha.split(';');
    var obj = {};
    cabecalho.forEach(function (col, idx) { obj[col] = (cols[idx] || '').trim(); });

    items.push({
      num_imovel: obj['Nº do imóvel'] || obj['N\u00ba do im\u00f3vel'] || '',
      uf:         obj['UF'] || '',
      cidade:     obj['Cidade'] || '',
      bairro:     obj['Bairro'] || '',
      endereco:   obj['Endereço'] || obj['Endere\u00e7o'] || '',
      preco:      obj['Preço'] || obj['Pre\u00e7o'] || '',
      avaliacao:  obj['Valor de avaliação'] || obj['Valor de avalia\u00e7\u00e3o'] || '',
      desconto:   obj['Desconto'] || '',
      descricao:  obj['Descrição'] || obj['Descri\u00e7\u00e3o'] || '',
      modalidade: obj['Modalidade de venda'] || '',
      link:       obj['Link de acesso'] || ''
    });
  }
  return items;
}
window.parseCSVCaixa = parseCSVCaixa;
window.parseCSV = parseCSVCaixa;

/* ── 11. CARREGAR OPORTUNIDADES (LAZY) ── */

(function () {
  var _loaded = false;
  var CSV_URL_SP = '/api/caixa/Lista_imoveis_SP.csv';
  window.CSV_URL_SP = CSV_URL_SP;

  function carregar() {
    if (_loaded) return;
    _loaded = true;

    var loading = document.getElementById('oport-loading');
    if (loading) loading.textContent = '⏳ Carregando oportunidades...';

    fetch('api.php?acao=buscar&ordem=preco_asc&limit=200')
      .then(function(r){ return r.json(); })
      .then(function(data){
        var items = (data.imoveis||[]).map(function(it){
          return {
            num_imovel:it.hdnimovel, uf:it.uf, cidade:it.cidade, bairro:it.bairro,
            endereco:it.endereco, descricao:it.descricao,
            modalidade:it.modalidade_raw||it.modalidade, link:it.link,
            preco:it.preco/100, avaliacao:it.avaliacao/100,
            desconto:String(it.desconto),
            fgts:it.fgts, financiamento:it.financiamento, disputa:it.disputa,
            data_encerramento:it.data_encerramento
          };
        });
        renderizar(items);
      })
      .catch(function(err){
        console.error('Falha ao carregar oportunidades:', err);
        var l = document.getElementById('oport-loading');
        if(l) l.innerHTML = '⚠️ Não foi possível carregar os dados agora.';
      });
  }

  var sec = document.getElementById('oportunidades');
  if (sec && 'IntersectionObserver' in window) {
    var obs = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (e.isIntersecting) { carregar(); obs.disconnect(); } });
    }, { rootMargin: '300px' });
    obs.observe(sec);
  } else {
    document.addEventListener('DOMContentLoaded', carregar);
  }
})();

/* ── 12. MODAL FILTROS AVANÇADOS ── */

function abrirModal() {
  var ov = document.getElementById('modalOverlay');
  if (!ov) return;
  ov.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function fecharModal() {
  var ov = document.getElementById('modalOverlay');
  if (!ov) return;
  ov.classList.remove('open');
  document.body.style.overflow = '';
}
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') fecharModal(); });

function addCidade() {
  var sel = document.getElementById('adv_cidade');
  var wrap = document.getElementById('chips_cidade');
  if (!sel || !wrap) return;
  var val = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text.trim() : '';
  if (!val || val === 'Selecione uma cidade') return;
  var dataVal = val.toUpperCase();
  var chips = wrap.querySelectorAll('.chip');
  for (var i = 0; i < chips.length; i++) {
    if (chips[i].dataset.val === dataVal) { sel.selectedIndex = 0; return; }
  }
  var chip = document.createElement('span');
  chip.className = 'chip';
  chip.dataset.val = dataVal;
  chip.innerHTML = val + ' <button type="button" onclick="this.parentNode.remove()" style="border:none;background:transparent;cursor:pointer;font-size:1rem;color:#0053a6;line-height:1;">×</button>';
  wrap.appendChild(chip);
  sel.selectedIndex = 0;
}

function carregarCidadesModal(uf) {
  var sel = document.getElementById('adv_cidade');
  if (!sel) return;
  sel.innerHTML = '<option value="">Selecione uma cidade</option>';
  if (!uf) return;
  fetch('api.php?acao=cidades&uf=' + encodeURIComponent(uf))
    .then(function(r){ return r.json(); })
    .then(function(d){
      if (!d.cidades) return;
      var map = {};
      d.cidades.forEach(function(c){ var k = c.cidade.trim(); if(k && !map[k]) map[k] = k; });
      var lista = Object.keys(map).sort(function(a,b){ return a.localeCompare(b,'pt-BR'); });
      lista.forEach(function(nome){
        var opt = document.createElement('option');
        opt.value = nome;
        opt.textContent = nome;
        sel.appendChild(opt);
      });
    })
    .catch(function(){});
}

function limparHero() {
  ['f_uf','f_cidade','f_tipo','f_modalidade','f_condicao','f_valor'].forEach(function(id){
    var el = document.getElementById(id); if(el) el.selectedIndex = 0;
  });
  carregarCidadesHero('');
}

function limparFiltros() {
  ['adv_cidade', 'preco_min', 'preco_max', 'desc_min', 'desc_max', 'area_min', 'area_max', 'adv_data'].forEach(function (id) {
    var el = document.getElementById(id);
    if (el) el.value = '';
  });
  var advUf = document.getElementById('adv_uf');
  if (advUf) { advUf.selectedIndex = 0; carregarCidadesModal(''); }
  var w = document.getElementById('chips_cidade');
  if (w) w.innerHTML = '';
  var hCond = document.getElementById('f_condicao'); if(hCond) hCond.selectedIndex = 0;
  document.querySelectorAll('.tipo_chk,.mod_chk,#c_fgts,#c_fin,#c_disputa').forEach(function (c) { c.checked = false; });
  document.querySelectorAll('input[name="r_cond"],input[name="r_iptu"]').forEach(function (r) { r.checked = false; });
  var at = document.getElementById('area_tipo');
  if (at) at.selectedIndex = 0;
}

function aplicarFiltros() {
  var qs = new URLSearchParams();

  var advUf = document.getElementById('adv_uf');
  if (advUf && advUf.value) qs.set('uf', advUf.value);

  var cidades = [];
  document.querySelectorAll('#chips_cidade .chip').forEach(function (c) {
    if (c.dataset.val) cidades.push(c.dataset.val);
  });
  if (cidades.length) qs.set('cidades', cidades.join(','));

  var pmin = document.getElementById('preco_min');
  var pmax = document.getElementById('preco_max');
  if (pmin && pmin.value.trim()) qs.set('preco_min', pmin.value.trim());
  if (pmax && pmax.value.trim()) qs.set('preco_max', pmax.value.trim());

  var dmin = document.getElementById('desc_min');
  var dmax = document.getElementById('desc_max');
  if (dmin && dmin.value.trim()) qs.set('desc_min', dmin.value.trim());
  if (dmax && dmax.value.trim()) qs.set('desc_max', dmax.value.trim());

  var tipos = [];
  document.querySelectorAll('.tipo_chk:checked').forEach(function (t) { tipos.push(t.value); });
  if (tipos.length) qs.set('tipos', tipos.join(','));

  var mods = [];
  document.querySelectorAll('.mod_chk:checked').forEach(function (m) { mods.push(m.value); });
  if (mods.length) qs.set('modalidades', mods.join(','));

  var fgts = document.getElementById('c_fgts');
  var fin = document.getElementById('c_fin');
  var disp = document.getElementById('c_disputa');
  if (fgts && fgts.checked) qs.set('fgts', '1');
  if (fin && fin.checked) qs.set('fin', '1');
  if (disp && disp.checked) qs.set('disputa', '1');

  var rc = document.querySelector('input[name="r_cond"]:checked');
  var ri = document.querySelector('input[name="r_iptu"]:checked');
  if (rc) qs.set('r_cond', rc.value);
  if (ri) qs.set('r_iptu', ri.value);

  var dt = document.getElementById('adv_data');
  if (dt && dt.value) qs.set('data_ate', dt.value);

  var at = document.getElementById('area_tipo');
  var amin = document.getElementById('area_min');
  var amax = document.getElementById('area_max');
  if (at && at.value) qs.set('area_tipo', at.value);
  if (amin && amin.value.trim()) qs.set('area_min', amin.value.trim());
  if (amax && amax.value.trim()) qs.set('area_max', amax.value.trim());

  fecharModal();
  window.location.href = 'resultados.html?' + qs.toString();
}

/* ── 13. BUSCAR (barra principal do hero) ── */

function buscar() {
  var uf = (document.getElementById('f_uf') ? document.getElementById('f_uf').value : '').trim();
  var cidade = (document.getElementById('f_cidade') ? document.getElementById('f_cidade').value : '').trim();
  var tipo = (document.getElementById('f_tipo') ? document.getElementById('f_tipo').value : '').trim();
  var modalidade = (document.getElementById('f_modalidade') ? document.getElementById('f_modalidade').value : '').trim();
  var valorMax = (document.getElementById('f_valor') ? document.getElementById('f_valor').value : '').trim();
  var codigo = (document.getElementById('f_codigo') ? document.getElementById('f_codigo').value : '').trim();

  /* Cidades: hero select + modal chips (unificados em cidades[]) */
  var cidades = [];
  if (cidade) cidades.push(cidade);
  document.querySelectorAll('#chips_cidade .chip').forEach(function (c) {
    if (c.dataset && c.dataset.val && cidades.indexOf(c.dataset.val) === -1) cidades.push(c.dataset.val);
  });

  /* Tipos: hero select + modal checkboxes (unificados em tipos[]) */
  var tipos = [];
  if (tipo) tipos.push(tipo);
  document.querySelectorAll('.tipo_chk:checked').forEach(function (t) {
    if (tipos.indexOf(t.value) === -1) tipos.push(t.value);
  });

  /* Modalidades: hero select + modal checkboxes (unificados em modalidades[]) */
  var modalidades = [];
  if (modalidade) modalidades.push(modalidade);
  document.querySelectorAll('.mod_chk:checked').forEach(function (m) {
    if (modalidades.indexOf(m.value) === -1) modalidades.push(m.value);
  });

  var precoMin = document.getElementById('preco_min') ? document.getElementById('preco_min').value : '';
  var precoMax = document.getElementById('preco_max') ? document.getElementById('preco_max').value : '';
  var descMin  = document.getElementById('desc_min')  ? document.getElementById('desc_min').value  : '';
  var descMax  = document.getElementById('desc_max')  ? document.getElementById('desc_max').value  : '';
  var areaMin  = document.getElementById('area_min')  ? document.getElementById('area_min').value  : '';
  var areaMax  = document.getElementById('area_max')  ? document.getElementById('area_max').value  : '';
  var areaTipo = document.getElementById('area_tipo') ? document.getElementById('area_tipo').value : '';
  var dataAte  = document.getElementById('adv_data')  ? document.getElementById('adv_data').value  : '';

  var heroCond = document.getElementById('f_condicao') ? document.getElementById('f_condicao').value : '';
  var fgts    = (heroCond === 'fgts')    || (document.getElementById('c_fgts')    && document.getElementById('c_fgts').checked);
  var fin     = (heroCond === 'fin')     || (document.getElementById('c_fin')     && document.getElementById('c_fin').checked);
  var disputa = (heroCond === 'disputa') || (document.getElementById('c_disputa') && document.getElementById('c_disputa').checked);

  var qs = new URLSearchParams();
  if (codigo)           qs.set('codigo', codigo);
  if (uf)               qs.set('uf', uf);
  if (cidades.length)   qs.set('cidades', cidades.join(','));
  if (tipos.length)     qs.set('tipos', tipos.join(','));
  if (modalidades.length) qs.set('modalidades', modalidades.join(','));
  if (valorMax)         qs.set('preco_max_rapido', valorMax);
  if (precoMin)         qs.set('preco_min', precoMin);
  if (precoMax)         qs.set('preco_max', precoMax);
  if (descMin)          qs.set('desc_min', descMin);
  if (descMax)          qs.set('desc_max', descMax);
  if (areaMin)          qs.set('area_min', areaMin);
  if (areaMax)          qs.set('area_max', areaMax);
  if (areaTipo)         qs.set('area_tipo', areaTipo);
  if (dataAte)          qs.set('data_ate', dataAte);
  if (fgts)             qs.set('fgts', '1');
  if (fin)              qs.set('fin', '1');
  if (disputa)          qs.set('disputa', '1');
  var rCond = document.querySelector('input[name="r_cond"]:checked');
  var rIptu = document.querySelector('input[name="r_iptu"]:checked');
  if (rCond) qs.set('r_cond', rCond.value);
  if (rIptu) qs.set('r_iptu', rIptu.value);

  window.location.href = 'resultados.html?' + qs.toString();
}

/* ── 14. SIMULADOR ── */

function aplicarMascarasSimulador() {
  ['sim_val', 'sim_ent', 'sim_renda'].forEach(function (id) {
    var el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input', function () {
      var n = limpaNumeroBR(this.value);
      this.value = n ? formataMilharBR(n) : '';
    });
  });
}
document.addEventListener('DOMContentLoaded', aplicarMascarasSimulador);

/* ── CARREGAR CIDADES NO HERO CONFORME ESTADO ── */
function carregarCidadesHero(uf) {
  var sel = document.getElementById('f_cidade');
  if (!sel) return;
  sel.innerHTML = '<option value="">Todas as cidades</option>';
  if (!uf) return;
  fetch('api.php?acao=cidades&uf=' + encodeURIComponent(uf))
    .then(function(r){ return r.json(); })
    .then(function(d){
      if (!d.cidades) return;
      var map = {};
      d.cidades.forEach(function(c){ var k = c.cidade.trim(); if(k && !map[k]) map[k] = k; });
      var lista = Object.keys(map).sort(function(a,b){ return a.localeCompare(b,'pt-BR'); });
      lista.forEach(function(nome){
        var opt = document.createElement('option');
        opt.value = nome;
        opt.textContent = nome;
        sel.appendChild(opt);
      });
    })
    .catch(function(){});
}

document.addEventListener('DOMContentLoaded', function(){
  var ufSel = document.getElementById('f_uf');
  if (ufSel) {
    ufSel.addEventListener('change', function(){
      carregarCidadesHero(this.value);
    });
  }
  var advUfSel = document.getElementById('adv_uf');
  if (advUfSel) {
    advUfSel.addEventListener('change', function(){
      carregarCidadesModal(this.value);
    });
  }
});

function rodarSimulador() {
  var val   = limpaNumeroBR(document.getElementById('sim_val').value)   || 0;
  var ent   = limpaNumeroBR(document.getElementById('sim_ent').value)   || 0;
  var prazo = parseInt(document.getElementById('sim_prazo').value)       || 0;
  var jaa   = parseFloat(document.getElementById('sim_juros').value)     || 0;
  var sis   = document.getElementById('sim_sis').value;
  var renda = limpaNumeroBR(document.getElementById('sim_renda').value) || 0;
  var el    = document.getElementById('sim_result');
  if (!el) return;

  if (val <= 0 || prazo <= 0) {
    el.innerHTML = '⚠️ Informe um <strong>valor do imóvel</strong> e um <strong>prazo</strong> válidos.';
    return;
  }

  var fin = Math.max(0, val - ent);
  var im  = (jaa / 100) / 12;
  var out = '';

  out += 'Valor do imóvel: <strong>' + brl(val) + '</strong><br>';
  out += 'Entrada: <strong>' + brl(ent) + '</strong><br>';
  out += 'Valor financiado: <strong>' + brl(fin) + '</strong><br>';
  out += 'Prazo: <strong>' + prazo + ' meses</strong><br>';
  out += 'Juros: <strong>' + jaa.toFixed(2) + '% a.a.</strong><br><br>';

  if (fin === 0) {
    out += '✅ Pagamento à vista sem parcelas de financiamento.';
  } else if (sis === 'PRICE') {
    var parcela = im === 0 ? fin / prazo : fin * im / (1 - Math.pow(1 + im, -prazo));
    out += 'Sistema: <strong>PRICE</strong><br>';
    out += 'Parcela mensal estimada: <strong>' + brl(parcela) + '</strong>';
    if (renda > 0)
      out += '<br>Comprometimento da renda: <strong>' + ((parcela / renda) * 100).toFixed(1) + '%</strong>';
    else
      out += '<br>Renda mínima sugerida (30%): <strong>' + brl(parcela / 0.3) + '</strong>';
  } else {
    var amort = fin / prazo;
    var p1 = amort + fin * im;
    var pN = amort + amort * im;
    out += 'Sistema: <strong>SAC</strong><br>';
    out += '1ª parcela estimada: <strong>' + brl(p1) + '</strong><br>';
    out += 'Última parcela estimada: <strong>' + brl(pN) + '</strong>';
    if (renda > 0)
      out += '<br>Comprometimento (1ª parcela): <strong>' + ((p1 / renda) * 100).toFixed(1) + '%</strong>';
    else
      out += '<br>Renda mínima sugerida (30%): <strong>' + brl(p1 / 0.3) + '</strong>';
  }

  out += '<br><br><em style="font-size:.74rem;color:#64748b">Estimativa sem seguros e taxas. Consulte condições oficiais na CAIXA.</em>';
  el.innerHTML = out;
}

/* ── 15. BUSCA POR FILTROS DA URL (página resultados inline) ── */

(function () {
  function getFiltros() {
    var p = new URLSearchParams(location.search);
    return {
      codigo:       (p.get('codigo')     || '').trim().toLowerCase(),
      uf:           (p.get('uf')         || '').trim().toUpperCase(),
      cidade:       norm(p.get('cidade')),
      cidades:      (p.get('cidades')    || '').split(',').map(function(x){return norm(x);}).filter(Boolean),
      tipo:         norm(p.get('tipo')),
      tipos:        (p.get('tipos')      || '').split(',').map(function(x){return norm(x);}).filter(Boolean),
      modalidade:   norm(p.get('modalidade')),
      modalidades:  (p.get('modalidades')|| '').split(',').map(function(x){return norm(x);}).filter(Boolean),
      precoMaxRap:  parseFloat(p.get('preco_max_rapido') || '') || null,
      precoMin:     parseFloat(p.get('preco_min')        || '') || null,
      precoMax:     parseFloat(p.get('preco_max')        || '') || null,
      descMin:      parseFloat(p.get('desc_min')         || '') || null,
      descMax:      parseFloat(p.get('desc_max')         || '') || null,
      areaMin:      parseFloat(p.get('area_min')         || '') || null,
      areaMax:      parseFloat(p.get('area_max')         || '') || null,
      fgts:         p.get('fgts')    === '1',
      fin:          p.get('fin')     === '1',
      disputa:      p.get('disputa') === '1',
      isBusca:      Array.from(p.keys()).length > 0
    };
  }

  function filtrar(lista, f) {
    return lista.filter(function (item) {
      if (f.codigo) {
        var num = (item.num_imovel || '').toString().toLowerCase();
        var lnk = (item.link || '').toLowerCase();
        if (num.indexOf(f.codigo) === -1 && lnk.indexOf(f.codigo) === -1) return false;
      }
      if (f.cidade && norm(item.cidade).indexOf(norm(f.cidade)) === -1) return false;
      if (f.cidades.length) {
        var cn = norm(item.cidade);
        if (!f.cidades.some(function (x) { return cn.indexOf(x) !== -1; })) return false;
      }
      if (f.tipo && norm(item.descricao).indexOf(norm(f.tipo)) === -1) return false;
      if (f.tipos.length) {
        var td = norm(item.descricao);
        if (!f.tipos.some(function (x) { return td.indexOf(x) !== -1; })) return false;
      }
      if (f.modalidade && norm(corrigirModalidade(item.modalidade)).indexOf(f.modalidade) === -1) return false;
      if (f.modalidades.length) {
        var m2 = norm(corrigirModalidade(item.modalidade));
        if (!f.modalidades.some(function (x) { return m2.indexOf(norm(x)) !== -1; })) return false;
      }
      var pr = toNumPreco(item.preco);
      if (f.precoMaxRap && pr && pr > f.precoMaxRap) return false;
      if (f.precoMin && pr && pr < f.precoMin) return false;
      if (f.precoMax && pr && pr > f.precoMax) return false;
      if ((f.descMin || f.descMax) && item.desconto) {
        var d = parseFloat(String(item.desconto).replace('%', '').replace(',', '.'));
        if (!isNaN(d)) {
          if (f.descMin && d < f.descMin) return false;
          if (f.descMax && d > f.descMax) return false;
        }
      }
      if (f.fgts    && !aceitaFgts(item))          return false;
      if (f.fin     && !aceitaFinanciamento(item))  return false;
      if (f.disputa && !estaEmDisputa(item))         return false;
      return true;
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var filtros = getFiltros();
    if (!filtros.isBusca) return;

    var sec  = document.getElementById('resultados-busca');
    var grid = document.getElementById('imoveis-grid-dinamico');
    var qtd  = document.getElementById('resultado-qtd');

    if (!grid) return;
    if (sec) sec.style.display = '';

    grid.innerHTML = '<p style="text-align:center;color:#64748b;padding:32px">🔎 Buscando imóveis...</p>';

    setTimeout(function () {
      if (sec) sec.scrollIntoView({ behavior: 'smooth' });
    }, 300);

    var apiUrl = 'api.php?acao=buscar&limit=200';
    if (filtros.uf)     apiUrl += '&uf='      + encodeURIComponent(filtros.uf);
    if (filtros.cidade) apiUrl += '&cidade='  + encodeURIComponent(filtros.cidade);
    if (filtros.cidades.length) apiUrl += '&cidades=' + encodeURIComponent(filtros.cidades.join(','));
    if (filtros.modalidade) apiUrl += '&modalidade=' + encodeURIComponent(filtros.modalidade);
    if (filtros.precoMaxRap) apiUrl += '&preco_max_rapido=' + filtros.precoMaxRap;
    if (filtros.precoMin)    apiUrl += '&preco_min=' + filtros.precoMin;
    if (filtros.precoMax)    apiUrl += '&preco_max=' + filtros.precoMax;
    if (filtros.fgts)    apiUrl += '&fgts=1';
    if (filtros.fin)     apiUrl += '&fin=1';
    if (filtros.disputa) apiUrl += '&disputa=1';

    fetch(apiUrl)
      .then(function(r){ return r.json(); })
      .then(function(data){
        var items = (data.imoveis||[]).map(function(it){
          return {num_imovel:it.hdnimovel,uf:it.uf,cidade:it.cidade,bairro:it.bairro,
            endereco:it.endereco,descricao:it.descricao,modalidade:it.modalidade_raw||it.modalidade,
            link:it.link,preco:it.preco/100,avaliacao:it.avaliacao/100,desconto:String(it.desconto),
            fgts:it.fgts,financiamento:it.financiamento,disputa:it.disputa,data_encerramento:it.data_encerramento};
        });
        items = filtrar(items, filtros);
        grid.innerHTML = '';
        if(qtd) qtd.textContent = items.length+' imóvel(is) encontrado(s)';
        if(items.length === 0){
          grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:#64748b;padding:32px">Nenhum imóvel encontrado.</p>';
          return;
        }
        items.forEach(function(item){ grid.appendChild(buildCard(item)); });
      })
      .catch(function(e){
        console.error('Erro API:', e);
        grid.innerHTML = '<p style="text-align:center;color:#b91c1c;padding:32px">Erro ao carregar imóveis.</p>';
      });
  });
})();
</script>

<!-- Scripts externos do projeto (mantidos) -->
<script src="imovel-chips.js"></script>
<script src="caixa-hdnimovel.js"></script>
<script src="home-preco-mask.js"></script>

<script>
function toggleFavorito(hdnimovel, element) {
    let favoritos = JSON.parse(localStorage.getItem("arremate_favs")) || [];
    const index = favoritos.indexOf(hdnimovel);
    if (index === -1) {
        favoritos.push(hdnimovel);
        element.classList.add("is-fav");
        element.innerHTML = "❤️";
    } else {
        favoritos.splice(index, 1);
        element.classList.remove("is-fav");
        element.innerHTML = "🤍";
    }
    localStorage.setItem("arremate_favs", JSON.stringify(favoritos));
}
</script>
</body>
</html>
