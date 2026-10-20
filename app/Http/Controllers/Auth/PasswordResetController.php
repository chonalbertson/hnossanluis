<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\User;
use App\Services\ActivityLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function __construct(
        protected ActivityLoggerService $activityLogger
    ) {}

    public function create(): View
    {
        return view('auth.forgot-password', [
            'seo' => [
                'title' => 'Recuperar Contraseña | Import Hns San Luis',
                'description' => 'Solicita el restablecimiento de tu contraseña en el portal de Import Hns San Luis.',
                'canonical' => route('password.request'),
                'robots' => 'noindex, nofollow',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Por favor ingresa tu correo electrónico registrado.',
            'email.email' => 'Ingresa un correo electrónico válido.',
        ]);

        $throttleKey = 'password-reset:'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => sprintf('Por favor espera %d segundos antes de volver a solicitar el enlace.', $seconds),
            ]);
        }

        RateLimiter::hit($throttleKey, 60);

        $email = strtolower(trim((string) $request->input('email')));
        $user = User::query()->where('email', $email)->first();

        if ($user) {
            $plainToken = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'token' => Hash::make($plainToken),
                    'created_at' => now(),
                ]
            );

            $resetUrl = route('password.reset', [
                'token' => $plainToken,
                'email' => $email,
            ]);

            try {
                Mail::to($user->email)->send(new PasswordResetMail($user, $resetUrl, 60));
            } catch (\Throwable $e) {
                Log::warning('PasswordResetMail failed: '.$e->getMessage());
            }

            $this->activityLogger->log(
                'auth.password_reset_requested',
                'auth',
                sprintf('Solicitud de recuperación de contraseña para %s.', $email),
                $user,
                [],
                $user->id
            );
        }

        return back()->with(
            'status',
            'Si el correo electrónico se encuentra registrado en nuestro sistema, recibirás las instrucciones para restablecer tu contraseña.'
        );
    }

    public function edit(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
            'seo' => [
                'title' => 'Restablecer Contraseña | Import Hns San Luis',
                'description' => 'Define tu nueva contraseña de acceso.',
                'robots' => 'noindex, nofollow',
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $email = strtolower(trim($validated['email']));
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        $isExpired = $record && $record->created_at
            ? Carbon::parse($record->created_at)->addMinutes(60)->isPast()
            : true;

        if (! $record || $isExpired || ! Hash::check($validated['token'], $record->token)) {
            if ($record && $isExpired) {
                DB::table('password_reset_tokens')->where('email', $email)->delete();
            }

            throw ValidationException::withMessages([
                'email' => 'El enlace de recuperación no es válido o ha expirado.',
            ]);
        }

        $user = User::query()->where('email', $email)->firstOrFail();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        $this->activityLogger->log(
            'auth.password_reset_completed',
            'auth',
            sprintf('Contraseña restablecida para el usuario %s.', $user->email),
            $user,
            [],
            $user->id
        );

        return redirect()->route('login')->with('status', 'Tu contraseña ha sido actualizada correctamente. Ya puedes iniciar sesión.');
    }
}
