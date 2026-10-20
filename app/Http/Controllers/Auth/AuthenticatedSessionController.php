<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        protected ActivityLoggerService $activityLogger
    ) {}

    public function create(): View
    {
        return view('auth.login', [
            'seo' => [
                'title' => 'Iniciar Sesión | Import Hns San Luis',
                'description' => 'Accede a tu cuenta en Import Hns San Luis para revisar tu historial de cotizaciones o gestionar el catálogo comercial.',
                'canonical' => route('login'),
                'robots' => 'noindex, nofollow',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Por favor ingresa tu correo electrónico.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'password.required' => 'Por favor ingresa tu contraseña.',
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->string('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => sprintf('Demasiados intentos de inicio de sesión. Por favor intenta de nuevo en %d segundos.', $seconds),
            ]);
        }

        if (! Auth::attempt([
            'email' => strtolower(trim($credentials['email'])),
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'Las credenciales ingresadas no coinciden con nuestros registros.',
            ]);
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Tu cuenta se encuentra desactivada temporalmente. Comunícate con nuestro equipo de soporte.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        $user->update(['last_login_at' => now()]);

        $this->activityLogger->log(
            'auth.login',
            'auth',
            sprintf('Inicio de sesión exitoso del usuario %s (%s).', $user->name, $user->email),
            $user,
            [],
            $user->id
        );

        if ($user->canAccessAdminPanel()) {
            return redirect()->intended(url('/admin'));
        }

        return redirect()->intended(route('profile.edit'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            $this->activityLogger->log(
                'auth.logout',
                'auth',
                sprintf('Cierre de sesión del usuario %s.', $user->email),
                $user,
                [],
                $user->id
            );
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Has cerrado sesión correctamente.');
    }
}
