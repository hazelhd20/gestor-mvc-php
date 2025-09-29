<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\Milestone;
use App\Models\Project;
use DateInterval;
use DateTimeImmutable;

class ProgressController extends Controller
{
    private Project $projects;
    private Milestone $milestones;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->projects = new Project();
        $this->milestones = new Milestone();
    }

    public function index(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        $projects = $this->projects->allForUser($user);
        $statusCounts = [
            'planeacion' => 0,
            'en_progreso' => 0,
            'en_revision' => 0,
            'finalizado' => 0,
        ];

        $milestoneCounts = [
            'total' => 0,
            'pendiente' => 0,
            'en_progreso' => 0,
            'en_revision' => 0,
            'completado' => 0,
        ];

        $projectsProgress = [];
        $upcoming = [];
        $recent = [];
        $today = new DateTimeImmutable('today');
        $soon = $today->add(new DateInterval('P7D'));

        foreach ($projects as $project) {
            $statusCounts[$project['status']] = ($statusCounts[$project['status']] ?? 0) + 1;

            $milestones = $this->milestones->allForProject((int) $project['id']);
            $total = count($milestones);
            $done = 0;
            $waitingReview = 0;

            foreach ($milestones as $milestone) {
                $milestoneCounts['total']++;
                $milestoneCounts[$milestone['status']] = ($milestoneCounts[$milestone['status']] ?? 0) + 1;

                if ($milestone['status'] === 'completado') {
                    $done++;
                    if (!empty($milestone['updated_at'])) {
                        $recent[] = [
                            'title' => $milestone['title'],
                            'project' => $project['title'],
                            'updated_at' => $milestone['updated_at'],
                        ];
                    }
                }

                if ($milestone['status'] === 'en_revision') {
                    $waitingReview++;
                }

                if (!empty($milestone['due_date'])) {
                    $dueDate = DateTimeImmutable::createFromFormat('Y-m-d', $milestone['due_date']);
                    if ($dueDate instanceof DateTimeImmutable) {
                        if ($dueDate <= $soon) {
                            $upcoming[] = [
                                'title' => $milestone['title'],
                                'project' => $project['title'],
                                'due_date' => $milestone['due_date'],
                                'status' => $milestone['status'],
                                'overdue' => $dueDate < $today,
                            ];
                        }
                    }
                }
            }

            $progressPercent = $total > 0 ? round(($done / $total) * 100) : 0;
            $projectsProgress[] = [
                'id' => (int) $project['id'],
                'title' => $project['title'],
                'status' => $project['status'],
                'total' => $total,
                'done' => $done,
                'waiting_review' => $waitingReview,
                'progress' => $progressPercent,
            ];
        }

        usort($projectsProgress, static fn ($a, $b) => $a['progress'] <=> $b['progress']);
        usort($upcoming, static fn ($a, $b) => strcmp($a['due_date'], $b['due_date']));
        $upcoming = array_slice($upcoming, 0, 6);

        usort($recent, static fn ($a, $b) => strcmp($b['updated_at'], $a['updated_at']));
        $recent = array_slice($recent, 0, 6);

        $completionRate = $milestoneCounts['total'] > 0
            ? round(($milestoneCounts['completado'] / $milestoneCounts['total']) * 100)
            : 0;

        $this->render('progress/index', [
            'user' => $user,
            'projects' => $projects,
            'projectsProgress' => $projectsProgress,
            'statusCounts' => $statusCounts,
            'milestoneCounts' => $milestoneCounts,
            'completionRate' => $completionRate,
            'upcoming' => $upcoming,
            'recent' => $recent,
        ]);
    }
}