<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\Milestone;
use App\Models\Project;
use DateTimeImmutable;
use RuntimeException;

class MilestonesController extends Controller
{
    private Milestone $milestones;
    private Project $projects;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->milestones = new Milestone();
        $this->projects = new Project();
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
        $dueDate = trim($_POST['due_date'] ?? '');

        $errors = [];
        $old = [
            'project_id' => $projectId,
            'title' => $title,
            'description' => $description,
            'due_date' => $dueDate,
        ];

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

        $parsedDueDate = null;
        if ($dueDate !== '') {
            try {
                $parsedDueDate = (new DateTimeImmutable($dueDate))->format('Y-m-d');
            } catch (RuntimeException) {
                $errors[] = 'La fecha limite del hito no es valida.';
            } catch (\Exception) {
                $errors[] = 'La fecha limite del hito no es valida.';
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
                'due_date' => $parsedDueDate,
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
        $allowed = ($role === 'director' && (int) $project['director_id'] === $userId)
            || ($role === 'estudiante' && (int) $project['student_id'] === $userId);

        if (!$allowed) {
            Session::flash('dashboard_errors', ['No tienes permisos sobre ese hito.']);
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

        Session::flash('dashboard_success', 'Estado del hito actualizado.');
        Session::flash('dashboard_project_id', (int) $project['id']);
        Session::flash('dashboard_tab', 'hitos');
        $this->redirectTo('/dashboard');
    }
}
