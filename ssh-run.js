const { Client } = require('./node_modules/ssh2');
const conn = new Client();
const command = process.argv[2] || 'echo OK && whoami && uname -a';

conn.on('ready', () => {
  conn.exec(command, (err, stream) => {
    if (err) { console.error('EXEC ERR:', err); conn.end(); return; }
    stream.on('data', d => process.stdout.write(d.toString()));
    stream.stderr.on('data', d => process.stderr.write(d.toString()));
    stream.on('close', () => conn.end());
  });
}).on('error', e => { console.error('CONN ERR:', e.message); process.exit(1); })
.connect({ host: 'lcmcreativestudio.vps-kinghost.net', port: 22, username: 'root', password: 'M@lu1710', readyTimeout: 15000 });
