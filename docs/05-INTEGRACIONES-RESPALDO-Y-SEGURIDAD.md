# Integraciones, Procedimiento de Respaldo y Seguridad — Import Hns San Luis

> [!IMPORTANT]
> Este documento detalla las integraciones externas implementadas en la plataforma (**WhatsApp Business**, **Correo Corporativo SMTP**, **Google Analytics 4 Zero-PII** y **Google Search Console**), el protocolo operativo de **Copias de Seguridad (Backup) y Restauración**, y las medidas de cumplimiento de seguridad y protección de datos personales (**Ley N° 29733 — Perú**).

---

## 1. Integración con WhatsApp Business

La integración está centralizada en [`WhatsAppService`](file:///C:/laragon/www/sanluis/app/Services/WhatsAppService.php) y es configurable en tiempo real desde `/admin/comunicaciones`.

### 1.1 Modalidad Actual: Enlaces Codificados Oficiales (`wa.me`)
No requiere pagar licencias de API ni intermediarios externos. Funciona en tres puntos clave de conversión:
1. **Desde la Ficha de Producto (`/catalogo/{slug}`):**
   - Genera un enlace `https://wa.me/519XXXXXXXX?text=...` con el nombre del producto, código SKU exacto, unidad de venta y URL canónica de la ficha técnica.
2. **Desde la Confirmación y Detalle de Cotización (`/cotizacion/confirmacion/{codigo}` y `/admin/cotizaciones/{id}`):**
   - Genera un mensaje prellenado con el código único de referencia `COT-YYYY-NNNNN`, la cantidad de ítems solicitados y el nombre/empresa del solicitante para acelerar la atención comercial.
3. **Botón Flotante Global y Página de Contacto:**
   - Permite iniciar una conversación general de asesoría técnica; puede habilitarse o deshabilitarse desde `/admin/comunicaciones` (`whatsapp_floating_button_enabled`).

### 1.2 Preparación para Futura Integración con WhatsApp Cloud API Oficial (Meta)
- El sistema reserva en `.env` las variables `WHATSAPP_API_ENABLED=false` y `WHATSAPP_API_TOKEN=`.
- El servicio `ActivityLoggerService` ya incluye `whatsapp_api_token` en su lista negra de sanitización (`[REDACTED]`) para que ningún token de acceso se filtre en los registros de auditoría.

---

## 2. Integración de Correo Corporativo (SMTP) y Probador Administrativo

### 2.1 Flujos de Correo Transaccional
La aplicación despacha cuatro tipos de correos mediante clases `Mailable` dedicadas en `app/Mail/`:
1. **[`QuoteReceivedCustomerMail`](file:///C:/laragon/www/sanluis/app/Mail/QuoteReceivedCustomerMail.php):** Confirmación inmediata enviada al cliente con el número de cotización `COT-YYYY-NNNNN` y el desglose de productos/SKUs solicitados.
2. **[`QuoteReceivedTeamMail`](file:///C:/laragon/www/sanluis/app/Mail/QuoteReceivedTeamMail.php):** Alerta interna enviada a `sales_notification_email` (`ventas@importhnssanluis.com`) con los datos del cliente, RUC y enlace directo al expediente en el panel administrativo.
3. **[`QuoteProposalMail`](file:///C:/laragon/www/sanluis/app/Mail/QuoteProposalMail.php):** Respuesta comercial oficial enviada desde el panel con los precios ofertados, condiciones de entrega y el archivo **`Cotizacion-COT-YYYY-NNNNN.pdf`** adjunto.
4. **[`SmtpTestMail`](file:///C:/laragon/www/sanluis/app/Mail/SmtpTestMail.php):** Correo de prueba técnica disparado desde `/admin/comunicaciones`.

### 2.2 Uso del Probador SMTP en `/admin/comunicaciones`
1. Configura en el archivo `.env` del servidor las credenciales de tu proveedor corporativo (`MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME=tls`).
2. Ingresa a `/admin/comunicaciones` con una cuenta de `Administrador`.
3. En la tarjeta **"Probar Configuración de Correo SMTP"**, escribe un correo receptor válido y haz clic en **"Enviar Correo de Prueba"**.
4. Si la conexión es exitosa, recibirás el correo de diagnóstico y el evento quedará registrado en `/admin/actividad`; si el servidor SMTP rechaza las credenciales o el puerto está bloqueado por firewall, el panel mostrará el mensaje de diagnóstico sin interrumpir el sistema.

### 2.3 Recomendaciones DNS Anti-Spam para `importhnssanluis.com`
Para garantizar que las propuestas comerciales en PDF lleguen a la bandeja principal de los clientes corporativos:
- **Registro SPF (`TXT`):** Autorizar al servidor de correo saliente (`v=spf1 include:_spf.google.com ~all` o el proveedor correspondiente).
- **Firma DKIM (`TXT`):** Activar la llave pública DKIM en el panel DNS del dominio.
- **Política DMARC (`TXT` en `_dmarc.importhnssanluis.com`):** `v=DMARC1; p=quarantine; rua=mailto:postmaster@importhnssanluis.com`.

---

## 3. Google Analytics 4 (Zero-PII) y Google Search Console

### 3.1 Activación de Google Analytics 4 (GA4)
1. Crea una propiedad GA4 en Google Analytics para `https://importhnssanluis.com` y copia el **ID de Medición** (formato `G-XXXXXXXXXX`).
2. En el panel administrativo, ve a **Módulo 13: SEO y Analítica Web (`/admin/seo`)**.
3. Pega el ID en el campo `ID de Medición Google Analytics 4`, marca la casilla **Activar Google Analytics 4** y guarda los cambios.
4. El componente [`seo-meta.blade.php`](file:///C:/laragon/www/sanluis/resources/views/components/seo-meta.blade.php) inyectará el script oficial `gtag.js` únicamente cuando `ga4_enabled` sea verdadero y exista un ID válido.

### 3.2 Eventos de Conversión Registrados (Política Zero-PII)
El módulo JavaScript en `resources/js/app.js` (`window.SanLuisAnalytics`) envía los siguientes eventos estandarizados **sin incluir jamás nombres, correos electrónicos, teléfonos ni números de RUC**:
- `view_item`: Visualización de ficha técnica de producto (SKU, nombre de producto, categoría, marca, moneda y precio si es público).
- `search`: Búsqueda técnica ejecutada en el catálogo (`search_term`).
- `begin_quote`: Clic en agregar producto a cotización o iniciar cotización directa (`item_id`, `item_name`, `cta_location`).
- `submit_quote`: Envío exitoso de solicitud de cotización (`quote_reference`, `client_type: b2b_ruc | b2c_particular`, `request_type: single | multiple`, `items_count`).
- `click_whatsapp`: Clic en botón de consulta por WhatsApp (`cta_location`, `item_id`).
- `file_download`: Descarga de ficha técnica PDF (`file_name`, `item_id`).
- `contact_submit`: Envío de formulario de contacto comercial (`inquiry_type`, `client_type`).

### 3.3 Verificación de Google Search Console y Envío de Sitemap
1. Ingresa a [Google Search Console](https://search.google.com/search-console) y añade la propiedad `https://importhnssanluis.com`.
2. Selecciona el método de verificación **Etiqueta HTML** y copia el código de verificación.
3. Pégalo en `/admin/seo` -> **Código de verificación de Google Search Console** y guarda.
4. En Search Console, dirígete a la sección **Sitemaps** y envía la ruta dinámica:
   ```text
   https://importhnssanluis.com/sitemap.xml
   ```
   El sitemap incluye automáticamente todas las páginas institucionales indexables, categorías activas y todos los productos con estado `published` (excluyendo borradores y archivados).

---

## 4. Procedimiento de Copias de Seguridad (Backup) y Restauración

### 4.1 Qué Debe Respaldarse
Para recuperar el 100% de la plataforma ante cualquier contingencia, deben respaldarse tres elementos:
1. **Base de datos MySQL (`sanluis_prod`):** Contiene productos, especificaciones, categorías, cotizaciones, clientes, usuarios, configuración y bitácora de auditoría.
2. **Carpeta de archivos subidos (`storage/app/public/`):** Contiene todas las fotografías del catálogo (`products/images/`) y las fichas técnicas en PDF (`products/documents/`).
3. **Archivo de configuración de entorno (`.env`):** Custodiado en bóveda segura de contraseñas.

### 4.2 Script Automatizado de Respaldo Diario (Linux Producción)
Crea `/usr/local/bin/backup-sanluis.sh` con permisos `chmod 700`:

```bash
#!/usr/bin/env bash
set -euo pipefail

BACKUP_DIR="/var/backups/sanluis"
APP_DIR="/var/www/importhnssanluis"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
DB_NAME="sanluis_prod"
DB_USER="sanluis_user"
DB_PASS="TU_PASSWORD_MYSQL"

mkdir -p "${BACKUP_DIR}/db" "${BACKUP_DIR}/storage"

# 1. Respaldo consistente de MySQL (sin bloquear tablas InnoDB)
mysqldump --single-transaction --routines --triggers --quick \
  -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" \
  | gzip -9 > "${BACKUP_DIR}/db/sanluis_db_${TIMESTAMP}.sql.gz"

# 2. Respaldo comprimido de imágenes y fichas técnicas PDF
tar -czf "${BACKUP_DIR}/storage/sanluis_storage_${TIMESTAMP}.tar.gz" \
  -C "${APP_DIR}/storage/app" public

# 3. Retención local: conservar últimos 14 días y purgar antiguos
find "${BACKUP_DIR}/db" -type f -name "*.sql.gz" -mtime +14 -delete
find "${BACKUP_DIR}/storage" -type f -name "*.tar.gz" -mtime +14 -delete

echo "[OK] Backup completado: ${TIMESTAMP}"
```

Programa su ejecución diaria a las 02:30 AM en el `crontab` de `root`:
```cron
30 2 * * * /usr/local/bin/backup-sanluis.sh >> /var/log/sanluis-backup.log 2>&1
```

### 4.3 Procedimiento Verificado de Restauración (Disaster Recovery)
Para restaurar una copia de seguridad en un servidor nuevo o de contingencia:

```bash
# 1. Colocar la aplicación en modo mantenimiento
cd /var/www/importhnssanluis
php artisan down

# 2. Restaurar la base de datos desde el archivo .sql.gz
gunzip < /var/backups/sanluis/db/sanluis_db_YYYYMMDD_HHMMSS.sql.gz \
  | mysql -u sanluis_user -p sanluis_prod

# 3. Restaurar imágenes y fichas técnicas PDF en storage/app/public
tar -xzf /var/backups/sanluis/storage/sanluis_storage_YYYYMMDD_HHMMSS.tar.gz \
  -C /var/www/importhnssanluis/storage/app/

# 4. Asegurar permisos y enlace simbólico
sudo chown -R deploy:www-data /var/www/importhnssanluis/storage
php artisan storage:link
php artisan cache:clear
php artisan config:cache

# 5. Reactivar el portal
php artisan up
```

---

## 5. Seguridad Aplicativa y Cumplimiento Legal (Ley N° 29733 — Perú)

1. **Protección de Datos Personales (Ley N° 29733 y su Reglamento):**
   - Todos los formularios públicos (`/cotizacion` y `/contacto`) exigen la aceptación explícita de la Política de Privacidad (`privacy_accepted`) y almacenan la marca de tiempo exacta del consentimiento en `privacy_accepted_at` como evidencia auditable.
   - Los titulares de datos pueden ejercer sus derechos ARCO (Acceso, Rectificación, Cancelación y Oposición) a través de los canales indicados en `/politica-de-privacidad`.
2. **Defensa en Profundidad en el Servidor:**
   - **Consultas Parametrizadas (PDO/Eloquent):** Prevención contra inyección SQL en todos los filtros del catálogo y buscador.
   - **Escape Automático en Blade (`{{ }}`):** Prevención contra Cross-Site Scripting (XSS).
   - **Protección CSRF:** Tokens obligatorios en todos los formularios `POST`, `PUT`, `PATCH` y `DELETE`.
   - **Validación Estricta de Archivos:** Solo se permiten extensiones y tipos MIME seguros (`jpg,jpeg,png,webp` para imágenes; `pdf` para fichas técnicas; `csv,txt` para importación), rechazando cualquier archivo ejecutable (`.php`, `.exe`, `.sh`).
   - **Rate Limiting y Honeypot:** Límite de 5 intentos por minuto por IP/correo en inicio de sesión, envío de cotizaciones y formulario de contacto, más campo trampa `website_hp` contra bots.
   - **Sanitización de Auditoría:** [`ActivityLoggerService`](file:///C:/laragon/www/sanluis/app/Services/ActivityLoggerService.php) enmascara automáticamente con `[REDACTED]` cualquier campo que contenga `password`, `token`, `secret` o `api_key`.

---

## 6. Sistema de Detección y Prevención de Malware (WAF + Escáner + Cuarentena)

La plataforma incorpora un sistema multicapa de detección y prevención de malware en tiempo real y mediante auditoría programada:

### 6.1 Inspección Binaria y Heurística en Tiempo Real (`MalwareScannerService`)
- **Ubicación:** [`app/Services/MalwareScannerService.php`](file:///C:/laragon/www/sanluis/app/Services/MalwareScannerService.php).
- **Vectores bloqueados en subidas de archivos (`inspectUploadedFile`):**
  1. **Doble extensión y archivos de configuración:** Bloquea cualquier archivo que contenga segmentos `.php`, `.phtml`, `.phar`, `.exe`, `.bat`, `.cmd`, `.sh`, `.vbs`, `.jsp`, `.asp`, `.cgi`, `.htaccess` o `.user.ini` en cualquier parte del nombre (ej. `bisagra.php.jpg`).
  2. **Ejecutables binarios camuflados:** Detecta cabeceras `MZ` (`\x4D\x5A`, ejecutables Windows PE/EXE/DLL) y `\x7FELF` (binarios Linux) renombrados como imágenes o documentos.
  3. **Validación estricta de Magic Bytes:** Verifica que la firma binaria coincida con la extensión declarada (`\xFF\xD8\xFF` para JPEG, `\x89PNG\r\n\x1a\n` para PNG, `RIFF....WEBP` para WebP y `%PDF-` para PDF).
  4. **Detección de Webshells y Malware Polyglot:** Analiza el contenido binario/texto de imágenes, PDFs y CSVs buscando firmas de ejecución remota (`<?php`, `<?=`, `<script`, `eval(`, `base64_decode(`, `gzinflate(`, `str_rot13(`, `shell_exec(`, `passthru(`, `system(`, `proc_open(`, `popen(`, `pcntl_exec(`, `assert(`, `$_GET[`, `$_POST[`, `$_REQUEST[`).
  5. **Prevención de Exploits en PDF:** Inspecciona documentos PDF bloqueando vectores ejecutables embebidos (`/JavaScript`, `/JS`, `/Launch`, `/EmbeddedFiles`, `/RichMedia`, `/SubmitForm`, `/ImportData`).
  6. **Prevención de CSV Formula Injection / DDE Malware:** Bloquea archivos CSV con payloads de ejecución de comandos (`=cmd|`, `+cmd|`, `-cmd|`, `=powershell`, `@SUM(`, `=HYPERLINK(`, `=IMPORTXML(`, `=WEBSERVICE(`) y sanitiza todas las celdas exportadas mediante `sanitizeCsvCell()`.

### 6.2 Firewall de Aplicación Web (WAF) y Cuarentena Automática (`MalwarePreventionMiddleware`)
- **Ubicación:** [`app/Http/Middleware/MalwarePreventionMiddleware.php`](file:///C:/laragon/www/sanluis/app/Http/Middleware/MalwarePreventionMiddleware.php) (activo en el grupo `web` de [`bootstrap/app.php`](file:///C:/laragon/www/sanluis/bootstrap/app.php)).
- **Protección de peticiones HTTP:** Intercepta intentos de *Path Traversal* (`../`, `..\`, `/etc/passwd`, `win.ini`, `boot.ini`), *Null Byte Injection* (`%00`), *Remote Code Execution* (`<?php`, `eval(base64_decode`, `shell_exec(`, `system(`) e inyecciones `<script>` no autorizadas, respondiendo `403 Forbidden` y registrando el evento en la tabla `security_incidents`.
- **Aislamiento en Cuarentena:** Cualquier archivo malicioso interceptado al subir se aísla fuera del directorio público en `storage/app/quarantine/{timestamp}_{sha256}.quarantine` (protegido con `.htaccess` `Require all denied`), registrando su hash `SHA-256` forense.

### 6.3 Endurecimiento de Almacenamiento y Escáner Programado (`sanluis:scan-malware`)
- **Bloqueo de ejecución en Apache:** [`storage/app/public/.htaccess`](file:///C:/laragon/www/sanluis/storage/app/public/.htaccess) deshabilita el motor PHP (`php_flag engine off`) y deniega el acceso a cualquier extensión de script o ejecutable dentro de `storage/app/public`.
- **Comando Artisan:** [`app/Console/Commands/ScanMalwareCommand.php`](file:///C:/laragon/www/sanluis/app/Console/Commands/ScanMalwareCommand.php) (`php artisan sanluis:scan-malware`), programado diariamente a las `03:00 AM` en [`routes/console.php`](file:///C:/laragon/www/sanluis/routes/console.php). Recorre recursivamente `storage/app/public`, detecta cualquier script infiltrado o archivo infectado, lo mueve automáticamente a cuarentena y guarda el reporte en `security_scans`.
- **Centro Administrativo de Seguridad Antimalware (`/admin/seguridad`):** Gestionado por [`SecurityCenterController`](file:///C:/laragon/www/sanluis/app/Http/Controllers/Admin/SecurityCenterController.php), permite visualizar KPIs del escudo WAF, ejecutar escaneos bajo demanda, inspeccionar la ficha forense de cada incidente (`SHA-256`, regla disparada, IP, payload), purgar muestras en cuarentena y marcar incidentes como resueltos.

