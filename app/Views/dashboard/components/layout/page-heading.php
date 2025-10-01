<?php
$totalProjects = count($projects ?? []);
$upcoming = count($upcomingMilestones ?? []);
$activeProjects = $stats['active'] ?? null;
?>
<div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-500 via-sky-500 to-purple-600 px-6 py-7 text-white shadow-xl glow-accent">
  <div class="flex flex-wrap items-start justify-between gap-6">
    <div class="max-w-xl">
      <p class="text-[11px] uppercase tracking-[0.2em] text-white/70">Panel de control</p>
      <h1 id="pageTitle" class="mt-2 text-2xl font-semibold leading-tight sm:text-3xl">
        <?= e($pageTitle ?? 'Panel'); ?>
      </h1>
      <p id="pageSubtitle" class="mt-2 text-sm text-white/80">
        <?= e($pageSubtitle ?? 'Resumen general y acciones rápidas'); ?>
      </p>
    </div>

    <div class="flex flex-col gap-2 text-xs sm:text-sm">
      <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 backdrop-blur">
        <i data-lucide="rocket" class="h-4 w-4"></i>
        <span class="font-medium">Proyectos activos: <?= e((string) ($activeProjects ?? '--')); ?></span>
      </div>
      <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 backdrop-blur">
        <i data-lucide="folders" class="h-4 w-4"></i>
        <span class="font-medium">Totales: <?= e((string) $totalProjects); ?></span>
      </div>
      <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 backdrop-blur">
        <i data-lucide="calendar-clock" class="h-4 w-4"></i>
        <span class="font-medium">Hitos próximos: <?= e((string) $upcoming); ?></span>
      </div>
    </div>
  </div>

  <form method="post" action="<?= e(url('/logout')); ?>" id="logoutForm" class="hidden"></form>
</div>
