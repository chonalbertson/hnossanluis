@extends('layouts.admin')

@section('title', 'WhatsApp y Correo Corporativo')
@section('header', '12. Configuración de WhatsApp Business y Correo Corporativo')
@section('subheader', 'Integración de atención vía WhatsApp y herramienta segura de prueba de correo SMTP sin exposición de credenciales.')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 text-xs">
        <!-- WhatsApp & Emails Configuration -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4">
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Canales de Atención WhatsApp, Asesoras y Correo</h2>

            <form method="POST" action="{{ route('admin.settings.communication.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Número Principal de WhatsApp Business (con código de país, ej. 51977208514) <span class="text-rose-600">*</span></label>
                    <input type="text" name="whatsapp_business_number" value="{{ old('whatsapp_business_number', $settings['whatsapp_business_number']) }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono">
                </div>

                <!-- Asesoras y Números de WhatsApp Visibles en la Web -->
                <div class="bg-emerald-50/60 border border-emerald-200 rounded-xl p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xs font-extrabold text-emerald-950 uppercase tracking-wider flex items-center gap-1.5">
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-[#16A34A] text-white">
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                </span>
                                <span>Asesoras y Números de WhatsApp (Visibles en la Web)</span>
                            </h3>
                            <p class="text-[11px] text-slate-600 mt-0.5">
                                Edite aquí los números de WhatsApp y el nombre de cada asesora (ej. Angie, Gracia, Sabrina). Si deja el nombre vacío, solo se mostrará el número con su ícono de WhatsApp.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2.5">
                        @foreach($settings['whatsapp_advisors'] as $idx => $adv)
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center bg-white p-2.5 rounded-lg border border-emerald-200/80">
                                <div class="sm:col-span-1 flex items-center justify-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#16A34A] text-white font-bold">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    </span>
                                </div>
                                <div class="sm:col-span-5">
                                    <label class="block text-[10px] font-bold uppercase text-slate-500 mb-0.5">Nombre Asesora #{{ $idx + 1 }}</label>
                                    <input type="text"
                                           name="advisors[{{ $idx }}][name]"
                                           value="{{ old("advisors.{$idx}.name", $adv['name']) }}"
                                           placeholder="Ej. Angie / Gracia / Sabrina"
                                           class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-semibold text-slate-900">
                                </div>
                                <div class="sm:col-span-6">
                                    <label class="block text-[10px] font-bold uppercase text-slate-500 mb-0.5">Número de WhatsApp #{{ $idx + 1 }}</label>
                                    <input type="text"
                                           name="advisors[{{ $idx }}][phone]"
                                           value="{{ old("advisors.{$idx}.phone", $adv['phone']) }}"
                                           placeholder="Ej. 977 208 514"
                                           class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-mono font-bold text-slate-900">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Mensaje Base Predeterminado de WhatsApp <span class="text-rose-600">*</span></label>
                    <textarea name="whatsapp_default_message" rows="3" required class="w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('whatsapp_default_message', $settings['whatsapp_default_message']) }}</textarea>
                </div>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="whatsapp_floating_button_enabled" value="1" @checked($settings['whatsapp_floating_button_enabled']) class="rounded border-slate-300 text-emerald-600">
                    <span class="font-semibold text-slate-700">Habilitar botón flotante de WhatsApp en tienda pública</span>
                </label>

                <div class="pt-3 border-t border-slate-100 space-y-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Correo Corporativo Público (Visible en web y PDF) <span class="text-rose-600">*</span></label>
                        <input type="email" name="company_contact_email" value="{{ old('company_contact_email', $settings['company_contact_email']) }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Correo Receptor de Alertas de Nuevas Cotizaciones <span class="text-rose-600">*</span></label>
                        <input type="email" name="sales_notification_email" value="{{ old('sales_notification_email', $settings['sales_notification_email']) }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 cursor-pointer">
                        Guardar Asesoras, Números de WhatsApp y Correos
                    </button>
                </div>
            </form>
        </div>

        <!-- SMTP Status & Test Email Tool -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4">
                <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Estado del Servidor de Correo (SMTP Seguro)</h2>
                <p class="text-slate-600">
                    Por seguridad, las contraseñas SMTP se gestionan exclusivamente en las variables de entorno del servidor (<code>.env</code>) y nunca se muestran en pantalla ni en los registros de auditoría.
                </p>

                <div class="grid grid-cols-2 gap-3 bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <div>
                        <span class="text-slate-400 block">Transporte (Mailer)</span>
                        <strong class="font-mono text-slate-900 uppercase">{{ $mailSummary['mailer'] }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Servidor / Puerto</span>
                        <strong class="font-mono text-slate-900">{{ $mailSummary['host'] }}:{{ $mailSummary['port'] }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Remitente Configurado</span>
                        <strong class="text-slate-900">{{ $mailSummary['from_address'] }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Credenciales SMTP</span>
                        <strong class="{{ $mailSummary['has_credentials'] ? 'text-emerald-700' : 'text-amber-700' }}">
                            {{ $mailSummary['has_credentials'] ? 'Configuradas ([PROTEGIDO])' : 'Modo Local / Log' }}
                        </strong>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.settings.communication.test-mail') }}" class="space-y-3 pt-2 border-t border-slate-100">
                    @csrf
                    <label class="block font-semibold text-slate-700">Enviar Correo de Prueba de Conectividad SMTP</label>
                    <div class="flex gap-2">
                        <input type="email" name="recipient_email" value="{{ auth()->user()?->email }}" required placeholder="correo@destino.com"
                               class="flex-1 rounded-lg border border-slate-300 px-3 py-2">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-slate-900 text-white font-semibold hover:bg-slate-800 cursor-pointer">
                            Enviar Correo de Prueba
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-emerald-50 rounded-xl border border-emerald-200 p-5 space-y-2">
                <h3 class="text-sm font-bold text-emerald-950">Prueba de Enlace WhatsApp Business</h3>
                <p class="text-emerald-800">Puede verificar el formato de enlace prellenado con el número configurado:</p>
                <div class="pt-1">
                    <a href="{{ $sampleWhatsAppUrl }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white font-bold hover:bg-emerald-700">
                        Probar Enlace de WhatsApp &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
