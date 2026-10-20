@extends('layouts.admin')

@section('title', 'Contenido de Páginas y Textos Legales')
@section('header', '10. Gestión de Contenido Institucional y Páginas Legales')
@section('subheader', 'Edición de textos de Inicio, Nosotros, Política de Privacidad (Ley N° 29733), Cookies, Términos y Condiciones de Cotización.')

@section('content')
    <div class="bg-slate-900 text-white rounded-xl border border-slate-800 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
        <div class="text-xs">
            <span class="font-bold text-amber-400">¿Desea cambiar las imágenes de fondo o textos del Slider de Inicio?</span>
            <p class="text-slate-300 mt-0.5">Administre los banners del carrusel principal, suba fondos personalizados (1920 x 720 px) y edite los botones de acción.</p>
        </div>
        <a href="{{ route('admin.hero-banners.index') }}" class="px-4 py-2 rounded-lg bg-amber-500 text-slate-950 hover:bg-amber-400 text-xs font-extrabold shrink-0">
            Administrar Banners del Slider &rarr;
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Clave</th>
                        <th class="px-4 py-3 text-left">Título de Página</th>
                        <th class="px-4 py-3 text-center">Indexable SEO</th>
                        <th class="px-4 py-3 text-left">Última Edición</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($pages as $page)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono font-bold text-slate-700">{{ $page->page_key }}</td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">{{ $page->title }}</div>
                                @if($page->subtitle)
                                    <div class="text-[11px] text-slate-500 line-clamp-1">{{ $page->subtitle }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $page->is_indexable ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                                    {{ $page->is_indexable ? 'Indexable' : 'NoIndex' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-500">
                                <div>{{ $page->updated_at?->format('d/m/Y H:i') }}</div>
                                <div class="text-[11px]">{{ $page->editor?->name ?? 'Sistema' }}</div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.pages.edit', $page) }}" class="px-3 py-1.5 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-semibold">
                                    Editar Contenido
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
