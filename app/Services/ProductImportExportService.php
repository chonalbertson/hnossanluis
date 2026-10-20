<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImportBatch;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ProductImportExportService
{
    public const REQUIRED_HEADERS = [
        'sku',
        'name',
        'category',
    ];

    public const STANDARD_HEADERS = [
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
    ];

    public function __construct(
        protected ActivityLoggerService $activityLogger,
        protected MalwareScannerService $malwareScanner
    ) {}

    /**
     * Parse and validate a CSV file without mutating products in DB.
     * Creates a ProductImportBatch in 'preview' status.
     */
    public function analyzeCsv(UploadedFile $file, User $user): ProductImportBatch
    {
        $inspection = $this->malwareScanner->inspectUploadedFile($file, 'csv_file');
        if (! $inspection['safe']) {
            $this->malwareScanner->recordIncident([
                'threat_type' => $inspection['threat_type'] ?? 'csv_formula_injection',
                'severity' => $inspection['severity'] ?? 'high',
                'status' => 'quarantined',
                'user_id' => $user->id,
                'file_name' => $file->getClientOriginalName(),
                'file_hash' => $inspection['sha256'] ?? null,
                'file_size' => $file->getSize(),
                'matched_rule' => $inspection['rule'] ?? 'csv_malware_check',
                'details' => [
                    'reason' => $inspection['reason'],
                    'context' => 'ProductImportExportService::analyzeCsv',
                ],
            ], $file);

            throw new InvalidArgumentException('Archivo CSV bloqueado por el motor antimalware: '.$inspection['reason']);
        }

        $path = $file->getRealPath();
        if (! $path || ! is_readable($path)) {
            throw new InvalidArgumentException('No se pudo leer el archivo CSV cargado.');
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new InvalidArgumentException('No se pudo abrir el archivo CSV.');
        }

        // Strip UTF-8 BOM if present on first line
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            throw new InvalidArgumentException('El archivo CSV está vacío.');
        }

        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $rawHeaders = str_getcsv(trim($firstLine), $delimiter);
        $headers = array_map(fn ($h) => strtolower(trim((string) $h)), $rawHeaders);

        foreach (self::REQUIRED_HEADERS as $reqHeader) {
            if (! in_array($reqHeader, $headers, true)) {
                fclose($handle);
                throw new InvalidArgumentException(sprintf(
                    'El archivo CSV no contiene la columna obligatoria "%s". Cabeceras requeridas mínimas: %s.',
                    $reqHeader,
                    implode(', ', self::REQUIRED_HEADERS)
                ));
            }
        }

        $existingSkus = Product::withTrashed()
            ->pluck('id', 'sku')
            ->mapWithKeys(fn ($id, $sku) => [strtoupper(trim((string) $sku)) => $id])
            ->all();

        $seenSkusInFile = [];
        $previewRows = [];
        $errorRows = [];
        $totalRows = 0;
        $toCreateCount = 0;
        $toUpdateCount = 0;
        $rejectedCount = 0;
        $lineNumber = 1;

        while (($rowValues = fgetcsv($handle, 0, $delimiter)) !== false) {
            $lineNumber++;

            // Skip completely blank lines
            if ($rowValues === [null] || count(array_filter($rowValues, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $totalRows++;
            $assoc = [];
            foreach ($headers as $idx => $headerName) {
                if ($headerName !== '') {
                    $rawValue = isset($rowValues[$idx]) ? trim((string) $rowValues[$idx]) : '';
                    $assoc[$headerName] = $this->malwareScanner->sanitizeCsvCell($rawValue);
                }
            }

            $rowErrors = [];
            $sku = strtoupper(trim($assoc['sku'] ?? ''));
            $name = trim($assoc['name'] ?? '');
            $categoryName = trim($assoc['category'] ?? '');

            if ($sku === '') {
                $rowErrors[] = 'El campo SKU es obligatorio.';
            } elseif (strlen($sku) > 64) {
                $rowErrors[] = 'El SKU excede los 64 caracteres permitidos.';
            } elseif (isset($seenSkusInFile[$sku])) {
                $rowErrors[] = sprintf('SKU duplicado dentro del mismo archivo CSV (apareció primero en la fila %d).', $seenSkusInFile[$sku]);
            } else {
                $seenSkusInFile[$sku] = $lineNumber;
            }

            if ($name === '') {
                $rowErrors[] = 'El nombre del producto es obligatorio.';
            }

            if ($categoryName === '') {
                $rowErrors[] = 'La categoría principal es obligatoria.';
            }

            $rawPrice = trim($assoc['price'] ?? '');
            $normalizedPrice = null;
            if ($rawPrice !== '') {
                $cleanPrice = str_replace(',', '.', $rawPrice);
                if (! is_numeric($cleanPrice) || (float) $cleanPrice < 0) {
                    $rowErrors[] = sprintf('Precio inválido "%s": debe ser un número mayor o igual a 0 o vacío.', $rawPrice);
                } else {
                    $normalizedPrice = (float) $cleanPrice > 0 ? round((float) $cleanPrice, 2) : null;
                }
            }

            $currency = strtoupper(trim($assoc['currency'] ?? '') ?: 'PEN');
            if (! in_array($currency, ['PEN', 'USD', 'EUR'], true)) {
                $rowErrors[] = sprintf('Moneda inválida "%s": valores permitidos PEN, USD, EUR.', $currency);
            }

            $priceVisibility = strtolower(trim($assoc['price_visibility'] ?? '') ?: 'inherit');
            if (! in_array($priceVisibility, ['inherit', 'show', 'hide'], true)) {
                $rowErrors[] = sprintf('Visibilidad de precio inválida "%s": valores permitidos inherit, show, hide.', $priceVisibility);
            }

            $availabilityStatus = strtolower(trim($assoc['availability_status'] ?? '') ?: 'available');
            if (! in_array($availabilityStatus, ['available', 'on_request', 'out_of_stock', 'unknown'], true)) {
                $rowErrors[] = sprintf('Estado de disponibilidad inválido "%s": valores permitidos available, on_request, out_of_stock, unknown.', $availabilityStatus);
            }

            $status = strtolower(trim($assoc['status'] ?? '') ?: 'published');
            if (! in_array($status, ['published', 'draft', 'archived'], true)) {
                $rowErrors[] = sprintf('Estado de publicación inválido "%s": valores permitidos published, draft, archived.', $status);
            }

            if (! empty($rowErrors)) {
                $rejectedCount++;
                $errorRows[] = [
                    'row_number' => $lineNumber,
                    'sku' => $sku,
                    'name' => $name,
                    'errors' => $rowErrors,
                    'raw' => $assoc,
                ];

                continue;
            }

            $action = isset($existingSkus[$sku]) ? 'update' : 'create';
            if ($action === 'update') {
                $toUpdateCount++;
            } else {
                $toCreateCount++;
            }

            $previewRows[] = [
                'row_number' => $lineNumber,
                'action' => $action,
                'sku' => $sku,
                'name' => $name,
                'category' => $categoryName,
                'subcategory' => trim($assoc['subcategory'] ?? ''),
                'brand' => trim($assoc['brand'] ?? ''),
                'short_description' => trim($assoc['short_description'] ?? ''),
                'detailed_description' => trim($assoc['detailed_description'] ?? ''),
                'dimensions' => trim($assoc['dimensions'] ?? ''),
                'material' => trim($assoc['material'] ?? ''),
                'sale_unit' => trim($assoc['sale_unit'] ?? '') ?: 'Unidad',
                'price' => $normalizedPrice,
                'currency' => $currency,
                'price_visibility' => $priceVisibility,
                'availability_status' => $availabilityStatus,
                'status' => $status,
                'specs' => trim($assoc['specs'] ?? ''),
            ];
        }

        fclose($handle);

        $batch = ProductImportBatch::query()->create([
            'user_id' => $user->id,
            'filename' => $file->getClientOriginalName() ?: basename($path),
            'status' => 'preview',
            'total_rows' => $totalRows,
            'to_create_count' => $toCreateCount,
            'to_update_count' => $toUpdateCount,
            'created_count' => 0,
            'updated_count' => 0,
            'rejected_count' => $rejectedCount,
            'preview_rows' => $previewRows,
            'error_rows' => $errorRows,
        ]);

        $this->activityLogger->log(
            'import.preview',
            'imports',
            sprintf('Vista previa de importación CSV "%s": %d filas (%d crear, %d actualizar, %d con error).', $batch->filename, $totalRows, $toCreateCount, $toUpdateCount, $rejectedCount),
            $batch,
            [
                'total_rows' => $totalRows,
                'to_create' => $toCreateCount,
                'to_update' => $toUpdateCount,
                'rejected' => $rejectedCount,
            ],
            $user->id
        );

        return $batch;
    }

    /**
     * Execute a validated preview batch inside a DB transaction.
     */
    public function executeBatch(ProductImportBatch $batch, User $user): ProductImportBatch
    {
        if ($batch->status === 'completed') {
            return $batch;
        }

        $rows = $batch->preview_rows ?? [];
        $createdCount = 0;
        $updatedCount = 0;

        DB::transaction(function () use ($rows, $batch, &$createdCount, &$updatedCount) {
            foreach (array_chunk($rows, 100) as $chunk) {
                foreach ($chunk as $rowData) {
                    $sku = strtoupper(trim((string) $rowData['sku']));
                    $name = trim((string) $rowData['name']);

                    // Resolve or create Main Category
                    $categoryName = trim((string) $rowData['category']);
                    $categorySlug = Str::slug($categoryName);
                    $category = Category::query()->where('slug', $categorySlug)->first()
                        ?? Category::query()->create([
                            'name' => $categoryName,
                            'slug' => $categorySlug,
                            'is_active' => true,
                        ]);

                    // Resolve or create Subcategory if provided
                    $subcategoryId = null;
                    $subcategoryName = trim((string) ($rowData['subcategory'] ?? ''));
                    if ($subcategoryName !== '') {
                        $subSlug = Str::slug($category->slug.'-'.$subcategoryName);
                        $subcategory = Category::query()
                            ->where('slug', $subSlug)
                            ->orWhere(function ($q) use ($subcategoryName, $category) {
                                $q->where('parent_id', $category->id)->where('name', $subcategoryName);
                            })
                            ->first()
                            ?? Category::query()->create([
                                'parent_id' => $category->id,
                                'name' => $subcategoryName,
                                'slug' => $subSlug,
                                'is_active' => true,
                            ]);
                        $subcategoryId = $subcategory->id;
                    }

                    // Resolve or create Brand if provided
                    $brandId = null;
                    $brandName = trim((string) ($rowData['brand'] ?? ''));
                    if ($brandName !== '') {
                        $brandSlug = Str::slug($brandName);
                        $brand = Brand::query()->where('slug', $brandSlug)->first()
                            ?? Brand::query()->create([
                                'name' => $brandName,
                                'slug' => $brandSlug,
                                'is_active' => true,
                            ]);
                        $brandId = $brand->id;
                    }

                    $existingProduct = Product::withTrashed()->where('sku', $sku)->first();

                    $attributes = [
                        'name' => $name,
                        'short_description' => $rowData['short_description'] ?: null,
                        'detailed_description' => $rowData['detailed_description'] ?: null,
                        'brand_id' => $brandId,
                        'category_id' => $category->id,
                        'subcategory_id' => $subcategoryId,
                        'dimensions' => $rowData['dimensions'] ?: null,
                        'material' => $rowData['material'] ?: null,
                        'sale_unit' => $rowData['sale_unit'] ?: 'Unidad',
                        'price' => $rowData['price'] !== null && (float) $rowData['price'] > 0 ? $rowData['price'] : null,
                        'currency' => $rowData['currency'] ?: 'PEN',
                        'price_visibility' => $rowData['price_visibility'] ?: 'inherit',
                        'availability_status' => $rowData['availability_status'] ?: 'available',
                        'status' => $rowData['status'] ?: 'published',
                    ];

                    if ($existingProduct) {
                        if ($existingProduct->trashed()) {
                            $existingProduct->restore();
                        }
                        $existingProduct->update($attributes);
                        $product = $existingProduct;
                        $updatedCount++;
                    } else {
                        $baseSlug = Str::slug($name.'-'.$sku);
                        $slug = $baseSlug;
                        $counter = 1;
                        while (Product::withTrashed()->where('slug', $slug)->exists()) {
                            $slug = $baseSlug.'-'.$counter++;
                        }

                        $product = Product::query()->create(array_merge($attributes, [
                            'sku' => $sku,
                            'slug' => $slug,
                        ]));
                        $createdCount++;
                    }

                    // Sync dynamic technical specifications if 'specs' column is provided ('Key:Value|Key2:Value2')
                    $specsString = trim((string) ($rowData['specs'] ?? ''));
                    if ($specsString !== '') {
                        $product->specifications()->delete();
                        $pairs = explode('|', $specsString);
                        $sortOrder = 1;
                        foreach ($pairs as $pair) {
                            if (! str_contains($pair, ':')) {
                                continue;
                            }
                            [$specKey, $specVal] = array_map('trim', explode(':', $pair, 2));
                            if ($specKey !== '' && $specVal !== '') {
                                $product->specifications()->create([
                                    'spec_group' => 'Especificaciones Técnicas',
                                    'spec_key' => $specKey,
                                    'spec_value' => $specVal,
                                    'sort_order' => $sortOrder++,
                                ]);
                            }
                        }
                    }
                }
            }

            $batch->update([
                'status' => 'completed',
                'created_count' => $createdCount,
                'updated_count' => $updatedCount,
                'executed_at' => now(),
            ]);
        });

        $this->activityLogger->log(
            'import.executed',
            'imports',
            sprintf('Importación masiva ejecutada (%s): %d creados, %d actualizados, %d rechazados.', $batch->filename, $createdCount, $updatedCount, $batch->rejected_count),
            $batch,
            [
                'created_count' => $createdCount,
                'updated_count' => $updatedCount,
                'rejected_count' => $batch->rejected_count,
            ],
            $user->id
        );

        return $batch->fresh();
    }

    /**
     * Export catalog products to a UTF-8 CSV string.
     */
    public function exportCsv(array $filters = []): string
    {
        $query = Product::query()
            ->with(['category', 'subcategory', 'brand', 'specifications'])
            ->orderBy('sku');

        if (! empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        if (! empty($filters['brand_id'])) {
            $query->where('brand_id', (int) $filters['brand_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', (string) $filters['status']);
        }

        $stream = fopen('php://temp', 'r+');
        // Write UTF-8 BOM for Excel compatibility
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, self::STANDARD_HEADERS);

        $query->chunk(200, function ($products) use ($stream) {
            foreach ($products as $product) {
                $specsFormatted = $product->specifications
                    ->map(fn ($s) => $s->spec_key.':'.$s->spec_value)
                    ->implode('|');

                $row = [
                    (string) $product->sku,
                    (string) $product->name,
                    (string) ($product->category?->name ?? ''),
                    (string) ($product->subcategory?->name ?? ''),
                    (string) ($product->brand?->name ?? ''),
                    (string) ($product->short_description ?? ''),
                    (string) ($product->detailed_description ?? ''),
                    (string) ($product->dimensions ?? ''),
                    (string) ($product->material ?? ''),
                    (string) $product->sale_unit,
                    $product->price !== null ? number_format((float) $product->price, 2, '.', '') : '',
                    (string) $product->currency,
                    (string) $product->price_visibility,
                    (string) $product->availability_status,
                    (string) $product->status,
                    (string) $specsFormatted,
                ];

                fputcsv($stream, array_map(fn ($val) => $this->malwareScanner->sanitizeCsvCell((string) $val), $row));
            }
        });

        rewind($stream);
        $csvContent = stream_get_contents($stream) ?: '';
        fclose($stream);

        return $csvContent;
    }

    /**
     * Export rejected rows with exact error reasons from an import batch.
     */
    public function exportErrorsCsv(ProductImportBatch $batch): string
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['fila_csv', 'sku', 'nombre', 'motivo_error']);

        foreach ($batch->error_rows ?? [] as $errorRow) {
            $errorsText = is_array($errorRow['errors'] ?? null)
                ? implode(' | ', $errorRow['errors'])
                : (string) ($errorRow['errors'] ?? 'Error de validación');

            $row = [
                (string) ($errorRow['row_number'] ?? ''),
                (string) ($errorRow['sku'] ?? ''),
                (string) ($errorRow['name'] ?? ''),
                $errorsText,
            ];

            fputcsv($stream, array_map(fn ($val) => $this->malwareScanner->sanitizeCsvCell((string) $val), $row));
        }

        rewind($stream);
        $csvContent = stream_get_contents($stream) ?: '';
        fclose($stream);

        return $csvContent;
    }

    /**
     * Generate a downloadable CSV template with example rows.
     */
    public function downloadTemplateCsv(): string
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, self::STANDARD_HEADERS);

        fputcsv($stream, [
            'SL-BIS-35SC-IN',
            'Bisagra Cazoleta 35mm Cierre Suave Acero Inoxidable',
            'Herrajes y Accesorios para Muebles',
            'Bisagras Cazoleta y Ocultas',
            'Ducasse Industrial',
            'Bisagra cazoleta 35mm clip-on con pistón hidráulico amortiguado.',
            'Bisagra de alta resistencia para puertas de muebles de cocina y mobiliario comercial. Incluye base clip de 4 agujeros y tornillos.',
            '35mm / Apertura 110°',
            'Acero Inoxidable SUS304',
            'Par',
            '14.50',
            'PEN',
            'inherit',
            'available',
            'published',
            'Diámetro Cazoleta:35 mm|Ángulo de Apertura:110°|Cierre:Suave Hidráulico|Ciclos de Apertura:80,000 ciclos',
        ]);

        fputcsv($stream, [
            'SL-COR-TEL-500HD',
            'Corredera Telescópica Pesada 500mm Carga 45kg',
            'Herrajes y Accesorios para Muebles',
            'Correderas Telescópicas y Ocultas',
            'Hafele Pro',
            'Corredera telescópica de extracción total 45mm x 500mm.',
            'Corredera de billas de acero zincado negro/plata para cajones de alto tránsito e industriales.',
            '500mm x 45mm x 1.2mm',
            'Acero laminado en frío zincado',
            'Juego',
            '',
            'PEN',
            'hide',
            'on_request',
            'published',
            'Largo:500 mm|Capacidad de Carga:45 kg|Extracción:Total 100%|Espesor Acero:1.2 x 1.2 x 1.5 mm',
        ]);

        rewind($stream);
        $csvContent = stream_get_contents($stream) ?: '';
        fclose($stream);

        return $csvContent;
    }
}
