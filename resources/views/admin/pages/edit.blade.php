@extends('layouts.admin')

@section('title', 'Editar Página: ' . $pageContent->title)
@section('header', 'Editar Página: ' . $pageContent->page_key)
@section('subheader', $pageContent->title)

@section('content')
    @if($pageContent->page_key === 'home')
        <div class="max-w-4xl bg-amber-50 border border-amber-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-amber-950">
            <div>
                <strong class="font-bold">Gestión de Banners del Slider de Inicio:</strong>
                <span>Para subir imágenes de fondo (medida recomendada 1920 x 720 px) y administrar las diapositivas del carrusel principal, utilice el gestor de Banners.</span>
            </div>
            <a href="{{ route('admin.hero-banners.index') }}" class="px-3.5 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-bold shrink-0">
                Ir a Banners / Slider &rarr;
            </a>
        </div>
    @endif

    <div class="max-w-4xl bg-white rounded-xl border border-slate-200 p-6 shadow-2xs">
        <form method="POST" action="{{ route('admin.pages.update', $pageContent) }}" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Título Principal <span class="text-rose-600">*</span></label>
                <input type="text" name="title" value="{{ old('title', $pageContent->title) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Subtítulo / Bajada</label>
                <input type="text" name="subtitle" value="{{ old('subtitle', $pageContent->subtitle) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Contenido (HTML permitido: &lt;h3&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt;) <span class="text-rose-600">*</span></label>
                <textarea name="content" rows="12" required class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono">{{ old('content', $pageContent->content) }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Meta Título SEO</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $pageContent->meta_title) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Meta Descripción SEO</label>
                    <input type="text" name="meta_description" value="{{ old('meta_description', $pageContent->meta_description) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_indexable" value="1" @checked(old('is_indexable', $pageContent->is_indexable)) class="rounded border-slate-300 text-amber-500">
                <span class="font-semibold text-slate-700">Permitir indexación en motores de búsqueda (index, follow)</span>
            </label>

            <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 cursor-pointer">Guardar Contenido</button>
                <a href="{{ route('admin.pages.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">Volver</a>
            </div>
        </form>
    </div>
@endsection
