@extends('layouts.admin')

@section('title', 'Clientes y Contactos B2B')
@section('header', '8. Cartera de Clientes y Contactos Autorizados')
@section('subheader', 'Registro centralizado de empresas, RUC, contactos de compras e historial de cotizaciones.')

@section('content')
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <span class="text-xs text-slate-600">Total clientes registrados: <strong>{{ $customers->total() }}</strong></span>
            <a href="{{ route('admin.customers.create') }}" class="px-4 py-2 rounded-lg bg-amber-500 text-slate-950 font-bold text-xs hover:bg-amber-400">
                + Registrar Cliente
            </a>
        </div>

        <form method="GET" action="{{ route('admin.customers.index') }}" class="flex flex-col sm:flex-row gap-3 pt-2 border-t border-slate-100 text-xs">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre, razón social, RUC, correo o teléfono..."
                   class="flex-1 rounded-lg border border-slate-300 px-3 py-2">
            <button type="submit" class="px-4 py-2 rounded-lg bg-slate-900 text-white font-semibold hover:bg-slate-800 cursor-pointer">Buscar</button>
            <a href="{{ route('admin.customers.index') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50">Limpiar</a>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Contacto</th>
                        <th class="px-4 py-3 text-left">Empresa / RUC</th>
                        <th class="px-4 py-3 text-left">Correo / Teléfono</th>
                        <th class="px-4 py-3 text-center">Cotizaciones</th>
                        <th class="px-4 py-3 text-center">Contacto Autorizado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-bold text-slate-900">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="hover:text-blue-600">{{ $customer->name }}</a>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800">{{ $customer->company ?: 'Particular' }}</div>
                                @if($customer->ruc)
                                    <div class="font-mono text-[11px] text-slate-500">RUC: {{ $customer->ruc }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div>{{ $customer->email }}</div>
                                <div class="text-slate-500">{{ $customer->phone }}</div>
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-slate-900">{{ $customer->quotes_count }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $customer->is_authorized_contact ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $customer->is_authorized_contact ? 'Autorizado' : 'Estándar' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="px-2.5 py-1 rounded border border-slate-300 text-slate-700 hover:bg-slate-100">Ficha</a>
                                    <a href="{{ route('admin.customers.edit', $customer) }}" class="px-2.5 py-1 rounded bg-slate-900 text-white hover:bg-slate-800 font-semibold">Editar</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">No hay clientes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
