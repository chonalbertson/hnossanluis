@extends('public.pages.company.layout')

@section('company-content')
    {{-- Casos --}}
    <section class="flex flex-col gap-8">
        @include('public.pages.company.partials.heading', [
            'eyebrow' => 'Aplicaciones reales',
            'title' => 'Dónde se instalan nuestros productos',
            'text' => 'Nuestros productos están presentes en proyectos de equipamiento inmobiliario, fabricación seriada de muebles y distribución ferretera en todo el territorio nacional.',
        ])

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach([
                ['tag' => 'Industria del Mueble', 'meta' => 'Producción en serie', 'color' => 'from-[#1E3A8A] to-[#0F172A]', 'badge' => 'bg-[#1E3A8A]', 'title' => 'Amoblamiento de cocinas, clósets y oficinas', 'text' => 'Suministro por cajas de bisagras cangrejo (cierre suave y estándar), correderas telescópicas pesadas, pistones a gas, jaladores, tapacantos y tornillos estructurales para talleres y plantas de mobiliario.', 'items' => ['Bisagras cangrejo', 'Correderas telescópicas', 'Pistones a gas', 'Tapacantos']],
                ['tag' => 'Construcción y Obras', 'meta' => 'Proyectos multifamiliares', 'color' => 'from-[#DC2626] to-[#7F1D1D]', 'badge' => 'bg-[#DC2626]', 'title' => 'Cerrajería, sistemas corredizos y fijaciones', 'text' => 'Abastecimiento de rieles y carros para puertas corredizas, cerraduras, candados, tarugos, arandelas, brocas y niveles para contratistas de acabados e instaladores de obra.', 'items' => ['Sistemas corredizos', 'Cerraduras', 'Tarugos y anclajes', 'Brocas']],
                ['tag' => 'Abastecimiento Mayorista', 'meta' => 'Lima y provincias', 'color' => 'from-emerald-600 to-emerald-900', 'badge' => 'bg-emerald-700', 'title' => 'Ferreterías y distribuidores regionales', 'text' => 'Atención mayorista de pegamentos de contacto, siliconas, pinturas en spray, herramientas manuales y eléctricas con despacho consolidado en agencias interprovinciales.', 'items' => ['Pegamentos', 'Siliconas', 'Pinturas en spray', 'Herramientas']],
                ['tag' => 'Diseño e Iluminación', 'meta' => 'Arquitectura interior', 'color' => 'from-amber-500 to-amber-800', 'badge' => 'bg-amber-600', 'title' => 'Iluminación LED y herrajes especiales', 'text' => 'Soluciones de cintas LED, fuentes transformadoras, perfiles de aluminio para LED, pasacables y organizadores para mobiliario comercial y corporativo.', 'items' => ['Cintas LED', 'Perfiles de aluminio', 'Pasacables', 'Organizadores']],
            ] as $case)
                <article class="group flex flex-col rounded-3xl bg-white border border-slate-200 overflow-hidden hover:shadow-2xl transition-shadow">
                    <div class="relative h-32 bg-gradient-to-br {{ $case['color'] }} p-6 flex items-end justify-between">
                        <div class="absolute inset-0 opacity-10" style="background-image: linear-gradient(45deg, #fff 25%, transparent 25%, transparent 50%, #fff 50%, #fff 75%, transparent 75%); background-size: 20px 20px;"></div>
                        <span class="relative px-3 py-1 rounded-md bg-white/15 backdrop-blur text-white text-[11px] font-extrabold uppercase tracking-wider">{{ $case['tag'] }}</span>
                        <span class="relative font-mono text-5xl font-black text-white/20">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="flex flex-col gap-3 p-7 flex-1">
                        <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400">{{ $case['meta'] }}</span>
                        <h3 class="text-xl font-black text-[#0F172A]">{{ $case['title'] }}</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">{{ $case['text'] }}</p>
                        <ul class="mt-auto pt-4 flex flex-wrap gap-2 border-t border-slate-100">
                            @foreach($case['items'] as $item)
                                <li class="px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200 text-[11px] font-bold text-slate-700">{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Testimonios --}}
    <section class="flex flex-col gap-8">
        @include('public.pages.company.partials.heading', [
            'eyebrow' => 'Clientes verificados',
            'title' => 'Lo que dicen quienes trabajan con nosotros',
            'center' => true,
        ])

        @if($testimonials->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($testimonials as $testimonial)
                    <figure class="flex flex-col gap-4 p-7 rounded-2xl bg-white border border-slate-200 shadow-sm">
                        <svg class="w-8 h-8 text-[#DC2626]/30" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                        <blockquote class="text-sm text-slate-700 leading-relaxed flex-1">{{ $testimonial->content }}</blockquote>
                        <figcaption class="flex items-center gap-3 pt-4 border-t border-slate-100">
                            <span class="w-10 h-10 rounded-full bg-[#1E3A8A] text-white font-black flex items-center justify-center shrink-0">{{ mb_strtoupper(mb_substr($testimonial->author_name, 0, 1)) }}</span>
                            <span>
                                <span class="block text-sm font-black text-[#0F172A]">{{ $testimonial->author_name }}</span>
                                @if($testimonial->company_name)
                                    <span class="block text-xs text-slate-500">{{ $testimonial->company_name }}</span>
                                @endif
                            </span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        @else
            <div class="p-8 rounded-2xl bg-white border border-dashed border-slate-300 text-center">
                <p class="text-sm text-slate-500">Pronto publicaremos testimonios verificados de nuestros clientes.</p>
                <a href="{{ route('pages.testimonials') }}" class="mt-3 inline-block text-xs font-extrabold text-[#1E3A8A] hover:text-[#DC2626]">Ver sectores atendidos &rarr;</a>
            </div>
        @endif
    </section>
@endsection
