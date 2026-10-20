@extends('layouts.admin')

@section('title', 'Registro de Actividad y Auditoría')
@section('header', '15. Registro de Actividad (Activity Log de Auditoría)')
@section('subheader', 'Trazabilidad inmutable de acciones administrativas, cambios de precios, estados de cotización e importaciones.')

@section('content')
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-2xs">
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 text-xs">
            <div class="lg:col-span-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar en descripción, acción o dirección IP..."
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <div>
                <select name="module" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                    <option value="">Todos los módulos</option>
                    @foreach($modules as $mod)
                        <option value="{{ $mod }}" @selected(request('module') === $mod)>{{ strtoupper($mod) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="user_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                    <option value="">Todos los usuarios</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected((string)request('user_id') === (string)$u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-lg bg-slate-900 text-white font-semibold hover:bg-slate-800 cursor-pointer">Filtrar</button>
                <a href="{{ route('admin.activity-logs.index') }}" class="py-2 px-3 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Fecha / Hora</th>
                        <th class="px-4 py-3 text-left">Usuario</th>
                        <th class="px-4 py-3 text-left">Módulo / Acción</th>
                        <th class="px-4 py-3 text-left">Descripción</th>
                        <th class="px-4 py-3 text-left">IP</th>
                        <th class="px-4 py-3 text-right">Detalle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-slate-500 whitespace-nowrap">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $log->user?->name ?? 'Sistema / Público' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded bg-slate-100 font-mono font-bold text-slate-700">{{ $log->module }}</span>
                                <span class="font-mono text-amber-700 ml-1">{{ $log->action }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $log->description }}</td>
                            <td class="px-4 py-3 font-mono text-slate-400">{{ $log->ip_address ?: '127.0.0.1' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.activity-logs.show', $log) }}" class="text-blue-600 hover:underline font-semibold">Inspeccionar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">No se encontraron registros de actividad.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection
