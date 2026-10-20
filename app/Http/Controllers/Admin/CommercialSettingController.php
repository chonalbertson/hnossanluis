<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\ActivityLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommercialSettingController extends Controller
{
    public function __construct(
        protected ActivityLoggerService $activityLogger
    ) {}

    public function edit(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('settings.manage'), 403);

        $settings = [
            'commercial_price_display_mode' => (string) SiteSetting::getValue('commercial_price_display_mode', 'mixed'),
            'commercial_default_currency' => (string) SiteSetting::getValue('commercial_default_currency', 'PEN'),
            'commercial_quote_validity_days' => (int) SiteSetting::getValue('commercial_quote_validity_days', 15),
            'commercial_tax_rate' => (int) SiteSetting::getValue('commercial_tax_rate', 18),
            'commercial_show_availability_badge' => (bool) SiteSetting::getValue('commercial_show_availability_badge', true),
            'commercial_default_quote_conditions' => (string) SiteSetting::getValue('commercial_default_quote_conditions', ''),
            'company_commercial_name' => (string) SiteSetting::getValue('company_commercial_name', 'Import Hnos San Luis'),
            'company_legal_name' => (string) SiteSetting::getValue('company_legal_name', 'IMPORT HNOS SAN LUIS S.A.C.'),
            'company_ruc' => (string) SiteSetting::getValue('company_ruc', '20608628143'),
            'company_address' => (string) SiteSetting::getValue('company_address', 'Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú'),
            'company_contact_phone' => (string) SiteSetting::getValue('company_contact_phone', '977 208 514'),
            'company_business_hours' => (string) SiteSetting::getValue('company_business_hours', ''),
        ];

        return view('admin.settings.commercial', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('settings.manage'), 403);

        $validated = $request->validate([
            'commercial_price_display_mode' => ['required', Rule::in(['show_all', 'hide_all', 'mixed'])],
            'commercial_default_currency' => ['required', Rule::in(['PEN', 'USD'])],
            'commercial_quote_validity_days' => ['required', 'integer', 'min:1', 'max:365'],
            'commercial_tax_rate' => ['required', 'integer', 'min:0', 'max:100'],
            'commercial_show_availability_badge' => ['nullable', 'boolean'],
            'commercial_default_quote_conditions' => ['nullable', 'string', 'max:2000'],
            'company_commercial_name' => ['required', 'string', 'max:180'],
            'company_legal_name' => ['required', 'string', 'max:200'],
            'company_ruc' => ['nullable', 'digits:11'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'company_contact_phone' => ['nullable', 'string', 'max:60'],
            'company_business_hours' => ['nullable', 'string', 'max:255'],
        ]);

        SiteSetting::setValue('commercial_price_display_mode', $validated['commercial_price_display_mode'], 'commercial', 'string', 'Modo de visualización de precios');
        SiteSetting::setValue('commercial_default_currency', $validated['commercial_default_currency'], 'commercial', 'string', 'Moneda predeterminada');
        SiteSetting::setValue('commercial_quote_validity_days', (int) $validated['commercial_quote_validity_days'], 'commercial', 'integer', 'Días de vigencia de cotizaciones');
        SiteSetting::setValue('commercial_tax_rate', (int) $validated['commercial_tax_rate'], 'commercial', 'integer', 'Porcentaje de IGV (%)');
        SiteSetting::setValue('commercial_show_availability_badge', $request->boolean('commercial_show_availability_badge'), 'commercial', 'boolean', 'Mostrar estado de disponibilidad');
        SiteSetting::setValue('commercial_default_quote_conditions', $validated['commercial_default_quote_conditions'] ?? '', 'commercial', 'string', 'Condiciones comerciales por defecto');

        SiteSetting::setValue('company_commercial_name', trim($validated['company_commercial_name']), 'contact', 'string', 'Nombre comercial');
        SiteSetting::setValue('company_legal_name', trim($validated['company_legal_name']), 'contact', 'string', 'Razón social');
        SiteSetting::setValue('company_ruc', $validated['company_ruc'] ?? '', 'contact', 'string', 'RUC corporativo');
        SiteSetting::setValue('company_address', $validated['company_address'] ?? '', 'contact', 'string', 'Dirección operativa');
        SiteSetting::setValue('company_contact_phone', $validated['company_contact_phone'] ?? '', 'contact', 'string', 'Teléfono comercial');
        SiteSetting::setValue('company_business_hours', $validated['company_business_hours'] ?? '', 'contact', 'string', 'Horario de atención');

        $this->activityLogger->log(
            'settings.commercial_updated',
            'settings',
            sprintf(
                'Configuración comercial actualizada (Modo precios: %s, Moneda: %s, Vigencia: %d días).',
                $validated['commercial_price_display_mode'],
                $validated['commercial_default_currency'],
                $validated['commercial_quote_validity_days']
            ),
            null,
            [
                'price_display_mode' => $validated['commercial_price_display_mode'],
                'default_currency' => $validated['commercial_default_currency'],
                'validity_days' => $validated['commercial_quote_validity_days'],
                'company_ruc' => $validated['company_ruc'] ?? null,
            ]
        );

        return back()->with('success', 'Configuración comercial y datos corporativos actualizados correctamente.');
    }
}
