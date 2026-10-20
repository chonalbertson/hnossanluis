<!DOCTYPE html>
<html lang="es-PE" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-seo-meta :seo="$seo ?? ['robots' => 'noindex, nofollow']" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col bg-slate-100 text-[#0F172A] antialiased selection:bg-[#DC2626] selection:text-white">
    <header class="bg-[#0F172A] text-white border-b-4 border-[#DC2626] py-3.5 shadow-md relative z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="bg-white rounded-xl px-3.5 py-2 shadow-sm transition-transform duration-200 group-hover:scale-[1.02]">
                    <picture>
                        <source srcset="{{ asset('images/logo-sanluis-horizontal.webp') }}" type="image/webp">
                        <img src="{{ asset('images/logo-sanluis-horizontal.png') }}"
                             alt="Import Hns San Luis"
                             width="220"
                             height="52"
                             class="h-10 sm:h-12 w-auto object-contain">
                    </picture>
                </div>
            </a>

            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('home') }}"
                   class="hidden sm:inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold text-slate-300 hover:text-white hover:bg-white/10 transition-colors">
                    <span>Inicio</span>
                </a>
                <a href="{{ route('catalog.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 px-3.5 py-2 text-xs sm:text-sm font-semibold text-white transition-all">
                    <svg class="w-4 h-4 text-[#DC2626]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    <span>Volver al Catálogo</span>
                </a>
            </div>
        </div>
    </header>

    <main id="main-content" class="relative flex-1 flex flex-col justify-center py-8 sm:py-12 px-4 sm:px-6 lg:px-8 overflow-hidden">
        {{-- Ambient industrial background accents --}}
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-32 -left-32 h-96 w-96 rounded-full bg-[#1E3A8A]/10 blur-3xl"></div>
            <div class="absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-[#DC2626]/10 blur-3xl"></div>
        </div>

        <div class="relative z-10 w-full">
            @hasSection('custom_alerts')
                @yield('custom_alerts')
            @else
                <div class="sm:mx-auto sm:w-full sm:max-w-md">
                    @if(session('status'))
                        <div role="status" class="mb-4 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 p-3.5 text-sm font-medium shadow-xs">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if(isset($errors) && $errors->any())
                        <div role="alert" class="mb-4 rounded-xl bg-red-50 border-l-4 border-l-[#DC2626] border border-red-200 text-red-900 p-3.5 text-sm shadow-xs">
                            <ul class="list-disc list-inside space-y-1 text-xs sm:text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500 relative z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2">
            <span>&copy; {{ date('Y') }} Import Hns San Luis. Todos los derechos reservados.</span>
            <div class="flex items-center gap-4">
                <a href="{{ route('pages.privacy') }}" class="hover:text-slate-800 underline transition-colors">Política de Privacidad (Ley N° 29733)</a>
                <a href="{{ route('contact.index') }}" class="hover:text-slate-800 underline transition-colors">Contacto Comercial</a>
            </div>
        </div>
    </footer>
</body>
</html>
