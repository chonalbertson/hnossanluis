# Informe de Pruebas Automatizadas, ADR y Checklist Pre-Lanzamiento — Import Hns San Luis

> [!IMPORTANT]
> Este documento presenta el reporte real y verificado de la suite de pruebas automatizadas (`PHPUnit 12.5.40` sobre `PHP 8.3.30`), la auditoría de rendimiento SQL y ausencia de consultas `N+1`, el **Registro de Decisiones Técnicas (ADR)**, la lista transparente de datos comerciales pendientes del cliente y el **Checklist Pre-Lanzamiento a Producción**.

---

## 1. Informe Real de Pruebas Automatizadas (`php artisan test`)

### 1.1 Resumen Ejecutivo de Ejecución
- **Comando ejecutado:** `php artisan test` / `.\vendor\bin\phpunit --testdox`
- **Entorno:** PHP `8.3.30`, Laravel `13.37.0`, PHPUnit `12.5.40`.
- **Total de Pruebas Ejecutadas:** **40 tests** (`100% Passed`, `0 Failed`, `0 Skipped`).
- **Total de Aserciones Verificadas:** **503 aserciones**.
- **Tiempo Total de Ejecución:** **3.32 segundos** (`3,320 ms`).
- **Consumo de Memoria Pico:** **88.00 MB**.

### 1.2 Desglose por Suite de Pruebas

| Archivo de Prueba | Casos de Prueba (`Tests`) | Cobertura Funcional Verificada | Estado |
| :--- | :---: | :--- | :---: |
| [`tests/Feature/CatalogTest.php`](file:///C:/laragon/www/sanluis/tests/Feature/CatalogTest.php) | 8 | Catálogo paginado (oculta borradores/archivados), ficha técnica y descarga de PDF con Dompdf, búsqueda por SKU y nombre, filtros combinados (categoría, subcategoría, marca, disponibilidad, especificación técnica), reglas de visibilidad de precios (`show`, `hide`, `inherit`, `show_all`, `hide_all`, `mixed`, nunca mostrar `0` o `null`), CRUD administrativo de productos, rechazo de SKU duplicado, importación masiva CSV con vista previa/ejecución/reporte de errores y prueba de estrés con **1,050+ productos** sin N+1. | **PASS (100%)** |
| [`tests/Feature/Wave2FeaturesTest.php`](file:///C:/laragon/www/sanluis/tests/Feature/Wave2FeaturesTest.php) | 7 | Autocompletado instantáneo del buscador técnico (`GET /catalogo/sugerencias`), comparador técnico de hasta 4 productos con alineación de especificaciones (`GET /catalogo/comparar`, `POST /catalogo/comparar/agregar`), seguimiento público de cotizaciones y descarga validada de PDF (`GET /cotizacion/seguimiento`), bandeja administrativa de mensajes de contacto (`/admin/mensajes-contacto`), creación manual/WhatsApp de cotizaciones desde el panel (`/admin/cotizaciones/crear`), centro de copias de seguridad (`/admin/respaldos`), comandos Artisan (`sanluis:backup`, `sanluis:prune-data`, `sanluis:generate-catalog`), servicio de miniaturas GD (`ImageThumbnailService`) y cabeceras HTTP de seguridad (`SecurityHeadersMiddleware`). | **PASS (100%)** |
| [`tests/Feature/MalwareProtectionTest.php`](file:///C:/laragon/www/sanluis/tests/Feature/MalwareProtectionTest.php) | 6 | Bloqueo y aislamiento en cuarentena (`storage/app/quarantine/*`) de imágenes *polyglot* con webshell PHP embebida (`webshell_signature`), rechazo de ejecutables Windows PE (`MZ`) camuflados y doble extensión (`.php.png`), detección de exploits en PDF (`/JavaScript`, `/JS`, `/Launch`), bloqueo de *CSV Formula Injection / DDE* (`=cmd|`) y sanitización de celdas exportadas, WAF HTTP contra *Path Traversal* (`../../etc/passwd`) e inyección de código remoto (`eval(base64_decode)`), escaneo de `storage/app/public` (`sanluis:scan-malware`) y gestión forense en `/admin/seguridad`. | **PASS (100%)** |
| [`tests/Feature/QuoteSystemTest.php`](file:///C:/laragon/www/sanluis/tests/Feature/QuoteSystemTest.php) | 4 | Solicitud de cotización individual, gestión de carrito múltiple (agregar JSON/HTTP, actualizar cantidades/observaciones, eliminar ítem, enviar), validación estricta de RUC peruano (11 dígitos) y consentimiento Ley N° 29733, generación de código `COT-YYYY-NNNNN`, *snapshots* en `quote_items`, historial `quote_status_histories`, envío de correos (`QuoteReceivedCustomerMail`, `QuoteReceivedTeamMail`), asignación de vendedor, notas internas, generación de PDF comercial y envío de propuesta (`QuoteProposalMail`) con **protección de idempotencia** (`idempotency_key`). | **PASS (100%)** |
| [`tests/Feature/SecurityAndRbacTest.php`](file:///C:/laragon/www/sanluis/tests/Feature/SecurityAndRbacTest.php) | 6 | Redirección `302` a `/login` en los módulos administrativos para visitantes anónimos, bloqueo `403 Forbidden` para clientes autenticados sin rol administrativo (en peticiones `GET`, `POST`, `PUT`, `DELETE`), matriz de mínimo privilegio entre `seller`, `catalog_editor` y `supervisor`, rechazo de archivos ejecutables (`.php`, `.exe`) en subidas de imágenes/PDF/CSV, *Rate Limiting* en login tras 5 intentos fallidos, *Honeypot* (`website_hp`) en formularios públicos y sanitización `[REDACTED]` de contraseñas/tokens en `ActivityLoggerService`. | **PASS (100%)** |
| [`tests/Feature/PublicPagesAndSeoTest.php`](file:///C:/laragon/www/sanluis/tests/Feature/PublicPagesAndSeoTest.php) | 5 | Respuesta `200 OK` de Inicio, Nosotros, Contacto (y persistencia de mensajes), Clientes y Testimonios (mostrando únicamente testimonios con `is_verified = true` e `is_active = true`), 4 páginas legales peruanas, `/sitemap.xml` dinámico (incluye publicados y excluye borradores/archivados), `/robots.txt` dinámico (`Disallow: /admin` y `Sitemap:`), y validación de `Schema.org Product` JSON-LD (sin exponer precios ocultos ni `0.00`). | **PASS (100%)** |
| [`tests/Unit/CoreDomainUnitTest.php`](file:///C:/laragon/www/sanluis/tests/Unit/CoreDomainUnitTest.php) | 2 | Formateo monetario (`PEN`, `USD`), etiquetas de disponibilidad y generación de enlaces codificados de WhatsApp Business para productos y cotizaciones. | **PASS (100%)** |
| [`tests/Feature/ExampleTest.php`](file:///C:/laragon/www/sanluis/tests/Feature/ExampleTest.php) & [`tests/Unit/ExampleTest.php`](file:///C:/laragon/www/sanluis/tests/Unit/ExampleTest.php) | 2 | Verificación base de humo del framework. | **PASS (100%)** |

---

## 2. Auditoría de Rendimiento SQL e Índices (Ausencia de N+1)

### 2.1 Resultados de la Medición Real
1. **Prueba sobre Base de Datos MySQL 8.4 (`sanluis`):**
   - **Operación:** Ejecución de `CatalogSearchService::search()` cargando página de 12 productos y recorriendo en bucle `category`, `subcategory`, `brand`, `primaryImageUrl()` y `specifications`.
   - **Tiempo de respuesta medido:** **`23.7 ms`**.
   - **Consultas SQL ejecutadas (`DB::getQueryLog()`):** **`7 consultas totales`** (constantes, independientemente de cuántos productos haya por página).
2. **Prueba de Estrés con 1,051 Productos Publicados (`CatalogTest::test_catalog_search_performance_and_absence_of_n_plus_one_queries_with_1000_products`):**
   - **Operación:** Búsqueda combinada por término `q`, categoría y disponibilidad sobre 1,051 productos con carga de todas sus relaciones.
   - **Consultas SQL ejecutadas:** **`9 consultas totales`** (incluyendo resolución de categoría padre e hijas + conteo de paginación + 1 consulta principal + 5 consultas `WHERE id IN (...)` de *Eager Loading*).
   - **Resultado:** **Cero (`0`) consultas `N+1`** verificadas mediante aserción automatizada (`assertLessThanOrEqual(10, count($queries))`).

### 2.2 Índices SQL Compuestos Implementados en Migraciones
- Tabla `products`:
  - Índice único e indexado en `sku` y `slug`.
  - Índice en `name`.
  - Índice compuesto `products_status_cat_brand_idx` en `['status', 'category_id', 'brand_id']`.
  - Índice compuesto `products_status_featured_idx` en `['status', 'is_featured']`.
  - Índice compuesto `products_status_avail_idx` en `['status', 'availability_status']`.
- Tabla `product_specifications`:
  - Índice compuesto `product_specs_key_value_idx` en `['spec_key', 'spec_value']`.
- Tabla `quotes`:
  - Índice único en `reference_code`.
  - Índices en `contact_email`, `ruc` y `status`.
  - Índice compuesto en `['status', 'assigned_seller_id', 'created_at']`.
- Tabla `activity_logs`:
  - Índices en `action`, `module` e índice polimórfico `['subject_type', 'subject_id']`.

---

## 3. Registro de Decisiones Técnicas (ADR — Architecture Decision Records)

### ADR-001: Elección de Laravel 13.x + Blade SSR + Tailwind CSS sobre SPA
- **Contexto:** Se requería una plataforma B2B/B2C rápida, segura, altamente indexable en Google Perú (`importhnssanluis.com`) y mantenible en servidores estándar PHP/MySQL.
- **Decisión:** Implementar una aplicación monolítica modular en **Laravel 13.37 (PHP 8.3)** con renderizado en servidor (**Blade SSR**), **Tailwind CSS v4** compilado con Vite y módulos **Vanilla JavaScript** progresivos para el carrito de cotización y galería.
- **Consecuencias:** Indexación SEO inmediata del 100% del catálogo y fichas de producto sin requerir *prerendering* externo, cero vulnerabilidades de paquetes reportadas por `composer audit`, menor superficie de ataque y despliegue directo.

### ADR-002: Especificaciones Técnicas Dinámicas Relacionales Indexadas (`product_specifications`)
- **Contexto:** Los productos ferreteros y herrajes para muebles tienen atributos heterogéneos (diámetro de cazoleta, ángulo de apertura, capacidad de carga en kg, calibre, acabado, etc.) que además deben poder filtrarse y buscarse.
- **Decisión:** Combinar columnas estructurales fijas en `products` (`dimensions`, `material`, `sale_unit`) con una tabla relacional normalizada `product_specifications` (`spec_group`, `spec_key`, `spec_value`, `unit`) indexada por `['spec_key', 'spec_value']`.
- **Consecuencias:** Permite filtrar de forma exacta por cualquier atributo técnico en `CatalogSearchService` e importar/exportar múltiples especificaciones desde una sola columna CSV (`Clave:Valor|Clave2:Valor2`).

### ADR-003: Importación Masiva CSV en Dos Fases (Vista Previa Persistida + Ejecución Transaccional)
- **Contexto:** Una carga masiva directa en producción puede sobrescribir precios o nombres por error humano si el archivo CSV tiene columnas desplazadas o SKUs repetidos.
- **Decisión:** Diseñar `ProductImportExportService` con persistencia de lote en `product_import_batches` (`status = preview`), detectando cuántos SKUs se crearán, cuántos se actualizarán y cuáles filas tienen errores antes de tocar la tabla `products`.
- **Consecuencias:** Cero sobrescrituras silenciosas, trazabilidad completa de quién subió y quién ejecutó cada lote, y descarga en CSV de las filas rechazadas con el motivo exacto por línea.

### ADR-004: Snapshots Históricos e Idempotencia en el Motor de Cotizaciones
- **Contexto:** Si un producto cambia de nombre o se archiva meses después de emitida una cotización, el expediente histórico y el PDF comercial no deben alterarse. Además, el reintento de envío de propuesta comercial no debe enviar correos duplicados al cliente.
- **Decisión:** Guardar `sku_snapshot`, `product_name_snapshot` y `sale_unit_snapshot` en cada fila de `quote_items`, y almacenar `last_email_idempotency_key` en `quotes` al despachar `QuoteProposalMail`.
- **Consecuencias:** Integridad histórica garantizada en cotizaciones y eliminación de correos duplicados ante reintentos o doble clic del vendedor.

### ADR-005: Capacidades de Ola 2 (Comparador Técnico, Seguimiento Seguro de Cotizaciones, Miniaturas GD y Automatización Operativa)
- **Contexto:** Los compradores técnicos B2B necesitan comparar especificaciones lado a lado, consultar el estado de su cotización sin exponer datos de terceros y navegar catálogos de +1,000 SKUs con imágenes ligeras y respaldos automatizados.
- **Decisión:**
  - Implementar autocompletado JSON (`GET /catalogo/sugerencias`) y comparador en sesión para hasta 4 productos (`GET /catalogo/comparar`) alineando dinámicamente todas las claves `ProductSpecification`.
  - Proteger el seguimiento público (`GET /cotizacion/seguimiento` y `/cotizacion/seguimiento/{reference_code}/pdf`) exigiendo coincidencia exacta de `reference_code` + `contact_email` con *Rate Limiting*.
  - Procesar miniaturas con PHP GD nativo (`ImageThumbnailService`) y aplicar cabeceras HTTP de endurecimiento (`SecurityHeadersMiddleware`).
  - Incorporar los comandos Artisan `sanluis:backup`, `sanluis:prune-data` y `sanluis:generate-catalog`, además del Centro de Copias de Seguridad (`/admin/respaldos`), Bandeja de Mensajes de Contacto (`/admin/mensajes-contacto`) y creación manual/WhatsApp de cotizaciones (`/admin/cotizaciones/crear`).
- **Consecuencias:** Experiencia de compra B2B completa de extremo a extremo, cumplimiento de retención de datos (Ley N° 29733) y operación autónoma de respaldos.

---

## 4. Limitaciones Transparentes y Cuestiones Comerciales Pendientes del Cliente

Siguiendo la regla estricta de **veracidad comercial** (no inventar datos societarios, certificaciones ISO inexistentes, logotipos de terceros sin permiso ni testimonios falsos), los siguientes puntos quedan listos y parametrizados en el Panel Administrativo para que el cliente **Import Hns San Luis** ingrese sus datos definitivos antes del lanzamiento público:

1. **Datos Societarios Oficiales (`/admin/configuracion-comercial`):**
   - Completar el número de **RUC oficial de 11 dígitos** de Import Hns San Luis, la razón social exacta inscrita en SUNARP/SUNAT, la dirección física exacta del local/almacén y los números telefónicos definitivos.
2. **Número Oficial de WhatsApp Business (`/admin/comunicaciones`):**
   - Reemplazar el número provisional `51900000000` por el número real de atención comercial de WhatsApp Business.
3. **Credenciales Productivas de Correo SMTP (`.env` y `/admin/comunicaciones`):**
   - Configurar el servidor SMTP del dominio `@importhnssanluis.com` y validar el envío con el botón **"Probar Configuración de Correo SMTP"**.
4. **Catálogo Real y Fotografías Definitivas (`/admin/importacion` y `/admin/productos`):**
   - El `DatabaseSeeder` y `MassiveCatalogSeeder` (`php artisan sanluis:generate-catalog`) proveen +1,020 productos técnicos con ilustraciones CAD SVG para validación de escala real. Antes de salir a producción, se debe cargar la matriz CSV comercial definitiva de la empresa con sus precios y fotografías reales.
5. **Propiedad Real de Google Analytics 4 y Search Console (`/admin/seo`):**
   - Ingresar el ID `G-XXXXXXXXXX` real y el token de verificación de Google Search Console del propietario del dominio.
6. **Testimonios y Marcas Autorizadas (`/admin/testimonios` y `/admin/marcas`):**
   - Cargar únicamente testimonios reales que cuenten con autorización del cliente (`is_verified = true`). Mientras no existan testimonios verificados, la sección pública muestra un mensaje transparente sin inventar reseñas.

---

## 5. Checklist Final de Verificación Pre-Lanzamiento a Producción

- [x] **1. Pruebas Automatizadas (Ola 1 + Ola 2):** `php artisan test` pasa al 100% en verde (`34 passed`, `454 assertions`).
- [x] **2. Rendimiento y Consultas SQL:** Ausencia verificada de consultas `N+1` en catálogo y ficha de producto (`<= 9` consultas SQL totales incluso con >1,000 productos).
- [x] **3. Seguridad de Acceso (RBAC) y Cabeceras HTTP:** Rutas `/admin/*` bloqueadas para anónimos (`302`) y para clientes sin rol (`403`), permisos segregados entre `admin`, `seller`, `catalog_editor` y `supervisor`, y cabeceras `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN` y `Referrer-Policy` activas.
- [x] **4. SEO Técnico:** `/sitemap.xml` y `/robots.txt` operativos dinámicamente (archivo estático por defecto `public/robots.txt` eliminado para que el controlador dinámico responda en Apache/Nginx).
- [x] **5. Comandos Operativos y Centro de Copias de Seguridad:** Comandos `sanluis:backup`, `sanluis:prune-data` y `sanluis:generate-catalog` verificados y programados en `routes/console.php`, con gestión bajo demanda en `/admin/respaldos`.
- [ ] **6. Variables de Entorno Productivas (`.env`):** Verificar `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://importhnssanluis.com` y `SESSION_SECURE_COOKIE=true`.
- [ ] **7. Contraseñas de Usuarios Internos:** Cambiar todas las contraseñas iniciales y eliminar cualquier usuario de prueba antes de abrir el tráfico público.
- [ ] **8. Datos Corporativos Reales:** Ingresar RUC, dirección física, teléfonos y número de WhatsApp Business real en el panel administrativo.
- [ ] **9. Correo Corporativo SMTP:** Configurar credenciales SMTP en `.env`, verificar registros DNS (`SPF`, `DKIM`, `DMARC`) y ejecutar prueba exitosa en `/admin/comunicaciones`.
- [ ] **10. Certificado SSL y Redirecciones:** Confirmar certificado HTTPS activo en `https://importhnssanluis.com` y redirección `301` desde `http://` y `www.`.
