@extends('layouts.admin')

@section('title', 'Nuevo Testimonio')
@section('header', 'Registrar Testimonio de Cliente')

@section('content')
    <div class="max-w-2xl bg-white rounded-xl border border-slate-200 p-6 shadow-2xs">
        <form method="POST" action="{{ route('admin.testimonials.store') }}" class="space-y-4 text-xs">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nombre del Cliente / Contacto <span class="text-rose-600">*</span></label>
                    <input type="text" name="author_name" value="{{ old('author_name') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Cargo / Especialidad</label>
                    <input type="text" name="author_role" value="{{ old('author_role') }}" placeholder="Ej. Jefe de Compras / Maestro Carpintero" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Empresa</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Calificación (1 a 5)</label>
                    <select name="rating" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}">{{ $i }} Estrellas</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Contenido del Testimonio <span class="text-rose-600">*</span></label>
                <textarea name="content" rows="3" required class="w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('content') }}</textarea>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Referencia de Autorización (Correo, Orden o Cotización del Cliente)</label>
                <input type="text" name="authorization_reference" value="{{ old('authorization_reference') }}" placeholder="Ej. Autorizado por correo el 15/10/2026 - COT-2026-00001"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>

            <div class="space-y-2 pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_verified" value="1" @checked(old('is_verified')) class="rounded border-slate-300 text-amber-500">
                    <span class="font-semibold text-slate-800">Confirmo que este testimonio es real y cuenta con autorización del cliente</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active')) class="rounded border-slate-300 text-amber-500">
                    <span class="font-semibold text-slate-700">Publicar en el sitio web (requiere autorización verificada)</span>
                </label>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 cursor-pointer">Guardar Testimonio</button>
                <a href="{{ route('admin.testimonials.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
