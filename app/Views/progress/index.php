<?php
$pageTitle = 'Visualizacion de progreso';
$pageSubtitle = 'Resumen global de proyectos, hitos y proximas entregas';
$activeNav = 'progreso';
$projects = $projects ?? [];
$projectsProgress = $projectsProgress ?? [];
$statusCounts = $statusCounts ?? [];
$milestoneCounts = $milestoneCounts ?? [];
$completionRate = $completionRate ?? 0;
$upcoming = $upcoming ?? [];
$recent = $recent ?? [];

$cardData = [
    ['label' => 'Proyectos activos', 'value' => count($projects), 'icon' => 'briefcase'],
    ['label' => 'Hitos totales', 'value' => $milestoneCounts['total'] ?? 0, 'icon' => 'git-branch'],
    ['label' => 'Completados', 'value' => $milestoneCounts['completado'] ?? 0, 'icon' => 'check-circle'],
    ['label' => 'En revision', 'value' => $milestoneCounts['en_revision'] ?? 0, 'icon' => 'inbox'],
];

$statusPalette = [
    'planeacion' => ['label' => 'Planeacion', 'color' => 'bg-sky-500'],
    'en_progreso' => ['label' => 'En progreso', 'color' => 'bg-indigo-500'],
    'en_revision' => ['label' => 'En revision', 'color' => 'bg-amber-500'],
    'finalizado' => ['label' => 'Finalizado', 'color' => 'bg-emerald-500'],
];

$progressInsights = [
    [
        'title' => 'Tasa de finalizacion',
        'value' => $completionRate . '%',
        'description' => 'Porcentaje promedio de hitos completados en todos los proyectos activos.',
        'icon' => 'activity',
    ],
    [
        'title' => 'Hitos pendientes',
        'value' => max(0, ($milestoneCounts['total'] ?? 0) - ($milestoneCounts['completado'] ?? 0)),
        'description' => 'Entregables que requieren atencion para alcanzar la meta de titulacion.',
        'icon' => 'flag',
    ],
    [
        'title' => 'En revision',
        'value' => $milestoneCounts['en_revision'] ?? 0,
        'description' => 'Hitos que esperan feedback por parte de los directores.',
        'icon' => 'file-search',
    ],
];

ob_start();
?>
<section class="mb-5 rounded-3xl border border-slate-200 bg-gradient-to-br from-indigo-600 via-indigo-500 to-sky-500 p-6 text-white shadow-sm dark:border-slate-800">
  <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
    <div>
      <p class="text-xs uppercase tracking-[0.2em] text-indigo-100/80">Panel de progreso</p>
      <h2 class="mt-2 text-2xl font-semibold">Visibilidad integral de proyectos y avances</h2>
      <p class="mt-1 max-w-2xl text-sm text-indigo-100/90">Consulta el estado agregado de los proyectos de titulacion, detecta cuellos de botella y prioriza acciones para mantener el ritmo de entrega.</p>
    </div>
    <div class="grid gap-3 text-sm lg:grid-cols-3">
      <?php foreach ($progressInsights as $insight): ?>
        <article class="rounded-2xl bg-white/15 p-4 shadow-sm">
          <div class="flex items-center gap-2 text-xs uppercase tracking-wide text-indigo-100/80">
            <i data-lucide="<?= e($insight['icon']); ?>" class="h-4 w-4"></i>
            <?= e($insight['title']); ?>
          </div>
          <p class="mt-2 text-xl font-semibold text-white"><?= e($insight['value']); ?></p>
          <p class="mt-1 text-[11px] text-indigo-100/80"><?= e($insight['description']); ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
  <?php foreach ($cardData as $card): ?>
    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-slate-500 dark:text-slate-400"><?= e($card['label']); ?></p>
          <p class="mt-2 text-2xl font-semibold text-slate-800 dark:text-slate-100"><?= e($card['value']); ?></p>
        </div>
        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-200">
          <i data-lucide="<?= e($card['icon']); ?>" class="h-5 w-5"></i>
        </span>
      </div>
    </article>
  <?php endforeach; ?>
</div>

<div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-[2fr,1fr]">
  <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <header class="mb-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">Avance por proyecto</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400">Progreso calculado con base en hitos completados.</p>
      </div>
      <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
        <?php foreach ($statusPalette as $statusKey => $meta): ?>
          <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 font-medium shadow-sm dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300">
            <span class="h-1.5 w-1.5 rounded-full <?= $meta['color']; ?>"></span>
            <?= e($meta['label']); ?> (<?= (int) ($statusCounts[$statusKey] ?? 0); ?>)
          </span>
        <?php endforeach; ?>
      </div>
    </header>

    <?php if ($projectsProgress === []): ?>
      <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50">
        Aun no hay hitos registrados en tus proyectos.
      </div>
    <?php else: ?>
      <ul class="space-y-3">
        <?php foreach ($projectsProgress as $project): ?>
          <li class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/70 p-4 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-white hover:shadow-lg dark:border-slate-800 dark:bg-slate-900/70 dark:hover:border-indigo-500/40">
            <span class="absolute inset-y-0 left-0 w-1 bg-gradient-to-b from-indigo-500 via-indigo-400 to-sky-400 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></span>
            <div class="flex items-start justify-between gap-3 pl-1">
              <div class="space-y-2">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100"><?= e($project['title']); ?></h3>
                <p class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                  <span class="inline-flex items-center gap-1 rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <?= e(ucwords(str_replace('_', ' ', $project['status']))); ?>
                  </span>
                  &bull; <?= $project['done']; ?>/<?= $project['total']; ?> hitos completados
                </p>
              </div>
              <div class="text-right">
                <p class="text-xl font-semibold text-indigo-600 dark:text-indigo-400"><?= $project['progress']; ?>%</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Avance acumulado</p>
              </div>
            </div>
            <div class="mt-3 h-2 w-full rounded-full bg-slate-200 dark:bg-slate-800">
              <div class="h-full rounded-full bg-indigo-500 transition-all dark:bg-indigo-400" style="width: <?= $project['progress']; ?>%"></div>
            </div>
            <dl class="mt-3 flex flex-wrap gap-4 text-[11px] text-slate-500 dark:text-slate-400">
              <div>
                <dt class="uppercase tracking-wide text-[10px] text-slate-400">Hitos</dt>
                <dd><?= $project['done']; ?>/<?= $project['total']; ?></dd>
              </div>
              <div>
                <dt class="uppercase tracking-wide text-[10px] text-slate-400">En revision</dt>
                <dd><?= $project['waiting_review']; ?></dd>
              </div>
            </dl>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>

  <aside class="space-y-4">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Proximas entregas</h2>
      <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Vencimientos dentro de los siguientes 7 dias.</p>
      <?php if ($upcoming === []): ?>
        <p class="mt-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-3 py-4 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50">Sin entregas proximas.</p>
      <?php else: ?>
        <ul class="mt-3 space-y-2 text-xs text-slate-600 dark:text-slate-300">
          <?php foreach ($upcoming as $item): ?>
            <li class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-center justify-between">
                <p class="font-semibold text-slate-700 dark:text-slate-200"><?= e($item['title']); ?></p>
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold <?= $item['overdue'] ? 'bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-200' : 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-200'; ?>">
                  <i data-lucide="clock" class="h-3.5 w-3.5"></i>
                  <?= $item['overdue'] ? 'Atrasado' : 'A tiempo'; ?>
                </span>
              </div>
              <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400"><?= e($item['project']); ?></p>
              <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Entrega: <?= e($item['due_date']); ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Actividad reciente</h2>
      <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Ultimos hitos marcados como completados.</p>
      <?php if ($recent === []): ?>
        <p class="mt-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-3 py-4 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50">Aun no hay hitos completados recientemente.</p>
      <?php else: ?>
        <ul class="mt-3 space-y-2 text-xs text-slate-600 dark:text-slate-300">
          <?php foreach ($recent as $item): ?>
            <li class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
              <p class="font-semibold text-slate-700 dark:text-slate-200"><?= e($item['title']); ?></p>
              <p class="text-[11px] text-slate-500 dark:text-slate-400"><?= e($item['project']); ?></p>
              <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Actualizado: <?= e(substr($item['updated_at'], 0, 16)); ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Recomendaciones</h2>
      <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Acciones sugeridas para mejorar el seguimiento.</p>
      <ul class="mt-3 space-y-2 text-xs text-slate-600 dark:text-slate-300">
        <li class="flex items-start gap-2"><i data-lucide="sparkles" class="mt-0.5 h-4 w-4 flex-none"></i><span>Reconoce avances completos en las reuniones semanales para mantener la motivacion.</span></li>
        <li class="flex items-start gap-2"><i data-lucide="bell-ring" class="mt-0.5 h-4 w-4 flex-none"></i><span>Agenda recordatorios para hitos con fecha proxima desde el modulo de hitos.</span></li>
        <li class="flex items-start gap-2"><i data-lucide="users" class="mt-0.5 h-4 w-4 flex-none"></i><span>Comparte este reporte con directores para alinear expectativas.</span></li>
      </ul>
    </section>
  </aside>
</div>
<?php
$pageContent = ob_get_clean();
require __DIR__ . '/../layouts/app-shell.php';