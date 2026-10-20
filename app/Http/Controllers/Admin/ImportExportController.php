<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductImportBatch;
use App\Services\ActivityLoggerService;
use App\Services\ProductImportExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class ImportExportController extends Controller
{
    public function __construct(
        protected ProductImportExportService $importExportService,
        protected ActivityLoggerService $activityLogger
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('imports.manage'), 403);

        $batches = ProductImportBatch::query()
            ->select([
                'id',
                'user_id',
                'filename',
                'status',
                'total_rows',
                'to_create_count',
                'to_update_count',
                'created_count',
                'updated_count',
                'rejected_count',
                'executed_at',
                'created_at',
                'updated_at',
            ])
            ->with('user')
            ->latest()
            ->paginate(12);

        $categories = Category::query()->whereNull('parent_id')->orderBy('name')->get();
        $brands = Brand::query()->orderBy('name')->get();

        return view('admin.imports.index', [
            'batches' => $batches,
            'categories' => $categories,
            'brands' => $brands,
            'standardHeaders' => ProductImportExportService::STANDARD_HEADERS,
            'requiredHeaders' => ProductImportExportService::REQUIRED_HEADERS,
        ]);
    }

    public function analyze(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('imports.manage'), 403);

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        try {
            $batch = $this->importExportService->analyzeCsv(
                $request->file('csv_file'),
                $request->user()
            );

            return redirect()
                ->route('admin.imports.preview', $batch)
                ->with('success', sprintf(
                    'Archivo "%s" analizado: %d filas procesadas (%d para crear, %d para actualizar, %d con observaciones).',
                    $batch->filename,
                    $batch->total_rows,
                    $batch->to_create_count,
                    $batch->to_update_count,
                    $batch->rejected_count
                ));
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function preview(Request $request, ProductImportBatch $batch): View
    {
        abort_unless($request->user()?->hasPermission('imports.manage'), 403);

        $batch->load('user');

        return view('admin.imports.preview', [
            'batch' => $batch,
        ]);
    }

    public function execute(Request $request, ProductImportBatch $batch): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('imports.manage'), 403);

        if ($batch->status === 'completed') {
            return redirect()
                ->route('admin.imports.preview', $batch)
                ->with('warning', 'Este lote de importación ya fue ejecutado anteriormente.');
        }

        $batch = $this->importExportService->executeBatch($batch, $request->user());

        return redirect()
            ->route('admin.imports.preview', $batch)
            ->with('success', sprintf(
                'Importación confirmada y ejecutada con éxito: %d productos creados, %d productos actualizados por SKU y %d filas rechazadas.',
                $batch->created_count,
                $batch->updated_count,
                $batch->rejected_count
            ));
    }

    public function downloadTemplate(Request $request): Response
    {
        abort_unless($request->user()?->hasPermission('imports.manage'), 403);

        $csv = $this->importExportService->downloadTemplateCsv();

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla-importacion-sanluis.csv"',
        ]);
    }

    public function export(Request $request): Response
    {
        abort_unless($request->user()?->hasPermission('imports.manage'), 403);

        $filters = $request->only(['category_id', 'brand_id', 'status']);
        $csv = $this->importExportService->exportCsv($filters);

        $this->activityLogger->log(
            'catalog.exported',
            'imports',
            'Exportación del catálogo de productos en formato CSV.',
            null,
            $filters
        );

        $filename = sprintf('catalogo-sanluis-%s.csv', now()->format('Ymd-His'));

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ]);
    }

    public function downloadErrors(Request $request, ProductImportBatch $batch): Response
    {
        abort_unless($request->user()?->hasPermission('imports.manage'), 403);

        $csv = $this->importExportService->exportErrorsCsv($batch);
        $filename = sprintf('errores-importacion-lote-%d.csv', $batch->id);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ]);
    }
}
