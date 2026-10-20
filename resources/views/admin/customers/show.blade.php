@extends('layouts.admin')

@section('title', 'Cliente: ' . $customer->name)
@section('header', 'Ficha de Cliente B2B: ' . $customer->name)
@section('subheader', $customer->company ?: $customer->email)

@section('content')
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">{{ $customer->company ?: $customer->name }}</h2>
                <p class="text-xs text-slate-500">RUC: <strong class="font-mono">{{ $customer->ruc ?: 'No registrado' }}</strong> | Sector: <strong>{{ $customer->sector ?: 'General' }}</strong></p>
            </div>
            <a href="{{ route('admin.customers.edit', $customer) }}" class="px-4 py-2 rounded-lg bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800">
                Editar Datos
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div>
                <span class="text-slate-500 block">Contacto Principal</span>
                <strong class="text-slate-900">{{ $customer->name }}</strong>
            </div>
            <div>
                <span class="text-slate-500 block">Correo y Teléfono</span>
                <div class="font-semibold text-blue-600">{{ $customer->email }}</div>
                <div>{{ $customer->phone ?: 'Sin teléfono' }}</div>
            </div>
            <div>
                <span class="text-slate-500 block">Dirección</span>
                <div>{{ $customer->address ?: 'Sin dirección registrada' }}</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200">
            <h3 class="text-sm font-bold text-slate-900">Historial de Cotizaciones del Cliente ({{ $customer->quotes->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Código</th>
                        <th class="px-4 py-3 text-left">Fecha</th>
                        <th class="px-4 py-3 text-center">Ítems</th>
                        <th class="px-4 py-3 text-left">Estado</th>
                        <th class="px-4 py-3 text-right">Total Ofertado</th>
                        <th class="px-4 py-3 text-right">Detalle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customer->quotes as $quote)
                        <tr>
                            <td class="px-4 py-3 font-mono font-bold text-blue-600">
                                <a href="{{ route('admin.quotes.show', $quote) }}">{{ $quote->reference_code }}</a>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $quote->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-center font-bold">{{ $quote->items->count() }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $quote->statusBadgeClass() }}">{{ $quote->statusLabel() }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-bold">
                                {{ $quote->total_offered !== null ? $quote->currencySymbol() . ' ' . number_format((float)$quote->total_offered, 2) : 'Pendiente' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.quotes.show', $quote) }}" class="text-blue-600 hover:underline font-semibold">Ver cotización &rarr;</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-500">Este cliente aún no registra cotizaciones.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
