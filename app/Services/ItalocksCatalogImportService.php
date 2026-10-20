<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSpecification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Integra en el catálogo los productos extraídos de italocks.com de forma aditiva e idempotente
 * (nunca elimina productos existentes; re-ejecutar actualiza por SKU).
 */
class ItalocksCatalogImportService
{
    protected const MAX_IMAGES = 2;

    /**
     * Categorías destino: nombre de origen => [slug, nombre, descripción SEO, aplicaciones].
     * "Cerraduras" se fusiona con la categoría existente del catálogo.
     *
     * @var array<string, array{slug: string, name: string, description: string, applications: string}>
     */
    protected const CATEGORY_MAP = [
        'Luminarias' => [
            'slug' => 'luminarias',
            'name' => 'Luminarias',
            'description' => 'Luminarias LED decorativas, plafones, apliques de pared y lámparas colgantes para iluminación residencial, comercial y de proyectos inmobiliarios en Perú.',
            'applications' => 'salas, comedores, dormitorios, oficinas, locales comerciales, halls y proyectos de interiorismo que requieren iluminación eficiente y decorativa',
        ],
        'Cerraduras' => [
            'slug' => 'chapas-cerraduras-y-otros',
            'name' => 'Cerraduras',
            'description' => 'Cerraduras, manijas, cilindros y chapas de seguridad para puertas residenciales y comerciales.',
            'applications' => 'puertas principales, interiores, oficinas, departamentos y proyectos de construcción que exigen seguridad y durabilidad',
        ],
        'Manillones' => [
            'slug' => 'manillones',
            'name' => 'Manillones',
            'description' => 'Manillones de acero inoxidable y aluminio para puertas de vidrio, madera y aluminio en acabados cromo, satinado y negro.',
            'applications' => 'puertas de vidrio templado, mamparas, ingresos de locales comerciales, oficinas corporativas y edificios',
        ],
        'Artículos para Puertas' => [
            'slug' => 'articulos-para-puertas',
            'name' => 'Artículos para Puertas',
            'description' => 'Herrajes y accesorios para puertas: manillones, topes, bisagras y complementos de instalación profesional.',
            'applications' => 'puertas de madera, vidrio y aluminio en viviendas, oficinas, locales comerciales y obras',
        ],
        'Accesorios de Baño' => [
            'slug' => 'accesorios-de-bano',
            'name' => 'Accesorios de Baño',
            'description' => 'Accesorios de baño en acero inoxidable: portarrollos, toalleros, ganchos y complementos de diseño moderno.',
            'applications' => 'baños residenciales, hoteles, oficinas, centros comerciales y proyectos inmobiliarios',
        ],
        'Sistemas LED' => [
            'slug' => 'sistemas-led',
            'name' => 'Sistemas LED',
            'description' => 'Cintas LED, neón flexible y sistemas de iluminación lineal para mobiliario, arquitectura y decoración.',
            'applications' => 'muebles de cocina, closets, vitrinas, cielos rasos, fachadas y decoración arquitectónica',
        ],
        'Espejos LED' => [
            'slug' => 'espejos-led',
            'name' => 'Espejos LED',
            'description' => 'Espejos con iluminación LED integrada para baños y vestidores con diseño contemporáneo.',
            'applications' => 'baños, vestidores, hoteles, salones de belleza y proyectos de diseño interior',
        ],
        'Accesorios Mueblista' => [
            'slug' => 'accesorios-mueblista',
            'name' => 'Accesorios Mueblista',
            'description' => 'Tiradores, jaladores y accesorios de diseño para la fabricación de muebles de cocina, closets y oficinas.',
            'applications' => 'muebles de cocina, reposteros, closets, cómodas, escritorios y mobiliario a medida',
        ],
        'Línea Mueblista' => [
            'slug' => 'linea-mueblista',
            'name' => 'Línea Mueblista',
            'description' => 'Herrajes y componentes para la industria del mueble y carpintería en melamina.',
            'applications' => 'talleres de carpintería, fábricas de muebles y proyectos de mobiliario a medida',
        ],
        'Tiradores' => [
            'slug' => 'tiradores',
            'name' => 'Tiradores',
            'description' => 'Tiradores y jaladores para muebles en aluminio, acero y zamak en múltiples medidas y acabados.',
            'applications' => 'cajones, puertas de muebles de cocina, closets y mobiliario de oficina',
        ],
        'Revestimientos' => [
            'slug' => 'revestimientos',
            'name' => 'Revestimientos',
            'description' => 'Revestimientos decorativos para muros y superficies interiores.',
            'applications' => 'muros de salas, recepciones, locales comerciales y proyectos de interiorismo',
        ],
    ];

    /**
     * Reglas de subcategoría por tipo de producto (regex sobre el nombre => nombre de subcategoría).
     *
     * @var array<string, string>
     */
    protected const SUBCATEGORY_RULES = [
        '/aplique/u' => 'Apliques de Pared',
        '/plaf[oó]n/u' => 'Plafones LED',
        '/ventilador/u' => 'Luminarias con Ventilador',
        '/riel para colgante/u' => 'Rieles y Accesorios de Iluminación',
        '/colgante/u' => 'Luminarias Colgantes',
        '/l[aá]mpara de (mesa|pie)|velador/u' => 'Lámparas de Mesa',
        '/decorativa/u' => 'Luminarias Decorativas',
        '/panel/u' => 'Paneles LED',
        '/reflector/u' => 'Reflectores LED',
        '/spot|dicroic|empotr|downlight/u' => 'Spots y Empotrables',
        '/ne[oó]n|cinta|tira led/u' => 'Cintas y Neón LED',
        '/perfil/u' => 'Perfiles LED',
        '/espejo/u' => 'Espejos con Luz LED',
        '/cilindro/u' => 'Cilindros',
        '/cerradura.*manija|manija/u' => 'Cerraduras y Manijas',
        '/cerradura|chapa|cerrojo/u' => 'Cerraduras de Seguridad',
        '/manill[oó]n/u' => 'Manillones para Puertas',
        '/tirador|jalador/u' => 'Tiradores para Muebles',
        '/portarrollo|porta rollo/u' => 'Portarrollos',
        '/toallero|gancho|percha|jabonera|dispensador/u' => 'Toalleros y Complementos',
        '/bisagra/u' => 'Bisagras',
        '/tope/u' => 'Topes para Puertas',
    ];

    /**
     * @var array<string, Category>
     */
    protected array $categoryCache = [];

    /**
     * @var array<string, Brand>
     */
    protected array $brandCache = [];

    /**
     * @var array{created: int, updated: int, categories_created: int, subcategories_created: int, brands_created: int, images_downloaded: int, images_reused: int}
     */
    protected array $summary = [
        'created' => 0,
        'updated' => 0,
        'categories_created' => 0,
        'subcategories_created' => 0,
        'brands_created' => 0,
        'images_downloaded' => 0,
        'images_reused' => 0,
    ];

    public function __construct(
        protected ItalocksScraperService $scraper,
        protected ImageThumbnailService $thumbnails,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $products
     * @return array{created: int, updated: int, categories_created: int, subcategories_created: int, brands_created: int, images_downloaded: int, images_reused: int}
     */
    public function import(array $products, bool $downloadImages = true, ?callable $onProductImported = null): array
    {
        $importedProducts = [];

        foreach ($products as $item) {
            $rawName = trim((string) ($item['name'] ?? ''));
            if ($rawName !== '' && ! preg_match('/plaf[oó]n|luminaria|l[aá]mpara|aplique|martillo|candado|espejo|base para monitor|conduit|cerradura|alicate|afilador|llave inglesa|adaptador angular|serrucho|nivel/iu', $rawName)) {
                $product = $this->importProduct($item, $downloadImages);
                $importedProducts[Str::lower($product->name)] = $product;
            }
            $onProductImported && $onProductImported();
        }

        if ($downloadImages) {
            $this->reuseSiblingImages($importedProducts);
        }

        Cache::flush();

        return $this->summary;
    }

    /**
     * Algunas variantes (regulable, riel, large…) no tienen fotos publicadas en el origen:
     * reutilizan las imágenes del modelo hermano con el mismo cuerpo.
     *
     * @param  array<string, Product>  $importedProducts
     */
    protected function reuseSiblingImages(array $importedProducts): void
    {
        foreach ($importedProducts as $lowerName => $product) {
            if ($product->images()->exists()) {
                continue;
            }

            $candidates = array_unique([
                trim(str_replace(' regulable', '', $lowerName)),
                str_replace(' riel ', ' fijo ', $lowerName),
                str_replace(' large', ' small', $lowerName),
                trim(str_replace([' regulable', ' riel '], ['', ' fijo '], $lowerName)),
            ]);

            foreach ($candidates as $candidate) {
                $sibling = $importedProducts[$candidate] ?? null;
                if ($candidate === $lowerName || ! $sibling || ! $sibling->images()->exists()) {
                    continue;
                }

                foreach ($sibling->images()->orderBy('sort_order')->get() as $image) {
                    ProductImage::query()->create([
                        'product_id' => $product->id,
                        'file_path' => $image->file_path,
                        'thumbnail_path' => $image->thumbnail_path,
                        'alt_text' => Str::limit($this->readableName($product->name).' - imagen '.$image->sort_order.' | Import Hns San Luis', 250, ''),
                        'is_primary' => $image->is_primary,
                        'sort_order' => $image->sort_order,
                    ]);
                    $this->summary['images_reused']++;
                }
                break;
            }
        }
    }

    protected function metaTitle(string $readableName, string $brand, bool $nameIncludesBrand): string
    {
        $suffix = ' | Import Hns San Luis';
        $withBrand = $readableName.($nameIncludesBrand ? '' : ' '.$brand);

        foreach ([$withBrand, $readableName] as $title) {
            if (mb_strlen($title.$suffix) <= 70) {
                return $title.$suffix;
            }
        }

        return Str::beforeLast(Str::limit($readableName, 70 - mb_strlen($suffix), ''), ' ').$suffix;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function importProduct(array $item, bool $downloadImages): Product
    {
        $name = trim((string) $item['name']);
        $categoryKey = $this->resolveSourceCategory($item);
        $categoryMeta = self::CATEGORY_MAP[$categoryKey];
        $parent = $this->parentCategory($categoryMeta);
        $subcategory = $this->subcategory($parent, $name);
        $brand = $this->brand((string) ($item['brand'] ?? '') ?: 'Italocks');

        $sku = $this->resolveSku((string) $item['sku'], $brand);
        $existing = Product::query()->where('sku', $sku)->first();

        $materials = array_values(array_filter((array) ($item['materials'] ?? [])));
        $colors = array_values(array_filter((array) ($item['colors'] ?? []))) ?: $this->colorsFromName($name);
        $sizes = array_values(array_filter((array) ($item['sizes'] ?? [])));
        $tech = $this->technicalData($item);
        $readableName = $this->readableName($name);
        $nameIncludesBrand = Str::contains(Str::lower($name), Str::lower($brand->name));

        $product = DB::transaction(function () use ($existing, $item, $name, $sku, $brand, $parent, $subcategory, $categoryMeta, $materials, $colors, $sizes, $tech, $readableName, $nameIncludesBrand): Product {
            $attributes = [
                'name' => $name,
                'short_description' => $this->shortDescription($readableName, $brand->name, $materials, $tech, $categoryMeta),
                'detailed_description' => $this->detailedDescription($item, $readableName, $sku, $brand->name, $materials, $colors, $sizes, $tech, $categoryMeta),
                'brand_id' => $brand->id,
                'category_id' => $parent->id,
                'subcategory_id' => $subcategory->id,
                'dimensions' => $tech['Dimensiones'] ?? ($sizes !== [] ? implode(' x ', $sizes) : $this->dimensionsFromName($name)),
                'material' => $materials !== [] ? Str::limit(implode(', ', $materials), 120, '') : null,
                'meta_title' => $this->metaTitle($readableName, $brand->name, $nameIncludesBrand),
                'meta_description' => $this->metaDescription($readableName, $brand->name, $materials, $tech, $categoryMeta),
            ];

            if ($existing) {
                $existing->update($attributes);
                $this->summary['updated']++;
                $product = $existing;
            } else {
                $product = Product::query()->create($attributes + [
                    'sku' => $sku,
                    'slug' => $this->uniqueSlug($name, $sku),
                    'sale_unit' => 'Unidad',
                    'currency' => 'PEN',
                    'price' => null,
                    'price_visibility' => 'hide',
                    'availability_status' => 'on_request',
                    'status' => 'published',
                    'is_featured' => false,
                    'views_count' => 0,
                ]);
                $this->summary['created']++;
            }

            $this->syncSpecifications($product, $sku, $brand->name, $parent->name, $materials, $colors, $sizes, $tech);

            return $product;
        });

        if ($downloadImages && ! $product->images()->exists()) {
            $this->downloadImages($product, (array) ($item['image_urls'] ?? []), $readableName, $brand->name);
        }

        $primaryPath = $product->images()->where('is_primary', true)->value('file_path');
        if ($primaryPath) {
            foreach ([$parent, $subcategory] as $category) {
                if (! $category->image_path) {
                    $category->update(['image_path' => $primaryPath]);
                }
            }
        }

        return $product;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function resolveSourceCategory(array $item): string
    {
        $source = (string) ($item['source_category'] ?? '');
        if (isset(self::CATEGORY_MAP[$source])) {
            return $source;
        }

        $name = Str::lower((string) $item['name']);

        return match (true) {
            (bool) preg_match('/espejo/u', $name) => 'Espejos LED',
            (bool) preg_match('/cinta|ne[oó]n/u', $name) => 'Sistemas LED',
            (bool) preg_match('/cerradura|cilindro|manija/u', $name) => 'Cerraduras',
            (bool) preg_match('/manill[oó]n/u', $name) => 'Manillones',
            (bool) preg_match('/tirador|jalador/u', $name) => 'Accesorios Mueblista',
            (bool) preg_match('/portarrollo|toallero/u', $name) => 'Accesorios de Baño',
            default => 'Luminarias',
        };
    }

    /**
     * @param  array{slug: string, name: string, description: string, applications: string}  $meta
     */
    protected function parentCategory(array $meta): Category
    {
        return $this->categoryCache['parent:'.$meta['slug']] ??= $this->firstOrCreateCategory(
            ['slug' => $meta['slug']],
            [
                'parent_id' => null,
                'name' => $meta['name'],
                'description' => $meta['description'],
                'is_active' => true,
                'sort_order' => (int) Category::query()->whereNull('parent_id')->max('sort_order') + 1,
                'meta_title' => "{$meta['name']} | Import Hns San Luis",
                'meta_description' => Str::limit("{$meta['description']} Cotiza al por mayor y menor en Import Hns San Luis, Lima - Perú.", 160, ''),
            ],
            'categories_created'
        );
    }

    protected function subcategory(Category $parent, string $productName): Category
    {
        $lowerName = Str::lower($productName);
        $subName = 'Otros '.$parent->name;
        foreach (self::SUBCATEGORY_RULES as $pattern => $ruleName) {
            if (preg_match($pattern, $lowerName)) {
                $subName = $ruleName;
                break;
            }
        }

        $cacheKey = "sub:{$parent->id}:{$subName}";
        if (isset($this->categoryCache[$cacheKey])) {
            return $this->categoryCache[$cacheKey];
        }

        $existing = Category::query()->where('parent_id', $parent->id)->where('name', $subName)->first();
        if ($existing) {
            return $this->categoryCache[$cacheKey] = $existing;
        }

        $slug = Str::slug($subName);
        if (Category::query()->where('slug', $slug)->exists()) {
            $slug = Str::slug($parent->slug.' '.$subName);
        }

        return $this->categoryCache[$cacheKey] = $this->firstOrCreateCategory(
            ['slug' => $slug],
            [
                'parent_id' => $parent->id,
                'name' => $subName,
                'description' => "Línea de {$subName} dentro de {$parent->name}, disponible en Import Hns San Luis con atención por mayor y menor.",
                'is_active' => true,
                'sort_order' => (int) Category::query()->where('parent_id', $parent->id)->max('sort_order') + 1,
                'meta_title' => Str::limit("{$subName} - {$parent->name} | Import Hns San Luis", 70, ''),
                'meta_description' => Str::limit("Encuentra {$subName} en Import Hns San Luis: catálogo de {$parent->name} con fichas técnicas y cotización rápida en Lima y todo el Perú.", 160, ''),
            ],
            'subcategories_created'
        );
    }

    /**
     * @param  array<string, string>  $match
     * @param  array<string, mixed>  $values
     */
    protected function firstOrCreateCategory(array $match, array $values, string $counter): Category
    {
        $category = Category::query()->firstOrCreate($match, $values);
        if ($category->wasRecentlyCreated) {
            $this->summary[$counter]++;
        }

        return $category;
    }

    protected function brand(string $name): Brand
    {
        $slug = Str::slug($name);

        if (! isset($this->brandCache[$slug])) {
            $brand = Brand::query()->firstOrCreate(['slug' => $slug], [
                'name' => Str::title(Str::lower($name)),
                'description' => "Productos de la marca {$name} comercializados por Import Hns San Luis en Lima y todo el Perú.",
                'is_verified' => true,
                'is_active' => true,
                'sort_order' => (int) Brand::query()->max('sort_order') + 1,
            ]);

            if ($brand->wasRecentlyCreated) {
                $this->summary['brands_created']++;
            }

            $this->brandCache[$slug] = $brand;
        }

        return $this->brandCache[$slug];
    }

    /**
     * Mantiene el código original; si ya lo usa un producto de otra marca, se diferencia con sufijo.
     */
    protected function resolveSku(string $rawSku, Brand $brand): string
    {
        $sku = Str::limit((string) preg_replace('/[^A-Z0-9\-\.\/]/', '', Str::upper(trim($rawSku))), 60, '');
        $owner = Product::query()->where('sku', $sku)->first();

        if ($owner && $owner->brand_id !== $brand->id) {
            return $sku.'-IT';
        }

        return $sku;
    }

    protected function uniqueSlug(string $name, string $sku): string
    {
        $base = Str::slug($name) ?: 'producto-'.Str::slug($sku);
        $slug = $base;

        if (Product::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.Str::slug($sku);
        }

        $counter = 2;
        while (Product::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    /**
     * Convierte nombres en MAYÚSCULAS a formato legible manteniendo códigos y medidas.
     */
    protected function readableName(string $name): string
    {
        if (mb_strtoupper($name) !== $name) {
            return Str::ucfirst($name);
        }

        return collect(preg_split('/\s+/u', Str::lower($name)))
            ->map(fn (string $word) => preg_match('/\d/u', $word) ? Str::upper($word) : (in_array($word, ['de', 'del', 'con', 'para', 'y', 'en', 'x', 'c/'], true) ? $word : Str::ucfirst($word)))
            ->implode(' ');
    }

    protected function dimensionsFromName(string $name): ?string
    {
        if (preg_match('/\d+(?:[.,]\d+)?\s?(?:x\s?\d+(?:[.,]\d+)?\s?)*(?:mm|cm|mts?)\b/iu', $name, $match)) {
            return Str::upper(str_replace(' ', '', $match[0]));
        }

        if (preg_match('/\b\d+\s?w\b/iu', $name, $match)) {
            return Str::upper(str_replace(' ', '', $match[0]));
        }

        return null;
    }

    /**
     * Extrae datos técnicos (potencia, temperatura de color, voltaje, cable, dimensiones…) del texto de origen.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, string>
     */
    protected function technicalData(array $item): array
    {
        $text = Str::upper((string) ($item['short_description'] ?? '').' '.(string) $item['name']);
        $data = [];

        if (preg_match('/(\d+(?:[.,]\d+)?)\s?W\b/u', $text, $match)) {
            $data['Potencia'] = str_replace(',', '.', $match[1]).'W';
        }
        if (preg_match('/\b(\d{4})\s?K\b/u', $text, $match)) {
            $kelvin = (int) $match[1];
            $tone = $kelvin < 3500 ? 'luz cálida' : ($kelvin <= 4500 ? 'luz neutra' : 'luz fría');
            $data['Temperatura de color'] = "{$kelvin}K ({$tone})";
        }
        if (preg_match('/\b(\d{2,3})\s?V\b/u', $text, $match)) {
            $data['Voltaje'] = $match[1].'V';
        }
        if (preg_match('/CABLE\s*(?:DE\s*)?(\d+(?:[.,]\d+)?)\s?(CM|M)\b/u', $text, $match)) {
            $data['Largo de cable'] = str_replace(',', '.', $match[1]).' '.Str::lower($match[2]);
        }
        if (preg_match('/\d+(?:[.,]\d+)?\s?X\s?\d+(?:[.,]\d+)?(?:\s?X\s?\d+(?:[.,]\d+)?)?\s?(?:CM|MM)\b/u', $text, $match)) {
            $data['Dimensiones'] = Str::lower(str_replace([' ', ','], ['', '.'], $match[0]));
        }
        if (preg_match('/\b(E27|E14|GU10|GU5\.3|MR16)\b/u', $text, $match)) {
            $data['Casquillo'] = $match[1];
        }
        if (preg_match('/\b(\d+(?:[.,]\d+)?)\s?(MTS?|METROS)\b/u', $text, $match)) {
            $data['Longitud'] = str_replace(',', '.', $match[1]).' m';
        }
        if (preg_match('/REGULABLE|DIMEABLE|DIMMABLE/u', $text)) {
            $data['Regulable (dimmer)'] = 'Sí';
        }

        return $data;
    }

    /**
     * @return list<string>
     */
    protected function colorsFromName(string $name): array
    {
        preg_match_all('/\b(negro|blanco|dorado|gris|azul|rojo|verde|rosado|rosa|lila|café|marfil|celeste|beige|plateado|rose gold|amarillo|naranja|marr[oó]n|cromo|satinado)\b/iu', $name, $matches);

        return array_values(array_unique(array_map(fn (string $color) => Str::ucfirst(Str::lower($color)), $matches[1])));
    }

    /**
     * @param  list<string>  $materials
     * @param  array<string, string>  $tech
     * @param  array{slug: string, name: string, description: string, applications: string}  $categoryMeta
     */
    protected function shortDescription(string $readableName, string $brand, array $materials, array $tech, array $categoryMeta): string
    {
        $highlights = array_values(array_filter([
            isset($tech['Potencia']) ? 'potencia de '.$tech['Potencia'] : null,
            $tech['Temperatura de color'] ?? null,
            isset($tech['Regulable (dimmer)']) ? 'intensidad regulable' : null,
            $materials !== [] ? 'material de '.Str::lower(implode(' y ', $materials)) : null,
        ]));

        $lastHighlight = array_pop($highlights);
        $highlightText = $lastHighlight === null ? '' : ', con '.($highlights !== [] ? implode(', ', $highlights).' y ' : '').$lastHighlight;

        $sentence = "{$readableName} de la marca {$brand}{$highlightText}.";
        $withApplications = "{$sentence} Ideal para {$categoryMeta['applications']}.";

        return mb_strlen($withApplications) <= 250 ? $withApplications : $sentence;
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  list<string>  $materials
     * @param  list<string>  $colors
     * @param  list<string>  $sizes
     * @param  array<string, string>  $tech
     * @param  array{slug: string, name: string, description: string, applications: string}  $categoryMeta
     */
    protected function detailedDescription(array $item, string $readableName, string $sku, string $brand, array $materials, array $colors, array $sizes, array $tech, array $categoryMeta): string
    {
        $e = fn (string $value): string => e($value);
        $categoryLower = Str::lower($categoryMeta['name']);

        $features = [
            "<li><strong>Marca:</strong> {$e($brand)}</li>",
            "<li><strong>Código:</strong> {$e($sku)}</li>",
        ];
        foreach ($tech as $label => $value) {
            $features[] = "<li><strong>{$e($label)}:</strong> {$e($value)}</li>";
        }
        if ($materials !== []) {
            $features[] = '<li><strong>Material:</strong> '.$e(implode(', ', $materials)).'</li>';
        }
        if ($colors !== []) {
            $features[] = '<li><strong>Color / acabado:</strong> '.$e(implode(', ', $colors)).'</li>';
        }
        if ($sizes !== [] && ! isset($tech['Dimensiones'])) {
            $features[] = '<li><strong>Medidas:</strong> '.$e(implode(' x ', $sizes)).'</li>';
        }
        if (! isset($tech['Dimensiones']) && $sizes === [] && ($dimension = $this->dimensionsFromName((string) $item['name']))) {
            $features[] = "<li><strong>Medida:</strong> {$e($dimension)}</li>";
        }
        $features[] = "<li><strong>Línea:</strong> {$e($categoryMeta['name'])}</li>";

        $benefit = match (true) {
            isset($tech['Potencia'], $tech['Temperatura de color']) => " Su potencia de {$e($tech['Potencia'])} y su {$e($tech['Temperatura de color'])} brindan una iluminación eficiente y confortable, con bajo consumo energético gracias a la tecnología LED.",
            isset($tech['Potencia']) => " Con una potencia de {$e($tech['Potencia'])}, ofrece una iluminación eficiente y de bajo consumo.",
            $materials !== [] => ' Está fabricado en '.$e(Str::lower(implode(' y ', $materials))).', lo que garantiza resistencia y un excelente desempeño en el uso diario.',
            default => ' Combina un diseño funcional con materiales de calidad para un uso prolongado.',
        };

        return implode("\n", array_filter([
            "<p><strong>{$e($readableName)}</strong> de la marca <strong>{$e($brand)}</strong> (código {$e($sku)}) es una solución de {$e($categoryLower)} pensada para quienes buscan calidad, diseño y durabilidad en sus proyectos.{$benefit}</p>",
            isset($tech['Regulable (dimmer)']) ? '<p>Permite regular la intensidad de la luz para crear distintos ambientes, desde una iluminación funcional hasta una atmósfera cálida y acogedora.</p>' : null,
            '<h3>Características principales</h3>',
            '<ul>'.implode('', $features).'</ul>',
            '<h3>Aplicaciones recomendadas</h3>',
            "<p>Recomendado para {$e($categoryMeta['applications'])}. Una opción confiable tanto para instaladores, arquitectos y contratistas como para clientes finales que buscan renovar sus espacios.</p>",
            '<h3>Compra con respaldo en Perú</h3>',
            "<p>En <strong>Import Hns San Luis</strong> atendemos ventas por mayor y menor de {$e($categoryLower)} {$e($brand)} en Lima y todo el Perú, con cotización formal, factura electrónica y despacho a provincias. Solicita hoy tu cotización de <strong>{$e($readableName)}</strong>.</p>",
        ]));
    }

    /**
     * @param  list<string>  $materials
     * @param  array<string, string>  $tech
     * @param  array{slug: string, name: string, description: string, applications: string}  $categoryMeta
     */
    protected function metaDescription(string $readableName, string $brand, array $materials, array $tech, array $categoryMeta): string
    {
        $detail = isset($tech['Potencia'])
            ? ' LED '.$tech['Potencia'].(isset($tech['Temperatura de color']) ? ' '.Str::before($tech['Temperatura de color'], ' ') : '')
            : ($materials !== [] ? ' en '.Str::lower($materials[0]) : '');

        return Str::limit("{$readableName} {$brand}{$detail}. ".Str::ucfirst(Str::lower($categoryMeta['name'])).' por mayor y menor en Lima y todo Perú. Cotiza con Import Hns San Luis.', 160, '');
    }

    /**
     * @param  list<string>  $materials
     * @param  list<string>  $colors
     * @param  list<string>  $sizes
     * @param  array<string, string>  $tech
     */
    protected function syncSpecifications(Product $product, string $sku, string $brand, string $category, array $materials, array $colors, array $sizes, array $tech): void
    {
        $rows = [
            ['Identificación', 'Marca', $brand],
            ['Identificación', 'Código', $sku],
            ['Identificación', 'Línea', $category],
        ];
        foreach ($tech as $label => $value) {
            $rows[] = ['Datos técnicos', $label, $value];
        }
        if ($materials !== []) {
            $rows[] = ['Características', 'Material', implode(', ', $materials)];
        }
        if ($colors !== []) {
            $rows[] = ['Características', 'Color / Acabado', implode(', ', $colors)];
        }
        if ($sizes !== [] && ! isset($tech['Dimensiones'])) {
            $rows[] = ['Características', 'Medidas', implode(' x ', $sizes)];
        }

        $product->specifications()->delete();

        $sortOrder = 1;
        ProductSpecification::query()->insert(array_map(fn (array $row) => [
            'product_id' => $product->id,
            'spec_group' => $row[0],
            'spec_key' => $row[1],
            'spec_value' => Str::limit($row[2], 250, ''),
            'unit' => null,
            'sort_order' => $sortOrder++,
            'created_at' => now(),
            'updated_at' => now(),
        ], $rows));
    }

    /**
     * Descarga las imágenes originales y conserva las 2 de mayor resolución (respetando el orden de la galería).
     *
     * @param  list<string>  $imageUrls
     */
    protected function downloadImages(Product $product, array $imageUrls, string $readableName, string $brand): void
    {
        $candidates = [];
        foreach (array_slice(array_values(array_unique($imageUrls)), 0, 6) as $position => $url) {
            $binary = $this->scraper->download($url);
            $size = $binary ? @getimagesizefromstring($binary) : false;
            if ($size === false || $size[0] < 150 || $size[1] < 150) {
                continue;
            }

            $extension = match ($size[2]) {
                IMAGETYPE_PNG => 'png',
                IMAGETYPE_WEBP => 'webp',
                default => 'jpg',
            };
            $candidates[] = ['position' => $position, 'area' => $size[0] * $size[1], 'binary' => $binary, 'extension' => $extension];
        }

        usort($candidates, fn (array $a, array $b) => $b['area'] <=> $a['area']);
        $selected = array_slice($candidates, 0, self::MAX_IMAGES);
        usort($selected, fn (array $a, array $b) => $a['position'] <=> $b['position']);

        foreach ($selected as $index => $image) {
            $path = sprintf('products/italocks/%s-%d.%s', $product->slug, $index + 1, $image['extension']);
            Storage::disk('public')->put($path, $image['binary']);

            ProductImage::query()->create([
                'product_id' => $product->id,
                'file_path' => $path,
                'thumbnail_path' => $this->thumbnails->generateThumbnail($path) ?? $path,
                'alt_text' => Str::limit("{$readableName} {$brand} - imagen ".($index + 1).' | Import Hns San Luis', 250, ''),
                'is_primary' => $index === 0,
                'sort_order' => $index + 1,
            ]);

            $this->summary['images_downloaded']++;
        }
    }
}
