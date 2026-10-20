<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroBanner;
use App\Services\ActivityLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HeroBannerController extends Controller
{
    public function __construct(
        protected ActivityLoggerService $activityLogger
    ) {}

    public function index(Request $request): View
    {
        abort_unless($this->canManageBanners($request), 403);

        if (Schema::hasTable('hero_banners') && HeroBanner::query()->count() === 0) {
            $this->seedDefaultBannersIfEmpty();
        }

        $banners = HeroBanner::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(15);

        return view('admin.hero-banners.index', [
            'banners' => $banners,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($this->canManageBanners($request), 403);

        $nextOrder = ((int) HeroBanner::query()->max('sort_order')) + 1;

        return view('admin.hero-banners.create', [
            'banner' => new HeroBanner([
                'accent' => 'red',
                'product_source' => 'destacados',
                'show_products_showcase' => true,
                'overlay_opacity' => 75,
                'primary_cta_label' => 'Explorar Catálogo',
                'primary_cta_url' => '/catalogo',
                'secondary_cta_label' => 'Cotizar Lista',
                'secondary_cta_url' => '/cotizacion',
                'sort_order' => $nextOrder,
                'is_active' => true,
            ]),
            'accentOptions' => $this->accentOptions(),
            'productSourceOptions' => $this->productSourceOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->canManageBanners($request), 403);

        $validated = $this->validateBanner($request);

        $backgroundImagePath = null;
        if ($request->hasFile('background_image')) {
            $backgroundImagePath = $request->file('background_image')->store('banners', 'public');
        }

        $mobileBackgroundImagePath = null;
        if ($request->hasFile('mobile_background_image')) {
            $mobileBackgroundImagePath = $request->file('mobile_background_image')->store('banners', 'public');
        }

        $banner = HeroBanner::query()->create([
            'label' => trim($validated['label']),
            'eyebrow' => isset($validated['eyebrow']) ? trim((string) $validated['eyebrow']) : null,
            'badge_metric' => isset($validated['badge_metric']) ? trim((string) $validated['badge_metric']) : null,
            'title' => trim($validated['title']),
            'highlight' => isset($validated['highlight']) ? trim((string) $validated['highlight']) : null,
            'suffix' => isset($validated['suffix']) ? trim((string) $validated['suffix']) : null,
            'description' => isset($validated['description']) ? trim((string) $validated['description']) : null,
            'background_image_path' => $backgroundImagePath,
            'mobile_background_image_path' => $mobileBackgroundImagePath,
            'overlay_opacity' => (int) ($validated['overlay_opacity'] ?? 75),
            'primary_cta_label' => isset($validated['primary_cta_label']) ? trim((string) $validated['primary_cta_label']) : null,
            'primary_cta_url' => isset($validated['primary_cta_url']) ? trim((string) $validated['primary_cta_url']) : null,
            'secondary_cta_label' => isset($validated['secondary_cta_label']) ? trim((string) $validated['secondary_cta_label']) : null,
            'secondary_cta_url' => isset($validated['secondary_cta_url']) ? trim((string) $validated['secondary_cta_url']) : null,
            'accent' => $validated['accent'] ?? 'red',
            'product_source' => $validated['product_source'] ?? 'destacados',
            'show_products_showcase' => $request->boolean('show_products_showcase', true),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->activityLogger->log(
            'hero_banner.created',
            'cms',
            sprintf('Banner de slider "%s" creado.', $banner->label),
            $banner,
            [
                'has_background_image' => $backgroundImagePath !== null,
                'has_mobile_image' => $mobileBackgroundImagePath !== null,
                'is_active' => $banner->is_active,
            ]
        );

        return redirect()
            ->route('admin.hero-banners.index')
            ->with('success', sprintf('Banner "%s" creado correctamente.', $banner->label));
    }

    public function edit(Request $request, HeroBanner $heroBanner): View
    {
        abort_unless($this->canManageBanners($request), 403);

        return view('admin.hero-banners.edit', [
            'banner' => $heroBanner,
            'accentOptions' => $this->accentOptions(),
            'productSourceOptions' => $this->productSourceOptions(),
        ]);
    }

    public function update(Request $request, HeroBanner $heroBanner): RedirectResponse
    {
        abort_unless($this->canManageBanners($request), 403);

        $validated = $this->validateBanner($request);

        $backgroundImagePath = $heroBanner->background_image_path;
        if ($request->boolean('remove_background_image') && $backgroundImagePath) {
            if (Storage::disk('public')->exists($backgroundImagePath)) {
                Storage::disk('public')->delete($backgroundImagePath);
            }
            $backgroundImagePath = null;
        }

        if ($request->hasFile('background_image')) {
            if ($backgroundImagePath && Storage::disk('public')->exists($backgroundImagePath)) {
                Storage::disk('public')->delete($backgroundImagePath);
            }
            $backgroundImagePath = $request->file('background_image')->store('banners', 'public');
        }

        $mobileBackgroundImagePath = $heroBanner->mobile_background_image_path;
        if ($request->boolean('remove_mobile_background_image') && $mobileBackgroundImagePath) {
            if (Storage::disk('public')->exists($mobileBackgroundImagePath)) {
                Storage::disk('public')->delete($mobileBackgroundImagePath);
            }
            $mobileBackgroundImagePath = null;
        }

        if ($request->hasFile('mobile_background_image')) {
            if ($mobileBackgroundImagePath && Storage::disk('public')->exists($mobileBackgroundImagePath)) {
                Storage::disk('public')->delete($mobileBackgroundImagePath);
            }
            $mobileBackgroundImagePath = $request->file('mobile_background_image')->store('banners', 'public');
        }

        $heroBanner->update([
            'label' => trim($validated['label']),
            'eyebrow' => isset($validated['eyebrow']) ? trim((string) $validated['eyebrow']) : null,
            'badge_metric' => isset($validated['badge_metric']) ? trim((string) $validated['badge_metric']) : null,
            'title' => trim($validated['title']),
            'highlight' => isset($validated['highlight']) ? trim((string) $validated['highlight']) : null,
            'suffix' => isset($validated['suffix']) ? trim((string) $validated['suffix']) : null,
            'description' => isset($validated['description']) ? trim((string) $validated['description']) : null,
            'background_image_path' => $backgroundImagePath,
            'mobile_background_image_path' => $mobileBackgroundImagePath,
            'overlay_opacity' => (int) ($validated['overlay_opacity'] ?? 75),
            'primary_cta_label' => isset($validated['primary_cta_label']) ? trim((string) $validated['primary_cta_label']) : null,
            'primary_cta_url' => isset($validated['primary_cta_url']) ? trim((string) $validated['primary_cta_url']) : null,
            'secondary_cta_label' => isset($validated['secondary_cta_label']) ? trim((string) $validated['secondary_cta_label']) : null,
            'secondary_cta_url' => isset($validated['secondary_cta_url']) ? trim((string) $validated['secondary_cta_url']) : null,
            'accent' => $validated['accent'] ?? 'red',
            'product_source' => $validated['product_source'] ?? 'destacados',
            'show_products_showcase' => $request->boolean('show_products_showcase'),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->activityLogger->log(
            'hero_banner.updated',
            'cms',
            sprintf('Banner de slider "%s" actualizado.', $heroBanner->label),
            $heroBanner,
            [
                'has_background_image' => $backgroundImagePath !== null,
                'has_mobile_image' => $mobileBackgroundImagePath !== null,
                'is_active' => $heroBanner->is_active,
            ]
        );

        return redirect()
            ->route('admin.hero-banners.index')
            ->with('success', sprintf('Banner "%s" actualizado correctamente.', $heroBanner->label));
    }

    public function toggle(Request $request, HeroBanner $heroBanner): RedirectResponse
    {
        abort_unless($this->canManageBanners($request), 403);

        $heroBanner->update(['is_active' => ! $heroBanner->is_active]);

        $this->activityLogger->log(
            'hero_banner.toggled',
            'cms',
            sprintf(
                'Visibilidad del banner "%s" cambiada a %s.',
                $heroBanner->label,
                $heroBanner->is_active ? 'Activo' : 'Inactivo'
            ),
            $heroBanner
        );

        return back()->with('success', sprintf('Estado del banner "%s" actualizado.', $heroBanner->label));
    }

    public function destroy(Request $request, HeroBanner $heroBanner): RedirectResponse
    {
        abort_unless($this->canManageBanners($request), 403);

        if ($heroBanner->background_image_path && Storage::disk('public')->exists($heroBanner->background_image_path)) {
            Storage::disk('public')->delete($heroBanner->background_image_path);
        }

        if ($heroBanner->mobile_background_image_path && Storage::disk('public')->exists($heroBanner->mobile_background_image_path)) {
            Storage::disk('public')->delete($heroBanner->mobile_background_image_path);
        }

        $label = $heroBanner->label;
        $heroBanner->delete();

        $this->activityLogger->log(
            'hero_banner.deleted',
            'cms',
            sprintf('Banner de slider "%s" eliminado.', $label)
        );

        return redirect()
            ->route('admin.hero-banners.index')
            ->with('success', sprintf('Banner "%s" eliminado.', $label));
    }

    protected function canManageBanners(Request $request): bool
    {
        $user = $request->user();
        if (! $user) {
            return false;
        }

        return $user->hasPermission('pages.manage') || $user->hasPermission('media.manage');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateBanner(Request $request): array
    {
        return $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'eyebrow' => ['nullable', 'string', 'max:180'],
            'badge_metric' => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:180'],
            'highlight' => ['nullable', 'string', 'max:180'],
            'suffix' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'background_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'mobile_background_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_background_image' => ['nullable', 'boolean'],
            'remove_mobile_background_image' => ['nullable', 'boolean'],
            'overlay_opacity' => ['nullable', 'integer', 'min:0', 'max:95'],
            'primary_cta_label' => ['nullable', 'string', 'max:100'],
            'primary_cta_url' => ['nullable', 'string', 'max:255'],
            'secondary_cta_label' => ['nullable', 'string', 'max:100'],
            'secondary_cta_url' => ['nullable', 'string', 'max:255'],
            'accent' => ['required', 'string', Rule::in(array_keys($this->accentOptions()))],
            'product_source' => ['required', 'string', Rule::in(array_keys($this->productSourceOptions()))],
            'show_products_showcase' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function accentOptions(): array
    {
        return [
            'red' => 'Rojo Corporativo San Luis (#DC2626)',
            'blue' => 'Azul Industrial (#1E3A8A)',
            'amber' => 'Ámbar / Dorado Técnico (#F59E0B)',
            'emerald' => 'Verde Esmeralda (#10B981)',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function productSourceOptions(): array
    {
        return [
            'destacados' => 'Productos Destacados (General)',
            'herrajes' => 'Herrajes, Bisagras y Sistemas Corredizos',
            'tiradores' => 'Tiradores, Perfiles Gola y Organización Cocina/Clóset',
            'cerraduras' => 'Cerraduras, Fijaciones y Químicos',
            'herramientas' => 'Herramientas Manuales e Iluminación LED',
            'none' => 'No mostrar vitrina flotante (Solo imagen de fondo + texto)',
        ];
    }

    protected function seedDefaultBannersIfEmpty(): void
    {
        foreach (HeroBanner::defaultSlidesDefinition() as $slide) {
            HeroBanner::query()->create($slide);
        }
    }
}
