@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navegación de páginas" class="flex items-center justify-between gap-2">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center gap-1 rounded-xl bg-slate-100 border border-slate-200 px-3.5 py-2 text-xs font-bold text-slate-400 cursor-not-allowed select-none">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span>Anterior</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               rel="prev"
               class="inline-flex items-center gap-1 rounded-xl bg-white hover:bg-blue-50 border border-slate-200 hover:border-blue-300 px-3.5 py-2 text-xs font-extrabold text-[#0F172A] hover:text-[#1E3A8A] shadow-2xs transition-colors">
                <svg class="w-3.5 h-3.5 shrink-0 text-[#1E3A8A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span>Anterior</span>
            </a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               rel="next"
               class="inline-flex items-center gap-1 rounded-xl bg-[#0F172A] hover:bg-[#1E3A8A] border border-[#0F172A] px-3.5 py-2 text-xs font-extrabold text-white shadow-2xs transition-colors">
                <span>Siguiente</span>
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        @else
            <span class="inline-flex items-center gap-1 rounded-xl bg-slate-100 border border-slate-200 px-3.5 py-2 text-xs font-bold text-slate-400 cursor-not-allowed select-none">
                <span>Siguiente</span>
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        @endif
    </nav>
@endif
