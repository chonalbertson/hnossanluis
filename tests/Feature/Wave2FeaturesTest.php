<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductImportBatch;
use App\Models\ProductSpecification;
use App\Models\Quote;
use App\Services\QuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Wave2FeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_technical_search_autocomplete_returns_published_products_and_categories_and_excludes_drafts(): void
    {
        $base = $this->createSampleCatalogItem([
            'sku' => 'SL-AUTO-001',
            'name' => 'Bisagra Cazoleta Hidráulica Autocompletado',
            'slug' => 'bisagra-cazoleta-hidraulica-autocompletado',
            'status' => 'published',
        ]);

        Product::query()->create([
            'sku' => 'SL-AUTO-DRAFT',
            'name' => 'Bisagra Cazoleta En Borrador Secreto',
            'slug' => 'bisagra-cazoleta-en-borrador-secreto',
            'category_id' => $base['category']->id,
            'sale_unit' => 'Par',
            'status' => 'draft',
        ]);

        Product::query()->create([
            'sku' => 'SL-AUTO-ARCH',
            'name' => 'Bisagra Cazoleta Archivada No Visible',
            'slug' => 'bisagra-cazoleta-archivada-no-visible',
            'category_id' => $base['category']->id,
            'sale_unit' => 'Par',
            'status' => 'archived',
        ]);

        // 1. Short query (< 2 chars) returns empty suggestions
        $shortRes = $this->getJson(route('catalog.suggestions', ['q' => 'B']));
        $shortRes->assertStatus(200)
            ->assertJson([
                'query' => 'B',
                'products' => [],
                'categories' => [],
            ]);

        // 2. Matching query by SKU/name returns published product and active category, excluding draft/archived
        $res = $this->getJson(route('catalog.suggestions', ['q' => 'Bisagra']));
        $res->assertStatus(200)
            ->assertJsonStructure([
                'query',
                'products' => [
                    '*' => ['id', 'sku', 'name', 'slug', 'url', 'category_name', 'brand_name', 'sale_unit', 'availability_label', 'price_formatted', 'image_url'],
                ],
                'categories' => [
                    '*' => ['id', 'name', 'slug', 'url'],
                ],
                'catalog_url',
            ]);

        $skus = collect($res->json('products'))->pluck('sku')->all();
        $this->assertContains('SL-AUTO-001', $skus);
        $this->assertNotContains('SL-AUTO-DRAFT', $skus);
        $this->assertNotContains('SL-AUTO-ARCH', $skus);

        $categorySlugs = collect($res->json('categories'))->pluck('slug')->all();
        $this->assertContains('bisagras-cazoleta', $categorySlugs);
    }

    public function test_technical_product_comparator_enforces_max_4_limit_and_aligns_specifications(): void
    {
        $base = $this->createSampleCatalogItem([
            'sku' => 'SL-CMP-001',
            'name' => 'Corredera Telescópica 300mm',
            'slug' => 'corredera-telescopica-300mm',
        ]);

        $products = [$base['product']];
        for ($i = 2; $i <= 5; $i++) {
            $products[] = Product::query()->create([
                'sku' => sprintf('SL-CMP-%03d', $i),
                'name' => sprintf('Corredera Telescópica %d00mm', $i + 2),
                'slug' => sprintf('corredera-telescopica-%d00mm', $i + 2),
                'category_id' => $base['category']->id,
                'brand_id' => $base['brand']->id,
                'sale_unit' => 'Juego',
                'price' => 15.00 + $i,
                'currency' => 'PEN',
                'price_visibility' => 'show',
                'availability_status' => 'available',
                'status' => 'published',
            ]);
        }

        // Attach technical specifications to product 1 and product 2
        ProductSpecification::query()->create([
            'product_id' => $products[0]->id,
            'spec_group' => 'Dimensiones',
            'spec_key' => 'Largo Nominal',
            'spec_value' => '300 mm',
            'sort_order' => 1,
        ]);
        ProductSpecification::query()->create([
            'product_id' => $products[0]->id,
            'spec_group' => 'Resistencia',
            'spec_key' => 'Capacidad de Carga',
            'spec_value' => '35 kg',
            'sort_order' => 2,
        ]);
        ProductSpecification::query()->create([
            'product_id' => $products[1]->id,
            'spec_group' => 'Dimensiones',
            'spec_key' => 'Largo Nominal',
            'spec_value' => '400 mm',
            'sort_order' => 1,
        ]);
        ProductSpecification::query()->create([
            'product_id' => $products[1]->id,
            'spec_group' => 'Resistencia',
            'spec_key' => 'Capacidad de Carga',
            'spec_value' => '45 kg',
            'sort_order' => 2,
        ]);

        // 1. Add first 4 products via JSON POST
        for ($i = 0; $i < 4; $i++) {
            $addRes = $this->postJson(route('catalog.compare.add'), [
                'product_id' => $products[$i]->id,
            ]);
            $addRes->assertStatus(200)
                ->assertJson([
                    'status' => 'ok',
                    'compare_count' => $i + 1,
                ]);
        }

        // 2. Reject 5th product with 422 limit_reached
        $fifthRes = $this->postJson(route('catalog.compare.add'), [
            'product_id' => $products[4]->id,
        ]);
        $fifthRes->assertStatus(422)
            ->assertJson([
                'status' => 'limit_reached',
                'compare_count' => 4,
            ]);

        // 3. Render comparison matrix and verify aligned specifications
        $comparePage = $this->get(route('catalog.compare'));
        $comparePage->assertStatus(200);
        $comparePage->assertSee('SL-CMP-001');
        $comparePage->assertSee('SL-CMP-002');
        $comparePage->assertSee('SL-CMP-003');
        $comparePage->assertSee('SL-CMP-004');
        $comparePage->assertDontSee('SL-CMP-005');
        $comparePage->assertSee('Largo Nominal');
        $comparePage->assertSee('Capacidad de Carga');
        $comparePage->assertSee('300 mm');
        $comparePage->assertSee('400 mm');
        $comparePage->assertSee('35 kg');
        $comparePage->assertSee('45 kg');

        // 4. Remove one product
        $removeRes = $this->deleteJson(route('catalog.compare.remove', $products[3]->id));
        $removeRes->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'compare_count' => 3,
            ]);

        // 5. Clear entire comparison list
        $clearRes = $this->deleteJson(route('catalog.compare.clear'));
        $clearRes->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'compare_count' => 0,
                'compare_ids' => [],
            ]);
    }

    public function test_public_quote_tracking_shows_status_and_pdf_with_matching_email_and_rejects_wrong_email(): void
    {
        Mail::fake();

        $base = $this->createSampleCatalogItem([
            'sku' => 'SL-TRK-001',
            'name' => 'Cerradura Arquitectónica Seguimiento',
        ]);

        /** @var QuoteService $quoteService */
        $quoteService = app(QuoteService::class);
        $quote = $quoteService->createQuote(
            [
                'contact_name' => 'Arq. Valeria Cáceres',
                'contact_email' => 'vcaceres@estudioarquitectura.pe',
                'contact_phone' => '+51 988 111 222',
                'company_name' => 'Estudio Arquitectura Lima S.A.C.',
                'ruc' => '20609988776',
                'request_type' => 'single',
                'origin' => 'web_catalog',
            ],
            [
                [
                    'product_id' => $base['product']->id,
                    'quantity' => 12,
                    'notes' => 'Acabado acero inoxidable satinado',
                ],
            ]
        );

        // 1. Query with matching reference_code + email shows quote details and status
        $validTrack = $this->get(route('quote.track', [
            'reference_code' => $quote->reference_code,
            'email' => 'vcaceres@estudioarquitectura.pe',
        ]));

        $validTrack->assertStatus(200);
        $validTrack->assertSee($quote->reference_code);
        $validTrack->assertSee('Arq. Valeria Cáceres');
        $validTrack->assertSee('Estudio Arquitectura Lima S.A.C.');
        $validTrack->assertSee('SL-TRK-001');

        // 2. Download PDF with matching email succeeds
        $validPdf = $this->get(route('quote.track.pdf', [
            'reference_code' => $quote->reference_code,
            'email' => 'vcaceres@estudioarquitectura.pe',
        ]));
        $validPdf->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $validPdf->headers->get('Content-Type'));

        // 3. Query with wrong email is rejected and never leaks private customer data
        $invalidTrack = $this->get(route('quote.track', [
            'reference_code' => $quote->reference_code,
            'email' => 'intruso@otrodominio.com',
        ]));

        $invalidTrack->assertStatus(200);
        $invalidTrack->assertSee('No encontramos una solicitud de cotización que coincida');
        $invalidTrack->assertDontSee('Arq. Valeria Cáceres');
        $invalidTrack->assertDontSee('Estudio Arquitectura Lima S.A.C.');
        $invalidTrack->assertDontSee('20609988776');

        // 4. Download PDF with wrong email redirects back with error
        $invalidPdf = $this->get(route('quote.track.pdf', [
            'reference_code' => $quote->reference_code,
            'email' => 'intruso@otrodominio.com',
        ]));
        $invalidPdf->assertRedirect(route('quote.track', ['reference_code' => $quote->reference_code]));
        $invalidPdf->assertSessionHas('error');
    }

    public function test_admin_contact_messages_inbox_lists_marks_as_read_on_view_and_updates_status(): void
    {
        $seller = $this->createUserWithRole('seller');

        $message = ContactMessage::query()->create([
            'name' => 'Ing. Marco Villavicencio',
            'email' => 'mvillavicencio@constructorasur.pe',
            'phone' => '987654321',
            'company' => 'Constructora Del Sur S.A.C.',
            'ruc' => '20543219876',
            'subject' => 'Requerimiento de anclajes para obra vial',
            'message' => 'Solicito contacto con un asesor corporativo para abastecimiento mensual.',
            'status' => 'nuevo',
            'privacy_accepted_at' => now(),
        ]);

        // 1. List contact messages
        $indexRes = $this->actingAs($seller)->get(route('admin.contact-messages.index'));
        $indexRes->assertStatus(200);
        $indexRes->assertSee('Ing. Marco Villavicencio');
        $indexRes->assertSee('Requerimiento de anclajes para obra vial');

        // 2. View message detail automatically transitions status from 'nuevo' to 'leido'
        $showRes = $this->actingAs($seller)->get(route('admin.contact-messages.show', $message));
        $showRes->assertStatus(200);
        $showRes->assertSee('Constructora Del Sur S.A.C.');
        $showRes->assertSee('https://wa.me/51987654321');

        $message->refresh();
        $this->assertSame('leido', $message->status);

        // 3. Change status to 'respondido'
        $statusRes = $this->actingAs($seller)->patch(route('admin.contact-messages.status', $message), [
            'status' => 'respondido',
        ]);
        $statusRes->assertRedirect();
        $statusRes->assertSessionHas('success');

        $message->refresh();
        $this->assertSame('respondido', $message->status);
    }

    public function test_admin_manual_quote_creation_persists_multi_item_whatsapp_quote_with_reference_code(): void
    {
        Mail::fake();

        $seller = $this->createUserWithRole('seller');
        $base = $this->createSampleCatalogItem([
            'sku' => 'SL-MAN-001',
            'name' => 'Tirador Acero Inoxidable 128mm',
            'price' => 12.50,
        ]);

        $product2 = Product::query()->create([
            'sku' => 'SL-MAN-002',
            'name' => 'Perilla Cilíndrica Acero Satinado',
            'slug' => 'perilla-cilindrica-acero-satinado',
            'category_id' => $base['category']->id,
            'brand_id' => $base['brand']->id,
            'sale_unit' => 'Unidad',
            'price' => 6.80,
            'currency' => 'PEN',
            'price_visibility' => 'show',
            'availability_status' => 'available',
            'status' => 'published',
        ]);

        // 1. Access manual creation form
        $createForm = $this->actingAs($seller)->get(route('admin.quotes.create'));
        $createForm->assertStatus(200);
        $createForm->assertSee('SL-MAN-001');

        // 2. Submit manual/WhatsApp quote with 2 products
        $storeRes = $this->actingAs($seller)->post(route('admin.quotes.store'), [
            'contact_name' => 'Roberto Gómez — Carpintería Industrial',
            'contact_email' => 'rgomez@carpinteriaindustrial.pe',
            'contact_phone' => '+51 944 555 666',
            'company_name' => 'Carpintería Industrial Gómez E.I.R.L.',
            'ruc' => '20601122334',
            'origin' => 'whatsapp',
            'currency' => 'PEN',
            'assigned_seller_id' => $seller->id,
            'customer_observations' => 'Pedido recibido por WhatsApp Business para despacho en Villa El Salvador.',
            'items' => [
                [
                    'product_id' => $base['product']->id,
                    'quantity' => 50,
                    'notes' => 'Con tornillos M4x25mm',
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 30,
                    'notes' => 'Empaque individual',
                ],
            ],
        ]);

        $quote = Quote::query()->where('contact_email', 'rgomez@carpinteriaindustrial.pe')->firstOrFail();

        $storeRes->assertRedirect(route('admin.quotes.show', $quote));
        $this->assertMatchesRegularExpression('/^COT-\d{4}-\d{5}$/', $quote->reference_code);
        $this->assertSame('whatsapp', $quote->origin);
        $this->assertSame('multiple', $quote->request_type);
        $this->assertSame($seller->id, $quote->assigned_seller_id);
        $this->assertCount(2, $quote->items);
    }

    public function test_backup_center_permissions_and_artisan_operational_commands_execute_successfully(): void
    {
        $admin = $this->createUserWithRole('admin');
        $seller = $this->createUserWithRole('seller');

        // 1. Seller without settings.manage is denied access to Backup Center
        $this->actingAs($seller)->get(route('admin.backups.index'))->assertStatus(403);
        $this->actingAs($seller)->post(route('admin.backups.create'))->assertStatus(403);

        // 2. Admin can view Backup Center and trigger on-demand backup
        $this->actingAs($admin)->get(route('admin.backups.index'))->assertStatus(200);

        $createBackupRes = $this->actingAs($admin)->post(route('admin.backups.create'));
        $createBackupRes->assertRedirect();
        $createBackupRes->assertSessionHas('success');

        $backupDir = storage_path('app/backups');
        $backupFiles = collect(File::files($backupDir))
            ->filter(fn ($f) => str_ends_with($f->getFilename(), '.zip'))
            ->values();
        $this->assertNotEmpty($backupFiles);

        $latestBackupName = $backupFiles->first()->getFilename();

        // Admin can download and delete the backup
        $downloadRes = $this->actingAs($admin)->get(route('admin.backups.download', $latestBackupName));
        $downloadRes->assertStatus(200);

        $deleteRes = $this->actingAs($admin)->delete(route('admin.backups.destroy', $latestBackupName));
        $deleteRes->assertRedirect();
        $deleteRes->assertSessionHas('success');

        // 3. Test sanluis:prune-data command
        $oldLog = ActivityLog::query()->create([
            'user_id' => $admin->id,
            'action' => 'old.test_action',
            'module' => 'system',
            'description' => 'Registro antiguo para prueba de depuración.',
        ]);
        ActivityLog::query()->whereKey($oldLog->id)->update(['created_at' => now()->subDays(400)]);

        $oldClosedMsg = ContactMessage::query()->create([
            'name' => 'Contacto Antiguo Respondido',
            'email' => 'antiguo@empresa.pe',
            'subject' => 'Consulta cerrada hace más de un año',
            'message' => 'Texto antiguo.',
            'status' => 'respondido',
        ]);
        ContactMessage::query()->whereKey($oldClosedMsg->id)->update(['created_at' => now()->subDays(400)]);

        $oldNewMsg = ContactMessage::query()->create([
            'name' => 'Contacto Antiguo Aún Nuevo',
            'email' => 'pendiente@empresa.pe',
            'subject' => 'Consulta pendiente',
            'message' => 'No debe borrarse porque sigue en estado nuevo.',
            'status' => 'nuevo',
        ]);
        ContactMessage::query()->whereKey($oldNewMsg->id)->update(['created_at' => now()->subDays(400)]);

        $oldPreviewBatch = ProductImportBatch::query()->create([
            'user_id' => $admin->id,
            'filename' => 'lote_abandonado.csv',
            'status' => 'preview',
            'total_rows' => 5,
        ]);
        ProductImportBatch::query()->whereKey($oldPreviewBatch->id)->update(['created_at' => now()->subDays(45)]);

        $pruneExit = Artisan::call('sanluis:prune-data', ['--days' => 365]);
        $this->assertSame(0, $pruneExit);
        $this->assertDatabaseMissing('activity_logs', ['id' => $oldLog->id]);
        $this->assertDatabaseMissing('contact_messages', ['id' => $oldClosedMsg->id]);
        $this->assertDatabaseHas('contact_messages', ['id' => $oldNewMsg->id]);
        $this->assertDatabaseMissing('product_import_batches', ['id' => $oldPreviewBatch->id]);

        // 4. Test sanluis:generate-catalog command (with minimum target count = 100 for fast execution)
        Storage::fake('public');
        $genExit = Artisan::call('sanluis:generate-catalog', ['--count' => 100]);
        $this->assertSame(0, $genExit);
        $this->assertGreaterThanOrEqual(100, Product::query()->where('status', 'published')->count());
        Storage::disk('public')->assertExists('products/svg/bisagra-cazoleta.svg');
        Storage::disk('public')->assertExists('samples/catalogo_importacion_ejemplo.csv');
    }

    public function test_security_headers_middleware_and_gd_image_thumbnail_generation_service(): void
    {
        Storage::fake('public');

        // 1. Verify HTTP Security Headers on public response
        $homeRes = $this->get(route('home'));
        $homeRes->assertStatus(200);
        $homeRes->assertHeader('X-Content-Type-Options', 'nosniff');
        $homeRes->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $homeRes->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertNotEmpty($homeRes->headers->get('Permissions-Policy'));

        // 2. Verify GD Thumbnail generation when uploading a product image in Admin MediaController
        $editor = $this->createUserWithRole('catalog_editor');
        $base = $this->createSampleCatalogItem([
            'sku' => 'SL-THUMB-001',
            'name' => 'Producto Con Miniatura GD',
        ]);

        $uploadedImage = UploadedFile::fake()->image('herraje-industrial.png', 800, 600);

        $uploadRes = $this->actingAs($editor)->post(route('admin.media.store-image'), [
            'product_id' => $base['product']->id,
            'image' => $uploadedImage,
            'alt_text' => 'Fotografía técnica con miniatura optimizada',
            'is_primary' => '1',
        ]);

        $uploadRes->assertRedirect();
        $uploadRes->assertSessionHas('success');

        $productImage = ProductImage::query()
            ->where('product_id', $base['product']->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertNotNull($productImage->file_path);
        $this->assertNotNull($productImage->thumbnail_path);
        Storage::disk('public')->assertExists($productImage->file_path);
        Storage::disk('public')->assertExists($productImage->thumbnail_path);
        $this->assertStringStartsWith('products/thumbs/', $productImage->thumbnail_path);
    }
}
