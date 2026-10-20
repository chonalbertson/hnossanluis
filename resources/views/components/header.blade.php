@php
    use App\Models\Category;
    use App\Models\SiteSetting;
    use App\Services\QuoteService;
    use App\Services\WhatsAppService;

    $companyName = (string) SiteSetting::getValue('company_commercial_name', 'Import Hnos San Luis');
    $companyLegalName = (string) SiteSetting::getValue('company_legal_name', 'IMPORT HNOS SAN LUIS S.A.C.');
    $companyRuc = (string) SiteSetting::getValue('company_ruc', '20608628143');
    $contactPhone = (string) SiteSetting::getValue('company_contact_phone', '977 208 514');
    $contactEmail = (string) SiteSetting::getValue('company_contact_email', 'ventas@hnssanluis.com');
    $whatsappAdvisors = app(WhatsAppService::class)->getAdvisors();

    $navCategories = Category::query()
        ->whereNull('parent_id')
        ->where('is_active', true)
        ->withCount(['products' => fn ($q) => $q->where('status', 'published')])
        ->with([
            'children' => fn ($q) => $q
                ->where('is_active', true)
                ->withCount(['subProducts' => fn ($sq) => $sq->where('status', 'published')])
                ->orderBy('sort_order'),
        ])
        ->orderBy('sort_order')
        ->get();

    $quoteCartCount = app(QuoteService::class)->countItems();
    $currentUser = auth()->user();
    $adminUrl = \Illuminate\Support\Facades\Route::has('admin.dashboard') ? route('admin.dashboard') : url('/admin');
@endphp

<!-- Accessible Skip to Main Content Link -->
<a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:bg-[#1E3A8A] focus:text-white focus:px-4 focus:py-2.5 focus:rounded-md focus:font-semibold focus:shadow-lg">
    Saltar al contenido
</a>

<!-- 1. Topbar Corporativo (#0F172A) - Oculto en móvil -->
<div class="hidden md:block bg-[#0F172A] text-slate-300 text-xs border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-1.5 flex items-center justify-between gap-2">
        <div class="flex flex-wrap items-center gap-2 font-medium text-slate-200">
            <span class="inline-block w-2 h-2 rounded-full bg-[#DC2626] shrink-0" aria-hidden="true"></span>
            <span class="font-bold text-white">{{ $companyLegalName }}</span>
            <span class="text-slate-500">&bull;</span>
            <span class="font-mono text-amber-400 font-semibold">RUC: {{ $companyRuc }}</span>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('pages.complaints-book') }}" class="inline-flex text-slate-200 hover:text-white font-semibold items-center gap-1 transition-colors">
                <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span>Libro de Reclamaciones</span>
            </a>

            <span class="text-slate-700" aria-hidden="true">|</span>

            @if($currentUser && $currentUser->canAccessAdminPanel())
                <a href="{{ $adminUrl }}"
                   class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-amber-500/15 hover:bg-amber-500/25 border border-amber-400/30 text-amber-300 hover:text-amber-200 font-semibold transition-colors"
                   title="Ir al Panel Administrativo">
                    <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Panel Admin</span>
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white font-semibold transition-colors"
                   title="Inicio de sesión de administrador">
                    <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    <span>Acceso Admin</span>
                </a>
            @endif
        </div>
    </div>
</div>

<!-- 2. Main Sticky Header -->
<header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-2.5 sm:py-3">
        <div class="flex items-center justify-between gap-2 sm:gap-4 lg:gap-6">
            <!-- Mobile Hamburger + Corporate Logo -->
            <div class="flex items-center gap-1.5 sm:gap-2.5 min-w-0">
                <button type="button"
                        data-mobile-menu-toggle
                        aria-expanded="false"
                        aria-controls="mobile-nav-drawer"
                        aria-label="Abrir menú"
                        class="lg:hidden inline-flex items-center justify-center p-1.5 sm:p-2 rounded-lg text-slate-700 hover:text-slate-900 hover:bg-slate-100 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <a href="{{ route('home') }}" class="group flex items-center min-w-0 py-0.5" aria-label="{{ $companyName }} - Inicio">
                    <picture class="block min-w-0">
                        <source srcset="{{ asset('images/logo-sanluis-horizontal.webp') }}" type="image/webp">
                        <img src="{{ asset('images/logo-sanluis-horizontal.png') }}"
                             alt="{{ $companyName }}"
                             width="264"
                             height="62"
                             fetchpriority="high"
                             class="h-9 sm:h-14 lg:h-16 w-auto max-w-[156px] sm:max-w-none object-contain transition-transform duration-200 group-hover:scale-[1.01]">
                    </picture>
                </a>
            </div>

            <!-- Central Clean Search Bar with Instant Autocomplete -->
            <div class="hidden md:block flex-1 max-w-2xl relative">
                <form action="{{ route('catalog.index') }}"
                      method="GET"
                      data-tech-search-form
                      data-suggestions-url="{{ route('catalog.suggestions') }}"
                      role="search"
                      aria-label="Buscador de productos">
                    <div class="group/search flex items-center rounded-2xl border border-slate-200/90 bg-slate-50/80 p-1 shadow-[inset_0_1px_2px_rgba(15,23,42,0.04)] hover:border-slate-300 hover:bg-white focus-within:border-[#1D4ED8] focus-within:bg-white focus-within:ring-4 focus-within:ring-blue-600/10 transition-all duration-200">
                        <div class="relative flex-1 flex items-center min-w-0">
                            <span class="ml-1.5 w-8 h-8 rounded-xl bg-white border border-slate-200/80 text-[#1E3A8A] flex items-center justify-center shrink-0 shadow-2xs group-focus-within/search:border-blue-200 group-focus-within/search:bg-blue-50/60 transition-colors pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </span>
                            <label for="header-search-input" class="sr-only">Buscar producto, SKU o marca</label>
                            <input id="header-search-input"
                                   type="search"
                                   role="combobox"
                                   name="q"
                                   value="{{ request('q') }}"
                                   autocomplete="off"
                                   aria-autocomplete="list"
                                   aria-expanded="false"
                                   aria-controls="desktop-search-suggestions"
                                   data-tech-search-input
                                   placeholder="Buscar producto, código SKU o marca..."
                                   class="w-full bg-transparent text-slate-900 text-sm font-medium px-3 py-2 focus:outline-none placeholder:text-slate-400 placeholder:font-normal">
                            <kbd class="hidden xl:inline-flex items-center justify-center mr-2 px-2 py-0.5 text-[10px] font-mono font-bold text-slate-400 bg-white border border-slate-200/90 rounded-md shadow-2xs shrink-0" title="Presiona / para buscar">/</kbd>
                        </div>

                        <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-[#1E3A8A] to-[#1D4ED8] hover:from-[#1D4ED8] hover:to-[#1E40AF] text-white font-bold px-4 py-2 text-xs uppercase tracking-wider shadow-xs transition-all shrink-0 cursor-pointer"
                                aria-label="Buscar">
                            <span>Buscar</span>
                            <svg class="w-3.5 h-3.5 opacity-85" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Instant Autocomplete Dropdown -->
                <div id="desktop-search-suggestions"
                     data-tech-search-dropdown
                     role="listbox"
                     aria-label="Sugerencias de productos"
                     class="hidden absolute left-0 right-0 top-full mt-2 bg-white rounded-2xl shadow-2xl border border-slate-200 z-50 overflow-hidden max-h-[75vh] overflow-y-auto">
                </div>
            </div>

            <!-- Right Actions: Concise Buttons -->
            <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
                @if($currentUser && $currentUser->canAccessAdminPanel())
                    <a href="{{ $adminUrl }}"
                       class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white shadow-xs transition-colors"
                       title="Panel Administrativo">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Admin</span>
                    </a>
                @endif

                <!-- Quote Cart Button -->
                <a href="{{ route('quote.index') }}"
                   class="group inline-flex items-center gap-1.5 sm:gap-2.5 pl-1.5 pr-2 sm:pl-2 sm:pr-3 py-1 sm:py-1.5 rounded-xl sm:rounded-2xl bg-gradient-to-r from-[#DC2626] to-[#B91C1C] hover:from-[#B91C1C] hover:to-[#991B1B] text-white shadow-sm shadow-red-900/15 hover:shadow-md transition-all shrink-0"
                   aria-label="Cotizar ({{ $quoteCartCount }} productos)">
                    <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl bg-white/15 group-hover:bg-white/20 flex items-center justify-center shrink-0 transition-colors">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </span>
                    <span class="flex flex-col text-left leading-none">
                        <span class="hidden sm:block text-[9px] font-bold uppercase tracking-wider text-red-100/90 mb-0.5">Lista B2B</span>
                        <span class="text-xs sm:text-sm font-extrabold tracking-tight">Cotizar</span>
                    </span>
                    <span data-quote-cart-count
                          class="inline-flex items-center justify-center min-w-5 h-5 sm:min-w-6 sm:h-6 px-1 sm:px-1.5 rounded-lg sm:rounded-xl bg-white text-[#DC2626] font-mono text-[11px] sm:text-xs font-black tabular-nums shadow-2xs">
                        {{ $quoteCartCount }}
                    </span>
                </a>
            </div>
        </div>

        <!-- Mobile Search Bar -->
        <div class="mt-2.5 md:hidden relative">
            <form action="{{ route('catalog.index') }}"
                  method="GET"
                  data-tech-search-form
                  data-suggestions-url="{{ route('catalog.suggestions') }}"
                  role="search"
                  aria-label="Buscar en catálogo">
                <div class="flex rounded-lg border border-slate-300 focus-within:border-[#1D4ED8] bg-slate-50 overflow-hidden">
                    <label for="mobile-search-input" class="sr-only">Buscar producto, SKU o marca</label>
                    <input id="mobile-search-input"
                           type="search"
                           role="combobox"
                           name="q"
                           value="{{ request('q') }}"
                           autocomplete="off"
                           aria-autocomplete="list"
                           aria-expanded="false"
                           aria-controls="mobile-search-suggestions"
                           data-tech-search-input
                           placeholder="Buscar producto, SKU o marca..."
                           class="w-full text-sm text-slate-900 bg-transparent px-3 py-2 focus:outline-none">
                    <button type="submit" class="bg-[#1E3A8A] text-white px-3.5 py-2 text-xs font-bold uppercase tracking-wider">
                        Buscar
                    </button>
                </div>
            </form>

            <div id="mobile-search-suggestions"
                 data-tech-search-dropdown
                 role="listbox"
                 aria-label="Sugerencias de productos"
                 class="hidden absolute left-0 right-0 top-full mt-1.5 bg-white rounded-xl shadow-2xl border border-slate-200 z-50 overflow-hidden max-h-[65vh] overflow-y-auto">
            </div>
        </div>
    </div>

    <!-- 3. Streamlined Corporate Navigation Bar (#1E3A8A) -->
    <nav aria-label="Navegación principal" class="hidden lg:block bg-[#1E3A8A] text-white border-t border-blue-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <div class="flex items-center">
                <!-- Interactive Corporate Mega-Menu Wrapper -->
                <div class="relative" data-category-megamenu-wrapper>
                    <button type="button"
                            data-category-menu-toggle
                            aria-expanded="false"
                            aria-controls="desktop-category-megamenu"
                            class="bg-gradient-to-r from-[#DC2626] to-[#B91C1C] hover:from-[#B91C1C] hover:to-[#991B1B] text-white font-extrabold text-xs uppercase tracking-wider px-5 py-2.5 flex items-center gap-2.5 transition-all cursor-pointer shadow-xs">
                        <span class="w-5 h-5 rounded-md bg-white/15 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </span>
                        <span>Categorías</span>
                        <svg data-category-menu-chevron class="w-3.5 h-3.5 ml-0.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Enterprise Corporate Mega-Menu -->
                    <div id="desktop-category-megamenu"
                         class="hidden absolute left-0 top-full w-[920px] xl:w-[1040px] max-w-[calc(100vw-2rem)] bg-white text-slate-900 rounded-b-2xl shadow-[0_28px_65px_-12px_rgba(15,23,42,0.45)] border border-slate-200 overflow-hidden z-50">
                        <!-- Corporate Top Accent Bar -->
                        <div class="h-1.5 w-full bg-gradient-to-r from-[#1E3A8A] via-[#DC2626] to-[#1E3A8A]"></div>

                        <div class="flex min-h-[430px] max-h-[500px]">
                            <!-- Left Column: Parent Categories with Icons & Corporate Active States -->
                            <div class="w-72 bg-[#F8FAFC] border-r border-slate-200 flex flex-col shrink-0">
                                <div class="px-4 py-2.5 bg-[#0F172A] text-white flex items-center justify-between border-b border-slate-800">
                                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-200 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-[#DC2626]"></span>
                                        Líneas de Producto
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full bg-[#1E3A8A] text-[10px] font-mono font-bold text-blue-200">
                                        {{ $navCategories->count() }} líneas
                                    </span>
                                </div>

                                <div class="py-1.5 overflow-y-auto flex-1 divide-y divide-slate-100">
                                    @foreach($navCategories as $idx => $category)
                                        @php $isFirst = $idx === 0; @endphp
                                        <a href="{{ route('catalog.index', ['category' => $category->slug]) }}"
                                           data-megamenu-tab="{{ $category->slug }}"
                                           class="group flex items-center justify-between gap-2.5 px-3.5 py-2.5 text-xs font-bold transition-all border-l-4 {{ $isFirst ? 'bg-[#1E3A8A] text-white border-[#DC2626] shadow-xs' : 'bg-transparent text-slate-700 border-transparent hover:bg-blue-50/80 hover:text-[#1E3A8A]' }}">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <span data-megamenu-tab-icon
                                                      class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ $isFirst ? 'bg-[#DC2626] text-white shadow-xs' : 'bg-white text-[#1E3A8A] border border-slate-200/90 group-hover:border-blue-300' }}">
                                                    {!! $category->iconSvg('w-4 h-4') !!}
                                                </span>
                                                <span class="truncate">{{ $category->name }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                <span data-megamenu-tab-badge
                                                      class="px-1.5 py-0.5 rounded-md font-mono text-[10px] font-extrabold tabular-nums transition-colors {{ $isFirst ? 'bg-blue-950/70 text-blue-200' : 'bg-slate-200/75 text-slate-600 group-hover:bg-blue-100 group-hover:text-[#1E3A8A]' }}">
                                                    {{ $category->products_count }}
                                                </span>
                                                <svg class="w-3.5 h-3.5 shrink-0 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/></svg>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Right Column: Active Category Showcase & Subcategories Grid -->
                            <div class="flex-1 bg-white flex flex-col justify-between overflow-y-auto">
                                <div class="p-5 flex-1">
                                    @foreach($navCategories as $idx => $category)
                                        <div data-megamenu-panel="{{ $category->slug }}" class="{{ $idx === 0 ? 'block' : 'hidden' }} space-y-4">
                                            <!-- Category Corporate Banner Header -->
                                            <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-[#0F172A] via-[#1E3A8A] to-[#1D4ED8] p-4 text-white flex items-center justify-between gap-4 shadow-xs">
                                                <div class="w-1.5 h-full bg-[#DC2626] absolute left-0 top-0"></div>
                                                <div class="flex items-center gap-3.5 pl-1.5 min-w-0">
                                                    <div class="w-11 h-11 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-white shrink-0 shadow-inner">
                                                        {!! $category->iconSvg('w-6 h-6') !!}
                                                    </div>
                                                    <div class="min-w-0">
                                                        <div class="flex items-center gap-2 flex-wrap">
                                                            <span class="px-2 py-0.5 rounded bg-[#DC2626] text-[10px] font-extrabold uppercase tracking-wider text-white">
                                                                Línea Especializada
                                                            </span>
                                                            <span class="text-[11px] font-mono font-bold text-blue-200">
                                                                {{ $category->products_count }} {{ $category->products_count === 1 ? 'producto' : 'productos' }} en catálogo
                                                            </span>
                                                        </div>
                                                        <a href="{{ route('catalog.index', ['category' => $category->slug]) }}"
                                                           class="text-base font-black text-white hover:text-amber-300 transition-colors block truncate mt-0.5">
                                                            {{ $category->name }}
                                                        </a>
                                                    </div>
                                                </div>

                                                <a href="{{ route('catalog.index', ['category' => $category->slug]) }}"
                                                   class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#DC2626] hover:bg-[#B91C1C] text-white text-xs font-extrabold uppercase tracking-wider shadow-xs transition-colors">
                                                    <span>Ver línea</span>
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                                </a>
                                            </div>

                                            <!-- Subcategories Cards Grid -->
                                            @if($category->children->isNotEmpty())
                                                <div>
                                                    <div class="flex items-center justify-between mb-2.5">
                                                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-[#1E3A8A]"></span>
                                                            Subcategorías de {{ $category->name }} ({{ $category->children->count() }})
                                                        </span>
                                                        <a href="{{ route('catalog.index', ['category' => $category->slug]) }}"
                                                           class="text-[11px] font-bold text-[#1D4ED8] hover:text-[#DC2626] transition-colors">
                                                            Explorar todas &rarr;
                                                        </a>
                                                    </div>

                                                    <ul class="grid grid-cols-2 xl:grid-cols-3 gap-2.5">
                                                        @foreach($category->children as $sub)
                                                            <li>
                                                                <a href="{{ route('catalog.index', ['category' => $category->slug, 'subcategory' => $sub->slug]) }}"
                                                                   class="group flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-200/80 bg-slate-50/60 hover:bg-white hover:border-[#1E3A8A] hover:shadow-md transition-all">
                                                                    <span class="w-8 h-8 rounded-lg bg-blue-50 text-[#1E3A8A] border border-blue-100 group-hover:bg-[#DC2626] group-hover:text-white group-hover:border-[#DC2626] flex items-center justify-center shrink-0 transition-colors">
                                                                        {!! $sub->iconSvg('w-4 h-4') !!}
                                                                    </span>
                                                                    <div class="min-w-0 flex-1">
                                                                        <span class="block text-xs font-bold text-slate-800 group-hover:text-[#1E3A8A] leading-snug truncate">
                                                                            {{ $sub->name }}
                                                                        </span>
                                                                        <span class="block text-[10px] font-mono font-semibold text-slate-400 group-hover:text-[#DC2626]">
                                                                            {{ $sub->sub_products_count }} {{ $sub->sub_products_count === 1 ? 'ítem' : 'ítems' }}
                                                                        </span>
                                                                    </div>
                                                                    <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-[#DC2626] group-hover:translate-x-0.5 transition-all shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @else
                                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-6 text-center space-y-2">
                                                    <p class="text-xs font-semibold text-slate-600">Explora todos los productos disponibles en la línea <strong>{{ $category->name }}</strong>.</p>
                                                    <a href="{{ route('catalog.index', ['category' => $category->slug]) }}"
                                                       class="inline-flex items-center gap-1.5 text-xs font-bold text-[#DC2626] hover:underline">
                                                        Ver productos de {{ $category->name }} &rarr;
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Corporate Mega-Menu Footer Bar -->
                                <div class="px-5 py-3 bg-[#F8FAFC] border-t border-slate-200 flex items-center justify-between gap-4 text-xs">
                                    <div class="flex items-center gap-4">
                                        <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-1.5 font-extrabold text-[#1E3A8A] hover:text-[#DC2626] transition-colors">
                                            <svg class="w-4 h-4 text-[#DC2626]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                            <span>Ver Catálogo Completo</span>
                                        </a>
                                        <span class="text-slate-300">|</span>
                                        <a href="{{ route('quote.index') }}" class="inline-flex items-center gap-1.5 font-bold text-slate-700 hover:text-[#DC2626] transition-colors">
                                            <svg class="w-4 h-4 text-[#1E3A8A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            <span>Cotización Mayorista B2B</span>
                                        </a>
                                    </div>
                                    <div class="text-[11px] font-semibold text-slate-500">
                                        {{ $companyLegalName }} &bull; <span class="font-mono font-bold text-[#0F172A]">RUC {{ $companyRuc }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Primary Navigation Links -->
                <ul class="flex items-center text-xs font-bold uppercase tracking-wider ml-2">
                    <li>
                        <a href="{{ route('home') }}"
                           class="px-3.5 py-2.5 inline-block hover:bg-blue-800 transition-colors {{ request()->routeIs('home') ? 'bg-blue-950/60 text-amber-300' : 'text-white' }}">
                            Inicio
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('catalog.index') }}"
                           class="px-3.5 py-2.5 inline-block hover:bg-blue-800 transition-colors {{ request()->routeIs('catalog.index', 'catalog.category', 'catalog.show') ? 'bg-blue-950/60 text-amber-300' : 'text-white' }}">
                            Catálogo
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('quote.index') }}"
                           class="px-3.5 py-2.5 inline-block hover:bg-blue-800 transition-colors {{ request()->routeIs('quote.index', 'quote.confirmation') ? 'bg-blue-950/60 text-amber-300' : 'text-white' }}">
                            Cotizar
                        </a>
                    </li>
                    <li class="relative group" data-company-menu-wrapper>
                        <a href="{{ route('pages.company') }}"
                           class="px-3.5 py-2.5 inline-flex items-center gap-1.5 hover:bg-blue-800 transition-colors {{ request()->routeIs('pages.company', 'pages.about', 'pages.testimonials') ? 'bg-blue-950/60 text-amber-300' : 'text-white' }}">
                            <span>Empresa</span>
                            <svg class="w-3.5 h-3.5 opacity-80 group-hover:rotate-180 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </a>

                        <!-- Empresa Dropdown Menu (Exact 8 Corporate Sections from Screenshot) -->
                        <div class="hidden group-hover:block focus-within:block absolute left-0 top-full w-72 bg-white text-slate-800 rounded-b-2xl shadow-[0_22px_50px_-12px_rgba(15,23,42,0.35)] border border-slate-200 z-50 normal-case tracking-normal py-2">
                            <div class="h-1 bg-gradient-to-r from-[#1E3A8A] via-[#DC2626] to-[#1E3A8A] -mt-2 mb-1.5 rounded-t-none"></div>

                            <a href="{{ route('pages.company', ['section' => 'nosotros-experiencia']) }}"
                               class="block px-5 py-2.5 text-sm font-bold text-[#1E293B] hover:text-[#1E3A8A] hover:bg-slate-50 transition-colors">
                                Nosotros / Experiencia
                            </a>

                            <a href="{{ route('pages.company', ['section' => 'especialidades-b2b']) }}"
                               class="block px-5 py-2.5 text-sm font-bold text-[#1E293B] hover:text-[#1E3A8A] hover:bg-slate-50 transition-colors">
                                Especialidades B2B
                            </a>

                            <a href="{{ route('pages.company', ['section' => 'certificaciones']) }}"
                               class="block px-5 py-2.5 text-sm font-bold text-[#1E293B] hover:text-[#1E3A8A] hover:bg-slate-50 transition-colors">
                                Certificaciones
                            </a>

                            <a href="{{ route('pages.company', ['section' => 'cobertura-nacional']) }}"
                               class="block px-5 py-2.5 text-sm font-bold text-[#1E293B] hover:text-[#1E3A8A] hover:bg-slate-50 transition-colors">
                                Cobertura Nacional
                            </a>

                            <a href="{{ route('pages.company', ['section' => 'nuestro-equipo']) }}"
                               class="block px-5 py-2.5 text-sm font-bold text-[#1E293B] hover:text-[#1E3A8A] hover:bg-slate-50 transition-colors">
                                Nuestro Equipo
                            </a>

                            <a href="{{ route('pages.company', ['section' => 'marcas-representadas']) }}"
                               class="block px-5 py-2.5 text-sm font-bold text-[#1E293B] hover:text-[#1E3A8A] hover:bg-slate-50 transition-colors">
                                Marcas Representadas
                            </a>

                            <!-- Políticas / Atención Corporativa with Nested Flyout (>) -->
                            <div class="relative group/policies">
                                <a href="{{ route('pages.company', ['section' => 'politicas-atencion-corporativa']) }}"
                                   class="flex items-center justify-between px-5 py-2.5 text-sm font-bold text-[#1E293B] hover:text-[#1E3A8A] hover:bg-slate-50 transition-colors">
                                    <span>Políticas / Atención Corporativa</span>
                                    <svg class="w-4 h-4 text-slate-500 group-hover/policies:text-[#DC2626] group-hover/policies:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>

                                <div class="hidden group-hover/policies:block absolute left-full top-0 w-64 bg-white text-slate-800 rounded-2xl shadow-2xl border border-slate-200 py-2 z-50">
                                    <a href="{{ route('pages.company', ['section' => 'politicas-atencion-corporativa']) }}"
                                       class="block px-4 py-2 text-xs font-extrabold text-[#1E3A8A] hover:bg-slate-50 border-b border-slate-100">
                                        Ver Centro de Políticas B2B &rarr;
                                    </a>
                                    <a href="{{ route('pages.quote-conditions') }}"
                                       class="block px-4 py-2 text-xs font-bold text-slate-700 hover:text-[#1E3A8A] hover:bg-slate-50">
                                        Condiciones de Cotización
                                    </a>
                                    <a href="{{ route('pages.terms') }}"
                                       class="block px-4 py-2 text-xs font-bold text-slate-700 hover:text-[#1E3A8A] hover:bg-slate-50">
                                        Términos y Condiciones
                                    </a>
                                    <a href="{{ route('pages.privacy') }}"
                                       class="block px-4 py-2 text-xs font-bold text-slate-700 hover:text-[#1E3A8A] hover:bg-slate-50">
                                        Política de Privacidad
                                    </a>
                                    <a href="{{ route('pages.cookies') }}"
                                       class="block px-4 py-2 text-xs font-bold text-slate-700 hover:text-[#1E3A8A] hover:bg-slate-50">
                                        Política de Cookies
                                    </a>
                                    <a href="{{ route('pages.complaints-book') }}"
                                       class="block px-4 py-2 text-xs font-bold text-[#DC2626] hover:bg-red-50">
                                        Libro de Reclamaciones
                                    </a>
                                </div>
                            </div>

                            <a href="{{ route('pages.company', ['section' => 'casos-y-proyectos-reales']) }}"
                               class="block px-5 py-2.5 text-sm font-bold text-[#1E293B] hover:text-[#1E3A8A] hover:bg-slate-50 transition-colors">
                                Casos y Proyectos Reales
                            </a>
                        </div>
                    </li>
                    <li>
                        <a href="{{ route('contact.index') }}"
                           class="px-3.5 py-2.5 inline-block hover:bg-blue-800 transition-colors {{ request()->routeIs('contact.*') ? 'bg-blue-950/60 text-amber-300' : 'text-white' }}">
                            Contacto
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<!-- Mobile Navigation Drawer -->
<div id="mobile-nav-drawer"
     class="hidden fixed inset-0 z-50 lg:hidden"
     role="dialog"
     aria-modal="true"
     aria-label="Menú móvil">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" data-mobile-menu-close></div>

    <div class="relative w-80 max-w-[88vw] bg-white h-full overflow-y-auto shadow-2xl flex flex-col justify-between">
        <div>
            <div class="p-3.5 bg-gradient-to-r from-[#0F172A] to-[#1E3A8A] text-white flex items-center justify-between border-b-2 border-[#DC2626]">
                <a href="{{ route('home') }}" class="bg-white rounded-lg px-2.5 py-1.5 shadow-xs inline-flex items-center" aria-label="{{ $companyName }} - Inicio">
                    <picture>
                        <source srcset="{{ asset('images/logo-sanluis-horizontal.webp') }}" type="image/webp">
                        <img src="{{ asset('images/logo-sanluis-horizontal.png') }}"
                             alt="{{ $companyName }}"
                             width="180"
                             height="42"
                             loading="lazy"
                             class="h-9 w-auto object-contain">
                    </picture>
                </a>
                <button type="button" data-mobile-menu-close aria-label="Cerrar menú" class="p-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-white/10 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-3 space-y-0.5 border-b border-slate-200">
                <a href="{{ route('home') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-900 hover:bg-slate-100">Inicio</a>
                <a href="{{ route('catalog.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-900 hover:bg-slate-100">Catálogo</a>
                <a href="{{ route('quote.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-bold text-[#DC2626] hover:bg-red-50">
                    <span>Cotizar</span>
                    <span data-quote-cart-count class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-red-100 text-[#DC2626] font-mono text-xs font-bold">{{ $quoteCartCount }}</span>
                </a>
                <div class="pt-1 pb-1">
                    <a href="{{ route('pages.company') }}" class="block px-3 py-2 rounded-lg text-sm font-extrabold text-[#1E3A8A] bg-blue-50/60 hover:bg-blue-50">
                        Empresa
                    </a>
                    <div class="pl-4 mt-1 space-y-0.5 border-l-2 border-slate-200 ml-3">
                        <a href="{{ route('pages.company', ['section' => 'nosotros-experiencia']) }}" class="block px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:text-[#1E3A8A]">Nosotros / Experiencia</a>
                        <a href="{{ route('pages.company', ['section' => 'especialidades-b2b']) }}" class="block px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:text-[#1E3A8A]">Especialidades B2B</a>
                        <a href="{{ route('pages.company', ['section' => 'certificaciones']) }}" class="block px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:text-[#1E3A8A]">Certificaciones</a>
                        <a href="{{ route('pages.company', ['section' => 'cobertura-nacional']) }}" class="block px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:text-[#1E3A8A]">Cobertura Nacional</a>
                        <a href="{{ route('pages.company', ['section' => 'nuestro-equipo']) }}" class="block px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:text-[#1E3A8A]">Nuestro Equipo</a>
                        <a href="{{ route('pages.company', ['section' => 'marcas-representadas']) }}" class="block px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:text-[#1E3A8A]">Marcas Representadas</a>
                        <a href="{{ route('pages.company', ['section' => 'politicas-atencion-corporativa']) }}" class="block px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:text-[#1E3A8A]">Políticas / Atención Corporativa</a>
                        <a href="{{ route('pages.company', ['section' => 'casos-y-proyectos-reales']) }}" class="block px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:text-[#1E3A8A]">Casos y Proyectos Reales</a>
                    </div>
                </div>
                <a href="{{ route('contact.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-900 hover:bg-slate-100">Contacto</a>
                <a href="{{ route('pages.complaints-book') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-[#1E3A8A] hover:bg-blue-50">Libro de Reclamaciones</a>
            </div>

            <div class="p-3">
                <p class="px-2 text-[11px] font-extrabold uppercase tracking-wider text-[#1E3A8A] mb-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#DC2626]"></span>
                    Categorías y Líneas
                </p>
                <div class="space-y-1">
                    @foreach($navCategories as $cat)
                        <a href="{{ route('catalog.index', ['category' => $cat->slug]) }}"
                           class="flex items-center justify-between gap-2 px-2.5 py-2 text-xs font-bold text-slate-800 bg-slate-50 hover:bg-[#1E3A8A] hover:text-white rounded-xl transition-colors group">
                            <span class="flex items-center gap-2 min-w-0">
                                <span class="w-6 h-6 rounded-lg bg-blue-100 text-[#1E3A8A] group-hover:bg-[#DC2626] group-hover:text-white flex items-center justify-center shrink-0 transition-colors">
                                    {!! $cat->iconSvg('w-3.5 h-3.5') !!}
                                </span>
                                <span class="truncate">{{ $cat->name }}</span>
                            </span>
                            <span class="px-1.5 py-0.5 rounded bg-slate-200/80 text-slate-600 group-hover:bg-blue-900 group-hover:text-blue-100 font-mono text-[10px] font-bold shrink-0">
                                {{ $cat->products_count }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        @if($currentUser && $currentUser->canAccessAdminPanel())
            <div class="p-3.5 bg-slate-50 border-t border-slate-200 space-y-2 text-xs">
                <a href="{{ $adminUrl }}" class="block w-full text-center py-2 px-3 rounded-lg bg-slate-900 text-white font-bold">
                    Admin
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-2 px-3 rounded-lg border border-slate-300 text-slate-700 font-semibold hover:bg-slate-100">
                        Salir
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>

<!-- Fixed Mobile Bottom Navigation Bar (Inicio - Catálogo - Cotización - WhatsApp) -->
@php
    $mobileWhatsappUrl = app(WhatsAppService::class)->generalContactUrl();
    $mobileWaGaPayload = json_encode([
        'cta_location' => 'mobile_bottom_nav',
        'context_type' => 'general',
    ], JSON_UNESCAPED_UNICODE);
@endphp
<nav aria-label="Navegación inferior móvil"
     class="md:hidden fixed bottom-0 inset-x-0 z-50 bg-[#C80000] text-white border-t border-red-900/60 shadow-[0_-4px_20px_rgba(15,23,42,0.28)] pb-[env(safe-area-inset-bottom)]">
    <div class="grid grid-cols-4 items-center h-16">
        <!-- 1. Inicio -->
        <a href="{{ route('home') }}"
           class="flex flex-col items-center justify-center gap-1 h-full transition-opacity {{ request()->routeIs('home') ? 'opacity-100' : 'opacity-90 hover:opacity-100' }}">
            <svg class="w-6 h-6 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10.5L12 3l9 7.5V20a1 1 0 01-1 1h-5.5v-6h-5v6H4a1 1 0 01-1-1v-9.5z"/>
            </svg>
            <span class="text-[11px] font-bold leading-none tracking-tight text-white">Inicio</span>
        </a>

        <!-- 2. Catálogo -->
        <a href="{{ route('catalog.index') }}"
           class="flex flex-col items-center justify-center gap-1 h-full transition-opacity {{ request()->routeIs('catalog.*') ? 'opacity-100' : 'opacity-90 hover:opacity-100' }}">
            <svg class="w-6 h-6 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <rect x="3.5" y="3.5" width="7" height="7" rx="1" stroke-width="2"/>
                <rect x="13.5" y="3.5" width="7" height="7" rx="1" stroke-width="2"/>
                <rect x="3.5" y="13.5" width="7" height="7" rx="1" stroke-width="2"/>
                <rect x="13.5" y="13.5" width="7" height="7" rx="1" stroke-width="2"/>
            </svg>
            <span class="text-[11px] font-bold leading-none tracking-tight text-white">Catálogo</span>
        </a>

        <!-- 3. Cotización -->
        <a href="{{ route('quote.index') }}"
           class="flex flex-col items-center justify-center gap-1 h-full transition-opacity {{ request()->routeIs('quote.*') ? 'opacity-100' : 'opacity-90 hover:opacity-100' }}">
            <span class="relative inline-flex items-center justify-center">
                <svg class="w-6 h-6 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3h7l5 5v12a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3v5h5M9 13h6M9 17h6M9 9h2"/>
                </svg>
                <span data-quote-cart-count
                      class="absolute -top-1.5 -right-3.5 inline-flex items-center justify-center min-w-5 h-5 px-1 rounded-full bg-white text-[#C80000] font-mono text-[11px] font-black tabular-nums shadow-xs">
                    {{ $quoteCartCount }}
                </span>
            </span>
            <span class="text-[11px] font-bold leading-none tracking-tight text-white">Cotización</span>
        </a>

        <!-- 4. WhatsApp -->
        <a href="{{ $mobileWhatsappUrl }}"
           target="_blank"
           rel="noopener noreferrer"
           data-ga-event="whatsapp_click"
           data-ga-params="{{ $mobileWaGaPayload }}"
           aria-label="Contactar por WhatsApp"
           class="flex flex-col items-center justify-center gap-1 h-full transition-transform active:scale-95">
            <svg class="w-6 h-6 text-[#25D366] fill-current shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            <span class="text-[11px] font-bold leading-none tracking-tight text-[#25D366]">WhatsApp</span>
        </a>
    </div>
</nav>
