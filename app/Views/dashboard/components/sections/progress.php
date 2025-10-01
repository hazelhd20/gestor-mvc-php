<?php
$isProgressActive = ($activeTab ?? 'dashboard') === 'progreso';
$boardMap = [
    'pendiente' => 'Pendiente',
    'en_progreso' => 'En progreso',
    'en_revision' => 'En revisión',
    'aprobado' => 'Aprobado',
];
?>
<section id="section-progreso" data-section="progreso" class="flex flex-col gap-6<?= $isProgressActive ? '' : ' hidden'; ?>">
  <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
    <?php foreach ($boardMap as $columnKey => $columnLabel): ?>
      <?php $items = $boardColumns[$columnKey] ?? []; ?>
      <article class="flex h-full flex-col rounded-3xl glass-card p-5 shadow-xl">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200"><?= e($columnLabel); ?></h3>
          <span class="inline-flex items-center gap-1 rounded-full bg-slate-500/10 px-3 py-0.5 text-[11px] font-semibold text-slate-600 dark:bg-slate-700/40 dark:text-slate-200">
            <i data-lucide="kanban" class="h-3.5 w-3.5"></i>
            <?= e((string) count($items)); ?>
          </span>
        </div>
        <div class="mt-4 flex-1 space-y-3 overflow-y-auto pr-1 scrollbar-thin">
          <?php if ($items === []): ?>
            <p class="rounded-2xl border border-dashed border-slate-200/70 bg-white/60 px-3 py-4 text-center text-xs text-slate-500 dark:border-slate-700/60 dark:bg-slate-900/40">Sin elementos.</p>
          <?php else: ?>
            <?php foreach ($items as $item): ?>
              <article class="rounded-2xl border border-slate-200/60 bg-white/80 p-3 text-xs shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800/60 dark:bg-slate-900/60">
                <p class="font-semibold text-slate-700 dark:text-slate-100"><?= e($item['title']); ?></p>
                <p class="mt-1 text-[11px] text-slate-500">Proyecto: <?= e($item['project_title']); ?></p>
                <p class="mt-2 inline-flex items-center gap-1 rounded-full bg-indigo-500/10 px-2.5 py-1 text-[11px] font-medium text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-200">
                  <i data-lucide="alarm-clock" class="h-3 w-3"></i>
                  <?= e(format_dashboard_date($item['due_date'] ?? null)); ?>
                </p>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
