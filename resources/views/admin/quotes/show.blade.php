@extends('layouts.admin')

@section('title', 'Cotización ' . $quote->reference_code)
@section('header', 'Cotización N° ' . $quote->reference_code)
@section('subheader', 'Solicitada el ' . $quote->created_at?->format('d/m/Y H:i') . ' por ' . $quote->contact_name)

@section('content')
    <!-- Top Summary & Actions -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $quote->statusBadgeClass() }}">
                Estado: {{ $quote->statusLabel() }}
            </span>
            <span class="text-xs text-slate-500">
                Canal: <strong class="text-slate-800">{{ $quote->origin }}</strong> |
                Tipo: <strong class="text-slate-800">{{ $quote->request_type === 'single' ? 'Cotización Rápida' : 'Lista Múltiple' }}</strong>
            </span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ $whatsAppUrl }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 text-xs font-semibold">
                <span>Contactar por WhatsApp</span>
            </a>
            <a href="{{ route('admin.quotes.pdf', $quote) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-800 hover:bg-slate-50 text-xs font-semibold">
                <span>Vista Previa PDF</span>
            </a>
            <a href="{{ route('admin.quotes.pdf', ['quote' => $quote, 'download' => 1]) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 text-xs font-semibold">
                <span>⬇ Descargar PDF</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Columns: Items, Commercial Proposal Form & History -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Customer Info Card -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
                <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 mb-3">Datos del Solicitante / Empresa</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-slate-500 block">Contacto Autorizado</span>
                        <strong class="text-slate-900 text-sm">{{ $quote->contact_name }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Empresa / Razón Social</span>
                        <strong class="text-slate-900">{{ $quote->company_name ?: 'Persona Natural / Particular' }}</strong>
                        @if($quote->ruc)
                            <div class="font-mono text-slate-600">RUC: {{ $quote->ruc }}</div>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-500 block">Correo y Teléfono</span>
                        <div class="font-semibold text-blue-600">{{ $quote->contact_email }}</div>
                        <div class="font-semibold text-slate-800">{{ $quote->contact_phone }}</div>
                    </div>
                </div>
                @if($quote->customer_observations)
                    <div class="mt-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-950">
                        <strong class="block mb-1">Observaciones / Requerimiento del Cliente:</strong>
                        {{ $quote->customer_observations }}
                    </div>
                @endif
            </div>

            <!-- Commercial Offer & Email Response Form -->
            @if(auth()->user()?->hasPermission('quotes.respond'))
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
                    <div class="border-b border-slate-100 pb-3 mb-4">
                        <h2 class="text-sm font-bold text-slate-900">Elaborar Propuesta Comercial y Enviar Cotización por Correo</h2>
                        <p class="text-xs text-slate-500">Ingrese los precios unitarios ofertados por ítem. Al enviar, se actualizará el PDF comercial y se despachará al cliente sin duplicados.</p>
                    </div>

                    <form method="POST" action="{{ route('admin.quotes.respond', $quote) }}" class="space-y-4 text-xs">
                        @csrf
                        <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

                        <div class="overflow-x-auto border border-slate-200 rounded-lg">
                            <table class="min-w-full divide-y divide-slate-200 text-xs">
                                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                                    <tr>
                                        <th class="px-3 py-2.5 text-left">SKU / Producto</th>
                                        <th class="px-3 py-2.5 text-center">Unidad</th>
                                        <th class="px-3 py-2.5 text-center">Cantidad</th>
                                        <th class="px-3 py-2.5 text-right">Precio Unit. Ofertado</th>
                                        <th class="px-3 py-2.5 text-right">Subtotal Ofertado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($quote->items as $item)
                                        <tr>
                                            <td class="px-3 py-2.5">
                                                <div class="font-mono font-bold text-slate-900">{{ $item->sku_snapshot }}</div>
                                                <div class="font-medium text-slate-800">{{ $item->product_name_snapshot }}</div>
                                                @if($item->item_notes)
                                                    <div class="text-[11px] text-amber-700">Nota ítem: {{ $item->item_notes }}</div>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2.5 text-center text-slate-600">{{ $item->sale_unit_snapshot }}</td>
                                            <td class="px-3 py-2.5 text-center font-bold text-slate-900">{{ number_format((float)$item->quantity, 2) }}</td>
                                            <td class="px-3 py-2.5 text-right">
                                                <input type="number" step="0.01" min="0"
                                                       name="unit_prices[{{ $item->id }}]"
                                                       value="{{ old('unit_prices.' . $item->id, $item->unit_price_offered ?? ((float) $item->product?->price > 0 ? $item->product?->price : null)) }}"
                                                       placeholder="0.00"
                                                       class="w-28 rounded border border-slate-300 px-2 py-1 text-right font-semibold focus:border-amber-500 focus:outline-none">
                                            </td>
                                            <td class="px-3 py-2.5 text-right font-semibold text-slate-800">
                                                {{ $item->subtotal_offered !== null ? number_format((float)$item->subtotal_offered, 2) : '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Moneda de la Oferta</label>
                                <select name="currency" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                                    <option value="PEN" @selected(old('currency', $quote->currency) === 'PEN')>Soles (PEN - S/)</option>
                                    <option value="USD" @selected(old('currency', $quote->currency) === 'USD')>Dólares (USD - $)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Validez de Oferta (Días)</label>
                                <input type="number" name="validity_days" value="{{ old('validity_days', $quote->validity_days ?: 15) }}" min="1" max="365"
                                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
                            </div>
                            <div class="flex items-end pb-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="include_tax" value="1" checked class="rounded border-slate-300 text-amber-500">
                                    <span class="font-semibold text-slate-700">Calcular IGV (18%) en Totales</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Mensaje Comercial para el Cliente <span class="text-rose-600">*</span></label>
                            <textarea name="commercial_response" rows="3" required
                                      placeholder="Estimado cliente, remitimos nuestra propuesta técnica y económica para su requerimiento..."
                                      class="w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('commercial_response', $quote->commercial_response ?: 'Estimado(a) ' . $quote->contact_name . ', adjuntamos nuestra propuesta comercial detallada para los productos solicitados. Quedamos a su disposición para coordinar la orden de compra y despacho.') }}</textarea>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Condiciones Comerciales y Plazo de Entrega</label>
                            <textarea name="commercial_conditions" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('commercial_conditions', $quote->commercial_conditions ?: $defaultConditions) }}</textarea>
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                            <div class="text-slate-600">
                                @if($quote->total_offered !== null)
                                    Total Ofertado Actual: <strong class="text-slate-900 text-sm">{{ $quote->currencySymbol() }} {{ number_format((float)$quote->total_offered, 2) }}</strong>
                                @endif
                            </div>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 shadow-xs cursor-pointer">
                                Guardar Propuesta, Generar PDF y Enviar por Correo
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <!-- Read-Only Items Table for Supervisors -->
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
                    <h2 class="text-sm font-bold text-slate-900 mb-3">Ítems Solicitados ({{ $quote->items->count() }})</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-xs">
                            <thead class="bg-slate-50 font-semibold text-slate-600">
                                <tr>
                                    <th class="px-3 py-2 text-left">SKU / Producto</th>
                                    <th class="px-3 py-2 text-center">Unidad</th>
                                    <th class="px-3 py-2 text-center">Cantidad</th>
                                    <th class="px-3 py-2 text-right">Precio Unit.</th>
                                    <th class="px-3 py-2 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($quote->items as $item)
                                    <tr>
                                        <td class="px-3 py-2 font-mono font-bold">{{ $item->sku_snapshot }} — {{ $item->product_name_snapshot }}</td>
                                        <td class="px-3 py-2 text-center">{{ $item->sale_unit_snapshot }}</td>
                                        <td class="px-3 py-2 text-center font-bold">{{ $item->quantity }}</td>
                                        <td class="px-3 py-2 text-right">{{ $item->unit_price_offered ?? '—' }}</td>
                                        <td class="px-3 py-2 text-right font-semibold">{{ $item->subtotal_offered ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Status History Timeline -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
                <h2 class="text-sm font-bold text-slate-900 mb-3">Historial de Estados y Trazabilidad</h2>
                <div class="space-y-3 text-xs">
                    @foreach($quote->statusHistories as $history)
                        <div class="flex items-start justify-between gap-3 p-3 rounded-lg bg-slate-50 border border-slate-200">
                            <div>
                                <div class="font-bold text-slate-900">
                                    Cambio a: <span class="uppercase text-amber-700">{{ \App\Models\Quote::STATUSES[$history->new_status] ?? $history->new_status }}</span>
                                    @if($history->previous_status)
                                        <span class="text-slate-400 font-normal">(desde {{ \App\Models\Quote::STATUSES[$history->previous_status] ?? $history->previous_status }})</span>
                                    @endif
                                </div>
                                @if($history->comment)
                                    <div class="text-slate-600 mt-1">{{ $history->comment }}</div>
                                @endif
                            </div>
                            <div class="text-right text-[11px] text-slate-500 shrink-0">
                                <div>{{ $history->created_at?->format('d/m/Y H:i') }}</div>
                                <div class="font-medium">{{ $history->actor?->name ?? 'Sistema / Web' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Column: Assignment, Status Change & Internal Notes -->
        <div class="space-y-6">
            @if(auth()->user()?->hasPermission('quotes.manage'))
                <!-- Seller Assignment -->
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
                    <h3 class="text-sm font-bold text-slate-900 mb-3">Asignación de Ejecutivo</h3>
                    <form method="POST" action="{{ route('admin.quotes.assign', $quote) }}" class="space-y-3 text-xs">
                        @csrf
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Ejecutivo Responsable</label>
                            <select name="assigned_seller_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                                <option value="">— Sin asignar —</option>
                                @foreach($sellers as $seller)
                                    <option value="{{ $seller->id }}" @selected((int)$quote->assigned_seller_id === $seller->id)>{{ $seller->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Fecha de Próximo Seguimiento</label>
                            <input type="date" name="follow_up_date" value="{{ $quote->follow_up_date?->format('Y-m-d') }}"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2">
                        </div>
                        <button type="submit" class="w-full py-2 rounded-lg bg-slate-900 text-white font-semibold hover:bg-slate-800 cursor-pointer">
                            Guardar Asignación
                        </button>
                    </form>
                </div>

                <!-- Status Change -->
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
                    <h3 class="text-sm font-bold text-slate-900 mb-3">Cambiar Estado de Cotización</h3>
                    <form method="POST" action="{{ route('admin.quotes.status', $quote) }}" class="space-y-3 text-xs">
                        @csrf
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nuevo Estado</label>
                            <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white font-semibold">
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}" @selected($quote->status === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Comentario de Seguimiento</label>
                            <textarea name="comment" rows="2" placeholder="Motivo del cambio o acuerdo con el cliente..." class="w-full rounded-lg border border-slate-300 px-3 py-2"></textarea>
                        </div>
                        <button type="submit" class="w-full py-2 rounded-lg bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 cursor-pointer">
                            Actualizar Estado
                        </button>
                    </form>
                </div>

                <!-- Internal Notes -->
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs space-y-4">
                    <h3 class="text-sm font-bold text-slate-900">Notas Internas (Solo Equipo)</h3>
                    <form method="POST" action="{{ route('admin.quotes.notes', $quote) }}" class="space-y-2 text-xs">
                        @csrf
                        <textarea name="note" rows="2" required placeholder="Escriba una nota interna sobre stock, descuento o llamada..."
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2"></textarea>
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" name="is_pinned" value="1" class="rounded border-slate-300 text-amber-500">
                                <span>Fijar nota</span>
                            </label>
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-900 text-white font-semibold hover:bg-slate-800 cursor-pointer">
                                + Agregar Nota
                            </button>
                        </div>
                    </form>

                    <div class="space-y-2 pt-2 border-t border-slate-100 text-xs">
                        @forelse($quote->internalNotes as $note)
                            <div class="p-3 rounded-lg {{ $note->is_pinned ? 'bg-amber-50 border border-amber-200' : 'bg-slate-50 border border-slate-200' }}">
                                <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1">
                                    <span class="font-bold text-slate-700">{{ $note->user?->name ?? 'Usuario' }}</span>
                                    <span>{{ $note->created_at?->format('d/m H:i') }}</span>
                                </div>
                                <p class="text-slate-800">{{ $note->note }}</p>
                            </div>
                        @empty
                            <p class="text-slate-400 text-center py-2">Sin notas internas registradas.</p>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
