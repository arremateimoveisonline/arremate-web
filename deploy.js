/**
 * deploy.js — Sobe arquivos para VPS via SFTP + configura cron
 */
const { Client } = require('./node_modules/ssh2');
const fs = require('fs');
const path = require('path');

const HOST = 'lcmcreativestudio.vps-kinghost.net';
const USER = 'root';
const PASS = 'M@lu1710';
const REMOTE_DIR = '/var/www/arremate-br';
const LOCAL_DIR  = 'C:/xampp/htdocs/arremate-br';

// Arquivos a enviar (excluímos arquivos apenas locais/XAMPP)
const FILES = [
  'index.php',
  'imovel.php',
  'resultados.html',
  'favoritos.html',
  'blog.html',
  'blog-como-comprar-imovel-caixa.html',
  'blog-fgts-imovel-caixa.html',
  'blog-filtrar-maior-desconto.html',
  'blog-imovel-ocupado-caixa.html',
  'blog-iptu-condominio-caixa.html',
  'blog-leilao-licitacao-venda-direta.html',
  'imovel-chips.js',
  'home-preco-mask.js',
  'caixa-hdnimovel.js',
  'caixa-foto.php',
  'caixa-scrape-detalhe.php',
  'scraper_caixa.php',
  'privacidade.html',
  'simulador-de-financiamento.php',
  'simulador-de-imovel-caixa.php',
  'api.php',
  'cookie-banner.php',
  'fgts-batch-scraper.php',
  'logo-fit.js',
  'caixa-detail-scraper.js',
  'caixa-csv-downloader.js',
];

function sshExec(conn, cmd) {
  return new Promise((resolve, reject) => {
    let out = '', err = '';
    conn.exec(cmd, (e, stream) => {
      if (e) return reject(e);
      stream.on('data', d => { out += d; process.stdout.write(d.toString()); });
      stream.stderr.on('data', d => { err += d; process.stderr.write(d.toString()); });
      stream.on('close', (code) => resolve({ code, out, err }));
    });
  });
}

function sftpPut(sftp, localPath, remotePath) {
  return new Promise((resolve, reject) => {
    sftp.fastPut(localPath, remotePath, {}, (err) => {
      if (err) reject(err); else resolve();
    });
  });
}

function getSftp(conn) {
  return new Promise((resolve, reject) => {
    conn.sftp((err, sftp) => { if (err) reject(err); else resolve(sftp); });
  });
}

async function main() {
  const conn = new Client();
  await new Promise((resolve, reject) => {
    conn.on('ready', resolve).on('error', reject)
      .connect({ host: HOST, port: 22, username: USER, password: PASS, readyTimeout: 15000 });
  });

  console.log('\n✅ Conectado à VPS\n');

  // 1. Upload dos arquivos
  const sftp = await getSftp(conn);
  console.log('📤 Enviando arquivos...');
  for (const file of FILES) {
    const local = path.join(LOCAL_DIR, file).replace(/\//g, '\\');
    const remote = REMOTE_DIR + '/' + file;
    if (!fs.existsSync(local)) { console.log(`  ⚠️  ${file} não encontrado localmente, pulando`); continue; }
    try {
      await sftpPut(sftp, local, remote);
      console.log(`  ✅ ${file}`);
    } catch(e) {
      console.log(`  ❌ ${file}: ${e.message}`);
    }
  }

  // 2. Permissões
  console.log('\n🔧 Ajustando permissões...');
  await sshExec(conn, `chown -R www-data:www-data ${REMOTE_DIR}/ && chmod -R 644 ${REMOTE_DIR}/*.php ${REMOTE_DIR}/*.html ${REMOTE_DIR}/*.js 2>/dev/null; chmod 755 ${REMOTE_DIR}/cron_atualizar.sh 2>/dev/null`);

  // 3. Cron robusto
  console.log('\n⏰ Configurando crons (06:00 e 18:00)...');
  const cronScript = `#!/bin/bash
# /var/www/arremate-br/cron_atualizar.sh
# Atualização diária: 06:00 e 18:00
set -euo pipefail

LOG="/var/log/arremate_cron.log"
LOCK="/tmp/arremate_cron.lock"
DB="/var/www/dados/imoveis.db"
PHP="/usr/bin/php"
SCRAPER="/var/www/arremate-br/scraper_caixa.php"

# Lock para evitar execuções simultâneas
exec 9>"\${LOCK}"
flock -n 9 || { echo "[\$(date)] SKIP: outra execução em andamento" >> "\$LOG"; exit 0; }

echo "" >> "\$LOG"
echo "=== [\$(date '+%Y-%m-%d %H:%M:%S')] INÍCIO ===========" >> "\$LOG"

# Backup do banco antes de atualizar
if [ -f "\$DB" ] && [ -s "\$DB" ]; then
  cp "\$DB" "\${DB}.bak"
  echo "[\$(date '+%H:%M:%S')] Backup criado" >> "\$LOG"
fi

# Download CSVs + importação SQLite (todos os estados)
echo "[\$(date '+%H:%M:%S')] Iniciando scraper..." >> "\$LOG"
if \$PHP "\$SCRAPER" --csv-only >> "\$LOG" 2>&1; then
  echo "[\$(date '+%H:%M:%S')] Scraper OK" >> "\$LOG"
  # Verifica se banco tem dados
  COUNT=\$(sqlite3 "\$DB" "SELECT COUNT(*) FROM imoveis;" 2>/dev/null || echo 0)
  echo "[\$(date '+%H:%M:%S')] Imóveis no banco: \$COUNT" >> "\$LOG"
  if [ "\$COUNT" -lt 100 ]; then
    echo "[\$(date '+%H:%M:%S')] ALERTA: banco com menos de 100 imóveis! Restaurando backup..." >> "\$LOG"
    [ -f "\${DB}.bak" ] && cp "\${DB}.bak" "\$DB"
  fi
else
  echo "[\$(date '+%H:%M:%S')] ERRO no scraper — restaurando backup" >> "\$LOG"
  [ -f "\${DB}.bak" ] && cp "\${DB}.bak" "\$DB"
fi

# Permissões
chown www-data:www-data "\$DB" 2>/dev/null || true
chmod 664 "\$DB" 2>/dev/null || true

# Limpeza de fotos antigas em cache (mais de 7 dias)
find /tmp/arremate-fotos -name "*.jpg" -mtime +7 -delete 2>/dev/null || true

# Rotação do log (mantém 5MB)
if [ -f "\$LOG" ] && [ \$(stat -c%s "\$LOG") -gt 5242880 ]; then
  mv "\$LOG" "\${LOG}.1"
  echo "[\$(date '+%H:%M:%S')] Log rotacionado" > "\$LOG"
fi

echo "=== [\$(date '+%Y-%m-%d %H:%M:%S')] FIM =============" >> "\$LOG"
`;

  // Escreve o script na VPS
  const escapedScript = cronScript.replace(/'/g, "'\\''");
  await sshExec(conn, `cat > /var/www/arremate-br/cron_atualizar.sh << 'ENDOFSCRIPT'\n${cronScript}\nENDOFSCRIPT`);
  await sshExec(conn, 'chmod +x /var/www/arremate-br/cron_atualizar.sh');

  // Configura crontab com os 2 horários
  const crontabContent = `# Certbot
0 12 * * * /usr/bin/certbot renew --quiet
# Arremate — atualização 06:00
0 6 * * * /var/www/arremate-br/cron_atualizar.sh
# Arremate — atualização 18:00
0 18 * * * /var/www/arremate-br/cron_atualizar.sh
`;
  await sshExec(conn, `printf '${crontabContent.replace(/'/g,"'\\''").replace(/\n/g,'\\n')}' | crontab -`);
  console.log('✅ Crons configurados (06:00 e 18:00)');

  // 4. Popular banco agora
  console.log('\n🗄️  Populando banco de dados (aguarde ~2 min)...');
  await sshExec(conn, 'php /var/www/arremate-br/scraper_caixa.php --csv-only 2>&1');

  // 5. Verifica resultado
  await sshExec(conn, `sqlite3 /var/www/dados/imoveis.db "SELECT COUNT(*) as total FROM imoveis;" && sqlite3 /var/www/dados/imoveis.db "SELECT uf, COUNT(*) as qt FROM imoveis GROUP BY uf ORDER BY qt DESC LIMIT 10;"`);

  // 6. Permissões finais
  await sshExec(conn, 'chown www-data:www-data /var/www/dados/imoveis.db && chmod 664 /var/www/dados/imoveis.db');

  // 7. Verifica crontab
  console.log('\n📋 Crontab atual:');
  await sshExec(conn, 'crontab -l');

  console.log('\n🎉 Deploy completo!');
  conn.end();
}

main().catch(e => { console.error('ERRO:', e); process.exit(1); });
