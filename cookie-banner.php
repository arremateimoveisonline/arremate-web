<!-- Cookie/LGPD Banner -->
<div id="cookie-banner" style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:9999;background:#0f172a;color:#f1f5f9;padding:14px 20px;box-shadow:0 -4px 20px rgba(0,0,0,.35);font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif">
  <div style="max-width:1200px;margin:0 auto;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
    <div style="flex:1;min-width:220px;font-size:.82rem;line-height:1.5;color:#cbd5e1">
      🍪 Usamos cookies essenciais e analíticos para melhorar sua experiência. Ao continuar, você concorda com nossa
      <a href="privacidade.html" style="color:#93c5fd;text-decoration:underline">Política de Privacidade</a>.
    </div>
    <div style="display:flex;gap:8px;flex-shrink:0;align-items:center">
      <button onclick="aceitarCookies()" style="background:#f39200;border:none;border-radius:999px;padding:8px 20px;font-weight:900;font-size:.82rem;color:#3b1f00;cursor:pointer;font-family:inherit;white-space:nowrap">Aceitar</button>
      <button onclick="fecharCookieBanner()" style="background:transparent;border:1px solid #475569;border-radius:999px;padding:7px 16px;font-weight:700;font-size:.82rem;color:#94a3b8;cursor:pointer;font-family:inherit;white-space:nowrap">Fechar</button>
    </div>
  </div>
</div>
<script>
(function(){
  var KEY='arremate_cookie_ok';
  var banner=document.getElementById('cookie-banner');
  if(!banner) return;
  if(!localStorage.getItem(KEY)){
    setTimeout(function(){banner.style.display='block';},800);
  }
  window.aceitarCookies=function(){localStorage.setItem(KEY,'1');banner.style.display='none';};
  window.fecharCookieBanner=function(){banner.style.display='none';};
})();
</script>
