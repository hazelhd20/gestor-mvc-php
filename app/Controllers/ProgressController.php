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
        $kanbanColumns = [
            'pendiente' => [],
            'en_progreso' => [],
            'en_revision' => [],
            'completado' => [],
        ];
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

                if (!array_key_exists($milestone['status'], $kanbanColumns)) {
                    $kanbanColumns[$milestone['status']] = [];
                }

                $kanbanColumns[$milestone['status']][] = [
                    'title' => $milestone['title'],
                    'project' => $project['title'],
                    'due_date' => $milestone['due_date'],
                    'updated_at' => $milestone['updated_at'],
                ];

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
                        $lastIndex = array_key_last($kanbanColumns[$milestone['status']]);
                        if ($lastIndex !== null) {
                            $kanbanColumns[$milestone['status']][$lastIndex]['overdue'] = $dueDate < $today;
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
        usort(
            $upcoming,
            static fn ($a, $b) => strcmp((string) ($a['due_date'] ?? ''), (string) ($b['due_date'] ?? ''))
        );
        $upcoming = array_slice($upcoming, 0, 6);

        usort(
            $recent,
            static fn ($a, $b) => strcmp((string) ($b['updated_at'] ?? ''), (string) ($a['updated_at'] ?? ''))
        );
        $recent = array_slice($recent, 0, 6);

        foreach ($kanbanColumns as &$column) {
            usort(
                $column,
                static function (array $a, array $b): int {
                    $dateA = (string) ($a['due_date'] ?? '');
                    $dateB = (string) ($b['due_date'] ?? '');

                    return strcmp($dateA, $dateB);
                }
            );
        }
        unset($column);

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
            'kanbanColumns' => $kanbanColumns,
        ]);
    }
}