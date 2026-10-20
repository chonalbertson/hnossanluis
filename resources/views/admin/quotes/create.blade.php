@extends('layouts.admin')

@section('title', 'Nueva Cotización Manual')
@section('header', 'Registrar Solicitud de Cotización Manual / WhatsApp')
@section('subheader', 'Creación directa de requerimientos recibidos por WhatsApp Business, teléfono o atención presencial B2B.')

@section('content')
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.quotes.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900">
            <span>&larr; Volver al listado de cotizaciones</span>
        </a>
    </div>

    <form method="POST" action="{{ route('admin.quotes.store') }}" class="space-y-6 text-xs">
        @csrf

        <!-- 1. Datos del Cliente y Canal -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">1. Datos del Cliente y Canal de Recepción</h2>
                    <p class="text-xs text-slate-500">Seleccione un cliente registrado para autocompletar sus datos o ingrese un nuevo contacto.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1">Cargar desde Cliente Registrado (Opcional)</label>
                    <select id="customer_selector" name="customer_id" onchange="fillCustomerFromSelector()" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                        <option value="">— Seleccionar cliente existente o ingresar manualmente abajo —</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}"
                                    data-name="{{ $customer->name }}"
                                    data-email="{{ $customer->email }}"
                                    data-phone="{{ $customer->phone }}"
                                    data-company="{{ $customer->company }}"
                                    data-ruc="{{ $customer->ruc }}"
                                    @selected((string) old('customer_id', request('customer_id')) === (string) $customer->id)>
                                {{ $customer->name }} {{ $customer->company ? '— ' . $customer->company : '' }} {{ $customer->ruc ? '(RUC: ' . $customer->ruc . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Canal de Origen <span class="text-rose-600">*</span></label>
                    <select name="origin" required class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white font-semibold">
                        <option value="whatsapp" @selected(old('origin', request('origin', 'whatsapp')) === 'whatsapp')>WhatsApp Business</option>
                        <option value="manual" @selected(old('origin', request('origin')) === 'manual')>Atención Telefónica / Presencial / Correo</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nombre del Contacto <span class="text-rose-600">*</span></label>
                    <input type="text" id="contact_name" name="contact_name" value="{{ old('contact_name', request('contact_name')) }}" required
                           placeholder="Ej. Ing. Carlos Quispe"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Correo Electrónico <span class="text-rose-600">*</span></label>
                    <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email', request('contact_email')) }}" required
                           placeholder="compras@empresa.com.pe"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Teléfono / WhatsApp <span class="text-rose-600">*</span></label>
                    <input type="text" id="contact_phone" name="contact_phone" value="{{ old('contact_phone', request('contact_phone')) }}" required
                           placeholder="+51 999 888 777"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1">Empresa / Razón Social (Opcional)</label>
                    <input type="text" id="company_name" name="company_name" value="{{ old('company_name', request('company_name')) }}"
                           placeholder="Constructora y Mobiliario S.A.C."
                           class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">RUC (11 dígitos, opcional)</label>
                    <input type="text" id="ruc" name="ruc" value="{{ old('ruc', request('ruc')) }}" maxlength="11"
                           placeholder="20123456789"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Moneda</label>
                    <select name="currency" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                        <option value="PEN" @selected(old('currency', $defaultCurrency) === 'PEN')>PEN (Soles S/)</option>
                        <option value="USD" @selected(old('currency', $defaultCurrency) === 'USD')>USD (Dólares $)</option>
                        <option value="EUR" @selected(old('currency', $defaultCurrency) === 'EUR')>EUR (Euros €)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Ejecutivo Comercial Asignado</label>
                    <select name="assigned_seller_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                        <option value="">— Asignarme a mí ({{ auth()->user()?->name }}) —</option>
                        @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}" @selected((string) old('assigned_seller_id', auth()->id()) === (string) $seller->id)>
                                {{ $seller->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1">Observaciones o Requerimiento del Cliente</label>
                    <input type="text" name="customer_observations" value="{{ old('customer_observations', request('customer_observations')) }}"
                           placeholder="Ej. Solicita despacho en obra San Isidro / precios por volumen..."
                           class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>
        </div>

        <!-- 2. Selección Dinámica de Productos del Catálogo -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">2. Productos Solicitados del Catálogo Técnico</h2>
                    <p class="text-xs text-slate-500">Agregue uno o varios productos SKU indicando la cantidad requerida y notas por línea.</p>
                </div>
                <button type="button" onclick="addQuoteItemRow()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 text-xs font-semibold cursor-pointer">
                    <span>+ Agregar Línea de Producto</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs" id="quote-items-table">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                        <tr>
                            <th class="px-3 py-2.5 text-left w-1/2">Producto (SKU — Nombre — Unidad)</th>
                            <th class="px-3 py-2.5 text-center w-28">Cantidad</th>
                            <th class="px-3 py-2.5 text-left">Nota Técnica / Especificación por Ítem</th>
                            <th class="px-3 py-2.5 text-right w-20">Quitar</th>
                        </tr>
                    </thead>
                    <tbody id="quote-items-body" class="divide-y divide-slate-100">
                        @php
                            $oldItems = old('items', [
                                ['product_id' => request('product_id', ''), 'quantity' => 1, 'notes' => '']
                            ]);
                        @endphp
                        @foreach($oldItems as $idx => $itemRow)
                            <tr class="quote-item-row">
                                <td class="px-3 py-2.5">
                                    <select name="items[{{ $idx }}][product_id]" required class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                                        <option value="">— Seleccione un producto SKU —</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" @selected((string) ($itemRow['product_id'] ?? '') === (string) $product->id)>
                                                [{{ $product->sku }}] {{ $product->name }} ({{ $product->sale_unit }})
                                                @if((float) $product->price > 0) — Ref: {{ $product->currency }} {{ number_format((float) $product->price, 2) }} @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-2.5">
                                    <input type="number" name="items[{{ $idx }}][quantity]" value="{{ $itemRow['quantity'] ?? 1 }}"
                                           min="0.01" step="0.01" required
                                           class="w-full rounded-lg border border-slate-300 px-2.5 py-2 text-center font-bold">
                                </td>
                                <td class="px-3 py-2.5">
                                    <input type="text" name="items[{{ $idx }}][notes]" value="{{ $itemRow['notes'] ?? '' }}"
                                           placeholder="Color, medida, acabado o comentario opcional..."
                                           class="w-full rounded-lg border border-slate-300 px-3 py-2">
                                </td>
                                <td class="px-3 py-2.5 text-right">
                                    <button type="button" onclick="removeQuoteItemRow(this)"
                                            class="px-2.5 py-1.5 rounded-lg border border-rose-200 text-rose-700 hover:bg-rose-50 font-medium cursor-pointer">
                                        ✕
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.quotes.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 font-semibold">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 shadow-xs cursor-pointer">
                Registrar Cotización y Pasar a Oferta Económica &rarr;
            </button>
        </div>
    </form>

    <!-- Hidden Template for New Product Rows -->
    <template id="quote-item-row-template">
        <tr class="quote-item-row">
            <td class="px-3 py-2.5">
                <select name="items[__INDEX__][product_id]" required class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                    <option value="">— Seleccione un producto SKU —</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">
                            [{{ $product->sku }}] {{ $product->name }} ({{ $product->sale_unit }})
                            @if((float) $product->price > 0) — Ref: {{ $product->currency }} {{ number_format((float) $product->price, 2) }} @endif
                        </option>
                    @endforeach
                </select>
            </td>
            <td class="px-3 py-2.5">
                <input type="number" name="items[__INDEX__][quantity]" value="1"
                       min="0.01" step="0.01" required
                       class="w-full rounded-lg border border-slate-300 px-2.5 py-2 text-center font-bold">
            </td>
            <td class="px-3 py-2.5">
                <input type="text" name="items[__INDEX__][notes]" value=""
                       placeholder="Color, medida, acabado o comentario opcional..."
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </td>
            <td class="px-3 py-2.5 text-right">
                <button type="button" onclick="removeQuoteItemRow(this)"
                        class="px-2.5 py-1.5 rounded-lg border border-rose-200 text-rose-700 hover:bg-rose-50 font-medium cursor-pointer">
                    ✕
                </button>
            </td>
        </tr>
    </template>

    @push('scripts')
        <script>
            let quoteItemIndex = {{ count($oldItems) }};

            function fillCustomerFromSelector() {
                const select = document.getElementById('customer_selector');
                if (!select) return;
                const option = select.options[select.selectedIndex];
                if (!option || !option.value) return;

                document.getElementById('contact_name').value = option.getAttribute('data-name') || '';
                document.getElementById('contact_email').value = option.getAttribute('data-email') || '';
                document.getElementById('contact_phone').value = option.getAttribute('data-phone') || '';
                document.getElementById('company_name').value = option.getAttribute('data-company') || '';
                document.getElementById('ruc').value = option.getAttribute('data-ruc') || '';
            }

            function addQuoteItemRow() {
                const tbody = document.getElementById('quote-items-body');
                const tpl = document.getElementById('quote-item-row-template');
                if (!tbody || !tpl) return;

                const html = tpl.innerHTML.replace(/__INDEX__/g, quoteItemIndex++);
                tbody.insertAdjacentHTML('beforeend', html);
            }

            function removeQuoteItemRow(btn) {
                const tbody = document.getElementById('quote-items-body');
                const rows = tbody.querySelectorAll('.quote-item-row');
                if (rows.length <= 1) {
                    alert('La cotización debe incluir al menos un producto.');
                    return;
                }
                const row = btn.closest('.quote-item-row');
                if (row) row.remove();
            }
        </script>
    @endpush
@endsection
