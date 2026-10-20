<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSpecification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MassiveCatalogSeeder extends Seeder
{
    protected int $targetCount = 1020;

    public function setTargetCount(int $count): self
    {
        $this->targetCount = max(100, $count);

        return $this;
    }

    /**
     * Run the massive catalog seeder (+1,000 published products, technical SVGs, >2,000 specifications, sample CSV).
     */
    public function run(): void
    {
        $this->generateTechnicalSvgAssets();
        $this->generateSampleImportCsv();

        $brands = $this->ensureBrands();
        $categories = $this->ensureCategories();

        // Backfill primary images for any existing products without images
        $this->attachSvgToExistingProducts();

        $currentPublished = Product::query()->where('status', 'published')->count();
        $needed = max(0, $this->targetCount - $currentPublished);

        if ($needed === 0) {
            return;
        }

        $existingSkus = Product::withTrashed()
            ->pluck('sku')
            ->mapWithKeys(fn ($sku) => [strtoupper((string) $sku) => true])
            ->all();

        $existingSlugs = Product::withTrashed()
            ->pluck('slug')
            ->mapWithKeys(fn ($slug) => [strtolower((string) $slug) => true])
            ->all();

        $now = now()->toDateTimeString();
        $productsToInsert = [];
        $specsBySku = [];
        $imagesBySku = [];

        $families = $this->getProductFamilies($categories, $brands);
        $familyCount = count($families);

        $seq = 1;
        $generated = 0;

        while ($generated < $needed) {
            $family = $families[$generated % $familyCount];
            $variantIndex = intdiv($generated, $familyCount) + 1;

            $item = ($family['builder'])($variantIndex, $seq);
            $seq++;

            $sku = strtoupper($item['sku']);
            if (isset($existingSkus[$sku])) {
                continue;
            }
            $existingSkus[$sku] = true;

            $baseSlug = Str::slug($item['name'].'-'.$sku);
            $slug = $baseSlug;
            $slugCounter = 1;
            while (isset($existingSlugs[$slug])) {
                $slug = $baseSlug.'-'.$slugCounter++;
            }
            $existingSlugs[$slug] = true;

            $productsToInsert[] = [
                'sku' => $sku,
                'name' => $item['name'],
                'slug' => $slug,
                'short_description' => $item['short_description'],
                'detailed_description' => $item['detailed_description'],
                'brand_id' => $item['brand_id'],
                'category_id' => $item['category_id'],
                'subcategory_id' => $item['subcategory_id'],
                'dimensions' => $item['dimensions'],
                'material' => $item['material'],
                'sale_unit' => $item['sale_unit'],
                'price' => $item['price'],
                'currency' => $item['currency'],
                'price_visibility' => $item['price_visibility'],
                'availability_status' => $item['availability_status'],
                'technical_pdf_path' => null,
                'status' => 'published',
                'is_featured' => $item['is_featured'],
                'views_count' => ($generated * 7) % 180,
                'meta_title' => $item['name'].' | Import Hns San Luis',
                'meta_description' => Str::limit($item['short_description'], 155),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $specsBySku[$sku] = $item['specs'];
            $imagesBySku[$sku] = [
                'file_path' => $item['svg_path'],
                'alt_text' => $item['name'].' ('.$sku.')',
            ];

            $generated++;
        }

        // Batch insert products, images, and technical specifications inside transactions
        foreach (array_chunk($productsToInsert, 200) as $productChunk) {
            DB::transaction(function () use ($productChunk, $specsBySku, $imagesBySku, $now) {
                Product::query()->insert($productChunk);

                $chunkSkus = array_column($productChunk, 'sku');
                $insertedMap = Product::query()
                    ->whereIn('sku', $chunkSkus)
                    ->pluck('id', 'sku')
                    ->all();

                $imageRows = [];
                $specRows = [];

                foreach ($chunkSkus as $sku) {
                    $productId = $insertedMap[$sku] ?? null;
                    if (! $productId) {
                        continue;
                    }

                    if (isset($imagesBySku[$sku])) {
                        $imageRows[] = [
                            'product_id' => $productId,
                            'file_path' => $imagesBySku[$sku]['file_path'],
                            'thumbnail_path' => $imagesBySku[$sku]['file_path'],
                            'alt_text' => $imagesBySku[$sku]['alt_text'],
                            'is_primary' => true,
                            'sort_order' => 1,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    if (! empty($specsBySku[$sku])) {
                        $order = 1;
                        foreach ($specsBySku[$sku] as $spec) {
                            $specRows[] = [
                                'product_id' => $productId,
                                'spec_group' => $spec['spec_group'],
                                'spec_key' => $spec['spec_key'],
                                'spec_value' => $spec['spec_value'],
                                'unit' => $spec['unit'] ?? null,
                                'sort_order' => $order++,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }
                }

                if (! empty($imageRows)) {
                    foreach (array_chunk($imageRows, 300) as $imgBatch) {
                        ProductImage::query()->insert($imgBatch);
                    }
                }

                if (! empty($specRows)) {
                    foreach (array_chunk($specRows, 500) as $specBatch) {
                        ProductSpecification::query()->insert($specBatch);
                    }
                }
            });
        }
    }

    /**
     * Generate crisp CAD-inspired SVG technical illustrations for every hardware category.
     */
    public function generateTechnicalSvgAssets(): void
    {
        $disk = Storage::disk('public');
        $disk->makeDirectory('products/svg');

        $svgs = [
            'bisagra-cazoleta.svg' => $this->buildTechnicalSvg(
                'HERRAJES DE PRECISIÓN',
                'BISAGRA CAZOLETA Ø35MM CLIP-ON',
                '#1E3A8A',
                '#F59E0B',
                '<circle cx="150" cy="200" r="56" fill="#1E293B" stroke="#F59E0B" stroke-width="3"/>
                 <circle cx="150" cy="200" r="38" fill="#0F172A" stroke="#94A3B8" stroke-width="2" stroke-dasharray="6 4"/>
                 <circle cx="150" cy="128" r="7" fill="#F59E0B"/>
                 <circle cx="150" cy="272" r="7" fill="#F59E0B"/>
                 <rect x="200" y="172" width="190" height="56" rx="8" fill="#334155" stroke="#CBD5E1" stroke-width="2.5"/>
                 <rect x="235" y="186" width="90" height="28" rx="4" fill="#0F172A" stroke="#F59E0B" stroke-width="1.8"/>
                 <circle cx="355" cy="200" r="9" fill="#F59E0B"/>
                 <rect x="330" y="135" width="46" height="130" rx="6" fill="none" stroke="#64748B" stroke-width="2" stroke-dasharray="4 4"/>',
                'Ø 35 mm | 110° - 165° | ACERO NIQUELADO / INOX'
            ),
            'corredera-telescopica.svg' => $this->buildTechnicalSvg(
                'SISTEMAS DE CAJONERÍA',
                'CORREDERA TELESCÓPICA EXTRACCIÓN TOTAL',
                '#0F172A',
                '#F59E0B',
                '<rect x="70" y="145" width="380" height="44" rx="5" fill="#1E293B" stroke="#94A3B8" stroke-width="2.5"/>
                 <rect x="110" y="155" width="340" height="24" rx="3" fill="#334155" stroke="#F59E0B" stroke-width="2"/>
                 <circle cx="145" cy="167" r="5" fill="#F59E0B"/><circle cx="170" cy="167" r="5" fill="#F59E0B"/><circle cx="195" cy="167" r="5" fill="#F59E0B"/>
                 <rect x="90" y="220" width="380" height="44" rx="5" fill="#1E293B" stroke="#94A3B8" stroke-width="2.5"/>
                 <rect x="90" y="230" width="310" height="24" rx="3" fill="#0F172A" stroke="#38BDF8" stroke-width="2"/>
                 <circle cx="330" cy="242" r="5" fill="#38BDF8"/><circle cx="355" cy="242" r="5" fill="#38BDF8"/>',
                '250mm - 600mm | CARGA 35KG - 45KG | SOFT CLOSE'
            ),
            'cerradura-arquitectonica.svg' => $this->buildTechnicalSvg(
                'CERRAJERÍA & SEGURIDAD',
                'CERRADURA Y CILINDRO ARQUITECTÓNICO',
                '#1E293B',
                '#F59E0B',
                '<rect x="155" y="105" width="110" height="190" rx="10" fill="#1E293B" stroke="#CBD5E1" stroke-width="2.5"/>
                 <rect x="265" y="130" width="18" height="42" fill="#F59E0B"/>
                 <rect x="265" y="210" width="24" height="55" fill="#38BDF8"/>
                 <circle cx="210" cy="165" r="22" fill="#0F172A" stroke="#F59E0B" stroke-width="2.5"/>
                 <circle cx="210" cy="238" r="14" fill="#0F172A" stroke="#94A3B8" stroke-width="2"/>
                 <rect x="206" y="238" width="8" height="20" rx="3" fill="#94A3B8"/>
                 <path d="M330 165 L415 165 L415 190 L395 190 L395 178 L375 178 L375 190 L355 190 L355 165 Z" fill="#F59E0B"/>',
                'ACERO INOX / BRONCE | ALTA SEGURIDAD B2B'
            ),
            'tirador-perfil.svg' => $this->buildTechnicalSvg(
                'PERFILERÍA & ACABADOS',
                'TIRADOR ARQUITECTÓNICO & PERFIL GOLA',
                '#172554',
                '#38BDF8',
                '<path d="M110 245 L110 170 Q110 145 135 145 L385 145 Q410 145 410 170 L410 245" fill="none" stroke="#F59E0B" stroke-width="18" stroke-linecap="round"/>
                 <rect x="94" y="235" width="32" height="18" rx="3" fill="#94A3B8"/>
                 <rect x="394" y="235" width="32" height="18" rx="3" fill="#94A3B8"/>
                 <line x1="110" y1="280" x2="410" y2="280" stroke="#38BDF8" stroke-width="2" stroke-dasharray="5 5"/>
                 <circle cx="110" cy="280" r="4" fill="#38BDF8"/><circle cx="410" cy="280" r="4" fill="#38BDF8"/>',
                '96mm - 3000mm | ALUMINIO ANODIZADO / INOX'
            ),
            'tornilleria-estructural.svg' => $this->buildTechnicalSvg(
                'FIJACIONES & ANCLAJES',
                'TORNILLERÍA Y ANCLAJE ESTRUCTURAL',
                '#0F172A',
                '#F59E0B',
                '<polygon points="120,165 165,135 165,265 120,235" fill="#F59E0B" stroke="#CBD5E1" stroke-width="2"/>
                 <rect x="165" y="178" width="230" height="44" fill="#334155" stroke="#94A3B8" stroke-width="2"/>
                 <polygon points="395,178 445,200 395,222" fill="#F59E0B"/>
                 <line x1="190" y1="172" x2="205" y2="228" stroke="#F59E0B" stroke-width="4"/>
                 <line x1="225" y1="172" x2="240" y2="228" stroke="#F59E0B" stroke-width="4"/>
                 <line x1="260" y1="172" x2="275" y2="228" stroke="#F59E0B" stroke-width="4"/>
                 <line x1="295" y1="172" x2="310" y2="228" stroke="#F59E0B" stroke-width="4"/>
                 <line x1="330" y1="172" x2="345" y2="228" stroke="#F59E0B" stroke-width="4"/>
                 <line x1="365" y1="172" x2="380" y2="228" stroke="#F59E0B" stroke-width="4"/>',
                'ACERO CARBONO ZINCADO / INOX | ROSCA PROFUNDA'
            ),
            'disco-abrasivo.svg' => $this->buildTechnicalSvg(
                'ABRASIVOS & CORTE INDUSTRIAL',
                'DISCO DE CORTE Y DESBASTE TÉCNICO',
                '#1E1B4B',
                '#F59E0B',
                '<circle cx="260" cy="200" r="96" fill="#1E293B" stroke="#F59E0B" stroke-width="5" stroke-dasharray="18 6"/>
                 <circle cx="260" cy="200" r="68" fill="none" stroke="#64748B" stroke-width="1.5" stroke-dasharray="5 5"/>
                 <circle cx="260" cy="200" r="32" fill="#334155" stroke="#CBD5E1" stroke-width="3"/>
                 <circle cx="260" cy="200" r="14" fill="#0F172A" stroke="#F59E0B" stroke-width="2.5"/>',
                'Ø 4.1/2" - 14" | INOX / METAL / CONCRETO / MELAMINA'
            ),
            'epp-seguridad.svg' => $this->buildTechnicalSvg(
                'SEGURIDAD INDUSTRIAL EPP',
                'PROTECCIÓN PERSONAL CERTIFICADA EN OBRA',
                '#0F172A',
                '#F59E0B',
                '<path d="M150 225 C150 140 370 140 370 225 Z" fill="#F59E0B" stroke="#FEF08A" stroke-width="3"/>
                 <path d="M130 225 L390 225 L405 242 L115 242 Z" fill="#D97706"/>
                 <path d="M225 148 L295 148 L285 215 L235 215 Z" fill="#B45309" opacity="0.45"/>
                 <circle cx="260" cy="185" r="16" fill="#0F172A" stroke="#FEF08A" stroke-width="2"/>',
                'NORMA ANSI / CE | ALTA RESISTENCIA INDUSTRIAL'
            ),
            'herramienta-industrial.svg' => $this->buildTechnicalSvg(
                'HERRAMIENTAS DE PRECISIÓN',
                'HERRAMIENTA Y SUJECIÓN PROFESIONAL',
                '#0F172A',
                '#38BDF8',
                '<rect x="105" y="182" width="230" height="36" rx="6" fill="#334155" stroke="#CBD5E1" stroke-width="2.5"/>
                 <rect x="130" y="135" width="38" height="110" rx="5" fill="#F59E0B" stroke="#FEF08A" stroke-width="2"/>
                 <rect x="275" y="135" width="38" height="110" rx="5" fill="#38BDF8" stroke="#BAE6FD" stroke-width="2"/>
                 <rect x="335" y="176" width="85" height="48" rx="10" fill="#F59E0B"/>',
                'ACERO CROMO-VANADIO | USO INTENSIVO TALLER & OBRA'
            ),
            'sistema-corredizo.svg' => $this->buildTechnicalSvg(
                'CARPINTERÍA ARQUITECTÓNICA',
                'CARRO RODAMIENTO Y RIEL DE ALUMINIO',
                '#0F172A',
                '#F59E0B',
                '<rect x="90" y="130" width="340" height="32" rx="4" fill="#334155" stroke="#94A3B8" stroke-width="2"/>
                 <rect x="165" y="175" width="190" height="68" rx="8" fill="#1E293B" stroke="#F59E0B" stroke-width="2.5"/>
                 <circle cx="205" cy="158" r="20" fill="#F59E0B" stroke="#0F172A" stroke-width="3"/>
                 <circle cx="205" cy="158" r="7" fill="#0F172A"/>
                 <circle cx="315" cy="158" r="20" fill="#F59E0B" stroke="#0F172A" stroke-width="3"/>
                 <circle cx="315" cy="158" r="7" fill="#0F172A"/>',
                'RODAMIENTO DE BOLAS | 50KG - 120KG POR HOJA'
            ),
        ];

        foreach ($svgs as $filename => $content) {
            $disk->put('products/svg/'.$filename, $content);
        }
    }

    protected function buildTechnicalSvg(
        string $badge,
        string $title,
        string $bgPrimary,
        string $accent,
        string $graphicInner,
        string $footerSpec
    ): string {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 520 380" width="520" height="380">
  <defs>
    <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$bgPrimary}" />
      <stop offset="100%" stop-color="#090D16" />
    </linearGradient>
    <pattern id="cadGrid" width="26" height="26" patternUnits="userSpaceOnUse">
      <path d="M 26 0 L 0 0 0 26" fill="none" stroke="#334155" stroke-width="0.6" stroke-opacity="0.45"/>
    </pattern>
  </defs>
  <rect width="520" height="380" fill="url(#bgGrad)"/>
  <rect width="520" height="380" fill="url(#cadGrid)"/>
  <rect x="16" y="16" width="488" height="348" rx="12" fill="none" stroke="#334155" stroke-width="1.5"/>
  <rect x="32" y="30" width="210" height="24" rx="4" fill="{$accent}" fill-opacity="0.18" stroke="{$accent}" stroke-width="1"/>
  <text x="42" y="46" font-family="Arial, sans-serif" font-size="10" font-weight="bold" fill="{$accent}" letter-spacing="1">{$badge}</text>
  <text x="486" y="46" font-family="Arial, sans-serif" font-size="10" font-weight="bold" fill="#94A3B8" text-anchor="end">IMPORT HNS SAN LUIS</text>
  <text x="32" y="76" font-family="Arial, sans-serif" font-size="13" font-weight="bold" fill="#F8FAFC" letter-spacing="0.5">{$title}</text>
  <g>
    {$graphicInner}
  </g>
  <line x1="32" y1="318" x2="488" y2="318" stroke="#334155" stroke-width="1"/>
  <text x="32" y="340" font-family="Consolas, monospace" font-size="11" fill="#CBD5E1">{$footerSpec}</text>
  <circle cx="478" cy="336" r="5" fill="{$accent}"/>
</svg>
SVG;
    }

    /**
     * Generate a realistic sample CSV file in storage/app/public/samples/catalogo_importacion_ejemplo.csv.
     */
    public function generateSampleImportCsv(): void
    {
        $disk = Storage::disk('public');
        $disk->makeDirectory('samples');

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF");

        fputcsv($stream, [
            'sku',
            'name',
            'category',
            'subcategory',
            'brand',
            'short_description',
            'detailed_description',
            'dimensions',
            'material',
            'sale_unit',
            'price',
            'currency',
            'price_visibility',
            'availability_status',
            'status',
            'specs',
        ]);

        $sampleRows = [
            [
                'SL-BIS-35SC-CURVA-IMP',
                'Bisagra Cazoleta 35mm Semicurva Cierre Suave Clip-On',
                'Herrajes y Accesorios para Muebles',
                'Bisagras Cazoleta y Especiales',
                'San Luis Línea Técnica',
                'Bisagra cazoleta 35mm brazo semicurvo con pistón hidráulico amortiguado.',
                'Diseñada para puertas centrales compartidas sobre un mismo lateral de 18mm. Incluye base clip de 4 huecos y tornillos.',
                'Ø 35 mm | Apertura 110°',
                'Acero laminado niquelado',
                'Par',
                '10.20',
                'PEN',
                'show',
                'available',
                'published',
                'Diámetro Cazoleta:35 mm|Tipo de Brazo:Semicurva (Media cobertura)|Cierre:Suave Hidráulico|Ciclos:80,000 ciclos',
            ],
            [
                'SL-COR-TEL-400HD-IMP',
                'Corredera Telescópica Pesada 400mm x 45mm Carga 45kg',
                'Herrajes y Accesorios para Muebles',
                'Correderas Telescópicas y Ocultas',
                'Ducasse Industrial',
                'Corredera telescópica extracción total 400mm (16") rodamiento de billas.',
                'Corredera de 3 cuerpos en acero zincado de 1.2x1.2x1.5mm para cajones de cocina, oficina y taller.',
                '400 mm (16") x 45 mm',
                'Acero zincado brillante',
                'Juego',
                '15.40',
                'PEN',
                'inherit',
                'available',
                'published',
                'Largo Nominal:400 mm|Ancho Perfil:45 mm|Capacidad de Carga:45 kg|Extracción:Total 100%',
            ],
            [
                'SL-TOR-MEL-3540-IMP',
                'Tornillo Autorroscante para Melamina 3.5 x 40mm Zincado (Millar)',
                'Fijaciones, Tornillería y Anclajes',
                'Tornillos para Melamina y Madera',
                'Fischer Fijaciones',
                'Tornillo aglomerado cabeza avellanada Pozi #2 3.5x40mm caja por 1,000 unidades.',
                'Rosca profunda cementada para fijación de tableros de 15mm y 18mm sin fisurar el canto.',
                'Ø 3.5 mm x 40 mm',
                'Acero al carbono cementado zincado',
                'Millar',
                '31.50',
                'PEN',
                'show',
                'available',
                'published',
                'Diámetro:3.5 mm|Largo:40 mm|Impronta:Pozi PZ2|Presentación:Caja x 1,000 uds',
            ],
            [
                'SL-CER-EMB-70INOX-IMP',
                'Cerradura de Embutir Arquitectónica Backset 60mm Acero Inoxidable',
                'Cerraduras y Cerrajería Arquitectónica',
                'Cerraduras y Cierrapuertas para Obras',
                'Forte Seguridad',
                'Cerradura de embutir grado 2 con cilindro de bronce de 70mm y 5 llaves multipunto.',
                'Recomendada para puertas principales y oficinas corporativas de alto tránsito.',
                'Backset 60 mm | Cilindro 70 mm',
                'Acero Inoxidable SUS304 y Bronce',
                'Unidad',
                '',
                'PEN',
                'hide',
                'on_request',
                'published',
                'Backset:60 mm|Cilindro:70 mm Bronce|Acabado:Inox Satinado|Grado ANSI:Grado 2 (200,000 ciclos)',
            ],
        ];

        foreach ($sampleRows as $row) {
            fputcsv($stream, $row);
        }

        rewind($stream);
        $csvContent = stream_get_contents($stream) ?: '';
        fclose($stream);

        $disk->put('samples/catalogo_importacion_ejemplo.csv', $csvContent);
    }

    /**
     * Ensure all brands exist and return map by slug.
     *
     * @return array<string, Brand>
     */
    protected function ensureBrands(): array
    {
        $brandsData = [
            ['name' => 'San Luis Línea Técnica', 'slug' => 'san-luis-linea-tecnica', 'description' => 'Línea seleccionada de herrajes, fijaciones y accesorios técnicos de alto rendimiento.', 'is_verified' => true, 'sort_order' => 1],
            ['name' => 'Ducasse Industrial', 'slug' => 'ducasse-industrial', 'description' => 'Sistemas corredizos y herrajes para mobiliario y arquitectura.', 'is_verified' => true, 'sort_order' => 2],
            ['name' => 'Hafele Pro', 'slug' => 'hafele-pro', 'description' => 'Herrajes especializados para muebles, cocinas y equipamiento comercial.', 'is_verified' => true, 'sort_order' => 3],
            ['name' => 'Forte Seguridad', 'slug' => 'forte-seguridad', 'description' => 'Cerraduras, candados y sistemas de seguridad para puertas y accesos.', 'is_verified' => true, 'sort_order' => 4],
            ['name' => 'Fischer Fijaciones', 'slug' => 'fischer-fijaciones', 'description' => 'Anclajes mecánicos, químicos y fijaciones estructurales para construcción.', 'is_verified' => true, 'sort_order' => 5],
            ['name' => 'Stanley Industrial', 'slug' => 'stanley-industrial', 'description' => 'Herramientas manuales de precisión y accesorios de sujeción profesional.', 'is_verified' => true, 'sort_order' => 6],
            ['name' => 'Norton Abrasivos', 'slug' => 'norton-abrasivos', 'description' => 'Discos de corte, desbaste, lijas técnicas y abrasivos industriales.', 'is_verified' => true, 'sort_order' => 7],
            ['name' => '3M Seguridad Industrial', 'slug' => '3m-seguridad-industrial', 'description' => 'Equipos de protección personal (EPP), cintas industriales y adhesivos.', 'is_verified' => true, 'sort_order' => 8],
        ];

        $map = [];
        foreach ($brandsData as $b) {
            $brand = Brand::query()->updateOrCreate(
                ['slug' => $b['slug']],
                array_merge($b, ['is_active' => true])
            );
            $map[$b['slug']] = $brand;
        }

        return $map;
    }

    /**
     * Ensure categories and subcategories exist and return map by slug.
     *
     * @return array<string, Category>
     */
    protected function ensureCategories(): array
    {
        $structure = [
            [
                'name' => 'Herrajes y Accesorios para Muebles',
                'slug' => 'herrajes-accesorios-muebles',
                'description' => 'Bisagras cazoleta, correderas telescópicas, pistones a gas, tiradores y soportes para fabricación de mobiliario.',
                'sort_order' => 1,
                'children' => [
                    ['name' => 'Bisagras Cazoleta y Especiales', 'slug' => 'bisagras-cazoleta-especiales', 'sort_order' => 1],
                    ['name' => 'Correderas Telescópicas y Ocultas', 'slug' => 'correderas-telescopicas-ocultas', 'sort_order' => 2],
                    ['name' => 'Tiradores, Manijas y Perillas', 'slug' => 'tiradores-manijas-perillas', 'sort_order' => 3],
                    ['name' => 'Pistones y Sistemas Elevables', 'slug' => 'pistones-sistemas-elevables', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Sistemas Corredizos y Carpintería de Aluminio',
                'slug' => 'sistemas-corredizos-aluminio',
                'description' => 'Rieles, carros colgantes, rodamientos y accesorios para puertas corredizas de clóset, mamparas y divisiones.',
                'sort_order' => 2,
                'children' => [
                    ['name' => 'Kits y Carros para Clóset', 'slug' => 'kits-carros-closet', 'sort_order' => 1],
                    ['name' => 'Rieles y Perfiles de Aluminio', 'slug' => 'rieles-perfiles-aluminio', 'sort_order' => 2],
                ],
            ],
            [
                'name' => 'Fijaciones, Tornillería y Anclajes',
                'slug' => 'fijaciones-tornilleria-anclajes',
                'description' => 'Tornillos autorroscantes para melamina, pernos de expansión, tarugos estructurales y conectores de armado.',
                'sort_order' => 3,
                'children' => [
                    ['name' => 'Tornillos para Melamina y Madera', 'slug' => 'tornillos-melamina-madera', 'sort_order' => 1],
                    ['name' => 'Anclajes de Expansión y Concreto', 'slug' => 'anclajes-expansion-concreto', 'sort_order' => 2],
                    ['name' => 'Conectores y Escuadras de Ensamble', 'slug' => 'conectores-escuadras-ensamble', 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Cerraduras y Cerrajería Arquitectónica',
                'slug' => 'cerraduras-cerrajeria-arquitectonica',
                'description' => 'Cerraduras de sobreponer, embutir, cerraduras para muebles de oficina, cierrapuertas hidráulicos y bisagras capuchinas.',
                'sort_order' => 4,
                'children' => [
                    ['name' => 'Cerraduras para Muebles y Archivos', 'slug' => 'cerraduras-muebles-archivos', 'sort_order' => 1],
                    ['name' => 'Cerraduras y Cierrapuertas para Obras', 'slug' => 'cerraduras-cierrapuertas-obras', 'sort_order' => 2],
                ],
            ],
            [
                'name' => 'Ferretería Industrial y Suministros de Obra',
                'slug' => 'ferreteria-industrial-suministros',
                'description' => 'Garruchas industriales de poliuretano, discos de corte, abrasivos, herramientas manuales y EPP para taller y obra.',
                'sort_order' => 5,
                'children' => [
                    ['name' => 'Garruchas y Ruedas Industriales', 'slug' => 'garruchas-ruedas-industriales', 'sort_order' => 1],
                    ['name' => 'Discos de Corte y Abrasivos Técnicos', 'slug' => 'discos-corte-abrasivos', 'sort_order' => 2],
                    ['name' => 'Herramientas de Taller y Montaje', 'slug' => 'herramientas-taller-montaje', 'sort_order' => 3],
                    ['name' => 'Equipos de Protección Personal (EPP)', 'slug' => 'equipos-proteccion-personal-epp', 'sort_order' => 4],
                ],
            ],
        ];

        $map = [];
        foreach ($structure as $catData) {
            $children = $catData['children'] ?? [];
            unset($catData['children']);

            $parent = Category::query()->updateOrCreate(
                ['slug' => $catData['slug']],
                array_merge($catData, ['is_active' => true, 'parent_id' => null])
            );
            $map[$parent->slug] = $parent;

            foreach ($children as $childData) {
                $child = Category::query()->updateOrCreate(
                    ['slug' => $childData['slug']],
                    [
                        'parent_id' => $parent->id,
                        'name' => $childData['name'],
                        'sort_order' => $childData['sort_order'],
                        'is_active' => true,
                    ]
                );
                $map[$child->slug] = $child;
            }
        }

        return $map;
    }

    /**
     * Attach SVG technical illustrations to any existing products that do not yet have a primary image.
     */
    protected function attachSvgToExistingProducts(): void
    {
        $productsWithoutImages = Product::query()
            ->doesntHave('images')
            ->with('subcategory')
            ->get();

        foreach ($productsWithoutImages as $product) {
            $svgPath = match (true) {
                str_starts_with($product->sku, 'SL-BIS-') => 'products/svg/bisagra-cazoleta.svg',
                str_starts_with($product->sku, 'SL-COR-CLO') || str_starts_with($product->sku, 'SL-RIE-') => 'products/svg/sistema-corredizo.svg',
                str_starts_with($product->sku, 'SL-COR-') => 'products/svg/corredera-telescopica.svg',
                str_starts_with($product->sku, 'SL-TIR-') || str_starts_with($product->sku, 'SL-PIS-') => 'products/svg/tirador-perfil.svg',
                str_starts_with($product->sku, 'SL-TOR-') || str_starts_with($product->sku, 'SL-ANC-') || str_starts_with($product->sku, 'SL-CON-') => 'products/svg/tornilleria-estructural.svg',
                str_starts_with($product->sku, 'SL-CER-') || str_starts_with($product->sku, 'SL-CIE-') => 'products/svg/cerradura-arquitectonica.svg',
                str_starts_with($product->sku, 'SL-DIS-') => 'products/svg/disco-abrasivo.svg',
                str_starts_with($product->sku, 'SL-EPP-') => 'products/svg/epp-seguridad.svg',
                default => 'products/svg/herramienta-industrial.svg',
            };

            ProductImage::query()->create([
                'product_id' => $product->id,
                'file_path' => $svgPath,
                'thumbnail_path' => $svgPath,
                'alt_text' => $product->name.' ('.$product->sku.')',
                'is_primary' => true,
                'sort_order' => 1,
            ]);
        }
    }

    /**
     * Define parametric technical builders for 6 main industrial product families.
     *
     * @param  array<string, Category>  $categories
     * @param  array<string, Brand>  $brands
     * @return array<int, array{builder: callable}>
     */
    protected function getProductFamilies(array $categories, array $brands): array
    {
        $visibilities = ['show', 'inherit', 'show', 'inherit', 'hide'];
        $availabilities = ['available', 'available', 'available', 'on_request', 'out_of_stock'];

        return [
            // 1. Bisagras Cazoleta y Especiales (SL-BIS-...)
            [
                'builder' => function (int $v, int $seq) use ($categories, $brands, $visibilities, $availabilities): array {
                    $cups = ['26mm', '35mm', '40mm'];
                    $arms = ['Recta (Cobertura Total)', 'Semicurva (Media Cobertura)', 'Curva / Interior (Embutida)', 'Angular 45°', 'Rincón 165°', 'Rincón Ciego 90°'];
                    $closings = ['Cierre Suave Hidráulico Clip-On', 'Estándar Resorte Doble Acción', 'Push-Open Apertura por Presión', 'Inoxidable SUS304 Cierre Suave'];
                    $finishes = ['Niquelado Satinado', 'Negro Ónix Titanio', 'Acero Inoxidable SUS304', 'Zincado Anticorrosivo'];
                    $units = ['Par', 'Caja x 100', 'Kit', 'Unidad'];
                    $brandKeys = ['san-luis-linea-tecnica', 'hafele-pro', 'ducasse-industrial'];

                    $cup = $cups[$v % count($cups)];
                    $arm = $arms[intdiv($v, 2) % count($arms)];
                    $closing = $closings[intdiv($v, 3) % count($closings)];
                    $finish = $finishes[intdiv($v, 5) % count($finishes)];
                    $unit = $units[$v % count($units)];
                    $brand = $brands[$brandKeys[$v % count($brandKeys)]];

                    $sku = sprintf('SL-BIS-%s-%04d', strtoupper(str_replace('mm', '', $cup)), $seq);
                    $name = sprintf('Bisagra Cazoleta %s %s %s — %s (%s)', $cup, $arm, $closing, $finish, $unit);

                    $basePrice = match ($unit) {
                        'Caja x 100' => round(340 + ($v % 18) * 14.5, 2),
                        'Kit' => round(22 + ($v % 10) * 2.4, 2),
                        default => round(7.20 + ($v % 12) * 1.35, 2),
                    };

                    $vis = $visibilities[$seq % count($visibilities)];
                    $avail = $availabilities[$seq % count($availabilities)];

                    return [
                        'sku' => $sku,
                        'name' => $name,
                        'category_id' => $categories['herrajes-accesorios-muebles']->id,
                        'subcategory_id' => $categories['bisagras-cazoleta-especiales']->id,
                        'brand_id' => $brand->id,
                        'short_description' => sprintf('Bisagra técnica de cazoleta %s %s con sistema %s y acabado %s.', $cup, strtolower($arm), strtolower($closing), strtolower($finish)),
                        'detailed_description' => sprintf('Herraje de precisión para puertas de muebles en melamina y MDF de 15mm a 22mm. Cazoleta de %s con regulación tridimensional excéntrica y ensayo de fatiga superior a 80,000 ciclos.', $cup),
                        'dimensions' => sprintf('Ø %s | Profundidad 11.8 mm | Espesor tablero 15-22 mm', $cup),
                        'material' => 'Acero laminado en frío — '.$finish,
                        'sale_unit' => $unit,
                        'price' => $vis === 'hide' ? null : $basePrice,
                        'currency' => 'PEN',
                        'price_visibility' => $vis,
                        'availability_status' => $avail,
                        'is_featured' => ($seq % 45 === 0),
                        'svg_path' => 'products/svg/bisagra-cazoleta.svg',
                        'specs' => [
                            ['spec_group' => 'Dimensiones', 'spec_key' => 'Diámetro Cazoleta', 'spec_value' => str_replace('mm', ' mm', $cup)],
                            ['spec_group' => 'Montaje', 'spec_key' => 'Tipo de Brazo', 'spec_value' => $arm],
                            ['spec_group' => 'Funcionamiento', 'spec_key' => 'Tipo de Cierre', 'spec_value' => $closing],
                            ['spec_group' => 'Acabado', 'spec_key' => 'Recubrimiento', 'spec_value' => $finish],
                        ],
                    ];
                },
            ],

            // 2. Correderas Telescópicas, Ocultas y Sistemas Corredizos (SL-COR-...)
            [
                'builder' => function (int $v, int $seq) use ($categories, $brands, $visibilities, $availabilities): array {
                    $lengths = [250, 300, 350, 400, 450, 500, 550, 600];
                    $types = [
                        ['label' => 'Corredera Telescópica Pesada 45mm Extracción Total', 'load' => '45 kg', 'closing' => 'Retención mecánica con tope de goma'],
                        ['label' => 'Corredera Telescópica Cierre Suave Hidráulico 45mm', 'load' => '40 kg', 'closing' => 'Suave Hidráulico (Soft Close)'],
                        ['label' => 'Corredera Telescópica Liviana 35mm Rodamiento Billas', 'load' => '25 kg', 'closing' => 'Estándar Billas de Acero'],
                        ['label' => 'Corredera Oculta Sincronizada Montaje Inferior Clips 3D', 'load' => '35 kg', 'closing' => 'Suave Sincronizado'],
                        ['label' => 'Corredera Telescópica Push-Open Apertura por Presión 45mm', 'load' => '35 kg', 'closing' => 'Push-to-Open sin tirador'],
                    ];
                    $finishes = ['Zincado Brillante', 'Electrozincado Negro', 'Tropicalizado Anticorrosivo'];
                    $units = ['Juego', 'Par', 'Kit'];
                    $brandKeys = ['ducasse-industrial', 'hafele-pro', 'san-luis-linea-tecnica'];

                    $len = $lengths[$v % count($lengths)];
                    $inches = (int) round($len / 25);
                    $type = $types[intdiv($v, 2) % count($types)];
                    $finish = $finishes[intdiv($v, 4) % count($finishes)];
                    $unit = $units[$v % count($units)];
                    $brand = $brands[$brandKeys[$v % count($brandKeys)]];

                    $sku = sprintf('SL-COR-%d-%04d', $len, $seq);
                    $name = sprintf('%s %dmm (%d") %s — %s', $type['label'], $len, $inches, $finish, $unit);

                    $basePrice = round(11.50 + ($len / 50) * 2.10 + ($v % 6) * 1.75, 2);
                    $vis = $visibilities[$seq % count($visibilities)];
                    $avail = $availabilities[$seq % count($availabilities)];

                    return [
                        'sku' => $sku,
                        'name' => $name,
                        'category_id' => $categories['herrajes-accesorios-muebles']->id,
                        'subcategory_id' => $categories['correderas-telescopicas-ocultas']->id,
                        'brand_id' => $brand->id,
                        'short_description' => sprintf('%s de %dmm (%d") con capacidad de %s y acabado %s.', $type['label'], $len, $inches, $type['load'], strtolower($finish)),
                        'detailed_description' => sprintf('Guía telescópica de tres cuerpos con rodamientos de bolas de acero lubricadas de alta precisión. Largo nominal %d mm (%d pulgadas), capacidad de carga dinámica de %s por juego.', $len, $inches, $type['load']),
                        'dimensions' => sprintf('%d mm (%d") x 45 mm x 12.7 mm', $len, $inches),
                        'material' => 'Acero laminado en frío 1.2x1.2x1.5mm — '.$finish,
                        'sale_unit' => $unit,
                        'price' => $vis === 'hide' ? null : $basePrice,
                        'currency' => 'PEN',
                        'price_visibility' => $vis,
                        'availability_status' => $avail,
                        'is_featured' => ($seq % 50 === 0),
                        'svg_path' => 'products/svg/corredera-telescopica.svg',
                        'specs' => [
                            ['spec_group' => 'Dimensiones', 'spec_key' => 'Largo Nominal', 'spec_value' => $len.' mm'],
                            ['spec_group' => 'Resistencia', 'spec_key' => 'Capacidad de Carga', 'spec_value' => $type['load']],
                            ['spec_group' => 'Funcionamiento', 'spec_key' => 'Tipo de Cierre', 'spec_value' => $type['closing']],
                            ['spec_group' => 'Acabado', 'spec_key' => 'Recubrimiento', 'spec_value' => $finish],
                        ],
                    ];
                },
            ],

            // 3. Cerraduras, Cerrajería Arquitectónica y Tiradores (SL-CER-...)
            [
                'builder' => function (int $v, int $seq) use ($categories, $brands, $visibilities, $availabilities): array {
                    $models = [
                        ['name' => 'Cerradura para Mueble y Cajón Cilindro 22mm', 'sub' => 'cerraduras-muebles-archivos', 'backset' => '22 mm', 'svg' => 'products/svg/cerradura-arquitectonica.svg'],
                        ['name' => 'Cerradura Trampa Central para Archivador 3 Gavetas', 'sub' => 'cerraduras-muebles-archivos', 'backset' => 'Barra 600 mm', 'svg' => 'products/svg/cerradura-arquitectonica.svg'],
                        ['name' => 'Cerradura de Sobreponer Blindada 3 Golpes Pitones de Acero', 'sub' => 'cerraduras-cierrapuertas-obras', 'backset' => '60 mm', 'svg' => 'products/svg/cerradura-arquitectonica.svg'],
                        ['name' => 'Cerradura de Embutir Arquitectónica Manija Inox Grado 2', 'sub' => 'cerraduras-cierrapuertas-obras', 'backset' => '60 mm / 70 mm', 'svg' => 'products/svg/cerradura-arquitectonica.svg'],
                        ['name' => 'Cierrapuertas Hidráulico Aéreo Brazo Retenedor Regulable', 'sub' => 'cerraduras-cierrapuertas-obras', 'backset' => '65 kg - 85 kg', 'svg' => 'products/svg/cerradura-arquitectonica.svg'],
                        ['name' => 'Bisagra Capuchina Arquitectónica Rodamiento 4" x 3" x 3mm', 'sub' => 'cerraduras-cierrapuertas-obras', 'backset' => '102 x 76 mm', 'svg' => 'products/svg/bisagra-cazoleta.svg'],
                    ];
                    $finishes = ['Acero Inoxidable SUS304', 'Bronce Antiguo Satinado', 'Negro Mate Arquitectónico', 'Cromado Brillante'];
                    $units = ['Unidad', 'Kit', 'Juego'];
                    $brandKeys = ['forte-seguridad', 'hafele-pro', 'san-luis-linea-tecnica'];

                    $m = $models[$v % count($models)];
                    $finish = $finishes[intdiv($v, 2) % count($finishes)];
                    $unit = $units[$v % count($units)];
                    $brand = $brands[$brandKeys[$v % count($brandKeys)]];

                    $sku = sprintf('SL-CER-%04d', $seq);
                    $name = sprintf('%s — %s (Serie %d)', $m['name'], $finish, $v);

                    $basePrice = round(18.00 + ($v % 15) * 9.80, 2);
                    $vis = $visibilities[$seq % count($visibilities)];
                    $avail = $availabilities[$seq % count($availabilities)];

                    return [
                        'sku' => $sku,
                        'name' => $name,
                        'category_id' => $categories['cerraduras-cerrajeria-arquitectonica']->id,
                        'subcategory_id' => $categories[$m['sub']]->id,
                        'brand_id' => $brand->id,
                        'short_description' => sprintf('%s en acabado %s para carpintería arquitectónica y mobiliario corporativo.', $m['name'], strtolower($finish)),
                        'detailed_description' => sprintf('Sistema de cerrajería profesional con mecanismo interno reforzado anticorrosivo. Medida técnica %s y acabado %s.', $m['backset'], $finish),
                        'dimensions' => $m['backset'],
                        'material' => 'Aleación Zamak / '.$finish,
                        'sale_unit' => $unit,
                        'price' => $vis === 'hide' ? null : $basePrice,
                        'currency' => 'PEN',
                        'price_visibility' => $vis,
                        'availability_status' => $avail,
                        'is_featured' => ($seq % 55 === 0),
                        'svg_path' => $m['svg'],
                        'specs' => [
                            ['spec_group' => 'Dimensiones', 'spec_key' => 'Medida / Backset', 'spec_value' => $m['backset']],
                            ['spec_group' => 'Acabado', 'spec_key' => 'Color / Acabado', 'spec_value' => $finish],
                            ['spec_group' => 'Seguridad', 'spec_key' => 'Nivel de Tránsito', 'spec_value' => 'Intensivo Comercial B2B'],
                        ],
                    ];
                },
            ],

            // 4. Tornillería Estructural, Fijaciones y Anclajes (SL-TOR-...)
            [
                'builder' => function (int $v, int $seq) use ($categories, $brands, $visibilities, $availabilities): array {
                    $screwMeasures = [
                        ['d' => '3.5', 'l' => '16', 'app' => 'Fijación de bisagras cazoleta y correderas en tablero 18mm'],
                        ['d' => '3.5', 'l' => '30', 'app' => 'Unión de zócalos y frentes de cajón'],
                        ['d' => '4.0', 'l' => '40', 'app' => 'Armado de módulos en melamina de 15mm'],
                        ['d' => '4.0', 'l' => '50', 'app' => 'Estructurado principal de tableros melamina 18mm'],
                        ['d' => '4.5', 'l' => '60', 'app' => 'Carpintería pesada y bastidores de madera'],
                        ['d' => '5.0', 'l' => '70', 'app' => 'Instalación de muebles altos a muro con tarugo'],
                        ['d' => '6.0', 'l' => '80', 'app' => 'Fijación estructural pesada en concreto y madera'],
                        ['d' => '8.0', 'l' => '100', 'app' => 'Anclaje mecánico de expansión para estructuras metálicas'],
                    ];
                    $coatings = ['Zincado Amarillo Bicromatizado', 'Zincado Blanco Brillante', 'Fosfatado Negro Drywall', 'Acero Inoxidable A2-304'];
                    $presentations = [
                        ['unit' => 'Millar', 'mult' => 1.0],
                        ['unit' => 'Caja x 100', 'mult' => 0.14],
                        ['unit' => 'Kit', 'mult' => 0.25],
                    ];
                    $brandKeys = ['fischer-fijaciones', 'san-luis-linea-tecnica'];

                    $sm = $screwMeasures[$v % count($screwMeasures)];
                    $coating = $coatings[intdiv($v, 2) % count($coatings)];
                    $pres = $presentations[intdiv($v, 3) % count($presentations)];
                    $brand = $brands[$brandKeys[$v % count($brandKeys)]];

                    $subSlug = ((float) $sm['d'] >= 6.0) ? 'anclajes-expansion-concreto' : 'tornillos-melamina-madera';
                    $sku = sprintf('SL-TOR-%sX%s-%04d', str_replace('.', '', $sm['d']), $sm['l'], $seq);
                    $name = sprintf('Tornillo Técnico Autorroscante %s x %smm %s (%s)', $sm['d'], $sm['l'], $coating, $pres['unit']);

                    $basePrice = round((19.50 + (float) $sm['d'] * 4.2 + (float) $sm['l'] * 0.35) * $pres['mult'], 2);
                    $vis = $visibilities[$seq % count($visibilities)];
                    $avail = $availabilities[$seq % count($availabilities)];

                    return [
                        'sku' => $sku,
                        'name' => $name,
                        'category_id' => $categories['fijaciones-tornilleria-anclajes']->id,
                        'subcategory_id' => $categories[$subSlug]->id,
                        'brand_id' => $brand->id,
                        'short_description' => sprintf('Tornillo técnico %s x %smm en %s. %s.', $sm['d'], $sm['l'], strtolower($coating), $sm['app']),
                        'detailed_description' => sprintf('Fijación de acero cementado y templado con filete profundo de corte rápido. Medida %s x %s mm, recubrimiento %s, presentación comercial en %s.', $sm['d'], $sm['l'], $coating, $pres['unit']),
                        'dimensions' => sprintf('Ø %s mm x Largo %s mm', $sm['d'], $sm['l']),
                        'material' => 'Acero al carbono cementado — '.$coating,
                        'sale_unit' => $pres['unit'],
                        'price' => $vis === 'hide' ? null : $basePrice,
                        'currency' => 'PEN',
                        'price_visibility' => $vis,
                        'availability_status' => $avail,
                        'is_featured' => ($seq % 60 === 0),
                        'svg_path' => 'products/svg/tornilleria-estructural.svg',
                        'specs' => [
                            ['spec_group' => 'Dimensiones', 'spec_key' => 'Rosca / Medida', 'spec_value' => sprintf('%s x %s mm', $sm['d'], $sm['l'])],
                            ['spec_group' => 'Acabado', 'spec_key' => 'Recubrimiento', 'spec_value' => $coating],
                            ['spec_group' => 'Montaje', 'spec_key' => 'Cabeza / Impronta', 'spec_value' => 'Avellanada Pozi PZ2'],
                            ['spec_group' => 'Comercial', 'spec_key' => 'Presentación', 'spec_value' => $pres['unit']],
                        ],
                    ];
                },
            ],

            // 5. Herramientas, Abrasivos, Garruchas y Perfilería (SL-HER-...)
            [
                'builder' => function (int $v, int $seq) use ($categories, $brands, $visibilities, $availabilities): array {
                    $items = [
                        ['prefix' => 'Broca Forstner Carburo Tungsteno Ø35mm Tope Profundidad', 'sub' => 'herramientas-taller-montaje', 'dim' => 'Ø 35 mm | Vástago 10 mm', 'mat' => 'Carburo de Tungsteno TCT', 'unit' => 'Unidad', 'svg' => 'products/svg/herramienta-industrial.svg', 'brand' => 'stanley-industrial'],
                        ['prefix' => 'Prensa Esquinera Rápida 90° para Ensamble de Melamina 18mm', 'sub' => 'herramientas-taller-montaje', 'dim' => 'Apertura 10-25 mm | 90°', 'mat' => 'Aluminio Inyectado y Resorte Acero', 'unit' => 'Par', 'svg' => 'products/svg/herramienta-industrial.svg', 'brand' => 'stanley-industrial'],
                        ['prefix' => 'Disco de Corte Fino para Acero Inoxidable 4.1/2" x 1.0mm x 7/8"', 'sub' => 'discos-corte-abrasivos', 'dim' => '115 x 1.0 x 22.23 mm', 'mat' => 'Óxido de Aluminio Libre de Fe/S/Cl', 'unit' => 'Caja x 100', 'svg' => 'products/svg/disco-abrasivo.svg', 'brand' => 'norton-abrasivos'],
                        ['prefix' => 'Disco de Sierra Circular TCT para Melamina 250mm (10") 80 Dientes Trapezoidal', 'sub' => 'discos-corte-abrasivos', 'dim' => 'Ø 250 mm x Eje 30 mm | 80Z TCG', 'mat' => 'Acero Templado con Pastillas de Carburo Micrograno', 'unit' => 'Unidad', 'svg' => 'products/svg/disco-abrasivo.svg', 'brand' => 'norton-abrasivos'],
                        ['prefix' => 'Garrucha Industrial Giratoria con Freno Doble Poliuretano', 'sub' => 'garruchas-ruedas-industriales', 'dim' => 'Ø 100 mm (4") | Placa 95x65 mm', 'mat' => 'Núcleo Poliamida / Banda Poliuretano Rojo', 'unit' => 'Unidad', 'svg' => 'products/svg/sistema-corredizo.svg', 'brand' => 'san-luis-linea-tecnica'],
                        ['prefix' => 'Plantilla Guía de Perforación para Bisagra 35mm y Sistema 32', 'sub' => 'herramientas-taller-montaje', 'dim' => 'Paso 32 mm | Cazoleta 35 mm', 'mat' => 'Aluminio Anodizado CNC con Bujes de Acero', 'unit' => 'Kit', 'svg' => 'products/svg/herramienta-industrial.svg', 'brand' => 'hafele-pro'],
                    ];

                    $it = $items[$v % count($items)];
                    $brand = $brands[$it['brand']];
                    $sku = sprintf('SL-HER-%04d', $seq);
                    $name = sprintf('%s — Mod. %d', $it['prefix'], $v);

                    $basePrice = round(24.00 + ($v % 16) * 8.40, 2);
                    $vis = $visibilities[$seq % count($visibilities)];
                    $avail = $availabilities[$seq % count($availabilities)];

                    return [
                        'sku' => $sku,
                        'name' => $name,
                        'category_id' => $categories['ferreteria-industrial-suministros']->id,
                        'subcategory_id' => $categories[$it['sub']]->id,
                        'brand_id' => $brand->id,
                        'short_description' => sprintf('%s para talleres de fabricación, contratistas y montaje en obra.', $it['prefix']),
                        'detailed_description' => sprintf('Suministro técnico industrial diseñado para trabajo continuo en carpintería metálica, melamina y construcción. Especificación: %s.', $it['dim']),
                        'dimensions' => $it['dim'],
                        'material' => $it['mat'],
                        'sale_unit' => $it['unit'],
                        'price' => $vis === 'hide' ? null : $basePrice,
                        'currency' => 'PEN',
                        'price_visibility' => $vis,
                        'availability_status' => $avail,
                        'is_featured' => ($seq % 65 === 0),
                        'svg_path' => $it['svg'],
                        'specs' => [
                            ['spec_group' => 'Dimensiones', 'spec_key' => 'Medida Nominal', 'spec_value' => $it['dim']],
                            ['spec_group' => 'Material', 'spec_key' => 'Composición', 'spec_value' => $it['mat']],
                            ['spec_group' => 'Aplicación', 'spec_key' => 'Uso Recomendado', 'spec_value' => 'Taller Industrial y Obra'],
                        ],
                    ];
                },
            ],

            // 6. Equipos de Protección Personal (SL-EPP-...)
            [
                'builder' => function (int $v, int $seq) use ($categories, $brands, $visibilities, $availabilities): array {
                    $epps = [
                        ['name' => 'Guante Anticorte Nivel 5 Palma de Poliuretano Ensamble Fino', 'norm' => 'EN 388 (4X43D)', 'mat' => 'Fibra HPPE Anticorte + Poliuretano', 'unit' => 'Par'],
                        ['name' => 'Lente de Seguridad Policarbonato Antiempañante filtro UV 99.9%', 'norm' => 'ANSI Z87.1+', 'mat' => 'Policarbonato Óptico Clase 1', 'unit' => 'Unidad'],
                        ['name' => 'Casco de Seguridad Industrial Tipo I Clase E con Ratchet 4 Puntas', 'norm' => 'ANSI/ISEA Z89.1', 'mat' => 'Polietileno de Alta Densidad (HDPE)', 'unit' => 'Unidad'],
                        ['name' => 'Protector Auditivo Tipo Copa Fono para Taller de Corte SNR 28dB', 'norm' => 'EN 352-1 / ANSI S3.19', 'mat' => 'ABS de Alto Impacto y Espuma Acústica', 'unit' => 'Unidad'],
                        ['name' => 'Respirador Media Cara Doble Filtro para Polvo de MDF y Pintura', 'norm' => 'NIOSH 42 CFR 84', 'mat' => 'Elastómero Termoplástico Hipoalergénico', 'unit' => 'Kit'],
                    ];
                    $sizes = ['Talla M', 'Talla L', 'Talla XL', 'Ajuste Universal'];
                    $brandKeys = ['3m-seguridad-industrial', 'stanley-industrial', 'san-luis-linea-tecnica'];

                    $epp = $epps[$v % count($epps)];
                    $size = $sizes[intdiv($v, 2) % count($sizes)];
                    $brand = $brands[$brandKeys[$v % count($brandKeys)]];

                    $sku = sprintf('SL-EPP-%04d', $seq);
                    $name = sprintf('%s — %s (Lote %d)', $epp['name'], $size, $v);

                    $basePrice = round(12.50 + ($v % 12) * 5.20, 2);
                    $vis = $visibilities[$seq % count($visibilities)];
                    $avail = $availabilities[$seq % count($availabilities)];

                    return [
                        'sku' => $sku,
                        'name' => $name,
                        'category_id' => $categories['ferreteria-industrial-suministros']->id,
                        'subcategory_id' => $categories['equipos-proteccion-personal-epp']->id,
                        'brand_id' => $brand->id,
                        'short_description' => sprintf('%s (%s) conforme a norma %s.', $epp['name'], $size, $epp['norm']),
                        'detailed_description' => sprintf('Equipo de protección personal para operarios de corte, enchape, instalación en obra y almacén industrial. Cumple estándar técnico %s.', $epp['norm']),
                        'dimensions' => $size,
                        'material' => $epp['mat'],
                        'sale_unit' => $epp['unit'],
                        'price' => $vis === 'hide' ? null : $basePrice,
                        'currency' => 'PEN',
                        'price_visibility' => $vis,
                        'availability_status' => $avail,
                        'is_featured' => ($seq % 70 === 0),
                        'svg_path' => 'products/svg/epp-seguridad.svg',
                        'specs' => [
                            ['spec_group' => 'Certificación', 'spec_key' => 'Norma Técnica', 'spec_value' => $epp['norm']],
                            ['spec_group' => 'Material', 'spec_key' => 'Fabricación', 'spec_value' => $epp['mat']],
                            ['spec_group' => 'Dimensiones', 'spec_key' => 'Talla / Ajuste', 'spec_value' => $size],
                        ],
                    ];
                },
            ],
        ];
    }
}
