<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductDocument;
use App\Models\ProductImage;
use App\Services\ActivityLoggerService;
use App\Services\ImageThumbnailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        protected ActivityLoggerService $activityLogger
    ) {}

    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->hasPermission('catalog.view') || $request->user()?->hasPermission('catalog.manage'),
            403
        );

        $query = Product::query()
            ->with(['category', 'subcategory', 'brand', 'images'])
            ->withCount(['specifications', 'quoteItems', 'variants']);

        if ($request->filled('trashed') && $request->string('trashed')->toString() === 'only') {
            $query->onlyTrashed();
        } elseif ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('q')) {
            $term = '%'.trim($request->string('q')->toString()).'%';
            $query->where(function ($q) use ($term) {
                $q->where('sku', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('material', 'like', $term)
                    ->orWhere('dimensions', 'like', $term)
                    ->orWhereHas('variants', function ($vq) use ($term) {
                        $vq->where('sku', 'like', $term)
                            ->orWhere('variant_label', 'like', $term);
                    });
            });
        }

        if ($request->filled('category_id')) {
            $catId = (int) $request->input('category_id');
            $query->where(function ($q) use ($catId) {
                $q->where('category_id', $catId)
                    ->orWhere('subcategory_id', $catId);
            });
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', (int) $request->input('brand_id'));
        }

        if ($request->filled('availability_status')) {
            $query->where('availability_status', $request->string('availability_status')->toString());
        }

        if ($request->filled('price_visibility')) {
            $vis = $request->string('price_visibility')->toString();
            if ($vis === 'show') {
                $query->where('price_visibility', 'show');
            } elseif ($vis === 'hide') {
                $query->whereIn('price_visibility', ['hide', 'inherit']);
            }
        }

        $products = $query->orderByDesc('updated_at')->paginate(15)->withQueryString();

        $categories = Category::query()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        $brands = Brand::query()->orderBy('name')->get();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
        ]);
    }

    public function show(Request $request, Product $product): View
    {
        abort_unless(
            $request->user()?->hasPermission('catalog.view') || $request->user()?->hasPermission('catalog.manage'),
            403
        );

        $product->load(['category', 'subcategory', 'brand', 'images', 'specifications', 'documents', 'variants']);

        return view('admin.products.show', [
            'product' => $product,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('catalog.manage'), 403);

        $categories = Category::query()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        $brands = Brand::query()->orderBy('name')->get();

        return view('admin.products.create', [
            'product' => new Product([
                'currency' => 'PEN',
                'sale_unit' => 'Unidad',
                'price_visibility' => 'hide',
                'availability_status' => 'available',
                'status' => 'published',
            ]),
            'categories' => $categories,
            'brands' => $brands,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('catalog.manage'), 403);

        $validated = $this->validateProduct($request);

        $product = DB::transaction(function () use ($request, $validated) {
            $sku = strtoupper(trim($validated['sku']));
            $slug = ! empty($validated['slug'])
                ? Str::slug($validated['slug'])
                : $this->generateUniqueSlug($validated['name'], $sku);

            $rawPrice = $validated['price'] ?? null;
            $normalizedPrice = ($rawPrice !== null && $rawPrice !== '' && (float) $rawPrice > 0)
                ? round((float) $rawPrice, 2)
                : null;

            $product = Product::query()->create([
                'sku' => $sku,
                'name' => trim($validated['name']),
                'slug' => $slug,
                'short_description' => $validated['short_description'] ?? null,
                'detailed_description' => $validated['detailed_description'] ?? null,
                'brand_id' => $validated['brand_id'] ?? null,
                'category_id' => $validated['category_id'],
                'subcategory_id' => $validated['subcategory_id'] ?? null,
                'dimensions' => $validated['dimensions'] ?? null,
                'material' => $validated['material'] ?? null,
                'sale_unit' => trim($validated['sale_unit'] ?: 'Unidad'),
                'price' => $normalizedPrice,
                'currency' => $validated['currency'],
                'price_visibility' => $validated['price_visibility'],
                'availability_status' => $validated['availability_status'],
                'status' => $validated['status'],
                'is_featured' => $request->boolean('is_featured'),
                'meta_title' => $validated['meta_title'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
            ]);

            $this->syncSpecifications($product, $request->input('specs', []));
            $this->syncVariants($product, $request->input('variants', []), $request->string('product_type', 'auto')->toString());
            $this->handleUploads($request, $product);

            $this->activityLogger->log(
                'product.created',
                'catalog',
                sprintf('Producto "%s" (SKU: %s) creado en el catálogo.', $product->name, $product->sku),
                $product,
                [
                    'sku' => $product->sku,
                    'status' => $product->status,
                    'price' => $product->price,
                    'price_visibility' => $product->price_visibility,
                    'variants_count' => $product->variants()->count(),
                ]
            );

            return $product;
        });

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', sprintf('Producto "%s" (SKU: %s) registrado correctamente.', $product->name, $product->sku));
    }

    public function edit(Request $request, Product $product): View
    {
        abort_unless($request->user()?->hasPermission('catalog.manage'), 403);

        $product->load(['images', 'specifications', 'documents', 'variants']);

        $categories = Category::query()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        $brands = Brand::query()->orderBy('name')->get();

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => $categories,
            'brands' => $brands,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('catalog.manage'), 403);

        $validated = $this->validateProduct($request, $product);

        DB::transaction(function () use ($request, $validated, $product) {
            $sku = strtoupper(trim($validated['sku']));
            $slug = ! empty($validated['slug'])
                ? Str::slug($validated['slug'])
                : $this->generateUniqueSlug($validated['name'], $sku, $product->id);

            $rawPrice = $validated['price'] ?? null;
            $normalizedPrice = ($rawPrice !== null && $rawPrice !== '' && (float) $rawPrice > 0)
                ? round((float) $rawPrice, 2)
                : null;

            $product->update([
                'sku' => $sku,
                'name' => trim($validated['name']),
                'slug' => $slug,
                'short_description' => $validated['short_description'] ?? null,
                'detailed_description' => $validated['detailed_description'] ?? null,
                'brand_id' => $validated['brand_id'] ?? null,
                'category_id' => $validated['category_id'],
                'subcategory_id' => $validated['subcategory_id'] ?? null,
                'dimensions' => $validated['dimensions'] ?? null,
                'material' => $validated['material'] ?? null,
                'sale_unit' => trim($validated['sale_unit'] ?: 'Unidad'),
                'price' => $normalizedPrice,
                'currency' => $validated['currency'],
                'price_visibility' => $validated['price_visibility'],
                'availability_status' => $validated['availability_status'],
                'status' => $validated['status'],
                'is_featured' => $request->boolean('is_featured'),
                'meta_title' => $validated['meta_title'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
            ]);

            $this->syncSpecifications($product, $request->input('specs', []));
            $this->syncVariants($product, $request->input('variants', []), $request->string('product_type', 'auto')->toString());
            $this->handleUploads($request, $product);

            $this->activityLogger->log(
                'product.updated',
                'catalog',
                sprintf('Producto "%s" (SKU: %s) actualizado.', $product->name, $product->sku),
                $product,
                [
                    'sku' => $product->sku,
                    'status' => $product->status,
                    'price' => $product->price,
                    'price_visibility' => $product->price_visibility,
                    'availability_status' => $product->availability_status,
                    'variants_count' => $product->variants()->count(),
                ]
            );
        });

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', sprintf('Producto "%s" actualizado correctamente.', $product->name));
    }

    public function togglePriceVisibility(Request $request, Product $product): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('catalog.manage'), 403);

        $newVisibility = $product->price_visibility === 'show' ? 'hide' : 'show';
        $product->update([
            'price_visibility' => $newVisibility,
        ]);

        $this->activityLogger->log(
            'product.price_visibility_toggled',
            'catalog',
            sprintf(
                'Visibilidad de precio público para "%s" (SKU: %s) cambiada a "%s".',
                $product->name,
                $product->sku,
                $newVisibility === 'show' ? 'Mostrar precio' : 'Ocultar precio (Solo cotización)'
            ),
            $product,
            [
                'sku' => $product->sku,
                'price_visibility' => $newVisibility,
            ]
        );

        $msg = $newVisibility === 'show'
            ? sprintf('Precio público HABILITADO para "%s" (SKU: %s).', $product->name, $product->sku)
            : sprintf('Precio público OCULTO (Solo bajo cotización) para "%s" (SKU: %s).', $product->name, $product->sku);

        return back()->with('success', $msg);
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('catalog.manage'), 403);

        $sku = $product->sku;
        $name = $product->name;

        $product->update(['status' => 'archived']);
        $product->delete();

        $this->activityLogger->log(
            'product.archived',
            'catalog',
            sprintf('Producto "%s" (SKU: %s) archivado / enviado a papelera.', $name, $sku),
            $product
        );

        return redirect()
            ->route('admin.products.index')
            ->with('success', sprintf('Producto "%s" (SKU: %s) archivado correctamente.', $name, $sku));
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('catalog.manage'), 403);

        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();
        $product->update(['status' => 'published']);

        $this->activityLogger->log(
            'product.restored',
            'catalog',
            sprintf('Producto "%s" (SKU: %s) restaurado y publicado.', $product->name, $product->sku),
            $product
        );

        return redirect()
            ->route('admin.products.index')
            ->with('success', sprintf('Producto "%s" restaurado correctamente.', $product->name));
    }

    public function forceDestroy(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('catalog.manage'), 403);

        $product = Product::withTrashed()
            ->with(['images', 'documents'])
            ->findOrFail($id);

        $sku = $product->sku;
        $name = $product->name;

        DB::transaction(function () use ($product, $sku, $name): void {
            $summary = $product->purgeAllRelatedDataAndMedia();
            $product->forceDelete();

            $this->activityLogger->log(
                'product.deleted',
                'catalog',
                sprintf(
                    'Producto "%s" (SKU: %s) eliminado definitivamente junto con sus imágenes (%d), fichas PDF (%d) y especificaciones (%d).',
                    $name,
                    $sku,
                    $summary['images_deleted'],
                    $summary['documents_deleted'],
                    $summary['specifications_deleted']
                ),
                null,
                array_merge([
                    'sku' => $sku,
                    'name' => $name,
                ], $summary)
            );
        });

        return redirect()
            ->route('admin.products.index')
            ->with('success', sprintf('Producto "%s" (SKU: %s) y todas sus imágenes y registros asociados fueron eliminados definitivamente.', $name, $sku));
    }

    public function destroyImage(Request $request, Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('catalog.manage'), 403);
        abort_unless($image->product_id === $product->id, 404);

        if ($image->file_path && Storage::disk('public')->exists($image->file_path)) {
            Storage::disk('public')->delete($image->file_path);
        }
        if ($image->thumbnail_path && Storage::disk('public')->exists($image->thumbnail_path)) {
            Storage::disk('public')->delete($image->thumbnail_path);
        }

        $image->delete();

        return back()->with('success', 'Imagen eliminada del producto.');
    }

    public function setPrimaryImage(Request $request, Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('catalog.manage'), 403);
        abort_unless($image->product_id === $product->id, 404);

        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return back()->with('success', 'Imagen principal actualizada.');
    }

    protected function validateProduct(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'sku' => [
                'required',
                'string',
                'max:64',
                Rule::unique('products', 'sku')->ignore($product?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->ignore($product?->id),
            ],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'detailed_description' => ['nullable', 'string'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'integer', 'exists:categories,id'],
            'dimensions' => ['nullable', 'string', 'max:255'],
            'material' => ['nullable', 'string', 'max:255'],
            'sale_unit' => ['required', 'string', 'max:60'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'currency' => ['required', Rule::in(['PEN', 'USD', 'EUR'])],
            'price_visibility' => ['required', Rule::in(['inherit', 'show', 'hide'])],
            'availability_status' => ['required', Rule::in(['available', 'on_request', 'out_of_stock', 'unknown'])],
            'status' => ['required', Rule::in(['published', 'draft', 'archived'])],
            'is_featured' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'primary_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery_images.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'technical_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'specs' => ['nullable', 'array'],
            'specs.*.spec_group' => ['nullable', 'string', 'max:100'],
            'specs.*.spec_key' => ['nullable', 'string', 'max:120'],
            'specs.*.spec_value' => ['nullable', 'string', 'max:255'],
            'specs.*.unit' => ['nullable', 'string', 'max:50'],
            'product_type' => ['nullable', Rule::in(['simple', 'variable', 'auto'])],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.variant_label' => ['nullable', 'string', 'max:120'],
            'variants.*.sku' => ['nullable', 'string', 'max:80'],
            'variants.*.name' => ['nullable', 'string', 'max:255'],
            'variants.*.dimensions' => ['nullable', 'string', 'max:255'],
            'variants.*.material' => ['nullable', 'string', 'max:255'],
            'variants.*.sale_unit' => ['nullable', 'string', 'max:60'],
            'variants.*.box_qty' => ['nullable', 'integer', 'min:1', 'max:999999'],
            'variants.*.carton_qty' => ['nullable', 'integer', 'min:1', 'max:999999'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'variants.*.availability_status' => ['nullable', Rule::in(['available', 'on_request', 'out_of_stock', 'unknown'])],
        ]);
    }

    protected function syncSpecifications(Product $product, array $specs): void
    {
        $product->specifications()->delete();

        $sortOrder = 1;
        foreach ($specs as $spec) {
            $key = trim((string) ($spec['spec_key'] ?? ''));
            $value = trim((string) ($spec['spec_value'] ?? ''));
            if ($key === '' || $value === '') {
                continue;
            }

            $product->specifications()->create([
                'spec_group' => trim((string) ($spec['spec_group'] ?? '')) ?: 'General',
                'spec_key' => $key,
                'spec_value' => $value,
                'unit' => trim((string) ($spec['unit'] ?? '')) ?: null,
                'sort_order' => $sortOrder++,
            ]);
        }
    }

    protected function syncVariants(Product $product, array $variants, string $productType = 'auto'): void
    {
        if ($productType === 'simple') {
            $product->variants()->delete();

            return;
        }

        $product->variants()->delete();

        $sortOrder = 1;
        foreach ($variants as $variantRow) {
            $label = trim((string) ($variantRow['variant_label'] ?? ''));
            $rawSku = strtoupper(trim((string) ($variantRow['sku'] ?? '')));

            if ($label === '' && $rawSku === '') {
                continue;
            }

            if ($label === '') {
                $label = $rawSku;
            }

            if ($rawSku === '') {
                $rawSku = strtoupper($product->sku.'-'.Str::slug($label));
            }

            $rawPrice = $variantRow['price'] ?? null;
            $normalizedPrice = ($rawPrice !== null && $rawPrice !== '' && (float) $rawPrice > 0)
                ? round((float) $rawPrice, 2)
                : null;

            $rawBoxQty = $variantRow['box_qty'] ?? null;
            $boxQty = ($rawBoxQty !== null && $rawBoxQty !== '' && (int) $rawBoxQty > 0) ? (int) $rawBoxQty : null;

            $rawCartonQty = $variantRow['carton_qty'] ?? null;
            $cartonQty = ($rawCartonQty !== null && $rawCartonQty !== '' && (int) $rawCartonQty > 0) ? (int) $rawCartonQty : null;

            $product->variants()->create([
                'sku' => $rawSku,
                'variant_label' => $label,
                'name' => trim((string) ($variantRow['name'] ?? '')) ?: ($product->name.' — '.$label),
                'dimensions' => trim((string) ($variantRow['dimensions'] ?? '')) ?: ($label ?: $product->dimensions),
                'material' => trim((string) ($variantRow['material'] ?? '')) ?: $product->material,
                'sale_unit' => trim((string) ($variantRow['sale_unit'] ?? '')) ?: $product->sale_unit,
                'box_qty' => $boxQty,
                'carton_qty' => $cartonQty,
                'price' => $normalizedPrice,
                'currency' => $product->currency ?: 'PEN',
                'availability_status' => in_array($variantRow['availability_status'] ?? '', ['available', 'on_request', 'out_of_stock', 'unknown'], true)
                    ? $variantRow['availability_status']
                    : $product->availability_status,
                'sort_order' => $sortOrder++,
            ]);
        }
    }

    protected function handleUploads(Request $request, Product $product): void
    {
        if ($request->hasFile('primary_image')) {
            $file = $request->file('primary_image');
            $path = $file->store('products/images', 'public');
            $thumbnailPath = $this->generateThumbnailIfPossible($path);
            $product->images()->update(['is_primary' => false]);
            $product->images()->create([
                'file_path' => $path,
                'thumbnail_path' => $thumbnailPath,
                'alt_text' => $product->name,
                'is_primary' => true,
                'sort_order' => 0,
            ]);
        }

        if ($request->hasFile('gallery_images')) {
            $sort = (int) $product->images()->max('sort_order') + 1;
            foreach ($request->file('gallery_images') as $galleryFile) {
                if (! $galleryFile) {
                    continue;
                }
                $path = $galleryFile->store('products/images', 'public');
                $thumbnailPath = $this->generateThumbnailIfPossible($path);
                $hasPrimary = $product->images()->where('is_primary', true)->exists();
                $product->images()->create([
                    'file_path' => $path,
                    'thumbnail_path' => $thumbnailPath,
                    'alt_text' => $product->name,
                    'is_primary' => ! $hasPrimary,
                    'sort_order' => $sort++,
                ]);
            }
        }

        if ($request->hasFile('technical_pdf')) {
            $pdfFile = $request->file('technical_pdf');
            $pdfPath = $pdfFile->store('products/documents', 'public');
            $product->update(['technical_pdf_path' => $pdfPath]);

            ProductDocument::query()->create([
                'product_id' => $product->id,
                'title' => 'Ficha Técnica - '.$product->sku,
                'file_path' => $pdfPath,
                'file_size' => $pdfFile->getSize(),
                'mime_type' => $pdfFile->getMimeType() ?: 'application/pdf',
            ]);
        }
    }

    protected function generateThumbnailIfPossible(string $path): ?string
    {
        if (! class_exists(ImageThumbnailService::class)) {
            return null;
        }

        try {
            $service = app(ImageThumbnailService::class);
            foreach (['generateThumbnail', 'generate', 'createThumbnail', 'make'] as $method) {
                if (method_exists($service, $method)) {
                    return $service->{$method}($path);
                }
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    protected function generateUniqueSlug(string $name, string $sku, ?int $ignoreId = null): string
    {
        $base = Str::slug($name.'-'.$sku) ?: Str::slug($sku);
        $slug = $base;
        $counter = 1;

        while (
            Product::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
