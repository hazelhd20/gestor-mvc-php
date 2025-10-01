<?php $isCommentsActive = ($activeTab ?? 'dashboard') === 'comentarios'; ?>
<section id="section-comentarios" data-section="comentarios" class="mt-8 space-y-6<?= $isCommentsActive ? '' : ' hidden'; ?>">
  <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="space-y-4">
      <?php if ($projectMilestones === []): ?>
        <p class="text-sm text-slate-500">Selecciona un proyecto con hitos para ver los comentarios.</p>
      <?php else: ?>
        <?php foreach ($projectMilestones as $milestone): ?>
          <?php $feedbackList = $feedbackByMilestone[$milestone['id']] ?? []; ?>
          <section class="rounded-xl border border-slate-200 bg-white/80 p-4 dark:border-slate-800 dark:bg-slate-900/40">
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Hito: <?= e($milestone['title']); ?></h3>
              <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-[11px] text-slate-500 dark:bg-slate-800">Comentarios: <?= e((string) count($feedbackList)); ?></span>
            </div>
            <div class="mt-3 space-y-3">
              <?php if ($feedbackList === []): ?>
                <p class="text-xs text-slate-500">Sin comentarios registrados.</p>
              <?php else: ?>
                <?php foreach ($feedbackList as $comment): ?>
                  <article class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between text-xs text-slate-400">
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
