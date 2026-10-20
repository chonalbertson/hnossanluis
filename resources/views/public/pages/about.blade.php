@extends('layouts.public')

@section('content')
    @php
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Nosotros', 'url' => route('pages.about')],
        ];
        $pillars = $page?->sections_json['pillars'] ?? [
            ['title' => 'Información Técnica Confiable', 'text' => 'Fichas con dimensiones, materiales y unidades de despacho detalladas por código SKU.'],
            ['title' => 'Respuesta Comercial Ordenada', 'text' => 'Cada solicitud recibe un código único de seguimiento (COT-YYYY-NNNNN).'],
            ['title' => 'Cobertura para Proyectos y Mayoristas', 'text' => 'Atención tanto para reposición de taller como para compras corporativas por volumen.'],
        ];
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-breadcrumb :items="$breadcrumbs" />

        <!-- Institutional Header -->
        <div class="bg-[#0F172A] text-white rounded-2xl p-6 sm:p-10 mb-10 border-b-4 border-[#DC2626] shadow-lg">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <span class="text-xs font-bold uppercase tracking-wider text-red-400">Presentación Institucional</span>
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800 border border-slate-700 text-xs font-mono text-amber-400 font-bold">
                    IMPORT HNOS SAN LUIS S.A.C. &bull; RUC: 20608628143
                </span>
            </div>
            <h1 class="text-2xl sm:text-4xl font-extrabold text-white mt-2">
                {{ $page?->title ?? 'Sobre IMPORT HNOS SAN LUIS S.A.C.' }}
            </h1>
            @if($page?->subtitle)
                <p class="text-sm sm:text-base text-slate-300 mt-3 max-w-3xl leading-relaxed">
                    {{ $page->subtitle }}
                </p>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-2xs cms-content">
                {!! $page?->content ?? '<p><strong>IMPORT HNOS SAN LUIS S.A.C.</strong> (RUC N° <strong>20608628143</strong>) es una organización comercial enfocada en el abastecimiento técnico de herrajes para muebles, accesorios de carpintería, fijaciones estructurales y artículos de ferretería para el sector construcción e industrial.</p>' !!}
            </div>

            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs space-y-4">
                    <h2 class="text-base font-extrabold text-[#0F172A] section-heading-bar">
                        Pilares de Servicio Comercial
                    </h2>
                    <div class="space-y-4 pt-1">
                        @foreach($pillars as $pillar)
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                                <h3 class="text-sm font-bold text-[#1E3A8A]">{{ $pillar['title'] ?? '' }}</h3>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $pillar['text'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-[#1E3A8A] text-white rounded-2xl p-6 space-y-4">
                    <h2 class="text-base font-extrabold">¿Requieres abastecimiento para tu empresa o proyecto?</h2>
                    <p class="text-xs text-blue-100 leading-relaxed">
                        Solicita una cotización formal desde nuestro catálogo técnico o comunícate con nuestro equipo comercial.
                    </p>
                    <div class="flex flex-col gap-2.5 pt-1">
                        <a href="{{ route('catalog.index') }}" class="w-full text-center py-2.5 px-4 rounded-lg bg-[#DC2626] hover:bg-[#B91C1C] text-white font-bold text-xs uppercase tracking-wider">
                            Ver Catálogo Técnico
                        </a>
                        <x-whatsapp-button
                            variant="inline"
                            :url="$whatsappUrl"
                            label="Contactar por WhatsApp"
                            ctaLocation="about_page"
                            contextType="general"
                            class="w-full py-2.5 text-xs" />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
