@extends('public.pages.company.layout')

@section('company-content')
    {{-- Quiénes somos + ficha corporativa --}}
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
        <div class="lg:col-span-7 flex flex-col gap-6">
            @include('public.pages.company.partials.heading', [
                'eyebrow' => 'Quiénes somos',
                'title' => 'Una empresa peruana al servicio de la industria del mueble y la construcción',
            ])
            <div class="cms-content text-sm sm:text-base text-slate-700 leading-relaxed space-y-4">
                {!! $page?->content ?? '<p><strong>IMPORT HNOS SAN LUIS S.A.C.</strong> (RUC N° <strong>20608628143</strong>) es una empresa peruana especializada en la importación, distribución mayorista y comercialización técnica de herrajes para muebles, sistemas corredizos, cerraduras, fijaciones estructurales, pegamentos, herramientas e insumos para la industria del mueble y la construcción.</p><p>Atendemos a fabricantes de mobiliario, carpinterías, constructoras, contratistas y distribuidores ferreteros con stock permanente, asesoría técnica directa y cotizaciones formales.</p>' !!}
            </div>
        </div>

        <aside class="lg:col-span-5">
            <div class="rounded-3xl bg-white border border-slate-200 shadow-xl overflow-hidden">
                <div class="px-6 py-4 bg-[#0F172A] text-white flex items-center justify-between">
                    <span class="text-xs font-extrabold uppercase tracking-widest">Ficha Corporativa</span>
                    <span class="px-2 py-0.5 rounded bg-[#DC2626] text-[10px] font-extrabold uppercase">Empresa Formal</span>
                </div>
                <dl class="divide-y divide-slate-100">
                    @foreach([
                        'Razón social' => $companyInfo['legal_name'],
                        'Nombre comercial' => $companyInfo['commercial_name'],
                        'RUC' => $companyInfo['ruc'],
                        'Sede principal' => $companyInfo['address'],
                        'Horario' => $companyInfo['hours'],
                        'Correo comercial' => $companyInfo['email'],
                        'Central telefónica' => $companyInfo['phone'],
                    ] as $label => $value)
                        <div class="px-6 py-3.5 grid grid-cols-3 gap-3">
                            <dt class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">{{ $label }}</dt>
                            <dd class="col-span-2 text-sm font-bold text-[#0F172A] {{ $label === 'RUC' ? 'font-mono' : '' }}">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </aside>
    </section>

    {{-- Misión / Visión / Valores --}}
    <section class="flex flex-col gap-8">
        @include('public.pages.company.partials.heading', [
            'eyebrow' => 'Nuestra filosofía',
            'title' => 'Misión, visión y valores',
            'center' => true,
        ])
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach([
                ['title' => 'Misión', 'color' => 'bg-[#1E3A8A]', 'text' => 'Proveer a carpinterías, fabricantes de mobiliario, constructoras y distribuidores ferreteros soluciones técnicas confiables, con disponibilidad real y precios competitivos de importador directo.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>'],
                ['title' => 'Visión', 'color' => 'bg-[#DC2626]', 'text' => 'Consolidarnos como el aliado B2B referente a nivel nacional en herrajes para muebles y ferretería técnica, destacando por la precisión de nuestro catálogo digital y rapidez logística.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>'],
                ['title' => 'Valores', 'color' => 'bg-emerald-600', 'text' => 'Transparencia en especificaciones técnicas (SKU, calibres y medidas), puntualidad en los despachos, formalidad comercial y asesoría directa personalizada.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>'],
            ] as $pillar)
                <article class="group relative p-7 rounded-2xl bg-white border border-slate-200 hover:shadow-xl transition-shadow">
                    <span class="w-12 h-12 rounded-xl {{ $pillar['color'] }} text-white flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $pillar['icon'] !!}</svg>
                    </span>
                    <h3 class="mt-5 text-lg font-black text-[#0F172A]">{{ $pillar['title'] }}</h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $pillar['text'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Modelo de trabajo --}}
    <section class="rounded-3xl bg-slate-900 text-white p-8 sm:p-12">
        <div class="flex flex-col gap-3 max-w-2xl">
            <span class="text-[11px] font-extrabold uppercase tracking-widest text-amber-300">Cómo trabajamos</span>
            <h2 class="text-2xl sm:text-3xl font-black">Un proceso comercial claro, de la cotización al despacho</h2>
        </div>
        <ol class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach([
                ['title' => 'Solicitud', 'text' => 'Envías tu lista de materiales desde el catálogo, WhatsApp o correo.'],
                ['title' => 'Cotización formal', 'text' => 'Validamos stock y emitimos propuesta PDF con precios por volumen.'],
                ['title' => 'Confirmación', 'text' => 'Aprobada la propuesta, emitimos factura electrónica con tu RUC.'],
                ['title' => 'Despacho', 'text' => 'Retiro en tienda, entrega en Lima o envío por agencia a provincias.'],
            ] as $step)
                <li class="relative flex flex-col gap-2 p-5 rounded-2xl bg-white/5 border border-white/10">
                    <span class="w-10 h-10 rounded-full bg-[#DC2626] font-mono font-black flex items-center justify-center">{{ $loop->iteration }}</span>
                    <h3 class="mt-2 text-base font-black">{{ $step['title'] }}</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">{{ $step['text'] }}</p>
                </li>
            @endforeach
        </ol>
    </section>
@endsection
