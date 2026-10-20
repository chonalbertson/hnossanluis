<?php

namespace App\Services;

use App\Mail\QuoteProposalMail;
use App\Mail\QuoteReceivedCustomerMail;
use App\Mail\QuoteReceivedTeamMail;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\QuoteStatusHistory;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;
use Throwable;

class QuoteService
{
    public const SESSION_CART_KEY = 'sanluis_quote_cart';

    public function __construct(
        protected ActivityLoggerService $activityLogger,
        protected QuotePdfService $pdfService
    ) {}

    /**
     * Retrieve current quote cart items from session.
     *
     * @return array<int|string, array<string, mixed>>
     */
    public function getCart(): array
    {
        $rawCart = Session::get(self::SESSION_CART_KEY, []);
        if (empty($rawCart) || ! is_array($rawCart)) {
            return [];
        }

        $productIds = [];
        $variantIds = [];

        foreach ($rawCart as $cartKey => $entry) {
            $pid = isset($entry['product_id']) ? (int) $entry['product_id'] : (int) $cartKey;
            if ($pid > 0) {
                $productIds[] = $pid;
            }
            if (! empty($entry['variant_id'])) {
                $variantIds[] = (int) $entry['variant_id'];
            }
        }

        $products = Product::query()
            ->with(['brand', 'category', 'images'])
            ->whereIn('id', array_unique($productIds))
            ->get()
            ->keyBy('id');

        $variants = ! empty($variantIds)
            ? ProductVariant::query()->whereIn('id', array_unique($variantIds))->get()->keyBy('id')
            : collect();

        $hydrated = [];
        foreach ($rawCart as $cartKey => $entry) {
            $pid = isset($entry['product_id']) ? (int) $entry['product_id'] : (int) $cartKey;
            /** @var Product|null $product */
            $product = $products->get($pid);
            $vid = ! empty($entry['variant_id']) ? (int) $entry['variant_id'] : null;
            /** @var ProductVariant|null $variant */
            $variant = $vid ? $variants->get($vid) : null;

            $normalizedKey = $vid ? 'v_'.$vid : $pid;

            $sku = $variant?->sku ?? ($entry['sku'] ?? $product?->sku ?? 'N/A');
            $variantLabel = $variant?->variant_label ?? ($entry['variant_label'] ?? null);
            $name = $variant
                ? ($variant->name ?: ($product ? $product->name.' — '.$variant->variant_label : 'Variante'))
                : ($entry['name'] ?? $product?->name ?? 'Producto');
            $saleUnit = $variant?->sale_unit ?: ($entry['sale_unit'] ?? $product?->sale_unit ?? 'Unidad');
            $formattedPrice = $variant
                ? $variant->formattedPrice($product)
                : $product?->formattedPrice();
            $availabilityLabel = $variant
                ? $variant->availabilityLabel()
                : ($product?->availabilityLabel() ?? 'Consultar');

            $hydrated[$normalizedKey] = [
                'cart_key' => $normalizedKey,
                'product_id' => $pid,
                'variant_id' => $vid,
                'variant_label' => $variantLabel,
                'sku' => $sku,
                'name' => $name,
                'slug' => $product?->slug ?? ($entry['slug'] ?? ''),
                'sale_unit' => $saleUnit,
                'quantity' => max(1, (float) ($entry['quantity'] ?? 1)),
                'notes' => $entry['notes'] ?? null,
                'formatted_price' => $formattedPrice,
                'availability_label' => $availabilityLabel,
                'image_url' => $product?->primaryImageUrl() ?? asset('images/placeholder-product.svg'),
                'product' => $product,
                'variant' => $variant,
            ];
        }

        return $hydrated;
    }

    /**
     * Add or increment a product (or specific variant) in the session quote list.
     */
    public function addItem(Product $product, float $quantity = 1, ?string $notes = null, ?ProductVariant $variant = null): array
    {
        $cart = Session::get(self::SESSION_CART_KEY, []);
        $pid = (int) $product->id;
        $cartKey = $variant ? 'v_'.$variant->id : $pid;
        $qty = max(1, round($quantity, 2));

        $itemName = $variant
            ? ($variant->name ?: $product->name.' — '.$variant->variant_label)
            : $product->name;

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] = round(((float) $cart[$cartKey]['quantity']) + $qty, 2);
            if ($notes !== null && trim($notes) !== '') {
                $cart[$cartKey]['notes'] = trim($notes);
            }
        } else {
            $cart[$cartKey] = [
                'product_id' => $pid,
                'variant_id' => $variant?->id,
                'variant_label' => $variant?->variant_label,
                'sku' => $variant?->sku ?: $product->sku,
                'name' => $itemName,
                'slug' => $product->slug,
                'sale_unit' => $variant?->sale_unit ?: $product->sale_unit,
                'quantity' => $qty,
                'notes' => $notes !== null ? trim($notes) : null,
            ];
        }

        Session::put(self::SESSION_CART_KEY, $cart);

        return $this->getCart();
    }

    /**
     * Update quantity and notes of an item in the session quote list.
     */
    public function updateItem(int|string $cartKey, float $quantity, ?string $notes = null): array
    {
        $cart = Session::get(self::SESSION_CART_KEY, []);

        if ($quantity <= 0) {
            unset($cart[$cartKey]);
        } elseif (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] = round($quantity, 2);
            if ($notes !== null) {
                $cart[$cartKey]['notes'] = trim($notes) ?: null;
            }
        }

        Session::put(self::SESSION_CART_KEY, $cart);

        return $this->getCart();
    }

    /**
     * Remove a product or variant from the session quote list.
     */
    public function removeItem(int|string $cartKey): array
    {
        $cart = Session::get(self::SESSION_CART_KEY, []);
        unset($cart[$cartKey]);
        Session::put(self::SESSION_CART_KEY, $cart);

        return $this->getCart();
    }

    /**
     * Clear the session quote cart.
     */
    public function clearCart(): void
    {
        Session::forget(self::SESSION_CART_KEY);
    }

    /**
     * Return total number of distinct product lines in the session quote cart.
     */
    public function countItems(): int
    {
        $cart = Session::get(self::SESSION_CART_KEY, []);

        return is_array($cart) ? count($cart) : 0;
    }

    /**
     * Create a new Quote with its items, customer record, initial history, and notifications.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $items
     */
    public function createQuote(array $data, array $items, ?User $user = null): Quote
    {
        if (empty($items)) {
            throw new InvalidArgumentException('La cotización debe incluir al menos un producto.');
        }

        $quote = DB::transaction(function () use ($data, $items, $user) {
            $referenceCode = $this->generateNextReferenceCode();

            $contactEmail = strtolower(trim((string) $data['contact_email']));
            $contactName = trim((string) $data['contact_name']);
            $contactPhone = trim((string) $data['contact_phone']);
            $companyName = ! empty($data['company_name']) ? trim((string) $data['company_name']) : null;
            $ruc = ! empty($data['ruc']) ? preg_replace('/\D/', '', (string) $data['ruc']) : null;

            $customer = Customer::query()->where('email', $contactEmail)->first();
            if (! $customer && $ruc) {
                $customer = Customer::query()->where('ruc', $ruc)->first();
            }

            if ($customer) {
                $customer->update(array_filter([
                    'user_id' => $customer->user_id ?? $user?->id,
                    'name' => $contactName,
                    'phone' => $contactPhone,
                    'company' => $companyName ?? $customer->company,
                    'ruc' => $ruc ?? $customer->ruc,
                ], fn ($val) => $val !== null));
            } else {
                $customer = Customer::query()->create([
                    'user_id' => $user?->id,
                    'name' => $contactName,
                    'email' => $contactEmail,
                    'phone' => $contactPhone,
                    'company' => $companyName,
                    'ruc' => $ruc,
                    'is_authorized_contact' => true,
                ]);
            }

            $validityDays = (int) SiteSetting::getValue(
                'commercial_quote_validity_days',
                env('COMMERCIAL_QUOTE_VALIDITY_DAYS', 15)
            );
            $defaultCurrency = (string) SiteSetting::getValue(
                'commercial_default_currency',
                env('COMMERCIAL_DEFAULT_CURRENCY', 'PEN')
            );

            $requestType = $data['request_type'] ?? (count($items) === 1 ? 'single' : 'multiple');
            $origin = $data['origin'] ?? 'web_catalog';

            $quote = Quote::query()->create([
                'reference_code' => $referenceCode,
                'customer_id' => $customer->id,
                'user_id' => $user?->id,
                'assigned_seller_id' => $data['assigned_seller_id'] ?? null,
                'contact_name' => $contactName,
                'contact_email' => $contactEmail,
                'contact_phone' => $contactPhone,
                'company_name' => $companyName,
                'ruc' => $ruc,
                'request_type' => $requestType,
                'origin' => $origin,
                'status' => 'nueva',
                'currency' => $data['currency'] ?? $defaultCurrency,
                'customer_observations' => ! empty($data['customer_observations']) ? trim((string) $data['customer_observations']) : null,
                'validity_days' => $validityDays > 0 ? $validityDays : 15,
                'valid_until' => now()->addDays($validityDays > 0 ? $validityDays : 15)->toDateString(),
                'privacy_accepted_at' => $data['privacy_accepted_at'] ?? now(),
            ]);

            $missingProductIds = [];
            $missingVariantIds = [];

            foreach ($items as $itemData) {
                if ((empty($itemData['product']) || ! ($itemData['product'] instanceof Product)) && ! empty($itemData['product_id'])) {
                    $missingProductIds[] = (int) $itemData['product_id'];
                }
                if ((empty($itemData['variant']) || ! ($itemData['variant'] instanceof ProductVariant)) && ! empty($itemData['variant_id'])) {
                    $missingVariantIds[] = (int) $itemData['variant_id'];
                }
            }

            $preloadedProducts = ! empty($missingProductIds)
                ? Product::withTrashed()->whereIn('id', array_unique($missingProductIds))->get()->keyBy('id')
                : collect();

            $preloadedVariants = ! empty($missingVariantIds)
                ? ProductVariant::query()->whereIn('id', array_unique($missingVariantIds))->get()->keyBy('id')
                : collect();

            foreach ($items as $itemData) {
                $product = null;
                if (! empty($itemData['product']) && $itemData['product'] instanceof Product) {
                    $product = $itemData['product'];
                } elseif (! empty($itemData['product_id'])) {
                    $product = $preloadedProducts->get((int) $itemData['product_id']);
                }

                $variant = null;
                if (! empty($itemData['variant']) && $itemData['variant'] instanceof ProductVariant) {
                    $variant = $itemData['variant'];
                } elseif (! empty($itemData['variant_id'])) {
                    $variant = $preloadedVariants->get((int) $itemData['variant_id']);
                }

                $qty = max(0.01, (float) ($itemData['quantity'] ?? 1));

                if ($variant) {
                    $skuSnapshot = $variant->sku ?: ($product?->sku ?? 'SKU-VAR');
                    $nameSnapshot = $variant->name ?: ($product ? $product->name.' — '.$variant->variant_label : $variant->variant_label);
                    $unitSnapshot = $variant->sale_unit ?: ($product?->sale_unit ?? 'Unidad');
                } else {
                    $skuSnapshot = (string) ($itemData['sku_snapshot'] ?? $itemData['sku'] ?? $product?->sku ?? 'SKU-MANUAL');
                    $nameSnapshot = (string) ($itemData['product_name_snapshot'] ?? $itemData['name'] ?? $product?->name ?? 'Producto solicitado');
                    $unitSnapshot = (string) ($itemData['sale_unit_snapshot'] ?? $itemData['sale_unit'] ?? $product?->sale_unit ?? 'Unidad');
                }

                QuoteItem::query()->create([
                    'quote_id' => $quote->id,
                    'product_id' => $product?->id,
                    'sku_snapshot' => $skuSnapshot,
                    'product_name_snapshot' => $nameSnapshot,
                    'sale_unit_snapshot' => $unitSnapshot,
                    'quantity' => $qty,
                    'item_notes' => ! empty($itemData['notes']) ? trim((string) $itemData['notes']) : (! empty($itemData['item_notes']) ? trim((string) $itemData['item_notes']) : null),
                ]);
            }

            QuoteStatusHistory::query()->create([
                'quote_id' => $quote->id,
                'changed_by' => $user?->id,
                'previous_status' => null,
                'new_status' => 'nueva',
                'comment' => 'Solicitud de cotización registrada desde el canal '.$origin.'.',
                'notified_customer' => true,
            ]);

            $this->activityLogger->log(
                'quote.created',
                'quotes',
                sprintf('Solicitud de cotización %s creada (%d ítems) por %s.', $quote->reference_code, count($items), $quote->contact_name),
                $quote,
                [
                    'reference_code' => $quote->reference_code,
                    'request_type' => $quote->request_type,
                    'origin' => $quote->origin,
                    'items_count' => count($items),
                ],
                $user?->id
            );

            return $quote->load(['items', 'customer']);
        });

        // Dispatch confirmation emails safely outside or after DB transaction without breaking quote creation
        try {
            Mail::to($quote->contact_email)->send(new QuoteReceivedCustomerMail($quote));

            $salesRecipient = (string) SiteSetting::getValue(
                'sales_notification_email',
                env('MAIL_SALES_NOTIFICATION_TO', 'ventas@hnssanluis.com')
            );
            if ($salesRecipient !== '') {
                Mail::to($salesRecipient)->send(new QuoteReceivedTeamMail($quote));
            }
        } catch (Throwable $e) {
            Log::warning('No se pudo enviar notificación de correo para cotización '.$quote->reference_code.': '.$e->getMessage());
        }

        return $quote;
    }

    /**
     * Change the status of a quote and record the audit history.
     */
    public function changeStatus(
        Quote $quote,
        string $newStatus,
        User $actor,
        ?string $comment = null,
        bool $notifyCustomer = false
    ): QuoteStatusHistory {
        if (! array_key_exists($newStatus, Quote::STATUSES)) {
            throw new InvalidArgumentException(sprintf('El estado de cotización "%s" no es válido.', $newStatus));
        }

        return DB::transaction(function () use ($quote, $newStatus, $actor, $comment, $notifyCustomer) {
            $previousStatus = $quote->status;

            $quote->update([
                'status' => $newStatus,
                'responded_at' => $newStatus === 'respondida' && ! $quote->responded_at ? now() : $quote->responded_at,
            ]);

            $history = QuoteStatusHistory::query()->create([
                'quote_id' => $quote->id,
                'changed_by' => $actor->id,
                'previous_status' => $previousStatus,
                'new_status' => $newStatus,
                'comment' => $comment,
                'notified_customer' => $notifyCustomer,
            ]);

            $this->activityLogger->log(
                'quote.status_changed',
                'quotes',
                sprintf('Cotización %s cambió de estado "%s" a "%s".', $quote->reference_code, $previousStatus, $newStatus),
                $quote,
                [
                    'previous_status' => $previousStatus,
                    'new_status' => $newStatus,
                    'comment' => $comment,
                ],
                $actor->id
            );

            return $history;
        });
    }

    /**
     * Update offered prices per item, commercial conditions, generate PDF, and send QuoteProposalMail idempotently.
     */
    public function sendCommercialResponse(
        Quote $quote,
        array $offerData,
        User $actor,
        ?string $idempotencyKey = null
    ): bool {
        if ($idempotencyKey !== null && $idempotencyKey !== '' && $quote->last_email_idempotency_key === $idempotencyKey) {
            return true;
        }

        $quote = DB::transaction(function () use ($quote, $offerData, $actor, $idempotencyKey) {
            $quote->load('items');

            $itemPrices = $offerData['unit_prices'] ?? $offerData['items'] ?? [];
            $subtotal = 0.0;

            foreach ($quote->items as $item) {
                $rawUnitPrice = $itemPrices[$item->id] ?? $item->unit_price_offered;

                if (is_array($rawUnitPrice)) {
                    $rawUnitPrice = $rawUnitPrice['unit_price_offered'] ?? null;
                }

                if ($rawUnitPrice !== null && $rawUnitPrice !== '' && (float) $rawUnitPrice >= 0) {
                    $unitPrice = round((float) $rawUnitPrice, 2);
                    $lineSubtotal = round($unitPrice * (float) $item->quantity, 2);
                    $item->update([
                        'unit_price_offered' => $unitPrice,
                        'subtotal_offered' => $lineSubtotal,
                    ]);
                    $subtotal += $lineSubtotal;
                }
            }

            $includeTax = filter_var($offerData['include_tax'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $taxRate = (float) ($offerData['tax_rate'] ?? 0.18);
            $taxOffered = $includeTax ? round($subtotal * $taxRate, 2) : 0.0;
            $totalOffered = round($subtotal + $taxOffered, 2);

            $validityDays = (int) ($offerData['validity_days'] ?? $quote->validity_days ?: 15);
            $previousStatus = $quote->status;

            $quote->update([
                'currency' => $offerData['currency'] ?? $quote->currency ?? 'PEN',
                'subtotal_offered' => $subtotal,
                'tax_offered' => $taxOffered,
                'total_offered' => $totalOffered,
                'commercial_response' => $offerData['commercial_response'] ?? $quote->commercial_response,
                'commercial_conditions' => $offerData['commercial_conditions'] ?? $quote->commercial_conditions,
                'validity_days' => $validityDays,
                'valid_until' => ! empty($offerData['valid_until'])
                    ? $offerData['valid_until']
                    : now()->addDays($validityDays)->toDateString(),
                'follow_up_date' => $offerData['follow_up_date'] ?? $quote->follow_up_date,
                'assigned_seller_id' => $quote->assigned_seller_id ?? $actor->id,
                'status' => 'respondida',
                'responded_at' => now(),
                'last_email_idempotency_key' => $idempotencyKey ?: uniqid('resp_', true),
            ]);

            QuoteStatusHistory::query()->create([
                'quote_id' => $quote->id,
                'changed_by' => $actor->id,
                'previous_status' => $previousStatus,
                'new_status' => 'respondida',
                'comment' => $offerData['comment'] ?? 'Propuesta comercial generada y enviada al cliente.',
                'notified_customer' => true,
            ]);

            $this->activityLogger->log(
                'quote.responded',
                'quotes',
                sprintf('Propuesta comercial enviada para cotización %s (Total: %s %s).', $quote->reference_code, $quote->currency, number_format($totalOffered, 2)),
                $quote,
                [
                    'subtotal_offered' => $subtotal,
                    'tax_offered' => $taxOffered,
                    'total_offered' => $totalOffered,
                    'currency' => $quote->currency,
                ],
                $actor->id
            );

            return $quote->fresh(['items', 'customer', 'assignedSeller']);
        });

        try {
            $pdfBinary = $this->pdfService->generatePdfContent($quote);
            Mail::to($quote->contact_email)->send(new QuoteProposalMail($quote, $pdfBinary));

            return true;
        } catch (Throwable $e) {
            Log::error('Error enviando correo de propuesta comercial '.$quote->reference_code.': '.$e->getMessage());

            return false;
        }
    }

    /**
     * Generate sequential reference code per year: COT-YYYY-NNNNN.
     */
    protected function generateNextReferenceCode(): string
    {
        $year = now()->format('Y');
        $prefix = sprintf('COT-%s-', $year);

        $latestQuote = Quote::withTrashed()
            ->where('reference_code', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $nextSequence = 1;
        if ($latestQuote && preg_match('/^COT-\d{4}-(\d+)$/', $latestQuote->reference_code, $matches)) {
            $nextSequence = ((int) $matches[1]) + 1;
        }

        $candidate = sprintf('%s%05d', $prefix, $nextSequence);
        while (Quote::withTrashed()->where('reference_code', $candidate)->exists()) {
            $nextSequence++;
            $candidate = sprintf('%s%05d', $prefix, $nextSequence);
        }

        return $candidate;
    }
}
