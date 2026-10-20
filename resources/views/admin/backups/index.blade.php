@extends('layouts.admin')

@section('title', 'Copias de Seguridad y Respaldos')
@section('header', 'Centro de Copias de Seguridad y Mantenimiento')
@section('subheader', 'Respaldo integral de base de datos, catálogo técnico, cotizaciones y archivos multimedia en formato ZIP.')

@section('content')
    <!-- Action Banner -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <h2 class="text-sm font-bold text-slate-900">Generación de Respaldo Bajo Demanda</h2>
            <p class="text-xs text-slate-500 max-w-2xl">
                Genere un paquete comprimido <code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-slate-800">.zip</code> en
                <code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-slate-800">{{ $backupPath }}</code> con la base de datos comercial,
                configuración del sistema y recursos técnicos de productos.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.backups.create') }}" class="shrink-0">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 text-xs shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span>Generar Nuevo Respaldo Ahora</span>
            </button>
        </form>
    </div>

    <!-- Backups Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Archivos de Respaldo Disponibles ({{ $backups->count() }})</h3>
            <span class="text-xs text-slate-500 font-mono">Directorio: {{ $backupPath }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Nombre del Archivo</th>
                        <th class="px-4 py-3 text-left">Fecha de Creación</th>
                        <th class="px-4 py-3 text-right">Tamaño</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($backups as $backup)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono font-bold text-slate-900">
                                {{ $backup['filename'] }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $backup['created_at']->format('d/m/Y H:i:s') }}
                                <span class="text-slate-400 ml-1">({{ $backup['created_at']->diffForHumans() }})</span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-semibold text-slate-700">
                                {{ $backup['size_formatted'] }}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.backups.download', $backup['filename']) }}"
                                       class="px-3 py-1.5 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-semibold">
                                        ⬇ Descargar ZIP
                                    </a>

                                    <form method="POST" action="{{ route('admin.backups.destroy', $backup['filename']) }}"
                                          onsubmit="return confirm('¿Eliminar permanentemente el respaldo {{ $backup['filename'] }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-2.5 py-1.5 rounded-lg border border-rose-200 text-rose-700 hover:bg-rose-50 font-medium cursor-pointer">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-slate-500">
                                No existen copias de seguridad generadas aún. Haga clic en <strong>"Generar Nuevo Respaldo Ahora"</strong> para crear el primer archivo ZIP.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
