@props(['seo' => []])

@php
    use App\Models\SiteSetting;

    $defaultTitle = (string) SiteSetting::getValue('seo_default_title', 'Import Hnos San Luis | Catálogo Técnico de Ferretería, Herrajes y Construcción');
    $defaultDescription = (string) SiteSetting::getValue('seo_default_description', 'Catálogo técnico B2B/B2C y solicitud de cotizaciones de herrajes para muebles, bisagras, correderas, cerraduras, fijaciones y construcción en Perú.');
    $siteName = (string) SiteSetting::getValue('company_commercial_name', 'Import Hnos San Luis');
    $legalName = (string) SiteSetting::getValue('company_legal_name', 'IMPORT HNOS SAN LUIS S.A.C.');
    $companyRuc = (string) SiteSetting::getValue('company_ruc', '20608628143');

    $title = trim((string) ($seo['title'] ?? $defaultTitle));
    $rawDescription = (string) ($seo['description'] ?? $defaultDescription);
    $description = trim((string) preg_replace('/\s+/', ' ', strip_tags($rawDescription))) ?: $defaultDescription;
    $canonicalUrl = $seo['canonical'] ?? url()->current();
    $robots = $seo['robots'] ?? 'index, follow';
    $ogType = $seo['og_type'] ?? 'website';
    $rawOgImage = $seo['og_image'] ?? null;
    $ogImage = ($rawOgImage && !str_ends_with(strtolower((string) $rawOgImage), '.svg'))
        ? $rawOgImage
        : asset('images/logo-sanluis.png');
    $prevUrl = $seo['prev_url'] ?? null;
    $nextUrl = $seo['next_url'] ?? null;

    $ga4Enabled = (bool) SiteSetting::getValue('ga4_enabled', false);
    $ga4MeasurementId = trim((string) SiteSetting::getValue('ga4_measurement_id', env('GA4_MEASUREMENT_ID', '')));

    $orgSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'HardwareStore',
        '@id' => url('/').'#organization',
        'name' => $siteName,
        'legalName' => $legalName,
        'taxID' => $companyRuc,
        'url' => url('/'),
        'logo' => asset('images/logo-sanluis.png'),
        'image' => asset('images/logo-sanluis.png'),
        'description' => $defaultDescription,
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => (string) SiteSetting::getValue('company_address', 'Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú'),
            'addressLocality' => 'Lima Centro',
            'addressRegion' => 'Lima',
            'addressCountry' => 'PE',
        ],
        'areaServed' => [
            '@type' => 'Country',
            'name' => 'PE',
        ],
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'contactType' => 'sales',
            'telephone' => (string) SiteSetting::getValue('company_contact_phone', '+51 977 208 514'),
            'email' => (string) SiteSetting::getValue('company_contact_email', 'ventas@hnssanluis.com'),
            'availableLanguage' => ['Spanish'],
            'areaServed' => 'PE',
        ],
    ];

    $websiteSchema = null;
    if (!empty($seo['include_website_schema'])) {
        $websiteSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => url('/').'#website',
            'url' => url('/'),
            'name' => $siteName,
            'inLanguage' => 'es-PE',
            'publisher' => ['@id' => url('/').'#organization'],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => route('catalog.index').'?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    $breadcrumbSchema = null;
    if (!empty($seo['breadcrumbs']) && is_array($seo['breadcrumbs'])) {
        $itemListElement = [];
        foreach (array_values($seo['breadcrumbs']) as $idx => $crumb) {
            $itemListElement[] = [
                '@type' => 'ListItem',
                'position' => $idx + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'],
            ];
        }
        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemListElement,
        ];
    }
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $robots }}">
<meta name="theme-color" content="#0F172A">
<link rel="canonical" href="{{ $canonicalUrl }}">
@if($prevUrl)
<link rel="prev" href="{{ $prevUrl }}">
@endif
@if($nextUrl)
<link rel="next" href="{{ $nextUrl }}">
@endif
<link rel="sitemap" type="application/xml" title="Sitemap" href="{{ route('seo.sitemap') }}">
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
<link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon.png') }}">
<link rel="icon" type="image/png" sizes="256x256" href="{{ asset('images/logo-sanluis-icon.png') }}">
<link rel="apple-touch-icon" sizes="256x256" href="{{ asset('images/logo-sanluis-icon.png') }}">

<!-- Open Graph / WhatsApp / LinkedIn -->
<meta property="og:locale" content="es_PE">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:alt" content="{{ $title }}">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $ogImage }}">
<meta name="twitter:image:alt" content="{{ $title }}">

<!-- JSON-LD Structured Data -->
<script type="application/ld+json">{!! json_encode($orgSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

@if($websiteSchema)
<script type="application/ld+json">{!! json_encode($websiteSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif

@if($breadcrumbSchema)
<script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif

@if(!empty($seo['product_schema']))
<script type="application/ld+json">{!! json_encode($seo['product_schema'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif

@if($ga4Enabled && $ga4MeasurementId !== '')
<!-- Google Analytics 4 (Zero-PII Configured) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($ga4MeasurementId) }}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ e($ga4MeasurementId) }}', { send_page_view: true, allow_google_signals: false });
</script>
@endif
