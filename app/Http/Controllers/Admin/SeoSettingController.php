<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\ActivityLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoSettingController extends Controller
{
    public function __construct(
        protected ActivityLoggerService $activityLogger
    ) {}

    public function edit(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('seo.manage'), 403);

        $settings = [
            'seo_default_title' => (string) SiteSetting::getValue('seo_default_title', 'Import Hns San Luis | Ferretería Industrial, Accesorios para Muebles y Construcción'),
            'seo_default_description' => (string) SiteSetting::getValue('seo_default_description', ''),
            'seo_allow_indexing' => (bool) SiteSetting::getValue('seo_allow_indexing', true),
            'seo_google_site_verification' => (string) SiteSetting::getValue('seo_google_site_verification', ''),
            'ga4_measurement_id' => (string) SiteSetting::getValue('ga4_measurement_id', ''),
            'ga4_enabled' => (bool) SiteSetting::getValue('ga4_enabled', false),
        ];

        return view('admin.settings.seo', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('seo.manage'), 403);

        $validated = $request->validate([
            'seo_default_title' => ['required', 'string', 'max:255'],
            'seo_default_description' => ['required', 'string', 'max:500'],
            'seo_allow_indexing' => ['nullable', 'boolean'],
            'seo_google_site_verification' => ['nullable', 'string', 'max:255'],
            'ga4_measurement_id' => ['nullable', 'string', 'max:40', 'regex:/^(G-[A-Z0-9]+)?$/i'],
            'ga4_enabled' => ['nullable', 'boolean'],
        ], [
            'ga4_measurement_id.regex' => 'El ID de medición de Google Analytics 4 debe tener el formato G-XXXXXXXXXX.',
        ]);

        SiteSetting::setValue('seo_default_title', trim($validated['seo_default_title']), 'seo', 'string', 'Título SEO predeterminado');
        SiteSetting::setValue('seo_default_description', trim($validated['seo_default_description']), 'seo', 'string', 'Meta descripción SEO predeterminada');
        SiteSetting::setValue('seo_allow_indexing', $request->boolean('seo_allow_indexing'), 'seo', 'boolean', 'Permitir indexación en motores de búsqueda');
        SiteSetting::setValue('seo_google_site_verification', trim((string) ($validated['seo_google_site_verification'] ?? '')), 'seo', 'string', 'Código de verificación de Google Search Console');
        SiteSetting::setValue('ga4_measurement_id', strtoupper(trim((string) ($validated['ga4_measurement_id'] ?? ''))), 'seo', 'string', 'ID de Medición Google Analytics 4');
        SiteSetting::setValue('ga4_enabled', $request->boolean('ga4_enabled'), 'seo', 'boolean', 'Activar Google Analytics 4');

        $this->activityLogger->log(
            'settings.seo_updated',
            'seo',
            'Configuración global de SEO, Search Console y Google Analytics 4 actualizada.',
            null,
            [
                'seo_allow_indexing' => $request->boolean('seo_allow_indexing'),
                'ga4_enabled' => $request->boolean('ga4_enabled'),
                'ga4_measurement_id' => $validated['ga4_measurement_id'] ?? null,
            ]
        );

        return back()->with('success', 'Configuración de SEO y Analítica Web actualizada correctamente.');
    }
}
