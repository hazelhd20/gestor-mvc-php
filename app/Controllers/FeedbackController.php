<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\Comment;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Submission;
use App\Models\User;

class FeedbackController extends Controller
{
    private Project $projects;
    private Milestone $milestones;
    private Submission $submissions;
    private Comment $comments;
    private User $users;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->projects = new Project();
        $this->milestones = new Milestone();
        $this->submissions = new Submission();
        $this->comments = new Comment();
        $this->users = new User();
    }

    public function index(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->redirectTo('/');
        }

        $projects = $this->projects->allForUser($user);
        if ($projects === []) {
            $this->render('feedback/index', [
                'user' => $user,
                'projects' => [],
                'selectedProject' => null,
                'milestones' => [],
                'selectedMilestone' => null,
                'latestSubmission' => null,
                'olderSubmissions' => [],
                'milestoneComments' => [],
                'commentTotals' => [],
                'usersMap' => [],
                'statusMessage' => Session::flash('milestone_status'),
                'feedbackFlash' => Session::flash('milestone_feedback') ?? [],
            ]);
            return;
        }

        $requestedProjectId = (int) ($_GET['project'] ?? 0);
        $selectedProject = null;
        foreach ($projects as $project) {
            if ((int) $project['id'] === $requestedProjectId) {
                $selectedProject = $project;
                break;
            }
        }
        if ($selectedProject === null) {
            $selectedProject = $projects[0];
        }

        $milestones = $this->milestones->allForProject((int) $selectedProject['id']);
        if ($milestones === []) {
            $this->render('feedback/index', [
                'user' => $user,
                'projects' => $projects,
                'selectedProject' => $selectedProject,
                'milestones' => [],
                'selectedMilestone' => null,
                'latestSubmission' => null,
                'olderSubmissions' => [],
                'milestoneComments' => [],
                'commentTotals' => [],
                'usersMap' => [],
                'statusMessage' => Session::flash('milestone_status'),
                'feedbackFlash' => Session::flash('milestone_feedback') ?? [],
            ]);
            return;
        }

        $requestedMilestoneId = (int) ($_GET['milestone'] ?? 0);
        $selectedMilestone = null;
        foreach ($milestones as $milestone) {
            if ((int) $milestone['id'] === $requestedMilestoneId) {
                $selectedMilestone = $milestone;
                break;
            }
        }
        if ($selectedMilestone === null) {
            $selectedMilestone = $milestones[0];
        }
        $selectedMilestoneId = (int) $selectedMilestone['id'];

        $submissionsByMilestone = [];
        $milestoneComments = [];
        $commentTotals = [];
        $userIds = [(int) $user['id']];

        foreach ($milestones as $milestone) {
            $milestoneId = (int) $milestone['id'];
            $submissions = $this->submissions->allForMilestone($milestoneId);
            $totalComments = 0;
            foreach ($submissions as &$submission) {
                $submission['comments'] = $this->comments->allForSubmission((int) $submission['id']);
                $totalComments += count($submission['comments']);
                $userIds[] = (int) $submission['user_id'];
                foreach ($submission['comments'] as $comment) {
                    $userIds[] = (int) $comment['user_id'];
                }
            }
            unset($submission);
            $submissionsByMilestone[$milestoneId] = $submissions;

            $generalComments = $this->comments->allForMilestone($milestoneId);
            $milestoneComments[$milestoneId] = $generalComments;
            $totalComments += count($generalComments);
            foreach ($generalComments as $comment) {
                $userIds[] = (int) $comment['user_id'];
            }

            $commentTotals[$milestoneId] = $totalComments;
        }

        $activeSubmissions = $submissionsByMilestone[$selectedMilestoneId] ?? [];
        $latestSubmission = $activeSubmissions[0] ?? null;
        $olderSubmissions = array_slice($activeSubmissions, 1);

        $usersMap = $this->users->findManyByIds($userIds);

        $this->render('feedback/index', [
            'user' => $user,
            'projects' => $projects,
            'selectedProject' => $selectedProject,
            'milestones' => $milestones,
            'selectedMilestone' => $selectedMilestone,
            'latestSubmission' => $latestSubmission,
            'olderSubmissions' => $olderSubmissions,
            'milestoneComments' => $milestoneComments,
            'commentTotals' => $commentTotals,
            'usersMap' => $usersMap,
            'statusMessage' => Session::flash('milestone_status'),
            'feedbackFlash' => Session::flash('milestone_feedback') ?? [],
        ]);
    }
}
