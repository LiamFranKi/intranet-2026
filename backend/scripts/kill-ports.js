const { exec } = require('child_process');
const os = require('os');

const ports = [5000, 3000, 5001, 5002]; // Puertos comunes

function killPort(port) {
  return new Promise((resolve, reject) => {
    const platform = os.platform();
    
    let command;
    if (platform === 'win32') {
      // Windows
      command = `netstat -ano | findstr :${port}`;
      exec(command, (error, stdout) => {
        if (stdout) {
          const lines = stdout.trim().split('\n');
          const pids = new Set();
          
          lines.forEach(line => {
            const parts = line.trim().split(/\s+/);
            const pid = parts[parts.length - 1];
            if (pid && !isNaN(pid)) {
              pids.add(pid);
            }
          });
          
          pids.forEach(pid => {
            exec(`taskkill /PID ${pid} /F`, (err) => {
              if (!err) {
                console.log(`✅ Puerto ${port} (PID ${pid}) cerrado`);
              }
            });
          });
        }
        resolve();
      });
    } else {
      // Linux/Mac
      command = `lsof -ti:${port} | xargs kill -9 2>/dev/null || true`;
      exec(command, (error) => {
        if (!error) {
          console.log(`✅ Puerto ${port} cerrado`);
        }
        resolve();
      });
    }
  });
}

async function main() {
  console.log('🔄 Cerrando puertos...\n');
  
  for (const port of ports) {
    await killPort(port);
  }
  
  console.log('\n✅ Proceso completado');
  process.exit(0);
}

main();

