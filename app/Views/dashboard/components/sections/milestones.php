<?php $isMilestonesActive = ($activeTab ?? 'dashboard') === 'hitos'; ?>
<section id="section-hitos" data-section="hitos" class="space-y-8<?= $isMilestonesActive ? '' : ' hidden'; ?>">
  <?php if (!$selectedProject): ?>
    <div class="rounded-2xl bg-white px-6 py-12 text-center text-sm text-slate-500 shadow-sm ring-1 ring-slate-200/70 dark:bg-slate-900 dark:text-slate-400 dark:ring-slate-800">
      Selecciona un proyecto para gestionar sus hitos.
    </div>
  <?php else: ?>
    <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/70 dark:bg-slate-900 dark:ring-slate-800">
      <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-1">
          <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Plan de hitos</h2>
          <p class="text-sm text-slate-500 dark:text-slate-400">Organiza entregas y revisiones del proyecto seleccionado para mantener un seguimiento claro.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <?php if (!empty($isDirector) && !empty($selectedProject)): ?>
            <button
              data-modal="modalMilestone"
              type="button"
              class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500"
            >
              <i data-lucide="plus" class="h-4 w-4"></i> Nuevo hito
            </button>
          <?php endif; ?>
          <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-200">
            <i data-lucide="bookmark" class="h-3.5 w-3.5"></i> <?= e(humanize_status($selectedProject['status'])); ?>
          </span>
        </div>
      </header>

      <div class="mt-5 rounded-2xl bg-slate-50/80 p-4 text-sm leading-6 text-slate-600 ring-1 ring-slate-200/70 dark:bg-slate-900/50 dark:text-slate-300 dark:ring-slate-800/70">
        <p class="font-medium text-slate-700 dark:text-slate-200"><?= e($selectedProject['title']); ?></p>
        <div class="mt-2 grid gap-2 text-xs sm:grid-cols-2">
          <span>Estudiante: <strong class="font-semibold text-slate-700 dark:text-slate-200"><?= e($selectedProject['student_name']); ?></strong></span>
          <span>Director: <strong class="font-semibold text-slate-700 dark:text-slate-200"><?= e($selectedProject['director_name']); ?></strong></span>
          <span class="sm:col-span-2">Periodo: <?= e(format_dashboard_period($selectedProject['start_date'] ?? null, $selectedProject['end_date'] ?? ($selectedProject['due_date'] ?? null))); ?></span>
        </div>
      </div>

      <div class="mt-6 space-y-5">
        <?php if ($projectMilestones === []): ?>
          <div class="rounded-2xl bg-slate-50 px-5 py-8 text-center text-sm text-slate-500 shadow-inner dark:bg-slate-900/50 dark:text-slate-400">Aún no hay hitos registrados.</div>
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
            <article class="rounded-2xl bg-white/70 p-5 shadow-sm ring-1 ring-slate-200/70 backdrop-blur dark:bg-slate-900/60 dark:ring-slate-800/70">
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="space-y-2">
                  <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100"><?= e($milestone['title']); ?></h3>
                  <p class="text-sm text-slate-600 dark:text-slate-300"><?= e($milestone['description'] ?: 'Sin descripción.'); ?></p>
                  <p class="text-xs text-slate-400">Periodo: <?= e(format_dashboard_period($milestone['start_date'] ?? null, $milestone['end_date'] ?? ($milestone['due_date'] ?? null))); ?></p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                  <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-[11px] font-semibold <?= e(status_badge_classes($milestone['status'])); ?>"><?= e(humanize_status($milestone['status'])); ?></span>
                  <?php if ($statusOptions !== []): ?>
                    <form method="post" action="<?= e(url('/milestones/status')); ?>" class="inline-flex items-center gap-1.5">
                      <input type="hidden" name="milestone_id" value="<?= e((string) $milestone['id']); ?>" />
                      <label class="sr-only" for="milestone-status-<?= e((string) $milestone['id']); ?>">Estado</label>
                      <select id="milestone-status-<?= e((string) $milestone['id']); ?>" name="status" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium outline-none transition focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-400">
                        <?php foreach ($statusOptions as $option): ?>
                          <option value="<?= e($option); ?>" <?= $option === ($milestone['status'] ?? '') ? 'selected' : ''; ?>><?= e(humanize_status($option)); ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                        <i data-lucide="save" class="h-3.5 w-3.5"></i>
                      </button>
                    </form>
                  <?php endif; ?>
                  <?php if (!empty($isDirectorOwner)): ?>
                    <button
                      type="button"
                      data-modal="modalMilestoneEdit"
                      data-milestone-edit
                      data-milestone-id="<?= e((string) $milestone['id']); ?>"
                      data-milestone-title="<?= e($milestone['title']); ?>"
                      data-milestone-description="<?= e($milestone['description'] ?? ''); ?>"
                      data-milestone-start="<?= e((string) ($milestone['start_date'] ?? '')); ?>"
                      data-milestone-end="<?= e((string) ($milestone['end_date'] ?? ($milestone['due_date'] ?? ''))); ?>"
                      class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-800"
                    >
                      <i data-lucide="pencil" class="h-3.5 w-3.5"></i> Editar
                    </button>
                    <form method="post" action="<?= e(url('/milestones/delete')); ?>" class="inline-flex items-center gap-1.5" onsubmit="return confirm('¿Seguro que deseas eliminar este hito? Esta acción no se puede deshacer.');">
                      <input type="hidden" name="milestone_id" value="<?= e((string) $milestone['id']); ?>" />
                      <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-rose-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-500">
                        <i data-lucide="trash" class="h-3.5 w-3.5"></i> Eliminar
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>

              <div class="mt-4 grid gap-4 lg:grid-cols-2">
                <section class="rounded-2xl bg-slate-50/80 p-4 text-xs ring-1 ring-slate-200/70 dark:bg-slate-900/40 dark:ring-slate-800/70">
                  <div class="flex items-center justify-between">
                    <p class="font-semibold text-slate-700 dark:text-slate-200">Entregables</p>
                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-[11px] font-semibold text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-200">
                      <?= e((string) ($milestone['deliverables_count'] ?? 0)); ?>
                    </span>
                  </div>
                  <div class="mt-3 max-h-44 space-y-3 overflow-y-auto pr-1 scrollbar-thin">
                    <?php if ($deliverables === []): ?>
                      <p class="text-slate-500 dark:text-slate-400">Sin entregas aún.</p>
                    <?php else: ?>
                      <?php foreach ($deliverables as $deliverable): ?>
                        <div class="rounded-xl bg-white px-3 py-2 shadow-sm ring-1 ring-slate-200/70 transition hover:ring-indigo-200 dark:bg-slate-950/30 dark:ring-slate-800/70">
                          <p class="text-slate-700 dark:text-slate-200"><?= e($deliverable['original_name']); ?></p>
                          <p class="text-[11px] text-slate-400">Autor: <?= e($deliverable['author_name']); ?></p>
                          <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-400">
                            <span><?= e($deliverable['notes'] ? 'Notas incluidas' : 'Archivo'); ?></span>
                            <?php if (!empty($deliverable['file_path'])): ?>
                              <a class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2 py-0.5 text-[11px] font-medium text-indigo-600 transition hover:border-indigo-200 hover:bg-indigo-50 dark:border-slate-700 dark:text-indigo-300 dark:hover:border-indigo-400/60 dark:hover:bg-indigo-900/40" href="<?= e(url('/deliverables/download?id=' . (int) $deliverable['id'])); ?>">
                                <i data-lucide="download" class="h-3 w-3"></i> Descargar
                              </a>
                            <?php endif; ?>
                          </div>
                          <?php if (!empty($deliverable['notes'])): ?>
                            <p class="mt-3 rounded-lg bg-slate-100 px-3 py-2 text-slate-600 dark:bg-slate-800 dark:text-slate-200">"<?= e($deliverable['notes']); ?>"</p>
                          <?php endif; ?>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>

                  <div class="mt-4">
                    <?php if ($canUploadDeliverable): ?>
                      <form method="post" action="<?= e(url('/deliverables')); ?>" enctype="multipart/form-data" class="space-y-3">
                        <input type="hidden" name="milestone_id" value="<?= e((string) $milestone['id']); ?>" />
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Registrar avance</label>
                        <input type="file" name="file" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600 transition focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:focus:border-indigo-400" />
                        <textarea name="notes" rows="2" placeholder="Notas complementarias (opcional)" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs outline-none transition focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:focus:border-indigo-400"></textarea>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                          <i data-lucide="upload" class="h-3.5 w-3.5"></i> Guardar avance
                        </button>
                      </form>
                    <?php else: ?>
                      <p class="text-[11px] text-slate-500 dark:text-slate-400">Solo el estudiante puede registrar avances antes de enviar a revisión o después de la aprobación.</p>
                    <?php endif; ?>
                  </div>
                </section>

                <section class="rounded-2xl bg-slate-50/80 p-4 text-xs ring-1 ring-slate-200/70 dark:bg-slate-900/40 dark:ring-slate-800/70">
                  <div class="flex items-center justify-between">
                    <p class="font-semibold text-slate-700 dark:text-slate-200">Feedback</p>
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-200">
                      <?= e((string) ($milestone['feedback_count'] ?? 0)); ?>
                    </span>
                  </div>
                  <div class="mt-3 max-h-44 space-y-3 overflow-y-auto pr-1 scrollbar-thin">
                    <?php if ($feedbackList === []): ?>
                      <p class="text-slate-500 dark:text-slate-400">Sin comentarios aún.</p>
                    <?php else: ?>
                      <?php foreach ($feedbackList as $comment): ?>
                        <div class="rounded-xl bg-white px-3 py-2 shadow-sm ring-1 ring-slate-200/70 transition hover:ring-emerald-200 dark:bg-slate-950/30 dark:ring-slate-800/70">
                          <p class="font-semibold text-slate-700 dark:text-slate-200"><?= e($comment['author_name']); ?></p>
                          <p class="mt-1 text-slate-600 dark:text-slate-300">"<?= e($comment['content']); ?>"</p>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                  <div class="mt-4">
                    <form method="post" action="<?= e(url('/feedback')); ?>" class="space-y-3">
                      <input type="hidden" name="milestone_id" value="<?= e((string) $milestone['id']); ?>" />
                      <?php if (!empty($selectedProject)): ?>
                        <input type="hidden" name="project_id" value="<?= e((string) $selectedProject['id']); ?>">
                      <?php endif; ?>
                      <textarea name="content" rows="3" placeholder="Escribe un comentario" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs outline-none transition focus:border-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:focus:border-emerald-400" required></textarea>
                      <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500">
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
