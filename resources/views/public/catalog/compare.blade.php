@extends('layouts.public')

@section('content')
    @php
        use App\Models\SiteSetting;
        use App\Services\WhatsAppService;

        $showAvailability = (bool) SiteSetting::getValue('commercial_show_availability_badge', true);
        $whatsAppService = app(WhatsAppService::class);
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-breadcrumb :items="$breadcrumbs" />

        <!-- Page Header -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6 mb-6 shadow-2xs">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F172A] section-heading-bar">
                            Comparador Técnico de Productos
                        </h1>
                        <span class="font-mono text-xs font-extrabold uppercase bg-blue-50 text-[#1E3A8A] border border-blue-200 px-3 py-1 rounded-full tabular-nums">
                            {{ $products->count() }} de {{ $maxProducts }} seleccionados
                        </span>
                    </div>
                    <p class="text-sm text-slate-600 mt-1.5 pl-4">
                        Compara códigos SKU, medidas, materiales, especificaciones técnicas y disponibilidad lado a lado antes de armar tu cotización.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    @if($products->isNotEmpty())
                        <button type="button"
                                data-copy-share-url="{{ $shareUrl }}"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold uppercase tracking-wider transition-colors">
                            <svg class="w-4 h-4 text-[#1E3A8A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                            <span>Copiar enlace comparativo</span>
                        </button>

                        <form action="{{ route('quote.add') }}" method="POST" data-ajax-quote-form>
                            @csrf
                            @foreach($products as $prod)
                                <input type="hidden" name="product_ids[]" value="{{ $prod->id }}">
                            @endforeach
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#DC2626] hover:bg-[#B91C1C] text-white text-xs font-extrabold uppercase tracking-wider shadow-xs transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/></svg>
                                <span>Cotizar todos ({{ $products->count() }})</span>
                            </button>
                        </form>

                        <form action="{{ route('catalog.compare.clear') }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 text-[#DC2626] text-xs font-bold uppercase tracking-wider transition-colors">
                                <span>Vaciar</span>
                            </button>
                        </form>
                    @endif

                    @if($products->count() < $maxProducts)
                        <a href="{{ route('catalog.index') }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#1E3A8A] hover:bg-[#1D4ED8] text-white text-xs font-bold uppercase tracking-wider transition-colors">
                            <span>+ Añadir producto</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        @if($products->isEmpty())
            <!-- Empty Comparator State -->
            <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center max-w-2xl mx-auto shadow-2xs space-y-5">
                <div class="w-16 h-16 rounded-full bg-blue-50 text-[#1E3A8A] mx-auto flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-extrabold text-[#0F172A]">Aún no has seleccionado productos para comparar</h2>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Selecciona hasta <strong>{{ $maxProducts }} productos</strong> desde el catálogo técnico haciendo clic en el botón <strong>"Comparar"</strong> de cualquier tarjeta o ficha técnica para evaluar sus medidas, materiales y especificaciones lado a lado.
                </p>
                <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                    <a href="{{ route('catalog.index') }}"
                       class="px-6 py-3 rounded-lg bg-[#1E3A8A] hover:bg-[#1D4ED8] text-white font-bold text-xs uppercase tracking-wider transition-colors">
                        Ir al Catálogo Técnico
                    </a>
                    <a href="{{ route('quote.index') }}"
                       class="px-6 py-3 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 font-bold text-xs uppercase tracking-wider transition-colors">
                        Ver Mi Lista de Cotización
                    </a>
                </div>
            </div>
        @else
            <!-- Side-by-Side Technical Comparison Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[720px]">
                        <thead>
                            <tr class="border-b-2 border-slate-200 bg-slate-50/70">
                                <th scope="col" class="w-56 min-w-[200px] p-4 sm:p-5 align-top bg-[#0F172A] text-white">
                                    <span class="block text-xs font-extrabold uppercase tracking-wider text-amber-400">Matriz Comparativa</span>
                                    <span class="block text-sm font-bold mt-1">Atributo / Especificación</span>
                                    <p class="text-xs text-slate-300 font-normal mt-2 leading-relaxed">
                                        Revisa las diferencias técnicas entre códigos SKU y añade los requeridos a tu pliego de cotización.
                                    </p>
                                </th>

                                @foreach($products as $product)
                                    <th scope="col" class="w-64 min-w-[240px] p-4 sm:p-5 align-top border-l border-slate-200 bg-white relative">
                                        <div class="flex items-center justify-between gap-2 mb-3">
                                            <span class="font-mono text-xs font-extrabold uppercase bg-slate-900 text-white px-2.5 py-0.5 rounded tabular-nums">
                                                {{ $product->sku }}
                                            </span>

                                            <form action="{{ route('catalog.compare.remove', $product->id) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                        aria-label="Quitar {{ $product->name }} del comparador"
                                                        title="Quitar del comparador"
                                                        class="text-xs font-semibold text-slate-400 hover:text-[#DC2626] inline-flex items-center gap-1 px-2 py-0.5 rounded hover:bg-red-50 transition-colors">
                                                    <span>&times; Quitar</span>
                                                </button>
                                            </form>
                                        </div>

                                        <a href="{{ route('catalog.show', $product->slug) }}"
                                           class="block h-36 rounded-lg bg-slate-50 border border-slate-100 p-3 mb-3 flex items-center justify-center overflow-hidden group">
                                            <img src="{{ $product->primaryImageUrl() }}"
                                                 alt="{{ $product->name }} - {{ $product->sku }}"
                                                 class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform">
                                        </a>

                                        <h2 class="text-sm font-extrabold text-[#0F172A] hover:text-[#1D4ED8] leading-snug mb-3 min-h-[2.5rem]">
                                            <a href="{{ route('catalog.show', $product->slug) }}">
                                                {{ $product->name }}
                                            </a>
                                        </h2>

                                        <div class="flex items-center gap-2">
                                            <form action="{{ route('quote.add') }}" method="POST" data-ajax-quote-form class="flex-1">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit"
                                                        class="w-full bg-[#DC2626] hover:bg-[#B91C1C] text-white font-bold py-2 px-3 rounded-lg text-xs uppercase tracking-wider transition-colors">
                                                    + Cotizar SKU
                                                </button>
                                            </form>

                                            <x-whatsapp-button
                                                variant="inline"
                                                :url="$whatsAppService->productInquiryUrl($product)"
                                                label=""
                                                ctaLocation="catalog_compare"
                                                contextType="single_product"
                                                :sku="$product->sku"
                                                :productName="$product->name"
                                                class="p-2"
                                                title="Consultar este SKU por WhatsApp" />
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 text-xs sm:text-sm">
                            <!-- Row: Precio Referencial -->
                            <tr class="odd:bg-white even:bg-slate-50/70">
                                <th scope="row" class="p-3.5 sm:p-4 font-bold text-slate-700 bg-slate-50 border-r border-slate-200">
                                    Precio / Modalidad
                                </th>
                                @foreach($products as $product)
                                    <td class="p-3.5 sm:p-4 border-l border-slate-200">
                                        @if($product->shouldShowPrice() && $product->formattedPrice())
                                            <span class="text-base font-extrabold text-[#0F172A] tabular-nums block">{{ $product->formattedPrice() }}</span>
                                            <span class="text-[11px] text-slate-500">Ref. por {{ $product->sale_unit }} (Inc. IGV)</span>
                                        @else
                                            <span class="inline-block text-xs font-bold text-[#1E3A8A] bg-[#EFF6FF] border border-[#BFDBFE] px-2.5 py-1 rounded">
                                                Bajo cotización por volumen / unidad
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Row: Disponibilidad -->
                            @if($showAvailability)
                                <tr class="odd:bg-white even:bg-slate-50/70">
                                    <th scope="row" class="p-3.5 sm:p-4 font-bold text-slate-700 bg-slate-50 border-r border-slate-200">
                                        Disponibilidad de Stock
                                    </th>
                                    @foreach($products as $product)
                                        <td class="p-3.5 sm:p-4 border-l border-slate-200">
                                            <span class="inline-block text-xs font-bold px-2.5 py-1 rounded border {{ $product->availabilityBadgeClass() }}">
                                                {{ $product->availabilityLabel() }}
                                            </span>
                                        </td>
                                    @endforeach
                                </tr>
                            @endif

                            <!-- Row: Marca -->
                            <tr class="odd:bg-white even:bg-slate-50/70">
                                <th scope="row" class="p-3.5 sm:p-4 font-bold text-slate-700 bg-slate-50 border-r border-slate-200">
                                    Marca
                                </th>
                                @foreach($products as $product)
                                    <td class="p-3.5 sm:p-4 border-l border-slate-200 font-semibold text-slate-900">
                                        @if($product->brand)
                                            <a href="{{ route('catalog.index', ['brand' => $product->brand->slug]) }}" class="text-[#1D4ED8] hover:underline">
                                                {{ $product->brand->name }}
                                            </a>
                                        @else
                                            <span class="text-slate-500">Línea Estándar</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Row: Categoría / Subcategoría -->
                            <tr class="odd:bg-white even:bg-slate-50/70">
                                <th scope="row" class="p-3.5 sm:p-4 font-bold text-slate-700 bg-slate-50 border-r border-slate-200">
                                    Categoría Técnica
                                </th>
                                @foreach($products as $product)
                                    <td class="p-3.5 sm:p-4 border-l border-slate-200 text-slate-800">
                                        @if($product->category)
                                            <span class="font-semibold">{{ $product->category->name }}</span>
                                        @endif
                                        @if($product->subcategory)
                                            <span class="block text-xs text-slate-500 mt-0.5">&rsaquo; {{ $product->subcategory->name }}</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Row: Unidad de Venta -->
                            <tr class="odd:bg-white even:bg-slate-50/70">
                                <th scope="row" class="p-3.5 sm:p-4 font-bold text-slate-700 bg-slate-50 border-r border-slate-200">
                                    Unidad de Venta
                                </th>
                                @foreach($products as $product)
                                    <td class="p-3.5 sm:p-4 border-l border-slate-200 font-bold text-slate-900">
                                        {{ $product->sale_unit }}
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Row: Medidas / Dimensiones -->
                            <tr class="odd:bg-white even:bg-slate-50/70">
                                <th scope="row" class="p-3.5 sm:p-4 font-bold text-slate-700 bg-slate-50 border-r border-slate-200">
                                    Medidas / Dimensiones
                                </th>
                                @foreach($products as $product)
                                    <td class="p-3.5 sm:p-4 border-l border-slate-200 font-mono text-xs text-slate-900 tabular-nums">
                                        {{ $product->dimensions ?: '—' }}
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Row: Material Base -->
                            <tr class="odd:bg-white even:bg-slate-50/70">
                                <th scope="row" class="p-3.5 sm:p-4 font-bold text-slate-700 bg-slate-50 border-r border-slate-200">
                                    Material de Fabricación
                                </th>
                                @foreach($products as $product)
                                    <td class="p-3.5 sm:p-4 border-l border-slate-200 text-slate-800">
                                        {{ $product->material ?: '—' }}
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Dynamic ProductSpecification Rows Aligned by Key -->
                            @if(!empty($specKeys))
                                <tr class="bg-[#1E3A8A]/10">
                                    <th colspan="{{ $products->count() + 1 }}" class="px-4 py-2 text-xs font-extrabold uppercase tracking-wider text-[#1E3A8A]">
                                        Especificaciones Técnicas Detalladas
                                    </th>
                                </tr>
                                @foreach($specKeys as $specKey)
                                    <tr class="odd:bg-white even:bg-slate-50/70">
                                        <th scope="row" class="p-3.5 sm:p-4 font-bold text-slate-700 bg-slate-50 border-r border-slate-200">
                                            {{ $specKey }}
                                        </th>
                                        @foreach($products as $product)
                                            <td class="p-3.5 sm:p-4 border-l border-slate-200 text-slate-900 font-medium tabular-nums">
                                                {{ $productSpecsMap[$product->id][$specKey] ?? '—' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endif

                            <!-- Row: Ficha Técnica PDF -->
                            <tr class="bg-slate-50">
                                <th scope="row" class="p-3.5 sm:p-4 font-bold text-slate-700 bg-slate-100 border-r border-slate-200">
                                    Documentación Técnica
                                </th>
                                @foreach($products as $product)
                                    <td class="p-3.5 sm:p-4 border-l border-slate-200">
                                        <a href="{{ route('catalog.download-pdf', $product->slug) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-blue-50 text-[#1D4ED8] border border-blue-200 font-bold text-xs uppercase tracking-wider transition-colors">
                                            <svg class="w-4 h-4 text-[#DC2626]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>Descargar Ficha PDF</span>
                                        </a>
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
