@extends('layouts.public')

@section('content')
    @php
        $timelineSteps = [
            'nueva' => [
                'label' => 'Solicitud Recibida',
                'desc' => 'Registrada con código de referencia',
            ],
            'en_revision' => [
                'label' => 'En Revisión Técnica',
                'desc' => 'Validación de SKU y stock',
            ],
            'en_preparacion' => [
                'label' => 'En Preparación',
                'desc' => 'Elaboración de propuesta',
            ],
            'respondida' => [
                'label' => 'Cotización Respondida',
                'desc' => 'Precios y condiciones listos',
            ],
        ];

        $statusOrder = ['nueva' => 1, 'en_revision' => 2, 'pendiente_info' => 2, 'en_preparacion' => 3, 'respondida' => 4, 'aceptada' => 5, 'rechazada' => 5, 'cerrada' => 5];
        $currentRank = $quote ? ($statusOrder[$quote->status] ?? 1) : 0;

        $hasOfferedPrices = $quote && (
            in_array($quote->status, ['respondida', 'aceptada'], true)
            || (float) $quote->total_offered > 0
            || $quote->items->contains(fn ($i) => $i->unit_price_offered !== null && (float) $i->unit_price_offered > 0)
        );
    @endphp

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-breadcrumb :items="$breadcrumbs" />

        <!-- Page Heading & Search Box -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden mb-8">
            <div class="bg-[#0F172A] text-white p-6 sm:p-8 border-b-4 border-[#DC2626]">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Seguimiento Público de Cotización
                </h1>
                <p class="text-sm text-slate-300 mt-1.5 max-w-2xl">
                    Ingresa el <strong>Código de Referencia</strong> (ej. <code class="font-mono text-amber-300">COT-{{ date('Y') }}-00001</code>) y el <strong>Correo Electrónico</strong> registrado en tu solicitud para consultar el estado en tiempo real, ver la propuesta comercial y descargar tu PDF.
                </p>
            </div>

            <form action="{{ route('quote.track') }}" method="GET" class="p-6 sm:p-8 bg-slate-50/60">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-5">
                        <label for="track-reference-code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Código de Referencia <span class="text-[#DC2626]">*</span>
                        </label>
                        <input id="track-reference-code"
                               type="text"
                               name="reference_code"
                               value="{{ $referenceCode }}"
                               required
                               placeholder="Ej. COT-2026-00001"
                               class="w-full rounded-xl border-2 border-slate-300 bg-white px-4 py-2.5 text-sm font-mono font-bold uppercase text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                    </div>

                    <div class="md:col-span-5">
                        <label for="track-email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Correo Electrónico Registrado <span class="text-[#DC2626]">*</span>
                        </label>
                        <input id="track-email"
                               type="email"
                               name="email"
                               value="{{ $email }}"
                               required
                               placeholder="correo@empresa.pe"
                               class="w-full rounded-xl border-2 border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit"
                                class="w-full bg-[#1E3A8A] hover:bg-[#1D4ED8] text-white font-extrabold py-3 px-4 rounded-xl text-xs uppercase tracking-wider shadow-xs transition-colors flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <span>Consultar</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @if($lookupError)
            <div role="alert" class="bg-red-50 border-l-4 border-[#DC2626] rounded-xl p-5 mb-8 text-sm text-red-900 flex items-start gap-3">
                <svg class="w-5 h-5 text-[#DC2626] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/>
                </svg>
                <div>
                    <p class="font-bold">No se pudo mostrar la cotización</p>
                    <p class="mt-0.5 text-red-800">{{ $lookupError }}</p>
                </div>
            </div>
        @endif

        @if($quote)
            <!-- Quote Tracking Result Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-md overflow-hidden space-y-8 p-6 sm:p-8">
                <!-- Header Summary & Quick Actions -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-6 border-b border-slate-200">
                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="font-mono text-xl sm:text-2xl font-extrabold text-[#0F172A] tabular-nums">
                                {{ $quote->reference_code }}
                            </span>
                            <span class="text-xs font-extrabold uppercase px-3 py-1 rounded-full border {{ $quote->statusBadgeClass() }}">
                                {{ $quote->statusLabel() }}
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-600">
                            Solicitada el <strong>{{ $quote->created_at?->format('d/m/Y H:i') }}</strong>
                            @if($quote->valid_until)
                                &bull; Vigencia referencial hasta el <strong>{{ $quote->valid_until->format('d/m/Y') }}</strong>
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        @if($pdfDownloadUrl)
                            <a href="{{ $pdfDownloadUrl }}"
                               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-[#1D4ED8] text-white font-bold text-xs uppercase tracking-wider shadow-xs transition-colors">
                                <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Descargar PDF de Cotización</span>
                            </a>
                        @endif

                        @if($whatsappSummaryUrl)
                            <x-whatsapp-button
                                variant="inline"
                                :url="$whatsappSummaryUrl"
                                label="Consultar por WhatsApp"
                                ctaLocation="quote_tracking"
                                contextType="quote_list"
                                :sku="$quote->reference_code"
                                class="px-4 py-2.5 text-xs uppercase tracking-wider" />
                        @endif
                    </div>
                </div>

                <!-- Visual Status Timeline -->
                <div aria-label="Línea de tiempo del estado de la cotización">
                    <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-4">
                        Progreso de Atención Comercial
                    </h2>
                    <ol class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        @php $stepIndex = 0; @endphp
                        @foreach($timelineSteps as $stepKey => $stepInfo)
                            @php
                                $stepIndex++;
                                $isCompleted = $currentRank > $stepIndex || ($currentRank === $stepIndex && in_array($quote->status, ['respondida', 'aceptada'], true));
                                $isCurrent = ($currentRank === $stepIndex) && ! $isCompleted;
                            @endphp
                            <li class="rounded-xl border p-3.5 transition-colors {{ $isCompleted ? 'bg-emerald-50/70 border-emerald-300 text-emerald-950' : ($isCurrent ? 'bg-blue-50 border-2 border-[#1E3A8A] text-[#0F172A]' : 'bg-slate-50 border-slate-200 text-slate-500') }}">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <span class="font-mono text-xs font-extrabold uppercase">Paso 0{{ $stepIndex }}</span>
                                    @if($isCompleted)
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-600 text-white text-xs font-bold" aria-label="Completado">&#10003;</span>
                                    @elseif($isCurrent)
                                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-[#1E3A8A]"></span>
                                    @endif
                                </div>
                                <p class="text-xs sm:text-sm font-extrabold">{{ $stepInfo['label'] }}</p>
                                <p class="text-[11px] opacity-80 mt-0.5">{{ $stepInfo['desc'] }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>

                <!-- Customer & Assigned Seller Metadata -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-slate-50 rounded-xl p-4 border border-slate-200 text-xs">
                    <div>
                        <span class="text-slate-500 font-bold uppercase block">Solicitante</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $quote->contact_name }}</span>
                        <span class="block text-slate-600 mt-0.5">{{ $quote->contact_email }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 font-bold uppercase block">Empresa / RUC</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $quote->company_name ?: 'Cliente Particular' }}</span>
                        @if($quote->ruc)
                            <span class="font-mono block text-xs text-slate-600">RUC: {{ $quote->ruc }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-500 font-bold uppercase block">Ejecutivo Asignado</span>
                        <span class="font-bold text-slate-900 text-sm">
                            {{ $quote->assignedSeller?->name ?? 'Área Comercial San Luis' }}
                        </span>
                        @if($quote->responded_at)
                            <span class="block text-emerald-700 font-semibold mt-0.5">Respondida: {{ $quote->responded_at->format('d/m/Y H:i') }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-500 font-bold uppercase block">Moneda y Vigencia</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $quote->currency ?: 'PEN' }} ({{ $quote->currencySymbol() }})</span>
                        <span class="block text-slate-600 mt-0.5">{{ $quote->validity_days ?: 15 }} días calendario</span>
                    </div>
                </div>

                <!-- Commercial Response & Conditions (if responded) -->
                @if($quote->commercial_response || $quote->commercial_conditions)
                    <div class="bg-blue-50/70 border-l-4 border-[#1E3A8A] rounded-r-xl p-5 space-y-3">
                        <h2 class="text-sm font-extrabold uppercase tracking-wider text-[#1E3A8A]">
                            Respuesta y Condiciones Comerciales
                        </h2>
                        @if($quote->commercial_response)
                            <div class="text-sm text-slate-800 leading-relaxed">
                                {!! nl2br(e($quote->commercial_response)) !!}
                            </div>
                        @endif
                        @if($quote->commercial_conditions)
                            <div class="pt-2 border-t border-blue-200/80 text-xs text-slate-700">
                                <strong class="uppercase text-slate-600 block mb-1">Condiciones de la Oferta:</strong>
                                {!! nl2br(e($quote->commercial_conditions)) !!}
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Items & Offered Prices Table -->
                <div>
                    <h2 class="text-base font-extrabold text-[#0F172A] section-heading-bar mb-4">
                        Detalle de Ítems Cotizados ({{ $quote->items->count() }})
                    </h2>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-[#1E3A8A] text-white text-xs uppercase tracking-wider">
                                    <th class="py-3 px-4 font-bold">Código SKU</th>
                                    <th class="py-3 px-4 font-bold">Producto Técnico</th>
                                    <th class="py-3 px-4 font-bold">Unidad</th>
                                    <th class="py-3 px-4 font-bold text-right">Cantidad</th>
                                    @if($hasOfferedPrices)
                                        <th class="py-3 px-4 font-bold text-right">P. Unit. Ofertado</th>
                                        <th class="py-3 px-4 font-bold text-right">Subtotal</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($quote->items as $item)
                                    <tr class="odd:bg-white even:bg-slate-50">
                                        <td class="py-3 px-4 font-mono font-bold text-xs text-slate-900 tabular-nums">
                                            {{ $item->sku_snapshot }}
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($item->product && $item->product->status === 'published')
                                                <a href="{{ route('catalog.show', $item->product->slug) }}" class="font-bold text-[#0F172A] hover:text-[#1D4ED8]">
                                                    {{ $item->product_name_snapshot }}
                                                </a>
                                            @else
                                                <div class="font-bold text-slate-900">{{ $item->product_name_snapshot }}</div>
                                            @endif
                                            @if($item->item_notes)
                                                <div class="text-xs text-slate-600 mt-0.5">Nota: {{ $item->item_notes }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-xs font-semibold text-slate-700">
                                            {{ $item->sale_unit_snapshot }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 tabular-nums">
                                            {{ number_format((float) $item->quantity, 0) }}
                                        </td>
                                        @if($hasOfferedPrices)
                                            <td class="py-3 px-4 text-right font-mono text-slate-800 tabular-nums">
                                                @if($item->unit_price_offered !== null && (float) $item->unit_price_offered > 0)
                                                    {{ $quote->currencySymbol() }} {{ number_format((float) $item->unit_price_offered, 2) }}
                                                @else
                                                    <span class="text-xs text-slate-500">En evaluación</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-right font-mono font-extrabold text-slate-900 tabular-nums">
                                                @if($item->subtotal_offered !== null && (float) $item->subtotal_offered > 0)
                                                    {{ $quote->currencySymbol() }} {{ number_format((float) $item->subtotal_offered, 2) }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                            @if($hasOfferedPrices && (float) $quote->total_offered > 0)
                                <tfoot class="bg-slate-100 border-t-2 border-slate-300 text-xs sm:text-sm">
                                    <tr>
                                        <td colspan="4" class="py-2.5 px-4 text-right font-bold text-slate-600">Subtotal Ofertado:</td>
                                        <td colspan="2" class="py-2.5 px-4 text-right font-mono font-bold text-slate-900 tabular-nums">
                                            {{ $quote->currencySymbol() }} {{ number_format((float) $quote->subtotal_offered, 2) }}
                                        </td>
                                    </tr>
                                    @if((float) $quote->tax_offered > 0)
                                        <tr>
                                            <td colspan="4" class="py-2 px-4 text-right font-bold text-slate-600">IGV (18%):</td>
                                            <td colspan="2" class="py-2 px-4 text-right font-mono font-bold text-slate-900 tabular-nums">
                                                {{ $quote->currencySymbol() }} {{ number_format((float) $quote->tax_offered, 2) }}
                                            </td>
                                        </tr>
                                    @endif
                                    <tr class="bg-[#0F172A] text-white">
                                        <td colspan="4" class="py-3 px-4 text-right font-extrabold uppercase tracking-wider">Total Propuesta Comercial:</td>
                                        <td colspan="2" class="py-3 px-4 text-right font-mono text-base font-extrabold text-amber-400 tabular-nums">
                                            {{ $quote->currencySymbol() }} {{ number_format((float) $quote->total_offered, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- Status History Log -->
                @if($quote->statusHistories->isNotEmpty())
                    <div class="pt-2 border-t border-slate-200">
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-3">
                            Historial de Actualizaciones
                        </h3>
                        <ul class="space-y-2 text-xs">
                            @foreach($quote->statusHistories as $history)
                                <li class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2.5">
                                    <div>
                                        <span class="font-bold text-[#1E3A8A] uppercase">
                                            {{ \App\Models\Quote::STATUSES[$history->new_status] ?? $history->new_status }}
                                        </span>
                                        @if($history->comment)
                                            <span class="text-slate-700 ml-1.5">— {{ $history->comment }}</span>
                                        @endif
                                    </div>
                                    <span class="font-mono text-slate-500 tabular-nums shrink-0">
                                        {{ $history->created_at?->format('d/m/Y H:i') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
