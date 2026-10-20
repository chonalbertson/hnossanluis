<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\HeroBanner;
use App\Models\PageContent;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsAppService
    ) {}

    public function index(): View
    {
        $homePage = PageContent::query()->where('page_key', 'home')->first();
        $aboutPage = PageContent::query()->where('page_key', 'about')->first();

        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with([
                'children' => fn ($q) => $q->where('is_active', true)
                    ->withCount(['subProducts' => fn ($sq) => $sq->where('status', 'published')])
                    ->orderBy('sort_order'),
            ])
            ->withCount([
                'products' => fn ($q) => $q->where('status', 'published'),
            ])
            ->orderBy('sort_order')
            ->get();

        $baseProductQuery = fn () => Product::query()
            ->with(['category', 'subcategory', 'brand', 'images', 'specifications', 'documents', 'variants'])
            ->where('status', 'published');

        $featuredProducts = $baseProductQuery()
            ->whereHas('images')
            ->inRandomOrder()
            ->limit(8)
            ->get();

        if ($featuredProducts->count() < 8) {
            $featuredProducts = $baseProductQuery()
                ->inRandomOrder()
                ->limit(8)
                ->get();
        }

        // Hero visual showcase: 4 random products with images across key categories
        $heroProducts = $baseProductQuery()
            ->whereHas('images')
            ->inRandomOrder()
            ->limit(4)
            ->get();

        // Interactive specialty tabs for the Home product showcase (randomized on every load)
        $herrajesCatIds = Category::query()
            ->whereIn('slug', [
                'herrajes-para-muebles',
                'tiradores-jaladores-y-perillas',
                'organizacion-cocina-y-closet',
                'accesorios-para-muebles',
                'sistemas-corredizos',
            ])
            ->pluck('id');

        $seguridadCatIds = Category::query()
            ->whereIn('slug', ['chapas-cerraduras-y-otros', 'tornillos-tarugos-y-otros', 'pegamentos-y-acabados'])
            ->pluck('id');

        $herramientasCatIds = Category::query()
            ->whereIn('slug', [
                'herramientas-manuales',
                'electricidad',
            ])
            ->pluck('id');

        $tabbedProducts = [
            'destacados' => $featuredProducts,
            'herrajes' => $baseProductQuery()
                ->whereIn('category_id', $herrajesCatIds)
                ->whereHas('images')
                ->inRandomOrder()
                ->limit(8)
                ->get(),
            'cerraduras' => $baseProductQuery()
                ->whereIn('category_id', $seguridadCatIds)
                ->whereHas('images')
                ->inRandomOrder()
                ->limit(8)
                ->get(),
            'herramientas' => $baseProductQuery()
                ->whereIn('category_id', $herramientasCatIds)
                ->whereHas('images')
                ->inRandomOrder()
                ->limit(8)
                ->get(),
        ];

        $verifiedBrands = Brand::query()
            ->where('is_active', true)
            ->where('is_verified', true)
            ->whereHas('products', fn ($q) => $q->where('status', 'published'))
            ->withCount([
                'products' => fn ($q) => $q->where('status', 'published'),
            ])
            ->orderByDesc('products_count')
            ->orderBy('name')
            ->limit(18)
            ->get();

        $promoProducts = $baseProductQuery()
            ->whereHas('images')
            ->whereNotIn('id', $featuredProducts->pluck('id'))
            ->inRandomOrder()
            ->limit(8)
            ->get();

        if ($promoProducts->count() < 8) {
            $promoProducts = $baseProductQuery()
                ->whereHas('images')
                ->inRandomOrder()
                ->limit(8)
                ->get();
        }

        // Only show verified and active testimonials; section hides automatically if empty
        $testimonials = Testimonial::query()
            ->where('is_active', true)
            ->where('is_verified', true)
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        $catalogStats = [
            'products' => Product::query()->where('status', 'published')->count(),
            'categories' => $categories->count(),
            'subcategories' => Category::query()->whereNotNull('parent_id')->where('is_active', true)->count(),
            'brands' => Brand::query()->where('is_active', true)->count(),
        ];

        $carouselBrandLogos = [
            ['slug' => 'ducasse', 'name' => 'Ducasse Industrial', 'webp' => asset('images/brands/logo-ducasse.webp'), 'png' => asset('images/brands/logo-ducasse.png'), 'url' => route('catalog.index', ['brand' => 'ducasse'])],
            ['slug' => 'halcon', 'name' => 'Halcon', 'webp' => asset('images/brands/logo-halcon.webp'), 'png' => asset('images/brands/logo-halcon.png'), 'url' => route('catalog.index', ['brand' => 'halcon'])],
            ['slug' => 'danco', 'name' => 'Danco Made in Italy', 'webp' => asset('images/brands/logo-danco.webp'), 'png' => asset('images/brands/logo-danco.png'), 'url' => route('catalog.index', ['brand' => 'danco'])],
            ['slug' => 'truper', 'name' => 'Truper', 'webp' => asset('images/brands/logo-truper.webp'), 'png' => asset('images/brands/logo-truper.png'), 'url' => route('catalog.index')],
            ['slug' => 'afix', 'name' => 'Afix', 'webp' => asset('images/brands/logo-afix.webp'), 'png' => asset('images/brands/logo-afix.png'), 'url' => route('catalog.index', ['brand' => 'afix'])],
            ['slug' => 'dmueble', 'name' => 'Dmueble', 'webp' => asset('images/brands/logo-dmueble.webp'), 'png' => asset('images/brands/logo-dmueble.png'), 'url' => route('catalog.index', ['brand' => 'dmueble'])],
            ['slug' => 'indeco', 'name' => 'Indeco', 'webp' => asset('images/brands/logo-indeco.webp'), 'png' => asset('images/brands/logo-indeco.png'), 'url' => route('catalog.index', ['brand' => 'indeco'])],
            ['slug' => 'travex', 'name' => 'Travex', 'webp' => asset('images/brands/logo-travex.webp'), 'png' => asset('images/brands/logo-travex.png'), 'url' => route('catalog.index', ['brand' => 'travex'])],
            ['slug' => 'bticino', 'name' => 'Bticino', 'webp' => asset('images/brands/logo-bticino.webp'), 'png' => asset('images/brands/logo-bticino.png'), 'url' => route('catalog.index', ['brand' => 'bticino'])],
        ];

        $tiradoresProducts = $baseProductQuery()
            ->whereHas('category', fn ($q) => $q->whereIn('slug', ['tiradores-jaladores-y-perillas', 'organizacion-cocina-y-closet']))
            ->whereHas('images')
            ->inRandomOrder()
            ->limit(3)
            ->get();

        $productPools = [
            'destacados' => $heroProducts->take(3),
            'herrajes' => $tabbedProducts['herrajes']->take(3),
            'tiradores' => $tiradoresProducts,
            'cerraduras' => $tabbedProducts['cerraduras']->take(3),
            'herramientas' => $tabbedProducts['herramientas']->take(3),
            'none' => collect(),
        ];

        $heroSlides = [];

        if (Schema::hasTable('hero_banners')) {
            if (HeroBanner::query()->count() === 0) {
                foreach (HeroBanner::defaultSlidesDefinition() as $defaultSlide) {
                    HeroBanner::query()->create($defaultSlide);
                }
            }

            $activeBanners = HeroBanner::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            foreach ($activeBanners as $banner) {
                $sourceKey = $banner->product_source ?: 'destacados';
                $slideProducts = ($banner->show_products_showcase && $sourceKey !== 'none')
                    ? ($productPools[$sourceKey] ?? $productPools['destacados'])
                    : collect();

                $heroSlides[] = [
                    'id' => $banner->id,
                    'label' => $banner->label,
                    'eyebrow' => $banner->eyebrow,
                    'badge_metric' => $banner->badge_metric,
                    'title' => $banner->title,
                    'highlight' => $banner->highlight,
                    'suffix' => $banner->suffix,
                    'text' => $banner->description,
                    'background_image_url' => $banner->backgroundImageUrl(),
                    'mobile_background_image_url' => $banner->mobileBackgroundImageUrl(),
                    'overlay_opacity' => $banner->overlay_opacity ?? 75,
                    'show_products_showcase' => (bool) $banner->show_products_showcase && $sourceKey !== 'none',
                    'primary' => [
                        'label' => $banner->primary_cta_label ?: 'Explorar Catálogo',
                        'url' => $banner->primary_cta_url ?: route('catalog.index'),
                    ],
                    'secondary' => [
                        'label' => $banner->secondary_cta_label ?: 'Cotizar Lista',
                        'url' => $banner->secondary_cta_url ?: route('quote.index'),
                    ],
                    'accent' => $banner->accent ?: 'red',
                    'products' => $slideProducts,
                ];
            }
        }

        if (empty($heroSlides)) {
            $heroSlides = [
                [
                    'label' => 'Catálogo General',
                    'eyebrow' => 'Import Hnos San Luis S.A.C. • Mayorista y Minorista',
                    'badge_metric' => '+1,330 SKUs Verificados',
                    'title' => 'Herrajes, Tiradores y',
                    'highlight' => 'Ferretería Profesional',
                    'suffix' => 'para tus Proyectos',
                    'text' => 'Abastecimiento técnico con cotizaciones por volumen (RUC) y por unidad para mueblería, carpintería en melamina, contratistas y obras en todo el Perú.',
                    'background_image_url' => null,
                    'mobile_background_image_url' => null,
                    'overlay_opacity' => 75,
                    'show_products_showcase' => true,
                    'primary' => ['label' => 'Explorar Catálogo', 'url' => route('catalog.index')],
                    'secondary' => ['label' => 'Cotizar Lista', 'url' => route('quote.index')],
                    'accent' => 'red',
                    'products' => $heroProducts->take(3),
                ],
                [
                    'label' => 'Herrajes y Corredizos',
                    'eyebrow' => 'Ingeniería en Movimiento para Mobiliario',
                    'badge_metric' => 'Cierre Suave & Push Open',
                    'title' => 'Bisagras, Correderas y',
                    'highlight' => 'Sistemas Corredizos',
                    'suffix' => 'de Alta Precisión',
                    'text' => 'Bisagras cazoleta cierre lento, correderas telescópicas y ocultas, sistemas corredizos Ducasse, pistones a gas y tapacantos con stock permanente.',
                    'background_image_url' => null,
                    'mobile_background_image_url' => null,
                    'overlay_opacity' => 75,
                    'show_products_showcase' => true,
                    'primary' => ['label' => 'Ver Herrajes y Bisagras', 'url' => route('catalog.index', ['category' => 'herrajes-para-muebles'])],
                    'secondary' => ['label' => 'Sistemas Corredizos', 'url' => route('catalog.index', ['category' => 'sistemas-corredizos'])],
                    'accent' => 'blue',
                    'products' => $tabbedProducts['herrajes']->take(3),
                ],
                [
                    'label' => 'Tiradores y Cocina',
                    'eyebrow' => 'Diseño Arquitectónico e Interiorismo',
                    'badge_metric' => '+480 Diseños en Stock',
                    'title' => 'Tiradores, Perfiles Gola y',
                    'highlight' => 'Organización de Cocina',
                    'suffix' => '& Clóset',
                    'text' => 'Colección exclusiva de jaladores en acero inoxidable y aluminio, perillas, condimenteros extraíbles, basureros, pantaloneros y accesorios de clóset.',
                    'background_image_url' => null,
                    'mobile_background_image_url' => null,
                    'overlay_opacity' => 75,
                    'show_products_showcase' => true,
                    'primary' => ['label' => 'Tiradores y Jaladores', 'url' => route('catalog.index', ['category' => 'tiradores-jaladores-y-perillas'])],
                    'secondary' => ['label' => 'Cocina y Clóset', 'url' => route('catalog.index', ['category' => 'organizacion-cocina-y-closet'])],
                    'accent' => 'amber',
                    'products' => $tiradoresProducts,
                ],
                [
                    'label' => 'Cerraduras, LED y Taller',
                    'eyebrow' => 'Seguridad, Iluminación LED y Ensamble',
                    'badge_metric' => 'Grado Industrial B2B',
                    'title' => 'Cerraduras, Fijaciones,',
                    'highlight' => 'Iluminación LED y Taller',
                    'suffix' => '',
                    'text' => 'Cerraduras y topes en acero inoxidable, conectores Minifix, cintas LED y tomas multimedia empotrables, pegamentos MDF y plantillas de carpintería.',
                    'background_image_url' => null,
                    'mobile_background_image_url' => null,
                    'overlay_opacity' => 75,
                    'show_products_showcase' => true,
                    'primary' => ['label' => 'Cerraduras y Fijación', 'url' => route('catalog.index', ['category' => 'chapas-cerraduras-y-otros'])],
                    'secondary' => ['label' => 'Iluminación y Tomas', 'url' => route('catalog.index', ['category' => 'electricidad'])],
                    'accent' => 'emerald',
                    'products' => $tabbedProducts['cerraduras']->take(3),
                ],
            ];
        }

        $seoTitle = $homePage?->meta_title
            ?: (string) SiteSetting::getValue('seo_default_title', 'Import Hns San Luis | Herrajes, Cerraduras y Ferretería en Perú');
        $seoDescription = $homePage?->meta_description
            ?: (string) SiteSetting::getValue('seo_default_description', 'Catálogo profesional B2B y B2C de herrajes para muebles, sistemas corredizos, cerraduras, fijaciones y herramientas en Perú.');

        return view('public.home', [
            'homePage' => $homePage,
            'aboutPage' => $aboutPage,
            'categories' => $categories,
            'heroProducts' => $heroProducts,
            'heroSlides' => $heroSlides,
            'featuredProducts' => $featuredProducts,
            'promoProducts' => $promoProducts,
            'tabbedProducts' => $tabbedProducts,
            'verifiedBrands' => $verifiedBrands,
            'carouselBrandLogos' => $carouselBrandLogos,
            'testimonials' => $testimonials,
            'catalogStats' => $catalogStats,
            'whatsappUrl' => $this->whatsAppService->generalContactUrl(),
            'seo' => [
                'title' => $seoTitle,
                'description' => $seoDescription,
                'canonical' => route('home'),
                'robots' => ($homePage === null || $homePage->is_indexable) ? 'index, follow' : 'noindex, follow',
                'include_website_schema' => true,
            ],
        ]);
    }
}
