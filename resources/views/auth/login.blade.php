@extends('layouts.auth')

@section('custom_alerts')
    {{-- Alerts are rendered inline inside the login card for a cohesive split-screen layout --}}
@endsection

@section('content')
    <div class="mx-auto w-full max-w-5xl">
        <div class="grid grid-cols-1 lg:grid-cols-12 bg-white rounded-3xl border border-slate-200/90 shadow-xl overflow-hidden">

            {{-- Left Column: Corporate B2B / Brand Showcase --}}
            <div class="lg:col-span-5 relative bg-gradient-to-br from-[#0F172A] via-[#1E293B] to-[#1E3A8A] text-white p-8 sm:p-10 flex flex-col justify-between overflow-hidden border-b-4 lg:border-b-0 lg:border-r-4 border-[#DC2626]">
                {{-- Decorative geometric accents --}}
                <div aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden">
                    <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-[#DC2626]/15 blur-2xl"></div>
                    <div class="absolute -left-16 -bottom-16 h-64 w-64 rounded-full bg-blue-500/15 blur-2xl"></div>
                    <svg class="absolute inset-0 h-full w-full opacity-[0.04]" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="login-grid" width="32" height="32" patternUnits="userSpaceOnUse">
                                <path d="M 32 0 L 0 0 0 32" fill="none" stroke="currentColor" stroke-width="1" />
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#login-grid)" />
                    </svg>
                </div>

                <div class="relative z-10">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/15 px-3 py-1 text-[11px] font-extrabold uppercase tracking-wider text-blue-100">
                        <span class="h-2 w-2 rounded-full bg-[#DC2626]"></span>
                        Portal Comercial B2B &amp; B2C
                    </div>

                    <h2 class="mt-5 text-2xl sm:text-3xl font-extrabold tracking-tight leading-tight text-white">
                        Abastecimiento ferretero y mueblista sin fricciones
                    </h2>

                    <p class="mt-3 text-xs sm:text-sm text-slate-300 leading-relaxed">
                        Gestiona tus cotizaciones por volumen, consulta especificaciones técnicas y accede a nuestro catálogo de herrajes, tapacantos, herramientas y sistemas LED.
                    </p>

                    <ul class="mt-7 space-y-4">
                        <li class="flex items-start gap-3.5">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#DC2626]/20 border border-[#DC2626]/40 text-red-300">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                            </span>
                            <div>
                                <p class="text-xs sm:text-sm font-bold text-white">Empaque por Caja y Cajón</p>
                                <p class="text-xs text-slate-300 mt-0.5">Unidades de despacho mayorista claras por SKU y variante.</p>
                            </div>
                        </li>

                        <li class="flex items-start gap-3.5">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-500/20 border border-blue-400/30 text-blue-200">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                                </svg>
                            </span>
                            <div>
                                <p class="text-xs sm:text-sm font-bold text-white">Historial de Cotizaciones</p>
                                <p class="text-xs text-slate-300 mt-0.5">Consulta tus solicitudes previas y agiliza reposiciones de stock.</p>
                            </div>
                        </li>

                        <li class="flex items-start gap-3.5">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-500/20 border border-emerald-400/30 text-emerald-200">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                </svg>
                            </span>
                            <div>
                                <p class="text-xs sm:text-sm font-bold text-white">Marcas Garantizadas</p>
                                <p class="text-xs text-slate-300 mt-0.5">Distribuidor directo de DMUEBLE, FORMMA, EMTOP y más.</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="relative z-10 mt-8 pt-6 border-t border-white/15 flex items-center justify-between gap-3 text-xs text-slate-300">
                    <div class="flex items-center gap-2">
                        <span class="inline-block h-2 w-2 rounded-full bg-emerald-400"></span>
                        <span>Atención comercial personalizada</span>
                    </div>
                    <a href="{{ route('contact.index') }}" class="font-bold text-white hover:text-red-300 underline underline-offset-4 transition-colors">
                        Soporte
                    </a>
                </div>
            </div>

            {{-- Right Column: Login Form --}}
            <div class="lg:col-span-7 p-6 sm:p-10 lg:p-12 flex flex-col justify-center bg-white">
                <div class="max-w-md mx-auto w-full">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#1E3A8A]/10 text-[#1E3A8A]">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-extrabold text-[#0F172A] uppercase tracking-tight">
                                Iniciar Sesión
                            </h1>
                            <p class="text-xs sm:text-sm text-slate-500">
                                Accede a tu cuenta de cliente o panel de gestión comercial.
                            </p>
                        </div>
                    </div>

                    @if(session('status'))
                        <div role="status" class="mt-4 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 p-3.5 text-xs sm:text-sm font-medium flex items-start gap-2.5">
                            <svg class="h-5 w-5 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    @if(isset($errors) && $errors->any())
                        <div role="alert" class="mt-4 rounded-xl bg-red-50 border-l-4 border-l-[#DC2626] border border-red-200 text-red-900 p-3.5 text-xs sm:text-sm">
                            <div class="flex items-start gap-2.5">
                                <svg class="h-5 w-5 text-[#DC2626] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                                <ul class="space-y-1 font-medium">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('login.submit') }}" method="POST" class="mt-6 space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Correo Electrónico <span class="text-[#DC2626]">*</span>
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                    </svg>
                                </span>
                                <input id="email"
                                       type="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       required
                                       autofocus
                                       autocomplete="email"
                                       placeholder="correo@empresa.pe"
                                       class="w-full rounded-xl border {{ $errors->has('email') ? 'border-[#DC2626] bg-red-50/30' : 'border-slate-300 bg-slate-50/50 focus:bg-white' }} pl-11 pr-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#1D4ED8] focus:ring-3 focus:ring-[#1D4ED8]/15 focus:outline-none transition-all">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Contraseña <span class="text-[#DC2626]">*</span>
                                </label>
                                <a href="{{ route('password.request') }}" class="text-xs font-bold text-[#1D4ED8] hover:text-[#1E3A8A] hover:underline transition-colors">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            </div>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                    </svg>
                                </span>
                                <input id="password"
                                       type="password"
                                       name="password"
                                       required
                                       autocomplete="current-password"
                                       placeholder="••••••••••••"
                                       class="w-full rounded-xl border {{ $errors->has('password') ? 'border-[#DC2626] bg-red-50/30' : 'border-slate-300 bg-slate-50/50 focus:bg-white' }} pl-11 pr-11 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#1D4ED8] focus:ring-3 focus:ring-[#1D4ED8]/15 focus:outline-none transition-all">
                                <button type="button"
                                        id="toggle-password-visibility"
                                        aria-label="Mostrar u ocultar contraseña"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-700 transition-colors cursor-pointer">
                                    <svg id="icon-eye-open" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <svg id="icon-eye-closed" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-0.5">
                            <label class="inline-flex items-center gap-2.5 text-xs sm:text-sm text-slate-700 font-medium cursor-pointer select-none">
                                <input type="checkbox"
                                       name="remember"
                                       value="1"
                                       {{ old('remember') ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-slate-300 text-[#1E3A8A] focus:ring-[#1D4ED8]">
                                <span>Mantener sesión iniciada</span>
                            </label>
                        </div>

                        <button type="submit"
                                class="group w-full inline-flex items-center justify-center gap-2 bg-[#DC2626] hover:bg-[#B91C1C] active:bg-[#991B1B] text-white font-extrabold py-3.5 px-5 rounded-xl text-sm uppercase tracking-wider shadow-md hover:shadow-lg transition-all cursor-pointer">
                            <span>Ingresar a Mi Cuenta</span>
                            <svg class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </button>

                        <div class="pt-5 border-t border-slate-100">
                            <div class="rounded-2xl bg-slate-50 border border-slate-200/80 p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                                <div class="text-center sm:text-left">
                                    <p class="text-xs font-bold text-slate-800">¿Aún no tienes una cuenta comercial?</p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Regístrate gratis para cotizar por volumen y guardar tus pedidos.</p>
                                </div>
                                <a href="{{ route('register') }}"
                                   class="shrink-0 inline-flex items-center justify-center rounded-xl bg-white hover:bg-[#EFF6FF] text-[#1E3A8A] border border-slate-300 hover:border-[#BFDBFE] px-4 py-2 text-xs font-extrabold uppercase tracking-wide shadow-2xs transition-colors">
                                    Regístrate aquí
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('toggle-password-visibility');
            const passwordInput = document.getElementById('password');
            const eyeOpen = document.getElementById('icon-eye-open');
            const eyeClosed = document.getElementById('icon-eye-closed');

            if (toggleBtn && passwordInput && eyeOpen && eyeClosed) {
                toggleBtn.addEventListener('click', function () {
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    eyeOpen.classList.toggle('hidden', isPassword);
                    eyeClosed.classList.toggle('hidden', !isPassword);
                });
            }
        });
    </script>
@endsection
