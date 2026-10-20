<?php

namespace Tests\Feature;

use App\Mail\ComplaintBookReceiptMail;
use App\Mail\ContactMessageReceivedMail;
use App\Mail\PasswordResetMail;
use App\Models\Product;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicPagesAndSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_renders_hero_slider_with_all_slides_and_controls(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('data-hero-slider', false);
        $response->assertSee('data-slider-next', false);
        $response->assertSeeInOrder(['Catálogo General', 'Herrajes y Corredizos', 'Cerraduras y Fijaciones', 'Herramientas y LED']);
        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
    }

    public function test_public_institutional_and_legal_pages_return_200_and_contact_form_persists_message(): void
    {
        $publicRoutes = [
            '/',
            '/empresa',
            '/empresa/nosotros-experiencia',
            '/empresa/especialidades-b2b',
            '/empresa/certificaciones',
            '/empresa/cobertura-nacional',
            '/empresa/nuestro-equipo',
            '/empresa/marcas-representadas',
            '/empresa/politicas-atencion-corporativa',
            '/empresa/casos-y-proyectos-reales',
            '/nosotros',
            '/contacto',
            '/clientes-y-testimonios',
            '/politica-de-privacidad',
            '/politica-de-cookies',
            '/terminos-y-condiciones',
            '/condiciones-de-cotizacion',
            '/libro-de-reclamaciones',
        ];

        foreach ($publicRoutes as $uri) {
            $response = $this->get($uri);
            $response->assertStatus(200);
            $response->assertSee('IMPORT HNOS SAN LUIS S.A.C.');
            $response->assertSee('20608628143');
            $response->assertSee('977 208 514');
            $response->assertSee('933 399 010');
            $response->assertSee('922 353 855');
            $response->assertSee('977 983 045');
            $response->assertSee('Angie');
            $response->assertSee('Gracia');
            $response->assertSee('Sabrina');
            $response->assertSee('Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú');
        }

        // Verify Admin can update WhatsApp advisor names and numbers from /admin/comunicaciones
        $admin = $this->createUserWithRole('admin');
        $updateWa = $this->actingAs($admin)->put(route('admin.settings.communication.update'), [
            'whatsapp_business_number' => '51977208514',
            'whatsapp_floating_button_enabled' => '1',
            'whatsapp_default_message' => 'Hola IMPORT HNOS SAN LUIS S.A.C., deseo cotizar.',
            'company_contact_email' => 'ventas@hnssanluis.com',
            'sales_notification_email' => 'ventas@hnssanluis.com',
            'advisors' => [
                ['name' => 'Angie', 'phone' => '977 208 514'],
                ['name' => 'Gracia', 'phone' => '933 399 010'],
                ['name' => 'Sabrina', 'phone' => '922 353 855'],
                ['name' => 'Valeria', 'phone' => '977 983 045'],
            ],
        ]);
        $updateWa->assertRedirect();
        $this->get('/')->assertSee('Valeria');

        Mail::fake();

        // Submit valid contact message
        $contactPost = $this->post(route('contact.submit'), [
            'name' => 'Ing. Raúl Mendoza',
            'email' => 'rmendoza@proyectoslima.pe',
            'phone' => '+51 955 444 333',
            'company' => 'Proyectos e Ingeniería Lima S.A.C.',
            'ruc' => '20512345678',
            'subject' => 'Consulta por abastecimiento mayorista de bisagras',
            'message' => 'Solicito información sobre tiempos de entrega para un pedido de 500 pares.',
            'privacy_accepted' => '1',
        ]);

        $contactPost->assertRedirect(route('contact.index'));
        $contactPost->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Ing. Raúl Mendoza',
            'email' => 'rmendoza@proyectoslima.pe',
            'ruc' => '20512345678',
            'subject' => 'Consulta por abastecimiento mayorista de bisagras',
            'status' => 'pending',
        ]);
        Mail::assertSent(ContactMessageReceivedMail::class);

        // Submit valid Libro de Reclamaciones sheet
        $complaintPost = $this->post(route('pages.complaints-book.submit'), [
            'document_type' => 'RUC',
            'document_number' => '20608628143',
            'consumer_name' => 'Carlos Quispe Huamán',
            'consumer_email' => 'cquispe@carpinteriamoderna.pe',
            'consumer_phone' => '987654321',
            'consumer_address' => 'Av. Industrial 450, Lima',
            'company_name' => 'Carpintería Moderna S.A.C.',
            'good_type' => 'Producto',
            'claimed_amount' => '350.00',
            'good_description' => 'Corredera telescópica 450mm - COT-2026-00010',
            'claim_type' => 'Reclamo',
            'claim_detail' => 'Solicito revisión de una caja de correderas entregada con medida distinta a la solicitada.',
            'consumer_request' => 'Cambio por la medida de 450mm indicada en la cotización.',
            'privacy_accepted' => '1',
        ]);

        $complaintPost->assertRedirect(route('pages.complaints-book'));
        $complaintPost->assertSessionHas('complaint_sheet_code');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Carlos Quispe Huamán',
            'email' => 'cquispe@carpinteriamoderna.pe',
            'status' => 'pending',
        ]);
        Mail::assertSent(ComplaintBookReceiptMail::class);
    }

    public function test_testimonials_page_only_displays_verified_and_active_testimonials(): void
    {
        Testimonial::query()->create([
            'author_name' => 'Cliente Verificado y Activo',
            'author_role' => 'Jefe de Adquisiciones',
            'company_name' => 'Mobiliario Corporativo Perú S.A.C.',
            'content' => 'Excelente atención técnica y cumplimiento puntual en nuestros despachos de herrajes.',
            'rating' => 5,
            'is_verified' => true,
            'authorization_reference' => 'AUT-2026-001',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Testimonial::query()->create([
            'author_name' => 'Testimonio No Verificado',
            'company_name' => 'Empresa Sin Autorización',
            'content' => 'Este testimonio no está verificado y nunca debe mostrarse públicamente.',
            'rating' => 5,
            'is_verified' => false,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Testimonial::query()->create([
            'author_name' => 'Testimonio Inactivo',
            'company_name' => 'Empresa Inactiva',
            'content' => 'Este testimonio está desactivado en el panel.',
            'rating' => 5,
            'is_verified' => true,
            'is_active' => false,
            'sort_order' => 3,
        ]);

        $response = $this->get(route('pages.testimonials'));
        $response->assertStatus(200);
        $response->assertSee('Cliente Verificado y Activo');
        $response->assertSee('Excelente atención técnica y cumplimiento puntual');
        $response->assertDontSee('Testimonio No Verificado');
        $response->assertDontSee('Este testimonio no está verificado y nunca debe mostrarse públicamente.');
        $response->assertDontSee('Testimonio Inactivo');
    }

    public function test_sitemap_xml_contains_published_products_and_excludes_draft_and_archived_products(): void
    {
        $base = $this->createSampleCatalogItem([
            'sku' => 'SL-SEO-PUB',
            'name' => 'Producto Publicado Indexable',
            'slug' => 'producto-publicado-indexable',
            'status' => 'published',
        ]);

        Product::query()->create([
            'sku' => 'SL-SEO-DRAFT',
            'name' => 'Producto En Borrador No Indexable',
            'slug' => 'producto-en-borrador-no-indexable',
            'category_id' => $base['category']->id,
            'sale_unit' => 'Unidad',
            'status' => 'draft',
        ]);

        Product::query()->create([
            'sku' => 'SL-SEO-ARCH',
            'name' => 'Producto Archivado No Indexable',
            'slug' => 'producto-archivado-no-indexable',
            'category_id' => $base['category']->id,
            'sale_unit' => 'Unidad',
            'status' => 'archived',
        ]);

        $response = $this->get(route('seo.sitemap'));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/xml', (string) $response->headers->get('Content-Type'));

        $xmlContent = $response->getContent();
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $xmlContent);
        $this->assertStringContainsString(route('catalog.show', 'producto-publicado-indexable'), $xmlContent);
        $this->assertStringNotContainsString('producto-en-borrador-no-indexable', $xmlContent);
        $this->assertStringNotContainsString('producto-archivado-no-indexable', $xmlContent);
    }

    public function test_robots_txt_disallows_admin_and_declares_sitemap_url(): void
    {
        $response = $this->get(route('seo.robots'));
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString("User-agent: Googlebot\nAllow: /", $content);
        $this->assertStringContainsString("User-agent: bingbot\nAllow: /", $content);
        $this->assertStringContainsString("User-agent: Amazonbot\nDisallow: /", $content);
        $this->assertStringContainsString("User-agent: Reflectionbot\nDisallow: /", $content);
        $this->assertStringContainsString("User-agent: Bytespider\nDisallow: /", $content);
        $this->assertStringContainsString("User-agent: meta-externalagent\nDisallow: /", $content);
        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Disallow: /admin', $content);
        $this->assertStringContainsString('Sitemap: '.route('seo.sitemap'), $content);
    }

    public function test_product_detail_json_ld_schema_respects_price_visibility_and_never_exposes_fake_or_hidden_prices(): void
    {
        $base = $this->createSampleCatalogItem([
            'sku' => 'SL-SCHEMA-VISIBLE',
            'name' => 'Cerradura Con Precio Público',
            'slug' => 'cerradura-con-precio-publico',
            'price' => 129.90,
            'currency' => 'PEN',
            'price_visibility' => 'show',
        ]);

        $hiddenPriceProduct = Product::query()->create([
            'sku' => 'SL-SCHEMA-HIDDEN',
            'name' => 'Cerradura Bajo Cotización Precio Oculto',
            'slug' => 'cerradura-bajo-cotizacion-precio-oculto',
            'category_id' => $base['category']->id,
            'brand_id' => $base['brand']->id,
            'sale_unit' => 'Unidad',
            'price' => 249.00,
            'currency' => 'PEN',
            'price_visibility' => 'hide',
            'availability_status' => 'on_request',
            'status' => 'published',
        ]);

        // 1. Product with visible price > 0 includes Schema.org Offer with exact price
        $visibleRes = $this->get(route('catalog.show', $base['product']->slug));
        $visibleRes->assertStatus(200);
        $visibleHtml = $visibleRes->getContent();
        $this->assertStringContainsString('"@type":"Product"', $visibleHtml);
        $this->assertStringContainsString('"sku":"SL-SCHEMA-VISIBLE"', $visibleHtml);
        $this->assertStringContainsString('"@type":"Offer"', $visibleHtml);
        $this->assertStringContainsString('"price":"129.90"', $visibleHtml);

        // 2. Product with hidden price must NOT expose Offer or hidden price 249.00 or fake 0.00 in JSON-LD
        $hiddenRes = $this->get(route('catalog.show', $hiddenPriceProduct->slug));
        $hiddenRes->assertStatus(200);
        $hiddenHtml = $hiddenRes->getContent();
        $this->assertStringContainsString('"@type":"Product"', $hiddenHtml);
        $this->assertStringContainsString('"sku":"SL-SCHEMA-HIDDEN"', $hiddenHtml);
        $this->assertStringNotContainsString('"@type":"Offer"', $hiddenHtml);
        $this->assertStringNotContainsString('249.00', $hiddenHtml);
        $this->assertStringNotContainsString('"price":"0.00"', $hiddenHtml);
    }

    public function test_each_company_menu_section_renders_as_an_individual_page(): void
    {
        $pages = [
            'nosotros-experiencia' => 'Misión, visión y valores',
            'especialidades-b2b' => 'Sectores que abastecemos',
            'certificaciones' => 'Cuatro filtros antes de que un producto llegue a tu obra',
            'cobertura-nacional' => 'Tres modalidades de entrega para llegar a todo el país',
            'nuestro-equipo' => 'Áreas que trabajan para tu abastecimiento',
            'marcas-representadas' => 'Fabricantes que respaldan nuestro catálogo',
            'politicas-atencion-corporativa' => 'Documentos oficiales para clientes y consumidores',
            'casos-y-proyectos-reales' => 'Dónde se instalan nuestros productos',
        ];

        foreach ($pages as $slug => $exclusiveHeading) {
            $response = $this->get(route('pages.company', ['section' => $slug]));

            $response->assertOk();
            $response->assertSee($exclusiveHeading);
            $response->assertSee('<link rel="canonical" href="'.route('pages.company', ['section' => $slug]).'"', false);

            foreach ($pages as $otherSlug => $otherHeading) {
                if ($otherSlug !== $slug) {
                    $response->assertDontSee($otherHeading);
                }
            }
        }

        $hub = $this->get(route('pages.company'));
        $hub->assertOk();
        foreach (array_keys($pages) as $slug) {
            $hub->assertSee(route('pages.company', ['section' => $slug]), false);
        }

        $this->get('/empresa/seccion-inexistente')->assertNotFound();
    }

    public function test_admin_can_register_variable_product_and_customer_can_view_and_quote_variants(): void
    {
        $admin = $this->createUserWithRole('admin');
        $base = $this->createSampleCatalogItem();

        $storeResponse = $this->actingAs($admin)->post(route('admin.products.store'), [
            'sku' => 'SL-COR-TEL-FAM',
            'name' => 'Corredera Telescópica Pesada 45mm Zincada',
            'category_id' => $base['category']->id,
            'brand_id' => $base['brand']->id,
            'dimensions' => '300mm a 500mm',
            'material' => 'Acero laminado en frío zincado',
            'sale_unit' => 'Par',
            'currency' => 'PEN',
            'price_visibility' => 'hide',
            'availability_status' => 'available',
            'status' => 'published',
            'product_type' => 'variable',
            'variants' => [
                [
                    'variant_label' => '300 mm (12")',
                    'sku' => 'SL-COR-TEL-300',
                    'dimensions' => '45 x 300 mm',
                    'material' => 'Acero Zincado',
                    'sale_unit' => 'Par',
                    'box_qty' => 15,
                    'carton_qty' => 150,
                    'price' => 11.50,
                    'availability_status' => 'available',
                ],
                [
                    'variant_label' => '450 mm (18")',
                    'sku' => 'SL-COR-TEL-450',
                    'dimensions' => '45 x 450 mm',
                    'material' => 'Acero Zincado',
                    'sale_unit' => 'Par',
                    'box_qty' => 15,
                    'carton_qty' => 150,
                    'price' => 16.90,
                    'availability_status' => 'available',
                ],
            ],
        ]);

        $storeResponse->assertRedirect();

        $variableProduct = Product::query()->where('sku', 'SL-COR-TEL-FAM')->with('variants')->firstOrFail();
        $this->assertCount(2, $variableProduct->variants);

        // Verify individual product view renders variants dropdown selector and B2B matrix
        $detailResponse = $this->get(route('catalog.show', $variableProduct->slug));
        $detailResponse->assertOk();
        $detailResponse->assertSee('Producto Variable (2 medidas)');
        $detailResponse->assertSee('SL-COR-TEL-300');
        $detailResponse->assertSee('SL-COR-TEL-450');
        $detailResponse->assertSee('300 mm (12")');
        $detailResponse->assertSee('450 mm (18")');
        $detailResponse->assertSee('data-variant-select', false);
        $detailResponse->assertSee('Elige un producto para cotizar');
        $detailResponse->assertSee('data-variant-matrix-form', false);

        // Reject adding variable product without choosing a variant option
        $missingVariantAdd = $this->postJson(route('quote.add'), [
            'product_id' => $variableProduct->id,
            'quantity' => 5,
        ]);
        $missingVariantAdd->assertStatus(422)
            ->assertJsonPath('message', 'Elige un producto para cotizar.');

        // Add multiple variants via B2B Matrix to Quote Cart
        $v300 = $variableProduct->variants->firstWhere('sku', 'SL-COR-TEL-300');
        $v450 = $variableProduct->variants->firstWhere('sku', 'SL-COR-TEL-450');

        $matrixAdd = $this->postJson(route('quote.add'), [
            'product_id' => $variableProduct->id,
            'variant_quantities' => [
                $v300->id => 15,
                $v450->id => 30,
            ],
            'notes' => 'Despacho en cajas cerradas',
        ]);

        $matrixAdd->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('cart_count', 2);

        // Submit formal quote and verify variant SKUs are snapshotted in quote_items
        $submitQuote = $this->post(route('quote.submit'), [
            'contact_name' => 'Ing. Martín Paredes',
            'contact_email' => 'mparedes@mobiliarioperu.pe',
            'contact_phone' => '999888777',
            'company_name' => 'Mobiliario Perú S.A.C.',
            'ruc' => '20556677889',
            'privacy_accepted' => '1',
        ]);

        $submitQuote->assertRedirect();

        $this->assertDatabaseHas('quote_items', [
            'product_id' => $variableProduct->id,
            'sku_snapshot' => 'SL-COR-TEL-300',
            'quantity' => 15,
        ]);

        $this->assertDatabaseHas('quote_items', [
            'product_id' => $variableProduct->id,
            'sku_snapshot' => 'SL-COR-TEL-450',
            'quantity' => 30,
        ]);
    }

    public function test_password_reset_dispatches_email_and_enforces_token_expiration(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'cliente.reset@importhnssanluis.com',
            'password' => Hash::make('OldPassword123!'),
        ]);

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        Mail::assertSent(PasswordResetMail::class, function (PasswordResetMail $mail) use ($user): bool {
            return $mail->hasTo($user->email) && str_contains($mail->resetUrl, 'recuperar-contrasena/');
        });

        // Expired token (> 60 minutes) must be rejected and deleted
        $expiredPlainToken = 'expired-token-1234567890';
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($expiredPlainToken),
                'created_at' => now()->subMinutes(61),
            ]
        );

        $expiredAttempt = $this->from(route('password.reset', ['token' => $expiredPlainToken]))->post(route('password.update'), [
            'token' => $expiredPlainToken,
            'email' => $user->email,
            'password' => 'NewPassword2026!',
            'password_confirmation' => 'NewPassword2026!',
        ]);

        $expiredAttempt->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }
}
