<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\HeroBanner;
use App\Models\PageContent;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Granular Permissions (18 permissions)
        $permissionsData = [
            ['slug' => 'dashboard.view', 'name' => 'Ver Panel de Control y Métricas', 'module' => 'dashboard'],
            ['slug' => 'catalog.view', 'name' => 'Ver Catálogo en Administración', 'module' => 'catalog'],
            ['slug' => 'catalog.manage', 'name' => 'Crear, Editar y Archivar Productos', 'module' => 'catalog'],
            ['slug' => 'categories.manage', 'name' => 'Gestionar Categorías y Subcategorías', 'module' => 'catalog'],
            ['slug' => 'brands.manage', 'name' => 'Gestionar Marcas', 'module' => 'catalog'],
            ['slug' => 'media.manage', 'name' => 'Gestionar Imágenes y Fichas Técnicas PDF', 'module' => 'catalog'],
            ['slug' => 'imports.manage', 'name' => 'Ejecutar Importación y Exportación Masiva CSV', 'module' => 'imports'],
            ['slug' => 'quotes.view', 'name' => 'Ver Solicitudes de Cotización', 'module' => 'quotes'],
            ['slug' => 'quotes.manage', 'name' => 'Gestionar Estados y Asignación de Cotizaciones', 'module' => 'quotes'],
            ['slug' => 'quotes.respond', 'name' => 'Responder y Enviar Propuestas Comerciales', 'module' => 'quotes'],
            ['slug' => 'customers.manage', 'name' => 'Gestionar Clientes y Contactos B2B', 'module' => 'customers'],
            ['slug' => 'users.manage', 'name' => 'Gestionar Usuarios Internos y Roles RBAC', 'module' => 'users'],
            ['slug' => 'pages.manage', 'name' => 'Editar Páginas Institucionales y Textos Legales', 'module' => 'cms'],
            ['slug' => 'settings.manage', 'name' => 'Configurar Parámetros Comerciales, SMTP y WhatsApp', 'module' => 'settings'],
            ['slug' => 'seo.manage', 'name' => 'Gestionar Metadatos SEO y Analítica GA4', 'module' => 'seo'],
            ['slug' => 'testimonials.manage', 'name' => 'Gestionar Testimonios Verificados', 'module' => 'cms'],
            ['slug' => 'activity_logs.view', 'name' => 'Auditar Registro de Actividades (Activity Log)', 'module' => 'audit'],
            ['slug' => 'reports.view', 'name' => 'Visualizar y Exportar Reportes Comerciales', 'module' => 'reports'],
        ];

        $permissionMap = [];
        foreach ($permissionsData as $perm) {
            $permission = Permission::query()->updateOrCreate(
                ['slug' => $perm['slug']],
                ['name' => $perm['name'], 'module' => $perm['module']]
            );
            $permissionMap[$perm['slug']] = $permission->id;
        }

        // 2. Seed Roles & Assign Permissions by Least Privilege
        $rolesData = [
            'admin' => [
                'name' => 'Administrador General',
                'description' => 'Acceso total a configuración, usuarios, catálogo, cotizaciones, auditoría y SEO.',
                'is_system' => true,
                'permissions' => array_keys($permissionMap),
            ],
            'seller' => [
                'name' => 'Vendedor / Asesor Comercial',
                'description' => 'Gestión operativa de solicitudes de cotización, propuestas comerciales y cartera de clientes B2B.',
                'is_system' => true,
                'permissions' => [
                    'dashboard.view',
                    'catalog.view',
                    'quotes.view',
                    'quotes.manage',
                    'quotes.respond',
                    'customers.manage',
                ],
            ],
            'catalog_editor' => [
                'name' => 'Editor de Catálogo Técnico',
                'description' => 'Administración de productos, categorías, marcas, fichas técnicas e importación masiva CSV.',
                'is_system' => true,
                'permissions' => [
                    'dashboard.view',
                    'catalog.view',
                    'catalog.manage',
                    'categories.manage',
                    'brands.manage',
                    'media.manage',
                    'imports.manage',
                ],
            ],
            'supervisor' => [
                'name' => 'Supervisor / Solo Lectura',
                'description' => 'Supervisión de indicadores comerciales, cotizaciones, reportes y auditoría sin permisos de edición destructiva.',
                'is_system' => true,
                'permissions' => [
                    'dashboard.view',
                    'catalog.view',
                    'quotes.view',
                    'activity_logs.view',
                    'reports.view',
                ],
            ],
        ];

        $roleModels = [];
        foreach ($rolesData as $slug => $rData) {
            $role = Role::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $rData['name'],
                    'description' => $rData['description'],
                    'is_system' => $rData['is_system'],
                ]
            );

            $permIds = array_values(array_intersect_key($permissionMap, array_flip($rData['permissions'])));
            $role->permissions()->sync($permIds);
            $roleModels[$slug] = $role;
        }

        // 3. Seed Users per Role
        $adminUser = User::query()->updateOrCreate(
            ['email' => 'admin@importhnssanluis.com'],
            [
                'role_id' => $roleModels['admin']->id,
                'name' => 'Administrador General San Luis',
                'phone' => '+51 999 100 100',
                'company' => 'Import Hns San Luis',
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('SanLuis2026!Admin'),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'ventas@hnssanluis.com'],
            [
                'role_id' => $roleModels['seller']->id,
                'name' => 'Carlos Mendoza (Ejecutivo B2B)',
                'phone' => '+51 999 200 200',
                'company' => 'Import Hns San Luis',
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('SanLuis2026!Seller'),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'catalogo@importhnssanluis.com'],
            [
                'role_id' => $roleModels['catalog_editor']->id,
                'name' => 'Lucía Paredes (Especialista de Catálogo)',
                'phone' => '+51 999 300 300',
                'company' => 'Import Hns San Luis',
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('SanLuis2026!Editor'),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'supervisor@importhnssanluis.com'],
            [
                'role_id' => $roleModels['supervisor']->id,
                'name' => 'Jorge Salazar (Supervisor Comercial)',
                'phone' => '+51 999 400 400',
                'company' => 'Import Hns San Luis',
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('SanLuis2026!Super'),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'compras@constructoraandina.pe'],
            [
                'role_id' => null,
                'name' => 'Ing. Roberto Quispe',
                'phone' => '+51 987 654 321',
                'company' => 'Constructora y Mobiliario Andina S.A.C.',
                'ruc' => '20554433221',
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('Cliente2026!B2B'),
            ]
        );

        // 4. Seed Initial SiteSettings
        $settings = [
            ['group' => 'commercial', 'key' => 'commercial_price_display_mode', 'value' => 'mixed', 'type' => 'string', 'label' => 'Modo de visualización de precios (show_all, hide_all, mixed)', 'public' => true],
            ['group' => 'commercial', 'key' => 'commercial_default_currency', 'value' => 'PEN', 'type' => 'string', 'label' => 'Moneda predeterminada del catálogo (PEN / USD)', 'public' => true],
            ['group' => 'commercial', 'key' => 'commercial_quote_validity_days', 'value' => '15', 'type' => 'integer', 'label' => 'Días de vigencia referencial de cotizaciones', 'public' => true],
            ['group' => 'commercial', 'key' => 'commercial_tax_rate', 'value' => '18', 'type' => 'integer', 'label' => 'Porcentaje de IGV (%)', 'public' => true],
            ['group' => 'commercial', 'key' => 'commercial_show_availability_badge', 'value' => '1', 'type' => 'boolean', 'label' => 'Mostrar etiqueta de estado de disponibilidad en productos', 'public' => true],
            ['group' => 'commercial', 'key' => 'commercial_default_quote_conditions', 'value' => 'Precios sujetos a confirmación de stock al momento de la orden de compra. Despachos a Lima Metropolitana y agencias de carga a nivel nacional previa coordinación comercial.', 'type' => 'string', 'label' => 'Condiciones comerciales predeterminadas para PDF de cotización', 'public' => true],

            ['group' => 'contact', 'key' => 'company_commercial_name', 'value' => 'Import Hnos San Luis', 'type' => 'string', 'label' => 'Nombre comercial', 'public' => true],
            ['group' => 'contact', 'key' => 'company_legal_name', 'value' => 'IMPORT HNOS SAN LUIS S.A.C.', 'type' => 'string', 'label' => 'Razón social / Denominación comercial', 'public' => true],
            ['group' => 'contact', 'key' => 'company_ruc', 'value' => '20608628143', 'type' => 'string', 'label' => 'RUC corporativo (editable en panel)', 'public' => true],
            ['group' => 'contact', 'key' => 'company_contact_email', 'value' => 'ventas@hnssanluis.com', 'type' => 'string', 'label' => 'Correo corporativo de atención comercial', 'public' => true],
            ['group' => 'contact', 'key' => 'sales_notification_email', 'value' => 'ventas@hnssanluis.com', 'type' => 'string', 'label' => 'Correo receptor de alertas de nuevas cotizaciones', 'public' => false],
            ['group' => 'contact', 'key' => 'company_contact_phone', 'value' => '977 208 514', 'type' => 'string', 'label' => 'Teléfono comercial principal', 'public' => true],
            ['group' => 'contact', 'key' => 'company_address', 'value' => 'Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú', 'type' => 'string', 'label' => 'Dirección o cobertura operativa', 'public' => true],
            ['group' => 'contact', 'key' => 'company_business_hours', 'value' => 'Lunes a Viernes: 8:30 am - 6:00 pm | Sábados: 9:00 am - 1:00 pm', 'type' => 'string', 'label' => 'Horario de atención comercial', 'public' => true],

            ['group' => 'whatsapp', 'key' => 'whatsapp_business_number', 'value' => '51977208514', 'type' => 'string', 'label' => 'Número de WhatsApp Business (con código de país 51)', 'public' => true],
            ['group' => 'whatsapp', 'key' => 'whatsapp_advisors', 'value' => [
                ['name' => 'Angie', 'phone' => '977 208 514'],
                ['name' => 'Gracia', 'phone' => '933 399 010'],
                ['name' => 'Sabrina', 'phone' => '922 353 855'],
                ['name' => '', 'phone' => '977 983 045'],
            ], 'type' => 'json', 'label' => 'Asesoras y números de WhatsApp visibles en la web', 'public' => true],
            ['group' => 'whatsapp', 'key' => 'whatsapp_floating_button_enabled', 'value' => '1', 'type' => 'boolean', 'label' => 'Habilitar botón flotante de WhatsApp', 'public' => true],
            ['group' => 'whatsapp', 'key' => 'whatsapp_default_message', 'value' => 'Hola IMPORT HNOS SAN LUIS S.A.C., deseo recibir asesoría técnica y cotización sobre su catálogo de productos.', 'type' => 'string', 'label' => 'Mensaje predeterminado de contacto por WhatsApp', 'public' => true],

            ['group' => 'seo', 'key' => 'seo_default_title', 'value' => 'IMPORT HNOS SAN LUIS S.A.C. | Ferretería Industrial, Accesorios para Muebles y Construcción', 'type' => 'string', 'label' => 'Título SEO predeterminado', 'public' => true],
            ['group' => 'seo', 'key' => 'seo_default_description', 'value' => 'Catálogo técnico B2B y solicitud de cotizaciones de IMPORT HNOS SAN LUIS S.A.C. (RUC 20608628143): herrajes para muebles, bisagras, correderas, cerraduras, fijaciones y construcción en Perú.', 'type' => 'string', 'label' => 'Meta descripción SEO predeterminada', 'public' => true],
            ['group' => 'seo', 'key' => 'ga4_measurement_id', 'value' => '', 'type' => 'string', 'label' => 'ID de Medición Google Analytics 4 (G-XXXXXXXXXX)', 'public' => true],
            ['group' => 'seo', 'key' => 'ga4_enabled', 'value' => '0', 'type' => 'boolean', 'label' => 'Activar Google Analytics 4', 'public' => true],
        ];

        foreach ($settings as $s) {
            SiteSetting::setValue(
                $s['key'],
                $s['value'],
                $s['group'],
                $s['type'],
                $s['label'],
                $s['public']
            );
        }

        // 5. Seed Institutional & Legal PageContents
        $pages = [
            [
                'page_key' => 'home',
                'title' => 'Abastecimiento Técnico en Ferretería Industrial, Herrajes para Muebles y Construcción',
                'subtitle' => 'Catálogo técnico B2B con atención ágil de cotizaciones por volumen y unidad para fabricantes de mobiliario, contratistas, ferreterías y proyectos de construcción.',
                'content' => '<p>En <strong>IMPORT HNOS SAN LUIS S.A.C.</strong> (RUC: <strong>20608628143</strong>), ubicados en <strong>Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú</strong>, facilitamos la búsqueda técnica y cotización de herrajes para mobiliario, sistemas de fijación, cerraduras arquitectónicas, herramientas y accesorios para construcción.</p><p>Explore nuestro catálogo por código SKU, especificaciones técnicas, marca o categoría y solicite su cotización formal en minutos con respaldo de nuestro equipo comercial.</p>',
                'sections_json' => [
                    'hero_badge' => 'CATÁLOGO TÉCNICO & COTIZACIÓN B2B',
                    'hero_cta_primary' => 'Explorar Catálogo Técnico',
                    'hero_cta_secondary' => 'Solicitar Cotización Rápida',
                    'highlights' => [
                        ['title' => 'Especificaciones Técnicas Claras', 'description' => 'Consulte medidas, materiales, unidades de venta y fichas técnicas por cada código SKU.'],
                        ['title' => 'Cotización Múltiple o Directa', 'description' => 'Arme su lista de requerimiento por proyecto o consulte disponibilidad inmediata vía WhatsApp.'],
                        ['title' => 'Atención Corporativa y Mayorista', 'description' => 'Propuestas formales en PDF con desglose por ítem, condiciones comerciales claras y seguimiento personalizado.'],
                    ],
                ],
                'meta_title' => 'IMPORT HNOS SAN LUIS S.A.C. | Catálogo Técnico de Ferretería, Herrajes y Construcción',
                'meta_description' => 'Solicite cotización de herrajes para muebles, bisagras, correderas, fijaciones industriales, herramientas y accesorios de construcción en IMPORT HNOS SAN LUIS S.A.C. (RUC 20608628143) — Av. Guillermo Dansey 429 - Lima Centro.',
            ],
            [
                'page_key' => 'about',
                'title' => 'Sobre IMPORT HNOS SAN LUIS S.A.C.',
                'subtitle' => 'Compromiso comercial con el abastecimiento técnico para talleres de melamina, carpintería arquitectónica, contratistas y distribuidores ferreteros (RUC: 20608628143 — Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú).',
                'content' => '<h3>Nuestra Propuesta de Valor</h3><p><strong>IMPORT HNOS SAN LUIS S.A.C.</strong> (identificada con <strong>RUC N° 20608628143</strong> y sede comercial en <strong>Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú</strong>) es una empresa peruana enfocada en la provisión de herrajes para muebles, accesorios de carpintería, fijaciones estructurales, herramientas y artículos de ferretería para el sector construcción e industrial.</p><h3>Enfoque en Claridad Técnica y Servicio B2B</h3><p>Entendemos que cada proyecto de fabricación o instalación requiere componentes con medidas, materiales y capacidades de carga precisas. Por ello, estructuramos nuestro catálogo digital para que maestros carpinteros, jefes de compras, arquitectos y distribuidores puedan identificar cada producto por su SKU y solicitar cotizaciones formales sin fricción.</p><h3>Atención Transparente</h3><p>Trabajamos con información real de disponibilidad y especificaciones verificables, brindando acompañamiento directo desde la solicitud de cotización hasta la coordinación de entrega.</p>',
                'sections_json' => [
                    'pillars' => [
                        ['title' => 'Información Técnica Confiable', 'text' => 'Fichas con dimensiones, materiales y unidades de despacho detalladas.'],
                        ['title' => 'Respuesta Comercial Ordenada', 'text' => 'Cada solicitud recibe un código único de seguimiento (COT-YYYY-NNNNN).'],
                        ['title' => 'Cobertura para Proyectos y Mayoristas', 'text' => 'Atención tanto para reposición de taller como para compras por volumen.'],
                    ],
                ],
                'meta_title' => 'Nosotros | IMPORT HNOS SAN LUIS S.A.C. (RUC 20608628143)',
                'meta_description' => 'Conozca el enfoque técnico y comercial de IMPORT HNOS SAN LUIS S.A.C. (RUC 20608628143) en Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú.',
            ],
            [
                'page_key' => 'privacy_policy',
                'title' => 'Política de Privacidad y Protección de Datos Personales',
                'subtitle' => 'En cumplimiento de la Ley N° 29733 — Ley de Protección de Datos Personales del Perú y su Reglamento.',
                'content' => '<h3>1. Identidad del Titular del Banco de Datos</h3><p><strong>IMPORT HNOS SAN LUIS S.A.C.</strong>, con <strong>RUC N° 20608628143</strong> y domicilio en <strong>Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú</strong>, informa a los usuarios y clientes que los datos personales suministrados a través de los formularios de solicitud de cotización, libro de reclamaciones, registro de cuenta y contacto comercial son tratados conforme a la Ley N° 29733 (Ley de Protección de Datos Personales) y su Reglamento aprobado por Decreto Supremo N° 003-2013-JUS.</p><h3>2. Finalidad del Tratamiento de Datos</h3><p>Los datos recopilados (nombre de contacto, razón social, RUC, correo electrónico, teléfono/WhatsApp y requerimientos de productos) se utilizan exclusivamente para:</p><ul><li>Evaluar, elaborar y enviar propuestas técnicas y económicas de las cotizaciones solicitadas.</li><li>Brindar seguimiento comercial y coordinación operativa de pedidos.</li><li>Atender consultas, quejas o reclamos registrados en el Libro de Reclamaciones Virtual.</li><li>Gestionar el historial de cotizaciones de usuarios registrados.</li></ul><h3>3. Derechos ARCO</h3><p>El titular de los datos personales puede ejercer en cualquier momento sus derechos de Acceso, Rectificación, Cancelación y Oposición (ARCO) enviando una solicitud al correo corporativo configurado en nuestros canales oficiales de contacto o en nuestra sede en Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú, indicando su nombre completo y petición concreta.</p><h3>4. Seguridad y Confidencialidad</h3><p>Implementamos controles de acceso basados en roles, cifrado de contraseñas y registro de auditoría interna para proteger la confidencialidad de la información comercial de nuestros clientes.</p>',
                'meta_title' => 'Política de Privacidad (Ley N° 29733) | IMPORT HNOS SAN LUIS S.A.C.',
                'meta_description' => 'Política de tratamiento de datos personales y ejercicio de derechos ARCO conforme a la Ley N° 29733 en IMPORT HNOS SAN LUIS S.A.C. (RUC 20608628143).',
            ],
            [
                'page_key' => 'cookies_policy',
                'title' => 'Política de Cookies',
                'subtitle' => 'Información transparente sobre el uso de cookies técnicas y analíticas en nuestro portal.',
                'content' => '<h3>1. ¿Qué son las cookies?</h3><p>Las cookies son pequeños archivos de texto almacenados en su navegador para mantener el estado de su sesión y su lista de cotización activa mientras navega por el catálogo de <strong>IMPORT HNOS SAN LUIS S.A.C.</strong> (RUC N° 20608628143).</p><h3>2. Tipos de cookies que utilizamos</h3><ul><li><strong>Cookies Técnicas y de Sesión (Estrictamente Necesarias):</strong> Permiten conservar los productos agregados a su carrito de cotización, proteger formularios contra ataques CSRF y mantener su sesión iniciada.</li><li><strong>Cookies de Analítica (Opcionales):</strong> Cuando están habilitadas, permiten medir de forma agregada qué categorías y productos generan mayor interés comercial.</li></ul>',
                'meta_title' => 'Política de Cookies | IMPORT HNOS SAN LUIS S.A.C.',
                'meta_description' => 'Información sobre las cookies técnicas y de sesión utilizadas en el catálogo web de IMPORT HNOS SAN LUIS S.A.C.',
            ],
            [
                'page_key' => 'terms_conditions',
                'title' => 'Términos y Condiciones de Uso',
                'subtitle' => 'Lineamientos de uso del catálogo digital y plataforma de solicitud de cotizaciones de IMPORT HNOS SAN LUIS S.A.C. (RUC 20608628143).',
                'content' => '<h3>1. Identificación de la Empresa y Naturaleza del Portal</h3><p>El presente sitio web es operado por <strong>IMPORT HNOS SAN LUIS S.A.C.</strong>, identificada con <strong>RUC N° 20608628143</strong> y domicilio en <strong>Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú</strong>. El portal opera como un catálogo técnico informativo y canal de recepción de solicitudes de cotización B2B/B2C. El envío de una solicitud de cotización no constituye un cobro automático ni perfecciona un contrato de compraventa hasta la confirmación expresa de la propuesta comercial y validación de stock.</p><h3>2. Exactitud de Especificaciones e Imágenes</h3><p>Las especificaciones técnicas, dimensiones, materiales y fotografías tienen carácter informativo y referencial. En caso de requerir tolerancias críticas para un proyecto industrial, nuestro equipo técnico confirmará la ficha exacta en la cotización formal.</p>',
                'meta_title' => 'Términos y Condiciones | IMPORT HNOS SAN LUIS S.A.C.',
                'meta_description' => 'Términos y condiciones de uso del catálogo digital de IMPORT HNOS SAN LUIS S.A.C. (RUC 20608628143).',
            ],
            [
                'page_key' => 'quote_conditions',
                'title' => 'Condiciones Generales de Cotización',
                'subtitle' => 'Criterios comerciales aplicables a las propuestas emitidas por IMPORT HNOS SAN LUIS S.A.C. (RUC 20608628143).',
                'content' => '<h3>1. Emisor y Vigencia de las Ofertas</h3><p>Toda cotización formal emitida por el equipo comercial de <strong>IMPORT HNOS SAN LUIS S.A.C.</strong> (RUC N° <strong>20608628143</strong>) indica su plazo de vigencia en días calendario (por defecto 15 días), sujeto a rotación de inventario.</p><h3>2. Precios y Moneda</h3><p>Los precios publicados en modalidad referencial o incluidos en las propuestas formales se expresan en Soles (PEN) o Dólares Americanos (USD) según se indique expresamente en el documento PDF de cotización.</p><h3>3. Disponibilidad y Productos Bajo Consulta</h3><p>Los ítems marcados como <em>Bajo consulta</em> corresponden a productos de importación por lote, medidas especiales o despacho programado, cuyo tiempo de entrega se detalla en la respuesta comercial.</p>',
                'meta_title' => 'Condiciones de Cotización | IMPORT HNOS SAN LUIS S.A.C.',
                'meta_description' => 'Condiciones comerciales, vigencia y políticas de despacho aplicables a las cotizaciones de IMPORT HNOS SAN LUIS S.A.C. (RUC 20608628143).',
            ],
        ];

        foreach ($pages as $p) {
            PageContent::query()->updateOrCreate(
                ['page_key' => $p['page_key']],
                array_merge($p, ['is_indexable' => true, 'updated_by' => $adminUser->id])
            );
        }

        // 5b. Seed Default Hero Slider Banners
        foreach (HeroBanner::defaultSlidesDefinition() as $bannerSlide) {
            HeroBanner::query()->updateOrCreate(
                ['label' => $bannerSlide['label']],
                $bannerSlide
            );
        }

        // 6. Import Official Real Catalog (14 Parent Categories, 68 Subcategories, 28 Brands, 395 Products, 673 HD Images)
        Artisan::call('sanluis:import-real-catalog', [
            '--source' => database_path('seeders/data/sanluis_real_catalog.json'),
        ]);

        // 7. Seed Verified B2B Testimonials
        $testimonials = [
            [
                'author_name' => 'Ing. Roberto Quispe',
                'author_role' => 'Jefe de Logística y Compras',
                'company_name' => 'Constructora y Mobiliario Andina S.A.C.',
                'content' => 'Trabajamos con IMPORT HNOS SAN LUIS S.A.C. para el abastecimiento de cerraduras, bisagras cazoleta y correderas telescópicas en nuestros proyectos multifamiliares. Su catálogo por código SKU y la rapidez en las cotizaciones formales nos ahorran horas de gestión.',
                'rating' => 5,
                'is_verified' => true,
                'authorization_reference' => 'OC-2026-0142',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'author_name' => 'Arq. Mariana Valdivia',
                'author_role' => 'Directora de Proyectos de Interiorismo',
                'company_name' => 'Estudio V&M Mobiliario Corporativo',
                'content' => 'La variedad de tiradores en aluminio, sistemas corredizos y accesorios para mueblería nos permite especificar herrajes confiables con despacho puntual en Lima Metropolitana.',
                'rating' => 5,
                'is_verified' => true,
                'authorization_reference' => 'COT-2026-0089',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'author_name' => 'Sr. César Huamán',
                'author_role' => 'Gerente Comercial',
                'company_name' => 'Ferretería y Distribuidora San Martín E.I.R.L.',
                'content' => 'Excelente atención mayorista para provincia. Las cajas llegan bien embaladas a agencia de carga y las fichas técnicas del portal facilitan verificar medidas y unidades por caja antes de pedir.',
                'rating' => 5,
                'is_verified' => true,
                'authorization_reference' => 'FAC-F001-04512',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($testimonials as $item) {
            Testimonial::query()->updateOrCreate(
                [
                    'author_name' => $item['author_name'],
                    'company_name' => $item['company_name'],
                ],
                $item
            );
        }

        // 8. Seed initial audit log entry
        ActivityLog::query()->firstOrCreate(
            ['action' => 'system.seeded', 'module' => 'system'],
            [
                'user_id' => $adminUser->id,
                'description' => 'Inicialización de base de datos, roles RBAC, configuración comercial y catálogo técnico oficial de Import Hns San Luis.',
                'properties' => [
                    'roles_count' => count($roleModels),
                    'permissions_count' => count($permissionMap),
                    'products_count' => Product::count(),
                    'brands_count' => Brand::count(),
                    'categories_count' => Category::count(),
                ],
                'ip_address' => '127.0.0.1',
            ]
        );
    }
}
