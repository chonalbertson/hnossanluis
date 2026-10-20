<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Extrae el catálogo público de italocks.com (listado AJAX por categoría + ficha de cada producto).
 */
class ItalocksScraperService
{
    public const BASE_URL = 'https://italocks.com';

    /**
     * Categorías del sitio origen (ID del filtro AJAX => nombre visible).
     *
     * @var array<int, string>
     */
    public const SOURCE_CATEGORIES = [
        1 => 'Artículos para Puertas',
        2 => 'Línea Mueblista',
        3 => 'Accesorios de Baño',
        5 => 'Sistemas LED',
        6 => 'Espejos LED',
        7 => 'Manillones',
        8 => 'Luminarias',
        9 => 'Cerraduras',
        10 => 'Accesorios Mueblista',
        11 => 'Tiradores',
        12 => 'Revestimientos',
    ];

    public function __construct(protected int $delayMilliseconds = 250) {}

    /**
     * Recorre todas las categorías y devuelve los productos con su ficha técnica completa.
     *
     * @param  callable(string): void|null  $progress
     * @return list<array{source_url: string, source_category: string, name: string, sku: string, short_description: string, brand: ?string, materials: list<string>, colors: list<string>, sizes: list<string>, image_urls: list<string>}>
     */
    public function scrapeCatalog(?callable $progress = null, ?int $limit = null): array
    {
        $productUrls = [];

        foreach (self::SOURCE_CATEGORIES as $categoryId => $categoryName) {
            foreach ($this->listCategoryProductUrls($categoryId) as $url) {
                $productUrls[$url] ??= $categoryName;
            }
            $progress && $progress("Categoría {$categoryName}: ".count(array_keys($productUrls, $categoryName, true)).' productos');
        }

        foreach ($this->listCategoryProductUrls(null) as $url) {
            $productUrls[$url] ??= 'Sin categoría';
        }

        if ($limit !== null) {
            $productUrls = array_slice($productUrls, 0, $limit, true);
        }

        $products = [];
        foreach ($productUrls as $url => $categoryName) {
            $detail = $this->scrapeProduct($url);
            if ($detail !== null) {
                $products[] = ['source_category' => $categoryName] + $detail;
                $progress && $progress('Ficha: '.$detail['name']);
            }
        }

        return $products;
    }

    /**
     * Devuelve las URLs de producto de una categoría recorriendo toda su paginación AJAX.
     *
     * @return list<string>
     */
    public function listCategoryProductUrls(?int $categoryId): array
    {
        $urls = [];
        $page = 1;
        $lastPage = 1;

        do {
            $html = $this->get(self::BASE_URL.'/ajax/cargar-productos/', [
                'pagina' => $page,
                'categoria' => $categoryId ?? '',
                'marca' => '',
            ], ajax: true);

            if ($html === null) {
                break;
            }

            preg_match_all('#class="product-title"\s+href="([^"]+)"#', $html, $matches);
            foreach ($matches[1] as $url) {
                $urls[] = rtrim(html_entity_decode($url), '/').'/';
            }

            preg_match_all('#loadPage\((\d+)\)#', $html, $pageMatches);
            $lastPage = max([$lastPage, ...array_map('intval', $pageMatches[1])]);
            $page++;
        } while ($page <= $lastPage && $matches[1] !== []);

        return array_values(array_unique($urls));
    }

    /**
     * @return array{source_url: string, name: string, sku: string, short_description: string, brand: ?string, materials: list<string>, colors: list<string>, sizes: list<string>, image_urls: list<string>}|null
     */
    public function scrapeProduct(string $url): ?array
    {
        $html = $this->get($url);

        return $html === null ? null : $this->parseProductHtml($html, $url);
    }

    /**
     * @return array{source_url: string, name: string, sku: string, short_description: string, brand: ?string, materials: list<string>, colors: list<string>, sizes: list<string>, image_urls: list<string>}|null
     */
    public function parseProductHtml(string $html, string $url): ?array
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);

        $titleNode = $xpath->query('//h2[contains(@class,"product_title")]')->item(0);
        if (! $titleNode instanceof DOMElement) {
            return null;
        }

        $skuNode = $xpath->query('.//small', $titleNode)->item(0);
        $sku = $skuNode ? $this->clean($skuNode->textContent) : '';
        if ($skuNode) {
            $skuNode->parentNode->removeChild($skuNode);
        }
        $name = $this->clean($titleNode->textContent);

        $attributesRoot = '//div[contains(@class,"product-attributes-section") and contains(@class,"d-lg-block")]';

        $imageUrls = [];
        foreach ($xpath->query('//ul[contains(@class,"easyzoom-gallery-slider")]//a/@href | //div[contains(@class,"easyzoom")]/a/@href') as $href) {
            $imageUrl = $this->absoluteUrl(trim($href->nodeValue));
            if ($imageUrl !== '' && ! in_array($imageUrl, $imageUrls, true)) {
                $imageUrls[] = $imageUrl;
            }
        }

        return [
            'source_url' => $url,
            'name' => $name,
            'sku' => $sku !== '' ? $sku : 'IT-'.strtoupper(Str::limit(basename(rtrim($url, '/')), 40, '')),
            'short_description' => $this->clean((string) $xpath->query('//div[contains(@class,"product-details__short-description")]')->item(0)?->textContent),
            'brand' => $this->clean((string) $xpath->query($attributesRoot.'//span[contains(@class,"brand-badge")]')->item(0)?->textContent) ?: null,
            'materials' => $this->texts($xpath, $attributesRoot.'//span[contains(@class,"material-tag")]'),
            'colors' => $this->texts($xpath, $attributesRoot.'//span[contains(@class,"color-circle")]/@data-tooltip'),
            'sizes' => $this->texts($xpath, $attributesRoot.'//span[contains(@class,"size-button")]'),
            'image_urls' => $imageUrls,
        ];
    }

    /**
     * Descarga el binario de una imagen (o null si falla).
     */
    public function download(string $url): ?string
    {
        $response = Http::withHeaders($this->headers())->timeout(60)->retry(2, 500, throw: false)->get($url);
        usleep($this->delayMilliseconds * 1000);

        return $response->successful() ? $response->body() : null;
    }

    /**
     * @param  array<string, mixed>  $query
     */
    protected function get(string $url, array $query = [], bool $ajax = false): ?string
    {
        $headers = $this->headers() + ($ajax ? ['X-Requested-With' => 'XMLHttpRequest'] : []);
        $response = Http::withHeaders($headers)->timeout(30)->retry(3, 800, throw: false)->get($url, $query);
        usleep($this->delayMilliseconds * 1000);

        return $response->successful() ? $response->body() : null;
    }

    /**
     * @return array<string, string>
     */
    protected function headers(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
            'Accept-Language' => 'es-PE,es;q=0.9',
        ];
    }

    /**
     * @return list<string>
     */
    protected function texts(DOMXPath $xpath, string $query): array
    {
        $values = [];
        foreach ($xpath->query($query) as $node) {
            $value = $this->clean($node->nodeValue ?? $node->textContent);
            if ($value !== '' && ! in_array(mb_strtolower($value), array_map('mb_strtolower', $values), true)) {
                $values[] = $value;
            }
        }

        return $values;
    }

    protected function clean(string $text): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    protected function absoluteUrl(string $url): string
    {
        if ($url === '' || str_starts_with($url, 'http')) {
            return $url;
        }

        return self::BASE_URL.'/'.ltrim($url, '/');
    }
}
