<?php

declare(strict_types=1);

use App\Core\Database;
use App\Models\Comment;

require __DIR__ . '/../app/bootstrap.php';

// Instanciar el modelo garantiza la ejecución de ensureTable() con las nuevas columnas.
new Comment();

$db = Database::connection();
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
$hasMilestoneColumn = false;
$submissionAllowsNull = true;

if ($driver === 'mysql') {
    $statement = $db->query("SHOW COLUMNS FROM comments");
    $columns = $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
    foreach ($columns as $column) {
        if (($column['Field'] ?? '') === 'milestone_id') {
            $hasMilestoneColumn = true;
        }
        if (($column['Field'] ?? '') === 'submission_id' && ($column['Null'] ?? '') === 'NO') {
            $submissionAllowsNull = false;
        }
    }
} else {
    $statement = $db->query("PRAGMA table_info('comments')");
    $columns = $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
    foreach ($columns as $column) {
        if (($column['name'] ?? '') === 'milestone_id') {
            $hasMilestoneColumn = true;
        }
        if (($column['name'] ?? '') === 'submission_id' && (int) ($column['notnull'] ?? 0) === 1) {
            $submissionAllowsNull = false;
        }
    }
}

if ($hasMilestoneColumn && $submissionAllowsNull) {
    echo "La tabla comments se actualizó correctamente para soportar conversaciones por hito." . PHP_EOL;
} else {
    echo "Revisa la tabla comments: milestone_id presente=" . ($hasMilestoneColumn ? 'sí' : 'no')
        . ', submission_id admite NULL=' . ($submissionAllowsNull ? 'sí' : 'no') . PHP_EOL;
}
