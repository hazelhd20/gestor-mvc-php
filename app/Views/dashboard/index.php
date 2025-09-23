<?php
use DateTimeImmutable;

$user = $user ?? null;
$projects = $projects ?? [];
$students = $students ?? [];
$success = $success ?? null;
$projectErrors = $projectErrors ?? [];
$projectOld = $projectOld ?? [];
$summary = $summary ?? ['total' => 0, 'active' => 0, 'finished' => 0, 'dueSoon' => 0];
$statusSummary = $statusSummary ?? ['planeacion' => 0, 'en_progreso' => 0, 'en_revision' => 0, 'finalizado' => 0];
$kanban = $kanban ?? ['planeacion' => [], 'en_progreso' => [], 'en_revision' => [], 'finalizado' => []];
$upcoming = $upcoming ?? [];

$isDirector = $user && ($user['role'] ?? '') === 'director';

$oldValue = static function (string $key, string $default = '') use ($projectOld): string {
    return e($projectOld[$key] ?? $default);
};

$hasProjectError = static function (string $key) use ($projectErrors): bool {
    return array_key_exists($key, $projectErrors);
};

$projectErrorText = static function (string $key) use ($projectErrors): string {
    return $projectErrors[$key] ?? '';
};

$statusStyles = [
    'planeacion' => [
        'label' => 'Planeacion',
        'chip' => 'bg-slate-200 text-slate-700',
        'border' => 'border-slate-200 bg-slate-50',
    ],
    'en_progreso' => [
        'label' => 'En progreso',
        'chip' => 'bg-blue-100 text-blue-700',
        'border' => 'border-blue-200 bg-blue-50',
    ],
    'en_revision' => [
        'label' => 'En revision',
        'chip' => 'bg-amber-100 text-amber-700',
        'border' => 'border-amber-200 bg-amber-50',
    ],
    'finalizado' => [
        'label' => 'Finalizado',
        'chip' => 'bg-emerald-100 text-emerald-700',
        'border' => 'border-emerald-200 bg-emerald-50',
    ],
];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Panel | Gestor de Titulacion</title>
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
  <div class="min-h-dvh grid lg:grid-cols-[260px_1fr]">
    <aside class="hidden lg:flex lg:flex-col gap-4 border-r border-slate-200 bg-white p-4">
      <div class="flex items-center gap-3">
        <div class="size-10 rounded-2xl bg-gradient-to-tr from-indigo-500 to-violet-500"></div>
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest text-indigo-500">Gestor</p>
          <p class="text-sm font-semibold text-slate-900">Panel de titulacion</p>
        </div>
      </div>
      <nav class="mt-3 text-sm">
        <p class="px-2 text-xs uppercase tracking-wider text-slate-400">Secciones</p>
        <ul class="mt-2 space-y-1 font-medium">
          <li>
            <a class="flex items-center gap-3 rounded-xl bg-indigo-50 px-3 py-2 text-indigo-700" href="#resumen">
              <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h7v7H3V3Zm0 11h7v7H3v-7Zm11-11h7v7h-7V3Zm0 11h7v7h-7v-7Z"/></svg>
              Resumen
            </a>
          </li>
          <li>
            <a class="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-slate-100" href="#kanban">
              <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="currentColor"><path d="M3 4h18v2H3V4Zm0 7h6v9H3v-9Zm8 0h6v9h-6v-9Zm8 0h2v9h-2v-9Z"/></svg>
              Tablero
            </a>
          </li>
          <?php if ($isDirector): ?>
          <li>
            <a class="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-slate-100" href="#nuevo">
              <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2h6Z"/></svg>
              Crear proyecto
            </a>
          </li>
          <?php endif; ?>
        </ul>
      </nav>
      <div class="mt-6 space-y-3 rounded-2xl bg-slate-50 p-4 text-xs">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Estado general</p>
        <div class="space-y-2">
          <?php foreach ($statusSummary as $key => $count): ?>
            <?php $style = $statusStyles[$key] ?? $statusStyles['planeacion']; ?>
            <div class="flex items-center justify-between rounded-xl bg-white px-3 py-2 shadow-sm">
              <span class="text-xs font-medium text-slate-500"><?= e($style['label']); ?></span>
              <span class="text-sm font-semibold text-slate-800"><?= e((string) $count); ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="mt-auto rounded-2xl bg-indigo-50 p-4 text-xs text-indigo-700">
        <p class="text-sm font-semibold">Consejo</p>
        <p class="mt-1 leading-relaxed text-indigo-600/80">Organiza tus hitos y entregables desde la vista Kanban para dar seguimiento continuo.</p>
      </div>
    </aside>

    <main class="flex flex-col">
      <header class="sticky top-0 z-10 border-b border-slate-200 bg-white/80 backdrop-blur">
        <div class="flex items-center gap-3 px-4 py-3">
          <button class="lg:hidden rounded-xl p-2 hover:bg-slate-100" aria-label="Abrir menu">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-6" viewBox="0 0 24 24" fill="currentColor"><path d="M4 6h16v2H4V6Zm0 5h16v2H4v-2Zm0 5h16v2H4v-2Z"/></svg>
          </button>
          <div class="relative flex-1">
            <input placeholder="Buscar proyectos, estudiantes o directores" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 pr-10 text-sm outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200" />
            <svg class="pointer-events-none absolute right-3 top-1/2 size-5 -translate-y-1/2 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 1 0-.71.71l.27.28v.79L20 21.5 21.5 20l-6-6ZM10 15a5 5 0 1 1 0-10 5 5 0 0 1 0 10Z"/></svg>
          </div>
          <button class="rounded-xl p-2 hover:bg-slate-100" aria-label="Notificaciones">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-6" viewBox="0 0 24 24" fill="currentColor"><path d="M12 22a2 2 0 0 0 2-2H10a2 2 0 0 0 2 2Zm6-6V11a6 6 0 1 0-12 0v5l-2 2v1h16v-1l-2-2Z"/></svg>
          </button>
          <div class="hidden text-right sm:block">
            <p class="text-sm font-semibold text-slate-700"><?= e($user['full_name'] ?? ''); ?></p>
            <p class="text-xs text-slate-500"><?= e($user ? ucfirst($user['role']) : ''); ?></p>
          </div>
          <div class="size-10 rounded-full bg-gradient-to-tr from-indigo-500 to-violet-500"></div>
        </div>
      </header>

      <section id="resumen" class="space-y-6 p-4 sm:p-6">
        <?php if ($success): ?>
          <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
            <?= e($success); ?>
          </div>
        <?php endif; ?>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total proyectos</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900"><?= e((string) $summary['total']); ?></p>
            <p class="mt-2 text-xs text-slate-500">Incluye todos los proyectos vinculados a tu rol.</p>
          </article>
          <article class="rounded-2xl border border-blue-200 bg-blue-50 p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">Activos</p>
            <p class="mt-3 text-3xl font-semibold text-blue-900"><?= e((string) $summary['active']); ?></p>
            <p class="mt-2 text-xs text-blue-700">Proyectos en planeacion, progreso o revision.</p>
          </article>
          <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">Finalizados</p>
            <p class="mt-3 text-3xl font-semibold text-emerald-900"><?= e((string) $summary['finished']); ?></p>
            <p class="mt-2 text-xs text-emerald-700">Proyectos completados y validados.</p>
          </article>
          <article class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">Entregas proximas</p>
            <p class="mt-3 text-3xl font-semibold text-amber-900"><?= e((string) $summary['dueSoon']); ?></p>
            <p class="mt-2 text-xs text-amber-700">Fechas dentro de los siguientes 7 dias.</p>
          </article>
        </div>

        <div class="grid gap-6 lg:grid-cols-<?= $isDirector ? '3' : '2'; ?>">
          <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <h2 class="text-lg font-semibold text-slate-900">Tu lista de proyectos</h2>
                  <p class="text-sm text-slate-500">Ordenados del mas reciente al mas antiguo.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                  <?= count($projects); ?> proyectos
                </span>
              </div>
              <?php if (!$projects): ?>
                <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                  <p class="text-sm font-medium text-slate-600">Aun no hay proyectos registrados.</p>
                  <?php if ($isDirector): ?>
                    <p class="mt-1 text-xs text-slate-500">Completa el formulario para crear el primero.</p>
                  <?php else: ?>
                    <p class="mt-1 text-xs text-slate-500">Tu director te asignara un proyecto para iniciar.</p>
                  <?php endif; ?>
                </div>
              <?php else: ?>
                <div class="mt-6 space-y-4">
                  <?php foreach ($projects as $project): ?>
                    <?php
                      $statusKey = $project['status'] ?? 'planeacion';
                      $style = $statusStyles[$statusKey] ?? $statusStyles['planeacion'];
                      $dueText = 'Sin fecha limite';
                      if (!empty($project['due_date'])) {
                          $due = DateTimeImmutable::createFromFormat('Y-m-d', $project['due_date']);
                          if ($due) {
                              $dueText = 'Entrega: ' . $due->format('d/m/Y');
                          }
                      }
                    ?>
                    <article class="rounded-2xl border <?= e($style['border']); ?> p-4">
                      <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                          <h3 class="text-base font-semibold text-slate-900"><?= e($project['title']); ?></h3>
                          <p class="mt-1 text-xs text-slate-500"><?= e($dueText); ?></p>
                        </div>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?= e($style['chip']); ?>">
                          <?= e($style['label']); ?>
                        </span>
                      </div>
                      <?php if (!empty($project['description'])): ?>
                        <p class="mt-3 text-sm text-slate-600"><?= e($project['description']); ?></p>
                      <?php endif; ?>
                      <div class="mt-4 grid gap-3 text-xs text-slate-500 sm:grid-cols-2">
                        <?php if (!empty($project['student_name'])): ?>
                          <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                            <p class="font-semibold text-slate-700">Estudiante</p>
                            <p class="mt-1 text-xs text-slate-500"><?= e($project['student_name']); ?></p>
                          </div>
                        <?php endif; ?>
                        <?php if (!empty($project['director_name'])): ?>
                          <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                            <p class="font-semibold text-slate-700">Director</p>
                            <p class="mt-1 text-xs text-slate-500"><?= e($project['director_name']); ?></p>
                          </div>
                        <?php endif; ?>
                      </div>
                    </article>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <div id="kanban" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <h2 class="text-lg font-semibold text-slate-900">Tablero simplificado</h2>
                  <p class="text-sm text-slate-500">Vista rapida de estados. Usa esto para priorizar tus siguientes modulos.</p>
                </div>
              </div>
              <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <?php foreach ($kanban as $statusKey => $items): ?>
                  <?php $style = $statusStyles[$statusKey] ?? $statusStyles['planeacion']; ?>
                  <div class="flex flex-col rounded-2xl border <?= e($style['border']); ?> bg-white/70 p-4">
                    <div class="flex items-center justify-between gap-3">
                      <h3 class="text-sm font-semibold text-slate-700"><?= e($style['label']); ?></h3>
                      <span class="rounded-full bg-white/80 px-2 py-1 text-xs font-semibold text-slate-600"><?= count($items); ?></span>
                    </div>
                    <div class="mt-4 space-y-4">
                      <?php if (!$items): ?>
                        <p class="text-xs text-slate-400">Sin proyectos en este estado.</p>
                      <?php else: ?>
                        <?php foreach ($items as $item): ?>
                          <?php
                            $dueLabel = 'Sin fecha';
                            if (!empty($item['due_date'])) {
                                $dueObj = DateTimeImmutable::createFromFormat('Y-m-d', $item['due_date']);
                                if ($dueObj) {
                                    $dueLabel = $dueObj->format('d/m');
                                }
                            }
                          ?>
                          <article class="rounded-xl border border-slate-200 bg-white px-3 py-3 text-xs shadow-sm">
                            <p class="font-semibold text-slate-700"><?= e($item['title']); ?></p>
                            <p class="mt-2 text-[11px] text-slate-400">Entrega <?= e($dueLabel); ?></p>
                          </article>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
              <div class="flex items-center justify-between gap-2">
                <h2 class="text-lg font-semibold text-slate-900">Entregas proximas</h2>
                <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600"><?= count($upcoming); ?></span>
              </div>
              <div class="mt-4 space-y-4">
                <?php if (!$upcoming): ?>
                  <p class="text-xs text-slate-400">No hay entregas registradas en los proximos dias.</p>
                <?php else: ?>
                  <?php foreach ($upcoming as $entry): ?>
                    <?php $project = $entry['project']; $due = $entry['due_date']; ?>
                    <article class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                      <p class="font-semibold text-amber-900"><?= e($project['title']); ?></p>
                      <p class="mt-1 text-xs text-amber-700">Entrega <?= e($due->format('d/m/Y')); ?></p>
                    </article>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>

            <?php if ($isDirector): ?>
              <div id="nuevo" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                  <div>
                    <h2 class="text-lg font-semibold text-slate-900">Crear nuevo proyecto</h2>
                    <p class="text-sm text-slate-500">Asigna responsable, fecha de entrega y contexto inicial.</p>
                  </div>
                </div>

                <?php if ($hasProjectError('general')): ?>
                  <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                    <?= e($projectErrorText('general')); ?>
                  </div>
                <?php endif; ?>

                <form class="mt-5 grid gap-4" method="post" action="<?= e(url('/projects')); ?>">
                  <div>
                    <label for="project-title" class="mb-1 block text-sm font-semibold text-slate-700">Titulo del proyecto</label>
                    <input id="project-title" name="title" type="text" required value="<?= $oldValue('title'); ?>" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200 <?= $hasProjectError('title') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : ''; ?>" placeholder="Ej. Sistema de seguimiento" />
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
                          <?= e($student['full_name']); ?><?= !empty($student['matricula']) ? ' · '.e($student['matricula']) : ''; ?>
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
                    <textarea id="project-description" name="description" rows="4" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200 <?= $hasProjectError('description') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : ''; ?>" placeholder="Describe objetivos, entregables iniciales y alcance"><?= $oldValue('description'); ?></textarea>
                  </div>

                  <div class="flex items-center justify-end gap-2">
                    <a href="<?= e(url('/dashboard')); ?>" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">Cancelar</a>
                    <button type="submit" class="inline-flex items-center rounded-xl bg-[#1E3A8A] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1B307C] focus:outline-none focus:ring-2 focus:ring-indigo-200">
                      Guardar proyecto
                    </button>
                  </div>
                </form>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <footer class="mt-auto border-t border-slate-200 bg-white px-4 py-3 text-xs text-slate-500">
        Gestor de titulacion · Panel principal · <?= date('Y'); ?>
      </footer>
    </main>
  </div>
</body>
</html>
