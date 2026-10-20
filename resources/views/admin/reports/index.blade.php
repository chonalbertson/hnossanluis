@extends('layouts.admin')

@section('title', 'Informes Comerciales')
@section('header', '16. Informes Comerciales y Analítica de Cotizaciones')
@section('subheader', 'Métricas de conversión, tiempos de atención por vendedor y ranking de productos más demandados.')

@section('content')
    <!-- Date Filter & Export Buttons -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs flex flex-col lg:flex-row lg:items-center justify-between gap-4 text-xs">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-center gap-3">
            <div>
                <label class="font-semibold text-slate-600 mr-1">Desde:</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="rounded-lg border border-slate-300 px-3 py-1.5">
            </div>
            <div>
                <label class="font-semibold text-slate-600 mr-1">Hasta:</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="rounded-lg border border-slate-300 px-3 py-1.5">
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-slate-900 text-white font-semibold hover:bg-slate-800 cursor-pointer">
                Actualizar Informe
            </button>
        </form>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.reports.export', ['type' => 'quotes_by_status', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
               class="px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 font-semibold">
                ⬇ CSV Cotizaciones por Estado
            </a>
            <a href="{{ route('admin.reports.export', ['type' => 'top_products', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
               class="px-3 py-2 rounded-lg bg-amber-500 text-slate-950 hover:bg-amber-400 font-bold">
                ⬇ CSV Productos Más Cotizados
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
            <span class="text-xs font-bold uppercase text-slate-500">Cotizaciones en Período</span>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalQuotes }}</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
            <span class="text-xs font-bold uppercase text-slate-500">Tasa de Respuesta</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $responseRate }}%</div>
            <span class="text-[11px] text-slate-400">{{ $respondedQuotes }} respondidas</span>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
            <span class="text-xs font-bold uppercase text-slate-500">Cotizaciones Aceptadas</span>
            <div class="text-2xl font-black text-blue-600 mt-1">{{ $acceptedQuotes }}</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
            <span class="text-xs font-bold uppercase text-slate-500">Volumen Ofertado (PEN)</span>
            <div class="text-2xl font-black text-slate-900 mt-1">S/ {{ number_format($totalAmountOfferedPen, 2) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Quotes by Status -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200">
                <h2 class="text-sm font-bold text-slate-900">Distribución de Cotizaciones por Estado</h2>
            </div>
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-2.5 text-left">Estado</th>
                        <th class="px-4 py-2.5 text-center">Solicitudes</th>
                        <th class="px-4 py-2.5 text-right">Monto Ofertado Acumulado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($statuses as $statusKey => $statusLabel)
                        @php $row = $quotesByStatus->get($statusKey); @endphp
                        <tr>
                            <td class="px-4 py-2.5 font-semibold text-slate-800">{{ $statusLabel }}</td>
                            <td class="px-4 py-2.5 text-center font-bold text-slate-900">{{ $row ? $row->total : 0 }}</td>
                            <td class="px-4 py-2.5 text-right text-slate-600">
                                S/ {{ number_format((float)($row->sum_offered ?? 0), 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Quotes by Seller -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200">
                <h2 class="text-sm font-bold text-slate-900">Desempeño por Ejecutivo Comercial</h2>
            </div>
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-2.5 text-left">Ejecutivo</th>
                        <th class="px-4 py-2.5 text-center">Asignadas</th>
                        <th class="px-4 py-2.5 text-center">Respondidas</th>
                        <th class="px-4 py-2.5 text-right">Total Ofertado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($quotesBySeller as $sRow)
                        <tr>
                            <td class="px-4 py-2.5 font-semibold text-slate-900">
                                {{ $sRow->assigned_seller_id ? ($sellerUsers->get($sRow->assigned_seller_id)?->name ?? 'Usuario #' . $sRow->assigned_seller_id) : 'Sin asignar' }}
                            </td>
                            <td class="px-4 py-2.5 text-center font-bold">{{ $sRow->total_assigned }}</td>
                            <td class="px-4 py-2.5 text-center text-emerald-700 font-bold">{{ $sRow->responded_count }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold">S/ {{ number_format((float)$sRow->sum_offered, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">Sin datos en el rango seleccionado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Quoted Products -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200">
            <h2 class="text-sm font-bold text-slate-900">Ranking de Productos Más Solicitados en Cotizaciones</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">SKU</th>
                        <th class="px-4 py-3 text-left">Producto</th>
                        <th class="px-4 py-3 text-center">Unidad</th>
                        <th class="px-4 py-3 text-center">N° de Cotizaciones</th>
                        <th class="px-4 py-3 text-right">Cantidad Total Requerida</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($topProducts as $prod)
                        <tr>
                            <td class="px-4 py-3 font-mono font-bold text-slate-900">{{ $prod->sku_snapshot }}</td>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $prod->product_name_snapshot }}</td>
                            <td class="px-4 py-3 text-center text-slate-500">{{ $prod->sale_unit_snapshot }}</td>
                            <td class="px-4 py-3 text-center font-bold text-blue-700">{{ $prod->quotes_count }}</td>
                            <td class="px-4 py-3 text-right font-bold text-slate-900">{{ number_format((float)$prod->total_quantity, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">Sin productos cotizados en este período.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
