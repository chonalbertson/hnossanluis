<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de Solicitud de Cotización {{ $quote->reference_code }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #1e293b; background-color: #f8fafc; margin: 0; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
        <tr>
            <td style="background-color: #0f172a; color: #ffffff; padding: 24px;">
                <h1 style="margin: 0; font-size: 20px; font-weight: bold;">Import Hns San Luis</h1>
                <p style="margin: 4px 0 0; font-size: 13px; color: #cbd5e1;">Solicitud de Cotización Recibida — {{ $quote->reference_code }}</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px;">
                <p style="margin-top: 0;">Estimado(a) <strong>{{ $quote->contact_name }}</strong>,</p>
                <p>Hemos recibido su solicitud de cotización con el código de referencia <strong>{{ $quote->reference_code }}</strong>. Nuestro equipo comercial revisará los requerimientos técnicos y disponibilidad para enviarle nuestra propuesta a la brevedad.</p>

                <div style="background-color: #f1f5f9; padding: 16px; border-radius: 6px; margin: 20px 0; font-size: 14px;">
                    <p style="margin: 0 0 6px;"><strong>Referencia:</strong> {{ $quote->reference_code }}</p>
                    @if($quote->company_name)
                        <p style="margin: 0 0 6px;"><strong>Empresa:</strong> {{ $quote->company_name }} @if($quote->ruc) (RUC: {{ $quote->ruc }}) @endif</p>
                    @endif
                    <p style="margin: 0;"><strong>Teléfono de contacto:</strong> {{ $quote->contact_phone }}</p>
                </div>

                <h3 style="font-size: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">Productos Solicitados ({{ $quote->items->count() }})</h3>
                <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="background-color: #f8fafc; text-align: left; border-bottom: 2px solid #cbd5e1;">
                            <th>SKU</th>
                            <th>Producto</th>
                            <th>Unidad</th>
                            <th style="text-align: right;">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quote->items as $item)
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="font-family: monospace;">{{ $item->sku_snapshot }}</td>
                                <td>
                                    <strong>{{ $item->product_name_snapshot }}</strong>
                                    @if($item->item_notes)
                                        <div style="font-size: 12px; color: #64748b;">Nota: {{ $item->item_notes }}</div>
                                    @endif
                                </td>
                                <td>{{ $item->sale_unit_snapshot }}</td>
                                <td style="text-align: right; font-weight: bold;">{{ number_format((float) $item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($quote->customer_observations)
                    <p style="margin-top: 16px; font-size: 13px;"><strong>Observaciones registradas:</strong><br>{{ $quote->customer_observations }}</p>
                @endif

                <p style="margin-top: 24px; font-size: 13px; color: #475569;">Si desea agilizar su atención, puede comunicarse con nuestro canal comercial indicando su número de referencia <strong>{{ $quote->reference_code }}</strong>.</p>
            </td>
        </tr>
        <tr>
            <td style="background-color: #f8fafc; padding: 16px 24px; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0;">
                Import Hns San Luis — Soluciones en Ferretería Industrial, Accesorios para Muebles y Construcción.
            </td>
        </tr>
    </table>
</body>
</html>
