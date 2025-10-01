<?php

declare(strict_types=1);

if (!function_exists('dashboard_build_view_model')) {
    function dashboard_build_view_model(array $context): array
    {
        $defaults = [
            'user' => [],
            'errors' => [],
            'success' => null,
            'old' => [],
            'activeTab' => 'dashboard',
            'projects' => [],
            'selectedProject' => null,
            'projectMilestones' => [],
            'deliverablesByMilestone' => [],
            'feedbackByMilestone' => [],
            'stats' => [],
            'upcomingMilestones' => [],
            'recentFeedback' => [],
            'boardColumns' => [],
            'students' => [],
            'modalTarget' => null,
        ];

        $data = array_merge($defaults, $context);
        $user = is_array($data['user']) ? $data['user'] : [];
        $data['user'] = $user;

        $data['fullName'] = is_string($user['full_name'] ?? null) && $user['full_name'] !== ''
            ? $user['full_name']
            : 'Usuario';
        $data['role'] = is_string($user['role'] ?? null) && $user['role'] !== ''
            ? $user['role']
            : 'Invitado';
        $data['userId'] = (int) ($user['id'] ?? 0);
        $data['isDirector'] = $data['role'] === 'director';
        $data['isStudent'] = $data['role'] === 'estudiante';

        $avatarPath = is_string($user['avatar_path'] ?? null) ? trim((string) $user['avatar_path']) : '';
        $data['avatarPath'] = $avatarPath !== '' ? $avatarPath : null;
        $data['avatarUrl'] = $avatarPath !== '' ? url('/' . ltrim($avatarPath, '/')) : null;

        $toUpper = function_exists('mb_strtoupper')
            ? static fn (string $value): string => mb_strtoupper($value)
            : static fn (string $value): string => strtoupper($value);

        $slice = function_exists('mb_substr')
            ? static fn (string $value, int $start, int $length = 1): string => mb_substr($value, $start, $length)
            : static fn (string $value, int $start, int $length = 1): string => substr($value, $start, $length);

        $length = function_exists('mb_strlen')
            ? static fn (string $value): int => mb_strlen($value)
            : static fn (string $value): int => strlen($value);

        $initials = '';
        $nameParts = preg_split('/\s+/', (string) $data['fullName']) ?: [];
        foreach ($nameParts as $part) {
            if ($part === '') {
                continue;
            }
            $initials .= $toUpper($slice($part, 0, 1));
            if ($length($initials) >= 2) {
                break;
            }
        }
        if ($initials === '') {
            $initials = $data['fullName'] !== '' ? $toUpper($slice($data['fullName'], 0, 1)) : 'U';
        }
        $data['avatarInitials'] = $initials;

        $data['errors'] = array_values(array_filter(
            is_array($data['errors']) ? $data['errors'] : [],
            static fn ($error) => is_string($error) && $error !== ''
        ));

        $data['success'] = is_string($data['success']) && $data['success'] !== ''
            ? $data['success']
            : null;

        $data['old'] = is_array($data['old']) ? $data['old'] : [];
        $data['projectOld'] = is_array($data['old']['project'] ?? null) ? $data['old']['project'] : [];
        $data['milestoneOld'] = is_array($data['old']['milestone'] ?? null) ? $data['old']['milestone'] : [];
        $data['projectEditOld'] = is_array($data['old']['project_edit'] ?? null) ? $data['old']['project_edit'] : [];
        $data['projectEditId'] = isset($data['old']['project_edit_id']) ? (int) $data['old']['project_edit_id'] : null;
        $data['milestoneEditOld'] = is_array($data['old']['milestone_edit'] ?? null) ? $data['old']['milestone_edit'] : [];
        $data['milestoneEditId'] = isset($data['old']['milestone_edit_id']) ? (int) $data['old']['milestone_edit_id'] : null;

        $statsDefaults = ['total' => 0, 'active' => 0, 'completed' => 0, 'due_soon' => 0];
        $data['stats'] = array_merge($statsDefaults, is_array($data['stats']) ? $data['stats'] : []);

        foreach ([
            'projects',
            'projectMilestones',
            'upcomingMilestones',
            'recentFeedback',
            'students',
        ] as $listKey) {
            $data[$listKey] = is_array($data[$listKey]) ? $data[$listKey] : [];
        }

        foreach ([
            'deliverablesByMilestone',
            'feedbackByMilestone',
            'boardColumns',
        ] as $mapKey) {
            $data[$mapKey] = is_array($data[$mapKey]) ? $data[$mapKey] : [];
        }

        $data['selectedProject'] = is_array($data['selectedProject']) ? $data['selectedProject'] : null;

        $data['navItems'] = [
            [
                'id' => 'dashboard',
                'label' => 'Dashboard',
                'icon' => 'layout-dashboard',
                'title' => 'Resumen',
                'subtitle' => 'Estado general y actividad reciente',
            ],
            [
                'id' => 'proyectos',
                'label' => 'Gestion de proyectos',
                'icon' => 'folder-kanban',
                'title' => 'Gestion de proyectos',
                'subtitle' => 'Crear, editar y asignar proyectos de titulacion',
            ],
            [
                'id' => 'hitos',
                'label' => 'Hitos y entregables',
                'icon' => 'flag',
                'title' => 'Hitos y entregables',
                'subtitle' => 'Define fechas limite, sube avances y valida entregables',
            ],
            [
                'id' => 'comentarios',
                'label' => 'Comentarios / Feedback',
                'icon' => 'message-square',
                'title' => 'Comentarios y feedback',
                'subtitle' => 'Comunicacion entre estudiante y director',
            ],
            [
                'id' => 'progreso',
                'label' => 'Visualizacion de progreso',
                'icon' => 'trending-up',
                'title' => 'Visualizacion de progreso',
                'subtitle' => 'Kanban simplificado para seguimiento visual',
            ],
        ];

        $data['pageTitle'] = 'Panel';
        $data['pageSubtitle'] = 'Resumen general y acciones rapidas';

        return $data;
    }
}

if (!function_exists('format_dashboard_date')) {
    function format_dashboard_date(?string $date): string
    {
        if (!$date) {
            return 'Sin fecha';
        }

        try {
            $dt = new \DateTimeImmutable($date);
            return $dt->format('d/m/Y');
        } catch (\Throwable) {
            return $date;
        }
    }
}

if (!function_exists('format_dashboard_period')) {
    function format_dashboard_period(?string $start, ?string $end): string
    {
        $formatDate = static function (?string $value): ?string {
            if (!$value) {
                return null;
            }

            try {
                return (new \DateTimeImmutable($value))->format('d/m/Y');
            } catch (\Throwable) {
                return $value;
            }
        };

        $startFormatted = $formatDate($start);
        $endFormatted = $formatDate($end);

        if ($startFormatted && $endFormatted) {
            if ($startFormatted === $endFormatted) {
                return $startFormatted;
            }

            return $startFormatted . ' - ' . $endFormatted;
        }

        if ($startFormatted) {
            return 'Desde ' . $startFormatted;
        }

        if ($endFormatted) {
            return 'Hasta ' . $endFormatted;
        }

        return 'Sin fecha';
    }
}

if (!function_exists('humanize_status')) {
    function humanize_status(string $status): string
    {
        return match ($status) {
            'planificado' => 'Planificado',
            'en_progreso' => 'En progreso',
            'en_riesgo' => 'En riesgo',
            'completado' => 'Completado',
            'pendiente' => 'Pendiente',
            'en_revision' => 'En revision',
            'aprobado' => 'Aprobado',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}

if (!function_exists('status_badge_classes')) {
    function status_badge_classes(string $status): string
    {
        return match ($status) {
            'planificado', 'pendiente' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
            'en_progreso' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-200',
            'en_riesgo' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/60 dark:text-amber-200',
            'en_revision' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-200',
            'aprobado', 'completado' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-200',
            default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
        };
    }
}

