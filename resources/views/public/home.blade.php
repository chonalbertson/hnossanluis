@extends('layouts.public')

@section('content')
    @php
        $slideAccents = [
            'red' => ['glow' => 'bg-[#DC2626]', 'glow2' => 'bg-blue-600', 'gradient' => 'from-amber-300 via-white to-red-200', 'chip' => 'text-red-300', 'ring' => 'border-red-400/30', 'from' => 'from-[#0B1220] via-[#111C3A] to-[#2A0F14]'],
            'blue' => ['glow' => 'bg-blue-500', 'glow2' => 'bg-sky-400', 'gradient' => 'from-sky-300 via-white to-blue-200', 'chip' => 'text-sky-300', 'ring' => 'border-sky-400/30', 'from' => 'from-[#0B1220] via-[#0F2357] to-[#0B1220]'],
            'amber' => ['glow' => 'bg-amber-500', 'glow2' => 'bg-[#DC2626]', 'gradient' => 'from-amber-300 via-yellow-100 to-amber-200', 'chip' => 'text-amber-300', 'ring' => 'border-amber-400/30', 'from' => 'from-[#0B1220] via-[#1F1A10] to-[#0B1220]'],
            'emerald' => ['glow' => 'bg-emerald-500', 'glow2' => 'bg-teal-400', 'gradient' => 'from-emerald-300 via-white to-teal-200', 'chip' => 'text-emerald-300', 'ring' => 'border-emerald-400/30', 'from' => 'from-[#0B1220] via-[#0B2A27] to-[#0B1220]'],
        ];
        $slides = $heroSlides ?? [];
    @endphp

    {{-- 1. HERO SLIDER CON EFECTOS DINÁMICOS --}}
    <section data-hero-slider
             class="hero-slider relative bg-[#0B1220] text-white overflow-hidden"
             style="--hero-duration: 7000ms"
             aria-roledescription="carrusel"
             aria-label="Líneas destacadas de Import Hns San Luis">
        <div class="grid">
            @foreach($slides as $slide)
                @php
                    $accent = $slideAccents[$slide['accent']] ?? $slideAccents['red'];
                    $slideProducts = ($slide['products'] ?? collect())->values();
                    $mainProduct = $slideProducts->get(0);
                    $bgUrl = $slide['background_image_url'] ?? null;
                    $mobileBgUrl = $slide['mobile_background_image_url'] ?? null;
                    $overlayOpacity = isset($slide['overlay_opacity']) ? max(0, min(95, (int) $slide['overlay_opacity'])) : 75;
                    $overlayAlpha = number_format($overlayOpacity / 100, 2, '.', '');
                    $showShowcase = ($slide['show_products_showcase'] ?? true) && $slideProducts->isNotEmpty();
                @endphp
                <article data-slide
                         class="hero-slide relative [grid-area:1/1] {{ $loop->first ? 'is-active' : '' }}"
                         aria-roledescription="diapositiva"
                         aria-label="{{ $loop->iteration }} de {{ $loop->count }}: {{ $slide['label'] }}"
                         @unless($loop->first) aria-hidden="true" inert @endunless>
                    {{-- Fondo con Imagen Personalizada o Gradiente Técnico, Efecto Ken Burns y Aura Pulsante --}}
                    <div class="slide-bg absolute inset-0 bg-gradient-to-br {{ $accent['from'] }}" aria-hidden="true">
                        @if($bgUrl)
                            <picture class="absolute inset-0 w-full h-full">
                                @if($mobileBgUrl)
                                    <source media="(max-width: 767px)" srcset="{{ $mobileBgUrl }}">
                                @endif
                                <img src="{{ $bgUrl }}"
                                     alt=""
                                     @if($loop->first) fetchpriority="high" @else loading="lazy" @endif
                                     decoding="async"
                                     class="w-full h-full object-cover object-center">
                            </picture>
                            <div class="absolute inset-0 bg-[#0B1220]" style="opacity: {{ $overlayAlpha }}"></div>
                            <div class="absolute inset-0 bg-gradient-to-r from-[#0B1220]/90 via-[#0B1220]/65 to-transparent"></div>
                        @endif
                        <div class="banner-aura-glow absolute -top-32 -right-24 w-[36rem] h-[36rem] rounded-full {{ $accent['glow'] }} opacity-25 blur-[120px]"></div>
                        <div class="banner-aura-glow absolute -bottom-40 -left-24 w-[32rem] h-[32rem] rounded-full {{ $accent['glow2'] }} opacity-20 blur-[120px] [animation-delay:-2.5s]"></div>
                        <div class="hero-grid-pattern absolute inset-0 {{ $bgUrl ? 'opacity-35' : '' }}"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0B1220]/85 via-transparent to-[#0B1220]/30"></div>
                    </div>

                    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-32 sm:pt-14 lg:pt-16 lg:pb-36 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center lg:min-h-[630px]">
                        {{-- Texto y Call to Actions con Efecto Shimmer --}}
                        <div class="{{ $showShowcase ? 'lg:col-span-6' : 'lg:col-span-8' }} flex flex-col gap-5 sm:gap-6">
                            @if(!empty($slide['eyebrow']) || !empty($slide['badge_metric']))
                                <div class="slide-anim flex flex-wrap items-center gap-2" style="--d: 100ms">
                                    @if(!empty($slide['eyebrow']))
                                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/15 backdrop-blur-md text-[11px] font-extrabold uppercase tracking-widest {{ $accent['chip'] }}">
                                            <span class="w-2 h-2 rounded-full bg-current animate-ping"></span>
                                            {{ $slide['eyebrow'] }}
                                        </span>
                                    @endif
                                    @if(!empty($slide['badge_metric']))
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-400/15 border border-amber-300/30 text-amber-300 font-mono text-[11px] font-extrabold">
                                            {{ $slide['badge_metric'] }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            @if($loop->first)
                                <h1 class="slide-anim text-3xl sm:text-5xl lg:text-[3.45rem] font-black tracking-tight leading-[1.05]" style="--d: 200ms">
                                    {{ $slide['title'] }}
                                    @if(!empty($slide['highlight']))
                                        <span class="text-transparent bg-clip-text bg-gradient-to-r {{ $accent['gradient'] }}">{{ $slide['highlight'] }}</span>
                                    @endif
                                    {{ $slide['suffix'] ?? '' }}
                                </h1>
                            @else
                                <h2 class="slide-anim text-3xl sm:text-5xl lg:text-[3.45rem] font-black tracking-tight leading-[1.05]" style="--d: 200ms">
                                    {{ $slide['title'] }}
                                    @if(!empty($slide['highlight']))
                                        <span class="text-transparent bg-clip-text bg-gradient-to-r {{ $accent['gradient'] }}">{{ $slide['highlight'] }}</span>
                                    @endif
                                    {{ $slide['suffix'] ?? '' }}
                                </h2>
                            @endif

                            @if(!empty($slide['text']))
                                <p class="slide-anim text-sm sm:text-base lg:text-lg text-slate-300 leading-relaxed max-w-xl" style="--d: 320ms">
                                    {{ $slide['text'] }}
                                </p>
                            @endif

                            <div class="slide-anim flex flex-wrap items-center gap-3 pt-1" style="--d: 440ms">
                                @if(!empty($slide['primary']['label']))
                                    <a href="{{ $slide['primary']['url'] }}"
                                       class="banner-shine-effect group inline-flex items-center gap-2.5 px-6 py-3.5 rounded-xl bg-[#DC2626] hover:bg-[#B91C1C] text-white font-extrabold text-xs sm:text-sm uppercase tracking-wider shadow-xl shadow-red-950/50 hover:-translate-y-0.5 transition-all">
                                        <span>{{ $slide['primary']['label'] }}</span>
                                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>
                                @endif
                                @if(!empty($slide['secondary']['label']))
                                    <a href="{{ $slide['secondary']['url'] }}"
                                       class="inline-flex items-center gap-2 px-5 py-3.5 rounded-xl bg-white/10 hover:bg-white hover:text-[#0F172A] border border-white/20 text-white font-extrabold text-xs sm:text-sm uppercase tracking-wider backdrop-blur-md hover:-translate-y-0.5 transition-all">
                                        {{ $slide['secondary']['label'] }}
                                    </a>
                                @endif
                                <x-whatsapp-button
                                    variant="inline"
                                    :url="$whatsappUrl"
                                    label=""
                                    ctaLocation="hero_slider"
                                    contextType="general"
                                    class="w-[50px] h-[50px] rounded-xl"
                                    aria-label="Cotizar por WhatsApp"
                                    title="Cotizar por WhatsApp" />
                            </div>

                            <ul class="slide-anim flex flex-wrap gap-x-5 gap-y-2 pt-2 text-xs font-semibold text-slate-300" style="--d: 560ms">
                                @foreach(['Atención Corporativa RUC y Taller', 'Ficha Técnica PDF Descargable', 'Despacho a Lima y Provincias'] as $perk)
                                    <li class="inline-flex items-center gap-1.5">
                                        <span class="w-4 h-4 rounded-full bg-emerald-500/20 border border-emerald-400/40 flex items-center justify-center">
                                            <svg class="w-2.5 h-2.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                        {{ $perk }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Composición visual con anillos orbitales dobles y pedestales flotantes --}}
                        @if($showShowcase)
                            <div class="lg:col-span-6">
                                <div class="slide-visual relative mx-auto h-[260px] sm:h-[360px] lg:h-[460px] max-w-[540px]">
                                    <div class="absolute inset-0 m-auto w-[235px] h-[235px] sm:w-[340px] sm:h-[340px] lg:w-[430px] lg:h-[430px] rounded-full border-2 border-dashed {{ $accent['ring'] }} hero-spin" aria-hidden="true"></div>
                                    <div class="absolute inset-0 m-auto w-[195px] h-[195px] sm:w-[285px] sm:h-[285px] lg:w-[360px] lg:h-[360px] rounded-full border border-white/15 hero-spin-reverse" aria-hidden="true"></div>
                                    <div class="banner-aura-glow absolute inset-0 m-auto w-[170px] h-[170px] sm:w-[250px] sm:h-[250px] lg:w-[320px] lg:h-[320px] rounded-full {{ $accent['glow'] }} opacity-25 blur-3xl" aria-hidden="true"></div>

                                    @if($mainProduct)
                                        <a href="{{ route('catalog.show', $mainProduct->slug) }}"
                                           class="banner-shine-effect group absolute inset-0 m-auto w-[170px] h-[170px] sm:w-[240px] sm:h-[240px] lg:w-[300px] lg:h-[300px] rounded-[2rem] bg-white shadow-[0_40px_80px_-20px_rgba(0,0,0,0.65)] ring-4 ring-white/20 p-5 sm:p-7 flex items-center justify-center">
                                            <img src="{{ $mainProduct->primaryThumbnailUrl() }}"
                                                 alt="{{ $mainProduct->name }}"
                                                 width="300" height="300"
                                                 @if($loop->first) fetchpriority="high" @else loading="lazy" @endif
                                                 decoding="async"
                                                 class="max-w-full max-h-full object-contain group-hover:scale-110 transition-transform duration-500">
                                            <span class="absolute top-3 left-3 px-2 py-0.5 rounded-md bg-[#0F172A]/90 text-amber-300 font-mono text-[10px] font-extrabold">
                                                SKU: {{ $mainProduct->sku }}
                                            </span>
                                            <span class="absolute -bottom-3 left-1/2 -translate-x-1/2 max-w-[90%] px-3.5 py-1.5 rounded-full bg-[#0F172A] border border-white/15 text-white text-[10px] sm:text-xs font-bold shadow-lg truncate">
                                                {{ \Illuminate\Support\Str::limit($mainProduct->name, 34) }}
                                            </span>
                                        </a>
                                    @else
                                        <div class="absolute inset-0 m-auto w-[170px] h-[170px] sm:w-[240px] sm:h-[240px] lg:w-[300px] lg:h-[300px] rounded-[2rem] bg-white shadow-2xl p-8 flex items-center justify-center">
                                            <img src="{{ asset('images/logo-sanluis.webp') }}" alt="Import Hns San Luis" width="300" height="184" decoding="async" class="max-w-full max-h-full object-contain">
                                        </div>
                                    @endif

                                    @foreach($slideProducts->slice(1, 2) as $satellite)
                                        <a href="{{ route('catalog.show', $satellite->slug) }}"
                                           class="hero-float group absolute hidden sm:flex {{ $loop->first ? 'top-2 left-0 lg:top-6 lg:left-2' : 'bottom-6 right-0 lg:bottom-10 lg:right-0 [animation-delay:-3s]' }} w-28 h-28 lg:w-36 lg:h-36 rounded-2xl bg-white/95 backdrop-blur shadow-2xl p-3 items-center justify-center ring-2 ring-white/40 hover:ring-amber-400 transition-all"
                                           title="{{ $satellite->name }}">
                                            <img src="{{ $satellite->primaryThumbnailUrl() }}"
                                                 alt="{{ $satellite->name }}"
                                                 width="144" height="144"
                                                 loading="lazy"
                                                 decoding="async"
                                                 class="max-w-full max-h-full object-contain group-hover:scale-110 transition-transform duration-500">
                                        </a>
                                    @endforeach

                                    <div class="hero-float absolute hidden lg:flex top-10 right-4 [animation-delay:-1.5s] items-center gap-2 px-3.5 py-2 rounded-xl bg-[#0F172A]/85 border border-white/20 backdrop-blur-md text-xs font-bold shadow-xl">
                                        <span class="w-7 h-7 rounded-lg bg-emerald-500 flex items-center justify-center text-white shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                        <div>
                                            <span class="block text-[10px] uppercase tracking-wider text-emerald-300 font-extrabold">Stock Real</span>
                                            <span class="block text-xs text-white font-black">10 Líneas Técnicas</span>
                                        </div>
                                    </div>
                                    <div class="hero-float absolute hidden lg:flex bottom-4 left-6 [animation-delay:-4.5s] items-center gap-2 px-3.5 py-2 rounded-xl bg-[#0F172A]/85 border border-white/20 backdrop-blur-md text-xs font-bold shadow-xl">
                                        <span class="w-7 h-7 rounded-lg bg-[#DC2626] flex items-center justify-center text-white shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </span>
                                        <div>
                                            <span class="block text-[10px] uppercase tracking-wider text-amber-300 font-extrabold">Catálogo B2B</span>
                                            <span class="block text-xs text-white font-black">Ficha Técnica PDF</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Controles del slider --}}
        @if(count($slides) > 1)
            <div class="absolute inset-x-0 bottom-0 z-20">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-6 sm:pb-8 flex items-end justify-between gap-6">
                    <div class="flex-1 grid gap-2 sm:gap-4 max-w-3xl" style="grid-template-columns: repeat({{ count($slides) }}, minmax(0, 1fr));" role="tablist" aria-label="Seleccionar diapositiva">
                        @foreach($slides as $slide)
                            <button type="button"
                                    data-slider-dot
                                    role="tab"
                                    aria-label="Ir a {{ $slide['label'] }}"
                                    @if($loop->first) aria-current="true" @endif
                                    class="group text-left cursor-pointer {{ $loop->first ? 'is-active' : '' }}">
                                <span class="block h-1.5 rounded-full bg-white/20 overflow-hidden">
                                    <span data-slider-progress class="hero-progress-fill block h-full w-full bg-gradient-to-r from-[#DC2626] to-amber-400 rounded-full"></span>
                                </span>
                                <span class="hidden sm:flex items-center gap-2 mt-2.5 text-[11px] font-extrabold uppercase tracking-wider text-white/50 group-hover:text-white group-[.is-active]:text-white transition-colors">
                                    <span class="font-mono text-amber-300/70 group-[.is-active]:text-amber-300">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="truncate">{{ $slide['label'] }}</span>
                                </span>
                            </button>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <span class="hidden md:block font-mono text-sm text-white/60" aria-live="polite">
                            <span data-slider-current class="text-white font-black text-lg">01</span> / {{ str_pad((string) count($slides), 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <button type="button" data-slider-prev aria-label="Diapositiva anterior"
                                class="w-11 h-11 rounded-full border border-white/25 bg-white/5 hover:bg-white hover:text-[#0F172A] backdrop-blur flex items-center justify-center transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button type="button" data-slider-next aria-label="Diapositiva siguiente"
                                class="w-11 h-11 rounded-full bg-[#DC2626] hover:bg-[#B91C1C] flex items-center justify-center shadow-lg shadow-red-950/40 transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        @endif
        <div class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-[#DC2626] via-amber-400 to-[#DC2626] z-30"></div>
    </section>

    {{-- 2. BENEFICIOS --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4" aria-label="Beneficios comerciales">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
            @foreach([
                ['title' => 'Venta Mayorista y Unidad', 'text' => 'Empresas (RUC) y talleres', 'tone' => 'bg-blue-50 text-[#1E3A8A]', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>'],
                ['title' => 'Cotización Formal PDF', 'text' => 'Precios según volumen', 'tone' => 'bg-red-50 text-[#DC2626]', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
                ['title' => 'Especificaciones Reales', 'text' => 'Medidas, materiales y fichas', 'tone' => 'bg-emerald-50 text-emerald-700', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>'],
                ['title' => 'Despacho Nacional', 'text' => 'Lima y provincias', 'tone' => 'bg-amber-50 text-amber-700', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>'],
            ] as $benefit)
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 rounded-2xl bg-white border border-slate-200 hover:border-[#1E3A8A]/40 hover:shadow-md transition-all">
                    <span class="w-11 h-11 rounded-xl {{ $benefit['tone'] }} flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $benefit['icon'] !!}</svg>
                    </span>
                    <div>
                        <h2 class="text-xs sm:text-sm font-extrabold text-[#0F172A]">{{ $benefit['title'] }}</h2>
                        <p class="text-[11px] sm:text-xs text-slate-500">{{ $benefit['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- 4. CATEGORÍAS CON ICONOS PROFESIONALES --}}
    @php
        $resolveCategorySector = function (\App\Models\Category $cat): string {
            return $cat->iconTheme()['sector'];
        };

        $sortedByVolume = $categories->sortByDesc('products_count')->values();
        $flagshipIds = $sortedByVolume->take(3)->pluck('id')->all();
        $primaryVisibleIds = $sortedByVolume->take(6)->pluck('id')->all();

        $flagshipCategories = $categories->whereIn('id', $flagshipIds)->sortByDesc('products_count')->values();
        $regularCategories = $categories->whereNotIn('id', $flagshipIds)->values();

        $sectorTabs = [
            ['key' => 'principales', 'label' => 'Principales', 'count' => min(6, $categories->count())],
            ['key' => 'all', 'label' => 'Ver más', 'count' => $categories->count()],
            ['key' => 'muebleria', 'label' => 'Mueblería y Melamina', 'count' => $categories->filter(fn ($c) => $resolveCategorySector($c) === 'muebleria')->count()],
            ['key' => 'cerrajeria', 'label' => 'Cerrajería, Fijación y Químicos', 'count' => $categories->filter(fn ($c) => $resolveCategorySector($c) === 'cerrajeria')->count()],
            ['key' => 'iluminacion', 'label' => 'Iluminación LED y Tomas', 'count' => $categories->filter(fn ($c) => $resolveCategorySector($c) === 'iluminacion')->count()],
            ['key' => 'herramientas', 'label' => 'Herramientas y Plantillas', 'count' => $categories->filter(fn ($c) => $resolveCategorySector($c) === 'herramientas')->count()],
        ];
    @endphp

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16" aria-labelledby="heading-categories" data-home-categories-section>
        {{-- Cabecera ejecutiva + Filtro por Rubro --}}
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5 mb-7">
            <div>
                <span class="inline-flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-widest text-[#DC2626] mb-2">
                    <span class="w-6 h-0.5 bg-[#DC2626]"></span> {{ $categories->count() }} Líneas Principales • {{ $catalogStats['subcategories'] ?? 35 }} Subcategorías
                </span>
                <h2 id="heading-categories" class="text-2xl sm:text-4xl font-black text-[#0F172A] tracking-tight">Explora por Categoría</h2>
                <p class="mt-1.5 text-xs sm:text-sm text-slate-600 max-w-2xl">
                    Catálogo organizado por especialidad técnica con iconografía industrial. Selecciona una categoría o subcategoría para filtrar al instante.
                </p>
            </div>

            <div class="flex items-center gap-3 self-start lg:self-end">
                <a href="{{ route('catalog.index') }}"
                   class="group inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 hover:border-[#1E3A8A] text-xs sm:text-sm font-extrabold text-[#1E3A8A] hover:text-[#DC2626] shadow-2xs transition-all">
                    <span>Ver las {{ $categories->count() }} categorías</span>
                    <span aria-hidden="true" class="w-5 h-5 rounded-full bg-blue-50 group-hover:bg-red-50 flex items-center justify-center group-hover:translate-x-0.5 transition-all">&rarr;</span>
                </a>
            </div>
        </div>

        {{-- Barra de filtros interactivos por sector --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-7 [scrollbar-width:none]" role="tablist" aria-label="Filtrar categorías por especialidad">
            @foreach($sectorTabs as $tab)
                <button type="button"
                        role="tab"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                        data-cat-sector-btn="{{ $tab['key'] }}"
                        class="shrink-0 inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-extrabold transition-all cursor-pointer border {{ $loop->first ? 'bg-[#0F172A] text-white border-[#0F172A] shadow-sm' : 'bg-white text-slate-700 border-slate-200 hover:border-[#1E3A8A] hover:text-[#1E3A8A]' }}">
                    <span>{{ $tab['label'] }}</span>
                    <span data-cat-sector-badge
                          class="px-1.5 py-0.5 rounded-md font-mono text-[10px] font-extrabold tabular-nums {{ $loop->first ? 'bg-[#DC2626] text-white' : 'bg-slate-100 text-slate-600' }}">
                        {{ $tab['count'] }}
                    </span>
                </button>
            @endforeach
        </div>

        {{-- Nivel 1: 3 Líneas Principales de Alto Volumen con Pedestal de Icono Profesional --}}
        @if($flagshipCategories->isNotEmpty())
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-5 mb-5" data-cat-flagship-grid>
                @foreach($flagshipCategories as $category)
                    @php
                        $theme = $category->iconTheme();
                        $sectorKey = $theme['sector'];
                    @endphp
                    <article data-cat-card
                             data-cat-sector="{{ $sectorKey }}"
                             data-cat-primary="true"
                             class="group relative bg-white rounded-2xl border border-slate-200/90 hover:border-[#1E3A8A] shadow-xs hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col justify-between">
                        <div class="h-1.5 w-full bg-gradient-to-r {{ $theme['bar'] }}"></div>

                        {{-- Sutil marca de agua vectorial en el fondo --}}
                        <div class="absolute -right-8 -top-6 w-36 h-36 rounded-full {{ $theme['glow'] }} blur-2xl pointer-events-none"></div>

                        <div class="relative p-5 sm:p-6 flex-1 flex flex-col justify-between gap-4">
                            {{-- Fila Superior: Código Técnico + Contador de SKUs --}}
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full border {{ $theme['badge'] }}">
                                    {{ $theme['code'] }} • Línea Principal
                                </span>
                                <span class="font-mono text-xs font-black bg-[#0F172A] text-white px-2.5 py-1 rounded-lg tabular-nums shadow-2xs">
                                    {{ $category->products_count }} SKUs
                                </span>
                            </div>

                            {{-- Contenido Central + Pedestal de Icono Vectorial Profesional --}}
                            <div class="flex items-center justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('catalog.index', ['category' => $category->slug]) }}" class="block group/title">
                                        <h3 class="text-lg sm:text-xl font-black text-[#0F172A] group-hover/title:text-[#1E3A8A] leading-tight transition-colors">
                                            {{ $category->name }}
                                        </h3>
                                    </a>
                                    @if($category->description)
                                        <p class="mt-1.5 text-xs text-slate-500 line-clamp-2 leading-relaxed">
                                            {{ $category->description }}
                                        </p>
                                    @endif
                                </div>

                                {{-- Pedestal de Icono Técnico Profesional (Sin fotos de producto) --}}
                                <a href="{{ route('catalog.index', ['category' => $category->slug]) }}"
                                   tabindex="-1"
                                   aria-hidden="true"
                                   class="cat-icon-pedestal w-20 h-20 sm:w-22 sm:h-22 rounded-2xl {{ $theme['iconBg'] }} ring-4 {{ $theme['iconRing'] }} p-4 flex flex-col items-center justify-center shrink-0 shadow-lg overflow-hidden">
                                    <div class="absolute inset-0 opacity-15" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 8px 8px;"></div>
                                    <div class="relative z-10">
                                        {!! $category->iconSvg('w-10 h-10 sm:w-11 sm:h-11') !!}
                                    </div>
                                </a>
                            </div>

                            {{-- Subcategorías con Mini Icono + CTA Directo --}}
                            <div class="pt-3 border-t border-slate-100 flex flex-col gap-3">
                                @if($category->children->isNotEmpty())
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @foreach($category->children as $sub)
                                            <a href="{{ route('catalog.index', ['category' => $category->slug, 'subcategory' => $sub->slug]) }}"
                                               class="inline-flex items-center gap-1.5 text-[11px] font-semibold bg-slate-50 hover:bg-[#1E3A8A] text-slate-700 hover:text-white px-2.5 py-1 rounded-lg border border-slate-200/80 hover:border-[#1E3A8A] transition-colors max-w-full">
                                                <span class="text-[#1E3A8A] group-hover:text-current shrink-0 opacity-80">{!! $sub->iconSvg('w-3.5 h-3.5') !!}</span>
                                                <span class="truncate max-w-[165px]">{{ $sub->name }}</span>
                                                @if(isset($sub->sub_products_count))
                                                    <span class="font-mono text-[10px] opacity-65">({{ $sub->sub_products_count }})</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                @endif

                                <a href="{{ route('catalog.index', ['category' => $category->slug]) }}"
                                   class="inline-flex items-center justify-between text-xs font-extrabold uppercase tracking-wider text-[#1E3A8A] group-hover:text-[#DC2626] transition-colors">
                                    <span>Explorar {{ $category->children->count() }} subcategorías</span>
                                    <span class="w-6 h-6 rounded-full bg-slate-100 group-hover:bg-[#DC2626] group-hover:text-white flex items-center justify-center transition-colors">&rarr;</span>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        {{-- Nivel 2: Cuadrícula Técnica de Especialidades con Iconos Profesionales --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-4" data-cat-regular-grid>
            @foreach($regularCategories as $category)
                @php
                    $theme = $category->iconTheme();
                    $sectorKey = $theme['sector'];
                    $isPrimary = in_array($category->id, $primaryVisibleIds, true);
                @endphp
                <article data-cat-card
                         data-cat-sector="{{ $sectorKey }}"
                         data-cat-primary="{{ $isPrimary ? 'true' : 'false' }}"
                         class="{{ $isPrimary ? 'flex' : 'hidden' }} group relative bg-white rounded-2xl border border-slate-200/90 hover:border-[#1E3A8A] p-4 sm:p-5 shadow-2xs hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex-col justify-between gap-3.5 overflow-hidden">
                    <div class="flex items-start gap-4">
                        {{-- Pedestal de Icono Vectorial Profesional --}}
                        <a href="{{ route('catalog.index', ['category' => $category->slug]) }}"
                           aria-label="Categoría {{ $category->name }}"
                           class="cat-icon-pedestal w-16 h-16 sm:w-18 sm:h-18 rounded-2xl {{ $theme['iconBg'] }} ring-4 {{ $theme['iconRing'] }} p-3 flex flex-col items-center justify-center shrink-0 shadow-md overflow-hidden">
                            <div class="absolute inset-0 opacity-15" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 7px 7px;"></div>
                            <div class="relative z-10">
                                {!! $category->iconSvg('w-8 h-8 sm:w-9 sm:h-9') !!}
                            </div>
                            <span class="relative z-10 mt-0.5 font-mono text-[9px] font-black tracking-wider opacity-75">
                                {{ $theme['code'] }}
                            </span>
                        </a>

                        {{-- Información de la línea y subcategorías --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <a href="{{ route('catalog.index', ['category' => $category->slug]) }}" class="block min-w-0">
                                    <h3 class="text-sm sm:text-base font-extrabold text-[#0F172A] group-hover:text-[#1E3A8A] transition-colors leading-snug">
                                        {{ $category->name }}
                                    </h3>
                                </a>
                                <span class="shrink-0 font-mono text-[10px] font-extrabold bg-slate-100 group-hover:bg-blue-50 text-slate-700 group-hover:text-[#1E3A8A] border border-slate-200/80 px-2 py-0.5 rounded-md tabular-nums transition-colors">
                                    {{ $category->products_count }} SKUs
                                </span>
                            </div>

                            @if($category->children->isNotEmpty())
                                <div class="mt-2.5 flex flex-wrap gap-1.5">
                                    @foreach($category->children as $sub)
                                        <a href="{{ route('catalog.index', ['category' => $category->slug, 'subcategory' => $sub->slug]) }}"
                                           class="inline-flex items-center gap-1 text-[11px] font-medium bg-slate-50 hover:bg-[#1E3A8A] text-slate-600 hover:text-white px-2 py-0.5 rounded-md border border-slate-200/70 hover:border-[#1E3A8A] transition-colors max-w-full">
                                            <span class="shrink-0 opacity-75">{!! $sub->iconSvg('w-3 h-3') !!}</span>
                                            <span class="truncate max-w-[140px]">{{ $sub->name }}</span>
                                            @if(isset($sub->sub_products_count))
                                                <span class="font-mono text-[9px] opacity-65">({{ $sub->sub_products_count }})</span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            @elseif($category->description)
                                <p class="mt-1.5 text-[11px] text-slate-500 line-clamp-2 leading-relaxed">
                                    {{ $category->description }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] font-bold text-slate-500">
                        <span>{{ $category->children->count() }} {{ $category->children->count() === 1 ? 'subcategoría' : 'subcategorías' }}</span>
                        <a href="{{ route('catalog.index', ['category' => $category->slug]) }}"
                           class="inline-flex items-center gap-1 text-[#1E3A8A] group-hover:text-[#DC2626] font-extrabold transition-colors">
                            <span>Ver productos</span>
                            <span aria-hidden="true" class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Botón / Pestaña inferior "Ver más" para mostrar las demás categorías --}}
        @if($categories->count() > 6)
            <div class="mt-7 text-center" data-cat-toggle-wrapper>
                <button type="button"
                        data-cat-expand-btn
                        data-expanded="false"
                        data-expand-label="Ver más categorías ({{ $categories->count() - 6 }} líneas adicionales)"
                        data-collapse-label="Ver menos (mostrar solo 6 categorías principales)"
                        class="inline-flex items-center gap-2.5 px-6 py-3 rounded-xl bg-white hover:bg-[#0F172A] text-[#0F172A] hover:text-white border border-slate-300 hover:border-[#0F172A] text-xs sm:text-sm font-extrabold shadow-xs transition-all cursor-pointer">
                    <span data-cat-expand-text>Ver más categorías ({{ $categories->count() - 6 }} líneas adicionales)</span>
                    <svg data-cat-expand-icon class="w-4 h-4 text-[#DC2626] transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>
        @endif
    </section>

    {{-- 5. PRODUCTOS DESTACADOS CON PESTAÑAS --}}
    <section class="bg-white border-y border-slate-200 py-12 lg:py-16" aria-labelledby="heading-featured">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5 mb-8">
                <div>
                    <span class="inline-flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-widest text-[#1E3A8A] mb-2">
                        <span class="w-6 h-0.5 bg-[#1E3A8A]"></span> Selección de Alta Rotación
                    </span>
                    <h2 id="heading-featured" class="text-2xl sm:text-4xl font-black text-[#0F172A] tracking-tight">Productos Destacados</h2>
                </div>

                <div class="flex gap-1.5 bg-slate-100 p-1.5 rounded-xl border border-slate-200 overflow-x-auto [scrollbar-width:none]" role="tablist" aria-label="Filtrar productos destacados">
                    @foreach(['destacados' => 'Destacados', 'herrajes' => 'Herrajes y Corredizos', 'cerraduras' => 'Cerraduras y Fijaciones', 'herramientas' => 'Herramientas y LED'] as $tabKey => $tabLabel)
                        <button type="button"
                                data-home-tab="{{ $tabKey }}"
                                role="tab"
                                aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                                class="shrink-0 whitespace-nowrap px-3.5 py-2 rounded-lg text-xs font-extrabold uppercase tracking-wider transition-all cursor-pointer {{ $loop->first ? 'bg-[#1E3A8A] text-white shadow-xs' : 'text-slate-700 hover:text-[#0F172A] hover:bg-white' }}">
                            {{ $tabLabel }}
                        </button>
                    @endforeach
                </div>
            </div>

            @php
                $tabsData = $tabbedProducts ?? ['destacados' => $featuredProducts];
            @endphp

            @foreach($tabsData as $tabKey => $productsCollection)
                <div data-home-tab-panel="{{ $tabKey }}" class="{{ $loop->first ? 'block' : 'hidden' }}">
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                        @foreach($productsCollection as $product)
                            <x-product-card :product="$product" viewMode="grid" />
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="mt-10 text-center">
                <a href="{{ route('catalog.index') }}"
                   class="group inline-flex items-center gap-2 px-7 py-3.5 rounded-xl bg-[#0F172A] hover:bg-[#1E3A8A] text-white font-extrabold text-xs sm:text-sm uppercase tracking-wider transition-colors shadow-lg">
                    Ver los {{ $catalogStats['products'] ?? 0 }} productos del catálogo
                    <span aria-hidden="true" class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </a>
            </div>
        </div>
    </section>

    {{-- 6. MARCAS --}}
    @if(!empty($carouselBrandLogos))
        <section class="py-12" aria-labelledby="heading-brands">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8">
                    <span class="text-[11px] font-extrabold uppercase tracking-widest text-[#DC2626]">Respaldo y Garantía</span>
                    <h2 id="heading-brands" class="mt-1 text-2xl sm:text-3xl font-black text-[#0F172A] tracking-tight">Marcas que Comercializamos</h2>
                </div>

                <div class="brand-marquee-viewport" aria-label="Carrusel continuo de marcas">
                    <div class="brand-marquee-track gap-4 sm:gap-6 px-3 items-center">
                        @foreach([false, true] as $isDuplicate)
                            @foreach($carouselBrandLogos as $brandLogo)
                                <a href="{{ $brandLogo['url'] }}"
                                   @if($isDuplicate) tabindex="-1" aria-hidden="true" @else title="Ver productos {{ $brandLogo['name'] }}" @endif
                                   class="w-40 sm:w-48 h-24 rounded-2xl bg-white border border-slate-200 hover:border-[#1E3A8A] hover:shadow-lg px-5 py-3 flex items-center justify-center shrink-0 transition-all group">
                                    <picture>
                                        <source srcset="{{ $brandLogo['webp'] }}" type="image/webp">
                                        <img src="{{ $brandLogo['png'] }}"
                                             alt="{{ $isDuplicate ? '' : $brandLogo['name'] }}"
                                             width="155"
                                             height="65"
                                             loading="lazy"
                                             class="max-h-12 w-auto object-contain grayscale opacity-70 group-hover:grayscale-0 group-hover:opacity-100 group-hover:scale-105 transition-all duration-300">
                                    </picture>
                                </a>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- 7. SOLUCIONES POR SECTOR --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12 lg:pb-16" aria-label="Soluciones por sector">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 sm:gap-6">
            @foreach([
                [
                    'tag' => 'Mueblería y Carpintería en Melamina', 'tagClass' => 'text-amber-300 bg-white/10',
                    'title' => 'Herrajes, Sistemas Corredizos y Acabados',
                    'text' => 'Correderas telescópicas y ocultas, bisagras cangrejo cierre suave, sistemas corredizos Ducasse, pistones a gas, perfiles de aluminio, tapacantos Pelikano e iluminación LED.',
                    'brands' => ['Ducasse', 'Pelikano', 'Venezzia', 'Danco'],
                    'cta' => 'Ver Herrajes', 'url' => route('catalog.index', ['category' => 'herrajes-para-muebles']),
                    'bg' => 'from-[#0F172A] via-[#14275F] to-[#1E3A8A]', 'btn' => 'bg-[#DC2626] hover:bg-[#B91C1C] text-white', 'glow' => 'bg-blue-400',
                    'products' => ($tabbedProducts['herrajes'] ?? collect())->slice(3, 2),
                ],
                [
                    'tag' => 'Ferretería, Seguridad y Obra', 'tagClass' => 'text-red-300 bg-red-500/15',
                    'title' => 'Cerraduras, Fijaciones y Herramientas',
                    'text' => 'Cerraduras de sobreponer y parche, tarugos y tornillería estructural, siliconas y selladores, herramientas eléctricas y manuales, brocas y equipamiento para obra.',
                    'brands' => ['Travex', 'Cantol', 'Bosch', 'Sika'],
                    'cta' => 'Ver Ferretería', 'url' => route('catalog.index', ['category' => 'chapas-cerraduras-y-otros']),
                    'bg' => 'from-[#0F172A] via-[#2A1216] to-[#450A0A]', 'btn' => 'bg-white hover:bg-slate-100 text-[#0F172A]', 'glow' => 'bg-red-500',
                    'products' => ($tabbedProducts['cerraduras'] ?? collect())->slice(3, 2),
                ],
            ] as $sector)
                <article class="banner-shine-effect group relative rounded-3xl bg-gradient-to-br {{ $sector['bg'] }} text-white p-6 sm:p-9 shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden flex flex-col justify-between min-h-[320px] border border-white/10">
                    <div class="banner-aura-glow absolute -right-16 -top-16 w-72 h-72 rounded-full {{ $sector['glow'] }} opacity-25 blur-3xl pointer-events-none"></div>
                    <div class="hero-grid-pattern absolute inset-0 opacity-30 pointer-events-none"></div>
                    <div class="absolute right-5 bottom-20 hidden sm:flex gap-3 pointer-events-none">
                        @foreach($sector['products'] as $sectorProduct)
                            <span class="w-24 h-24 rounded-2xl bg-white p-2.5 shadow-2xl flex items-center justify-center {{ $loop->first ? 'rotate-[-6deg] group-hover:rotate-0' : 'rotate-[6deg] translate-y-4 group-hover:rotate-0' }} transition-transform duration-500">
                                <img src="{{ $sectorProduct->primaryThumbnailUrl() }}" alt="{{ $sectorProduct->name }}" width="96" height="96" loading="lazy" decoding="async" class="max-w-full max-h-full object-contain">
                            </span>
                        @endforeach
                    </div>
                    <div class="relative space-y-3 max-w-md">
                        <span class="inline-block text-[11px] font-extrabold uppercase tracking-widest {{ $sector['tagClass'] }} px-2.5 py-1 rounded-md">{{ $sector['tag'] }}</span>
                        <h2 class="text-2xl sm:text-3xl font-black tracking-tight">{{ $sector['title'] }}</h2>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">{{ $sector['text'] }}</p>
                    </div>
                    <div class="relative mt-8 pt-5 border-t border-white/10 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($sector['brands'] as $sectorBrand)
                                <span class="text-[11px] font-semibold bg-white/10 text-slate-200 px-2.5 py-1 rounded">{{ $sectorBrand }}</span>
                            @endforeach
                        </div>
                        <a href="{{ $sector['url'] }}"
                           class="inline-flex items-center gap-1.5 px-5 py-3 rounded-xl {{ $sector['btn'] }} text-xs font-extrabold uppercase tracking-wider transition-colors shrink-0">
                            {{ $sector['cta'] }} <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- 8. PRODUCTOS EN PROMOCIÓN --}}
    @if(!empty($promoProducts) && $promoProducts->isNotEmpty())
        <section class="relative bg-gradient-to-b from-slate-50 via-white to-slate-50 border-y border-slate-200 py-12 lg:py-16 overflow-hidden" aria-labelledby="heading-promotions">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                {{-- Cabecera de Campaña Promocional --}}
                <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5 mb-8">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 border border-red-200 text-[#DC2626] text-[11px] font-extrabold uppercase tracking-widest mb-2.5">
                            <span class="w-2 h-2 rounded-full bg-[#DC2626] animate-ping"></span>
                            <span>Campaña Mayorista y Unidad • Ofertas por Volumen</span>
                        </div>
                        <h2 id="heading-promotions" class="text-2xl sm:text-4xl font-black text-[#0F172A] tracking-tight">
                            Productos en <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#DC2626] to-amber-600">Promoción</span>
                        </h2>
                        <p class="mt-1.5 text-xs sm:text-sm text-slate-600 max-w-2xl">
                            Aprovecha condiciones especiales de abastecimiento por caja, ciento o proyecto en herrajes, tiradores, sistemas corredizos y accesorios seleccionados.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5 self-start lg:self-end">
                        <a href="{{ route('quote.index') }}"
                           class="banner-shine-effect inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] hover:bg-[#B91C1C] text-white text-xs sm:text-sm font-extrabold uppercase tracking-wider shadow-md transition-colors">
                            <span>Cotizar Promociones</span>
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                        <a href="{{ route('catalog.index') }}"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 hover:border-[#1E3A8A] text-xs sm:text-sm font-extrabold text-[#1E3A8A] transition-colors">
                            <span>Ver Todo el Catálogo</span>
                        </a>
                    </div>
                </div>

                {{-- Cuadrícula de Productos en Promoción --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                    @foreach($promoProducts as $promoProduct)
                        <x-product-card :product="$promoProduct" viewMode="grid" :promoBadge="true" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 9. CÓMO COTIZAR + CTA --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div class="relative overflow-hidden rounded-3xl bg-[#0F172A] text-white p-6 sm:p-10 lg:p-12 shadow-2xl">
            <div class="absolute -right-24 -top-24 w-96 h-96 rounded-full bg-[#DC2626]/20 blur-3xl pointer-events-none"></div>
            <div class="absolute -left-24 -bottom-24 w-96 h-96 rounded-full bg-blue-600/20 blur-3xl pointer-events-none"></div>
            <div class="relative grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center">
                <div class="lg:col-span-7 space-y-6">
                    <div>
                        <span class="text-[11px] font-extrabold uppercase tracking-widest text-amber-400">Proceso Ágil B2B y B2C</span>
                        <h2 class="text-2xl sm:text-3xl font-black mt-2 leading-tight">Cotiza el requerimiento de tu obra, taller o negocio en 3 pasos</h2>
                    </div>
                    <ol class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach([
                            ['title' => 'Elige tus productos', 'text' => 'Añade ítems y cantidades a tu lista desde el catálogo.', 'color' => 'bg-[#DC2626]'],
                            ['title' => 'Envía tu solicitud', 'text' => 'Indica tus datos o RUC y obtén tu código de cotización.', 'color' => 'bg-[#1D4ED8]'],
                            ['title' => 'Recibe tu oferta PDF', 'text' => 'Te enviamos la cotización formal con precios por volumen.', 'color' => 'bg-emerald-600'],
                        ] as $step)
                            <li class="relative bg-white/5 border border-white/10 rounded-2xl p-5">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full {{ $step['color'] }} font-mono text-sm font-black mb-3">{{ $loop->iteration }}</span>
                                <h3 class="text-sm font-bold">{{ $step['title'] }}</h3>
                                <p class="text-xs text-slate-400 mt-1 leading-relaxed">{{ $step['text'] }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>

                <div class="lg:col-span-5 bg-white/5 backdrop-blur border border-white/10 rounded-2xl p-6 sm:p-7 space-y-4">
                    <h3 class="text-lg font-black">¿Listo para enviar tu lista de compra?</h3>
                    <p class="text-sm text-slate-300 leading-relaxed">Atendemos pedidos corporativos con factura (RUC) y compras directas en Lima y todo el Perú.</p>
                    <div class="flex flex-col sm:flex-row gap-2.5">
                        <a href="{{ route('quote.index') }}"
                           class="flex-1 text-center px-4 py-3.5 rounded-xl bg-[#DC2626] hover:bg-[#B91C1C] text-white font-extrabold text-xs uppercase tracking-wider transition-colors shadow-md">
                            Solicitar Cotización
                        </a>
                        <x-whatsapp-button
                            variant="inline"
                            :url="$whatsappUrl"
                            label="WhatsApp Ventas"
                            ctaLocation="home_bottom_cta"
                            contextType="general"
                            class="flex-1 justify-center px-4 py-3.5 text-xs rounded-xl" />
                    </div>
                    <div class="pt-3 border-t border-white/10 flex items-center justify-between text-xs">
                        <span class="text-slate-400">¿Ya enviaste una solicitud?</span>
                        <a href="{{ route('quote.track') }}" class="font-bold text-amber-300 hover:underline">Consultar estado &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
