<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Services\ItalocksCatalogImportService;
use App\Services\ItalocksScraperService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ItalocksCatalogImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_scraper_follows_category_pagination_and_parses_product_sheet(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'italocks.com/ajax/cargar-productos/?pagina=1&categoria=8&marca=' => Http::response(
                '<a class="product-title" href="https://italocks.com/producto/plafon-a/"></a><a onclick="loadPage(2)">2</a>'
            ),
            'italocks.com/ajax/cargar-productos/?pagina=2&categoria=8&marca=' => Http::response(
                '<a class="product-title" href="https://italocks.com/producto/plafon-b/"></a>'
            ),
            'italocks.com/producto/plafon-a/' => Http::response($this->productSheetHtml()),
        ]);

        $scraper = new ItalocksScraperService(0);

        $this->assertSame(
            ['https://italocks.com/producto/plafon-a/', 'https://italocks.com/producto/plafon-b/'],
            $scraper->listCategoryProductUrls(8)
        );

        $product = $scraper->scrapeProduct('https://italocks.com/producto/plafon-a/');

        $this->assertSame('Plafón Elegant negro 36W', $product['name']);
        $this->assertSame('PL-036', $product['sku']);
        $this->assertSame('Eurolux', $product['brand']);
        $this->assertSame(['Aluminio', 'Acrílico'], $product['materials']);
        $this->assertSame(['Negro'], $product['colors']);
        $this->assertSame([
            'https://italocks.com/images/productos/prod1.jpg',
            'https://italocks.com/images/productos/prod2.jpg',
            'https://italocks.com/images/productos/prod3.jpg',
        ], $product['image_urls']);
    }

    public function test_import_builds_hierarchy_with_seo_and_two_highest_resolution_images_without_touching_existing_catalog(): void
    {
        Storage::fake('public');
        Http::preventStrayRequests();
        Http::fake([
            'italocks.com/images/productos/small.jpg' => Http::response(UploadedFile::fake()->image('s.jpg', 300, 300)->getContent()),
            'italocks.com/images/productos/large.jpg' => Http::response(UploadedFile::fake()->image('l.jpg', 1600, 1600)->getContent()),
            'italocks.com/images/productos/medium.jpg' => Http::response(UploadedFile::fake()->image('m.jpg', 900, 900)->getContent()),
        ]);
        $this->app->instance(ItalocksScraperService::class, new ItalocksScraperService(0));

        $existing = $this->createSampleCatalogItem(['sku' => 'PL-036', 'name' => 'Producto propio San Luis']);

        $item = [
            'source_url' => 'https://italocks.com/producto/colgante-a/',
            'source_category' => 'Luminarias',
            'name' => 'Colgante Elegant negro 36W',
            'sku' => 'PL-036',
            'short_description' => 'Colgante Elegant negro 36W',
            'brand' => 'Eurolux',
            'materials' => ['Aluminio'],
            'colors' => ['Negro'],
            'sizes' => [],
            'image_urls' => [
                'https://italocks.com/images/productos/small.jpg',
                'https://italocks.com/images/productos/large.jpg',
                'https://italocks.com/images/productos/medium.jpg',
            ],
        ];

        $summary = app(ItalocksCatalogImportService::class)->import([$item]);

        $this->assertSame(1, $summary['created']);
        $this->assertSame(2, $summary['images_downloaded']);

        $parent = Category::query()->where('slug', 'luminarias')->firstOrFail();
        $this->assertNull($parent->parent_id);
        $subcategory = Category::query()->where('name', 'Luminarias Colgantes')->firstOrFail();
        $this->assertSame($parent->id, $subcategory->parent_id);

        $imported = Product::query()->where('sku', 'PL-036-IT')->firstOrFail();
        $this->assertSame($subcategory->id, $imported->subcategory_id);
        $this->assertSame('Eurolux', $imported->brand->name);
        $this->assertSame('36W', $imported->dimensions);
        $this->assertStringContainsString('Import Hns San Luis', $imported->meta_title);
        $this->assertLessThanOrEqual(160, mb_strlen($imported->meta_description));
        $this->assertStringContainsString('<h3>Características principales</h3>', $imported->detailed_description);

        $images = $imported->images()->orderBy('sort_order')->get();
        $this->assertCount(2, $images);
        $this->assertSame([1600, 900], $images->map(fn ($image) => getimagesize(Storage::disk('public')->path($image->file_path))[0])->all());
        $this->assertTrue($images->first()->is_primary);

        $this->assertSame('Producto propio san luis', $existing['product']->fresh()->name);

        $secondRun = app(ItalocksCatalogImportService::class)->import([$item]);

        $this->assertSame(['created' => 0, 'updated' => 1], array_intersect_key($secondRun, ['created' => 0, 'updated' => 0]));
        $this->assertSame(1, Product::query()->where('sku', 'like', 'PL-036-IT%')->count());
        $this->assertCount(2, $imported->images()->get());
    }

    public function test_variant_without_published_photos_reuses_sibling_model_images(): void
    {
        Storage::fake('public');
        Http::preventStrayRequests();
        Http::fake([
            'italocks.com/images/productos/base.jpg' => Http::response(UploadedFile::fake()->image('b.jpg', 800, 800)->getContent()),
            'italocks.com/images/productos/missing.jpg' => Http::response('<html>No encontrado</html>'),
        ]);
        $this->app->instance(ItalocksScraperService::class, new ItalocksScraperService(0));

        $base = ['source_category' => 'Luminarias', 'brand' => 'Eurolux', 'materials' => [], 'colors' => [], 'sizes' => [], 'short_description' => ''];

        app(ItalocksCatalogImportService::class)->import([
            $base + ['name' => 'Colgante decorativa Shell azul', 'sku' => 'LMP-073', 'image_urls' => ['https://italocks.com/images/productos/base.jpg']],
            $base + ['name' => 'Colgante decorativa Shell azul regulable', 'sku' => 'LMP-073R', 'image_urls' => ['https://italocks.com/images/productos/missing.jpg']],
        ]);

        $baseImage = Product::query()->where('sku', 'LMP-073')->firstOrFail()->images()->sole();
        $variantImage = Product::query()->where('sku', 'LMP-073R')->firstOrFail()->images()->sole();

        $this->assertSame($baseImage->file_path, $variantImage->file_path);
        $this->assertTrue($variantImage->is_primary);
    }

    protected function productSheetHtml(): string
    {
        return <<<'HTML'
            <h2 class="product_title entry-title mb-0">Plafón Elegant negro 36W<br><small>PL-036</small></h2>
            <div class="product-details__short-description"><p>Plafón LED de sobreponer</p></div>
            <div class="product-attributes-section mt-30 mb-30 d-none d-lg-block">
                <span class="brand-badge">Eurolux</span>
                <span class="material-tag primary">Aluminio</span>
                <span class="material-tag secondary">Acrílico</span>
                <span class="color-circle" data-tooltip="Negro"></span>
            </div>
            <div class="easyzoom"><a href="https://italocks.com/images/productos/prod1.jpg"><img src="x"></a></div>
            <ul class="thumbnails easyzoom-gallery-slider">
                <li><a href="https://italocks.com/images/productos/prod1.jpg"></a></li>
                <li><a href="https://italocks.com/images/productos/prod2.jpg"></a></li>
                <li><a href="https://italocks.com/images/productos/prod3.jpg"></a></li>
            </ul>
            HTML;
    }
}
