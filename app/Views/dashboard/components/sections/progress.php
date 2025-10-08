<?php
$isProgressActive = ($activeTab ?? 'dashboard') === 'progreso';
$boardMap = [
    'pendiente' => 'Pendiente',
    'en_progreso' => 'En progreso',
    'en_revision' => 'En revision',
    'aprobado' => 'Aprobado',
];
$boardColumnsList = is_array($boardColumns ?? null) ? $boardColumns : [];
$totalItems = 0;
foreach ($boardMap as $columnKey => $_columnLabel) {
    $totalItems += count($boardColumnsList[$columnKey] ?? []);
}
?>
<section
  id="section-progreso"
  data-section="progreso"
  data-selected-project="<?= e((string) ($selectedProject['id'] ?? 0)); ?>"
  class="space-y-8<?= $isProgressActive ? '' : ' hidden'; ?>"
>
  <article class="rounded-3xl border border-slate-200/70 bg-gradient-to-br from-white via-white to-indigo-50/40 p-6 shadow-lg shadow-indigo-100/50 transition dark:border-slate-800/70 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950/70 dark:shadow-none">
    <div class="flex flex-col gap-6">
      <header class="flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-300">
        <span class="inline-flex items-center gap-2 rounded-full border border-indigo-200/70 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 shadow-sm dark:border-indigo-900/60 dark:bg-indigo-950/50 dark:text-indigo-200">
          <i data-lucide="circle-check" class="h-3.5 w-3.5"></i>
          <?= e((string) $totalItems); ?> registros
        </span>
        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200/70 bg-white/80 px-3 py-1 text-xs font-semibold text-slate-600 shadow-sm dark:border-slate-700/60 dark:bg-slate-900/70 dark:text-slate-200">
          <i data-lucide="rows" class="h-3.5 w-3.5 text-indigo-400"></i>
          <?= e((string) count($boardMap)); ?> columnas
        </span>
      </header>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <?php foreach ($boardMap as $columnKey => $columnLabel): ?>
          <?php $items = $boardColumnsList[$columnKey] ?? []; ?>
          <article class="flex h-full flex-col rounded-2xl border border-slate-200/60 bg-white/80 p-5 text-xs shadow-lg shadow-slate-200/60 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-xl dark:border-slate-800/60 dark:bg-slate-900/60 dark:shadow-none">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200">
                <i data-lucide="diamond" class="h-3.5 w-3.5 text-indigo-400"></i>
                <span><?= e($columnLabel); ?></span>
              </div>
              <span class="inline-flex items-center gap-2 rounded-full border border-slate-200/70 bg-white px-2.5 py-0.5 text-[11px] font-semibold text-slate-600 shadow-sm dark:border-slate-700/60 dark:bg-slate-900/70 dark:text-slate-200">
                <i data-lucide="circle-check" class="h-3 w-3"></i>
                <?= e((string) count($items)); ?>
              </span>
            </div>
            <div class="mt-4 flex-1 space-y-3 overflow-y-auto pr-1 scrollbar-thin">
              <?php if ($items === []): ?>
                <p class="rounded-xl border border-dashed border-slate-300/80 bg-slate-50/70 px-3 py-4 text-center text-slate-500 dark:border-slate-700/60 dark:bg-slate-900/50">Sin elementos.</p>
              <?php else: ?>
                <?php foreach ($items as $item): ?>
                  <div class="rounded-xl border border-slate-200/70 bg-white/90 px-3 py-3 text-xs shadow-sm transition hover:border-indigo-200 hover:bg-white dark:border-slate-800/60 dark:bg-slate-950/40 dark:hover:border-indigo-900/50">
                    <p class="font-semibold text-slate-700 dark:text-slate-100"><?= e($item['title']); ?></p>
                    <p class="mt-1 text-[11px] text-slate-500">Proyecto: <?= e($item['project_title']); ?></p>
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px] text-slate-400">
                      <span class="inline-flex items-center gap-1 rounded-full border border-slate-200/70 bg-white/70 px-2.5 py-0.5 shadow-sm dark:border-slate-700/60 dark:bg-slate-900/70">
                        <i data-lucide="calendar" class="h-3 w-3 text-indigo-400"></i>
                        <?= e(format_dashboard_period($item['start_date'] ?? null, $item['end_date'] ?? ($item['due_date'] ?? null))); ?>
                      </span>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </article>
</section>




