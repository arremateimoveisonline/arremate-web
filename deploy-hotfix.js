/**
 * deploy-hotfix.js — Sobe arquivos alterados + migra banco na VPS
 */
const { Client } = require('./node_modules/ssh2');
const fs   = require('fs');
const path = require('path');

const HOST  = 'lcmcreativestudio.vps-kinghost.net';
const USER  = 'root';
const PASS  = 'M@lu1710';
const REMOTE = '/var/www/arremate-br';
const LOCAL  = 'C:/xampp/htdocs/arremate-br';

const FILES = [
  'index.php',
  'resultados.html',
  'favoritos.html',
  'imovel.php',
  'imovel-chips.js',
  'caixa-scrape-detalhe.php',
  'scraper_caixa.php',
  'api.php',
  'caixa-detail-scraper.js',
  'blog.html',
  'blog-como-comprar-imovel-caixa.html',
  'blog-fgts-imovel-caixa.html',
  'blog-filtrar-maior-desconto.html',
  'blog-imovel-ocupado-caixa.html',
  'blog-iptu-condominio-caixa.html',
  'blog-leilao-licitacao-venda-direta.html',
  'simulador-de-financiamento.php',
  'simulador-de-imovel-caixa.php',
];

function sftpPut(sftp, local, remote) {
  return new Promise((resolve, reject) => {
    sftp.fastPut(local, remote, {}, err => err ? reject(err) : resolve());
  });
}

function sshExec(conn, cmd) {
  return new Promise((resolve, reject) => {
    let out = '', err = '';
    conn.exec(cmd, (e, stream) => {
      if (e) return reject(e);
      stream.on('data', d => { out += d; process.stdout.write(d.toString()); });
      stream.stderr.on('data', d => { err += d; process.stderr.write(d.toString()); });
      stream.on('close', code => resolve({ code, out, err }));
    });
  });
}

async function main() {
  const conn = new Client();
  await new Promise((resolve, reject) =>
    conn.on('ready', resolve).on('error', reject)
        .connect({ host: HOST, port: 22, username: USER, password: PASS, readyTimeout: 15000 })
  );
  console.log('✅ Conectado à VPS\n');

  const sftp = await new Promise((resolve, reject) =>
    conn.sftp((err, sftp) => err ? reject(err) : resolve(sftp))
  );

  // 1. Upload dos arquivos
  console.log('📤 Enviando arquivos...');
  for (const f of FILES) {
    const local  = path.join(LOCAL, f).replace(/\//g, '\\');
    const remote = REMOTE + '/' + f;
    if (!fs.existsSync(local)) { console.log(`⚠️  ${f} não encontrado, pulando`); continue; }
    try {
      await sftpPut(sftp, local, remote);
      console.log(`  ✅ ${f}`);
    } catch(e) {
      console.log(`  ❌ ${f}: ${e.message}`);
    }
  }

  // 2. Migração do banco — adiciona colunas novas se ainda não existirem
  console.log('\n🗄️  Migrando banco de dados...');
  const migrationCmds = [
    `sqlite3 /var/www/dados/imoveis.db "ALTER TABLE imoveis ADD COLUMN area_privativa REAL DEFAULT 0;" 2>/dev/null && echo "  ✅ area_privativa adicionada" || echo "  ℹ️  area_privativa já existe"`,
    `sqlite3 /var/www/dados/imoveis.db "ALTER TABLE imoveis ADD COLUMN area_total REAL DEFAULT 0;" 2>/dev/null && echo "  ✅ area_total adicionada" || echo "  ℹ️  area_total já existe"`,
    `sqlite3 /var/www/dados/imoveis.db "ALTER TABLE imoveis ADD COLUMN area_terreno REAL DEFAULT 0;" 2>/dev/null && echo "  ✅ area_terreno adicionada" || echo "  ℹ️  area_terreno já existe"`,
    `sqlite3 /var/www/dados/imoveis.db "ALTER TABLE imoveis ADD COLUMN status_caixa TEXT DEFAULT '';" 2>/dev/null && echo "  ✅ status_caixa adicionada" || echo "  ℹ️  status_caixa já existe"`,
    `sqlite3 /var/www/dados/imoveis.db "ALTER TABLE imoveis ADD COLUMN edital_url TEXT DEFAULT '';" 2>/dev/null && echo "  ✅ edital_url adicionada" || echo "  ℹ️  edital_url já existe"`,
  ];
  for (const cmd of migrationCmds) {
    await sshExec(conn, cmd);
  }

  // 3. Verifica banco
  console.log('\n📊 Estado do banco após migração:');
  await sshExec(conn, `sqlite3 /var/www/dados/imoveis.db "SELECT COUNT(*) as total_imoveis FROM imoveis;"`);
  await sshExec(conn, `sqlite3 /var/www/dados/imoveis.db "PRAGMA table_info(imoveis);" | grep -E "area_|status_caixa"`);

  // 4. Permissões
  await sshExec(conn, `chown www-data:www-data /var/www/dados/imoveis.db 2>/dev/null; chmod 664 /var/www/dados/imoveis.db 2>/dev/null; true`);

  conn.end();
  console.log('\n🎉 Deploy concluído!');
}

main().catch(e => { console.error('ERRO:', e); process.exit(1); });
