<?php

namespace App\Console\Commands;

use App\Services\ActivityLoggerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use ZipArchive;

class BackupPlatformCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanluis:backup {--keep=7 : Cantidad máxima de archivos de respaldo recientes a conservar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un respaldo comprimido ZIP de las tablas críticas y archivos esenciales de la plataforma Import Hns San Luis.';

    /**
     * Core tables included in the platform backup.
     *
     * @var array<int, string>
     */
    protected array $tables = [
        'roles',
        'users',
        'categories',
        'brands',
        'products',
        'product_specifications',
        'quotes',
        'quote_items',
        'customers',
        'site_settings',
        'page_contents',
    ];

    /**
     * Execute the console command.
     */
    public function handle(ActivityLoggerService $activityLogger): int
    {
        $keep = max(1, (int) $this->option('keep'));
        $backupDir = storage_path('app/backups');

        if (! File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_His');
        $filename = sprintf('sanluis-backup-%s.zip', $timestamp);
        $zipPath = $backupDir.DIRECTORY_SEPARATOR.$filename;

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error('No se pudo crear el archivo ZIP de respaldo en: '.$zipPath);

            return self::FAILURE;
        }

        $manifest = [
            'platform' => 'Import Hns San Luis B2B/B2C',
            'created_at' => now()->toIso8601String(),
            'environment' => app()->environment(),
            'database' => config('database.connections.mysql.database'),
            'tables' => [],
        ];

        $sqlSchemaDump = "-- Import Hns San Luis Schema & Metadata Dump\n-- Generated at: ".now()->toDateTimeString()."\n\n";

        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            try {
                $createTableResult = DB::select("SHOW CREATE TABLE `{$table}`");
                if (! empty($createTableResult)) {
                    $rowObj = (array) $createTableResult[0];
                    $createSql = $rowObj['Create Table'] ?? '';
                    if ($createSql !== '') {
                        $sqlSchemaDump .= "DROP TABLE IF EXISTS `{$table}`;\n{$createSql};\n\n";
                    }
                }
            } catch (\Throwable) {
                // Ignore if driver is not MySQL
            }

            $rows = [];
            DB::table($table)->orderBy('id')->chunk(500, function ($chunk) use (&$rows, $table) {
                foreach ($chunk as $record) {
                    $arr = (array) $record;
                    if ($table === 'users') {
                        unset($arr['remember_token']);
                    }
                    $rows[] = $arr;
                }
            });

            $manifest['tables'][$table] = count($rows);
            $zip->addFromString(
                "database/{$table}.json",
                json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]'
            );
        }

        $zip->addFromString('database/schema.sql', $sqlSchemaDump);
        $zip->addFromString(
            'manifest.json',
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}'
        );

        // Include essential public storage files (SVG assets, product images, sample CSVs)
        $publicStoragePath = storage_path('app/public');
        $filesAdded = 0;
        if (File::isDirectory($publicStoragePath)) {
            $allFiles = File::allFiles($publicStoragePath);
            foreach ($allFiles as $fileInfo) {
                if ($fileInfo->getSize() <= 15 * 1024 * 1024) {
                    $relativePath = str_replace('\\', '/', $fileInfo->getRelativePathname());
                    $zip->addFile($fileInfo->getRealPath(), 'storage/public/'.$relativePath);
                    $filesAdded++;
                }
            }
        }

        $zip->close();

        // Rotate old backups according to --keep
        $existingBackups = collect(File::glob($backupDir.DIRECTORY_SEPARATOR.'sanluis-backup-*.zip'))
            ->sortByDesc(fn ($path) => File::lastModified($path))
            ->values();

        $deletedBackups = 0;
        if ($existingBackups->count() > $keep) {
            foreach ($existingBackups->slice($keep) as $oldBackupPath) {
                if (@unlink($oldBackupPath)) {
                    $deletedBackups++;
                }
            }
        }

        $sizeKb = File::exists($zipPath) ? round(File::size($zipPath) / 1024, 2) : 0;

        $activityLogger->log(
            'system.backup_created',
            'settings',
            sprintf('Respaldo integral generado (%s, %.2f KB, %d tablas, %d archivos adjuntos).', $filename, $sizeKb, count($manifest['tables']), $filesAdded),
            null,
            [
                'filename' => $filename,
                'size_kb' => $sizeKb,
                'tables_backed_up' => $manifest['tables'],
                'files_added' => $filesAdded,
                'old_backups_pruned' => $deletedBackups,
            ]
        );

        $this->info(sprintf('Respaldo creado exitosamente: storage/app/backups/%s (%.2f KB)', $filename, $sizeKb));

        return self::SUCCESS;
    }
}
