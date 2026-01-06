# 📦 INSTALACIÓN DE DEPENDENCIAS

## 🚀 PASO 1: INSTALAR DEPENDENCIAS DEL BACKEND

```bash
cd backend
npm install express mysql2 pg jsonwebtoken bcryptjs dotenv cors helmet express-rate-limit nodemailer handlebars web-push multer
npm install -D nodemon
```

## 🎨 PASO 2: INICIALIZAR FRONTEND

```bash
cd frontend
npx create-react-app . --template minimal
```

## 📚 PASO 3: INSTALAR DEPENDENCIAS DEL FRONTEND

```bash
cd frontend
npm install react-router-dom axios sweetalert2 workbox-webpack-plugin react-toastify @mui/material @emotion/react @emotion/styled @mui/icons-material react-avatar
```

## ✅ PASO 4: CONFIGURAR VARIABLES DE ENTORNO

### Backend:
```bash
cd backend
copy .env.example .env
# Editar .env con tus credenciales
```

### Frontend:
```bash
cd frontend
copy .env.example .env
# Editar .env con tus configuraciones
```

## 🧪 PASO 5: PROBAR INSTALACIÓN

### Backend:
```bash
cd backend
npm run kill
npm run dev
```

### Frontend:
```bash
cd frontend
npm start
```

---

**¡Listo! Ahora puedes empezar a desarrollar.** 🚀

