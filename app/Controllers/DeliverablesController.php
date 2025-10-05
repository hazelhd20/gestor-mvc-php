<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\Deliverable;
use App\Models\Milestone;
use App\Models\Notification;
use App\Models\Project;
use RuntimeException;
use Throwable;

class DeliverablesController extends Controller
{
    private Deliverable $deliverables;
    private Milestone $milestones;
    private Project $projects;
    private Notification $notifications;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->deliverables = new Deliverable();
        $this->milestones = new Milestone();
        $this->projects = new Project();
        $this->notifications = new Notification();
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
        $isStudentOwner = ($role === 'estudiante' && (int) $project['student_id'] === $userId);

        if (!$isStudentOwner) {
            Session::flash('dashboard_errors', ['Solo el estudiante asignado puede registrar avances.']);
            Session::flash('dashboard_project_id', (int) $project['id']);
            Session::flash('dashboard_tab', 'hitos');
            $this->redirectTo('/dashboard');
        }

        if (in_array($milestone['status'], ['en_revision', 'aprobado'], true)) {
            Session::flash('dashboard_errors', ['No es posible registrar avances mientras el hito esta en revision o aprobado.']);
            Session::flash('dashboard_project_id', (int) $project['id']);
            Session::flash('dashboard_tab', 'hitos');
            $this->redirectTo('/dashboard');
        }
        if ($notes !== '') {
            $notesLength = function_exists('mb_strlen') ? mb_strlen($notes) : strlen($notes);
            if ($notesLength > 2000) {
                Session::flash('dashboard_errors', ['Las notas del avance no pueden exceder los 2000 caracteres.']);
                Session::flash('dashboard_project_id', (int) $project['id']);
                Session::flash('dashboard_tab', 'hitos');
                $this->redirectTo('/dashboard');
            }
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
            $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
            $allowedExtensions = ['pdf','doc','docx','ppt','pptx','xls','xlsx','zip','rar','7z','txt','md','csv','jpg','jpeg','png','gif'];
            if ($extension === '' || !in_array($extension, $allowedExtensions, true)) {
                Session::flash('dashboard_errors', ['El tipo de archivo no es permitido. Usa formatos como PDF, DOCX, PPTX, ZIP o PNG.']);
                Session::flash('dashboard_project_id', (int) $project['id']);
                Session::flash('dashboard_tab', 'hitos');
                $this->redirectTo('/dashboard');
            }

            $allowedMimeTypes = ['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.presentationml.presentation','application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/zip','application/x-zip-compressed','application/x-rar-compressed','application/x-7z-compressed','text/plain','text/markdown','text/csv','image/jpeg','image/png','image/gif'];
            $detectedMime = $uploaded['type'] ?? '';
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                if ($finfo) {
                    $detected = finfo_file($finfo, $uploaded['tmp_name']);
                    if (is_string($detected) && $detected !== '') {
                        $detectedMime = $detected;
                    }
                    finfo_close($finfo);
                }
            }

            if ($detectedMime !== '' && !in_array($detectedMime, $allowedMimeTypes, true)) {
                Session::flash('dashboard_errors', ['El tipo de archivo no es permitido. Usa formatos como PDF, DOCX, PPTX, ZIP o PNG.']);
                Session::flash('dashboard_project_id', (int) $project['id']);
                Session::flash('dashboard_tab', 'hitos');
                $this->redirectTo('/dashboard');
            }

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
            $mimeType = mime_content_type($targetPath) ?: ($detectedMime !== '' ? $detectedMime : null);
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

        $this->notify(
            (int) ($project['director_id'] ?? 0),
            'deliverable_submitted',
            'Nuevo avance registrado',
            sprintf(
                'El estudiante %s registro un avance para el hito "%s".',
                (string) ($user['full_name'] ?? 'Sin nombre'),
                (string) ($milestone['title'] ?? 'Sin titulo')
            ),
            url('/dashboard?tab=hitos&project=' . (int) $project['id']),
            [
                'project_id' => (int) $project['id'],
                'project_title' => $project['title'] ?? null,
                'milestone_id' => $milestoneId,
                'milestone_title' => $milestone['title'] ?? null,
            ]
        );

        Session::flash('dashboard_success', 'Avance registrado correctamente.');
        Session::flash('dashboard_project_id', (int) $project['id']);
        Session::flash('dashboard_tab', 'hitos');
        $this->redirectTo('/dashboard');
    }

    private function notify(
        int $recipientId,
        string $type,
        string $title,
        ?string $body = null,
        ?string $actionUrl = null,
        array $data = []
    ): void {
        if ($recipientId <= 0) {
            return;
        }

        try {
            $this->notifications->create([
                'user_id' => $recipientId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'action_url' => $actionUrl,
                'data' => $data,
            ]);
        } catch (Throwable) {
            // Ignorar errores de notificaciones para no afectar el flujo principal
        }
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
