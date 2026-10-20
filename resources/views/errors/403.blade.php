@extends('layouts.public')

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
        <div class="bg-white rounded-2xl border border-slate-200 p-8 sm:p-12 shadow-xs space-y-5">
            <span class="inline-block font-mono text-sm font-extrabold uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200 px-3 py-1 rounded">
                Error 403 — Acceso Restringido
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F172A]">
                No cuentas con permisos para acceder a esta sección
            </h1>
            <p class="text-sm text-slate-600 leading-relaxed">
                Esta operación requiere privilegios específicos dentro del sistema de Import Hns San Luis.
            </p>
            <div class="pt-2 flex justify-center gap-3">
                <a href="{{ route('home') }}" class="px-5 py-2.5 rounded-lg bg-[#1E3A8A] text-white font-bold text-xs uppercase tracking-wider">
                    Volver al Inicio
                </a>
            </div>
        </div>
    </div>
@endsection
