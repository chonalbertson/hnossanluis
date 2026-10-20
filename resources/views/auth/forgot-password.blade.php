@extends('layouts.auth')

@section('content')
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-lg overflow-hidden">
            <div class="bg-[#0F172A] text-white px-6 py-5 border-b-4 border-[#1E3A8A]">
                <h1 class="text-xl font-extrabold uppercase tracking-wide">Recuperar Contraseña</h1>
                <p class="text-xs text-slate-300 mt-1">Ingresa tu correo electrónico registrado para recibir instrucciones de recuperación.</p>
            </div>

            <form action="{{ route('password.email') }}" method="POST" class="p-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1">
                        Correo Electrónico Registrado <span class="text-[#DC2626]">*</span>
                    </label>
                    <input id="email"
                           type="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                </div>

                <button type="submit"
                        class="w-full bg-[#1E3A8A] hover:bg-[#1D4ED8] text-white font-extrabold py-3 px-4 rounded-xl text-sm uppercase tracking-wider transition-colors">
                    Enviar Instrucciones
                </button>

                <div class="pt-3 border-t border-slate-100 text-center text-xs">
                    <a href="{{ route('login') }}" class="font-bold text-slate-700 hover:text-[#1D4ED8]">
                        &larr; Volver al Inicio de Sesión
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
