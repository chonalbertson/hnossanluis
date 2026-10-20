<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ActivityLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        protected ActivityLoggerService $activityLogger
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('categories.manage'), 403);

        $parentCategories = Category::query()
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->withCount(['products', 'subProducts'])->orderBy('sort_order')])
            ->withCount(['products', 'subProducts'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', [
            'parentCategories' => $parentCategories,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('categories.manage'), 403);

        $parents = Category::query()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.create', [
            'category' => new Category(['is_active' => true, 'sort_order' => 0]),
            'parents' => $parents,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('categories.manage'), 403);

        $validated = $this->validateCategory($request);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : $this->generateUniqueSlug($validated['name']);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        $attributeSchema = $this->parseAttributeSchema($request->input('attribute_schema_raw'));

        $category = Category::query()->create([
            'parent_id' => $validated['parent_id'] ?? null,
            'name' => trim($validated['name']),
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'attribute_schema' => $attributeSchema,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
        ]);

        $this->activityLogger->log(
            'category.created',
            'catalog',
            sprintf('Categoría "%s" (%s) creada.', $category->name, $category->slug),
            $category
        );

        return redirect()
            ->route('admin.categories.index')
            ->with('success', sprintf('Categoría "%s" creada correctamente.', $category->name));
    }

    public function edit(Request $request, Category $category): View
    {
        abort_unless($request->user()?->hasPermission('categories.manage'), 403);

        $parents = Category::query()
            ->whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.edit', [
            'category' => $category,
            'parents' => $parents,
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('categories.manage'), 403);

        $validated = $this->validateCategory($request, $category);

        if (! empty($validated['parent_id']) && (int) $validated['parent_id'] === $category->id) {
            return back()->withErrors(['parent_id' => 'Una categoría no puede ser subcategoría de sí misma.']);
        }

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : $this->generateUniqueSlug($validated['name'], $category->id);

        $imagePath = $category->image_path;
        if ($request->hasFile('image')) {
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        $attributeSchema = $this->parseAttributeSchema($request->input('attribute_schema_raw'));

        $category->update([
            'parent_id' => $validated['parent_id'] ?? null,
            'name' => trim($validated['name']),
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'attribute_schema' => $attributeSchema,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
        ]);

        $this->activityLogger->log(
            'category.updated',
            'catalog',
            sprintf('Categoría "%s" (%s) actualizada.', $category->name, $category->slug),
            $category
        );

        return redirect()
            ->route('admin.categories.index')
            ->with('success', sprintf('Categoría "%s" actualizada correctamente.', $category->name));
    }

    public function toggle(Request $request, Category $category): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('categories.manage'), 403);

        $category->update(['is_active' => ! $category->is_active]);

        $this->activityLogger->log(
            'category.toggled',
            'catalog',
            sprintf('Categoría "%s" cambiada a estado %s.', $category->name, $category->is_active ? 'Activa' : 'Inactiva'),
            $category
        );

        return back()->with('success', sprintf('Estado de la categoría "%s" actualizado.', $category->name));
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('categories.manage'), 403);

        if ($category->children()->exists()) {
            return back()->with('error', 'No se puede eliminar una categoría principal que tiene subcategorías asociadas.');
        }

        if ($category->products()->withTrashed()->exists() || $category->subProducts()->withTrashed()->exists()) {
            return back()->with('error', 'No se puede eliminar una categoría que tiene productos asociados. Desactívela o reasigne los productos primero.');
        }

        $name = $category->name;
        $category->delete();

        $this->activityLogger->log(
            'category.deleted',
            'catalog',
            sprintf('Categoría "%s" eliminada.', $name)
        );

        return redirect()
            ->route('admin.categories.index')
            ->with('success', sprintf('Categoría "%s" eliminada.', $name));
    }

    protected function validateCategory(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:180'],
            'slug' => [
                'nullable',
                'string',
                'max:200',
                Rule::unique('categories', 'slug')->ignore($category?->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);
    }

    protected function parseAttributeSchema(?string $raw): ?array
    {
        if (! $raw || trim($raw) === '') {
            return null;
        }

        $items = array_values(array_filter(array_map('trim', explode(',', $raw)), fn ($v) => $v !== ''));

        return empty($items) ? null : $items;
    }

    protected function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'categoria';
        $slug = $base;
        $counter = 1;

        while (
            Category::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
