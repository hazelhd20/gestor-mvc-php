<?php $isCommentsActive = ($activeTab ?? 'dashboard') === 'comentarios'; ?>
<section id="section-comentarios" data-section="comentarios" class="space-y-6<?= $isCommentsActive ? '' : ' hidden'; ?>">
  <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="space-y-5">
      <?php if ($projectMilestones === []): ?>
        <p class="text-sm text-slate-500">Selecciona un proyecto con hitos para ver los comentarios.</p>
      <?php else: ?>
        <?php foreach ($projectMilestones as $milestone): ?>
          <?php $feedbackList = $feedbackByMilestone[$milestone['id']] ?? []; ?>
          <section class="rounded-xl bg-slate-50/80 p-4 ring-1 ring-slate-200/70 backdrop-blur-sm dark:bg-slate-900/40 dark:ring-slate-800/70">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Hito: <?= e($milestone['title']); ?></h3>
              <span class="rounded-lg bg-slate-200/70 px-2 py-0.5 text-[11px] font-medium text-slate-600 dark:bg-slate-800/70 dark:text-slate-300">Comentarios: <?= e((string) count($feedbackList)); ?></span>
            </div>
            <div class="mt-3 space-y-3">
              <?php if ($feedbackList === []): ?>
                <p class="text-xs text-slate-500">Sin comentarios registrados.</p>
              <?php else: ?>
                <?php foreach ($feedbackList as $comment): ?>
                  <article class="rounded-lg bg-white/80 px-3 py-2 text-sm ring-1 ring-slate-200/70 transition hover:bg-white dark:bg-slate-900/50 dark:ring-slate-800/70">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-400">
                      <span class="font-semibold text-slate-600 dark:text-slate-200"><?= e($comment['author_name']); ?></span>
                      <span><?= e(format_dashboard_date($comment['created_at'] ?? null)); ?></span>
                    </div>
                    <p class="mt-1 text-slate-700 dark:text-slate-200"><?= e($comment['content']); ?></p>
                  </article>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </section>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </article>
</section>
