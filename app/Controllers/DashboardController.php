<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\Project;
use DateInterval;
use DateTimeImmutable;

class DashboardController extends Controller
{
    private Project $projects;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->projects = new Project();
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

        $context = $this->buildContext($projects);

        $this->render('dashboard/index', array_merge($context, [
            'user' => $currentUser,
            'success' => Session::flash('success'),
        ]));
    }

    private function buildContext(array $projects): array
    {
        $statusKeys = ['planeacion', 'en_progreso', 'en_revision', 'finalizado'];
        $statusSummary = array_fill_keys($statusKeys, 0);

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
            'recentProjects' => array_slice($projects, 0, 5),
            'summary' => $summary,
            'statusSummary' => $statusSummary,
            'upcoming' => $upcoming,
        ];
    }
}

