<!DOCTYPE html>
<html lang="es-PE" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-site-verification" content="U8QgOdTTnNUSIF6qAsHL7d3kYSmyCI0yKG1CZJVrUlM">

    <x-seo-meta :seo="$seo ?? []" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-full flex flex-col bg-[#F8FAFC] text-[#0F172A] antialiased pb-16 md:pb-0">
    <x-header />

    <!-- Global Flash Notifications -->
    @if(session('success') || session('error') || session('status') || (isset($errors) && $errors->any()))
        <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-4">
            @if(session('success'))
                <div role="status" aria-live="polite" class="mb-3 rounded-lg bg-emerald-50 border border-emerald-300 text-emerald-900 px-4 py-3 text-sm flex items-center justify-between gap-3 shadow-2xs">
                    <div class="flex items-center gap-2.5 font-medium">
                        <svg class="w-5 h-5 text-emerald-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('status'))
                <div role="status" aria-live="polite" class="mb-3 rounded-lg bg-blue-50 border border-blue-300 text-blue-900 px-4 py-3 text-sm font-medium shadow-2xs">
                    {{ session('status') }}
                </div>
            @endif

            @if(session('error'))
                <div role="alert" class="mb-3 rounded-lg bg-red-50 border border-red-300 text-red-900 px-4 py-3 text-sm font-medium shadow-2xs">
                    {{ session('error') }}
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div role="alert" class="mb-3 rounded-lg bg-red-50 border-l-4 border-l-[#DC2626] border border-red-200 text-red-900 p-4 text-sm shadow-2xs">
                    <p class="font-bold mb-1">Por favor corrige los siguientes campos antes de continuar:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs sm:text-sm">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <main id="main-content" class="flex-1">
        @yield('content')
    </main>

    <!-- Toast Container for Reactive Quote Actions -->
    <div id="sanluis-toast-container" class="fixed bottom-20 right-5 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-auto"></div>

    <x-footer />
    <x-whatsapp-button variant="floating" />
    <x-cookie-banner />

    @if(session('ga4_submit_quote'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.SanLuisAnalytics) {
                    window.SanLuisAnalytics.trackSubmitQuote(@json(session('ga4_submit_quote')));
                }
            });
        </script>
    @endif

    @if(session('ga4_contact_submit'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.SanLuisAnalytics) {
                    window.SanLuisAnalytics.trackContactSubmit(@json(session('ga4_contact_submit')));
                }
            });
        </script>
    @endif

    @stack('scripts')
</body>
</html>
