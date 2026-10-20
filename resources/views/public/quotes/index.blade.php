@extends('layouts.public')

@section('content')
    @php
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Solicitar Cotización', 'url' => route('quote.index')],
        ];

        $hasCartItems = !empty($cartItems);
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-breadcrumb :items="$breadcrumbs" />

        <!-- Page Heading -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6 mb-8 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F172A] section-heading-bar">
                    Solicitud de Cotización Técnica (B2B / B2C)
                </h1>
                <p class="text-sm text-slate-600 mt-1.5 pl-4">
                    Revisa las cantidades y especificaciones de tu lista de productos y envía tu requerimiento formal para atención prioritaria.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="{{ route('quote.track') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-blue-200 bg-blue-50 hover:bg-blue-100 text-xs font-bold uppercase tracking-wider text-[#1E3A8A]">
                    <svg class="w-4 h-4 text-[#1E3A8A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Seguimiento de Cotización</span>
                </a>
                <a href="{{ route('catalog.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-slate-300 bg-slate-50 hover:bg-slate-100 text-xs font-bold uppercase tracking-wider text-slate-800">
                    <span>+ Seguir agregando productos</span>
                </a>
            </div>
        </div>

        @if(!$hasCartItems && !$directProduct)
            <!-- Empty Quote Cart State -->
            <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center max-w-2xl mx-auto shadow-2xs space-y-5">
                <div class="w-16 h-16 rounded-full bg-blue-50 text-[#1E3A8A] mx-auto flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h2 class="text-xl font-extrabold text-[#0F172A]">Tu lista de cotización está vacía</h2>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Explora nuestro catálogo técnico por código SKU, categoría o medida y haz clic en <strong>"Cotizar"</strong> para armar tu lista de requerimiento.
                </p>
                <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                    <a href="{{ route('catalog.index') }}"
                       class="px-6 py-3 rounded-lg bg-[#DC2626] hover:bg-[#B91C1C] text-white font-bold text-xs uppercase tracking-wider transition-colors">
                        Explorar Catálogo Técnico
                    </a>
                    <a href="{{ route('quote.track') }}"
                       class="px-6 py-3 rounded-lg bg-[#1E3A8A] hover:bg-[#1D4ED8] text-white font-bold text-xs uppercase tracking-wider transition-colors">
                        Seguimiento de Cotización
                    </a>
                    <a href="{{ route('contact.index') }}"
                       class="px-6 py-3 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 font-bold text-xs uppercase tracking-wider transition-colors">
                        Consulta Especial sin SKU
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Left Column: Items List (Cart or Direct Single Product) -->
                <div class="lg:col-span-7 space-y-6">
                    @if($directProduct && !$hasCartItems)
                        <!-- Direct Individual Product Quote Box -->
                        <div class="bg-white rounded-xl border-2 border-[#1E3A8A] overflow-hidden shadow-2xs">
                            <div class="bg-[#1E3A8A] text-white px-5 py-3 flex items-center justify-between">
                                <h2 class="text-sm font-bold uppercase tracking-wider">Cotización Directa de Producto</h2>
                                <span class="font-mono text-xs bg-blue-950 px-2.5 py-0.5 rounded">SKU: {{ $directVariant?->sku ?: $directProduct->sku }}</span>
                            </div>
                            <div class="p-5 flex items-center gap-4">
                                <img src="{{ $directProduct->primaryImageUrl() }}"
                                     alt="{{ $directProduct->name }}"
                                     class="w-20 h-20 object-contain rounded border border-slate-200 p-1.5 bg-white shrink-0">
                                <div class="space-y-1">
                                    <h3 class="text-base font-bold text-[#0F172A]">
                                        {{ $directVariant ? ($directVariant->name ?: $directProduct->name . ' — ' . $directVariant->variant_label) : $directProduct->name }}
                                    </h3>
                                    @if($directVariant)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-50 text-[#1E3A8A] border border-blue-200 text-[11px] font-bold">
                                            Variante: {{ $directVariant->variant_label }}
                                        </span>
                                    @endif
                                    <p class="text-xs text-slate-600">Unidad de venta: <strong>{{ $directVariant?->sale_unit ?: $directProduct->sale_unit }}</strong></p>
                                    @php
                                        $directPriceFormatted = $directVariant ? $directVariant->formattedPrice($directProduct) : $directProduct->formattedPrice();
                                    @endphp
                                    @if($directPriceFormatted)
                                        <p class="text-sm font-extrabold text-[#0F172A] tabular-nums">{{ $directPriceFormatted }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($hasCartItems)
                        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-2xs">
                            <div class="bg-[#0F172A] text-white px-5 py-3.5 flex items-center justify-between">
                                <h2 class="text-sm font-bold uppercase tracking-wider">
                                    Productos en tu Pliego ({{ count($cartItems) }} {{ count($cartItems) === 1 ? 'ítem' : 'ítems' }})
                                </h2>
                                <form action="{{ route('quote.clear') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-red-300 hover:text-white underline">
                                        Vaciar lista
                                    </button>
                                </form>
                            </div>

                            <form action="{{ route('quote.update') }}" method="POST" id="update-cart-form">
                                @csrf
                                <div class="divide-y divide-slate-200">
                                    @foreach($cartItems as $pid => $item)
                                        <div class="p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                            <a href="{{ $item['slug'] ? route('catalog.show', $item['slug']) : '#' }}"
                                               class="w-16 h-16 rounded-lg border border-slate-200 bg-white p-1.5 flex items-center justify-center shrink-0">
                                                <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" class="max-h-full max-w-full object-contain">
                                            </a>

                                            <div class="flex-1 min-w-0 space-y-1.5 w-full">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="font-mono text-xs font-bold bg-slate-100 text-slate-800 px-2 py-0.5 rounded border border-slate-200 tabular-nums">
                                                        {{ $item['sku'] }}
                                                    </span>
                                                    @if(!empty($item['variant_label']))
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-50 text-[#1E3A8A] border border-blue-200 text-[11px] font-bold">
                                                            Variante: {{ $item['variant_label'] }}
                                                        </span>
                                                    @endif
                                                    <span class="text-xs text-slate-500 font-medium">
                                                        Unidad: <strong>{{ $item['sale_unit'] }}</strong>
                                                    </span>
                                                    @if(!empty($item['formatted_price']))
                                                        <span class="text-xs font-bold text-[#1E3A8A] tabular-nums">
                                                            Ref: {{ $item['formatted_price'] }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <h3 class="text-sm font-bold text-[#0F172A]">
                                                    @if($item['slug'])
                                                        <a href="{{ route('catalog.show', $item['slug']) }}" class="hover:text-[#1D4ED8]">{{ $item['name'] }}</a>
                                                    @else
                                                        {{ $item['name'] }}
                                                    @endif
                                                </h3>

                                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 pt-1">
                                                    <div class="sm:col-span-4">
                                                        <label for="cart-qty-{{ $pid }}" class="block text-[11px] font-bold text-slate-600 mb-0.5">
                                                            Cantidad ({{ $item['sale_unit'] }})
                                                        </label>
                                                        <input id="cart-qty-{{ $pid }}"
                                                               type="number"
                                                               name="items[{{ $pid }}][quantity]"
                                                               value="{{ $item['quantity'] }}"
                                                               min="1"
                                                               step="1"
                                                               class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-bold text-slate-900 tabular-nums">
                                                    </div>
                                                    <div class="sm:col-span-8">
                                                        <label for="cart-notes-{{ $pid }}" class="block text-[11px] font-bold text-slate-600 mb-0.5">
                                                            Observación por ítem (opcional)
                                                        </label>
                                                        <input id="cart-notes-{{ $pid }}"
                                                               type="text"
                                                               name="items[{{ $pid }}][notes]"
                                                               value="{{ $item['notes'] }}"
                                                               placeholder="Medida, color o detalle específico..."
                                                               class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="self-end sm:self-center shrink-0">
                                                <button type="submit"
                                                        form="remove-item-{{ $pid }}"
                                                        aria-label="Eliminar {{ $item['name'] }} de la lista"
                                                        class="p-2 text-slate-400 hover:text-[#DC2626] rounded-lg hover:bg-red-50 transition-colors"
                                                        title="Quitar ítem">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="bg-slate-50 px-5 py-3 border-t border-slate-200 flex items-center justify-between">
                                    <span class="text-xs text-slate-600">¿Modificaste cantidades o notas por ítem?</span>
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#1E3A8A] hover:bg-[#1D4ED8] text-white text-xs font-bold uppercase tracking-wider transition-colors">
                                        Actualizar Cantidades
                                    </button>
                                </div>
                            </form>

                            @foreach($cartItems as $pid => $item)
                                <form id="remove-item-{{ $pid }}" action="{{ route('quote.remove', $pid) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Right Column: Formal Quote Submission Form -->
                <div class="lg:col-span-5">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-md overflow-hidden sticky top-24">
                        <div class="bg-[#1E3A8A] text-white px-6 py-4 border-b-4 border-[#DC2626]">
                            <h2 class="text-base font-extrabold uppercase tracking-wide">Datos del Solicitante</h2>
                            <p class="text-xs text-blue-100 mt-0.5">Recibirás un código de seguimiento único (COT-YYYY-NNNNN).</p>
                        </div>

                        <form action="{{ route('quote.submit') }}" method="POST" class="p-6 space-y-4">
                            @csrf

                            <!-- Honeypot Antispam -->
                            <div class="hidden" aria-hidden="true">
                                <label for="quote-website-hp">No completar</label>
                                <input id="quote-website-hp" type="text" name="website_hp" value="" tabindex="-1" autocomplete="off">
                            </div>

                            @if($directProduct && !$hasCartItems)
                                <input type="hidden" name="request_type" value="single">
                                <input type="hidden" name="product_id" value="{{ $directProduct->id }}">
                                @if(!empty($directVariant))
                                    <input type="hidden" name="variant_id" value="{{ $directVariant->id }}">
                                @endif
                                <div>
                                    <label for="direct-qty" class="block text-xs font-bold text-slate-700 mb-1">
                                        Cantidad requerida ({{ $directVariant?->sale_unit ?: $directProduct->sale_unit }}) *
                                    </label>
                                    <input id="direct-qty"
                                           type="number"
                                           name="quantity"
                                           value="{{ old('quantity', $directQuantity) }}"
                                           min="1"
                                           step="1"
                                           required
                                           class="w-full rounded-lg border-2 border-slate-300 px-3 py-2 text-sm font-bold tabular-nums">
                                </div>
                            @endif

                            <div>
                                <label for="contact_name" class="block text-xs font-bold text-slate-700 mb-1">
                                    Nombre Completo de Contacto <span class="text-[#DC2626]">*</span>
                                </label>
                                <input id="contact_name"
                                       type="text"
                                       name="contact_name"
                                       value="{{ old('contact_name', $user?->name) }}"
                                       required
                                       placeholder="Ej. Ing. Roberto Quispe"
                                       class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="contact_email" class="block text-xs font-bold text-slate-700 mb-1">
                                        Correo Electrónico <span class="text-[#DC2626]">*</span>
                                    </label>
                                    <input id="contact_email"
                                           type="email"
                                           name="contact_email"
                                           value="{{ old('contact_email', $user?->email) }}"
                                           required
                                           placeholder="correo@empresa.pe"
                                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                                </div>

                                <div>
                                    <label for="contact_phone" class="block text-xs font-bold text-slate-700 mb-1">
                                        Teléfono / WhatsApp <span class="text-[#DC2626]">*</span>
                                    </label>
                                    <input id="contact_phone"
                                           type="tel"
                                           name="contact_phone"
                                           value="{{ old('contact_phone', $user?->phone) }}"
                                           required
                                           placeholder="+51 999 000 000"
                                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm font-mono text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1 border-t border-slate-100">
                                <div>
                                    <label for="company_name" class="block text-xs font-bold text-slate-700 mb-1">
                                        Empresa / Razón Social <span class="text-slate-400 font-normal">(Opcional)</span>
                                    </label>
                                    <input id="company_name"
                                           type="text"
                                           name="company_name"
                                           value="{{ old('company_name', $user?->company) }}"
                                           placeholder="Constructora / Taller..."
                                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">
                                </div>

                                <div>
                                    <label for="ruc" class="block text-xs font-bold text-slate-700 mb-1">
                                        RUC <span class="text-slate-400 font-normal">(11 dígitos, opcional)</span>
                                    </label>
                                    <input id="ruc"
                                           type="text"
                                           name="ruc"
                                           maxlength="11"
                                           pattern="\d{11}"
                                           value="{{ old('ruc', $user?->ruc) }}"
                                           placeholder="20554433221"
                                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm font-mono text-slate-900 tabular-nums focus:border-[#1D4ED8] focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label for="customer_observations" class="block text-xs font-bold text-slate-700 mb-1">
                                    Observaciones Generales del Proyecto / Despacho
                                </label>
                                <textarea id="customer_observations"
                                          name="customer_observations"
                                          rows="3"
                                          placeholder="Indica lugar de entrega, plazo requerido o condiciones especiales..."
                                          class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1D4ED8] focus:outline-none">{{ old('customer_observations') }}</textarea>
                            </div>

                            <!-- Mandatory Ley N° 29733 Privacy Checkbox -->
                            <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200 flex items-start gap-2.5">
                                <input id="privacy_accepted"
                                       type="checkbox"
                                       name="privacy_accepted"
                                       value="1"
                                       @checked(old('privacy_accepted'))
                                       required
                                       class="mt-0.5 h-4 w-4 rounded border-slate-300 text-[#1E3A8A] focus:ring-[#1D4ED8]">
                                <label for="privacy_accepted" class="text-xs text-slate-700 leading-relaxed">
                                    He leído y acepto la <a href="{{ route('pages.privacy') }}" target="_blank" class="text-[#1D4ED8] font-bold underline">Política de Privacidad (Ley N° 29733)</a> y las <a href="{{ route('pages.quote-conditions') }}" target="_blank" class="text-[#1D4ED8] font-bold underline">Condiciones de Cotización</a>. <span class="text-[#DC2626] font-bold">*</span>
                                </label>
                            </div>

                            <button type="submit"
                                    class="w-full bg-[#DC2626] hover:bg-[#B91C1C] text-white font-extrabold py-3.5 px-6 rounded-xl text-sm uppercase tracking-wider shadow-lg transition-colors">
                                Enviar Solicitud de Cotización
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
