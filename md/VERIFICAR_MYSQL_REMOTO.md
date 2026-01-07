# Guía: Verificar Conexión MySQL Remota

## Problema
El servidor MySQL remoto (`89.117.52.9:3306`) no está aceptando conexiones desde tu IP local.

---

## 1. Verificar Conectividad Básica

### Desde Windows (PowerShell):
```powershell
# Verificar si el puerto está abierto
Test-NetConnection -ComputerName 89.117.52.9 -Port 3306
```

### O usando Telnet:
```powershell
telnet 89.117.52.9 3306
```
- Si se conecta: El puerto está abierto
- Si falla: El puerto está cerrado o bloqueado por firewall

---

## 2. Verificar desde el Servidor MySQL

### A. Conectarse al servidor MySQL remoto (SSH):
```bash
ssh root@89.117.52.9
```

### B. Verificar si MySQL está escuchando en todas las interfaces:
```bash
# Verificar configuración de bind-address
grep -i bind-address /etc/mysql/mysql.conf.d/mysqld.cnf
# O
grep -i bind-address /etc/my.cnf
```

**Debe mostrar:**
```
bind-address = 0.0.0.0
```
**NO debe mostrar:**
```
bind-address = 127.0.0.1  # Esto solo permite conexiones locales
```

### C. Si está configurado como `127.0.0.1`, cambiarlo:
```bash
# Editar configuración
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Cambiar:
bind-address = 127.0.0.1
# Por:
bind-address = 0.0.0.0

# Reiniciar MySQL
sudo systemctl restart mysql
```

---

## 3. Verificar Usuario y Permisos

### A. Conectarse a MySQL:
```bash
mysql -u root -p
```

### B. Verificar si el usuario existe y tiene permisos remotos:
```sql
-- Ver usuarios y sus hosts permitidos
SELECT user, host FROM mysql.user WHERE user = 'vanguard';

-- Ver privilegios específicos
SHOW GRANTS FOR 'vanguard'@'%';
```

### C. Crear o modificar usuario para permitir conexiones remotas:
```sql
-- Crear usuario si no existe (o modificar si ya existe)
CREATE USER 'vanguard'@'%' IDENTIFIED BY 'QI_jkA]RsHF_gUDN';

-- Dar permisos completos (o ajustar según necesidad)
GRANT ALL PRIVILEGES ON vanguard.* TO 'vanguard'@'%';

-- O solo permisos de lectura:
GRANT SELECT ON vanguard.* TO 'vanguard'@'%';

-- Aplicar cambios
FLUSH PRIVILEGES;

-- Verificar
SELECT user, host FROM mysql.user WHERE user = 'vanguard';
```

**Nota:** El `%` permite conexiones desde cualquier IP. Para más seguridad, puedes usar tu IP específica:
```sql
CREATE USER 'vanguard'@'TU_IP_LOCAL' IDENTIFIED BY 'QI_jkA]RsHF_gUDN';
GRANT SELECT ON vanguard.* TO 'vanguard'@'TU_IP_LOCAL';
FLUSH PRIVILEGES;
```

---

## 4. Verificar Firewall del Servidor

### A. Verificar si el puerto 3306 está abierto:
```bash
# Ubuntu/Debian (ufw)
sudo ufw status | grep 3306

# Si no está abierto, abrirlo:
sudo ufw allow 3306/tcp
```

### B. Verificar con iptables:
```bash
sudo iptables -L -n | grep 3306

# Si está bloqueado, permitirlo:
sudo iptables -A INPUT -p tcp --dport 3306 -j ACCEPT
sudo iptables-save
```

---

## 5. Verificar desde tu PC Local

### A. Probar conexión directa con cliente MySQL:
```bash
# Si tienes MySQL client instalado
mysql -h 89.117.52.9 -u vanguard -p
# Ingresa la contraseña: QI_jkA]RsHF_gUDN
```

### B. O usar herramienta como MySQL Workbench o DBeaver:
- Host: `89.117.52.9`
- Port: `3306`
- User: `vanguard`
- Password: `QI_jkA]RsHF_gUDN`
- Database: `vanguard` (o el nombre de tu base de datos)

---

## 6. Alternativa: Túnel SSH (Recomendado para Desarrollo)

Si no puedes configurar MySQL para conexiones remotas, usa un túnel SSH:

### En PowerShell:
```powershell
# Instalar OpenSSH si no está instalado (Windows 10+)
# Luego:
ssh -L 3306:localhost:3306 root@89.117.52.9
```

### Luego cambiar `.env` del backend:
```env
MYSQL_HOST=localhost
MYSQL_PORT=3306
MYSQL_USER=vanguard
MYSQL_PASSWORD=QI_jkA]RsHF_gUDN
MYSQL_DATABASE=vanguard
```

**Con esto, el backend se conectará a `localhost:3306` que será redirigido al MySQL remoto a través del túnel SSH.**

---

## 7. Verificar desde Node.js (Prueba Rápida)

Crea un archivo temporal `test-mysql.js`:

```javascript
const mysql = require('mysql2/promise');

async function test() {
  try {
    const connection = await mysql.createConnection({
      host: '89.117.52.9',
      port: 3306,
      user: 'vanguard',
      password: 'QI_jkA]RsHF_gUDN',
      database: 'vanguard'
    });
    
    console.log('✅ Conexión exitosa!');
    const [rows] = await connection.execute('SELECT 1 as test');
    console.log('✅ Query exitosa:', rows);
    await connection.end();
  } catch (error) {
    console.error('❌ Error:', error.message);
    console.error('Código:', error.code);
  }
}

test();
```

Ejecutar:
```bash
node test-mysql.js
```

---

## Resumen de Checklist

- [ ] Verificar conectividad de red: `Test-NetConnection 89.117.52.9 -Port 3306`
- [ ] Verificar `bind-address` en MySQL (debe ser `0.0.0.0`)
- [ ] Verificar usuario tiene permisos desde `%` o tu IP específica
- [ ] Verificar firewall permite puerto 3306
- [ ] Probar conexión directa con cliente MySQL
- [ ] Si todo falla, usar túnel SSH como alternativa temporal

---

## Solución Rápida: Túnel SSH

**Mientras configuras MySQL remotamente, puedes usar túnel SSH:**

1. Abre una terminal y ejecuta:
   ```bash
   ssh -L 3306:localhost:3306 root@89.117.52.9
   ```

2. Deja esa terminal abierta (el túnel debe estar activo)

3. Cambia `backend/.env`:
   ```env
   MYSQL_HOST=localhost
   ```

4. Reinicia el backend (`npm run dev`)

5. ✅ Debería funcionar ahora

