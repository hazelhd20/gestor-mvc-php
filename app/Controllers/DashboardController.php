<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\Deliverable;
use App\Models\Feedback;
use App\Models\Milestone;
use App\Models\Notification;
use App\Models\Project;
use App\Models\User;

class DashboardController extends Controller
{
    private Project $projects;
    private Milestone $milestones;
    private Deliverable $deliverables;
    private Feedback $feedback;
    private User $users;
    private Notification $notifications;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->projects = new Project();
        $this->milestones = new Milestone();
        $this->deliverables = new Deliverable();
        $this->feedback = new Feedback();
        $this->users = new User();
        $this->notifications = new Notification();
    }

    public function index(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        $errors = Session::flash('dashboard_errors') ?? [];
        $success = Session::flash('dashboard_success');
        $old = Session::flash('dashboard_old') ?? [];
        $modalTarget = Session::flash('dashboard_modal');
        $requestedTab = $_GET['tab'] ?? null;
        $activeTab = Session::flash('dashboard_tab') ?? $requestedTab ?? 'dashboard';
        $activeTab = $this->sanitizeTab($activeTab);

        $projects = $this->projects->allForUser($user);
        $projectIds = array_map(static fn ($project) => (int) $project['id'], $projects);
        $selectedProjectId = (int) ($_GET['project'] ?? 0);
        $selectedProjectId = Session::flash('dashboard_project_id') ?? $selectedProjectId;

        if ($selectedProjectId && !in_array((int) $selectedProjectId, $projectIds, true)) {
            $selectedProjectId = 0;
        }

        if ($selectedProjectId === 0 && $projectIds !== []) {
            $selectedProjectId = $projectIds[0];
        }

        $selectedProject = null;
        $projectMilestones = [];
        $deliverablesByMilestone = [];
        $feedbackByMilestone = [];

        if ($selectedProjectId) {
            $selectedProject = $this->projects->find($selectedProjectId);
            if ($selectedProject) {
                $projectMilestones = $this->milestones->forProject($selectedProjectId);
                $milestoneIds = array_map(static fn ($milestone) => (int) $milestone['id'], $projectMilestones);
                $deliverablesByMilestone = $this->deliverables->forMilestones($milestoneIds);
                $feedbackByMilestone = $this->feedback->forMilestones($milestoneIds);
            }
        }

        $stats = $this->projects->statsForUser($user);
        $upcomingMilestones = $this->projects->upcomingMilestones($user);
        $recentFeedback = $this->feedback->recentForUser($user);
        $boardColumns = $this->projects->boardColumns($user, $selectedProjectId ?: null);

        $notifications = $this->notifications->allForUser((int) ($user['id'] ?? 0), 15);
        $unreadNotifications = $this->notifications->countUnreadForUser((int) ($user['id'] ?? 0));

        $students = [];
        if (($user['role'] ?? '') === 'director') {
            $students = $this->users->allByRole('estudiante');
        }

        $this->render('dashboard/index', [
            'user' => $user,
            'errors' => $errors,
            'success' => $success,
            'old' => $old,
            'activeTab' => $activeTab,
            'projects' => $projects,
            'selectedProject' => $selectedProject,
            'projectMilestones' => $projectMilestones,
            'deliverablesByMilestone' => $deliverablesByMilestone,
            'feedbackByMilestone' => $feedbackByMilestone,
            'stats' => $stats,
            'upcomingMilestones' => $upcomingMilestones,
            'recentFeedback' => $recentFeedback,
            'boardColumns' => $boardColumns,
            'students' => $students,
            'modalTarget' => $modalTarget,
            'notifications' => $notifications,
            'unreadNotificationCount' => $unreadNotifications,
        ]);
    }

    private function sanitizeTab(string $tab): string
    {
        $allowed = ['dashboard', 'proyectos', 'hitos', 'comentarios', 'progreso'];
        return in_array($tab, $allowed, true) ? $tab : 'dashboard';
    }
}
