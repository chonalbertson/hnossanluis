<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSpecification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportScrapedCatalogCommand extends Command
{
    protected $signature = 'sanluis:import-real-catalog {--source= : Ruta opcional al JSON procesado}';

    protected $description = 'Limpia el catálogo demostrativo e importa el catálogo técnico real completo (categorías, subcategorías, marcas, productos, especificaciones, SEO e imágenes HD) bajo la identidad de Import Hns San Luis.';

    public function handle(): int
    {
        $defaultDatasetPath = database_path('seeders/data/sanluis_real_catalog.json');
        $sourcePath = $this->option('source') ?: $defaultDatasetPath;

        if (! is_file($sourcePath)) {
            $this->error("No se encontró el archivo de datos en: {$sourcePath}");

            return self::FAILURE;
        }

        $rawItems = json_decode((string) file_get_contents($sourcePath), true);
        if (! is_array($rawItems) || empty($rawItems)) {
            $this->error('El archivo JSON del catálogo está vacío o es inválido.');

            return self::FAILURE;
        }

        $this->info('1. Eliminando productos, categorías, subcategorías y marcas anteriores para una implementación 100% limpia...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('product_specifications')->truncate();
        DB::table('product_images')->truncate();
        DB::table('product_documents')->truncate();
        DB::table('quote_items')->truncate();
        DB::table('quote_status_histories')->truncate();
        DB::table('internal_notes')->truncate();
        DB::table('quotes')->truncate();
        DB::table('product_import_batches')->truncate();
        DB::table('products')->truncate();
        DB::table('categories')->truncate();
        DB::table('brands')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Define Parent Category -> Subcategories Hierarchy
        $hierarchy = $this->getCategoryHierarchy();
        $parentCategoryModels = [];
        $subCategoryModels = [];
        $subSlugToParentSlug = [];

        $this->info('2. Creando jerarquía de Categorías y Subcategorías con SEO de Import Hns San Luis...');

        $sortParent = 1;
        foreach ($hierarchy as $parentSlug => $parentData) {
            $parentCat = Category::query()->create([
                'parent_id' => null,
                'name' => $parentData['name'],
                'slug' => $parentSlug,
                'description' => $parentData['description'],
                'is_active' => true,
                'sort_order' => $sortParent++,
                'meta_title' => "{$parentData['name']} | Import Hns San Luis",
                'meta_description' => "Catálogo técnico de {$parentData['name']} en Import Hns San Luis. Cotización rápida para empresas, constructoras, carpinterías y profesionales en Perú.",
            ]);
            $parentCategoryModels[$parentSlug] = $parentCat;

            $sortSub = 1;
            foreach ($parentData['subcategories'] as $subSlug => $subName) {
                $subCat = Category::query()->create([
                    'parent_id' => $parentCat->id,
                    'name' => $subName,
                    'slug' => $subSlug,
                    'description' => "Línea especializada de {$subName} distribuida por Import Hns San Luis.",
                    'is_active' => true,
                    'sort_order' => $sortSub++,
                    'meta_title' => "{$subName} - {$parentData['name']} | Import Hns San Luis",
                    'meta_description' => "Solicita cotización de {$subName} en Import Hns San Luis. Especificaciones técnicas y atención comercial en todo el Perú.",
                ]);
                $subCategoryModels[$subSlug] = $subCat;
                $subSlugToParentSlug[$subSlug] = $parentSlug;
            }
        }

        // 3. Create Brands and map products
        $this->info('3. Registrando Marcas verificadas y procesando los '.count($rawItems).' productos técnicos...');

        $brandModels = [];
        $getOrCreateBrand = function (string $brandName) use (&$brandModels): Brand {
            $slug = Str::slug($brandName);
            if (! isset($brandModels[$slug])) {
                $logoRelPath = is_file(storage_path("app/public/brands/logo-{$slug}.webp"))
                    ? "brands/logo-{$slug}.webp"
                    : null;

                $brandModels[$slug] = Brand::query()->create([
                    'name' => $brandName,
                    'slug' => $slug,
                    'logo_path' => $logoRelPath,
                    'description' => "Productos y soluciones técnicas de la marca {$brandName} comercializados por Import Hns San Luis.",
                    'is_verified' => true,
                    'is_active' => true,
                    'sort_order' => count($brandModels) + 1,
                ]);
            }

            return $brandModels[$slug];
        };

        $usedSkus = [];
        $usedSlugs = [];
        $cleanDatasetForSeeder = [];
        $totalSpecsCreated = 0;
        $totalImagesLinked = 0;
        $featuredCount = 0;

        foreach ($rawItems as $index => $item) {
            $rawTitle = $this->sanitizeText((string) ($item['title'] ?? ''));
            if ($rawTitle === '') {
                continue;
            }

            // Format title cleanly
            $cleanTitle = $this->formatProductTitle($rawTitle);

            // Resolve Brand (100% linked) and exclude Truper products completely
            $brandName = $this->resolveBrandName($item, $cleanTitle);
            if (
                strcasecmp($brandName, 'Truper') === 0
                || stripos($rawTitle, 'truper') !== false
                || stripos((string) ($item['slug'] ?? ''), 'truper') !== false
            ) {
                continue;
            }

            $brandModel = $getOrCreateBrand($brandName);

            // Resolve Category & Subcategory
            [$parentCat, $subCat] = $this->resolveCategories(
                $item,
                $cleanTitle,
                $parentCategoryModels,
                $subCategoryModels,
                $subSlugToParentSlug
            );

            // Resolve unique SKU
            $rawSku = strtoupper(trim((string) ($item['sku'] ?? '')));
            $rawSku = preg_replace('/[^A-Z0-9\-\.\/]/', '', $rawSku);
            if ($rawSku === '') {
                $rawSku = 'SL-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);
            }
            $sku = $rawSku;
            $skuCounter = 2;
            while (isset($usedSkus[$sku])) {
                $sku = $rawSku.'-'.$skuCounter++;
            }
            $usedSkus[$sku] = true;

            // Resolve unique Slug (removing any external store trace)
            $baseSlug = Str::slug($cleanTitle);
            if ($baseSlug === '') {
                $baseSlug = 'producto-'.strtolower($sku);
            }
            $slug = $baseSlug;
            $slugCounter = 2;
            while (isset($usedSlugs[$slug])) {
                $slug = $baseSlug.'-'.strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $sku));
                if (isset($usedSlugs[$slug])) {
                    $slug = $baseSlug.'-'.$slugCounter++;
                }
            }
            $usedSlugs[$slug] = true;

            // Parse Description, Specifications, Dimensions, Material, Sale Unit
            $rawHtmlSource = (string) ($item['raw_description_html'] ?? $item['description_html'] ?? '');
            $sanitizedRawHtml = (string) preg_replace(
                '/cloti\s*xpress|clotixpress\.com\.pe|ferreter[ií]a\s*clotixpress|savec\.pe|sistema\.savec\.pe|\bsavec\b|carpintec\.com\.pe|\bcarpintec\b/iu',
                'Import Hns San Luis',
                $rawHtmlSource
            );

            $parsedDesc = $this->parseDescriptionAndSpecs(
                $sanitizedRawHtml,
                $cleanTitle,
                $brandName,
                $item['weight_kg'] ?? null
            );

            if (! empty($item['parsed_specs']) && is_array($item['parsed_specs'])) {
                $parsedDesc['specifications'] = $item['parsed_specs'];
            }

            $price = isset($item['price']) ? round((float) $item['price'], 2) : null;
            $isAvailable = (bool) ($item['is_available'] ?? true);
            $availabilityStatus = $isAvailable ? 'available' : 'on_request';

            $colSlugs = array_keys($item['collections'] ?? []);
            $isFeatured = in_array('destacados', $colSlugs, true)
                || in_array('productos-destacados', $colSlugs, true)
                || ($featuredCount < 16 && $isAvailable && ! empty($item['local_images']));
            if ($isFeatured) {
                $featuredCount++;
            }

            // Build clean SEO metadata for Import Hns San Luis
            $metaTitle = Str::limit("{$cleanTitle} | {$brandName} - Import Hns San Luis", 155, '');
            $metaDescription = Str::limit(
                "Cotiza {$cleanTitle} (SKU: {$sku}, Marca: {$brandName}) en Import Hns San Luis. {$parsedDesc['short_description']}",
                310,
                ''
            );

            $priceVisibility = in_array($item['price_visibility'] ?? 'hide', ['show', 'hide', 'inherit'], true)
                ? (string) ($item['price_visibility'] ?? 'hide')
                : 'hide';

            $product = Product::query()->create([
                'sku' => $sku,
                'name' => $cleanTitle,
                'slug' => $slug,
                'short_description' => $parsedDesc['short_description'],
                'detailed_description' => $parsedDesc['detailed_description'],
                'brand_id' => $brandModel->id,
                'category_id' => $parentCat->id,
                'subcategory_id' => $subCat?->id,
                'dimensions' => $parsedDesc['dimensions'],
                'material' => $parsedDesc['material'],
                'sale_unit' => $parsedDesc['sale_unit'],
                'price' => ($price !== null && $price > 0) ? $price : 0,
                'currency' => 'PEN',
                'price_visibility' => $priceVisibility,
                'availability_status' => $availabilityStatus,
                'status' => 'published',
                'is_featured' => $isFeatured,
                'views_count' => random_int(15, 240),
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
            ]);

            // Insert Technical Specifications
            $specSort = 1;
            $specsBatch = [];
            foreach ($parsedDesc['specifications'] as $spec) {
                $specsBatch[] = [
                    'product_id' => $product->id,
                    'spec_group' => $spec['group'],
                    'spec_key' => $spec['key'],
                    'spec_value' => $spec['value'],
                    'unit' => $spec['unit'],
                    'sort_order' => $specSort++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $totalSpecsCreated++;
            }
            if (! empty($specsBatch)) {
                ProductSpecification::query()->insert($specsBatch);
            }

            // Link up to 2 high-resolution local images
            $localImages = array_slice($item['local_images'] ?? [], 0, 2);
            foreach ($localImages as $imgIdx => $imgData) {
                ProductImage::query()->create([
                    'product_id' => $product->id,
                    'file_path' => $imgData['file_path'],
                    'thumbnail_path' => $imgData['thumbnail_path'] ?? $imgData['file_path'],
                    'alt_text' => "{$cleanTitle} - {$brandName} (Imagen ".($imgIdx + 1).') | Import Hns San Luis',
                    'is_primary' => $imgIdx === 0,
                    'sort_order' => $imgIdx + 1,
                ]);
                $totalImagesLinked++;
            }

            // Set primary image as category image if category doesn't have one yet
            if (! empty($localImages[0]['file_path'])) {
                if (! $parentCat->image_path) {
                    $parentCat->update(['image_path' => $localImages[0]['file_path']]);
                }
                if ($subCat && ! $subCat->image_path) {
                    $subCat->update(['image_path' => $localImages[0]['file_path']]);
                }
            }

            // Save clean zero-trace entry for DatabaseSeeder persistence
            $cleanDatasetForSeeder[] = [
                'sku' => $sku,
                'slug' => $slug,
                'title' => $cleanTitle,
                'brand' => $brandName,
                'category_slug' => $parentCat->slug,
                'subcategory_slug' => $subCat?->slug,
                'price' => $price,
                'price_visibility' => 'hide',
                'is_available' => $isAvailable,
                'weight_kg' => $item['weight_kg'] ?? null,
                'raw_description_html' => $sanitizedRawHtml,
                'description_html' => $parsedDesc['detailed_description'],
                'parsed_specs' => $parsedDesc['specifications'],
                'collections' => $item['collections'] ?? [],
                'local_images' => $localImages,
            ];
        }

        // Remove any subcategories or parent categories that have 0 products
        Category::query()
            ->whereNotNull('parent_id')
            ->whereDoesntHave('subProducts')
            ->delete();

        Category::query()
            ->whereNull('parent_id')
            ->whereDoesntHave('products')
            ->delete();

        // Persist clean zero-trace dataset in database/seeders/data/sanluis_real_catalog.json
        File::ensureDirectoryExists(dirname($defaultDatasetPath));
        file_put_contents(
            $defaultDatasetPath,
            json_encode($cleanDatasetForSeeder, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        Cache::flush();

        $this->table(
            ['Métrica del Catálogo Real Integrado', 'Total en Base de Datos'],
            [
                ['Categorías Principales', Category::whereNull('parent_id')->count()],
                ['Subcategorías Técnicas', Category::whereNotNull('parent_id')->count()],
                ['Marcas Registradas y Enlazadas', Brand::count()],
                ['Productos Publicados (100% con Marca y Categoría)', Product::count()],
                ['Especificaciones Técnicas Indexadas', $totalSpecsCreated],
                ['Imágenes HD Descargadas (máx. 2 por producto)', $totalImagesLinked],
            ]
        );

        return self::SUCCESS;
    }

    /**
     * Define the 10 parent categories and their 35 subcategories.
     *
     * @return array<string, array{name: string, description: string, subcategories: array<string, string>}>
     */
    protected function getCategoryHierarchy(): array
    {
        return [
            'herrajes-para-muebles' => [
                'name' => 'Herrajes y Bisagras',
                'description' => 'Bisagras cazoleta cierre suave, correderas telescópicas y ocultas, pistones a gas, sistemas elevables y retenes push open.',
                'subcategories' => [
                    'bisagras-cangrejo-para-muebles' => 'Bisagras Cazoleta y Especiales',
                    'bisagras-para-puertas' => 'Bisagras Capuchinas, Vaivén y Piano',
                    'correderas-para-muebles' => 'Correderas Telescópicas y Ocultas',
                    'pistones-y-push-open' => 'Pistones a Gas, Elevables y Push Open',
                ],
            ],
            'tiradores-jaladores-y-perillas' => [
                'name' => 'Tiradores, Jaladores y Perillas',
                'description' => 'Amplia colección de tiradores arquitectónicos, jaladores en acero inoxidable y aluminio, perillas decorativas, tinas embutidas y perfiles Gola.',
                'subcategories' => [
                    'jaladores-para-muebles' => 'Tiradores y Jaladores de Mueble',
                    'perillas-y-botones' => 'Perillas y Botones Decorativos',
                    'tiradores-embutidos-y-tinas' => 'Tinas y Tiradores Embutidos',
                    'perfiles-gola-y-aluminio' => 'Perfiles Tirador y Gola de Aluminio',
                    'manillones-para-puertas' => 'Manillones para Puertas y Mamparas',
                ],
            ],
            'organizacion-cocina-y-closet' => [
                'name' => 'Organización de Cocina y Clóset',
                'description' => 'Sistemas extraíbles para cocina, condimenteros, cestos, portacubiertos, basureros, pantaloneros, zapateras, tubos de clóset y percheros.',
                'subcategories' => [
                    'herrajes-para-cocina' => 'Extraíbles, Cestos y Condimenteros',
                    'portacubiertos-y-basureros' => 'Portacubiertos y Basureros Extraíbles',
                    'herrajes-para-closet' => 'Organizadores de Clóset y Pantaloneros',
                    'tubos-y-soportes-para-closets' => 'Tubos y Soportes para Clóset',
                    'percheros-y-ganchos' => 'Percheros, Ganchos y Baño',
                ],
            ],
            'accesorios-para-muebles' => [
                'name' => 'Tapacantos, Soportes y Patas',
                'description' => 'Tapacantos de PVC y melamina, tapatornillos adhesivos, soportes para repisas y vidrio, patas regulables y garruchas.',
                'subcategories' => [
                    'tapacantos-melamina' => 'Tapacantos de PVC y Melamina',
                    'tapa-tornillos-adhesivos' => 'Tapa Tornillos Adhesivos',
                    'escuadras-y-soportes-de-repisas' => 'Soportes de Repisa y Cristal',
                    'patas-y-ruedas-para-muebles' => 'Patas y Niveladores para Muebles',
                    'garruchas-y-ruedas-para-muebles' => 'Garruchas y Ruedas Giratorias',
                ],
            ],
            'sistemas-corredizos' => [
                'name' => 'Sistemas Corredizos y Rieles',
                'description' => 'Kits de carros rodamiento, sistemas corredizos colgados y apoyados, sistemas tipo granero, plegables y rieles de aluminio y acero.',
                'subcategories' => [
                    'sistemas-corredizos-para-puertas' => 'Sistemas y Carros Corredizos',
                    'rieles-y-sistemas-para-puertas' => 'Rieles y Guías para Puertas',
                ],
            ],
            'chapas-cerraduras-y-otros' => [
                'name' => 'Cerraduras, Topes y Rejillas',
                'description' => 'Cerraduras para muebles y puertas, cerrojos, brazos hidráulicos, topes de puerta en acero inoxidable y rejillas de ventilación.',
                'subcategories' => [
                    'cerraduras-y-cerrojos' => 'Cerraduras, Chapas y Cerrojos',
                    'topes-y-accesorios-de-puerta' => 'Topes de Puerta y Señalética Inox',
                    'rejillas' => 'Rejillas de Ventilación Aluminio',
                ],
            ],
            'tornillos-tarugos-y-otros' => [
                'name' => 'Fijaciones, Tornillería y Ensamble',
                'description' => 'Tornillos autorroscantes para melamina, eurotornillos, conectores Minifix, ángulos de unión, tarugos y alcayatas.',
                'subcategories' => [
                    'angulos-y-uniones-para-muebles' => 'Conectores Minifix, Ángulos y Uniones',
                    'tarugos' => 'Tarugos, Alcayatas y Abrazaderas',
                    'tornillos' => 'Tornillos, Pernos y Tuercas',
                ],
            ],
            'electricidad' => [
                'name' => 'Iluminación LED, Tomas y Pasacables',
                'description' => 'Cintas LED, perfiles difusores de aluminio, fuentes de poder, tomas multimedia empotrables con USB/HDMI y pasacables de escritorio.',
                'subcategories' => [
                    'tomas-multimedia-y-escritorio' => 'Tomas Multimedia, USB y Cargadores',
                    'pasacables' => 'Pasacables de Aluminio y PVC',
                    'transformadores-cintas-led' => 'Cintas LED, Sensores y Fuentes',
                    'perfil-para-led-y-conexiones' => 'Perfiles Difusores y Canaletas LED',
                ],
            ],
            'pegamentos-y-acabados' => [
                'name' => 'Pegamentos, Siliconas y Químicos',
                'description' => 'Pegamentos de contacto, adhesivos instantáneos MDF, siliconas acéticas y poliuretano, pinturas en spray y lubricantes.',
                'subcategories' => [
                    'pegamentos-de-contacto' => 'Pegamentos de Contacto y Kits MDF',
                    'siliconas' => 'Siliconas y Selladores Flexibles',
                    'pintura-en-spray-limpieza-y-mas' => 'Pinturas en Spray y Mantenimiento',
                ],
            ],
            'herramientas-manuales' => [
                'name' => 'Herramientas, Brocas y Plantillas',
                'description' => 'Brocas Forstner y para concreto/madera, fresas, plantillas de perforación, escuadras, herramientas de carpintería y equipos.',
                'subcategories' => [
                    'brocas-y-copas-para-madera' => 'Brocas, Fresas y Puntas',
                    'plantillas-y-medicion' => 'Plantillas de Carpintería y Medición',
                    'cutters-y-galopas' => 'Herramientas de Taller y Equipos',
                ],
            ],
        ];
    }

    /**
     * Resolve the exact Brand name for a product.
     */
    protected function resolveBrandName(array $item, string $cleanTitle): string
    {
        $knownBrands = [
            'truper' => 'Truper',
            'ducasse' => 'Ducasse',
            'halcon' => 'Halcon',
            'travex' => 'Travex',
            'danco' => 'Danco',
            'pelikano' => 'Pelikano',
            'tapacanto(pelikano)' => 'Pelikano',
            'venezzia' => 'Venezzia',
            'ultracraft' => 'Ultracraft',
            'pretul' => 'Pretul',
            'hermex' => 'Hermex',
            'suyay' => 'Suyay',
            'seita' => 'Seita',
            'dmueble' => 'Dmueble',
            'imbot' => 'Imbot',
            'imbut' => 'Imbot',
            'inbot' => 'Imbot',
            'inbot bricolaje' => 'Imbot',
            'eurolocks' => 'Eurolocks',
            'eurolock' => 'Eurolocks',
            'sika' => 'Sika',
            'sikaflex' => 'Sika',
            'sikasil' => 'Sika',
            'bahco' => 'Bahco',
            'dewalt' => 'DeWalt',
            'koslo' => 'Koslo',
            'taviexport' => 'Taviexport',
            'somafix' => 'Somafix',
            'pl285' => 'PL285',
            'africano' => 'Africano',
            'veytonflex' => 'Veytonflex',
            'afix' => 'Afix',
            'pegadit' => 'Afix',
            'fischer' => 'Fischer',
            'fitcher' => 'Fischer',
            'wd-40' => 'WD-40',
            'wd40' => 'WD-40',
            'volteck' => 'Volteck',
            'fiero' => 'Fiero',
            'foset' => 'Foset',
            'klintek' => 'Klintek',
            'soldimix' => 'Soldimix',
            'cantol' => 'Cantol',
            'forte' => 'Forte',
            '3m' => '3M',
            'bosch' => 'Bosch',
            'stanley' => 'Stanley',
            'total' => 'Total',
            'abro' => 'Abro',
            'tekbond' => 'Tekbond',
            'koven' => 'Koven',
            'lecco' => 'Lecco',
            'gy' => 'GY',
            'kova' => 'Kova',
            'yale' => 'Yale',
            'innova' => 'Innova',
            'san luis industrial' => 'San Luis Industrial',
        ];

        $bObj = strtolower(trim((string) ($item['brand_obj'] ?? $item['brand'] ?? '')));
        if ($bObj !== '' && isset($knownBrands[$bObj])) {
            return $knownBrands[$bObj];
        }

        $notice = strtolower(trim((string) ($item['notice'] ?? '')));
        if ($notice !== '' && ! str_contains($notice, 'igual a')) {
            if (isset($knownBrands[$notice])) {
                return $knownBrands[$notice];
            }
            foreach ($knownBrands as $k => $canonical) {
                if (preg_match('/\b'.preg_quote($k, '/').'\b/u', $notice)) {
                    return $canonical;
                }
            }
        }

        $descText = html_entity_decode(strip_tags((string) ($item['description_html'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (preg_match('/Marca\s*:\s*([^\r\n<|]+)/iu', $descText, $m)) {
            $rawBrand = preg_replace('/imagen\s*referencial.*$/iu', '', $m[1]);
            $rawBrand = preg_replace('/color\s*:.*$/iu', '', (string) $rawBrand);
            $rawBrand = preg_replace('/^[\s\.\:\;\-\*\•]+|[\s\.\:\;\-\*\•]+$/u', '', (string) $rawBrand);
            $rawBrand = mb_strtolower(trim((string) $rawBrand), 'UTF-8');
            if ($rawBrand !== '' && $rawBrand !== 'sin marca') {
                if (isset($knownBrands[$rawBrand])) {
                    return $knownBrands[$rawBrand];
                }
                foreach ($knownBrands as $k => $canonical) {
                    if (str_contains($rawBrand, $k)) {
                        return $canonical;
                    }
                }
            }
        }

        $haystack = strtolower($cleanTitle.' '.($item['slug'] ?? ''));
        foreach ($knownBrands as $k => $canonical) {
            if (strlen($k) >= 4 && preg_match('/(?:^|[^a-z0-9])'.preg_quote($k, '/').'(?:$|[^a-z0-9])/i', $haystack)) {
                return $canonical;
            }
        }

        $sku = strtoupper(trim((string) ($item['sku'] ?? '')));
        if (str_starts_with($sku, 'DW')) {
            return 'DeWalt';
        }
        if (preg_match('/^432\d\d$/', $sku)) {
            return 'Hermex';
        }
        if ($sku === '20017') {
            return 'Pretul';
        }
        if (preg_match('/^\d{5}$/', $sku) && ! str_starts_with($sku, '0') && ! str_starts_with($sku, '5')) {
            return 'Truper';
        }
        if (str_starts_with(strtolower($cleanTitle), 'smart pata')) {
            return 'Smart Herrajes';
        }

        return 'San Luis Industrial';
    }

    /**
     * Resolve parent Category and Subcategory for a product.
     *
     * @return array{0: Category, 1: ?Category}
     */
    protected function resolveCategories(
        array $item,
        string $cleanTitle,
        array $parentCategoryModels,
        array $subCategoryModels,
        array $subSlugToParentSlug
    ): array {
        // Explicit category_slug and subcategory_slug take highest priority
        $explicitCatSlug = (string) ($item['category_slug'] ?? '');
        $explicitSubSlug = (string) ($item['subcategory_slug'] ?? '');
        if ($explicitCatSlug !== '' && isset($parentCategoryModels[$explicitCatSlug])) {
            $sub = ($explicitSubSlug !== '' && isset($subCategoryModels[$explicitSubSlug]))
                ? $subCategoryModels[$explicitSubSlug]
                : null;

            return [$parentCategoryModels[$explicitCatSlug], $sub];
        }

        // Direct subcategory matches from item collections
        $colSlugs = array_keys($item['collections'] ?? []);
        foreach ($colSlugs as $cSlug) {
            if (isset($subCategoryModels[$cSlug])) {
                $sub = $subCategoryModels[$cSlug];
                $parentSlug = $subSlugToParentSlug[$cSlug];

                return [$parentCategoryModels[$parentSlug], $sub];
            }
        }

        // Direct parent category matches from item collections
        $collectionToParentMap = [
            'ferreteria-para-muebles' => ['herrajes-para-muebles', 'correderas-para-muebles'],
            'herrajes-y-ferreteria' => ['herrajes-para-muebles', 'bisagras-cangrejo-para-muebles'],
            'herrajes' => ['herrajes-para-muebles', 'bisagras-cangrejo-para-muebles'],
            'sistemas-corredizos' => ['sistemas-corredizos', 'rieles-y-sistemas-para-puertas'],
            'chapas-cerraduras-y-otros' => ['chapas-cerraduras-y-otros', 'cerraduras-y-cerrojos'],
            'tornillos-tarugos-y-otros' => ['tornillos-tarugos-y-otros', 'tarugos'],
            'pegamentos-y-acabados' => ['pegamentos-y-acabados', 'pegamentos-de-contacto'],
            'herramientas-manuales' => ['herramientas-manuales', 'alicates'],
            'herramientas' => ['herramientas-manuales', 'alicates'],
            'herramientas-portatiles-y-complementos' => ['herramientas-portatiles-y-complementos', 'atornilladores-taladros-y-complementos'],
            'medicion-y-brocas' => ['medicion-y-brocas', 'brocas-y-copas-para-madera'],
            'electricidad' => ['electricidad', 'canaletas'],
            'cajas-de-herramientas-y-organizadores' => ['cajas-de-herramientas-y-organizadores', 'cajas-y-maletas-de-herramientas'],
            'maquinas-y-complementos' => ['maquinas-y-complementos', 'compresoras-y-complementos'],
            'construccion' => ['construccion', 'escaleras'],
            'automotriz' => ['automotriz', 'gatas-lagarto-y-botella'],
            'liquidos-lubricantes-y-mas' => ['automotriz', 'grasas'],
            'productos-para-el-hogar' => ['hogar', 'accesorios-para-el-hogar'],
            'hogar' => ['hogar', 'accesorios-para-el-hogar'],
        ];

        $titleLower = mb_strtolower($cleanTitle, 'UTF-8');

        // Keyword-based accurate subcategory refinement when product is only in a broad collection (like Novedades / Ferretería para muebles)
        if (str_contains($titleLower, 'riel') || str_contains($titleLower, 'carro ') || str_contains($titleLower, 'guía ') || str_contains($titleLower, 'guia ') || str_contains($titleLower, 'sistema corredizo')) {
            return [$parentCategoryModels['sistemas-corredizos'], $subCategoryModels['rieles-y-sistemas-para-puertas']];
        }
        if (str_contains($titleLower, 'jalador') || str_contains($titleLower, 'tirador') || str_contains($titleLower, 'perilla')) {
            return [$parentCategoryModels['sistemas-corredizos'], $subCategoryModels['jaladores']];
        }
        if (str_contains($titleLower, 'pistón') || str_contains($titleLower, 'piston') || str_contains($titleLower, 'brazo')) {
            return [$parentCategoryModels['sistemas-corredizos'], $subCategoryModels['pistones-y-mas']];
        }
        if (str_contains($titleLower, 'corredera')) {
            return [$parentCategoryModels['herrajes-para-muebles'], $subCategoryModels['correderas-para-muebles']];
        }
        if (str_contains($titleLower, 'bisagra cangrejo') || str_contains($titleLower, 'bisagra interior') || str_contains($titleLower, 'bisagra recta') || str_contains($titleLower, 'bisagra super')) {
            return [$parentCategoryModels['herrajes-para-muebles'], $subCategoryModels['bisagras-cangrejo-para-muebles']];
        }
        if (str_contains($titleLower, 'bisagra')) {
            return [$parentCategoryModels['herrajes-para-muebles'], $subCategoryModels['bisagras-para-puertas']];
        }
        if (str_contains($titleLower, 'pata ') || str_contains($titleLower, 'rueda') || str_contains($titleLower, 'garrucha') || str_contains($titleLower, 'resbalón') || str_contains($titleLower, 'resbalon')) {
            return [$parentCategoryModels['herrajes-para-muebles'], $subCategoryModels['patas-y-ruedas-para-muebles']];
        }
        if (str_contains($titleLower, 'pasacable')) {
            return [$parentCategoryModels['herrajes-para-muebles'], $subCategoryModels['pasacables']];
        }
        if (str_contains($titleLower, 'soporte') || str_contains($titleLower, 'colgador')) {
            return [$parentCategoryModels['herrajes-para-muebles'], $subCategoryModels['escuadras-y-soportes-de-repisas']];
        }
        if (str_contains($titleLower, 'rejilla')) {
            return [$parentCategoryModels['chapas-cerraduras-y-otros'], $subCategoryModels['rejillas']];
        }
        if (str_contains($titleLower, 'cerradura') || str_contains($titleLower, 'chapa') || str_contains($titleLower, 'cerrojo')) {
            return [$parentCategoryModels['chapas-cerraduras-y-otros'], $subCategoryModels['cerraduras-y-cerrojos']];
        }
        if (str_contains($titleLower, 'candado')) {
            return [$parentCategoryModels['chapas-cerraduras-y-otros'], $subCategoryModels['candados']];
        }
        if (str_contains($titleLower, 'tarugo') || str_contains($titleLower, 'alcayata')) {
            return [$parentCategoryModels['tornillos-tarugos-y-otros'], $subCategoryModels['tarugos']];
        }
        if (str_contains($titleLower, 'tornillo')) {
            return [$parentCategoryModels['tornillos-tarugos-y-otros'], $subCategoryModels['tornillos']];
        }
        if (str_contains($titleLower, 'conduit') || str_contains($titleLower, 'emt') || str_contains($titleLower, 'canaleta') || str_contains($titleLower, 'caja de pase') || str_contains($titleLower, 'caja rectangular')) {
            return [$parentCategoryModels['electricidad'], $subCategoryModels['canaletas']];
        }
        if (str_contains($titleLower, 'led') || str_contains($titleLower, 'transformador') || str_contains($titleLower, 'fuente de poder')) {
            return [$parentCategoryModels['electricidad'], $subCategoryModels['transformadores-cintas-led']];
        }
        if (str_contains($titleLower, 'interruptor') || str_contains($titleLower, 'sensor')) {
            return [$parentCategoryModels['electricidad'], $subCategoryModels['tomacorrientes-interruptores-y-sockets']];
        }
        if (str_contains($titleLower, 'broca') || str_contains($titleLower, 'afilador de broca') || str_contains($titleLower, 'plantilla')) {
            return [$parentCategoryModels['medicion-y-brocas'], $subCategoryModels['brocas-y-copas-para-madera']];
        }
        if (str_contains($titleLower, 'escuadra')) {
            return [$parentCategoryModels['medicion-y-brocas'], $subCategoryModels['escuadras']];
        }
        if (str_contains($titleLower, 'llave') || str_contains($titleLower, 'dado') || str_contains($titleLower, 'ratchet') || str_contains($titleLower, 'adaptador')) {
            return [$parentCategoryModels['herramientas-manuales'], $subCategoryModels['dados-llaves-y-torquimetro']];
        }
        if (str_contains($titleLower, 'martillo')) {
            return [$parentCategoryModels['herramientas-manuales'], $subCategoryModels['martillos-y-combas']];
        }
        if (str_contains($titleLower, 'cepillo') || str_contains($titleLower, 'cortador') || str_contains($titleLower, 'refilador') || str_contains($titleLower, 'perforador')) {
            return [$parentCategoryModels['herramientas-manuales'], $subCategoryModels['cutters-y-galopas']];
        }
        if (str_contains($titleLower, 'fresa') || str_contains($titleLower, 'prensa') || str_contains($titleLower, 'abrazadera')) {
            return [$parentCategoryModels['herramientas-portatiles-y-complementos'], $subCategoryModels['fresas-y-prensas']];
        }

        foreach ($colSlugs as $cSlug) {
            if (isset($collectionToParentMap[$cSlug])) {
                [$pSlug, $sSlug] = $collectionToParentMap[$cSlug];

                return [$parentCategoryModels[$pSlug], $subCategoryModels[$sSlug] ?? null];
            }
        }

        return [$parentCategoryModels['herrajes-para-muebles'], $subCategoryModels['escuadras-y-soportes-de-repisas']];
    }

    /**
     * Sanitize text removing any trace of external source domains, names, or reference disclaimers.
     */
    protected function sanitizeText(string $text): string
    {
        $clean = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $clean = mb_scrub($clean, 'UTF-8');
        $clean = preg_replace('/cloti\s*xpress|clotixpress\.com\.pe|ferreter[ií]a\s*clotixpress|savec\.pe|sistema\.savec\.pe|\bsavec\b|carpintec\.com\.pe|\bcarpintec\b/iu', 'Import Hns San Luis', $clean);
        $clean = preg_replace('/imagen\s*referencial/iu', '', (string) $clean);
        $clean = str_replace("\xC2\xA0", ' ', (string) $clean);

        return trim((string) preg_replace('/\s+/u', ' ', (string) $clean));
    }

    /**
     * Normalize product title casing and remove external brand prefixes when redundant.
     */
    protected function formatProductTitle(string $rawTitle): string
    {
        $title = $this->sanitizeText($rawTitle);
        $title = preg_replace('/^TRUPER\s+/iu', '', $title);

        return trim((string) $title);
    }

    /**
     * Parse product HTML description into clean short_description, detailed_description, dimensions, material, sale_unit, and specifications array.
     *
     * @return array{short_description: string, detailed_description: string, dimensions: ?string, material: ?string, sale_unit: string, specifications: array<int, array{group: string, key: string, value: string, unit: ?string}>}
     */
    protected function parseDescriptionAndSpecs(string $rawHtml, string $title, string $brandName, mixed $weightKg): array
    {
        $textWithBreaks = str_ireplace(['</p>', '<br>', '<br/>', '<br />', '</li>', '</tr>', '</div>'], "\n", $rawHtml);
        $plainLines = explode("\n", strip_tags($textWithBreaks));

        $paragraphs = [];
        $specs = [];
        $seenSpecKeys = [];

        $addSpec = function (string $key, string $val, string $group = 'Especificaciones Técnicas', ?string $unit = null) use (&$specs, &$seenSpecKeys) {
            $k = (string) preg_replace('/^[\s\.\:\;\-\*\•]+|[\s\.\:\;\-\*\•]+$/u', '', $this->sanitizeText($key));
            $v = (string) preg_replace('/^[\s\.\:\;\-\*\•]+|[\s\.\:\;\-\*\•]+$/u', '', $this->sanitizeText($val));
            if ($k === '' || $v === '' || mb_strlen($k, 'UTF-8') > 55 || mb_strlen($v, 'UTF-8') > 160) {
                return;
            }
            $normKey = mb_strtolower($k, 'UTF-8');
            if (isset($seenSpecKeys[$normKey])) {
                return;
            }
            $seenSpecKeys[$normKey] = true;
            $specs[] = [
                'group' => $group,
                'key' => mb_strtoupper(mb_substr($k, 0, 1, 'UTF-8'), 'UTF-8').mb_substr($k, 1, null, 'UTF-8'),
                'value' => $v,
                'unit' => $unit,
            ];
        };

        $addSpec('Marca', $brandName, 'Identificación');

        foreach ($plainLines as $line) {
            $cleanLine = $this->sanitizeText($line);
            $cleanLine = (string) preg_replace('/^[\s\•\-\*]+/u', '', $cleanLine);
            if ($cleanLine === '') {
                continue;
            }

            $upper = mb_strtoupper($cleanLine, 'UTF-8');
            if (in_array($upper, ['DESCRIPCION', 'DESCRIPCIÓN', 'DESCRIPCIÒN', 'ESPECIFICACIONES', 'CARACTERISTICAS', 'CARACTERÍSTICAS', 'CARACTERÌSTICAS', 'FICHA TÉCNICA', 'FICHA TECNICA', 'INFORMACIÓN', 'INFORMACION'], true)) {
                continue;
            }

            if (preg_match('/^([^:]{2,40}):\s*(.+)$/u', $cleanLine, $m)) {
                $k = trim($m[1]);
                $v = trim($m[2]);
                if (! in_array(mb_strtoupper($k, 'UTF-8'), ['DESCRIPCION', 'DESCRIPCIÓN', 'CARACTERÍSTICAS', 'ESPECIFICACIONES', 'INSTRUCCIONES DE USO', 'AREA DE APLICACIÓN', 'INFORMACIÓN'], true)) {
                    $addSpec($k, $v);

                    continue;
                }
            }

            $paragraphs[] = $cleanLine;
        }

        // Extract dimensions from specs or title
        $dimensions = null;
        foreach ($specs as $s) {
            if (preg_match('/medida|tama[ñn]o|dimensi|largo|di[áa]metro|ancho/iu', $s['key'])) {
                $dimensions = Str::limit($s['value'], 90, '');
                break;
            }
        }
        if ($dimensions === null && preg_match('/(\d+(?:[\.,\/\*xX]\d+)*\s*(?:mm|cm|m|mts|mt|pulg|\"|\'\'|kg|w|v|lm|ml|gl|oz|t)\b)/iu', $title, $mDim)) {
            $dimensions = trim($mDim[1]);
            $addSpec('Medida / Capacidad', $dimensions);
        }

        // Extract material from specs or title/description
        $material = null;
        foreach ($specs as $s) {
            if (preg_match('/material|acabado|fabricado/iu', $s['key'])) {
                $material = Str::limit($s['value'], 90, '');
                break;
            }
        }
        if ($material === null) {
            $combinedText = $title.' '.implode(' ', $paragraphs);
            if (preg_match('/(Acero Inoxidable|Acero al Cromo Vanadio|Acero al Carbono|Acero LAF|Acero|Aluminio Anodizado|Aluminio|Bronce|Lat[óo]n|Cobre|Madera|Nylon|PVC|Poliuretano|Melamina|Zamak|ABS|Caucho|Hierro)/iu', $combinedText, $mMat)) {
                $material = ucwords(mb_strtolower($mMat[1], 'UTF-8'));
                $addSpec('Material Principal', $material);
            }
        }

        if (is_numeric($weightKg) && (float) $weightKg > 0) {
            $addSpec('Peso Unitario Referencial', number_format((float) $weightKg, 2).' kg', 'Logística y Despacho');
        }

        // Determine sale_unit from title
        $saleUnit = 'Unidad';
        if (preg_match('/\bx\s*par\b|\bpar\b/iu', $title)) {
            $saleUnit = 'Par';
        } elseif (preg_match('/\bx\s*cto\b|\bciento\b/iu', $title)) {
            $saleUnit = 'Ciento';
        } elseif (preg_match('/\bkit\b|\bjuego\b|\bset\b/iu', $title)) {
            $saleUnit = 'Juego / Kit';
        } elseif (preg_match('/\bx\s*(?:mt|metro|mts)\b/iu', $title)) {
            $saleUnit = 'Metro';
        } elseif (preg_match('/\bcaja\b/iu', $title) && ! str_contains(mb_strtolower($title, 'UTF-8'), 'caja p/herramienta') && ! str_contains(mb_strtolower($title, 'UTF-8'), 'caja de pase') && ! str_contains(mb_strtolower($title, 'UTF-8'), 'caja rectangular')) {
            $saleUnit = 'Caja';
        }

        $addSpec('Unidad de Venta', $saleUnit, 'Comercialización');

        if (empty($paragraphs)) {
            $paragraphs[] = "{$title} de la marca {$brandName}, diseñado para aplicaciones profesionales en carpintería, construcción, ferretería técnica e industria.";
        }

        $shortDescription = Str::limit(implode(' ', array_slice($paragraphs, 0, 2)), 260);

        $htmlParts = [];
        foreach ($paragraphs as $pText) {
            $htmlParts[] = '<p>'.e($pText).'</p>';
        }
        $detailedDescription = implode("\n", $htmlParts);

        return [
            'short_description' => $shortDescription,
            'detailed_description' => $detailedDescription,
            'dimensions' => $dimensions,
            'material' => $material,
            'sale_unit' => $saleUnit,
            'specifications' => $specs,
        ];
    }
}
