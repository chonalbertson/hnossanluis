<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia de Hoja de Reclamación Virtual {{ $sheetCode }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #0f172a; margin: 0; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
        <tr>
            <td style="background-color: #0b162c; padding: 24px 32px; color: #ffffff;">
                <p style="margin: 0; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: #f87171; font-weight: 700;">
                    LIBRO DE RECLAMACIONES VIRTUAL • LEY N° 29571
                </p>
                <h1 style="margin: 8px 0 0; font-size: 20px; font-weight: 800;">
                    Hoja de Reclamación N° {{ $sheetCode }}
                </h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 32px;">
                <p style="margin: 0 0 16px; font-size: 14px; line-height: 1.6; color: #334155;">
                    Estimado(a) <strong>{{ $record->name }}</strong>, hemos registrado exitosamente tu <strong>{{ strtoupper($claimType) }}</strong> en el Libro de Reclamaciones Virtual de <strong>IMPORT HNOS SAN LUIS S.A.C. (RUC 20608628143)</strong>.
                </p>

                <div style="padding: 16px; background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; font-family: monospace; font-size: 12px; line-height: 1.6; color: #0f172a; white-space: pre-line; margin-bottom: 20px;">{{ $record->message }}</div>

                <p style="margin: 0; font-size: 12px; line-height: 1.6; color: #64748b;">
                    De conformidad con el Código de Protección y Defensa del Consumidor (Ley N° 29571), el proveedor dará respuesta a la presente Hoja de Reclamación en un plazo no mayor a quince (15) días hábiles improrrogables.
                </p>
            </td>
        </tr>
        <tr>
            <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 32px; font-size: 12px; color: #64748b; text-align: center;">
                IMPORT HNOS SAN LUIS S.A.C. • RUC 20608628143 • Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú
            </td>
        </tr>
    </table>
</body>
</html>
