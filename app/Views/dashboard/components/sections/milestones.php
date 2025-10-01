<?php $isMilestonesActive = ($activeTab ?? 'dashboard') === 'hitos'; ?>
<section id="section-hitos" data-section="hitos" class="mt-10 space-y-6<?= $isMilestonesActive ? '' : ' hidden'; ?>">
  <?php if (!$selectedProject): ?>
    <div class="surface px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-300">
      Selecciona un proyecto para gestionar sus hitos.
    </div>
  <?php else: ?>
    <article class="surface space-y-5 p-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100"><?= e($selectedProject['title']); ?></h3>
          <p class="text-sm text-slate-500">Estudiante: <?= e($selectedProject['student_name']); ?> · Director: <?= e($selectedProject['director_name']); ?></p>
        </div>
        <span class="rounded-full px-3 py-1 text-xs font-semibold <?= e(status_badge_classes($selectedProject['status'])); ?>">Estado: <?= e(humanize_status($selectedProject['status'])); ?></span>
      </div>

      <div class="space-y-4">
        <?php if ($projectMilestones === []): ?>
          <div class="surface-muted px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-200">Aún no hay hitos registrados.</div>
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
            <article class="surface-muted p-5 text-sm dark:text-slate-200">
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                  <h4 class="text-base font-semibold text-slate-800 dark:text-slate-100"><?= e($milestone['title']); ?></h4>
                  <p class="mt-2 text-sm text-slate-500"><?= e($milestone['description'] ?: 'Sin descripción.'); ?></p>
                  <p class="mt-3 text-xs text-slate-400">Entrega: <?= e(format_dashboard_date($milestone['due_date'] ?? null)); ?></p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                  <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold <?= e(status_badge_classes($milestone['status'])); ?>"><?= e(humanize_status($milestone['status'])); ?></span>
                  <?php if ($statusOptions !== []): ?>
                    <form method="post" action="<?= e(url('/milestones/status')); ?>" class="flex items-center gap-1">
                      <input type="hidden" name="milestone_id" value="<?= e((string) $milestone['id']); ?>" />
                      <label class="sr-only" for="milestone-status-<?= e((string) $milestone['id']); ?>">Estado</label>
                      <select id="milestone-status-<?= e((string) $milestone['id']); ?>" name="status" class="rounded-lg border border-slate-200/70 bg-white/90 px-2 py-1 text-xs outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900/60">
                        <?php foreach ($statusOptions as $option): ?>
                          <option value="<?= e($option); ?>" <?= $option === ($milestone['status'] ?? '') ? 'selected' : ''; ?>><?= e(humanize_status($option)); ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-2.5 py-1 text-xs font-semibold text-white transition hover:bg-indigo-700">
                        <i data-lucide="save" class="h-3.5 w-3.5"></i>
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>

              <div class="mt-5 grid gap-4 lg:grid-cols-2">
                <section class="surface p-4 text-xs dark:text-slate-200">
                  <div class="flex items-center justify-between">
                    <p class="font-semibold text-slate-700 dark:text-slate-200">Entregables</p>
                    <span class="badge-soft text-indigo-600 dark:text-indigo-200">
                      <?= e((string) ($milestone['deliverables_count'] ?? 0)); ?>
                    </span>
                  </div>
                  <div class="mt-4 max-h-40 space-y-3 overflow-y-auto pr-1 scrollbar-thin">
                    <?php if ($deliverables === []): ?>
                      <p class="text-slate-500">Sin entregas aún.</p>
                    <?php else: ?>
                      <?php foreach ($deliverables as $deliverable): ?>
                        <article class="surface-muted px-3 py-2 text-left">
                          <p class="text-slate-700 dark:text-slate-200"><?= e($deliverable['original_name']); ?></p>
                          <p class="text-[11px] text-slate-400">Autor: <?= e($deliverable['author_name']); ?></p>
                          <div class="mt-1 flex items-center justify-between text-[11px] text-slate-400">
                            <span><?= e($deliverable['notes'] ? 'Notas incluidas' : 'Archivo'); ?></span>
                            <?php if (!empty($deliverable['file_path'])): ?>
                              <a class="inline-flex items-center gap-1 rounded-lg border border-slate-200/70 bg-white/80 px-2 py-0.5 text-[11px] text-indigo-600 transition hover:border-indigo-200 hover:text-indigo-700 dark:border-slate-700 dark:bg-slate-900/40 dark:text-indigo-300 dark:hover:border-indigo-400" href="<?= e(url('/deliverables/download?id=' . (int) $deliverable['id'])); ?>">
                                <i data-lucide="download" class="h-3 w-3"></i> Descargar
                              </a>
                            <?php endif; ?>
                          </div>
                          <?php if (!empty($deliverable['notes'])): ?>
                            <p class="mt-2 rounded-lg bg-slate-100/80 px-2 py-1 text-slate-600 dark:bg-slate-800/70 dark:text-slate-200">"<?= e($deliverable['notes']); ?>"</p>
                          <?php endif; ?>
                        </article>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>

                  <div class="mt-4">
                    <?php if ($canUploadDeliverable): ?>
                      <form method="post" action="<?= e(url('/deliverables')); ?>" enctype="multipart/form-data" class="space-y-3">
                        <input type="hidden" name="milestone_id" value="<?= e((string) $milestone['id']); ?>" />
                        <label class="block text-[11px] font-semibold uppercase text-slate-500">Subir avance</label>
                        <input type="file" name="file" class="w-full rounded-lg border border-slate-200/70 bg-white px-3 py-2 text-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900/60" />
                        <textarea name="notes" rows="2" placeholder="Notas complementarias (opcional)" class="w-full rounded-lg border border-slate-200/70 bg-white px-3 py-2 text-xs outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900/60"></textarea>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700">
                          <i data-lucide="upload" class="h-3.5 w-3.5"></i> Registrar avance
                        </button>
                      </form>
                    <?php else: ?>
                      <p class="text-[11px] text-slate-500">Solo el estudiante puede registrar avances antes de enviar a revisión o después de aprobación.</p>
                    <?php endif; ?>
                  </div>
                </section>

                <section class="surface p-4 text-xs dark:text-slate-200">
                  <div class="flex items-center justify-between">
                    <p class="font-semibold text-slate-700 dark:text-slate-200">Feedback</p>
                    <span class="badge-soft text-emerald-600 dark:text-emerald-200">
                      <?= e((string) ($milestone['feedback_count'] ?? 0)); ?>
                    </span>
                  </div>
                  <div class="mt-4 max-h-40 space-y-3 overflow-y-auto pr-1 scrollbar-thin">
                    <?php if ($feedbackList === []): ?>
                      <p class="text-slate-500">Sin comentarios aún.</p>
                    <?php else: ?>
                      <?php foreach ($feedbackList as $comment): ?>
                        <article class="surface-muted px-3 py-2">
                          <p class="font-semibold text-slate-700 dark:text-slate-200"><?= e($comment['author_name']); ?></p>
                          <p class="mt-1 text-slate-600 dark:text-slate-300">"<?= e($comment['content']); ?>"</p>
                        </article>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                  <div class="mt-3">
                    <form method="post" action="<?= e(url('/feedback')); ?>" class="space-y-3">
                      <input type="hidden" name="milestone_id" value="<?= e((string) $milestone['id']); ?>" />
                      <textarea name="content" rows="3" placeholder="Escribe un comentario" class="w-full rounded-lg border border-slate-200/70 bg-white px-3 py-2 text-xs outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900/60" required></textarea>
                      <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
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
