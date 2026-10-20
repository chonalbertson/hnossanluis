<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Quote;
use App\Services\QuotePdfService;
use App\Services\QuoteService;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class QuoteCartController extends Controller
{
    public function __construct(
        protected QuoteService $quoteService,
        protected WhatsAppService $whatsAppService,
        protected QuotePdfService $quotePdfService
    ) {}

    /**
     * Display the quote cart and formal quote submission form.
     * Supports both individual product quote (?product_id=X) and multi-product cart.
     */
    public function index(Request $request): View
    {
        $directProduct = null;
        $directVariant = null;

        if ($request->filled('product_id')) {
            $directProduct = Product::query()
                ->with(['brand', 'category', 'images', 'variants'])
                ->where('status', 'published')
                ->find((int) $request->query('product_id'));

            if ($directProduct && $request->filled('variant_id')) {
                $directVariant = $directProduct->variants->firstWhere('id', (int) $request->query('variant_id'));
            }

            // If user clicked "Cotizar directo" with ?add=1, also ensure it's in the cart
            if ($directProduct && $request->boolean('add')) {
                $this->quoteService->addItem(
                    $directProduct,
                    max(1, (float) $request->query('quantity', 1)),
                    null,
                    $directVariant
                );
            }
        }

        $cartItems = $this->quoteService->getCart();

        return view('public.quotes.index', [
            'cartItems' => $cartItems,
            'directProduct' => $directProduct,
            'directVariant' => $directVariant,
            'directQuantity' => max(1, (float) $request->query('quantity', 1)),
            'user' => Auth::user(),
            'seo' => [
                'title' => 'Solicitar Cotización Ferretera B2B y B2C | Import Hns San Luis',
                'description' => 'Envía tu lista de productos o códigos SKU para cotización formal. Atención a ferreterías, constructoras, talleres de melamina y clientes en todo el Perú.',
                'canonical' => route('quote.index'),
                'robots' => $request->has('product_id') ? 'noindex, follow' : 'index, follow',
                'breadcrumbs' => [
                    ['name' => 'Inicio', 'url' => route('home')],
                    ['name' => 'Solicitar Cotización', 'url' => route('quote.index')],
                ],
            ],
        ]);
    }

    /**
     * Add one or multiple products (or product variants) to the session quote list.
     */
    public function add(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->has('product_ids') && is_array($request->input('product_ids'))) {
            $validatedBatch = $request->validate([
                'product_ids' => ['required', 'array', 'min:1', 'max:10'],
                'product_ids.*' => ['integer', 'exists:products,id'],
            ]);

            $products = Product::query()
                ->where('status', 'published')
                ->whereIn('id', $validatedBatch['product_ids'])
                ->get();

            $cart = $this->quoteService->getCart();
            foreach ($products as $prod) {
                $cart = $this->quoteService->addItem($prod, 1.0);
            }

            $message = sprintf('Se añadieron %d producto(s) del comparador a tu lista de cotización.', $products->count());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'ok',
                    'message' => $message,
                    'cart_count' => count($cart),
                ]);
            }

            return redirect()->route('quote.index')->with('success', $message);
        }

        // Multi-variant matrix submission from Variable Product Detail View
        if ($request->has('variant_quantities') && is_array($request->input('variant_quantities'))) {
            $validatedMatrix = $request->validate([
                'product_id' => ['required', 'integer', 'exists:products,id'],
                'variant_quantities' => ['required', 'array'],
                'variant_quantities.*' => ['nullable', 'numeric', 'min:0', 'max:999999'],
                'notes' => ['nullable', 'string', 'max:500'],
            ]);

            $product = Product::query()
                ->with('variants')
                ->where('status', 'published')
                ->findOrFail((int) $validatedMatrix['product_id']);

            $variantsById = $product->variants->keyBy('id');
            $addedCount = 0;
            $cart = $this->quoteService->getCart();

            foreach ($validatedMatrix['variant_quantities'] as $variantId => $rawQty) {
                $qty = (float) $rawQty;
                if ($qty <= 0) {
                    continue;
                }
                /** @var ProductVariant|null $variant */
                $variant = $variantsById->get((int) $variantId);
                if (! $variant) {
                    continue;
                }
                $cart = $this->quoteService->addItem($product, $qty, $validatedMatrix['notes'] ?? null, $variant);
                $addedCount++;
            }

            if ($addedCount === 0) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Ingresa una cantidad mayor a 0 en al menos una variante o medida.',
                    ], 422);
                }

                return back()->with('error', 'Ingresa una cantidad mayor a 0 en al menos una variante o medida.');
            }

            $message = sprintf('Se añadieron %d variante(s) de "%s" a tu pliego de cotización.', $addedCount, $product->name);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'ok',
                    'message' => $message,
                    'cart_count' => count($cart),
                ]);
            }

            if ($request->boolean('redirect_to_quote')) {
                return redirect()->route('quote.index')->with('success', $message);
            }

            return back()->with('success', $message);
        }

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'numeric', 'min:0.01', 'max:999999'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::query()
            ->with('variants')
            ->where('status', 'published')
            ->findOrFail((int) $validated['product_id']);

        $variant = null;
        if ($product->variants->isNotEmpty()) {
            if (! empty($validated['variant_id'])) {
                $variant = $product->variants->firstWhere('id', (int) $validated['variant_id']);
            }

            if (! $variant) {
                $errorMessage = 'Elige un producto para cotizar.';
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $errorMessage,
                    ], 422);
                }

                return back()->with('error', $errorMessage);
            }
        }

        $qty = isset($validated['quantity']) ? (float) $validated['quantity'] : 1.0;
        $cart = $this->quoteService->addItem($product, $qty, $validated['notes'] ?? null, $variant);

        $displaySku = $variant?->sku ?: $product->sku;
        $displayName = $variant ? ($variant->name ?: $product->name.' — '.$variant->variant_label) : $product->name;
        $message = sprintf('"%s" (%s) añadido a tu lista de cotización.', $displayName, $displaySku);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'ok',
                'message' => $message,
                'cart_count' => count($cart),
                'product' => [
                    'id' => $product->id,
                    'variant_id' => $variant?->id,
                    'sku' => $displaySku,
                    'name' => $displayName,
                ],
            ]);
        }

        if ($request->boolean('redirect_to_quote')) {
            return redirect()->route('quote.index')->with('success', $message);
        }

        return back()->with('success', $message);
    }

    /**
     * Update quantities and notes of one or multiple items in the quote cart.
     */
    public function update(Request $request): RedirectResponse
    {
        if ($request->has('items') && is_array($request->input('items'))) {
            foreach ($request->input('items') as $cartKey => $itemData) {
                $normalizedKey = is_numeric($cartKey) ? (int) $cartKey : (string) $cartKey;
                $qty = isset($itemData['quantity']) ? (float) $itemData['quantity'] : 1.0;
                $notes = isset($itemData['notes']) ? (string) $itemData['notes'] : null;
                $this->quoteService->updateItem($normalizedKey, $qty, $notes);
            }
        } elseif ($request->filled('product_id')) {
            $validated = $request->validate([
                'product_id' => ['required'],
                'quantity' => ['required', 'numeric', 'min:0', 'max:999999'],
                'notes' => ['nullable', 'string', 'max:500'],
            ]);

            $rawKey = $validated['product_id'];
            $normalizedKey = is_numeric($rawKey) ? (int) $rawKey : (string) $rawKey;

            $this->quoteService->updateItem(
                $normalizedKey,
                (float) $validated['quantity'],
                $validated['notes'] ?? null
            );
        }

        return redirect()->route('quote.index')->with('success', 'Lista de cotización actualizada correctamente.');
    }

    /**
     * Remove an item from the quote cart.
     */
    public function remove(int|string $productId): RedirectResponse
    {
        $normalizedKey = is_numeric($productId) ? (int) $productId : $productId;
        $this->quoteService->removeItem($normalizedKey);

        return redirect()->route('quote.index')->with('success', 'Producto retirado de la lista de cotización.');
    }

    /**
     * Clear all items from the quote cart.
     */
    public function clear(): RedirectResponse
    {
        $this->quoteService->clearCart();

        return redirect()->route('quote.index')->with('success', 'Se ha vaciado la lista de cotización.');
    }

    /**
     * Validate and submit a single or multi-item quote request.
     */
    public function submit(Request $request): RedirectResponse
    {
        // 1. Anti-spam Honeypot Check
        if ($request->filled('website_hp')) {
            throw ValidationException::withMessages([
                'contact_name' => 'No se pudo procesar la solicitud por validación de seguridad.',
            ]);
        }

        // 2. Rate Limiting (max 5 submissions per minute per IP)
        $rateLimitKey = 'quote-submit:'.$request->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            throw ValidationException::withMessages([
                'contact_email' => sprintf('Demasiados intentos seguidos. Por favor espera %d segundos antes de enviar otra cotización.', $seconds),
            ]);
        }

        // Normalize RUC digits before validation if provided
        if ($request->filled('ruc')) {
            $request->merge([
                'ruc' => preg_replace('/\D/', '', (string) $request->input('ruc')),
            ]);
        }

        $validated = $request->validate([
            'contact_name' => ['required', 'string', 'min:2', 'max:150'],
            'contact_email' => ['required', 'email', 'max:150'],
            'contact_phone' => ['required', 'string', 'min:6', 'max:40'],
            'company_name' => ['nullable', 'string', 'max:180'],
            'ruc' => ['nullable', 'regex:/^\d{11}$/'],
            'customer_observations' => ['nullable', 'string', 'max:2000'],
            'privacy_accepted' => ['accepted'],
            'request_type' => ['nullable', 'in:single,multiple'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'numeric', 'min:0.01', 'max:999999'],
            'item_notes' => ['nullable', 'string', 'max:500'],
            'items' => ['nullable', 'array'],
        ], [
            'contact_name.required' => 'Por favor ingresa tu nombre de contacto.',
            'contact_email.required' => 'Por favor ingresa un correo electrónico válido para recibir la cotización.',
            'contact_email.email' => 'El formato del correo electrónico no es válido.',
            'contact_phone.required' => 'Por favor ingresa un teléfono o número de WhatsApp de contacto.',
            'ruc.regex' => 'El número de RUC debe contener exactamente 11 dígitos numéricos.',
            'privacy_accepted.accepted' => 'Debes aceptar la Política de Privacidad (Ley N° 29733) para procesar tu solicitud.',
        ]);

        // Sync any inline quantity/notes edits submitted from the cart table
        if (! empty($validated['items']) && is_array($validated['items'])) {
            foreach ($validated['items'] as $cartKey => $itemInput) {
                $normalizedKey = is_numeric($cartKey) ? (int) $cartKey : (string) $cartKey;
                $qty = max(1, (float) ($itemInput['quantity'] ?? 1));
                $notes = isset($itemInput['notes']) ? (string) $itemInput['notes'] : null;
                $this->quoteService->updateItem($normalizedKey, $qty, $notes);
            }
        }

        $itemsToQuote = [];
        $usedSessionCart = false;

        // Determine if this is a direct single-product quote or a cart quote
        if (! empty($validated['product_id']) && ($validated['request_type'] ?? '') === 'single') {
            $product = Product::query()
                ->with('variants')
                ->where('status', 'published')
                ->findOrFail((int) $validated['product_id']);

            $variant = ! empty($validated['variant_id'])
                ? $product->variants->firstWhere('id', (int) $validated['variant_id'])
                : null;

            $itemsToQuote[] = [
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'product' => $product,
                'variant' => $variant,
                'sku' => $variant?->sku ?: $product->sku,
                'name' => $variant ? ($variant->name ?: $product->name.' — '.$variant->variant_label) : $product->name,
                'sale_unit' => $variant?->sale_unit ?: $product->sale_unit,
                'quantity' => max(1, (float) ($validated['quantity'] ?? 1)),
                'notes' => $validated['item_notes'] ?? null,
            ];
        } else {
            $cartItems = $this->quoteService->getCart();
            if (! empty($cartItems)) {
                $itemsToQuote = array_values($cartItems);
                $usedSessionCart = true;
            } elseif (! empty($validated['product_id'])) {
                $product = Product::query()
                    ->with('variants')
                    ->where('status', 'published')
                    ->findOrFail((int) $validated['product_id']);

                $variant = ! empty($validated['variant_id'])
                    ? $product->variants->firstWhere('id', (int) $validated['variant_id'])
                    : null;

                $itemsToQuote[] = [
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'product' => $product,
                    'variant' => $variant,
                    'sku' => $variant?->sku ?: $product->sku,
                    'name' => $variant ? ($variant->name ?: $product->name.' — '.$variant->variant_label) : $product->name,
                    'sale_unit' => $variant?->sale_unit ?: $product->sale_unit,
                    'quantity' => max(1, (float) ($validated['quantity'] ?? 1)),
                    'notes' => $validated['item_notes'] ?? null,
                ];
            }
        }

        if (empty($itemsToQuote)) {
            throw ValidationException::withMessages([
                'items' => 'Tu lista de cotización está vacía. Agrega al menos un producto del catálogo para solicitar una cotización.',
            ]);
        }

        RateLimiter::hit($rateLimitKey, 60);

        $quote = $this->quoteService->createQuote(
            [
                'contact_name' => $validated['contact_name'],
                'contact_email' => $validated['contact_email'],
                'contact_phone' => $validated['contact_phone'],
                'company_name' => $validated['company_name'] ?? null,
                'ruc' => $validated['ruc'] ?? null,
                'customer_observations' => $validated['customer_observations'] ?? null,
                'request_type' => count($itemsToQuote) === 1 ? 'single' : 'multiple',
                'origin' => 'web_catalog',
                'privacy_accepted_at' => now(),
            ],
            $itemsToQuote,
            Auth::user()
        );

        if ($usedSessionCart) {
            $this->quoteService->clearCart();
        }

        // Store non-PII GA4 conversion payload for confirmation page
        $totalUnits = collect($quote->items)->sum(fn ($i) => (float) $i->quantity);
        session()->flash('ga4_submit_quote', [
            'quote_reference' => $quote->reference_code,
            'client_type' => ! empty($quote->ruc) ? 'b2b_ruc' : 'b2c_particular',
            'request_type' => $quote->request_type,
            'items_count' => $quote->items->count(),
            'total_units' => $totalUnits,
            'currency' => $quote->currency,
        ]);

        return redirect()
            ->route('quote.confirmation', ['reference_code' => $quote->reference_code])
            ->with('success', sprintf('Solicitud de cotización %s registrada exitosamente.', $quote->reference_code));
    }

    /**
     * Show the confirmation page for a submitted quote.
     */
    public function confirmation(string $referenceCode): View
    {
        $quote = Quote::query()
            ->with(['items.product', 'customer'])
            ->where('reference_code', $referenceCode)
            ->firstOrFail();

        $whatsappSummaryUrl = $this->whatsAppService->quoteSummaryUrl($quote);

        return view('public.quotes.confirmation', [
            'quote' => $quote,
            'whatsappSummaryUrl' => $whatsappSummaryUrl,
            'seo' => [
                'title' => sprintf('Confirmación de Cotización %s | Import Hns San Luis', $quote->reference_code),
                'description' => sprintf('Resumen de solicitud de cotización N° %s en Import Hns San Luis.', $quote->reference_code),
                'canonical' => route('quote.confirmation', $quote->reference_code),
                'robots' => 'noindex, nofollow',
            ],
        ]);
    }

    /**
     * Public Quote Tracking page (GET /cotizacion/seguimiento).
     * Requires both reference_code (e.g., COT-2026-00001) and the registered contact_email
     * to protect customer privacy and prevent unauthorized enumeration.
     */
    public function track(Request $request): View
    {
        $referenceCode = strtoupper(trim((string) $request->query('reference_code', '')));
        $email = strtolower(trim((string) ($request->query('email', $request->query('contact_email', '')))));

        $quote = null;
        $lookupError = null;
        $whatsappSummaryUrl = null;
        $pdfDownloadUrl = null;
        $hasSearched = ($referenceCode !== '' || $email !== '');

        if ($hasSearched) {
            if ($referenceCode === '' || $email === '') {
                $lookupError = 'Por favor ingresa tanto el Código de Referencia (ej. COT-2026-00001) como el Correo Electrónico registrado en tu solicitud.';
            } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $lookupError = 'El formato del correo electrónico ingresado no es válido.';
            } else {
                $rateLimitKey = 'quote-track:'.$request->ip();
                if (RateLimiter::tooManyAttempts($rateLimitKey, 15)) {
                    $seconds = RateLimiter::availableIn($rateLimitKey);
                    $lookupError = sprintf('Demasiadas consultas seguidas. Por favor espera %d segundos antes de volver a consultar.', $seconds);
                } else {
                    RateLimiter::hit($rateLimitKey, 60);

                    $candidate = Quote::query()
                        ->with(['items.product', 'customer', 'assignedSeller', 'statusHistories'])
                        ->where('reference_code', $referenceCode)
                        ->first();

                    if ($candidate && strtolower(trim((string) $candidate->contact_email)) === $email) {
                        $quote = $candidate;
                        $whatsappSummaryUrl = $this->whatsAppService->quoteSummaryUrl($quote);
                        $pdfDownloadUrl = route('quote.track.pdf', [
                            'reference_code' => $quote->reference_code,
                            'email' => $quote->contact_email,
                        ]);
                    } else {
                        $lookupError = 'No encontramos una solicitud de cotización que coincida con el código de referencia y el correo electrónico ingresados. Verifica los datos e intenta nuevamente.';
                    }
                }
            }
        }

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Solicitar Cotización', 'url' => route('quote.index')],
            ['name' => 'Seguimiento de Cotización', 'url' => route('quote.track')],
        ];

        return view('public.quotes.track', [
            'quote' => $quote,
            'referenceCode' => $referenceCode,
            'email' => $email,
            'hasSearched' => $hasSearched,
            'lookupError' => $lookupError,
            'whatsappSummaryUrl' => $whatsappSummaryUrl,
            'pdfDownloadUrl' => $pdfDownloadUrl,
            'breadcrumbs' => $breadcrumbs,
            'seo' => [
                'title' => 'Seguimiento de Solicitud de Cotización | Import Hns San Luis',
                'description' => 'Consulta en tiempo real el estado de tu solicitud de cotización en Import Hns San Luis con tu código COT-YYYY-NNNNN y correo electrónico.',
                'canonical' => route('quote.track'),
                'robots' => $hasSearched ? 'noindex, nofollow' : 'index, follow',
                'breadcrumbs' => $breadcrumbs,
            ],
        ]);
    }

    /**
     * Download the official Quote PDF from the public tracking page, validating the customer's email.
     */
    public function downloadTrackedPdf(Request $request, string $referenceCode): Response|RedirectResponse
    {
        $normalizedRef = strtoupper(trim($referenceCode));
        $email = strtolower(trim((string) $request->query('email', '')));

        if ($email === '') {
            return redirect()
                ->route('quote.track', ['reference_code' => $normalizedRef])
                ->with('error', 'Debes verificar el correo electrónico asociado a la cotización para descargar el documento PDF.');
        }

        $quote = Quote::query()
            ->with(['items.product', 'customer', 'assignedSeller'])
            ->where('reference_code', $normalizedRef)
            ->first();

        if (! $quote || strtolower(trim((string) $quote->contact_email)) !== $email) {
            return redirect()
                ->route('quote.track', ['reference_code' => $normalizedRef])
                ->with('error', 'No se pudo validar el acceso al PDF de la cotización con el correo proporcionado.');
        }

        return $this->quotePdfService->streamPdf($quote, true);
    }
}
