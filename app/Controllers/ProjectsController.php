<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\Notification;
use App\Models\Project;
use App\Models\User;
use DateTimeImmutable;
use RuntimeException;
use Throwable;

class ProjectsController extends Controller
{
    private Project $projects;
    private User $users;
    private Notification $notifications;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->projects = new Project();
        $this->users = new User();
        $this->notifications = new Notification();
    }

    public function store(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        if (($user['role'] ?? '') !== 'director') {
            Session::flash('dashboard_errors', ['No tienes permisos para crear proyectos.']);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $startDate = trim($_POST['start_date'] ?? '');
        $endDate = trim($_POST['end_date'] ?? ($_POST['due_date'] ?? ''));

        $errors = [];
        $old = [
            'title' => $title,
            'description' => $description,
            'student_id' => $studentId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        if ($title === '') {
            $errors[] = 'El titulo del proyecto es obligatorio.';
        }

        if ($studentId <= 0) {
            $errors[] = 'Selecciona un estudiante valido.';
        } else {
            $student = $this->users->findById($studentId);
            if (!$student || ($student['role'] ?? '') !== 'estudiante') {
                $errors[] = 'El estudiante seleccionado no es valido.';
            }
        }

        if ($studentId > 0 && $this->projects->studentHasActiveProject($studentId)) {
            $errors[] = 'El estudiante ya tiene un proyecto activo asignado.';
        }

        $parsedStartDate = null;
        $startDateObject = null;
        if ($startDate === '') {
            $errors[] = 'La fecha de inicio del proyecto es obligatoria.';
        } else {
            try {
                $startDateObject = new DateTimeImmutable($startDate);
                $parsedStartDate = $startDateObject->format('Y-m-d');
            } catch (RuntimeException) {
                $errors[] = 'La fecha de inicio del proyecto no es valida.';
            } catch (\Exception) {
                $errors[] = 'La fecha de inicio del proyecto no es valida.';
            }
        }

        $parsedEndDate = null;
        $endDateObject = null;
        if ($endDate === '') {
            $errors[] = 'La fecha de finalizacion del proyecto es obligatoria.';
        } else {
            try {
                $endDateObject = new DateTimeImmutable($endDate);
                $parsedEndDate = $endDateObject->format('Y-m-d');
            } catch (RuntimeException) {
                $errors[] = 'La fecha de finalizacion del proyecto no es valida.';
            } catch (\Exception) {
                $errors[] = 'La fecha de finalizacion del proyecto no es valida.';
            }
        }

        if ($startDateObject && $endDateObject && $endDateObject < $startDateObject) {
            $errors[] = 'La fecha de finalizacion debe ser posterior o igual a la fecha de inicio.';
        }

        if ($startDateObject) {
            $today = new DateTimeImmutable('today');
            if ($startDateObject < $today) {
                $errors[] = 'La fecha de inicio debe ser igual o posterior a hoy.';
            }
        }

        if ($endDateObject) {
            $today = new DateTimeImmutable('today');
            if ($endDateObject < $today) {
                $errors[] = 'La fecha de finalizacion debe ser igual o posterior a hoy.';
            }
        }

        if ($errors !== []) {
            Session::flash('dashboard_errors', $errors);
            Session::flash('dashboard_old', ['project' => $old]);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        try {
            $project = $this->projects->create([
                'title' => $title,
                'description' => $description !== '' ? $description : null,
                'student_id' => $studentId,
                'director_id' => (int) $user['id'],
                'status' => 'planificado',
                'start_date' => $parsedStartDate,
                'end_date' => $parsedEndDate,
            ]);
        } catch (RuntimeException $exception) {
            Session::flash('dashboard_errors', [$exception->getMessage()]);
            Session::flash('dashboard_old', ['project' => $old]);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        $this->notify(
            (int) ($project['student_id'] ?? 0),
            'project_assigned',
            'Nuevo proyecto asignado',
            sprintf(
                'Has sido asignado al proyecto "%s".',
                (string) ($project['title'] ?? 'Sin titulo')
            ),
            url('/dashboard?tab=proyectos&project=' . (int) $project['id']),
            [
                'project_id' => (int) $project['id'],
                'project_title' => $project['title'] ?? null,
                'director_id' => (int) ($project['director_id'] ?? 0),
            ]
        );

        Session::flash('dashboard_success', 'Proyecto creado correctamente.');
        Session::flash('dashboard_project_id', (int) $project['id']);
        Session::flash('dashboard_tab', 'proyectos');
        $this->redirectTo($this->dashboardRedirectUrl());
    }

    public function updateStatus(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        $projectId = (int) ($_POST['project_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if ($projectId <= 0) {
            Session::flash('dashboard_errors', ['Proyecto no valido.']);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        $project = $this->projects->find($projectId);
        if (!$project) {
            Session::flash('dashboard_errors', ['No encontramos el proyecto.']);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        $userId = (int) ($user['id'] ?? 0);
        $role = $user['role'] ?? '';

        if ($role !== 'director') {
            Session::flash('dashboard_errors', ['Solo el director puede actualizar el estado del proyecto.']);
            Session::flash('dashboard_project_id', $projectId);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        if ((int) $project['director_id'] !== $userId) {
            Session::flash('dashboard_errors', ['No tienes permisos sobre este proyecto.']);
            Session::flash('dashboard_project_id', $projectId);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        $allowedStatuses = ['planificado', 'en_progreso', 'en_riesgo', 'completado'];
        if (!in_array($status, $allowedStatuses, true)) {
            Session::flash('dashboard_errors', ['Estado de proyecto no valido.']);
            Session::flash('dashboard_project_id', $projectId);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        try {
            $this->projects->updateStatus($projectId, $status);
        } catch (RuntimeException $exception) {
            Session::flash('dashboard_errors', [$exception->getMessage()]);
            Session::flash('dashboard_project_id', $projectId);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        $statusLabels = [
            'planificado' => 'Planificado',
            'en_progreso' => 'En progreso',
            'en_riesgo' => 'En riesgo',
            'completado' => 'Completado',
        ];

        $this->notify(
            (int) ($project['student_id'] ?? 0),
            'project_status_updated',
            'Estado del proyecto actualizado',
            sprintf(
                '%s actualizó el proyecto "%s" al estado %s.',
                (string) ($user['full_name'] ?? 'El director'),
                (string) ($project['title'] ?? 'Sin titulo'),
                $statusLabels[$status] ?? $status
            ),
            url('/dashboard?tab=proyectos&project=' . $projectId),
            [
                'project_id' => $projectId,
                'project_title' => $project['title'] ?? null,
                'new_status' => $status,
            ]
        );

        Session::flash('dashboard_success', 'Estado del proyecto actualizado.');
        Session::flash('dashboard_project_id', $projectId);
        Session::flash('dashboard_tab', 'proyectos');
        $this->redirectTo($this->dashboardRedirectUrl());
    }

    public function update(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        if (($user['role'] ?? '') !== 'director') {
            Session::flash('dashboard_errors', ['No tienes permisos para actualizar proyectos.']);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        $projectId = (int) ($_POST['project_id'] ?? 0);
        $project = $projectId > 0 ? $this->projects->find($projectId) : null;

        if (!$project) {
            Session::flash('dashboard_errors', ['No encontramos el proyecto solicitado.']);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        if ((int) $project['director_id'] !== (int) ($user['id'] ?? 0)) {
            Session::flash('dashboard_errors', ['No tienes permisos sobre este proyecto.']);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $startDate = trim($_POST['start_date'] ?? '');
        $endDate = trim($_POST['end_date'] ?? '');

        $errors = [];
        $old = [
            'project_id' => $projectId,
            'title' => $title,
            'description' => $description,
            'student_id' => $studentId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        if ($title === '') {
            $errors[] = 'El titulo del proyecto es obligatorio.';
        }

        if ($studentId <= 0) {
            $errors[] = 'Selecciona un estudiante valido.';
        } else {
            $student = $this->users->findById($studentId);
            if (!$student || ($student['role'] ?? '') !== 'estudiante') {
                $errors[] = 'El estudiante seleccionado no es valido.';
            }
        }

        if ($studentId > 0 && $this->projects->studentHasActiveProject($studentId, $projectId)) {
            $errors[] = 'El estudiante ya tiene un proyecto activo asignado.';
        }

        $parsedStart = null;
        $startObject = null;
        if ($startDate === '') {
            $errors[] = 'La fecha de inicio del proyecto es obligatoria.';
        } else {
            try {
                $startObject = new DateTimeImmutable($startDate);
                $parsedStart = $startObject->format('Y-m-d');
            } catch (RuntimeException) {
                $errors[] = 'La fecha de inicio del proyecto no es valida.';
            } catch (\Exception) {
                $errors[] = 'La fecha de inicio del proyecto no es valida.';
            }
        }

        $parsedEnd = null;
        $endObject = null;
        if ($endDate === '') {
            $errors[] = 'La fecha de finalizacion del proyecto es obligatoria.';
        } else {
            try {
                $endObject = new DateTimeImmutable($endDate);
                $parsedEnd = $endObject->format('Y-m-d');
            } catch (RuntimeException) {
                $errors[] = 'La fecha de finalizacion del proyecto no es valida.';
            } catch (\Exception) {
                $errors[] = 'La fecha de finalizacion del proyecto no es valida.';
            }
        }

        if ($startObject && $endObject && $endObject < $startObject) {
            $errors[] = 'La fecha de finalizacion debe ser posterior o igual a la fecha de inicio.';
        }

        if ($errors !== []) {
            Session::flash('dashboard_errors', $errors);
            Session::flash('dashboard_old', ['project_edit' => $old, 'project_edit_id' => $projectId]);
            Session::flash('dashboard_project_id', $projectId);
            Session::flash('dashboard_tab', 'proyectos');
            Session::flash('dashboard_modal', 'modalProjectEdit');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        $previousStudentId = (int) ($project['student_id'] ?? 0);

        try {
            $updated = $this->projects->update($projectId, [
                'title' => $title,
                'description' => $description !== '' ? $description : null,
                'student_id' => $studentId,
                'start_date' => $parsedStart,
                'end_date' => $parsedEnd,
            ]);
        } catch (RuntimeException $exception) {
            Session::flash('dashboard_errors', [$exception->getMessage()]);
            Session::flash('dashboard_old', ['project_edit' => $old, 'project_edit_id' => $projectId]);
            Session::flash('dashboard_project_id', $projectId);
            Session::flash('dashboard_tab', 'proyectos');
            Session::flash('dashboard_modal', 'modalProjectEdit');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        $newStudentId = (int) ($updated['student_id'] ?? 0);

        if ($newStudentId > 0) {
            $this->notify(
                $newStudentId,
                'project_updated',
                'Detalles del proyecto actualizados',
                sprintf(
                    'Se actualizaron los detalles del proyecto "%s".',
                    (string) ($updated['title'] ?? 'Sin titulo')
                ),
                url('/dashboard?tab=proyectos&project=' . (int) $updated['id']),
                [
                    'project_id' => (int) $updated['id'],
                    'project_title' => $updated['title'] ?? null,
                    'changed_fields' => ['title', 'description', 'fechas'],
                ]
            );
        }

        if ($previousStudentId > 0 && $previousStudentId !== $newStudentId) {
            $this->notify(
                $previousStudentId,
                'project_unassigned',
                'Proyecto reasignado',
                sprintf(
                    'Ya no estas asignado al proyecto "%s".',
                    (string) ($project['title'] ?? 'Sin titulo')
                ),
                url('/dashboard?tab=proyectos'),
                [
                    'project_id' => (int) $project['id'],
                    'project_title' => $project['title'] ?? null,
                    'reassigned' => true,
                ]
            );
        }

        Session::flash('dashboard_success', 'Proyecto actualizado correctamente.');
        Session::flash('dashboard_project_id', (int) $updated['id']);
        Session::flash('dashboard_tab', 'proyectos');
        $this->redirectTo($this->dashboardRedirectUrl());
    }

    public function destroy(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        if (($user['role'] ?? '') !== 'director') {
            Session::flash('dashboard_errors', ['No tienes permisos para eliminar proyectos.']);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        $projectId = (int) ($_POST['project_id'] ?? 0);
        $project = $projectId > 0 ? $this->projects->find($projectId) : null;

        if (!$project) {
            Session::flash('dashboard_errors', ['No encontramos el proyecto indicado.']);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        if ((int) $project['director_id'] !== (int) ($user['id'] ?? 0)) {
            Session::flash('dashboard_errors', ['No tienes permisos sobre este proyecto.']);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        try {
            $this->projects->delete($projectId);
        } catch (RuntimeException $exception) {
            Session::flash('dashboard_errors', [$exception->getMessage()]);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        $this->notify(
            (int) ($project['student_id'] ?? 0),
            'project_deleted',
            'Proyecto eliminado',
            sprintf(
                'El director eliminó el proyecto "%s".',
                (string) ($project['title'] ?? 'Sin titulo')
            ),
            url('/dashboard?tab=proyectos'),
            [
                'project_id' => $projectId,
                'project_title' => $project['title'] ?? null,
                'deleted' => true,
            ]
        );

        Session::flash('dashboard_success', 'Proyecto eliminado correctamente.');
        Session::flash('dashboard_project_id', 0);
        Session::flash('dashboard_tab', 'proyectos');
        $this->redirectTo($this->dashboardRedirectUrl());
    }

    private function dashboardRedirectUrl(): string
    {
        $url = $this->buildDashboardUrl(
            $_POST['return_tab'] ?? null,
            $_POST['return_project'] ?? null,
            $_POST['return_anchor'] ?? null
        );

        return $url ?? '/dashboard';
    }

    private function buildDashboardUrl($tab, $project, $anchor): ?string
    {
        if (!is_string($tab)) {
            return null;
        }

        $tab = trim($tab);
        if ($tab === '' || !preg_match('/^[a-z0-9_-]+$/i', $tab)) {
            return null;
        }

        $projectId = null;
        if (is_string($project) || is_int($project)) {
            $projectString = trim((string) $project);
            if ($projectString !== '' && ctype_digit($projectString)) {
                $candidate = (int) $projectString;
                if ($candidate > 0) {
                    $projectId = $candidate;
                }
            }
        }

        $params = ['tab' => $tab];
        if ($projectId !== null) {
            $params['project'] = $projectId;
        }

        $url = '/dashboard?' . http_build_query($params);

        $anchorValue = null;
        if (is_string($anchor)) {
            $anchorCandidate = trim($anchor);
            if ($anchorCandidate !== '' && preg_match('/^[A-Za-z0-9_-]+$/', $anchorCandidate)) {
                $anchorValue = $anchorCandidate;
            }
        }

        if ($anchorValue !== null) {
            $url .= '#' . $anchorValue;
        }

        return $url;
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
            // Evitar que fallas de notificación detengan el flujo principal
        }
    }
}
