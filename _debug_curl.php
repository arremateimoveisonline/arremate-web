<?php
$jar = '/tmp/arremate_caixa_cookies.txt';
$ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';
$url = 'https://venda-imoveis.caixa.gov.br/listaweb/Lista_imoveis_SP.csv';

// Testa shell_exec
$test = shell_exec('echo SHELL_OK') ?? 'DISABLED';
echo "shell_exec: ".trim($test)."\n";

// Monta e executa o mesmo curl que scraper usa
$jarE = escapeshellarg($jar);
$uaE  = escapeshellarg($ua);
$urlE = escapeshellarg($url);
$sentinel = '__HTTP_CODE__';
$cmd = "curl -s -L --max-redirs 5 --max-time 30 "
     . "-c {$jarE} -b {$jarE} -A {$uaE} "
     . "-H 'Accept: text/html,application/xhtml+xml,*/*;q=0.8' "
     . "-H 'Accept-Language: pt-BR,pt;q=0.9' "
     . "-H 'Connection: keep-alive' "
     . "-w '\\n{$sentinel}%{http_code}' "
     . "{$urlE} 2>/dev/null";

echo "CMD: $cmd\n\n";

$out = (string)shell_exec($cmd);
$pos = strrpos($out, "\n{$sentinel}");
if ($pos !== false) {
    $code = (int)substr($out, $pos + strlen("\n{$sentinel}"));
    $body = substr($out, 0, $pos);
} else {
    $code = 0;
    $body = $out;
}
echo "HTTP: $code\n";
echo "SIZE: ".strlen($body)."\n";
echo "BODY(100): ".substr($body, 0, 100)."\n";
echo "IS_BLOCKED: ".(stripos($body,'radware')!==false||stripos($body,'captcha')!==false||stripos($body,'perfdrive')!==false ? 'SIM':'NAO')."\n";
