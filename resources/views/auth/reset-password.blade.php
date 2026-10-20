@extends('layouts.auth')

@section('content')
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-lg overflow-hidden">
            <div class="bg-[#0F172A] text-white px-6 py-5 border-b-4 border-[#DC2626]">
                <h1 class="text-xl font-extrabold uppercase tracking-wide">Definir Nueva Contraseña</h1>
                <p class="text-xs text-slate-300 mt-1">Ingresa y confirma tu nueva contraseña segura.</p>
            </div>

            <form action="{{ route('password.update') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1">
                        Correo Electrónico <span class="text-[#DC2626]">*</span>
                    </label>
                    <input id="email"
                           type="email"
                           name="email"
                           value="{{ old('email', $email) }}"
                           required
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900">
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1">
                        Nueva Contraseña <span class="text-[#DC2626]">*</span>
                    </label>
                    <input id="password"
                           type="password"
                           name="password"
                           required
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1">
                        Confirmar Nueva Contraseña <span class="text-[#DC2626]">*</span>
                    </label>
                    <input id="password_confirmation"
                           type="password"
                           name="password_confirmation"
                           required
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900">
                </div>

                <button type="submit"
                        class="w-full bg-[#DC2626] hover:bg-[#B91C1C] text-white font-extrabold py-3 px-4 rounded-xl text-sm uppercase tracking-wider transition-colors">
                    Guardar Contraseña
                </button>
            </form>
        </div>
    </div>
@endsection
