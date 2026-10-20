<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\InternalNote;
use App\Models\Product;
use App\Models\Quote;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\ActivityLoggerService;
use App\Services\QuotePdfService;
use App\Services\QuoteService;
use App\Services\WhatsAppService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class QuoteController extends Controller
{
    public function __construct(
        protected QuoteService $quoteService,
        protected QuotePdfService $pdfService,
        protected WhatsAppService $whatsAppService,
        protected ActivityLoggerService $activityLogger
    ) {}

    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->hasPermission('quotes.view')
            || $request->user()?->hasPermission('quotes.manage')
            || $request->user()?->hasPermission('quotes.respond'),
            403
        );

        $query = $this->buildFilteredQuery($request);
        $quotes = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $sellers = User::query()
            ->where('is_active', true)
            ->whereHas('role', fn ($q) => $q->whereIn('slug', ['admin', 'seller']))
            ->orderBy('name')
            ->get();

        return view('admin.quotes.index', [
            'quotes' => $quotes,
            'statuses' => Quote::availableStatuses(),
            'sellers' => $sellers,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless(
            $request->user()?->hasPermission('quotes.manage')
            || $request->user()?->hasPermission('quotes.respond'),
            403
        );

        $customers = Customer::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'company', 'ruc']);

        $products = Product::query()
            ->where('status', 'published')
            ->orderBy('sku')
            ->get(['id', 'sku', 'name', 'sale_unit', 'price', 'currency']);

        $sellers = User::query()
            ->where('is_active', true)
            ->whereHas('role', fn ($q) => $q->whereIn('slug', ['admin', 'seller']))
            ->orderBy('name')
            ->get();

        $defaultCurrency = (string) SiteSetting::getValue('commercial_default_currency', 'PEN');

        return view('admin.quotes.create', [
            'customers' => $customers,
            'products' => $products,
            'sellers' => $sellers,
            'defaultCurrency' => $defaultCurrency,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(
            $request->user()?->hasPermission('quotes.manage')
            || $request->user()?->hasPermission('quotes.respond'),
            403
        );

        if ($request->filled('customer_id')) {
            $selectedCustomer = Customer::query()->find((int) $request->input('customer_id'));
            if ($selectedCustomer) {
                $request->merge([
                    'contact_name' => $request->input('contact_name') ?: $selectedCustomer->name,
                    'contact_email' => $request->input('contact_email') ?: $selectedCustomer->email,
                    'contact_phone' => $request->input('contact_phone') ?: ($selectedCustomer->phone ?: '900000000'),
                    'company_name' => $request->input('company_name') ?: $selectedCustomer->company,
                    'ruc' => $request->input('ruc') ?: $selectedCustomer->ruc,
                ]);
            }
        }

        $validated = $request->validate([
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'contact_name' => ['required', 'string', 'max:180'],
            'contact_email' => ['required', 'email', 'max:180'],
            'contact_phone' => ['required', 'string', 'max:40'],
            'company_name' => ['nullable', 'string', 'max:200'],
            'ruc' => ['nullable', 'digits:11'],
            'origin' => ['required', Rule::in(['manual', 'whatsapp', 'web_catalog'])],
            'currency' => ['nullable', Rule::in(['PEN', 'USD', 'EUR'])],
            'assigned_seller_id' => ['nullable', 'integer', 'exists:users,id'],
            'customer_observations' => ['nullable', 'string', 'max:3000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:999999'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        $quote = $this->quoteService->createQuote(
            [
                'contact_name' => $validated['contact_name'],
                'contact_email' => $validated['contact_email'],
                'contact_phone' => $validated['contact_phone'],
                'company_name' => $validated['company_name'] ?? null,
                'ruc' => $validated['ruc'] ?? null,
                'origin' => $validated['origin'],
                'currency' => $validated['currency'] ?? null,
                'assigned_seller_id' => $validated['assigned_seller_id'] ?? $request->user()->id,
                'customer_observations' => $validated['customer_observations'] ?? null,
                'request_type' => count($validated['items']) === 1 ? 'single' : 'multiple',
            ],
            $validated['items'],
            $request->user()
        );

        return redirect()
            ->route('admin.quotes.show', $quote)
            ->with('success', sprintf(
                'Cotización %s registrada exitosamente (%d ítem(s) por canal %s). Ahora puede cotizar precios o generar el PDF.',
                $quote->reference_code,
                $quote->items->count(),
                strtoupper($quote->origin)
            ));
    }

    public function show(Request $request, Quote $quote): View
    {
        abort_unless(
            $request->user()?->hasPermission('quotes.view')
            || $request->user()?->hasPermission('quotes.manage')
            || $request->user()?->hasPermission('quotes.respond'),
            403
        );

        $quote->load([
            'customer',
            'assignedSeller',
            'items.product',
            'statusHistories.actor',
            'internalNotes.user',
        ]);

        $sellers = User::query()
            ->where('is_active', true)
            ->whereHas('role', fn ($q) => $q->whereIn('slug', ['admin', 'seller']))
            ->orderBy('name')
            ->get();

        $defaultConditions = (string) SiteSetting::getValue(
            'commercial_default_quote_conditions',
            'Precios sujetos a confirmación de stock al momento de la orden de compra.'
        );

        return view('admin.quotes.show', [
            'quote' => $quote,
            'statuses' => Quote::availableStatuses(),
            'sellers' => $sellers,
            'defaultConditions' => $defaultConditions,
            'whatsAppUrl' => $this->whatsAppService->quoteSummaryUrl($quote),
            'idempotencyKey' => uniqid('quote_resp_'.$quote->id.'_', true),
        ]);
    }

    public function assign(Request $request, Quote $quote): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('quotes.manage'), 403);

        $validated = $request->validate([
            'assigned_seller_id' => ['nullable', 'integer', 'exists:users,id'],
            'follow_up_date' => ['nullable', 'date'],
        ]);

        $quote->update([
            'assigned_seller_id' => $validated['assigned_seller_id'] ?? null,
            'follow_up_date' => $validated['follow_up_date'] ?? $quote->follow_up_date,
        ]);

        $sellerName = $quote->assignedSeller?->name ?? 'Sin asignar';

        $this->activityLogger->log(
            'quote.assigned',
            'quotes',
            sprintf('Cotización %s asignada al ejecutivo: %s.', $quote->reference_code, $sellerName),
            $quote,
            ['assigned_seller_id' => $quote->assigned_seller_id]
        );

        return back()->with('success', sprintf('Asignación comercial de la cotización %s actualizada.', $quote->reference_code));
    }

    public function changeStatus(Request $request, Quote $quote): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('quotes.manage'), 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Quote::availableStatuses()))],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->quoteService->changeStatus(
            $quote,
            $validated['status'],
            $request->user(),
            $validated['comment'] ?? null
        );

        return back()->with('success', sprintf(
            'Estado de la cotización %s actualizado a "%s".',
            $quote->reference_code,
            Quote::availableStatuses()[$validated['status']] ?? $validated['status']
        ));
    }

    public function storeNote(Request $request, Quote $quote): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('quotes.manage'), 403);

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $note = InternalNote::query()->create([
            'quote_id' => $quote->id,
            'user_id' => $request->user()->id,
            'note' => trim($validated['note']),
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        $this->activityLogger->log(
            'quote.note_added',
            'quotes',
            sprintf('Nota interna agregada a la cotización %s.', $quote->reference_code),
            $quote,
            ['note_id' => $note->id]
        );

        return back()->with('success', 'Nota interna registrada correctamente.');
    }

    public function destroyNote(Request $request, Quote $quote, InternalNote $note): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('quotes.manage'), 403);
        abort_unless($note->quote_id === $quote->id, 404);

        $note->delete();

        return back()->with('success', 'Nota interna eliminada.');
    }

    public function respond(Request $request, Quote $quote): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('quotes.respond'), 403);

        $validated = $request->validate([
            'unit_prices' => ['required', 'array'],
            'unit_prices.*' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'currency' => ['required', Rule::in(['PEN', 'USD', 'EUR'])],
            'include_tax' => ['nullable', 'boolean'],
            'validity_days' => ['required', 'integer', 'min:1', 'max:365'],
            'commercial_response' => ['required', 'string', 'max:5000'],
            'commercial_conditions' => ['nullable', 'string', 'max:3000'],
            'follow_up_date' => ['nullable', 'date'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'idempotency_key' => ['nullable', 'string', 'max:120'],
        ]);

        $validated['include_tax'] = $request->boolean('include_tax', true);

        $sent = $this->quoteService->sendCommercialResponse(
            $quote,
            $validated,
            $request->user(),
            $validated['idempotency_key'] ?? null
        );

        if ($sent) {
            return back()->with('success', sprintf(
                'Propuesta comercial registrada, PDF generado y correo enviado al cliente (%s) para la cotización %s.',
                $quote->contact_email,
                $quote->reference_code
            ));
        }

        return back()->with('warning', sprintf(
            'Los precios y condiciones de la cotización %s se guardaron correctamente, pero hubo una incidencia al despachar el correo SMTP. Puede descargar el PDF y enviarlo al cliente.',
            $quote->reference_code
        ));
    }

    public function pdf(Request $request, Quote $quote): Response
    {
        abort_unless(
            $request->user()?->hasPermission('quotes.view')
            || $request->user()?->hasPermission('quotes.manage')
            || $request->user()?->hasPermission('quotes.respond'),
            403
        );

        return $this->pdfService->streamPdf($quote, $request->boolean('download'));
    }

    public function export(Request $request): Response
    {
        abort_unless(
            $request->user()?->hasPermission('quotes.view')
            || $request->user()?->hasPermission('quotes.manage')
            || $request->user()?->hasPermission('quotes.respond'),
            403
        );

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, [
            'codigo_cotizacion',
            'fecha_solicitud',
            'estado',
            'contacto',
            'empresa',
            'ruc',
            'email',
            'telefono',
            'ejecutivo_asignado',
            'tipo_solicitud',
            'canal_origen',
            'cantidad_items',
            'moneda',
            'subtotal_ofertado',
            'igv_ofertado',
            'total_ofertado',
        ]);

        $exportedCount = 0;

        $this->buildFilteredQuery($request, false)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->chunk(500, function ($quotes) use ($stream, &$exportedCount) {
                foreach ($quotes as $q) {
                    $exportedCount++;
                    fputcsv($stream, [
                        $q->reference_code,
                        $q->created_at?->format('Y-m-d H:i'),
                        $q->statusLabel(),
                        $q->contact_name,
                        $q->company_name ?? '',
                        $q->ruc ?? '',
                        $q->contact_email,
                        $q->contact_phone,
                        $q->assignedSeller?->name ?? 'Sin asignar',
                        $q->request_type,
                        $q->origin,
                        $q->items_count,
                        $q->currency,
                        $q->subtotal_offered ?? '',
                        $q->tax_offered ?? '',
                        $q->total_offered ?? '',
                    ]);
                }
            });

        rewind($stream);
        $csv = stream_get_contents($stream) ?: '';
        fclose($stream);

        $this->activityLogger->log(
            'quotes.exported',
            'quotes',
            sprintf('Exportación CSV de %d solicitudes de cotización.', $exportedCount)
        );

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => sprintf('attachment; filename="cotizaciones-sanluis-%s.csv"', now()->format('Ymd-His')),
        ]);
    }

    protected function buildFilteredQuery(Request $request, bool $withItems = true): Builder
    {
        $query = $withItems
            ? Quote::query()->with(['customer', 'assignedSeller', 'items'])
            : Quote::query()->with(['customer', 'assignedSeller'])->withCount('items');

        if ($request->filled('q')) {
            $term = '%'.trim($request->string('q')->toString()).'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('reference_code', 'like', $term)
                    ->orWhere('contact_name', 'like', $term)
                    ->orWhere('contact_email', 'like', $term)
                    ->orWhere('company_name', 'like', $term)
                    ->orWhere('ruc', 'like', $term)
                    ->orWhere('contact_phone', 'like', $term);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('assigned_seller_id')) {
            $sellerFilter = $request->string('assigned_seller_id')->toString();
            if ($sellerFilter === 'unassigned') {
                $query->whereNull('assigned_seller_id');
            } else {
                $query->where('assigned_seller_id', (int) $sellerFilter);
            }
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->string('date_from')->toString())->startOfDay());
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->string('date_to')->toString())->endOfDay());
        }

        return $query;
    }
}
