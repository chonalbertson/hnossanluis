@extends('layouts.admin')

@section('title', 'Configuración Comercial')
@section('header', '11. Configuración Comercial y Datos Corporativos')
@section('subheader', 'Modo global de visualización de precios, moneda, vigencia de cotizaciones e identidad legal de Import Hns San Luis.')

@section('content')
    <form method="POST" action="{{ route('admin.settings.commercial.update') }}" class="grid grid-cols-1 lg:grid-cols-2 gap-6 text-xs">
        @csrf
        @method('PUT')

        <!-- Reglas Comerciales y Modo de Precios -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4">
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Política Global de Precios y Cotizaciones</h2>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Modo Global de Visualización de Precios en el Catálogo</label>
                <select name="commercial_price_display_mode" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white font-semibold">
                    <option value="mixed" @selected($settings['commercial_price_display_mode'] === 'mixed')>
                        Modo Mixto (Respeta visibilidad por producto; oculta precios vacíos/0)
                    </option>
                    <option value="show_all" @selected($settings['commercial_price_display_mode'] === 'show_all')>
                        Mostrar Precios (Para todos los productos con precio > 0 que no estén ocultos)
                    </option>
                    <option value="hide_all" @selected($settings['commercial_price_display_mode'] === 'hide_all')>
                        Ocultar Todos los Precios (100% catálogo bajo solicitud de cotización)
                    </option>
                </select>
                <p class="text-[11px] text-slate-500 mt-1">Nota: En ningún modo se mostrará "S/ 0.00" si un producto no tiene precio asignado.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Moneda por Defecto</label>
                    <select name="commercial_default_currency" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                        <option value="PEN" @selected($settings['commercial_default_currency'] === 'PEN')>PEN (S/)</option>
                        <option value="USD" @selected($settings['commercial_default_currency'] === 'USD')>USD ($)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Vigencia Cotización (Días)</label>
                    <input type="number" name="commercial_quote_validity_days" value="{{ old('commercial_quote_validity_days', $settings['commercial_quote_validity_days']) }}" min="1" max="365"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tasa IGV (%)</label>
                    <input type="number" name="commercial_tax_rate" value="{{ old('commercial_tax_rate', $settings['commercial_tax_rate']) }}" min="0" max="100"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Condiciones Comerciales Predeterminadas (para PDF)</label>
                <textarea name="commercial_default_quote_conditions" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('commercial_default_quote_conditions', $settings['commercial_default_quote_conditions']) }}</textarea>
            </div>

            <label class="flex items-center gap-2 pt-1 cursor-pointer">
                <input type="checkbox" name="commercial_show_availability_badge" value="1" @checked($settings['commercial_show_availability_badge']) class="rounded border-slate-300 text-amber-500">
                <span class="font-semibold text-slate-700">Mostrar etiquetas de estado de disponibilidad en tarjetas de producto</span>
            </label>
        </div>

        <!-- Identidad Corporativa y RUC -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4">
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Identidad Corporativa (Veracidad Comercial)</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nombre Comercial <span class="text-rose-600">*</span></label>
                    <input type="text" name="company_commercial_name" value="{{ old('company_commercial_name', $settings['company_commercial_name']) }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Razón Social / Denominación <span class="text-rose-600">*</span></label>
                    <input type="text" name="company_legal_name" value="{{ old('company_legal_name', $settings['company_legal_name']) }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">RUC Corporativo (11 dígitos, opcional)</label>
                    <input type="text" name="company_ruc" value="{{ old('company_ruc', $settings['company_ruc']) }}" maxlength="11" placeholder="Dejar vacío si aún no desea mostrarlo"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Teléfono Comercial Principal</label>
                    <input type="text" name="company_contact_phone" value="{{ old('company_contact_phone', $settings['company_contact_phone']) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Dirección / Cobertura Operativa</label>
                <input type="text" name="company_address" value="{{ old('company_address', $settings['company_address']) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Horario de Atención Comercial</label>
                <input type="text" name="company_business_hours" value="{{ old('company_business_hours', $settings['company_business_hours']) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>

            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#16A34A] text-white flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </span>
                    <span class="text-[11px] font-semibold text-emerald-950">¿Desea cambiar los nombres de las asesoras (Angie, Gracia, Sabrina) y números de WhatsApp?</span>
                </div>
                <a href="{{ route('admin.settings.communication') }}" class="px-3 py-1.5 rounded-lg bg-[#16A34A] hover:bg-[#15803D] text-white font-bold text-[11px] shrink-0">
                    Editar Asesoras WhatsApp &rarr;
                </a>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 shadow-xs cursor-pointer">
                    Guardar Configuración Comercial y Corporativa
                </button>
            </div>
        </div>
    </form>
@endsection
