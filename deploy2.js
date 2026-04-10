/**
 * deploy2.js — Upload scraper v4 + cron atualizado + configura crontab 06:00 e 18:05
 */
const { Client } = require('./node_modules/ssh2');
const fs = require('fs');
const path = require('path');

const HOST      = 'lcmcreativestudio.vps-kinghost.net';
const USER      = 'root';
const PASS      = 'M@lu1710';
const REMOTE    = '/var/www/arremate-br';
const LOCAL     = 'C:/xampp/htdocs/arremate-br';

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

function sftpPut(sftp, local, remote) {
  return new Promise((resolve, reject) => {
    sftp.fastPut(local, remote, {}, err => err ? reject(err) : resolve());
  });
}

function getSftp(conn) {
  return new Promise((resolve, reject) => {
    conn.sftp((err, sftp) => err ? reject(err) : resolve(sftp));
  });
}

async function main() {
  const conn = new Client();
  await new Promise((resolve, reject) =>
    conn.on('ready', resolve).on('error', reject)
        .connect({ host: HOST, port: 22, username: USER, password: PASS, readyTimeout: 15000 })
  );
  console.log('✅ Conectado\n');

  const sftp = await getSftp(conn);

  // Arquivos a atualizar nesta rodada
  const files = ['scraper_caixa.php', 'cron_atualizar.sh'];
  for (const f of files) {
    const local  = path.join(LOCAL, f).replace(/\//g, '\\');
    const remote = REMOTE + '/' + f;
    await sftpPut(sftp, local, remote);
    console.log(`✅ ${f}`);
  }

  // Permissões
  await sshExec(conn, `chmod +x ${REMOTE}/cron_atualizar.sh && chmod 644 ${REMOTE}/scraper_caixa.php`);

  // Crontab com 06:00 e 18:05
  const crontab = `# Certbot renovacao SSL
0 12 * * * /usr/bin/certbot renew --quiet
# Arremate — atualizacao 06:00
0 6 * * * /var/www/arremate-br/cron_atualizar.sh
# Arremate — atualizacao 18:05
5 18 * * * /var/www/arremate-br/cron_atualizar.sh
`;
  // Escreve crontab via heredoc
  await sshExec(conn, `crontab - << 'CRONTAB'\n${crontab}CRONTAB`);

  console.log('\n📋 Crontab configurado:');
  await sshExec(conn, 'crontab -l');

  // Verifica banco atual
  console.log('\n🗄️  Banco de dados atual:');
  await sshExec(conn, `sqlite3 /var/www/dados/imoveis.db "SELECT COUNT(*) as imoveis FROM imoveis;" && sqlite3 /var/www/dados/imoveis.db "SELECT uf, COUNT(*) as qt FROM imoveis GROUP BY uf ORDER BY qt DESC LIMIT 10;"`);

  console.log('\n✅ Tudo pronto!');
  conn.end();
}

main().catch(e => { console.error('ERRO:', e); process.exit(1); });
