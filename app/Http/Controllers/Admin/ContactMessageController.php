<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Customer;
use App\Services\ActivityLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function __construct(
        protected ActivityLoggerService $activityLogger
    ) {}

    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->hasPermission('quotes.view')
            || $request->user()?->hasPermission('quotes.manage')
            || $request->user()?->hasPermission('quotes.respond'),
            403
        );

        $query = ContactMessage::query();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('q')) {
            $term = '%'.trim($request->string('q')->toString()).'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('company', 'like', $term)
                    ->orWhere('ruc', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('subject', 'like', $term);
            });
        }

        $messages = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $statusCounts = [
            'all' => ContactMessage::query()->count(),
            'nuevo' => ContactMessage::query()->where('status', 'nuevo')->count(),
            'leido' => ContactMessage::query()->where('status', 'leido')->count(),
            'respondido' => ContactMessage::query()->where('status', 'respondido')->count(),
        ];

        return view('admin.contact-messages.index', [
            'messages' => $messages,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function show(Request $request, ContactMessage $contactMessage): View
    {
        abort_unless(
            $request->user()?->hasPermission('quotes.view')
            || $request->user()?->hasPermission('quotes.manage')
            || $request->user()?->hasPermission('quotes.respond'),
            403
        );

        if ($contactMessage->status === 'nuevo') {
            $contactMessage->update(['status' => 'leido']);

            $this->activityLogger->log(
                'contact_message.read',
                'contact_messages',
                sprintf('Mensaje de contacto #%d de "%s" marcado como leído.', $contactMessage->id, $contactMessage->name),
                $contactMessage
            );
        }

        $existingCustomer = Customer::query()
            ->where('email', strtolower(trim($contactMessage->email)))
            ->when($contactMessage->ruc, fn ($q) => $q->orWhere('ruc', $contactMessage->ruc))
            ->first();

        $whatsAppReplyUrl = null;
        if (! empty($contactMessage->phone)) {
            $digits = preg_replace('/\D/', '', (string) $contactMessage->phone);
            if ($digits !== '') {
                if (strlen($digits) === 9 && str_starts_with($digits, '9')) {
                    $digits = '51'.$digits;
                }
                $replyText = sprintf(
                    'Hola %s, le saludamos de Import Hns San Luis en atención a su consulta web sobre "%s".',
                    $contactMessage->name,
                    $contactMessage->subject ?: 'Catálogo y Cotizaciones B2B'
                );
                $whatsAppReplyUrl = sprintf('https://wa.me/%s?text=%s', $digits, rawurlencode($replyText));
            }
        }

        return view('admin.contact-messages.show', [
            'contactMessage' => $contactMessage,
            'existingCustomer' => $existingCustomer,
            'whatsAppReplyUrl' => $whatsAppReplyUrl,
        ]);
    }

    public function updateStatus(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        abort_unless(
            $request->user()?->hasPermission('quotes.manage')
            || $request->user()?->hasPermission('quotes.respond')
            || $request->user()?->hasPermission('quotes.view'),
            403
        );

        $validated = $request->validate([
            'status' => ['required', Rule::in(['nuevo', 'leido', 'respondido'])],
        ]);

        $previousStatus = $contactMessage->status;
        $contactMessage->update(['status' => $validated['status']]);

        $this->activityLogger->log(
            'contact_message.status_changed',
            'contact_messages',
            sprintf(
                'Estado del mensaje de contacto #%d (%s) cambiado de "%s" a "%s".',
                $contactMessage->id,
                $contactMessage->email,
                $previousStatus,
                $validated['status']
            ),
            $contactMessage,
            [
                'previous_status' => $previousStatus,
                'new_status' => $validated['status'],
            ]
        );

        return back()->with('success', sprintf('Estado del mensaje actualizado a "%s".', strtoupper($validated['status'])));
    }

    public function destroy(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('quotes.manage'), 403);

        $sender = $contactMessage->name.' ('.$contactMessage->email.')';
        $id = $contactMessage->id;
        $contactMessage->delete();

        $this->activityLogger->log(
            'contact_message.deleted',
            'contact_messages',
            sprintf('Mensaje de contacto #%d de %s eliminado.', $id, $sender)
        );

        return redirect()
            ->route('admin.contact-messages.index')
            ->with('success', 'Mensaje de contacto eliminado correctamente.');
    }
}
