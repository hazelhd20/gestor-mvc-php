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
        $dueDate = trim($_POST['due_date'] ?? '');

        $errors = [];
        $old = [
            'title' => $title,
            'description' => $description,
            'student_id' => $studentId,
            'due_date' => $dueDate,
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

        $parsedDueDate = null;
        if ($dueDate !== '') {
            try {
                $parsedDueDate = (new DateTimeImmutable($dueDate))->format('Y-m-d');
            } catch (RuntimeException) {
                $errors[] = 'La fecha de entrega no es valida.';
            } catch (\Exception) {
                $errors[] = 'La fecha de entrega no es valida.';
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
                'due_date' => $parsedDueDate,
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

        $ownsProject = ($role === 'director' && (int) $project['director_id'] === $userId)
            || ($role === 'estudiante' && (int) $project['student_id'] === $userId);

        if (!$ownsProject) {
            Session::flash('dashboard_errors', ['No tienes permisos sobre este proyecto.']);
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
}
