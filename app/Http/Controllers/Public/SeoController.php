<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\PageContent;
use App\Models\Product;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    /**
     * Dynamic XML Sitemap including indexable pages, corporate sections, active categories, verified brands, and published products.
     */
    public function sitemap(): Response
    {
        $indexablePages = PageContent::query()
            ->where('is_indexable', true)
            ->get()
            ->keyBy('page_key');

        $companySections = [
            'nosotros-experiencia',
            'especialidades-b2b',
            'certificaciones',
            'cobertura-nacional',
            'nuestro-equipo',
            'marcas-representadas',
            'politicas-atencion-corporativa',
            'casos-y-proyectos-reales',
        ];

        $staticRoutes = [
            ['key' => 'home', 'url' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['key' => null, 'url' => route('catalog.index'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['key' => null, 'url' => route('quote.index'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['key' => 'about', 'url' => route('pages.company'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['key' => 'about', 'url' => route('pages.about'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['key' => null, 'url' => route('pages.testimonials'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['key' => null, 'url' => route('contact.index'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['key' => 'privacy_policy', 'url' => route('pages.privacy'), 'priority' => '0.4', 'changefreq' => 'yearly'],
            ['key' => 'cookies_policy', 'url' => route('pages.cookies'), 'priority' => '0.4', 'changefreq' => 'yearly'],
            ['key' => 'terms_conditions', 'url' => route('pages.terms'), 'priority' => '0.4', 'changefreq' => 'yearly'],
            ['key' => 'quote_conditions', 'url' => route('pages.quote-conditions'), 'priority' => '0.5', 'changefreq' => 'yearly'],
            ['key' => null, 'url' => route('pages.complaints-book'), 'priority' => '0.5', 'changefreq' => 'yearly'],
        ];

        foreach ($companySections as $sec) {
            $staticRoutes[] = [
                'key' => 'about',
                'url' => route('pages.company', ['section' => $sec]),
                'priority' => '0.7',
                'changefreq' => 'monthly',
            ];
        }

        $urls = [];
        foreach ($staticRoutes as $item) {
            if ($item['key'] !== null && $indexablePages->has($item['key']) === false) {
                $pageRecord = PageContent::query()->where('page_key', $item['key'])->first();
                if ($pageRecord && ! $pageRecord->is_indexable) {
                    continue;
                }
            }
            $lastmod = ($item['key'] && $indexablePages->get($item['key'])?->updated_at)
                ? $indexablePages->get($item['key'])->updated_at->toAtomString()
                : now()->toAtomString();

            $urls[] = [
                'loc' => $item['url'],
                'lastmod' => $lastmod,
                'changefreq' => $item['changefreq'],
                'priority' => $item['priority'],
            ];
        }

        // Active Categories & Subcategories (matching canonical URL structure)
        $categories = Category::query()
            ->with('parent')
            ->where('is_active', true)
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->get();

        foreach ($categories as $category) {
            $catUrl = ($category->parent_id !== null && $category->parent)
                ? route('catalog.index', ['category' => $category->parent->slug, 'subcategory' => $category->slug])
                : route('catalog.index', ['category' => $category->slug]);

            $urls[] = [
                'loc' => $catUrl,
                'lastmod' => ($category->updated_at ?? now())->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => $category->parent_id === null ? '0.8' : '0.7',
            ];
        }

        // Active Verified Brands with published products
        $brands = Brand::query()
            ->where('is_active', true)
            ->whereHas('products', fn ($q) => $q->where('status', 'published'))
            ->orderBy('name')
            ->get(['slug', 'updated_at']);

        foreach ($brands as $brand) {
            $urls[] = [
                'loc' => route('catalog.index', ['brand' => $brand->slug]),
                'lastmod' => ($brand->updated_at ?? now())->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        // Published Products
        $products = Product::query()
            ->where('status', 'published')
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at']);

        foreach ($products as $product) {
            $urls[] = [
                'loc' => route('catalog.show', $product->slug),
                'lastmod' => ($product->updated_at ?? now())->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $u) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($u['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</loc>\n";
            $xml .= '    <lastmod>'.htmlspecialchars($u['lastmod'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</lastmod>\n";
            $xml .= '    <changefreq>'.$u['changefreq']."</changefreq>\n";
            $xml .= '    <priority>'.$u['priority']."</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Dynamic robots.txt blocking /admin and non-indexable endpoints, pointing to sitemap.xml.
     */
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /admin/',
            'Disallow: /mi-cuenta',
            'Disallow: /login',
            'Disallow: /registro',
            'Disallow: /recuperar-contrasena',
            'Disallow: /catalogo/sugerencias',
            'Disallow: /catalogo/comparar',
            'Disallow: /cotizacion/confirmacion/',
            'Disallow: /cotizacion/seguimiento',
            'Disallow: /*?sort=',
            'Disallow: /*&sort=',
            'Disallow: /*?view=',
            'Disallow: /*&view=',
            '',
            'Sitemap: '.route('seo.sitemap'),
        ];

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
