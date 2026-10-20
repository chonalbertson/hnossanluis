@extends('public.pages.company.layout')

@section('company-content')
    {{-- Sede central + mapa --}}
    <section class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-stretch">
        <div class="lg:col-span-2 flex flex-col gap-6 p-8 rounded-3xl bg-gradient-to-br from-[#0F172A] to-[#1E3A8A] text-white">
            <div class="flex flex-col gap-2">
                <span class="text-[11px] font-extrabold uppercase tracking-widest text-amber-300">Sede central y despacho directo</span>
                <h2 class="text-2xl font-black leading-tight">{{ $companyInfo['address'] }}</h2>
            </div>
            <ul class="flex flex-col gap-4 text-sm">
                <li class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-blue-100">{{ $companyInfo['hours'] }}</span>
                </li>
                <li class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span class="text-blue-100 font-mono">{{ $companyInfo['phone'] }}</span>
                </li>
                <li class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span class="text-blue-100 break-all">{{ $companyInfo['email'] }}</span>
                </li>
            </ul>
            <a href="https://www.google.com/maps/search/?api=1&query={{ rawurlencode('XX34+W4J Lima') }}"
               target="_blank" rel="noopener noreferrer"
               class="mt-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-[#DC2626] hover:bg-[#B91C1C] text-white text-xs font-extrabold uppercase tracking-wider transition-colors">
                Cómo llegar en Google Maps
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
        <div class="lg:col-span-3 min-h-80 rounded-3xl overflow-hidden border border-slate-200 shadow-lg bg-slate-100">
            <iframe title="Ubicación de {{ $companyInfo['legal_name'] }}"
                    src="https://maps.google.com/maps?q={{ rawurlencode('XX34+W4J Lima, Perú') }}&z=17&hl=es&output=embed"
                    class="w-full h-full min-h-80 border-0"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen></iframe>
        </div>
    </section>

    {{-- Zonas de atención --}}
    <section class="flex flex-col gap-8">
        @include('public.pages.company.partials.heading', [
            'eyebrow' => 'Zonas de atención',
            'title' => 'Tres modalidades de entrega para llegar a todo el país',
        ])
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach([
                ['zone' => 'Zona 1', 'name' => 'Tienda y almacén', 'title' => 'Atención en Lima Centro', 'accent' => 'border-t-[#1E3A8A] text-[#1E3A8A]', 'text' => 'Retiro inmediato en mostrador comercial ubicado en Av. Guillermo Dansey 429, eje ferretero principal de Lima.'],
                ['zone' => 'Zona 2', 'name' => 'Lima Metropolitana y Callao', 'title' => 'Despacho a talleres y obras', 'accent' => 'border-t-[#DC2626] text-[#DC2626]', 'text' => 'Coordinación ágil de entregas hacia parques industriales: Villa El Salvador, Ate, San Juan de Lurigancho, Los Olivos, Callao, Surquillo y más.'],
                ['zone' => 'Zona 3', 'name' => 'Envíos a provincias', 'title' => 'Red de agencias de carga', 'accent' => 'border-t-emerald-600 text-emerald-700', 'text' => 'Embalaje seguro y embarque diario hacia las principales ciudades del país a través de la agencia de transporte de tu preferencia.'],
            ] as $zone)
                <article class="flex flex-col gap-3 p-7 rounded-2xl bg-white border border-slate-200 border-t-4 {{ $zone['accent'] }} shadow-sm hover:shadow-xl transition-shadow">
                    <span class="text-[11px] font-extrabold uppercase tracking-widest">{{ $zone['zone'] }} &bull; {{ $zone['name'] }}</span>
                    <h3 class="text-lg font-black text-[#0F172A]">{{ $zone['title'] }}</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">{{ $zone['text'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Ciudades --}}
    <section class="rounded-3xl bg-white border border-slate-200 p-8 sm:p-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="text-[11px] font-extrabold uppercase tracking-widest text-[#DC2626]">Envíos frecuentes</span>
                <h2 class="mt-1 text-xl sm:text-2xl font-black text-[#0F172A]">Llegamos a las principales ciudades del Perú</h2>
            </div>
            <span class="px-3 py-1.5 rounded-lg bg-[#0F172A] text-white text-xs font-extrabold uppercase tracking-wider self-start md:self-auto">Lima &amp; Provincias</span>
        </div>
        <ul class="mt-6 flex flex-wrap gap-2">
            @foreach(['Lima', 'Callao', 'Arequipa', 'Trujillo', 'Chiclayo', 'Piura', 'Cusco', 'Huancayo', 'Cajamarca', 'Ica', 'Chimbote', 'Huánuco', 'Pucallpa', 'Tarapoto', 'Juliaca', 'Puno', 'Tacna', 'Ayacucho', 'Iquitos', 'Tumbes'] as $city)
                <li class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-50 border border-slate-200 text-xs font-bold text-slate-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#DC2626]"></span>
                    {{ $city }}
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Cómo despachamos --}}
    <section class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @foreach([
            ['title' => 'Confirmas tu pedido', 'text' => 'Con la cotización aprobada y el comprobante emitido.'],
            ['title' => 'Preparamos y embalamos', 'text' => 'Verificación de SKU y embalaje protegido.'],
            ['title' => 'Despachamos', 'text' => 'Entrega directa en Lima o embarque en agencia.'],
            ['title' => 'Te notificamos', 'text' => 'Compartimos guía y datos de recojo por WhatsApp.'],
        ] as $step)
            <div class="flex items-start gap-3 p-5 rounded-2xl bg-slate-900 text-white">
                <span class="font-mono text-2xl font-black text-amber-300">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                <div>
                    <h3 class="text-sm font-black">{{ $step['title'] }}</h3>
                    <p class="text-xs text-slate-300 mt-1">{{ $step['text'] }}</p>
                </div>
            </div>
        @endforeach
    </section>
@endsection
