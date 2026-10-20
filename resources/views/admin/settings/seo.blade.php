@extends('layouts.admin')

@section('title', 'SEO y Analítica Web')
@section('header', '13. Configuración de SEO Técnico y Google Analytics 4')
@section('subheader', 'Metadatos globales por defecto, control de indexación, Google Search Console y medición GA4.')

@section('content')
    <div class="max-w-3xl bg-white rounded-xl border border-slate-200 p-6 shadow-2xs">
        <form method="POST" action="{{ route('admin.settings.seo.update') }}" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Título SEO Global por Defecto <span class="text-rose-600">*</span></label>
                <input type="text" name="seo_default_title" value="{{ old('seo_default_title', $settings['seo_default_title']) }}" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Meta Descripción Global por Defecto <span class="text-rose-600">*</span></label>
                <textarea name="seo_default_description" rows="3" required class="w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('seo_default_description', $settings['seo_default_description']) }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">ID de Medición Google Analytics 4</label>
                    <input type="text" name="ga4_measurement_id" value="{{ old('ga4_measurement_id', $settings['ga4_measurement_id']) }}" placeholder="G-XXXXXXXXXX"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono uppercase">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Verificación Google Search Console (meta content)</label>
                    <input type="text" name="seo_google_site_verification" value="{{ old('seo_google_site_verification', $settings['seo_google_site_verification']) }}" placeholder="Código de verificación..."
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono">
                </div>
            </div>

            <div class="space-y-2 pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="seo_allow_indexing" value="1" @checked($settings['seo_allow_indexing']) class="rounded border-slate-300 text-amber-500">
                    <span class="font-semibold text-slate-700">Permitir indexación del sitio público en buscadores (robots: index, follow)</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="ga4_enabled" value="1" @checked($settings['ga4_enabled']) class="rounded border-slate-300 text-amber-500">
                    <span class="font-semibold text-slate-700">Activar script de medición Google Analytics 4 en páginas públicas</span>
                </label>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-slate-500">Sitemap XML disponible en: <a href="{{ url('/sitemap.xml') }}" target="_blank" class="text-blue-600 underline">/sitemap.xml</a></span>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 cursor-pointer">
                    Guardar Configuración SEO
                </button>
            </div>
        </form>
    </div>
@endsection
