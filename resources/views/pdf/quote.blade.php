<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización {{ $quote->reference_code }} - Import Hns San Luis</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .brand-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
        }
        .brand-subtitle {
            font-size: 10px;
            color: #475569;
            margin: 3px 0 0;
        }
        .quote-box {
            text-align: right;
        }
        .quote-code {
            font-size: 15px;
            font-weight: bold;
            color: #b45309;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            background-color: #f1f5f9;
            padding: 6px 10px;
            border-left: 3px solid #0f172a;
            margin: 14px 0 8px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .info-table td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .items-table th {
            background-color: #0f172a;
            color: #ffffff;
            text-align: left;
            padding: 7px 8px;
            font-size: 10px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 7px 8px;
            border-bottom: 1px solid #cbd5e1;
            vertical-align: top;
        }
        .text-right {
            text-align: right;
        }
        .totals-table {
            width: 260px;
            margin-left: auto;
            margin-top: 12px;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .totals-table .grand-total td {
            font-size: 13px;
            font-weight: bold;
            background-color: #f8fafc;
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
        }
        .conditions-box {
            margin-top: 18px;
            padding: 10px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            font-size: 10px;
        }
        .footer {
            margin-top: 28px;
            padding-top: 10px;
            border-top: 1px solid #cbd5e1;
            font-size: 9px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td width="60%">
                <p class="brand-title">{{ $companyLegalName ?? 'IMPORT HNOS SAN LUIS S.A.C.' }}</p>
                <p class="brand-subtitle"><strong>RUC: {{ $companyRuc ?? '20608628143' }}</strong> | {{ $companyAddress ?? 'Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú' }}</p>
                <p class="brand-subtitle">Ferretería Industrial, Herrajes y Accesorios para Muebles y Construcción</p>
                <p class="brand-subtitle">Email: {{ $companyEmail ?? 'ventas@hnssanluis.com' }} | WhatsApp: {{ $companyPhone ?? '977 208 514' }}</p>
            </td>
            <td width="40%" class="quote-box">
                <div class="quote-code">{{ $quote->reference_code }}</div>
                <div><strong>Fecha de emisión:</strong> {{ $quote->created_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y') }}</div>
                <div><strong>Estado:</strong> {{ $quote->statusLabel() }}</div>
                @if($quote->valid_until)
                    <div><strong>Válida hasta:</strong> {{ $quote->valid_until->format('d/m/Y') }}</div>
                @else
                    <div><strong>Vigencia referencial:</strong> {{ $quote->validity_days }} días</div>
                @endif
            </td>
        </tr>
    </table>

    <div class="section-title">DATOS DEL SOLICITANTE</div>
    <table class="info-table">
        <tr>
            <td width="18%"><strong>Contacto:</strong></td>
            <td width="32%">{{ $quote->contact_name }}</td>
            <td width="18%"><strong>Empresa:</strong></td>
            <td width="32%">{{ $quote->company_name ?: 'Particular / Por definir' }}</td>
        </tr>
        <tr>
            <td><strong>Correo:</strong></td>
            <td>{{ $quote->contact_email }}</td>
            <td><strong>RUC:</strong></td>
            <td>{{ $quote->ruc ?: '-' }}</td>
        </tr>
        <tr>
            <td><strong>Teléfono:</strong></td>
            <td>{{ $quote->contact_phone }}</td>
            <td><strong>Asesor asignado:</strong></td>
            <td>{{ $quote->assignedSeller?->name ?? 'Equipo Comercial B2B' }}</td>
        </tr>
    </table>

    <div class="section-title">DETALLE DE PRODUCTOS COTIZADOS</div>
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="16%">SKU</th>
                <th width="39%">Descripción Técnica</th>
                <th width="10%">Unidad</th>
                <th width="10%" class="text-right">Cant.</th>
                <th width="10%" class="text-right">P. Unit.</th>
                <th width="10%" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td><strong>{{ $item->sku_snapshot }}</strong></td>
                    <td>
                        {{ $item->product_name_snapshot }}
                        @if($item->item_notes)
                            <div style="font-size: 9px; color: #475569; margin-top: 2px;">Nota: {{ $item->item_notes }}</div>
                        @endif
                    </td>
                    <td>{{ $item->sale_unit_snapshot }}</td>
                    <td class="text-right">{{ number_format((float) $item->quantity, 2) }}</td>
                    <td class="text-right">
                        @if($item->unit_price_offered !== null)
                            {{ $quote->currencySymbol() }} {{ number_format((float) $item->unit_price_offered, 2) }}
                        @else
                            A cotizar
                        @endif
                    </td>
                    <td class="text-right">
                        @if($item->subtotal_offered !== null)
                            {{ $quote->currencySymbol() }} {{ number_format((float) $item->subtotal_offered, 2) }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($quote->total_offered !== null)
        <table class="totals-table">
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td class="text-right">{{ $quote->currencySymbol() }} {{ number_format((float) $quote->subtotal_offered, 2) }}</td>
            </tr>
            <tr>
                <td><strong>IGV (18%):</strong></td>
                <td class="text-right">{{ $quote->currencySymbol() }} {{ number_format((float) $quote->tax_offered, 2) }}</td>
            </tr>
            <tr class="grand-total">
                <td>TOTAL ({{ $quote->currency }}):</td>
                <td class="text-right">{{ $quote->currencySymbol() }} {{ number_format((float) $quote->total_offered, 2) }}</td>
            </tr>
        </table>
    @endif

    @if($quote->customer_observations)
        <div class="conditions-box">
            <strong>Observaciones del Cliente:</strong><br>
            {{ $quote->customer_observations }}
        </div>
    @endif

    @if($quote->commercial_response)
        <div class="conditions-box">
            <strong>Respuesta Comercial:</strong><br>
            {!! nl2br(e($quote->commercial_response)) !!}
        </div>
    @endif

    <div class="conditions-box">
        <strong>Condiciones Comerciales y de Cotización:</strong><br>
        {{ $quote->commercial_conditions ?: 'Precios sujetos a variación y disponibilidad de stock al momento de la confirmación de compra. Tiempo de vigencia referencial: '.$quote->validity_days.' días calendario.' }}
    </div>

    <div class="footer">
        Documento generado por el Sistema de Gestión Comercial de {{ $companyLegalName ?? 'IMPORT HNOS SAN LUIS S.A.C.' }} (RUC: {{ $companyRuc ?? '20608628143' }}) — {{ $quote->reference_code }}
    </div>
</body>
</html>
