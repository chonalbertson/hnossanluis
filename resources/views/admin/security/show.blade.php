@extends('layouts.admin')

@section('title', 'Incidente Forense #' . $incident->id . ' | Seguridad Antimalware')
@section('header', 'Ficha Forense de Incidente de Seguridad #' . $incident->id)
@section('subheader', $incident->threat_type_label . ' — Registrado el ' . $incident->created_at?->format('d/m/Y H:i:s'))

@section('content')
    @php
        $severityBadge = match($incident->severity) {
            'critical' => 'bg-rose-100 text-rose-800 border-rose-300',
            'high' => 'bg-orange-100 text-orange-800 border-orange-300',
            'medium' => 'bg-amber-100 text-amber-800 border-amber-300',
            default => 'bg-slate-100 text-slate-700 border-slate-300',
        };
        $statusBadge = match($incident->status) {
            'quarantined' => 'bg-purple-100 text-purple-800 border-purple-300',
            'blocked' => 'bg-rose-50 text-rose-700 border-rose-200',
            'resolved' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'false_positive' => 'bg-slate-100 text-slate-600 border-slate-300',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
        $formattedSize = null;
        if ($incident->file_size !== null) {
            if ($incident->file_size >= 1048576) {
                $formattedSize = number_format($incident->file_size / 1048576, 2) . ' MB (' . number_format($incident->file_size) . ' bytes)';
            } elseif ($incident->file_size >= 1024) {
                $formattedSize = number_format($incident->file_size / 1024, 2) . ' KB (' . number_format($incident->file_size) . ' bytes)';
            } else {
                $formattedSize = number_format($incident->file_size) . ' bytes';
            }
        }
    @endphp

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.security.index') }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
            <span>&larr; Volver al Centro de Seguridad</span>
        </a>

        <div class="flex items-center gap-2">
            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold border {{ $severityBadge }}">
                Severidad: {{ strtoupper($incident->severity_label) }}
            </span>
            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold border {{ $statusBadge }}">
                Estado: {{ $incident->status_label }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna Principal: Evidencia Técnica y Forense -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-5 text-xs">
                <div class="border-b border-slate-100 pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Clasificación de Amenaza</span>
                        <h2 class="text-base font-black text-slate-900 mt-0.5">{{ $incident->threat_type_label }}</h2>
                    </div>
                    <div class="font-mono text-xs px-3 py-1.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 font-bold">
                        Regla: {{ $incident->matched_rule }}
                    </div>
                </div>

                <!-- Datos de Red y Petición HTTP -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200">
                        <span class="text-slate-400 block text-[11px] font-semibold uppercase">Dirección IP Origen</span>
                        <strong class="font-mono text-sm text-slate-900 mt-1 block">{{ $incident->ip_address ?: '127.0.0.1' }}</strong>
                    </div>

                    <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200">
                        <span class="text-slate-400 block text-[11px] font-semibold uppercase">Método HTTP / Vector</span>
                        <strong class="font-mono text-sm text-slate-900 mt-1 block">{{ $incident->http_method ?: 'N/A' }}</strong>
                    </div>

                    <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200">
                        <span class="text-slate-400 block text-[11px] font-semibold uppercase">Usuario en Sesión</span>
                        @if($incident->user)
                            <strong class="text-slate-900 mt-1 block truncate">{{ $incident->user->name }}</strong>
                            <span class="text-[11px] text-slate-500 block truncate">{{ $incident->user->email }}</span>
                        @else
                            <strong class="text-slate-700 mt-1 block">Visitante / No autenticado</strong>
                        @endif
                    </div>
                </div>

                <div>
                    <span class="text-slate-400 block text-[11px] font-semibold uppercase mb-1">URL / Ruta Interceptada</span>
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 font-mono text-slate-800 break-all">
                        {{ $incident->url ?: 'No registrada' }}
                    </div>
                </div>

                <!-- Evidencia de Archivo y Hash SHA-256 -->
                <div class="pt-4 border-t border-slate-100 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Evidencia de Archivo y Firma Criptográfica</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200">
                            <span class="text-slate-400 block text-[11px] font-semibold uppercase">Nombre del Archivo</span>
                            <strong class="font-mono text-slate-900 mt-1 block break-all">
                                {{ $incident->file_name ?: 'Sin archivo adjunto (Payload HTTP)' }}
                            </strong>
                        </div>

                        <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200">
                            <span class="text-slate-400 block text-[11px] font-semibold uppercase">Tamaño del Archivo</span>
                            <strong class="font-mono text-slate-900 mt-1 block">
                                {{ $formattedSize ?: 'N/A' }}
                            </strong>
                        </div>
                    </div>

                    <div>
                        <span class="text-slate-400 block text-[11px] font-semibold uppercase mb-1">Hash Criptográfico SHA-256</span>
                        <div class="p-3 rounded-lg bg-slate-900 text-emerald-400 font-mono text-xs break-all select-all border border-slate-800">
                            {{ $incident->file_hash ?: 'No disponible (incidente de nivel de petición sin stream de archivo)' }}
                        </div>
                    </div>

                    @if($incident->quarantine_path)
                        <div class="p-4 rounded-xl bg-purple-50 border border-purple-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded bg-purple-200 text-purple-900 font-bold text-[10px] uppercase">Aislado en Bóveda</span>
                                    <span class="font-mono font-bold text-purple-950">storage/app/{{ $incident->quarantine_path }}</span>
                                </div>
                                <p class="text-[11px] text-purple-800">
                                    @if($quarantineFileExists)
                                        El archivo malicioso se encuentra físicamente aislado fuera del alcance web público con ejecución bloqueada (<code class="font-mono">.quarantine</code>).
                                    @else
                                        El registro apunta a cuarentena, pero el archivo físico ya no se encuentra en el disco.
                                    @endif
                                </p>
                            </div>

                            <form method="POST" action="{{ route('admin.security.incidents.delete-quarantine', $incident) }}"
                                  onsubmit="return confirm('¿Está seguro de eliminar permanentemente este archivo en cuarentena del disco del servidor?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-rose-600 text-white hover:bg-rose-700 font-bold text-xs transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span>Purgar Archivo de Cuarentena</span>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                <!-- Payload / Detalles Técnicos en JSON -->
                <div class="pt-4 border-t border-slate-100">
                    <span class="text-slate-400 block text-[11px] font-semibold uppercase mb-1.5">Detalles Técnicos / Payload Forense (JSON)</span>
                    @if(!empty($incident->details))
                        <pre class="p-4 rounded-xl bg-slate-900 text-amber-300 font-mono text-xs overflow-x-auto leading-relaxed">{{ json_encode($incident->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    @else
                        <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 text-slate-500">
                            No se registraron metadatos adicionales en formato JSON para este incidente.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Columna Lateral: Gestión de Resolución y Estado -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4 text-xs">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">
                    Dictamen y Resolución del Incidente
                </h3>

                @if($incident->resolved_at)
                    <div class="p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-900 space-y-1">
                        <div class="font-bold">Incidente revisado y dictaminado</div>
                        <div>Estado actual: <strong>{{ $incident->status_label }}</strong></div>
                        <div>Por: <strong>{{ $incident->resolver?->name ?? 'Administrador' }}</strong></div>
                        <div>Fecha: <strong>{{ $incident->resolved_at->format('d/m/Y H:i:s') }}</strong></div>
                    </div>
                @else
                    <div class="p-3.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-900">
                        Este incidente se encuentra en estado <strong>{{ $incident->status_label }}</strong> pendiente de revisión administrativa.
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.security.incidents.resolve', $incident) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="status" class="block font-bold text-slate-700 mb-1">Cambiar Estado Forense</label>
                        <select id="status" name="status" required
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white text-slate-900 font-semibold">
                            <option value="resolved" @selected($incident->status === 'resolved')>
                                Resuelto (Amenaza neutralizada y auditada)
                            </option>
                            <option value="false_positive" @selected($incident->status === 'false_positive')>
                                Falso Positivo (Descartar alerta)
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="resolution_notes" class="block font-bold text-slate-700 mb-1">Notas de Auditoría / Resolución</label>
                        <textarea id="resolution_notes" name="resolution_notes" rows="5"
                                  placeholder="Documente las acciones tomadas, bloqueo de IP en firewall perimetral o motivo de descarte..."
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-800">{{ old('resolution_notes', $incident->resolution_notes) }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full py-2.5 px-4 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-bold text-xs uppercase tracking-wider transition cursor-pointer">
                        Guardar Dictamen de Seguridad
                    </button>
                </form>
            </div>

            <!-- Tarjeta de Recomendación Operativa -->
            <div class="bg-slate-900 text-slate-200 rounded-xl border border-slate-800 p-5 space-y-2.5 text-xs">
                <div class="flex items-center gap-2 text-amber-400 font-bold">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Protocolo de Respuesta Recomendado</span>
                </div>
                <p class="text-slate-300 leading-relaxed">
                    1. Verifique el hash <strong>SHA-256</strong> y la regla disparada (<code class="text-amber-300 font-mono">{{ $incident->matched_rule }}</code>).
                </p>
                <p class="text-slate-300 leading-relaxed">
                    2. Si existe un archivo aislado en <code class="text-amber-300 font-mono">storage/app/quarantine</code> y confirma que es malicioso, utilice el botón <strong>Purgar Archivo de Cuarentena</strong> para destruirlo definitivamente.
                </p>
                <p class="text-slate-300 leading-relaxed">
                    3. Todas las acciones quedan registradas de forma inmutable en el <strong>Registro de Actividad</strong>.
                </p>
            </div>
        </div>
    </div>
@endsection
