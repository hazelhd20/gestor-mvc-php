<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\Milestone;
use App\Models\Notification;
use App\Models\Project;
use DateTimeImmutable;
use RuntimeException;
use Throwable;

class MilestonesController extends Controller
{
    private Milestone $milestones;
    private Project $projects;
    private Notification $notifications;

    public function __construct()
    {
        parent::__construct();
        Session::start();
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

        if (($user['role'] ?? '') !== 'director') {
            Session::flash('dashboard_errors', ['Solo los directores pueden crear hitos.']);
            Session::flash('dashboard_tab', 'hitos');
            $this->redirectTo('/dashboard');
        }

        $projectId = (int) ($_POST['project_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $startDate = trim($_POST['start_date'] ?? '');
        $endDate = trim($_POST['end_date'] ?? ($_POST['due_date'] ?? ''));

        $errors = [];
        $old = [
            'project_id' => $projectId,
            'title' => $title,
            'description' => $description,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        $project = null;

        if ($projectId <= 0) {
            $errors[] = 'Selecciona un proyecto valido.';
        } else {
            $project = $this->projects->find($projectId);
            if (!$project) {
                $errors[] = 'El proyecto indicado no existe.';
            } elseif ((int) $project['director_id'] !== (int) $user['id']) {
                $errors[] = 'No tienes acceso a ese proyecto.';
            }
        }

        if ($title === '') {
            $errors[] = 'El titulo del hito es obligatorio.';
        }

        $parsedStartDate = null;
        $startDateObject = null;
        if ($startDate === '') {
            $errors[] = 'La fecha de inicio del hito es obligatoria.';
        } else {
            try {
                $startDateObject = new DateTimeImmutable($startDate);
                $parsedStartDate = $startDateObject->format('Y-m-d');
            } catch (RuntimeException) {
                $errors[] = 'La fecha de inicio del hito no es valida.';
            } catch (\Exception) {
                $errors[] = 'La fecha de inicio del hito no es valida.';
            }
        }

        $parsedEndDate = null;
        $endDateObject = null;
        if ($endDate === '') {
            $errors[] = 'La fecha de finalizacion del hito es obligatoria.';
        } else {
            try {
                $endDateObject = new DateTimeImmutable($endDate);
                $parsedEndDate = $endDateObject->format('Y-m-d');
            } catch (RuntimeException) {
                $errors[] = 'La fecha de finalizacion del hito no es valida.';
            } catch (\Exception) {
                $errors[] = 'La fecha de finalizacion del hito no es valida.';
            }
        }

        if ($startDateObject && $endDateObject && $endDateObject < $startDateObject) {
            $errors[] = 'La fecha de finalizacion del hito debe ser posterior o igual a la fecha de inicio.';
        }

        if ($startDateObject && $project && ($project['start_date'] ?? null)) {
            try {
                $projectStart = new DateTimeImmutable((string) $project['start_date']);
                if ($startDateObject < $projectStart) {
                    $errors[] = 'La fecha de inicio del hito no puede ser anterior a la fecha de inicio del proyecto.';
                }
            } catch (\Exception) {
                // Ignorar conversion invalida
            }
        }

        $projectEndValue = null;
        if ($project) {
            $projectEndValue = $project['end_date'] ?? $project['due_date'] ?? null;
        }

        if ($endDateObject) {
            $today = new DateTimeImmutable('today');
            if ($endDateObject < $today) {
                $errors[] = 'La fecha de finalizacion del hito debe ser igual o posterior a hoy.';
            }

            if ($projectEndValue) {
                try {
                    $projectEnd = new DateTimeImmutable((string) $projectEndValue);
                    if ($endDateObject > $projectEnd) {
                        $errors[] = 'La fecha de finalizacion del hito no puede exceder la fecha limite del proyecto.';
                    }
                } catch (\Exception) {
                    // Ignorar conversion invalida
                }
            }
        }

        if ($errors !== []) {
            Session::flash('dashboard_errors', $errors);
            Session::flash('dashboard_old', ['milestone' => $old]);
            Session::flash('dashboard_project_id', $projectId);
            Session::flash('dashboard_tab', 'hitos');
            $this->redirectTo('/dashboard');
        }

        try {
            $milestone = $this->milestones->create([
                'project_id' => $projectId,
                'title' => $title,
                'description' => $description !== '' ? $description : null,
                'start_date' => $parsedStartDate,
                'end_date' => $parsedEndDate,
                'status' => 'pendiente',
            ]);
        } catch (RuntimeException $exception) {
            Session::flash('dashboard_errors', [$exception->getMessage()]);
            Session::flash('dashboard_old', ['milestone' => $old]);
            Session::flash('dashboard_project_id', $projectId);
            Session::flash('dashboard_tab', 'hitos');
            $this->redirectTo('/dashboard');
        }

        Session::flash('dashboard_success', 'Hito creado correctamente.');
        Session::flash('dashboard_project_id', (int) $milestone['project_id']);
        Session::flash('dashboard_tab', 'hitos');
        $this->redirectTo('/dashboard');
    }

    public function updateStatus(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        $milestoneId = (int) ($_POST['milestone_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if ($milestoneId <= 0) {
            Session::flash('dashboard_errors', ['Hito no valido.']);
            Session::flash('dashboard_tab', 'hitos');
            $this->redirectTo('/dashboard');
        }

        $milestone = $this->milestones->find($milestoneId);
        if (!$milestone) {
            Session::flash('dashboard_errors', ['No encontramos el hito.']);
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

        $ownsMilestone = ($role === 'director' && (int) $project['director_id'] === $userId)
            || ($role === 'estudiante' && (int) $project['student_id'] === $userId);

        if (!$ownsMilestone) {
            Session::flash('dashboard_errors', ['No tienes permisos sobre ese hito.']);
            Session::flash('dashboard_project_id', (int) $project['id']);
            Session::flash('dashboard_tab', 'hitos');
            $this->redirectTo('/dashboard');
        }

        $currentStatus = $milestone['status'];

        if ($currentStatus === 'aprobado') {
            if ($role !== 'director') {
                Session::flash('dashboard_errors', ['El hito ya fue aprobado y solo el director puede modificarlo.']);
                Session::flash('dashboard_project_id', (int) $project['id']);
                Session::flash('dashboard_tab', 'hitos');
                $this->redirectTo('/dashboard');
            }
            if ($role === 'director' && $status !== 'aprobado') {
                Session::flash('dashboard_errors', ['Un hito aprobado solo puede mantenerse en estado aprobado.']);
                Session::flash('dashboard_project_id', (int) $project['id']);
                Session::flash('dashboard_tab', 'hitos');
                $this->redirectTo('/dashboard');
            }
        }

        $allowedByRole = [
            'director' => ['pendiente', 'en_progreso', 'en_revision', 'aprobado'],
            'estudiante' => ['pendiente', 'en_progreso', 'en_revision'],
        ];

        $roleStatuses = $allowedByRole[$role] ?? [];

        if ($role === 'estudiante') {
            $flow = ['pendiente', 'en_progreso', 'en_revision'];
            $currentIndex = array_search($currentStatus, $flow, true);
            $allowedTransitions = [];
            if ($currentIndex !== false) {
                $allowedTransitions[] = $flow[$currentIndex];
                if (isset($flow[$currentIndex + 1])) {
                    $allowedTransitions[] = $flow[$currentIndex + 1];
                }
            }

            if ($currentIndex === false || !in_array($status, $allowedTransitions, true)) {
                Session::flash('dashboard_errors', ['Como estudiante solo puedes avanzar el hito al siguiente estado permitido.']);
                Session::flash('dashboard_project_id', (int) $project['id']);
                Session::flash('dashboard_tab', 'hitos');
                $this->redirectTo('/dashboard');
            }
        }

        if (!in_array($status, $roleStatuses, true)) {
            Session::flash('dashboard_errors', ['No tienes permisos para mover el hito a ese estado.']);
            Session::flash('dashboard_project_id', (int) $project['id']);
            Session::flash('dashboard_tab', 'hitos');
            $this->redirectTo('/dashboard');
        }

        try {
            $this->milestones->updateStatus($milestoneId, $status);
        } catch (RuntimeException $exception) {
            Session::flash('dashboard_errors', [$exception->getMessage()]);
            Session::flash('dashboard_project_id', (int) $project['id']);
            Session::flash('dashboard_tab', 'hitos');
            $this->redirectTo('/dashboard');
        }

        $recipientId = $role === 'director'
            ? (int) ($project['student_id'] ?? 0)
            : (int) ($project['director_id'] ?? 0);

        $statusLabels = [
            'pendiente' => 'Pendiente',
            'en_progreso' => 'En progreso',
            'en_revision' => 'En revisión',
            'aprobado' => 'Aprobado',
        ];

        $this->notify(
            $recipientId,
            'milestone_status_updated',
            'Estado del hito actualizado',
            sprintf(
                '%s actualizó el hito "%s" al estado %s.',
                (string) ($user['full_name'] ?? 'Un usuario'),
                (string) ($milestone['title'] ?? 'Sin titulo'),
                $statusLabels[$status] ?? $status
            ),
            url('/dashboard?tab=hitos&project=' . (int) $project['id']),
            [
                'project_id' => (int) $project['id'],
                'project_title' => $project['title'] ?? null,
                'milestone_id' => $milestoneId,
                'milestone_title' => $milestone['title'] ?? null,
                'new_status' => $status,
            ]
        );

        Session::flash('dashboard_success', 'Estado del hito actualizado.');
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
            // Ignorar fallos de notificación para mantener la experiencia del usuario
        }
    }

    public function update(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        if (($user['role'] ?? '') !== 'director') {
            Session::flash('dashboard_errors', ['Solo los directores pueden actualizar hitos.']);
            Session::flash('dashboard_tab', 'hitos');
            $this->redirectTo('/dashboard');
        }

        $milestoneId = (int) ($_POST['milestone_id'] ?? 0);
        $milestone = $milestoneId > 0 ? $this->milestones->find($milestoneId) : null;

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

        if ((int) $project['director_id'] !== (int) ($user['id'] ?? 0)) {
            Session::flash('dashboard_errors', ['No tienes permisos sobre ese hito.']);
            Session::flash('dashboard_project_id', (int) $project['id']);
            Session::flash('dashboard_tab', 'hitos');
            $this->redirectTo('/dashboard');
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $startDate = trim($_POST['start_date'] ?? '');
        $endDate = trim($_POST['end_date'] ?? '');

        $errors = [];
        $old = [
            'milestone_id' => $milestoneId,
            'project_id' => (int) $project['id'],
            'title' => $title,
            'description' => $description,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        if ($title === '') {
            $errors[] = 'El titulo del hito es obligatorio.';
        }

        $parsedStart = null;
        $startObject = null;
        if ($startDate === '') {
            $errors[] = 'La fecha de inicio del hito es obligatoria.';
        } else {
            try {
                $startObject = new DateTimeImmutable($startDate);
                $parsedStart = $startObject->format('Y-m-d');
            } catch (RuntimeException) {
                $errors[] = 'La fecha de inicio del hito no es valida.';
            } catch (\Exception) {
                $errors[] = 'La fecha de inicio del hito no es valida.';
            }
        }

        $parsedEnd = null;
        $endObject = null;
        if ($endDate === '') {
            $errors[] = 'La fecha de finalizacion del hito es obligatoria.';
        } else {
            try {
                $endObject = new DateTimeImmutable($endDate);
                $parsedEnd = $endObject->format('Y-m-d');
            } catch (RuntimeException) {
                $errors[] = 'La fecha de finalizacion del hito no es valida.';
            } catch (\Exception) {
                $errors[] = 'La fecha de finalizacion del hito no es valida.';
            }
        }

        if ($startObject && $endObject && $endObject < $startObject) {
            $errors[] = 'La fecha de finalizacion del hito debe ser posterior o igual a la fecha de inicio.';
        }

        $projectStart = $project['start_date'] ?? null;
        if ($startObject && $projectStart) {
            try {
                $projectStartDate = new DateTimeImmutable((string) $projectStart);
                if ($startObject < $projectStartDate) {
                    $errors[] = 'La fecha de inicio del hito no puede ser anterior a la del proyecto.';
                }
            } catch (\Exception) {
                // Ignorar fechas de proyecto invalidas
            }
        }

        $projectEndValue = $project['end_date'] ?? $project['due_date'] ?? null;
        if ($endObject && $projectEndValue) {
            try {
                $projectEndDate = new DateTimeImmutable((string) $projectEndValue);
                if ($endObject > $projectEndDate) {
                    $errors[] = 'La fecha de finalizacion del hito no puede exceder la fecha limite del proyecto.';
                }
            } catch (\Exception) {
                // Ignorar fechas de proyecto invalidas
            }
        }

        if ($errors !== []) {
            Session::flash('dashboard_errors', $errors);
            Session::flash('dashboard_old', ['milestone_edit' => $old, 'milestone_edit_id' => $milestoneId]);
            Session::flash('dashboard_project_id', (int) $project['id']);
            Session::flash('dashboard_tab', 'hitos');
            Session::flash('dashboard_modal', 'modalMilestoneEdit');
            $this->redirectTo('/dashboard');
        }

        try {
            $this->milestones->update($milestoneId, [
                'title' => $title,
                'description' => $description !== '' ? $description : null,
                'start_date' => $parsedStart,
                'end_date' => $parsedEnd,
            ]);
        } catch (RuntimeException $exception) {
            Session::flash('dashboard_errors', [$exception->getMessage()]);
            Session::flash('dashboard_old', ['milestone_edit' => $old, 'milestone_edit_id' => $milestoneId]);
            Session::flash('dashboard_project_id', (int) $project['id']);
            Session::flash('dashboard_tab', 'hitos');
            Session::flash('dashboard_modal', 'modalMilestoneEdit');
            $this->redirectTo('/dashboard');
        }

        Session::flash('dashboard_success', 'Hito actualizado correctamente.');
        Session::flash('dashboard_project_id', (int) $project['id']);
        Session::flash('dashboard_tab', 'hitos');
        $this->redirectTo('/dashboard');
    }

    public function destroy(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        if (($user['role'] ?? '') !== 'director') {
            Session::flash('dashboard_errors', ['Solo los directores pueden eliminar hitos.']);
            Session::flash('dashboard_tab', 'hitos');
            $this->redirectTo('/dashboard');
        }

        $milestoneId = (int) ($_POST['milestone_id'] ?? 0);
        $milestone = $milestoneId > 0 ? $this->milestones->find($milestoneId) : null;

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

        if ((int) $project['director_id'] !== (int) ($user['id'] ?? 0)) {
            Session::flash('dashboard_errors', ['No tienes permisos sobre ese hito.']);
            Session::flash('dashboard_project_id', (int) $project['id']);
            Session::flash('dashboard_tab', 'hitos');
            $this->redirectTo('/dashboard');
        }

        try {
            $this->milestones->delete($milestoneId);
        } catch (RuntimeException $exception) {
            Session::flash('dashboard_errors', [$exception->getMessage()]);
            Session::flash('dashboard_project_id', (int) $project['id']);
            Session::flash('dashboard_tab', 'hitos');
            $this->redirectTo('/dashboard');
        }

        Session::flash('dashboard_success', 'Hito eliminado correctamente.');
        Session::flash('dashboard_project_id', (int) $project['id']);
        Session::flash('dashboard_tab', 'hitos');
        $this->redirectTo('/dashboard');
    }

}
