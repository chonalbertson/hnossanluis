<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Mensaje Comercial Web</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #0f172a; margin: 0; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
        <tr>
            <td style="background-color: #0b162c; padding: 24px 32px; color: #ffffff;">
                <p style="margin: 0; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: #f87171; font-weight: 700;">
                    IMPORT HNOS SAN LUIS S.A.C. • RUC 20608628143
                </p>
                <h1 style="margin: 8px 0 0; font-size: 20px; font-weight: 800;">
                    Nuevo Mensaje Comercial desde la Web
                </h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 32px;">
                <table width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; background-color: #f8fafc; margin-bottom: 24px;">
                    <tr>
                        <td style="padding: 6px 0; font-size: 13px; color: #64748b; width: 140px; font-weight: 600;">Remitente:</td>
                        <td style="padding: 6px 0; font-size: 13px; color: #0f172a; font-weight: 700;">{{ $contactMessage->name }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; font-size: 13px; color: #64748b; font-weight: 600;">Correo:</td>
                        <td style="padding: 6px 0; font-size: 13px; color: #1e3a8a; font-weight: 600;">{{ $contactMessage->email }}</td>
                    </tr>
                    @if($contactMessage->phone)
                    <tr>
                        <td style="padding: 6px 0; font-size: 13px; color: #64748b; font-weight: 600;">Teléfono:</td>
                        <td style="padding: 6px 0; font-size: 13px; color: #0f172a;">{{ $contactMessage->phone }}</td>
                    </tr>
                    @endif
                    @if($contactMessage->company)
                    <tr>
                        <td style="padding: 6px 0; font-size: 13px; color: #64748b; font-weight: 600;">Empresa:</td>
                        <td style="padding: 6px 0; font-size: 13px; color: #0f172a;">{{ $contactMessage->company }}</td>
                    </tr>
                    @endif
                    @if($contactMessage->ruc)
                    <tr>
                        <td style="padding: 6px 0; font-size: 13px; color: #64748b; font-weight: 600;">RUC:</td>
                        <td style="padding: 6px 0; font-size: 13px; color: #0f172a; font-family: monospace;">{{ $contactMessage->ruc }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding: 6px 0; font-size: 13px; color: #64748b; font-weight: 600;">Asunto:</td>
                        <td style="padding: 6px 0; font-size: 13px; color: #0f172a; font-weight: 700;">{{ $contactMessage->subject }}</td>
                    </tr>
                </table>

                <p style="margin: 0 0 8px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #475569;">
                    Detalle del Mensaje:
                </p>
                <div style="padding: 16px; background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; line-height: 1.6; color: #1e293b; white-space: pre-line;">{{ $contactMessage->message }}</div>
            </td>
        </tr>
        <tr>
            <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 32px; font-size: 12px; color: #64748b; text-align: center;">
                IMPORT HNOS SAN LUIS S.A.C. • Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú
            </td>
        </tr>
    </table>
</body>
</html>
