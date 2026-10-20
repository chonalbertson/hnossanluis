# Guía de Instalación, Despliegue y Configuración de Servidor — Import Hns San Luis

> [!IMPORTANT]
> Este documento detalla el procedimiento técnico exacto para levantar la plataforma en un entorno de **desarrollo local** (Windows Laragon / Linux / macOS) y para su **despliegue seguro en un servidor de producción** bajo el dominio oficial previsto `https://importhnssanluis.com`.

---

## 1. Requisitos del Servidor y Entorno

### 1.1 Especificaciones de Software
- **Sistema Operativo Recomendado en Producción:** Ubuntu Server 24.04 LTS / Debian 12 (o Windows Server / Laragon en entornos locales).
- **Servidor Web:** Nginx `1.24+` o Apache `2.4+` (con `mod_rewrite` y `mod_headers` habilitados).
- **PHP:** `8.3.x` (FPM en Linux) con las siguientes extensiones obligatorias:
  - `bcmath`, `ctype`, `curl`, `dom`, `exif`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `pdo_sqlite` (para pruebas), `sodium`, `tokenizer`, `xml`, `zip`, `Zend OPcache`.
- **Motor de Base de Datos:** MySQL `8.0+` / `8.4 LTS` o MariaDB `10.11+` con juego de caracteres `utf8mb4` y colación `utf8mb4_unicode_ci`.
- **Gestores de Paquetes:** Composer `2.7+`, Node.js `20.x+` LTS y NPM `10.x+`.
- **Certificado SSL/TLS:** Let's Encrypt (Certbot) o certificado comercial activo con TLS 1.2 / 1.3.

### 1.2 Parámetros Recomendados en `php.ini` (Producción)
Para permitir la carga de fichas técnicas en PDF, catálogos CSV e imágenes de productos sin interrupciones:

```ini
memory_limit = 256M
upload_max_filesize = 16M
post_max_size = 20M
max_execution_time = 60
expose_php = Off
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = 0 ; Recargar php-fpm en cada despliegue
```

---

## 2. Guía Paso a Paso para Desarrollo Local (Windows Laragon)

1. **Preparar el `PATH` en la terminal PowerShell:**
   ```powershell
   $env:PATH = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;C:\laragon\bin\composer;C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin;" + $env:PATH
   ```

2. **Crear la base de datos local en MySQL:**
   ```sql
   CREATE DATABASE IF NOT EXISTS sanluis CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE DATABASE IF NOT EXISTS sanluis_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Instalar dependencias y configurar `.env`:**
   ```powershell
   cd C:\laragon\www\sanluis
   composer install
   npm install
   Copy-Item .env.example .env
   php artisan key:generate
   ```

4. **Ejecutar migraciones, datos semilla y enlace simbólico:**
   ```powershell
   php artisan migrate --seed
   php artisan storage:link
   npm run build
   ```

5. **Ejecutar pruebas automatizadas para verificar integridad:**
   ```powershell
   php artisan test
   ```

---

## 3. Lista Detallada de Variables de Entorno (`.env`)

> [!CAUTION]
> El archivo `.env` **jamás** debe subirse al repositorio Git (está excluido en `.gitignore`). En producción, créalo directamente en el servidor a partir de `.env.example` con permisos restrictivos (`chmod 600 .env`).

| Variable | Ejemplo en Producción | Descripción y Regla de Seguridad |
| :--- | :--- | :--- |
| `APP_NAME` | `"Import Hns San Luis"` | Nombre corporativo usado en correos, títulos y plantillas PDF. |
| `APP_ENV` | `production` | **Obligatorio** `production` en servidor público. |
| `APP_KEY` | `base64:...` | Clave criptográfica generada con `php artisan key:generate`. |
| `APP_DEBUG` | `false` | **Obligatorio** `false` en producción para no exponer trazas de error. |
| `APP_URL` | `https://importhnssanluis.com` | Dominio canónico sin barra final (`/`); usado en Sitemap, JSON-LD y correos. |
| `APP_TIMEZONE` | `America/Lima` | Zona horaria oficial de Perú (`UTC-5`). |
| `APP_LOCALE` | `es` | Idioma predeterminado de mensajes y validaciones. |
| `BCRYPT_ROUNDS` | `12` | Costo de hash de contraseñas para cuentas de usuarios. |
| `LOG_CHANNEL` | `daily` | Rotación diaria de logs en `storage/logs/` para evitar archivos gigantes. |
| `LOG_LEVEL` | `warning` | Nivel de registro en producción (`warning` o `error`). |
| `DB_CONNECTION` | `mysql` | Conector principal de base de datos relacional. |
| `DB_HOST` | `127.0.0.1` | Host del servidor MySQL/MariaDB. |
| `DB_PORT` | `3306` | Puerto de MySQL/MariaDB. |
| `DB_DATABASE` | `sanluis_prod` | Nombre de la base de datos de producción. |
| `DB_USERNAME` | `sanluis_user` | Usuario dedicado de BD (no usar `root` en producción). |
| `DB_PASSWORD` | `"ClaveFuerteAleatoria..."` | Contraseña robusta del usuario MySQL. |
| `SESSION_DRIVER` | `database` | Sesiones almacenadas en la tabla `sessions`. |
| `SESSION_LIFETIME` | `120` | Tiempo de vida de sesión en minutos. |
| `SESSION_ENCRYPT` | `true` | Cifrado de datos de sesión en base de datos. |
| `SESSION_SECURE_COOKIE` | `true` | **Obligatorio** `true` bajo HTTPS para transmitir cookies solo por canal seguro. |
| `FILESYSTEM_DISK` | `public` | Disco predeterminado para imágenes de productos y fichas técnicas PDF. |
| `QUEUE_CONNECTION` | `database` | Cola de trabajos asíncronos respaldada por la tabla `jobs`. |
| `CACHE_STORE` | `database` | Almacenamiento de caché en la tabla `cache` (o `redis` si está disponible). |
| `MAIL_MAILER` | `smtp` | Motor de envío de correos transaccionales. |
| `MAIL_HOST` | `smtp.importhnssanluis.com` | Servidor SMTP corporativo (Google Workspace, Microsoft 365, Amazon SES, cPanel, etc.). |
| `MAIL_PORT` | `587` | Puerto SMTP (`587` con TLS o `465` con SSL). |
| `MAIL_SCHEME` | `tls` | Esquema de cifrado SMTP (`tls` o `ssl`). |
| `MAIL_USERNAME` | `notificaciones@importhnssanluis.com` | Cuenta autenticada en el servidor SMTP. |
| `MAIL_PASSWORD` | `"SecretoSmtp..."` | Contraseña o App Password de la cuenta SMTP. |
| `MAIL_FROM_ADDRESS` | `"ventas@importhnssanluis.com"` | Dirección remitente visible para los clientes. |
| `MAIL_FROM_NAME` | `"Import Hns San Luis"` | Nombre remitente visible en los correos. |
| `MAIL_SALES_NOTIFICATION_TO` | `"ventas@importhnssanluis.com"` | Correo receptor predeterminado de alertas de nuevas cotizaciones. |
| `COMMERCIAL_PRICE_DISPLAY_MODE` | `mixed` | Modo inicial de precios (`show_all`, `hide_all`, `mixed`), editable en el panel. |
| `COMMERCIAL_DEFAULT_CURRENCY` | `PEN` | Moneda predeterminada (`PEN`, `USD`, `EUR`). |
| `COMMERCIAL_QUOTE_VALIDITY_DAYS` | `15` | Vigencia predeterminada en días para las cotizaciones. |
| `WHATSAPP_BUSINESS_NUMBER` | `"519XXXXXXXX"` | Número oficial de WhatsApp Business (solo dígitos, con prefijo `51`). |
| `GA4_MEASUREMENT_ID` | `"G-XXXXXXXXXX"` | ID de medición de Google Analytics 4 (dejar vacío hasta contar con propiedad real). |
| `GA4_ENABLED` | `false` | Cambiar a `true` o activar desde `/admin/seo` cuando se configure el ID real. |
| `GOOGLE_SITE_VERIFICATION` | `""` | Token de verificación HTML de Google Search Console. |

---

## 4. Procedimiento de Despliegue en Servidor de Producción

### 4.1 Estructura de Directorios y Permisos en Linux
Ubica el código en `/var/www/importhnssanluis` y asigna la propiedad al usuario de despliegue (`deploy`) y el grupo del servidor web (`www-data`):

```bash
sudo mkdir -p /var/www/importhnssanluis
cd /var/www/importhnssanluis

# Clonar repositorio en la rama principal
git clone <URL_DEL_REPOSITORIO_PRIVADO> .

# Instalar dependencias de producción (sin paquetes de desarrollo)
composer install --no-dev --optimize-autoloader --no-interaction

# Compilar activos de producción
npm ci
npm run build

# Configurar entorno de producción
cp .env.example .env
nano .env # Configurar APP_ENV=production, APP_DEBUG=false, DB_*, MAIL_*, etc.
php artisan key:generate --force

# Ejecutar migraciones de esquema (NO ejecutar db:seed de prueba en producción)
php artisan migrate --force

# Crear enlace simbólico public/storage -> storage/app/public
php artisan storage:link

# Cachear configuración, rutas y vistas para máximo rendimiento
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ajustar permisos de sistema de archivos
sudo chown -R deploy:www-data /var/www/importhnssanluis
sudo find /var/www/importhnssanluis -type f -exec chmod 644 {} \;
sudo find /var/www/importhnssanluis -type d -exec chmod 755 {} \;
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
sudo chmod 600 /var/www/importhnssanluis/.env
```

> [!WARNING]
> **Creación del Primer Usuario Administrador en Producción:** En lugar de ejecutar el `DatabaseSeeder` completo (que incluye productos de demostración y usuarios de prueba), en producción se debe poblar únicamente la matriz de roles/permisos y crear el usuario administrador real con una contraseña robusta o cambiar de inmediato las contraseñas desde `/admin/usuarios` y eliminar los productos de ejemplo.

---

## 5. Configuración del Servidor Web, Dominio y SSL/HTTPS

El `DocumentRoot` o `root` del servidor web **debe apuntar exclusivamente a la subcarpeta `/public`** (`/var/www/importhnssanluis/public`) para impedir que archivos internos (`.env`, `storage/`, `vendor/`, `database/`) sean accesibles desde internet.

### 5.1 Configuración de Nginx (`/etc/nginx/sites-available/importhnssanluis.conf`)
Incluye redirección canónica de `http://` y `www.` hacia `https://importhnssanluis.com`, cabeceras de seguridad y caché de activos estáticos:

```nginx
# Redirección HTTP -> HTTPS canónico
server {
    listen 80;
    listen [::]:80;
    server_name importhnssanluis.com www.importhnssanluis.com;
    return 301 https://importhnssanluis.com$request_uri;
}

# Redirección www -> sin www en HTTPS
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name www.importhnssanluis.com;

    ssl_certificate /etc/letsencrypt/live/importhnssanluis.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/importhnssanluis.com/privkey.pem;

    return 301 https://importhnssanluis.com$request_uri;
}

# Servidor Principal HTTPS
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name importhnssanluis.com;

    root /var/www/importhnssanluis/public;
    index index.php;

    charset utf-8;
    client_max_body_size 20M;

    # Certificados SSL (Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/importhnssanluis.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/importhnssanluis.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers on;

    # Cabeceras de Seguridad
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Impedir ejecución de scripts PHP dentro de storage público
    location ~* ^/storage/.*\.(php|phar|phtml|sh|exe)$ {
        deny all;
    }

    location = /favicon.ico { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Caché de activos compilados de Vite y fuentes
    location ~* \.(css|js|woff2|woff|svg|jpg|jpeg|png|webp|ico)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }
}
```

### 5.2 Configuración de Apache (`VirtualHost` + `.htaccess`)
Si el servidor utiliza Apache 2.4, el proyecto ya incluye `public/.htaccess` configurado. El `VirtualHost` debe definirse así:

```apache
<VirtualHost *:80>
    ServerName importhnssanluis.com
    ServerAlias www.importhnssanluis.com
    Redirect permanent / https://importhnssanluis.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName importhnssanluis.com
    DocumentRoot /var/www/importhnssanluis/public

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/importhnssanluis.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/importhnssanluis.com/privkey.pem

    <Directory /var/www/importhnssanluis/public>
        AllowOverride All
        Require all granted
    </Directory>

    <Directory /var/www/importhnssanluis/public/storage>
        php_admin_flag engine off
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/sanluis-error.log
    CustomLog ${APACHE_LOG_DIR}/sanluis-access.log combined
</VirtualHost>
```

---

## 6. Colas de Trabajo (`Supervisor`) y Tareas Programadas (`Cron`)

### 6.1 Configuración de Supervisor para `queue:work`
Para procesar tareas en segundo plano de forma resiliente, crea `/etc/supervisor/conf.d/sanluis-worker.conf`:

```ini
[program:sanluis-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/importhnssanluis/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/importhnssanluis/storage/logs/worker.log
stopwaitsecs=3600
```

Activa el worker con:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start sanluis-worker:*
```

### 6.2 Programador de Tareas de Laravel (`Cron`)
Edita el crontab del usuario del servidor web (`sudo crontab -u www-data -e`) y añade:

```cron
* * * * * cd /var/www/importhnssanluis && php artisan schedule:run >> /dev/null 2>&1
```

---

## 7. Script de Actualización Continua (En Futuros Despliegues)

Cada vez que se publique una nueva versión en producción:

```bash
cd /var/www/importhnssanluis
php artisan down --refresh=15
git pull origin main
composer install --no-dev --optimize-autoloader --no-interaction
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan up
```
