<?php

$user = $user ?? null;
$projects = $projects ?? [];
$recentProjects = $recentProjects ?? array_slice($projects, 0, 5);
$summary = $summary ?? ['total' => 0, 'active' => 0, 'finished' => 0, 'dueSoon' => 0];
$statusSummary = $statusSummary ?? ['planeacion' => 0, 'en_progreso' => 0, 'en_revision' => 0, 'finalizado' => 0];
$upcoming = $upcoming ?? [];
$success = $success ?? null;

$isDirector = ($user['role'] ?? '') === 'director';
$firstName = $user ? explode(' ', (string) $user['full_name'])[0] : 'Usuario';

$statusCatalog = [
    'planeacion' => ['label' => 'Planeacion', 'chip' => 'bg-slate-200 text-slate-700'],
    'en_progreso' => ['label' => 'En progreso', 'chip' => 'bg-blue-100 text-blue-700'],
    'en_revision' => ['label' => 'En revision', 'chip' => 'bg-amber-100 text-amber-700'],
    'finalizado' => ['label' => 'Finalizado', 'chip' => 'bg-emerald-100 text-emerald-700'],
];

$today = new DateTimeImmutable('today');
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
  <div class="min-h-dvh">
    <header class="border-b border-slate-200 bg-white">
      <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.3em] text-indigo-500">Gestor</p>
          <h1 class="mt-1 text-2xl font-semibold text-slate-900">Hola, <?= e($firstName); ?></h1>
          <p class="mt-1 text-sm text-slate-500">Administra hitos y avances de titulacion desde este panel.</p>
        </div>
        <form method="post" action="<?= e(url('/logout')); ?>" class="self-start sm:self-auto">
          <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100">
            Cerrar sesion
          </button>
        </form>
      </div>
    </header>

    <main class="mx-auto max-w-6xl space-y-8 px-4 py-8">
      <?php if ($success): ?>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
          <?= e($success); ?>
        </div>
      <?php endif; ?>

      <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
          <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Proyectos totales</p>
          <p class="mt-2 text-3xl font-semibold text-slate-900"><?= e((string) $summary['total']); ?></p>
          <p class="mt-1 text-xs text-slate-500">Incluye activos y finalizados.</p>
        </article>
        <article class="rounded-2xl border border-blue-200 bg-blue-50 p-4 shadow-sm">
          <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">Activos</p>
          <p class="mt-2 text-3xl font-semibold text-blue-700"><?= e((string) $summary['active']); ?></p>
          <p class="mt-1 text-xs text-blue-600">Proyectos en planeacion, progreso o revision.</p>
        </article>
        <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
          <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Finalizados</p>
          <p class="mt-2 text-3xl font-semibold text-emerald-700"><?= e((string) $summary['finished']); ?></p>
          <p class="mt-1 text-xs text-emerald-600">Proyectos concluidos y aprobados.</p>
        </article>
        <article class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
          <p class="text-xs font-semibold uppercase tracking-wider text-amber-600">Entrega cercana</p>
          <p class="mt-2 text-3xl font-semibold text-amber-700"><?= e((string) $summary['dueSoon']); ?></p>
          <p class="mt-1 text-xs text-amber-600">Vence en los proximos 7 dias.</p>
        </article>
      </section>

      <section class="grid gap-6 lg:grid-cols-[2fr_1fr]">
        <div class="space-y-6">
          <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-lg font-semibold text-slate-900">Estado de proyectos</h2>
                <p class="text-sm text-slate-500">Distribucion resumida por etapa.</p>
              </div>
              <a href="<?= e(url('/projects')); ?>" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                Abrir gestion de proyectos
              </a>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
              <?php foreach ($statusSummary as $key => $count): ?>
                <?php $style = $statusCatalog[$key] ?? $statusCatalog['planeacion']; ?>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                  <p class="text-xs font-semibold uppercase tracking-wider text-slate-500"><?= e($style['label']); ?></p>
                  <p class="mt-2 text-2xl font-semibold text-slate-900"><?= e((string) $count); ?></p>
                  <span class="mt-3 inline-flex w-auto items-center rounded-full px-3 py-1 text-xs font-semibold <?= e($style['chip']); ?>">
                    <?= e($style['label']); ?>
                  </span>
                </div>
              <?php endforeach; ?>
            </div>
          </article>

          <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-lg font-semibold text-slate-900">Ultimos proyectos</h2>
                <p class="text-sm text-slate-500">Resumen de las cinco iniciativas mas recientes.</p>
              </div>
              <span class="text-xs font-medium text-slate-400">Actualizado automaticamente</span>
            </div>
            <?php if ($recentProjects === []): ?>
              <p class="mt-4 text-sm text-slate-500">Aun no hay proyectos registrados.</p>
            <?php else: ?>
              <ul class="mt-4 space-y-3">
                <?php foreach ($recentProjects as $project): ?>
                  <?php
                    $status = $project['status'] ?? 'planeacion';
                    $style = $statusCatalog[$status] ?? $statusCatalog['planeacion'];
                    $counterLabel = $isDirector ? 'Estudiante' : 'Director';
                    $counterValue = $isDirector ? ($project['student_name'] ?? 'Sin asignar') : ($project['director_name'] ?? 'Sin asignar');
                    $createdAtText = '';
                    if (!empty($project['created_at'])) {
                        $createdAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) $project['created_at']);
                        $createdAtText = $createdAt instanceof DateTimeImmutable ? $createdAt->format('d/m/Y') : '';
                    }
                  ?>
                  <li class="flex items-start justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div>
                      <p class="font-semibold text-slate-900"><?= e($project['title']); ?></p>
                      <p class="mt-1 text-xs text-slate-500"><?= e($counterLabel); ?>: <?= e($counterValue); ?></p>
                    </div>
                    <div class="text-right">
                      <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?= e($style['chip']); ?>">
                        <?= e($style['label']); ?>
                      </span>
                      <?php if ($createdAtText !== ''): ?>
                        <p class="mt-2 text-xs text-slate-500">Creado <?= e($createdAtText); ?></p>
                      <?php endif; ?>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </article>
        </div>

        <aside class="space-y-6">
          <article class="rounded-2xl border border-indigo-100 bg-indigo-50 p-5 text-sm text-indigo-700">
            <h2 class="text-sm font-semibold text-indigo-900">Proximos hitos</h2>
            <?php if ($upcoming === []): ?>
              <p class="mt-3 text-xs text-indigo-700/80">No hay entregas programadas dentro de los proximos dias.</p>
            <?php else: ?>
              <ul class="mt-3 space-y-3">
                <?php foreach ($upcoming as $entry): ?>
                  <?php
                    $project = $entry['project'];
                    /** @var DateTimeImmutable $due */
                    $due = $entry['due_date'];
                    $diffDays = $today->diff($due)->days;
                    $isPast = $due < $today;
                  ?>
                  <li class="rounded-xl border border-indigo-100 bg-white p-3">
                    <p class="text-sm font-semibold text-slate-900"><?= e($project['title']); ?></p>
                    <p class="mt-1 text-xs text-slate-500">Entrega <?= e($due->format('d/m/Y')); ?></p>
                    <p class="mt-2 text-xs font-semibold <?= e($isPast ? 'text-red-600' : ($diffDays <= 7 ? 'text-amber-600' : 'text-indigo-600')); ?>">
                      <?php if ($isPast): ?>
                        Fecha vencida
                      <?php elseif ($diffDays === 0): ?>
                        Entrega hoy
                      <?php else: ?>
                        Faltan <?= e((string) $diffDays); ?> dias
                      <?php endif; ?>
                    </p>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </article>

          <article class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Modulos del sistema</h2>
            <p class="text-sm text-slate-500">Accede a cada funcionalidad segun la etapa de trabajo.</p>
            <div class="space-y-3">
              <a href="<?= e(url('/projects')); ?>" class="block rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
                Gestion de proyectos
                <span class="mt-1 block text-xs font-normal text-indigo-600">Crear, asignar y seguir proyectos por fase.</span>
              </a>
              <div class="block rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                Hitos y entregables
                <span class="mt-1 block text-xs text-slate-400">Proximamente.</span>
              </div>
              <div class="block rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                Comentarios y feedback
                <span class="mt-1 block text-xs text-slate-400">Proximamente.</span>
              </div>
              <div class="block rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                Visualizacion de progreso
                <span class="mt-1 block text-xs text-slate-400">Proximamente.</span>
              </div>
            </div>
          </article>
        </aside>
      </section>
    </main>
  </div>
</body>
</html>



