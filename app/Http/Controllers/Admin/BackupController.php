<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLoggerService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;
use ZipArchive;

class BackupController extends Controller
{
    public function __construct(
        protected ActivityLoggerService $activityLogger
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('settings.manage'), 403);

        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $files = collect(File::files($backupDir))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), ['zip', 'sql', 'gz'], true))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(function ($file) {
                $bytes = $file->getSize();

                return [
                    'filename' => $file->getFilename(),
                    'size_bytes' => $bytes,
                    'size_formatted' => $this->formatBytes($bytes),
                    'created_at' => Carbon::createFromTimestamp($file->getMTime()),
                ];
            })
            ->values();

        return view('admin.backups.index', [
            'backups' => $files,
            'backupPath' => 'storage/app/backups/',
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('settings.manage'), 403);

        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $beforeFiles = collect(File::files($backupDir))
            ->map(fn ($f) => $f->getFilename())
            ->all();

        $createdFilename = null;

        try {
            $exitCode = Artisan::call('sanluis:backup');
            if ($exitCode === 0) {
                $afterFiles = collect(File::files($backupDir))
                    ->sortByDesc(fn ($f) => $f->getMTime())
                    ->map(fn ($f) => $f->getFilename())
                    ->all();

                $newFiles = array_values(array_diff($afterFiles, $beforeFiles));
                if (! empty($newFiles)) {
                    $createdFilename = $newFiles[0];
                } elseif (! empty($afterFiles)) {
                    $createdFilename = $afterFiles[0];
                }
            }
        } catch (Throwable) {
            // Fallback to direct ZIP creation below if command is not registered yet or fails
        }

        if (! $createdFilename) {
            $createdFilename = $this->createFallbackBackupZip($backupDir);
        }

        if (! $createdFilename) {
            return back()->with('error', 'No fue posible generar el archivo de respaldo ZIP. Verifique los permisos de escritura en storage/app/backups.');
        }

        $this->activityLogger->log(
            'backup.created',
            'settings',
            sprintf('Copia de seguridad "%s" generada bajo demanda.', $createdFilename),
            null,
            ['filename' => $createdFilename]
        );

        return back()->with('success', sprintf('Respaldo del sistema "%s" generado correctamente.', $createdFilename));
    }

    public function download(Request $request, string $filename): BinaryFileResponse|RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('settings.manage'), 403);

        $safeName = basename($filename);
        if (! preg_match('/^[a-zA-Z0-9_\-\.]+\.(zip|sql|gz)$/i', $safeName)) {
            abort(404, 'Nombre de archivo de respaldo no válido.');
        }

        $fullPath = storage_path('app/backups/'.$safeName);
        if (! File::exists($fullPath)) {
            return redirect()
                ->route('admin.backups.index')
                ->with('error', 'El archivo de respaldo solicitado no existe.');
        }

        $this->activityLogger->log(
            'backup.downloaded',
            'settings',
            sprintf('Copia de seguridad "%s" descargada por el administrador.', $safeName),
            null,
            ['filename' => $safeName]
        );

        return response()->download($fullPath, $safeName);
    }

    public function destroy(Request $request, string $filename): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('settings.manage'), 403);

        $safeName = basename($filename);
        if (! preg_match('/^[a-zA-Z0-9_\-\.]+\.(zip|sql|gz)$/i', $safeName)) {
            abort(404, 'Nombre de archivo de respaldo no válido.');
        }

        $fullPath = storage_path('app/backups/'.$safeName);
        if (! File::exists($fullPath)) {
            return back()->with('error', 'El archivo de respaldo no existe o ya fue eliminado.');
        }

        File::delete($fullPath);

        $this->activityLogger->log(
            'backup.deleted',
            'settings',
            sprintf('Copia de seguridad "%s" eliminada del servidor.', $safeName),
            null,
            ['filename' => $safeName]
        );

        return back()->with('success', sprintf('Archivo de respaldo "%s" eliminado correctamente.', $safeName));
    }

    protected function createFallbackBackupZip(string $backupDir): ?string
    {
        if (! class_exists(ZipArchive::class)) {
            return null;
        }

        $filename = sprintf('backup-sanluis-%s.zip', now()->format('Y-m-d-His'));
        $zipPath = $backupDir.DIRECTORY_SEPARATOR.$filename;

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return null;
        }

        $tablesToExport = [
            'roles',
            'permissions',
            'site_settings',
            'page_contents',
            'brands',
            'categories',
            'products',
            'product_specifications',
            'product_images',
            'product_documents',
            'customers',
            'quotes',
            'quote_items',
            'quote_status_histories',
            'testimonials',
            'contact_messages',
        ];

        $snapshot = [
            'generated_at' => now()->toIso8601String(),
            'application' => config('app.name', 'Import Hns San Luis'),
            'tables' => [],
        ];

        foreach ($tablesToExport as $table) {
            try {
                if (Schema::hasTable($table)) {
                    $snapshot['tables'][$table] = DB::table($table)->get()->toArray();
                }
            } catch (Throwable) {
                // Ignore table if unavailable
            }
        }

        $zip->addFromString(
            'database/sanluis_snapshot.json',
            json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}'
        );

        $publicStorageDir = storage_path('app/public/products');
        if (File::isDirectory($publicStorageDir)) {
            foreach (File::allFiles($publicStorageDir) as $mediaFile) {
                $relativePath = 'storage/products/'.str_replace('\\', '/', $mediaFile->getRelativePathname());
                $zip->addFile($mediaFile->getRealPath(), $relativePath);
            }
        }

        $zip->close();

        return File::exists($zipPath) ? $filename : null;
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }
}
