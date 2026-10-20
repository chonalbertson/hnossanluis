<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Cotización {{ $quote->reference_code }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #1e293b; background-color: #f8fafc; margin: 0; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 680px; margin: 0 auto; background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden;">
        <tr>
            <td style="background-color: #1e293b; color: #ffffff; padding: 20px 24px;">
                <h2 style="margin: 0; font-size: 18px;">Nueva Solicitud de Cotización: {{ $quote->reference_code }}</h2>
                <p style="margin: 4px 0 0; font-size: 12px; color: #94a3b8;">Origen: {{ strtoupper($quote->origin) }} | Tipo: {{ strtoupper($quote->request_type) }}</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px;">
                <h3 style="margin-top: 0; font-size: 15px;">Datos del Solicitante</h3>
                <table width="100%" cellpadding="6" cellspacing="0" style="font-size: 13px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                    <tr>
                        <td width="35%"><strong>Contacto:</strong></td>
                        <td>{{ $quote->contact_name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Correo:</strong></td>
                        <td>{{ $quote->contact_email }}</td>
                    </tr>
                    <tr>
                        <td><strong>Teléfono / WhatsApp:</strong></td>
                        <td>{{ $quote->contact_phone }}</td>
                    </tr>
                    <tr>
                        <td><strong>Empresa / RUC:</strong></td>
                        <td>{{ $quote->company_name ?: 'No especificada' }} {{ $quote->ruc ? '(RUC: '.$quote->ruc.')' : '' }}</td>
                    </tr>
                </table>

                <h3 style="font-size: 15px; margin-top: 20px;">Ítems Solicitados ({{ $quote->items->count() }})</h3>
                <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="background-color: #f1f5f9; text-align: left; border-bottom: 2px solid #cbd5e1;">
                            <th>SKU</th>
                            <th>Producto</th>
                            <th>Unidad</th>
                            <th style="text-align: right;">Cant.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quote->items as $item)
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="font-family: monospace;">{{ $item->sku_snapshot }}</td>
                                <td>
                                    {{ $item->product_name_snapshot }}
                                    @if($item->item_notes)
                                        <div style="font-size: 12px; color: #64748b;">Nota ítem: {{ $item->item_notes }}</div>
                                    @endif
                                </td>
                                <td>{{ $item->sale_unit_snapshot }}</td>
                                <td style="text-align: right; font-weight: bold;">{{ number_format((float) $item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($quote->customer_observations)
                    <div style="margin-top: 16px; padding: 12px; background-color: #fffbeb; border-left: 4px solid #f59e0b; font-size: 13px;">
                        <strong>Observaciones del cliente:</strong><br>
                        {{ $quote->customer_observations }}
                    </div>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
