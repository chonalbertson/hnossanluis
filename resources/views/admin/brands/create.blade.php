@extends('layouts.admin')

@section('title', 'Nueva Marca')
@section('header', 'Registrar Nueva Marca')

@section('content')
    <div class="max-w-2xl bg-white rounded-xl border border-slate-200 p-6 shadow-2xs">
        <form method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nombre de la Marca <span class="text-rose-600">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Slug URL (opcional)</label>
                    <input type="text" name="slug" value="{{ old('slug') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Sitio Web Oficial</label>
                    <input type="url" name="website" value="{{ old('website') }}" placeholder="https://..." class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Orden de Aparición</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Descripción de Línea</label>
                <textarea name="description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Logotipo (JPG, PNG, WEBP, SVG máx. 4MB)</label>
                <input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp,.svg" class="w-full text-slate-600">
            </div>

            <div class="space-y-2 pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_verified" value="1" checked class="rounded border-slate-300 text-amber-500">
                    <span class="font-semibold text-slate-700">Marca verificada / comercializada oficialmente</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-amber-500">
                    <span class="font-semibold text-slate-700">Marca activa en catálogo</span>
                </label>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 cursor-pointer">Guardar Marca</button>
                <a href="{{ route('admin.brands.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
