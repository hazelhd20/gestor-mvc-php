<?php /** @var string $fullName */ ?>
<?php /** @var string $resetUrl */ ?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <title>Restablece tu contraseña</title>
  </head>
  <body style="font-family: Arial, sans-serif; background-color: #f8fafc; color: #0f172a; margin: 0; padding: 32px;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden;">
      <tr>
        <td style="padding: 32px;">
          <h1 style="margin-top: 0; font-size: 22px;">Hola, <?= e($fullName); ?></h1>
          <p style="font-size: 15px; line-height: 1.6;">
            Recibimos una solicitud para restablecer la contraseña de tu cuenta en <strong>Gestor de Titulación</strong>.
          </p>
          <p style="font-size: 15px; line-height: 1.6;">
            Haz clic en el siguiente botón para crear una contraseña nueva. El enlace es válido por 1 hora.
          </p>
          <p style="text-align: center; margin: 32px 0;">
            <a href="<?= e($resetUrl); ?>" style="background-color: #0ea5e9; color: #ffffff; padding: 12px 24px; border-radius: 9999px; text-decoration: none; font-weight: bold; display: inline-block;">
              Cambiar contraseña
            </a>
          </p>
          <p style="font-size: 14px; color: #475569; line-height: 1.6;">
            Si el botón no funciona, copia y pega este enlace en tu navegador:<br />
            <span style="word-break: break-all;">
              <a href="<?= e($resetUrl); ?>" style="color: #0ea5e9;">
                <?= e($resetUrl); ?>
              </a>
            </span>
          </p>
          <p style="font-size: 13px; color: #64748b; line-height: 1.6;">
            Si no solicitaste el cambio de contraseña, puedes ignorar este mensaje. Tu contraseña seguirá siendo la misma.
          </p>
          <p style="font-size: 14px; margin-top: 32px;">Atentamente,<br />Equipo Gestor de Titulación</p>
        </td>
      </tr>
    </table>
  </body>
</html>
