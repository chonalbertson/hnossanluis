<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\ContactMessageReceivedMail;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use App\Services\ActivityLoggerService;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function __construct(
        protected ActivityLoggerService $activityLogger,
        protected WhatsAppService $whatsAppService
    ) {}

    public function index(): View
    {
        $contactInfo = [
            'commercial_name' => (string) SiteSetting::getValue('company_commercial_name', 'Import Hnos San Luis'),
            'legal_name' => (string) SiteSetting::getValue('company_legal_name', 'IMPORT HNOS SAN LUIS S.A.C.'),
            'ruc' => (string) SiteSetting::getValue('company_ruc', '20608628143'),
            'email' => (string) SiteSetting::getValue('company_contact_email', 'ventas@hnssanluis.com'),
            'phone' => (string) SiteSetting::getValue('company_contact_phone', '977 208 514'),
            'address' => (string) SiteSetting::getValue('company_address', 'Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú'),
            'hours' => (string) SiteSetting::getValue('company_business_hours', 'Lunes a Viernes: 8:30 am - 6:00 pm | Sábados: 9:00 am - 1:00 pm'),
        ];

        return view('public.contact', [
            'contactInfo' => $contactInfo,
            'whatsappUrl' => $this->whatsAppService->generalContactUrl(),
            'seo' => [
                'title' => 'Contacto Comercial y Atención Técnica | IMPORT HNOS SAN LUIS S.A.C.',
                'description' => 'Comunícate con el equipo técnico y comercial de IMPORT HNOS SAN LUIS S.A.C. (RUC 20608628143) para atención mayorista B2B, proyectos de construcción y consultas ferreteras.',
                'canonical' => route('contact.index'),
                'robots' => 'index, follow',
                'breadcrumbs' => [
                    ['name' => 'Inicio', 'url' => route('home')],
                    ['name' => 'Contacto Comercial', 'url' => route('contact.index')],
                ],
            ],
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        // 1. Anti-spam Honeypot
        if ($request->filled('website_hp')) {
            throw ValidationException::withMessages([
                'name' => 'No se pudo procesar el mensaje por validación antispam.',
            ]);
        }

        // 2. Rate Limiting (max 5 contact messages per minute per IP)
        $rateLimitKey = 'contact-submit:'.$request->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            throw ValidationException::withMessages([
                'email' => sprintf('Has enviado varios mensajes recientemente. Por favor espera %d segundos antes de intentar nuevamente.', $seconds),
            ]);
        }

        if ($request->filled('ruc')) {
            $request->merge([
                'ruc' => preg_replace('/\D/', '', (string) $request->input('ruc')),
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:180'],
            'ruc' => ['nullable', 'regex:/^\d{11}$/'],
            'subject' => ['required', 'string', 'min:3', 'max:180'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
            'privacy_accepted' => ['accepted'],
        ], [
            'name.required' => 'Por favor ingresa tu nombre completo.',
            'email.required' => 'Por favor ingresa un correo electrónico de contacto.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'ruc.regex' => 'El RUC debe tener exactamente 11 dígitos numéricos.',
            'subject.required' => 'Indica el asunto o motivo de tu consulta.',
            'message.required' => 'Escribe el detalle de tu consulta o requerimiento.',
            'message.min' => 'El mensaje debe contener al menos 10 caracteres.',
            'privacy_accepted.accepted' => 'Debes aceptar la Política de Privacidad (Ley N° 29733).',
        ]);

        RateLimiter::hit($rateLimitKey, 60);

        $contactMessage = ContactMessage::query()->create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'phone' => ! empty($validated['phone']) ? trim($validated['phone']) : null,
            'company' => ! empty($validated['company']) ? trim($validated['company']) : null,
            'ruc' => $validated['ruc'] ?? null,
            'subject' => trim($validated['subject']),
            'message' => trim($validated['message']),
            'status' => 'pending',
            'privacy_accepted_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        try {
            $companyEmail = (string) SiteSetting::getValue('company_contact_email', 'ventas@hnssanluis.com');
            if ($companyEmail !== '') {
                Mail::to($companyEmail)->send(new ContactMessageReceivedMail($contactMessage));
            }
        } catch (\Throwable $e) {
            Log::warning('ContactMessageReceivedMail failed: '.$e->getMessage());
        }

        $this->activityLogger->log(
            'contact.submitted',
            'contact',
            sprintf('Mensaje de contacto recibido desde la web: "%s".', $contactMessage->subject),
            $contactMessage,
            [
                'subject' => $contactMessage->subject,
                'has_company' => ! empty($contactMessage->company),
                'has_ruc' => ! empty($contactMessage->ruc),
            ]
        );

        // Flash non-PII GA4 event payload
        session()->flash('ga4_contact_submit', [
            'inquiry_type' => 'contact_web',
            'client_type' => ! empty($contactMessage->ruc) ? 'b2b_ruc' : 'b2c_general',
        ]);

        return redirect()
            ->route('contact.index')
            ->with('success', 'Hemos recibido tu mensaje correctamente. Nuestro equipo comercial se pondrá en contacto contigo a la brevedad.');
    }
}
