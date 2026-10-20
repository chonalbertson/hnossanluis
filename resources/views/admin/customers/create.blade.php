@extends('layouts.admin')

@section('title', 'Nuevo Cliente')
@section('header', 'Registrar Cliente o Contacto B2B')

@section('content')
    <div class="max-w-3xl bg-white rounded-xl border border-slate-200 p-6 shadow-2xs">
        <form method="POST" action="{{ route('admin.customers.store') }}" class="space-y-4 text-xs">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nombre Completo del Contacto <span class="text-rose-600">*</span></label>
                    <input type="text" name="name" value="{{ old('name', request('name')) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Correo Electrónico <span class="text-rose-600">*</span></label>
                    <input type="email" name="email" value="{{ old('email', request('email')) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Teléfono / WhatsApp</label>
                    <input type="text" name="phone" value="{{ old('phone', request('phone')) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Empresa / Razón Social</label>
                    <input type="text" name="company" value="{{ old('company', request('company')) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">RUC (11 dígitos)</label>
                    <input type="text" name="ruc" value="{{ old('ruc', request('ruc')) }}" maxlength="11" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Dirección de Entrega / Oficina</label>
                    <input type="text" name="address" value="{{ old('address', request('address')) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Rubro / Sector</label>
                    <input type="text" name="sector" value="{{ old('sector', request('sector')) }}" placeholder="Ej. Fabricación de Muebles, Constructora, Ferretería..." class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Notas Comerciales</label>
                <textarea name="notes" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('notes', request('notes')) }}</textarea>
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_authorized_contact" value="1" checked class="rounded border-slate-300 text-amber-500">
                <span class="font-semibold text-slate-700">Contacto de compras verificado y autorizado</span>
            </label>

            <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 cursor-pointer">Registrar Cliente</button>
                <a href="{{ route('admin.customers.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
