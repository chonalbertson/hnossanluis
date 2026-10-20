<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Propuesta Comercial {{ $quote->reference_code }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #1e293b; background-color: #f8fafc; margin: 0; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 680px; margin: 0 auto; background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden;">
        <tr>
            <td style="background-color: #0f172a; color: #ffffff; padding: 24px;">
                <h1 style="margin: 0; font-size: 20px;">Propuesta Comercial #{{ $quote->reference_code }}</h1>
                <p style="margin: 4px 0 0; font-size: 13px; color: #cbd5e1;">Import Hns San Luis — Atención Comercial B2B</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px;">
                <p style="margin-top: 0;">Estimado(a) <strong>{{ $quote->contact_name }}</strong>,</p>
                <p>Adjuntamos nuestra propuesta comercial correspondiente a su solicitud <strong>{{ $quote->reference_code }}</strong>.</p>

                @if($quote->commercial_response)
                    <div style="background-color: #f8fafc; border-left: 4px solid #d97706; padding: 14px; margin: 16px 0; font-size: 14px; white-space: pre-line;">{{ $quote->commercial_response }}</div>
                @endif

                <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse; font-size: 13px; margin-top: 16px;">
                    <thead>
                        <tr style="background-color: #f1f5f9; text-align: left; border-bottom: 2px solid #cbd5e1;">
                            <th>SKU</th>
                            <th>Descripción</th>
                            <th style="text-align: right;">Cant.</th>
                            <th style="text-align: right;">P. Unit.</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quote->items as $item)
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="font-family: monospace;">{{ $item->sku_snapshot }}</td>
                                <td>{{ $item->product_name_snapshot }} ({{ $item->sale_unit_snapshot }})</td>
                                <td style="text-align: right;">{{ number_format((float) $item->quantity, 2) }}</td>
                                <td style="text-align: right;">
                                    @if($item->unit_price_offered !== null)
                                        {{ $quote->currencySymbol() }} {{ number_format((float) $item->unit_price_offered, 2) }}
                                    @else
                                        Por confirmar
                                    @endif
                                </td>
                                <td style="text-align: right; font-weight: bold;">
                                    @if($item->subtotal_offered !== null)
                                        {{ $quote->currencySymbol() }} {{ number_format((float) $item->subtotal_offered, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    @if($quote->total_offered !== null)
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right; padding-top: 12px;"><strong>Subtotal:</strong></td>
                                <td style="text-align: right; padding-top: 12px;">{{ $quote->currencySymbol() }} {{ number_format((float) $quote->subtotal_offered, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" style="text-align: right;"><strong>IGV (18%):</strong></td>
                                <td style="text-align: right;">{{ $quote->currencySymbol() }} {{ number_format((float) $quote->tax_offered, 2) }}</td>
                            </tr>
                            <tr style="font-size: 15px;">
                                <td colspan="4" style="text-align: right;"><strong>Total Ofertado:</strong></td>
                                <td style="text-align: right; font-weight: bold; color: #0f172a;">{{ $quote->currencySymbol() }} {{ number_format((float) $quote->total_offered, 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>

                @if($quote->commercial_conditions)
                    <p style="margin-top: 16px; font-size: 13px;"><strong>Condiciones Comerciales:</strong><br>{{ $quote->commercial_conditions }}</p>
                @endif

                @if($quote->valid_until)
                    <p style="font-size: 13px; color: #475569;"><strong>Vigencia de la oferta:</strong> Hasta el {{ $quote->valid_until->format('d/m/Y') }} ({{ $quote->validity_days }} días).</p>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
