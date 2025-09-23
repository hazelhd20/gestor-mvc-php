<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\Project;
use App\Models\User;
use DateTimeImmutable;
use RuntimeException;

class ProjectController extends Controller
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
        $currentUser = Session::user();
        if (!$currentUser) {
            Session::flash('errors', ['login_general' => 'Debes iniciar sesion para continuar.']);
            Session::flash('tab', 'login');
            $this->redirectTo('/');
        }

        if ($currentUser['role'] !== 'director') {
            Session::flash('project_errors', ['general' => 'No tienes permisos para crear proyectos.']);
            $this->redirectTo('/dashboard');
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $dueDate = trim($_POST['due_date'] ?? '');

        $errors = [];

        if ($title === '') {
            $errors['title'] = 'El titulo es obligatorio.';
        }

        $student = $this->users->findById($studentId);
        if (!$student || $student['role'] !== 'estudiante') {
            $errors['student_id'] = 'Selecciona un estudiante valido.';
        }

        if ($dueDate !== '') {
            $isValid = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate) === 1;
            if (!$isValid) {
                $errors['due_date'] = 'Usa el formato AAAA-MM-DD.';
            } elseif (!DateTimeImmutable::createFromFormat('Y-m-d', $dueDate)) {
                $errors['due_date'] = 'La fecha limite no es valida.';
            }
        } else {
            $dueDate = null;
        }

        $old = [
            'title' => $title,
            'description' => $description,
            'student_id' => $studentId,
            'due_date' => $dueDate,
        ];

        if ($errors) {
            Session::flash('project_errors', $errors);
            Session::flash('project_old', $old);
            $this->redirectTo('/dashboard');
        }

        try {
            $this->projects->create([
                'title' => $title,
                'description' => $description,
                'due_date' => $dueDate,
                'status' => 'planeacion',
                'director_id' => (int) $currentUser['id'],
                'student_id' => $studentId,
            ]);
        } catch (RuntimeException $exception) {
            Session::flash('project_errors', ['general' => $exception->getMessage()]);
            Session::flash('project_old', $old);
            $this->redirectTo('/dashboard');
        }

        Session::flash('success', 'Proyecto creado correctamente.');
        $this->redirectTo('/dashboard');
    }
}
