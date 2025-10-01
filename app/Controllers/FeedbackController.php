<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\Feedback;
use App\Models\Milestone;
use App\Models\Project;
use RuntimeException;

class FeedbackController extends Controller
{
    private Feedback $feedback;
    private Milestone $milestones;
    private Project $projects;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->feedback = new Feedback();
        $this->milestones = new Milestone();
        $this->projects = new Project();
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
            $this->redirectTo('/dashboard');
        }

        $milestone = $this->milestones->find($milestoneId);
        if (!$milestone) {
            Session::flash('dashboard_errors', ['No encontramos el hito seleccionado.']);
            Session::flash('dashboard_tab', 'comentarios');
            $this->redirectTo('/dashboard');
        }

        $project = $this->projects->find((int) $milestone['project_id']);
        if (!$project) {
            Session::flash('dashboard_errors', ['No encontramos el proyecto relacionado.']);
            Session::flash('dashboard_tab', 'comentarios');
            $this->redirectTo('/dashboard');
        }

        $projectId = (int) $project['id'];

        if ($content === '') {
            Session::flash('dashboard_errors', ['El contenido del comentario no puede estar vacio.']);
            Session::flash('dashboard_tab', 'comentarios');
            Session::flash('dashboard_project_id', $projectId);
            $this->redirectTo('/dashboard');

        }

        $contentLength = function_exists('mb_strlen') ? mb_strlen($content) : strlen($content);
        if ($contentLength > 1000) {
            Session::flash('dashboard_errors', ['El comentario es demasiado largo (maximo 1000 caracteres).']);
            Session::flash('dashboard_tab', 'comentarios');
            Session::flash('dashboard_project_id', $projectId);
            $this->redirectTo('/dashboard');
        }

        $userId = (int) ($user['id'] ?? 0);
        $role = $user['role'] ?? '';
        $allowed = ($role === 'director' && (int) $project['director_id'] === $userId)
            || ($role === 'estudiante' && (int) $project['student_id'] === $userId);

        if (!$allowed) {
            Session::flash('dashboard_errors', ['No tienes permisos para comentar en este hito.']);
            Session::flash('dashboard_tab', 'comentarios');
            Session::flash('dashboard_project_id', $projectId);
            $this->redirectTo('/dashboard');
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
            $this->redirectTo('/dashboard');
        }

        Session::flash('dashboard_success', 'Comentario registrado correctamente.');
        Session::flash('dashboard_project_id', $projectId);
        Session::flash('dashboard_tab', 'comentarios');
        $this->redirectTo('/dashboard');
    }
}
