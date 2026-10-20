<?php

namespace App\Console\Commands;

use App\Services\ItalocksCatalogImportService;
use App\Services\ItalocksScraperService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportItalocksCatalogCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'sanluis:import-italocks
        {--refresh : Vuelve a scrapear italocks.com aunque exista el JSON en caché}
        {--scrape-only : Solo extrae y guarda el JSON, sin importar}
        {--limit= : Limita la cantidad de productos (pruebas)}
        {--skip-images : No descarga imágenes}';

    /**
     * @var string
     */
    protected $description = 'Scrapea el catálogo de italocks.com y lo integra (categorías, subcategorías, marcas, productos con SEO y máx. 2 imágenes HD) sin borrar el catálogo existente.';

    public function handle(ItalocksScraperService $scraper, ItalocksCatalogImportService $importer): int
    {
        $cachePath = storage_path('app/italocks/catalog.json');
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        if ($this->option('refresh') || ! is_file($cachePath)) {
            $this->info('1. Extrayendo catálogo de italocks.com...');
            $products = $scraper->scrapeCatalog(fn (string $message) => $this->line("   · {$message}"), $limit);

            File::ensureDirectoryExists(dirname($cachePath));
            File::put($cachePath, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->info('   '.count($products)." productos guardados en {$cachePath}");
        } else {
            $products = json_decode(File::get($cachePath), true) ?: [];
            $this->info('1. Usando catálogo en caché ('.count($products).' productos). Usa --refresh para volver a extraer.');
        }

        if ($this->option('scrape-only')) {
            return self::SUCCESS;
        }

        if ($limit !== null) {
            $products = array_slice($products, 0, $limit);
        }

        $this->info('2. Integrando productos en el catálogo...');
        $bar = $this->output->createProgressBar(count($products));

        $summary = $importer->import(
            $products,
            downloadImages: ! $this->option('skip-images'),
            onProductImported: fn () => $bar->advance(),
        );

        $bar->finish();
        $this->newLine(2);

        $this->table(['Resultado', 'Total'], [
            ['Productos creados', $summary['created']],
            ['Productos actualizados', $summary['updated']],
            ['Categorías nuevas', $summary['categories_created']],
            ['Subcategorías nuevas', $summary['subcategories_created']],
            ['Marcas nuevas', $summary['brands_created']],
            ['Imágenes descargadas', $summary['images_downloaded']],
            ['Imágenes reutilizadas (variantes sin foto en origen)', $summary['images_reused']],
        ]);

        return self::SUCCESS;
    }
}
