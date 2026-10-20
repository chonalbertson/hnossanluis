@extends('public.pages.company.layout')

@section('company-content')
    {{-- Líneas técnicas (catálogo real) --}}
    <section class="flex flex-col gap-8">
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
            @include('public.pages.company.partials.heading', [
                'eyebrow' => 'Líneas de suministro',
                'title' => 'Divisiones técnicas para cada etapa de tu proyecto',
                'text' => 'Desde el armado integral de mobiliario en melamina y madera hasta el equipamiento de obra civil y mantenimiento industrial.',
            ])
            <a href="{{ route('catalog.index') }}"
               class="inline-flex items-center gap-2 self-start lg:self-auto px-4 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-[#0F172A] text-white text-xs font-extrabold uppercase tracking-wider transition-colors shrink-0">
                Ver catálogo completo
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>

        @if($categories->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($categories as $cat)
                    <a href="{{ route('catalog.index', ['category' => $cat->slug]) }}"
                       class="group flex flex-col gap-4 p-6 rounded-2xl bg-white border border-slate-200 hover:border-[#1E3A8A] hover:shadow-xl transition-all">
                        <div class="flex items-start justify-between gap-3">
                            <span class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 text-[#1E3A8A] group-hover:from-[#DC2626] group-hover:to-[#B91C1C] group-hover:text-white flex items-center justify-center shrink-0 transition-colors">
                                {!! $cat->iconSvg('w-6 h-6') !!}
                            </span>
                            <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-mono text-[11px] font-bold">{{ $cat->products_count }} SKUs</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-base font-black text-[#0F172A] group-hover:text-[#1E3A8A] transition-colors">{{ $cat->name }}</h3>
                            @if($cat->children->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    @foreach($cat->children->take(5) as $child)
                                        <span class="px-2 py-0.5 rounded-md bg-slate-50 border border-slate-200 text-[11px] font-semibold text-slate-600">{{ $child->name }}</span>
                                    @endforeach
                                    @if($cat->children->count() > 5)
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold text-[#1E3A8A]">+{{ $cat->children->count() - 5 }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <span class="text-xs font-extrabold text-[#DC2626] inline-flex items-center gap-1.5 group-hover:gap-2.5 transition-all">
                            Explorar línea
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        @else
            <p class="p-6 rounded-2xl bg-white border border-dashed border-slate-300 text-sm text-slate-500 text-center">Pronto publicaremos nuestras líneas de suministro en el catálogo.</p>
        @endif
    </section>

    {{-- Sectores atendidos --}}
    <section class="flex flex-col gap-8">
        @include('public.pages.company.partials.heading', [
            'eyebrow' => 'Clientes B2B',
            'title' => 'Sectores que abastecemos',
            'center' => true,
        ])
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach([
                ['name' => 'Fábricas de muebles', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>'],
                ['name' => 'Constructoras', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'],
                ['name' => 'Ferreterías', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>'],
                ['name' => 'Carpinterías', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'],
                ['name' => 'Contratistas', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>'],
                ['name' => 'Arquitectos y diseñadores', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>'],
            ] as $sector)
                <div class="flex flex-col items-center text-center gap-3 p-5 rounded-2xl bg-white border border-slate-200 hover:border-[#DC2626] hover:-translate-y-1 transition-all">
                    <span class="w-12 h-12 rounded-full bg-[#0F172A] text-white flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $sector['icon'] !!}</svg>
                    </span>
                    <span class="text-xs font-extrabold text-[#0F172A]">{{ $sector['name'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Capacidades --}}
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center rounded-3xl bg-gradient-to-br from-blue-50 to-white border border-blue-100 p-8 sm:p-12">
        <div class="flex flex-col gap-4">
            <span class="text-[11px] font-extrabold uppercase tracking-widest text-[#DC2626]">Valor agregado B2B</span>
            <h2 class="text-2xl sm:text-3xl font-black text-[#0F172A] leading-tight">Más que un proveedor: un aliado de abastecimiento</h2>
            <p class="text-sm text-slate-600 leading-relaxed">Acompañamos a tu área de compras con información técnica precisa y condiciones comerciales pensadas para volumen.</p>
        </div>
        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach([
                ['title' => 'Precios por volumen', 'text' => 'Escalas por caja, millar o paquete.'],
                ['title' => 'Stock permanente', 'text' => 'Continuidad para producción en serie.'],
                ['title' => 'Asesoría técnica', 'text' => 'Equivalencias, medidas y compatibilidades.'],
                ['title' => 'Fichas técnicas PDF', 'text' => 'Documentación para expedientes de obra.'],
            ] as $capability)
                <li class="flex items-start gap-3 p-4 rounded-xl bg-white border border-slate-200 shadow-sm">
                    <span class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-black text-[#0F172A]">{{ $capability['title'] }}</h3>
                        <p class="text-xs text-slate-600 mt-0.5">{{ $capability['text'] }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
@endsection
