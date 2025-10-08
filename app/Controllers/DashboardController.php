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
    private const MODULE_CONFIG = [
        'dashboard-overview' => ['component' => 'dashboard-overview', 'section' => 'dashboard', 'tab' => 'dashboard'],
        'projects' => ['component' => 'projects', 'section' => 'proyectos', 'tab' => 'proyectos'],
        'milestones' => ['component' => 'milestones', 'section' => 'hitos', 'tab' => 'hitos'],
        'comments' => ['component' => 'comments', 'section' => 'comentarios', 'tab' => 'comentarios'],
        'progress' => ['component' => 'progress', 'section' => 'progreso', 'tab' => 'progreso'],
    ];

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
        $activeTabCandidate = Session::flash('dashboard_tab') ?? $requestedTab ?? 'dashboard';

        $selectedProjectCandidate = Session::flash('dashboard_project_id');
        if ($selectedProjectCandidate === null) {
            $selectedProjectCandidate = (int) ($_GET['project'] ?? 0);
        } else {
            $selectedProjectCandidate = (int) $selectedProjectCandidate;
        }

        $dashboardData = $this->buildDashboardData($user, [
            'activeTab' => $activeTabCandidate,
            'selectedProjectId' => $selectedProjectCandidate,
        ]);

        $notifications = $this->notifications->allForUser((int) ($user['id'] ?? 0), 15);
        $unreadNotifications = $this->notifications->countUnreadForUser((int) ($user['id'] ?? 0));

        $viewData = array_merge(
            [
                'user' => $user,
                'errors' => $errors,
                'success' => $success,
                'old' => $old,
                'modalTarget' => $modalTarget,
                'notifications' => $notifications,
                'unreadNotificationCount' => $unreadNotifications,
            ],
            $dashboardData
        );

        $this->render('dashboard/index', $viewData);
    }

    public function modules(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->json(['error' => 'No autorizado'], 401);
            return;
        }

        $module = isset($_GET['module']) ? trim((string) $_GET['module']) : '';
        if ($module === '' || !isset(self::MODULE_CONFIG[$module])) {
            $this->json(['error' => 'Modulo no disponible'], 400);
            return;
        }

        $config = self::MODULE_CONFIG[$module];
        $projectId = isset($_GET['project']) ? (int) $_GET['project'] : 0;

        $requestedTab = isset($_GET['tab']) ? (string) $_GET['tab'] : null;
        $activeTab = $requestedTab !== null ? $this->sanitizeTab($requestedTab) : $config['tab'];

        $dashboardData = $this->buildDashboardData($user, [
            'activeTab' => $activeTab,
            'selectedProjectId' => $projectId,
        ]);

        $context = array_merge(
            $dashboardData,
            [
                'user' => $user,
                'errors' => [],
                'success' => null,
                'old' => [],
                'modalTarget' => null,
                'notifications' => $this->notifications->allForUser((int) ($user['id'] ?? 0), 15),
                'unreadNotificationCount' => $this->notifications->countUnreadForUser((int) ($user['id'] ?? 0)),
            ]
        );

        $this->ensureDashboardHelpersLoaded();

        try {
            $viewData = dashboard_build_view_model($context);
        } catch (\Throwable $exception) {
            $this->json(['error' => 'No fue posible preparar los datos del módulo.'], 500);
            return;
        }

        ob_start();
        try {
            dashboard_section($config['component'], $viewData);
            $html = (string) ob_get_clean();
        } catch (\Throwable $exception) {
            ob_end_clean();
            $this->json(['error' => 'No fue posible renderizar el módulo.'], 500);
            return;
        }

        $selectedProject = $viewData['selectedProject'] ?? null;
        $selectedProjectId = is_array($selectedProject) ? (int) ($selectedProject['id'] ?? 0) : 0;

        $this->json([
            'module' => $module,
            'html' => $html,
            'active_tab' => $viewData['activeTab'] ?? $config['tab'],
            'selected_project_id' => $selectedProjectId > 0 ? $selectedProjectId : null,
        ]);
    }

    /**
     * @param array<string, mixed> $user
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function buildDashboardData(array $user, array $options = []): array
    {
        $activeTabCandidate = is_string($options['activeTab'] ?? null) ? $options['activeTab'] : 'dashboard';
        $activeTab = $this->sanitizeTab($activeTabCandidate);

        $projects = $this->projects->allForUser($user);
        $projectIds = array_values(array_filter(
            array_map(static fn ($project) => (int) ($project['id'] ?? 0), $projects),
            static fn (int $id): bool => $id > 0
        ));

        $selectedProjectId = (int) ($options['selectedProjectId'] ?? 0);
        if ($selectedProjectId > 0 && !in_array($selectedProjectId, $projectIds, true)) {
            $selectedProjectId = 0;
        }
        if ($selectedProjectId === 0 && $projectIds !== []) {
            $selectedProjectId = $projectIds[0];
        }

        $selectedProject = null;
        $projectMilestones = [];
        $deliverablesByMilestone = [];
        $feedbackByMilestone = [];

        if ($selectedProjectId > 0) {
            $selectedProject = $this->projects->find($selectedProjectId);
            if ($selectedProject) {
                $projectMilestones = $this->milestones->forProject($selectedProjectId);
                $milestoneIds = array_values(array_filter(
                    array_map(static fn ($milestone) => (int) ($milestone['id'] ?? 0), $projectMilestones),
                    static fn (int $id): bool => $id > 0
                ));
                $deliverablesByMilestone = $this->deliverables->forMilestones($milestoneIds);
                $feedbackByMilestone = $this->feedback->forMilestones($milestoneIds);
            } else {
                $selectedProjectId = 0;
            }
        }

        $stats = $this->projects->statsForUser($user);
        $upcomingMilestones = $this->projects->upcomingMilestones($user);
        $recentFeedback = $this->feedback->recentForUser($user);
        $boardColumns = $this->projects->boardColumns($user, $selectedProjectId > 0 ? $selectedProjectId : null);

        $students = [];
        if (($user['role'] ?? '') === 'director') {
            $students = $this->users->allByRole('estudiante');
        }

        return [
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
            'selectedProjectId' => $selectedProjectId,
        ];
    }

    private function ensureDashboardHelpersLoaded(): void
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }

        require_once __DIR__ . '/../Views/dashboard/helpers.php';
        require_once __DIR__ . '/../Views/dashboard/components.php';
        $loaded = true;
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function sanitizeTab(string $tab): string
    {
        $allowed = ['dashboard', 'proyectos', 'hitos', 'comentarios', 'progreso'];
        return in_array($tab, $allowed, true) ? $tab : 'dashboard';
    }
}
