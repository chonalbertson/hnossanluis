<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\ComplaintBookReceiptMail;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\PageContent;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Services\ActivityLoggerService;
use App\Services\WhatsAppService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsAppService,
        protected ActivityLoggerService $activityLogger
    ) {}

    /**
     * Área Corporativa: /empresa (hub) y /empresa/{section} (8 páginas institucionales individuales).
     */
    public function company(?string $section = null, ?string $customCanonical = null): View
    {
        $sections = $this->companySections();
        $page = PageContent::query()->where('page_key', 'about')->first();

        $currentSection = ($section !== null && isset($sections[$section])) ? $section : null;
        $currentMeta = $currentSection !== null ? $sections[$currentSection] : null;

        $slugs = array_keys($sections);
        $position = $currentSection !== null ? array_search($currentSection, $slugs, true) : false;
        $previousSlug = ($position !== false && $position > 0) ? $slugs[$position - 1] : null;
        $nextSlug = ($position !== false && $position < count($slugs) - 1) ? $slugs[$position + 1] : null;

        $label = $currentMeta['title'] ?? 'Empresa y Soluciones Corporativas B2B';
        $summary = $currentMeta['summary'] ?? 'Conoce a IMPORT HNOS SAN LUIS S.A.C.: experiencia, especialidades, cobertura, equipo, marcas, políticas y proyectos en abastecimiento B2B de herrajes y ferretería industrial.';

        $canonical = $customCanonical ?: ($currentSection !== null
            ? route('pages.company', ['section' => $currentSection])
            : route('pages.company'));

        $view = $currentSection !== null ? 'public.pages.company.'.$currentSection : 'public.pages.company.index';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Empresa', 'url' => route('pages.company')],
        ];
        if ($currentSection !== null) {
            $breadcrumbs[] = ['name' => $label, 'url' => $canonical];
        }

        return view($view, array_merge([
            'page' => $page,
            'sections' => $sections,
            'currentSection' => $currentSection,
            'currentMeta' => $currentMeta,
            'previousSlug' => $previousSlug,
            'nextSlug' => $nextSlug,
            'companyInfo' => $this->companyInfo(),
            'whatsappUrl' => $this->whatsAppService->generalContactUrl(),
            'stats' => [
                'products' => Product::query()->where('status', 'published')->count(),
                'categories' => Category::query()->whereNull('parent_id')->where('is_active', true)->count(),
                'brands' => Brand::query()->where('is_active', true)->where('is_verified', true)->count(),
            ],
            'seo' => [
                'title' => $label.' | IMPORT HNOS SAN LUIS S.A.C. (RUC 20608628143)',
                'description' => $summary.' IMPORT HNOS SAN LUIS S.A.C. (RUC 20608628143) — Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú.',
                'canonical' => $canonical,
                'robots' => ($page === null || $page->is_indexable) ? 'index, follow' : 'noindex, follow',
                'breadcrumbs' => $breadcrumbs,
            ],
        ], $this->companySectionData($currentSection)));
    }

    /**
     * Presentación empresarial (/nosotros) integrada con la página corporativa Empresa.
     */
    public function about(): View
    {
        return $this->company('nosotros-experiencia', route('pages.about'));
    }

    /**
     * Metadatos de las 8 páginas del menú Empresa (en el orden del menú principal).
     *
     * @return array<string, array{label: string, title: string, eyebrow: string, summary: string, icon: string}>
     */
    protected function companySections(): array
    {
        return [
            'nosotros-experiencia' => [
                'label' => 'Nosotros / Experiencia',
                'title' => 'Nosotros y Experiencia',
                'eyebrow' => 'Identidad Institucional',
                'summary' => 'Importadores y distribuidores mayoristas de herrajes para muebles, fijaciones y ferretería técnica para la industria del mueble y la construcción en el Perú.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
            ],
            'especialidades-b2b' => [
                'label' => 'Especialidades B2B',
                'title' => 'Especialidades B2B',
                'eyebrow' => 'Divisiones de Abastecimiento',
                'summary' => 'Líneas técnicas especializadas para fábricas de muebles, constructoras, distribuidores ferreteros, contratistas y talleres de carpintería.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>',
            ],
            'certificaciones' => [
                'label' => 'Certificaciones',
                'title' => 'Certificaciones y Estándares de Calidad',
                'eyebrow' => 'Garantía Técnica y Normativa',
                'summary' => 'Trabajamos con fabricantes que cumplen exigencias de durabilidad mecánica, resistencia a la corrosión, trazabilidad por SKU y formalidad tributaria.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
            ],
            'cobertura-nacional' => [
                'label' => 'Cobertura Nacional',
                'title' => 'Cobertura Nacional',
                'eyebrow' => 'Alcance Logístico',
                'summary' => 'Atención en tienda en Lima Centro, despacho a talleres y obras en Lima Metropolitana y Callao, y envíos diarios a provincias mediante agencias de carga.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            ],
            'nuestro-equipo' => [
                'label' => 'Nuestro Equipo',
                'title' => 'Nuestro Equipo',
                'eyebrow' => 'Atención Personalizada',
                'summary' => 'Asesoras comerciales y equipo técnico que te acompañan en la selección de herrajes, validación de stock y cotizaciones por volumen.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
            ],
            'marcas-representadas' => [
                'label' => 'Marcas Representadas',
                'title' => 'Marcas Representadas',
                'eyebrow' => 'Respaldo de Fabricantes',
                'summary' => 'Marcas líderes en herrajes para muebles, cerrajería, adhesivos, herramientas y accesorios industriales, disponibles en nuestro catálogo técnico.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>',
            ],
            'politicas-atencion-corporativa' => [
                'label' => 'Políticas / Atención Corporativa',
                'title' => 'Políticas y Atención Corporativa',
                'eyebrow' => 'Transparencia y Cumplimiento',
                'summary' => 'Condiciones de cotización, términos de uso, protección de datos personales (Ley N° 29733), Libro de Reclamaciones y canales oficiales de atención.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
            ],
            'casos-y-proyectos-reales' => [
                'label' => 'Casos y Proyectos Reales',
                'title' => 'Casos y Proyectos Reales',
                'eyebrow' => 'Aplicaciones e Industria',
                'summary' => 'Nuestros productos están presentes en equipamiento inmobiliario, fabricación seriada de muebles y distribución ferretera en todo el Perú.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>',
            ],
        ];
    }

    /**
     * Datos específicos que necesita cada página individual de Empresa.
     *
     * @return array<string, mixed>
     */
    protected function companySectionData(?string $section): array
    {
        return match ($section) {
            'especialidades-b2b' => ['categories' => $this->activeRootCategories()],
            'nuestro-equipo' => ['whatsappAdvisors' => $this->whatsAppService->getAdvisors()],
            'marcas-representadas' => ['verifiedBrands' => Brand::query()
                ->where('is_active', true)
                ->where('is_verified', true)
                ->withCount(['products' => fn ($q) => $q->where('status', 'published')])
                ->orderBy('sort_order')
                ->get()],
            'casos-y-proyectos-reales' => ['testimonials' => Testimonial::query()
                ->where('is_active', true)
                ->where('is_verified', true)
                ->orderBy('sort_order')
                ->get()],
            default => [],
        };
    }

    /**
     * @return Collection<int, Category>
     */
    protected function activeRootCategories(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->where('status', 'published')])
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @return array{commercial_name: string, legal_name: string, ruc: string, address: string, email: string, phone: string, hours: string}
     */
    protected function companyInfo(): array
    {
        return [
            'commercial_name' => (string) SiteSetting::getValue('company_commercial_name', 'Import Hnos San Luis'),
            'legal_name' => (string) SiteSetting::getValue('company_legal_name', 'IMPORT HNOS SAN LUIS S.A.C.'),
            'ruc' => (string) SiteSetting::getValue('company_ruc', '20608628143'),
            'address' => (string) SiteSetting::getValue('company_address', 'Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú'),
            'email' => (string) SiteSetting::getValue('company_contact_email', 'ventas@hnssanluis.com'),
            'phone' => (string) SiteSetting::getValue('company_contact_phone', '977 208 514'),
            'hours' => (string) SiteSetting::getValue('company_business_hours', 'Lunes a Viernes: 8:30 am - 6:00 pm | Sábados: 9:00 am - 1:00 pm'),
        ];
    }

    /**
     * Clientes y Testimonios (/clientes-y-testimonios):
     * Muestra solo testimonios verificados/activos o un mensaje transparente si aún no hay publicados.
     */
    public function testimonials(): View
    {
        $testimonials = Testimonial::query()
            ->where('is_active', true)
            ->where('is_verified', true)
            ->orderBy('sort_order')
            ->get();

        $verifiedBrands = Brand::query()
            ->where('is_active', true)
            ->where('is_verified', true)
            ->orderBy('sort_order')
            ->get();

        return view('public.pages.testimonials', [
            'testimonials' => $testimonials,
            'verifiedBrands' => $verifiedBrands,
            'whatsappUrl' => $this->whatsAppService->generalContactUrl(),
            'seo' => [
                'title' => 'Clientes, Sectores Atendidos y Testimonios | IMPORT HNOS SAN LUIS S.A.C.',
                'description' => 'Sectores atendidos y referencias comerciales verificadas de IMPORT HNOS SAN LUIS S.A.C. en ferretería industrial, carpintería y construcción.',
                'canonical' => route('pages.testimonials'),
                'robots' => 'index, follow',
                'breadcrumbs' => [
                    ['name' => 'Inicio', 'url' => route('home')],
                    ['name' => 'Clientes y Testimonios', 'url' => route('pages.testimonials')],
                ],
            ],
        ]);
    }

    public function privacy(): View
    {
        return $this->renderLegalPage(
            'privacy_policy',
            'Política de Privacidad y Protección de Datos Personales (Ley N° 29733)',
            route('pages.privacy')
        );
    }

    public function cookies(): View
    {
        return $this->renderLegalPage(
            'cookies_policy',
            'Política de Cookies',
            route('pages.cookies')
        );
    }

    public function terms(): View
    {
        return $this->renderLegalPage(
            'terms_conditions',
            'Términos y Condiciones de Uso',
            route('pages.terms')
        );
    }

    public function quoteConditions(): View
    {
        return $this->renderLegalPage(
            'quote_conditions',
            'Condiciones Generales de Cotización',
            route('pages.quote-conditions')
        );
    }

    /**
     * Libro de Reclamaciones Virtual conforme al Código de Protección y Defensa del Consumidor (Ley N° 29571 - INDECOPI).
     */
    public function complaintsBook(): View
    {
        $companyInfo = [
            'commercial_name' => (string) SiteSetting::getValue('company_commercial_name', 'Import Hnos San Luis'),
            'legal_name' => (string) SiteSetting::getValue('company_legal_name', 'IMPORT HNOS SAN LUIS S.A.C.'),
            'ruc' => (string) SiteSetting::getValue('company_ruc', '20608628143'),
            'address' => (string) SiteSetting::getValue('company_address', 'Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú'),
            'email' => (string) SiteSetting::getValue('company_contact_email', 'ventas@hnssanluis.com'),
            'phone' => (string) SiteSetting::getValue('company_contact_phone', '977 208 514'),
        ];

        return view('public.pages.complaints-book', [
            'companyInfo' => $companyInfo,
            'seo' => [
                'title' => 'Libro de Reclamaciones Virtual | IMPORT HNOS SAN LUIS S.A.C. (RUC 20608628143)',
                'description' => 'Libro de Reclamaciones Virtual de IMPORT HNOS SAN LUIS S.A.C. — RUC 20608628143 (Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú), conforme al Código de Protección y Defensa del Consumidor (Ley N° 29571 - INDECOPI).',
                'canonical' => route('pages.complaints-book'),
                'robots' => 'index, follow',
                'breadcrumbs' => [
                    ['name' => 'Inicio', 'url' => route('home')],
                    ['name' => 'Libro de Reclamaciones Virtual', 'url' => route('pages.complaints-book')],
                ],
            ],
        ]);
    }

    /**
     * Procesa y registra una Hoja de Reclamación Virtual (Reclamo o Queja).
     */
    public function submitComplaintsBook(Request $request): RedirectResponse
    {
        if ($request->filled('website_hp')) {
            throw ValidationException::withMessages([
                'consumer_name' => 'No se pudo procesar el registro por validación antispam.',
            ]);
        }

        $rateLimitKey = 'complaints-book:'.$request->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            throw ValidationException::withMessages([
                'consumer_email' => sprintf('Has enviado registros recientemente. Por favor espera %d segundos antes de intentar nuevamente.', $seconds),
            ]);
        }

        $validated = $request->validate([
            'document_type' => ['required', Rule::in(['DNI', 'RUC', 'CE', 'Pasaporte'])],
            'document_number' => ['required', 'string', 'min:6', 'max:20'],
            'consumer_name' => ['required', 'string', 'min:3', 'max:150'],
            'consumer_email' => ['required', 'email', 'max:150'],
            'consumer_phone' => ['required', 'string', 'min:6', 'max:40'],
            'consumer_address' => ['required', 'string', 'min:5', 'max:250'],
            'company_name' => ['nullable', 'string', 'max:180'],
            'good_type' => ['required', Rule::in(['Producto', 'Servicio'])],
            'claimed_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'good_description' => ['required', 'string', 'min:4', 'max:250'],
            'claim_type' => ['required', Rule::in(['Reclamo', 'Queja'])],
            'claim_detail' => ['required', 'string', 'min:10', 'max:3000'],
            'consumer_request' => ['required', 'string', 'min:5', 'max:2000'],
            'privacy_accepted' => ['accepted'],
        ], [
            'document_type.required' => 'Selecciona tu tipo de documento.',
            'document_number.required' => 'Ingresa tu número de documento.',
            'consumer_name.required' => 'Ingresa tu nombre completo.',
            'consumer_email.required' => 'Ingresa tu correo electrónico.',
            'consumer_phone.required' => 'Ingresa tu teléfono de contacto.',
            'consumer_address.required' => 'Ingresa tu domicilio.',
            'good_type.required' => 'Selecciona si se trata de un Producto o Servicio.',
            'good_description.required' => 'Describe el bien contratado o código de cotización/pedido.',
            'claim_type.required' => 'Selecciona si deseas registrar un Reclamo o una Queja.',
            'claim_detail.required' => 'Describe el detalle de tu reclamo o queja (mínimo 10 caracteres).',
            'consumer_request.required' => 'Indica el pedido concreto del consumidor.',
            'privacy_accepted.accepted' => 'Debes aceptar la Política de Privacidad y declaración jurada.',
        ]);

        RateLimiter::hit($rateLimitKey, 60);

        $sheetCode = sprintf('REC-%s-%05d', date('Y'), random_int(10000, 99999));

        $formattedMessage = implode("\n", [
            "=== HOJA DE RECLAMACIÓN VIRTUAL: {$sheetCode} ===",
            'Proveedor: IMPORT HNOS SAN LUIS S.A.C. — RUC: 20608628143',
            'Domicilio del Establecimiento: Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú',
            'Fecha y Hora de Registro: '.now()->format('d/m/Y H:i:s'),
            '',
            '1. IDENTIFICACIÓN DEL CONSUMIDOR RECLAMANTE:',
            "- Documento: {$validated['document_type']} {$validated['document_number']}",
            "- Nombre Completo: {$validated['consumer_name']}",
            '- Empresa: '.($validated['company_name'] ?: 'Persona Natural'),
            "- Domicilio: {$validated['consumer_address']}",
            "- Teléfono: {$validated['consumer_phone']}",
            "- Correo: {$validated['consumer_email']}",
            '',
            '2. IDENTIFICACIÓN DEL BIEN CONTRATADO:',
            "- Tipo: {$validated['good_type']}",
            '- Monto Reclamado (S/): '.($validated['claimed_amount'] !== null ? number_format((float) $validated['claimed_amount'], 2) : 'No especificado'),
            "- Descripción / Referencia: {$validated['good_description']}",
            '',
            '3. DETALLE DE LA RECLAMACIÓN Y PEDIDO DEL CONSUMIDOR:',
            '- Tipo: '.strtoupper($validated['claim_type']),
            "- Detalle: {$validated['claim_detail']}",
            "- Pedido del Consumidor: {$validated['consumer_request']}",
        ]);

        $rucField = ($validated['document_type'] === 'RUC' && preg_match('/^\d{11}$/', $validated['document_number']))
            ? $validated['document_number']
            : null;

        $record = ContactMessage::query()->create([
            'name' => trim($validated['consumer_name']),
            'email' => strtolower(trim($validated['consumer_email'])),
            'phone' => trim($validated['consumer_phone']),
            'company' => ! empty($validated['company_name']) ? trim($validated['company_name']) : null,
            'ruc' => $rucField,
            'subject' => sprintf('[LIBRO DE RECLAMACIONES %s] %s - %s', $sheetCode, strtoupper($validated['claim_type']), trim($validated['good_description'])),
            'message' => $formattedMessage,
            'status' => 'pending',
            'privacy_accepted_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        try {
            $companyEmail = (string) SiteSetting::getValue('company_contact_email', 'ventas@hnssanluis.com');
            $recipients = array_values(array_unique(array_filter([
                $record->email,
                $companyEmail,
            ])));
            foreach ($recipients as $recipient) {
                Mail::to($recipient)->send(new ComplaintBookReceiptMail($record, $sheetCode, $validated['claim_type']));
            }
        } catch (\Throwable $e) {
            Log::warning('ComplaintBookReceiptMail failed: '.$e->getMessage());
        }

        $this->activityLogger->log(
            'complaints_book.submitted',
            'complaints_book',
            sprintf('Hoja de Reclamación %s (%s) registrada en el Libro de Reclamaciones.', $sheetCode, $validated['claim_type']),
            $record,
            [
                'sheet_code' => $sheetCode,
                'claim_type' => $validated['claim_type'],
                'good_type' => $validated['good_type'],
            ]
        );

        return redirect()
            ->route('pages.complaints-book')
            ->with('complaint_sheet_code', $sheetCode)
            ->with('success', sprintf('Tu Hoja de Reclamación N° %s ha sido registrada exitosamente en el Libro de Reclamaciones de IMPORT HNOS SAN LUIS S.A.C. (RUC 20608628143).', $sheetCode));
    }

    protected function renderLegalPage(string $pageKey, string $fallbackTitle, string $canonicalUrl): View
    {
        $page = PageContent::query()->where('page_key', $pageKey)->first();

        return view('public.pages.legal', [
            'page' => $page,
            'pageKey' => $pageKey,
            'fallbackTitle' => $fallbackTitle,
            'seo' => [
                'title' => $page?->meta_title ?: $fallbackTitle.' | IMPORT HNOS SAN LUIS S.A.C.',
                'description' => $page?->meta_description ?: $fallbackTitle.' de IMPORT HNOS SAN LUIS S.A.C. (RUC 20608628143).',
                'canonical' => $canonicalUrl,
                'robots' => ($page === null || $page->is_indexable) ? 'index, follow' : 'noindex, follow',
                'breadcrumbs' => [
                    ['name' => 'Inicio', 'url' => route('home')],
                    ['name' => $fallbackTitle, 'url' => $canonicalUrl],
                ],
            ],
        ]);
    }
}
