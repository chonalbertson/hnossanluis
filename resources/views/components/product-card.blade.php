@props([
    'product',
    'viewMode' => 'grid',
    'promoBadge' => false,
])

@php
    use App\Models\SiteSetting;
    use App\Services\WhatsAppService;

    $showAvailability = (bool) SiteSetting::getValue('commercial_show_availability_badge', true);
    $showPrice = $product->shouldShowPrice();
    $formattedPrice = $showPrice ? $product->formattedPrice() : null;
    $primaryThumb = $product->primaryThumbnailUrl();
    $secondaryImage = $product->secondaryThumbnailUrl();
    $whatsappUrl = app(WhatsAppService::class)->productInquiryUrl($product);

    $availabilityDotClass = match ($product->availability_status) {
        'available' => 'bg-emerald-500',
        'on_request' => 'bg-amber-500',
        'out_of_stock' => 'bg-rose-500',
        default => 'bg-slate-400',
    };

    $hasCardVariants = $product->relationLoaded('variants') && $product->variants->isNotEmpty();
    $firstCardVariant = $hasCardVariants ? $product->variants->first() : null;
    $initialCardSku = $firstCardVariant ? $firstCardVariant->sku : $product->sku;

    $beginQuotePayload = json_encode([
        'cta_location' => $viewMode === 'list' ? 'catalog_list' : 'product_card',
        'item_id' => $initialCardSku,
        'item_name' => $product->name,
        'item_category' => $product->category?->name ?? '',
        'item_brand' => $product->brand?->name ?? '',
        'unit_of_measure' => $product->sale_unit,
        'quantity' => 1,
    ], JSON_UNESCAPED_UNICODE);

    $pdfPayload = json_encode([
        'file_name' => 'Ficha-Tecnica-'.$initialCardSku.'.pdf',
        'file_extension' => 'pdf',
        'link_text' => 'Ficha PDF',
        'item_id' => $initialCardSku,
        'item_name' => $product->name,
        'item_category' => $product->category?->name ?? '',
    ], JSON_UNESCAPED_UNICODE);
@endphp

@if($viewMode === 'list')
    <!-- Redesigned Industrial Technical List Row View -->
    <article data-product-card-root class="bg-white rounded-2xl border border-slate-200/90 hover:border-[#1E3A8A]/40 shadow-2xs hover:shadow-md transition-all duration-200 p-4 sm:p-5 flex flex-col lg:flex-row items-start lg:items-center gap-5 group">
        <!-- Thumbnail with 2nd image hover -->
        <a href="{{ route('catalog.show', $product->slug) }}"
           class="relative w-28 h-28 sm:w-32 sm:h-32 rounded-xl bg-white border border-slate-200 p-2.5 shrink-0 flex items-center justify-center overflow-hidden">
            <img src="{{ $primaryThumb }}"
                 alt="{{ $product->name }} - SKU {{ $initialCardSku }}"
                 width="160"
                 height="160"
                 loading="lazy"
                 decoding="async"
                 class="w-full h-full object-contain transition-all duration-300 {{ $secondaryImage ? 'group-hover:opacity-0' : 'group-hover:scale-105' }}">
            @if($secondaryImage)
                <img src="{{ $secondaryImage }}"
                     alt="{{ $product->name }} - Vista 2"
                     width="160"
                     height="160"
                     loading="lazy"
                     decoding="async"
                     class="absolute inset-0 w-full h-full object-contain p-2.5 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            @endif
        </a>

        <!-- Technical Identity & Attributes -->
        <div class="flex-1 min-w-0 space-y-2.5 w-full">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-mono text-xs font-extrabold uppercase tracking-wider bg-[#0F172A] text-white px-2.5 py-0.5 rounded-md tabular-nums">
                    SKU: <span data-card-sku>{{ $initialCardSku }}</span>
                </span>

                @if($product->brand)
                    <a href="{{ route('catalog.index', ['brand' => $product->brand->slug, 'view' => 'list']) }}"
                       class="text-xs font-extrabold uppercase tracking-wider text-[#1E3A8A] bg-blue-50 hover:bg-blue-100 border border-blue-200 px-2.5 py-0.5 rounded-md transition-colors">
                        {{ $product->brand->name }}
                    </a>
                @endif

                @if($showAvailability)
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-0.5 rounded-md border {{ $product->availabilityBadgeClass() }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $availabilityDotClass }}"></span>
                        {{ $product->availabilityLabel() }}
                    </span>
                @endif

                @if($hasCardVariants)
                    <span class="text-[10px] font-extrabold uppercase tracking-wider bg-blue-900 text-amber-300 px-2 py-0.5 rounded-md">
                        {{ $product->variants->count() }} opciones
                    </span>
                @endif

                @if($product->is_featured)
                    <span class="text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300 px-2 py-0.5 rounded-md">
                        Destacado
                    </span>
                @endif
            </div>

            <div>
                @if($product->category)
                    <div class="text-[11px] font-semibold text-slate-500 mb-0.5">
                        {{ $product->category->name }}
                        @if($product->subcategory)
                            <span class="text-slate-400">/</span> <span class="text-[#1D4ED8]">{{ $product->subcategory->name }}</span>
                        @endif
                    </div>
                @endif
                <h3 class="text-base sm:text-lg font-extrabold text-[#0F172A] group-hover:text-[#1D4ED8] transition-colors leading-snug">
                    <a href="{{ route('catalog.show', $product->slug) }}">{{ $product->name }}</a>
                </h3>
            </div>

            @if($product->short_description)
                <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">{{ strip_tags((string) $product->short_description) }}</p>
            @endif

            <!-- Engineering Spec Strip -->
            <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-800 font-semibold px-2.5 py-1 rounded-md border border-slate-200/80">
                    <span class="text-slate-500 font-normal">Presentación:</span>
                    <span data-card-unit>{{ $firstCardVariant ? ($firstCardVariant->sale_unit ?: $product->sale_unit) : $product->sale_unit }}</span>
                </span>
                @if($firstCardVariant && $firstCardVariant->packagingSummary())
                    <span class="inline-flex items-center gap-1 bg-blue-50 text-[#1E3A8A] font-semibold px-2.5 py-1 rounded-md border border-blue-200/80 tabular-nums">
                        <span data-card-packaging>{{ $firstCardVariant->packagingSummary() }}</span>
                    </span>
                @elseif($product->dimensions)
                    <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-800 font-semibold px-2.5 py-1 rounded-md border border-slate-200/80 tabular-nums">
                        <span class="text-slate-500 font-normal">Medida:</span>
                        <span data-card-packaging>{{ $product->dimensions }}</span>
                    </span>
                @endif
                @if($product->material)
                    <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-800 font-semibold px-2.5 py-1 rounded-md border border-slate-200/80">
                        <span class="text-slate-500 font-normal">Material:</span> {{ $product->material }}
                    </span>
                @endif

                <a href="{{ route('catalog.download-pdf', $product->slug) }}"
                   data-ga-event="file_download"
                   data-ga-params="{{ $pdfPayload }}"
                   class="inline-flex items-center gap-1 text-[#1E3A8A] hover:text-[#DC2626] bg-slate-50 hover:bg-red-50 border border-slate-200 hover:border-red-200 font-bold px-2.5 py-1 rounded-md transition-colors">
                    <svg class="w-3.5 h-3.5 text-[#DC2626]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Ficha PDF</span>
                </a>
            </div>
        </div>

        <!-- Right Conversion Column (Quote-first unless price enabled by Admin) -->
        <div class="w-full lg:w-80 shrink-0 flex flex-col justify-between gap-2.5 border-t lg:border-t-0 lg:border-l border-slate-200 pt-3.5 lg:pt-0 lg:pl-5">
            @if($showPrice && $formattedPrice)
                <div class="bg-emerald-50/70 border border-emerald-200 rounded-xl px-3.5 py-2 flex items-baseline justify-between">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-800 block">Precio Unitario</span>
                        <span class="text-xl font-extrabold text-[#0F172A] tabular-nums">{{ $formattedPrice }}</span>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-600">x {{ $product->sale_unit }}</span>
                </div>
            @else
                <div class="bg-slate-50 border border-slate-200/90 rounded-xl px-3 py-2 flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-[#1E3A8A]/10 text-[#1E3A8A] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <span class="block text-[11px] font-extrabold uppercase tracking-wide text-[#0F172A]">Atención por Cotización</span>
                        <span class="block text-[11px] text-slate-500 truncate">Precios mayoristas y por unidad</span>
                    </div>
                </div>
            @endif

            <form action="{{ route('quote.add') }}" method="POST" data-ajax-quote-form @if($hasCardVariants) data-requires-variant="true" @endif class="space-y-2">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                @if($hasCardVariants)
                    <div>
                        <label for="variant-list-{{ $product->id }}" class="block text-[10px] font-extrabold uppercase tracking-wider text-[#1E3A8A] mb-1">
                            Elegir producto a cotizar:
                        </label>
                        <select id="variant-list-{{ $product->id }}"
                                name="variant_id"
                                data-card-variant-select
                                class="w-full rounded-lg border-2 border-blue-200 bg-blue-50/40 px-2.5 py-1.5 text-xs font-extrabold text-[#0F172A] focus:border-[#1E3A8A] focus:bg-white focus:outline-none cursor-pointer">
                            <option value="">— Elige un producto para cotizar —</option>
                            @foreach($product->variants as $v)
                                @php
                                    $vFullName = ($v->name && mb_strtolower(trim($v->name)) !== mb_strtolower(trim((string) $v->variant_label)))
                                        ? trim($v->name).' — '.trim((string) $v->variant_label)
                                        : trim((string) ($v->name ?: $v->variant_label ?: $product->name));
                                @endphp
                                <option value="{{ $v->id }}"
                                        data-variant-sku="{{ $v->sku }}"
                                        data-variant-label="{{ $vFullName }}"
                                        data-variant-unit="{{ $v->sale_unit ?: $product->sale_unit }}"
                                        data-variant-packaging="{{ $v->packagingSummary() ?: ($v->dimensions ?: $v->variant_label) }}">
                                    {{ $v->sku }} — {{ $vFullName }}@if($v->carton_qty) (Cajón: {{ $v->carton_qty }} {{ $v->sale_unit ?: $product->sale_unit }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex items-center gap-2">
                    <label class="sr-only" for="qty-list-{{ $product->id }}">Cantidad para {{ $product->name }}</label>
                    <input id="qty-list-{{ $product->id }}"
                           type="number"
                           name="quantity"
                           value="1"
                           min="1"
                           max="9999"
                           aria-label="Cantidad"
                           class="w-16 rounded-xl border border-slate-300 bg-slate-50 px-2 py-2 text-center text-xs font-extrabold text-slate-900 tabular-nums focus:border-[#1E3A8A] focus:bg-white focus:outline-none">
                    <button type="submit"
                            data-card-quote-btn
                            data-enabled-label="Cotizar"
                            data-disabled-label="Elige un producto para cotizar"
                            data-ga-event="begin_quote"
                            data-ga-params="{{ $beginQuotePayload }}"
                            @disabled($hasCardVariants)
                            class="flex-1 font-extrabold py-2.5 px-3.5 rounded-xl text-xs uppercase tracking-wider transition-colors flex items-center justify-center gap-1.5 {{ $hasCardVariants ? 'bg-slate-200 text-slate-500 border border-slate-300 cursor-not-allowed shadow-none' : 'bg-[#DC2626] hover:bg-[#B91C1C] text-white shadow-2xs cursor-pointer' }}">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M12 4v16m8-8H4"/></svg>
                        <span data-card-quote-text>{{ $hasCardVariants ? 'Elige un producto para cotizar' : 'Cotizar' }}</span>
                    </button>
                </div>
            </form>

            <div class="flex items-center gap-2">
                <x-whatsapp-button
                    variant="inline"
                    :url="$whatsappUrl"
                    label="WhatsApp"
                    ctaLocation="catalog_list"
                    contextType="single_product"
                    :sku="$initialCardSku"
                    :productName="$product->name"
                    class="w-full py-1.5 px-3 text-[11px] rounded-lg justify-center"
                    title="Consultar este SKU por WhatsApp" />
            </div>
        </div>
    </article>
@else
    <!-- Redesigned Industrial B2B/B2C Grid Card View -->
    <article data-product-card-root class="min-w-0 bg-white rounded-xl sm:rounded-2xl border border-slate-200/90 hover:border-[#1E3A8A]/50 shadow-2xs hover:shadow-lg transition-all duration-200 flex flex-col justify-between overflow-hidden group">
        <div>
            <!-- Top Technical Identity Bar -->
            <div class="px-2.5 py-2 sm:px-3.5 sm:py-2.5 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between gap-1.5 sm:gap-2">
                @if($product->brand)
                    <a href="{{ route('catalog.index', ['brand' => $product->brand->slug]) }}"
                       class="min-w-0 text-[10px] sm:text-[11px] font-extrabold uppercase tracking-wider text-[#1E3A8A] hover:text-[#DC2626] truncate transition-colors">
                        {{ $product->brand->name }}
                    </a>
                @else
                    <span class="min-w-0 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-500 truncate">San Luis</span>
                @endif

                <span class="font-mono text-[9px] sm:text-[11px] font-extrabold uppercase bg-[#0F172A] text-white px-1.5 sm:px-2 py-0.5 rounded tabular-nums shrink-0 max-w-[60%] truncate">
                    <span class="hidden sm:inline">SKU: </span><span data-card-sku>{{ $initialCardSku }}</span>
                </span>
            </div>

            <!-- High-Resolution Product Image Stage -->
            <div class="relative aspect-square bg-white p-3 sm:p-4 border-b border-slate-100 flex items-center justify-center overflow-hidden">
                <!-- Top-Left Status Badges -->
                <div class="absolute top-2 left-2 sm:top-2.5 sm:left-2.5 z-10 flex flex-col gap-1 max-w-[75%]">
                    @if($promoBadge)
                        <span class="banner-shine-effect inline-flex items-center gap-1 bg-gradient-to-r from-[#DC2626] via-[#B91C1C] to-amber-500 text-white text-[9px] sm:text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md shadow-sm w-fit">
                            <svg class="w-3 h-3 shrink-0 text-amber-200" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                            <span>En Promoción</span>
                        </span>
                    @endif
                    @if($showAvailability)
                        <span class="inline-flex items-center gap-1 sm:gap-1.5 text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded-md border shadow-2xs backdrop-blur-xs w-fit {{ $product->availabilityBadgeClass() }}">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $availabilityDotClass }}"></span>
                            <span class="truncate">{{ $product->availabilityLabel() }}</span>
                        </span>
                    @endif
                    @if($hasCardVariants)
                        <span class="bg-[#0F172A] text-amber-400 text-[9px] sm:text-[10px] font-extrabold uppercase tracking-wider px-1.5 sm:px-2 py-0.5 rounded-md shadow-2xs w-fit">
                            {{ $product->variants->count() }} opciones
                        </span>
                    @endif
                    @if($product->is_featured && !$promoBadge)
                        <span class="bg-amber-400 text-slate-950 text-[9px] sm:text-[10px] font-extrabold uppercase tracking-wider px-1.5 sm:px-2 py-0.5 rounded-md shadow-2xs w-fit">
                            Destacado
                        </span>
                    @endif
                </div>

                <!-- Top-Right Quick Utility Actions (PDF Datasheet) -->
                <div class="absolute top-2 right-2 sm:top-2.5 sm:right-2.5 z-10 flex flex-col gap-1 sm:gap-1.5">
                    <a href="{{ route('catalog.download-pdf', $product->slug) }}"
                       data-ga-event="file_download"
                       data-ga-params="{{ $pdfPayload }}"
                       title="Descargar Ficha Técnica PDF ({{ $initialCardSku }})"
                       aria-label="Descargar Ficha Técnica PDF ({{ $initialCardSku }})"
                       class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white/95 hover:bg-red-50 text-slate-700 hover:text-[#DC2626] border border-slate-200 hover:border-red-200 shadow-2xs transition-colors flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-[#DC2626]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </a>
                </div>

                <!-- Product Link & High-Res Image (with 2nd image hover transition) -->
                <a href="{{ route('catalog.show', $product->slug) }}" class="w-full h-full flex items-center justify-center relative">
                    <img src="{{ $primaryThumb }}"
                         alt="{{ $product->name }} - SKU {{ $initialCardSku }}"
                         width="400"
                         height="400"
                         loading="lazy"
                         decoding="async"
                         class="max-h-full max-w-full object-contain transition-all duration-300 {{ $secondaryImage ? 'group-hover:opacity-0' : 'group-hover:scale-105' }}">

                    @if($secondaryImage)
                        <img src="{{ $secondaryImage }}"
                             alt="{{ $product->name }} - Vista técnica secundaria"
                             width="400"
                             height="400"
                             loading="lazy"
                             decoding="async"
                             class="absolute inset-0 w-full h-full object-contain opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    @endif
                </a>
            </div>

            <!-- Product Body: Category, Title & Compact Engineering Spec Strip -->
            <div class="p-2.5 sm:p-4 space-y-2 sm:space-y-2.5">
                @if($product->category)
                    <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-slate-400 truncate">
                        {{ $product->category->name }}
                        @if($product->subcategory)
                            <span class="text-slate-300">/</span> <span class="text-[#1E3A8A]">{{ $product->subcategory->name }}</span>
                        @endif
                    </div>
                @endif

                <h3 class="text-xs sm:text-sm font-extrabold text-[#0F172A] group-hover:text-[#1D4ED8] transition-colors line-clamp-2 min-h-[2rem] sm:min-h-[2.5rem] leading-snug">
                    <a href="{{ route('catalog.show', $product->slug) }}">
                        {{ $product->name }}
                    </a>
                </h3>

                <!-- Compact Technical Spec Strip -->
                <div class="grid grid-cols-2 gap-1 sm:gap-1.5 bg-slate-50 rounded-lg border border-slate-200/80 p-1.5 sm:p-2 text-[10px] sm:text-[11px]">
                    <div class="truncate">
                        <span class="block text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-slate-400">Presentación</span>
                        <span data-card-unit class="font-bold text-slate-800">{{ $firstCardVariant ? ($firstCardVariant->sale_unit ?: $product->sale_unit) : $product->sale_unit }}</span>
                    </div>
                    <div class="truncate border-l border-slate-200 pl-1.5 sm:pl-2">
                        <span class="block text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-slate-400">
                            {{ $hasCardVariants ? 'Empaque / Medida' : ($product->dimensions ? 'Medida / Cap.' : 'Material') }}
                        </span>
                        <span data-card-packaging class="font-bold text-slate-800 tabular-nums">
                            @if($firstCardVariant)
                                {{ $firstCardVariant->packagingSummary() ?: ($firstCardVariant->dimensions ?: $firstCardVariant->variant_label) }}
                            @else
                                {{ $product->dimensions ?: ($product->material ?: 'Uso Técnico') }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Commercial Action Footer (Strictly hides price unless enabled per-product by Admin) -->
        <div class="px-2.5 pb-2.5 sm:px-4 sm:pb-4 pt-2 border-t border-slate-100 space-y-2 sm:space-y-2.5 bg-white">
            @if($showPrice && $formattedPrice)
                <div class="flex flex-wrap items-baseline justify-between gap-x-1 bg-emerald-50/70 border border-emerald-200/90 rounded-lg px-2 sm:px-3 py-1.5">
                    <div class="min-w-0">
                        <span class="text-[9px] sm:text-[10px] font-extrabold uppercase tracking-wider text-emerald-800 block">Precio Unitario</span>
                        <span class="text-sm sm:text-base font-extrabold text-[#0F172A] tabular-nums">{{ $formattedPrice }}</span>
                    </div>
                    <span class="text-[10px] sm:text-[11px] font-semibold text-slate-600 truncate">por {{ $product->sale_unit }}</span>
                </div>
            @endif

            <!-- Variant Selector + Quantity + Add to Quote + WhatsApp -->
            <form action="{{ route('quote.add') }}" method="POST" data-ajax-quote-form @if($hasCardVariants) data-requires-variant="true" @endif class="space-y-2">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                @if($hasCardVariants)
                    <div>
                        <label for="variant-grid-{{ $product->id }}" class="block text-[9px] sm:text-[10px] font-extrabold uppercase tracking-wider text-[#1E3A8A] mb-1">
                            Elegir producto a cotizar:
                        </label>
                        <select id="variant-grid-{{ $product->id }}"
                                name="variant_id"
                                data-card-variant-select
                                class="w-full rounded-lg border-2 border-blue-200 bg-blue-50/50 px-2 py-1.5 text-[11px] font-extrabold text-[#0F172A] focus:border-[#1E3A8A] focus:bg-white focus:outline-none cursor-pointer">
                            <option value="">— Elige un producto para cotizar —</option>
                            @foreach($product->variants as $v)
                                @php
                                    $vFullName = ($v->name && mb_strtolower(trim($v->name)) !== mb_strtolower(trim((string) $v->variant_label)))
                                        ? trim($v->name).' — '.trim((string) $v->variant_label)
                                        : trim((string) ($v->name ?: $v->variant_label ?: $product->name));
                                @endphp
                                <option value="{{ $v->id }}"
                                        data-variant-sku="{{ $v->sku }}"
                                        data-variant-label="{{ $vFullName }}"
                                        data-variant-unit="{{ $v->sale_unit ?: $product->sale_unit }}"
                                        data-variant-packaging="{{ $v->packagingSummary() ?: ($v->dimensions ?: $v->variant_label) }}">
                                    {{ $v->sku }} — {{ $vFullName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex items-center gap-1.5">
                    <label class="sr-only" for="qty-grid-{{ $product->id }}">Cantidad para {{ $product->name }}</label>
                    <input id="qty-grid-{{ $product->id }}"
                           type="number"
                           name="quantity"
                           value="1"
                           min="1"
                           max="9999"
                           aria-label="Cantidad"
                           title="Cantidad a cotizar ({{ $product->sale_unit }})"
                           class="hidden sm:block w-13 rounded-lg border border-slate-300 bg-slate-50 px-1.5 py-2 text-center text-xs font-extrabold text-slate-900 tabular-nums focus:border-[#1E3A8A] focus:bg-white focus:outline-none">
                    <button type="submit"
                            data-card-quote-btn
                            data-enabled-label="Cotizar"
                            data-disabled-label="Elige un producto para cotizar"
                            data-ga-event="begin_quote"
                            data-ga-params="{{ $beginQuotePayload }}"
                            @disabled($hasCardVariants)
                            class="flex-1 min-w-0 font-extrabold py-2 px-2 sm:px-3 rounded-lg text-[10px] sm:text-xs uppercase tracking-wider transition-colors flex items-center justify-center gap-1 {{ $hasCardVariants ? 'bg-slate-200 text-slate-500 border border-slate-300 cursor-not-allowed shadow-none' : 'bg-[#DC2626] hover:bg-[#B91C1C] text-white shadow-2xs cursor-pointer' }}">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M12 4v16m8-8H4"/></svg>
                        <span data-card-quote-text class="truncate">{{ $hasCardVariants ? 'Elige un producto para cotizar' : 'Cotizar' }}</span>
                    </button>

                    <x-whatsapp-button
                        variant="inline"
                        :url="$whatsappUrl"
                        label=""
                        ctaLocation="product_card"
                        contextType="single_product"
                        :sku="$initialCardSku"
                        :productName="$product->name"
                        class="w-[34px] h-[34px] sm:w-9 sm:h-9 rounded-lg shrink-0"
                        aria-label="Consultar {{ $product->name }} por WhatsApp"
                        title="Consultar este SKU por WhatsApp" />
                </div>
            </form>
        </div>
    </article>
@endif
