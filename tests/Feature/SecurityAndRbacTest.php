<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Services\ActivityLoggerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityAndRbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_are_redirected_to_login_on_all_admin_modules(): void
    {
        $adminUrls = [
            '/admin',
            '/admin/productos',
            '/admin/productos/crear',
            '/admin/categorias',
            '/admin/marcas',
            '/admin/archivos',
            '/admin/importacion',
            '/admin/cotizaciones',
            '/admin/clientes',
            '/admin/usuarios',
            '/admin/paginas',
            '/admin/configuracion-comercial',
            '/admin/comunicaciones',
            '/admin/seo',
            '/admin/testimonios',
            '/admin/actividad',
            '/admin/informes',
        ];

        foreach ($adminUrls as $url) {
            $response = $this->get($url);
            $response->assertRedirect(route('login'));
        }
    }

    public function test_authenticated_regular_client_without_admin_role_receives_403_forbidden_on_get_and_post_put_delete(): void
    {
        $clientUser = $this->createUserWithRole(null, [
            'name' => 'Cliente Regular Sin Rol',
            'email' => 'cliente@empresaexterna.pe',
        ]);

        $targetAdminUser = $this->createUserWithRole('admin');
        $sampleProduct = $this->createSampleCatalogItem()['product'];

        // Direct GET attempts to admin modules must return 403 Forbidden
        $this->actingAs($clientUser)->get('/admin')->assertStatus(403);
        $this->actingAs($clientUser)->get('/admin/productos')->assertStatus(403);
        $this->actingAs($clientUser)->get('/admin/usuarios')->assertStatus(403);
        $this->actingAs($clientUser)->get('/admin/cotizaciones')->assertStatus(403);
        $this->actingAs($clientUser)->get('/admin/seo')->assertStatus(403);
        $this->actingAs($clientUser)->get('/admin/informes')->assertStatus(403);

        // Manipulated POST / PUT / DELETE requests must also return 403 Forbidden
        $this->actingAs($clientUser)->post('/admin/productos', [
            'sku' => 'SL-HACK-001',
            'name' => 'Intento No Autorizado',
        ])->assertStatus(403);

        $this->actingAs($clientUser)->put('/admin/productos/'.$sampleProduct->id, [
            'sku' => 'SL-HACK-002',
            'name' => 'Modificación No Autorizada',
        ])->assertStatus(403);

        $this->actingAs($clientUser)->delete('/admin/productos/'.$sampleProduct->id)->assertStatus(403);
        $this->actingAs($clientUser)->delete('/admin/usuarios/'.$targetAdminUser->id)->assertStatus(403);
    }

    public function test_least_privilege_rbac_enforcement_across_seller_catalog_editor_and_supervisor_roles(): void
    {
        $seller = $this->createUserWithRole('seller');
        $editor = $this->createUserWithRole('catalog_editor');
        $supervisor = $this->createUserWithRole('supervisor');
        $dummyUser = $this->createUserWithRole(null);

        $product = $this->createSampleCatalogItem()['product'];
        $quote = Quote::query()->create([
            'reference_code' => 'COT-2026-00099',
            'contact_name' => 'Cliente Prueba RBAC',
            'contact_email' => 'rbac@cliente.pe',
            'contact_phone' => '999888777',
            'status' => 'nueva',
            'currency' => 'PEN',
        ]);

        // --- 1. SELLER ROLE ---
        // Allowed: dashboard, view/respond quotes, manage customers
        $this->actingAs($seller)->get(route('admin.dashboard'))->assertStatus(200);
        $this->actingAs($seller)->get(route('admin.quotes.index'))->assertStatus(200);
        $this->actingAs($seller)->get(route('admin.quotes.show', $quote))->assertStatus(200);
        $this->actingAs($seller)->get(route('admin.customers.index'))->assertStatus(200);

        // Forbidden (403) for Seller: manage users, edit SEO, create/delete products, run CSV imports
        $this->actingAs($seller)->get(route('admin.users.index'))->assertStatus(403);
        $this->actingAs($seller)->post(route('admin.users.store'), [])->assertStatus(403);
        $this->actingAs($seller)->get(route('admin.settings.seo'))->assertStatus(403);
        $this->actingAs($seller)->put(route('admin.settings.seo.update'), [])->assertStatus(403);
        $this->actingAs($seller)->get(route('admin.products.create'))->assertStatus(403);
        $this->actingAs($seller)->delete(route('admin.products.destroy', $product))->assertStatus(403);
        $this->actingAs($seller)->get(route('admin.imports.index'))->assertStatus(403);

        // --- 2. CATALOG EDITOR ROLE ---
        // Allowed: dashboard, manage products, categories, brands, media, imports
        $this->actingAs($editor)->get(route('admin.dashboard'))->assertStatus(200);
        $this->actingAs($editor)->get(route('admin.products.index'))->assertStatus(200);
        $this->actingAs($editor)->get(route('admin.products.create'))->assertStatus(200);
        $this->actingAs($editor)->get(route('admin.categories.index'))->assertStatus(200);
        $this->actingAs($editor)->get(route('admin.brands.index'))->assertStatus(200);
        $this->actingAs($editor)->get(route('admin.imports.index'))->assertStatus(200);

        // Forbidden (403) for Catalog Editor: manage users, view/respond quotes, edit SEO/settings
        $this->actingAs($editor)->get(route('admin.users.index'))->assertStatus(403);
        $this->actingAs($editor)->delete(route('admin.users.destroy', $dummyUser))->assertStatus(403);
        $this->actingAs($editor)->get(route('admin.quotes.index'))->assertStatus(403);
        $this->actingAs($editor)->post(route('admin.quotes.respond', $quote), [])->assertStatus(403);
        $this->actingAs($editor)->get(route('admin.settings.seo'))->assertStatus(403);

        // --- 3. SUPERVISOR ROLE ---
        // Allowed: dashboard, view reports, view activity logs, view quotes & products (read-only)
        $this->actingAs($supervisor)->get(route('admin.dashboard'))->assertStatus(200);
        $this->actingAs($supervisor)->get(route('admin.reports.index'))->assertStatus(200);
        $this->actingAs($supervisor)->get(route('admin.activity-logs.index'))->assertStatus(200);
        $this->actingAs($supervisor)->get(route('admin.quotes.index'))->assertStatus(200);
        $this->actingAs($supervisor)->get(route('admin.products.index'))->assertStatus(200);

        // Forbidden (403) for Supervisor: create/delete users, create/delete products, respond/change quote status
        $this->actingAs($supervisor)->get(route('admin.users.index'))->assertStatus(403);
        $this->actingAs($supervisor)->post(route('admin.users.store'), [])->assertStatus(403);
        $this->actingAs($supervisor)->delete(route('admin.users.destroy', $dummyUser))->assertStatus(403);
        $this->actingAs($supervisor)->get(route('admin.products.create'))->assertStatus(403);
        $this->actingAs($supervisor)->post(route('admin.quotes.status', $quote), ['status' => 'cerrada'])->assertStatus(403);
        $this->actingAs($supervisor)->post(route('admin.quotes.respond', $quote), [])->assertStatus(403);
    }

    public function test_file_upload_validation_rejects_executable_or_disallowed_extensions(): void
    {
        Storage::fake('public');

        $editor = $this->createUserWithRole('catalog_editor');
        $product = $this->createSampleCatalogItem()['product'];

        // 1. Attempt to upload a .php or .exe file as a product image
        $maliciousPhpFile = UploadedFile::fake()->create('shell.php', 50, 'application/x-php');
        $imageUploadResponse = $this->actingAs($editor)->post(route('admin.media.store-image'), [
            'product_id' => $product->id,
            'image' => $maliciousPhpFile,
        ]);
        $imageUploadResponse->assertSessionHasErrors(['image']);

        // 2. Attempt to upload an .exe file as a technical PDF document
        $maliciousExeFile = UploadedFile::fake()->create('malware.exe', 120, 'application/x-msdownload');
        $docUploadResponse = $this->actingAs($editor)->post(route('admin.media.store-document'), [
            'product_id' => $product->id,
            'title' => 'Supuesta Ficha Técnica',
            'document' => $maliciousExeFile,
        ]);
        $docUploadResponse->assertSessionHasErrors(['document']);

        // 3. Attempt to upload a .exe file to CSV importer
        $importUploadResponse = $this->actingAs($editor)->post(route('admin.imports.analyze'), [
            'csv_file' => $maliciousExeFile,
        ]);
        $importUploadResponse->assertSessionHasErrors(['csv_file']);
    }

    public function test_rate_limiting_and_honeypot_protection_on_login_and_public_forms(): void
    {
        $user = $this->createUserWithRole('admin', [
            'email' => 'seguridad@importhnssanluis.com',
        ]);

        // 1. Login brute-force rate limiting after 5 failed attempts
        for ($i = 1; $i <= 5; $i++) {
            $this->post(route('login.submit'), [
                'email' => 'seguridad@importhnssanluis.com',
                'password' => 'ClaveIncorrecta'.$i,
            ])->assertSessionHasErrors(['email']);
        }

        $sixthAttempt = $this->post(route('login.submit'), [
            'email' => 'seguridad@importhnssanluis.com',
            'password' => 'ClaveIncorrecta6',
        ]);
        $sixthAttempt->assertSessionHasErrors(['email']);
        $this->assertStringContainsString(
            'Demasiados intentos',
            session('errors')->first('email')
        );

        // 2. Honeypot antispam protection on Contact form (`website_hp`)
        $honeypotContact = $this->post(route('contact.submit'), [
            'name' => 'Bot Spam',
            'email' => 'bot@spam.com',
            'subject' => 'Spam automatizado',
            'message' => 'Mensaje enviado por un bot llenando el campo oculto.',
            'privacy_accepted' => '1',
            'website_hp' => 'http://spam-link.example.com',
        ]);
        $honeypotContact->assertSessionHasErrors(['name']);

        // 3. Honeypot antispam protection on Quote form (`website_hp`)
        $product = $this->createSampleCatalogItem()['product'];
        $honeypotQuote = $this->post(route('quote.submit'), [
            'request_type' => 'single',
            'product_id' => $product->id,
            'quantity' => 1,
            'contact_name' => 'Bot Cotizador',
            'contact_email' => 'bot@spam.com',
            'contact_phone' => '999999999',
            'privacy_accepted' => '1',
            'website_hp' => 'filled-by-bot',
        ]);
        $honeypotQuote->assertSessionHasErrors(['contact_name']);
    }

    public function test_activity_logger_service_redacts_sensitive_fields_and_never_stores_plaintext_secrets(): void
    {
        $admin = $this->createUserWithRole('admin');
        /** @var ActivityLoggerService $logger */
        $logger = app(ActivityLoggerService::class);

        $log = $logger->log(
            'security.test',
            'settings',
            'Prueba de sanitización de secretos en auditoría.',
            $admin,
            [
                'admin_email' => $admin->email,
                'password' => 'SuperSecretPassword123!',
                'password_confirmation' => 'SuperSecretPassword123!',
                'smtp_password' => 'SmtpSecretKey999',
                'whatsapp_api_token' => 'EAABsbCS1iHgBO...',
                'nested_config' => [
                    'client_secret' => 'MySecretValue',
                    'api_key' => 'Key_Live_998877',
                    'safe_parameter' => 'enabled',
                ],
            ],
            $admin->id
        );

        $properties = $log->fresh()->properties;

        $this->assertSame($admin->email, $properties['admin_email']);
        $this->assertSame('[REDACTED]', $properties['password']);
        $this->assertSame('[REDACTED]', $properties['password_confirmation']);
        $this->assertSame('[REDACTED]', $properties['smtp_password']);
        $this->assertSame('[REDACTED]', $properties['whatsapp_api_token']);
        $this->assertSame('[REDACTED]', $properties['nested_config']['client_secret']);
        $this->assertSame('[REDACTED]', $properties['nested_config']['api_key']);
        $this->assertSame('enabled', $properties['nested_config']['safe_parameter']);

        $rawJsonInDb = json_encode($properties);
        $this->assertStringNotContainsString('SuperSecretPassword123!', $rawJsonInDb);
        $this->assertStringNotContainsString('SmtpSecretKey999', $rawJsonInDb);
        $this->assertStringNotContainsString('Key_Live_998877', $rawJsonInDb);
    }
}
