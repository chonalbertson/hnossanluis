<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prueba de Configuración SMTP</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #1e293b; padding: 24px;">
    <div style="max-width: 560px; margin: 0 auto; border: 1px solid #cbd5e1; border-radius: 8px; padding: 24px;">
        <h2 style="margin-top: 0; color: #0f172a;">Prueba de Correo Corporativo Exitosa</h2>
        <p>Este es un correo de verificación enviado desde el panel administrativo de <strong>Import Hns San Luis</strong>.</p>
        <ul style="font-size: 14px; color: #475569;">
            <li><strong>Destinatario:</strong> {{ $recipientEmail }}</li>
            <li><strong>Ejecutado por:</strong> {{ $triggeredByName ?? 'Administrador del Sistema' }}</li>
            <li><strong>Fecha y hora:</strong> {{ $testedAt }}</li>
        </ul>
    </div>
</body>
</html>
