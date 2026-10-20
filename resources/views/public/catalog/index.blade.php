@extends('layouts.public')

@section('content')
    @php
        $hasActiveFilters = $filters['q'] !== ''
            || !empty($filters['category'])
            || !empty($filters['subcategory'])
            || !empty($filters['brand'])
            || !empty($filters['availability'])
            || !empty($filters['material'])
            || !empty($filters['spec_key']);

        $availabilityOptions = [
            'available' => 'Disponible',
            'on_request' => 'Bajo consulta',
            'out_of_stock' => 'Sin stock',
        ];

        $sortOptions = [
            'relevance' => 'Aleatorio',
            'name_asc' => 'Nombre (A - Z)',
            'name_desc' => 'Nombre (Z - A)',
            'sku_asc' => 'Código SKU (A - Z)',
            'newest' => 'Más recientes',
        ];
    @endphp

    <!-- Industrial Hero Header Banner -->
    <section class="bg-gradient-to-br from-[#0F172A] via-[#1E293B] to-[#1E3A8A] text-white border-b-4 border-[#DC2626] relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 pointer-events-none"
             style="background-image: radial-gradient(#94A3B8 1px, transparent 1px); background-size: 20px 20px;"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-7 sm:py-9 relative z-10">
            <div class="mb-3 [&_nav]:mb-0 [&_ol]:text-slate-300 [&_a]:text-slate-300 [&_a:hover]:text-white [&_span]:text-white">
                <x-breadcrumb :items="$breadcrumbs" />
            </div>

            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                <div class="max-w-3xl space-y-2.5">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-[11px] font-extrabold uppercase tracking-wider text-amber-300">
                        <span class="w-2 h-2 rounded-full bg-[#DC2626]"></span>
                        <span>Catálogo Industrial B2B / B2C • Atención por Cotización</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white leading-tight">
                        @if($activeSubcategory)
                            {{ $activeSubcategory->name }}
                        @elseif($activeCategory)
                            {{ $activeCategory->name }}
                        @elseif($activeBrand)
                            Catálogo Oficial: Marca {{ $activeBrand->name }}
                        @elseif($filters['q'] !== '')
                            Resultados Técnicos para "{{ $filters['q'] }}"
                        @else
                            Catálogo Técnico de Productos y Herrajes
                        @endif
                    </h1>

                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-2xl">
                        @if($activeSubcategory && $activeSubcategory->description)
                            {{ $activeSubcategory->description }}
                        @elseif($activeCategory && $activeCategory->description)
                            {{ $activeCategory->description }}
                        @elseif($activeBrand && $activeBrand->description)
                            {{ $activeBrand->description }}
                        @else
                            Explora nuestro inventario técnico por código SKU, medidas, marcas y especificaciones. Selecciona las cantidades requeridas y solicita tu cotización personalizada al por mayor o menor.
                        @endif
                    </p>
                </div>

                <!-- Right KPI & Action Pills -->
                <div class="flex flex-wrap items-center gap-2.5 self-start lg:self-end shrink-0">
                    <div class="bg-slate-900/80 border border-slate-700 px-3.5 py-2 rounded-xl flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span class="font-mono text-xs font-extrabold text-white tabular-nums">
                            {{ $products->total() }} producto(s) encontrado(s)
                        </span>
                    </div>

                    <a href="{{ route('quote.index') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#DC2626] hover:bg-[#B91C1C] text-white text-xs font-extrabold uppercase tracking-wider shadow-md transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Ver Mi Cotización</span>
                    </a>

                    <button type="button"
                            data-filter-drawer-toggle
                            class="lg:hidden inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white text-[#0F172A] text-xs font-extrabold uppercase tracking-wider shadow-sm">
                        <svg class="w-4 h-4 text-[#1E3A8A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span>Filtros Técnicos</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-7">
        <!-- Quick Subcategory / Category Navigation Strip -->
        @if($activeCategory && $activeCategory->children->isNotEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 shadow-2xs">
                <div class="flex items-center justify-between gap-2 mb-3">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-[#0F172A] flex items-center gap-2">
                        <span class="w-1.5 h-4 bg-[#1E3A8A] rounded-full"></span>
                        Subcategorías en {{ $activeCategory->name }}
                    </span>
                    @if($activeSubcategory)
                        <a href="{{ route('catalog.index', ['category' => $activeCategory->slug, 'view' => $viewMode]) }}"
                           class="text-xs font-bold text-[#1D4ED8] hover:underline">
                            Ver todas en {{ $activeCategory->name }}
                        </a>
                    @endif
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('catalog.index', ['category' => $activeCategory->slug, 'view' => $viewMode]) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-bold border transition-all {{ !$activeSubcategory ? 'bg-[#1E3A8A] text-white border-[#1E3A8A] shadow-2xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:border-blue-300 hover:text-[#1E3A8A]' }}">
                        Todas ({{ $activeCategory->products_count ?? $products->total() }})
                    </a>
                    @foreach($activeCategory->children as $sub)
                        @php
                            $isSubSelected = $activeSubcategory && $activeSubcategory->id === $sub->id;
                        @endphp
                        <a href="{{ route('catalog.index', ['category' => $activeCategory->slug, 'subcategory' => $sub->slug, 'view' => $viewMode]) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-bold border transition-all flex items-center gap-1.5 {{ $isSubSelected ? 'bg-[#DC2626] text-white border-[#DC2626] shadow-2xs' : 'bg-white text-slate-700 border-slate-200 hover:border-blue-300 hover:bg-blue-50/40 hover:text-[#1E3A8A]' }}">
                            <span>{{ $sub->name }}</span>
                            @if(isset($sub->sub_products_count))
                                <span class="text-[10px] px-1.5 py-0.2 rounded {{ $isSubSelected ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $sub->sub_products_count }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @elseif(!$activeCategory)
            <div class="mb-6 overflow-x-auto pb-1">
                <div class="flex items-center gap-2 min-w-max">
                    <a href="{{ route('catalog.index', ['view' => $viewMode]) }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-extrabold uppercase tracking-wider border transition-colors {{ !$activeCategory && !$activeBrand ? 'bg-[#0F172A] text-white border-[#0F172A]' : 'bg-white text-slate-700 border-slate-200 hover:border-blue-300' }}">
                        Todas las Líneas
                    </a>
                    @foreach($categories as $catChip)
                        <a href="{{ route('catalog.index', ['category' => $catChip->slug, 'view' => $viewMode]) }}"
                           class="px-3.5 py-2 rounded-xl text-xs font-bold bg-white hover:bg-blue-50 text-slate-800 hover:text-[#1E3A8A] border border-slate-200 hover:border-blue-300 transition-colors flex items-center gap-2 shadow-2xs">
                            <span>{{ $catChip->name }}</span>
                            @if(isset($catChip->products_count))
                                <span class="font-mono text-[10px] font-extrabold bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded">
                                    {{ $catChip->products_count }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Active Filter Pills Bar -->
        @if($hasActiveFilters)
            <div class="bg-white rounded-xl border border-slate-200 px-4 py-3 mb-6 flex flex-wrap items-center gap-2 shadow-2xs">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 mr-1">Filtros activos:</span>

                @if($filters['q'] !== '')
                    <a href="{{ request()->fullUrlWithQuery(['q' => null, 'page' => null]) }}"
                       class="inline-flex items-center gap-1.5 text-xs font-bold bg-blue-50 text-[#1E3A8A] border border-blue-200 px-3 py-1 rounded-full hover:bg-blue-100">
                        <span>Búsqueda: "{{ $filters['q'] }}"</span>
                        <span aria-hidden="true" class="font-extrabold">&times;</span>
                    </a>
                @endif

                @if($activeCategory)
                    <a href="{{ request()->fullUrlWithQuery(['category' => null, 'subcategory' => null, 'page' => null]) }}"
                       class="inline-flex items-center gap-1.5 text-xs font-bold bg-blue-50 text-[#1E3A8A] border border-blue-200 px-3 py-1 rounded-full hover:bg-blue-100">
                        <span>Categoría: {{ $activeCategory->name }}</span>
                        <span aria-hidden="true" class="font-extrabold">&times;</span>
                    </a>
                @endif

                @if($activeSubcategory)
                    <a href="{{ request()->fullUrlWithQuery(['subcategory' => null, 'page' => null]) }}"
                       class="inline-flex items-center gap-1.5 text-xs font-bold bg-blue-50 text-[#1E3A8A] border border-blue-200 px-3 py-1 rounded-full hover:bg-blue-100">
                        <span>Subcategoría: {{ $activeSubcategory->name }}</span>
                        <span aria-hidden="true" class="font-extrabold">&times;</span>
                    </a>
                @endif

                @if($activeBrand)
                    <a href="{{ request()->fullUrlWithQuery(['brand' => null, 'page' => null]) }}"
                       class="inline-flex items-center gap-1.5 text-xs font-bold bg-blue-50 text-[#1E3A8A] border border-blue-200 px-3 py-1 rounded-full hover:bg-blue-100">
                        <span>Marca: {{ $activeBrand->name }}</span>
                        <span aria-hidden="true" class="font-extrabold">&times;</span>
                    </a>
                @endif

                @if(!empty($filters['availability']) && !is_array($filters['availability']))
                    <a href="{{ request()->fullUrlWithQuery(['availability' => null, 'page' => null]) }}"
                       class="inline-flex items-center gap-1.5 text-xs font-bold bg-blue-50 text-[#1E3A8A] border border-blue-200 px-3 py-1 rounded-full hover:bg-blue-100">
                        <span>Estado: {{ $availabilityOptions[$filters['availability']] ?? $filters['availability'] }}</span>
                        <span aria-hidden="true" class="font-extrabold">&times;</span>
                    </a>
                @endif

                @if(!empty($filters['material']))
                    <a href="{{ request()->fullUrlWithQuery(['material' => null, 'page' => null]) }}"
                       class="inline-flex items-center gap-1.5 text-xs font-bold bg-blue-50 text-[#1E3A8A] border border-blue-200 px-3 py-1 rounded-full hover:bg-blue-100">
                        <span>Material: {{ $filters['material'] }}</span>
                        <span aria-hidden="true" class="font-extrabold">&times;</span>
                    </a>
                @endif

                @if(!empty($filters['spec_key']) && !empty($filters['spec_value']))
                    <a href="{{ request()->fullUrlWithQuery(['spec_key' => null, 'spec_value' => null, 'page' => null]) }}"
                       class="inline-flex items-center gap-1.5 text-xs font-bold bg-blue-50 text-[#1E3A8A] border border-blue-200 px-3 py-1 rounded-full hover:bg-blue-100">
                        <span>{{ $filters['spec_key'] }}: {{ $filters['spec_value'] }}</span>
                        <span aria-hidden="true" class="font-extrabold">&times;</span>
                    </a>
                @endif

                <a href="{{ route('catalog.index', ['view' => $viewMode]) }}"
                   class="text-xs font-extrabold text-[#DC2626] hover:underline ml-auto">
                    Limpiar todos los filtros
                </a>
            </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-7 items-start">
            <!-- Left Sidebar: Combinable Technical Filters (Desktop) -->
            <aside class="hidden lg:block w-80 shrink-0 bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs" aria-label="Filtros del catálogo">
                <div class="bg-[#0F172A] text-white px-5 py-3.5 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span class="text-xs font-extrabold uppercase tracking-wider">Filtros Técnicos</span>
                    </div>
                    @if($hasActiveFilters)
                        <a href="{{ route('catalog.index', ['view' => $viewMode]) }}" class="text-[11px] font-bold text-amber-300 hover:underline">
                            Reiniciar
                        </a>
                    @endif
                </div>

                <form action="{{ route('catalog.index') }}" method="GET" id="desktop-catalog-filter-form" class="p-5 space-y-6">
                    <input type="hidden" name="view" value="{{ $viewMode }}">
                    <input type="hidden" name="sort" value="{{ $filters['sort'] }}">

                    <!-- Search within catalog -->
                    <div>
                        <label for="sidebar-q" class="block text-xs font-extrabold uppercase tracking-wider text-slate-800 mb-2">
                            Buscar SKU / Medida / Producto
                        </label>
                        <div class="relative">
                            <input id="sidebar-q"
                                   type="search"
                                   name="q"
                                   value="{{ $filters['q'] }}"
                                   placeholder="Ej. 13273, Ducasse, 35mm..."
                                   class="w-full rounded-xl border border-slate-300 bg-slate-50 focus:bg-white pl-3.5 pr-9 py-2.5 text-xs font-semibold text-slate-900 focus:border-[#1E3A8A] focus:outline-none">
                            <button type="submit" aria-label="Buscar" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-[#1E3A8A]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Categories & Subcategories Tree + Select -->
                    <div class="border-t border-slate-200 pt-5">
                        <label for="sidebar-category" class="block text-xs font-extrabold uppercase tracking-wider text-slate-800 mb-2">
                            Línea / Categoría Principal
                        </label>
                        <select id="sidebar-category"
                                name="category"
                                onchange="this.form.submit()"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-xs font-semibold text-slate-900 bg-slate-50 focus:bg-white focus:border-[#1E3A8A]">
                            <option value="">Todas las categorías ({{ $categories->sum('products_count') ?: $products->total() }})</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->slug }}" @selected(($activeCategory?->slug ?? $filters['category']) === $cat->slug)>
                                    {{ $cat->name }} {{ isset($cat->products_count) ? '('.$cat->products_count.')' : '' }}
                                </option>
                            @endforeach
                        </select>

                        @if($activeCategory && $activeCategory->children->isNotEmpty())
                            <div class="mt-3">
                                <label for="sidebar-subcategory" class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                    Subcategoría en {{ $activeCategory->name }}
                                </label>
                                <select id="sidebar-subcategory"
                                        name="subcategory"
                                        onchange="this.form.submit()"
                                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-900 bg-white focus:border-[#1E3A8A]">
                                    <option value="">Todas las subcategorías</option>
                                    @foreach($activeCategory->children as $sub)
                                        <option value="{{ $sub->slug }}" @selected(($activeSubcategory?->slug ?? $filters['subcategory']) === $sub->slug)>
                                            {{ $sub->name }} {{ isset($sub->sub_products_count) ? '('.$sub->sub_products_count.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Quick Interactive Category Tree List -->
                        <div class="mt-3 space-y-1 border border-slate-100 rounded-xl p-2 bg-slate-50/60">
                            @foreach($categories as $catItem)
                                @php
                                    $isCatActive = ($activeCategory?->slug ?? $filters['category']) === $catItem->slug;
                                @endphp
                                <div>
                                    <a href="{{ route('catalog.index', ['category' => $catItem->slug, 'view' => $viewMode]) }}"
                                       class="flex items-center justify-between gap-2 px-2.5 py-1.5 rounded-lg text-xs transition-colors {{ $isCatActive ? 'bg-[#1E3A8A] text-white font-bold' : 'text-slate-700 hover:bg-white hover:text-[#1E3A8A] font-medium' }}">
                                        <span class="truncate">{{ $catItem->name }}</span>
                                        @if(isset($catItem->products_count))
                                            <span class="font-mono text-[10px] px-1.5 py-0.5 rounded {{ $isCatActive ? 'bg-white/20 text-white' : 'bg-slate-200/70 text-slate-600' }}">
                                                {{ $catItem->products_count }}
                                            </span>
                                        @endif
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Brand Filter -->
                    <div class="border-t border-slate-200 pt-5">
                        <label for="sidebar-brand" class="block text-xs font-extrabold uppercase tracking-wider text-slate-800 mb-2">
                            Marca Registrada
                        </label>
                        <select id="sidebar-brand"
                                name="brand"
                                onchange="this.form.submit()"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-xs font-semibold text-slate-900 bg-slate-50 focus:bg-white focus:border-[#1E3A8A]">
                            <option value="">Todas las marcas ({{ $brands->count() }})</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->slug }}" @selected($filters['brand'] === $brand->slug)>
                                    {{ $brand->name }} {{ isset($brand->products_count) ? '('.$brand->products_count.')' : '' }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Popular Brand Quick Chips -->
                        <div class="flex flex-wrap gap-1.5 mt-2.5">
                            @foreach($brands->sortByDesc('products_count')->take(8) as $topBrand)
                                @php
                                    $isBrandSelected = $filters['brand'] === $topBrand->slug;
                                @endphp
                                <a href="{{ request()->fullUrlWithQuery(['brand' => $isBrandSelected ? null : $topBrand->slug, 'page' => null]) }}"
                                   class="text-[11px] px-2.5 py-1 rounded-lg border font-bold transition-colors {{ $isBrandSelected ? 'bg-[#1E3A8A] text-white border-[#1E3A8A]' : 'bg-slate-50 text-slate-700 border-slate-200 hover:border-blue-300 hover:text-[#1E3A8A]' }}">
                                    {{ $topBrand->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Availability Filter -->
                    <div class="border-t border-slate-200 pt-5">
                        <label for="sidebar-availability" class="block text-xs font-extrabold uppercase tracking-wider text-slate-800 mb-2">
                            Disponibilidad de Stock
                        </label>
                        <select id="sidebar-availability"
                                name="availability"
                                onchange="this.form.submit()"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-xs font-semibold text-slate-900 bg-slate-50 focus:bg-white focus:border-[#1E3A8A]">
                            <option value="">Todos los estados</option>
                            @foreach($availabilityOptions as $availKey => $availLabel)
                                <option value="{{ $availKey }}" @selected($filters['availability'] === $availKey)>
                                    {{ $availLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Material Filter -->
                    @if($materials->isNotEmpty())
                    <div class="border-t border-slate-200 pt-5">
                        <label for="sidebar-material" class="block text-xs font-extrabold uppercase tracking-wider text-slate-800 mb-2">
                            Material de Fabricación
                        </label>
                        <select id="sidebar-material"
                                name="material"
                                onchange="this.form.submit()"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-xs font-semibold text-slate-900 bg-slate-50 focus:bg-white focus:border-[#1E3A8A]">
                            <option value="">Todos los materiales</option>
                            @foreach($materials as $mat)
                                <option value="{{ $mat }}" @selected($filters['material'] === $mat)>
                                    {{ $mat }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Technical Specification Facets -->
                    @if($specifications->isNotEmpty())
                    <div class="border-t border-slate-200 pt-5 space-y-3">
                        <p class="text-xs font-extrabold uppercase tracking-wider text-slate-800">Atributos Técnicos</p>
                        @foreach($specifications->take(4) as $specKey => $specValues)
                            <div>
                                <p class="text-[11px] font-bold text-slate-600 mb-1.5">{{ $specKey }}</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($specValues->take(6) as $specRow)
                                        @php
                                            $isActiveSpec = ($filters['spec_key'] === $specKey && $filters['spec_value'] === $specRow->spec_value);
                                        @endphp
                                        <a href="{{ request()->fullUrlWithQuery([
                                            'spec_key' => $isActiveSpec ? null : $specKey,
                                            'spec_value' => $isActiveSpec ? null : $specRow->spec_value,
                                            'page' => null,
                                        ]) }}"
                                           class="text-[11px] px-2 py-0.5 rounded-md border transition-colors {{ $isActiveSpec ? 'bg-[#1E3A8A] text-white border-[#1E3A8A] font-bold' : 'bg-slate-50 text-slate-700 border-slate-200 hover:border-blue-300' }}">
                                            {{ $specRow->spec_value }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @endif

                    <div class="pt-2 flex flex-col gap-2">
                        <button type="submit"
                                class="w-full bg-[#1E3A8A] hover:bg-[#1D4ED8] text-white font-extrabold py-2.5 px-4 rounded-xl text-xs uppercase tracking-wider transition-colors cursor-pointer">
                            Aplicar Filtros
                        </button>
                        @if($hasActiveFilters)
                            <a href="{{ route('catalog.index', ['view' => $viewMode]) }}"
                               class="w-full text-center py-2 px-4 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-bold">
                                Restablecer Todo
                            </a>
                        @endif
                    </div>
                </form>
            </aside>

            <!-- Right Column: Toolbar (Sort + View Toggle) & Product Results -->
            <div class="flex-1 min-w-0 w-full">
                <!-- Toolbar -->
                <div class="bg-white rounded-2xl border border-slate-200 px-4 sm:px-5 py-3.5 mb-5 flex flex-wrap items-center justify-between gap-4 shadow-2xs">
                    <div class="flex items-center gap-2 text-xs text-slate-600">
                        @if($products->total() > 0)
                            <span>Mostrando <strong class="text-slate-900">{{ $products->firstItem() }}–{{ $products->lastItem() }}</strong> de <strong class="text-slate-900">{{ $products->total() }}</strong> productos técnicos</span>
                        @else
                            <span>Sin coincidencias exactas</span>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Sort Selector -->
                        <form action="{{ route('catalog.index') }}" method="GET" class="flex items-center gap-2">
                            @foreach(request()->except(['sort', 'page']) as $k => $v)
                                @if(is_scalar($v))
                                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                @endif
                            @endforeach
                            <label for="catalog-sort-select" class="text-xs font-bold text-slate-600">Ordenar por:</label>
                            <select id="catalog-sort-select"
                                    name="sort"
                                    onchange="this.form.submit()"
                                    class="rounded-xl border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-800 focus:border-[#1E3A8A]">
                                @foreach($sortOptions as $sortKey => $sortLabel)
                                    <option value="{{ $sortKey }}" @selected($filters['sort'] === $sortKey)>
                                        {{ $sortLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </form>

                        <!-- Grid / List View Switcher -->
                        <div class="inline-flex rounded-xl border border-slate-300 overflow-hidden" role="group" aria-label="Modo de vista del catálogo">
                            <a href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}"
                               aria-label="Vista en cuadrícula"
                               class="px-3 py-1.5 text-xs font-bold flex items-center gap-1.5 {{ $viewMode === 'grid' ? 'bg-[#0F172A] text-white' : 'bg-white text-slate-700 hover:bg-slate-100' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                <span class="hidden sm:inline">Cuadrícula</span>
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}"
                               aria-label="Vista en lista técnica"
                               class="px-3 py-1.5 text-xs font-bold flex items-center gap-1.5 border-l border-slate-300 {{ $viewMode === 'list' ? 'bg-[#0F172A] text-white' : 'bg-white text-slate-700 hover:bg-slate-100' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                <span class="hidden sm:inline">Lista Técnica</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Product Grid or Technical List -->
                @if($products->isEmpty())
                    <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center space-y-4">
                        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-500 mx-auto flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <h2 class="text-lg font-extrabold text-[#0F172A]">No encontramos productos con esos criterios exactos</h2>
                        <p class="text-sm text-slate-600 max-w-md mx-auto">
                            Intenta buscar con un término más general, verificar el código SKU o envíanos tu requerimiento técnico directamente para cotizarlo bajo pedido.
                        </p>
                        <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                            <a href="{{ route('catalog.index') }}" class="px-4 py-2.5 rounded-xl bg-[#1E3A8A] text-white text-xs font-extrabold uppercase tracking-wider">
                                Ver todo el catálogo
                            </a>
                            <a href="{{ route('contact.index') }}" class="px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-extrabold uppercase tracking-wider">
                                Consultar requerimiento especial
                            </a>
                        </div>
                    </div>
                @else
                    @if($viewMode === 'list')
                        <div class="space-y-4">
                            @foreach($products as $product)
                                <x-product-card :product="$product" viewMode="list" />
                            @endforeach
                        </div>
                    @else
                        <div class="grid grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-5">
                            @foreach($products as $product)
                                <x-product-card :product="$product" viewMode="grid" />
                            @endforeach
                        </div>
                    @endif

                    <!-- Server-Side Pagination -->
                    @if($products->hasPages())
                        <div class="mt-8 bg-white rounded-2xl border border-slate-200 px-4 py-3.5 shadow-2xs">
                            {{ $products->links() }}
                        </div>
                    @endif
                @endif

                <!-- Bottom B2B Commercial Quote Callout -->
                <div class="mt-10 bg-gradient-to-r from-[#0F172A] to-[#1E3A8A] rounded-2xl p-6 sm:p-7 text-white flex flex-col md:flex-row items-start md:items-center justify-between gap-5 shadow-md">
                    <div class="space-y-1.5 max-w-xl">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-300">Atención Corporativa y Mayorista</span>
                        <h2 class="text-lg sm:text-xl font-extrabold text-white">¿Necesitas cotizar múltiples ítems para obra, proyecto o carpintería?</h2>
                        <p class="text-xs sm:text-sm text-slate-300">
                            Agrega todos los SKUs requeridos a tu lista de cotización y recibe una propuesta formal en PDF con precios especiales por volumen.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 shrink-0">
                        <a href="{{ route('quote.index') }}"
                           class="px-5 py-3 rounded-xl bg-[#DC2626] hover:bg-[#B91C1C] text-white font-extrabold text-xs uppercase tracking-wider shadow-sm transition-colors">
                            Solicitar Cotización Ahora
                        </a>
                        <a href="{{ route('contact.index') }}"
                           class="px-4 py-3 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-xs uppercase tracking-wider transition-colors">
                            Contacto Comercial
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Filter Drawer -->
    <div id="catalog-filter-drawer"
         class="hidden fixed inset-0 z-50 lg:hidden"
         role="dialog"
         aria-modal="true"
         aria-label="Filtros técnicos del catálogo">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" data-filter-drawer-close></div>
        <div class="relative w-80 max-w-[85vw] bg-white h-full overflow-y-auto shadow-2xl p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-4">
                    <h2 class="text-sm font-extrabold uppercase tracking-wider text-[#0F172A]">Filtrar Catálogo</h2>
                    <button type="button" data-filter-drawer-close aria-label="Cerrar filtros" class="p-1 text-slate-500 hover:text-slate-900">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('catalog.index') }}" method="GET" class="space-y-4">
                    <input type="hidden" name="view" value="{{ $viewMode }}">

                    <div>
                        <label for="mobile-filter-q" class="block text-xs font-bold uppercase text-slate-700 mb-1">Buscar SKU o Producto</label>
                        <input id="mobile-filter-q" type="search" name="q" value="{{ $filters['q'] }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                    </div>

                    <div>
                        <label for="mobile-filter-cat" class="block text-xs font-bold uppercase text-slate-700 mb-1">Categoría</label>
                        <select id="mobile-filter-cat" name="category" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs bg-white">
                            <option value="">Todas</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->slug }}" @selected(($activeCategory?->slug ?? $filters['category']) === $cat->slug)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="mobile-filter-brand" class="block text-xs font-bold uppercase text-slate-700 mb-1">Marca</label>
                        <select id="mobile-filter-brand" name="brand" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs bg-white">
                            <option value="">Todas</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->slug }}" @selected($filters['brand'] === $brand->slug)>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="mobile-filter-avail" class="block text-xs font-bold uppercase text-slate-700 mb-1">Disponibilidad</label>
                        <select id="mobile-filter-avail" name="availability" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs bg-white">
                            <option value="">Todos</option>
                            @foreach($availabilityOptions as $availKey => $availLabel)
                                <option value="{{ $availKey }}" @selected($filters['availability'] === $availKey)>{{ $availLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-3 flex flex-col gap-2">
                        <button type="submit" class="w-full bg-[#DC2626] text-white font-bold py-2.5 rounded-lg text-xs uppercase tracking-wider">
                            Aplicar Filtros
                        </button>
                        <a href="{{ route('catalog.index') }}" class="w-full text-center py-2 border border-slate-300 rounded-lg text-xs font-semibold text-slate-700">
                            Limpiar todo
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($hasActiveFilters)
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    if (window.SanLuisAnalytics) {
                        window.SanLuisAnalytics.trackFilterCatalog({
                            category: @json($activeCategory?->slug ?? $filters['category']),
                            subcategory: @json($activeSubcategory?->slug ?? $filters['subcategory']),
                            brand: @json($filters['brand']),
                            availability: @json($filters['availability']),
                            material: @json($filters['material']),
                            sort: @json($filters['sort']),
                            results_count: {{ (int) $products->total() }}
                        });
                    }
                });
            </script>
        @endpush
    @endif
@endsection
