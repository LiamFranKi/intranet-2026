const net = require('net');

function findFreePort(startPort = 5000) {
  return new Promise((resolve, reject) => {
    const server = net.createServer();
    
    server.listen(startPort, () => {
      const port = server.address().port;
      server.close(() => {
        resolve(port);
      });
    });
    
    server.on('error', (err) => {
      if (err.code === 'EADDRINUSE') {
        // Puerto ocupado, probar siguiente
        findFreePort(startPort + 1).then(resolve).catch(reject);
      } else {
        reject(err);
      }
    });
  });
}

// Ejecutar y guardar puerto en archivo
async function main() {
  try {
    const port = await findFreePort(5000);
    const fs = require('fs');
    const path = require('path');
    const portFile = path.join(__dirname, '..', '.port');
    fs.writeFileSync(portFile, port.toString());
    console.log(`✅ Puerto libre encontrado: ${port}`);
    process.exit(0);
  } catch (error) {
    console.error('❌ Error:', error);
    process.exit(1);
  }
}

main();

