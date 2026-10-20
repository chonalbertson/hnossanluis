@extends('layouts.admin')

@section('title', 'Vista Previa de Importación #' . $batch->id)
@section('header', 'Vista Previa y Reporte de Importación CSV #' . $batch->id)
@section('subheader', 'Archivo: ' . $batch->filename . ' | Estado: ' . ($batch->status === 'completed' ? 'Ejecutado' : 'Pendiente de Confirmación'))

@section('content')
    <!-- Summary KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
            <span class="text-xs font-bold uppercase text-slate-500">Filas Analizadas</span>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $batch->total_rows }}</div>
        </div>
        <div class="bg-emerald-50 rounded-xl border border-emerald-200 p-4 shadow-2xs">
            <span class="text-xs font-bold uppercase text-emerald-700">SKU Nuevos (Crear)</span>
            <div class="text-2xl font-black text-emerald-800 mt-1">
                {{ $batch->status === 'completed' ? $batch->created_count : $batch->to_create_count }}
            </div>
        </div>
        <div class="bg-blue-50 rounded-xl border border-blue-200 p-4 shadow-2xs">
            <span class="text-xs font-bold uppercase text-blue-700">SKU Existentes (Actualizar)</span>
            <div class="text-2xl font-black text-blue-800 mt-1">
                {{ $batch->status === 'completed' ? $batch->updated_count : $batch->to_update_count }}
            </div>
        </div>
        <div class="bg-rose-50 rounded-xl border border-rose-200 p-4 shadow-2xs">
            <span class="text-xs font-bold uppercase text-rose-700">Filas Rechazadas con Error</span>
            <div class="text-2xl font-black text-rose-800 mt-1">{{ $batch->rejected_count }}</div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-xs text-slate-600">
            @if($batch->status === 'preview')
                Verifique el detalle de filas válidas y rechazadas a continuación. Al confirmar, se procesarán <strong>{{ $batch->to_create_count + $batch->to_update_count }}</strong> productos dentro de una transacción segura.
            @else
                Este lote fue ejecutado el <strong>{{ $batch->executed_at?->format('d/m/Y H:i:s') }}</strong>.
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if($batch->rejected_count > 0)
                <a href="{{ route('admin.imports.errors', $batch) }}" class="px-4 py-2.5 rounded-xl border border-rose-300 bg-rose-50 text-rose-800 hover:bg-rose-100 text-xs font-bold">
                    ⬇ Descargar Reporte de Errores CSV ({{ $batch->rejected_count }})
                </a>
            @endif

            @if($batch->status === 'preview' && ($batch->to_create_count + $batch->to_update_count) > 0)
                <form method="POST" action="{{ route('admin.imports.execute', $batch) }}">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 text-slate-950 hover:bg-amber-400 text-xs font-bold shadow-sm cursor-pointer">
                        ✓ Confirmar y Ejecutar Importación Ahora
                    </button>
                </form>
            @endif

            <a href="{{ route('admin.imports.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold">
                &larr; Volver a Importaciones
            </a>
        </div>
    </div>

    <!-- Rejected Rows Table -->
    @if(!empty($batch->error_rows))
        <div class="bg-white rounded-xl border border-rose-200 shadow-2xs overflow-hidden">
            <div class="px-5 py-4 bg-rose-50 border-b border-rose-200 flex items-center justify-between">
                <h3 class="text-sm font-bold text-rose-900">Filas Rechazadas por Validación ({{ count($batch->error_rows) }})</h3>
                <span class="text-xs text-rose-700">Estas filas no se importarán hasta corregir su formato</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-rose-100 text-xs">
                    <thead class="bg-rose-50/50 text-rose-900 uppercase font-semibold">
                        <tr>
                            <th class="px-4 py-2.5 text-left">Fila CSV</th>
                            <th class="px-4 py-2.5 text-left">SKU</th>
                            <th class="px-4 py-2.5 text-left">Nombre</th>
                            <th class="px-4 py-2.5 text-left">Motivo Exacto del Error</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-rose-100">
                        @foreach($batch->error_rows as $err)
                            <tr>
                                <td class="px-4 py-2.5 font-mono font-bold text-rose-800">Fila {{ $err['row_number'] ?? '?' }}</td>
                                <td class="px-4 py-2.5 font-mono">{{ $err['sku'] ?: '(vacío)' }}</td>
                                <td class="px-4 py-2.5">{{ $err['name'] ?: '(vacío)' }}</td>
                                <td class="px-4 py-2.5 text-rose-700 font-medium">
                                    {{ is_array($err['errors'] ?? null) ? implode(' | ', $err['errors']) : ($err['errors'] ?? '') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Valid Preview Rows Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200">
            <h3 class="text-sm font-bold text-slate-900">Filas Válidas para Procesar ({{ count($batch->preview_rows ?? []) }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-2.5 text-left">Fila</th>
                        <th class="px-4 py-2.5 text-left">Acción</th>
                        <th class="px-4 py-2.5 text-left">SKU</th>
                        <th class="px-4 py-2.5 text-left">Producto</th>
                        <th class="px-4 py-2.5 text-left">Categoría / Marca</th>
                        <th class="px-4 py-2.5 text-left">Precio / Unidad</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse(array_slice($batch->preview_rows ?? [], 0, 100) as $row)
                        <tr>
                            <td class="px-4 py-2.5 font-mono text-slate-500">#{{ $row['row_number'] }}</td>
                            <td class="px-4 py-2.5">
                                @if(($row['action'] ?? '') === 'update')
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-800">Actualizar SKU</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">Nuevo SKU</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 font-mono font-bold text-slate-900">{{ $row['sku'] }}</td>
                            <td class="px-4 py-2.5 font-medium text-slate-800">{{ $row['name'] }}</td>
                            <td class="px-4 py-2.5 text-slate-600">
                                {{ $row['category'] }} {{ !empty($row['subcategory']) ? '→ ' . $row['subcategory'] : '' }}
                                @if(!empty($row['brand'])) <span class="text-slate-400">({{ $row['brand'] }})</span> @endif
                            </td>
                            <td class="px-4 py-2.5">
                                {{ $row['price'] !== null ? $row['currency'] . ' ' . number_format((float)$row['price'], 2) : 'Bajo cotización' }}
                                / {{ $row['sale_unit'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-500">No hay filas válidas en este lote.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
