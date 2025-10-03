<?php

namespace App\Controllers;

use App\Core\Config;
use App\Core\Controller;
use App\Helpers\Session;
use App\Models\PasswordReset;
use App\Models\User;
use App\Services\EmailTemplateRenderer;
use App\Services\Mailer;
use RuntimeException;

class AuthController extends Controller
{
    private User $users;
    private PasswordReset $passwordResets;
    private EmailTemplateRenderer $emailTemplates;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->users = new User();
        $this->passwordResets = new PasswordReset();
        $this->emailTemplates = new EmailTemplateRenderer();
    }

    public function show(): void
    {
        $user = Session::user();

        if ($user) {
            $this->redirectTo('/dashboard');
        }

        $errors = Session::flash('errors') ?? [];
        $success = Session::flash('success');
        $old = Session::flash('old') ?? [];
        $tab = Session::flash('tab') ?? 'login';

        $this->render('auth/index', [
            'errors' => $errors,
            'success' => $success,
            'old' => $old,
            'activeTab' => $tab,
            'user' => null,
        ]);
    }

    public function login(): void
    {
        Session::start();

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $errors = [];
        $old = ['login_email' => $email];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['login_email'] = 'Ingresa un correo valido.';
        }

        if ($password === '') {
            $errors['login_password'] = 'La contrasena es obligatoria.';
        }

        if ($errors) {
            Session::flash('errors', $errors);
            Session::flash('old', $old);
            Session::flash('tab', 'login');
            $this->redirectTo('/');
        }

        $user = $this->users->verifyCredentials($email, $password);
        if (!$user) {
            Session::flash('errors', ['login_general' => 'Credenciales incorrectas.']);
            Session::flash('old', $old);
            Session::flash('tab', 'login');
            $this->redirectTo('/');
        }

        if (empty($user['email_verified_at'])) {
            try {
                $token = $this->users->generateEmailVerificationToken((int) $user['id']);
                $this->sendVerificationEmail($user, $token);
                $message = 'Debes confirmar tu correo electrónico antes de ingresar. Enviamos un nuevo enlace de verificación.';
            } catch (RuntimeException $exception) {
                $message = 'Debes confirmar tu correo electrónico antes de ingresar y no fue posible reenviar el enlace. Contacta al administrador.';
            }

            Session::flash('errors', ['login_general' => $message]);
            Session::flash('old', $old);
            Session::flash('tab', 'login');
            $this->redirectTo('/');
        }

        Session::regenerate();
        Session::put('user', [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'avatar_path' => $user['avatar_path'] ?? null,
        ]);

        Session::flash('success', 'Inicio de sesion exitoso.');
        $this->redirectTo('/dashboard');
    }

    public function showPasswordChange(): void
    {
        Session::start();

        if (Session::user()) {
            $this->redirectTo('/dashboard');
        }

        $status = Session::flash('password_change_status');
        $errors = Session::flash('password_change_errors') ?? [];
        $old = Session::flash('password_change_old') ?? [];
        $tokenError = Session::flash('password_change_token_error');

        $token = trim($_GET['token'] ?? '');
        $resetData = null;

        if ($token !== '' && empty($errors['token'])) {
            $record = $this->passwordResets->findValidByToken($token);
            if ($record) {
                $resetData = [
                    'token' => $token,
                    'email' => $record['email'],
                    'full_name' => $record['full_name'],
                ];
            } elseif ($tokenError === null) {
                $tokenError = 'El enlace para restablecer la contraseña no es válido o ya expiró.';
            }
        }

        $this->render('auth/password_change', [
            'status' => $status,
            'errors' => $errors,
            'old' => $old,
            'token' => $token,
            'resetData' => $resetData,
            'tokenError' => $tokenError,
        ]);
    }

    public function requestPasswordChange(): void
    {
        Session::start();

        $token = trim($_POST['token'] ?? '');

        if ($token === '') {
            $email = strtolower(trim($_POST['email'] ?? ''));
            $errors = [];

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Ingresa un correo válido.';
            }

            if ($errors) {
                Session::flash('password_change_errors', $errors);
                Session::flash('password_change_old', ['email' => $email]);
                $this->redirectTo('/password/change');
            }

            $user = $this->users->findByEmail($email);
            if (!$user) {
                Session::flash('password_change_errors', ['email' => 'No encontramos una cuenta con ese correo.']);
                Session::flash('password_change_old', ['email' => $email]);
                $this->redirectTo('/password/change');
            }

            if (empty($user['email_verified_at'])) {
                Session::flash('password_change_errors', ['email' => 'Debes verificar tu correo antes de restablecer tu contraseña.']);
                Session::flash('password_change_old', ['email' => $email]);
                $this->redirectTo('/password/change');
            }

            try {
                $resetToken = $this->passwordResets->createToken((int) $user['id']);
                $this->sendPasswordResetEmail($user, $resetToken);
            } catch (RuntimeException $exception) {
                Session::flash('password_change_errors', ['email' => 'No fue posible enviar el correo de recuperación. Intenta más tarde.']);
                Session::flash('password_change_old', ['email' => $email]);
                $this->redirectTo('/password/change');
            }

            Session::flash('password_change_status', 'Te enviamos un correo con un enlace seguro para restablecer tu contraseña. Revisa tu bandeja de entrada.');
            Session::flash('password_change_old', ['email' => $email]);
            $this->redirectTo('/password/change');
        }

        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';
        $errors = [];

        if (strlen($password) < 8) {
            $errors['password'] = 'La contraseña debe tener al menos 8 caracteres.';
        }

        if ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = 'Las contraseñas no coinciden.';
        }

        if ($errors) {
            Session::flash('password_change_errors', $errors);
            $this->redirectTo('/password/change?token=' . urlencode($token));
        }

        $record = $this->passwordResets->findValidByToken($token);
        if (!$record) {
            Session::flash('password_change_errors', ['token' => 'El enlace para restablecer la contraseña no es válido o ya expiró.']);
            Session::flash('password_change_token_error', 'El enlace para restablecer la contraseña no es válido o ya expiró.');
            $this->redirectTo('/password/change');
        }

        $user = $this->users->findById((int) $record['user_id']);
        if (!$user) {
            $this->passwordResets->invalidateByToken($token);
            Session::flash('password_change_errors', ['token' => 'No fue posible validar tu solicitud de restablecimiento. Intenta nuevamente.']);
            $this->redirectTo('/password/change');
        }

        if (password_verify($password, $user['password'])) {
            Session::flash('password_change_errors', ['password' => 'La nueva contraseña debe ser diferente a la que usas actualmente.']);
            $this->redirectTo('/password/change?token=' . urlencode($token));
        }

        $this->users->updatePassword((int) $user['id'], $password);
        $this->passwordResets->deleteByUser((int) $user['id']);

        Session::flash('success', 'Tu contraseña se actualizó correctamente. Ahora puedes iniciar sesión.');
        Session::flash('tab', 'login');
        Session::flash('old', ['login_email' => $user['email']]);
        $this->redirectTo('/');
    }

    public function register(): void
    {
        Session::start();

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'estudiante';
        $matricula = trim($_POST['matricula'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['password_confirmation'] ?? '';

        $allowedRoles = ['estudiante', 'director'];
        if (!in_array($role, $allowedRoles, true)) {
            $role = 'estudiante';
        }

        $errors = [];
        $old = [
            'full_name' => $fullName,
            'email' => $email,
            'role' => $role,
            'matricula' => $matricula,
            'department' => $department,
        ];

        if ($fullName === '') {
            $errors['full_name'] = 'El nombre completo es obligatorio.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Ingresa un correo valido.';
        }

        if (strlen($password) < 8) {
            $errors['password'] = 'La contrasena debe tener al menos 8 caracteres.';
        }

        if ($password !== $confirmPassword) {
            $errors['password_confirmation'] = 'Las contrasenas no coinciden.';
        }

        if ($role === 'estudiante' && $matricula === '') {
            $errors['matricula'] = 'La matricula es obligatoria para estudiantes.';
        }

        if ($role === 'director' && $department === '') {
            $errors['department'] = 'El departamento es obligatorio para directores.';
        }

        if ($errors) {
            Session::flash('errors', $errors);
            Session::flash('old', $old);
            Session::flash('tab', 'register');
            $this->redirectTo('/');
        }

        try {
            $user = $this->users->create([
                'full_name' => $fullName,
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'matricula' => $role === 'estudiante' ? $matricula : null,
                'department' => $role === 'director' ? $department : null,
            ]);
        } catch (RuntimeException $exception) {
            Session::flash('errors', ['register_general' => $exception->getMessage()]);
            Session::flash('old', $old);
            Session::flash('tab', 'register');
            $this->redirectTo('/');
        }

        $statusMessage = 'Cuenta creada correctamente. Revisa tu correo para confirmar la dirección y activar tu cuenta.';

        try {
            $token = $this->users->generateEmailVerificationToken((int) $user['id']);
            $this->sendVerificationEmail($user, $token);
        } catch (RuntimeException $exception) {
            $statusMessage = 'Cuenta creada correctamente, pero no fue posible enviar el correo de verificación. Contacta al administrador para completar el registro.';
        }

        Session::flash('success', $statusMessage);
        Session::flash('old', ['login_email' => $email]);
        Session::flash('tab', 'login');
        $this->redirectTo('/');
    }

    public function logout(): void
    {
        Session::start();
        Session::logout();
        Session::flash('success', 'Cerraste sesion correctamente.');
        Session::flash('tab', 'login');
        $this->redirectTo('/');
    }

    public function verifyEmail(): void
    {
        Session::start();

        $token = trim($_GET['token'] ?? '');

        if ($token === '') {
            Session::flash('errors', ['login_general' => 'El enlace de verificación no es válido.']);
            Session::flash('tab', 'login');
            $this->redirectTo('/');
        }

        $user = $this->users->findByVerificationToken($token);
        if (!$user) {
            Session::flash('errors', ['login_general' => 'El enlace de verificación no es válido o ya expiró.']);
            Session::flash('tab', 'login');
            $this->redirectTo('/');
        }

        $this->users->markEmailAsVerified((int) $user['id']);

        Session::flash('success', 'Tu correo fue verificado correctamente. Ya puedes iniciar sesión.');
        Session::flash('old', ['login_email' => $user['email']]);
        Session::flash('tab', 'login');
        $this->redirectTo('/');
    }

    private function sendVerificationEmail(array $user, string $token): void
    {
        $verificationUrl = $this->absoluteUrl('email/verify?token=' . urlencode($token));

        $htmlBody = $this->emailTemplates->render('verify', [
            'fullName' => $user['full_name'],
            'verificationUrl' => $verificationUrl,
        ]);

        $textBody = "Hola {$user['full_name']},\n\nPara activar tu cuenta en Gestor de Titulación confirma tu correo en el siguiente enlace: {$verificationUrl}\n\nEl enlace caduca en 48 horas.";

        $mailer = new Mailer();
        $mailer->send($user['email'], 'Verifica tu correo electrónico', $htmlBody, $textBody);
    }

    private function sendPasswordResetEmail(array $user, string $token): void
    {
        $resetUrl = $this->absoluteUrl('password/change?token=' . urlencode($token));

        $htmlBody = $this->emailTemplates->render('reset_password', [
            'fullName' => $user['full_name'],
            'resetUrl' => $resetUrl,
        ]);

        $textBody = "Hola {$user['full_name']},\n\nRecibimos una solicitud para restablecer tu contraseña en Gestor de Titulación. Cambia tu contraseña con el siguiente enlace (válido por 1 hora): {$resetUrl}\n\nSi no solicitaste el cambio puedes ignorar este mensaje.";

        $mailer = new Mailer();
        $mailer->send($user['email'], 'Restablece tu contraseña', $htmlBody, $textBody);
    }

    private function absoluteUrl(string $path): string
    {
        $base = Config::get('app.base_url');

        if ($base === '') {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $basePath = \base_url();

            if ($basePath === '' || $basePath === '/') {
                $base = $scheme . '://' . $host;
            } else {
                $base = rtrim($scheme . '://' . $host . $basePath, '/');
            }
        }

        $trimmed = ltrim($path, '/');

        if ($trimmed === '') {
            return rtrim($base, '/');
        }

        return rtrim($base, '/') . '/' . $trimmed;
    }
}
