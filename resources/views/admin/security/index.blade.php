@extends('layouts.admin')

@section('title', 'Centro de Seguridad Antimalware y WAF')
@section('header', 'Centro de Seguridad Antimalware, WAF y Cuarentena')
@section('subheader', 'Inspección profunda de archivos (Magic Bytes, Anti-Webshell, Anti-PDF Exploit, Anti-CSV DDE), firewall de aplicaciones y gestión de cuarentena.')

@section('content')
    <!-- Banner Superior de Escudo y Acción de Escaneo Bajo Demanda -->
    <div class="bg-slate-900 text-white rounded-xl border border-slate-800 p-5 sm:p-6 shadow-xs flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>ESCUDO ANTIMALWARE Y WAF INDUSTRIAL ACTIVO</span>
            </div>
            <h2 class="text-lg sm:text-xl font-black tracking-tight text-white">
                Protección en Tiempo Real de Almacenamiento, Subidas y Formularios B2B
            </h2>
            <p class="text-xs text-slate-300 max-w-3xl leading-relaxed">
                Cada imagen, ficha técnica PDF y lote CSV es inspeccionado a nivel binario antes de tocar el disco. Los archivos sospechosos en <code class="text-amber-300 font-mono">storage/app/public</code> son aislados automáticamente en la bóveda <code class="text-amber-300 font-mono">storage/app/quarantine</code> con ejecución bloqueada.
            </p>
            @if($latestScan)
                <div class="pt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-400">
                    <span>
                        Último escaneo: <strong class="text-slate-200">{{ $latestScan->created_at?->format('d/m/Y H:i:s') }}</strong> ({{ $latestScan->created_at?->diffForHumans() }})
                    </span>
                    <span>•</span>
                    <span>
                        Archivos verificados: <strong class="text-slate-200">{{ number_format($latestScan->files_scanned) }}</strong> en <strong class="text-slate-200">{{ number_format($latestScan->duration_ms) }} ms</strong>
                    </span>
                    <span>•</span>
                    <span>
                        Resultado:
                        @if($latestScan->threats_found > 0)
                            <strong class="text-rose-400">{{ $latestScan->threats_found }} amenaza(s) aislada(s)</strong>
                        @else
                            <strong class="text-emerald-400">0 amenazas (Limpio)</strong>
                        @endif
                    </span>
                </div>
            @else
                <div class="pt-1 text-xs text-amber-300 font-medium">
                    Aún no se ha ejecutado una auditoría manual de almacenamiento. Ejecute un escaneo ahora para verificar la integridad.
                </div>
            @endif
        </div>

        <div class="shrink-0">
            <form method="POST" action="{{ route('admin.security.scan') }}">
                @csrf
                <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-5 py-3 rounded-xl bg-amber-500 text-slate-950 hover:bg-amber-400 font-black text-xs sm:text-sm tracking-wide uppercase transition shadow-md cursor-pointer">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Ejecutar Escaneo Antimalware Ahora</span>
                </button>
            </form>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Estado del Escudo</span>
                <span class="p-2 rounded-lg bg-emerald-100 text-emerald-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-3 text-2xl font-black text-emerald-600">PROTEGIDO (5/5)</div>
            <div class="mt-1 text-xs text-slate-500">
                WAF HTTP + Escáner de Firmas Activos
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Incidentes Pendientes</span>
                <span class="p-2 rounded-lg {{ $kpis['pending_incidents'] > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-3 text-3xl font-black {{ $kpis['pending_incidents'] > 0 ? 'text-amber-600' : 'text-slate-900' }}">
                {{ number_format($kpis['pending_incidents']) }}
            </div>
            <div class="mt-1 text-xs text-slate-500">
                Histórico total: <strong class="text-slate-700">{{ number_format($kpis['total_incidents']) }}</strong> | Resueltos: <strong class="text-emerald-600">{{ number_format($kpis['resolved_incidents']) }}</strong>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Amenazas Críticas</span>
                <span class="p-2 rounded-lg {{ $kpis['critical_incidents'] > 0 ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-3 text-3xl font-black {{ $kpis['critical_incidents'] > 0 ? 'text-rose-600' : 'text-slate-900' }}">
                {{ number_format($kpis['critical_incidents']) }}
            </div>
            <div class="mt-1 text-xs text-slate-500">
                Críticas sin resolver (Total críticas: <strong class="text-slate-700">{{ number_format($kpis['total_critical_incidents']) }}</strong>)
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Archivos en Cuarentena</span>
                <span class="p-2 rounded-lg {{ $kpis['quarantined_files'] > 0 ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </span>
            </div>
            <div class="mt-3 text-3xl font-black {{ $kpis['quarantined_files'] > 0 ? 'text-purple-700' : 'text-slate-900' }}">
                {{ number_format($kpis['quarantined_files']) }}
            </div>
            <div class="mt-1 text-xs text-slate-500">
                Aislados en <code class="font-mono text-[11px] text-slate-700">storage/app/quarantine</code>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Capas del Escudo WAF / Antimalware -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Motores de Defensa Activos en el Servidor</h3>
                <p class="text-xs text-slate-500">Reglas de inspección síncrona aplicadas a peticiones HTTP, cargas de medios y lotes de importación.</p>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[11px] font-bold">
                100% Operativo
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3 text-xs">
            <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/70 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-900">1. Magic Bytes & Binarios</span>
                    <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">ACTIVO</span>
                </div>
                <p class="text-slate-600 leading-relaxed">
                    Verifica cabeceras binarias reales (<code class="font-mono">FF D8 FF</code>, <code class="font-mono">89 PNG</code>, <code class="font-mono">RIFF/WEBP</code>, <code class="font-mono">%PDF-</code>) y bloquea ejecutables MZ/PE y ELF camuflados.
                </p>
            </div>

            <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/70 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-900">2. Anti-Webshell</span>
                    <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">ACTIVO</span>
                </div>
                <p class="text-slate-600 leading-relaxed">
                    Detecta payloads políglotas PHP/JS (<code class="font-mono">&lt;?php</code>, <code class="font-mono">eval()</code>, <code class="font-mono">base64_decode</code>, <code class="font-mono">shell_exec</code>, <code class="font-mono">system</code>) ocultos en metadatos EXIF o archivos.
                </p>
            </div>

            <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/70 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-900">3. Anti-PDF Exploit</span>
                    <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">ACTIVO</span>
                </div>
                <p class="text-slate-600 leading-relaxed">
                    Analiza fichas técnicas PDF bloqueando directivas maliciosas <code class="font-mono">/JavaScript</code>, <code class="font-mono">/JS</code>, <code class="font-mono">/Launch</code>, <code class="font-mono">/EmbeddedFiles</code> y <code class="font-mono">/RichMedia</code>.
                </p>
            </div>

            <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/70 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-900">4. Anti-CSV Formula / DDE</span>
                    <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">ACTIVO</span>
                </div>
                <p class="text-slate-600 leading-relaxed">
                    Neutraliza inyecciones de fórmulas DDE (<code class="font-mono">=cmd|</code>, <code class="font-mono">=powershell</code>, <code class="font-mono">@SUM</code>, <code class="font-mono">=HYPERLINK</code>) tanto en subida como en exportación CSV.
                </p>
            </div>

            <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/70 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-900">5. Anti-Path Traversal</span>
                    <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">ACTIVO</span>
                </div>
                <p class="text-slate-600 leading-relaxed">
                    Bloquea dobles extensiones (<code class="font-mono">.php.jpg</code>), escalada de directorios (<code class="font-mono">../</code>), bytes nulos (<code class="font-mono">%00</code>) y scripts en almacenamiento público.
                </p>
            </div>
        </div>
    </div>

    <!-- Filtros de Incidentes -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-2xs">
        <form method="GET" action="{{ route('admin.security.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 text-xs">
            <div class="lg:col-span-2">
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Buscar por IP, nombre de archivo, regla o hash SHA-256..."
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>

            <div>
                <select name="threat_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                    <option value="">Todos los tipos de amenaza</option>
                    @foreach($threatTypes as $key => $label)
                        <option value="{{ $key }}" @selected(request('threat_type') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="severity" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                    <option value="">Todas las severidades</option>
                    <option value="critical" @selected(request('severity') === 'critical')>Crítica</option>
                    <option value="high" @selected(request('severity') === 'high')>Alta</option>
                    <option value="medium" @selected(request('severity') === 'medium')>Media</option>
                    <option value="low" @selected(request('severity') === 'low')>Baja</option>
                </select>
            </div>

            <div>
                <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                    <option value="">Todos los estados</option>
                    <option value="blocked" @selected(request('status') === 'blocked')>Bloqueado</option>
                    <option value="quarantined" @selected(request('status') === 'quarantined')>En Cuarentena</option>
                    <option value="resolved" @selected(request('status') === 'resolved')>Resuelto</option>
                    <option value="false_positive" @selected(request('status') === 'false_positive')>Falso Positivo</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-lg bg-slate-900 text-white font-semibold hover:bg-slate-800 cursor-pointer">
                    Filtrar
                </button>
                <a href="{{ route('admin.security.index') }}" class="py-2 px-3 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla de Incidentes de Seguridad -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Registro Forense de Incidentes y Amenazas Interceptadas</h3>
                <p class="text-xs text-slate-500">Eventos bloqueados en tiempo real por el WAF o aislados por el escáner antimalware.</p>
            </div>
            <span class="text-xs font-semibold text-slate-500">Total listados: {{ $incidents->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">ID / Fecha</th>
                        <th class="px-4 py-3 text-left">Amenaza y Regla Disparada</th>
                        <th class="px-4 py-3 text-center">Severidad</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-left">Archivo / Hash SHA-256</th>
                        <th class="px-4 py-3 text-left">Origen (IP / Usuario)</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($incidents as $incident)
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
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-mono font-bold text-slate-900">#{{ $incident->id }}</div>
                                <div class="text-[11px] text-slate-500">{{ $incident->created_at?->format('d/m/Y H:i:s') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">{{ $incident->threat_type_label }}</div>
                                <div class="font-mono text-[11px] text-amber-700 mt-0.5">{{ $incident->matched_rule }}</div>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $severityBadge }}">
                                    {{ strtoupper($incident->severity_label) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $statusBadge }}">
                                    {{ $incident->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($incident->file_name)
                                    <div class="font-mono font-semibold text-slate-800 truncate max-w-xs" title="{{ $incident->file_name }}">
                                        {{ $incident->file_name }}
                                    </div>
                                @else
                                    <span class="text-slate-400">Payload HTTP sin archivo</span>
                                @endif
                                @if($incident->file_hash)
                                    <div class="font-mono text-[10px] text-slate-400 truncate max-w-[200px]" title="SHA-256: {{ $incident->file_hash }}">
                                        SHA256: {{ substr($incident->file_hash, 0, 18) }}...
                                    </div>
                                @endif
                                @if($incident->quarantine_path)
                                    <span class="inline-block mt-0.5 px-1.5 py-0.2 rounded bg-purple-50 text-purple-700 font-mono text-[10px] border border-purple-200">
                                        En bóveda de cuarentena
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-mono text-slate-800 font-semibold">{{ $incident->ip_address ?: '127.0.0.1' }}</div>
                                <div class="text-[11px] text-slate-500">
                                    {{ $incident->user?->name ?? 'Visitante / No autenticado' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.security.incidents.show', $incident) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-semibold text-xs transition">
                                    <span>Ficha Forense</span>
                                    <span>&rarr;</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">
                                <div class="font-semibold text-slate-700">No se registran incidentes de seguridad con los criterios seleccionados.</div>
                                <div class="text-xs text-slate-400 mt-1">El escudo antimalware y WAF continúa monitoreando todas las operaciones.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($incidents->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
                {{ $incidents->links() }}
            </div>
        @endif
    </div>

    <!-- Historial Reciente de Escaneos Antimalware -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Historial de Escaneos Antimalware (Últimos 10)</h3>
                <p class="text-xs text-slate-500">Auditorías ejecutadas manualmente o por tareas programadas sobre el almacenamiento del servidor.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">ID</th>
                        <th class="px-4 py-3 text-left">Fecha y Hora</th>
                        <th class="px-4 py-3 text-left">Tipo de Escaneo</th>
                        <th class="px-4 py-3 text-left">Ejecutado por</th>
                        <th class="px-4 py-3 text-center">Archivos Analizados</th>
                        <th class="px-4 py-3 text-center">Amenazas Detectadas</th>
                        <th class="px-4 py-3 text-center">En Cuarentena</th>
                        <th class="px-4 py-3 text-center">Duración</th>
                        <th class="px-4 py-3 text-right">Resultado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentScans as $scan)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono font-bold text-slate-800">#{{ $scan->id }}</td>
                            <td class="px-4 py-3 font-mono text-slate-600 whitespace-nowrap">{{ $scan->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $scan->scan_type_label }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $scan->triggeredBy?->name ?? 'Sistema / Consola' }}</td>
                            <td class="px-4 py-3 text-center font-mono font-semibold text-slate-800">{{ number_format($scan->files_scanned) }}</td>
                            <td class="px-4 py-3 text-center font-mono font-bold {{ $scan->threats_found > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ number_format($scan->threats_found) }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono font-semibold text-purple-700">
                                {{ number_format($scan->quarantined_count) }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono text-slate-500">{{ number_format($scan->duration_ms) }} ms</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @if($scan->status === 'clean')
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        {{ $scan->status_label }}
                                    </span>
                                @elseif($scan->status === 'threats_found')
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                        {{ $scan->status_label }}
                                    </span>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-300">
                                        {{ $scan->status_label }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-slate-500">
                                No se han registrado escaneos antimalware aún. Use el botón superior "Ejecutar Escaneo Antimalware Ahora".
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
