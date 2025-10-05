<?php
$isCommentsActive = ($activeTab ?? 'dashboard') === 'comentarios';
$projectMilestonesList = is_array($projectMilestones ?? null) ? $projectMilestones : [];
$totalComments = 0;
foreach ($projectMilestonesList as $milestone) {
    $totalComments += count($feedbackByMilestone[$milestone['id']] ?? []);
}
?>
<section id="section-comentarios" data-section="comentarios" class="space-y-8<?= $isCommentsActive ? '' : ' hidden'; ?>">
  <article class="rounded-3xl border border-slate-200/70 bg-gradient-to-br from-white via-white to-indigo-50/40 p-6 shadow-xl shadow-indigo-100/50 transition dark:border-slate-800/70 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950/70 dark:shadow-none">
    <div class="flex flex-col gap-6">
      <header class="flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-300">
        <span class="inline-flex items-center gap-2 rounded-full border border-indigo-200/70 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 shadow-sm dark:border-indigo-900/60 dark:bg-indigo-950/50 dark:text-indigo-200">
          <i data-lucide="circle-check" class="h-3.5 w-3.5"></i>
          <?= e((string) $totalComments); ?> totales
        </span>
        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200/70 bg-white/80 px-3 py-1 text-xs font-semibold text-slate-600 shadow-sm dark:border-slate-700/60 dark:bg-slate-900/70 dark:text-slate-200">
          <i data-lucide="flag" class="h-3.5 w-3.5 text-indigo-400"></i>
          <?= e((string) count($projectMilestonesList)); ?> hitos
        </span>
      </header>

      <div class="space-y-4">
        <?php if ($projectMilestonesList === []): ?>
          <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 px-6 py-10 text-center text-sm text-slate-500 shadow-sm dark:border-slate-700 dark:bg-slate-900/60">
            <i data-lucide="sparkles" class="mx-auto mb-3 h-6 w-6 text-indigo-400"></i>
            <p class="font-medium text-slate-600 dark:text-slate-200">Selecciona un proyecto con hitos para ver los comentarios.</p>
          </div>
        <?php else: ?>
          <?php foreach ($projectMilestonesList as $milestone): ?>
            <?php $feedbackList = $feedbackByMilestone[$milestone['id']] ?? []; ?>
            <section class="rounded-2xl border border-slate-200/70 bg-white/80 p-5 shadow-md shadow-slate-200/60 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-xl dark:border-slate-800/70 dark:bg-slate-900/60 dark:shadow-none">
              <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-2">
                  <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100"><?= e($milestone['title']); ?></h3>
                  <p class="text-sm text-slate-500 dark:text-slate-300"><?= e($milestone['description'] ?? 'Sin descripcion.'); ?></p>
                </div>
                <span class="inline-flex items-center gap-2 self-start rounded-full border border-emerald-200/70 bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-700 shadow-sm dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
                  <i data-lucide="message-circle" class="h-3 w-3"></i>
                  <?= e((string) count($feedbackList)); ?> comentarios
                </span>
              </div>

              <div class="mt-4 space-y-3">
                <?php if ($feedbackList === []): ?>
                  <p class="rounded-xl border border-dashed border-slate-300/80 bg-slate-50/70 px-3 py-3 text-center text-xs text-slate-500 dark:border-slate-700/60 dark:bg-slate-900/50">Sin comentarios registrados.</p>
                <?php else: ?>
                  <?php foreach ($feedbackList as $comment): ?>
                    <article id="comentario-<?= e((string) $comment['id']); ?>" class="rounded-xl border border-slate-200/70 bg-white/90 px-3 py-3 text-sm shadow-sm transition hover:border-emerald-200 hover:bg-white dark:border-slate-800/70 dark:bg-slate-950/40 dark:hover:border-emerald-900/50">
                      <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-400">
                        <span class="font-semibold text-slate-600 dark:text-slate-200"><?= e($comment['author_name']); ?></span>
                        <span><?= e(format_dashboard_date($comment['created_at'] ?? null)); ?></span>
                      </div>
                      <p class="mt-2 text-slate-700 dark:text-slate-200">"<?= e($comment['content']); ?>"</p>
                    </article>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </section>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </article>
</section>



