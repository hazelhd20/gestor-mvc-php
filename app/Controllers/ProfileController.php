<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\User;
use RuntimeException;

class ProfileController extends Controller
{
    private User $users;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->users = new User();
    }

    public function updateAvatar(): void
    {
        $sessionUser = Session::user();
        if (!$sessionUser) {
            $this->redirectTo('/');
        }

        $userId = (int) ($sessionUser['id'] ?? 0);
        if ($userId <= 0) {
            $this->redirectTo('/');
        }

        $returnTab = $_POST['return_tab'] ?? 'dashboard';
        $returnProject = (int) ($_POST['return_project'] ?? 0);

        $file = $_FILES['avatar'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            Session::flash('dashboard_errors', ['Selecciona una imagen antes de guardar.']);
            Session::flash('dashboard_tab', $returnTab);
            if ($returnProject > 0) {
                Session::flash('dashboard_project_id', $returnProject);
            }
            $this->redirectTo('/dashboard');
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            Session::flash('dashboard_errors', ['Hubo un problema al subir la imagen.']);
            Session::flash('dashboard_tab', $returnTab);
            if ($returnProject > 0) {
                Session::flash('dashboard_project_id', $returnProject);
            }
            $this->redirectTo('/dashboard');
        }

        $maxBytes = 5 * 1024 * 1024; // 5 MB
        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > $maxBytes) {
            Session::flash('dashboard_errors', ['La imagen debe pesar menos de 5 MB.']);
            Session::flash('dashboard_tab', $returnTab);
            if ($returnProject > 0) {
                Session::flash('dashboard_project_id', $returnProject);
            }
            $this->redirectTo('/dashboard');
        }

        $originalName = (string) ($file['name'] ?? 'avatar');
        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if ($extension === '' || !in_array($extension, $allowedExtensions, true)) {
            Session::flash('dashboard_errors', ['Formato no soportado. Usa JPG, PNG, GIF o WEBP.']);
            Session::flash('dashboard_tab', $returnTab);
            if ($returnProject > 0) {
                Session::flash('dashboard_project_id', $returnProject);
            }
            $this->redirectTo('/dashboard');
        }

        $detectedMime = '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $detected = finfo_file($finfo, $file['tmp_name']);
                if (is_string($detected)) {
                    $detectedMime = $detected;
                }
                finfo_close($finfo);
            }
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if ($detectedMime !== '' && !in_array($detectedMime, $allowedMimeTypes, true)) {
            Session::flash('dashboard_errors', ['La imagen parece estar en un formato no permitido.']);
            Session::flash('dashboard_tab', $returnTab);
            if ($returnProject > 0) {
                Session::flash('dashboard_project_id', $returnProject);
            }
            $this->redirectTo('/dashboard');
        }

        $safeBase = preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
        $safeBase = $safeBase !== '' ? substr($safeBase, 0, 40) : 'avatar';

        try {
            $unique = bin2hex(random_bytes(8));
        } catch (RuntimeException) {
            $unique = uniqid('avatar_', true);
        }

        $fileName = 'user_' . $userId . '_' . $unique;
        if ($extension !== '') {
            $fileName .= '.' . $extension;
        }

        $relativePath = 'uploads/avatars/' . $fileName;
        $uploadDir = base_path('public/uploads/avatars');
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            Session::flash('dashboard_errors', ['No pudimos preparar el directorio de subida.']);
            Session::flash('dashboard_tab', $returnTab);
            if ($returnProject > 0) {
                Session::flash('dashboard_project_id', $returnProject);
            }
            $this->redirectTo('/dashboard');
        }

        $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            Session::flash('dashboard_errors', ['No pudimos guardar la imagen en el servidor.']);
            Session::flash('dashboard_tab', $returnTab);
            if ($returnProject > 0) {
                Session::flash('dashboard_project_id', $returnProject);
            }
            $this->redirectTo('/dashboard');
        }

        $previousPath = $sessionUser['avatar_path'] ?? '';
        if (is_string($previousPath) && $previousPath !== '' && strncmp($previousPath, 'uploads/avatars/', 16) === 0) {
            $oldFullPath = base_path('public/' . $previousPath);
            if (is_file($oldFullPath)) {
                @unlink($oldFullPath);
            }
        }

        try {
            $this->users->updateAvatar($userId, $relativePath);
        } catch (RuntimeException $exception) {
            if (is_file($targetPath)) {
                @unlink($targetPath);
            }
            Session::flash('dashboard_errors', [$exception->getMessage()]);
            Session::flash('dashboard_tab', $returnTab);
            if ($returnProject > 0) {
                Session::flash('dashboard_project_id', $returnProject);
            }
            $this->redirectTo('/dashboard');
        }

        $updatedUser = $this->users->findById($userId);
        if ($updatedUser) {
            Session::put('user', [
                'id' => (int) $updatedUser['id'],
                'full_name' => $updatedUser['full_name'],
                'email' => $updatedUser['email'],
                'role' => $updatedUser['role'],
                'avatar_path' => $updatedUser['avatar_path'] ?? null,
            ]);
        }

        Session::flash('dashboard_success', 'Actualizaste tu foto de perfil.');
        Session::flash('dashboard_tab', $returnTab);
        if ($returnProject > 0) {
            Session::flash('dashboard_project_id', $returnProject);
        }

        $this->redirectTo('/dashboard');
    }
}

