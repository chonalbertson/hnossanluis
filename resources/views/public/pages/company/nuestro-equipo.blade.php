@extends('public.pages.company.layout')

@section('company-content')
    {{-- Asesoras comerciales --}}
    <section class="flex flex-col gap-8">
        @include('public.pages.company.partials.heading', [
            'eyebrow' => 'Asesoras comerciales',
            'title' => 'Personas reales que conocen cada producto',
            'text' => 'Nuestro equipo brinda acompañamiento directo en la selección de herrajes, equivalencias de medidas, validación de stock y cotizaciones por volumen. Escríbenos directamente por WhatsApp.',
        ])

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($whatsappAdvisors as $advisor)
                @php
                    $advisorName = $advisor['name'] !== '' ? $advisor['name'] : 'Central Ventas';
                @endphp
                <article class="group flex flex-col rounded-3xl bg-white border border-slate-200 overflow-hidden hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                    <div class="relative h-24 bg-gradient-to-br from-[#1E3A8A] to-[#0F172A]">
                        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 14px 14px;"></div>
                    </div>
                    <div class="flex flex-col items-center text-center gap-3 px-6 pb-6 -mt-10">
                        <span class="relative w-20 h-20 rounded-full bg-white p-1 shadow-lg">
                            <span class="w-full h-full rounded-full bg-gradient-to-br from-[#DC2626] to-[#991B1B] text-white text-2xl font-black flex items-center justify-center">
                                {{ mb_strtoupper(mb_substr($advisorName, 0, 1)) }}
                            </span>
                            <span class="absolute bottom-1 right-1 w-4 h-4 rounded-full bg-[#22C55E] border-2 border-white" title="Disponible en horario comercial"></span>
                        </span>
                        <div>
                            <h3 class="text-lg font-black text-[#0F172A]">{{ $advisorName }}</h3>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Asesora Comercial B2B</p>
                        </div>
                        <p class="font-mono text-base font-black text-[#1E3A8A]">{{ $advisor['phone'] }}</p>
                        <a href="{{ $advisor['url'] }}"
                           target="_blank" rel="noopener noreferrer"
                           data-ga-event="whatsapp_click"
                           data-ga-params='{"cta_location":"company_team","context_type":"advisor"}'
                           class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#22C55E] hover:bg-[#16A34A] text-white text-xs font-extrabold uppercase tracking-wider transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            Escribir por WhatsApp
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Áreas de atención --}}
    <section class="flex flex-col gap-8">
        @include('public.pages.company.partials.heading', [
            'eyebrow' => 'Organización',
            'title' => 'Áreas que trabajan para tu abastecimiento',
            'center' => true,
        ])
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach([
                ['title' => 'Ventas corporativas', 'text' => 'Cotizaciones formales, precios por volumen y seguimiento de pedidos.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>'],
                ['title' => 'Asesoría técnica', 'text' => 'Selección de herrajes, medidas, capacidades de carga y equivalencias.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>'],
                ['title' => 'Logística y despacho', 'text' => 'Preparación, embalaje y coordinación de entregas en Lima y provincias.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'],
                ['title' => 'Facturación', 'text' => 'Comprobantes electrónicos SUNAT y atención administrativa.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>'],
            ] as $area)
                <div class="flex flex-col gap-3 p-6 rounded-2xl bg-white border border-slate-200 hover:border-[#1E3A8A] transition-colors">
                    <span class="w-11 h-11 rounded-xl bg-blue-50 text-[#1E3A8A] flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $area['icon'] !!}</svg>
                    </span>
                    <h3 class="text-base font-black text-[#0F172A]">{{ $area['title'] }}</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">{{ $area['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Canales de contacto --}}
    <section class="grid grid-cols-1 md:grid-cols-3 gap-px rounded-3xl overflow-hidden bg-slate-200 border border-slate-200">
        @foreach([
            ['label' => 'Horario de atención', 'value' => $companyInfo['hours']],
            ['label' => 'Correo comercial', 'value' => $companyInfo['email']],
            ['label' => 'Visítanos', 'value' => $companyInfo['address']],
        ] as $channel)
            <div class="bg-white p-6">
                <p class="text-[11px] font-extrabold uppercase tracking-widest text-[#DC2626]">{{ $channel['label'] }}</p>
                <p class="mt-2 text-sm font-bold text-[#0F172A] break-words">{{ $channel['value'] }}</p>
            </div>
        @endforeach
    </section>
@endsection
