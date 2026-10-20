<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Services\ActivityLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function __construct(
        protected ActivityLoggerService $activityLogger
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('brands.manage'), 403);

        $brands = Brand::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.brands.index', [
            'brands' => $brands,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('brands.manage'), 403);

        return view('admin.brands.create', [
            'brand' => new Brand([
                'is_active' => true,
                'is_verified' => true,
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('brands.manage'), 403);

        $validated = $this->validateBrand($request);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : $this->generateUniqueSlug($validated['name']);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('brands', 'public');
        }

        $brand = Brand::query()->create([
            'name' => trim($validated['name']),
            'slug' => $slug,
            'logo_path' => $logoPath,
            'website' => $validated['website'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_verified' => $request->boolean('is_verified'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        $this->activityLogger->log(
            'brand.created',
            'catalog',
            sprintf('Marca "%s" (%s) registrada.', $brand->name, $brand->slug),
            $brand
        );

        return redirect()
            ->route('admin.brands.index')
            ->with('success', sprintf('Marca "%s" creada correctamente.', $brand->name));
    }

    public function edit(Request $request, Brand $brand): View
    {
        abort_unless($request->user()?->hasPermission('brands.manage'), 403);

        return view('admin.brands.edit', [
            'brand' => $brand,
        ]);
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('brands.manage'), 403);

        $validated = $this->validateBrand($request, $brand);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : $this->generateUniqueSlug($validated['name'], $brand->id);

        $logoPath = $brand->logo_path;
        if ($request->hasFile('logo')) {
            if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }
            $logoPath = $request->file('logo')->store('brands', 'public');
        }

        $brand->update([
            'name' => trim($validated['name']),
            'slug' => $slug,
            'logo_path' => $logoPath,
            'website' => $validated['website'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_verified' => $request->boolean('is_verified'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        $this->activityLogger->log(
            'brand.updated',
            'catalog',
            sprintf('Marca "%s" (%s) actualizada.', $brand->name, $brand->slug),
            $brand
        );

        return redirect()
            ->route('admin.brands.index')
            ->with('success', sprintf('Marca "%s" actualizada correctamente.', $brand->name));
    }

    public function toggle(Request $request, Brand $brand): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('brands.manage'), 403);

        $brand->update(['is_active' => ! $brand->is_active]);

        $this->activityLogger->log(
            'brand.toggled',
            'catalog',
            sprintf('Marca "%s" cambiada a estado %s.', $brand->name, $brand->is_active ? 'Activa' : 'Inactiva'),
            $brand
        );

        return back()->with('success', sprintf('Estado de la marca "%s" actualizado.', $brand->name));
    }

    public function destroy(Request $request, Brand $brand): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('brands.manage'), 403);

        if ($brand->products()->withTrashed()->exists()) {
            return back()->with('error', 'No se puede eliminar una marca que tiene productos asociados. Desactívela en su lugar.');
        }

        $name = $brand->name;
        $brand->delete();

        $this->activityLogger->log(
            'brand.deleted',
            'catalog',
            sprintf('Marca "%s" eliminada.', $name)
        );

        return redirect()
            ->route('admin.brands.index')
            ->with('success', sprintf('Marca "%s" eliminada.', $name));
    }

    protected function validateBrand(Request $request, ?Brand $brand = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('brands', 'name')->ignore($brand?->id),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:180',
                Rule::unique('brands', 'slug')->ignore($brand?->id),
            ],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_verified' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);
    }

    protected function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'marca';
        $slug = $base;
        $counter = 1;

        while (
            Brand::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
