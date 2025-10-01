<?php $isMilestonesActive = ($activeTab ?? 'dashboard') === 'hitos'; ?>
<section id="section-hitos" data-section="hitos" class="flex flex-col gap-6<?= $isMilestonesActive ? '' : ' hidden'; ?>">
  <?php if (!$selectedProject): ?>
    <div class="glass-card px-6 py-12 text-center text-sm text-slate-500 shadow-xl dark:text-slate-400">
      Selecciona un proyecto para gestionar sus hitos.
    </div>
  <?php else: ?>
    <article class="rounded-3xl glass-card p-6 shadow-xl">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100"><?= e($selectedProject['title']); ?></h3>
          <p class="text-xs text-slate-500 dark:text-slate-400">Estudiante: <?= e($selectedProject['student_name']); ?> · Director: <?= e($selectedProject['director_name']); ?></p>
        </div>
        <span class="inline-flex items-center gap-2 rounded-full bg-white/60 px-4 py-1 text-xs font-semibold text-slate-600 shadow-sm dark:bg-slate-900/60 dark:text-slate-200">
          <i data-lucide="sparkles" class="h-3.5 w-3.5"></i>
          Estado: <span class="rounded-full px-2 py-0.5 <?= e(status_badge_classes($selectedProject['status'])); ?>"><?= e(humanize_status($selectedProject['status'])); ?></span>
        </span>
      </div>

      <div class="mt-6 space-y-4">
        <?php if ($projectMilestones === []): ?>
          <div class="rounded-2xl border border-dashed border-slate-200/70 bg-white/60 px-5 py-8 text-center text-sm text-slate-500 dark:border-slate-700/60 dark:bg-slate-900/40">Aún no hay hitos registrados.</div>
        <?php else: ?>
          <?php foreach ($projectMilestones as $milestone): ?>
            <?php
              $isDirectorOwner = !empty($isDirector) && (int) ($selectedProject['director_id'] ?? 0) === $userId;
              $isStudentOwner = !empty($isStudent) && (int) ($selectedProject['student_id'] ?? 0) === $userId;
              $statusOptions = [];

              if ($isDirectorOwner) {
                  $statusOptions = ['pendiente','en_progreso','en_revision','aprobado'];
              } elseif ($isStudentOwner && ($milestone['status'] ?? '') !== 'aprobado') {
                  $statusOptions = ['pendiente','en_progreso','en_revision'];
              }

              $deliverables = $deliverablesByMilestone[$milestone['id']] ?? [];
              $feedbackList = $feedbackByMilestone[$milestone['id']] ?? [];
              $canUploadDeliverable = !empty($isStudentOwner)
                  && !in_array($milestone['status'], ['en_revision', 'aprobado'], true);
            ?>
            <article class="rounded-3xl border border-slate-200/60 bg-white/70 p-5 shadow-md transition duration-200 hover:-translate-y-0.5 hover:shadow-xl dark:border-slate-800/60 dark:bg-slate-900/60">
              <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100"><?= e($milestone['title']); ?></h4>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"><?= e($milestone['description'] ?: 'Sin descripción.'); ?></p>
                  <p class="mt-2 inline-flex items-center gap-1 rounded-full bg-indigo-500/10 px-3 py-1 text-[11px] font-medium text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-200">
                    <i data-lucide="calendar" class="h-3.5 w-3.5"></i>
                    Entrega: <?= e(format_dashboard_date($milestone['due_date'] ?? null)); ?>
                  </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                  <span class="rounded-full px-3 py-1 text-[11px] font-semibold <?= e(status_badge_classes($milestone['status'])); ?>"><?= e(humanize_status($milestone['status'])); ?></span>
                  <?php if ($statusOptions !== []): ?>
                    <form method="post" action="<?= e(url('/milestones/status')); ?>" class="flex items-center gap-1">
                      <input type="hidden" name="milestone_id" value="<?= e((string) $milestone['id']); ?>" />
                      <label class="sr-only" for="milestone-status-<?= e((string) $milestone['id']); ?>">Estado</label>
                      <select id="milestone-status-<?= e((string) $milestone['id']); ?>" name="status" class="rounded-full border border-slate-200/80 bg-white px-3 py-1 text-xs outline-none transition focus:border-indigo-500 dark:border-slate-700/60 dark:bg-slate-900">
                        <?php foreach ($statusOptions as $option): ?>
                          <option value="<?= e($option); ?>" <?= $option === ($milestone['status'] ?? '') ? 'selected' : ''; ?>><?= e(humanize_status($option)); ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button type="submit" class="inline-flex items-center gap-1 rounded-full bg-indigo-600 px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-700">
                        <i data-lucide="save" class="h-3.5 w-3.5"></i>
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>

              <div class="mt-5 grid gap-4 lg:grid-cols-2">
                <section class="rounded-2xl border border-slate-200/60 bg-white/70 p-4 text-xs shadow-sm dark:border-slate-800/60 dark:bg-slate-950/40">
                  <div class="flex items-center justify-between">
                    <p class="font-semibold text-slate-700 dark:text-slate-200">Entregables</p>
                    <span class="inline-flex items-center gap-1 rounded-full bg-indigo-500/10 px-3 py-0.5 text-[11px] font-semibold text-indigo-600 dark:bg-indigo-500/30 dark:text-indigo-200">
                      <i data-lucide="file-check" class="h-3.5 w-3.5"></i>
                      <?= e((string) ($milestone['deliverables_count'] ?? 0)); ?>
                    </span>
                  </div>
                  <div class="mt-3 max-h-40 space-y-3 overflow-y-auto pr-1 scrollbar-thin">
                    <?php if ($deliverables === []): ?>
                      <p class="text-slate-500 dark:text-slate-400">Sin entregas aún.</p>
                    <?php else: ?>
                      <?php foreach ($deliverables as $deliverable): ?>
                        <article class="rounded-2xl border border-slate-200/60 bg-white/80 px-3 py-2 shadow-sm dark:border-slate-800/60 dark:bg-slate-950/40">
                          <p class="text-slate-700 dark:text-slate-200"><?= e($deliverable['original_name']); ?></p>
                          <p class="text-[11px] text-slate-400">Autor: <?= e($deliverable['author_name']); ?></p>
                          <div class="mt-1 flex items-center justify-between text-[11px] text-slate-400">
                            <span><?= e($deliverable['notes'] ? 'Notas incluidas' : 'Archivo'); ?></span>
                            <?php if (!empty($deliverable['file_path'])): ?>
                              <a class="inline-flex items-center gap-1 rounded-full border border-indigo-500/30 px-2.5 py-0.5 text-[11px] font-medium text-indigo-600 transition hover:-translate-y-0.5 hover:bg-indigo-50 dark:border-indigo-500/40 dark:text-indigo-200 dark:hover:bg-indigo-500/20" href="<?= e(url('/deliverables/download?id=' . (int) $deliverable['id'])); ?>">
                                <i data-lucide="download" class="h-3 w-3"></i> Descargar
                              </a>
                            <?php endif; ?>
                          </div>
                          <?php if (!empty($deliverable['notes'])): ?>
                            <p class="mt-2 rounded-2xl bg-slate-100/80 px-3 py-1.5 text-slate-600 dark:bg-slate-800/70 dark:text-slate-200">"<?= e($deliverable['notes']); ?>"</p>
                          <?php endif; ?>
                        </article>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>

                  <div class="mt-3">
                    <?php if ($canUploadDeliverable): ?>
                      <form method="post" action="<?= e(url('/deliverables')); ?>" enctype="multipart/form-data" class="space-y-3">
                        <input type="hidden" name="milestone_id" value="<?= e((string) $milestone['id']); ?>" />
                        <label class="block text-[11px] font-semibold uppercase text-slate-500">Subir avance</label>
                        <input type="file" name="file" class="w-full rounded-2xl border border-slate-200/80 bg-white px-3 py-2 text-xs transition focus:border-indigo-500 dark:border-slate-700/60 dark:bg-slate-900" />
                        <textarea name="notes" rows="2" placeholder="Notas complementarias (opcional)" class="w-full rounded-2xl border border-slate-200/80 bg-white px-3 py-2 text-xs outline-none transition focus:border-indigo-500 dark:border-slate-700/60 dark:bg-slate-900"></textarea>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-700">
                          <i data-lucide="upload" class="h-3.5 w-3.5"></i> Registrar avance
                        </button>
                      </form>
                    <?php else: ?>
                      <p class="text-[11px] text-slate-500 dark:text-slate-400">Solo el estudiante puede registrar avances antes de enviar a revisión o después de aprobación.</p>
                    <?php endif; ?>
                  </div>
                </section>

                <section class="rounded-2xl border border-slate-200/60 bg-white/70 p-4 text-xs shadow-sm dark:border-slate-800/60 dark:bg-slate-950/40">
                  <div class="flex items-center justify-between">
                    <p class="font-semibold text-slate-700 dark:text-slate-200">Feedback</p>
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-3 py-0.5 text-[11px] font-semibold text-emerald-600 dark:bg-emerald-500/30 dark:text-emerald-200">
                      <i data-lucide="message-circle" class="h-3.5 w-3.5"></i>
                      <?= e((string) ($milestone['feedback_count'] ?? 0)); ?>
                    </span>
                  </div>
                  <div class="mt-3 max-h-40 space-y-3 overflow-y-auto pr-1 scrollbar-thin">
                    <?php if ($feedbackList === []): ?>
                      <p class="text-slate-500 dark:text-slate-400">Sin comentarios aún.</p>
                    <?php else: ?>
                      <?php foreach ($feedbackList as $comment): ?>
                        <article class="rounded-2xl border border-slate-200/60 bg-white/80 px-3 py-2 shadow-sm dark:border-slate-800/60 dark:bg-slate-950/40">
                          <p class="font-semibold text-slate-700 dark:text-slate-200"><?= e($comment['author_name']); ?></p>
                          <p class="mt-1 text-slate-600 dark:text-slate-300">"<?= e($comment['content']); ?>"</p>
                        </article>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                  <div class="mt-3">
                    <form method="post" action="<?= e(url('/feedback')); ?>" class="space-y-2">
                      <input type="hidden" name="milestone_id" value="<?= e((string) $milestone['id']); ?>" />
                      <textarea name="content" rows="3" placeholder="Escribe un comentario" class="w-full rounded-2xl border border-slate-200/80 bg-white px-3 py-2 text-xs outline-none transition focus:border-emerald-500 dark:border-slate-700/60 dark:bg-slate-900" required></textarea>
                      <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-700">
                        <i data-lucide="message-circle" class="h-3.5 w-3.5"></i> Enviar feedback
                      </button>
                    </form>
                  </div>
                </section>
              </div>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </article>
  <?php endif; ?>
</section>
