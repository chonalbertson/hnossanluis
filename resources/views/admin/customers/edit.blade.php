@extends('layouts.admin')

@section('title', 'Editar Cliente')
@section('header', 'Editar Cliente: ' . $customer->name)

@section('content')
    <div class="max-w-3xl bg-white rounded-xl border border-slate-200 p-6 shadow-2xs">
        <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nombre Completo del Contacto <span class="text-rose-600">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Correo Electrónico <span class="text-rose-600">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Teléfono / WhatsApp</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Empresa / Razón Social</label>
                    <input type="text" name="company" value="{{ old('company', $customer->company) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">RUC (11 dígitos)</label>
                    <input type="text" name="ruc" value="{{ old('ruc', $customer->ruc) }}" maxlength="11" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Dirección de Entrega / Oficina</label>
                    <input type="text" name="address" value="{{ old('address', $customer->address) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Rubro / Sector</label>
                    <input type="text" name="sector" value="{{ old('sector', $customer->sector) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Notas Comerciales</label>
                <textarea name="notes" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('notes', $customer->notes) }}</textarea>
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_authorized_contact" value="1" @checked(old('is_authorized_contact', $customer->is_authorized_contact)) class="rounded border-slate-300 text-amber-500">
                <span class="font-semibold text-slate-700">Contacto de compras verificado y autorizado</span>
            </label>

            <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 cursor-pointer">Guardar Cambios</button>
                <a href="{{ route('admin.customers.show', $customer) }}" class="px-4 py-2.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
