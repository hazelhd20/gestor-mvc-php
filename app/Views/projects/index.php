<?php

$user = $user ?? null;
$projects = $projects ?? [];
$students = $students ?? [];
$statusCatalog = $statusCatalog ?? [];
$statusSummary = $statusSummary ?? [];
$upcoming = $upcoming ?? [];
$success = $success ?? null;
$projectErrors = $projectErrors ?? [];
$projectOld = $projectOld ?? [];

$isDirector = ($user['role'] ?? '') === 'director';

$oldValue = static function (string $key, string $default = '') use ($projectOld): string {
    return e((string) ($projectOld[$key] ?? $default));
};

$hasProjectError = static function (string $key) use ($projectErrors): bool {
    return array_key_exists($key, $projectErrors);
};

$projectErrorText = static function (string $key) use ($projectErrors): string {
    return $projectErrors[$key] ?? '';
};

$statusStyles = [
    'planeacion' => [
        'chip' => 'bg-slate-200 text-slate-700',
    ],
    'en_progreso' => [
        'chip' => 'bg-blue-100 text-blue-700',
    ],
    'en_revision' => [
        'chip' => 'bg-amber-100 text-amber-700',
    ],
    'finalizado' => [
        'chip' => 'bg-emerald-100 text-emerald-700',
    ],
];

$today = new DateTimeImmutable('today');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gestion de proyectos | Gestor de titulacion</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    * { scrollbar-width: thin; }
    *::-webkit-scrollbar { width: 8px; height: 8px; }
    *::-webkit-scrollbar-thumb { background: #c7c7d1; border-radius: 9999px; }
    *::-webkit-scrollbar-track { background: transparent; }
    html { font-family: Inter, system-ui, -apple-system, "Segoe UI", sans-serif; }
  </style>
</head>
<body class="bg-slate-50 text-slate-800">
  <div class="min-h-dvh">
    <header class="border-b border-slate-200 bg-white">
      <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.25em] text-indigo-500">Modulo</p>
          <h1 class="text-xl font-semibold text-slate-900">Gestion de proyectos</h1>
        </div>
        <div class="flex items-center gap-3">
          <a href="<?= e(url('/dashboard')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m15 19-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Volver al panel
          </a>
          <form method="post" action="<?= e(url('/logout')); ?>">
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
              Cerrar sesion
            </button>
          </form>
        </div>
      </div>
    </header>

    <main class="mx-auto max-w-6xl space-y-8 px-4 py-8">
      <?php if ($success): ?>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
          <?= e($success); ?>
        </div>
      <?php endif; ?>

      <?php if ($hasProjectError('general')): ?>
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
          <?= e($projectErrorText('general')); ?>
        </div>
      <?php endif; ?>

      <section aria-labelledby="resumen">
        <div class="flex items-center justify-between">
          <div>
            <h2 id="resumen" class="text-lg font-semibold text-slate-900">Resumen rapido</h2>
            <p class="text-sm text-slate-500">Estado agregado por fase del proyecto.</p>
          </div>
          <?php if ($isDirector): ?>
            <a href="#nuevo-proyecto" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 5v14m7-7H5" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Crear proyecto
            </a>
          <?php endif; ?>
        </div>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <?php foreach ($statusCatalog as $key => $meta): ?>
            <?php $count = $statusSummary[$key] ?? 0; ?>
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
              <p class="text-xs font-semibold uppercase tracking-wider text-slate-500"><?= e($meta['label']); ?></p>
              <p class="mt-1 text-2xl font-semibold text-slate-900"><?= e((string) $count); ?></p>
              <p class="mt-2 text-xs text-slate-500"><?= e($meta['description']); ?></p>
            </article>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="grid gap-6 lg:grid-cols-[2fr_1fr]">
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-900">Proyectos registrados</h2>
            <span class="text-sm text-slate-500"><?= e((string) count($projects)); ?> en total</span>
          </div>

          <?php if ($projects === []): ?>
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-10 text-center">
              <p class="text-base font-semibold text-slate-900">Aun no hay proyectos</p>
              <p class="mt-2 text-sm text-slate-500">
                <?php if ($isDirector): ?>
                  Crea un nuevo proyecto para comenzar a dar seguimiento a una tesina.
                <?php else: ?>
                  Tu director asignara tus proyectos y apareceran aqui automaticamente.
                <?php endif; ?>
              </p>
              <?php if ($isDirector): ?>
                <a href="#nuevo-proyecto" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                  Crear tu primer proyecto
                </a>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
              <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                  <tr>
                    <th class="px-4 py-3">Proyecto</th>
                    <th class="px-4 py-3"><?= e($isDirector ? 'Estudiante' : 'Director'); ?></th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3">Entrega</th>
                    <th class="px-4 py-3">Creado</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <?php foreach ($projects as $project): ?>
                    <?php
                      $status = $project['status'] ?? 'planeacion';
                      $style = $statusStyles[$status] ?? $statusStyles['planeacion'];
                      $meta = $statusCatalog[$status] ?? ['label' => ucfirst(str_replace('_', ' ', $status))];
                      $dueDateText = 'Sin fecha';
                      $dueState = '';
                      if (!empty($project['due_date'])) {
                          $dueDate = DateTimeImmutable::createFromFormat('Y-m-d', (string) $project['due_date']);
                          if ($dueDate instanceof DateTimeImmutable) {
                              $dueDateText = $dueDate->format('d/m/Y');
                              if ($status !== 'finalizado') {
                                  if ($dueDate < $today) {
                                      $dueState = 'text-red-600';
                                  } elseif ($dueDate <= $today->add(new DateInterval('P7D'))) {
                                      $dueState = 'text-amber-600';
                                  } else {
                                      $dueState = 'text-slate-600';
                                  }
                              } else {
                                  $dueState = 'text-slate-500';
                              }
                          }
                      }

                      $counterpartName = $isDirector ? ($project['student_name'] ?? 'Sin asignar') : ($project['director_name'] ?? 'Sin asignar');
                      $counterpartEmail = $isDirector ? ($project['student_email'] ?? '') : ($project['director_email'] ?? '');
                      $createdAtText = '';
                      if (!empty($project['created_at'])) {
                          $createdAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) $project['created_at']);
                          $createdAtText = $createdAt instanceof DateTimeImmutable ? $createdAt->format('d/m/Y') : '';
                      }
                    ?>
                    <tr class="hover:bg-slate-50/60">
                      <td class="px-4 py-3">
                        <p class="font-medium text-slate-900"><?= e($project['title']); ?></p>
                        <?php if (!empty($project['description'])): ?>
                          <p class="mt-1 line-clamp-2 text-xs text-slate-500"><?= e($project['description']); ?></p>
                        <?php endif; ?>
                      </td>
                      <td class="px-4 py-3">
                        <p class="font-medium text-slate-800"><?= e($counterpartName); ?></p>
                        <?php if ($counterpartEmail !== ''): ?>
                          <p class="text-xs text-slate-500"><?= e($counterpartEmail); ?></p>
                        <?php endif; ?>
                      </td>
                      <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?= e($style['chip']); ?>">
                          <?= e($meta['label'] ?? ucfirst($status)); ?>
                        </span>
                      </td>
                      <td class="px-4 py-3">
                        <p class="text-sm font-medium <?= e($dueState); ?>"><?= e($dueDateText); ?></p>
                      </td>
                      <td class="px-4 py-3">
                        <p class="text-sm text-slate-500"><?= e($createdAtText); ?></p>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

        <aside class="space-y-6">
          <?php if ($upcoming !== []): ?>
            <section class="rounded-2xl border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-700">
              <h3 class="text-sm font-semibold text-indigo-900">Entregas proximas</h3>
              <ul class="mt-3 space-y-3">
                <?php foreach ($upcoming as $entry): ?>
                  <?php
                    $project = $entry['project'];
                    /** @var DateTimeImmutable $due */
                    $due = $entry['due_date'];
                    $diffDays = $today->diff($due)->days;
                    $isPast = $due < $today;
                    $counterLabel = $isDirector ? 'Estudiante' : 'Director';
                    $counterValue = $isDirector ? ($project['student_name'] ?? '') : ($project['director_name'] ?? '');
                  ?>
                  <li class="rounded-xl border border-indigo-100 bg-white p-3">
                    <p class="text-sm font-semibold text-slate-900"><?= e($project['title']); ?></p>
                    <p class="mt-1 text-xs text-slate-500">
                      <?= e($counterLabel); ?>: <?= e($counterValue !== '' ? $counterValue : 'Sin asignar'); ?>
                    </p>
                    <p class="mt-2 text-xs font-semibold <?= e($isPast ? 'text-red-600' : ($diffDays <= 7 ? 'text-amber-600' : 'text-indigo-600')); ?>">
                      <?= e($due->format('d/m/Y')); ?> -
                      <?php if ($isPast): ?>
                        Vencido
                      <?php elseif ($diffDays === 0): ?>
                        Entrega hoy
                      <?php else: ?>
                        Faltan <?= e((string) $diffDays); ?> dias
                      <?php endif; ?>
                    </p>
                  </li>
                <?php endforeach; ?>
              </ul>
            </section>
          <?php endif; ?>

          <?php if ($isDirector): ?>
            <section id="nuevo-proyecto" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
              <h3 class="text-base font-semibold text-slate-900">Crear nuevo proyecto</h3>
              <p class="mt-1 text-xs text-slate-500">Completa los campos para asignar un proyecto de titulacion.</p>

              <form class="mt-4 space-y-4" method="post" action="<?= e(url('/projects')); ?>">
                <div>
                  <label for="project-title" class="mb-1 block text-sm font-semibold text-slate-700">Titulo</label>
                  <input id="project-title" name="title" type="text" required value="<?= $oldValue('title'); ?>" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200 <?= $hasProjectError('title') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : ''; ?>" placeholder="Ej. Seguimiento de avances" />
                  <?php if ($hasProjectError('title')): ?>
                    <p class="mt-1 text-xs font-semibold text-red-600"><?= e($projectErrorText('title')); ?></p>
                  <?php endif; ?>
                </div>

                <div>
                  <label for="project-student" class="mb-1 block text-sm font-semibold text-slate-700">Estudiante asignado</label>
                  <select id="project-student" name="student_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200 <?= $hasProjectError('student_id') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : ''; ?>">
                    <option value="">Selecciona un estudiante</option>
                    <?php foreach ($students as $student): ?>
                      <option value="<?= e($student['id']); ?>" <?= (string) ($projectOld['student_id'] ?? '') === (string) $student['id'] ? 'selected' : ''; ?>>
                        <?= e($student['full_name']); ?><?= !empty($student['matricula']) ? ' - '.e($student['matricula']) : ''; ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <?php if ($hasProjectError('student_id')): ?>
                    <p class="mt-1 text-xs font-semibold text-red-600"><?= e($projectErrorText('student_id')); ?></p>
                  <?php endif; ?>
                </div>

                <div>
                  <label for="project-due" class="mb-1 block text-sm font-semibold text-slate-700">Fecha limite (opcional)</label>
                  <input id="project-due" name="due_date" type="date" value="<?= $oldValue('due_date'); ?>" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200 <?= $hasProjectError('due_date') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : ''; ?>" />
                  <?php if ($hasProjectError('due_date')): ?>
                    <p class="mt-1 text-xs font-semibold text-red-600"><?= e($projectErrorText('due_date')); ?></p>
                  <?php endif; ?>
                </div>

                <div>
                  <label for="project-description" class="mb-1 block text-sm font-semibold text-slate-700">Descripcion</label>
                  <textarea id="project-description" name="description" rows="4" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200" placeholder="Describe hitos iniciales y alcance."><?= $oldValue('description'); ?></textarea>
                </div>

                <div class="flex items-center justify-end gap-2">
                  <a href="<?= e(url('/projects')); ?>" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">Limpiar</a>
                  <button type="submit" class="inline-flex items-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    Guardar proyecto
                  </button>
                </div>
              </form>
            </section>
          <?php endif; ?>
        </aside>
      </section>
    </main>
  </div>
</body>
</html>




