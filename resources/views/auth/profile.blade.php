@extends('layouts.public')

@section('content')
    @php
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Mi Cuenta e Historial de Cotizaciones', 'url' => route('profile.edit')],
        ];
        $adminUrl = \Illuminate\Support\Facades\Route::has('admin.dashboard') ? route('admin.dashboard') : url('/admin');
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-breadcrumb :items="$breadcrumbs" />

        <!-- Top Account Header -->
        <div class="bg-[#0F172A] text-white rounded-2xl p-6 sm:p-8 mb-8 border-b-4 border-[#DC2626] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-blue-300">Portal de Usuario</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white mt-0.5">{{ $user->name }}</h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1">
                    {{ $user->email }}
                    @if($user->company)
                        &bull; <strong>{{ $user->company }}</strong>
                    @endif
                    @if($user->ruc)
                        (RUC: <span class="font-mono">{{ $user->ruc }}</span>)
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                @if($user->canAccessAdminPanel())
                    <a href="{{ $adminUrl }}" class="px-4 py-2.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold text-xs uppercase tracking-wider">
                        Ir al Panel Administrativo
                    </a>
                @endif

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 rounded-lg border border-slate-600 hover:bg-slate-800 text-slate-200 font-bold text-xs uppercase tracking-wider">
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Left Column: Profile Data & Password Change -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Commercial Profile Form -->
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs">
                    <h2 class="text-base font-extrabold text-[#0F172A] section-heading-bar mb-4">
                        Datos de Contacto y Facturación
                    </h2>

                    <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="profile_name" class="block text-xs font-bold text-slate-700 mb-1">Nombre Completo *</label>
                            <input id="profile_name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900">
                        </div>

                        <div>
                            <label for="profile_email" class="block text-xs font-bold text-slate-700 mb-1">Correo Electrónico *</label>
                            <input id="profile_email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900">
                        </div>

                        <div>
                            <label for="profile_phone" class="block text-xs font-bold text-slate-700 mb-1">Teléfono / WhatsApp</label>
                            <input id="profile_phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono text-slate-900">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="profile_company" class="block text-xs font-bold text-slate-700 mb-1">Empresa</label>
                                <input id="profile_company" type="text" name="company" value="{{ old('company', $user->company) }}"
                                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900">
                            </div>
                            <div>
                                <label for="profile_ruc" class="block text-xs font-bold text-slate-700 mb-1">RUC (11 dígitos)</label>
                                <input id="profile_ruc" type="text" name="ruc" maxlength="11" pattern="\d{11}" value="{{ old('ruc', $user->ruc) }}"
                                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono text-slate-900 tabular-nums">
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-[#1E3A8A] hover:bg-[#1D4ED8] text-white font-bold py-2.5 px-4 rounded-lg text-xs uppercase tracking-wider transition-colors">
                            Guardar Cambios de Perfil
                        </button>
                    </form>
                </div>

                <!-- Password Update Form -->
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs">
                    <h2 class="text-base font-extrabold text-[#0F172A] section-heading-bar mb-4">
                        Actualizar Contraseña
                    </h2>

                    <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="current_password" class="block text-xs font-bold text-slate-700 mb-1">Contraseña Actual *</label>
                            <input id="current_password" type="password" name="current_password" required
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="new_password" class="block text-xs font-bold text-slate-700 mb-1">Nueva Contraseña *</label>
                                <input id="new_password" type="password" name="password" required
                                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label for="new_password_confirmation" class="block text-xs font-bold text-slate-700 mb-1">Confirmar *</label>
                                <input id="new_password_confirmation" type="password" name="password_confirmation" required
                                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 px-4 rounded-lg text-xs uppercase tracking-wider transition-colors">
                            Cambiar Contraseña
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Column: User Quote History -->
            <div class="lg:col-span-7">
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-2xs">
                    <div class="bg-[#1E3A8A] text-white px-6 py-4 flex items-center justify-between">
                        <h2 class="text-sm font-extrabold uppercase tracking-wider">Historial de Solicitudes de Cotización</h2>
                        <a href="{{ route('catalog.index') }}" class="text-xs font-bold text-blue-200 hover:text-white underline">
                            + Nueva Cotización
                        </a>
                    </div>

                    @if($quotes->isEmpty())
                        <div class="p-8 text-center space-y-3">
                            <p class="text-sm font-bold text-slate-800">Aún no registras solicitudes de cotización</p>
                            <p class="text-xs text-slate-600">Explora el catálogo técnico y añade productos a tu pliego de cotización.</p>
                            <a href="{{ route('catalog.index') }}" class="inline-block px-5 py-2.5 rounded-lg bg-[#DC2626] text-white text-xs font-bold uppercase tracking-wider">
                                Ir al Catálogo Técnico
                            </a>
                        </div>
                    @else
                        <div class="divide-y divide-slate-200">
                            @foreach($quotes as $q)
                                <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50 transition-colors">
                                    <div class="space-y-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('quote.confirmation', $q->reference_code) }}"
                                               class="font-mono text-sm font-extrabold text-[#1E3A8A] hover:underline tabular-nums">
                                                {{ $q->reference_code }}
                                            </a>
                                            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded border {{ $q->statusBadgeClass() }}">
                                                {{ $q->statusLabel() }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-600">
                                            Registrada el {{ $q->created_at?->format('d/m/Y H:i') }} &bull;
                                            <strong>{{ $q->items->count() }} ítem(s)</strong>
                                        </p>
                                        @if($q->total_offered !== null)
                                            <p class="text-xs font-bold text-emerald-800 tabular-nums">
                                                Propuesta ofertada: {{ $q->currencySymbol() }} {{ number_format((float) $q->total_offered, 2) }}
                                            </p>
                                        @endif
                                    </div>

                                    <a href="{{ route('quote.confirmation', $q->reference_code) }}"
                                       class="inline-flex items-center gap-1 px-3.5 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-xs font-bold text-slate-800 shrink-0">
                                        <span>Ver Resumen</span>
                                        <span aria-hidden="true">&rarr;</span>
                                    </a>
                                </div>
                            @endforeach
                        </div>

                        @if($quotes->hasPages())
                            <div class="p-4 border-t border-slate-200">
                                {{ $quotes->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
