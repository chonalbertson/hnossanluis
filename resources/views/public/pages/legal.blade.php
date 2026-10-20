@extends('layouts.public')

@section('content')
    @php
        $title = $page?->title ?: $fallbackTitle;
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $title, 'url' => url()->current()],
        ];
    @endphp

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-breadcrumb :items="$breadcrumbs" />

        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="bg-[#0F172A] text-white p-6 sm:p-8 border-b-4 border-[#1E3A8A]">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-300">Marco Normativo y Transparencia Comercial</span>
                    <span class="text-xs font-mono text-amber-400 font-bold">IMPORT HNOS SAN LUIS S.A.C. &bull; RUC: 20608628143</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white mt-1">{{ $title }}</h1>
                @if($page?->subtitle)
                    <p class="text-sm text-slate-300 mt-2">{{ $page->subtitle }}</p>
                @endif
            </div>

            <div class="p-6 sm:p-8 cms-content">
                @if($page && $page->content)
                    {!! $page->content !!}
                @else
                    <p>La información correspondiente a <strong>{{ $title }}</strong> se encuentra vigente para todas las operaciones del portal de <strong>IMPORT HNOS SAN LUIS S.A.C. (RUC: 20608628143)</strong>.</p>
                @endif

                <div class="mt-8 pt-6 border-t border-slate-200 flex flex-wrap items-center justify-between gap-4 text-xs text-slate-500">
                    <span>Última actualización: {{ $page?->updated_at?->format('d/m/Y') ?? now()->format('d/m/Y') }}</span>
                    <div class="flex flex-wrap gap-4 font-semibold text-[#1D4ED8]">
                        <a href="{{ route('pages.privacy') }}" class="hover:underline">Privacidad (Ley N° 29733)</a>
                        <a href="{{ route('pages.cookies') }}" class="hover:underline">Política de Cookies</a>
                        <a href="{{ route('pages.terms') }}" class="hover:underline">Términos de Uso</a>
                        <a href="{{ route('pages.quote-conditions') }}" class="hover:underline">Condiciones de Cotización</a>
                        <a href="{{ route('pages.complaints-book') }}" class="hover:underline">Libro de Reclamaciones</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
