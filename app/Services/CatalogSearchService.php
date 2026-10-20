<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpecification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CatalogSearchService
{
    /**
     * Perform a server-side paginated and filtered search across the product catalog.
     */
    public function search(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Product::query()
            ->select([
                'id',
                'sku',
                'name',
                'slug',
                'short_description',
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
                'created_at',
                'updated_at',
            ])
            ->with(['category', 'subcategory', 'brand', 'images', 'specifications', 'variants']);

        if (empty($filters['include_all_statuses'])) {
            $query->where('status', 'published');
        } elseif (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Search term 'q'
        $searchTerm = trim((string) ($filters['q'] ?? ''));
        if ($searchTerm !== '') {
            $likeTerm = '%'.$searchTerm.'%';
            $query->where(function (Builder $q) use ($likeTerm, $searchTerm) {
                $q->where('sku', 'like', $likeTerm)
                    ->orWhere('name', 'like', $likeTerm)
                    ->orWhere('short_description', 'like', $likeTerm)
                    ->orWhere('material', 'like', $likeTerm)
                    ->orWhere('dimensions', 'like', $likeTerm)
                    ->orWhereHas('brand', function (Builder $brandQuery) use ($likeTerm) {
                        $brandQuery->where('name', 'like', $likeTerm);
                    })
                    ->orWhereHas('specifications', function (Builder $specQuery) use ($likeTerm) {
                        $specQuery->where('spec_value', 'like', $likeTerm)
                            ->orWhere('spec_key', 'like', $likeTerm);
                    })
                    ->orWhereHas('variants', function (Builder $variantQuery) use ($likeTerm) {
                        $variantQuery->where('sku', 'like', $likeTerm)
                            ->orWhere('variant_label', 'like', $likeTerm)
                            ->orWhere('dimensions', 'like', $likeTerm);
                    });

                // Exact prefix boost handled in sorting
                if (strlen($searchTerm) >= 3) {
                    $q->orWhere('detailed_description', 'like', $likeTerm);
                }
            });
        }

        // Category filter (supports slug or ID, and includes child subcategories)
        if (! empty($filters['category'])) {
            $categoryFilter = $filters['category'];
            $category = is_numeric($categoryFilter)
                ? Category::query()->with('children')->find((int) $categoryFilter)
                : Category::query()->with('children')->where('slug', (string) $categoryFilter)->first();

            if ($category) {
                $categoryIds = collect([$category->id])
                    ->merge($category->children->pluck('id'))
                    ->unique()
                    ->values()
                    ->all();

                $query->where(function (Builder $q) use ($categoryIds, $category) {
                    $q->whereIn('category_id', $categoryIds)
                        ->orWhere('subcategory_id', $category->id);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Subcategory filter (supports slug or ID)
        if (! empty($filters['subcategory'])) {
            $subFilter = $filters['subcategory'];
            if (is_numeric($subFilter)) {
                $query->where('subcategory_id', (int) $subFilter);
            } else {
                $subCategory = Category::query()->where('slug', (string) $subFilter)->first();
                if ($subCategory) {
                    $query->where('subcategory_id', $subCategory->id);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
        }

        // Brand filter (single slug/id or array of slugs/ids)
        if (! empty($filters['brand'])) {
            $brandInput = is_array($filters['brand']) ? $filters['brand'] : [$filters['brand']];
            $brandIds = [];
            $brandSlugs = [];

            foreach ($brandInput as $item) {
                if (is_numeric($item)) {
                    $brandIds[] = (int) $item;
                } elseif (is_string($item) && trim($item) !== '') {
                    $brandSlugs[] = trim($item);
                }
            }

            if (! empty($brandSlugs)) {
                $resolvedIds = Brand::query()->whereIn('slug', $brandSlugs)->pluck('id')->all();
                $brandIds = array_values(array_unique(array_merge($brandIds, $resolvedIds)));
            }

            if (! empty($brandIds)) {
                $query->whereIn('brand_id', $brandIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Availability filter
        if (! empty($filters['availability'])) {
            $availInput = is_array($filters['availability']) ? $filters['availability'] : [$filters['availability']];
            $query->whereIn('availability_status', $availInput);
        }

        // Technical specification filter (spec_key + spec_value or specs array)
        if (! empty($filters['spec_key']) && isset($filters['spec_value']) && $filters['spec_value'] !== '') {
            $specKey = (string) $filters['spec_key'];
            $specVal = (string) $filters['spec_value'];
            $query->whereHas('specifications', function (Builder $specQuery) use ($specKey, $specVal) {
                $specQuery->where('spec_key', $specKey)
                    ->where('spec_value', $specVal);
            });
        }

        if (! empty($filters['specs']) && is_array($filters['specs'])) {
            foreach ($filters['specs'] as $sKey => $sValue) {
                if ($sValue === null || $sValue === '') {
                    continue;
                }
                $query->whereHas('specifications', function (Builder $specQuery) use ($sKey, $sValue) {
                    $specQuery->where('spec_key', (string) $sKey)
                        ->where('spec_value', (string) $sValue);
                });
            }
        }

        // Featured filter
        if (isset($filters['featured']) && $filters['featured'] !== '' && $filters['featured'] !== null) {
            $query->where('is_featured', filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN));
        }

        // Material filter
        if (! empty($filters['material'])) {
            $query->where('material', 'like', '%'.trim((string) $filters['material']).'%');
        }

        // Sorting
        $sort = (string) ($filters['sort'] ?? 'relevance');
        match ($sort) {
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'sku_asc' => $query->orderBy('sku', 'asc'),
            'newest' => $query->orderByDesc('created_at'),
            'random' => $query->inRandomOrder(),
            default => $this->applyRelevanceSort($query, $searchTerm),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    protected function applyRelevanceSort(Builder $query, string $searchTerm): void
    {
        if ($searchTerm !== '') {
            $exactSku = $searchTerm;
            $prefixTerm = $searchTerm.'%';
            $query->orderByRaw(
                'CASE WHEN sku = ? THEN 0 WHEN sku LIKE ? THEN 1 WHEN name LIKE ? THEN 2 ELSE 3 END',
                [$exactSku, $prefixTerm, $prefixTerm]
            );
        }

        $query->inRandomOrder();
    }

    /**
     * Return available filter options for the catalog sidebar.
     */
    public function getFilterOptions(?int $categoryId = null): array
    {
        $categories = Category::cachedNavigationTree();

        $brands = Brand::query()
            ->where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->where('status', 'published')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $specKeysQuery = ProductSpecification::query()
            ->select(['spec_key', 'spec_value'])
            ->whereNotIn('spec_key', ['Marca', 'Peso Unitario Referencial'])
            ->whereHas('product', function (Builder $q) use ($categoryId) {
                $q->where('status', 'published');
                if ($categoryId) {
                    $q->where(function (Builder $sub) use ($categoryId) {
                        $sub->where('category_id', $categoryId)
                            ->orWhere('subcategory_id', $categoryId);
                    });
                }
            })
            ->distinct()
            ->orderBy('spec_key')
            ->orderBy('spec_value')
            ->limit(150)
            ->get()
            ->groupBy('spec_key');

        return [
            'categories' => $categories,
            'brands' => $brands,
            'specifications' => $specKeysQuery,
        ];
    }
}
