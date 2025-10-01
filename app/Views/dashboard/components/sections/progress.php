<?php
$isProgressActive = ($activeTab ?? 'dashboard') === 'progreso';
$boardMap = [
    'pendiente' => 'Pendiente',
    'en_progreso' => 'En progreso',
    'en_revision' => 'En revisión',
    'aprobado' => 'Aprobado',
];
?>
<section id="section-progreso" data-section="progreso" class="space-y-8<?= $isProgressActive ? '' : ' hidden'; ?>">
  <header class="flex flex-wrap items-start justify-between gap-3">
    <div class="space-y-1">
      <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Tablero de progreso</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400">Visualiza el flujo de trabajo por estado y detecta rápidamente los hitos que requieren atención.</p>
    </div>
  </header>

  <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
    <?php foreach ($boardMap as $columnKey => $columnLabel): ?>
      <?php $items = $boardColumns[$columnKey] ?? []; ?>
      <article class="flex h-full flex-col rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 dark:bg-slate-900 dark:ring-slate-800">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200"><?= e($columnLabel); ?></h3>
          <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-300">
            <?= e((string) count($items)); ?>
          </span>
        </div>
        <div class="mt-4 flex-1 space-y-3 overflow-y-auto pr-1 scrollbar-thin">
          <?php if ($items === []): ?>
            <p class="rounded-xl bg-slate-50 px-3 py-4 text-xs text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">Sin elementos.</p>
          <?php else: ?>
            <?php foreach ($items as $item): ?>
              <div class="rounded-2xl bg-slate-50/80 p-3 text-xs ring-1 ring-slate-200/70 transition hover:ring-indigo-200 dark:bg-slate-900/40 dark:ring-slate-800/70">
                <p class="font-semibold text-slate-700 dark:text-slate-100"><?= e($item['title']); ?></p>
                <p class="mt-1 text-[11px] text-slate-500">Proyecto: <?= e($item['project_title']); ?></p>
                <p class="mt-2 text-[11px] text-slate-400">Periodo: <?= e(format_dashboard_period($item['start_date'] ?? null, $item['end_date'] ?? ($item['due_date'] ?? null))); ?></p>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
