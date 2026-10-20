<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\CatalogSearchService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public const SESSION_COMPARE_KEY = 'compare_product_ids';

    public const MAX_COMPARE_PRODUCTS = 4;

    public function __construct(
        protected CatalogSearchService $catalogSearchService
    ) {}

    public function index(Request $request, ?string $categorySlug = null): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'category' => $categorySlug ?: $request->query('category'),
            'subcategory' => $request->query('subcategory'),
            'brand' => $request->query('brand'),
            'availability' => $request->query('availability'),
            'material' => $request->query('material'),
            'spec_key' => $request->query('spec_key'),
            'spec_value' => $request->query('spec_value'),
            'specs' => $request->query('specs', []),
            'featured' => $request->query('featured'),
            'sort' => $request->query('sort', 'relevance'),
        ];

        $viewMode = in_array($request->query('view'), ['grid', 'list'], true)
            ? (string) $request->query('view')
            : 'grid';

        $activeCategory = null;
        if (! empty($filters['category'])) {
            $activeCategory = is_numeric($filters['category'])
                ? Category::query()->with('children', 'parent')->find((int) $filters['category'])
                : Category::query()->with('children', 'parent')->where('slug', (string) $filters['category'])->first();

            // If a subcategory slug was passed into category filter, resolve parent/subcategory gracefully
            if ($activeCategory && $activeCategory->parent_id !== null && empty($filters['subcategory'])) {
                $filters['subcategory'] = $activeCategory->slug;
                $filters['category'] = $activeCategory->parent?->slug;
            }
        }

        $activeSubcategory = null;
        if (! empty($filters['subcategory'])) {
            $activeSubcategory = is_numeric($filters['subcategory'])
                ? Category::query()->with('parent')->find((int) $filters['subcategory'])
                : Category::query()->with('parent')->where('slug', (string) $filters['subcategory'])->first();
        }

        $activeBrand = null;
        if (! empty($filters['brand']) && ! is_array($filters['brand'])) {
            $activeBrand = is_numeric($filters['brand'])
                ? Brand::query()->find((int) $filters['brand'])
                : Brand::query()->where('slug', (string) $filters['brand'])->first();
        }

        $products = $this->catalogSearchService->search($filters, 12);
        $filterOptions = $this->catalogSearchService->getFilterOptions($activeCategory?->id);

        // Distinct materials from published products for technical filtering
        $materials = Product::query()
            ->where('status', 'published')
            ->whereNotNull('material')
            ->where('material', '!=', '')
            ->distinct()
            ->orderBy('material')
            ->limit(30)
            ->pluck('material');

        // Build SEO metadata and robots directives (avoid indexing complex faceted filter combinations)
        $hasComplexFilters = $filters['q'] !== ''
            || ! empty($filters['availability'])
            || ! empty($filters['material'])
            || ! empty($filters['spec_key'])
            || ! empty($filters['specs'])
            || ($filters['sort'] !== 'relevance')
            || $request->has('page');

        $title = 'Catálogo Técnico de Ferretería, Herrajes y Construcción | Import Hns San Luis';
        $description = 'Explora nuestro catálogo técnico ferretero por código SKU, medidas, marcas y especificaciones. Fichas técnicas en PDF y cotización rápida B2B/B2C.';
        $canonicalUrl = route('catalog.index');

        if ($activeSubcategory) {
            $title = ($activeSubcategory->meta_title ?: $activeSubcategory->name.' | Catálogo Técnico | Import Hns San Luis');
            $description = $activeSubcategory->meta_description
                ?: sprintf('Cotiza %s al por mayor y menor en Import Hns San Luis. Especificaciones técnicas, códigos SKU y atención rápida.', $activeSubcategory->name);
            if (! $hasComplexFilters && empty($filters['brand'])) {
                $canonicalUrl = route('catalog.index', ['category' => $activeCategory?->slug, 'subcategory' => $activeSubcategory->slug]);
            }
        } elseif ($activeCategory) {
            $title = ($activeCategory->meta_title ?: $activeCategory->name.' | Catálogo y Cotización | Import Hns San Luis');
            $description = $activeCategory->meta_description
                ?: ($activeCategory->description ?: sprintf('Catálogo técnico de %s en Import Hns San Luis.', $activeCategory->name));
            if (! $hasComplexFilters && empty($filters['brand'])) {
                $canonicalUrl = route('catalog.index', ['category' => $activeCategory->slug]);
            }
        } elseif ($activeBrand) {
            $title = sprintf('Productos %s | Catálogo Técnico | Import Hns San Luis', $activeBrand->name);
            $description = $activeBrand->description
                ?: sprintf('Consulta códigos SKU, especificaciones y solicita cotización de productos %s en Import Hns San Luis.', $activeBrand->name);
            if (! $hasComplexFilters) {
                $canonicalUrl = route('catalog.index', ['brand' => $activeBrand->slug]);
            }
        }

        if ($filters['q'] !== '') {
            $title = sprintf('Resultados para "%s" | Catálogo Técnico | Import Hns San Luis', $filters['q']);
        }

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Catálogo Técnico', 'url' => route('catalog.index')],
        ];
        if ($activeCategory) {
            $breadcrumbs[] = [
                'name' => $activeCategory->name,
                'url' => route('catalog.index', ['category' => $activeCategory->slug]),
            ];
        }
        if ($activeSubcategory) {
            $breadcrumbs[] = [
                'name' => $activeSubcategory->name,
                'url' => route('catalog.index', ['category' => $activeCategory?->slug, 'subcategory' => $activeSubcategory->slug]),
            ];
        }
        if ($activeBrand) {
            $breadcrumbs[] = [
                'name' => 'Marca '.$activeBrand->name,
                'url' => route('catalog.index', ['brand' => $activeBrand->slug]),
            ];
        }

        $compareIds = $this->getSessionCompareIds();

        return view('public.catalog.index', [
            'products' => $products,
            'filters' => $filters,
            'viewMode' => $viewMode,
            'categories' => $filterOptions['categories'],
            'brands' => $filterOptions['brands'],
            'specifications' => $filterOptions['specifications'],
            'materials' => $materials,
            'activeCategory' => $activeCategory,
            'activeSubcategory' => $activeSubcategory,
            'activeBrand' => $activeBrand,
            'compareIds' => $compareIds,
            'breadcrumbs' => $breadcrumbs,
            'seo' => [
                'title' => $title,
                'description' => $description,
                'canonical' => $canonicalUrl,
                'prev_url' => $products->previousPageUrl(),
                'next_url' => $products->nextPageUrl(),
                'robots' => $hasComplexFilters ? 'noindex, follow' : 'index, follow',
                'breadcrumbs' => $breadcrumbs,
            ],
        ]);
    }

    /**
     * Instant technical search autocomplete endpoint (GET /catalogo/sugerencias).
     */
    public function suggestions(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $categorySlug = trim((string) $request->query('category', ''));

        $catalogUrl = route('catalog.index', array_filter([
            'q' => $q !== '' ? $q : null,
            'category' => $categorySlug !== '' ? $categorySlug : null,
        ]));

        if (mb_strlen($q) < 2) {
            return response()->json([
                'query' => $q,
                'products' => [],
                'categories' => [],
                'catalog_url' => $catalogUrl,
            ]);
        }

        $likeTerm = '%'.$q.'%';
        $prefixTerm = $q.'%';

        $productQuery = Product::query()
            ->with(['category', 'brand', 'images'])
            ->where('status', 'published')
            ->where(function (Builder $builder) use ($likeTerm) {
                $builder->where('sku', 'like', $likeTerm)
                    ->orWhere('name', 'like', $likeTerm)
                    ->orWhere('short_description', 'like', $likeTerm)
                    ->orWhere('material', 'like', $likeTerm)
                    ->orWhere('dimensions', 'like', $likeTerm)
                    ->orWhereHas('brand', function (Builder $bq) use ($likeTerm) {
                        $bq->where('name', 'like', $likeTerm);
                    })
                    ->orWhereHas('specifications', function (Builder $sq) use ($likeTerm) {
                        $sq->where('spec_value', 'like', $likeTerm)
                            ->orWhere('spec_key', 'like', $likeTerm);
                    });
            });

        if ($categorySlug !== '') {
            $category = Category::query()->with('children')->where('slug', $categorySlug)->first();
            if ($category) {
                $catIds = collect([$category->id])->merge($category->children->pluck('id'))->unique()->values()->all();
                $productQuery->where(function (Builder $cq) use ($catIds, $category) {
                    $cq->whereIn('category_id', $catIds)
                        ->orWhere('subcategory_id', $category->id);
                });
            }
        }

        $products = $productQuery
            ->orderByRaw(
                'CASE WHEN sku = ? THEN 0 WHEN sku LIKE ? THEN 1 WHEN name LIKE ? THEN 2 ELSE 3 END',
                [$q, $prefixTerm, $prefixTerm]
            )
            ->orderByDesc('is_featured')
            ->orderBy('name', 'asc')
            ->limit(8)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'slug' => $product->slug,
                'url' => route('catalog.show', $product->slug),
                'category_name' => $product->category?->name ?? '',
                'brand_name' => $product->brand?->name ?? '',
                'sale_unit' => $product->sale_unit,
                'availability_label' => $product->availabilityLabel(),
                'availability_badge' => $product->availabilityBadgeClass(),
                'price_formatted' => $product->formattedPrice(),
                'image_url' => $product->primaryThumbnailUrl(),
            ])
            ->values();

        $categories = Category::query()
            ->where('is_active', true)
            ->where(function (Builder $cq) use ($likeTerm) {
                $cq->where('name', 'like', $likeTerm)
                    ->orWhere('description', 'like', $likeTerm);
            })
            ->orderBy('sort_order')
            ->limit(3)
            ->get()
            ->map(fn (Category $cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'url' => route('catalog.index', ['category' => $cat->slug]),
            ])
            ->values();

        return response()->json([
            'query' => $q,
            'products' => $products,
            'categories' => $categories,
            'catalog_url' => $catalogUrl,
        ]);
    }

    /**
     * Display side-by-side technical product comparison table (up to 4 products).
     */
    public function compare(Request $request): View
    {
        $sessionIds = $this->getSessionCompareIds();

        // Allow passing ?ids=1,2,3 via query string for shareable comparison links
        $rawQueryIds = $request->query('ids');
        $requestedIds = [];

        if (is_string($rawQueryIds) && trim($rawQueryIds) !== '') {
            $requestedIds = array_map('intval', explode(',', $rawQueryIds));
        } elseif (is_array($rawQueryIds)) {
            $requestedIds = array_map('intval', $rawQueryIds);
        }

        $requestedIds = array_values(array_unique(array_filter($requestedIds, fn (int $id) => $id > 0)));
        if (! empty($requestedIds)) {
            $requestedIds = array_slice($requestedIds, 0, self::MAX_COMPARE_PRODUCTS);
            session([self::SESSION_COMPARE_KEY => $requestedIds]);
            $activeIds = $requestedIds;
        } else {
            $activeIds = $sessionIds;
        }

        $products = collect();
        if (! empty($activeIds)) {
            $fetched = Product::query()
                ->with(['category', 'subcategory', 'brand', 'images', 'specifications', 'documents'])
                ->where('status', 'published')
                ->whereIn('id', $activeIds)
                ->get()
                ->keyBy('id');

            foreach ($activeIds as $pid) {
                if ($fetched->has($pid)) {
                    $products->push($fetched->get($pid));
                }
            }

            // Keep session clean with only valid published product IDs
            $validIds = $products->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
            if ($validIds !== $activeIds) {
                session([self::SESSION_COMPARE_KEY => $validIds]);
            }
        }

        // Collect all distinct specification keys across compared products in order
        $specKeys = [];
        $productSpecsMap = [];

        foreach ($products as $product) {
            $productSpecsMap[$product->id] = [];
            foreach ($product->specifications as $spec) {
                $key = trim((string) $spec->spec_key);
                if ($key === '') {
                    continue;
                }
                if (! in_array($key, $specKeys, true)) {
                    $specKeys[] = $key;
                }
                $val = trim($spec->spec_value.' '.($spec->unit ?? ''));
                $productSpecsMap[$product->id][$key] = $val;
            }
        }

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Catálogo Técnico', 'url' => route('catalog.index')],
            ['name' => 'Comparador Técnico de Productos', 'url' => route('catalog.compare')],
        ];

        $shareUrl = $products->isNotEmpty()
            ? route('catalog.compare', ['ids' => $products->pluck('id')->implode(',')])
            : route('catalog.compare');

        return view('public.catalog.compare', [
            'products' => $products,
            'specKeys' => $specKeys,
            'productSpecsMap' => $productSpecsMap,
            'maxProducts' => self::MAX_COMPARE_PRODUCTS,
            'shareUrl' => $shareUrl,
            'breadcrumbs' => $breadcrumbs,
            'seo' => [
                'title' => 'Comparador Técnico de Productos y Especificaciones | Import Hns San Luis',
                'description' => 'Compara hasta 4 productos ferreteros lado a lado: códigos SKU, medidas, materiales, unidades de venta, fichas técnicas PDF y precios referenciales.',
                'canonical' => route('catalog.compare'),
                'robots' => 'noindex, follow',
                'breadcrumbs' => $breadcrumbs,
            ],
        ]);
    }

    /**
     * Add a product to the technical comparison list in session (max 4 products).
     */
    public function addToCompare(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $product = Product::query()
            ->where('status', 'published')
            ->findOrFail((int) $validated['product_id']);

        $ids = $this->getSessionCompareIds();
        $alreadyInList = in_array($product->id, $ids, true);

        if (! $alreadyInList && count($ids) >= self::MAX_COMPARE_PRODUCTS) {
            $limitMsg = sprintf('Puedes comparar hasta un máximo de %d productos simultáneamente. Retira uno para agregar "%s".', self::MAX_COMPARE_PRODUCTS, $product->sku);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'limit_reached',
                    'message' => $limitMsg,
                    'compare_count' => count($ids),
                    'compare_ids' => $ids,
                    'compare_url' => route('catalog.compare'),
                ], 422);
            }

            return back()->with('error', $limitMsg);
        }

        if (! $alreadyInList) {
            $ids[] = $product->id;
            session([self::SESSION_COMPARE_KEY => $ids]);
            $message = sprintf('"%s" (%s) añadido al comparador técnico (%d/%d).', $product->name, $product->sku, count($ids), self::MAX_COMPARE_PRODUCTS);
        } else {
            $message = sprintf('"%s" (%s) ya se encuentra en tu comparador técnico (%d/%d).', $product->name, $product->sku, count($ids), self::MAX_COMPARE_PRODUCTS);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'ok',
                'message' => $message,
                'compare_count' => count($ids),
                'compare_ids' => $ids,
                'compare_url' => route('catalog.compare'),
                'product' => [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                ],
            ]);
        }

        if ($request->boolean('redirect_to_compare')) {
            return redirect()->route('catalog.compare')->with('success', $message);
        }

        return back()->with('success', $message);
    }

    /**
     * Remove a product from the technical comparison list in session.
     */
    public function removeFromCompare(Request $request, ?int $productId = null): JsonResponse|RedirectResponse
    {
        $targetId = $productId ?? (int) $request->input('product_id', 0);
        $ids = array_values(array_filter(
            $this->getSessionCompareIds(),
            fn (int $id) => $id !== $targetId
        ));

        session([self::SESSION_COMPARE_KEY => $ids]);

        $message = 'Producto retirado del comparador técnico.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'ok',
                'message' => $message,
                'compare_count' => count($ids),
                'compare_ids' => $ids,
                'compare_url' => route('catalog.compare'),
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Clear all products from the technical comparison list in session.
     */
    public function clearCompare(Request $request): JsonResponse|RedirectResponse
    {
        session()->forget(self::SESSION_COMPARE_KEY);

        $message = 'Se ha vaciado el comparador técnico de productos.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'ok',
                'message' => $message,
                'compare_count' => 0,
                'compare_ids' => [],
                'compare_url' => route('catalog.compare'),
            ]);
        }

        return redirect()->route('catalog.compare')->with('success', $message);
    }

    /**
     * Retrieve sanitized array of product IDs stored in session for comparison.
     *
     * @return array<int, int>
     */
    protected function getSessionCompareIds(): array
    {
        $raw = session(self::SESSION_COMPARE_KEY, []);
        if (! is_array($raw)) {
            return [];
        }

        return array_slice(
            array_values(array_unique(array_filter(array_map('intval', $raw), fn (int $id) => $id > 0))),
            0,
            self::MAX_COMPARE_PRODUCTS
        );
    }
}
