const{Client}=require('./node_modules/ssh2');
const conn=new Client();
conn.on('ready',()=>{
  conn.exec('php -r "echo extension_loaded(\'gd\') ? \'GD:OK\' : \'GD:NO\';"',(e,s)=>{
    if(e){console.error(e);conn.end();return;}
    s.on('data',d=>process.stdout.write(d.toString()));
    s.stderr.on('data',d=>process.stderr.write(d.toString()));
    s.on('close',()=>{
      conn.exec('php -m',(e2,s2)=>{
        if(e2){conn.end();return;}
        let out='';
        s2.on('data',d=>out+=d);
        s2.on('close',()=>{
          console.log('\nMódulos com image/gd:');
          out.split('\n').filter(l=>l.toLowerCase().match(/gd|image|freetype/)).forEach(l=>console.log(' ',l));
          conn.end();
        });
      });
    });
  });
}).on('error',e=>console.error('CONN ERR:',e.message))
.connect({host:'lcmcreativestudio.vps-kinghost.net',port:22,username:'root',password:'M@lu1710',readyTimeout:15000});
