@extends('layouts.auth')

@section('content')
    <div class="sm:mx-auto sm:w-full sm:max-w-lg">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-lg overflow-hidden">
            <div class="bg-[#1E3A8A] text-white px-6 py-5 border-b-4 border-[#DC2626]">
                <h1 class="text-xl font-extrabold uppercase tracking-wide">Crear Cuenta de Cliente</h1>
                <p class="text-xs text-blue-100 mt-1">Registra tus datos para agilizar tus cotizaciones B2B/B2C y consultar tu historial.</p>
            </div>

            <form action="{{ route('register.submit') }}" method="POST" class="p-6 space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1">
                        Nombre Completo <span class="text-[#DC2626]">*</span>
                    </label>
                    <input id="name"
                           type="text"
                           name="name"
                           value="{{ old('name') }}"
                           required
                           autofocus
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 mb-1">
                            Correo Electrónico <span class="text-[#DC2626]">*</span>
                        </label>
                        <input id="email"
                               type="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-bold text-slate-700 mb-1">
                            Teléfono / WhatsApp <span class="text-[#DC2626]">*</span>
                        </label>
                        <input id="phone"
                               type="tel"
                               name="phone"
                               value="{{ old('phone') }}"
                               required
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm font-mono text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="company" class="block text-xs font-bold text-slate-700 mb-1">
                            Empresa / Razón Social (Opcional)
                        </label>
                        <input id="company"
                               type="text"
                               name="company"
                               value="{{ old('company') }}"
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                    </div>

                    <div>
                        <label for="ruc" class="block text-xs font-bold text-slate-700 mb-1">
                            RUC (11 dígitos, opcional)
                        </label>
                        <input id="ruc"
                               type="text"
                               name="ruc"
                               maxlength="11"
                               pattern="\d{11}"
                               value="{{ old('ruc') }}"
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm font-mono text-slate-900 tabular-nums focus:border-[#1D4ED8] focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 mb-1">
                            Contraseña (mín. 8 caracteres) <span class="text-[#DC2626]">*</span>
                        </label>
                        <input id="password"
                               type="password"
                               name="password"
                               required
                               autocomplete="new-password"
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1">
                            Confirmar Contraseña <span class="text-[#DC2626]">*</span>
                        </label>
                        <input id="password_confirmation"
                               type="password"
                               name="password_confirmation"
                               required
                               autocomplete="new-password"
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                    </div>
                </div>

                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 flex items-start gap-2.5">
                    <input id="privacy_accepted"
                           type="checkbox"
                           name="privacy_accepted"
                           value="1"
                           required
                           class="mt-0.5 h-4 w-4 rounded border-slate-300 text-[#1E3A8A]">
                    <label for="privacy_accepted" class="text-xs text-slate-700 leading-relaxed">
                        He leído y acepto la <a href="{{ route('pages.privacy') }}" target="_blank" class="text-[#1D4ED8] font-bold underline">Política de Privacidad (Ley N° 29733)</a> y los <a href="{{ route('pages.terms') }}" target="_blank" class="text-[#1D4ED8] font-bold underline">Términos de Uso</a>. *
                    </label>
                </div>

                <button type="submit"
                        class="w-full bg-[#DC2626] hover:bg-[#B91C1C] text-white font-extrabold py-3 px-4 rounded-xl text-sm uppercase tracking-wider shadow-md transition-colors">
                    Crear Mi Cuenta Comercial
                </button>

                <div class="pt-3 border-t border-slate-100 text-center text-xs text-slate-600">
                    ¿Ya tienes una cuenta registrada?
                    <a href="{{ route('login') }}" class="font-bold text-[#1D4ED8] hover:underline ml-1">
                        Iniciar Sesión
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
