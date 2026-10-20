@extends('layouts.public')

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
        <div class="bg-white rounded-2xl border border-slate-200 p-8 sm:p-12 shadow-xs space-y-6">
            <span class="inline-block font-mono text-sm font-extrabold uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200 px-3 py-1 rounded">
                Error 429 — Demasiadas Solicitudes
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F172A]">
                Has realizado demasiadas consultas en poco tiempo
            </h1>
            <p class="text-sm sm:text-base text-slate-600 max-w-xl mx-auto leading-relaxed">
                Nuestro sistema de protección antispam ha limitado temporalmente las solicitudes desde tu conexión. Por favor espera unos segundos antes de continuar navegando o enviar un nuevo formulario.
            </p>

            <div class="pt-4 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('catalog.index') }}" class="px-5 py-2.5 rounded-lg bg-[#DC2626] text-white font-bold text-xs uppercase tracking-wider">
                    Volver al Catálogo Técnico
                </a>
                <a href="{{ route('contact.index') }}" class="px-5 py-2.5 rounded-lg bg-[#1E3A8A] text-white font-bold text-xs uppercase tracking-wider">
                    Contacto Comercial
                </a>
                <a href="{{ route('home') }}" class="px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 font-bold text-xs uppercase tracking-wider">
                    Ir al Inicio
                </a>
            </div>
        </div>
    </div>
@endsection
