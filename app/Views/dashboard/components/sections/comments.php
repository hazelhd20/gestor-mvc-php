<?php $isCommentsActive = ($activeTab ?? 'dashboard') === 'comentarios'; ?>
<section id="section-comentarios" data-section="comentarios" class="mt-10 space-y-6<?= $isCommentsActive ? '' : ' hidden'; ?>">
  <article class="surface p-6">
    <div class="space-y-4">
      <?php if ($projectMilestones === []): ?>
        <p class="text-sm text-slate-500">Selecciona un proyecto con hitos para ver los comentarios.</p>
      <?php else: ?>
        <?php foreach ($projectMilestones as $milestone): ?>
          <?php $feedbackList = $feedbackByMilestone[$milestone['id']] ?? []; ?>
          <section class="surface-muted p-4">
            <div class="flex items-center justify-between">
              <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">Hito: <?= e($milestone['title']); ?></h3>
              <span class="badge-soft">Comentarios: <?= e((string) count($feedbackList)); ?></span>
            </div>
            <div class="mt-3 space-y-3">
              <?php if ($feedbackList === []): ?>
                <p class="text-xs text-slate-500">Sin comentarios registrados.</p>
              <?php else: ?>
                <?php foreach ($feedbackList as $comment): ?>
                  <article class="surface px-3 py-2 text-sm dark:text-slate-200">
                    <div class="flex items-center justify-between text-xs text-slate-400 dark:text-slate-500">
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
