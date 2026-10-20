@extends('public.pages.company.layout')

@section('company-content')
    {{-- Estándares técnicos --}}
    <section class="flex flex-col gap-8">
        @include('public.pages.company.partials.heading', [
            'eyebrow' => 'Estándares que exigimos',
            'title' => 'Calidad verificable en cada producto que comercializamos',
            'text' => 'En ' . $companyInfo['legal_name'] . ' trabajamos exclusivamente con fabricantes y marcas cuyas líneas de producción cumplen exigencias técnicas de durabilidad, resistencia y trazabilidad comercial.',
        ])

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach([
                ['title' => 'Desempeño mecánico y ciclos de apertura', 'tag' => 'Durabilidad', 'tone' => 'text-[#1E3A8A] bg-blue-50', 'text' => 'Bisagras cangrejo, correderas telescópicas y pistones a gas seleccionados por su espesor de acero en milímetros, suavidad de rodamiento y capacidad real de carga (kg).'],
                ['title' => 'Recubrimiento anticorrosivo', 'tag' => 'Resistencia', 'tone' => 'text-[#DC2626] bg-red-50', 'text' => 'Acabados zincados, niquelados, cromados y acero inoxidable aptos para climas de alta humedad relativa como Lima Metropolitana y zonas costeras del Perú.'],
                ['title' => 'Fichas técnicas verificables por SKU', 'tag' => 'Trazabilidad', 'tone' => 'text-emerald-700 bg-emerald-50', 'text' => 'Cada ítem del catálogo cuenta con código SKU único, unidad de venta clara (Unidad, Par, Juego, Caja, Millar) y ficha técnica descargable en PDF para expedientes de obra.'],
                ['title' => 'Formalidad tributaria y legal en el Perú', 'tag' => 'Cumplimiento', 'tone' => 'text-amber-700 bg-amber-50', 'text' => 'Emisión de comprobantes electrónicos SUNAT (Factura y Boleta con RUC ' . $companyInfo['ruc'] . '), cumplimiento de la Ley N° 29733 y Libro de Reclamaciones INDECOPI.'],
            ] as $standard)
                <article class="relative flex gap-5 p-7 rounded-2xl bg-white border border-slate-200 hover:shadow-xl transition-shadow overflow-hidden">
                    <span class="absolute -right-3 -top-6 font-mono text-8xl font-black text-slate-100 select-none pointer-events-none">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <div class="relative flex flex-col gap-3">
                        <span class="w-fit px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-widest {{ $standard['tone'] }}">{{ $standard['tag'] }}</span>
                        <h3 class="text-lg font-black text-[#0F172A]">{{ $standard['title'] }}</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">{{ $standard['text'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Proceso de control de calidad --}}
    <section class="rounded-3xl bg-[#0F172A] text-white p-8 sm:p-12 overflow-hidden relative">
        <div class="absolute -left-20 -bottom-20 w-80 h-80 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
        <div class="relative flex flex-col gap-3 max-w-2xl">
            <span class="text-[11px] font-extrabold uppercase tracking-widest text-emerald-300">Control de calidad</span>
            <h2 class="text-2xl sm:text-3xl font-black">Cuatro filtros antes de que un producto llegue a tu obra</h2>
        </div>
        <ol class="relative mt-10 grid grid-cols-1 md:grid-cols-4 gap-6">
            @foreach([
                ['title' => 'Selección de fabricante', 'text' => 'Evaluamos procedencia, materiales y consistencia de producción.'],
                ['title' => 'Verificación técnica', 'text' => 'Contrastamos medidas, calibres y capacidad de carga declarados.'],
                ['title' => 'Almacenaje controlado', 'text' => 'Productos protegidos de humedad y golpes hasta su despacho.'],
                ['title' => 'Revisión de despacho', 'text' => 'Conteo y verificación de SKU contra la cotización aprobada.'],
            ] as $step)
                <li class="flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-full border-2 border-emerald-400 text-emerald-300 font-mono font-black flex items-center justify-center shrink-0">{{ $loop->iteration }}</span>
                        @unless($loop->last)
                            <span class="hidden md:block flex-1 h-px bg-gradient-to-r from-emerald-400/60 to-transparent"></span>
                        @endunless
                    </div>
                    <h3 class="text-base font-black">{{ $step['title'] }}</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">{{ $step['text'] }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- Formalidad --}}
    <section class="flex flex-col gap-8">
        @include('public.pages.company.partials.heading', [
            'eyebrow' => 'Respaldo formal',
            'title' => 'Una empresa 100% formal y transparente',
            'center' => true,
        ])
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach([
                ['title' => 'SUNAT', 'text' => 'Facturación electrónica con RUC ' . $companyInfo['ruc']],
                ['title' => 'Ley N° 29733', 'text' => 'Protección de datos personales'],
                ['title' => 'INDECOPI', 'text' => 'Libro de Reclamaciones virtual'],
                ['title' => 'Ficha técnica', 'text' => 'Documentación PDF por SKU'],
            ] as $badge)
                <div class="flex flex-col items-center text-center gap-2 p-6 rounded-2xl bg-white border-2 border-slate-200 hover:border-emerald-500 transition-colors">
                    <span class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $sections['certificaciones']['icon'] !!}</svg>
                    </span>
                    <span class="text-base font-black text-[#0F172A]">{{ $badge['title'] }}</span>
                    <span class="text-xs text-slate-500">{{ $badge['text'] }}</span>
                </div>
            @endforeach
        </div>
    </section>
@endsection
