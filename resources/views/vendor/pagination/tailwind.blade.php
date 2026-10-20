@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = method_exists($paginator, 'lastPage') ? $paginator->lastPage() : null;
        $mobileStart = $lastPage ? max(1, min($currentPage - 1, $lastPage - 2)) : 1;
        $mobileEnd = $lastPage ? min($lastPage, max($currentPage + 1, 3)) : 1;
    @endphp
    <nav role="navigation" aria-label="Navegación de páginas" class="w-full">
        {{-- Mobile View --}}
        <div class="flex flex-col gap-2.5 sm:hidden">
            {{-- Top Summary Row --}}
            <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500 px-0.5">
                <span>
                    @if ($paginator->firstItem())
                        Mostrando <strong class="font-extrabold text-[#0F172A] tabular-nums">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</strong> de <strong class="font-extrabold text-[#0F172A] tabular-nums">{{ $paginator->total() }}</strong>
                    @else
                        <strong class="font-extrabold text-[#0F172A] tabular-nums">{{ $paginator->count() }}</strong> resultados
                    @endif
                </span>
                @if ($lastPage)
                    <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 border border-slate-200/80 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-[#0F172A] tabular-nums">
                        Pág. {{ $currentPage }} / {{ $lastPage }}
                    </span>
                @endif
            </div>

            {{-- Controls Row --}}
            <div class="flex items-center justify-between gap-1.5">
                {{-- Previous Button --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true"
                          class="inline-flex items-center justify-center gap-1 rounded-xl bg-slate-100 border border-slate-200 px-3 py-2 text-xs font-bold text-slate-400 cursor-not-allowed select-none shrink-0">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <span>Anterior</span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}"
                       rel="prev"
                       class="inline-flex items-center justify-center gap-1 rounded-xl bg-white hover:bg-blue-50 active:bg-blue-100 border border-slate-200 hover:border-blue-300 px-3 py-2 text-xs font-extrabold text-[#0F172A] hover:text-[#1E3A8A] shadow-2xs transition-colors shrink-0">
                        <svg class="w-3.5 h-3.5 shrink-0 text-[#1E3A8A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <span>Anterior</span>
                    </a>
                @endif

                {{-- Compact Mobile Page Numbers --}}
                @if ($lastPage && $lastPage > 1)
                    <div class="flex items-center justify-center gap-1 min-w-0">
                        @if ($mobileStart > 1)
                            <a href="{{ $paginator->url(1) }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:border-blue-300 hover:text-[#1E3A8A] tabular-nums transition-colors">
                                1
                            </a>
                            @if ($mobileStart > 2)
                                <span class="text-xs font-bold text-slate-400 px-0.5">&hellip;</span>
                            @endif
                        @endif

                        @for ($page = $mobileStart; $page <= $mobileEnd; $page++)
                            @if ($page == $currentPage)
                                <span aria-current="page"
                                      class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#1E3A8A] text-white text-xs font-extrabold shadow-2xs tabular-nums">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $paginator->url($page) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:border-blue-300 hover:text-[#1E3A8A] tabular-nums transition-colors">
                                    {{ $page }}
                                </a>
                            @endif
                        @endfor

                        @if ($mobileEnd < $lastPage)
                            @if ($mobileEnd < $lastPage - 1)
                                <span class="text-xs font-bold text-slate-400 px-0.5">&hellip;</span>
                            @endif
                            <a href="{{ $paginator->url($lastPage) }}"
                               class="inline-flex items-center justify-center min-w-8 h-8 px-1.5 rounded-lg border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:border-blue-300 hover:text-[#1E3A8A] tabular-nums transition-colors">
                                {{ $lastPage }}
                            </a>
                        @endif
                    </div>
                @endif

                {{-- Next Button --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}"
                       rel="next"
                       class="inline-flex items-center justify-center gap-1 rounded-xl bg-[#0F172A] hover:bg-[#1E3A8A] active:bg-blue-950 border border-[#0F172A] px-3 py-2 text-xs font-extrabold text-white shadow-2xs transition-colors shrink-0">
                        <span>Siguiente</span>
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @else
                    <span aria-disabled="true"
                          class="inline-flex items-center justify-center gap-1 rounded-xl bg-slate-100 border border-slate-200 px-3 py-2 text-xs font-bold text-slate-400 cursor-not-allowed select-none shrink-0">
                        <span>Siguiente</span>
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                @endif
            </div>
        </div>

        {{-- Desktop View --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between sm:gap-4">
            <div>
                <p class="text-xs sm:text-sm text-slate-600">
                    Mostrando
                    @if ($paginator->firstItem())
                        <span class="font-extrabold text-[#0F172A] tabular-nums">{{ $paginator->firstItem() }}</span>
                        a
                        <span class="font-extrabold text-[#0F172A] tabular-nums">{{ $paginator->lastItem() }}</span>
                    @else
                        <span class="font-extrabold text-[#0F172A] tabular-nums">{{ $paginator->count() }}</span>
                    @endif
                    de
                    <span class="font-extrabold text-[#0F172A] tabular-nums">{{ $paginator->total() }}</span>
                    resultados
                </p>
            </div>

            <div class="flex items-center gap-1">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true"
                          aria-label="Anterior"
                          class="inline-flex items-center gap-1 px-3 py-2 text-xs font-bold text-slate-400 bg-slate-100 border border-slate-200 cursor-not-allowed rounded-xl select-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span>Anterior</span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}"
                       rel="prev"
                       aria-label="Anterior"
                       class="inline-flex items-center gap-1 px-3 py-2 text-xs font-extrabold text-[#0F172A] bg-white border border-slate-200 rounded-xl hover:bg-blue-50 hover:text-[#1E3A8A] hover:border-blue-300 transition-colors shadow-2xs">
                        <svg class="w-4 h-4 text-[#1E3A8A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span>Anterior</span>
                    </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span aria-disabled="true"
                              class="inline-flex items-center justify-center px-2.5 py-2 text-xs font-bold text-slate-400 select-none">
                            {{ $element }}
                        </span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                      class="inline-flex items-center justify-center min-w-9 h-9 px-3 text-xs font-extrabold text-white bg-[#1E3A8A] border border-[#1E3A8A] rounded-xl shadow-2xs tabular-nums">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}"
                                   aria-label="Ir a la página {{ $page }}"
                                   class="inline-flex items-center justify-center min-w-9 h-9 px-3 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-blue-50 hover:text-[#1E3A8A] hover:border-blue-300 transition-colors tabular-nums">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}"
                       rel="next"
                       aria-label="Siguiente"
                       class="inline-flex items-center gap-1 px-3 py-2 text-xs font-extrabold text-white bg-[#0F172A] border border-[#0F172A] rounded-xl hover:bg-[#1E3A8A] hover:border-[#1E3A8A] transition-colors shadow-2xs">
                        <span>Siguiente</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @else
                    <span aria-disabled="true"
                          aria-label="Siguiente"
                          class="inline-flex items-center gap-1 px-3 py-2 text-xs font-bold text-slate-400 bg-slate-100 border border-slate-200 cursor-not-allowed rounded-xl select-none">
                        <span>Siguiente</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
