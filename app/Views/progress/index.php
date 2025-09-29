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

ob_start();
?>
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
    <header class="mb-4 flex items-center justify-between">
      <div>
        <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">Avance por proyecto</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400">Progreso calculado con base en hitos completados.</p>
      </div>
      <div class="text-right text-xs text-slate-500 dark:text-slate-400">
        <p class="uppercase tracking-wide text-[10px] text-slate-400">Tasa de finalizacion</p>
        <p class="text-lg font-semibold text-slate-800 dark:text-slate-100"><?= $completionRate; ?>%</p>
      </div>
    </header>

    <?php if ($projectsProgress === []): ?>
      <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50">
        Aun no hay hitos registrados en tus proyectos.
      </div>
    <?php else: ?>
      <ul class="space-y-3">
        <?php foreach ($projectsProgress as $project): ?>
          <li class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-900/70">
            <div class="flex items-start justify-between gap-3">
              <div>
                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100"><?= e($project['title']); ?></h3>
                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Estado: <?= e(ucwords(str_replace('_', ' ', $project['status']))); ?></p>
              </div>
              <p class="text-sm font-semibold text-slate-700 dark:text-slate-200"><?= $project['progress']; ?>%</p>
            </div>
            <div class="mt-3 h-2 w-full rounded-full bg-slate-200 dark:bg-slate-800">
              <div class="h-full rounded-full bg-indigo-500 dark:bg-indigo-400" style="width: <?= $project['progress']; ?>%"></div>
            </div>
            <dl class="mt-2 flex flex-wrap gap-4 text-[11px] text-slate-500 dark:text-slate-400">
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
            <li class="rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-800 dark:bg-slate-900">
              <p class="font-semibold text-slate-700 dark:text-slate-200"><?= e($item['title']); ?></p>
              <p class="text-[11px] text-slate-500 dark:text-slate-400"><?= e($item['project']); ?></p>
              <p class="mt-1 text-[11px] <?= $item['overdue'] ? 'text-rose-500' : 'text-slate-500'; ?>">Entrega: <?= e($item['due_date']); ?><?= $item['overdue'] ? ' (atrasado)' : ''; ?></p>
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
            <li class="rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-800 dark:bg-slate-900">
              <p class="font-semibold text-slate-700 dark:text-slate-200"><?= e($item['title']); ?></p>
              <p class="text-[11px] text-slate-500 dark:text-slate-400"><?= e($item['project']); ?></p>
              <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Actualizado: <?= e(substr($item['updated_at'], 0, 16)); ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
  </aside>
</div>
<?php
$pageContent = ob_get_clean();
require __DIR__ . '/../layouts/app-shell.php';