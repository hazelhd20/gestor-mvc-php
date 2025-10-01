<?php $isCommentsActive = ($activeTab ?? 'dashboard') === 'comentarios'; ?>
<section id="section-comentarios" data-section="comentarios" class="flex flex-col gap-6<?= $isCommentsActive ? '' : ' hidden'; ?>">
  <article class="rounded-3xl glass-card p-6 shadow-xl">
    <div class="space-y-4">
      <?php if ($projectMilestones === []): ?>
        <p class="rounded-2xl border border-dashed border-slate-200/70 bg-white/60 px-4 py-6 text-center text-sm text-slate-500 dark:border-slate-700/60 dark:bg-slate-900/40">Selecciona un proyecto con hitos para ver los comentarios.</p>
      <?php else: ?>
        <?php foreach ($projectMilestones as $milestone): ?>
          <?php $feedbackList = $feedbackByMilestone[$milestone['id']] ?? []; ?>
          <section class="rounded-3xl border border-slate-200/60 bg-white/70 p-5 shadow-sm dark:border-slate-800/60 dark:bg-slate-900/50">
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Hito: <?= e($milestone['title']); ?></h3>
              <span class="inline-flex items-center gap-1 rounded-full bg-slate-500/10 px-3 py-0.5 text-[11px] font-semibold text-slate-600 dark:bg-slate-700/40 dark:text-slate-200">
                <i data-lucide="chat" class="h-3.5 w-3.5"></i>
                Comentarios: <?= e((string) count($feedbackList)); ?>
              </span>
            </div>
            <div class="mt-3 space-y-3">
              <?php if ($feedbackList === []): ?>
                <p class="rounded-2xl border border-dashed border-slate-200/70 bg-white/60 px-3 py-4 text-center text-xs text-slate-500 dark:border-slate-700/60 dark:bg-slate-900/40">Sin comentarios registrados.</p>
              <?php else: ?>
                <?php foreach ($feedbackList as $comment): ?>
                  <article class="rounded-2xl border border-slate-200/60 bg-white/80 px-4 py-3 text-sm shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800/60 dark:bg-slate-900/60">
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
