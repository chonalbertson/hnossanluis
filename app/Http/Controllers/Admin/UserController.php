<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        protected ActivityLoggerService $activityLogger
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('users.manage'), 403);

        $query = User::query()->with('role');

        if ($request->filled('role_id')) {
            if ($request->string('role_id')->toString() === 'client') {
                $query->whereNull('role_id');
            } else {
                $query->where('role_id', (int) $request->input('role_id'));
            }
        }

        if ($request->filled('q')) {
            $term = '%'.trim($request->string('q')->toString()).'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('company', 'like', $term);
            });
        }

        $users = $query->orderBy('role_id')->orderBy('name')->paginate(15)->withQueryString();
        $roles = Role::query()->with('permissions')->orderBy('id')->get();
        $permissionsByModule = Permission::query()->orderBy('module')->orderBy('slug')->get()->groupBy('module');

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $roles,
            'permissionsByModule' => $permissionsByModule,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('users.manage'), 403);

        $roles = Role::query()->with('permissions')->orderBy('id')->get();

        return view('admin.users.create', [
            'user' => new User(['is_active' => true]),
            'roles' => $roles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('users.manage'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'email' => ['required', 'email', 'max:180', 'unique:users,email'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'company' => ['nullable', 'string', 'max:180'],
            'ruc' => ['nullable', 'digits:11'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user = User::query()->create([
            'role_id' => $validated['role_id'] ?? null,
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'phone' => $validated['phone'] ?? null,
            'company' => $validated['company'] ?? null,
            'ruc' => $validated['ruc'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => now(),
            'password' => Hash::make($validated['password']),
        ]);

        $this->activityLogger->log(
            'user.created',
            'users',
            sprintf('Usuario "%s" (%s) creado con rol "%s".', $user->name, $user->email, $user->role?->name ?? 'Cliente'),
            $user,
            ['role' => $user->role?->slug ?? 'client', 'is_active' => $user->is_active]
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', sprintf('Usuario "%s" creado correctamente.', $user->name));
    }

    public function edit(Request $request, User $user): View
    {
        abort_unless($request->user()?->hasPermission('users.manage'), 403);

        $roles = Role::query()->with('permissions')->orderBy('id')->get();

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('users.manage'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')->ignore($user->id)],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'company' => ['nullable', 'string', 'max:180'],
            'ruc' => ['nullable', 'digits:11'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $isActive = $request->boolean('is_active');
        if ($user->id === $request->user()->id && ! $isActive) {
            return back()->with('error', 'Por seguridad no puede desactivar su propia cuenta de administrador.');
        }

        $attributes = [
            'role_id' => $validated['role_id'] ?? null,
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'phone' => $validated['phone'] ?? null,
            'company' => $validated['company'] ?? null,
            'ruc' => $validated['ruc'] ?? null,
            'is_active' => $isActive,
        ];

        if (! empty($validated['password'])) {
            $attributes['password'] = Hash::make($validated['password']);
        }

        $user->update($attributes);

        $this->activityLogger->log(
            'user.updated',
            'users',
            sprintf('Usuario "%s" (%s) actualizado. Rol: %s.', $user->name, $user->email, $user->role?->name ?? 'Cliente'),
            $user,
            ['role' => $user->role?->slug ?? 'client', 'is_active' => $user->is_active]
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', sprintf('Usuario "%s" actualizado correctamente.', $user->name));
    }

    public function toggle(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('users.manage'), 403);

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'No puede desactivar su propia cuenta en sesión.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        $this->activityLogger->log(
            'user.toggled',
            'users',
            sprintf('Cuenta de "%s" cambiada a estado %s.', $user->email, $user->is_active ? 'Activa' : 'Inactiva'),
            $user
        );

        return back()->with('success', sprintf('Estado del usuario "%s" actualizado.', $user->name));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('users.manage'), 403);

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'No puede eliminar su propia cuenta en sesión.');
        }

        $email = $user->email;
        $user->update(['is_active' => false]);
        $user->delete();

        $this->activityLogger->log(
            'user.deleted',
            'users',
            sprintf('Usuario "%s" desactivado y enviado a papelera.', $email)
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', sprintf('Usuario "%s" eliminado correctamente.', $email));
    }
}
