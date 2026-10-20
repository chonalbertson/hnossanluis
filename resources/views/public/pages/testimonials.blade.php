@extends('layouts.public')

@section('content')
    @php
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Clientes y Testimonios', 'url' => route('pages.testimonials')],
        ];
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-breadcrumb :items="$breadcrumbs" />

        <div class="bg-[#0F172A] text-white rounded-2xl p-6 sm:p-10 mb-10 border-b-4 border-[#DC2626] shadow-lg">
            <span class="text-xs font-bold uppercase tracking-wider text-red-400">Alcance Comercial B2B y B2C</span>
            <h1 class="text-2xl sm:text-4xl font-extrabold text-white mt-1">
                Sectores Atendidos y Referencias Comerciales
            </h1>
            <p class="text-sm sm:text-base text-slate-300 mt-3 max-w-3xl leading-relaxed">
                Abastecemos con precisión técnica a talleres de fabricación de mobiliario en melamina, contratistas de obra, carpintería metálica/aluminio y distribuidores ferreteros.
            </p>
        </div>

        <!-- Sectors Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs">
                <h2 class="text-base font-extrabold text-[#1E3A8A] mb-2">Fabricantes de Muebles y Melamina</h2>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Suministro de bisagras cazoleta, correderas telescópicas y ocultas, perfiles Gola, pistones a gas y tornillería especializada por caja o millar.
                </p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs">
                <h2 class="text-base font-extrabold text-[#1E3A8A] mb-2">Constructoras y Contratistas</h2>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Anclajes mecánicos para concreto, cerraduras arquitectónicas, cierrapuertas hidráulicos y discos de corte con ficha técnica para expedientes de obra.
                </p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs">
                <h2 class="text-base font-extrabold text-[#1E3A8A] mb-2">Ferreterías y Distribuidores</h2>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Atención por volumen con RUC, despacho coordinado en Lima Metropolitana y envío por agencias de carga a nivel nacional.
                </p>
            </div>
        </div>

        <!-- Verified Testimonials Section -->
        <section aria-labelledby="heading-verified-testimonials" class="mb-12">
            <h2 id="heading-verified-testimonials" class="text-xl sm:text-2xl font-extrabold text-[#0F172A] section-heading-bar mb-6">
                Testimonios Verificados de Clientes
            </h2>

            @if($testimonials->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($testimonials as $testimonial)
                        <blockquote class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-1 text-amber-500 mb-3" aria-label="Calificación {{ $testimonial->rating }} de 5">
                                    @for($i = 1; $i <= max(1, min(5, (int) $testimonial->rating)); $i++)
                                        <span>&#9733;</span>
                                    @endfor
                                </div>
                                <p class="text-sm text-slate-700 italic leading-relaxed mb-4">
                                    "{{ $testimonial->content }}"
                                </p>
                            </div>
                            <footer class="border-t border-slate-100 pt-3">
                                <div class="font-bold text-sm text-[#0F172A]">{{ $testimonial->author_name }}</div>
                                <div class="text-xs text-slate-600">
                                    {{ $testimonial->author_role }}
                                    @if($testimonial->company_name)
                                        — <strong class="text-[#1E3A8A]">{{ $testimonial->company_name }}</strong>
                                    @endif
                                </div>
                            </footer>
                        </blockquote>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-xl border border-slate-200 p-8 text-center space-y-3">
                    <p class="text-sm font-semibold text-slate-800">
                        Política de Transparencia Comercial
                    </p>
                    <p class="text-xs sm:text-sm text-slate-600 max-w-2xl mx-auto leading-relaxed">
                        En <strong>Import Hns San Luis</strong> únicamente publicamos testimonios que cuentan con autorización expresa y verificación comercial de nuestros clientes. Si deseas compartir tu experiencia con nuestro servicio técnico o solicitar referencias comerciales para licitaciones, contáctanos directamente.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('contact.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-[#1E3A8A] text-white text-xs font-bold uppercase tracking-wider">
                            Contactar con el Área Comercial
                        </a>
                    </div>
                </div>
            @endif
        </section>
    </div>
@endsection
