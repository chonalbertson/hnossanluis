<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Services\ActivityLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function __construct(
        protected ActivityLoggerService $activityLogger
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('testimonials.manage'), 403);

        $testimonials = Testimonial::query()
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.testimonials.index', [
            'testimonials' => $testimonials,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('testimonials.manage'), 403);

        return view('admin.testimonials.create', [
            'testimonial' => new Testimonial([
                'rating' => 5,
                'is_verified' => false,
                'is_active' => false,
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('testimonials.manage'), 403);

        $validated = $this->validateTestimonial($request);

        $isVerified = $request->boolean('is_verified');
        $isActive = $request->boolean('is_active') && $isVerified;

        $testimonial = Testimonial::query()->create([
            'author_name' => trim($validated['author_name']),
            'author_role' => $validated['author_role'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'content' => trim($validated['content']),
            'rating' => (int) ($validated['rating'] ?? 5),
            'is_verified' => $isVerified,
            'authorization_reference' => $validated['authorization_reference'] ?? null,
            'is_active' => $isActive,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        $this->activityLogger->log(
            'testimonial.created',
            'cms',
            sprintf('Testimonio de "%s" (%s) registrado.', $testimonial->author_name, $testimonial->company_name ?: 'Cliente'),
            $testimonial,
            ['is_verified' => $isVerified, 'is_active' => $isActive]
        );

        $message = ($request->boolean('is_active') && ! $isVerified)
            ? 'Testimonio guardado como inactivo porque requiere marcar la autorización verificada para publicarse en la web.'
            : 'Testimonio registrado correctamente.';

        return redirect()
            ->route('admin.testimonials.index')
            ->with('success', $message);
    }

    public function edit(Request $request, Testimonial $testimonial): View
    {
        abort_unless($request->user()?->hasPermission('testimonials.manage'), 403);

        return view('admin.testimonials.edit', [
            'testimonial' => $testimonial,
        ]);
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('testimonials.manage'), 403);

        $validated = $this->validateTestimonial($request);

        $isVerified = $request->boolean('is_verified');
        $isActive = $request->boolean('is_active') && $isVerified;

        $testimonial->update([
            'author_name' => trim($validated['author_name']),
            'author_role' => $validated['author_role'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'content' => trim($validated['content']),
            'rating' => (int) ($validated['rating'] ?? 5),
            'is_verified' => $isVerified,
            'authorization_reference' => $validated['authorization_reference'] ?? null,
            'is_active' => $isActive,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        $this->activityLogger->log(
            'testimonial.updated',
            'cms',
            sprintf('Testimonio de "%s" actualizado.', $testimonial->author_name),
            $testimonial,
            ['is_verified' => $isVerified, 'is_active' => $isActive]
        );

        return redirect()
            ->route('admin.testimonials.index')
            ->with('success', 'Testimonio actualizado correctamente.');
    }

    public function toggle(Request $request, Testimonial $testimonial): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('testimonials.manage'), 403);

        if (! $testimonial->is_active && ! $testimonial->is_verified) {
            return back()->with('error', 'Por política de veracidad comercial, solo se pueden publicar testimonios que cuenten con autorización verificada.');
        }

        $testimonial->update(['is_active' => ! $testimonial->is_active]);

        $this->activityLogger->log(
            'testimonial.toggled',
            'cms',
            sprintf('Visibilidad pública de testimonio de "%s" cambiada a %s.', $testimonial->author_name, $testimonial->is_active ? 'Visible' : 'Oculto'),
            $testimonial
        );

        return back()->with('success', 'Visibilidad del testimonio actualizada.');
    }

    public function destroy(Request $request, Testimonial $testimonial): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('testimonials.manage'), 403);

        $author = $testimonial->author_name;
        $testimonial->delete();

        $this->activityLogger->log(
            'testimonial.deleted',
            'cms',
            sprintf('Testimonio de "%s" eliminado.', $author)
        );

        return redirect()
            ->route('admin.testimonials.index')
            ->with('success', 'Testimonio eliminado.');
    }

    protected function validateTestimonial(Request $request): array
    {
        return $request->validate([
            'author_name' => ['required', 'string', 'max:180'],
            'author_role' => ['nullable', 'string', 'max:150'],
            'company_name' => ['nullable', 'string', 'max:180'],
            'content' => ['required', 'string', 'max:1500'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'is_verified' => ['nullable', 'boolean'],
            'authorization_reference' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);
    }
}
