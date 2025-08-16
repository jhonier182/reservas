# 🚀 Sistema de Reservas con Google Calendar

## 📋 Requisitos Previos

- **XAMPP** instalado y funcionando
- **PHP 8.1+** 
- **Composer** instalado
- **Git** instalado

## 🗄️ Configuración de Base de Datos MySQL

### 1. Iniciar XAMPP
```bash
# Abrir XAMPP Control Panel
# Iniciar Apache y MySQL
```

### 2. Crear Base de Datos
```bash
# Conectar a MySQL
mysql -u root

# Crear base de datos
CREATE DATABASE reservas;

# Verificar que se creó
SHOW DATABASES;

# Salir de MySQL
EXIT;
```

### 3. Configurar Variables de Entorno
```bash
# Editar archivo .env
# Asegurarse de que contenga:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=reservas
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Instalar Dependencias PHP
```bash
# Instalar dependencias
composer install

# Si hay problemas de plataforma
composer install --ignore-platform-reqs

# Instalar dependencias de desarrollo
composer install --dev
```

### 5. Limpiar Caché de Configuración
```bash
# Limpiar toda la caché
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Regenerar caché
php artisan config:cache
```

### 6. Ejecutar Migraciones
```bash
# Verificar estado actual
php artisan migrate:status

# Ejecutar migraciones
php artisan migrate

# Si hay problemas, hacer fresh
php artisan migrate:fresh

# Ejecutar seeders (opcional)
php artisan db:seed
```

### 7. Verificar Configuración
```bash
# Verificar conexión a base de datos
php artisan tinker
DB::connection()->getPdo();

# Verificar configuración de base de datos
php artisan config:show database

# Verificar estado de migraciones
php artisan migrate:status

# Verificar rutas
php artisan route:list
```

### 8. Configurar Google OAuth
```bash
# Crear directorio para tokens
mkdir -p storage/app/google/tokens

# Verificar permisos
# Asegurarse de que storage/app sea escribible
```

### 9. Iniciar Servidor
```bash
# Iniciar servidor de desarrollo
php artisan serve

# O usar XAMPP en http://localhost/todolist
```

## 🔧 Comandos de Verificación

### Verificar Versiones
```bash
# Verificar PHP
php --version

# Verificar Composer
composer --version

# Verificar MySQL
mysql --version

# Verificar extensiones PHP
php -m
```

### Verificar Estado de la Aplicación
```bash
# Estado general
php artisan about

# Estado de migraciones
php artisan migrate:status

# Lista de rutas
php artisan route:list

# Configuración de base de datos
php artisan config:show database
```

### Comandos de Mantenimiento
```bash
# Limpiar caché
php artisan optimize:clear

# Limpiar configuración
php artisan config:clear

# Limpiar vistas
php artisan view:clear

# Limpiar rutas
php artisan route:clear

# Regenerar caché
php artisan optimize
```

## 🚨 Solución de Problemas Comunes

### Error: "Database file at path [...] does not exist"
```bash
# 1. Verificar que .env tenga DB_CONNECTION=mysql
# 2. Limpiar caché
php artisan config:clear
php artisan config:cache
# 3. Verificar que MySQL esté corriendo en XAMPP
```

### Error: "Connection refused"
```bash
# 1. Verificar que XAMPP esté corriendo
# 2. Verificar puerto 3306
# 3. Verificar usuario y contraseña
```

### Error: "Table doesn't exist"
```bash
# 1. Ejecutar migraciones
php artisan migrate
# 2. Si hay problemas
php artisan migrate:fresh
```

## 📱 Funcionalidades

- ✅ **Autenticación de usuarios**
- ✅ **Integración con Google Calendar**
- ✅ **Sistema de reservas**
- ✅ **Vista de calendario completa**
- ✅ **Sincronización automática**
- ✅ **Interfaz responsive**

## 🌐 Acceso

- **URL Local**: http://localhost/todolist
- **Servidor Artisan**: http://localhost:8000

## 📞 Soporte

Para problemas técnicos:
1. Verificar logs en `storage/logs/laravel.log`
2. Ejecutar comandos de verificación
3. Revisar configuración de `.env`
4. Verificar que XAMPP esté funcionando

---

**¡Sistema listo para usar! 🎉**
