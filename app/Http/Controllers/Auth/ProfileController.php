<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Quote;
use App\Services\ActivityLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected ActivityLoggerService $activityLogger
    ) {}

    /**
     * Show user profile and quote history (/mi-cuenta).
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $quotes = Quote::query()
            ->with('items')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('contact_email', $user->email);
            })
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('auth.profile', [
            'user' => $user,
            'quotes' => $quotes,
            'seo' => [
                'title' => 'Mi Cuenta e Historial de Cotizaciones | Import Hns San Luis',
                'description' => 'Gestiona tus datos comerciales y consulta el estado de tus solicitudes de cotización en Import Hns San Luis.',
                'canonical' => route('profile.edit'),
                'robots' => 'noindex, nofollow',
            ],
        ]);
    }

    /**
     * Update profile details.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($request->filled('ruc')) {
            $request->merge([
                'ruc' => preg_replace('/\D/', '', (string) $request->input('ruc')),
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:180'],
            'ruc' => ['nullable', 'regex:/^\d{11}$/'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo electrónico ya está siendo utilizado por otra cuenta.',
            'ruc.regex' => 'El número de RUC debe contener exactamente 11 dígitos numéricos.',
        ]);

        $user->update([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'phone' => ! empty($validated['phone']) ? trim($validated['phone']) : null,
            'company' => ! empty($validated['company']) ? trim($validated['company']) : null,
            'ruc' => $validated['ruc'] ?? null,
        ]);

        Customer::query()->updateOrCreate(
            ['email' => $user->email],
            [
                'user_id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'company' => $user->company,
                'ruc' => $user->ruc,
            ]
        );

        $this->activityLogger->log(
            'profile.updated',
            'auth',
            sprintf('El usuario %s actualizó sus datos de perfil comercial.', $user->email),
            $user,
            [],
            $user->id
        );

        return redirect()->route('profile.edit')->with('success', 'Tus datos de perfil comercial han sido actualizados.');
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'La contraseña actual ingresada no es correcta.',
            'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->activityLogger->log(
            'profile.password_changed',
            'auth',
            sprintf('El usuario %s actualizó su contraseña.', $user->email),
            $user,
            [],
            $user->id
        );

        return redirect()->route('profile.edit')->with('success', 'Tu contraseña ha sido actualizada correctamente.');
    }
}
