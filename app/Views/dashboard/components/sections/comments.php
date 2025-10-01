<?php $isCommentsActive = ($activeTab ?? 'dashboard') === 'comentarios'; ?>
<section id="section-comentarios" data-section="comentarios" class="space-y-8<?= $isCommentsActive ? '' : ' hidden'; ?>">
  <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/70 dark:bg-slate-900 dark:ring-slate-800">
    <header class="flex flex-wrap items-start justify-between gap-3">
      <div class="space-y-1">
        <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Comentarios por hito</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">Consulta la retroalimentación registrada para cada avance y mantén a tu equipo informado.</p>
      </div>
    </header>

    <div class="mt-6 space-y-5">
      <?php if ($projectMilestones === []): ?>
        <p class="rounded-2xl bg-slate-50 px-4 py-6 text-center text-sm text-slate-500 shadow-inner dark:bg-slate-900/50 dark:text-slate-400">Selecciona un proyecto con hitos para ver los comentarios.</p>
      <?php else: ?>
        <?php foreach ($projectMilestones as $milestone): ?>
          <?php $feedbackList = $feedbackByMilestone[$milestone['id']] ?? []; ?>
          <section class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-200/70 backdrop-blur-sm dark:bg-slate-900/40 dark:ring-slate-800/70">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Hito: <?= e($milestone['title']); ?></h3>
              <span class="inline-flex items-center rounded-full bg-slate-200/70 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600 dark:bg-slate-800/70 dark:text-slate-300">Comentarios: <?= e((string) count($feedbackList)); ?></span>
            </div>
            <div class="mt-4 space-y-3">
              <?php if ($feedbackList === []): ?>
                <p class="text-xs text-slate-500 dark:text-slate-400">Sin comentarios registrados.</p>
              <?php else: ?>
                <?php foreach ($feedbackList as $comment): ?>
                  <article class="rounded-xl bg-white px-3 py-2 text-sm shadow-sm ring-1 ring-slate-200/70 transition hover:ring-indigo-200 dark:bg-slate-900/50 dark:ring-slate-800/70">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-400">
                      <span class="font-semibold text-slate-600 dark:text-slate-200"><?= e($comment['author_name']); ?></span>
                      <span><?= e(format_dashboard_date($comment['created_at'] ?? null)); ?></span>
                    </div>
                    <p class="mt-2 text-slate-700 dark:text-slate-200"><?= e($comment['content']); ?></p>
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
