<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SmtpTestMail;
use App\Models\SiteSetting;
use App\Services\ActivityLoggerService;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class CommunicationSettingController extends Controller
{
    public function __construct(
        protected ActivityLoggerService $activityLogger,
        protected WhatsAppService $whatsAppService
    ) {}

    public function edit(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('settings.manage'), 403);

        $advisors = $this->whatsAppService->getAdvisors();
        // Ensure at least 4 editable slots in the admin form
        while (count($advisors) < 4) {
            $advisors[] = ['name' => '', 'phone' => '', 'digits' => '', 'url' => ''];
        }

        $settings = [
            'whatsapp_business_number' => (string) SiteSetting::getValue('whatsapp_business_number', '51977208514'),
            'whatsapp_advisors' => $advisors,
            'whatsapp_floating_button_enabled' => (bool) SiteSetting::getValue('whatsapp_floating_button_enabled', true),
            'whatsapp_default_message' => (string) SiteSetting::getValue('whatsapp_default_message', ''),
            'company_contact_email' => (string) SiteSetting::getValue('company_contact_email', 'ventas@hnssanluis.com'),
            'sales_notification_email' => (string) SiteSetting::getValue('sales_notification_email', 'ventas@hnssanluis.com'),
        ];

        // Non-sensitive mail environment summary (never exposes passwords)
        $mailSummary = [
            'mailer' => config('mail.default', 'log'),
            'host' => config('mail.mailers.smtp.host', '127.0.0.1'),
            'port' => config('mail.mailers.smtp.port', 2525),
            'from_address' => config('mail.from.address', 'ventas@hnssanluis.com'),
            'from_name' => config('mail.from.name', 'IMPORT HNOS SAN LUIS S.A.C.'),
            'has_credentials' => ! empty(config('mail.mailers.smtp.username')),
        ];

        return view('admin.settings.communication', [
            'settings' => $settings,
            'mailSummary' => $mailSummary,
            'sampleWhatsAppUrl' => $this->whatsAppService->generalContactUrl(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('settings.manage'), 403);

        $validated = $request->validate([
            'whatsapp_business_number' => ['required', 'string', 'max:25', 'regex:/^[0-9+\s()-]{8,25}$/'],
            'whatsapp_floating_button_enabled' => ['nullable', 'boolean'],
            'whatsapp_default_message' => ['required', 'string', 'max:500'],
            'company_contact_email' => ['required', 'email', 'max:180'],
            'sales_notification_email' => ['required', 'email', 'max:180'],
            'advisors' => ['nullable', 'array', 'max:8'],
            'advisors.*.name' => ['nullable', 'string', 'max:60'],
            'advisors.*.phone' => ['nullable', 'string', 'max:25'],
        ]);

        $sanitizedNumber = $this->whatsAppService->normalizePhoneDigits($validated['whatsapp_business_number']);

        SiteSetting::setValue('whatsapp_business_number', $sanitizedNumber, 'whatsapp', 'string', 'Número de WhatsApp Business');
        SiteSetting::setValue('whatsapp_floating_button_enabled', $request->boolean('whatsapp_floating_button_enabled'), 'whatsapp', 'boolean', 'Habilitar botón flotante de WhatsApp');
        SiteSetting::setValue('whatsapp_default_message', trim($validated['whatsapp_default_message']), 'whatsapp', 'string', 'Mensaje predeterminado de WhatsApp');

        if ($request->has('advisors') && is_array($validated['advisors'] ?? null)) {
            $cleanAdvisors = [];
            foreach ($validated['advisors'] as $row) {
                $phone = trim((string) ($row['phone'] ?? ''));
                $name = trim((string) ($row['name'] ?? ''));
                if ($phone !== '') {
                    $cleanAdvisors[] = [
                        'name' => $name,
                        'phone' => $phone,
                    ];
                }
            }

            if (! empty($cleanAdvisors)) {
                SiteSetting::setValue('whatsapp_advisors', $cleanAdvisors, 'whatsapp', 'json', 'Asesoras y números de WhatsApp visibles en la web');
                SiteSetting::setValue('company_contact_phone', $cleanAdvisors[0]['phone'], 'contact', 'string', 'Teléfono comercial principal');
            }
        }

        SiteSetting::setValue('company_contact_email', strtolower(trim($validated['company_contact_email'])), 'contact', 'string', 'Correo corporativo de atención comercial', true);
        SiteSetting::setValue('sales_notification_email', strtolower(trim($validated['sales_notification_email'])), 'contact', 'string', 'Correo receptor de alertas de cotizaciones', false);

        $this->activityLogger->log(
            'settings.communication_updated',
            'settings',
            sprintf('Configuración de WhatsApp Business (%s), asesoras y correos de notificación actualizada.', $sanitizedNumber),
            null,
            [
                'whatsapp_business_number' => $sanitizedNumber,
                'whatsapp_floating_button_enabled' => $request->boolean('whatsapp_floating_button_enabled'),
                'company_contact_email' => $validated['company_contact_email'],
                'sales_notification_email' => $validated['sales_notification_email'],
            ]
        );

        return back()->with('success', 'Configuración de números y asesoras de WhatsApp y correos corporativos guardada correctamente.');
    }

    public function testMail(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('settings.manage'), 403);

        $validated = $request->validate([
            'recipient_email' => ['required', 'email', 'max:180'],
        ]);

        $recipient = strtolower(trim($validated['recipient_email']));

        try {
            Mail::to($recipient)->send(new SmtpTestMail($recipient, $request->user()?->name));

            $this->activityLogger->log(
                'settings.smtp_tested',
                'settings',
                sprintf('Prueba de envío de correo corporativo ejecutada exitosamente hacia %s.', $recipient),
                null,
                [
                    'recipient_email' => $recipient,
                    'mailer' => config('mail.default'),
                    'status' => 'sent',
                ]
            );

            return back()->with('success', sprintf(
                'Correo de prueba despachado exitosamente a "%s" mediante el transporte "%s".',
                $recipient,
                config('mail.default', 'log')
            ));
        } catch (Throwable $e) {
            Log::warning('Fallo en prueba de correo SMTP hacia '.$recipient.': '.$e->getMessage());

            $this->activityLogger->log(
                'settings.smtp_test_failed',
                'settings',
                sprintf('Fallo en prueba de envío de correo hacia %s.', $recipient),
                null,
                [
                    'recipient_email' => $recipient,
                    'mailer' => config('mail.default'),
                    'status' => 'failed',
                ]
            );

            return back()->with('error', 'No se pudo completar el envío de correo de prueba. Verifique los parámetros MAIL_* en el servidor.');
        }
    }
}
