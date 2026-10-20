{{-- Encabezado de bloque reutilizable en las páginas de Empresa. Parámetros: $eyebrow, $title, $text (opcional), $center (opcional). --}}
<div class="flex flex-col gap-3 {{ ($center ?? false) ? 'items-center text-center mx-auto' : '' }} max-w-3xl">
    <span class="inline-flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-widest text-[#DC2626]">
        <span class="w-6 h-0.5 bg-[#DC2626]"></span>
        {{ $eyebrow }}
    </span>
    <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-[#0F172A] leading-tight">{{ $title }}</h2>
    @if(!empty($text))
        <p class="text-sm sm:text-base text-slate-600 leading-relaxed">{{ $text }}</p>
    @endif
</div>
