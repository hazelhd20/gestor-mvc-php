<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\User;
use RuntimeException;

class AuthController extends Controller
{
    private User $users;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->users = new User();
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

        Session::regenerate();
        Session::put('user', [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
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

        $this->render('auth/password_change', [
            'status' => $status,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    public function requestPasswordChange(): void
    {
        Session::start();

        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

        $errors = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Ingresa un correo valido.';
        }

        if (strlen($password) < 8) {
            $errors['password'] = 'La contrasena debe tener al menos 8 caracteres.';
        }

        if ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = 'Las contrasenas no coinciden.';
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

        $this->users->updatePassword((int) $user['id'], $password);

        Session::flash('password_change_status', 'La contrasena se actualizo correctamente. Ya puedes iniciar sesion.');
        Session::flash('password_change_old', ['email' => $email]);
        $this->redirectTo('/password/change');
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
            $this->users->create([
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

        Session::flash('success', 'Cuenta creada correctamente. Ahora puedes iniciar sesion.');
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
}
