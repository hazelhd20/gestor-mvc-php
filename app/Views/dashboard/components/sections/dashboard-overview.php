<?php
$isDashboardActive = ($activeTab ?? 'dashboard') === 'dashboard';
$statCards = [
    [
        'label' => 'Proyectos totales',
        'value' => $stats['total'] ?? 0,
        'icon' => 'layers',
        'accent' => 'text-indigo-500',
    ],
    [
        'label' => 'Activos',
        'value' => $stats['active'] ?? 0,
        'icon' => 'activity',
        'accent' => 'text-emerald-500',
    ],
    [
        'label' => 'Completados',
        'value' => $stats['completed'] ?? 0,
        'icon' => 'check-circle',
        'accent' => 'text-emerald-500',
    ],
    [
        'label' => 'Vencen pronto (7 dias)',
        'value' => $stats['due_soon'] ?? 0,
        'icon' => 'clock',
        'accent' => 'text-amber-500',
    ],
];
$upcomingCount = count($upcomingMilestones ?? []);
$recentFeedbackCount = count($recentFeedback ?? []);
?>
<section id="section-dashboard" data-section="dashboard" class="space-y-8<?= $isDashboardActive ? '' : ' hidden'; ?>">
  <article class="rounded-3xl border border-slate-200/70 bg-gradient-to-br from-white via-white to-indigo-50/40 p-6 shadow-xl shadow-indigo-100/50 transition dark:border-slate-800/70 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950/70 dark:shadow-none">
    <div class="flex flex-col gap-6">
      <header class="flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-300">
        <span class="inline-flex items-center gap-2 rounded-full border border-indigo-200/70 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 shadow-sm dark:border-indigo-900/60 dark:bg-indigo-950/50 dark:text-indigo-200">
          <i data-lucide="circle-check" class="h-3.5 w-3.5"></i>
          <?= e((string) ($stats['total'] ?? 0)); ?> proyectos
        </span>
        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200/70 bg-white/80 px-3 py-1 text-xs font-semibold text-slate-600 shadow-sm dark:border-slate-700/60 dark:bg-slate-900/70 dark:text-slate-200">
          <i data-lucide="calendar" class="h-3.5 w-3.5 text-indigo-400"></i>
          <?= e((string) $upcomingCount); ?> hitos proximos
        </span>
      </header>

      <div class="grid grid-cols-1 items-stretch gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <?php foreach ($statCards as $card): ?>
          <article class="flex h-full flex-col justify-between rounded-2xl border border-slate-200/60 bg-white/80 p-4 shadow-md shadow-slate-200/60 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-xl dark:border-slate-800/60 dark:bg-slate-900/60 dark:shadow-none">
            <div class="flex items-center justify-between gap-3">
              <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-300"><?= e($card['label']); ?></p>
              <i data-lucide="<?= e($card['icon']); ?>" class="h-5 w-5 <?= e($card['accent']); ?>"></i>
            </div>
            <p class="mt-4 text-3xl font-semibold text-slate-800 dark:text-slate-100"><?= e((string) $card['value']); ?></p>
          </article>
        <?php endforeach; ?>
      </div>

      <div class="grid grid-cols-1 items-stretch gap-4 xl:grid-cols-2">
        <article class="flex h-full flex-col rounded-2xl border border-slate-200/60 bg-white/80 p-5 shadow-md shadow-slate-200/60 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-xl dark:border-slate-800/60 dark:bg-slate-900/60 dark:shadow-none">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
              <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">Proximos hitos</h2>
              <p class="text-xs text-slate-500">Ordenados por fecha estimada de entrega.</p>
            </div>
            <span class="inline-flex items-center gap-2 self-start rounded-full border border-indigo-200/70 bg-indigo-50 px-3 py-1 text-[11px] font-semibold text-indigo-700 shadow-sm dark:border-indigo-900/60 dark:bg-indigo-950/40 dark:text-indigo-200">
              <i data-lucide="calendar" class="h-3 w-3"></i>
              <?= e((string) $upcomingCount); ?> pendientes
            </span>
          </div>
          <div class="mt-4 flex-1 space-y-3">
            <?php if ($upcomingMilestones === []): ?>
              <p class="rounded-2xl border border-dashed border-slate-300/80 bg-slate-50/70 px-4 py-6 text-center text-sm text-slate-500 dark:border-slate-700/60 dark:bg-slate-900/50">Sin hitos programados en los proximos dias.</p>
            <?php else: ?>
              <?php foreach ($upcomingMilestones as $item): ?>
                <div class="rounded-xl border border-slate-200/70 bg-white/90 px-4 py-3 transition hover:border-indigo-200 hover:bg-white dark:border-slate-800/60 dark:bg-slate-950/40 dark:hover:border-indigo-900/50">
                  <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                      <p class="font-medium text-slate-800 dark:text-slate-100"><?= e($item['title']); ?></p>
                      <p class="text-xs text-slate-500">Proyecto: <?= e($item['project_title']); ?></p>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200/70 bg-white/80 px-3 py-1 text-[11px] font-semibold text-slate-600 shadow-sm dark:border-slate-700/60 dark:bg-slate-900/70 dark:text-slate-200">
                      <i data-lucide="clock" class="h-3 w-3 text-indigo-400"></i>
                      <?= e(format_dashboard_period($item['start_date'] ?? null, $item['end_date'] ?? ($item['due_date'] ?? null))); ?>
                    </span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </article>

        <article class="flex h-full flex-col rounded-2xl border border-slate-200/60 bg-white/80 p-5 shadow-md shadow-slate-200/60 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-xl dark:border-slate-800/60 dark:bg-slate-900/60 dark:shadow-none">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
              <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">Feedback reciente</h2>
              <p class="text-xs text-slate-500">Comentarios mas recientes registrados.</p>
            </div>
            <span class="inline-flex items-center gap-2 self-start rounded-full border border-emerald-200/70 bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-700 shadow-sm dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
              <i data-lucide="message-circle" class="h-3 w-3"></i>
              <?= e((string) $recentFeedbackCount); ?> registros
            </span>
          </div>
          <div class="mt-4 flex-1 space-y-3">
            <?php if ($recentFeedback === []): ?>
              <p class="rounded-2xl border border-dashed border-slate-300/80 bg-slate-50/70 px-4 py-6 text-center text-sm text-slate-500 dark:border-slate-700/60 dark:bg-slate-900/50">Aun no hay comentarios registrados.</p>
            <?php else: ?>
              <?php foreach ($recentFeedback as $comment): ?>
                <div class="rounded-xl border border-slate-200/70 bg-white/90 px-4 py-3 shadow-sm transition hover:border-emerald-200 hover:bg-white dark:border-slate-800/60 dark:bg-slate-950/40 dark:hover:border-emerald-900/50">
                  <div class="flex flex-col gap-2">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                      <p class="font-medium text-slate-800 dark:text-slate-100"><?= e($comment['author_name']); ?></p>
                      <span class="text-[11px] uppercase tracking-wide text-slate-400">Hito: <?= e($comment['milestone_title']); ?></span>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">"<?= e($comment['content']); ?>"</p>
                    <p class="text-xs text-slate-400">Proyecto: <?= e($comment['project_title']); ?></p>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </article>
      </div>
    </div>
  </article>
</section>


