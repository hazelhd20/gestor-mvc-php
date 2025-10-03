# Configuración del correo SMTP en cPanel

Sigue estos pasos para habilitar el envío automático de correos de verificación y recuperación de contraseña desde la aplicación.

## 1. Obtener las credenciales SMTP en cPanel
1. Ingresa al panel de cPanel con tu usuario y contraseña del hosting.
2. Abre la sección **Cuentas de correo electrónico** y localiza la cuenta que usará la plataforma (por ejemplo, `no-reply@tudominio.com`).
3. Haz clic en **Connect Devices / Configurar cliente de correo**.
4. Copia los siguientes datos del apartado *Mail Client Manual Settings*:
   - Servidor de correo saliente (SMTP) y el puerto recomendado (generalmente 465 para SSL o 587 para TLS).
   - Usuario SMTP (normalmente la dirección de correo completa).
   - Contraseña SMTP (la misma contraseña de la cuenta de correo o la generada para aplicaciones específicas).
   - Tipo de cifrado (SSL o TLS).

## 2. Definir variables de entorno en el hosting
La aplicación carga automáticamente un archivo `.env` ubicado en la raíz del proyecto al iniciar. Puedes editarlo desde el Administrador de archivos de cPanel o crear las mismas variables en la sección **Configuración de PHP**. En ambos casos establece los valores obtenidos en el paso anterior:

```
APP_URL=https://tudominio.com
MAIL_HOST=mail.tudominio.com
MAIL_PORT=587
MAIL_USERNAME=no-reply@tudominio.com
MAIL_PASSWORD=la-contraseña-generada
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@tudominio.com
MAIL_FROM_NAME="Gestor de Titulación"
```

> **Importante:** `APP_URL` debe apuntar al dominio público de la aplicación para que los enlaces incluidos en los correos sean absolutos. Si el servidor exige SSL, usa el puerto `465` y establece `MAIL_ENCRYPTION=ssl`.

## 3. Verificar puertos de salida
Asegúrate de que el hosting permita conexiones salientes por el puerto configurado. En caso de bloquear el 587, utiliza el 465 (SSL). También puedes validar la conectividad desde cPanel con la herramienta **Terminal** ejecutando:

```
openssl s_client -connect mail.tudominio.com:587 -starttls smtp
```

Si la conexión responde, el puerto está habilitado.

## 4. Probar el envío
1. Vacía la caché de configuración o reinicia la aplicación si es necesario.
2. Registra una cuenta nueva desde el formulario de registro para recibir el correo de verificación.
3. Utiliza la opción “¿Olvidaste tu contraseña?” para confirmar que también llegan los correos de restablecimiento.

Si los mensajes llegan a spam, añade el remitente a la libreta de direcciones o configura un registro SPF/DMARC desde el panel DNS del dominio.

## 5. Solución de problemas comunes
- **Error de autenticación SMTP:** revisa el usuario y la contraseña. En muchos hosting debe utilizarse la dirección de correo completa como usuario.
- **Tiempo de espera agotado:** confirma que la IP del servidor no esté bloqueada por el firewall del hosting.
- **Certificado inválido:** si el certificado del servidor no coincide con el dominio del host SMTP, usa el nombre exacto indicado en cPanel (por ejemplo `srvXXX.tudominio.com`).

Una vez configurados estos parámetros, la aplicación enviará automáticamente los correos de verificación y recuperación utilizando la librería PHPMailer incluida en el proyecto.
