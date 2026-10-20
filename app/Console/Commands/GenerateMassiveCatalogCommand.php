<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSpecification;
use App\Services\ActivityLoggerService;
use Database\Seeders\MassiveCatalogSeeder;
use Illuminate\Console\Command;

class GenerateMassiveCatalogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanluis:generate-catalog {--count=1000 : Cantidad mínima de productos publicados a asegurar en la base de datos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera ilustraciones técnicas SVG, archivo CSV de ejemplo y catálogo técnico B2B a escala real (+1,000 productos y +2,000 especificaciones).';

    /**
     * Execute the console command.
     */
    public function handle(MassiveCatalogSeeder $seeder, ActivityLoggerService $activityLogger): int
    {
        $targetCount = max(100, (int) $this->option('count'));
        $startTime = microtime(true);

        $this->info(sprintf('Iniciando generación de catálogo de escala real (meta: %d productos publicados)...', $targetCount));

        $seeder->setTargetCount($targetCount)->run();

        $publishedCount = Product::query()->where('status', 'published')->count();
        $specsCount = ProductSpecification::query()->count();
        $imagesCount = ProductImage::query()->count();
        $elapsed = round(microtime(true) - $startTime, 2);

        $activityLogger->log(
            'catalog.massive_seed',
            'catalog',
            sprintf('Catálogo masivo generado/verificado: %d productos publicados, %d especificaciones técnicas y %d imágenes en %.2fs.', $publishedCount, $specsCount, $imagesCount, $elapsed),
            null,
            [
                'target_count' => $targetCount,
                'published_products' => $publishedCount,
                'specifications_count' => $specsCount,
                'images_count' => $imagesCount,
                'elapsed_seconds' => $elapsed,
            ]
        );

        $this->info(sprintf('Completado en %.2f segundos.', $elapsed));
        $this->table(
            ['Métrica', 'Total en Base de Datos'],
            [
                ['Productos Publicados', number_format($publishedCount)],
                ['Especificaciones Técnicas Indexadas', number_format($specsCount)],
                ['Imágenes / Ilustraciones SVG Asignadas', number_format($imagesCount)],
                ['CSV de Ejemplo Generado', 'storage/app/public/samples/catalogo_importacion_ejemplo.csv'],
            ]
        );

        return self::SUCCESS;
    }
}
