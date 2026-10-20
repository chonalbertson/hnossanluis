@extends('layouts.public')

@section('content')
    @php
        use App\Models\SiteSetting;

        $showAvailability = (bool) SiteSetting::getValue('commercial_show_availability_badge', true);
        $hasVariants = $product->variants->isNotEmpty();
        $currentVariant = $activeVariant;
        $isQuoteDisabled = $hasVariants && ! $currentVariant;

        $displaySku = $currentVariant?->sku ?: $product->sku;
        $displayUnit = $currentVariant?->sale_unit ?: $product->sale_unit;
        $displayDimensions = $currentVariant?->dimensions ?: ($product->dimensions ?: 'Ver tabla técnica');
        $displayMaterial = $currentVariant?->material ?: ($product->material ?: 'Estándar técnico');
        $displayPackaging = ($currentVariant && ($currentVariant->box_qty || $currentVariant->carton_qty))
            ? trim(($currentVariant->box_qty ? 'Caja: '.$currentVariant->box_qty : '').($currentVariant->box_qty && $currentVariant->carton_qty ? ' | ' : '').($currentVariant->carton_qty ? 'Cajón: '.$currentVariant->carton_qty : ''))
            : 'Por unidad / volumen';

        $showPrice = $product->shouldShowPrice();
        $formattedPrice = $currentVariant
            ? ($currentVariant->formattedPrice($product) ?: $product->formattedPrice())
            : $product->formattedPrice();

        $beginQuotePayload = json_encode([
            'cta_location' => 'product_detail',
            'item_id' => $displaySku,
            'item_name' => $product->name,
            'item_category' => $product->category?->name ?? '',
            'item_brand' => $product->brand?->name ?? '',
            'unit_of_measure' => $displayUnit,
            'quantity' => 1,
        ], JSON_UNESCAPED_UNICODE);

        $pdfPayload = json_encode([
            'file_name' => 'Ficha-Tecnica-'.$product->sku.'.pdf',
            'file_extension' => 'pdf',
            'link_text' => 'Descargar Ficha Técnica PDF',
            'item_id' => $product->sku,
            'item_name' => $product->name,
            'item_category' => $product->category?->name ?? '',
        ], JSON_UNESCAPED_UNICODE);

        $viewItemPayload = [
            'sku' => $product->sku,
            'item_name' => $product->name,
            'item_category' => $product->category?->name ?? '',
            'item_brand' => $product->brand?->name ?? '',
            'sale_unit' => $product->sale_unit,
            'currency' => $product->currency ?: 'PEN',
            'price' => $showPrice ? (float) ($currentVariant?->price ?? $product->price) : null,
            'value' => $showPrice ? (float) ($currentVariant?->price ?? $product->price) : null,
        ];
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8" data-product-detail-root>
        <x-breadcrumb :items="$breadcrumbs" />

        <!-- Top Executive Technical Product Card -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden mb-10">
            <!-- Top Technical Identity Ribbon -->
            <div class="bg-[#0F172A] text-white px-4 sm:px-8 py-3 flex flex-wrap items-center justify-between gap-3 border-b-2 border-[#DC2626]">
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <span class="inline-flex items-center gap-1.5 font-mono text-xs sm:text-sm font-extrabold uppercase tracking-wider bg-white/10 text-white px-3 py-1 rounded-md border border-white/15 tabular-nums">
                        <span class="text-slate-400">SKU:</span>
                        <span data-product-active-sku>{{ $displaySku }}</span>
                    </span>

                    <button type="button"
                            data-copy-sku-btn
                            data-sku-value="{{ $displaySku }}"
                            class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-300 hover:text-white bg-white/5 hover:bg-white/15 px-2.5 py-1 rounded-md border border-white/10 transition-colors cursor-pointer"
                            title="Copiar código SKU al portapapeles">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span data-copy-sku-text>Copiar SKU</span>
                    </button>

                    @if($hasVariants)
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-extrabold uppercase tracking-wider bg-[#DC2626] text-white px-2.5 py-1 rounded-md shadow-2xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                            <span>Producto Variable ({{ $product->variants->count() }} medidas)</span>
                        </span>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2 text-xs">
                    @if($product->category)
                        <a href="{{ route('catalog.index', ['category' => $product->category->slug]) }}"
                           class="text-slate-300 hover:text-white font-semibold transition-colors">
                            {{ $product->category->name }}
                        </a>
                    @endif
                    @if($product->subcategory)
                        <span class="text-slate-500">/</span>
                        <a href="{{ route('catalog.index', ['category' => $product->category?->slug, 'subcategory' => $product->subcategory->slug]) }}"
                           class="text-amber-400 hover:underline font-bold">
                            {{ $product->subcategory->name }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="p-5 sm:p-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
                    <!-- Left Column (5 cols): Technical Image Gallery & Logistics Guarantee -->
                    <div class="lg:col-span-5 space-y-4">
                        <div class="aspect-square rounded-2xl bg-white border border-slate-200 p-6 flex items-center justify-center relative overflow-hidden group">
                            <div class="absolute top-3 left-3 z-10 flex flex-col gap-1.5">
                                @if($product->is_featured)
                                    <span class="bg-[#1E3A8A] text-white text-[11px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-md shadow-2xs">
                                        Destacado
                                    </span>
                                @endif
                                @if($hasVariants)
                                    <span data-gallery-variant-badge class="{{ $currentVariant ? '' : 'hidden' }} bg-slate-900/90 text-amber-400 text-[11px] font-mono font-bold px-2.5 py-1 rounded-md shadow-2xs">
                                        {{ $currentVariant?->variant_label }}
                                    </span>
                                @endif
                            </div>

                            <img src="{{ $product->primaryImageUrl() }}"
                                 alt="{{ $product->name }} - Código SKU {{ $displaySku }}"
                                 width="800"
                                 height="800"
                                 fetchpriority="high"
                                 decoding="async"
                                 data-gallery-main
                                 class="max-h-full max-w-full object-contain transition-transform duration-300 group-hover:scale-105">
                        </div>

                        @if($product->images->count() > 1)
                            <div class="grid grid-cols-5 gap-2.5" role="group" aria-label="Galería de imágenes del producto">
                                @foreach($product->images as $idx => $img)
                                    <button type="button"
                                            data-gallery-thumb
                                            data-gallery-src="{{ $img->url() }}"
                                            data-gallery-alt="{{ $img->alt_text ?: $product->name }}"
                                            class="aspect-square rounded-xl bg-white p-1.5 border {{ $idx === 0 ? 'ring-2 ring-blue-700 border-blue-700' : 'border-slate-200' }} hover:border-blue-500 overflow-hidden cursor-pointer transition-all">
                                        <img src="{{ $img->thumbnailUrl() }}"
                                             alt="{{ $img->alt_text ?: $product->name }}"
                                             width="120"
                                             height="120"
                                             loading="lazy"
                                             decoding="async"
                                             class="w-full h-full object-contain">
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <!-- B2B Supply & Technical Support Card -->
                        <div class="rounded-xl bg-slate-50 border border-slate-200/90 p-4 space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
                                <span class="text-xs font-extrabold uppercase tracking-wider text-[#0F172A]">Respaldo Corporativo San Luis</span>
                                <span class="text-[11px] font-bold text-[#1E3A8A] bg-blue-50 px-2 py-0.5 rounded border border-blue-200">B2B / B2C</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div class="flex items-start gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-[#1E3A8A]/10 text-[#1E3A8A] flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900 block">Abastecimiento Escalonado</span>
                                        <span class="text-slate-600 text-[11px]">Por unidad, caja cerrada o cajón master.</span>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900 block">Despacho Nacional</span>
                                        <span class="text-slate-600 text-[11px]">Lima 24–48h y agencias a todo el Perú.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column (7 cols): Identification, Interactive Variant Selector, Engineering Specs & Conversion -->
                    <div class="lg:col-span-7 flex flex-col justify-between space-y-6">
                        <div class="space-y-5">
                            <!-- Brand & Availability Row -->
                            <div class="flex flex-wrap items-center justify-between gap-2.5">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if($product->brand)
                                        <a href="{{ route('catalog.index', ['brand' => $product->brand->slug]) }}"
                                           class="text-xs font-extrabold uppercase tracking-wider bg-blue-50 text-[#1E3A8A] border border-blue-200 px-3 py-1 rounded-md hover:bg-blue-100 transition-colors">
                                            Marca: {{ $product->brand->name }}
                                        </a>
                                    @endif

                                    @if($showAvailability)
                                        <span data-product-active-availability
                                              data-base-availability-label="{{ $product->availabilityLabel() }}"
                                              data-base-availability-class="{{ $product->availabilityBadgeClass() }}"
                                              class="text-xs font-bold px-3 py-1 rounded-md border {{ $currentVariant ? $currentVariant->availabilityBadgeClass() : $product->availabilityBadgeClass() }}">
                                            {{ $currentVariant ? $currentVariant->availabilityLabel() : $product->availabilityLabel() }}
                                        </span>
                                    @endif
                                </div>

                                <span class="text-xs font-semibold text-slate-500">
                                    Código Base: <strong class="font-mono text-slate-800" data-product-base-sku>{{ $product->sku }}</strong>
                                </span>
                            </div>

                            <!-- H1 Title & Active Variant Subtitle -->
                            <div>
                                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F172A] leading-tight">
                                    {{ $product->name }}
                                </h1>
                                @if($hasVariants)
                                    <div class="mt-2 inline-flex items-center gap-2 text-xs font-bold text-[#1E3A8A] bg-blue-50/90 border border-blue-200 px-3 py-1 rounded-lg">
                                        <span>Presentación seleccionada:</span>
                                        <strong data-product-active-label class="text-[#0F172A] font-extrabold">
                                            {{ $currentVariant ? ($currentVariant->name ?: $currentVariant->variant_label) : 'Elige un producto para cotizar' }}
                                        </strong>
                                    </div>
                                @endif
                            </div>

                            @if($product->short_description)
                                <p class="text-sm sm:text-base text-slate-700 leading-relaxed bg-slate-50 border-l-4 border-[#1E3A8A] p-3.5 rounded-r-xl">
                                    {{ strip_tags((string) $product->short_description) }}
                                </p>
                            @endif

                            <!-- Interactive Variant Dropdown Selector (For Variable Products) -->
                            @if($hasVariants)
                                <div class="rounded-2xl border-2 border-blue-200 bg-gradient-to-br from-blue-50/60 via-white to-slate-50 p-4 sm:p-5 space-y-3.5 shadow-2xs">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <div>
                                            <label for="product-variant-select" class="text-xs font-extrabold uppercase tracking-wider text-[#1E3A8A] block">
                                                Elige un Producto / Medida para Cotizar ({{ $product->variants->count() }} disponibles)
                                            </label>
                                            <p class="text-xs text-slate-600 mt-0.5">
                                                Selecciona una opción del listado desplegable para ver su nombre completo y activar la cotización.
                                            </p>
                                        </div>
                                        <a href="#matriz-variantes-b2b" class="text-xs font-extrabold text-[#DC2626] hover:underline shrink-0">
                                            Cotizar varias medidas a la vez &darr;
                                        </a>
                                    </div>

                                    <div>
                                        <select id="product-variant-select"
                                                data-variant-select
                                                aria-label="Elige un producto para cotizar"
                                                class="w-full rounded-xl border-2 border-blue-300 bg-white px-3.5 py-3 text-xs sm:text-sm font-extrabold text-[#0F172A] shadow-2xs focus:border-[#1E3A8A] focus:ring-2 focus:ring-blue-500/20 focus:outline-none cursor-pointer transition-all">
                                            <option value="">— Elige un producto para cotizar ({{ $product->variants->count() }} opciones disponibles) —</option>
                                            @foreach($product->variants as $variant)
                                                @php
                                                    $isSelectedVariant = $currentVariant && $currentVariant->id === $variant->id;
                                                    $variantPriceFormatted = $variant->formattedPrice($product);
                                                    $variantPackaging = ($variant->box_qty || $variant->carton_qty)
                                                        ? trim(($variant->box_qty ? 'Caja: '.$variant->box_qty : '').($variant->box_qty && $variant->carton_qty ? ' | ' : '').($variant->carton_qty ? 'Cajón: '.$variant->carton_qty : ''))
                                                        : 'Por unidad / volumen';
                                                    $variantFullName = ($variant->name && mb_strtolower(trim($variant->name)) !== mb_strtolower(trim((string) $variant->variant_label)))
                                                        ? trim($variant->name).' — '.trim((string) $variant->variant_label)
                                                        : trim((string) ($variant->name ?: $variant->variant_label ?: $product->name));
                                                @endphp
                                                <option value="{{ $variant->id }}"
                                                        data-variant-pill
                                                        data-variant-id="{{ $variant->id }}"
                                                        data-variant-sku="{{ $variant->sku }}"
                                                        data-variant-label="{{ $variantFullName }}"
                                                        data-variant-name="{{ $variant->name ?: ($product->name.' — '.$variant->variant_label) }}"
                                                        data-variant-dimensions="{{ $variant->dimensions ?: ($variant->variant_label ?: $product->dimensions) }}"
                                                        data-variant-material="{{ $variant->material ?: ($product->material ?: 'Estándar técnico') }}"
                                                        data-variant-unit="{{ $variant->sale_unit ?: $product->sale_unit }}"
                                                        data-variant-packaging="{{ $variantPackaging }}"
                                                        data-variant-price="{{ $variantPriceFormatted ?: '' }}"
                                                        data-variant-availability-label="{{ $variant->availabilityLabel() }}"
                                                        data-variant-availability-class="{{ $variant->availabilityBadgeClass() }}"
                                                        @selected($isSelectedVariant)>
                                                    {{ $variant->sku }} — {{ $variantFullName }}@if($variant->dimensions && !str_contains(mb_strtolower($variantFullName), mb_strtolower($variant->dimensions))) ({{ $variant->dimensions }})@endif — {{ $variant->availabilityLabel() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div data-variant-selection-hint class="{{ $currentVariant ? 'hidden' : 'flex' }} items-center gap-2 text-xs font-bold text-amber-900 bg-amber-50 border border-amber-200 rounded-xl px-3.5 py-2.5">
                                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>Elige un producto de la lista desplegable para activar el botón de cotización.</span>
                                    </div>

                                    <div data-variant-selection-summary class="{{ $currentVariant ? 'flex' : 'hidden' }} flex-wrap items-center justify-between gap-2 text-xs bg-white border border-blue-200 rounded-xl px-3.5 py-2.5 shadow-2xs">
                                        <div class="min-w-0 flex-1">
                                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Opción seleccionada:</span>
                                            <p data-variant-summary-name class="font-extrabold text-[#0F172A] text-xs sm:text-sm break-words">
                                                {{ $currentVariant ? ($currentVariant->name ?: $currentVariant->variant_label) : '' }}
                                            </p>
                                        </div>
                                        <span data-variant-summary-sku class="font-mono text-xs font-extrabold bg-[#1E3A8A] text-white px-2.5 py-1 rounded-lg shrink-0">
                                            SKU: {{ $currentVariant?->sku }}
                                        </span>
                                    </div>
                                </div>
                            @endif

                            @if($showPrice && $formattedPrice)
                                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block">
                                            Precio Referencial por <span data-product-active-unit-label>{{ $displayUnit }}</span>
                                        </span>
                                        <div class="flex items-baseline gap-2 mt-0.5">
                                            <span data-product-active-price class="text-2xl sm:text-3xl font-extrabold text-[#0F172A] tabular-nums">{{ $formattedPrice }}</span>
                                            <span class="text-xs font-semibold text-slate-600">Inc. IGV (Escalas por volumen B2B)</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Conversion Form: Add to Quote List + Individual Quick Quote + WhatsApp -->
                        <div class="pt-4 border-t border-slate-200 space-y-3.5">
                            <form action="{{ route('quote.add') }}" method="POST" data-ajax-quote-form @if($hasVariants) data-requires-variant="true" @endif class="space-y-3.5">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="variant_id" value="{{ $currentVariant?->id }}" data-active-variant-input>

                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                    <div class="sm:col-span-5">
                                        <div class="flex items-center justify-between mb-1">
                                            <label for="detail-quantity-input" class="block text-xs font-bold text-slate-700">
                                                Cantidad (<span data-product-active-unit-label>{{ $displayUnit }}</span>)
                                            </label>
                                            <div class="inline-flex items-center gap-1">
                                                <button type="button" data-qty-preset="10" class="text-[10px] font-bold bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-[#1E3A8A] px-1.5 py-0.5 rounded border border-slate-200 cursor-pointer">+10</button>
                                                <button type="button" data-qty-preset="50" class="text-[10px] font-bold bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-[#1E3A8A] px-1.5 py-0.5 rounded border border-slate-200 cursor-pointer">+50</button>
                                                <button type="button" data-qty-preset="100" class="text-[10px] font-bold bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-[#1E3A8A] px-1.5 py-0.5 rounded border border-slate-200 cursor-pointer">+100</button>
                                            </div>
                                        </div>
                                        <div class="flex items-center rounded-xl border-2 border-slate-300 bg-white overflow-hidden focus-within:border-[#1D4ED8]">
                                            <button type="button" data-qty-step="-1" aria-label="Disminuir cantidad" class="px-3 py-2.5 bg-slate-50 hover:bg-slate-100 text-slate-700 font-extrabold text-sm border-r border-slate-200 cursor-pointer">&minus;</button>
                                            <input id="detail-quantity-input"
                                                   type="number"
                                                   name="quantity"
                                                   value="1"
                                                   min="1"
                                                   step="1"
                                                   required
                                                   class="w-full px-3 py-2 text-center text-sm font-extrabold text-slate-900 tabular-nums focus:outline-none">
                                            <button type="button" data-qty-step="1" aria-label="Aumentar cantidad" class="px-3 py-2.5 bg-slate-50 hover:bg-slate-100 text-slate-700 font-extrabold text-sm border-l border-slate-200 cursor-pointer">+</button>
                                        </div>
                                    </div>

                                    <div class="sm:col-span-7">
                                        <label for="detail-notes-input" class="block text-xs font-bold text-slate-700 mb-1">
                                            Nota técnica opcional (ej. espesor, color, acabado)
                                        </label>
                                        <input id="detail-notes-input"
                                               type="text"
                                               name="notes"
                                               placeholder="Especificación adicional para este ítem..."
                                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                    <button type="submit"
                                            data-quote-submit-btn
                                            data-enabled-label="Añadir a Lista de Cotización"
                                            data-disabled-label="Elige un producto para cotizar"
                                            data-ga-event="begin_quote"
                                            data-ga-params="{{ $beginQuotePayload }}"
                                            @disabled($isQuoteDisabled)
                                            class="w-full font-extrabold py-3.5 px-4 sm:px-5 rounded-xl text-xs sm:text-sm uppercase tracking-wider transition-all flex items-center justify-center gap-2 {{ $isQuoteDisabled ? 'bg-slate-200 text-slate-500 border border-slate-300 cursor-not-allowed shadow-none' : 'bg-[#DC2626] hover:bg-[#B91C1C] text-white shadow-md cursor-pointer' }}">
                                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                        <span data-quote-submit-text>{{ $isQuoteDisabled ? 'Elige un producto para cotizar' : 'Añadir a Lista de Cotización' }}</span>
                                    </button>

                                    <x-whatsapp-button
                                        variant="inline"
                                        :url="$whatsappInquiryUrl"
                                        label="Consultar por WhatsApp"
                                        ctaLocation="product_detail"
                                        contextType="single_product"
                                        :sku="$displaySku"
                                        :productName="$product->name"
                                        class="w-full py-3.5 px-4 sm:px-5 rounded-xl text-xs sm:text-sm font-extrabold uppercase tracking-wider shadow-md" />
                                </div>
                            </form>

                            <div class="pt-3 border-t border-slate-100">
                                <x-social-share
                                    variant="compact"
                                    :url="route('catalog.show', $product->slug)"
                                    :title="$product->name"
                                    :sku="$displaySku" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- B2B Multi-Variant Matrix Table (For Variable Products) -->
        @if($hasVariants)
            <section id="matriz-variantes-b2b" aria-labelledby="heading-variants-matrix" class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden mb-10">
                <div class="bg-[#0F172A] text-white px-5 sm:px-7 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b-4 border-[#1E3A8A]">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider bg-[#DC2626] text-white">Pedido Mayorista B2B</span>
                            <h2 id="heading-variants-matrix" class="text-base sm:text-lg font-extrabold uppercase tracking-wide">
                                Matriz de Medidas y Variantes — {{ $product->name }}
                            </h2>
                        </div>
                        <p class="text-xs text-slate-300 mt-0.5">
                            Ingresa las cantidades requeridas en una o múltiples medidas para agregarlas en bloque a tu pliego de cotización.
                        </p>
                    </div>
                    <div class="text-xs font-mono text-amber-400 font-bold shrink-0">
                        {{ $product->variants->count() }} códigos SKU disponibles
                    </div>
                </div>

                <form action="{{ route('quote.add') }}" method="POST" data-ajax-quote-form data-variant-matrix-form>
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-xs">
                            <thead class="bg-slate-50 text-slate-600 uppercase font-extrabold">
                                <tr>
                                    <th class="px-4 py-3 text-left">Código SKU</th>
                                    <th class="px-4 py-3 text-left">Medida / Variación</th>
                                    <th class="px-4 py-3 text-left">Dimensiones / Material</th>
                                    <th class="px-4 py-3 text-left">Unidad / Empaque</th>
                                    @if($showPrice)
                                        <th class="px-4 py-3 text-left">Precio Ref.</th>
                                    @endif
                                    <th class="px-4 py-3 text-left">Disponibilidad</th>
                                    <th class="px-4 py-3 text-right w-44">Cantidad a Cotizar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($product->variants as $variant)
                                    <tr class="hover:bg-blue-50/40 transition-colors">
                                        <td class="px-4 py-3 font-mono font-extrabold text-slate-900 whitespace-nowrap">
                                            {{ $variant->sku }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-extrabold text-[#1E3A8A] text-xs sm:text-sm">{{ $variant->variant_label }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">
                                            <span class="font-semibold text-slate-800">{{ $variant->dimensions ?: '—' }}</span>
                                            @if($variant->material)
                                                <span class="block text-[11px] text-slate-500">{{ $variant->material }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-700">
                                            <span class="font-bold">{{ $variant->sale_unit ?: $product->sale_unit }}</span>
                                            @if($variant->box_qty || $variant->carton_qty)
                                                <span class="block text-[11px] text-slate-500 tabular-nums">
                                                    @if($variant->box_qty) Caja: {{ $variant->box_qty }} @endif
                                                    @if($variant->box_qty && $variant->carton_qty) &bull; @endif
                                                    @if($variant->carton_qty) Cajón: {{ $variant->carton_qty }} @endif
                                                </span>
                                            @endif
                                        </td>
                                        @if($showPrice)
                                            <td class="px-4 py-3 font-extrabold text-[#0F172A] tabular-nums whitespace-nowrap">
                                                {{ $variant->formattedPrice($product) ?: 'Bajo cotización' }}
                                            </td>
                                        @endif
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $variant->availabilityBadgeClass() }}">
                                                {{ $variant->availabilityLabel() }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="inline-flex items-center gap-1.5">
                                                <label class="sr-only" for="matrix-qty-{{ $variant->id }}">Cantidad para {{ $variant->variant_label }}</label>
                                                <input id="matrix-qty-{{ $variant->id }}"
                                                       type="number"
                                                       name="variant_quantities[{{ $variant->id }}]"
                                                       data-matrix-qty-input
                                                       value="0"
                                                       min="0"
                                                       step="1"
                                                       class="w-24 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-right text-xs font-extrabold text-slate-900 tabular-nums focus:border-[#1E3A8A] focus:outline-none">
                                                <span class="text-[11px] text-slate-500 font-semibold">{{ $variant->sale_unit ?: $product->sale_unit }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-slate-50 px-5 sm:px-7 py-4 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="text-xs text-slate-600">
                            Variantes con cantidad seleccionada: <strong data-matrix-selected-count class="text-[#0F172A] font-extrabold">0</strong>
                            <span class="mx-1.5 text-slate-300">|</span>
                            Total unidades: <strong data-matrix-total-units class="text-[#1E3A8A] font-extrabold tabular-nums">0</strong>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <input type="text"
                                   name="notes"
                                   placeholder="Observación general para estas variantes (opcional)..."
                                   class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs text-slate-900 sm:w-72 focus:border-[#1D4ED8] focus:outline-none">
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl bg-[#DC2626] hover:bg-[#B91C1C] text-white font-extrabold text-xs uppercase tracking-wider shadow-sm transition-colors cursor-pointer">
                                + Añadir Medidas Seleccionadas al Pliego
                            </button>
                        </div>
                    </div>
                </form>
            </section>
        @endif

        <!-- Detailed Description & Zebra Technical Specifications Table -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12">
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs">
                    <h2 class="text-lg font-extrabold text-[#0F172A] section-heading-bar mb-4">
                        Descripción y Aplicación Técnica
                    </h2>
                    <div class="text-sm text-slate-700 leading-relaxed space-y-3 cms-content">
                        @if($product->detailed_description)
                            @php
                                $rawDesc = trim((string) $product->detailed_description);
                                $hasHtmlTags = $rawDesc !== strip_tags($rawDesc);
                            @endphp
                            @if($hasHtmlTags)
                                {!! strip_tags($rawDesc, '<p><br><h2><h3><h4><ul><ol><li><strong><b><em><i><span>') !!}
                            @else
                                {!! nl2br(e($rawDesc)) !!}
                            @endif
                        @elseif($product->short_description)
                            <p>{{ strip_tags((string) $product->short_description) }}</p>
                        @else
                            <p>Producto perteneciente a la línea técnica de <strong>Import Hns San Luis</strong>, identificado con código SKU <code class="font-mono font-bold">{{ $product->sku }}</code>.</p>
                        @endif
                    </div>

                    @if($product->documents->isNotEmpty())
                        <div class="mt-6 pt-5 border-t border-slate-200">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Documentos Técnicos Adjuntos</h3>
                            <ul class="space-y-2">
                                @foreach($product->documents as $doc)
                                    <li>
                                        <a href="{{ route('catalog.download-pdf', ['slug' => $product->slug, 'document_id' => $doc->id]) }}"
                                           data-ga-event="file_download"
                                           data-ga-params="{{ $pdfPayload }}"
                                           class="flex items-center justify-between p-3 rounded-lg bg-slate-50 hover:bg-blue-50 border border-slate-200 text-xs font-semibold text-[#1E3A8A]">
                                            <span>{{ $doc->title ?: 'Ficha Técnica PDF' }}</span>
                                            <span class="text-[#DC2626] font-bold uppercase">Descargar PDF &darr;</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-7">
                <x-tech-specs-table :product="$product" :groupedSpecs="$groupedSpecs" />
            </div>
        </div>

        <!-- Related Products -->
        @if($relatedProducts->isNotEmpty())
            <section aria-labelledby="heading-related" class="pt-4 border-t border-slate-200">
                <div class="flex items-center justify-between mb-6">
                    <h2 id="heading-related" class="text-xl font-extrabold text-[#0F172A] section-heading-bar">
                        Productos Relacionados y Complementarios
                    </h2>
                    @if($product->category)
                        <a href="{{ route('catalog.index', ['category' => $product->category->slug]) }}" class="text-xs sm:text-sm font-bold text-[#1D4ED8] hover:underline">
                            Ver más en {{ $product->category->name }} &rarr;
                        </a>
                    @endif
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                    @foreach($relatedProducts as $related)
                        <x-product-card :product="$related" viewMode="grid" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    <!-- Modal de Solicitud de Cotización Individual Rápida -->
    <div id="quick-quote-modal"
         class="hidden fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true"
         aria-labelledby="quick-quote-modal-title">
        <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs" data-quick-quote-close></div>

        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl border border-slate-200 max-w-xl w-full shadow-2xl overflow-hidden">
                <div class="bg-[#0F172A] text-white px-6 py-4 flex items-center justify-between border-b-4 border-[#DC2626]">
                    <div>
                        <h2 id="quick-quote-modal-title" class="text-base font-extrabold uppercase tracking-wide">
                            Cotización Rápida Individual
                        </h2>
                        <p class="text-xs text-slate-300 font-mono mt-0.5">
                            <span data-product-active-sku>{{ $displaySku }}</span> — <span data-quick-quote-product-name>{{ $currentVariant ? ($currentVariant->name ?: $product->name.' — '.$currentVariant->variant_label) : $product->name }}</span>
                        </p>
                    </div>
                    <button type="button" data-quick-quote-close aria-label="Cerrar ventana" class="p-1.5 text-slate-300 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('quote.submit') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="request_type" value="single">
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="variant_id" value="{{ $currentVariant?->id }}" data-active-variant-input>

                    <!-- Honeypot Antispam -->
                    <div class="hidden" aria-hidden="true">
                        <label for="quick-quote-hp">No llenar este campo</label>
                        <input id="quick-quote-hp" type="text" name="website_hp" value="" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-3.5 rounded-lg border border-slate-200">
                        <div>
                            <label for="qq-quantity" class="block text-xs font-bold text-slate-700 mb-1">
                                Cantidad requerida (<span data-product-active-unit-label>{{ $displayUnit }}</span>) *
                            </label>
                            <input id="qq-quantity" type="number" name="quantity" value="1" min="1" step="1" required
                                   class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-bold tabular-nums">
                        </div>
                        <div>
                            <label for="qq-item-notes" class="block text-xs font-bold text-slate-700 mb-1">
                                Especificación del ítem
                            </label>
                            <input id="qq-item-notes" type="text" name="item_notes" placeholder="Opcional..."
                                   class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="qq-name" class="block text-xs font-bold text-slate-700 mb-1">Nombre de Contacto *</label>
                            <input id="qq-name" type="text" name="contact_name" value="{{ old('contact_name', auth()->user()?->name) }}" required
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label for="qq-phone" class="block text-xs font-bold text-slate-700 mb-1">Teléfono / WhatsApp *</label>
                            <input id="qq-phone" type="tel" name="contact_phone" value="{{ old('contact_phone', auth()->user()?->phone) }}" required
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="qq-email" class="block text-xs font-bold text-slate-700 mb-1">Correo Electrónico *</label>
                            <input id="qq-email" type="email" name="contact_email" value="{{ old('contact_email', auth()->user()?->email) }}" required
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label for="qq-ruc" class="block text-xs font-bold text-slate-700 mb-1">RUC (11 dígitos, opcional)</label>
                            <input id="qq-ruc" type="text" name="ruc" maxlength="11" pattern="\d{11}" value="{{ old('ruc', auth()->user()?->ruc) }}" placeholder="Ej. 20554433221"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono">
                        </div>
                    </div>

                    <div>
                        <label for="qq-company" class="block text-xs font-bold text-slate-700 mb-1">Empresa / Razón Social (opcional)</label>
                        <input id="qq-company" type="text" name="company_name" value="{{ old('company_name', auth()->user()?->company) }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label for="qq-obs" class="block text-xs font-bold text-slate-700 mb-1">Observaciones Generales</label>
                        <textarea id="qq-obs" name="customer_observations" rows="2" placeholder="Lugar de entrega, requerimiento adicional..."
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('customer_observations') }}</textarea>
                    </div>

                    <div class="flex items-start gap-2.5 pt-1">
                        <input id="qq-privacy" type="checkbox" name="privacy_accepted" value="1" required
                               class="mt-1 h-4 w-4 rounded border-slate-300 text-[#1E3A8A] focus:ring-[#1D4ED8]">
                        <label for="qq-privacy" class="text-xs text-slate-600 leading-relaxed">
                            He leído y acepto la <a href="{{ route('pages.privacy') }}" target="_blank" class="text-[#1D4ED8] underline font-semibold">Política de Privacidad (Ley N° 29733)</a> y las <a href="{{ route('pages.quote-conditions') }}" target="_blank" class="text-[#1D4ED8] underline font-semibold">Condiciones de Cotización</a>. *
                        </label>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-3">
                        <button type="button" data-quick-quote-close class="px-4 py-2.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100">
                            Cancelar
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#DC2626] hover:bg-[#B91C1C] text-white text-xs font-extrabold uppercase tracking-wider">
                            Enviar Solicitud Formal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.SanLuisAnalytics) {
                    window.SanLuisAnalytics.trackViewItem(@json($viewItemPayload));
                }
            });
        </script>
    @endpush
@endsection
