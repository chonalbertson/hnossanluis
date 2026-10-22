<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SyncHalconCatalogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanluis:sync-halcon
        {--source= : Ruta alternativa al JSON de importación}
        {--force-update : Si el producto ya existe, actualiza sus campos en lugar de saltarlo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa de forma incremental y 100% segura los productos del Catálogo Halcon 2026 sin borrar ni reemplazar productos existentes.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sourcePath = $this->option('source') ?: storage_path('app/halcon_catalog_batch.json');

        if (! File::exists($sourcePath)) {
            $this->error("No se encontró el archivo de datos en: {$sourcePath}");

            return self::FAILURE;
        }

        $items = json_decode(File::get($sourcePath), true);
        if (! is_array($items) || empty($items)) {
            $this->error('El archivo de datos está vacío o es inválido.');

            return self::FAILURE;
        }

        $this->info('Iniciando sincronización incremental segura de '.count($items).' productos Halcon...');
        $this->line('REGLA: No se borra ni reemplaza ningún producto existente en la base de datos.');

        $brand = Brand::firstOrCreate(['id' => 4], ['name' => 'Halcon', 'slug' => 'halcon']);
        $createdCount = 0;
        $skippedCount = 0;
        $updatedCount = 0;

        $bar = $this->output->createProgressBar(count($items));

        foreach ($items as $item) {
            $sku = $item['sku'];
            $existing = Product::where('sku', $sku)->first();

            if ($existing && ! $this->option('force-update')) {
                $skippedCount++;
                $bar->advance();

                continue;
            }

            DB::transaction(function () use ($item, $existing, &$createdCount, &$updatedCount) {
                $productData = [
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'sku' => $item['sku'],
                    'brand_id' => $item['brand_id'] ?: 4,
                    'category_id' => $item['category_id'] ?: 28,
                    'subcategory_id' => $item['subcategory_id'] ?: 8,
                    'dimensions' => $item['dimensions'] ?? null,
                    'material' => $item['material'] ?? null,
                    'sale_unit' => $item['sale_unit'] ?? 'PZ',
                    'price' => $item['price'] ?? 0.00,
                    'currency' => $item['currency'] ?? 'PEN',
                    'price_visibility' => $item['price_visibility'] ?? 'hide',
                    'availability_status' => $item['availability_status'] ?? 'available',
                    'status' => $item['status'] ?? 'published',
                    'short_description' => $item['short_description'] ?? null,
                    'detailed_description' => $item['detailed_description'] ?? null,
                    'meta_title' => $item['meta_title'] ?? null,
                    'meta_description' => $item['meta_description'] ?? null,
                ];

                if ($existing) {
                    $existing->update($productData);
                    $product = $existing;
                    $updatedCount++;
                } else {
                    $product = Product::create($productData);
                    $createdCount++;
                }

                // Especificaciones
                if (! empty($item['specifications'])) {
                    $product->specifications()->delete();
                    foreach ($item['specifications'] as $spec) {
                        $product->specifications()->create($spec);
                    }
                }

                // Variantes
                if (! empty($item['variants'])) {
                    $product->variants()->delete();
                    foreach ($item['variants'] as $variant) {
                        $product->variants()->create($variant);
                    }
                }

                // Imágenes
                if (! empty($item['images'])) {
                    $product->images()->delete();
                    foreach ($item['images'] as $img) {
                        $product->images()->create($img);
                    }
                }
            });

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Sincronización finalizada con éxito:');
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Total procesados', count($items)],
                ['Productos creados', $createdCount],
                ['Productos omitidos (ya existían)', $skippedCount],
                ['Productos actualizados', $updatedCount],
            ]
        );

        return self::SUCCESS;
    }
}
