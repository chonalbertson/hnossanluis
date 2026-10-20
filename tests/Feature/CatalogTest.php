<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImportBatch;
use App\Models\SiteSetting;
use App\Services\CatalogSearchService;
use App\Services\ProductImportExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_displays_paginated_published_products_and_hides_drafts_or_archived(): void
    {
        $base = $this->createSampleCatalogItem([
            'sku' => 'SL-PUB-001',
            'name' => 'Producto Publicado Principal 001',
            'status' => 'published',
        ]);

        for ($i = 2; $i <= 15; $i++) {
            Product::query()->create([
                'sku' => sprintf('SL-PUB-%03d', $i),
                'name' => sprintf('Producto Publicado %03d', $i),
                'slug' => sprintf('producto-publicado-%03d', $i),
                'category_id' => $base['category']->id,
                'brand_id' => $base['brand']->id,
                'sale_unit' => 'Unidad',
                'price' => 20.00 + $i,
                'currency' => 'PEN',
                'price_visibility' => 'show',
                'availability_status' => 'available',
                'status' => 'published',
            ]);
        }

        Product::query()->create([
            'sku' => 'SL-DRAFT-900',
            'name' => 'Producto Borrador Secreto',
            'slug' => 'producto-borrador-secreto',
            'category_id' => $base['category']->id,
            'sale_unit' => 'Unidad',
            'status' => 'draft',
        ]);

        Product::query()->create([
            'sku' => 'SL-ARCH-901',
            'name' => 'Producto Archivado Oculto',
            'slug' => 'producto-archivado-oculto',
            'category_id' => $base['category']->id,
            'sale_unit' => 'Unidad',
            'status' => 'archived',
        ]);

        $responsePage1 = $this->get(route('catalog.index'));
        $responsePage1->assertStatus(200);
        $responsePage1->assertSee('SL-PUB-');
        $responsePage1->assertDontSee('Producto Borrador Secreto');
        $responsePage1->assertDontSee('Producto Archivado Oculto');

        $responsePage2 = $this->get(route('catalog.index', ['page' => 2]));
        $responsePage2->assertStatus(200);
        $responsePage2->assertSee('SL-PUB-');
        $responsePage2->assertDontSee('Producto Borrador Secreto');
    }

    public function test_product_detail_page_displays_technical_specifications_and_downloads_pdf_datasheet(): void
    {
        $data = $this->createSampleCatalogItem([
            'sku' => 'SL-BIS-3500',
            'name' => 'Bisagra Cazoleta Hidráulica 35mm Inox',
            'slug' => 'bisagra-cazoleta-hidraulica-35mm-inox',
            'dimensions' => '35mm x 110°',
            'material' => 'Acero Inoxidable SUS304',
        ]);

        /** @var Product $product */
        $product = $data['product'];
        $product->specifications()->createMany([
            [
                'spec_group' => 'Dimensiones y Montaje',
                'spec_key' => 'Diámetro de Cazoleta',
                'spec_value' => '35',
                'unit' => 'mm',
                'sort_order' => 1,
            ],
            [
                'spec_group' => 'Rendimiento',
                'spec_key' => 'Ciclos de Apertura',
                'spec_value' => '80,000 ciclos',
                'unit' => null,
                'sort_order' => 2,
            ],
        ]);

        $detailResponse = $this->get(route('catalog.show', $product->slug));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('SL-BIS-3500');
        $detailResponse->assertSee('Bisagra cazoleta hidráulica 35mm inox');
        $detailResponse->assertSee('Diámetro de Cazoleta');
        $detailResponse->assertSee('80,000 ciclos');
        $detailResponse->assertSee('Acero Inoxidable SUS304');

        $this->assertSame(1, $product->fresh()->views_count);

        $pdfResponse = $this->get(route('catalog.download-pdf', $product->slug));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString(
            'Ficha-Tecnica-SL-BIS-3500.pdf',
            (string) $pdfResponse->headers->get('Content-Disposition')
        );
    }

    public function test_catalog_search_by_sku_and_by_name(): void
    {
        $this->createSampleCatalogItem([
            'sku' => 'SL-COR-500HD',
            'name' => 'Corredera Telescópica Pesada 500mm Zincada',
            'slug' => 'corredera-telescopica-pesada-500mm-zincada',
        ]);

        $this->createSampleCatalogItem([
            'sku' => 'SL-CER-990X',
            'name' => 'Cerradura Embutir Cilindro Europerfil',
            'slug' => 'cerradura-embutir-cilindro-europerfil',
        ]);

        // Search by exact/partial SKU
        $skuSearch = $this->get(route('catalog.index', ['q' => 'SL-COR-500HD']));
        $skuSearch->assertStatus(200);
        $skuSearch->assertSee('Corredera telescópica pesada 500mm zincada');
        $skuSearch->assertDontSee('Cerradura embutir cilindro europerfil');

        // Search by product name keyword
        $nameSearch = $this->get(route('catalog.index', ['q' => 'Europerfil']));
        $nameSearch->assertStatus(200);
        $nameSearch->assertSee('SL-CER-990X');
        $nameSearch->assertDontSee('SL-COR-500HD');
    }

    public function test_catalog_combined_filters_by_category_subcategory_brand_availability_and_specs(): void
    {
        $catHerrajes = Category::query()->create([
            'name' => 'Herrajes Industriales',
            'slug' => 'herrajes-industriales',
            'is_active' => true,
        ]);
        $subCorrederas = Category::query()->create([
            'parent_id' => $catHerrajes->id,
            'name' => 'Correderas de Carga',
            'slug' => 'correderas-de-carga',
            'is_active' => true,
        ]);
        $catFijaciones = Category::query()->create([
            'name' => 'Fijaciones y Anclajes',
            'slug' => 'fijaciones-y-anclajes',
            'is_active' => true,
        ]);

        $brandHafele = Brand::query()->create([
            'name' => 'Hafele Pro',
            'slug' => 'hafele-pro',
            'is_active' => true,
        ]);
        $brandFischer = Brand::query()->create([
            'name' => 'Fischer Fixings',
            'slug' => 'fischer-fixings',
            'is_active' => true,
        ]);

        $targetProduct = Product::query()->create([
            'sku' => 'SL-TARGET-500',
            'name' => 'Corredera Extracción Total 500mm 45kg',
            'slug' => 'corredera-extraccion-total-500mm-45kg',
            'category_id' => $catHerrajes->id,
            'subcategory_id' => $subCorrederas->id,
            'brand_id' => $brandHafele->id,
            'sale_unit' => 'Juego',
            'availability_status' => 'available',
            'status' => 'published',
        ]);
        $targetProduct->specifications()->create([
            'spec_group' => 'General',
            'spec_key' => 'Capacidad de Carga',
            'spec_value' => '45 kg',
        ]);

        // Same category/subcategory/brand but different availability ('on_request')
        Product::query()->create([
            'sku' => 'SL-OTHER-AVAIL',
            'name' => 'Corredera Oculta Especial Bajo Pedido',
            'slug' => 'corredera-oculta-especial-bajo-pedido',
            'category_id' => $catHerrajes->id,
            'subcategory_id' => $subCorrederas->id,
            'brand_id' => $brandHafele->id,
            'sale_unit' => 'Juego',
            'availability_status' => 'on_request',
            'status' => 'published',
        ]);

        // Different category and brand
        Product::query()->create([
            'sku' => 'SL-ANCHOR-12',
            'name' => 'Taco Expansivo Metálico M12',
            'slug' => 'taco-expansivo-metalico-m12',
            'category_id' => $catFijaciones->id,
            'brand_id' => $brandFischer->id,
            'sale_unit' => 'Caja x 50',
            'availability_status' => 'available',
            'status' => 'published',
        ]);

        $response = $this->get(route('catalog.index', [
            'category' => 'herrajes-industriales',
            'subcategory' => 'correderas-de-carga',
            'brand' => 'hafele-pro',
            'availability' => 'available',
            'spec_key' => 'Capacidad de Carga',
            'spec_value' => '45 kg',
        ]));

        $response->assertStatus(200);
        $response->assertSee('SL-TARGET-500');
        $response->assertDontSee('SL-OTHER-AVAIL');
        $response->assertDontSee('SL-ANCHOR-12');
    }

    public function test_price_visibility_rules_individual_and_global_never_showing_zero_or_null(): void
    {
        $base = $this->createSampleCatalogItem([
            'sku' => 'SL-PRICE-SHOW',
            'name' => 'Producto Precio Visible',
            'slug' => 'producto-precio-visible',
            'price' => 145.50,
            'currency' => 'PEN',
            'price_visibility' => 'show',
        ]);

        $productShow = $base['product'];

        $productHide = Product::query()->create([
            'sku' => 'SL-PRICE-HIDE',
            'name' => 'Producto Precio Oculto',
            'slug' => 'producto-precio-oculto',
            'category_id' => $base['category']->id,
            'sale_unit' => 'Unidad',
            'price' => 89.00,
            'currency' => 'PEN',
            'price_visibility' => 'hide',
            'availability_status' => 'available',
            'status' => 'published',
        ]);

        $productZero = Product::query()->create([
            'sku' => 'SL-PRICE-ZERO',
            'name' => 'Producto Precio Cero',
            'slug' => 'producto-precio-cero',
            'category_id' => $base['category']->id,
            'sale_unit' => 'Unidad',
            'price' => 0,
            'currency' => 'PEN',
            'price_visibility' => 'show',
            'availability_status' => 'available',
            'status' => 'published',
        ]);

        $productNull = Product::query()->create([
            'sku' => 'SL-PRICE-NULL',
            'name' => 'Producto Sin Precio',
            'slug' => 'producto-sin-precio',
            'category_id' => $base['category']->id,
            'sale_unit' => 'Unidad',
            'price' => null,
            'currency' => 'PEN',
            'price_visibility' => 'inherit',
            'availability_status' => 'on_request',
            'status' => 'published',
        ]);

        // 1. Global mode 'mixed'
        SiteSetting::setValue('commercial_price_display_mode', 'mixed', 'commercial');
        $this->assertTrue($productShow->shouldShowPrice());
        $this->assertSame('S/ 145.50', $productShow->formattedPrice());
        $this->assertFalse($productHide->shouldShowPrice());
        $this->assertNull($productHide->formattedPrice());
        $this->assertFalse($productZero->shouldShowPrice());
        $this->assertNull($productZero->formattedPrice());
        $this->assertFalse($productNull->shouldShowPrice());
        $this->assertNull($productNull->formattedPrice());

        // 2. Global mode 'hide_all' overrides individual 'show'
        SiteSetting::setValue('commercial_price_display_mode', 'hide_all', 'commercial');
        $this->assertFalse($productShow->shouldShowPrice());
        $this->assertNull($productShow->formattedPrice());

        // Verify product detail page in 'hide_all' does not render S/ 145.50
        $resHideAll = $this->get(route('catalog.show', $productShow->slug));
        $resHideAll->assertStatus(200);
        $resHideAll->assertDontSee('S/ 145.50');

        // 3. Global mode 'show_all' shows 'show' and 'inherit' with price > 0, but never 'hide' or 0/null
        SiteSetting::setValue('commercial_price_display_mode', 'show_all', 'commercial');
        $this->assertTrue($productShow->shouldShowPrice());
        $this->assertFalse($productHide->shouldShowPrice());
        $this->assertFalse($productZero->shouldShowPrice());
        $this->assertFalse($productNull->shouldShowPrice());

        // 4. Default mode 'mixed': catalog hides prices unless Admin enables product by product via toggle
        SiteSetting::setValue('commercial_price_display_mode', 'mixed', 'commercial');
        $productShow->update(['price_visibility' => 'hide']);
        $this->get(route('catalog.index'))
            ->assertStatus(200)
            ->assertDontSee('S/ 145.50')
            ->assertDontSee('S/ 89.00');

        $editor = $this->createUserWithRole('catalog_editor');
        $this->actingAs($editor)
            ->patch(route('admin.products.toggle-price', $productHide))
            ->assertRedirect();

        $this->assertSame('show', $productHide->fresh()->price_visibility);
        $this->get(route('catalog.index'))
            ->assertStatus(200)
            ->assertSee('S/ 89.00')
            ->assertDontSee('S/ 145.50');
    }

    public function test_admin_can_create_edit_archive_and_restore_products_and_rejects_duplicate_sku(): void
    {
        Storage::fake('public');

        $editor = $this->createUserWithRole('catalog_editor');
        $taxonomy = $this->createSampleCatalogItem([
            'sku' => 'SL-BASE-001',
            'name' => 'Producto Base Existente',
        ]);

        // 1. Create a new product via Admin POST
        $createResponse = $this->actingAs($editor)->post(route('admin.products.store'), [
            'sku' => 'sl-nuevo-777',
            'name' => 'Cerradura Arquitectónica Acero Inox',
            'short_description' => 'Cerradura grado 2 para puertas cortafuego y alto tráfico.',
            'detailed_description' => 'Cuerpo de acero inoxidable 304 y cilindro de latón macizo.',
            'brand_id' => $taxonomy['brand']->id,
            'category_id' => $taxonomy['category']->id,
            'subcategory_id' => $taxonomy['subcategory']->id,
            'dimensions' => 'Backset 60mm',
            'material' => 'Acero Inoxidable SUS304',
            'sale_unit' => 'Unidad',
            'price' => '189.90',
            'currency' => 'PEN',
            'price_visibility' => 'show',
            'availability_status' => 'available',
            'status' => 'published',
            'is_featured' => '1',
            'primary_image' => UploadedFile::fake()->image('cerradura.jpg', 600, 600),
            'technical_pdf' => UploadedFile::fake()->create('ficha-cerradura.pdf', 256, 'application/pdf'),
            'specs' => [
                ['spec_group' => 'Certificación', 'spec_key' => 'Grado ANSI', 'spec_value' => 'Grado 2', 'unit' => ''],
                ['spec_group' => 'Medidas', 'spec_key' => 'Espesor de Puerta', 'spec_value' => '35 - 45', 'unit' => 'mm'],
            ],
        ]);

        $newProduct = Product::query()->where('sku', 'SL-NUEVO-777')->first();
        $this->assertNotNull($newProduct);
        $createResponse->assertRedirect(route('admin.products.edit', $newProduct));
        $this->assertEquals(189.90, (float) $newProduct->price);
        $this->assertCount(2, $newProduct->specifications);
        $this->assertCount(1, $newProduct->images);
        $this->assertCount(1, $newProduct->documents);

        // 2. Reject creation with duplicate SKU
        $dupCreateResponse = $this->actingAs($editor)->post(route('admin.products.store'), [
            'sku' => 'SL-NUEVO-777',
            'name' => 'Intento Duplicado',
            'category_id' => $taxonomy['category']->id,
            'sale_unit' => 'Unidad',
            'currency' => 'PEN',
            'price_visibility' => 'inherit',
            'availability_status' => 'available',
            'status' => 'published',
        ]);
        $dupCreateResponse->assertSessionHasErrors(['sku']);

        // 3. Edit product
        $updateResponse = $this->actingAs($editor)->put(route('admin.products.update', $newProduct), [
            'sku' => 'SL-NUEVO-777',
            'name' => 'Cerradura Arquitectónica Acero Inox Pro',
            'category_id' => $taxonomy['category']->id,
            'sale_unit' => 'Unidad',
            'price' => '210.00',
            'currency' => 'PEN',
            'price_visibility' => 'show',
            'availability_status' => 'on_request',
            'status' => 'published',
            'specs' => [
                ['spec_group' => 'Certificación', 'spec_key' => 'Grado ANSI', 'spec_value' => 'Grado 1', 'unit' => ''],
            ],
        ]);
        $updateResponse->assertRedirect(route('admin.products.edit', $newProduct));
        $newProduct->refresh();
        $this->assertSame('Cerradura arquitectónica acero inox pro', $newProduct->name);
        $this->assertEquals(210.00, (float) $newProduct->price);
        $this->assertSame('on_request', $newProduct->availability_status);
        $this->assertCount(1, $newProduct->specifications);

        // 4. Reject edit when trying to use another product's existing SKU
        $dupUpdateResponse = $this->actingAs($editor)->put(route('admin.products.update', $newProduct), [
            'sku' => 'SL-BASE-001',
            'name' => 'Cerradura Conflicto SKU',
            'category_id' => $taxonomy['category']->id,
            'sale_unit' => 'Unidad',
            'currency' => 'PEN',
            'price_visibility' => 'show',
            'availability_status' => 'available',
            'status' => 'published',
        ]);
        $dupUpdateResponse->assertSessionHasErrors(['sku']);

        // 5. Archive (soft delete) product
        $archiveResponse = $this->actingAs($editor)->delete(route('admin.products.destroy', $newProduct));
        $archiveResponse->assertRedirect(route('admin.products.index'));
        $this->assertSoftDeleted('products', ['id' => $newProduct->id]);
        $this->assertSame('archived', Product::withTrashed()->find($newProduct->id)->status);

        // 6. Restore product
        $restoreResponse = $this->actingAs($editor)->post(route('admin.products.restore', $newProduct->id));
        $restoreResponse->assertRedirect(route('admin.products.index'));
        $this->assertFalse($newProduct->fresh()->trashed());
        $this->assertSame('published', $newProduct->fresh()->status);

        // 7. Verify Eliminar button appears on admin product list, detail, and edit pages
        $this->actingAs($editor)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee(route('admin.products.force-destroy', $newProduct->id), false);

        $this->actingAs($editor)
            ->get(route('admin.products.show', $newProduct))
            ->assertOk()
            ->assertSee(route('admin.products.force-destroy', $newProduct->id), false);

        $this->actingAs($editor)
            ->get(route('admin.products.edit', $newProduct))
            ->assertOk()
            ->assertSee(route('admin.products.force-destroy', $newProduct->id), false);

        // 8. Permanently delete product and verify all images, thumbnails, PDFs, specs, and records are removed
        $imageRecord = $newProduct->images()->firstOrFail();
        $docRecord = $newProduct->documents()->firstOrFail();
        $imagePath = $imageRecord->file_path;
        $thumbPath = $imageRecord->thumbnail_path;
        $pdfPath = $docRecord->file_path;

        $taxonomy['category']->update(['image_path' => $imagePath]);

        Storage::disk('public')->assertExists($imagePath);
        Storage::disk('public')->assertExists($pdfPath);

        $seller = $this->createUserWithRole('seller');
        $this->actingAs($seller)
            ->delete(route('admin.products.force-destroy', $newProduct->id))
            ->assertForbidden();

        $forceDeleteResponse = $this->actingAs($editor)
            ->delete(route('admin.products.force-destroy', $newProduct->id));

        $forceDeleteResponse->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseMissing('products', ['id' => $newProduct->id]);
        $this->assertDatabaseMissing('product_images', ['product_id' => $newProduct->id]);
        $this->assertDatabaseMissing('product_documents', ['product_id' => $newProduct->id]);
        $this->assertDatabaseMissing('product_specifications', ['product_id' => $newProduct->id]);
        Storage::disk('public')->assertMissing($imagePath);
        if ($thumbPath) {
            Storage::disk('public')->assertMissing($thumbPath);
        }
        Storage::disk('public')->assertMissing($pdfPath);
        $this->assertNull($taxonomy['category']->fresh()->image_path);
    }

    public function test_csv_mass_import_valid_preview_execution_and_error_report_for_invalid_or_duplicate_rows(): void
    {
        $editor = $this->createUserWithRole('catalog_editor');

        $existing = $this->createSampleCatalogItem([
            'sku' => 'SL-EXIST-100',
            'name' => 'Producto Existente Antes de Importar',
            'price' => 30.00,
        ])['product'];

        $csvLines = [
            implode(',', ProductImportExportServiceHeadersHelper::headers()),
            // Row 2: Valid new product
            'SL-CSV-NEW01,"Corredera Telescópica 450mm Zincada","Herrajes para Muebles","Correderas","Ducasse Industrial","Corredera 450mm","Extracción total","450x45mm","Acero Zincado","Juego","24.50","PEN","show","available","published","Largo:450 mm|Carga:35 kg"',
            // Row 3: Valid update to existing SKU
            'SL-EXIST-100,"Producto Existente Actualizado por CSV","Herrajes para Muebles","Bisagras Cazoleta","Ducasse Industrial","Descripción actualizada","Detalle actualizado","35mm","Acero Inox","Par","36.90","PEN","show","available","published","Acabado:Níquel Satinado"',
            // Row 4: Duplicate SKU inside the same CSV file (must be rejected)
            'SL-CSV-NEW01,"Producto Con SKU Repetido En CSV","Herrajes para Muebles","","","","","","","Unidad","15.00","PEN","show","available","published",""',
            // Row 5: Invalid row (missing required name and negative price)
            'SL-CSV-BAD99,"","Herrajes para Muebles","","","","","","","Unidad","-50.00","PEN","show","available","published",""',
        ];

        $csvContent = implode("\n", $csvLines);
        $uploadedFile = UploadedFile::fake()->createWithContent('importacion-lote-prueba.csv', $csvContent);

        // 1. Analyze CSV -> creates Preview batch without mutating products yet
        $analyzeResponse = $this->actingAs($editor)->post(route('admin.imports.analyze'), [
            'csv_file' => $uploadedFile,
        ]);

        $batch = ProductImportBatch::query()->latest('id')->first();
        $this->assertNotNull($batch);
        $analyzeResponse->assertRedirect(route('admin.imports.preview', $batch));

        $this->assertSame('preview', $batch->status);
        $this->assertSame(4, $batch->total_rows);
        $this->assertSame(1, $batch->to_create_count);
        $this->assertSame(1, $batch->to_update_count);
        $this->assertSame(2, $batch->rejected_count);

        // Existing product is NOT overwritten before explicit confirmation
        $this->assertSame('Producto existente antes de importar', $existing->fresh()->name);
        $this->assertNull(Product::query()->where('sku', 'SL-CSV-NEW01')->first());

        // 2. Execute confirmed batch
        $executeResponse = $this->actingAs($editor)->post(route('admin.imports.execute', $batch));
        $executeResponse->assertRedirect(route('admin.imports.preview', $batch));

        $batch->refresh();
        $this->assertSame('completed', $batch->status);
        $this->assertSame(1, $batch->created_count);
        $this->assertSame(1, $batch->updated_count);

        // Verify new product created with dynamic specs
        $createdProduct = Product::query()->where('sku', 'SL-CSV-NEW01')->first();
        $this->assertNotNull($createdProduct);
        $this->assertSame('Corredera telescópica 450mm zincada', $createdProduct->name);
        $this->assertEquals(24.50, (float) $createdProduct->price);
        $this->assertCount(2, $createdProduct->specifications);

        // Verify existing product updated cleanly
        $existing->refresh();
        $this->assertSame('Producto existente actualizado por csv', $existing->name);
        $this->assertEquals(36.90, (float) $existing->price);

        // Verify invalid product was NOT created
        $this->assertNull(Product::query()->where('sku', 'SL-CSV-BAD99')->first());

        // 3. Download error report CSV
        $errorsCsvResponse = $this->actingAs($editor)->get(route('admin.imports.errors', $batch));
        $errorsCsvResponse->assertStatus(200);
        $errorsCsvContent = $errorsCsvResponse->getContent();
        $this->assertStringContainsString('SL-CSV-NEW01', $errorsCsvContent);
        $this->assertStringContainsString('SKU duplicado dentro del mismo archivo CSV', $errorsCsvContent);
        $this->assertStringContainsString('SL-CSV-BAD99', $errorsCsvContent);
        $this->assertStringContainsString('El nombre del producto es obligatorio', $errorsCsvContent);

        // 4. Verify Template & Catalog Export endpoints
        $this->actingAs($editor)->get(route('admin.imports.template'))->assertStatus(200);
        $exportResponse = $this->actingAs($editor)->get(route('admin.imports.export'));
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('SL-CSV-NEW01', $exportResponse->getContent());
    }

    public function test_catalog_search_performance_and_absence_of_n_plus_one_queries_with_1000_products(): void
    {
        $taxonomy = $this->createSampleCatalogItem([
            'sku' => 'SL-PERF-0000',
            'name' => 'Bisagra Industrial Base Rendimiento',
        ]);

        $now = now();
        $bulkProducts = [];
        for ($i = 1; $i <= 1050; $i++) {
            $bulkProducts[] = [
                'sku' => sprintf('SL-PERF-%04d', $i),
                'name' => sprintf('Bisagra Industrial Pesada Modelo %04d Acero', $i),
                'slug' => sprintf('bisagra-industrial-pesada-modelo-%04d-acero', $i),
                'short_description' => 'Herraje técnico para mobiliario y construcción.',
                'brand_id' => $taxonomy['brand']->id,
                'category_id' => $taxonomy['category']->id,
                'subcategory_id' => $taxonomy['subcategory']->id,
                'dimensions' => '35mm x 110°',
                'material' => 'Acero Inoxidable',
                'sale_unit' => 'Par',
                'price' => 15.00 + ($i % 50),
                'currency' => 'PEN',
                'price_visibility' => 'show',
                'availability_status' => ($i % 3 === 0) ? 'on_request' : 'available',
                'status' => 'published',
                'is_featured' => ($i % 20 === 0),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($bulkProducts, 250) as $chunk) {
            Product::query()->insert($chunk);
        }

        $this->assertGreaterThanOrEqual(1050, Product::query()->count());

        DB::flushQueryLog();
        DB::enableQueryLog();

        $startTime = hrtime(true);
        /** @var CatalogSearchService $searchService */
        $searchService = app(CatalogSearchService::class);
        $paginator = $searchService->search([
            'q' => 'Bisagra',
            'category' => $taxonomy['category']->slug,
            'availability' => 'available',
        ], 12);

        // Access relations on all 12 items of the page to verify zero lazy-loading (N+1) queries
        foreach ($paginator->items() as $item) {
            $_ = $item->category?->name;
            $_ = $item->subcategory?->name;
            $_ = $item->brand?->name;
            $_ = $item->primaryImageUrl();
            $_ = $item->specifications->count();
        }

        $elapsedMs = (hrtime(true) - $startTime) / 1_000_000;
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(12, $paginator->items());
        $this->assertGreaterThan(700, $paginator->total());
        // Expected queries: 1 category lookup + 1 children + 1 count + 1 page select + 5 eager loads (category, subcategory, brand, images, specifications) = max 9 queries regardless of page size
        $this->assertLessThanOrEqual(10, count($queries), 'Se detectaron consultas N+1 en el listado del catálogo.');
        $this->assertLessThan(500.0, $elapsedMs, sprintf('La búsqueda con >1,000 productos tardó %.2f ms (límite 500 ms).', $elapsedMs));
    }
}

class ProductImportExportServiceHeadersHelper
{
    public static function headers(): array
    {
        return ProductImportExportService::STANDARD_HEADERS;
    }
}
