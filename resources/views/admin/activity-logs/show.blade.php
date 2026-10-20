@extends('layouts.admin')

@section('title', 'Detalle de Auditoría #' . $log->id)
@section('header', 'Evento de Auditoría #' . $log->id)
@section('subheader', $log->action . ' — ' . $log->created_at?->format('d/m/Y H:i:s'))

@section('content')
    <div class="max-w-3xl bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4 text-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <span class="text-slate-400 block">Usuario Actor</span>
                <strong class="text-slate-900">{{ $log->user?->name ?? 'Sistema / Visitante' }} ({{ $log->user?->email ?? 'N/A' }})</strong>
            </div>
            <div>
                <span class="text-slate-400 block">Módulo y Acción</span>
                <strong class="font-mono text-slate-900">{{ $log->module }} / {{ $log->action }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block">Dirección IP</span>
                <strong class="font-mono text-slate-900">{{ $log->ip_address ?: '127.0.0.1' }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block">Entidad Afectada</span>
                <strong class="font-mono text-slate-900">{{ $log->subject_type ? class_basename($log->subject_type) . ' #' . $log->subject_id : 'Global' }}</strong>
            </div>
        </div>

        <div class="pt-3 border-t border-slate-100">
            <span class="text-slate-400 block mb-1">Descripción Registrada</span>
            <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 font-medium text-slate-800">{{ $log->description }}</div>
        </div>

        @if(!empty($log->properties))
            <div class="pt-3 border-t border-slate-100">
                <span class="text-slate-400 block mb-1">Propiedades Sanitizadas (JSON)</span>
                <pre class="p-4 rounded-lg bg-slate-900 text-amber-300 font-mono overflow-x-auto">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endif

        <div class="pt-4">
            <a href="{{ route('admin.activity-logs.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold">
                &larr; Volver al Registro de Actividad
            </a>
        </div>
    </div>
@endsection
