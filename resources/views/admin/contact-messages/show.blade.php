@extends('layouts.admin')

@section('title', 'Detalle de Mensaje #' . $contactMessage->id)
@section('header', 'Mensaje de Contacto #' . $contactMessage->id)
@section('subheader', 'Recibido el ' . ($contactMessage->created_at?->format('d/m/Y H:i') ?? 'N/A'))

@section('content')
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('admin.contact-messages.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900">
            <span>&larr; Volver a la bandeja de mensajes</span>
        </a>

        @php
            $badgeClass = match($contactMessage->status) {
                'nuevo' => 'bg-amber-100 text-amber-800 border-amber-200',
                'leido' => 'bg-blue-100 text-blue-800 border-blue-200',
                'respondido' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                default => 'bg-slate-100 text-slate-700 border-slate-200',
            };
            $statusLabel = match($contactMessage->status) {
                'nuevo' => 'Nuevo',
                'leido' => 'Leído',
                'respondido' => 'Respondido',
                default => ucfirst((string) $contactMessage->status),
            };
        @endphp
        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold border {{ $badgeClass }}">
            Estado actual: {{ $statusLabel }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Message Content & Quick B2B Conversion -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4">
                <div class="border-b border-slate-100 pb-4">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Asunto de la consulta</span>
                    <h2 class="text-lg font-bold text-slate-900 mt-1">{{ $contactMessage->subject ?: 'Consulta comercial general' }}</h2>
                </div>

                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Mensaje del cliente</span>
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 text-sm text-slate-800 whitespace-pre-line leading-relaxed">{{ $contactMessage->message }}</div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500">
                    <div>
                        Consentimiento Ley N° 29733:
                        <strong class="text-slate-700">
                            {{ $contactMessage->privacy_accepted_at ? $contactMessage->privacy_accepted_at->format('d/m/Y H:i') : 'Aceptado en formulario web' }}
                        </strong>
                    </div>
                    @if($contactMessage->ip_address)
                        <div>IP Origen: <span class="font-mono text-slate-700">{{ $contactMessage->ip_address }}</span></div>
                    @endif
                </div>
            </div>

            <!-- Direct Reply & Conversion Actions -->
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4">
                <h3 class="text-sm font-bold text-slate-900">Acciones Comerciales y Conversión en 1 Clic</h3>
                <p class="text-xs text-slate-500">
                    Responda directamente al remitente o convierta esta consulta en una cotización formal o cliente autorizado B2B prellenando sus datos automáticamente.
                </p>

                <div class="flex flex-wrap gap-3 pt-2">
                    @if($whatsAppReplyUrl)
                        <a href="{{ $whatsAppReplyUrl }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-500 text-xs font-bold transition">
                            <span>Responder por WhatsApp</span>
                        </a>
                    @endif

                    <a href="mailto:{{ $contactMessage->email }}?subject={{ rawurlencode('Re: ' . ($contactMessage->subject ?: 'Consulta Import Hns San Luis')) }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-500 text-xs font-bold transition">
                        <span>Responder por Correo ({{ $contactMessage->email }})</span>
                    </a>

                    @if(auth()->user()?->hasPermission('quotes.manage') || auth()->user()?->hasPermission('quotes.respond'))
                        <a href="{{ route('admin.quotes.create', [
                            'customer_id' => $existingCustomer?->id,
                            'contact_name' => $contactMessage->name,
                            'contact_email' => $contactMessage->email,
                            'contact_phone' => $contactMessage->phone,
                            'company_name' => $contactMessage->company,
                            'ruc' => $contactMessage->ruc,
                            'origin' => 'manual',
                            'customer_observations' => trim(($contactMessage->subject ? $contactMessage->subject . ': ' : '') . $contactMessage->message),
                        ]) }}"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-amber-500 text-slate-950 hover:bg-amber-400 text-xs font-bold transition">
                            <span>+ Iniciar Cotización Manual con estos datos</span>
                        </a>
                    @endif

                    @if(auth()->user()?->hasPermission('customers.manage'))
                        @if($existingCustomer)
                            <a href="{{ route('admin.customers.show', $existingCustomer) }}"
                               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                                <span>Ver Ficha de Cliente Registrado</span>
                            </a>
                        @else
                            <a href="{{ route('admin.customers.create', [
                                'name' => $contactMessage->name,
                                'email' => $contactMessage->email,
                                'phone' => $contactMessage->phone,
                                'company' => $contactMessage->company,
                                'ruc' => $contactMessage->ruc,
                                'notes' => 'Registrado desde consulta web #' . $contactMessage->id . ': ' . ($contactMessage->subject ?: 'Contacto'),
                            ]) }}"
                               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-800 hover:bg-slate-50 text-xs font-semibold transition">
                                <span>Registrar como Cliente Autorizado</span>
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Sender Info & Status Management -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs space-y-3 text-xs">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Datos del Remitente</h3>
                <div>
                    <span class="text-slate-500 block">Nombre de Contacto:</span>
                    <strong class="text-slate-900 text-sm">{{ $contactMessage->name }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block">Correo Electrónico:</span>
                    <a href="mailto:{{ $contactMessage->email }}" class="font-semibold text-blue-600 hover:underline">{{ $contactMessage->email }}</a>
                </div>
                <div>
                    <span class="text-slate-500 block">Teléfono / Celular:</span>
                    <strong class="text-slate-800">{{ $contactMessage->phone ?: 'No especificado' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block">Empresa / Razón Social:</span>
                    <strong class="text-slate-800">{{ $contactMessage->company ?: 'No especificada' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block">RUC:</span>
                    <strong class="font-mono text-slate-800">{{ $contactMessage->ruc ?: 'No especificado' }}</strong>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs space-y-4 text-xs">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Actualizar Estado de Atención</h3>

                <form method="POST" action="{{ route('admin.contact-messages.status', $contactMessage) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Estado del mensaje</label>
                        <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                            <option value="nuevo" @selected($contactMessage->status === 'nuevo')>Nuevo</option>
                            <option value="leido" @selected($contactMessage->status === 'leido')>Leído</option>
                            <option value="respondido" @selected($contactMessage->status === 'respondido')>Respondido</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-bold cursor-pointer">
                        Guardar Estado
                    </button>
                </form>

                @if($contactMessage->status !== 'respondido')
                    <form method="POST" action="{{ route('admin.contact-messages.status', $contactMessage) }}">
                        @csrf
                        <input type="hidden" name="status" value="respondido">
                        <button type="submit" class="w-full px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-500 font-bold cursor-pointer">
                            ✓ Marcar como Respondido
                        </button>
                    </form>
                @endif
            </div>

            @if(auth()->user()?->hasPermission('quotes.manage'))
                <div class="bg-white rounded-xl border border-rose-200 p-5 shadow-2xs text-xs space-y-3">
                    <h3 class="text-sm font-bold text-rose-900">Zona de Eliminación</h3>
                    <form method="POST" action="{{ route('admin.contact-messages.destroy', $contactMessage) }}"
                          onsubmit="return confirm('¿Está seguro de eliminar este mensaje de contacto?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2 rounded-lg border border-rose-300 text-rose-700 hover:bg-rose-50 font-semibold cursor-pointer">
                            Eliminar Mensaje
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
