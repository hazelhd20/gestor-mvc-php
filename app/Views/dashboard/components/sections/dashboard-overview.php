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
        'label' => 'Vencen pronto (7 días)',
        'value' => $stats['due_soon'] ?? 0,
        'icon' => 'clock',
        'accent' => 'text-amber-500',
    ],
];
?>
<section id="section-dashboard" data-section="dashboard" class="space-y-6<?= $isDashboardActive ? '' : ' hidden'; ?>">
  <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
    <?php foreach ($statCards as $card): ?>
      <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between">
          <p class="text-xs font-medium uppercase tracking-wide text-slate-500"><?= e($card['label']); ?></p>
          <i data-lucide="<?= e($card['icon']); ?>" class="h-5 w-5 <?= e($card['accent']); ?>"></i>
        </div>
        <p class="mt-4 text-3xl font-semibold"><?= e((string) $card['value']); ?></p>
      </article>
    <?php endforeach; ?>
  </div>

  <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="flex items-center justify-between">
        <h2 class="text-base font-semibold">Próximos hitos</h2>
        <span class="text-xs text-slate-500"><?= e((string) count($upcomingMilestones)); ?> pendientes</span>
      </div>
      <div class="mt-4 space-y-3">
        <?php if ($upcomingMilestones === []): ?>
          <p class="text-sm text-slate-500">Sin hitos programados en los próximos días.</p>
        <?php else: ?>
          <?php foreach ($upcomingMilestones as $item): ?>
            <div class="rounded-xl border border-slate-200/70 bg-slate-50 px-3 py-3 text-sm dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="font-medium text-slate-800 dark:text-slate-100"><?= e($item['title']); ?></p>
                  <p class="text-xs text-slate-500">Proyecto: <?= e($item['project_title']); ?></p>
                </div>
                <span class="rounded-lg bg-indigo-100 px-2 py-1 text-[11px] font-medium text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-200">
                  <?= e(format_dashboard_date($item['due_date'] ?? null)); ?>
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </article>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="flex items-center justify-between">
        <h2 class="text-base font-semibold">Feedback reciente</h2>
        <span class="text-xs text-slate-500"><?= e((string) count($recentFeedback)); ?> registros</span>
      </div>
      <div class="mt-4 space-y-3">
        <?php if ($recentFeedback === []): ?>
          <p class="text-sm text-slate-500">Aún no hay comentarios registrados.</p>
        <?php else: ?>
          <?php foreach ($recentFeedback as $comment): ?>
            <div class="rounded-xl border border-slate-200/70 px-3 py-3 text-sm dark:border-slate-800">
              <div class="flex items-center justify-between gap-2">
                <p class="font-medium text-slate-800 dark:text-slate-100">
                  <?= e($comment['author_name']); ?>
                </p>
                <span class="text-[11px] uppercase tracking-wide text-slate-400">Hito: <?= e($comment['milestone_title']); ?></span>
              </div>
              <p class="mt-2 text-slate-600 dark:text-slate-300">"<?= e($comment['content']); ?>"</p>
              <p class="mt-1 text-xs text-slate-400">Proyecto: <?= e($comment['project_title']); ?></p>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </article>
  </div>
</section>
