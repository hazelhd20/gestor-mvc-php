<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\Project;
use App\Models\User;
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

    public function index(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        $projects = $this->projects->allForUser($user);
        $students = $user['role'] === 'director' ? $this->users->allByRole('estudiante') : [];
        $directors = $user['role'] === 'estudiante' ? $this->users->allByRole('director') : [];

        $relatedUserIds = [];
        foreach ($projects as $project) {
            $relatedUserIds[] = (int) $project['student_id'];
            $relatedUserIds[] = (int) $project['director_id'];
        }
        $peopleMap = $this->users->findManyByIds($relatedUserIds);

        $this->render('projects/index', [
            'user' => $user,
            'projects' => $projects,
            'students' => $students,
            'directors' => $directors,
            'peopleMap' => $peopleMap,
            'errors' => Session::flash('project_errors') ?? [],
            'old' => Session::flash('project_old') ?? [],
            'statusMessage' => Session::flash('project_status'),
        ]);
    }

    public function store(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $directorId = (int) ($_POST['director_id'] ?? 0);

        $errors = [];
        if ($title === '') {
            $errors['title'] = 'El titulo es obligatorio.';
        }

        if ($user['role'] === 'director') {
            if ($studentId <= 0) {
                $errors['student_id'] = 'Selecciona al estudiante responsable.';
            }
            $directorId = (int) $user['id'];
        } else {
            if ($directorId <= 0) {
                $errors['director_id'] = 'Selecciona al director responsable.';
            }
            $studentId = (int) $user['id'];
        }

        $student = $studentId > 0 ? $this->users->findById($studentId) : null;
        if (!$student || ($student['role'] ?? '') !== 'estudiante') {
            $errors['student_id'] = 'Selecciona un estudiante valido.';
        }

        $director = $directorId > 0 ? $this->users->findById($directorId) : null;
        if (!$director || ($director['role'] ?? '') !== 'director') {
            $errors['director_id'] = 'Selecciona un director valido.';
        }

        if ($user['role'] === 'estudiante' && $director && (int) $director['id'] === (int) $studentId) {
            $errors['director_id'] = 'No puedes asignarte a ti mismo como director.';
        }

        if ($errors) {
            Session::flash('project_errors', $errors);
            Session::flash('project_old', [
                'title' => $title,
                'description' => $description,
                'student_id' => $user['role'] === 'director' ? $studentId : null,
                'director_id' => $user['role'] === 'estudiante' ? $directorId : null,
            ]);
            $this->redirectTo('/projects');
        }

        try {
            $this->projects->create([
                'title' => $title,
                'description' => $description,
                'student_id' => $studentId,
                'director_id' => $directorId,
            ]);
        } catch (RuntimeException $exception) {
            Session::flash('project_errors', ['general' => $exception->getMessage()]);
            Session::flash('project_old', [
                'title' => $title,
                'description' => $description,
                'student_id' => $user['role'] === 'director' ? $studentId : null,
                'director_id' => $user['role'] === 'estudiante' ? $directorId : null,
            ]);
            $this->redirectTo('/projects');
        }

        Session::flash('project_status', 'Proyecto registrado correctamente.');
        $this->redirectTo('/projects');
    }

    public function updateStatus(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        $projectId = (int) ($_POST['project_id'] ?? 0);
        $status = $_POST['status'] ?? 'planeacion';

        $project = $this->projects->find($projectId);
        if (!$project || (int) $project['director_id'] !== (int) $user['id']) {
            Session::flash('project_status', 'No tienes permisos para actualizar este proyecto.');
            $this->redirectTo('/projects');
        }

        $allowedStatus = ['planeacion', 'en_progreso', 'en_revision', 'finalizado'];
        if (!in_array($status, $allowedStatus, true)) {
            $status = 'planeacion';
        }

        $this->projects->updateStatus($projectId, $status);
        Session::flash('project_status', 'Estado del proyecto actualizado.');
        $this->redirectTo('/projects');
    }
}