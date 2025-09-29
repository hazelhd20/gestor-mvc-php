<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\MilestoneController;
use App\Controllers\ProgressController;
use App\Controllers\ProjectController;
use App\Core\Router;

require __DIR__ . '/../app/bootstrap.php';

$router = new Router();

$router->get('/', [AuthController::class, 'show']);
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/projects', [ProjectController::class, 'index']);
$router->get('/milestones', [MilestoneController::class, 'index']);
$router->get('/progress', [ProgressController::class, 'index']);
$router->get('/submissions/download', [MilestoneController::class, 'download']);
$router->get('/password/change', [AuthController::class, 'showPasswordChange']);

$router->post('/login', [AuthController::class, 'login']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/projects', [ProjectController::class, 'store']);
$router->post('/projects/status', [ProjectController::class, 'updateStatus']);
$router->post('/milestones', [MilestoneController::class, 'store']);
$router->post('/milestones/status', [MilestoneController::class, 'updateStatus']);
$router->post('/submissions', [MilestoneController::class, 'submit']);
$router->post('/comments', [MilestoneController::class, 'comment']);
$router->post('/password/change', [AuthController::class, 'requestPasswordChange']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');