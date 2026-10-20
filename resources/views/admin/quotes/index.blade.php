@extends('layouts.admin')

@section('title', 'Solicitudes de Cotización')
@section('header', '7. Bandeja Comercial de Solicitudes de Cotización')
@section('subheader', 'Seguimiento integral por estado, ejecutivo asignado, generación de propuesta PDF y envío por correo.')

@section('content')
    <!-- Search & Filters -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="text-sm text-slate-600">
                Total solicitudes filtradas: <strong class="text-slate-900">{{ $quotes->total() }}</strong>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if(auth()->user()?->hasPermission('quotes.manage') || auth()->user()?->hasPermission('quotes.respond'))
                    <a href="{{ route('admin.quotes.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-amber-500 text-slate-950 hover:bg-amber-400 text-xs font-bold shadow-2xs">
                        <span>+ Nueva Cotización Manual</span>
                    </a>
                @endif
                <a href="{{ route('admin.quotes.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 text-xs font-semibold">
                    <span>⬇ Exportar Cotizaciones CSV</span>
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.quotes.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 pt-2 border-t border-slate-100 text-xs">
            <div class="lg:col-span-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Código COT-2026-..., cliente, empresa, RUC o correo..."
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>

            <div>
                <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                    <option value="">Todos los estados</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="assigned_seller_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                    <option value="">Todos los vendedores</option>
                    <option value="unassigned" @selected(request('assigned_seller_id') === 'unassigned')>— Sin asignar —</option>
                    @foreach($sellers as $seller)
                        <option value="{{ $seller->id }}" @selected((string) request('assigned_seller_id') === (string) $seller->id)>{{ $seller->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border border-slate-300 px-2 py-2" title="Desde">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border border-slate-300 px-2 py-2" title="Hasta">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-3 py-2 rounded-lg bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 cursor-pointer">Filtrar</button>
                <a href="{{ route('admin.quotes.index') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- Quotes Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Código / Fecha</th>
                        <th class="px-4 py-3 text-left">Cliente / Empresa / RUC</th>
                        <th class="px-4 py-3 text-center">Ítems</th>
                        <th class="px-4 py-3 text-left">Ejecutivo</th>
                        <th class="px-4 py-3 text-left">Estado</th>
                        <th class="px-4 py-3 text-right">Total Ofertado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($quotes as $quote)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.quotes.show', $quote) }}" class="font-mono font-bold text-blue-600 hover:underline">
                                    {{ $quote->reference_code }}
                                </a>
                                <div class="text-[11px] text-slate-500">{{ $quote->created_at?->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">{{ $quote->contact_name }}</div>
                                @if($quote->company_name)
                                    <div class="text-slate-700 font-medium">{{ $quote->company_name }} @if($quote->ruc) <span class="font-mono text-slate-500">(RUC: {{ $quote->ruc }})</span> @endif</div>
                                @endif
                                <div class="text-[11px] text-slate-500">{{ $quote->contact_email }} | {{ $quote->contact_phone }}</div>
                            </td>
                            <td class="px-4 py-3 text-center font-bold">{{ $quote->items->count() }}</td>
                            <td class="px-4 py-3 text-slate-700">
                                {{ $quote->assignedSeller?->name ?? 'Sin asignar' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $quote->statusBadgeClass() }}">
                                    {{ $quote->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-slate-900">
                                @if($quote->total_offered !== null)
                                    {{ $quote->currencySymbol() }} {{ number_format((float) $quote->total_offered, 2) }}
                                @else
                                    <span class="text-slate-400 font-normal">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.quotes.show', $quote) }}" class="px-3 py-1.5 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-semibold">
                                        Gestionar
                                    </a>
                                    <a href="{{ route('admin.quotes.pdf', $quote) }}" target="_blank" class="px-2.5 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 font-medium">
                                        PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">No se encontraron solicitudes de cotización.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quotes->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
                {{ $quotes->links() }}
            </div>
        @endif
    </div>
@endsection
