<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Database;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;

require __DIR__ . '/../app/bootstrap.php';

$testDbPath = __DIR__ . '/../storage/tests.sqlite';
if (file_exists($testDbPath)) {
    unlink($testDbPath);
}

Config::set('db.sqlite.database', $testDbPath);

$reflection = new ReflectionClass(Database::class);
$connectionProperty = $reflection->getProperty('connection');
$connectionProperty->setAccessible(true);
$connectionProperty->setValue(null, null);

Database::connection();

$users = new User();
$projects = new Project();
$milestones = new Milestone();

$director = $users->create([
    'full_name' => 'Director Uno',
    'email' => 'director@example.com',
    'password' => 'secret123',
    'role' => 'director',
    'matricula' => null,
    'department' => 'Engineering',
]);

$studentOne = $users->create([
    'full_name' => 'Estudiante Uno',
    'email' => 'student1@example.com',
    'password' => 'secret123',
    'role' => 'estudiante',
    'matricula' => 'A001',
    'department' => 'Engineering',
]);

$studentTwo = $users->create([
    'full_name' => 'Estudiante Dos',
    'email' => 'student2@example.com',
    'password' => 'secret123',
    'role' => 'estudiante',
    'matricula' => 'A002',
    'department' => 'Engineering',
]);

$projectOne = $projects->create([
    'title' => 'Proyecto Uno',
    'description' => 'Descripcion del proyecto uno',
    'student_id' => (int) $studentOne['id'],
    'director_id' => (int) $director['id'],
    'status' => 'en_progreso',
    'start_date' => null,
    'end_date' => null,
]);

$projectTwo = $projects->create([
    'title' => 'Proyecto Dos',
    'description' => 'Descripcion del proyecto dos',
    'student_id' => (int) $studentTwo['id'],
    'director_id' => (int) $director['id'],
    'status' => 'planificado',
    'start_date' => null,
    'end_date' => null,
]);

$milestoneA = $milestones->create([
    'project_id' => (int) $projectOne['id'],
    'title' => 'Definir alcance',
    'description' => null,
    'status' => 'pendiente',
    'start_date' => null,
    'end_date' => null,
]);

$milestoneB = $milestones->create([
    'project_id' => (int) $projectOne['id'],
    'title' => 'Revision intermedia',
    'description' => null,
    'status' => 'en_revision',
    'start_date' => null,
    'end_date' => null,
]);

$milestoneC = $milestones->create([
    'project_id' => (int) $projectTwo['id'],
    'title' => 'Entrega prototipo',
    'description' => null,
    'status' => 'en_progreso',
    'start_date' => null,
    'end_date' => null,
]);

function assertMilestoneIds(array $board, string $columnKey, array $expectedIds, string $message): void
{
    $ids = array_map(static fn (array $item): int => (int) $item['id'], $board[$columnKey] ?? []);
    sort($ids);
    $expected = $expectedIds;
    sort($expected);

    if ($ids !== $expected) {
        throw new RuntimeException($message . ' Esperado: ' . json_encode($expected) . ' Actual: ' . json_encode($ids));
    }
}

$directorBoardProjectOne = $projects->boardColumns(['id' => $director['id'], 'role' => 'director'], (int) $projectOne['id']);
assertMilestoneIds($directorBoardProjectOne, 'pendiente', [(int) $milestoneA['id']], 'El tablero del director para el proyecto uno debe incluir el hito pendiente.');
assertMilestoneIds($directorBoardProjectOne, 'en_revision', [(int) $milestoneB['id']], 'El tablero del director para el proyecto uno debe incluir el hito en revision.');
assertMilestoneIds($directorBoardProjectOne, 'en_progreso', [], 'El tablero del director para el proyecto uno no debe incluir hitos de otros proyectos.');

$directorBoardProjectTwo = $projects->boardColumns(['id' => $director['id'], 'role' => 'director'], (int) $projectTwo['id']);
assertMilestoneIds($directorBoardProjectTwo, 'pendiente', [], 'El tablero del director para el proyecto dos no debe incluir hitos pendientes de otros proyectos.');
assertMilestoneIds($directorBoardProjectTwo, 'en_revision', [], 'El tablero del director para el proyecto dos no debe incluir hitos en revision de otros proyectos.');
assertMilestoneIds($directorBoardProjectTwo, 'en_progreso', [(int) $milestoneC['id']], 'El tablero del director para el proyecto dos debe incluir su propio hito en progreso.');

$studentBoardOwnProject = $projects->boardColumns(['id' => $studentOne['id'], 'role' => 'estudiante'], (int) $projectOne['id']);
assertMilestoneIds($studentBoardOwnProject, 'pendiente', [(int) $milestoneA['id']], 'El estudiante debe ver sus hitos pendientes del proyecto seleccionado.');
assertMilestoneIds($studentBoardOwnProject, 'en_revision', [(int) $milestoneB['id']], 'El estudiante debe ver sus hitos en revision del proyecto seleccionado.');

$studentBoardOtherProject = $projects->boardColumns(['id' => $studentOne['id'], 'role' => 'estudiante'], (int) $projectTwo['id']);
assertMilestoneIds($studentBoardOtherProject, 'pendiente', [], 'El estudiante no debe ver hitos de proyectos ajenos.');
assertMilestoneIds($studentBoardOtherProject, 'en_progreso', [], 'El estudiante no debe ver hitos en progreso de proyectos ajenos.');
assertMilestoneIds($studentBoardOtherProject, 'en_revision', [], 'El estudiante no debe ver hitos en revision de proyectos ajenos.');

echo "Project board column filtering tests passed\n";
