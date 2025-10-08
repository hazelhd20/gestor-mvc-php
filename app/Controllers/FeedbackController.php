<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\Feedback;
use App\Models\Milestone;
use App\Models\Notification;
use App\Models\Project;
use RuntimeException;
use Throwable;

class FeedbackController extends Controller
{
    private Feedback $feedback;
    private Milestone $milestones;
    private Project $projects;
    private Notification $notifications;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->feedback = new Feedback();
        $this->milestones = new Milestone();
        $this->projects = new Project();
        $this->notifications = new Notification();
    }

    public function store(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        $milestoneId = (int) ($_POST['milestone_id'] ?? 0);
        $rawContent = $_POST['content'] ?? '';
        $content = trim($rawContent);

        if ($content !== '') {
            $content = preg_replace('/\r\n|\r/', "\n", $content);
            $content = preg_replace('/[ \t]{2,}/', ' ', $content);
            $content = preg_replace('/\n{3,}/', "\n\n", $content);
            $content = trim($content);
        }

        if ($milestoneId <= 0) {
            Session::flash('dashboard_errors', ['Selecciona un hito para dejar comentarios.']);
            Session::flash('dashboard_tab', 'comentarios');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        $milestone = $this->milestones->find($milestoneId);
        if (!$milestone) {
            Session::flash('dashboard_errors', ['No encontramos el hito seleccionado.']);
            Session::flash('dashboard_tab', 'comentarios');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        $project = $this->projects->find((int) $milestone['project_id']);
        if (!$project) {
            Session::flash('dashboard_errors', ['No encontramos el proyecto relacionado.']);
            Session::flash('dashboard_tab', 'comentarios');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        $projectId = (int) $project['id'];

        if ($content === '') {
            Session::flash('dashboard_errors', ['El contenido del comentario no puede estar vacio.']);
            Session::flash('dashboard_tab', 'comentarios');
            Session::flash('dashboard_project_id', $projectId);
            $this->redirectTo($this->dashboardRedirectUrl());

        }

        $contentLength = function_exists('mb_strlen') ? mb_strlen($content) : strlen($content);
        if ($contentLength > 1000) {
            Session::flash('dashboard_errors', ['El comentario es demasiado largo (maximo 1000 caracteres).']);
            Session::flash('dashboard_tab', 'comentarios');
            Session::flash('dashboard_project_id', $projectId);
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        $userId = (int) ($user['id'] ?? 0);
        $role = $user['role'] ?? '';
        $allowed = ($role === 'director' && (int) $project['director_id'] === $userId)
            || ($role === 'estudiante' && (int) $project['student_id'] === $userId);

        if (!$allowed) {
            Session::flash('dashboard_errors', ['No tienes permisos para comentar en este hito.']);
            Session::flash('dashboard_tab', 'comentarios');
            Session::flash('dashboard_project_id', $projectId);
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        $recipientId = null;
        if ($role === 'director') {
            $recipientId = (int) $project['student_id'];
        } elseif ($role === 'estudiante') {
            $recipientId = (int) $project['director_id'];
        }

        try {
            $this->feedback->create([
                'milestone_id' => $milestoneId,
                'author_id' => $userId,
                'recipient_id' => $recipientId,
                'content' => $content,
            ]);
        } catch (RuntimeException $exception) {
            Session::flash('dashboard_errors', [$exception->getMessage()]);
            Session::flash('dashboard_project_id', $projectId);
            Session::flash('dashboard_tab', 'comentarios');
            $this->redirectTo($this->dashboardRedirectUrl());
        }

        $this->notify(
            (int) $recipientId,
            'feedback_received',
            'Nuevo comentario recibido',
            sprintf(
                '%s ha dejado un comentario en el hito "%s".',
                (string) ($user['full_name'] ?? 'Un usuario'),
                (string) ($milestone['title'] ?? 'Sin titulo')
            ),
            url('/dashboard?tab=hitos&project=' . $projectId) . '#milestone-' . $milestoneId,
            [
                'project_id' => $projectId,
                'project_title' => $project['title'] ?? null,
                'milestone_id' => $milestoneId,
                'milestone_title' => $milestone['title'] ?? null,
                'author_id' => $userId,
            ]
        );

        Session::flash('dashboard_success', 'Comentario registrado correctamente.');
        Session::flash('dashboard_project_id', $projectId);
        Session::flash('dashboard_tab', 'hitos');
        $this->redirectTo($this->dashboardRedirectUrl());
    }

    private function dashboardRedirectUrl(): string
    {
        $url = $this->buildDashboardUrl(
            $_POST['return_tab'] ?? null,
            $_POST['return_project'] ?? null,
            $_POST['return_anchor'] ?? null
        );

        return $url ?? '/dashboard';
    }

    private function buildDashboardUrl($tab, $project, $anchor): ?string
    {
        if (!is_string($tab)) {
            return null;
        }

        $tab = trim($tab);
        if ($tab === '' || !preg_match('/^[a-z0-9_-]+$/i', $tab)) {
            return null;
        }

        $projectId = null;
        if (is_string($project) || is_int($project)) {
            $projectString = trim((string) $project);
            if ($projectString !== '' && ctype_digit($projectString)) {
                $candidate = (int) $projectString;
                if ($candidate > 0) {
                    $projectId = $candidate;
                }
            }
        }

        $params = ['tab' => $tab];
        if ($projectId !== null) {
            $params['project'] = $projectId;
        }

        $url = '/dashboard?' . http_build_query($params);

        $anchorValue = null;
        if (is_string($anchor)) {
            $anchorCandidate = trim($anchor);
            if ($anchorCandidate !== '' && preg_match('/^[A-Za-z0-9_-]+$/', $anchorCandidate)) {
                $anchorValue = $anchorCandidate;
            }
        }

        if ($anchorValue !== null) {
            $url .= '#' . $anchorValue;
        }

        return $url;
    }

    private function notify(
        int $recipientId,
        string $type,
        string $title,
        ?string $body = null,
        ?string $actionUrl = null,
        array $data = []
    ): void {
        if ($recipientId <= 0) {
            return;
        }

        try {
            $this->notifications->create([
                'user_id' => $recipientId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'action_url' => $actionUrl,
                'data' => $data,
            ]);
        } catch (Throwable) {
            // Silenciar fallos de notificación para no interrumpir el flujo principal
        }
    }
}
