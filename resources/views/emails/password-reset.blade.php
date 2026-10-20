<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperación de Contraseña - Import Hns San Luis</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #1e293b; background-color: #f8fafc; margin: 0; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
        <tr>
            <td style="background-color: #0f172a; color: #ffffff; padding: 24px; border-bottom: 4px solid #dc2626;">
                <h1 style="margin: 0; font-size: 20px; font-weight: bold;">Import Hns San Luis</h1>
                <p style="margin: 4px 0 0; font-size: 13px; color: #cbd5e1;">Solicitud de Restablecimiento de Contraseña</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px;">
                <p style="margin-top: 0;">Hola <strong>{{ $user->name }}</strong>,</p>
                <p>Recibimos una solicitud para restablecer la contraseña asociada a tu cuenta (<strong>{{ $user->email }}</strong>) en el portal de <strong>Import Hns San Luis</strong>.</p>

                <div style="text-align: center; margin: 28px 0;">
                    <a href="{{ $resetUrl }}"
                       style="display: inline-block; background-color: #dc2626; color: #ffffff; font-weight: bold; font-size: 14px; text-decoration: none; padding: 12px 28px; border-radius: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
                        Restablecer Contraseña
                    </a>
                </div>

                <p style="font-size: 13px; color: #475569;">Este enlace expirará en <strong>{{ $expiresInMinutes }} minutos</strong>. Si no realizaste esta solicitud, puedes ignorar este mensaje con total seguridad; tu contraseña actual no será modificada.</p>

                <div style="background-color: #f1f5f9; padding: 12px 16px; border-radius: 6px; margin-top: 20px; font-size: 12px; color: #475569; word-break: break-all;">
                    Si tienes problemas con el botón, copia y pega el siguiente enlace en tu navegador:<br>
                    <a href="{{ $resetUrl }}" style="color: #1d4ed8;">{{ $resetUrl }}</a>
                </div>
            </td>
        </tr>
        <tr>
            <td style="background-color: #f8fafc; padding: 16px 24px; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0;">
                IMPORT HNOS SAN LUIS S.A.C. (RUC 20608628143) — Av. Guillermo Dansey 429, Lima Centro, Lima, Perú.
            </td>
        </tr>
    </table>
</body>
</html>
