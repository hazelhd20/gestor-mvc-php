<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\Project;
use App\Models\User;
use DateInterval;
use DateTimeImmutable;

class DashboardController extends Controller
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
        $currentUser = Session::user();
        if (!$currentUser) {
            Session::flash('errors', ['login_general' => 'Debes iniciar sesion para continuar.']);
            Session::flash('tab', 'login');
            $this->redirectTo('/');
        }

        $projects = $currentUser['role'] === 'director'
            ? $this->projects->allForDirector((int) $currentUser['id'])
            : $this->projects->allForStudent((int) $currentUser['id']);

        $students = $currentUser['role'] === 'director'
            ? $this->users->allByRole('estudiante')
            : [];

        $context = $this->buildContext($projects);

        $this->render('dashboard/index', array_merge($context, [
            'user' => $currentUser,
            'students' => $students,
            'success' => Session::flash('success'),
            'projectErrors' => Session::flash('project_errors') ?? [],
            'projectOld' => Session::flash('project_old') ?? [],
        ]));
    }

    private function buildContext(array $projects): array
    {
        $statusKeys = ['planeacion', 'en_progreso', 'en_revision', 'finalizado'];
        $statusSummary = array_fill_keys($statusKeys, 0);
        $kanban = array_fill_keys($statusKeys, []);

        $today = new DateTimeImmutable('today');
        $dueSoonThreshold = $today->add(new DateInterval('P7D'));

        $activeCount = 0;
        $dueSoon = 0;
        $upcoming = [];

        foreach ($projects as $project) {
            $status = $project['status'] ?? 'planeacion';
            if (!array_key_exists($status, $statusSummary)) {
                $status = 'planeacion';
            }

            $statusSummary[$status]++;
            $kanban[$status][] = $project;

            if ($status !== 'finalizado') {
                $activeCount++;
            }

            if (!empty($project['due_date'])) {
                $dueDate = DateTimeImmutable::createFromFormat('Y-m-d', $project['due_date']);
                if ($dueDate instanceof DateTimeImmutable) {
                    if ($dueDate >= $today && $dueDate <= $dueSoonThreshold && $status !== 'finalizado') {
                        $dueSoon++;
                    }

                    $upcoming[] = [
                        'project' => $project,
                        'due_date' => $dueDate,
                    ];
                }
            }
        }

        usort($upcoming, static fn (array $a, array $b): int => $a['due_date'] <=> $b['due_date']);
        $upcoming = array_slice($upcoming, 0, 5);

        $summary = [
            'total' => count($projects),
            'active' => $activeCount,
            'finished' => $statusSummary['finalizado'] ?? 0,
            'dueSoon' => $dueSoon,
        ];

        return [
            'projects' => $projects,
            'summary' => $summary,
            'statusSummary' => $statusSummary,
            'kanban' => $kanban,
            'upcoming' => $upcoming,
        ];
    }
}
