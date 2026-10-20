<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Panel Administrativo') | Import Hns San Luis Backoffice</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" sizes="256x256" href="{{ asset('images/logo-sanluis-icon.png') }}">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-100">
    @php
        /** @var \App\Models\User|null $currentUser */
        $currentUser = auth()->user();
        $pendingQuotesCount = \App\Models\Quote::query()->whereIn('status', ['nueva', 'en_revision', 'pendiente_info'])->count();
        $newContactMessagesCount = \App\Models\ContactMessage::query()->where('status', 'nuevo')->count();
        $unresolvedSecurityIncidentsCount = \Illuminate\Support\Facades\Schema::hasTable('security_incidents')
            ? \App\Models\SecurityIncident::query()->whereIn('status', ['blocked', 'quarantined'])->count()
            : 0;
    @endphp

    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Mobile Sidebar Backdrop -->
        <div id="admin-sidebar-backdrop" onclick="toggleAdminSidebar()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-40 hidden lg:hidden"></div>

        <!-- Sidebar Navigation (16 Modules with RBAC Visibility) -->
        <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-slate-200 flex flex-col transform -translate-x-full lg:translate-x-0 lg:static lg:inset-auto lg:shrink-0 transition-transform duration-200 ease-in-out border-r border-slate-800">
            <!-- Brand Header -->
            <div class="h-16 px-4 flex items-center justify-between bg-slate-950 border-b border-slate-800 shrink-0">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <span class="w-11 h-11 bg-white rounded-xl p-1 flex items-center justify-center shadow-sm ring-1 ring-white/15 shrink-0">
                        <picture>
                            <source srcset="{{ asset('images/logo-sanluis-icon.webp') }}" type="image/webp">
                            <img src="{{ asset('images/logo-sanluis-icon.png') }}"
                                 alt="Import Hns San Luis"
                                 width="40"
                                 height="40"
                                 class="w-9 h-9 object-contain">
                        </picture>
                    </span>
                    <div>
                        <span class="block text-xs font-extrabold text-white tracking-tight leading-none">IMPORT HNOS SAN LUIS</span>
                        <span class="block text-[10px] font-semibold text-amber-400 tracking-wider uppercase mt-1">Backoffice B2B</span>
                    </div>
                </a>
                <button type="button" onclick="toggleAdminSidebar()" class="lg:hidden text-slate-400 hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Scrollable Modules List -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-5 text-sm">
                <!-- Resumen Operativo -->
                <div>
                    <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Principal</p>
                    <ul class="space-y-1">
                        @if($currentUser?->hasPermission('dashboard.view'))
                            <li>
                                <a href="{{ route('admin.dashboard') }}"
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-amber-500 text-slate-950 font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>

                <!-- Catálogo -->
                @if($currentUser?->hasPermission('catalog.view') || $currentUser?->hasPermission('catalog.manage') || $currentUser?->hasPermission('categories.manage') || $currentUser?->hasPermission('brands.manage') || $currentUser?->hasPermission('media.manage') || $currentUser?->hasPermission('imports.manage'))
                    <div>
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Catálogo</p>
                        <ul class="space-y-1">
                            @if($currentUser?->hasPermission('catalog.view') || $currentUser?->hasPermission('catalog.manage'))
                                <li>
                                    <a href="{{ route('admin.products.index') }}"
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.products.*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                        <span>Productos</span>
                                    </a>
                                </li>
                            @endif

                            @if($currentUser?->hasPermission('categories.manage'))
                                <li>
                                    <a href="{{ route('admin.categories.index') }}"
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.categories.*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                        </svg>
                                        <span>Categorías</span>
                                    </a>
                                </li>
                            @endif

                            @if($currentUser?->hasPermission('brands.manage'))
                                <li>
                                    <a href="{{ route('admin.brands.index') }}"
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.brands.*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                        </svg>
                                        <span>Marcas</span>
                                    </a>
                                </li>
                            @endif

                            @if($currentUser?->hasPermission('media.manage'))
                                <li>
                                    <a href="{{ route('admin.media.index') }}"
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.media.*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>Archivos</span>
                                    </a>
                                </li>
                            @endif

                            @if($currentUser?->hasPermission('imports.manage'))
                                <li>
                                    <a href="{{ route('admin.imports.index') }}"
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.imports.*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <span>Importar CSV</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif

                <!-- Comercial -->
                @if($currentUser?->hasPermission('quotes.view') || $currentUser?->hasPermission('quotes.manage') || $currentUser?->hasPermission('quotes.respond') || $currentUser?->hasPermission('customers.manage') || $currentUser?->hasPermission('reports.view'))
                    <div>
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Comercial</p>
                        <ul class="space-y-1">
                            @if($currentUser?->hasPermission('quotes.view') || $currentUser?->hasPermission('quotes.manage') || $currentUser?->hasPermission('quotes.respond'))
                                <li>
                                    <a href="{{ route('admin.quotes.index') }}"
                                       class="flex items-center justify-between px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.quotes.*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                        <span class="flex items-center gap-2.5">
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span>Cotizaciones</span>
                                        </span>
                                        @if($pendingQuotesCount > 0)
                                            <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ request()->routeIs('admin.quotes.*') ? 'bg-slate-950 text-amber-400' : 'bg-amber-500 text-slate-950' }}">
                                                {{ $pendingQuotesCount }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.contact-messages.index') }}"
                                       class="flex items-center justify-between px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.contact-messages.*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                        <span class="flex items-center gap-2.5">
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                            </svg>
                                            <span>Mensajes</span>
                                        </span>
                                        @if($newContactMessagesCount > 0)
                                            <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ request()->routeIs('admin.contact-messages.*') ? 'bg-slate-950 text-amber-400' : 'bg-amber-500 text-slate-950' }}">
                                                {{ $newContactMessagesCount }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                            @endif

                            @if($currentUser?->hasPermission('customers.manage'))
                                <li>
                                    <a href="{{ route('admin.customers.index') }}"
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.customers.*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        <span>Clientes</span>
                                    </a>
                                </li>
                            @endif

                            @if($currentUser?->hasPermission('reports.view'))
                                <li>
                                    <a href="{{ route('admin.reports.index') }}"
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.reports.*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                        <span>Informes</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif

                <!-- CMS y SEO -->
                @if($currentUser?->hasPermission('pages.manage') || $currentUser?->hasPermission('media.manage') || $currentUser?->hasPermission('seo.manage') || $currentUser?->hasPermission('testimonials.manage'))
                    <div>
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Contenido y SEO</p>
                        <ul class="space-y-1">
                            @if($currentUser?->hasPermission('pages.manage') || $currentUser?->hasPermission('media.manage'))
                                <li>
                                    <a href="{{ route('admin.hero-banners.index') }}"
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.hero-banners.*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                                        </svg>
                                        <span>Banners / Slider</span>
                                    </a>
                                </li>
                            @endif

                            @if($currentUser?->hasPermission('pages.manage'))
                                <li>
                                    <a href="{{ route('admin.pages.index') }}"
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.pages.*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span>Páginas</span>
                                    </a>
                                </li>
                            @endif

                            @if($currentUser?->hasPermission('seo.manage'))
                                <li>
                                    <a href="{{ route('admin.settings.seo') }}"
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.settings.seo*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                        <span>SEO y GA4</span>
                                    </a>
                                </li>
                            @endif

                            @if($currentUser?->hasPermission('testimonials.manage'))
                                <li>
                                    <a href="{{ route('admin.testimonials.index') }}"
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.testimonials.*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                        </svg>
                                        <span>Testimonios</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif

                <!-- Sistema y Seguridad -->
                @if($currentUser?->hasPermission('users.manage') || $currentUser?->hasPermission('settings.manage') || $currentUser?->hasPermission('activity_logs.view'))
                    <div>
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Sistema</p>
                        <ul class="space-y-1">
                            @if($currentUser?->hasPermission('users.manage'))
                                <li>
                                    <a href="{{ route('admin.users.index') }}"
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.users.*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                        <span>Usuarios</span>
                                    </a>
                                </li>
                            @endif

                            @if($currentUser?->hasPermission('settings.manage'))
                                <li>
                                    <a href="{{ route('admin.settings.commercial') }}"
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.settings.commercial*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>Comercial</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.settings.communication') }}"
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.settings.communication*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <span>Comunicaciones</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.backups.index') }}"
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.backups.*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                                        </svg>
                                        <span>Respaldos</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.security.index') }}"
                                       class="flex items-center justify-between px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.security.*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                        <span class="flex items-center gap-2.5">
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                            </svg>
                                            <span>Seguridad</span>
                                        </span>
                                        @if($unresolvedSecurityIncidentsCount > 0)
                                            <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-rose-600 text-white">
                                                {{ $unresolvedSecurityIncidentsCount }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                            @endif

                            @if($currentUser?->hasPermission('activity_logs.view'))
                                <li>
                                    <a href="{{ route('admin.activity-logs.index') }}"
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.activity-logs.*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                        </svg>
                                        <span>Actividad</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif
            </nav>

            <!-- User Footer -->
            <div class="p-4 bg-slate-950 border-t border-slate-800 text-xs text-slate-400">
                <div class="font-semibold text-white truncate">{{ $currentUser?->name }}</div>
                <div class="text-amber-400 font-medium truncate">{{ $currentUser?->role?->name ?? 'Sin rol' }}</div>
                <div class="truncate text-slate-500 mt-0.5">{{ $currentUser?->email }}</div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar -->
            <header class="h-16 bg-white border-b border-slate-200 px-4 sm:px-6 lg:px-8 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <button type="button" onclick="toggleAdminSidebar()" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">@yield('header', 'Panel Administrativo')</h1>
                        @hasSection('subheader')
                            <p class="text-xs text-slate-500 hidden sm:block">@yield('subheader')</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        <span>Ver tienda pública</span>
                    </a>

                    <form method="POST" action="{{ \Illuminate\Support\Facades\Route::has('logout') ? route('logout') : url('/logout') }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-900 text-white hover:bg-slate-800 text-xs font-semibold transition cursor-pointer">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span class="hidden sm:inline">Cerrar sesión</span>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Page Body -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">
                @if (session('success'))
                    <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 flex items-start gap-3 text-emerald-900 shadow-2xs">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm font-medium">{{ session('success') }}</div>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 flex items-start gap-3 text-amber-900 shadow-2xs">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="text-sm font-medium">{{ session('warning') }}</div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 flex items-start gap-3 text-rose-900 shadow-2xs">
                        <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm font-medium">{{ session('error') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 text-rose-900 shadow-2xs">
                        <div class="flex items-center gap-2 font-semibold text-sm mb-2">
                            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>Por favor corrija los siguientes errores de validación:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-1 text-rose-800">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-slate-200 px-6 py-3 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2">
                <span>&copy; {{ date('Y') }} Import Hns San Luis — Sistema de Gestión de Catálogo Técnico y Cotizaciones B2B</span>
                <span>Seguridad RBAC Activa | Rol: <strong class="text-slate-700">{{ $currentUser?->role?->name ?? 'Usuario' }}</strong></span>
            </footer>
        </div>
    </div>

    <script>
        function toggleAdminSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('admin-sidebar-backdrop');
            if (!sidebar || !backdrop) return;
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }
    </script>
    @stack('scripts')
</body>
</html>
