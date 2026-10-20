<?php

namespace Tests\Feature;

use App\Mail\QuoteProposalMail;
use App\Mail\QuoteReceivedCustomerMail;
use App\Mail\QuoteReceivedTeamMail;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteStatusHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class QuoteSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_submit_individual_product_quote_creating_reference_snapshots_history_and_emails(): void
    {
        Mail::fake();

        $product = $this->createSampleCatalogItem([
            'sku' => 'SL-IND-001',
            'name' => 'Cerradura de Embutir Acero Inoxidable',
            'sale_unit' => 'Unidad',
            'price' => 95.00,
        ])['product'];

        $response = $this->post(route('quote.submit'), [
            'request_type' => 'single',
            'product_id' => $product->id,
            'quantity' => 15,
            'item_notes' => 'Requerimos llaves maestras adicionales.',
            'contact_name' => 'Ing. Martín Cáceres',
            'contact_email' => 'mcaceres@constructoracaceres.pe',
            'contact_phone' => '+51 987 654 321',
            'company_name' => 'Constructora Cáceres S.A.C.',
            'ruc' => '20601234567',
            'customer_observations' => 'Entrega en obra en San Isidro.',
            'privacy_accepted' => '1',
        ]);

        $quote = Quote::query()->latest('id')->first();
        $this->assertNotNull($quote);

        $expectedPrefix = sprintf('COT-%s-', now()->format('Y'));
        $this->assertStringStartsWith($expectedPrefix, $quote->reference_code);
        $this->assertMatchesRegularExpression('/^COT-\d{4}-\d{5}$/', $quote->reference_code);

        $response->assertRedirect(route('quote.confirmation', ['reference_code' => $quote->reference_code]));

        // Verify persistence in quotes, quote_items (with snapshots) and quote_status_histories
        $this->assertDatabaseHas('quotes', [
            'id' => $quote->id,
            'reference_code' => $quote->reference_code,
            'contact_email' => 'mcaceres@constructoracaceres.pe',
            'company_name' => 'Constructora Cáceres S.A.C.',
            'ruc' => '20601234567',
            'request_type' => 'single',
            'status' => 'nueva',
        ]);

        $this->assertDatabaseHas('quote_items', [
            'quote_id' => $quote->id,
            'product_id' => $product->id,
            'sku_snapshot' => 'SL-IND-001',
            'product_name_snapshot' => 'Cerradura de embutir acero inoxidable',
            'sale_unit_snapshot' => 'Unidad',
            'quantity' => 15,
            'item_notes' => 'Requerimos llaves maestras adicionales.',
        ]);

        $this->assertDatabaseHas('quote_status_histories', [
            'quote_id' => $quote->id,
            'previous_status' => null,
            'new_status' => 'nueva',
        ]);

        // Verify confirmation page loads properly
        $confirmResponse = $this->get(route('quote.confirmation', ['reference_code' => $quote->reference_code]));
        $confirmResponse->assertStatus(200);
        $confirmResponse->assertSee($quote->reference_code);
        $confirmResponse->assertSee('SL-IND-001');

        // Verify customer & sales team emails were dispatched
        Mail::assertSent(QuoteReceivedCustomerMail::class, function (QuoteReceivedCustomerMail $mail) use ($quote) {
            return $mail->hasTo('mcaceres@constructoracaceres.pe')
                && $mail->quote->id === $quote->id;
        });

        Mail::assertSent(QuoteReceivedTeamMail::class, function (QuoteReceivedTeamMail $mail) use ($quote) {
            return $mail->quote->id === $quote->id;
        });
    }

    public function test_can_manage_multi_product_quote_cart_and_submit_multiple_quote(): void
    {
        Mail::fake();

        $base = $this->createSampleCatalogItem([
            'sku' => 'SL-CART-01',
            'name' => 'Bisagra Cazoleta 35mm Clip-On',
            'sale_unit' => 'Par',
        ]);
        $product1 = $base['product'];

        $product2 = Product::query()->create([
            'sku' => 'SL-CART-02',
            'name' => 'Corredera Telescópica 500mm Pesada',
            'slug' => 'corredera-telescopica-500mm-pesada',
            'category_id' => $base['category']->id,
            'brand_id' => $base['brand']->id,
            'sale_unit' => 'Juego',
            'availability_status' => 'available',
            'status' => 'published',
        ]);

        $product3 = Product::query()->create([
            'sku' => 'SL-CART-03',
            'name' => 'Tirador Barra Acero Inox 192mm',
            'slug' => 'tirador-barra-acero-inox-192mm',
            'category_id' => $base['category']->id,
            'brand_id' => $base['brand']->id,
            'sale_unit' => 'Unidad',
            'availability_status' => 'available',
            'status' => 'published',
        ]);

        // 1. Add 3 products to quote cart (including JSON/AJAX response check)
        $jsonAdd = $this->postJson(route('quote.add'), [
            'product_id' => $product1->id,
            'quantity' => 50,
            'notes' => 'Para muebles altos de cocina',
        ]);
        $jsonAdd->assertStatus(200)->assertJson([
            'status' => 'ok',
            'cart_count' => 1,
        ]);

        $this->post(route('quote.add'), [
            'product_id' => $product2->id,
            'quantity' => 25,
            'notes' => 'Extracción total',
        ])->assertRedirect();

        $this->post(route('quote.add'), [
            'product_id' => $product3->id,
            'quantity' => 10,
        ])->assertRedirect();

        // 2. Update quantities and observations in cart
        $this->patch(route('quote.update'), [
            'items' => [
                $product1->id => ['quantity' => 80, 'notes' => 'Actualizado a 80 pares'],
                $product2->id => ['quantity' => 30, 'notes' => 'Extracción total 45kg'],
            ],
        ])->assertRedirect(route('quote.index'));

        // 3. Remove product 3 from cart
        $this->delete(route('quote.remove', $product3->id))
            ->assertRedirect(route('quote.index'));

        // Verify cart view displays product 1 & 2, and not product 3
        $cartPage = $this->get(route('quote.index'));
        $cartPage->assertStatus(200);
        $cartPage->assertSee('SL-CART-01');
        $cartPage->assertSee('SL-CART-02');
        $cartPage->assertDontSee('SL-CART-03');

        // 4. Submit multi-product quote from session cart
        $submitResponse = $this->post(route('quote.submit'), [
            'contact_name' => 'Arq. Valeria Lozano',
            'contact_email' => 'vlozano@estudiomobiliario.pe',
            'contact_phone' => '998877665',
            'company_name' => 'Estudio Mobiliario Lozano E.I.R.L.',
            'ruc' => '20556677889',
            'customer_observations' => 'Cotizar despacho a Arequipa.',
            'privacy_accepted' => 'on',
        ]);

        $quote = Quote::query()->where('contact_email', 'vlozano@estudiomobiliario.pe')->first();
        $this->assertNotNull($quote);
        $submitResponse->assertRedirect(route('quote.confirmation', ['reference_code' => $quote->reference_code]));

        $this->assertSame('multiple', $quote->request_type);
        $this->assertCount(2, $quote->items);

        $this->assertDatabaseHas('quote_items', [
            'quote_id' => $quote->id,
            'sku_snapshot' => 'SL-CART-01',
            'quantity' => 80,
            'item_notes' => 'Actualizado a 80 pares',
        ]);
        $this->assertDatabaseHas('quote_items', [
            'quote_id' => $quote->id,
            'sku_snapshot' => 'SL-CART-02',
            'quantity' => 30,
            'item_notes' => 'Extracción total 45kg',
        ]);
    }

    public function test_quote_form_validates_required_fields_peruvian_ruc_and_privacy_consent(): void
    {
        $product = $this->createSampleCatalogItem()['product'];

        // 1. Missing required contact fields and privacy checkbox, plus invalid 9-digit RUC
        $invalidResponse = $this->post(route('quote.submit'), [
            'request_type' => 'single',
            'product_id' => $product->id,
            'quantity' => 5,
            'contact_name' => '',
            'contact_email' => 'correo-invalido',
            'contact_phone' => '',
            'ruc' => '123456789', // Invalid RUC (not 11 digits)
        ]);

        $invalidResponse->assertSessionHasErrors([
            'contact_name',
            'contact_email',
            'contact_phone',
            'ruc',
            'privacy_accepted',
        ]);

        $this->assertSame(0, Quote::query()->count());
    }

    public function test_admin_can_assign_seller_change_status_add_notes_generate_pdf_and_send_idempotent_commercial_response(): void
    {
        Mail::fake();

        $seller = $this->createUserWithRole('seller', [
            'name' => 'Ejecutivo Comercial B2B',
            'email' => 'ejecutivo@importhnssanluis.com',
        ]);

        $product = $this->createSampleCatalogItem([
            'sku' => 'SL-PRO-550',
            'name' => 'Freno Hidráulico Aéreo para Puerta Pesada',
            'sale_unit' => 'Unidad',
            'price' => 150.00,
        ])['product'];

        // Create initial quote
        $this->post(route('quote.submit'), [
            'request_type' => 'single',
            'product_id' => $product->id,
            'quantity' => 10,
            'contact_name' => 'Jefe de Compras',
            'contact_email' => 'compras@corporacionperu.pe',
            'contact_phone' => '+51 944 555 666',
            'company_name' => 'Corporación Industrial Perú S.A.',
            'ruc' => '20100099988',
            'privacy_accepted' => '1',
        ]);

        /** @var Quote $quote */
        $quote = Quote::query()->with('items')->firstOrFail();
        $quoteItem = $quote->items->first();

        Mail::fake(); // Reset mail fake before admin operations

        // 1. Assign seller
        $assignResponse = $this->actingAs($seller)->post(route('admin.quotes.assign', $quote), [
            'assigned_seller_id' => $seller->id,
            'follow_up_date' => now()->addDays(5)->toDateString(),
        ]);
        $assignResponse->assertRedirect();
        $this->assertSame($seller->id, $quote->fresh()->assigned_seller_id);

        // 2. Change status and verify QuoteStatusHistory audit trail
        $statusResponse = $this->actingAs($seller)->post(route('admin.quotes.status', $quote), [
            'status' => 'en_revision',
            'comment' => 'Verificando disponibilidad de stock en almacén central.',
        ]);
        $statusResponse->assertRedirect();
        $this->assertSame('en_revision', $quote->fresh()->status);
        $this->assertDatabaseHas('quote_status_histories', [
            'quote_id' => $quote->id,
            'changed_by' => $seller->id,
            'previous_status' => 'nueva',
            'new_status' => 'en_revision',
            'comment' => 'Verificando disponibilidad de stock en almacén central.',
        ]);

        // 3. Add internal note
        $noteResponse = $this->actingAs($seller)->post(route('admin.quotes.notes', $quote), [
            'note' => 'Cliente frecuente con descuento corporativo del 5%.',
            'is_pinned' => '1',
        ]);
        $noteResponse->assertRedirect();
        $this->assertDatabaseHas('internal_notes', [
            'quote_id' => $quote->id,
            'user_id' => $seller->id,
            'note' => 'Cliente frecuente con descuento corporativo del 5%.',
            'is_pinned' => true,
        ]);

        // 4. Generate Quote PDF
        $pdfResponse = $this->actingAs($seller)->get(route('admin.quotes.pdf', $quote));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString(
            'Cotizacion-'.$quote->reference_code.'.pdf',
            (string) $pdfResponse->headers->get('Content-Disposition')
        );

        // 5. Send Commercial Proposal with offered prices & idempotency protection
        $idempotencyKey = 'idem_key_test_987654321';

        $respondResponse = $this->actingAs($seller)->post(route('admin.quotes.respond', $quote), [
            'unit_prices' => [
                $quoteItem->id => '140.00', // 10 units * 140.00 = 1400.00 subtotal + 18% IGV (252.00) = 1652.00 total
            ],
            'currency' => 'PEN',
            'include_tax' => '1',
            'validity_days' => 15,
            'commercial_response' => 'Estimados, adjuntamos nuestra propuesta comercial con precio especial corporativo.',
            'commercial_conditions' => 'Entrega inmediata desde nuestro almacén en Lima.',
            'comment' => 'Propuesta enviada con descuento corporativo.',
            'idempotency_key' => $idempotencyKey,
        ]);

        $respondResponse->assertRedirect();
        $quote->refresh();

        $this->assertSame('respondida', $quote->status);
        $this->assertEquals(1400.00, (float) $quote->subtotal_offered);
        $this->assertEquals(252.00, (float) $quote->tax_offered);
        $this->assertEquals(1652.00, (float) $quote->total_offered);
        $this->assertSame($idempotencyKey, $quote->last_email_idempotency_key);
        $this->assertNotNull($quote->responded_at);

        Mail::assertSent(QuoteProposalMail::class, 1);

        // 6. Retry sending commercial response with the SAME idempotency_key (must NOT send duplicate email or duplicate status history)
        $historiesCountBeforeRetry = QuoteStatusHistory::query()->where('quote_id', $quote->id)->count();

        $retryResponse = $this->actingAs($seller)->post(route('admin.quotes.respond', $quote), [
            'unit_prices' => [
                $quoteItem->id => '140.00',
            ],
            'currency' => 'PEN',
            'include_tax' => '1',
            'validity_days' => 15,
            'commercial_response' => 'Reintento accidental por doble clic.',
            'idempotency_key' => $idempotencyKey,
        ]);

        $retryResponse->assertRedirect();

        // Still only 1 email sent!
        Mail::assertSent(QuoteProposalMail::class, 1);
        $this->assertSame(
            $historiesCountBeforeRetry,
            QuoteStatusHistory::query()->where('quote_id', $quote->id)->count()
        );
    }
}
