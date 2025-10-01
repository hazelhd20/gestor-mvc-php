<?php

declare(strict_types=1);

header('Content-Type: text/html; charset=UTF-8');

require __DIR__ . '/helpers.php';
require __DIR__ . '/components.php';

$viewData = dashboard_build_view_model([
    'user' => $user ?? [],
    'errors' => $errors ?? [],
    'success' => $success ?? null,
    'old' => $old ?? [],
    'activeTab' => $activeTab ?? 'dashboard',
    'projects' => $projects ?? [],
    'selectedProject' => $selectedProject ?? null,
    'projectMilestones' => $projectMilestones ?? [],
    'deliverablesByMilestone' => $deliverablesByMilestone ?? [],
    'feedbackByMilestone' => $feedbackByMilestone ?? [],
    'stats' => $stats ?? [],
    'upcomingMilestones' => $upcomingMilestones ?? [],
    'recentFeedback' => $recentFeedback ?? [],
    'boardColumns' => $boardColumns ?? [],
    'students' => $students ?? [],
]);

render_dashboard_page($viewData);
