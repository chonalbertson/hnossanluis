<?php

namespace Tests;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    /**
     * Seed the 18 permissions and 4 RBAC roles with least-privilege assignments.
     *
     * @return array<string, Role>
     */
    protected function seedRolesAndPermissions(): array
    {
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

        $rolesData = [
            'admin' => [
                'name' => 'Administrador General',
                'description' => 'Acceso total al sistema.',
                'is_system' => true,
                'permissions' => array_keys($permissionMap),
            ],
            'seller' => [
                'name' => 'Vendedor / Asesor Comercial',
                'description' => 'Gestión operativa de solicitudes de cotización y clientes.',
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
                'description' => 'Administración de productos, categorías, marcas e importaciones.',
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
                'description' => 'Supervisión de indicadores, cotizaciones, reportes y auditoría.',
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

        $roles = [];
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
            $roles[$slug] = $role;
        }

        return $roles;
    }

    /**
     * Create a user with a given role slug ('admin', 'seller', 'catalog_editor', 'supervisor', or null for regular customer).
     */
    protected function createUserWithRole(?string $roleSlug = null, array $overrides = []): User
    {
        $roles = $this->seedRolesAndPermissions();
        $roleId = $roleSlug && isset($roles[$roleSlug]) ? $roles[$roleSlug]->id : null;

        return User::query()->create(array_merge([
            'role_id' => $roleId,
            'name' => $roleSlug ? 'Usuario '.ucfirst($roleSlug) : 'Cliente B2B',
            'email' => ($roleSlug ?? 'client').'_'.Str::lower(Str::random(6)).'@importhnssanluis.com',
            'phone' => '+51 999 888 777',
            'company' => 'Empresa Test S.A.C.',
            'ruc' => '20554433221',
            'is_active' => true,
            'email_verified_at' => now(),
            'password' => Hash::make('Password123!'),
        ], $overrides));
    }

    /**
     * Create base catalog taxonomy (category, subcategory, brand) and a sample published product.
     *
     * @return array{category: Category, subcategory: Category, brand: Brand, product: Product}
     */
    protected function createSampleCatalogItem(array $productOverrides = []): array
    {
        $category = Category::query()->firstOrCreate(
            ['slug' => 'herrajes-para-muebles'],
            [
                'name' => 'Herrajes para Muebles',
                'description' => 'Bisagras, correderas y sistemas para mobiliario.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $subcategory = Category::query()->firstOrCreate(
            ['slug' => 'bisagras-cazoleta'],
            [
                'parent_id' => $category->id,
                'name' => 'Bisagras Cazoleta',
                'description' => 'Bisagras ocultas y cierre suave.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $brand = Brand::query()->firstOrCreate(
            ['slug' => 'ducasse-industrial'],
            [
                'name' => 'Ducasse Industrial',
                'is_verified' => true,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $sku = $productOverrides['sku'] ?? 'SL-BIS-'.strtoupper(Str::random(5));
        $name = $productOverrides['name'] ?? 'Bisagra Cazoleta 35mm Cierre Suave';
        $slug = $productOverrides['slug'] ?? Str::slug($name.'-'.$sku);

        $product = Product::query()->create(array_merge([
            'sku' => $sku,
            'name' => $name,
            'slug' => $slug,
            'short_description' => 'Bisagra cazoleta 35mm acero inoxidable con pistón hidráulico.',
            'detailed_description' => 'Diseñada para puertas de muebles de alto tránsito e instalaciones húmedas.',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'dimensions' => '35mm / 110°',
            'material' => 'Acero Inoxidable SUS304',
            'sale_unit' => 'Par',
            'price' => 16.50,
            'currency' => 'PEN',
            'price_visibility' => 'show',
            'availability_status' => 'available',
            'status' => 'published',
            'is_featured' => true,
        ], $productOverrides));

        return [
            'category' => $category,
            'subcategory' => $subcategory,
            'brand' => $brand,
            'product' => $product,
        ];
    }
}
