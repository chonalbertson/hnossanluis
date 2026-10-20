<div id="sanluis-cookie-banner"
     role="region"
     aria-label="Aviso de privacidad y uso de cookies"
     class="hidden fixed bottom-0 inset-x-0 z-50 bg-[#0F172A] text-slate-200 border-t-2 border-[#1E3A8A] shadow-2xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="text-xs leading-relaxed text-slate-300 max-w-4xl">
            <strong class="text-white font-semibold">Privacidad y Cookies Técnicas:</strong>
            Utilizamos cookies de sesión necesarias para conservar tu lista de cotización activa y medir de forma agregada el rendimiento del catálogo, en cumplimiento de la
            <a href="{{ route('pages.privacy') }}" class="underline text-blue-300 hover:text-white font-medium">Política de Privacidad (Ley N° 29733)</a> y nuestra
            <a href="{{ route('pages.cookies') }}" class="underline text-blue-300 hover:text-white font-medium">Política de Cookies</a>.
        </div>
        <div class="flex items-center gap-2.5 shrink-0">
            <button type="button"
                    data-cookie-choice="essential"
                    class="px-3 py-1.5 rounded-md border border-slate-600 hover:bg-slate-800 text-slate-200 text-xs font-semibold transition-colors">
                Solo necesarias
            </button>
            <button type="button"
                    data-cookie-choice="all"
                    class="px-4 py-1.5 rounded-md bg-[#DC2626] hover:bg-[#B91C1C] text-white text-xs font-bold transition-colors">
                Aceptar y continuar
            </button>
        </div>
    </div>
</div>
