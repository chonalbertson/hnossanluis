@extends('layouts.public')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-lg overflow-hidden">
            <!-- Top Success Banner -->
            <div class="bg-[#0F172A] text-white p-6 sm:p-8 border-b-4 border-emerald-500">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1.5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold uppercase tracking-wider">
                            Solicitud Registrada Exitosamente
                        </span>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-white">
                            Confirmación de Cotización
                        </h1>
                        <p class="text-sm text-slate-300">
                            Hemos enviado una copia del resumen al correo <strong class="text-white">{{ $quote->contact_email }}</strong>.
                        </p>
                    </div>

                    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 text-left sm:text-right shrink-0">
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Código de Referencia</span>
                        <span class="font-mono text-xl sm:text-2xl font-extrabold text-amber-400 tabular-nums">{{ $quote->reference_code }}</span>
                        <span class="block text-xs text-slate-300 mt-0.5">Estado: {{ $quote->statusLabel() }}</span>
                    </div>
                </div>
            </div>

            <!-- Customer & Commercial Metadata -->
            <div class="p-6 sm:p-8 space-y-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-slate-50 rounded-xl p-4 border border-slate-200 text-xs">
                    <div>
                        <span class="text-slate-500 font-bold uppercase block">Solicitante</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $quote->contact_name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 font-bold uppercase block">Empresa / RUC</span>
                        <span class="font-bold text-slate-900 text-sm">
                            {{ $quote->company_name ?: 'Particular' }}
                            @if($quote->ruc)
                                <span class="font-mono block text-xs text-slate-600">RUC: {{ $quote->ruc }}</span>
                            @endif
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-500 font-bold uppercase block">Fecha de Registro</span>
                        <span class="font-bold text-slate-900 text-sm tabular-nums">{{ $quote->created_at?->format('d/m/Y H:i') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 font-bold uppercase block">Vigencia Referencial</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $quote->validity_days }} días calendario</span>
                    </div>
                </div>

                <!-- Requested Items Summary Table -->
                <div>
                    <h2 class="text-base font-extrabold text-[#0F172A] section-heading-bar mb-4">
                        Resumen de Ítems Solicitados ({{ $quote->items->count() }})
                    </h2>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-[#1E3A8A] text-white text-xs uppercase tracking-wider">
                                    <th class="py-3 px-4 font-bold">Código SKU</th>
                                    <th class="py-3 px-4 font-bold">Producto Técnico</th>
                                    <th class="py-3 px-4 font-bold">Unidad</th>
                                    <th class="py-3 px-4 font-bold text-right">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($quote->items as $item)
                                    <tr class="odd:bg-white even:bg-slate-50">
                                        <td class="py-3 px-4 font-mono font-bold text-xs text-slate-900 tabular-nums">
                                            {{ $item->sku_snapshot }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-semibold text-slate-900">{{ $item->product_name_snapshot }}</div>
                                            @if($item->item_notes)
                                                <div class="text-xs text-slate-600 mt-0.5">Nota: {{ $item->item_notes }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-xs font-semibold text-slate-700">
                                            {{ $item->sale_unit_snapshot }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono font-extrabold text-slate-900 tabular-nums">
                                            {{ number_format((float) $item->quantity, 0) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($quote->customer_observations)
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs">
                        <strong class="uppercase text-slate-600 block mb-1">Observaciones Generales Registradas:</strong>
                        <p class="text-slate-800 text-sm">{{ $quote->customer_observations }}</p>
                    </div>
                @endif

                <!-- WhatsApp Summary Conversion Box -->
                <div class="bg-emerald-50 border-2 border-emerald-200 rounded-xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <h3 class="text-base font-extrabold text-emerald-950">¿Necesitas atención inmediata por WhatsApp?</h3>
                        <p class="text-xs text-emerald-800 leading-relaxed">
                            Envía el código <strong class="font-mono">{{ $quote->reference_code }}</strong> directamente a nuestro equipo comercial de WhatsApp para agilizar la validación de stock.
                        </p>
                    </div>

                    <x-whatsapp-button
                        variant="inline"
                        :url="$whatsappSummaryUrl"
                        label="Enviar Resumen por WhatsApp"
                        ctaLocation="quote_confirmation"
                        contextType="quote_list"
                        :sku="$quote->reference_code"
                        class="px-5 py-3 text-xs uppercase tracking-wider shrink-0 shadow-md" />
                </div>

                <div class="pt-4 border-t border-slate-200 flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ route('catalog.index') }}" class="text-xs sm:text-sm font-bold text-[#1D4ED8] hover:underline">
                        &larr; Volver al Catálogo Técnico
                    </a>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <a href="{{ route('quote.track', ['reference_code' => $quote->reference_code, 'email' => $quote->contact_email]) }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-[#1E3A8A] hover:bg-[#1D4ED8] text-white text-xs font-bold uppercase tracking-wider transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Seguimiento de Cotización</span>
                        </a>
                        <a href="{{ route('quote.track.pdf', ['reference_code' => $quote->reference_code, 'email' => $quote->contact_email]) }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-800 text-xs font-bold uppercase tracking-wider transition-colors">
                            <svg class="w-4 h-4 text-[#DC2626]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Descargar PDF</span>
                        </a>
                        @auth
                            <a href="{{ route('profile.edit') }}" class="px-4 py-2.5 rounded-lg bg-slate-900 text-white text-xs font-bold uppercase tracking-wider">
                                Mi Historial
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
