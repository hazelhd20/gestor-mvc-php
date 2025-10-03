<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\Project;
use App\Models\User;
use DateTimeImmutable;
use RuntimeException;

class ProjectsController extends Controller
{
    private Project $projects;
    private User $users;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->projects = new Project();
        $this->users = new User();
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
            $this->redirectTo('/dashboard');
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
            $this->redirectTo('/dashboard');
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
            $this->redirectTo('/dashboard');
        }

        Session::flash('dashboard_success', 'Proyecto creado correctamente.');
        Session::flash('dashboard_project_id', (int) $project['id']);
        Session::flash('dashboard_tab', 'proyectos');
        $this->redirectTo('/dashboard');
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
            $this->redirectTo('/dashboard');
        }

        $project = $this->projects->find($projectId);
        if (!$project) {
            Session::flash('dashboard_errors', ['No encontramos el proyecto.']);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo('/dashboard');
        }

        $userId = (int) ($user['id'] ?? 0);
        $role = $user['role'] ?? '';

        if ($role !== 'director') {
            Session::flash('dashboard_errors', ['Solo el director puede actualizar el estado del proyecto.']);
            Session::flash('dashboard_project_id', $projectId);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo('/dashboard');
        }

        if ((int) $project['director_id'] !== $userId) {
            Session::flash('dashboard_errors', ['No tienes permisos sobre este proyecto.']);
            Session::flash('dashboard_project_id', $projectId);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo('/dashboard');
        }

        $allowedStatuses = ['planificado', 'en_progreso', 'en_riesgo', 'completado'];
        if (!in_array($status, $allowedStatuses, true)) {
            Session::flash('dashboard_errors', ['Estado de proyecto no valido.']);
            Session::flash('dashboard_project_id', $projectId);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo('/dashboard');
        }

        try {
            $this->projects->updateStatus($projectId, $status);
        } catch (RuntimeException $exception) {
            Session::flash('dashboard_errors', [$exception->getMessage()]);
            Session::flash('dashboard_project_id', $projectId);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo('/dashboard');
        }

        Session::flash('dashboard_success', 'Estado del proyecto actualizado.');
        Session::flash('dashboard_project_id', $projectId);
        Session::flash('dashboard_tab', 'proyectos');
        $this->redirectTo('/dashboard');
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
            $this->redirectTo('/dashboard');
        }

        $projectId = (int) ($_POST['project_id'] ?? 0);
        $project = $projectId > 0 ? $this->projects->find($projectId) : null;

        if (!$project) {
            Session::flash('dashboard_errors', ['No encontramos el proyecto solicitado.']);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo('/dashboard');
        }

        if ((int) $project['director_id'] !== (int) ($user['id'] ?? 0)) {
            Session::flash('dashboard_errors', ['No tienes permisos sobre este proyecto.']);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo('/dashboard');
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
            $this->redirectTo('/dashboard');
        }

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
            $this->redirectTo('/dashboard');
        }

        Session::flash('dashboard_success', 'Proyecto actualizado correctamente.');
        Session::flash('dashboard_project_id', (int) $updated['id']);
        Session::flash('dashboard_tab', 'proyectos');
        $this->redirectTo('/dashboard');
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
            $this->redirectTo('/dashboard');
        }

        $projectId = (int) ($_POST['project_id'] ?? 0);
        $project = $projectId > 0 ? $this->projects->find($projectId) : null;

        if (!$project) {
            Session::flash('dashboard_errors', ['No encontramos el proyecto indicado.']);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo('/dashboard');
        }

        if ((int) $project['director_id'] !== (int) ($user['id'] ?? 0)) {
            Session::flash('dashboard_errors', ['No tienes permisos sobre este proyecto.']);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo('/dashboard');
        }

        try {
            $this->projects->delete($projectId);
        } catch (RuntimeException $exception) {
            Session::flash('dashboard_errors', [$exception->getMessage()]);
            Session::flash('dashboard_tab', 'proyectos');
            $this->redirectTo('/dashboard');
        }

        Session::flash('dashboard_success', 'Proyecto eliminado correctamente.');
        Session::flash('dashboard_project_id', 0);
        Session::flash('dashboard_tab', 'proyectos');
        $this->redirectTo('/dashboard');
    }

}
