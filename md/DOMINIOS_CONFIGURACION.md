# 🌐 CONFIGURACIÓN DE DOMINIOS - ACLARACIÓN

## 📋 ESTRUCTURA DE DOMINIOS

### vanguardschools.edu.pe
- **DNS apunta a:** VPS de PHP/MySQL (sin dominio directo, solo IP)
- **Ubicación:** VPS separado (NO Hostinger)
- **Contiene:**
  - Sistema PHP completo
  - Base de datos MySQL
  - API PHP (para recibir notas de React)

### vanguardschools.com
- **Pertenece a:** Hostinger
- **Ubicación:** VPS Hostinger (72.60.172.101)
- **Uso:** Dominio principal de Hostinger

### intranet.vanguardschools.com
- **Pertenece a:** Hostinger (subdominio)
- **Ubicación:** VPS Hostinger (72.60.172.101)
- **Carpeta:** `/intranet`
- **Contiene:**
  - Aplicación React (Frontend)
  - API Node.js (Backend)
  - Base de datos PostgreSQL

---

## 🔌 CONEXIONES

### MySQL
- **Host:** mysql.vanguardschools.edu.pe
- **Desde:** VPS Hostinger (React) → VPS MySQL (PHP)
- **Tipo:** Conexión remota (solo lectura)

### PostgreSQL
- **Host:** localhost
- **Ubicación:** VPS Hostinger (mismo servidor que React)
- **Tipo:** Conexión local

### PHP API
- **URL:** https://vanguardschools.edu.pe/api
- **Desde:** VPS Hostinger (React) → VPS PHP (para exportar notas)
- **Tipo:** HTTP/HTTPS

---

## 📝 NOTAS IMPORTANTES

1. **MySQL está en otro VPS:**
   - No tiene dominio directo
   - Se accede por DNS: mysql.vanguardschools.edu.pe
   - React se conecta remotamente (solo lectura)

2. **PostgreSQL está en Hostinger:**
   - Mismo servidor que React
   - Conexión local (localhost)
   - Base de datos: `aula_virtual`

3. **Subdominio intranet:**
   - Pertenece a Hostinger
   - Se configura en el panel de Hostinger
   - Apunta a la carpeta `/intranet`

---

## ✅ CONFIGURACIÓN ACTUAL

### Backend .env
```env
MYSQL_HOST=mysql.vanguardschools.edu.pe  # VPS MySQL (remoto)
POSTGRES_HOST=localhost                   # Hostinger (local)
PHP_API_URL=https://vanguardschools.edu.pe/api  # VPS PHP
FRONTEND_URL=https://intranet.vanguardschools.com
```

### Frontend .env
```env
REACT_APP_API_URL=https://intranet.vanguardschools.com/api
```

---

**Esta configuración es correcta y no afecta el funcionamiento del sistema.** 🌐

