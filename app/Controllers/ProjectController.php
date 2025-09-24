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

    public function index(): void
    {
        $currentUser = $this->requireUser();

        $projects = $currentUser['role'] === 'director'
            ? $this->projects->allForDirector((int) $currentUser['id'])
            : $this->projects->allForStudent((int) $currentUser['id']);

        $students = $currentUser['role'] === 'director'
            ? $this->users->allByRole('estudiante')
            : [];

        $statusCatalog = Project::statusCatalog();
        $statusSummary = array_fill_keys(array_keys($statusCatalog), 0);

        foreach ($projects as $project) {
            $status = $project['status'] ?? 'planeacion';
            if (!array_key_exists($status, $statusSummary)) {
                $status = 'planeacion';
            }

            $statusSummary[$status]++;
        }

        $upcoming = $this->nextDeadlines($projects);

        $this->render('projects/index', [
            'user' => $currentUser,
            'projects' => $projects,
            'students' => $students,
            'statusCatalog' => $statusCatalog,
            'statusSummary' => $statusSummary,
            'upcoming' => $upcoming,
            'success' => Session::flash('success'),
            'projectErrors' => Session::flash('project_errors') ?? [],
            'projectOld' => Session::flash('project_old') ?? [],
        ]);
    }

    public function store(): void
    {
        $currentUser = $this->requireUser();

        if (($currentUser['role'] ?? '') !== 'director') {
            Session::flash('project_errors', ['general' => 'No tienes permisos para crear proyectos.']);
            $this->redirectTo('/projects');
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $rawDueDate = trim((string) ($_POST['due_date'] ?? ''));

        $errors = [];

        if ($title === '') {
            $errors['title'] = 'El titulo es obligatorio.';
        }

        $student = $this->users->findById($studentId);
        if (!$student || ($student['role'] ?? '') !== 'estudiante') {
            $errors['student_id'] = 'Selecciona un estudiante valido.';
        }

        $dueDate = null;
        if ($rawDueDate !== '') {
            $isValid = preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDueDate) === 1;
            if (!$isValid) {
                $errors['due_date'] = 'Usa el formato AAAA-MM-DD.';
            } else {
                $parsedDueDate = DateTimeImmutable::createFromFormat('Y-m-d', $rawDueDate);
                if ($parsedDueDate === false) {
                    $errors['due_date'] = 'La fecha limite no es valida.';
                } else {
                    $dueDate = $parsedDueDate->format('Y-m-d');
                }
            }
        }

        $old = [
            'title' => $title,
            'description' => $description,
            'student_id' => $studentId,
            'due_date' => $rawDueDate,
        ];

        if ($errors) {
            Session::flash('project_errors', $errors);
            Session::flash('project_old', $old);
            $this->redirectTo('/projects');
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
            $this->redirectTo('/projects');
        }

        Session::flash('success', 'Proyecto creado correctamente.');
        $this->redirectTo('/projects');
    }

    private function requireUser(): array
    {
        $currentUser = Session::user();
        if ($currentUser) {
            return $currentUser;
        }

        Session::flash('errors', ['login_general' => 'Debes iniciar sesion para continuar.']);
        Session::flash('tab', 'login');
        $this->redirectTo('/');

        return [];
    }

    private function nextDeadlines(array $projects): array
    {
        $upcoming = [];

        foreach ($projects as $project) {
            if (empty($project['due_date'])) {
                continue;
            }

            $dueDate = DateTimeImmutable::createFromFormat('Y-m-d', (string) $project['due_date']);
            if (!$dueDate instanceof DateTimeImmutable) {
                continue;
            }

            $upcoming[] = [
                'project' => $project,
                'due_date' => $dueDate,
            ];
        }

        usort($upcoming, static fn (array $a, array $b): int => $a['due_date'] <=> $b['due_date']);

        return array_slice($upcoming, 0, 5);
    }
}

