<?php /** @var string $fullName */ ?>
<?php /** @var string $verificationUrl */ ?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <title>Verifica tu correo</title>
  </head>
  <body style="font-family: Arial, sans-serif; background-color: #f8fafc; color: #0f172a; margin: 0; padding: 32px;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden;">
      <tr>
        <td style="padding: 32px;">
          <h1 style="margin-top: 0; font-size: 22px;">Hola, <?= e($fullName); ?> 👋</h1>
          <p style="font-size: 15px; line-height: 1.6;">
            Recibimos una solicitud para crear una cuenta en la plataforma <strong>Gestor de Titulación</strong> con esta dirección de correo.
          </p>
          <p style="font-size: 15px; line-height: 1.6;">
            Para completar el registro y activar tu acceso, haz clic en el siguiente botón:
          </p>
          <p style="text-align: center; margin: 32px 0;">
            <a href="<?= e($verificationUrl); ?>" style="background-color: #4f46e5; color: #ffffff; padding: 12px 24px; border-radius: 9999px; text-decoration: none; font-weight: bold; display: inline-block;">
              Verificar correo
            </a>
          </p>
          <p style="font-size: 14px; color: #475569; line-height: 1.6;">
            Si el botón no funciona, copia y pega este enlace en tu navegador:<br />
            <span style="word-break: break-all;">
              <a href="<?= e($verificationUrl); ?>" style="color: #4f46e5;">
                <?= e($verificationUrl); ?>
              </a>
            </span>
          </p>
          <p style="font-size: 13px; color: #64748b; line-height: 1.6;">
            Si tú no solicitaste esta cuenta, puedes ignorar este correo. El enlace caduca en 48 horas por seguridad.
          </p>
          <p style="font-size: 14px; margin-top: 32px;">Gracias,<br />Equipo Gestor de Titulación</p>
        </td>
      </tr>
    </table>
  </body>
</html>
