@extends('layouts.admin')

@section('title', 'Detalle Técnico ' . $product->sku)
@section('header', 'Ficha Técnica Interna: ' . $product->sku)
@section('subheader', $product->name)

@section('content')
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
                <span class="font-mono text-xs font-bold px-2.5 py-1 rounded bg-slate-900 text-amber-400">{{ $product->sku }}</span>
                <h2 class="text-lg font-bold text-slate-900 mt-2">{{ $product->name }}</h2>
                <p class="text-xs text-slate-500">
                    Categoría: <strong>{{ $product->category?->name }}</strong>
                    @if($product->subcategory) &rarr; <strong>{{ $product->subcategory->name }}</strong> @endif
                    | Marca: <strong>{{ $product->brand?->name ?? 'Genérica' }}</strong>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ url('/catalogo/' . $product->slug) }}" target="_blank" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    Ver en Catálogo Público
                </a>
                @if(auth()->user()?->hasPermission('catalog.manage'))
                    <a href="{{ route('admin.products.edit', $product) }}" class="px-4 py-2 rounded-lg bg-amber-500 text-slate-950 font-bold text-xs hover:bg-amber-400">
                        Editar Producto
                    </a>
                    <form method="POST" action="{{ route('admin.products.force-destroy', $product->id) }}" onsubmit="return confirm('¿Eliminar definitivamente este producto, sus imágenes, fichas técnicas y toda su información asociada? Esta acción no se puede deshacer.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 rounded-lg bg-rose-600 text-white font-bold text-xs hover:bg-rose-700 cursor-pointer">
                            Eliminar Producto
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">
            <div class="space-y-2 bg-slate-50 p-4 rounded-lg border border-slate-200">
                <div class="font-bold text-slate-800 uppercase">Datos Comerciales</div>
                <div>Unidad de Venta: <strong>{{ $product->sale_unit }}</strong></div>
                <div>Precio Registrado: <strong>{{ (float) $product->price > 0 ? $product->currency . ' ' . number_format((float)$product->price, 2) : 'Bajo cotización' }}</strong></div>
                <div>Política de Visibilidad: <strong>{{ $product->price_visibility }}</strong></div>
                <div>Disponibilidad: <span class="px-2 py-0.5 rounded-full font-semibold {{ $product->availabilityBadgeClass() }}">{{ $product->availabilityLabel() }}</span></div>
            </div>

            <div class="space-y-2 bg-slate-50 p-4 rounded-lg border border-slate-200">
                <div class="font-bold text-slate-800 uppercase">Características Físicas</div>
                <div>Medidas / Dimensiones: <strong>{{ $product->dimensions ?: 'No especificadas' }}</strong></div>
                <div>Material / Aleación: <strong>{{ $product->material ?: 'No especificado' }}</strong></div>
                <div>Vistas acumuladas: <strong>{{ number_format($product->views_count) }}</strong></div>
            </div>

            <div class="space-y-2 bg-slate-50 p-4 rounded-lg border border-slate-200">
                <div class="font-bold text-slate-800 uppercase">Resumen Técnico</div>
                <p class="text-slate-600">{{ $product->short_description ?: 'Sin descripción breve.' }}</p>
            </div>
        </div>

        @if($product->variants->isNotEmpty())
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-900">Variantes / Presentaciones Registradas ({{ $product->variants->count() }})</h3>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">Producto Variable</span>
                </div>
                <div class="overflow-x-auto border border-slate-200 rounded-lg">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="bg-slate-50 font-semibold text-slate-600">
                            <tr>
                                <th class="px-4 py-2 text-left">SKU Variante</th>
                                <th class="px-4 py-2 text-left">Medida / Etiqueta</th>
                                <th class="px-4 py-2 text-left">Dimensiones / Material</th>
                                <th class="px-4 py-2 text-left">Unidad / Empaque</th>
                                <th class="px-4 py-2 text-left">Precio Ref.</th>
                                <th class="px-4 py-2 text-left">Disponibilidad</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($product->variants as $variant)
                                <tr>
                                    <td class="px-4 py-2 font-mono font-bold text-slate-900">{{ $variant->sku }}</td>
                                    <td class="px-4 py-2 font-bold text-blue-900">{{ $variant->variant_label }}</td>
                                    <td class="px-4 py-2 text-slate-600">
                                        {{ $variant->dimensions ?: '—' }}
                                        @if($variant->material)
                                            <span class="text-slate-400">({{ $variant->material }})</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-slate-700">
                                        {{ $variant->sale_unit ?: $product->sale_unit }}
                                        @if($variant->box_qty || $variant->carton_qty)
                                            <span class="text-[11px] text-slate-500 block">
                                                @if($variant->box_qty) Caja: {{ $variant->box_qty }} @endif
                                                @if($variant->carton_qty) | Cajón: {{ $variant->carton_qty }} @endif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 font-semibold text-slate-800 tabular-nums">
                                        {{ (float) $variant->price > 0 ? ($variant->currency ?: $product->currency) . ' ' . number_format((float) $variant->price, 2) : 'Bajo cotización' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $variant->availabilityBadgeClass() }}">
                                            {{ $variant->availabilityLabel() }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div>
            <h3 class="text-sm font-bold text-slate-900 mb-3">Tabla de Especificaciones Técnicas ({{ $product->specifications->count() }})</h3>
            <div class="overflow-x-auto border border-slate-200 rounded-lg">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 font-semibold text-slate-600">
                        <tr>
                            <th class="px-4 py-2 text-left">Grupo</th>
                            <th class="px-4 py-2 text-left">Atributo Técnico</th>
                            <th class="px-4 py-2 text-left">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($product->specifications as $spec)
                            <tr>
                                <td class="px-4 py-2 text-slate-500">{{ $spec->spec_group }}</td>
                                <td class="px-4 py-2 font-semibold text-slate-800">{{ $spec->spec_key }}</td>
                                <td class="px-4 py-2 text-slate-700">{{ $spec->spec_value }} {{ $spec->unit }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-4 text-center text-slate-400">Sin especificaciones adicionales registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
