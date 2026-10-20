<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Services\ActivityLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(
        protected ActivityLoggerService $activityLogger
    ) {}

    public function create(): View
    {
        return view('auth.register', [
            'seo' => [
                'title' => 'Crear Cuenta de Cliente | Import Hns San Luis',
                'description' => 'Regístrate en Import Hns San Luis para agilizar tus solicitudes de cotización B2B/B2C y consultar el historial de tus requerimientos.',
                'canonical' => route('register'),
                'robots' => 'noindex, nofollow',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('ruc')) {
            $request->merge([
                'ruc' => preg_replace('/\D/', '', (string) $request->input('ruc')),
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['required', 'string', 'min:6', 'max:40'],
            'company' => ['nullable', 'string', 'max:180'],
            'ruc' => ['nullable', 'regex:/^\d{11}$/'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'privacy_accepted' => ['accepted'],
        ], [
            'name.required' => 'Por favor ingresa tu nombre completo.',
            'email.required' => 'Ingresa tu correo electrónico.',
            'email.unique' => 'Este correo electrónico ya se encuentra registrado.',
            'phone.required' => 'Ingresa un teléfono o celular de contacto.',
            'ruc.regex' => 'El número de RUC debe contener exactamente 11 dígitos numéricos.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'privacy_accepted.accepted' => 'Debes aceptar la Política de Privacidad (Ley N° 29733).',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $email = strtolower(trim($validated['email']));

            $user = User::query()->create([
                'role_id' => null,
                'name' => trim($validated['name']),
                'email' => $email,
                'phone' => trim($validated['phone']),
                'company' => ! empty($validated['company']) ? trim($validated['company']) : null,
                'ruc' => $validated['ruc'] ?? null,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make($validated['password']),
                'last_login_at' => now(),
            ]);

            Customer::query()->updateOrCreate(
                ['email' => $email],
                [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'company' => $user->company,
                    'ruc' => $user->ruc,
                    'is_authorized_contact' => true,
                ]
            );

            $this->activityLogger->log(
                'auth.registered',
                'auth',
                sprintf('Nuevo cliente registrado en el portal: %s (%s).', $user->name, $user->email),
                $user,
                [
                    'has_company' => ! empty($user->company),
                    'has_ruc' => ! empty($user->ruc),
                ],
                $user->id
            );

            return $user;
        });

        Auth::login($user);

        return redirect()
            ->route('profile.edit')
            ->with('success', '¡Bienvenido a Import Hns San Luis! Tu cuenta ha sido creada exitosamente.');
    }
}
