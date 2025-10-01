<?php
$isProgressActive = ($activeTab ?? 'dashboard') === 'progreso';
$boardMap = [
    'pendiente' => 'Pendiente',
    'en_progreso' => 'En progreso',
    'en_revision' => 'En revisión',
    'aprobado' => 'Aprobado',
];
?>
<section id="section-progreso" data-section="progreso" class="mt-10 space-y-6<?= $isProgressActive ? '' : ' hidden'; ?>">
  <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
    <?php foreach ($boardMap as $columnKey => $columnLabel): ?>
      <?php $items = $boardColumns[$columnKey] ?? []; ?>
      <article class="surface flex h-full flex-col gap-4 p-5">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200"><?= e($columnLabel); ?></h3>
          <span class="badge-soft">
            <?= e((string) count($items)); ?>
          </span>
        </div>
        <div class="flex-1 space-y-3 overflow-y-auto pr-1 scrollbar-thin">
          <?php if ($items === []): ?>
            <p class="text-xs text-slate-500">Sin elementos.</p>
          <?php else: ?>
            <?php foreach ($items as $item): ?>
              <article class="surface-muted p-3 text-xs dark:text-slate-200">
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
