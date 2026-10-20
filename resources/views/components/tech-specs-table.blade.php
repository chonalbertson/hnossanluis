@props([
    'product',
    'groupedSpecs' => null,
])

@php
    $specsGroups = $groupedSpecs ?? $product->specifications->groupBy(fn ($s) => $s->spec_group ?: 'Especificaciones Técnicas');

    $pdfPayload = json_encode([
        'file_name' => 'Ficha-Tecnica-'.$product->sku.'.pdf',
        'file_extension' => 'pdf',
        'link_text' => 'Descargar Ficha Técnica (PDF)',
        'item_id' => $product->sku,
        'item_name' => $product->name,
        'item_category' => $product->category?->name ?? '',
    ], JSON_UNESCAPED_UNICODE);
@endphp

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-2xs">
    <!-- Technical Header Bar -->
    <div class="bg-[#1E3A8A] text-white px-5 py-3.5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <svg class="w-5 h-5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h2 class="text-base font-bold tracking-wide uppercase">Ficha de Especificaciones Técnicas</h2>
        </div>
        <div class="flex items-center gap-3 text-xs font-mono bg-blue-950/60 px-3 py-1 rounded border border-blue-700">
            <span>SKU: <strong>{{ $product->sku }}</strong></span>
            <span>|</span>
            <span>Unidad: <strong>{{ $product->sale_unit }}</strong></span>
        </div>
    </div>

    <!-- Base Engineering Attributes Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <caption class="sr-only">Especificaciones técnicas de {{ $product->name }}</caption>
            <tbody class="divide-y divide-slate-200 text-sm">
                <tr class="bg-slate-50/70">
                    <th scope="colgroup" colspan="2" class="py-2.5 px-5 text-xs font-bold uppercase tracking-wider text-[#1E3A8A] bg-blue-50/80 border-b border-blue-100">
                        Identificación y Parámetros Base
                    </th>
                </tr>
                <tr class="odd:bg-white even:bg-[#F8FAFC]">
                    <th scope="row" class="w-2/5 py-3 px-5 font-semibold text-slate-700 bg-slate-50/50">Código SKU</th>
                    <td class="w-3/5 py-3 px-5 font-mono font-bold text-slate-900 tabular-nums">{{ $product->sku }}</td>
                </tr>
                @if($product->brand)
                <tr class="odd:bg-white even:bg-[#F8FAFC]">
                    <th scope="row" class="w-2/5 py-3 px-5 font-semibold text-slate-700 bg-slate-50/50">Marca</th>
                    <td class="w-3/5 py-3 px-5 font-medium text-slate-900">{{ $product->brand->name }}</td>
                </tr>
                @endif
                <tr class="odd:bg-white even:bg-[#F8FAFC]">
                    <th scope="row" class="w-2/5 py-3 px-5 font-semibold text-slate-700 bg-slate-50/50">Unidad de Venta / Despacho</th>
                    <td class="w-3/5 py-3 px-5 font-semibold text-slate-900">{{ $product->sale_unit }}</td>
                </tr>
                @if($product->dimensions)
                <tr class="odd:bg-white even:bg-[#F8FAFC]">
                    <th scope="row" class="w-2/5 py-3 px-5 font-semibold text-slate-700 bg-slate-50/50">Dimensiones / Medidas</th>
                    <td class="w-3/5 py-3 px-5 font-medium text-slate-900 tabular-nums">{{ $product->dimensions }}</td>
                </tr>
                @endif
                @if($product->material)
                <tr class="odd:bg-white even:bg-[#F8FAFC]">
                    <th scope="row" class="w-2/5 py-3 px-5 font-semibold text-slate-700 bg-slate-50/50">Material de Fabricación</th>
                    <td class="w-3/5 py-3 px-5 font-medium text-slate-900">{{ $product->material }}</td>
                </tr>
                @endif

                <!-- Dynamic Grouped Specifications -->
                @foreach($specsGroups as $groupName => $specs)
                    <tr>
                        <th scope="colgroup" colspan="2" class="py-2.5 px-5 text-xs font-bold uppercase tracking-wider text-[#1E3A8A] bg-blue-50/80 border-y border-blue-100">
                            {{ $groupName }}
                        </th>
                    </tr>
                    @foreach($specs as $spec)
                        <tr class="odd:bg-white even:bg-[#F8FAFC]">
                            <th scope="row" class="w-2/5 py-3 px-5 font-semibold text-slate-700 bg-slate-50/50">
                                {{ $spec->spec_key }}
                            </th>
                            <td class="w-3/5 py-3 px-5 font-medium text-slate-900 tabular-nums">
                                {{ $spec->spec_value }}@if($spec->unit) <span class="text-slate-600">{{ $spec->unit }}</span>@endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- PDF Datasheet Download Callout -->
    <div class="p-4 sm:p-5 bg-[#EFF6FF] border-t border-[#BFDBFE] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-lg bg-[#DC2626] text-white flex items-center justify-center shrink-0 font-bold text-xs">
                PDF
            </div>
            <div>
                <h3 class="text-sm font-bold text-[#0F172A]">Documento de Ficha Técnica — {{ $product->sku }}</h3>
                <p class="text-xs text-slate-600">Descarga la hoja técnica en formato PDF para adjuntar a tus expedientes de compra o proyectos.</p>
            </div>
        </div>
        <a href="{{ route('catalog.download-pdf', $product->slug) }}"
           data-ga-event="file_download"
           data-ga-params="{{ $pdfPayload }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-bold text-xs uppercase tracking-wider transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <span>Descargar Ficha Técnica (PDF)</span>
        </a>
    </div>
</div>
