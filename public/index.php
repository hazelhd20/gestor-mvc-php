<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\DeliverablesController;
use App\Controllers\FeedbackController;
use App\Controllers\MilestonesController;
use App\Controllers\ProjectsController;
use App\Controllers\ProfileController;
use App\Core\Router;

require __DIR__ . '/../app/bootstrap.php';

$router = new Router();

$router->get('/', [AuthController::class, 'show']);
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/projects', static function (): void {
    redirect_to('/dashboard?tab=proyectos');
});
$router->get('/deliverables/download', [DeliverablesController::class, 'download']);
$router->get('/password/change', [AuthController::class, 'showPasswordChange']);

$router->post('/login', [AuthController::class, 'login']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/password/change', [AuthController::class, 'requestPasswordChange']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->post('/projects', [ProjectsController::class, 'store']);
$router->post('/projects/status', [ProjectsController::class, 'updateStatus']);
$router->post('/projects/update', [ProjectsController::class, 'update']);
$router->post('/projects/delete', [ProjectsController::class, 'destroy']);
$router->post('/milestones', [MilestonesController::class, 'store']);
$router->post('/milestones/status', [MilestonesController::class, 'updateStatus']);
$router->post('/milestones/update', [MilestonesController::class, 'update']);
$router->post('/milestones/delete', [MilestonesController::class, 'destroy']);
$router->post('/deliverables', [DeliverablesController::class, 'store']);
$router->post('/feedback', [FeedbackController::class, 'store']);
$router->post('/profile/avatar', [ProfileController::class, 'updateAvatar']);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
