<?php
$isDashboardActive = ($activeTab ?? 'dashboard') === 'dashboard';
$statCards = [
    [
        'label' => 'Proyectos totales',
        'value' => $stats['total'] ?? 0,
        'icon' => 'layers',
        'icon_color' => 'text-indigo-500',
        'gradient' => 'from-indigo-500/35 via-indigo-500/10 to-transparent',
    ],
    [
        'label' => 'Activos',
        'value' => $stats['active'] ?? 0,
        'icon' => 'activity',
        'icon_color' => 'text-emerald-500',
        'gradient' => 'from-emerald-400/35 via-emerald-400/10 to-transparent',
    ],
    [
        'label' => 'Completados',
        'value' => $stats['completed'] ?? 0,
        'icon' => 'check-circle',
        'icon_color' => 'text-sky-500',
        'gradient' => 'from-sky-400/35 via-sky-400/10 to-transparent',
    ],
    [
        'label' => 'Vencen pronto (7 días)',
        'value' => $stats['due_soon'] ?? 0,
        'icon' => 'clock',
        'icon_color' => 'text-amber-500',
        'gradient' => 'from-amber-400/35 via-amber-400/10 to-transparent',
    ],
];
?>
<section id="section-dashboard" data-section="dashboard" class="flex flex-col gap-6<?= $isDashboardActive ? '' : ' hidden'; ?>">
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <?php foreach ($statCards as $card): ?>
      <article class="relative overflow-hidden rounded-3xl glass-card p-5 transition-transform duration-300 ease-out hover:-translate-y-1 hover:shadow-2xl">
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-br <?= e($card['gradient']); ?> opacity-90"></div>
        <div class="relative z-[1] flex flex-col gap-4">
          <div class="flex items-center justify-between">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-300/80"><?= e($card['label']); ?></p>
            <span class="inline-flex rounded-2xl bg-white/60 p-2 text-slate-600 shadow-sm dark:bg-slate-900/60">
              <i data-lucide="<?= e($card['icon']); ?>" class="h-5 w-5 <?= e($card['icon_color']); ?>"></i>
            </span>
          </div>
          <p class="text-3xl font-semibold text-slate-900 dark:text-white"><?= e((string) $card['value']); ?></p>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    <article class="rounded-3xl glass-card p-6 shadow-lg">
      <div class="flex items-center justify-between">
        <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">Próximos hitos</h2>
        <span class="text-xs font-medium text-slate-500 dark:text-slate-400"><?= e((string) count($upcomingMilestones)); ?> pendientes</span>
      </div>
      <div class="mt-5 space-y-3">
        <?php if ($upcomingMilestones === []): ?>
          <p class="rounded-2xl border border-dashed border-slate-200/70 bg-white/60 px-4 py-6 text-center text-sm text-slate-500 dark:border-slate-700/60 dark:bg-slate-900/50">Sin hitos programados en los próximos días.</p>
        <?php else: ?>
          <?php foreach ($upcomingMilestones as $item): ?>
            <div class="rounded-2xl border border-white/70 bg-white/70 px-4 py-3 text-sm shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-slate-700/60 dark:bg-slate-900/60">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="font-medium text-slate-800 dark:text-slate-100"><?= e($item['title']); ?></p>
                  <p class="text-xs text-slate-500">Proyecto: <?= e($item['project_title']); ?></p>
                </div>
                <span class="inline-flex items-center rounded-full bg-indigo-500/15 px-3 py-1 text-[11px] font-semibold text-indigo-600 dark:bg-indigo-500/30 dark:text-indigo-200">
                  <?= e(format_dashboard_date($item['due_date'] ?? null)); ?>
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </article>

    <article class="rounded-3xl glass-card p-6 shadow-lg">
      <div class="flex items-center justify-between">
        <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">Feedback reciente</h2>
        <span class="text-xs font-medium text-slate-500 dark:text-slate-400"><?= e((string) count($recentFeedback)); ?> registros</span>
      </div>
      <div class="mt-5 space-y-3">
        <?php if ($recentFeedback === []): ?>
          <p class="rounded-2xl border border-dashed border-slate-200/70 bg-white/60 px-4 py-6 text-center text-sm text-slate-500 dark:border-slate-700/60 dark:bg-slate-900/50">Aún no hay comentarios registrados.</p>
        <?php else: ?>
          <?php foreach ($recentFeedback as $comment): ?>
            <div class="rounded-2xl border border-white/70 bg-white/70 px-4 py-3 text-sm shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-slate-700/60 dark:bg-slate-900/60">
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
