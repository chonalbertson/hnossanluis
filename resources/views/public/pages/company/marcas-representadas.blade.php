@extends('public.pages.company.layout')

@section('company-content')
    {{-- Marcas --}}
    <section class="flex flex-col gap-8">
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
            @include('public.pages.company.partials.heading', [
                'eyebrow' => 'Portafolio de marcas',
                'title' => 'Fabricantes que respaldan nuestro catálogo',
                'text' => 'Haz clic en cualquier marca para explorar sus productos disponibles con stock, ficha técnica y precio por volumen.',
            ])
            <span class="self-start lg:self-auto px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-extrabold uppercase tracking-wider shrink-0">
                {{ $verifiedBrands->count() }} marcas verificadas
            </span>
        </div>

        @if($verifiedBrands->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach($verifiedBrands as $brand)
                    <a href="{{ route('catalog.index', ['brand' => $brand->slug]) }}"
                       class="group flex flex-col rounded-2xl bg-white border border-slate-200 hover:border-[#1E3A8A] hover:shadow-xl transition-all overflow-hidden">
                        <div class="h-28 flex items-center justify-center p-5 bg-gradient-to-b from-white to-slate-50">
                            @if($brand->logo_path)
                                <img src="{{ asset('storage/' . $brand->logo_path) }}"
                                     alt="{{ $brand->name }}"
                                     loading="lazy"
                                     class="max-h-16 w-auto object-contain grayscale group-hover:grayscale-0 group-hover:scale-105 transition-all duration-300">
                            @else
                                <span class="text-lg font-black text-slate-400 group-hover:text-[#1E3A8A] transition-colors text-center">{{ $brand->name }}</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between gap-2 px-4 py-3 border-t border-slate-100">
                            <span class="text-xs font-extrabold text-[#0F172A] group-hover:text-[#1E3A8A] truncate">{{ $brand->name }}</span>
                            <span class="px-2 py-0.5 rounded-full bg-slate-100 group-hover:bg-[#DC2626] group-hover:text-white text-[10px] font-mono font-bold text-slate-500 transition-colors shrink-0">{{ $brand->products_count }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <p class="p-8 rounded-2xl bg-white border border-dashed border-slate-300 text-sm text-slate-500 text-center">Estamos actualizando nuestro portafolio de marcas. Consulta disponibilidad con nuestras asesoras.</p>
        @endif
    </section>

    {{-- Ventajas --}}
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        @foreach([
            ['title' => 'Productos originales', 'text' => 'Abastecimiento directo de fabricantes y distribuidores autorizados, sin intermediarios dudosos.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>'],
            ['title' => 'Continuidad de modelos', 'text' => 'Mantenemos las mismas referencias en stock para que tu producción en serie no se detenga.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>'],
            ['title' => 'Garantía del fabricante', 'text' => 'Respaldo técnico y comercial ante cualquier consulta sobre el desempeño del producto.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>'],
        ] as $advantage)
            <div class="flex flex-col gap-4 p-7 rounded-2xl bg-gradient-to-br from-slate-900 to-[#1E3A8A] text-white">
                <span class="w-12 h-12 rounded-xl bg-white/10 border border-white/20 text-amber-300 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $advantage['icon'] !!}</svg>
                </span>
                <h3 class="text-lg font-black">{{ $advantage['title'] }}</h3>
                <p class="text-sm text-blue-100 leading-relaxed">{{ $advantage['text'] }}</p>
            </div>
        @endforeach
    </section>
@endsection
