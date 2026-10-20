@extends('layouts.admin')

@section('title', 'Editar Marca')
@section('header', 'Editar Marca: ' . $brand->name)

@section('content')
    <div class="max-w-2xl bg-white rounded-xl border border-slate-200 p-6 shadow-2xs">
        <form method="POST" action="{{ route('admin.brands.update', $brand) }}" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nombre de la Marca <span class="text-rose-600">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $brand->name) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Slug URL</label>
                    <input type="text" name="slug" value="{{ old('slug', $brand->slug) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Sitio Web Oficial</label>
                    <input type="url" name="website" value="{{ old('website', $brand->website) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Orden de Aparición</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $brand->sort_order) }}" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Descripción de Línea</label>
                <textarea name="description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('description', $brand->description) }}</textarea>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Reemplazar Logotipo (Opcional)</label>
                <input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp,.svg" class="w-full text-slate-600">
            </div>

            <div class="space-y-2 pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_verified" value="1" @checked(old('is_verified', $brand->is_verified)) class="rounded border-slate-300 text-amber-500">
                    <span class="font-semibold text-slate-700">Marca verificada / comercializada oficialmente</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $brand->is_active)) class="rounded border-slate-300 text-amber-500">
                    <span class="font-semibold text-slate-700">Marca activa en catálogo</span>
                </label>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 cursor-pointer">Actualizar Marca</button>
                <a href="{{ route('admin.brands.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
