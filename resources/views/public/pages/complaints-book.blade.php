@extends('layouts.public')

@section('content')
    @php
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Libro de Reclamaciones Virtual', 'url' => route('pages.complaints-book')],
        ];
    @endphp

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-breadcrumb :items="$breadcrumbs" />

        <!-- Official INDECOPI Header -->
        <div class="bg-[#0F172A] text-white rounded-2xl p-6 sm:p-8 border-b-4 border-[#DC2626] shadow-lg mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-[#1E3A8A] border border-blue-400/30 flex items-center justify-center shrink-0 text-white shadow-inner">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs font-extrabold uppercase tracking-wider text-amber-400">Código de Protección y Defensa del Consumidor — Ley N° 29571</span>
                        <h1 class="text-2xl sm:text-3xl font-black uppercase tracking-tight text-white mt-1">
                            Libro de Reclamaciones Virtual
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-300 mt-1">
                            Hoja de Reclamación Electrónica conforme a las disposiciones de INDECOPI (Perú).
                        </p>
                    </div>
                </div>

                <div class="bg-slate-800/90 border border-slate-700 rounded-xl px-4 py-3 text-xs space-y-1 shrink-0">
                    <p class="text-slate-400 uppercase font-bold tracking-wider text-[10px]">Datos del Proveedor</p>
                    <p class="font-extrabold text-white text-sm">{{ $companyInfo['legal_name'] }}</p>
                    <p class="font-mono font-bold text-amber-400">RUC: {{ $companyInfo['ruc'] }}</p>
                    <p class="text-slate-300">{{ $companyInfo['address'] }}</p>
                    <p class="text-slate-300">Fecha: <span class="font-mono text-white">{{ now()->format('d/m/Y') }}</span></p>
                </div>
            </div>
        </div>

        @if(session('complaint_sheet_code'))
            <div class="mb-8 rounded-2xl bg-emerald-50 border-2 border-emerald-600 p-6 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <span class="text-xs font-extrabold uppercase tracking-wider text-emerald-800">Constancia de Recepción</span>
                        <h2 class="text-lg font-black text-emerald-950">
                            Hoja de Reclamación Registrada: <span class="font-mono text-[#1E3A8A]">{{ session('complaint_sheet_code') }}</span>
                        </h2>
                        <p class="text-xs text-emerald-900 leading-relaxed">
                            Hemos registrado tu solicitud en el Libro de Reclamaciones de <strong>{{ $companyInfo['legal_name'] }} (RUC: {{ $companyInfo['ruc'] }})</strong>. Nuestro equipo dará respuesta en el plazo legal establecido por ley.
                        </p>
                    </div>
                    <button type="button" onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs uppercase tracking-wider shrink-0">
                        Imprimir Constancia
                    </button>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs">
            <form action="{{ route('pages.complaints-book.submit') }}" method="POST" class="space-y-8">
                @csrf

                <!-- Honeypot -->
                <div class="hidden" aria-hidden="true">
                    <label for="complaints-website-hp">No llenar este campo</label>
                    <input id="complaints-website-hp" type="text" name="website_hp" value="" tabindex="-1" autocomplete="off">
                </div>

                <!-- 1. Identificación del Consumidor Reclamante -->
                <section class="space-y-4">
                    <div class="border-b border-slate-200 pb-2.5 flex items-center justify-between">
                        <h2 class="text-base sm:text-lg font-extrabold text-[#0F172A] uppercase tracking-wide">
                            1. Identificación del Consumidor Reclamante
                        </h2>
                        <span class="text-xs text-slate-500">Campos con <strong class="text-[#DC2626]">*</strong> obligatorios</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="document_type" class="block text-xs font-bold text-slate-700 mb-1">
                                Tipo de Documento <span class="text-[#DC2626]">*</span>
                            </label>
                            <select id="document_type"
                                    name="document_type"
                                    required
                                    class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                                @foreach(['DNI', 'RUC', 'CE', 'Pasaporte'] as $docType)
                                    <option value="{{ $docType }}" @selected(old('document_type', 'DNI') === $docType)>{{ $docType }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="document_number" class="block text-xs font-bold text-slate-700 mb-1">
                                N° de Documento <span class="text-[#DC2626]">*</span>
                            </label>
                            <input id="document_number"
                                   type="text"
                                   name="document_number"
                                   value="{{ old('document_number', auth()->user()?->ruc) }}"
                                   required
                                   maxlength="20"
                                   placeholder="Ej. 12345678 o 20608628143"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm font-mono text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                        </div>

                        <div>
                            <label for="consumer_phone" class="block text-xs font-bold text-slate-700 mb-1">
                                Teléfono / Celular <span class="text-[#DC2626]">*</span>
                            </label>
                            <input id="consumer_phone"
                                   type="tel"
                                   name="consumer_phone"
                                   value="{{ old('consumer_phone', auth()->user()?->phone) }}"
                                   required
                                   placeholder="Ej. 999 888 777"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm font-mono text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="consumer_name" class="block text-xs font-bold text-slate-700 mb-1">
                                Nombres y Apellidos Completos <span class="text-[#DC2626]">*</span>
                            </label>
                            <input id="consumer_name"
                                   type="text"
                                   name="consumer_name"
                                   value="{{ old('consumer_name', auth()->user()?->name) }}"
                                   required
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                        </div>

                        <div>
                            <label for="consumer_email" class="block text-xs font-bold text-slate-700 mb-1">
                                Correo Electrónico <span class="text-[#DC2626]">*</span>
                            </label>
                            <input id="consumer_email"
                                   type="email"
                                   name="consumer_email"
                                   value="{{ old('consumer_email', auth()->user()?->email) }}"
                                   required
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="consumer_address" class="block text-xs font-bold text-slate-700 mb-1">
                                Domicilio / Dirección <span class="text-[#DC2626]">*</span>
                            </label>
                            <input id="consumer_address"
                                   type="text"
                                   name="consumer_address"
                                   value="{{ old('consumer_address') }}"
                                   required
                                   placeholder="Dirección, Distrito, Provincia"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                        </div>

                        <div>
                            <label for="company_name" class="block text-xs font-bold text-slate-700 mb-1">
                                Razón Social / Empresa (Opcional)
                            </label>
                            <input id="company_name"
                                   type="text"
                                   name="company_name"
                                   value="{{ old('company_name', auth()->user()?->company) }}"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                        </div>
                    </div>
                </section>

                <!-- 2. Identificación del Bien Contratado -->
                <section class="space-y-4">
                    <div class="border-b border-slate-200 pb-2.5">
                        <h2 class="text-base sm:text-lg font-extrabold text-[#0F172A] uppercase tracking-wide">
                            2. Identificación del Bien Contratado
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="good_type" class="block text-xs font-bold text-slate-700 mb-1">
                                Tipo de Bien <span class="text-[#DC2626]">*</span>
                            </label>
                            <select id="good_type"
                                    name="good_type"
                                    required
                                    class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                                <option value="Producto" @selected(old('good_type', 'Producto') === 'Producto')>Producto</option>
                                <option value="Servicio" @selected(old('good_type') === 'Servicio')>Servicio</option>
                            </select>
                        </div>

                        <div>
                            <label for="claimed_amount" class="block text-xs font-bold text-slate-700 mb-1">
                                Monto Reclamado (S/) (Opcional)
                            </label>
                            <input id="claimed_amount"
                                   type="number"
                                   step="0.01"
                                   min="0"
                                   name="claimed_amount"
                                   value="{{ old('claimed_amount') }}"
                                   placeholder="0.00"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm font-mono text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                        </div>

                        <div>
                            <label for="good_description" class="block text-xs font-bold text-slate-700 mb-1">
                                Descripción del Producto / N° Cotización <span class="text-[#DC2626]">*</span>
                            </label>
                            <input id="good_description"
                                   type="text"
                                   name="good_description"
                                   value="{{ old('good_description') }}"
                                   required
                                   placeholder="Ej. Bisagra / COT-2026-00001"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                        </div>
                    </div>
                </section>

                <!-- 3. Detalle de la Reclamación y Pedido del Consumidor -->
                <section class="space-y-4">
                    <div class="border-b border-slate-200 pb-2.5">
                        <h2 class="text-base sm:text-lg font-extrabold text-[#0F172A] uppercase tracking-wide">
                            3. Detalle de la Reclamación y Pedido del Consumidor
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="flex items-start gap-3 p-4 rounded-xl border border-slate-300 bg-slate-50 cursor-pointer hover:border-[#1E3A8A]">
                            <input type="radio"
                                   name="claim_type"
                                   value="Reclamo"
                                   @checked(old('claim_type', 'Reclamo') === 'Reclamo')
                                   class="mt-1 h-4 w-4 text-[#1E3A8A] focus:ring-[#1D4ED8]">
                            <span class="text-xs leading-relaxed">
                                <strong class="block text-sm text-slate-900">RECLAMO</strong>
                                Disconformidad relacionada directamente a los productos o servicios adquiridos o cotizados.
                            </span>
                        </label>

                        <label class="flex items-start gap-3 p-4 rounded-xl border border-slate-300 bg-slate-50 cursor-pointer hover:border-[#1E3A8A]">
                            <input type="radio"
                                   name="claim_type"
                                   value="Queja"
                                   @checked(old('claim_type') === 'Queja')
                                   class="mt-1 h-4 w-4 text-[#1E3A8A] focus:ring-[#1D4ED8]">
                            <span class="text-xs leading-relaxed">
                                <strong class="block text-sm text-slate-900">QUEJA</strong>
                                Disconformidad no relacionada a los productos o servicios; o malestar o descontento respecto a la atención al público.
                            </span>
                        </label>
                    </div>

                    <div>
                        <label for="claim_detail" class="block text-xs font-bold text-slate-700 mb-1">
                            Detalle del Reclamo o Queja <span class="text-[#DC2626]">*</span>
                        </label>
                        <textarea id="claim_detail"
                                  name="claim_detail"
                                  rows="4"
                                  required
                                  placeholder="Explica de forma clara los hechos que motivan tu reclamo o queja..."
                                  class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">{{ old('claim_detail') }}</textarea>
                    </div>

                    <div>
                        <label for="consumer_request" class="block text-xs font-bold text-slate-700 mb-1">
                            Pedido Concreto del Consumidor <span class="text-[#DC2626]">*</span>
                        </label>
                        <textarea id="consumer_request"
                                  name="consumer_request"
                                  rows="3"
                                  required
                                  placeholder="Indica qué solución o acción solicitas respecto a tu reclamo o queja..."
                                  class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">{{ old('consumer_request') }}</textarea>
                    </div>
                </section>

                <!-- Legal Notice & Privacy -->
                <div class="space-y-4 pt-2">
                    <div class="bg-blue-50/70 border border-blue-200 rounded-xl p-4 text-xs text-slate-700 space-y-1.5 leading-relaxed">
                        <p><strong>Notas Legales (Ley N° 29571 — INDECOPI):</strong></p>
                        <p>&bull; La formulación del reclamo no impide acudir a otras vías de solución de controversias ni es requisito previo para interponer una denuncia ante el INDECOPI.</p>
                        <p>&bull; El proveedor (<strong>{{ $companyInfo['legal_name'] }} — RUC {{ $companyInfo['ruc'] }}</strong>) deberá dar respuesta al reclamo o queja en un plazo no mayor a quince (15) días hábiles improrrogables.</p>
                    </div>

                    <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200 flex items-start gap-2.5">
                        <input id="complaint_privacy"
                               type="checkbox"
                               name="privacy_accepted"
                               value="1"
                               @checked(old('privacy_accepted'))
                               required
                               class="mt-0.5 h-4 w-4 rounded border-slate-300 text-[#1E3A8A] focus:ring-[#1D4ED8]">
                        <label for="complaint_privacy" class="text-xs text-slate-700 leading-relaxed">
                            Declaro ser el titular del contenido de la presente Hoja de Reclamación y autorizo el tratamiento de mis datos personales para la gestión y respuesta de mi solicitud conforme a la <a href="{{ route('pages.privacy') }}" target="_blank" class="text-[#1D4ED8] font-bold underline">Política de Privacidad (Ley N° 29733)</a>. <span class="text-[#DC2626] font-bold">*</span>
                        </label>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                                class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-[#DC2626] hover:bg-[#B91C1C] text-white font-extrabold text-sm uppercase tracking-wider shadow-md transition-colors">
                            Enviar Hoja de Reclamación
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
