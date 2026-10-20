@extends('layouts.public')

@section('content')
    @php
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Contacto Comercial', 'url' => route('contact.index')],
        ];
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-breadcrumb :items="$breadcrumbs" />

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Left Column: Corporate Contact Information from SiteSetting -->
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-[#0F172A] text-white rounded-2xl p-6 sm:p-8 border-b-4 border-[#DC2626] shadow-lg space-y-6">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-red-400">Canales Oficiales de Atención</span>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-white mt-1">
                            Contacto Comercial y Técnico
                        </h1>
                        <p class="text-sm text-slate-300 mt-2 leading-relaxed">
                            Comunícate con el equipo de <strong>{{ $contactInfo['commercial_name'] }}</strong> para atención mayorista B2B, abastecimiento de obras o soporte técnico sobre nuestro catálogo.
                        </p>
                    </div>

                    <div class="space-y-4 pt-2 border-t border-slate-800 text-sm">
                        <div class="bg-slate-800/80 rounded-xl p-3.5 border border-slate-700 space-y-1">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Razón Social y RUC</span>
                            <span class="font-extrabold text-white block">{{ $contactInfo['legal_name'] }}</span>
                            <span class="font-mono font-bold text-amber-400 block">RUC: {{ $contactInfo['ruc'] }}</span>
                        </div>

                        <div class="space-y-2.5">
                            <span class="text-xs font-bold uppercase text-[#BFDBFE] block">Cotiza por WhatsApp con nuestras asesoras:</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                @foreach(app(\App\Services\WhatsAppService::class)->getAdvisors() as $advisor)
                                    <a href="{{ $advisor['url'] }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       data-ga-event="whatsapp_click"
                                       data-ga-params='{"cta_location":"contact_page_card","context_type":"advisor"}'
                                       class="flex flex-col items-start bg-slate-800/90 hover:bg-slate-800 border border-slate-700 hover:border-[#22C55E] rounded-xl px-3 py-2.5 transition-all group">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-full bg-[#22C55E] text-white flex items-center justify-center shrink-0 shadow-xs">
                                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                                </svg>
                                            </span>
                                            <span class="font-mono text-sm font-black text-white group-hover:text-emerald-300 tabular-nums">
                                                {{ $advisor['phone'] }}
                                            </span>
                                        </div>
                                        @if($advisor['name'] !== '')
                                            <span class="mt-1.5 ml-8 inline-block px-2.5 py-0.5 rounded bg-[#BFDBFE] text-[#0F172A] text-[11px] font-extrabold">
                                                {{ $advisor['name'] }}
                                            </span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <span class="text-xs font-bold uppercase text-slate-400 block">Correo Corporativo</span>
                            <a href="mailto:{{ $contactInfo['email'] }}" class="font-bold text-blue-300 hover:text-white underline">
                                {{ $contactInfo['email'] }}
                            </a>
                        </div>

                        <div>
                            <span class="text-xs font-bold uppercase text-slate-400 block">Dirección Comercial (Tienda y Despachos)</span>
                            <span class="text-white font-semibold block">{{ $contactInfo['address'] }}</span>
                            <a href="https://www.google.com/maps/search/?api=1&query={{ rawurlencode('XX34+W4J Lima') }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="inline-flex items-center gap-1 text-xs font-bold text-amber-400 hover:text-amber-300 mt-1 underline">
                                <span>Ver ubicación en Google Maps</span>
                                <span>&rarr;</span>
                            </a>
                        </div>

                        <div>
                            <span class="text-xs font-bold uppercase text-slate-400 block">Horario de Atención</span>
                            <span class="text-slate-200">{{ $contactInfo['hours'] }}</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-800 space-y-3">
                        <x-whatsapp-button
                            variant="inline"
                            :url="$whatsappUrl"
                            label="Iniciar Chat en WhatsApp Business"
                            ctaLocation="contact_page"
                            contextType="general"
                            class="w-full py-3 text-sm" />

                        <a href="{{ route('pages.complaints-book') }}"
                           class="flex items-center justify-center gap-2.5 w-full px-4 py-3 rounded-xl bg-white hover:bg-blue-50 text-[#0F172A] font-extrabold text-xs uppercase tracking-wider border-2 border-[#1E3A8A] transition-colors">
                            <svg class="w-5 h-5 text-[#1E3A8A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <span>Libro de Reclamaciones Virtual</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Column: Validated Contact Form with Honeypot & Rate Limiting -->
            <div class="lg:col-span-7">
                <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs">
                    <h2 class="text-xl font-extrabold text-[#0F172A] section-heading-bar mb-2">
                        Envíanos una Consulta o Requerimiento
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-600 mb-6 pl-4">
                        Completa tus datos de contacto y el detalle de tu consulta. Los campos marcados con <span class="text-[#DC2626] font-bold">*</span> son obligatorios.
                    </p>

                    <form action="{{ route('contact.submit') }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Anti-spam Honeypot -->
                        <div class="hidden" aria-hidden="true">
                            <label for="contact-website-hp">No llenar este campo</label>
                            <input id="contact-website-hp" type="text" name="website_hp" value="" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-xs font-bold text-slate-700 mb-1">
                                    Nombre Completo <span class="text-[#DC2626]">*</span>
                                </label>
                                <input id="name"
                                       type="text"
                                       name="name"
                                       value="{{ old('name', auth()->user()?->name) }}"
                                       required
                                       class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-bold text-slate-700 mb-1">
                                    Correo Electrónico <span class="text-[#DC2626]">*</span>
                                </label>
                                <input id="email"
                                       type="email"
                                       name="email"
                                       value="{{ old('email', auth()->user()?->email) }}"
                                       required
                                       class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="phone" class="block text-xs font-bold text-slate-700 mb-1">
                                    Teléfono / Celular
                                </label>
                                <input id="phone"
                                       type="tel"
                                       name="phone"
                                       value="{{ old('phone', auth()->user()?->phone) }}"
                                       class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm font-mono text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                            </div>

                            <div>
                                <label for="company" class="block text-xs font-bold text-slate-700 mb-1">
                                    Empresa (Opcional)
                                </label>
                                <input id="company"
                                       type="text"
                                       name="company"
                                       value="{{ old('company', auth()->user()?->company) }}"
                                       class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                            </div>

                            <div>
                                <label for="contact-ruc" class="block text-xs font-bold text-slate-700 mb-1">
                                    RUC (11 dígitos)
                                </label>
                                <input id="contact-ruc"
                                       type="text"
                                       name="ruc"
                                       maxlength="11"
                                       pattern="\d{11}"
                                       value="{{ old('ruc', auth()->user()?->ruc) }}"
                                       class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm font-mono text-slate-900 tabular-nums focus:border-[#1D4ED8] focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <label for="subject" class="block text-xs font-bold text-slate-700 mb-1">
                                Asunto o Motivo de Consulta <span class="text-[#DC2626]">*</span>
                            </label>
                            <input id="subject"
                                   type="text"
                                   name="subject"
                                   value="{{ old('subject') }}"
                                   required
                                   placeholder="Ej. Consulta de abastecimiento por volumen / Distribución / Especificación técnica"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                        </div>

                        <div>
                            <label for="message" class="block text-xs font-bold text-slate-700 mb-1">
                                Mensaje o Detalle Técnico <span class="text-[#DC2626]">*</span>
                            </label>
                            <textarea id="message"
                                      name="message"
                                      rows="4"
                                      required
                                      placeholder="Escribe los detalles de tu consulta comercial o técnica..."
                                      class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">{{ old('message') }}</textarea>
                        </div>

                        <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200 flex items-start gap-2.5">
                            <input id="contact_privacy"
                                   type="checkbox"
                                   name="privacy_accepted"
                                   value="1"
                                   @checked(old('privacy_accepted'))
                                   required
                                   class="mt-0.5 h-4 w-4 rounded border-slate-300 text-[#1E3A8A] focus:ring-[#1D4ED8]">
                            <label for="contact_privacy" class="text-xs text-slate-700 leading-relaxed">
                                Autorizo el tratamiento de mis datos personales para atender mi consulta comercial conforme a la <a href="{{ route('pages.privacy') }}" target="_blank" class="text-[#1D4ED8] font-bold underline">Política de Privacidad (Ley N° 29733)</a>. <span class="text-[#DC2626] font-bold">*</span>
                            </label>
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                    class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-[#DC2626] hover:bg-[#B91C1C] text-white font-extrabold text-sm uppercase tracking-wider shadow-md transition-colors">
                                Enviar Mensaje
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Mapa de Ubicación (Plus Code XX34+W4J Lima) -->
        @php
            $mapPlusCode = 'XX34+W4J Lima';
            $mapEmbedUrl = 'https://maps.google.com/maps?q='.rawurlencode($mapPlusCode.', Perú').'&z=17&hl=es&output=embed';
            $mapDirectionsUrl = 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($mapPlusCode);
        @endphp
        <section class="mt-10 rounded-2xl overflow-hidden border border-slate-200 bg-white shadow-xs" aria-labelledby="contact-map-title">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-5 sm:px-6 py-4 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-[#DC2626] text-white flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    <div>
                        <h2 id="contact-map-title" class="text-base sm:text-lg font-extrabold text-[#0F172A]">Visítanos en nuestra tienda</h2>
                        <p class="text-xs sm:text-sm text-slate-600">{{ $contactInfo['address'] }} &bull; <span class="font-mono font-bold text-[#1E3A8A]">{{ $mapPlusCode }}</span></p>
                    </div>
                </div>
                <a href="{{ $mapDirectionsUrl }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-[#0F172A] text-white text-xs font-extrabold uppercase tracking-wider transition-colors shrink-0">
                    Cómo llegar
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
            <iframe title="Mapa de ubicación de {{ $contactInfo['legal_name'] }}"
                    src="{{ $mapEmbedUrl }}"
                    class="w-full h-80 sm:h-[420px] border-0 block"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen></iframe>
        </section>
    </div>
@endsection
