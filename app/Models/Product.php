<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'slug',
        'short_description',
        'detailed_description',
        'brand_id',
        'category_id',
        'subcategory_id',
        'dimensions',
        'material',
        'sale_unit',
        'price',
        'currency',
        'price_visibility',
        'availability_status',
        'technical_pdf_path',
        'status',
        'is_featured',
        'views_count',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_featured' => 'boolean',
            'views_count' => 'integer',
        ];
    }

    public static function formatSentenceCase(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return '';
        }

        return Str::ucfirst(mb_strtolower($trimmed, 'UTF-8'));
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => self::formatSentenceCase($value),
            set: fn (?string $value): ?string => self::formatSentenceCase($value),
        );
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderByDesc('is_primary')->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function specifications(): HasMany
    {
        return $this->hasMany(ProductSpecification::class)->orderBy('sort_order');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProductDocument::class);
    }

    public function quoteItems(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    public function hasVariants(): bool
    {
        return $this->relationLoaded('variants')
            ? $this->variants->isNotEmpty()
            : $this->variants()->exists();
    }

    /**
     * Determine whether a given price value should be displayed on the public catalog
     * according to the product's price_visibility and global commercial mode.
     */
    public function shouldShowPriceForValue(?float $priceValue, ?string $globalMode = null): bool
    {
        if ($priceValue === null || $priceValue <= 0) {
            return false;
        }

        $mode = $globalMode ?? (string) SiteSetting::getValue(
            'commercial_price_display_mode',
            env('COMMERCIAL_PRICE_DISPLAY_MODE', 'mixed')
        );

        if ($mode === 'hide_all') {
            return false;
        }

        if ($this->price_visibility === 'hide') {
            return false;
        }

        if ($this->price_visibility === 'show') {
            return true;
        }

        // 'inherit': only show if global mode is explicitly 'show_all'
        return $mode === 'show_all';
    }

    /**
     * Determine whether the price should be displayed on the public catalog.
     * Never displays price if price is null or <= 0.
     * By default, prices are hidden unless the administrator explicitly enables it for the product ('show')
     * or sets the global commercial mode to 'show_all'.
     */
    public function shouldShowPrice(?string $globalMode = null): bool
    {
        return $this->shouldShowPriceForValue(
            $this->price !== null ? (float) $this->price : null,
            $globalMode
        );
    }

    /**
     * Format price with currency symbol only if shouldShowPrice() is true.
     */
    public function formattedPrice(?string $globalMode = null): ?string
    {
        if (! $this->shouldShowPrice($globalMode)) {
            return null;
        }

        $symbol = match (strtoupper((string) $this->currency)) {
            'USD' => '$',
            'EUR' => '€',
            default => 'S/',
        };

        return sprintf('%s %s', $symbol, number_format((float) $this->price, 2, '.', ','));
    }

    /**
     * Human-readable availability status label.
     */
    public function availabilityLabel(): string
    {
        return match ($this->availability_status) {
            'available' => 'Disponible',
            'on_request' => 'Bajo consulta',
            'out_of_stock' => 'Sin stock',
            default => 'Consultar disponibilidad',
        };
    }

    /**
     * CSS badge classes for availability status.
     */
    public function availabilityBadgeClass(): string
    {
        return match ($this->availability_status) {
            'available' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'on_request' => 'bg-amber-100 text-amber-800 border-amber-200',
            'out_of_stock' => 'bg-rose-100 text-rose-800 border-rose-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    /**
     * Primary image URL or fallback placeholder SVG URL.
     */
    public function primaryImageUrl(): string
    {
        $image = null;

        if ($this->relationLoaded('images')) {
            $image = $this->images->firstWhere('is_primary', true) ?? $this->images->first();
        } elseif ($this->relationLoaded('primaryImage')) {
            $image = $this->primaryImage;
        } else {
            $image = $this->images()->first();
        }

        if ($image && $image->file_path) {
            if (str_starts_with($image->file_path, 'http://') || str_starts_with($image->file_path, 'https://') || str_starts_with($image->file_path, '/')) {
                return $image->file_path;
            }

            return Storage::disk('public')->url($image->file_path);
        }

        return asset('images/placeholder-product.svg');
    }

    /**
     * Primary thumbnail URL (400x400 WebP) for product cards, grids, and autocomplete.
     */
    public function primaryThumbnailUrl(): string
    {
        $image = null;

        if ($this->relationLoaded('images')) {
            $image = $this->images->firstWhere('is_primary', true) ?? $this->images->first();
        } elseif ($this->relationLoaded('primaryImage')) {
            $image = $this->primaryImage;
        } else {
            $image = $this->images()->first();
        }

        if ($image) {
            return $image->thumbnailUrl();
        }

        return asset('images/placeholder-product.svg');
    }

    /**
     * Secondary image URL (if product has a 2nd gallery image) for hover preview.
     */
    public function secondaryImageUrl(): ?string
    {
        $images = $this->relationLoaded('images') ? $this->images : $this->images()->get();
        if ($images->count() < 2) {
            return null;
        }

        $primary = $images->firstWhere('is_primary', true) ?? $images->first();
        $secondary = $images->first(fn ($img) => $img->id !== $primary?->id);

        if ($secondary && $secondary->file_path) {
            return $secondary->url();
        }

        return null;
    }

    /**
     * Secondary thumbnail URL (400x400 WebP) for hover preview in product cards.
     */
    public function secondaryThumbnailUrl(): ?string
    {
        $images = $this->relationLoaded('images') ? $this->images : $this->images()->get();
        if ($images->count() < 2) {
            return null;
        }

        $primary = $images->firstWhere('is_primary', true) ?? $images->first();
        $secondary = $images->first(fn ($img) => $img->id !== $primary?->id);

        if ($secondary && ($secondary->thumbnail_path || $secondary->file_path)) {
            return $secondary->thumbnailUrl();
        }

        return null;
    }

    /**
     * Delete all associated images, thumbnails, technical PDFs, specifications,
     * quote items, and activity logs for this product.
     *
     * @return array{images_deleted: int, documents_deleted: int, specifications_deleted: int, quote_items_deleted: int}
     */
    public function purgeAllRelatedDataAndMedia(): array
    {
        $this->loadMissing(['images', 'documents']);

        $imageIds = $this->images->pluck('id')->all();
        $documentIds = $this->documents->pluck('id')->all();

        $imagesDeleted = 0;
        foreach ($this->images as $image) {
            if ($image->file_path) {
                $this->deletePublicFileIfUnshared($image->file_path);
                $this->reassignOrClearCategoryImage($image->file_path);
            }

            if ($image->thumbnail_path && $image->thumbnail_path !== $image->file_path) {
                $this->deletePublicFileIfUnshared($image->thumbnail_path);
            }

            $imagesDeleted++;
        }

        $documentsDeleted = 0;
        foreach ($this->documents as $document) {
            if ($document->file_path) {
                $this->deletePublicFileIfUnshared($document->file_path);
            }

            $documentsDeleted++;
        }

        if ($this->technical_pdf_path) {
            $this->deletePublicFileIfUnshared($this->technical_pdf_path);
        }

        $this->images()->delete();
        $this->documents()->delete();
        $this->variants()->delete();
        $specificationsDeleted = $this->specifications()->delete();
        $quoteItemsDeleted = $this->quoteItems()->delete();

        ActivityLog::query()
            ->where(function ($query) use ($imageIds, $documentIds): void {
                $query->where(function ($q): void {
                    $q->where('subject_type', self::class)
                        ->where('subject_id', $this->id);
                });

                if (! empty($imageIds)) {
                    $query->orWhere(function ($q) use ($imageIds): void {
                        $q->where('subject_type', ProductImage::class)
                            ->whereIn('subject_id', $imageIds);
                    });
                }

                if (! empty($documentIds)) {
                    $query->orWhere(function ($q) use ($documentIds): void {
                        $q->where('subject_type', ProductDocument::class)
                            ->whereIn('subject_id', $documentIds);
                    });
                }
            })
            ->delete();

        if (app()->bound('session') && session()->isStarted()) {
            $cart = session()->get('quote_cart', []);
            if (is_array($cart) && isset($cart[$this->id])) {
                unset($cart[$this->id]);
                session()->put('quote_cart', $cart);
            }

            $compareIds = session()->get('catalog_compare_ids', []);
            if (is_array($compareIds) && in_array($this->id, $compareIds, true)) {
                session()->put(
                    'catalog_compare_ids',
                    array_values(array_filter($compareIds, fn ($id): bool => (int) $id !== $this->id))
                );
            }
        }

        return [
            'images_deleted' => $imagesDeleted,
            'documents_deleted' => $documentsDeleted,
            'specifications_deleted' => $specificationsDeleted,
            'quote_items_deleted' => $quoteItemsDeleted,
        ];
    }

    protected function deletePublicFileIfUnshared(string $rawPath): void
    {
        if (str_starts_with($rawPath, 'http://') || str_starts_with($rawPath, 'https://')) {
            return;
        }

        $normalized = ltrim($rawPath, '/');
        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, strlen('storage/'));
        }

        $usedByOtherImage = ProductImage::query()
            ->where('product_id', '!=', $this->id)
            ->where(function ($query) use ($rawPath, $normalized): void {
                $query->whereIn('file_path', [$rawPath, $normalized])
                    ->orWhereIn('thumbnail_path', [$rawPath, $normalized]);
            })
            ->exists();

        if ($usedByOtherImage) {
            return;
        }

        $usedByOtherDocument = ProductDocument::query()
            ->where('product_id', '!=', $this->id)
            ->whereIn('file_path', [$rawPath, $normalized])
            ->exists();

        if ($usedByOtherDocument) {
            return;
        }

        $usedByOtherProductPdf = self::withTrashed()
            ->where('id', '!=', $this->id)
            ->whereIn('technical_pdf_path', [$rawPath, $normalized])
            ->exists();

        if ($usedByOtherProductPdf) {
            return;
        }

        if ($normalized !== '' && Storage::disk('public')->exists($normalized)) {
            Storage::disk('public')->delete($normalized);
        } elseif ($rawPath !== '' && Storage::disk('public')->exists($rawPath)) {
            Storage::disk('public')->delete($rawPath);
        }
    }

    protected function reassignOrClearCategoryImage(string $deletedPath): void
    {
        $normalized = ltrim($deletedPath, '/');
        $affectedCategories = Category::query()
            ->whereIn('image_path', [$deletedPath, $normalized])
            ->get();

        foreach ($affectedCategories as $category) {
            $replacementPath = ProductImage::query()
                ->where('product_id', '!=', $this->id)
                ->whereHas('product', function ($query) use ($category): void {
                    $query->where('status', 'published')
                        ->where(function ($q) use ($category): void {
                            $q->where('category_id', $category->id)
                                ->orWhere('subcategory_id', $category->id);
                        });
                })
                ->orderByDesc('is_primary')
                ->orderBy('sort_order')
                ->value('file_path');

            $category->update(['image_path' => $replacementPath]);
        }
    }
}
