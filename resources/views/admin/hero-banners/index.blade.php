@extends('layouts.admin')

@section('title', 'Banners del Slider Principal')
@section('header', 'Gestión de Banners y Fondos del Slider Principal')
@section('subheader', 'Administre las diapositivas del carrusel de Inicio, suba imágenes de fondo personalizadas y configure llamados a la acción.')

@section('content')
    {{-- Guía de Medidas Recomendadas para Banners del Slider --}}
    <div class="bg-slate-900 text-white rounded-xl border border-slate-800 p-5 shadow-2xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 text-amber-400 text-xs font-extrabold uppercase tracking-wider">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Guía Técnica de Medidas Recomendadas para el Slider</span>
                </div>
                <p class="text-xs text-slate-300 max-w-3xl leading-relaxed">
                    Para lograr nitidez óptima en monitores Full HD/2K y dispositivos móviles sin afectar la velocidad de carga, prepare sus imágenes de fondo respetando las siguientes dimensiones:
                </p>
            </div>

            <a href="{{ route('admin.hero-banners.create') }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-amber-500 text-slate-950 hover:bg-amber-400 text-xs font-extrabold shadow-xs shrink-0 transition">
                <span>+ Nuevo Banner de Slider</span>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-4 text-xs">
            <div class="bg-slate-800/90 border border-slate-700/80 rounded-lg p-3">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-amber-400">Fondo Escritorio (Desktop)</span>
                <span class="block font-mono text-sm font-black text-white mt-0.5">{{ \App\Models\HeroBanner::RECOMMENDED_DESKTOP_DIMENSIONS }}</span>
                <span class="block text-[11px] text-slate-400 mt-0.5">Proporción panorámica 8:3 (Mínimo 1600 x 600 px)</span>
            </div>

            <div class="bg-slate-800/90 border border-slate-700/80 rounded-lg p-3">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-sky-400">Fondo Móvil (Opcional)</span>
                <span class="block font-mono text-sm font-black text-white mt-0.5">{{ \App\Models\HeroBanner::RECOMMENDED_MOBILE_DIMENSIONS }}</span>
                <span class="block text-[11px] text-slate-400 mt-0.5">Proporción vertical 4:5 (O 1080 x 1080 px cuadrado)</span>
            </div>

            <div class="bg-slate-800/90 border border-slate-700/80 rounded-lg p-3">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-emerald-400">Formatos y Peso</span>
                <span class="block font-mono text-sm font-black text-white mt-0.5">{{ \App\Models\HeroBanner::RECOMMENDED_FORMATS }}</span>
                <span class="block text-[11px] text-slate-400 mt-0.5">Peso ideal &lt; 350 KB (Máximo {{ \App\Models\HeroBanner::RECOMMENDED_MAX_SIZE_MB }} MB)</span>
            </div>

            <div class="bg-slate-800/90 border border-slate-700/80 rounded-lg p-3">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-rose-400">Área Segura de Diseño</span>
                <span class="block font-bold text-sm text-white mt-0.5">Centrar elementos clave</span>
                <span class="block text-[11px] text-slate-400 mt-0.5">El texto se ubica a la izquierda y la vitrina a la derecha</span>
            </div>
        </div>
    </div>

    {{-- Tabla de Banners --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between bg-slate-50/60">
            <p class="text-xs text-slate-600">
                Diapositivas registradas: <strong>{{ $banners->total() }}</strong>
            </p>
            <a href="{{ route('home') }}" target="_blank" class="text-xs font-bold text-blue-700 hover:underline">
                Ver slider en vivo en Inicio &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-center w-16">Orden</th>
                        <th class="px-4 py-3 text-left">Vista Previa Fondo</th>
                        <th class="px-4 py-3 text-left">Pestaña / Titular del Slide</th>
                        <th class="px-4 py-3 text-center">Vitrina Flotante</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($banners as $banner)
                        @php
                            $bgUrl = $banner->backgroundImageUrl();
                            $accentColors = [
                                'red' => 'from-[#0B1220] via-[#111C3A] to-[#DC2626]',
                                'blue' => 'from-[#0B1220] via-[#0F2357] to-sky-600',
                                'amber' => 'from-[#0B1220] via-[#1F1A10] to-amber-500',
                                'emerald' => 'from-[#0B1220] via-[#0B2A27] to-emerald-600',
                            ];
                            $previewGradient = $accentColors[$banner->accent] ?? $accentColors['red'];
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-center font-mono font-bold text-slate-700">
                                #{{ $banner->sort_order }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="relative w-36 h-16 rounded-lg overflow-hidden border border-slate-200 shadow-2xs bg-gradient-to-br {{ $previewGradient }} flex items-center justify-center">
                                    @if($bgUrl)
                                        <img src="{{ $bgUrl }}"
                                             alt="{{ $banner->label }}"
                                             class="w-full h-full object-cover">
                                        <span class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded bg-slate-950/80 text-[9px] font-mono text-amber-300 font-bold">
                                            Imagen ({{ $banner->overlay_opacity }}% velo)
                                        </span>
                                    @else
                                        <span class="text-[10px] font-bold text-white/85 px-2 text-center leading-tight">
                                            Gradiente Técnico ({{ strtoupper($banner->accent) }})
                                        </span>
                                    @endif
                                </div>
                                @if($banner->mobile_background_image_path)
                                    <span class="inline-block mt-1 text-[10px] font-semibold text-blue-700 bg-blue-50 border border-blue-200 px-1.5 py-0.5 rounded">
                                        + Fondo móvil activo
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 max-w-md">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded bg-slate-900 text-amber-400 font-mono text-[10px] font-bold uppercase">
                                        {{ $banner->label }}
                                    </span>
                                    @if($banner->badge_metric)
                                        <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200 font-mono text-[10px] font-semibold">
                                            {{ $banner->badge_metric }}
                                        </span>
                                    @endif
                                </div>
                                <div class="font-bold text-slate-900 mt-1 text-sm leading-snug">
                                    {{ $banner->title }}
                                    @if($banner->highlight)
                                        <span class="text-red-600">{{ $banner->highlight }}</span>
                                    @endif
                                    {{ $banner->suffix }}
                                </div>
                                @if($banner->description)
                                    <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">{{ $banner->description }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($banner->show_products_showcase && $banner->product_source !== 'none')
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-800">
                                        Activa ({{ ucfirst($banner->product_source) }})
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">
                                        Solo Banner / Fondo
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $banner->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                                    {{ $banner->is_active ? 'Publicado' : 'Oculto' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <form method="POST" action="{{ route('admin.hero-banners.toggle', $banner) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2.5 py-1 rounded border border-slate-300 text-slate-700 hover:bg-slate-50 cursor-pointer">
                                            {{ $banner->is_active ? 'Ocultar' : 'Publicar' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.hero-banners.edit', $banner) }}"
                                       class="px-2.5 py-1 rounded bg-slate-900 text-white hover:bg-slate-800 font-semibold">
                                        Editar / Subir Fondo
                                    </a>
                                    <form method="POST" action="{{ route('admin.hero-banners.destroy', $banner) }}" onsubmit="return confirm('¿Eliminar este banner del slider?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 rounded border border-rose-200 text-rose-700 hover:bg-rose-50 cursor-pointer">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                No hay banners personalizados registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($banners->hasPages())
            <div class="px-4 py-3 border-t border-slate-200">
                {{ $banners->links() }}
            </div>
        @endif
    </div>
@endsection
