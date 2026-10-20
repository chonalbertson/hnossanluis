<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha Técnica {{ $product->sku }} — {{ $companyLegalName ?? $companyName ?? 'IMPORT HNOS SAN LUIS S.A.C.' }}</title>
    <style>
        @page {
            margin: 24px 28px 52px 28px;
        }
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }
        table {
            border-collapse: collapse;
        }
        /* Header */
        .header-table {
            width: 100%;
            margin-bottom: 8px;
        }
        .header-table td {
            vertical-align: top;
        }
        .logo-img {
            max-height: 46px;
            max-width: 240px;
            display: block;
            margin-bottom: 5px;
        }
        .company-legal {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
            letter-spacing: 0.2px;
        }
        .company-tagline {
            font-size: 9px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            margin: 1px 0 3px 0;
        }
        .company-meta {
            font-size: 8.5px;
            color: #334155;
            margin: 1px 0;
        }
        .doc-badge-box {
            border: 1px solid #cbd5e1;
            border-top: 4px solid #dc2626;
            background-color: #f8fafc;
            padding: 8px 10px;
            text-align: right;
        }
        .doc-badge-type {
            font-size: 8.5px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .doc-badge-sku {
            font-size: 13px;
            font-weight: bold;
            color: #dc2626;
            margin: 3px 0;
        }
        .doc-badge-meta {
            font-size: 8.5px;
            color: #475569;
            margin-top: 2px;
        }
        .status-pill {
            display: inline-block;
            padding: 2px 7px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #e2e8f0;
            color: #0f172a;
            border-radius: 3px;
            margin-top: 4px;
        }
        .status-available {
            background-color: #dcfce7;
            color: #166534;
        }
        .status-request {
            background-color: #fef3c7;
            color: #92400e;
        }
        /* Brand divider */
        .divider-bar {
            width: 100%;
            margin-bottom: 12px;
        }
        .divider-navy {
            height: 3px;
            background-color: #0f172a;
            width: 72%;
        }
        .divider-red {
            height: 3px;
            background-color: #dc2626;
            width: 28%;
        }

        /* Product Title Banner */
        .title-banner {
            background-color: #0f172a;
            color: #ffffff;
            padding: 9px 12px;
            border-left: 5px solid #dc2626;
            margin-bottom: 12px;
        }
        .title-taxonomy {
            font-size: 8.5px;
            color: #93c5fd;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .product-title {
            font-size: 14px;
            font-weight: bold;
            color: #ffffff;
            margin: 0;
            line-height: 1.25;
        }

        /* Hero 2-col layout */
        .hero-table {
            width: 100%;
            margin-bottom: 12px;
        }
        .hero-image-cell {
            width: 35%;
            vertical-align: top;
            padding-right: 12px;
        }
        .hero-details-cell {
            width: 65%;
            vertical-align: top;
        }
        .image-frame {
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            padding: 8px;
            text-align: center;
        }
        .product-main-img {
            max-width: 195px;
            max-height: 175px;
            display: block;
            margin: 0 auto;
        }
        .product-sub-img {
            max-width: 110px;
            max-height: 80px;
            display: block;
            margin: 6px auto 0 auto;
            border-top: 1px dashed #e2e8f0;
            padding-top: 6px;
        }
        .image-caption {
            font-size: 7.5px;
            color: #64748b;
            margin-top: 5px;
            text-align: center;
        }
        .no-image-box {
            padding: 36px 10px;
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            color: #64748b;
            font-size: 9px;
            text-align: center;
        }

        /* Quick Data Grid */
        .quick-grid {
            width: 100%;
            border: 1px solid #cbd5e1;
            margin-bottom: 9px;
        }
        .quick-grid td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            font-size: 9.5px;
            vertical-align: top;
        }
        .quick-label {
            font-size: 8px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            display: block;
            margin-bottom: 1px;
        }
        .quick-value {
            font-weight: bold;
            color: #0f172a;
        }
        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #1e3a8a;
            padding: 8px 10px;
            font-size: 9.5px;
            color: #1e293b;
        }
        .summary-title {
            font-size: 9px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        /* Section Headings */
        .section-header {
            background-color: #1e3a8a;
            color: #ffffff;
            padding: 6px 10px;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-left: 4px solid #dc2626;
            margin-top: 12px;
            margin-bottom: 0;
        }

        /* Specs & Variants Tables */
        .data-table {
            width: 100%;
            border: 1px solid #cbd5e1;
            margin-bottom: 10px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            text-align: left;
            padding: 5px 8px;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e1;
        }
        .data-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9.5px;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .spec-group-badge {
            display: inline-block;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1e3a8a;
            background-color: #dbeafe;
            padding: 1px 5px;
            border-radius: 2px;
            margin-right: 4px;
        }
        .key-col {
            width: 40%;
            font-weight: bold;
            color: #334155;
        }

        /* Corporate Contact & B2B Box */
        .corporate-card {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-top: 3px solid #0f172a;
            background-color: #f8fafc;
            margin-top: 12px;
        }
        .corporate-card td {
            padding: 8px 10px;
            vertical-align: top;
            font-size: 8.5px;
            color: #334155;
        }
        .corp-col-title {
            font-size: 9px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 4px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
        }
        .advisor-badge {
            display: inline-block;
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 2px 6px;
            border-radius: 3px;
            margin: 2px 3px 2px 0;
            font-size: 8px;
            color: #0f172a;
        }
        .conditions-note {
            font-size: 8px;
            color: #475569;
            margin-top: 6px;
            padding-top: 5px;
            border-top: 1px dashed #cbd5e1;
        }

        /* Fixed Footer */
        .page-footer {
            position: fixed;
            bottom: -36px;
            left: 0;
            right: 0;
            height: 28px;
            border-top: 2px solid #dc2626;
            padding-top: 5px;
            font-size: 8px;
            color: #475569;
        }
    </style>
</head>
<body>
    @php
        $legalName = $companyLegalName ?? 'IMPORT HNOS SAN LUIS S.A.C.';
        $commercialName = $companyName ?? 'Import Hnos San Luis';
        $ruc = $companyRuc ?? '20608628143';
        $address = $companyAddress ?? 'Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú';
        $email = $companyEmail ?? 'ventas@hnssanluis.com';
        $phone = $companyPhone ?? '977 208 514';
        $hours = $companyHours ?? 'Lunes a Viernes: 8:30 am - 6:00 pm | Sábados: 9:00 am - 1:00 pm';
        $advisors = $whatsappAdvisors ?? [];
        $productUrl = route('catalog.show', $product->slug);

        $cleanShortDesc = trim(strip_tags((string) ($product->short_description ?: $product->name)));
        $cleanDetailedDesc = trim(strip_tags((string) ($product->detailed_description ?? '')));
    @endphp

    <!-- Fixed Page Footer -->
    <div class="page-footer">
        <table width="100%">
            <tr>
                <td width="68%" style="text-align: left;">
                    <strong>{{ $legalName }}</strong> • RUC: <strong>{{ $ruc }}</strong> • {{ $address }}
                </td>
                <td width="32%" style="text-align: right;">
                    Ficha Técnica SKU: <strong>{{ $product->sku }}</strong> | {{ now()->format('d/m/Y') }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Corporate Header -->
    <table class="header-table">
        <tr>
            <td width="63%">
                @if(!empty($companyLogoDataUri))
                    <img src="{{ $companyLogoDataUri }}" alt="{{ $legalName }}" class="logo-img">
                @else
                    <p class="company-legal" style="font-size: 16px; color: #1e3a8a;">{{ strtoupper($commercialName) }}</p>
                @endif
                <p class="company-legal">{{ $legalName }} — RUC: {{ $ruc }}</p>
                <p class="company-tagline">Ferretería Industrial, Herrajes, Accesorios en General para Melamine y Construcción</p>
                <p class="company-meta"><strong>Dirección:</strong> {{ $address }}</p>
                <p class="company-meta"><strong>Central / WhatsApp:</strong> {{ $phone }} &nbsp;|&nbsp; <strong>Correo:</strong> {{ $email }}</p>
            </td>
            <td width="37%">
                <div class="doc-badge-box">
                    <div class="doc-badge-type">Ficha Técnica Oficial</div>
                    <div class="doc-badge-sku">SKU: {{ $product->sku }}</div>
                    <div class="doc-badge-meta"><strong>Fecha de emisión:</strong> {{ now()->format('d/m/Y') }}</div>
                    <div class="doc-badge-meta"><strong>Modalidad:</strong> Cotización B2B / Mayor y Menor</div>
                    <div class="status-pill {{ $product->availability_status === 'available' ? 'status-available' : 'status-request' }}">
                        {{ $product->availabilityLabel() }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Dual-Tone Corporate Bar -->
    <table class="divider-bar">
        <tr>
            <td class="divider-navy"></td>
            <td class="divider-red"></td>
        </tr>
    </table>

    <!-- Product Title Banner -->
    <div class="title-banner">
        <div class="title-taxonomy">
            {{ $product->category?->name ?? 'Catálogo Técnico' }}
            @if($product->subcategory)
                &raquo; {{ $product->subcategory->name }}
            @endif
            @if($product->brand)
                &nbsp;|&nbsp; MARCA: {{ strtoupper($product->brand->name) }}
            @endif
        </div>
        <h1 class="product-title">{{ $product->name }}</h1>
    </div>

    <!-- Product Hero Section: Image + Technical Highlights -->
    <table class="hero-table">
        <tr>
            @if(!empty($primaryImageDataUri))
                <td class="hero-image-cell">
                    <div class="image-frame">
                        <img src="{{ $primaryImageDataUri }}" alt="{{ $product->name }}" class="product-main-img">
                        @if(!empty($secondaryImageDataUri))
                            <img src="{{ $secondaryImageDataUri }}" alt="{{ $product->name }} - Vista adicional" class="product-sub-img">
                        @endif
                        <div class="image-caption">Imagen técnica referencial — Código {{ $product->sku }}</div>
                    </div>
                </td>
            @endif
            <td class="{{ !empty($primaryImageDataUri) ? 'hero-details-cell' : '' }}" width="{{ !empty($primaryImageDataUri) ? '65%' : '100%' }}">
                <table class="quick-grid">
                    <tr>
                        <td width="50%">
                            <span class="quick-label">Código SKU</span>
                            <span class="quick-value" style="color: #dc2626;">{{ $product->sku }}</span>
                        </td>
                        <td width="50%">
                            <span class="quick-label">Marca / Fabricante</span>
                            <span class="quick-value">{{ $product->brand?->name ?? 'Import Hnos San Luis' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="quick-label">Línea / Categoría</span>
                            <span class="quick-value">{{ $product->category?->name ?? 'Ferretería y Herrajes' }}</span>
                        </td>
                        <td>
                            <span class="quick-label">Subcategoría</span>
                            <span class="quick-value">{{ $product->subcategory?->name ?? 'Línea Especializada' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="quick-label">Unidad de Venta</span>
                            <span class="quick-value">{{ $product->sale_unit ?: 'Unidad' }}</span>
                        </td>
                        <td>
                            <span class="quick-label">Disponibilidad Comercial</span>
                            <span class="quick-value">{{ $product->availabilityLabel() }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="quick-label">Dimensiones / Medidas</span>
                            <span class="quick-value">{{ $product->dimensions ?: 'Según especificación de modelo' }}</span>
                        </td>
                        <td>
                            <span class="quick-label">Material / Acabado</span>
                            <span class="quick-value">{{ $product->material ?: 'Grado técnico industrial' }}</span>
                        </td>
                    </tr>
                </table>

                <div class="summary-box">
                    <div class="summary-title">Descripción y Aplicación Técnica</div>
                    <div>{{ $cleanShortDesc }}</div>
                    @if($cleanDetailedDesc !== '' && $cleanDetailedDesc !== $cleanShortDesc)
                        <div style="margin-top: 5px; color: #334155;">
                            {{ \Illuminate\Support\Str::limit($cleanDetailedDesc, 520) }}
                        </div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Specifications Table -->
    <div class="section-header">Parámetros Técnicos y Especificaciones de Suministro</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="42%">Parámetro / Atributo Técnico</th>
                <th width="58%">Valor / Especificación</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="key-col"><span class="spec-group-badge">ID</span> Código de Producto (SKU)</td>
                <td><strong>{{ $product->sku }}</strong></td>
            </tr>
            @if($product->brand)
                <tr>
                    <td class="key-col"><span class="spec-group-badge">ID</span> Marca Comercial</td>
                    <td>{{ $product->brand->name }}</td>
                </tr>
            @endif
            @if($product->category)
                <tr>
                    <td class="key-col"><span class="spec-group-badge">Catálogo</span> Clasificación Técnica</td>
                    <td>{{ $product->category->name }}{{ $product->subcategory ? ' / '.$product->subcategory->name : '' }}</td>
                </tr>
            @endif
            <tr>
                <td class="key-col"><span class="spec-group-badge">Despacho</span> Unidad de Venta</td>
                <td>{{ $product->sale_unit ?: 'Unidad' }}</td>
            </tr>
            @if($product->dimensions)
                <tr>
                    <td class="key-col"><span class="spec-group-badge">Físico</span> Dimensiones / Medidas</td>
                    <td>{{ $product->dimensions }}</td>
                </tr>
            @endif
            @if($product->material)
                <tr>
                    <td class="key-col"><span class="spec-group-badge">Material</span> Composición / Acabado</td>
                    <td>{{ $product->material }}</td>
                </tr>
            @endif
            @foreach($product->specifications as $spec)
                <tr>
                    <td class="key-col">
                        @if($spec->spec_group)
                            <span class="spec-group-badge">{{ $spec->spec_group }}</span>
                        @endif
                        {{ $spec->spec_key }}
                    </td>
                    <td>{{ $spec->spec_value }}{{ $spec->unit ? ' '.$spec->unit : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Variants Matrix (if product has variants) -->
    @if($product->variants->isNotEmpty())
        <div class="section-header">Matriz de Variantes / Medidas Disponibles ({{ $product->variants->count() }} presentaciones)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th width="18%">Código SKU</th>
                    <th width="25%">Medida / Variación</th>
                    <th width="25%">Dimensiones / Material</th>
                    <th width="18%">Unidad / Empaque</th>
                    <th width="14%">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($product->variants as $variant)
                    <tr>
                        <td style="font-weight: bold; color: #0f172a;">{{ $variant->sku }}</td>
                        <td><strong style="color: #1e3a8a;">{{ $variant->variant_label ?: $variant->name }}</strong></td>
                        <td>
                            {{ $variant->dimensions ?: '—' }}
                            @if($variant->material)
                                <span style="color: #64748b;">({{ $variant->material }})</span>
                            @endif
                        </td>
                        <td>
                            {{ $variant->sale_unit ?: $product->sale_unit }}
                            @if($variant->box_qty || $variant->carton_qty)
                                <div style="font-size: 8px; color: #475569;">
                                    {{ $variant->box_qty ? 'Caja: '.$variant->box_qty : '' }}{{ $variant->box_qty && $variant->carton_qty ? ' | ' : '' }}{{ $variant->carton_qty ? 'Cajón: '.$variant->carton_qty : '' }}
                                </div>
                            @endif
                        </td>
                        <td>{{ $variant->availabilityLabel() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Corporate Information & Commercial Contact Block -->
    <table class="corporate-card">
        <tr>
            <td width="52%" style="border-right: 1px solid #cbd5e1;">
                <div class="corp-col-title">Información Corporativa de la Empresa</div>
                <div><strong>Razón Social:</strong> {{ $legalName }}</div>
                <div><strong>RUC:</strong> {{ $ruc }}</div>
                <div><strong>Sede Comercial:</strong> {{ $address }}</div>
                <div><strong>Horario de Atención:</strong> {{ $hours }}</div>
                <div><strong>Correo de Cotizaciones:</strong> {{ $email }}</div>
                <div style="margin-top: 3px;"><strong>Enlace en Catálogo Web:</strong> <span style="color: #1e3a8a;">{{ $productUrl }}</span></div>
            </td>
            <td width="48%">
                <div class="corp-col-title">Canales de Atención y Cotizaciones B2B</div>
                <div><strong>Central Comercial / WhatsApp:</strong> {{ $phone }}</div>
                @if(!empty($advisors))
                    <div style="margin-top: 4px;">
                        <strong>Asesoría Comercial Directa (WhatsApp):</strong><br>
                        @foreach($advisors as $adv)
                            @if(!empty($adv['phone']))
                                <span class="advisor-badge">
                                    <strong>{{ !empty($adv['name']) ? $adv['name'].':' : 'Ventas:' }}</strong> {{ $adv['phone'] }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                @endif
                <div class="conditions-note">
                    <strong>Condiciones de Suministro:</strong> {{ $quoteConditions ?? 'Ventas al por mayor y menor. Despachos en Lima Metropolitana y envíos a nivel nacional por agencia de carga previa coordinación comercial.' }}
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
