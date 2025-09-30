<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\Deliverable;
use App\Models\Milestone;
use App\Models\Project;
use RuntimeException;

class DeliverablesController extends Controller
{
    private Deliverable $deliverables;
    private Milestone $milestones;
    private Project $projects;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->deliverables = new Deliverable();
        $this->milestones = new Milestone();
        $this->projects = new Project();
    }

    public function store(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        $milestoneId = (int) ($_POST['milestone_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');

        if ($milestoneId <= 0) {
            Session::flash('dashboard_errors', ['Selecciona un hito valido antes de subir un entregable.']);
            Session::flash('dashboard_tab', 'hitos');
            $this->redirectTo('/dashboard');
        }

        $milestone = $this->milestones->find($milestoneId);
        if (!$milestone) {
            Session::flash('dashboard_errors', ['No encontramos el hito indicado.']);
            Session::flash('dashboard_tab', 'hitos');
            $this->redirectTo('/dashboard');
        }

        $project = $this->projects->find((int) $milestone['project_id']);
        if (!$project) {
            Session::flash('dashboard_errors', ['No encontramos el proyecto relacionado.']);
            Session::flash('dashboard_tab', 'hitos');
            $this->redirectTo('/dashboard');
        }

        $userId = (int) ($user['id'] ?? 0);
        $role = $user['role'] ?? '';
        $allowed = ($role === 'estudiante' && (int) $project['student_id'] === $userId)
            || ($role === 'director' && (int) $project['director_id'] === $userId);

        if (!$allowed) {
            Session::flash('dashboard_errors', ['No tienes permisos para subir avances en este proyecto.']);
            Session::flash('dashboard_tab', 'hitos');
            $this->redirectTo('/dashboard');
        }

        $uploaded = $_FILES['file'] ?? null;
        $hasFile = $uploaded && ($uploaded['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

        if (!$hasFile && $notes === '') {
            Session::flash('dashboard_errors', ['Sube un archivo o escribe notas del avance.']);
            Session::flash('dashboard_tab', 'hitos');
            Session::flash('dashboard_project_id', (int) $project['id']);
            $this->redirectTo('/dashboard');
        }

        $storedPath = null;
        $originalName = null;
        $mimeType = null;
        $fileSize = null;

        if ($hasFile) {
            if (($uploaded['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                Session::flash('dashboard_errors', ['Hubo un problema al subir el archivo.']);
                Session::flash('dashboard_tab', 'hitos');
                Session::flash('dashboard_project_id', (int) $project['id']);
                $this->redirectTo('/dashboard');
            }

            $fileSize = (int) ($uploaded['size'] ?? 0);
            $maxBytes = 15 * 1024 * 1024;
            if ($fileSize > $maxBytes) {
                Session::flash('dashboard_errors', ['El archivo excede el limite de 15 MB.']);
                Session::flash('dashboard_tab', 'hitos');
                Session::flash('dashboard_project_id', (int) $project['id']);
                $this->redirectTo('/dashboard');
            }

            $originalName = (string) ($uploaded['name'] ?? 'archivo');
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $safeBase = preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
            $safeBase = $safeBase !== '' ? $safeBase : 'entrega';
            $unique = bin2hex(random_bytes(8));
            $safeFileName = $safeBase . '_' . $unique;
            if ($extension !== '') {
                $safeFileName .= '.' . strtolower($extension);
            }

            $uploadDir = base_path('storage/uploads/project_' . (int) $project['id']);
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $safeFileName;
            if (!move_uploaded_file($uploaded['tmp_name'], $targetPath)) {
                Session::flash('dashboard_errors', ['No pudimos guardar el archivo en el servidor.']);
                Session::flash('dashboard_tab', 'hitos');
                Session::flash('dashboard_project_id', (int) $project['id']);
                $this->redirectTo('/dashboard');
            }

            $storedPath = 'storage/uploads/project_' . (int) $project['id'] . '/' . $safeFileName;
            $mimeType = mime_content_type($targetPath) ?: ($uploaded['type'] ?? null);
        }

        try {
            $this->deliverables->create([
                'milestone_id' => $milestoneId,
                'user_id' => $userId,
                'file_path' => $storedPath ?? '',
                'original_name' => $originalName ?? ($notes !== '' ? 'Notas de avance' : 'Entrega sin titulo'),
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'notes' => $notes !== '' ? $notes : null,
            ]);
        } catch (RuntimeException $exception) {
            if ($storedPath) {
                $fullPath = base_path($storedPath);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            Session::flash('dashboard_errors', [$exception->getMessage()]);
            Session::flash('dashboard_project_id', (int) $project['id']);
            Session::flash('dashboard_tab', 'hitos');
            $this->redirectTo('/dashboard');
        }

        Session::flash('dashboard_success', 'Avance registrado correctamente.');
        Session::flash('dashboard_project_id', (int) $project['id']);
        Session::flash('dashboard_tab', 'hitos');
        $this->redirectTo('/dashboard');
    }

    public function download(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        $deliverableId = (int) ($_GET['id'] ?? 0);
        if ($deliverableId <= 0) {
            http_response_code(404);
            echo 'Archivo no encontrado';
            return;
        }

        $deliverable = $this->deliverables->find($deliverableId);
        if (!$deliverable || ($deliverable['file_path'] ?? '') === '') {
            http_response_code(404);
            echo 'Archivo no disponible';
            return;
        }

        $milestone = $this->milestones->find((int) $deliverable['milestone_id']);
        $project = $milestone ? $this->projects->find((int) $milestone['project_id']) : null;

        if (!$milestone || !$project) {
            http_response_code(404);
            echo 'Registro no valido';
            return;
        }

        $userId = (int) ($user['id'] ?? 0);
        $role = $user['role'] ?? '';
        $allowed = ($role === 'director' && (int) $project['director_id'] === $userId)
            || ($role === 'estudiante' && (int) $project['student_id'] === $userId);

        if (!$allowed) {
            http_response_code(403);
            echo 'Sin permisos para descargar este archivo';
            return;
        }

        $filePath = base_path($deliverable['file_path']);
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo 'Archivo no disponible';
            return;
        }

        $mime = $deliverable['mime_type'] ?? 'application/octet-stream';
        $downloadName = $deliverable['original_name'] ?: basename($filePath);

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . str_replace('"', '', basename($downloadName)) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache');
        readfile($filePath);
        exit;
    }
}
