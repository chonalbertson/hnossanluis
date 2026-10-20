# Import Hns San Luis — Plataforma Web B2B/B2C, Catálogo Técnico y Sistema de Cotizaciones

Plataforma corporativa, catálogo técnico escalable (+1,000 productos) y motor integral de cotizaciones B2B/B2C desarrollado para **Import Hns San Luis** (dominio previsto: `https://importhnssanluis.com`), orientado al abastecimiento de ferretería industrial, herrajes y accesorios para muebles y suministros para construcción en el mercado peruano.

---

## 1. Características Principales Implementadas

- **Catálogo Técnico Escalable (+1,000 SKUs), Autocompletado y Comparador:**
  - Búsqueda rápida y tolerante por código SKU exacto/parcial, nombre de producto, marca, material, medidas y especificaciones técnicas dinámicas.
  - **Autocompletado Instantáneo (`GET /catalogo/sugerencias`):** Sugerencias en vivo de productos publicados (SKU, nombre, precio/estado, miniatura) y categorías activas.
  - **Comparador Técnico de Productos (`/catalogo/comparar`):** Matriz comparativa lado a lado para hasta 4 productos simultáneos, alineando dinámicamente sus atributos técnicos (`ProductSpecification`) y permitiendo añadir todos a la lista de cotización con un clic.
  - Filtros combinables en servidor por Categoría, Subcategoría, Marca, Estado de Disponibilidad (`Disponible`, `Bajo consulta`, `Sin stock`), Material y Atributos Técnicos (`spec_key` / `spec_value`).
  - Paginación del lado del servidor con carga ansiosa (*Eager Loading*) auditada sin consultas `N+1`.
  - Descarga de Ficha Técnica en PDF por producto (archivo adjunto oficial o generación dinámica con `Dompdf`) y generación automática de miniaturas optimizadas con PHP GD (`ImageThumbnailService`).
- **Política Flexible de Precios y Disponibilidad:**
  - Configuración global (`show_all`, `hide_all`, `mixed`) e individual por producto (`show`, `hide`, `inherit`).
  - Protección estricta: **nunca** muestra `S/ 0.00` ni precios vacíos como precio público real; en productos con precio oculto o bajo consulta presenta el llamado a la acción *"Solicitar cotización"*.
- **Motor de Cotizaciones (Individual, Carrito Múltiple y Seguimiento Público):**
  - Solicitud directa desde cualquier producto o armado de lista/carrito de cotización múltiple por proyecto (sin obligar al registro previo).
  - **Seguimiento Público de Cotizaciones (`GET /cotizacion/seguimiento`):** Consulta en tiempo real del estado de la solicitud y descarga segura del PDF oficial (`/cotizacion/seguimiento/{reference_code}/pdf`) validando coincidencia estricta de `reference_code` + `contact_email`.
  - Validación de datos de contacto, RUC peruano opcional (exactamente 11 dígitos numéricos cuando se ingresa), aceptación de Política de Privacidad (Ley N° 29733), protección *Honeypot* y *Rate Limiting*.
  - Generación de código correlativo único (`COT-YYYY-NNNNN`), preservación histórica de ítems (*snapshots* de SKU, nombre y unidad de venta) y trazabilidad de estados.
  - Envío de notificaciones transaccionales por correo al cliente y al equipo de ventas, más enlace directo para continuar la atención por **WhatsApp Business**.
- **Panel Administrativo Completo con RBAC Real y Operaciones:**
  - Control de acceso basado en roles y permisos en servidor (`Administrador`, `Vendedor`, `Editor de catálogo`, `Supervisor`).
  - Módulos de: Dashboard con KPIs reales, Productos, Categorías/Subcategorías, Marcas, Gestor de Medios y Fichas PDF, Importación/Exportación Masiva CSV con Vista Previa y Reporte de Errores, Solicitudes de Cotización (incluyendo **Creación Manual / WhatsApp desde el panel** en `/admin/cotizaciones/crear`, generación de propuesta formal en PDF e idempotencia de envío), **Bandeja de Mensajes de Contacto (`/admin/mensajes-contacto`)**, Clientes B2B, Usuarios/Roles, Contenido de Páginas, Configuración Comercial, Comunicaciones (WhatsApp + Probador SMTP), **Centro de Copias de Seguridad (`/admin/respaldos`)**, SEO/GA4, Testimonios Verificados, Registro de Actividad (*Activity Log* con sanitización de secretos) e Informes Comerciales exportables.
- **SEO Técnico, Cabeceras de Seguridad, Analítica GA4 (Zero-PII) y Cumplimiento Legal Peruano:**
  - `/sitemap.xml` y `/robots.txt` dinámicos, URLs canónicas, metadatos Open Graph y datos estructurados JSON-LD (`HardwareStore`, `WebSite`, `BreadcrumbList`, `Product`).
  - Cabeceras HTTP de endurecimiento (`SecurityHeadersMiddleware`: `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy`, `Permissions-Policy`).
  - Eventos de conversión GA4 en cliente (`view_item`, `search`, `begin_quote`, `submit_quote`, `click_whatsapp`, `file_download`, `contact_submit`) sin transmitir datos personales (Zero-PII).
  - 4 páginas legales adaptadas al marco normativo peruano (**Ley N° 29733 — Ley de Protección de Datos Personales**).

---

## 2. Stack Tecnológico Verificado

| Capa | Tecnología | Versión Verificada |
| :--- | :--- | :--- |
| **Lenguaje Backend** | PHP | `8.3.30` |
| **Framework Web** | Laravel Framework | `13.37.0` |
| **Base de Datos Principal** | MySQL / MariaDB (con soporte SQLite en testing) | `MySQL 8.4.3` |
| **Motor de Plantillas y UI** | Laravel Blade + Tailwind CSS v4 + Vanilla JS | `Vite 8.x / Tailwind 4.x` |
| **Generación de PDFs** | Dompdf (`dompdf/dompdf`) | `3.1.x` |
| **Suite de Pruebas** | PHPUnit | `12.5.40` |

---

## 3. Requisitos del Sistema

- **PHP >= 8.3** con extensiones: `bcmath`, `ctype`, `curl`, `dom`, `exif`, `fileinfo`, `gd`, `intl`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `pdo_sqlite`, `tokenizer`, `xml`, `zip`.
- **Composer >= 2.7**
- **MySQL >= 8.0** o **MariaDB >= 10.6** (codificación `utf8mb4_unicode_ci`).
- **Node.js >= 20.x** y **NPM >= 10.x** (para compilación de activos frontend con Vite).

---

## 4. Instalación y Ejecución en Entorno Local

> [!TIP]
> **Nota para Windows Laragon:** Si ejecutas comandos desde PowerShell, asegúrate de anteponer los binarios de Laragon al `PATH`:
> ```powershell
> $env:PATH = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;C:\laragon\bin\composer;C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin;" + $env:PATH
> ```

1. **Clonar o ubicarse en el directorio del proyecto:**
   ```bash
   cd C:\laragon\www\sanluis
   ```

2. **Instalar dependencias de PHP y Node.js:**
   ```bash
   composer install
   npm install
   ```

3. **Configurar variables de entorno:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Ajusta las credenciales de base de datos en `.env` (`DB_DATABASE=sanluis`, `DB_USERNAME=root`, `DB_PASSWORD=`).

4. **Ejecutar migraciones y poblar datos iniciales de demostración:**
   ```bash
   php artisan migrate --seed
   ```

5. **Crear el enlace simbólico de almacenamiento público y compilar activos:**
   ```bash
   php artisan storage:link
   npm run build
   ```

6. **(Opcional) Generar catálogo de escala real (+1,020 productos, +3,500 especificaciones e ilustraciones CAD SVG) y comandos operativos y antimalware:**
   ```bash
   php artisan sanluis:generate-catalog --count=1020
   php artisan sanluis:scan-malware
   php artisan sanluis:backup --keep=7
   php artisan sanluis:prune-data --days=365
   ```

7. **Iniciar el servidor de desarrollo (o acceder directamente a `https://sanluis.test/` en Laragon):**
   ```bash
   php artisan serve
   ```

---

## 5. Usuarios de Prueba (Únicamente para Entorno Local / QA)

> [!CAUTION]
> **Advertencia de Seguridad:** Estas cuentas pertenecen exclusivamente al `DatabaseSeeder` de desarrollo local y pruebas. **Nunca** deben mantenerse con estas contraseñas en un servidor de producción.

| Rol en el Sistema | Correo Electrónico | Contraseña Local | Permisos Principales |
| :--- | :--- | :--- | :--- |
| **Administrador General (`admin`)** | `admin@importhnssanluis.com` | `SanLuis2026!Admin` | Acceso total a todos los módulos, centro de seguridad antimalware, respaldos, usuarios, SEO y configuración. |
| **Vendedor / Ejecutivo (`seller`)** | `ventas@importhnssanluis.com` | `SanLuis2026!Seller` | Gestión, creación manual y respuesta de cotizaciones, mensajes de contacto y clientes. |
| **Editor de Catálogo (`catalog_editor`)** | `catalogo@importhnssanluis.com` | `SanLuis2026!Editor` | Productos, categorías, marcas, archivos PDF/imágenes e importación CSV. |
| **Supervisor (`supervisor`)** | `supervisor@importhnssanluis.com` | `SanLuis2026!Super` | Solo lectura de dashboard, catálogo, cotizaciones, reportes y auditoría. |
| **Cliente Registrado B2B** | `compras@constructoraandina.pe` | `Cliente2026!B2B` | Acceso a `/mi-cuenta` (bloqueado con `403 Forbidden` en `/admin`). |

---

## 6. Ejecución de Pruebas Automatizadas (`40 tests`, `503 assertions`)

El proyecto incluye una batería completa de **40 pruebas automatizadas** (`Feature` y `Unit` con **503 aserciones**) que validan el catálogo, autocompletado, comparador técnico, importación CSV, motor de cotizaciones, seguimiento público, bandeja de mensajes de contacto, centro de respaldos, sistema de detección y prevención de malware (WAF, *Magic Bytes*, anti-webshell, anti-PDF exploit, anti-CSV DDE y cuarentena), comandos Artisan, miniaturas GD, cabeceras de seguridad, RBAC por rol, páginas legales, SEO y rendimiento sin consultas `N+1`:

```bash
php artisan test
```

Para ver el detalle legible por caso de prueba:

```bash
./vendor/bin/phpunit --testdox
```

---

## 7. Estructura del Proyecto

```text
C:\laragon\www\sanluis\
├── app/
│   ├── Console/
│   │   └── Commands/           # BackupPlatformCommand, GenerateMassiveCatalogCommand, PruneExpiredDataCommand, ScanMalwareCommand
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Controladores de los módulos administrativos (incluye SecurityCenter, ContactMessage y Backup)
│   │   │   ├── Auth/           # Autenticación, registro, recuperación y perfil
│   │   │   └── Public/         # Inicio, Catálogo, Sugerencias, Comparador, Ficha, Cotizador, Seguimiento y SEO
│   │   └── Middleware/         # EnsureUserHasRole, EnsureUserHasPermission, SecurityHeadersMiddleware, MalwarePreventionMiddleware
│   ├── Mail/                   # Correos transaccionales (Cliente, Equipo Interno, Propuesta PDF, Test SMTP)
│   ├── Models/                 # Modelos Eloquent (incluye SecurityIncident y SecurityScan)
│   ├── Policies/               # Políticas de autorización (ProductPolicy, QuotePolicy, UserPolicy)
│   └── Services/               # Lógica de negocio desacoplada:
│       ├── ActivityLoggerService.php
│       ├── CatalogSearchService.php
│       ├── ImageThumbnailService.php
│       ├── MalwareScannerService.php
│       ├── ProductImportExportService.php
│       ├── QuotePdfService.php
│       ├── QuoteService.php
│       └── WhatsAppService.php
├── database/
│   ├── migrations/             # Esquema relacional completo con claves foráneas e índices compuestos
│   └── seeders/                # DatabaseSeeder y MassiveCatalogSeeder (+1,020 productos y SVGs técnicos)
├── docs/                       # Documentación técnica, operativa y de producción completa
├── public/                     # Punto de entrada web y activos compilados (build/)
├── resources/
│   ├── css/ & js/              # Estilos Tailwind CSS y módulos JS (buscador en vivo, comparador, carrito, GA4)
│   └── views/                  # Plantillas Blade (layouts, components, public, admin, emails, pdf)
├── routes/
│   ├── web.php                 # Rutas públicas y de autenticación
│   ├── admin.php               # Rutas administrativas protegidas por RBAC
│   └── console.php             # Tareas programadas (sanluis:backup diario y sanluis:prune-data semanal)
└── tests/
    ├── Feature/                # CatalogTest, QuoteSystemTest, SecurityAndRbacTest, PublicPagesAndSeoTest, Wave2FeaturesTest
    └── Unit/                   # CoreDomainUnitTest, ExampleTest
```

---

## 8. Índice de Documentación (`docs/`)

Toda la documentación arquitectónica, de diseño, despliegue, operación e informes de calidad se encuentra en la carpeta [`docs/`](file:///C:/laragon/www/sanluis/docs):

1. [**01 — Arquitectura y Planificación Maestro**](file:///C:/laragon/www/sanluis/docs/01-ARQUITECTURA-Y-PLANIFICACION.md): Requisitos confirmados, modelo entidad-relación, decisiones de arquitectura y cuestiones comerciales parametrizadas.
2. [**02 — Diseño UI/UX, Sistema de Diseño y Estrategia SEO/GA4**](file:///C:/laragon/www/sanluis/docs/02-DISENO-UI-UX-Y-SEO.md): Paleta corporativa industrial, tipografía, componentes reutilizables, arquitectura SEO para Perú y diccionario de eventos GA4 Zero-PII.
3. [**03 — Guía de Instalación, Despliegue y Configuración de Servidor**](file:///C:/laragon/www/sanluis/docs/03-INSTALACION-DESPLIEGUE-Y-SERVIDOR.md): Guía paso a paso para desarrollo local y puesta en producción (Nginx/Apache, PHP 8.3, MySQL 8.4, SSL, Supervisor, Cron y variables `.env`).
4. [**04 — Guía de Administración y Manual de Importación del Catálogo**](file:///C:/laragon/www/sanluis/docs/04-GUIA-ADMINISTRACION-E-IMPORTACION.md): Manual operativo de los 16 módulos administrativos, matriz RBAC, flujo de cotizaciones y guía detallada de importación/exportación masiva CSV.
5. [**05 — Integraciones, Respaldo, Restauración y Seguridad**](file:///C:/laragon/www/sanluis/docs/05-INTEGRACIONES-RESPALDO-Y-SEGURIDAD.md): Configuración de WhatsApp Business, SMTP, GA4, Google Search Console, procedimiento de backup/restore y cumplimiento de la Ley N° 29733.
6. [**06 — Informe de Pruebas, Decisiones Técnicas (ADR) y Checklist de Lanzamiento**](file:///C:/laragon/www/sanluis/docs/06-INFORME-PRUEBAS-Y-CHECKLIST-LANZAMIENTO.md): Resultados verificados de PHPUnit, auditoría de rendimiento SQL (sin N+1), registro ADR, pendientes comerciales del cliente y lista de comprobación pre-lanzamiento.
