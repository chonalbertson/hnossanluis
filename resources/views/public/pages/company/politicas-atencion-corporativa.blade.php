@extends('public.pages.company.layout')

@section('company-content')
    {{-- Documentos y políticas --}}
    <section class="flex flex-col gap-8">
        @include('public.pages.company.partials.heading', [
            'eyebrow' => 'Centro de políticas',
            'title' => 'Documentos oficiales para clientes y consumidores',
            'text' => 'Ponemos a tu disposición nuestras políticas comerciales, condiciones de cotización, protección de datos personales y canales oficiales de atención.',
        ])

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach([
                ['route' => route('pages.quote-conditions'), 'tag' => 'Comercial B2B', 'title' => 'Condiciones de Cotización', 'text' => 'Vigencia de ofertas, validez de stock, facturación con RUC y modalidades de despacho.', 'tone' => 'bg-blue-50 text-[#1E3A8A]', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>'],
                ['route' => route('pages.terms'), 'tag' => 'Marco legal', 'title' => 'Términos y Condiciones', 'text' => 'Lineamientos de uso del portal técnico y condiciones generales de contratación.', 'tone' => 'bg-slate-100 text-slate-700', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>'],
                ['route' => route('pages.privacy'), 'tag' => 'Ley N° 29733', 'title' => 'Política de Privacidad', 'text' => 'Tratamiento confidencial de datos de clientes y ejercicio de derechos ARCO.', 'tone' => 'bg-emerald-50 text-emerald-700', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>'],
                ['route' => route('pages.cookies'), 'tag' => 'Privacidad digital', 'title' => 'Política de Cookies', 'text' => 'Uso de sesiones para tu lista de cotización y analítica agregada sin datos personales.', 'tone' => 'bg-amber-50 text-amber-700', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-9-9 3 3 0 003 3 3 3 0 003 3 3 3 0 003 3zM8 10h.01M12 15h.01M9 15h.01"/>'],
                ['route' => route('quote.track'), 'tag' => 'Autogestión', 'title' => 'Seguimiento de Cotización', 'text' => 'Consulta el estado de tu solicitud y descarga tu propuesta PDF con tu código COT.', 'tone' => 'bg-indigo-50 text-indigo-700', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>'],
                ['route' => route('contact.index'), 'tag' => 'Atención', 'title' => 'Contacto Comercial', 'text' => 'Escríbenos para consultas comerciales, técnicas o administrativas.', 'tone' => 'bg-sky-50 text-sky-700', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>'],
            ] as $doc)
                <a href="{{ $doc['route'] }}"
                   class="group flex flex-col gap-4 p-6 rounded-2xl bg-white border border-slate-200 hover:border-[#1E3A8A] hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between">
                        <span class="w-11 h-11 rounded-xl {{ $doc['tone'] }} flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $doc['icon'] !!}</svg>
                        </span>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">{{ $doc['tag'] }}</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-black text-[#0F172A] group-hover:text-[#1E3A8A] transition-colors">{{ $doc['title'] }}</h3>
                        <p class="mt-1.5 text-xs text-slate-600 leading-relaxed">{{ $doc['text'] }}</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-xs font-extrabold text-[#1E3A8A] group-hover:gap-2.5 transition-all">
                        Leer documento
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Libro de Reclamaciones --}}
    <section class="relative overflow-hidden rounded-3xl border-2 border-[#DC2626] bg-white p-8 sm:p-10">
        <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-red-50 pointer-events-none"></div>
        <div class="relative flex flex-col md:flex-row md:items-center gap-6">
            <span class="w-16 h-16 rounded-2xl bg-[#DC2626] text-white flex items-center justify-center shrink-0 shadow-lg shadow-red-200">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </span>
            <div class="flex-1">
                <span class="text-[11px] font-extrabold uppercase tracking-widest text-[#DC2626]">INDECOPI &bull; Ley N° 29571</span>
                <h2 class="mt-1 text-xl sm:text-2xl font-black text-[#0F172A]">Libro de Reclamaciones Virtual</h2>
                <p class="mt-1 text-sm text-slate-600">Registra una hoja de reclamación o queja y recibe tu constancia de forma inmediata.</p>
            </div>
            <a href="{{ route('pages.complaints-book') }}"
               class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-[#DC2626] hover:bg-[#B91C1C] text-white text-xs font-extrabold uppercase tracking-wider transition-colors shrink-0">
                Registrar reclamo
            </a>
        </div>
    </section>

    {{-- Compromisos de atención --}}
    <section class="flex flex-col gap-8">
        @include('public.pages.company.partials.heading', [
            'eyebrow' => 'Compromisos',
            'title' => 'Nuestra promesa de atención corporativa',
            'center' => true,
        ])
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach([
                ['title' => 'Respuesta oportuna', 'text' => 'Atendemos cada solicitud dentro del horario comercial.'],
                ['title' => 'Información clara', 'text' => 'Precios, stock y condiciones por escrito en cada cotización.'],
                ['title' => 'Confidencialidad', 'text' => 'Tus datos y listas de materiales se tratan de forma reservada.'],
                ['title' => 'Mejora continua', 'text' => 'Cada reclamo o sugerencia se registra y se atiende formalmente.'],
            ] as $commitment)
                <div class="flex flex-col gap-2 p-6 rounded-2xl bg-slate-50 border border-slate-200 border-l-4 border-l-[#1E3A8A]">
                    <h3 class="text-base font-black text-[#0F172A]">{{ $commitment['title'] }}</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">{{ $commitment['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endsection
