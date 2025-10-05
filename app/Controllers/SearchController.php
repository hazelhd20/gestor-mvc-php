<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\Feedback;
use App\Models\Milestone;
use App\Models\Project;
use function json_encode;

class SearchController extends Controller
{
    private Project $projects;
    private Milestone $milestones;
    private Feedback $feedback;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->projects = new Project();
        $this->milestones = new Milestone();
        $this->feedback = new Feedback();
    }

    public function index(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->json(['error' => 'No autorizado'], 401);
            return;
        }

        $term = isset($_GET['q']) ? (string) $_GET['q'] : '';
        $term = trim($term);

        $length = function_exists('mb_strlen') ? mb_strlen($term) : strlen($term);
        if ($term === '' || $length < 2) {
            $this->json([
                'term' => '',
                'projects' => [],
                'milestones' => [],
                'feedback' => [],
            ]);
            return;
        }

        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 5;
        $limit = max(1, min($limit, 10));

        $projects = $this->projects->searchForUser($user, $term, $limit);
        $milestones = $this->milestones->searchForUser($user, $term, $limit);
        $feedback = $this->feedback->searchForUser($user, $term, $limit);

        $this->json([
            'term' => $term,
            'projects' => array_map(fn (array $item): array => $this->formatProject($item), $projects),
            'milestones' => array_map(fn (array $item): array => $this->formatMilestone($item), $milestones),
            'feedback' => array_map(fn (array $item): array => $this->formatFeedback($item), $feedback),
        ]);
    }

    private function formatProject(array $project): array
    {
        $id = (int) ($project['id'] ?? 0);

        return [
            'id' => $id,
            'title' => (string) ($project['title'] ?? ''),
            'description' => $this->snippet($project['description'] ?? null),
            'meta' => $this->humanizeStatus((string) ($project['status'] ?? '')),
            'url' => $id > 0 ? \url('/dashboard?tab=proyectos&project=' . $id) : \url('/dashboard?tab=proyectos'),
            'icon' => 'briefcase',
        ];
    }

    private function formatMilestone(array $milestone): array
    {
        $id = (int) ($milestone['id'] ?? 0);
        $projectId = (int) ($milestone['project_id'] ?? 0);
        $status = $this->humanizeStatus((string) ($milestone['status'] ?? ''));
        $projectTitle = (string) ($milestone['project_title'] ?? '');
        $meta = $projectTitle !== '' ? $status . ' • ' . $projectTitle : $status;

        return [
            'id' => $id,
            'title' => (string) ($milestone['title'] ?? ''),
            'description' => $this->snippet($milestone['description'] ?? null),
            'meta' => $meta,
            'url' => $projectId > 0
                ? \url('/dashboard?tab=hitos&project=' . $projectId . '#milestone-' . $id)
                : \url('/dashboard?tab=hitos'),
            'icon' => 'flag',
        ];
    }

    private function formatFeedback(array $feedback): array
    {
        $id = (int) ($feedback['id'] ?? 0);
        $projectId = (int) ($feedback['project_id'] ?? 0);
        $content = (string) ($feedback['content'] ?? '');
        $milestoneTitle = (string) ($feedback['milestone_title'] ?? '');
        $projectTitle = (string) ($feedback['project_title'] ?? '');
        $author = (string) ($feedback['author_name'] ?? '');

        $descriptionParts = [];
        if ($milestoneTitle !== '') {
            $descriptionParts[] = 'Hito: ' . $milestoneTitle;
        }
        if ($author !== '') {
            $descriptionParts[] = 'Autor: ' . $author;
        }

        $meta = $projectTitle !== '' ? 'Proyecto: ' . $projectTitle : '';

        return [
            'id' => $id,
            'title' => $this->snippet($content),
            'description' => implode(' • ', $descriptionParts),
            'meta' => $meta,
            'url' => $projectId > 0
                ? \url('/dashboard?tab=comentarios&project=' . $projectId . '#comentario-' . $id)
                : \url('/dashboard?tab=comentarios'),
            'icon' => 'message-circle',
        ];
    }

    private function snippet(?string $value, int $length = 120): string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($text) <= $length) {
                return $text;
            }

            return rtrim(mb_substr($text, 0, $length - 1)) . '…';
        }

        if (strlen($text) <= $length) {
            return $text;
        }

        return rtrim(substr($text, 0, $length - 1)) . '…';
    }

    private function humanizeStatus(string $status): string
    {
        $map = [
            'planificado' => 'Planificado',
            'en_progreso' => 'En progreso',
            'en_riesgo' => 'En riesgo',
            'completado' => 'Completado',
            'pendiente' => 'Pendiente',
            'en_revision' => 'En revisión',
            'aprobado' => 'Aprobado',
        ];

        if (isset($map[$status])) {
            return $map[$status];
        }

        $status = str_replace('_', ' ', $status);
        return $status !== '' ? ucfirst($status) : '';
    }

    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
