@extends('layouts.public')

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
        <div class="bg-white rounded-2xl border border-slate-200 p-8 sm:p-12 shadow-xs space-y-6">
            <span class="inline-block font-mono text-sm font-extrabold uppercase tracking-wider bg-red-50 text-[#DC2626] border border-red-200 px-3 py-1 rounded">
                Error 404 — Recurso No Encontrado
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F172A]">
                La página o el código SKU solicitado no se encuentra disponible
            </h1>
            <p class="text-sm sm:text-base text-slate-600 max-w-xl mx-auto leading-relaxed">
                Es posible que el enlace haya cambiado o que el producto haya sido actualizado en nuestro catálogo técnico. Usa el buscador para localizar el artículo por código o nombre:
            </p>

            <form action="{{ route('catalog.index') }}" method="GET" class="max-w-lg mx-auto flex gap-2">
                <label for="err-404-q" class="sr-only">Buscar en el catálogo</label>
                <input id="err-404-q" type="search" name="q" placeholder="Buscar producto, SKU o medida..."
                       class="flex-1 rounded-lg border-2 border-slate-300 px-4 py-2.5 text-sm">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#1E3A8A] text-white font-bold text-xs uppercase tracking-wider">
                    Buscar
                </button>
            </form>

            <div class="pt-4 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('catalog.index') }}" class="px-5 py-2.5 rounded-lg bg-[#DC2626] text-white font-bold text-xs uppercase tracking-wider">
                    Explorar Catálogo Técnico
                </a>
                <a href="{{ route('home') }}" class="px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 font-bold text-xs uppercase tracking-wider">
                    Ir al Inicio
                </a>
            </div>
        </div>
    </div>
@endsection
