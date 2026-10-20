@extends('layouts.admin')

@section('title', 'Nueva Categoría')
@section('header', 'Crear Categoría o Subcategoría')

@section('content')
    <div class="max-w-3xl bg-white rounded-xl border border-slate-200 p-6 shadow-2xs">
        <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nombre de Categoría <span class="text-rose-600">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Categoría Padre (Opcional si es Subcategoría)</label>
                    <select name="parent_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                        <option value="">— Es Categoría Principal —</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}" @selected((int) old('parent_id') === $parent->id)>{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Slug URL (se autogenera si se deja vacío)</label>
                    <input type="text" name="slug" value="{{ old('slug') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Orden de Visualización</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Descripción Técnica</label>
                <textarea name="description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Atributos Técnicos Sugeridos (separados por coma)</label>
                <input type="text" name="attribute_schema_raw" value="{{ old('attribute_schema_raw') }}" placeholder="Ej. Capacidad de Carga, Diámetro, Acabado, Ciclos"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Meta Título SEO</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Imagen Representativa (Opcional)</label>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" class="w-full text-slate-600">
                </div>
            </div>

            <label class="flex items-center gap-2 pt-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-amber-500">
                <span class="font-semibold text-slate-700">Categoría activa y visible en navegación pública</span>
            </label>

            <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 cursor-pointer">Guardar Categoría</button>
                <a href="{{ route('admin.categories.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
