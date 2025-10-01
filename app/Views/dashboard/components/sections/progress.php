<?php
$isProgressActive = ($activeTab ?? 'dashboard') === 'progreso';
$boardMap = [
    'pendiente' => 'Pendiente',
    'en_progreso' => 'En progreso',
    'en_revision' => 'En revisión',
    'aprobado' => 'Aprobado',
];
?>
<section id="section-progreso" data-section="progreso" class="mt-8 space-y-6<?= $isProgressActive ? '' : ' hidden'; ?>">
  <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    <?php foreach ($boardMap as $columnKey => $columnLabel): ?>
      <?php $items = $boardColumns[$columnKey] ?? []; ?>
      <article class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200"><?= e($columnLabel); ?></h3>
          <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-[11px] text-slate-500 dark:bg-slate-800">
            <?= e((string) count($items)); ?>
          </span>
        </div>
        <div class="mt-3 flex-1 space-y-3 overflow-y-auto pr-1 scrollbar-thin">
          <?php if ($items === []): ?>
            <p class="text-xs text-slate-500">Sin elementos.</p>
          <?php else: ?>
            <?php foreach ($items as $item): ?>
              <article class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs dark:border-slate-800 dark:bg-slate-900/50">
                <p class="font-semibold text-slate-700 dark:text-slate-100"><?= e($item['title']); ?></p>
                <p class="mt-1 text-[11px] text-slate-500">Proyecto: <?= e($item['project_title']); ?></p>
                <p class="mt-2 text-[11px] text-slate-400">Entrega: <?= e(format_dashboard_date($item['due_date'] ?? null)); ?></p>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
