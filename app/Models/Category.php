<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Throwable;

class Category extends Model
{
    use HasFactory;

    public const NAV_CACHE_KEY = 'catalog:nav_categories_tree_v2';

    protected static ?Collection $memoizedNavTree = null;

    protected static ?int $memoizedAppId = null;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image_path',
        'attribute_schema',
        'sort_order',
        'is_active',
        'meta_title',
        'meta_description',
    ];

    protected static function booted(): void
    {
        static::saved(function (): void {
            self::clearNavigationCache();
        });

        static::deleted(function (): void {
            self::clearNavigationCache();
        });
    }

    public static function cachedNavigationTree(): Collection
    {
        $currentAppId = spl_object_id(app());
        if (self::$memoizedAppId !== $currentAppId) {
            self::$memoizedNavTree = null;
            self::$memoizedAppId = $currentAppId;
        }

        if (self::$memoizedNavTree !== null) {
            return self::$memoizedNavTree;
        }

        $rawTree = Cache::remember(
            self::NAV_CACHE_KEY,
            1800,
            fn (): array => self::query()
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->withCount(['products' => fn ($q) => $q->where('status', 'published')])
                ->with([
                    'children' => fn ($q) => $q
                        ->where('is_active', true)
                        ->withCount(['subProducts' => fn ($sq) => $sq->where('status', 'published')])
                        ->orderBy('sort_order'),
                ])
                ->orderBy('sort_order')
                ->get()
                ->map(function (self $cat): array {
                    $attrs = $cat->getAttributes();
                    $attrs['children'] = $cat->children
                        ->map(fn (self $sub): array => $sub->getAttributes())
                        ->all();

                    return $attrs;
                })
                ->all()
        );

        $models = array_map(function (array $row): self {
            $childrenRows = is_array($row['children'] ?? null) ? $row['children'] : [];
            unset($row['children']);

            $parent = (new self)->newFromBuilder($row);
            $children = new Collection(array_map(
                fn (array $subRow): self => (new self)->newFromBuilder($subRow),
                $childrenRows
            ));
            $parent->setRelation('children', $children);

            return $parent;
        }, is_array($rawTree) ? $rawTree : []);

        return self::$memoizedNavTree = new Collection($models);
    }

    public static function clearNavigationCache(): void
    {
        self::$memoizedNavTree = null;

        try {
            Cache::forget(self::NAV_CACHE_KEY);
        } catch (Throwable) {
            // Ignore cache driver issues during CLI/migrations
        }
    }

    protected function casts(): array
    {
        return [
            'attribute_schema' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function subProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'subcategory_id');
    }

    /**
     * Return a technical CAD-inspired SVG icon tailored to the category or subcategory slug.
     */
    public function iconSvg(string $class = 'w-4 h-4'): string
    {
        $slug = strtolower((string) $this->slug);

        $svgInner = match (true) {
            // 1. Herrajes y Bisagras (Parent & Subcategories)
            $slug === 'herrajes-para-muebles' || str_contains($slug, 'bisagras-cangrejo') => '<rect x="3" y="4" width="7" height="16" rx="1.5" fill="currentColor" fill-opacity="0.15" stroke="currentColor" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 9h5.5a3.5 3.5 0 110 6H10M6.5 7.5h.01M6.5 16.5h.01"/><circle cx="16" cy="12" r="2.2" fill="currentColor" fill-opacity="0.2" stroke="currentColor" stroke-width="1.8"/>',
            str_contains($slug, 'bisagras-para-puertas') => '<rect x="4" y="4" width="6" height="16" rx="1" fill="currentColor" fill-opacity="0.14" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="4" width="6" height="16" rx="1" fill="currentColor" fill-opacity="0.14" stroke="currentColor" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 7h4M10 12h4M10 17h4M12 4v16M7 8h.01M7 16h.01M17 8h.01M17 16h.01"/>',
            str_contains($slug, 'correderas-para-muebles') => '<rect x="2.5" y="6" width="19" height="5" rx="1" fill="currentColor" fill-opacity="0.15" stroke="currentColor" stroke-width="1.8"/><rect x="5" y="13" width="16.5" height="5" rx="1" stroke="currentColor" stroke-width="1.8"/><circle cx="6.5" cy="8.5" r="1" fill="currentColor"/><circle cx="12" cy="8.5" r="1" fill="currentColor"/><circle cx="9.5" cy="15.5" r="1" fill="currentColor"/><circle cx="16.5" cy="15.5" r="1" fill="currentColor"/>',
            str_contains($slug, 'pistones-y-push') => '<rect x="3" y="9.5" width="10" height="5" rx="1.2" fill="currentColor" fill-opacity="0.16" stroke="currentColor" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 12h6m2 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM3 12H1.5m5-6l3 3m0 6l-3 3"/>',

            // 2. Tiradores, Jaladores y Perillas (Parent & Subcategories)
            $slug === 'tiradores-jaladores-y-perillas' || str_contains($slug, 'jaladores-para-muebles') => '<rect x="3" y="14" width="18" height="6" rx="1.5" fill="currentColor" fill-opacity="0.14" stroke="currentColor" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14M7.5 7v7M16.5 7v7"/>',
            str_contains($slug, 'perillas-y-botones') => '<circle cx="12" cy="9" r="5" fill="currentColor" fill-opacity="0.16" stroke="currentColor" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 13.5v3.5h4v-3.5M6 17h12M4 20h16"/>',
            str_contains($slug, 'tiradores-embutidos') => '<rect x="3" y="6" width="18" height="12" rx="2.5" fill="currentColor" fill-opacity="0.14" stroke="currentColor" stroke-width="1.8"/><rect x="7" y="9.5" width="10" height="5" rx="1.5" stroke="currentColor" stroke-width="1.8"/>',
            str_contains($slug, 'perfiles-gola') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M4 5h16v4H8a2 2 0 00-2 2v3a2 2 0 002 2h12v3H4V5z" fill="currentColor" fill-opacity="0.14"/>',
            str_contains($slug, 'manillones-para-puertas') => '<rect x="6" y="3" width="3.5" height="18" rx="1.5" fill="currentColor" fill-opacity="0.16" stroke="currentColor" stroke-width="1.8"/><rect x="14.5" y="3" width="3.5" height="18" rx="1.5" fill="currentColor" fill-opacity="0.16" stroke="currentColor" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.5 7h5M9.5 17h5"/>',

            // 3. Organización de Cocina y Clóset (Parent & Subcategories)
            $slug === 'organizacion-cocina-y-closet' || str_contains($slug, 'herrajes-para-cocina') => '<rect x="3" y="8" width="18" height="11" rx="2" fill="currentColor" fill-opacity="0.14" stroke="currentColor" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12h18M7.5 8v11M12 8v11M16.5 8v11M6 8V5.5A1.5 1.5 0 017.5 4h9A1.5 1.5 0 0118 5.5V8"/>',
            str_contains($slug, 'portacubiertos-y-basureros') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 7h14l-1.2 12.2A2 2 0 0115.8 21H8.2a2 2 0 01-2-1.8L5 7z" fill="currentColor" fill-opacity="0.14"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.5 7h17M9 7V4.5A1.5 1.5 0 0110.5 3h3A1.5 1.5 0 0115 4.5V7M10 11v6M14 11v6"/>',
            str_contains($slug, 'herrajes-para-closet') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4a2 2 0 10-2 2c0 1.1.9 1.5 2 2.5L4 14a1.5 1.5 0 00.9 2.7h14.2A1.5 1.5 0 0020 14l-8-5.5z" fill="currentColor" fill-opacity="0.15"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16.7V20m5-3.3V20m5-3.3V20"/>',
            str_contains($slug, 'tubos-y-soportes') => '<circle cx="5" cy="12" r="3" fill="currentColor" fill-opacity="0.18" stroke="currentColor" stroke-width="1.8"/><circle cx="19" cy="12" r="3" fill="currentColor" fill-opacity="0.18" stroke="currentColor" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10.5h8M8 13.5h8M5 9V5m14 4V5"/>',
            str_contains($slug, 'percheros-y-ganchos') => '<rect x="3" y="6" width="18" height="4" rx="1.2" fill="currentColor" fill-opacity="0.15" stroke="currentColor" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 10v5a2 2 0 104 0M15 10v5a2 2 0 104 0"/>',

            // 4. Tapacantos, Soportes y Patas (Parent & Subcategories)
            $slug === 'accesorios-para-muebles' || str_contains($slug, 'tapacantos-melamina') => '<circle cx="11" cy="12" r="7.5" fill="currentColor" fill-opacity="0.14" stroke="currentColor" stroke-width="1.8"/><circle cx="11" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M11 19.5h10M16 16.5l5 3"/>',
            str_contains($slug, 'tapa-tornillos') => '<circle cx="8" cy="8" r="3.5" fill="currentColor" fill-opacity="0.18" stroke="currentColor" stroke-width="1.8"/><circle cx="16" cy="8" r="3.5" fill="currentColor" fill-opacity="0.18" stroke="currentColor" stroke-width="1.8"/><circle cx="8" cy="16" r="3.5" fill="currentColor" fill-opacity="0.18" stroke="currentColor" stroke-width="1.8"/><circle cx="16" cy="16" r="3.5" fill="currentColor" fill-opacity="0.18" stroke="currentColor" stroke-width="1.8"/>',
            str_contains($slug, 'escuadras-y-soportes') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 4h4v16H5V4zm4 0h11v4H9V4zm0 4l9 9" fill="currentColor" fill-opacity="0.14"/>',
            str_contains($slug, 'patas-y-ruedas') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5h16M8 5h8l-1 11H9L8 5z" fill="currentColor" fill-opacity="0.15"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 19h12M10 16v3m4-3v3"/>',
            str_contains($slug, 'garruchas-y-ruedas') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 4h14M8 4v4l4 4" /><circle cx="13" cy="15" r="5" fill="currentColor" fill-opacity="0.16" stroke="currentColor" stroke-width="1.8"/><circle cx="13" cy="15" r="1.5" fill="currentColor"/>',

            // 5. Sistemas Corredizos y Rieles (Parent & Subcategories)
            $slug === 'sistemas-corredizos' || str_contains($slug, 'sistemas-corredizos-para-puertas') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5h18"/><circle cx="8" cy="5" r="2" fill="currentColor"/><circle cx="16" cy="5" r="2" fill="currentColor"/><rect x="5" y="9" width="14" height="11" rx="1.5" fill="currentColor" fill-opacity="0.15" stroke="currentColor" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7v2m8-2v2M12 9v11m-3-5.5h1.5m3 0H15"/>',
            str_contains($slug, 'rieles-y-sistemas') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M3 7h18v4H3V7zm0 7h18v3H3v-3z" fill="currentColor" fill-opacity="0.15"/><circle cx="7" cy="9" r="1" fill="currentColor"/><circle cx="12" cy="9" r="1" fill="currentColor"/><circle cx="17" cy="9" r="1" fill="currentColor"/>',

            // 6. Cerraduras, Topes y Rejillas (Parent & Subcategories)
            $slug === 'chapas-cerraduras-y-otros' || str_contains($slug, 'cerraduras-y-cerrojos') => '<rect x="5" y="10" width="14" height="10" rx="2" fill="currentColor" fill-opacity="0.15" stroke="currentColor" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10V7a4 4 0 118 0v3m-4 4v3"/><circle cx="12" cy="14" r="1.2" fill="currentColor"/>',
            str_contains($slug, 'topes-y-accesorios') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 18h16M7 18a5 5 0 0110 0H7z" fill="currentColor" fill-opacity="0.16"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4"/>',
            str_contains($slug, 'rejillas') => '<rect x="3" y="6" width="18" height="12" rx="1.5" fill="currentColor" fill-opacity="0.14" stroke="currentColor" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.5 9.5h11M6.5 12h11M6.5 14.5h11"/>',

            // 7. Fijaciones, Tornillería y Ensamble (Parent & Subcategories)
            $slug === 'tornillos-tarugos-y-otros' || str_contains($slug, 'tornillos') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 4h10l-2 3.5H9L7 4z" fill="currentColor" fill-opacity="0.18"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 7.5v10L12 20l2-2.5v-10M9 10.5h6M9 13.5h6M9.5 16.5h5"/>',
            str_contains($slug, 'angulos-y-uniones') => '<circle cx="9" cy="12" r="5" fill="currentColor" fill-opacity="0.16" stroke="currentColor" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 10v4m-2-2h4m3 0h6m-2-2v4"/>',
            str_contains($slug, 'tarugos') => '<rect x="8" y="4" width="8" height="15" rx="1.5" fill="currentColor" fill-opacity="0.15" stroke="currentColor" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 4h12M12 9v10M8 9l-2 2m10-2l2 2M8 14l-2 2m10-2l2 2"/>',

            // 8. Iluminación LED, Tomas y Pasacables (Parent & Subcategories)
            $slug === 'electricidad' || str_contains($slug, 'transformadores-cintas-led') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 18h6m-5 3h4M12 2v2m7.07 2.93l-1.41 1.41M22 12h-2M4 12H2m3.34-5.66L3.93 4.93"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6a5 5 0 00-3 9v2a1 1 0 001 1h4a1 1 0 001-1v-2a5 5 0 00-3-9z" fill="currentColor" fill-opacity="0.16"/>',
            str_contains($slug, 'tomas-multimedia') => '<rect x="3" y="6" width="18" height="12" rx="2" fill="currentColor" fill-opacity="0.14" stroke="currentColor" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 10.5v3M10.5 10.5v3M14.5 10.5h3v3h-3z"/>',
            str_contains($slug, 'pasacables') => '<circle cx="12" cy="12" r="8" fill="currentColor" fill-opacity="0.14" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="4.5" stroke="currentColor" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 7.5v3"/>',
            str_contains($slug, 'perfil-para-led') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 15l4-6h14l-4 6H3z" fill="currentColor" fill-opacity="0.16"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 6l1-2m4 2l1-2m4 2l1-2"/>',

            // 9. Pegamentos, Siliconas y Químicos (Parent & Subcategories)
            $slug === 'pegamentos-y-acabados' || str_contains($slug, 'pegamentos-de-contacto') => '<rect x="6" y="8" width="12" height="12" rx="2" fill="currentColor" fill-opacity="0.15" stroke="currentColor" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 8V5.5A1.5 1.5 0 0110.5 4h3A1.5 1.5 0 0115 5.5V8M6 13h12"/>',
            str_contains($slug, 'siliconas') => '<rect x="8" y="8" width="8" height="12" rx="1.5" fill="currentColor" fill-opacity="0.15" stroke="currentColor" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.5 8l1.5-5 1.5 5M8 13h8"/>',
            str_contains($slug, 'pintura-en-spray') => '<rect x="7" y="9" width="10" height="11" rx="2" fill="currentColor" fill-opacity="0.15" stroke="currentColor" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 9V6h4v3m-2-3V4m4 1l3-1m-3 2.5h3"/>',

            // 10. Herramientas, Brocas y Plantillas (Parent & Subcategories)
            $slug === 'herramientas-manuales' || str_contains($slug, 'cutters-y-galopas') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z" fill="currentColor" fill-opacity="0.14"/>',
            str_contains($slug, 'brocas-y-copas') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 3h4v4h-4V3zm-2 4h8l-1 10-3 4-3-4L8 7z" fill="currentColor" fill-opacity="0.15"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 10l6 2m-6 3l6 2"/>',
            str_contains($slug, 'plantillas-y-medicion') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 20h16V4L4 20zm4-3h8v-5l-8 5z" fill="currentColor" fill-opacity="0.15"/>',

            default => '<rect x="4" y="4" width="16" height="16" rx="2" fill="currentColor" fill-opacity="0.14" stroke="currentColor" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16M4 12h16"/>',
        };

        return '<svg class="'.e($class).'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">'.$svgInner.'</svg>';
    }

    /**
     * Return a visual theme palette for category icon cards on the Home page.
     *
     * @return array{code: string, sector: string, bar: string, iconBg: string, iconRing: string, badge: string, glow: string}
     */
    public function iconTheme(): array
    {
        $slug = strtolower((string) $this->slug);

        return match ($slug) {
            'herrajes-para-muebles' => [
                'code' => 'LIN-01',
                'sector' => 'muebleria',
                'bar' => 'from-[#1E3A8A] via-[#1D4ED8] to-[#DC2626]',
                'iconBg' => 'bg-gradient-to-br from-[#1E3A8A] to-[#0F172A] text-white',
                'iconRing' => 'ring-blue-500/20 group-hover:ring-blue-500/50',
                'badge' => 'bg-blue-50 text-[#1E3A8A] border-blue-200',
                'glow' => 'bg-blue-500/15',
            ],
            'tiradores-jaladores-y-perillas' => [
                'code' => 'LIN-02',
                'sector' => 'muebleria',
                'bar' => 'from-[#DC2626] via-[#B91C1C] to-[#1E3A8A]',
                'iconBg' => 'bg-gradient-to-br from-[#DC2626] to-[#991B1B] text-white',
                'iconRing' => 'ring-red-500/20 group-hover:ring-red-500/50',
                'badge' => 'bg-red-50 text-[#DC2626] border-red-200',
                'glow' => 'bg-red-500/15',
            ],
            'organizacion-cocina-y-closet' => [
                'code' => 'LIN-03',
                'sector' => 'muebleria',
                'bar' => 'from-[#0F172A] via-[#1E3A8A] to-amber-500',
                'iconBg' => 'bg-gradient-to-br from-[#0F172A] to-[#1E3A8A] text-amber-400',
                'iconRing' => 'ring-amber-500/25 group-hover:ring-amber-500/50',
                'badge' => 'bg-amber-50 text-amber-800 border-amber-200',
                'glow' => 'bg-amber-500/15',
            ],
            'accesorios-para-muebles' => [
                'code' => 'LIN-04',
                'sector' => 'muebleria',
                'bar' => 'from-[#1D4ED8] to-sky-500',
                'iconBg' => 'bg-gradient-to-br from-[#1D4ED8] to-[#1E3A8A] text-white',
                'iconRing' => 'ring-sky-500/20 group-hover:ring-sky-500/50',
                'badge' => 'bg-sky-50 text-sky-800 border-sky-200',
                'glow' => 'bg-sky-500/15',
            ],
            'sistemas-corredizos' => [
                'code' => 'LIN-05',
                'sector' => 'muebleria',
                'bar' => 'from-[#0F172A] to-[#1D4ED8]',
                'iconBg' => 'bg-gradient-to-br from-slate-800 to-[#0F172A] text-sky-300',
                'iconRing' => 'ring-slate-400/25 group-hover:ring-blue-500/50',
                'badge' => 'bg-slate-100 text-slate-800 border-slate-300',
                'glow' => 'bg-blue-600/15',
            ],
            'chapas-cerraduras-y-otros' => [
                'code' => 'LIN-06',
                'sector' => 'cerrajeria',
                'bar' => 'from-[#DC2626] to-amber-500',
                'iconBg' => 'bg-gradient-to-br from-[#B91C1C] to-[#7F1D1D] text-amber-300',
                'iconRing' => 'ring-red-500/20 group-hover:ring-red-500/50',
                'badge' => 'bg-red-50 text-[#B91C1C] border-red-200',
                'glow' => 'bg-red-500/15',
            ],
            'tornillos-tarugos-y-otros' => [
                'code' => 'LIN-07',
                'sector' => 'cerrajeria',
                'bar' => 'from-slate-700 to-[#1E3A8A]',
                'iconBg' => 'bg-gradient-to-br from-slate-700 to-slate-900 text-white',
                'iconRing' => 'ring-slate-400/25 group-hover:ring-slate-500/50',
                'badge' => 'bg-slate-100 text-slate-700 border-slate-300',
                'glow' => 'bg-slate-500/15',
            ],
            'electricidad' => [
                'code' => 'LIN-08',
                'sector' => 'iluminacion',
                'bar' => 'from-amber-500 to-[#DC2626]',
                'iconBg' => 'bg-gradient-to-br from-amber-500 to-orange-600 text-white',
                'iconRing' => 'ring-amber-400/30 group-hover:ring-amber-500/60',
                'badge' => 'bg-amber-50 text-amber-800 border-amber-200',
                'glow' => 'bg-amber-500/15',
            ],
            'pegamentos-y-acabados' => [
                'code' => 'LIN-09',
                'sector' => 'cerrajeria',
                'bar' => 'from-emerald-600 to-teal-600',
                'iconBg' => 'bg-gradient-to-br from-emerald-600 to-teal-800 text-white',
                'iconRing' => 'ring-emerald-500/20 group-hover:ring-emerald-500/50',
                'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'glow' => 'bg-emerald-500/15',
            ],
            default => [
                'code' => 'LIN-10',
                'sector' => 'herramientas',
                'bar' => 'from-[#1E3A8A] to-indigo-600',
                'iconBg' => 'bg-gradient-to-br from-indigo-700 to-[#0F172A] text-white',
                'iconRing' => 'ring-indigo-500/20 group-hover:ring-indigo-500/50',
                'badge' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                'glow' => 'bg-indigo-500/15',
            ],
        };
    }
}
