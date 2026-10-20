<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Quote;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreDomainUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_availability_labels_and_currency_formatting(): void
    {
        $productPen = new Product([
            'sku' => 'SL-UNIT-01',
            'name' => 'Producto Moneda Soles',
            'price' => 1250.5,
            'currency' => 'PEN',
            'price_visibility' => 'show',
            'availability_status' => 'available',
        ]);

        $productUsd = new Product([
            'sku' => 'SL-UNIT-02',
            'name' => 'Producto Moneda Dólares',
            'price' => 45.00,
            'currency' => 'USD',
            'price_visibility' => 'show',
            'availability_status' => 'on_request',
        ]);

        $this->assertSame('S/ 1,250.50', $productPen->formattedPrice('show_all'));
        $this->assertSame('Disponible', $productPen->availabilityLabel());

        $this->assertSame('$ 45.00', $productUsd->formattedPrice('show_all'));
        $this->assertSame('Bajo consulta', $productUsd->availabilityLabel());
    }

    public function test_whatsapp_service_builds_properly_encoded_product_and_quote_links(): void
    {
        $product = $this->createSampleCatalogItem([
            'sku' => 'SL-WA-100',
            'name' => 'Bisagra Cazoleta 35mm',
            'slug' => 'bisagra-cazoleta-35mm',
            'sale_unit' => 'Par',
        ])['product'];

        /** @var WhatsAppService $whatsAppService */
        $whatsAppService = app(WhatsAppService::class);

        $productUrl = $whatsAppService->productInquiryUrl($product, 'https://importhnssanluis.com/catalogo/bisagra-cazoleta-35mm');
        $this->assertStringStartsWith('https://wa.me/', $productUrl);
        $this->assertStringContainsString(rawurlencode('SL-WA-100'), $productUrl);
        $this->assertStringContainsString(rawurlencode('Bisagra cazoleta 35mm'), $productUrl);

        $quote = Quote::query()->create([
            'reference_code' => 'COT-2026-00500',
            'contact_name' => 'Ing. Alberto Soto',
            'company_name' => 'Estructuras Soto S.A.C.',
            'contact_email' => 'asoto@estructuras.pe',
            'contact_phone' => '999111222',
            'status' => 'nueva',
            'currency' => 'PEN',
        ]);

        $quoteWaUrl = $whatsAppService->quoteSummaryUrl($quote);
        $this->assertStringStartsWith('https://wa.me/', $quoteWaUrl);
        $this->assertStringContainsString(rawurlencode('COT-2026-00500'), $quoteWaUrl);
        $this->assertStringContainsString(rawurlencode('Estructuras Soto S.A.C.'), $quoteWaUrl);
    }
}
