@extends('public.pages.company.layout')

@section('company-title')
    Soluciones Integrales en <span class="text-amber-300">Herrajes, Fijaciones</span> y Suministro Industrial B2B
@endsection

@section('company-lead')
    Abastecemos a fábricas de muebles, constructoras, distribuidores ferreteros, contratistas y talleres en todo el Perú desde nuestra sede central en Lima Centro, con stock permanente, especificaciones verificadas por SKU y cotizaciones formales.
@endsection

@section('company-content')
    <section class="flex flex-col gap-10">
        @include('public.pages.company.partials.heading', [
            'eyebrow' => 'Conoce a ' . $companyInfo['commercial_name'],
            'title' => 'Todo sobre nuestra empresa, en un solo lugar',
            'text' => 'Explora cada área institucional en su propia página: quiénes somos, qué abastecemos, cómo garantizamos calidad, hasta dónde llegamos y quiénes te atienden.',
        ])

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($sections as $slug => $meta)
                <a href="{{ route('pages.company', ['section' => $slug]) }}"
                   class="group relative flex flex-col gap-4 p-6 rounded-2xl bg-white border border-slate-200 hover:border-transparent hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#1E3A8A] to-[#DC2626] scale-x-0 group-hover:scale-x-100 origin-left transition-transform duration-300"></span>
                    <div class="flex items-start justify-between">
                        <span class="w-12 h-12 rounded-xl bg-blue-50 text-[#1E3A8A] group-hover:bg-[#1E3A8A] group-hover:text-white flex items-center justify-center transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $meta['icon'] !!}</svg>
                        </span>
                        <span class="font-mono text-2xl font-black text-slate-200 group-hover:text-[#DC2626]/30 transition-colors">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="flex flex-col gap-1.5 flex-1">
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#DC2626]">{{ $meta['eyebrow'] }}</span>
                        <h3 class="text-base font-black text-[#0F172A] group-hover:text-[#1E3A8A] transition-colors">{{ $meta['label'] }}</h3>
                        <p class="text-xs text-slate-600 leading-relaxed line-clamp-3">{{ $meta['summary'] }}</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-xs font-extrabold text-[#1E3A8A] group-hover:gap-2.5 transition-all">
                        Ver página
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        @foreach([
            ['title' => 'Importador directo', 'text' => 'Precios competitivos de importador y abastecimiento continuo para producción en serie.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'],
            ['title' => 'Catálogo técnico verificado', 'text' => 'Cada producto con SKU único, unidad de venta clara y ficha técnica descargable en PDF.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>'],
            ['title' => 'Despacho a todo el Perú', 'text' => 'Entrega en Lima Metropolitana y Callao, y envíos diarios a provincias por agencias de carga.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>'],
        ] as $highlight)
            <div class="flex items-start gap-4 p-6 rounded-2xl bg-[#0F172A] text-white">
                <span class="w-11 h-11 rounded-xl bg-[#DC2626] flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $highlight['icon'] !!}</svg>
                </span>
                <div>
                    <h3 class="text-base font-black">{{ $highlight['title'] }}</h3>
                    <p class="text-xs text-slate-300 mt-1 leading-relaxed">{{ $highlight['text'] }}</p>
                </div>
            </div>
        @endforeach
    </section>
@endsection
