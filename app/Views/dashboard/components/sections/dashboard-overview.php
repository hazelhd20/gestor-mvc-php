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

$iconStyles = [
    'text-indigo-500' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-200',
    'text-emerald-500' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-200',
    'text-amber-500' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-200',
];
?>
<section id="section-dashboard" data-section="dashboard" class="space-y-8<?= $isDashboardActive ? '' : ' hidden'; ?>">
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <?php foreach ($statCards as $card): ?>
      <?php $accent = $iconStyles[$card['accent']] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-800/70 dark:text-slate-200'; ?>
      <article class="flex flex-col gap-5 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 dark:bg-slate-900 dark:ring-slate-800">
        <div class="flex items-start justify-between">
          <div class="space-y-1">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400"><?= e($card['label']); ?></p>
            <p class="text-3xl font-semibold text-slate-800 dark:text-slate-100"><?= e((string) $card['value']); ?></p>
          </div>
          <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl <?= e($accent); ?>">
            <i data-lucide="<?= e($card['icon']); ?>" class="h-5 w-5"></i>
          </span>
        </div>
        <div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-slate-800">
          <span class="block h-full rounded-full bg-gradient-to-r from-slate-300/80 via-slate-400/80 to-slate-500/70 dark:from-slate-700 dark:via-slate-600 dark:to-slate-500"></span>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/70 dark:bg-slate-900 dark:ring-slate-800">
      <header class="flex items-start justify-between gap-3">
        <div>
          <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">Próximos hitos</h2>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Controla los compromisos de esta semana para anticiparte a los entregables.</p>
        </div>
        <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-200">
          <?= e((string) count($upcomingMilestones)); ?> pendientes
        </span>
      </header>
      <div class="mt-5">
        <?php if ($upcomingMilestones === []): ?>
          <p class="rounded-xl bg-slate-50 px-4 py-5 text-sm text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">Sin hitos programados en los próximos días.</p>
        <?php else: ?>
          <ul class="divide-y divide-slate-100 text-sm dark:divide-slate-800">
            <?php foreach ($upcomingMilestones as $item): ?>
              <li class="flex items-start justify-between gap-4 py-4 first:pt-0 last:pb-0">
                <div class="space-y-1">
                  <p class="font-medium text-slate-800 dark:text-slate-100"><?= e($item['title']); ?></p>
                  <p class="text-xs text-slate-500">Proyecto: <?= e($item['project_title']); ?></p>
                </div>
                <span class="inline-flex items-center rounded-lg bg-indigo-100 px-3 py-1 text-[11px] font-semibold text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-200">
                  <?= e(format_dashboard_period($item['start_date'] ?? null, $item['end_date'] ?? ($item['due_date'] ?? null))); ?>
                </span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </article>

    <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/70 dark:bg-slate-900 dark:ring-slate-800">
      <header class="flex items-start justify-between gap-3">
        <div>
          <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">Feedback reciente</h2>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Sigue de cerca los comentarios para mantener el acompañamiento oportuno.</p>
        </div>
        <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-200">
          <?= e((string) count($recentFeedback)); ?> registros
        </span>
      </header>
      <div class="mt-5">
        <?php if ($recentFeedback === []): ?>
          <p class="rounded-xl bg-slate-50 px-4 py-5 text-sm text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">Aún no hay comentarios registrados.</p>
        <?php else: ?>
          <ul class="divide-y divide-slate-100 text-sm dark:divide-slate-800">
            <?php foreach ($recentFeedback as $comment): ?>
              <li class="py-4 first:pt-0 last:pb-0">
                <div class="flex flex-wrap items-center justify-between gap-2">
                  <p class="font-medium text-slate-800 dark:text-slate-100">
                    <?= e($comment['author_name']); ?>
                  </p>
                  <span class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Hito: <?= e($comment['milestone_title']); ?></span>
                </div>
                <p class="mt-2 text-slate-600 dark:text-slate-300">"<?= e($comment['content']); ?>"</p>
                <p class="mt-2 text-xs text-slate-400">Proyecto: <?= e($comment['project_title']); ?></p>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </article>
  </div>
</section>
