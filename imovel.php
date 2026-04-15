<?php
/**
 * imovel.php — Arremate Imóveis Online
 * Server-rendered via SQLite/PDO.
 * CRECI 043342 exibido APENAS para imóveis com UF = SP.
 * CSS e estrutura HTML de Cesar preservados na íntegra.
 */

define('DB_PATH',   __DIR__ . '/../dados/imoveis.db');
define('WA_NUMBER', '5512997651740');
define('CRECI_NUM', '043342');

function fmtBRL_php(int $c): string {
    return $c > 0 ? 'R$ ' . number_format($c / 100, 2, ',', '.') : 'R$ 0,00';
}
function esc(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function jsStr(string $s): string {
    return str_replace(["'", "\\", "\n", "\r"], ["\\'", "\\\\", "\\n", ""], $s);
}
/** Garante UTF-8 — dados do CSV/banco podem vir em ISO-8859-1 */
function toUtf8(string $s): string {
    return mb_check_encoding($s, 'UTF-8') ? $s : mb_convert_encoding($s, 'UTF-8', 'ISO-8859-1');
}

/* Leitura e sanitização do ID */
$rawId     = trim($_GET['hdnimovel'] ?? $_GET['id'] ?? '');
$hdnimovel = preg_replace('/\D/', '', $rawId);

$found  = false;
$imovel = [];

if ($hdnimovel === '') {
    $erroMsg = 'ID de imóvel não informado.';
} elseif (!file_exists(DB_PATH)) {
    $erroMsg = 'Banco de dados indisponível.';
} else {
    try {
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->exec('PRAGMA query_only=1');
        $stmt = $db->prepare('SELECT * FROM imoveis WHERE hdnimovel = :h OR numero = :h LIMIT 1');
        $stmt->execute([':h' => $hdnimovel]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) { $found = true; $imovel = $row; }
        else { $erroMsg = 'Imóvel não encontrado ou já removido.'; }
    } catch (Exception $e) {
        $erroMsg = 'Erro interno ao consultar o banco.';
    }
}

if ($found) {
    $hdn        = (string)($imovel['hdnimovel'] ?: $imovel['numero'] ?: $hdnimovel);
    $numero     = (string)($imovel['numero']    ?: $hdn);
    $uf         = strtoupper(trim($imovel['uf']       ?? 'SP'));
    $cidade     = toUtf8(trim($imovel['cidade']    ?? ''));
    $bairro     = toUtf8(trim($imovel['bairro']    ?? ''));
    $endereco   = toUtf8(trim($imovel['endereco']  ?? ''));
    $descricao  = toUtf8(trim($imovel['descricao'] ?? ''));
    $modalidade = toUtf8(trim($imovel['modalidade']     ?? ''));
    $mod_raw    = toUtf8(trim($imovel['modalidade_raw'] ?? $modalidade));
    $link       = trim($imovel['link']      ?? '#');
    $precoCent  = (int)($imovel['preco']         ?? 0);
    $avalCent   = (int)($imovel['avaliacao']     ?? 0);
    $desconto   = (float)($imovel['desconto']    ?? 0);
    $fgts       = (int)($imovel['fgts']          ?? 0);
    $fin        = (int)($imovel['financiamento'] ?? 0);
    $disputa    = (int)($imovel['disputa']       ?? 0);
    $condominio = (string)($imovel['condominio'] ?? '');
    $iptu       = (string)($imovel['iptu']       ?? '');
    $data_enc   = (string)($imovel['data_encerramento'] ?? '');
    $data_leil1 = (string)($imovel['data_leilao_1']    ?? '');
    $foto_url   = (string)($imovel['foto_url'] ?? '');

    $precoBrl   = fmtBRL_php($precoCent);
    $avalBrl    = fmtBRL_php($avalCent);
    $economia   = $avalCent - $precoCent;
    $econBrl    = $economia > 0 ? fmtBRL_php($economia) : '';
    $isSP       = ($uf === 'SP');
    $isCompDireta = (bool)preg_match('/direta/i', $mod_raw);

    /* Tipo para SEO e WA */
    $descLow = mb_strtolower($descricao);
    $tipos = ['apartamento'=>'Apartamento','casa'=>'Casa','terreno'=>'Terreno',
              'gleba'=>'Gleba','loja'=>'Loja','predio'=>'Prédio','sala'=>'Sala',
              'lote'=>'Lote','comercial'=>'Comercial'];
    $tipoTxt = 'Imóvel';
    foreach ($tipos as $k => $v) { if (strpos($descLow, $k) !== false) { $tipoTxt = $v; break; } }

    /* URL canônica da página — usada no OG e na mensagem WhatsApp */
    $proto   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $pageUrl = $proto . '://' . ($_SERVER['HTTP_HOST'] ?? 'arremate.com.br') . '/imovel.php?hdnimovel=' . $hdn;

    /* WhatsApp dinâmico por imóvel — URL no texto gera preview automático */
    $waMsgRaw = "Olá! Tenho interesse neste imóvel da CAIXA:\n"
              . "{$tipoTxt} em {$cidade}/{$uf}\n"
              . "Endereço: {$endereco}\n"
              . "Preço: {$precoBrl}\n\n"
              . $pageUrl;
    $waUrl = 'https://wa.me/' . WA_NUMBER . '?text=' . rawurlencode($waMsgRaw);

    /* Edital: URL real vem do scraper; fallback = página do imóvel na CAIXA */
    $linkEdital    = $link ?: '#';
    $linkEditalAlt = $link ?: '#';
    $linkMatricula = $hdn ? 'https://venda-imoveis.caixa.gov.br/editais/matricula/' . $uf . '/' . $hdn . '.pdf' : $link;
    $fotoUrl       = $foto_url ?: 'https://venda-imoveis.caixa.gov.br/fotos/F' . str_pad($hdn, 14, '0', STR_PAD_LEFT) . '21.jpg';

    $pageTitle = $tipoTxt . ' em ' . $cidade . ' - CAIXA | Arremate Imóveis Online';
    $pageDesc  = $tipoTxt . ' CAIXA em ' . $cidade . '/' . $uf . '. Preço: ' . $precoBrl . '. '
               . ($desconto > 0 ? round($desconto) . '% de desconto. ' : '')
               . ($isSP ? 'Assessoramento especializado CRECI-SP ' . CRECI_NUM . '.'
                        : 'Plataforma nacional de leilões CAIXA em todo o Brasil.');
} else {
    $hdn=$hdnimovel; $numero=''; $uf='SP'; $cidade=''; $bairro=''; $endereco='';
    $descricao=''; $modalidade=''; $mod_raw=''; $link='#';
    $precoCent=0; $avalCent=0; $desconto=0; $tipoTxt='Imóvel';
    $fgts=0; $fin=0; $disputa=0; $condominio=''; $iptu=''; $data_enc=''; $data_leil1='';
    $precoBrl=''; $avalBrl=''; $econBrl='';
    $isSP=true; $isCompDireta=false; $fotoUrl=''; $foto_url='';
    $waUrl='https://wa.me/'.WA_NUMBER;
    $linkEdital='#'; $linkEditalAlt='#'; $linkMatricula='#';
    $pageTitle='Imóvel não encontrado | Arremate Imóveis Online';
    $pageDesc='Imóvel não encontrado. Busque outros imóveis CAIXA disponíveis.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="color-scheme" content="light">
  <title><?= esc($pageTitle) ?></title>
  <meta name="description" content="<?= esc($pageDesc) ?>">
<?php if ($found): ?>
  <meta property="og:title" content="<?= esc($pageTitle) ?>">
  <meta property="og:description" content="<?= esc($pageDesc) ?>">
  <meta property="og:image" content="<?= esc($fotoUrl) ?>">
  <meta property="og:image:width" content="800">
  <meta property="og:image:height" content="600">
  <meta property="og:url" content="<?= esc($pageUrl) ?>">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Arremate Imóveis Online">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:image" content="<?= esc($fotoUrl) ?>">
<?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--azul:#0053a6;--azul-esc:#00366f;--laranja:#f39200;--azul-bg:#f1f7ff;--azul-card:#e8f2ff;--borda:#cfe2ff;--texto:#1e293b;--muted:#64748b;--radius:14px;--sombra:0 4px 18px rgba(0,83,166,.10);--hdrH:84px;}
    @media(max-width:900px){:root{--hdrH:76px}}
    html{scroll-behavior:smooth;color-scheme:light;overflow-x:hidden}
    html{overflow-x:hidden}
    body{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--azul-bg);color:var(--texto);line-height:1.55;font-size:15px;overflow-x:hidden}
    a{color:inherit;text-decoration:none}
    .menu-chk{display:none!important;position:absolute;left:-9999px}
    .site-header{position:sticky;top:0;z-index:200;background:#01468d;color:#fff;box-shadow:0 3px 12px rgba(0,0,0,.25)}
    .hdr{max-width:1400px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:0 20px;min-height:84px}
    .logo{display:flex;align-items:center;gap:12px;flex-shrink:0}
    .logo-icon{width:70px;height:70px;flex-shrink:0;border-radius:14px;overflow:hidden}
    .logo-icon-img{width:70px;height:70px;display:block;object-fit:contain;border-radius:14px}
    .logo-txt{display:flex;flex-direction:column;min-width:0}
    .logo-aio{font-size:1.38rem;font-weight:900;color:#fff;line-height:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-shadow:0 2px 6px rgba(0,0,0,.25)}
    .logo-sub{font-size:.72rem;color:rgba(255,255,255,.88);line-height:1.2;margin-top:4px;text-align:justify;text-align-last:justify}
    .logo-sub-full{display:block;width:100%}.logo-sub-mobile{display:none}
    .nav-links{display:flex;align-items:center;gap:10px;flex-wrap:nowrap;font-size:.82rem;margin-left:auto;flex-shrink:0}
    .nav-links a{color:#fff;opacity:.9;font-weight:600;white-space:nowrap;transition:opacity .2s;text-decoration:none;padding:4px 2px}
    .nav-links a:hover{opacity:1;text-decoration:underline;text-underline-offset:3px}
    .btn-nav-cta{background:var(--laranja)!important;color:#3b1f00!important;padding:7px 14px!important;border-radius:999px!important;font-weight:900!important;opacity:1!important;box-shadow:0 3px 8px rgba(0,0,0,.22)}
    .nav-links a.active{opacity:1;background:rgba(255,255,255,.18);border-radius:8px;padding:4px 10px;font-weight:900}
    .nav-mobile a.active{background:#c0d8f8;color:var(--azul-esc);font-weight:900}
    .hamburger{display:none;align-items:center;justify-content:center;width:44px;height:44px;flex-shrink:0;background:rgba(255,255,255,.12);border:1.5px solid rgba(255,255,255,.3);border-radius:10px;cursor:pointer;color:#fff;transition:background .15s}
    .hamburger svg{width:20px;height:20px;display:block}
    .hamburger:hover,.hamburger:focus-visible{background:rgba(255,255,255,.22);outline:none}
    .nav-mobile{display:none;flex-direction:column;width:100%;background:#dceeff;border-top:2px solid #a8cfee}
    .menu-chk:checked ~ .nav-mobile{display:flex!important}
    .nav-mobile a{display:block;padding:14px 20px;font-size:.97rem;font-weight:700;color:#0b1a33;background:#e8f3ff;border-bottom:1px solid #b8d8f5;text-decoration:none}
    .nav-mobile a:hover{background:#cde5ff}
    .nav-mob-cta{background:#e97500!important;color:#fff!important;font-weight:900!important;border-bottom:none!important}
    .nav-mob-close{display:flex;align-items:center;padding:12px 20px;font-size:.9rem;font-weight:700;color:#01468d;background:#c8e0f8;border-bottom:2px solid #a8cfee;cursor:pointer}
    @media(max-width:900px){.hdr{padding:0 8px 0 6px;min-height:76px;gap:6px}.nav-links{display:none!important}.hamburger{display:flex}.logo-icon{width:44px;height:44px}.logo-icon-img{width:44px;height:44px}.logo-aio{font-size:1.2rem}.logo{flex-shrink:1;min-width:0;gap:5px}.logo-txt{min-width:0;overflow:hidden;max-width:calc(100vw - 112px)}.logo-sub-full{display:none}.logo-sub-mobile{display:block}.logo-sub{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:.71rem;text-align:left;text-align-last:left}}
    .page-wrap{max-width:1100px;margin:0 auto;padding:20px 16px 24px}
    .breadcrumb{display:flex;align-items:center;gap:8px;font-size:.82rem;color:var(--muted);margin-bottom:16px;flex-wrap:wrap}
    .breadcrumb a{color:var(--azul);font-weight:700}.breadcrumb a:hover{text-decoration:underline}
    .breadcrumb-sep{color:#cbd5e1}
    .det-grid{display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start}
    @media(max-width:900px){.det-grid{grid-template-columns:1fr}}
    .panel{background:#fff;border:1px solid var(--borda);border-radius:var(--radius);box-shadow:var(--sombra);overflow:hidden}
    .panel-body{padding:18px 20px}
    .foto-wrap{position:relative;height:320px;background:linear-gradient(135deg,#dbeafe,#eff6ff);overflow:hidden}
    @media(max-width:600px){.foto-wrap{height:220px}}
    .foto-principal{width:100%;height:100%;object-fit:cover;display:block}
    .foto-fallback{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:4rem;color:#cbd5e1}
    .gallery-toggle{position:absolute;bottom:12px;left:50%;transform:translateX(-50%);z-index:20;display:flex;gap:0;background:rgba(0,0,0,.55);backdrop-filter:blur(8px);border-radius:999px;padding:3px;box-shadow:0 4px 14px rgba(0,0,0,.35)}
    .gallery-btn{border:none;background:transparent;color:rgba(255,255,255,.85);font-size:.78rem;font-weight:800;padding:7px 18px;border-radius:999px;cursor:pointer;transition:all .2s;font-family:inherit;white-space:nowrap}
    .gallery-btn.active{background:#fff;color:#0f172a;box-shadow:0 2px 8px rgba(0,0,0,.2)}
    .gallery-btn:hover:not(.active){color:#fff;background:rgba(255,255,255,.15)}
    .gallery-mapa{position:absolute;inset:0;z-index:5;background:#e5e7eb}
    .gallery-mapa iframe{width:100%;height:100%;border:none}
    .tag-tipo-det{position:absolute;top:12px;left:12px;background:#064e3b;color:#f9fafb;font-size:.78rem;font-weight:900;padding:5px 14px;border-radius:999px;z-index:10}
    .tag-mod-det{position:absolute;top:12px;right:12px;background:var(--laranja);color:#3b1f00;font-size:.78rem;font-weight:900;padding:5px 14px;border-radius:999px;z-index:10;box-shadow:0 3px 8px rgba(0,0,0,.18)}
    .det-cidade-row{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:2px}
    .det-cidade{font-size:1rem;font-weight:900;color:#111827}
    .det-actions-mobile{display:flex;align-items:center;gap:6px;flex-shrink:0}
    .btn-det-action{background:none;border:1.5px solid #e2e8f0;border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.1rem;transition:background .15s,border-color .15s;flex-shrink:0;padding:0}
    .btn-det-action:hover{background:#f1f5f9;border-color:#cbd5e1}
    .btn-det-action.is-fav{border-color:#e11d48;background:#fff1f2}
    .det-titulo{font-size:.80rem;font-weight:600;color:var(--muted);margin-bottom:1px}
    .det-endereco{font-size:.78rem;color:#6b7280;margin-bottom:6px}
    .preco-destaque{background:linear-gradient(135deg,#0f172a,#1d4ed8);border-radius:12px;padding:14px 18px;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px}
    .preco-venda-det{color:#f9fafb;font-size:1.5rem;font-weight:900}
    .preco-av-det{color:#94a3b8;font-size:.82rem;text-decoration:line-through}
    .desconto-badge-det{background:var(--laranja);color:#3b1f00;font-size:1rem;font-weight:900;padding:6px 16px;border-radius:999px;box-shadow:0 3px 8px rgba(0,0,0,.2)}
    .atributos-row{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px}
    .attr-chip{display:inline-flex;align-items:center;gap:5px;background:#f0f7ff;border:1px solid #dbeafe;border-radius:999px;padding:5px 12px;font-size:.8rem;font-weight:700;color:#1e3a8a}
    .cond-row{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px}
    .badge-cond{padding:4px 12px;border-radius:999px;font-weight:800;font-size:.76rem}
    .badge-fgts{background:#dcfce7;color:#166534}.badge-fin{background:#dbeafe;color:#1d4ed8}.badge-disputa{background:#fee2e2;color:#b91c1c}.badge-mod{background:#fef3c7;color:#92400e}
    .det-descricao{font-size:.88rem;color:#374151;line-height:1.7;background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px;margin-bottom:14px}
    .mapa-wrap{border-radius:12px;overflow:hidden;border:1px solid var(--borda);height:280px;background:#e5e7eb;position:relative}
    .mapa-wrap iframe{width:100%;height:100%;border:none;display:block}
    .mapa-loading{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:.88rem;color:#64748b;background:#f1f5f9}
    .docs-list{display:flex;flex-direction:column;gap:8px}
    .doc-item{display:flex;align-items:center;justify-content:space-between;gap:10px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:10px 14px}
    .doc-info{display:flex;align-items:center;gap:8px;font-size:.84rem;font-weight:700;color:#0f172a}
    .doc-icon{font-size:1.2rem}
    .doc-btn{background:var(--azul);color:#fff;padding:6px 14px;border-radius:999px;font-size:.76rem;font-weight:900;white-space:nowrap}
    .doc-btn:hover{filter:brightness(1.1)}
    .doc-nota{font-size:.75rem;color:var(--muted);margin-top:6px;line-height:1.4}
    .sim-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    @media(max-width:500px){.sim-grid{grid-template-columns:1fr}}
    .fgroup{display:flex;flex-direction:column;gap:4px}
    .fgroup label{font-size:.74rem;font-weight:700;color:#475569;display:flex;align-items:center;gap:5px}
    .fgroup input,.fgroup select{border:1px solid #cbd5e1;border-radius:10px;padding:9px 10px;font-size:.84rem;width:100%;background:#fff;color:#0f172a;font-family:inherit}
    .fgroup input:focus,.fgroup select:focus{border-color:var(--azul);outline:none;box-shadow:0 0 0 2px rgba(0,83,166,.14)}
    .sim-tip{position:relative;display:inline-flex;align-items:center;cursor:pointer}
    .sim-tip-icon{width:14px;height:14px;border-radius:50%;background:#0053a6;color:#fff;font-size:.6rem;font-weight:900;display:flex;align-items:center;justify-content:center;flex-shrink:0;line-height:1}
    .sim-tip-box{display:none;position:absolute;left:0;top:calc(100% + 6px);background:#1e293b;color:#f1f5f9;font-size:.73rem;font-weight:400;line-height:1.45;padding:9px 12px;border-radius:8px;width:230px;z-index:300;box-shadow:0 6px 18px rgba(0,0,0,.28);pointer-events:none}
    .sim-tip-box::before{content:'';position:absolute;top:-5px;left:10px;border-left:5px solid transparent;border-right:5px solid transparent;border-bottom:5px solid #1e293b}
    .sim-tip.open .sim-tip-box,.sim-tip:hover .sim-tip-box{display:block}
    @media(max-width:600px){.sim-tip-box{width:180px}}
    .btn-simular{width:100%;background:linear-gradient(120deg,var(--laranja),#ffb347);border:none;border-radius:999px;padding:10px;font-weight:900;font-size:.9rem;color:#3b1f00;cursor:pointer;margin-top:10px;font-family:inherit}
    .btn-simular:hover{filter:brightness(1.05)}
    .sim-resultado{background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;padding:12px 14px;font-size:.84rem;line-height:1.8;margin-top:10px;display:none}
    .sim-nota{font-size:.73rem;color:var(--muted);text-align:center;margin-top:10px;line-height:1.5;padding:0 4px}
    .sidebar-det{display:flex;flex-direction:column;gap:16px}
    .creci-card{background:linear-gradient(135deg,#0b1a33,#01468d);color:#fff;border-radius:10px;padding:10px 14px}
    .creci-card-title{font-size:.7rem;font-weight:900;text-transform:uppercase;letter-spacing:.08em;opacity:.8;margin-bottom:3px}
    .creci-num{font-size:1.1rem;font-weight:900;letter-spacing:.05em;margin-bottom:2px}
    .creci-sub{font-size:.7rem;opacity:.85;line-height:1.3}
    .whats-det{display:flex;align-items:center;justify-content:center;gap:10px;background:#22c55e;color:#052e16;padding:13px 20px;border-radius:999px;font-weight:900;font-size:.95rem;box-shadow:0 4px 14px rgba(34,197,94,.35);transition:filter .2s}
    .whats-det:hover{filter:brightness(1.06)}
    .link-caixa{display:flex;align-items:center;justify-content:center;gap:8px;background:#fff;border:2px solid var(--azul);color:var(--azul);padding:11px 20px;border-radius:999px;font-weight:900;font-size:.88rem;transition:background .2s}
    .link-caixa:hover{background:var(--azul-card)}
    .btn-caixa-mobile-wrap{display:none;margin:16px 0}
    .info-list{display:flex;flex-direction:column;gap:8px}
    .info-row{display:flex;justify-content:space-between;align-items:flex-start;font-size:.84rem;padding:6px 0;border-bottom:1px solid #f1f5f9}
    .info-row:last-child{border-bottom:none}
    .info-label{color:var(--muted);font-weight:600;flex-shrink:0;padding-top:1px;margin-right:8px}
    .info-val{font-weight:900;color:#0f172a;text-align:right;max-width:60%;line-height:1.5;word-break:break-word}
    .whats-fixo{position:fixed;bottom:16px;right:16px;z-index:999;background:#22c55e;color:#052e16;padding:13px 20px;border-radius:999px;font-weight:900;font-size:.92rem;box-shadow:0 6px 20px rgba(34,197,94,.45);display:flex;align-items:center;gap:8px}
    .whats-fixo:hover{filter:brightness(1.06)}
    @media(min-width:901px){.whats-fixo{display:none}}
    .loading-wrap{text-align:center;padding:60px 20px;color:var(--muted);font-size:1rem}
    .loading-spinner{display:inline-block;width:32px;height:32px;border:3px solid #dbeafe;border-top-color:var(--azul);border-radius:50%;animation:spin .8s linear infinite;margin-bottom:12px}
    @keyframes spin{to{transform:rotate(360deg)}}
    .erro-wrap{text-align:center;padding:60px 20px}
    .erro-wrap h2{color:#b91c1c;margin-bottom:8px}
    .erro-wrap p{color:var(--muted);font-size:.9rem}
    footer{background:#020617;color:#64748b;padding:18px 20px 24px;font-size:.78rem;margin-top:0}
    .footer-inner{max-width:1100px;margin:0 auto;display:flex;flex-wrap:wrap;gap:10px;justify-content:space-between;align-items:center}
    .footer-links{display:flex;flex-wrap:wrap;gap:12px}
    .footer-links a{color:#cbd5e1;font-weight:700}
    .pagamento-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:8px;margin-bottom:14px}
    .pagamento-item{display:flex;flex-direction:column;align-items:center;gap:5px;background:#f0f7ff;border:1.5px solid #dbeafe;border-radius:10px;padding:10px 8px;text-align:center;font-size:.74rem;font-weight:800;color:#1e3a8a}
    .pagamento-item.inativo{background:#f8fafc;border-color:#e2e8f0;color:#94a3b8}
    .pagamento-icon{font-size:1.5rem}
    .pagamento-ok{font-size:.65rem;font-weight:900;color:#166534;background:#dcfce7;border-radius:999px;padding:1px 7px;margin-top:2px}
    .pagamento-no{font-size:.65rem;font-weight:900;color:#991b1b;background:#fee2e2;border-radius:999px;padding:1px 7px;margin-top:2px}
    .areas-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px}
    .area-item{background:#f0f7ff;border:1px solid #dbeafe;border-radius:10px;padding:8px;text-align:center}
    .area-val{font-size:1rem;font-weight:900;color:#0053a6}
    .area-label{font-size:.68rem;color:#64748b;margin-top:2px}
    .barra-contato{background:#0b1220;color:#e2e8f0;padding:16px 20px}
    .barra-wrap{max-width:1100px;margin:0 auto;display:flex;flex-wrap:wrap;gap:12px 24px;align-items:center;justify-content:space-between}
    footer.barra-contato{padding:24px 20px 0}
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
    .creci-info strong{display:block;font-size:.92rem;color:#f1f5f9;margin-bottom:2px}
    .creci-info span{font-size:.8rem}
    .whats-btn{background:#22c55e;color:#052e16;padding:11px 20px;border-radius:999px;font-weight:900;display:inline-flex;align-items:center;gap:8px;white-space:nowrap;font-size:.9rem}
    .whats-btn:hover{filter:brightness(1.06)}
    @media(max-width:600px){
      .det-grid{display:flex;flex-direction:column;gap:14px}
      .sidebar-det{order:2}
      .mobile-btns-topo{display:flex;flex-direction:column;gap:10px;margin-bottom:4px}
      .sidebar-btns-original{display:none!important}
      .preco-destaque{flex-direction:column;align-items:flex-start;gap:8px}
      .preco-venda-det{font-size:1.3rem}
      .pagamento-grid{grid-template-columns:repeat(2,1fr)}
      .btn-caixa-mobile-wrap{display:block!important}
      .areas-grid{grid-template-columns:repeat(3,1fr)}
      .panel-body{padding:14px 14px}
      .foto-wrap{height:240px}
      .sim-grid{grid-template-columns:1fr}
      .creci-card{padding:10px 14px}
      .whats-det{font-size:.88rem;padding:12px 16px}
      .doc-item{flex-wrap:wrap;gap:6px}
      .doc-btn{width:100%;text-align:center;padding:8px}
      .breadcrumb{font-size:.76rem}
      .barra-wrap{flex-direction:column;align-items:flex-start;gap:10px}
    }
    .sec-label{font-size:.72rem;font-weight:900;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:10px;display:flex;align-items:center;gap:6px}
  </style>
<style id="arremate-logic">
  body.is-compra-direta .tag-mod-det{background:#16a34a!important;color:#fff!important}
  body.is-compra-direta .cronometro-box,body.is-compra-direta #countdown,body.is-compra-direta .label-disputa,body.is-compra-direta .badge-disputa,body.is-compra-direta [id*="countdown"],body.is-compra-direta [class*="timer"],body.is-compra-direta [class*="cronometro"],body.is-compra-direta .tempo-restante{display:none!important}
  .status-direta{display:none;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:15px;border-radius:12px;margin-bottom:20px;font-weight:700;text-align:center}
  body.is-compra-direta .status-direta{display:block!important}
</style>
<script>
document.addEventListener("DOMContentLoaded",function(){
  const check=()=>{
    const bt=document.body.innerText.toLowerCase();
    if(bt.includes("venda direta online")||bt.includes("compra direta")){
      document.querySelectorAll(".cronometro-box,#countdown,.label-disputa,.badge-disputa").forEach(el=>el.style.display="none");
      const tag=document.querySelector(".tag-mod-det");
      if(tag){tag.innerText="Compra Direta";tag.style.backgroundColor="#16a34a";}
    }
  };
  check();setTimeout(check,500);setTimeout(check,2000);
});
</script>
<script id="correcao-final-arremate">
(function(){
  const arr_fix=()=>{
    const modTag=document.querySelector('.tag-mod-det')||document.getElementById('tag-mod');
    const desc=(document.querySelector('.det-descricao')?.innerText||'').toLowerCase();
    const rawText=(modTag?.innerText||'').toLowerCase();
    if(rawText.includes('direta')||desc.includes('direta')||desc.includes('venda direta online')){
      if(modTag){modTag.innerText='Compra Direta';modTag.style.backgroundColor='#16a34a';modTag.style.color='#fff';}
      document.querySelectorAll('.cronometro-box,#countdown,.label-disputa,.badge-disputa').forEach(el=>{el.style.display='none';});
      const lb=document.querySelector('.label-disputa');
      if(lb){lb.innerText='Disponível para Proposta';lb.style.display='block';}
    }
  };
  window.addEventListener('load',arr_fix);
  setTimeout(arr_fix,500);setTimeout(arr_fix,2000);
})();
</script>
</head>
<body<?= $isCompDireta ? ' class="is-direta is-compra-direta"' : '' ?>>
<header class="site-header">
  <input type="checkbox" id="menu-toggle" class="menu-chk" aria-hidden="true">
  <div class="hdr">
    <a href="index.php" class="logo">
      <div class="logo-icon">
        <img src="https://cdn.tess.im/assets/uploads/0e90758d-2354-4677-b743-9724498c3976.jpg" class="logo-icon-img" alt="Arremate Imóveis Online" loading="eager" decoding="async">
      </div>
      <div class="logo-txt">
        <div class="logo-aio">Arremate Imóveis Online</div>
        <div class="logo-sub">
          <span class="logo-sub-full">Onde a busca termina e a sua conquista começa.</span>
          <span class="logo-sub-mobile">Onde a busca termina e a sua conquista começa.</span>
        </div>
      </div>
    </a>
    <nav class="nav-links">
      <a href="index.php">Início</a>
      <a href="index.php#oportunidades">Oportunidades</a>
      <a href="resultados.html">Buscar Imóveis</a>
      <a href="favoritos.html">❤️ Favoritos</a>
      <a href="simulador-de-financiamento.php">Simulador</a>
      <a href="index.php#duvidas">Dúvidas</a>
      <a href="blog.html">Blog</a>
    </nav>
    <label for="menu-toggle" class="hamburger" aria-label="Abrir menu">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
        <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
      </svg>
    </label>
  </div>
  <nav class="nav-mobile">
    <label for="menu-toggle" class="nav-mob-close">✕ Fechar</label>
    <a href="index.php" onclick="document.getElementById('menu-toggle').checked=false">🏠 Início</a>
    <a href="index.php#oportunidades" onclick="document.getElementById('menu-toggle').checked=false">🏡 Oportunidades</a>
    <a href="resultados.html" onclick="document.getElementById('menu-toggle').checked=false">🔍 Buscar Imóveis</a>
    <a href="favoritos.html" onclick="document.getElementById('menu-toggle').checked=false">❤️ Favoritos</a>
    <a href="simulador-de-financiamento.php" onclick="document.getElementById('menu-toggle').checked=false">📊 Simulador</a>
    <a href="index.php#duvidas" onclick="document.getElementById('menu-toggle').checked=false">❓ Dúvidas</a>
    <a href="blog.html" onclick="document.getElementById('menu-toggle').checked=false">📝 Blog</a>
  </nav>
</header>

<div class="page-wrap">
  <div class="breadcrumb">
    <span style="font-weight:700">Detalhes do Imóvel</span><span class="breadcrumb-sep">›</span>
    <span id="breadcrumb-titulo"><?= $found ? esc($tipoTxt . ' em ' . $cidade) : 'Detalhe do imóvel' ?></span>
  </div>

  <div class="loading-wrap" id="loading-wrap" style="display:none">
    <div class="loading-spinner"></div>
    <div>Carregando dados do imóvel...</div>
  </div>

  <div class="erro-wrap" id="erro-wrap" style="display:<?= $found ? 'none' : 'block' ?>">
    <h2>😕 Imóvel não encontrado</h2>
    <p>Este imóvel pode ter sido vendido ou removido.<br>Tente buscar outro imóvel disponível.</p>
    <a href="resultados.html" style="display:inline-block;margin-top:16px;background:var(--azul);color:#fff;padding:10px 24px;border-radius:999px;font-weight:900;">← Voltar para busca</a>
  </div>

  <div id="det-conteudo" style="display:<?= $found ? 'block' : 'none' ?>">
    <div class="det-grid">
      <div style="display:flex;flex-direction:column;gap:16px">

        <!-- BLOCO 1: Apresentação -->
        <div class="panel">
          <div class="foto-wrap" id="foto-wrap">
            <div class="foto-fallback" id="foto-fallback">🏡</div>
            <div class="gallery-toggle" id="gallery-toggle">
              <button class="gallery-btn active" id="btn-foto" onclick="toggleGallery('foto')">📷 Foto</button>
              <button class="gallery-btn" id="btn-mapa" onclick="toggleGallery('mapa')">🗺️ Mapa</button>
            </div>
            <div id="gallery-mapa" class="gallery-mapa" style="display:none"></div>
            <span class="tag-tipo-det" id="tag-tipo"><?= esc($tipoTxt) ?></span>
            <span class="tag-mod-det" id="tag-mod"><?= esc($modalidade) ?></span>
          </div>
          <div class="panel-body">
            <div class="det-cidade-row">
              <div class="det-cidade" id="det-cidade"><?= esc(strtoupper($cidade)) ?> · <?= esc($uf) ?></div>
              <div class="det-actions-mobile">
                <button class="btn-det-action" id="btn-fav-mobile" onclick="toggleFavDetalhe()" title="Favoritar imóvel">
                  <svg viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2" width="18" height="18"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </button>
                <button class="btn-det-action" id="btn-share-mobile" onclick="compartilharDetalhe()" title="Compartilhar imóvel">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </button>
              </div>
            </div>
            <div class="det-titulo" id="det-titulo"><?= esc($bairro ?: $tipoTxt . ' em ' . $uf) ?></div>
            <div class="det-endereco" id="det-endereco"><?= esc($endereco) ?></div>
            <div class="preco-destaque">
              <div style="width:100%">
                <div class="preco-av-det" id="preco-av"><?= $avalCent > 0 ? 'Avaliação: ' . esc($avalBrl) : '' ?></div>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                  <div class="preco-venda-det" id="preco-venda"><?= esc($precoBrl) ?></div>
                  <div class="desconto-badge-det" id="desconto-badge" style="display:<?= $desconto > 0 ? 'block' : 'none' ?>">
                    <?= $desconto > 0 ? '-' . round($desconto) . '% OFF' : '' ?>
                  </div>
                </div>
                <?php if ($econBrl): ?>
                <div style="color:#86efac;font-size:.78rem;margin-top:4px">💰 Economia real: <strong><?= esc($econBrl) ?></strong> abaixo da avaliação</div>
                <?php endif; ?>
              </div>
            </div>
            <div class="atributos-row" id="atributos-row"></div>
            <div class="cond-row" id="cond-row"></div>
          </div>
        </div>

        <!-- MOBILE: botões de ação -->
        <div class="mobile-btns-topo">
<?php if ($isSP): ?>
          <button class="creci-card" id="btn-copiar-creci-mobile" onclick="copiarCreci(event)" style="width:100%;display:flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;border:none;font-family:inherit;font-size:.9rem;font-weight:900;letter-spacing:.03em">📋 Copiar CRECI: <?= CRECI_NUM ?></button>
<?php endif; ?>
        </div>

        <!-- BLOCO 2: Áreas + Pagamento -->
        <div class="panel">
          <div class="panel-body">
            <div class="sec-label">📐 Áreas do imóvel</div>
            <div class="areas-grid" id="areas-grid"></div>
            <div class="sec-label" style="margin-top:14px">💳 Formas de pagamento aceitas</div>
            <div class="pagamento-grid" id="pagamento-grid"></div>
            <div class="btn-caixa-mobile-wrap">
              <a href="<?= esc($link) ?>" class="link-caixa" target="_blank" rel="noopener">🏦 Ver no portal da CAIXA ↗</a>
            </div>
            <div id="regras-despesas" style="display:none;margin-top:16px">
              <div class="sec-label">🏛️ Regras para pagamento das despesas</div>
              <div id="regras-despesas-content" style="display:flex;flex-direction:column;gap:10px"></div>
            </div>
          </div>
        </div>

        <!-- BLOCO 4: Documentos -->
        <div class="panel">
          <div class="panel-body">
            <div class="sec-label">📄 Documentos do leilão</div>
            <div class="docs-list">
              <div class="doc-item" id="doc-edital-wrap">
<?php if($isCompDireta): ?>
                <div class="doc-info"><span class="doc-icon">📄</span><span>Regras de Venda Online</span></div>
                <a class="doc-btn" id="doc-edital" href="https://venda-imoveis.caixa.gov.br/editais/regras-VOL/comocomprar.pdf?v=01" target="_blank" rel="noopener">📄 Ver Regras</a>
<?php else: ?>
                <div class="doc-info"><span class="doc-icon">📋</span><span>Edital do Leilão</span></div>
                <a class="doc-btn" id="doc-edital" href="<?= esc($linkEdital) ?>" target="_blank" rel="noopener">📥 Baixar Edital</a>
<?php endif; ?>
              </div>
              <div class="doc-item" id="doc-matricula-wrap">
                <div class="doc-info"><span class="doc-icon">📄</span><span>Matrícula do Imóvel</span></div>
                <a class="doc-btn" id="doc-matricula" href="<?= esc($linkMatricula) ?>" target="_blank" rel="noopener">📄 Baixar Matrícula</a>
              </div>
            </div>
            <p class="doc-nota">⚠️ Documentos disponíveis no portal oficial da CAIXA. Confirme as informações diretamente na fonte antes de participar do processo.</p>
          </div>
        </div>

      </div>
      <div id="mapa-wrap" style="display:none"></div>

      <!-- SIDEBAR -->
      <div class="sidebar-det">
        <div class="panel">
          <div class="panel-body">
            <div class="sec-label">📋 Informações do imóvel</div>
            <div class="info-list" id="info-list"></div>
          </div>
        </div>

<?php if ($isSP): ?>
        <!-- ══ CRECI: exibido APENAS UF=SP ══ -->
        <button class="creci-card sidebar-btns-original" onclick="copiarCreci(event)" id="btn-copiar-creci"
          style="width:100%;display:flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;border:none;font-family:inherit;font-size:.9rem;font-weight:900;letter-spacing:.03em">
          📋 Copiar CRECI: <?= CRECI_NUM ?>
        </button>
        <div style="display:flex;flex-direction:column;gap:10px" class="sidebar-btns-original">
          <a href="<?= esc($waUrl) ?>" class="whats-det" target="_blank" rel="noopener" id="whats-link">💬 Falar com especialista</a>
          <a href="<?= esc($link) ?>" class="link-caixa" id="link-caixa-btn" target="_blank" rel="noopener">🏦 Ver no portal da CAIXA ↗</a>
        </div>
<?php else: ?>
        <!-- Fora de SP: plataforma nacional (sem CRECI) -->
        <div style="display:flex;flex-direction:column;gap:10px" class="sidebar-btns-original">
          <div style="background:#ede9fe;border-radius:10px;padding:12px 14px;font-size:.82rem;color:#5b21b6;font-weight:800;text-align:center">
            🌎 Plataforma inteligente para leilões da CAIXA em todo o Brasil
          </div>
          <a href="<?= esc($link) ?>" class="link-caixa" id="link-caixa-btn" target="_blank" rel="noopener">🏦 Ver no portal da CAIXA ↗</a>
        </div>
<?php endif; ?>

        <!-- Simulador -->
        <div class="panel">
          <div class="panel-body">
            <div class="sec-label">📊 Simule o financiamento</div>
            <div class="sim-grid" style="grid-template-columns:1fr">
              <div class="fgroup"><label>Valor do imóvel (R$) <span class="sim-tip" id="tip-val2"><span class="sim-tip-icon">i</span><span class="sim-tip-box">Informe o Valor de Avaliação do edital se desejar liberar a cota máxima de crédito. Esse valor é a base para reduzir sua entrada e aumentar o financiamento.</span></span></label><input type="text" id="sim-val2" inputmode="numeric" placeholder="Ex.: R$ 350.000,00"></div>
              <div class="fgroup"><label>Entrada (R$) <span class="sim-tip" id="tip-ent2"><span class="sim-tip-icon">i</span><span class="sim-tip-box">Para imóveis de leilão, o uso do Valor de Avaliação permite reduzir a entrada para 5%. Caso utilize o valor de venda, a entrada padrão sobe para 20%.</span></span></label><input type="text" id="sim-ent2" inputmode="numeric" placeholder="Ex.: R$ 60.000,00"></div>
              <div class="fgroup"><label>Prazo (meses) <span class="sim-tip"><span class="sim-tip-icon">i</span><span class="sim-tip-box">Quanto maior o prazo, menor a parcela mensal, mas maior o total de juros pago. O prazo máximo para financiamento CAIXA é de 35 anos (420 meses).</span></span></label><input type="number" id="sim-prazo2" value="360" min="12" step="12"></div>
              <div class="fgroup"><label>Juros (% a.a.) <span class="sim-tip"><span class="sim-tip-icon">i</span><span class="sim-tip-box">A taxa varia conforme relacionamento com a CAIXA, uso do FGTS e perfil do cliente. A média atual para financiamentos habitacionais é de 10,5% a.a.</span></span></label><input type="number" id="sim-juros2" value="10.5" min="0" step="0.1"></div>
              <div class="fgroup"><label>Sistema <span class="sim-tip"><span class="sim-tip-icon">i</span><span class="sim-tip-box">PRICE: parcelas fixas, mais previsibilidade no orçamento. SAC: parcelas decrescentes, menos juros no total. Para imóveis da CAIXA, o SAC costuma ser mais vantajoso a longo prazo.</span></span></label><select id="sim-sis2"><option value="PRICE">PRICE (parcela fixa)</option><option value="SAC">SAC (decrescente)</option></select></div>
            </div>
            <button class="btn-simular" onclick="rodarSim2()">📊 Simular</button>
            <div class="sim-resultado" id="sim-resultado2"></div>
            <p class="sim-nota">📋 Simulação estimada para referência. Não inclui seguros, taxas administrativas nem aprovação de crédito pelo banco. Para condições reais, consulte uma agência CAIXA ou correspondente bancário.</p>
          </div>
        </div>

        <div class="panel">
          <div class="panel-body" style="font-size:.8rem;color:var(--muted);line-height:1.6">
            <div class="sec-label">⚠️ Atenção</div>
            <p>Este site é <strong>independente</strong> e não é vinculado à Caixa Econômica Federal.</p>
            <p style="margin-top:6px">Confirme IPTU, condomínio, ocupação e documentação diretamente na CAIXA, Prefeitura ou Cartório antes de participar do processo.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if ($isSP): ?>
<a href="<?= esc($waUrl) ?>" class="whats-fixo" id="whats-fixo" target="_blank" rel="noopener">💬 Falar no WhatsApp</a>
<?php endif; ?>

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
      © <?= date('Y') ?> Arremate Imóveis Online — A plataforma de busca de imóveis da CAIXA em todo o Brasil.<br>
      <span style="color:#4b5563;font-size:13px">Este não é um site oficial da Caixa Econômica Federal. Plataforma independente de busca e comparação.</span>
    </div>
  </div>
</footer>

<script src="imovel-chips.js"></script>

<script>
/* ── Dados injetados pelo PHP via SQLite ───────────────────────── */
window.__IMOVEL_DATA__ = {
  hdnimovel:         '<?= jsStr($hdn) ?>',
  num_imovel:        '<?= jsStr($numero ?: $hdn) ?>',
  uf:                '<?= jsStr($uf) ?>',
  cidade:            '<?= jsStr($cidade) ?>',
  bairro:            '<?= jsStr($bairro) ?>',
  endereco:          '<?= jsStr($endereco) ?>',
  descricao:         '<?= jsStr($descricao) ?>',
  modalidade:        '<?= jsStr($modalidade) ?>',
  modalidade_raw:    '<?= jsStr($mod_raw) ?>',
  link:              '<?= jsStr($link) ?>',
  preco:             <?= $precoCent ?>,
  avaliacao:         <?= $avalCent ?>,
  desconto:          <?= json_encode($desconto) ?>,
  fgts:              <?= $fgts ?>,
  financiamento:     <?= $fin ?>,
  disputa:           <?= $disputa ?>,
  condominio:        '<?= jsStr($condominio) ?>',
  iptu:              '<?= jsStr($iptu) ?>',
  data_encerramento: '<?= jsStr($data_enc) ?>',
  data_leilao_1:     '<?= jsStr($data_leil1) ?>',
  foto_url:          '<?= jsStr($foto_url) ?>',
  nao_encontrado:    <?= $found ? 'false' : 'true' ?>
};
window.__IS_SP__ = <?= $isSP ? 'true' : 'false' ?>;
</script>

<script>
window.corTipo = function(tipo) {
  var t = (tipo||'').toLowerCase();
  if (t === 'apartamento') return {bg:'#6c757d',color:'#fff'};
  if (t === 'casa')        return {bg:'#7d5a3c',color:'#fff'};
  if (t === 'terreno' || t === 'lote' || t === 'gleba') return {bg:'#556b2f',color:'#fff'};
  return {bg:'#064e3b',color:'#f9fafb'};
};
window.corMod = function(modLabel) {
  var m = (modLabel||'').toLowerCase();
  if (m.indexOf('leil') !== -1)   return {bg:'#003366',color:'#fff'};
  if (m.indexOf('licita') !== -1) return {bg:'#6f42c1',color:'#fff'};
  if (m === 'venda online')       return {bg:'#28a745',color:'#fff'};
  if (m.indexOf('direta') !== -1 || m === 'compra direta') return {bg:'#17A2B8',color:'#fff'};
  return {bg:'#f39200',color:'#3b1f00'};
};
window.corrigirModalidade = function(m) {
  if(!m) return "";
  var t = m.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g,"").trim();
  // "Venda Direta Online" ou qualquer variante com "direta" → Compra Direta
  if(t.indexOf("direta") !== -1) return "Compra Direta";
  // Licitação Aberta
  if(t.indexOf("licita") !== -1 || t.indexOf("aberta") !== -1) return "Licitação Aberta";
  // Leilão SFI (tem "leil" mas NÃO tem "online" sem "direta")
  if(t.indexOf("leil") !== -1) return "Leilão SFI - Edital Único";
  // Venda Online pura (sem "direta", sem "leil") → Venda Online
  if(t.indexOf("online") !== -1) return "Venda Online";
  return m;
};

function norm(v){return String(v||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase().trim();}
function fmtBRL(v){if(v===null||v===undefined||v==='')return '';var n=parseFloat(String(v).replace(/[^0-9.,]/g,'').replace(',','.'));if(isNaN(n))return '';return n.toLocaleString('pt-BR',{style:'currency',currency:'BRL',minimumFractionDigits:2});}
function parsePrecoNum(v){if(!v)return 0;var s=String(v).replace(/[R$\s\.]/g,'').replace(',','.');return parseFloat(s)||0;}
function limpaNumeroBR(v){var s=String(v||'').replace(/[R$\s]/g,'').replace(/\./g,'').replace(',','.');return parseFloat(s)||0;}
function formataMilhar(n){var p=parseFloat(n).toFixed(2).split('.');p[0]=p[0].replace(/\B(?=(\d{3})+(?!\d))/g,'.');return p.join(',');}
function getHdnFromLink(link){if(!link)return '';try{var u=new URL(String(link).trim());return(u.searchParams.get('hdnimovel')||'').replace(/\D/g,'');}catch(e){var m=String(link).match(/hdnimovel=(\d+)/i);return m?m[1]:'';}}
function setText(id,val){var el=document.getElementById(id);if(el)el.textContent=val;}
function setHref(id,val){var el=document.getElementById(id);if(el)el.href=val;}
function inferTipoLocal(desc){var d=norm(desc||'');if(d.indexOf('apartamento')!==-1)return 'Apartamento';if(d.indexOf('casa')!==-1)return 'Casa';if(d.indexOf('terreno')!==-1)return 'Terreno';if(d.indexOf('gleba')!==-1)return 'Gleba';if(d.indexOf('loja')!==-1)return 'Loja';if(d.indexOf('predio')!==-1)return 'Prédio';if(d.indexOf('sala')!==-1)return 'Sala';if(d.indexOf('lote')!==-1)return 'Lote';return 'Imóvel';}

var mapaInicializado=false;var mapaEndereco='';var mapaIframeHtml='';
function initMapa(){mapaInicializado=true;if(mapaEndereco)renderMapa(mapaEndereco);}
function renderMapa(endereco){
  mapaEndereco=endereco;
  var enc=encodeURIComponent(endereco+', Brasil');
  mapaIframeHtml='<iframe src="https://maps.google.com/maps?q='+enc+'&output=embed&z=15" allowfullscreen loading="lazy" style="width:100%;height:100%;border:none"></iframe>';
  /* Render no bloco separado de mapa (BLOCO 5) */
  var wrap=document.getElementById('mapa-wrap');if(!wrap)return;
  var loading=document.getElementById('mapa-loading');if(loading)loading.remove();
  wrap.innerHTML=mapaIframeHtml;
  /* Preparar mapa na galeria também */
  var gm=document.getElementById('gallery-mapa');
  if(gm)gm.innerHTML=mapaIframeHtml;
}

/* ── Toggle Foto/Mapa na galeria ── */
function toggleGallery(mode){
  var btnFoto=document.getElementById('btn-foto');
  var btnMapa=document.getElementById('btn-mapa');
  var galleryMapa=document.getElementById('gallery-mapa');
  var fotoEl=document.querySelector('.foto-principal');
  var fallback=document.getElementById('foto-fallback');
  var tagTipo=document.getElementById('tag-tipo');

  if(mode==='mapa'){
    btnMapa.classList.add('active');btnFoto.classList.remove('active');
    if(tagTipo)tagTipo.style.display='none';
    if(galleryMapa){
      if(!galleryMapa.innerHTML&&mapaIframeHtml)galleryMapa.innerHTML=mapaIframeHtml;
      else if(!galleryMapa.innerHTML&&mapaEndereco){var enc=encodeURIComponent(mapaEndereco+', Brasil');galleryMapa.innerHTML='<iframe src="https://maps.google.com/maps?q='+enc+'&output=embed&z=15" allowfullscreen loading="lazy" style="width:100%;height:100%;border:none"></iframe>';}
      galleryMapa.style.display='block';
    }
    if(fotoEl)fotoEl.style.display='none';
    if(fallback)fallback.style.display='none';
  } else {
    btnFoto.classList.add('active');btnMapa.classList.remove('active');
    if(tagTipo)tagTipo.style.display='';
    if(galleryMapa)galleryMapa.style.display='none';
    if(fotoEl)fotoEl.style.display='block';
    else if(fallback)fallback.style.display='flex';
  }
}

function aplicarMascaraSim(){
  ['sim-val','sim-ent','sim-renda','sim-val2','sim-ent2'].forEach(function(id){var el=document.getElementById(id);if(!el)return;el.addEventListener('input',function(){var raw=this.value.replace(/[^\d,]/g,'');var parts=raw.split(',');var intFmt=parts[0]?parts[0].replace(/\B(?=(\d{3})+(?!\d))/g,'.'):'';var dec=parts.length>1?','+parts[1].substring(0,2):',00';this.value=intFmt?'R$ '+intFmt+dec:'';});});
  /* Tooltip toggle nos campos do simulador */
  ['tip-val2','tip-ent2'].forEach(function(id){
    var tip=document.getElementById(id); if(!tip) return;
    tip.addEventListener('click',function(e){e.stopPropagation();tip.classList.toggle('open');});
  });
  document.addEventListener('click',function(){
    ['tip-val2','tip-ent2'].forEach(function(id){var t=document.getElementById(id);if(t)t.classList.remove('open');});
  });
}

function extrairAreas(desc){
  var areas={total:0,privativa:0,terreno:0};
  var m;
  // Formato CSV: "45,16M2 DE AREA PRIVATIVA" ou "45,16 DE AREA PRIVATIVA"
  // Formato scraper: "Área Privativa: 45,16 m²"
  m=desc.match(/([\d.,]+)\s*(?:m[²2])?\s*de\s*[aá]rea\s*total/i)||desc.match(/[aá]rea\s*total[:\s]+([\d.,]+)/i);
  if(m)areas.total=parseFloat((m[1]||m[2]||'0').replace(',','.'));
  m=desc.match(/([\d.,]+)\s*(?:m[²2])?\s*de\s*[aá]rea\s*privativa/i)||desc.match(/[aá]rea\s*privativa[:\s]+([\d.,]+)/i);
  if(m)areas.privativa=parseFloat((m[1]||m[2]||'0').replace(',','.'));
  m=desc.match(/([\d.,]+)\s*(?:m[²2])?\s*de\s*[aá]rea\s*do\s*terreno/i)||desc.match(/[aá]rea\s*(?:do\s*)?terreno[:\s]+([\d.,]+)/i);
  if(m)areas.terreno=parseFloat((m[1]||m[2]||'0').replace(',','.'));
  return areas;
}

function renderAreas(desc){
  var areas=extrairAreas(desc);
  var grid=document.getElementById('areas-grid');if(!grid)return;
  [{label:'Área Total',val:areas.total,icon:'📏'},{label:'Área Privativa',val:areas.privativa,icon:'🏠'},{label:'Área do Terreno',val:areas.terreno,icon:'🌳'}].forEach(function(i){
    var v=i.val>0?i.val.toFixed(2).replace('.',',')+'m²':'—';
    grid.innerHTML+='<div class="area-item"><div class="area-val">'+i.icon+' '+v+'</div><div class="area-label">'+i.label+'</div></div>';
  });
}

function renderPagamento(desc,modalidade,apiData){
  var grid=document.getElementById('pagamento-grid');if(!grid)return;
  grid.innerHTML=''; /* limpa antes de renderizar para evitar duplicação */
  var d=(desc+' '+modalidade).toLowerCase();
  var isTerreno=(d.indexOf('terreno')!==-1||d.indexOf('lote')!==-1||d.indexOf('gleba')!==-1);
  var _db=window.__IMOVEL_DATA__||{};
  var fgts2=isTerreno?false:(apiData?apiData.fgts==1:_db.fgts==1);
  var fin2=apiData?apiData.financiamento==1:_db.financiamento==1;
  var parcel=apiData?apiData.parcelamento==1:false;
  [{icon:'💵',label:'À vista',ok:true},{icon:'🏦',label:'Financiamento CAIXA',ok:fin2},{icon:'💼',label:'FGTS',ok:fgts2},{icon:'🔄',label:'Parcelamento',ok:parcel}].forEach(function(i){
    var cls=i.ok?'pagamento-item':'pagamento-item inativo';
    var badge=i.ok?'<div class="pagamento-ok">✓ Aceito</div>':'<div class="pagamento-no">✗ Não</div>';
    grid.innerHTML+='<div class="'+cls+'"><div class="pagamento-icon">'+i.icon+'</div><div>'+i.label+'</div>'+badge+'</div>';
  });
  // Regras de despesas — sempre exibidas (textos oficiais da CAIXA)
  var rw=document.getElementById('regras-despesas'),rc=document.getElementById('regras-despesas-content');
  if(rc)rc.innerHTML='';
  if(rw)rw.style.display='block';
  if(rc){
    var cmTxt={
      limitada:'As despesas de condomínio vencidas e não pagas são de responsabilidade da CAIXA Econômica Federal, limitadas ao equivalente a 10% (dez por cento) do valor de avaliação do imóvel; as despesas que ultrapassarem esse limite são de responsabilidade do adquirente.',
      comprador:'As despesas de condomínio são de responsabilidade do adquirente.',
    };
    var iptuTxt={
      caixa:'Os tributos, taxas e demais encargos incidentes sobre o imóvel até a data desta venda são de responsabilidade da CAIXA Econômica Federal.',
      comprador:'Os tributos, taxas e demais encargos incidentes sobre o imóvel são de responsabilidade do adquirente.',
    };
    var condVal=(apiData&&apiData.condominio)?apiData.condominio:'';
    var iptuVal=(apiData&&apiData.iptu)?apiData.iptu:'';
    var condTexto=condVal?(cmTxt[condVal]||condVal):'Consulte o edital do imóvel para verificar a responsabilidade pelas despesas de condomínio.';
    var iptuTexto=iptuVal?(iptuTxt[iptuVal]||iptuVal):'Consulte o edital do imóvel para verificar a responsabilidade pelos tributos e taxas.';
    var row='<div style="background:#f8fafc;border-radius:8px;padding:10px 12px;border-left:3px solid #0053a6;margin-bottom:8px"><div style="color:#0053a6;font-weight:700;font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">{titulo}</div><div style="color:#334155;font-size:.82rem;line-height:1.6">{texto}</div></div>';
    rc.innerHTML+=row.replace('{titulo}','Condomínio').replace('{texto}',condTexto);
    rc.innerHTML+=row.replace('{titulo}','Tributos e Taxas').replace('{texto}',iptuTexto);
  }
}

function canonModalidade(v){var s=String(v||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase().trim();if(s.includes('direta')||s.includes('venda direta'))return 'compra_direta';if(s.includes('venda online'))return 'venda_online';if(s.includes('leil'))return 'leilao_sfi';if(s.includes('licita'))return 'licitacao_aberta';return s.replace(/\s+/g,'_');}

/* ══ buscarDadosApi — usa dados PHP; scraper sob demanda (pode estar blocked) ══ */
async function buscarDadosApi(hdn){
  var base = (window.__IMOVEL_DATA__ && !window.__IMOVEL_DATA__.nao_encontrado) ? window.__IMOVEL_DATA__ : null;
  if (!base || !hdn) return base;

  // Calcular links de fallback a partir dos dados PHP
  var uf = base.uf || 'SP';
  var linkCaixaFb = base.link || '#'; /* Página do imóvel na CAIXA — contém o link do edital */
  var linkMatriculaFb = 'https://venda-imoveis.caixa.gov.br/editais/matricula/' + uf + '/' + hdn + '.pdf';

  try {
    var resp = await fetch('caixa-scrape-detalhe.php?hdnimovel=' + encodeURIComponent(hdn));
    if (!resp.ok) throw new Error('HTTP ' + resp.status);
    var data = await resp.json();
    if (data && data.sucesso) {
      base.fgts = data.fgts;
      base.financiamento = data.financiamento;
      if (data.condominio) base.condominio = data.condominio;
      if (data.iptu) base.iptu = data.iptu;
      if (data.foto_url) base.foto_url = data.foto_url;
      if (data.data_encerramento) base.data_encerramento = data.data_encerramento;
      if (data.data_leilao_1)    base.data_leilao_1    = data.data_leilao_1;
      if (data.data_inicio)      base.data_inicio       = data.data_inicio;

      if (data.foto_url) {
        var fotoEl = document.querySelector('.foto-principal');
        if (fotoEl) { var ti=new Image(); ti.onload=function(){fotoEl.src=data.foto_url;}; ti.src=data.foto_url; }
      }

      // Documentos: edital com URL do scraper ou fallback para página da CAIXA
      var edUrl = data.edital_url || linkCaixaFb;
      setHref('doc-edital', edUrl);
      var edWrap = document.getElementById('doc-edital-wrap');
      if (edWrap) edWrap.style.display = 'flex';

      // Matrícula
      setHref('doc-matricula', data.matricula_url || linkMatriculaFb);

      // Badges condição
      var condRow = document.getElementById('cond-row');
      if (condRow) {
        var modLabel = window.corrigirModalidade(base.modalidade_raw || base.modalidade) || '';
        condRow.innerHTML = '';
        if (modLabel) condRow.innerHTML += '<span class="badge-cond badge-mod">📋 ' + modLabel + '</span>';
        if (data.fgts == 1) condRow.innerHTML += '<span class="badge-cond badge-fgts">✅ Aceita FGTS</span>';
        if (data.financiamento == 1) condRow.innerHTML += '<span class="badge-cond badge-fin">🏦 Aceita Financiamento</span>';
        if (base.disputa == 1) condRow.innerHTML += '<span class="badge-cond badge-disputa">⚡ Em disputa</span>';
      }

      // Atualizar datas na lista de informações quando retornadas pela API
      if (data.data_encerramento || data.data_leilao_1) {
        var infoList = document.getElementById('info-list');
        if (infoList) {
          var modC2api = canonModalidade(base.modalidade_raw || base.modalidade || '');

          if (modC2api === 'leilao_sfi' && (data.data_leilao_1 || data.data_encerramento)) {
            // SFI: atualiza/cria linha do 1º e 2º Leilão
            var r1 = infoList.querySelector('[data-field="data_leilao_1"]');
            var v1 = fmtData(data.data_leilao_1 || '');
            if (r1) { r1.querySelector('.info-val').textContent = v1 || '—'; }
            else { infoList.innerHTML += '<div class="info-row" data-field="data_leilao_1"><span class="info-label">📅 1º Leilão</span><span class="info-val">' + (v1||'—') + '</span></div>'; }

            var r2 = infoList.querySelector('[data-field="data_encerramento"]');
            var v2 = fmtData(data.data_encerramento || '');
            if (r2) { r2.querySelector('.info-val').textContent = v2 || '—'; }
            else { infoList.innerHTML += '<div class="info-row" data-field="data_encerramento"><span class="info-label">📅 2º Leilão</span><span class="info-val">' + (v2||'—') + '</span></div>'; }
          } else if (data.data_encerramento) {
            // Demais modalidades: única data de encerramento
            var labelApi = '📅 Encerra em';
            if (modC2api === 'licitacao_aberta') labelApi = '📅 Data da Licitação';
            else if (modC2api === 'compra_direta') labelApi = '📅 Disponível até';
            var vEnc = fmtData(data.data_encerramento);
            var existsRow = infoList.querySelector('[data-field="data_encerramento"]');
            if (existsRow) { existsRow.querySelector('.info-val').textContent = vEnc; }
            else { infoList.innerHTML += '<div class="info-row" data-field="data_encerramento"><span class="info-label">' + labelApi + '</span><span class="info-val">' + vEnc + '</span></div>'; }
          }
        }
      }

      // Atualizar áreas: usar campos diretos do scraper quando disponíveis
      var areasGrid = document.getElementById('areas-grid');
      if (areasGrid && (data.area_privativa > 0 || data.area_total > 0 || data.area_terreno > 0)) {
        areasGrid.innerHTML = '';
        [{label:'Área Total',val:data.area_total||0,icon:'📏'},
         {label:'Área Privativa',val:data.area_privativa||0,icon:'🏠'},
         {label:'Área do Terreno',val:data.area_terreno||0,icon:'🌳'}].forEach(function(i){
          var v=i.val>0?i.val.toFixed(2).replace('.',',')+'m²':'—';
          areasGrid.innerHTML+='<div class="area-item"><div class="area-val">'+i.icon+' '+v+'</div><div class="area-label">'+i.label+'</div></div>';
        });
      } else if (data.descricao && data.descricao !== '') {
        // Fallback: reanalisar descrição retornada pelo scraper
        base.descricao = data.descricao;
        if (areasGrid) { areasGrid.innerHTML = ''; renderAreas(base.descricao); }
      }

    }
  } catch (e) {
    console.log('Scrape indisponível (Radware?), usando dados locais:', e.message);
    setHref('doc-edital', linkCaixaFb);
    setHref('doc-matricula', linkMatriculaFb);
  }
  return base;
}

function montarPagina(item){
  var tipoTxt=inferTipoLocal(item.descricao);
  document.title=tipoTxt+' em '+(item.cidade||'SP')+' - CAIXA | Arremate Imóveis Online';
  setText('breadcrumb-titulo',tipoTxt+' em '+(item.cidade||'SP'));
  var hdn=item.num_imovel||item.hdnimovel||getHdnFromLink(item.link||'');
  if(hdn){var img=document.createElement('img');img.className='foto-principal';img.alt=tipoTxt;img.src='caixa-foto.php?hdnimovel='+encodeURIComponent(hdn);img.addEventListener('load',function(){var ff=document.getElementById('foto-fallback');if(ff)ff.style.display='none';});img.addEventListener('error',function(){img.remove();});var fw=document.getElementById('foto-wrap');if(fw)fw.appendChild(img);}
  setText('tag-tipo',tipoTxt);
  var elTipo=document.getElementById('tag-tipo');if(elTipo){var cT=window.corTipo(tipoTxt);elTipo.style.background=cT.bg;elTipo.style.color=cT.color;}
  var modLabel=window.corrigirModalidade(item.modalidade)||'';
  setText('tag-mod',modLabel);
  var elMod=document.getElementById('tag-mod');if(elMod&&modLabel){var cM=window.corMod(modLabel);elMod.style.background=cM.bg;elMod.style.color=cM.color;}
  if(modLabel==='Compra Direta'){
    document.body.classList.add('is-direta','is-compra-direta');
    /* Troca botão edital por Regras de Venda Online */
    var edInfo=document.querySelector('#doc-edital-wrap .doc-info span:last-child');
    var edBtn=document.getElementById('doc-edital');
    var edIcon=document.querySelector('#doc-edital-wrap .doc-icon');
    if(edInfo)edInfo.textContent='Regras de Venda Online';
    if(edIcon)edIcon.textContent='📄';
    if(edBtn){edBtn.href='https://venda-imoveis.caixa.gov.br/editais/regras-VOL/comocomprar.pdf?v=01';edBtn.textContent='📄 Ver Regras';}
  }
  setText('det-cidade',(item.cidade||'').toUpperCase()+' · '+(item.uf||'SP'));
  setText('det-titulo',item.bairro?item.bairro.trim():tipoTxt+' em '+(item.uf||'SP'));
  setText('det-endereco',item.endereco||'');
  setText('preco-venda',fmtBRL(item.preco));
  var avEl=document.getElementById('preco-av');if(item.avaliacao&&avEl)avEl.textContent='Avaliação: '+fmtBRL(item.avaliacao);
  var pct=parseFloat(String(item.desconto||'0').replace('%','').replace(',','.'));
  var dbEl=document.getElementById('desconto-badge');if(pct>0&&dbEl){dbEl.style.display='block';dbEl.textContent='-'+pct.toFixed(0)+'% OFF';}
  renderAreas(item.descricao||'');
  /* Renderiza pagamento/regras imediatamente com dados do banco (sem latência de scrape) */
  renderPagamento(item.descricao||'',window.corrigirModalidade(item.modalidade)||'',item);
  buscarDadosApi(hdn).then(function(apiData){
    /* Atualiza se o scraper trouxer dados mais recentes */
    if(apiData&&(apiData.condominio||apiData.iptu))renderPagamento(item.descricao||'',window.corrigirModalidade(item.modalidade)||'',apiData);
    var condRow=document.getElementById('cond-row');
    if(condRow&&apiData){condRow.innerHTML='';if(apiData.fgts==1)condRow.innerHTML+='<span class="badge-cond badge-fgts">✅ Aceita FGTS</span>';if(apiData.financiamento==1)condRow.innerHTML+='<span class="badge-cond badge-fin">🏦 Aceita Financiamento</span>';if(apiData.disputa==1)condRow.innerHTML+='<span class="badge-cond badge-disputa">⚡ Em disputa</span>';}
    var infoList=document.getElementById('info-list');
    if(infoList&&apiData){
      var cmInfo={limitada:'CAIXA paga até 10% do valor de avaliação; excedente é do adquirente.',comprador:'Responsabilidade do adquirente.'};
      var iptuInfo={caixa:'Responsabilidade da CAIXA até a data da venda.',comprador:'Responsabilidade do adquirente.'};
      var condLabel=apiData.condominio?(cmInfo[apiData.condominio]||apiData.condominio):'Consulte o edital';
      var iptuLabel=apiData.iptu?(iptuInfo[apiData.iptu]||apiData.iptu):'Consulte o edital';
      infoList.innerHTML+='<div class="info-row"><span class="info-label">Condomínio</span><span class="info-val">'+condLabel+'</span></div>';
      infoList.innerHTML+='<div class="info-row"><span class="info-label">Tributos</span><span class="info-val">'+iptuLabel+'</span></div>';
    }
  });
  if(typeof buildChipsRow==='function'){var ar=buildChipsRow(item.descricao||'');var arWrap=document.getElementById('atributos-row');if(ar&&arWrap)arWrap.appendChild(ar);}
  var condRow=document.getElementById('cond-row');
  if(condRow&&modLabel)condRow.innerHTML='<span class="badge-cond badge-mod">📋 '+modLabel+'</span>';
  var infoList=document.getElementById('info-list');
  var infoItems=[{l:'Nº do imóvel',v:item.num_imovel||hdn||'—'},{l:'Cidade',v:(item.cidade||'—')+' · '+(item.uf||'SP')},{l:'Modalidade',v:modLabel||'—'}];
  var modC2=canonModalidade(item.modalidade||'');

  // Helper: formata 'YYYY-MM-DD HH:MM:SS' → 'DD/MM/YYYY às HH:MM'
  function fmtData(raw) {
    if (!raw || !/^\d{4}-\d{2}-\d{2}/.test(raw)) return raw || '';
    var dp = raw.split(/[T ]/);
    var parts = dp[0].split('-');
    var s = parts[2]+'/'+parts[1]+'/'+parts[0];
    if (dp[1]) s += ' às ' + dp[1].substring(0,5);
    return s;
  }

  // Datas de leilão — SFI tem 2 datas; demais modalidades têm apenas encerramento
  var dataLeil1 = item.data_leilao_1 || '';
  var dataEnc   = item.data_encerramento || '';

  if (modC2 === 'leilao_sfi' && dataLeil1) {
    // SFI: exibe 1º e 2º Leilão separadamente
    infoItems.push({l: '📅 1º Leilão', v: fmtData(dataLeil1) || '—'});
    infoItems.push({l: '📅 2º Leilão', v: fmtData(dataEnc)   || '—'});
  } else {
    // Licitação Aberta / Venda Online / Compra Direta: uma única data
    var labelData = '📅 Encerra em';
    if (modC2 === 'licitacao_aberta') labelData = '📅 Data da Licitação';
    else if (modC2 === 'compra_direta') labelData = '📅 Disponível até';
    if (modC2 !== 'compra_direta' || dataEnc) {
      infoItems.push({l: labelData, v: fmtData(dataEnc) || '—'});
    }
  }
  infoItems=infoItems.concat([{l:'Preço de venda',v:fmtBRL(item.preco)||'—'},{l:'Avaliação',v:item.avaliacao?fmtBRL(item.avaliacao):'—'},{l:'Desconto',v:pct>0?pct.toFixed(0)+'%':'—'}]);
  if(infoList)infoItems.forEach(function(i){infoList.innerHTML+='<div class="info-row"><span class="info-label">'+i.l+'</span><span class="info-val">'+i.v+'</span></div>';});
  var linkCaixa=item.link||'#';
  // Nota: doc-edital e doc-matricula já têm hrefs do PHP (SSR); buscarDadosApi pode atualizá-los se scraper funcionar
  setHref('link-caixa-mobile',linkCaixa);setHref('doc-caixa',linkCaixa);setHref('link-caixa-btn',linkCaixa);
  /* WhatsApp com hdnimovel correto passado para todos os hooks */
  var paginaUrl=window.location.origin+'/imovel.php?hdnimovel='+(item.num_imovel||hdn||'');
  var msgWa=encodeURIComponent('Olá! Tenho interesse neste imóvel da CAIXA:\n'+tipoTxt+' em '+(item.cidade||'SP')+'\nEndereço: '+(item.endereco||item.bairro||'')+'\nPreço: '+fmtBRL(item.preco)+'\n\n'+paginaUrl);
  var waUrl='https://wa.me/<?= WA_NUMBER ?>?text='+msgWa;
  setHref('whats-link',waUrl);setHref('whats-fixo',waUrl);setHref('whats-link-mobile',waUrl);setHref('whats-barra',waUrl);
  var precoNum = (typeof item.preco === 'number') ? item.preco : parseFloat(String(item.preco).replace(/[R$\s]/g,'').replace(',','.')) || 0;
  // sim-val2 deixado em branco — usuário preenche manualmente
  renderMapa((item.endereco||'')+', '+(item.bairro||'')+', '+(item.cidade||'São Paulo'));
  document.getElementById('loading-wrap').style.display='none';
  document.getElementById('det-conteudo').style.display='block';
}

/* ══ carregarImovel: lê window.__IMOVEL_DATA__ (PHP), sem fetch ══ */
function carregarImovel(){
  var d=window.__IMOVEL_DATA__;
  if(!d||d.nao_encontrado){document.getElementById('loading-wrap').style.display='none';document.getElementById('erro-wrap').style.display='block';return;}
  var item={
    num_imovel:d.hdnimovel,hdnimovel:d.hdnimovel,
    uf:d.uf,cidade:d.cidade,bairro:d.bairro,endereco:d.endereco,descricao:d.descricao,
    modalidade:d.modalidade_raw||d.modalidade,link:d.link,
    preco:d.preco/100,        /* centavos → reais para fmtBRL JS */
    avaliacao:d.avaliacao/100,
    desconto:String(d.desconto),
    fgts:d.fgts,financiamento:d.financiamento,disputa:d.disputa,
    condominio:d.condominio,iptu:d.iptu,data_encerramento:d.data_encerramento
  };
  try{montarPagina(item);aplicarMascaraSim();}
  catch(e){console.error('ERRO montarPagina:',e,e.stack);document.getElementById('loading-wrap').style.display='none';document.getElementById('erro-wrap').style.display='block';}
}
document.addEventListener('DOMContentLoaded',carregarImovel);

/* ── FAVORITOS (localStorage, mesmo padrão de favoritos.html) ── */
var FAV_KEY='arremate_favoritos';
function lerFavs(){try{var r=localStorage.getItem(FAV_KEY);if(!r)return[];var p=JSON.parse(r);if(!Array.isArray(p))return[];return p.map(function(i){return typeof i==='string'?{id:i,savedAt:0}:{id:String(i.id||''),savedAt:i.savedAt||0};}).filter(function(i){return i.id!=='';});}catch(e){return[];}}
function salvarFavs(l){try{localStorage.setItem(FAV_KEY,JSON.stringify(l));}catch(e){}}
function isFav(id){return lerFavs().some(function(f){return f.id===String(id);});}
function addFav(id){if(isFav(id))return;var l=lerFavs();l.push({id:String(id),savedAt:Date.now()});salvarFavs(l);}
function removeFav(id){salvarFavs(lerFavs().filter(function(f){return f.id!==String(id);}));}

var _SVG_HEART_FILLED='<svg viewBox="0 0 24 24" fill="#dc2626" stroke="#dc2626" stroke-width="2" style="width:16px;height:16px;flex-shrink:0"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>';
var _SVG_HEART_EMPTY='<svg viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2" style="width:16px;height:16px;flex-shrink:0"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>';

var _SVG_HEART_FILLED_SM='<svg viewBox="0 0 24 24" fill="#dc2626" stroke="#dc2626" stroke-width="2" width="18" height="18"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>';
var _SVG_HEART_EMPTY_SM='<svg viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2" width="18" height="18"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>';

function toggleFavDetalhe(){
  var d=window.__IMOVEL_DATA__;if(!d)return;
  var id=d.hdnimovel;
  if(isFav(id)){removeFav(id);}else{addFav(id);}
  atualizarBtnFavDetalhe();
}

function atualizarBtnFavDetalhe(){
  var d=window.__IMOVEL_DATA__;if(!d)return;
  var fav=isFav(d.hdnimovel);
  var btnCard=document.getElementById('btn-fav-mobile');
  if(btnCard){btnCard.innerHTML=fav?_SVG_HEART_FILLED_SM:_SVG_HEART_EMPTY_SM;btnCard.classList.toggle('is-fav',fav);btnCard.title=fav?'Remover dos favoritos':'Favoritar imóvel';}
}
document.addEventListener('DOMContentLoaded',function(){setTimeout(atualizarBtnFavDetalhe,100);});

/* ── COMPARTILHAR ── */
function compartilharDetalhe(){
  var d=window.__IMOVEL_DATA__;if(!d)return;
  var url=window.location.href;
  var texto='Imóvel CAIXA em '+(d.cidade||'')+'/'+(d.uf||'SP')+' por '+(fmtBRL(d.preco/100)||'consulte')+' - Arremate Imóveis Online';
  if(navigator.share){
    navigator.share({title:texto,url:url}).catch(function(){});
  }else{
    navigator.clipboard.writeText(url).then(function(){
      var btn=document.getElementById('btn-share-det');
      if(btn){var old=btn.textContent;btn.textContent='✅ Link copiado!';setTimeout(function(){btn.textContent=old;},2000);}
    }).catch(function(){prompt('Copie o link:',url);});
  }
}

function copiarCreci(e){
  if(e)e.stopPropagation();
  var txt='CRECI-SP <?= CRECI_NUM ?>';
  var labelOriginal='📋 Copiar CRECI: <?= CRECI_NUM ?>';
  var labelCopiado='✅ Copiado!';
  function marcarCopiado(){
    ['btn-copiar-creci','btn-copiar-creci-mobile'].forEach(function(id){
      var btn=document.getElementById(id);
      if(btn){btn.textContent=labelCopiado;setTimeout(function(){btn.textContent=labelOriginal;},2000);}
    });
  }
  if(navigator.clipboard&&navigator.clipboard.writeText){
    navigator.clipboard.writeText(txt).then(marcarCopiado).catch(function(){
      var el=document.createElement('textarea');el.value=txt;document.body.appendChild(el);el.select();document.execCommand('copy');document.body.removeChild(el);marcarCopiado();
    });
  } else {
    var el=document.createElement('textarea');el.value=txt;document.body.appendChild(el);el.select();document.execCommand('copy');document.body.removeChild(el);marcarCopiado();
  }
}

function rodarSim(){
  var val=limpaNumeroBR(document.getElementById('sim-val')?document.getElementById('sim-val').value:0)||0;
  var ent=limpaNumeroBR(document.getElementById('sim-ent')?document.getElementById('sim-ent').value:0)||0;
  var prazo=parseInt(document.getElementById('sim-prazo')?document.getElementById('sim-prazo').value:360)||360;
  var jaa=parseFloat(document.getElementById('sim-juros')?document.getElementById('sim-juros').value:10.5)||10.5;
  var sis=document.getElementById('sim-sis')?document.getElementById('sim-sis').value:'PRICE';
  var renda=limpaNumeroBR(document.getElementById('sim-renda')?document.getElementById('sim-renda').value:0)||0;
  var el=document.getElementById('sim-resultado');if(!el)return;
  if(val<=0){el.style.display='block';el.innerHTML='⚠️ Informe o valor do imóvel.';return;}
  var fin2=Math.max(0,val-ent);var im=(jaa/100)/12;var out='';
  var brl2=function(n){return 'R$ '+n.toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2});};
  out+='<strong>Valor financiado:</strong> '+brl2(fin2)+'<br><strong>Prazo:</strong> '+prazo+' meses · <strong>Juros:</strong> '+jaa.toFixed(2)+'% a.a.<br><br>';
  if(fin2===0){out+='✅ Pagamento à vista.';}
  else if(sis==='PRICE'){var p=im===0?fin2/prazo:fin2*im/(1-Math.pow(1+im,-prazo));out+='<strong>Sistema PRICE</strong><br>Parcela: <strong style="color:#0053a6">'+brl2(p)+'</strong>';if(renda>0)out+='<br>Comprometimento: <strong>'+((p/renda)*100).toFixed(1)+'%</strong>';else out+='<br>Renda mínima (30%): <strong>'+brl2(p/0.3)+'</strong>';}
  else{var amort=fin2/prazo;var p1=amort+fin2*im;var pN=amort+amort*im;out+='<strong>Sistema SAC</strong><br>1ª parcela: <strong style="color:#0053a6">'+brl2(p1)+'</strong> · Última: <strong>'+brl2(pN)+'</strong>';if(renda>0)out+='<br>Comprometimento inicial: <strong>'+((p1/renda)*100).toFixed(1)+'%</strong>';else out+='<br>Renda mínima (30%): <strong>'+brl2(p1/0.3)+'</strong>';}
  el.style.display='block';el.innerHTML=out;
}

function rodarSim2(){
  var val=limpaNumeroBR(document.getElementById('sim-val2').value)||0;
  var ent=limpaNumeroBR(document.getElementById('sim-ent2').value)||0;
  var prazo=parseInt(document.getElementById('sim-prazo2').value)||360;
  var jaa=parseFloat(document.getElementById('sim-juros2').value)||10.5;
  var sis=document.getElementById('sim-sis2').value;
  var el=document.getElementById('sim-resultado2');
  if(val<=0){el.style.display='block';el.innerHTML='⚠️ Informe o valor.';return;}
  var fin2=Math.max(0,val-ent);var im=(jaa/100)/12;var out='';
  var brl2=function(n){return 'R$ '+n.toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2});};
  out+='<strong>Financiado:</strong> '+brl2(fin2)+'<br>';
  if(fin2===0){out+='✅ À vista.';}
  else if(sis==='PRICE'){var p=im===0?fin2/prazo:fin2*im/(1-Math.pow(1+im,-prazo));out+='Parcela: <strong style="color:#0053a6">'+brl2(p)+'</strong>';}
  else{var amort=fin2/prazo;var p1=amort+fin2*im;out+='1ª parcela: <strong style="color:#0053a6">'+brl2(p1)+'</strong>';}
  el.style.display='block';el.innerHTML=out;
}
</script>

<script>
  function forcarModalidadeCorreta(){
    const tagMod=document.getElementById('tag-mod')||document.querySelector('.tag-mod-det');
    const desc=(document.getElementById('det-descricao')?.innerText||'').toLowerCase();
    const modTexto=(tagMod?.innerText||'').toLowerCase();
    if(modTexto.includes('direta')||desc.includes('venda direta online')||desc.includes('compra direta')){
      if(tagMod){tagMod.innerText='Compra Direta';tagMod.style.backgroundColor='#16a34a';tagMod.style.color='#ffffff';}
      const crono=document.getElementById('countdown')||document.querySelector('.cronometro-box');
      if(crono)crono.style.display='none';
      const lbl=document.querySelector('.label-disputa');
      if(lbl)lbl.innerText='Disponível para Proposta (Sem Disputa)';
    }
  }
  window.addEventListener('load',()=>{forcarModalidadeCorreta();setTimeout(forcarModalidadeCorreta,500);setTimeout(forcarModalidadeCorreta,1500);});
</script>
<script>
(function(){
  var HDN = '<?= esc($hdn) ?>';
  var CHAVE_FAV = 'arremate_favs';

  function getFavs(){ return JSON.parse(localStorage.getItem(CHAVE_FAV)||'[]'); }
  function saveFavs(arr){ localStorage.setItem(CHAVE_FAV, JSON.stringify(arr)); }

  function atualizarBtnFav(btn, isFav){
    btn.textContent = isFav ? '❤️' : '🤍';
    btn.classList.toggle('is-fav', isFav);
    btn.title = isFav ? 'Remover dos favoritos' : 'Favoritar imóvel';
  }

  window.toggleFavMobile = function(){
    var btn = document.getElementById('btn-fav-mobile');
    if (!btn) return;
    var favs = getFavs();
    var idx  = favs.indexOf(HDN);
    if (idx === -1) { favs.push(HDN); }
    else            { favs.splice(idx, 1); }
    saveFavs(favs);
    atualizarBtnFav(btn, favs.indexOf(HDN) !== -1);
  };

  window.compartilharImovel = function(){
    var url   = window.location.href;
    var title = document.title;
    if (navigator.share) {
      navigator.share({ title: title, url: url }).catch(function(){});
    } else {
      navigator.clipboard.writeText(url).then(function(){
        var btn = document.getElementById('btn-share-mobile');
        if (btn) {
          var orig = btn.innerHTML;
          btn.innerHTML = '✅';
          setTimeout(function(){ btn.innerHTML = orig; }, 2000);
        }
      });
    }
  };

  // Inicializa estado do botão de favorito ao carregar
  document.addEventListener('DOMContentLoaded', function(){
    var btn = document.getElementById('btn-fav-mobile');
    if (btn && HDN) atualizarBtnFav(btn, getFavs().indexOf(HDN) !== -1);
  });
})();
</script>
<?php include __DIR__ . '/cookie-banner.php'; ?>
<script src="logo-fit.js"></script>
</body>
</html>
