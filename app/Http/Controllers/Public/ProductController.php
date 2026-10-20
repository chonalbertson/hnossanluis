<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Services\ActivityLoggerService;
use App\Services\WhatsAppService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsAppService,
        protected ActivityLoggerService $activityLogger
    ) {}

    public function show(Request $request, string $slug): View
    {
        $product = Product::query()
            ->with(['category', 'subcategory', 'brand', 'images', 'specifications', 'documents', 'variants'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        Product::withoutTimestamps(fn () => $product->increment('views_count'));

        $groupedSpecs = $product->specifications->groupBy(
            fn ($spec) => $spec->spec_group ?: 'Especificaciones Técnicas'
        );

        $activeVariant = null;
        if ($product->variants->isNotEmpty()) {
            if ($request->filled('variant_id')) {
                $activeVariant = $product->variants->firstWhere('id', (int) $request->query('variant_id'));
            } elseif ($request->filled('variant')) {
                $vSku = strtoupper(trim((string) $request->query('variant')));
                $activeVariant = $product->variants->first(fn ($v) => strtoupper($v->sku) === $vSku);
            }
        }

        $relatedProducts = Product::query()
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
            ->with(['category', 'subcategory', 'brand', 'images', 'variants'])
            ->where('status', 'published')
            ->where('id', '!=', $product->id)
            ->where(function ($query) use ($product) {
                if ($product->subcategory_id) {
                    $query->where('subcategory_id', $product->subcategory_id)
                        ->orWhere('category_id', $product->category_id);
                } else {
                    $query->where('category_id', $product->category_id);
                }
            })
            ->inRandomOrder()
            ->limit(4)
            ->get();

        $canonicalUrl = route('catalog.show', $product->slug);
        $whatsappInquiryUrl = $this->whatsAppService->productInquiryUrl($product, $canonicalUrl);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Catálogo Técnico', 'url' => route('catalog.index')],
        ];

        if ($product->category) {
            $breadcrumbs[] = [
                'name' => $product->category->name,
                'url' => route('catalog.index', ['category' => $product->category->slug]),
            ];
        }
        if ($product->subcategory) {
            $breadcrumbs[] = [
                'name' => $product->subcategory->name,
                'url' => route('catalog.index', [
                    'category' => $product->category?->slug,
                    'subcategory' => $product->subcategory->slug,
                ]),
            ];
        }
        $breadcrumbs[] = [
            'name' => $product->name,
            'url' => $canonicalUrl,
        ];

        $seoTitle = $product->meta_title
            ?: sprintf('%s (SKU: %s) | Import Hns San Luis', $product->name, $product->sku);
        $seoDescription = $product->meta_description
            ?: sprintf(
                '%s. Código SKU: %s. Marca: %s. Unidad de venta: %s. Revisa ficha técnica y solicita cotización en línea o por WhatsApp.',
                trim(strip_tags((string) ($product->short_description ?: $product->name))),
                $product->sku,
                $product->brand?->name ?? 'San Luis',
                $product->sale_unit
            );

        return view('public.catalog.show', [
            'product' => $product,
            'activeVariant' => $activeVariant,
            'groupedSpecs' => $groupedSpecs,
            'relatedProducts' => $relatedProducts,
            'whatsappInquiryUrl' => $whatsappInquiryUrl,
            'breadcrumbs' => $breadcrumbs,
            'seo' => [
                'title' => $seoTitle,
                'description' => $seoDescription,
                'canonical' => $canonicalUrl,
                'robots' => 'index, follow',
                'og_type' => 'product',
                'og_image' => $product->primaryImageUrl(),
                'breadcrumbs' => $breadcrumbs,
                'product_schema' => $this->buildProductSchema($product, $canonicalUrl),
            ],
        ]);
    }

    /**
     * Download or generate the technical PDF datasheet for a published product.
     */
    public function downloadPdf(Request $request, string $slug): Response
    {
        $product = Product::query()
            ->with(['category', 'subcategory', 'brand', 'images', 'specifications', 'documents', 'variants'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // 1. Check if a specific ProductDocument ID was requested or if product has attached documents
        $docId = $request->query('document_id');
        $document = $docId
            ? $product->documents->firstWhere('id', (int) $docId)
            : $product->documents->first();

        if ($document) {
            $document->increment('downloads_count');

            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                return Storage::disk('public')->download(
                    $document->file_path,
                    sprintf('Ficha-Tecnica-%s.pdf', $product->sku)
                );
            }
        }

        // 2. Check if product has a direct technical_pdf_path stored on public disk
        if ($product->technical_pdf_path && Storage::disk('public')->exists($product->technical_pdf_path)) {
            return Storage::disk('public')->download(
                $product->technical_pdf_path,
                sprintf('Ficha-Tecnica-%s.pdf', $product->sku)
            );
        }

        // 3. Dynamically generate an official Technical Datasheet PDF with real specs using Dompdf
        $companyName = (string) SiteSetting::getValue('company_commercial_name', 'Import Hnos San Luis');
        $companyLegalName = (string) SiteSetting::getValue('company_legal_name', 'IMPORT HNOS SAN LUIS S.A.C.');
        $companyRuc = (string) SiteSetting::getValue('company_ruc', '20608628143');
        $companyAddress = (string) SiteSetting::getValue('company_address', 'Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú');
        $companyEmail = (string) SiteSetting::getValue('company_contact_email', 'ventas@hnssanluis.com');
        $companyPhone = (string) SiteSetting::getValue('company_contact_phone', '977 208 514');
        $companyHours = (string) SiteSetting::getValue('company_business_hours', 'Lunes a Viernes: 8:30 am - 6:00 pm | Sábados: 9:00 am - 1:00 pm');
        $quoteConditions = (string) SiteSetting::getValue(
            'commercial_default_quote_conditions',
            'Ventas al por mayor y menor. Precios sujetos a confirmación de stock al momento de la orden de compra. Despachos a Lima Metropolitana y agencias de carga a nivel nacional previa coordinación comercial.'
        );

        $rawAdvisors = SiteSetting::getValue('whatsapp_advisors', [
            ['name' => 'Angie', 'phone' => '977 208 514'],
            ['name' => 'Gracia', 'phone' => '933 399 010'],
            ['name' => 'Sabrina', 'phone' => '922 353 855'],
            ['name' => 'Ventas', 'phone' => '977 983 045'],
        ]);
        $whatsappAdvisors = is_array($rawAdvisors) ? $rawAdvisors : [];

        $companyLogoDataUri = $this->resolvePdfImageDataUri(public_path('images/logo-sanluis-horizontal.png'));

        $primaryImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
        $secondaryImage = $product->images
            ->filter(fn ($img) => $primaryImage && $img->id !== $primaryImage->id)
            ->first();

        $primaryImageDataUri = $primaryImage
            ? $this->resolvePublicStorageImageDataUri($primaryImage->thumbnail_path ?: $primaryImage->file_path)
                ?? $this->resolvePublicStorageImageDataUri($primaryImage->file_path)
            : null;

        $secondaryImageDataUri = $secondaryImage
            ? $this->resolvePublicStorageImageDataUri($secondaryImage->thumbnail_path ?: $secondaryImage->file_path)
                ?? $this->resolvePublicStorageImageDataUri($secondaryImage->file_path)
            : null;

        $html = view('public.catalog.pdf-datasheet', [
            'product' => $product,
            'companyName' => $companyName,
            'companyLegalName' => $companyLegalName,
            'companyRuc' => $companyRuc,
            'companyAddress' => $companyAddress,
            'companyEmail' => $companyEmail,
            'companyPhone' => $companyPhone,
            'companyHours' => $companyHours,
            'quoteConditions' => $quoteConditions,
            'whatsappAdvisors' => $whatsappAdvisors,
            'companyLogoDataUri' => $companyLogoDataUri,
            'primaryImageDataUri' => $primaryImageDataUri,
            'secondaryImageDataUri' => $secondaryImageDataUri,
        ])->render();

        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = sprintf('Ficha-Tecnica-%s.pdf', $product->sku);

        return response((string) $dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ]);
    }

    /**
     * Resolve a storage/public relative path or public asset path into a Dompdf-compatible Base64 Data URI.
     */
    protected function resolvePublicStorageImageDataUri(?string $relativePath): ?string
    {
        if (! $relativePath || str_starts_with($relativePath, 'http://') || str_starts_with($relativePath, 'https://')) {
            return null;
        }

        $normalized = ltrim($relativePath, '/');
        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, strlen('storage/'));
        }

        if (Storage::disk('public')->exists($normalized)) {
            return $this->resolvePdfImageDataUri(Storage::disk('public')->path($normalized));
        }

        $publicCandidate = public_path($normalized);
        if (is_file($publicCandidate)) {
            return $this->resolvePdfImageDataUri($publicCandidate);
        }

        return null;
    }

    /**
     * Convert an absolute image path on disk into a Base64 Data URI (converting WebP to PNG for Dompdf compatibility).
     */
    protected function resolvePdfImageDataUri(string $absolutePath): ?string
    {
        if (! is_file($absolutePath) || ! is_readable($absolutePath)) {
            return null;
        }

        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        if ($ext === 'svg') {
            $raw = @file_get_contents($absolutePath);

            return $raw !== false ? 'data:image/svg+xml;base64,'.base64_encode($raw) : null;
        }

        if ($ext === 'webp' && function_exists('imagecreatefromwebp')) {
            $img = @imagecreatefromwebp($absolutePath);
            if ($img !== false) {
                imagepalettetotruecolor($img);
                imagesavealpha($img, true);
                ob_start();
                imagepng($img, null, 6);
                $pngData = ob_get_clean();
                imagedestroy($img);

                if (is_string($pngData) && $pngData !== '') {
                    return 'data:image/png;base64,'.base64_encode($pngData);
                }
            }
        }

        $raw = @file_get_contents($absolutePath);
        if ($raw === false || $raw === '') {
            return null;
        }

        $mime = match ($ext) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };

        return sprintf('data:%s;base64,%s', $mime, base64_encode($raw));
    }

    /**
     * Build Schema.org Product JSON-LD without inventing fake prices or fake reviews.
     */
    protected function buildProductSchema(Product $product, string $canonicalUrl): array
    {
        $additionalProperties = [];

        if ($product->dimensions) {
            $additionalProperties[] = [
                '@type' => 'PropertyValue',
                'name' => 'Dimensiones / Medidas',
                'value' => $product->dimensions,
            ];
        }
        if ($product->material) {
            $additionalProperties[] = [
                '@type' => 'PropertyValue',
                'name' => 'Material de Fabricación',
                'value' => $product->material,
            ];
        }
        if ($product->sale_unit) {
            $additionalProperties[] = [
                '@type' => 'PropertyValue',
                'name' => 'Unidad de Venta',
                'value' => $product->sale_unit,
            ];
        }
        if ($product->box_qty) {
            $additionalProperties[] = [
                '@type' => 'PropertyValue',
                'name' => 'Cantidad por Caja',
                'value' => $product->box_qty.' '.$product->sale_unit,
            ];
        }
        if ($product->carton_qty) {
            $additionalProperties[] = [
                '@type' => 'PropertyValue',
                'name' => 'Cantidad por Cajón',
                'value' => $product->carton_qty.' '.$product->sale_unit,
            ];
        }

        foreach ($product->specifications as $spec) {
            $additionalProperties[] = [
                '@type' => 'PropertyValue',
                'name' => $spec->spec_key,
                'value' => trim($spec->spec_value.' '.($spec->unit ?? '')),
            ];
        }

        $imageUrls = $product->images->isNotEmpty()
            ? $product->images->map(fn ($img) => $img->url())->values()->all()
            : [$product->primaryImageUrl()];

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'sku' => $product->sku,
            'mpn' => $product->sku,
            'description' => trim(strip_tags((string) ($product->short_description ?: $product->detailed_description ?: $product->name))),
            'image' => $imageUrls,
            'url' => $canonicalUrl,
        ];

        if ($product->brand) {
            $schema['brand'] = [
                '@type' => 'Brand',
                'name' => $product->brand->name,
            ];
        }

        if ($product->category) {
            $schema['category'] = $product->subcategory
                ? sprintf('%s > %s', $product->category->name, $product->subcategory->name)
                : $product->category->name;
        }

        if (! empty($additionalProperties)) {
            $schema['additionalProperty'] = $additionalProperties;
        }

        // Only expose Offer price if shouldShowPrice() is true and price > 0
        if ($product->shouldShowPrice()) {
            $availabilitySchema = match ($product->availability_status) {
                'available' => 'https://schema.org/InStock',
                'on_request' => 'https://schema.org/PreOrder',
                'out_of_stock' => 'https://schema.org/OutOfStock',
                default => 'https://schema.org/InStock',
            };

            $schema['offers'] = [
                '@type' => 'Offer',
                'url' => $canonicalUrl,
                'priceCurrency' => $product->currency ?: 'PEN',
                'price' => number_format((float) $product->price, 2, '.', ''),
                'availability' => $availabilitySchema,
                'itemCondition' => 'https://schema.org/NewCondition',
            ];
        }

        return $schema;
    }
}
