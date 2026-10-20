@extends('layouts.admin')

@section('title', 'Mensajes de Contacto')
@section('header', 'Bandeja de Mensajes de Contacto Comercial')
@section('subheader', 'Atención de consultas técnicas, solicitudes corporativas B2B y conversión rápida a clientes o cotizaciones.')

@section('content')
    <!-- Status Summary & Search Bar -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-2xs space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <a href="{{ route('admin.contact-messages.index') }}"
                   class="px-3 py-1.5 rounded-lg font-semibold border transition {{ !request('status') ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                    Todos ({{ $statusCounts['all'] ?? 0 }})
                </a>
                <a href="{{ route('admin.contact-messages.index', ['status' => 'nuevo']) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold border transition {{ request('status') === 'nuevo' ? 'bg-amber-500 text-slate-950 border-amber-500' : 'bg-amber-50 text-amber-800 border-amber-200 hover:bg-amber-100' }}">
                    Nuevos ({{ $statusCounts['nuevo'] ?? 0 }})
                </a>
                <a href="{{ route('admin.contact-messages.index', ['status' => 'leido']) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold border transition {{ request('status') === 'leido' ? 'bg-blue-600 text-white border-blue-600' : 'bg-blue-50 text-blue-800 border-blue-200 hover:bg-blue-100' }}">
                    Leídos ({{ $statusCounts['leido'] ?? 0 }})
                </a>
                <a href="{{ route('admin.contact-messages.index', ['status' => 'respondido']) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold border transition {{ request('status') === 'respondido' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-emerald-50 text-emerald-800 border-emerald-200 hover:bg-emerald-100' }}">
                    Respondidos ({{ $statusCounts['respondido'] ?? 0 }})
                </a>
            </div>

            @if(auth()->user()?->hasPermission('quotes.manage') || auth()->user()?->hasPermission('quotes.respond'))
                <a href="{{ route('admin.quotes.create') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 text-xs font-semibold">
                    <span>+ Nueva Cotización Manual</span>
                </a>
            @endif
        </div>

        <form method="GET" action="{{ route('admin.contact-messages.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 pt-2 border-t border-slate-100 text-xs">
            <div class="sm:col-span-2">
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Buscar por nombre, correo, empresa, RUC, teléfono o asunto..."
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>

            <div>
                <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                    <option value="">Todos los estados</option>
                    <option value="nuevo" @selected(request('status') === 'nuevo')>Nuevo</option>
                    <option value="leido" @selected(request('status') === 'leido')>Leído</option>
                    <option value="respondido" @selected(request('status') === 'respondido')>Respondido</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-3 py-2 rounded-lg bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 cursor-pointer">
                    Filtrar
                </button>
                <a href="{{ route('admin.contact-messages.index') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Messages Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Fecha</th>
                        <th class="px-4 py-3 text-left">Remitente / Empresa</th>
                        <th class="px-4 py-3 text-left">Asunto / Mensaje</th>
                        <th class="px-4 py-3 text-left">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($messages as $msg)
                        <tr class="hover:bg-slate-50 {{ $msg->status === 'nuevo' ? 'bg-amber-50/30' : '' }}">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-semibold text-slate-800">{{ $msg->created_at?->format('d/m/Y') }}</div>
                                <div class="text-[11px] text-slate-500">{{ $msg->created_at?->format('H:i') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">{{ $msg->name }}</div>
                                @if($msg->company)
                                    <div class="text-slate-700 font-medium">
                                        {{ $msg->company }}
                                        @if($msg->ruc)
                                            <span class="font-mono text-slate-500">(RUC: {{ $msg->ruc }})</span>
                                        @endif
                                    </div>
                                @endif
                                <div class="text-[11px] text-slate-500">
                                    {{ $msg->email }}
                                    @if($msg->phone)
                                        | {{ $msg->phone }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 max-w-md">
                                <a href="{{ route('admin.contact-messages.show', $msg) }}" class="font-bold text-blue-600 hover:underline block truncate">
                                    {{ $msg->subject ?: 'Consulta comercial general' }}
                                </a>
                                <div class="text-slate-600 truncate mt-0.5">{{ \Illuminate\Support\Str::limit($msg->message, 95) }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @php
                                    $badgeClass = match($msg->status) {
                                        'nuevo' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        'leido' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'respondido' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };
                                    $statusLabel = match($msg->status) {
                                        'nuevo' => 'Nuevo',
                                        'leido' => 'Leído',
                                        'respondido' => 'Respondido',
                                        default => ucfirst((string) $msg->status),
                                    };
                                @endphp
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $badgeClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.contact-messages.show', $msg) }}"
                                       class="px-3 py-1.5 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-semibold">
                                        Ver / Atender
                                    </a>
                                    @if(auth()->user()?->hasPermission('quotes.manage'))
                                        <form method="POST" action="{{ route('admin.contact-messages.destroy', $msg) }}"
                                              onsubmit="return confirm('¿Eliminar este mensaje de contacto?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-rose-200 text-rose-700 hover:bg-rose-50 font-medium cursor-pointer">
                                                Eliminar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-slate-500">
                                No se encontraron mensajes de contacto con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($messages->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
                {{ $messages->links() }}
            </div>
        @endif
    </div>
@endsection
