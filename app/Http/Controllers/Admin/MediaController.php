<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductDocument;
use App\Models\ProductImage;
use App\Services\ActivityLoggerService;
use App\Services\ImageThumbnailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function __construct(
        protected ActivityLoggerService $activityLogger
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('media.manage'), 403);

        $imagesQuery = ProductImage::query()->with('product');
        $documentsQuery = ProductDocument::query()->with('product');

        if ($request->filled('product_id')) {
            $pid = (int) $request->input('product_id');
            $imagesQuery->where('product_id', $pid);
            $documentsQuery->where('product_id', $pid);
        }

        $images = $imagesQuery->latest()->paginate(12, ['*'], 'images_page')->withQueryString();
        $documents = $documentsQuery->latest()->paginate(12, ['*'], 'docs_page')->withQueryString();
        $products = Product::query()->orderBy('sku')->get(['id', 'sku', 'name']);

        return view('admin.media.index', [
            'images' => $images,
            'documents' => $documents,
            'products' => $products,
        ]);
    }

    public function storeImage(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('media.manage'), 403);

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $product = Product::query()->findOrFail($validated['product_id']);
        $file = $request->file('image');
        $path = $file->store('products/images', 'public');
        $thumbnailPath = $this->generateThumbnailIfPossible($path);

        $isPrimary = $request->boolean('is_primary') || ! $product->images()->exists();
        if ($isPrimary) {
            $product->images()->update(['is_primary' => false]);
        }

        $image = ProductImage::query()->create([
            'product_id' => $product->id,
            'file_path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'alt_text' => $validated['alt_text'] ?: $product->name,
            'is_primary' => $isPrimary,
            'sort_order' => ((int) $product->images()->max('sort_order')) + 1,
        ]);

        $this->activityLogger->log(
            'media.image_uploaded',
            'media',
            sprintf('Imagen subida para el producto %s (%s).', $product->sku, $product->name),
            $image
        );

        return back()->with('success', sprintf('Imagen vinculada correctamente al producto %s.', $product->sku));
    }

    public function storeDocument(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('media.manage'), 403);

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'title' => ['required', 'string', 'max:255'],
            'document' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $product = Product::query()->findOrFail($validated['product_id']);
        $file = $request->file('document');
        $path = $file->store('products/documents', 'public');

        $doc = ProductDocument::query()->create([
            'product_id' => $product->id,
            'title' => trim($validated['title']),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType() ?: 'application/pdf',
        ]);

        if (! $product->technical_pdf_path) {
            $product->update(['technical_pdf_path' => $path]);
        }

        $this->activityLogger->log(
            'media.document_uploaded',
            'media',
            sprintf('Ficha técnica PDF "%s" subida para el producto %s.', $doc->title, $product->sku),
            $doc
        );

        return back()->with('success', sprintf('Ficha técnica PDF vinculada al producto %s.', $product->sku));
    }

    public function destroyImage(Request $request, ProductImage $image): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('media.manage'), 403);

        if ($image->file_path && Storage::disk('public')->exists($image->file_path)) {
            Storage::disk('public')->delete($image->file_path);
        }
        if ($image->thumbnail_path && Storage::disk('public')->exists($image->thumbnail_path)) {
            Storage::disk('public')->delete($image->thumbnail_path);
        }

        $sku = $image->product?->sku ?? 'N/A';
        $image->delete();

        $this->activityLogger->log(
            'media.image_deleted',
            'media',
            sprintf('Imagen eliminada del producto SKU %s.', $sku)
        );

        return back()->with('success', 'Imagen eliminada correctamente.');
    }

    public function destroyDocument(Request $request, ProductDocument $document): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('media.manage'), 403);

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        if ($document->product && $document->product->technical_pdf_path === $document->file_path) {
            $document->product->update(['technical_pdf_path' => null]);
        }

        $title = $document->title;
        $document->delete();

        $this->activityLogger->log(
            'media.document_deleted',
            'media',
            sprintf('Documento técnico "%s" eliminado.', $title)
        );

        return back()->with('success', 'Documento técnico eliminado correctamente.');
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
}
