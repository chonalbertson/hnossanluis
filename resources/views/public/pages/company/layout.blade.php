@extends('layouts.public')

{{--
    Layout compartido de las páginas individuales del menú Empresa.
    Variables: $sections, $currentSection, $currentMeta, $previousSlug, $nextSlug, $companyInfo, $stats, $whatsappUrl.
--}}

@section('content')
    @php
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Empresa', 'url' => route('pages.company')],
        ];
        if ($currentMeta) {
            $breadcrumbs[] = ['name' => $currentMeta['label'], 'url' => route('pages.company', ['section' => $currentSection])];
        }
        $sectionNumber = $currentSection ? str_pad((string) (array_search($currentSection, array_keys($sections), true) + 1), 2, '0', STR_PAD_LEFT) : null;
    @endphp

    {{-- HERO --}}
    <section class="relative overflow-hidden bg-[#0F172A] text-white">
        <div class="absolute inset-0 bg-gradient-to-br from-[#0F172A] via-[#1E3A8A] to-[#0F172A]"></div>
        <div class="absolute inset-0 opacity-[0.07] pointer-events-none"
             style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 48px 48px;"></div>
        <div class="absolute -right-24 -top-24 w-96 h-96 rounded-full bg-[#DC2626]/20 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-32 bottom-0 w-96 h-96 rounded-full bg-blue-400/10 blur-3xl pointer-events-none"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-12 lg:pt-10 lg:pb-16">
            <x-breadcrumb :items="$breadcrumbs" />

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center mt-4">
                <div class="lg:col-span-8 flex flex-col gap-5">
                    <div class="inline-flex w-fit flex-wrap items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/15 text-[11px] font-extrabold uppercase tracking-widest">
                        <span class="w-2 h-2 rounded-full bg-[#DC2626] animate-pulse"></span>
                        @if($currentMeta)
                            <span class="text-amber-300 font-mono">{{ $sectionNumber }}</span>
                            <span class="text-white/40">/</span>
                            <span>{{ $currentMeta['eyebrow'] }}</span>
                        @else
                            <span>{{ $companyInfo['legal_name'] }}</span>
                            <span class="text-white/40">&bull;</span>
                            <span class="font-mono text-amber-300">RUC {{ $companyInfo['ruc'] }}</span>
                        @endif
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight leading-[1.1]">
                        @yield('company-title', $currentMeta['title'] ?? 'Empresa')
                    </h1>

                    <p class="text-sm sm:text-base lg:text-lg text-blue-100/90 max-w-3xl leading-relaxed">
                        @yield('company-lead', $currentMeta['summary'] ?? '')
                    </p>

                    <div class="flex flex-wrap items-center gap-3 pt-1">
                        <a href="{{ route('quote.index') }}"
                           class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-[#DC2626] hover:bg-[#B91C1C] text-white font-extrabold text-xs sm:text-sm uppercase tracking-wider shadow-lg shadow-red-900/30 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Solicitar Cotización B2B
                        </a>
                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer"
                           data-ga-event="whatsapp_click"
                           data-ga-params='{"cta_location":"company_hero","context_type":"general"}'
                           class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-xs sm:text-sm uppercase tracking-wider transition-colors">
                            <svg class="w-4 h-4 text-[#22C55E]" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            Hablar con un Asesor
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div class="relative rounded-3xl bg-white/[0.06] backdrop-blur-md border border-white/15 p-6 shadow-2xl">
                        <div class="flex items-center gap-4">
                            <span class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#DC2626] to-[#991B1B] text-white flex items-center justify-center shadow-lg shadow-red-900/40 shrink-0">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    {!! $currentMeta['icon'] ?? $sections['nosotros-experiencia']['icon'] !!}
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-[10px] font-extrabold uppercase tracking-widest text-blue-300">Razón Social</p>
                                <p class="text-sm font-black leading-tight">{{ $companyInfo['legal_name'] }}</p>
                                <p class="text-xs font-mono text-amber-300 mt-0.5">RUC {{ $companyInfo['ruc'] }}</p>
                            </div>
                        </div>

                        <dl class="grid grid-cols-3 gap-2 mt-6 pt-5 border-t border-white/10 text-center">
                            <div>
                                <dt class="sr-only">Productos</dt>
                                <dd class="text-xl font-black font-mono">+{{ number_format($stats['products']) }}</dd>
                                <p class="text-[10px] uppercase tracking-wider text-blue-200 font-bold">SKUs</p>
                            </div>
                            <div class="border-x border-white/10">
                                <dt class="sr-only">Líneas</dt>
                                <dd class="text-xl font-black font-mono text-amber-300">{{ $stats['categories'] }}</dd>
                                <p class="text-[10px] uppercase tracking-wider text-blue-200 font-bold">Líneas</p>
                            </div>
                            <div>
                                <dt class="sr-only">Marcas</dt>
                                <dd class="text-xl font-black font-mono text-emerald-300">{{ $stats['brands'] }}</dd>
                                <p class="text-[10px] uppercase tracking-wider text-blue-200 font-bold">Marcas</p>
                            </div>
                        </dl>

                        <p class="mt-5 flex items-start gap-2 text-xs text-blue-100/80">
                            <svg class="w-4 h-4 text-[#DC2626] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $companyInfo['address'] }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="relative h-1 bg-gradient-to-r from-[#DC2626] via-amber-400 to-[#DC2626]"></div>
    </section>

    {{-- SUB-NAVEGACIÓN EMPRESA --}}
    <nav aria-label="Páginas de Empresa" class="bg-white border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <ul class="flex gap-1 overflow-x-auto py-2 -mx-1 [scrollbar-width:none]">
                <li class="shrink-0">
                    <a href="{{ route('pages.company') }}"
                       class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-bold whitespace-nowrap transition-colors {{ $currentSection === null ? 'bg-[#0F172A] text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-[#1E3A8A]' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Resumen
                    </a>
                </li>
                @foreach($sections as $slug => $meta)
                    <li class="shrink-0">
                        <a href="{{ route('pages.company', ['section' => $slug]) }}"
                           @if($currentSection === $slug) aria-current="page" @endif
                           class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-bold whitespace-nowrap transition-colors {{ $currentSection === $slug ? 'bg-[#1E3A8A] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-[#1E3A8A]' }}">
                            <svg class="w-4 h-4 {{ $currentSection === $slug ? 'text-amber-300' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $meta['icon'] !!}</svg>
                            {{ $meta['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </nav>

    {{-- CONTENIDO DE LA PÁGINA --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16 flex flex-col gap-16">
        @yield('company-content')
    </div>

    {{-- NAVEGACIÓN ANTERIOR / SIGUIENTE --}}
    @if($currentSection)
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @if($previousSlug)
                    <a href="{{ route('pages.company', ['section' => $previousSlug]) }}"
                       class="group flex items-center gap-4 p-5 rounded-2xl bg-white border border-slate-200 hover:border-[#1E3A8A] hover:shadow-lg transition-all">
                        <span class="w-10 h-10 rounded-full bg-slate-100 group-hover:bg-[#1E3A8A] group-hover:text-white text-slate-500 flex items-center justify-center shrink-0 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Anterior</span>
                            <span class="block text-sm font-black text-[#0F172A] group-hover:text-[#1E3A8A] truncate">{{ $sections[$previousSlug]['label'] }}</span>
                        </span>
                    </a>
                @else
                    <span class="hidden sm:block"></span>
                @endif

                @if($nextSlug)
                    <a href="{{ route('pages.company', ['section' => $nextSlug]) }}"
                       class="group flex items-center justify-end gap-4 p-5 rounded-2xl bg-white border border-slate-200 hover:border-[#1E3A8A] hover:shadow-lg transition-all text-right">
                        <span class="min-w-0">
                            <span class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Siguiente</span>
                            <span class="block text-sm font-black text-[#0F172A] group-hover:text-[#1E3A8A] truncate">{{ $sections[$nextSlug]['label'] }}</span>
                        </span>
                        <span class="w-10 h-10 rounded-full bg-slate-100 group-hover:bg-[#1E3A8A] group-hover:text-white text-slate-500 flex items-center justify-center shrink-0 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </a>
                @endif
            </div>
        </div>
    @endif

    {{-- CTA CORPORATIVO --}}
    <section class="bg-white border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#1E3A8A] to-[#0F172A] px-6 py-10 sm:px-12 shadow-xl">
                <div class="absolute right-0 top-0 w-72 h-72 rounded-full bg-[#DC2626]/25 blur-3xl pointer-events-none"></div>
                <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="max-w-2xl">
                        <p class="text-[11px] font-extrabold uppercase tracking-widest text-amber-300">Atención Corporativa B2B</p>
                        <h2 class="mt-2 text-2xl sm:text-3xl font-black text-white leading-tight">¿Tienes una lista de materiales o un proyecto en curso?</h2>
                        <p class="mt-2 text-sm text-blue-100">Envíanos tu requerimiento y recibe una cotización formal con precios por volumen, stock confirmado y factura electrónica con RUC.</p>
                    </div>
                    <div class="flex flex-wrap gap-3 shrink-0">
                        <a href="{{ route('quote.index') }}"
                           class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-[#DC2626] hover:bg-[#B91C1C] text-white font-extrabold text-sm uppercase tracking-wider shadow-lg transition-colors">
                            Enviar Lista a Cotizar
                        </a>
                        <a href="{{ route('contact.index') }}"
                           class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-white text-[#0F172A] hover:bg-slate-100 font-extrabold text-sm uppercase tracking-wider transition-colors">
                            Contacto Comercial
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
