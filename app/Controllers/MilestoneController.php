<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\Comment;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Submission;
use App\Models\User;
use DateTimeImmutable;
use RuntimeException;

class MilestoneController extends Controller
{
    private Milestone $milestones;
    private Project $projects;
    private Submission $submissions;
    private Comment $comments;
    private User $users;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->milestones = new Milestone();
        $this->projects = new Project();
        $this->submissions = new Submission();
        $this->comments = new Comment();
        $this->users = new User();
    }

    public function index(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        $projects = $this->projects->allForUser($user);
        if ($projects === []) {
            $this->render('milestones/index', [
                'user' => $user,
                'projects' => [],
                'selectedProject' => null,
                'milestones' => [],
                'submissionsByMilestone' => [],
                'usersMap' => [],
                'errors' => [],
                'old' => [],
                'statusMessage' => null,
                'feedbackFlash' => [],
            ]);
            return;
        }

        $requestedProjectId = (int) ($_GET['project'] ?? 0);
        $selectedProject = null;
        foreach ($projects as $project) {
            if ((int) $project['id'] === $requestedProjectId) {
                $selectedProject = $project;
                break;
            }
        }

        if ($selectedProject === null) {
            $selectedProject = $projects[0];
        }

        $milestones = $this->milestones->allForProject((int) $selectedProject['id']);
        $submissionsByMilestone = [];
        $milestoneComments = [];
        $userIds = [(int) $user['id']];

        foreach ($milestones as $milestone) {
            $submissions = $this->submissions->allForMilestone((int) $milestone['id']);
            foreach ($submissions as &$submission) {
                $submission['comments'] = $this->comments->allForSubmission((int) $submission['id']);
                $userIds[] = (int) $submission['user_id'];
                foreach ($submission['comments'] as $comment) {
                    $userIds[] = (int) $comment['user_id'];
                }
            }
            $submissionsByMilestone[(int) $milestone['id']] = $submissions;

            $milestoneFeedback = $this->comments->allForMilestone((int) $milestone['id']);
            $milestoneComments[(int) $milestone['id']] = $milestoneFeedback;
            foreach ($milestoneFeedback as $comment) {
                $userIds[] = (int) $comment['user_id'];
            }
        }

        $usersMap = $this->users->findManyByIds($userIds);

        $this->render('milestones/index', [
            'user' => $user,
            'projects' => $projects,
            'selectedProject' => $selectedProject,
            'milestones' => $milestones,
            'submissionsByMilestone' => $submissionsByMilestone,
            'milestoneComments' => $milestoneComments,
            'usersMap' => $usersMap,
            'errors' => Session::flash('milestone_errors') ?? [],
            'old' => Session::flash('milestone_old') ?? [],
            'statusMessage' => Session::flash('milestone_status'),
            'feedbackFlash' => Session::flash('milestone_feedback') ?? [],
        ]);
    }

    public function store(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        $projectId = (int) ($_POST['project_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $dueDate = trim($_POST['due_date'] ?? '') ?: null;

        $errors = [];
        if ($projectId <= 0 || !$this->projects->findForUser($projectId, $user)) {
            $errors['project_id'] = 'Selecciona un proyecto valido.';
        }
        if ($title === '') {
            $errors['title'] = 'El titulo es obligatorio.';
        }
        if ($dueDate) {
            $validDate = DateTimeImmutable::createFromFormat('Y-m-d', $dueDate);
            if (!$validDate) {
                $errors['due_date'] = 'Ingresa una fecha valida (YYYY-MM-DD).';
            }
        }

        if ($errors) {
            Session::flash('milestone_errors', $errors);
            Session::flash('milestone_old', [
                'project_id' => $projectId,
                'title' => $title,
                'description' => $description,
                'due_date' => $dueDate,
            ]);
            $this->redirectTo('/milestones?project=' . max($projectId, 0));
        }

        $this->milestones->create([
            'project_id' => $projectId,
            'title' => $title,
            'description' => $description,
            'due_date' => $dueDate,
        ]);

        Session::flash('milestone_status', 'Hito registrado correctamente.');
        $this->redirectTo('/milestones?project=' . $projectId);
    }

    public function submit(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        $milestoneId = (int) ($_POST['milestone_id'] ?? 0);
        $projectId = (int) ($_POST['project_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');

        $milestone = $this->milestones->find($milestoneId);
        $project = $this->projects->findForUser($projectId, $user);

        $errors = [];
        if (!$milestone || !$project || (int) $milestone['project_id'] !== $projectId) {
            $errors['submission'] = 'No tienes acceso a este hito.';
        }

        $attachmentPath = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['attachment'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors['attachment'] = 'Ocurrio un error al subir el archivo.';
            } else {
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'rar', '7z', 'jpg', 'jpeg', 'png'];
                if ($extension !== '' && !in_array($extension, $allowed, true)) {
                    $errors['attachment'] = 'Formato de archivo no permitido.';
                } else {
                    $uploadsDir = base_path('storage/uploads');
                    if (!is_dir($uploadsDir)) {
                        mkdir($uploadsDir, 0775, true);
                    }
                    $filename = uniqid('submission_', true) . ($extension !== '' ? '.' . $extension : '');
                    $destination = $uploadsDir . '/' . $filename;
                    if (!move_uploaded_file($file['tmp_name'], $destination)) {
                        $errors['attachment'] = 'No pudimos guardar el archivo.';
                    } else {
                        $attachmentPath = 'uploads/' . $filename;
                    }
                }
            }
        }

        if ($notes === '' && !$attachmentPath) {
            $errors['notes'] = 'Agrega una nota o adjunta un archivo.';
        }

        if ($errors) {
            Session::flash('milestone_feedback', [
                'type' => 'submission',
                'target' => $milestoneId,
                'errors' => $errors,
                'old' => ['notes' => $notes],
            ]);
            $this->redirectTo('/milestones?project=' . max($projectId, 0));
        }

        $this->submissions->create([
            'milestone_id' => $milestoneId,
            'user_id' => (int) $user['id'],
            'notes' => $notes,
            'attachment_path' => $attachmentPath,
        ]);

        if ($user['role'] === 'estudiante') {
            $this->milestones->updateStatus($milestoneId, 'en_revision');
        }

        Session::flash('milestone_status', 'Entregable enviado correctamente.');
        $this->redirectTo('/milestones?project=' . $projectId);
    }

    public function comment(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        $submissionId = (int) ($_POST['submission_id'] ?? 0);
        $milestoneId = (int) ($_POST['milestone_id'] ?? 0);
        $projectId = (int) ($_POST['project_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        $threadScope = $_POST['thread_scope'] ?? ($submissionId > 0 ? 'submission' : 'milestone');

        if (!in_array($threadScope, ['submission', 'milestone'], true)) {
            $threadScope = $submissionId > 0 ? 'submission' : 'milestone';
        }

        if ($threadScope === 'milestone') {
            $submissionId = 0;
        }

        $submission = $submissionId > 0 ? $this->submissions->find($submissionId) : null;
        $milestone = null;
        $project = null;
        $errors = [];

        if ($submission) {
            $milestone = $this->milestones->find((int) $submission['milestone_id']);
        }

        if (!$milestone && $milestoneId > 0) {
            $milestone = $this->milestones->find($milestoneId);
        }

        if ($milestone) {
            $project = $this->projects->findForUser((int) $milestone['project_id'], $user);
        }

        if (!$milestone || !$project || (int) $project['id'] !== $projectId) {
            $errors['comment'] = 'No tienes acceso a este hito.';
        } elseif ($submission && (int) $submission['milestone_id'] !== (int) $milestone['id']) {
            $errors['comment'] = 'No tienes acceso a esta entrega.';
        }

        if ($message === '') {
            $errors['message'] = 'Escribe un comentario.';
        }

        if ($errors) {
            Session::flash('milestone_feedback', [
                'type' => 'comment',
                'target' => (int) ($milestone['id'] ?? $milestoneId),
                'errors' => $errors,
                'old' => [
                    'message' => $message,
                    'thread_scope' => $threadScope,
                ],
            ]);
            $this->redirectTo('/milestones?project=' . max($projectId, 0));
        }

        $this->comments->create([
            'submission_id' => $submission ? (int) $submission['id'] : null,
            'milestone_id' => $milestone ? (int) $milestone['id'] : null,
            'user_id' => (int) $user['id'],
            'message' => $message,
        ]);

        Session::flash('milestone_status', 'Comentario enviado.');
        $targetMilestone = $milestone ? (int) $milestone['id'] : $milestoneId;
        $this->redirectTo('/milestones?project=' . $projectId . '#milestone-' . $targetMilestone . '-feedback');
    }

    public function updateStatus(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        $milestoneId = (int) ($_POST['milestone_id'] ?? 0);
        $status = $_POST['status'] ?? 'pendiente';
        $projectId = (int) ($_POST['project_id'] ?? 0);

        $milestone = $this->milestones->find($milestoneId);
        $project = $milestone ? $this->projects->findForUser((int) $milestone['project_id'], $user) : null;
        if (!$milestone || !$project || (int) $project['id'] !== $projectId) {
            $this->redirectTo('/milestones?project=' . max($projectId, 0));
        }

        if ($user['role'] !== 'director') {
            Session::flash('milestone_status', 'Solo los directores pueden actualizar el estado del hito.');
            $this->redirectTo('/milestones?project=' . $projectId);
        }

        $allowedStatus = ['pendiente', 'en_progreso', 'en_revision', 'completado'];
        if (!in_array($status, $allowedStatus, true)) {
            $status = 'pendiente';
        }

        $this->milestones->updateStatus($milestoneId, $status);
        Session::flash('milestone_status', 'Estado del hito actualizado.');
        $this->redirectTo('/milestones?project=' . $projectId . '#milestone-' . $milestoneId);
    }

    public function download(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        $submissionId = (int) ($_GET['id'] ?? 0);
        $submission = $this->submissions->find($submissionId);
        if (!$submission || empty($submission['attachment_path'])) {
            throw new RuntimeException('Archivo no disponible.');
        }

        $milestone = $this->milestones->find((int) $submission['milestone_id']);
        $project = $milestone ? $this->projects->findForUser((int) $milestone['project_id'], $user) : null;
        if (!$milestone || !$project) {
            $this->redirectTo('/milestones');
        }

        $absolutePath = base_path('storage/' . ltrim($submission['attachment_path'], '/'));
        if (!is_file($absolutePath)) {
            throw new RuntimeException('Archivo no disponible.');
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($absolutePath) . '"');
        header('Content-Length: ' . filesize($absolutePath));
        readfile($absolutePath);
        exit;
    }
}
