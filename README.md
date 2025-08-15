
## 🔧 Comandos Útiles

### Instalación de Dependencias

```bash
# Instalar dependencias PHP con Composer
composer install

# Si tienes problemas con la memoria de PHP, usa:
composer install --ignore-platform-reqs

# Para desarrollo, instalar dependencias adicionales:
composer install --dev

# Verificar que todas las dependencias estén instaladas
composer show
```

### Verificar Versiones y Extensiones
```bash
# Verificar versión de PHP (debe ser 8.2+)
php --version

# Verificar versión de Composer
composer --version

# Verificar extensiones PHP necesarias
php -m | grep -E "(curl|json|mbstring|openssl|pdo_mysql)"

# Si falta alguna extensión, instálala:
# Para XAMPP (Windows):
# - Abre php.ini en xampp/php/
# - Descomenta las líneas de extensiones necesarias

# Para Linux/Ubuntu:
sudo apt-get install php8.2-curl php8.2-json php8.2-mbstring php8.2-openssl php8.2-mysql

# Para macOS con Homebrew:
brew install php@8.2
```

### Verificar Base de Datos
```bash
# Verificar que MySQL esté funcionando
mysql --version

# Conectar a MySQL y crear base de datos
mysql -u root -p
CREATE DATABASE todolist CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
SHOW DATABASES;
exit;

# Verificar conexión desde Laravel
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

### Comandos de Verificación Final
```bash
# Verificar que todas las rutas estén registradas
php artisan route:list

# Verificar estado de migraciones
php artisan migrate:status

# Verificar configuración de la aplicación
php artisan config:show

# Verificar que el storage esté configurado
php artisan storage:link

# Limpiar todas las cachés
php artisan optimize:clear

# Verificar que el proyecto esté listo
php artisan about
```





### Desarrollo

  ##prender servidor


php artisan serve  

```bash
# Limpiar caché
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Recargar configuración
php artisan config:cache

# Ver rutas disponibles
php artisan route:list

# Ver estado de migraciones
php artisan migrate:status
```

### Base de Datos
```bash
# Revertir migraciones
php artisan migrate:rollback

# Ejecutar migraciones específicas
php artisan migrate --path=database/migrations/2025_08_15_031809_create_reservations_table.php

# Cargar seeders
php artisan db:seed --class=UserSeeder
```

### Google Calendar
```bash
# Probar conexión
php artisan calendar:test --user="email@gmail.com"

# Ver información del token
php artisan tinker
>>> app('App\Services\GoogleCalendarService')->getTokenInfo('email@gmail.com')
```

### Instalación de Dependencias

```bash
# Instalar dependencias PHP con Composer
composer install

# Si tienes problemas con la memoria de PHP, usa:
composer install --ignore-platform-reqs

# Para desarrollo, instalar dependencias adicionales:
composer install --dev

# Verificar que todas las dependencias estén instaladas
composer show
```

### Verificar Versiones y Extensiones
```bash
# Verificar versión de PHP (debe ser 8.2+)
php --version

# Verificar versión de Composer
composer --version

# Verificar extensiones PHP necesarias
php -m | grep -E "(curl|json|mbstring|openssl|pdo_mysql)"

# Si falta alguna extensión, instálala:
# Para XAMPP (Windows):
# - Abre php.ini en xampp/php/
# - Descomenta las líneas de extensiones necesarias

# Para Linux/Ubuntu:
sudo apt-get install php8.2-curl php8.2-json php8.2-mbstring php8.2-openssl php8.2-mysql

# Para macOS con Homebrew:
brew install php@8.2
```

### Verificar Base de Datos
```bash
# Verificar que MySQL esté funcionando
mysql --version

# Conectar a MySQL y crear base de datos
mysql -u root -p
CREATE DATABASE todolist CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
SHOW DATABASES;
exit;

# Verificar conexión desde Laravel
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

### Comandos de Verificación Final
```bash
# Verificar que todas las rutas estén registradas
php artisan route:list

# Verificar estado de migraciones
php artisan migrate:status

# Verificar configuración de la aplicación
php artisan config:show

# Verificar que el storage esté configurado
php artisan storage:link

# Limpiar todas las cachés
php artisan optimize:clear

# Verificar que el proyecto esté listo
php artisan about
```
