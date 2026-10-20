@extends('layouts.admin')

@section('title', 'Importación y Exportación CSV')
@section('header', '6. Importación y Exportación Masiva del Catálogo (CSV)')
@section('subheader', 'Carga masiva con vista previa obligatoria, detección automática de SKU existentes/nuevos y reporte de errores.')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Upload CSV for Analysis & Preview -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900">1. Subir Archivo CSV para Vista Previa</h2>
                <a href="{{ route('admin.imports.template') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-800 hover:bg-slate-200 text-xs font-semibold">
                    <span>⬇ Descargar Plantilla CSV</span>
                </a>
            </div>

            <p class="text-xs text-slate-600">
                El archivo será analizado sin modificar la base de datos. Podrá revisar qué productos se <strong>crearán</strong>, cuáles se <strong>actualizarán por coincidencia de SKU</strong> y qué filas presentan errores antes de confirmar.
            </p>

            <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-[11px] text-slate-600">
                <div class="font-bold text-slate-800 mb-1">Cabeceras obligatorias mínimas:</div>
                <code class="text-amber-700 font-mono font-bold">{{ implode(', ', $requiredHeaders) }}</code>
                <div class="font-bold text-slate-800 mt-2 mb-1">Todas las columnas soportadas:</div>
                <code class="text-slate-600 font-mono break-all">{{ implode(', ', $standardHeaders) }}</code>
            </div>

            <form method="POST" action="{{ route('admin.imports.analyze') }}" enctype="multipart/form-data" class="space-y-4 pt-2">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Seleccionar archivo CSV (UTF-8, máx. 10MB)</label>
                    <input type="file" name="csv_file" required accept=".csv,.txt"
                           class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-800">
                </div>
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-amber-500 text-slate-950 hover:bg-amber-400 font-bold text-xs shadow-xs cursor-pointer">
                    Analizar Archivo y Generar Vista Previa &rarr;
                </button>
            </form>
        </div>

        <!-- Export Catalog to CSV -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4">
            <h2 class="text-sm font-bold text-slate-900">2. Exportar Catálogo Actual a CSV</h2>
            <p class="text-xs text-slate-600">
                Descargue el catálogo completo o filtrado en formato CSV compatible con Excel (UTF-8 BOM), listo para actualizar precios, unidades o especificaciones y volver a importar.
            </p>

            <form method="GET" action="{{ route('admin.imports.export') }}" class="space-y-3 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Filtrar por Categoría Principal</label>
                    <select name="category_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Filtrar por Marca</label>
                        <select name="brand_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                            <option value="">Todas las marcas</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Estado de Publicación</label>
                        <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                            <option value="">Todos los estados</option>
                            <option value="published">Solo Publicados</option>
                            <option value="draft">Solo Borradores</option>
                            <option value="archived">Solo Archivados</option>
                        </select>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 text-white hover:bg-slate-800 font-bold text-xs cursor-pointer">
                        ⬇ Exportar Productos a CSV
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Import Batches History -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200">
            <h3 class="text-sm font-bold text-slate-900">Historial de Lotes de Importación</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Lote / Archivo</th>
                        <th class="px-4 py-3 text-left">Usuario</th>
                        <th class="px-4 py-3 text-center">Filas Totales</th>
                        <th class="px-4 py-3 text-center">Crear / Creados</th>
                        <th class="px-4 py-3 text-center">Actualizar / Actualizados</th>
                        <th class="px-4 py-3 text-center">Rechazados</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($batches as $batch)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">#{{ $batch->id }} — {{ $batch->filename }}</div>
                                <div class="text-[11px] text-slate-500">{{ $batch->created_at?->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $batch->user?->name ?? 'Sistema' }}</td>
                            <td class="px-4 py-3 text-center font-semibold">{{ $batch->total_rows }}</td>
                            <td class="px-4 py-3 text-center text-emerald-700 font-bold">
                                {{ $batch->status === 'completed' ? $batch->created_count : $batch->to_create_count }}
                            </td>
                            <td class="px-4 py-3 text-center text-blue-700 font-bold">
                                {{ $batch->status === 'completed' ? $batch->updated_count : $batch->to_update_count }}
                            </td>
                            <td class="px-4 py-3 text-center {{ $batch->rejected_count > 0 ? 'text-rose-600 font-bold' : 'text-slate-400' }}">
                                {{ $batch->rejected_count }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($batch->status === 'completed')
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">Ejecutado</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800">En Vista Previa</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.imports.preview', $batch) }}" class="px-2.5 py-1 rounded bg-slate-900 text-white hover:bg-slate-800 font-semibold">
                                        {{ $batch->status === 'completed' ? 'Ver Reporte' : 'Revisar y Confirmar' }}
                                    </a>
                                    @if($batch->rejected_count > 0)
                                        <a href="{{ route('admin.imports.errors', $batch) }}" class="px-2.5 py-1 rounded border border-rose-200 text-rose-700 hover:bg-rose-50 font-semibold">
                                            CSV Errores
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">No se han registrado importaciones masivas aún.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
