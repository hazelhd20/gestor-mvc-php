<?php $isMilestonesActive = ($activeTab ?? 'dashboard') === 'hitos'; ?>
<section id="section-hitos" data-section="hitos" class="space-y-8<?= $isMilestonesActive ? '' : ' hidden'; ?>">
  <?php if (!$selectedProject): ?>
    <div class="rounded-3xl border border-dashed border-slate-200 bg-gradient-to-b from-white via-white to-slate-50 px-6 py-12 text-center text-sm text-slate-500 shadow-inner dark:border-slate-800 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">
      <i data-lucide="target" class="mx-auto mb-4 h-8 w-8 text-indigo-500/70"></i>
      <p class="text-sm font-medium text-slate-600 dark:text-slate-200">Selecciona un proyecto para gestionar sus hitos y entregables.</p>
      <p class="mt-1 text-xs text-slate-400">Activa un proyecto para comenzar a registrar avances y recibir feedback.</p>
    </div>
  <?php else: ?>
    <?php
      $milestonesCount = is_array($projectMilestones) ? count($projectMilestones) : 0;
      $projectStatusClasses = trim('inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold shadow-sm ring-1 ring-inset ring-black/5 dark:ring-white/10 ' . (status_badge_classes($selectedProject['status']) ?? 'bg-slate-200 text-slate-600'));
    ?>
    <article class="rounded-3xl border border-slate-200/70 bg-gradient-to-br from-white via-white to-indigo-50/40 p-6 shadow-xl shadow-indigo-100/50 transition dark:border-slate-800/70 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950/70 dark:shadow-none">
      <div class="flex flex-col gap-6">
        <header class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div class="space-y-3">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100"><?= e($selectedProject['title']); ?></h3>
            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-300">
              <span class="inline-flex items-center gap-2 rounded-full border border-slate-200/70 bg-white/80 px-3 py-1 shadow-sm dark:border-slate-700/60 dark:bg-slate-900/70">
                <i data-lucide="graduation-cap" class="h-3.5 w-3.5 text-indigo-400"></i>
                <span class="font-semibold text-slate-600 dark:text-slate-100"><?= e($selectedProject['student_name']); ?></span>
              </span>
              <span class="inline-flex items-center gap-2 rounded-full border border-slate-200/70 bg-white/80 px-3 py-1 shadow-sm dark:border-slate-700/60 dark:bg-slate-900/70">
                <i data-lucide="users" class="h-3.5 w-3.5 text-indigo-400"></i>
                <span class="font-semibold text-slate-600 dark:text-slate-100"><?= e($selectedProject['director_name']); ?></span>
              </span>
              <span class="inline-flex items-center gap-2 rounded-full border border-slate-200/70 bg-white/80 px-3 py-1 shadow-sm text-slate-500 dark:border-slate-700/60 dark:bg-slate-900/70 dark:text-slate-300">
                <i data-lucide="calendar" class="h-3.5 w-3.5 text-indigo-400"></i>
                <?= e(format_dashboard_period($selectedProject['start_date'] ?? null, $selectedProject['end_date'] ?? ($selectedProject['due_date'] ?? null))); ?>
              </span>
            </div>
          </div>
          <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:flex-wrap sm:items-center">
            <span class="inline-flex items-center gap-2 rounded-full border border-indigo-200/70 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 shadow-sm dark:border-indigo-900/60 dark:bg-indigo-950/50 dark:text-indigo-200">
              <i data-lucide="circle-check" class="h-3.5 w-3.5"></i>
              <?= e((string) $milestonesCount); ?> hitos
            </span>
            <div class="flex flex-wrap items-center justify-start gap-2 sm:justify-end sm:gap-3">
              <span class="<?= e($projectStatusClasses); ?>">
                <i data-lucide="circle-dot" class="h-3.5 w-3.5"></i>
                <?= e(humanize_status($selectedProject['status'])); ?>
              </span>
              <?php if (!empty($isDirector) && !empty($selectedProject)): ?>
                <button
                  data-modal="modalMilestone"
                  type="button"
                  class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-200/70 transition hover:-translate-y-0.5 hover:from-indigo-500 hover:to-indigo-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 dark:shadow-none"
                >
                  <i data-lucide="plus" class="h-4 w-4"></i> Nuevo hito
                </button>
              <?php endif; ?>
            </div>
          </div>
        </header>

        <div class="space-y-5">
          <?php if ($projectMilestones === []): ?>
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 px-6 py-10 text-center text-sm text-slate-500 shadow-sm dark:border-slate-700 dark:bg-slate-900/60">
              <i data-lucide="sparkles" class="mx-auto mb-3 h-6 w-6 text-indigo-400"></i>
              <p class="font-medium text-slate-600 dark:text-slate-200">Aun no hay hitos registrados.</p>
              <p class="mt-1 text-xs text-slate-400">Crea el primer hito para planificar entregas y seguimiento.</p>
            </div>
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
                $milestoneStatusClasses = trim('inline-flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-wide shadow-sm ring-1 ring-inset ring-black/5 dark:ring-white/10 ' . (status_badge_classes($milestone['status']) ?? 'bg-slate-200 text-slate-600'));
                $milestoneDeliverablesCount = (int) ($milestone['deliverables_count'] ?? count($deliverables));
              ?>
              <article id="milestone-<?= e((string) $milestone['id']); ?>" class="group relative overflow-hidden rounded-2xl border border-slate-200/70 bg-white/80 p-5 shadow-md shadow-slate-200/60 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-xl dark:border-slate-800/70 dark:bg-slate-900/60 dark:shadow-none">
                <div class="flex flex-col gap-5">
                  <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="space-y-3"><h4 class="text-base font-semibold text-slate-800 dark:text-slate-100"><?= e($milestone['title']); ?></h4>
                      <p class="text-sm text-slate-500 dark:text-slate-300"><?= e($milestone['description'] ?: 'Sin descripcion.'); ?></p>
                      <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-400 sm:gap-3">
                        <span class="inline-flex items-center gap-1 rounded-full border border-slate-200/70 bg-white/70 px-2.5 py-1 shadow-sm dark:border-slate-700/60 dark:bg-slate-900/70">
                          <i data-lucide="calendar" class="h-3 w-3 text-indigo-400"></i>
                          <?= e(format_dashboard_period($milestone['start_date'] ?? null, $milestone['end_date'] ?? ($milestone['due_date'] ?? null))); ?>
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full border border-slate-200/70 bg-white/70 px-2.5 py-1 shadow-sm text-slate-500 dark:border-slate-700/60 dark:bg-slate-900/70 dark:text-slate-300">
                          <i data-lucide="package" class="h-3 w-3 text-indigo-400"></i>
                          <?= e((string) $milestoneDeliverablesCount); ?> entregables
                        </span>
                      </div>
                    </div>
                    <div class="flex flex-col items-start gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:gap-3">
                      <span class="<?= e($milestoneStatusClasses); ?>">
                        <i data-lucide="circle-dot" class="h-3 w-3"></i>
                        <?= e(humanize_status($milestone['status'])); ?>
                      </span>
                      <?php if ($statusOptions !== []): ?>
                        <form method="post" action="<?= e(url('/milestones/status')); ?>" class="flex items-center gap-2 sm:gap-3">
                          <input type="hidden" name="milestone_id" value="<?= e((string) $milestone['id']); ?>" />
                          <input type="hidden" name="return_tab" value="<?= e((string) ($activeTab ?? 'dashboard')); ?>" />
                          <input type="hidden" name="return_project" value="<?= e((string) ($selectedProject['id'] ?? $milestone['project_id'] ?? '')); ?>" />
                          <input type="hidden" name="return_anchor" value="<?= e('milestone-' . (string) $milestone['id']); ?>" />
                          <label class="sr-only" for="milestone-status-<?= e((string) $milestone['id']); ?>">Estado</label>
                          <select id="milestone-status-<?= e((string) $milestone['id']); ?>" name="status" class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm transition hover:border-indigo-200 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:focus:border-indigo-500">
                            <?php foreach ($statusOptions as $option): ?>
                              <option value="<?= e($option); ?>" <?= $option === ($milestone['status'] ?? '') ? 'selected' : ''; ?>><?= e(humanize_status($option)); ?></option>
                            <?php endforeach; ?>
                          </select>
                          <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl border border-indigo-200/70 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 dark:border-indigo-900/60 dark:bg-indigo-950/40 dark:text-indigo-200 dark:hover:bg-indigo-900/50">
                            <i data-lucide="save" class="h-3.5 w-3.5"></i>
                            <span class="sr-only">Guardar estado</span>
                          </button>
                        </form>
                      <?php endif; ?>
                      <?php if ($isDirectorOwner): ?>
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                          <button
                            type="button"
                            data-modal="modalMilestoneEdit"
                            data-milestone-edit
                            data-milestone-id="<?= e((string) $milestone['id']); ?>"
                            data-milestone-title="<?= e($milestone['title']); ?>"
                            data-milestone-description="<?= e((string) ($milestone['description'] ?? '')); ?>"
                            data-milestone-start="<?= e((string) ($milestone['start_date'] ?? '')); ?>"
                            data-milestone-end="<?= e((string) ($milestone['end_date'] ?? ($milestone['due_date'] ?? ''))); ?>"
                            class="inline-flex items-center gap-2 rounded-xl border border-indigo-200/70 bg-white px-3 py-1.5 text-xs font-semibold text-indigo-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 dark:border-indigo-900/50 dark:bg-slate-900/60 dark:text-indigo-300 dark:hover:bg-indigo-900/40"
                          >
                            <i data-lucide="pencil" class="h-3.5 w-3.5"></i> Editar
                          </button>
                          <form method="post" action="<?= e(url('/milestones/delete')); ?>" class="inline-flex items-center gap-2" onsubmit="return confirm('Seguro que deseas eliminar este hito? Esta accion no se puede deshacer.');">
                            <input type="hidden" name="milestone_id" value="<?= e((string) $milestone['id']); ?>" />
                            <input type="hidden" name="return_tab" value="<?= e((string) ($activeTab ?? 'dashboard')); ?>" />
                            <input type="hidden" name="return_project" value="<?= e((string) ($selectedProject['id'] ?? $milestone['project_id'] ?? '')); ?>" />
                            <input type="hidden" name="return_anchor" value="<?= e('milestone-' . (string) $milestone['id']); ?>" />
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-rose-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-400/60 dark:bg-rose-500 dark:hover:bg-rose-400">
                              <i data-lucide="trash" class="h-3.5 w-3.5"></i> Eliminar
                            </button>
                          </form>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>

                  <div class="grid gap-4 lg:grid-cols-2">
                    <section class="rounded-2xl border border-slate-200/60 bg-white/80 p-5 text-xs shadow-inner shadow-slate-200/40 dark:border-slate-800/60 dark:bg-slate-900/60">
                      <div class="flex items-center justify-between">
                        <p class="font-semibold text-slate-700 dark:text-slate-200">Entregables</p>
                        <span class="inline-flex items-center gap-1 rounded-full border border-indigo-200/70 bg-indigo-50 px-2.5 py-0.5 text-[11px] font-semibold text-indigo-700 shadow-sm dark:border-indigo-900/60 dark:bg-indigo-950/40 dark:text-indigo-200">
                          <i data-lucide="box" class="h-3 w-3"></i>
                          <?= e((string) $milestoneDeliverablesCount); ?>
                        </span>
                      </div>
                      <div class="mt-3 max-h-48 space-y-3 overflow-y-auto pr-1 scrollbar-thin">
                        <?php if ($deliverables === []): ?>
                          <p class="rounded-xl border border-dashed border-slate-300/80 bg-slate-50/70 px-3 py-2 text-center text-slate-500 dark:border-slate-700/60 dark:bg-slate-900/50">Sin entregas aun.</p>
                        <?php else: ?>
                          <?php foreach ($deliverables as $deliverable): ?>
                            <div class="rounded-xl border border-slate-200/70 bg-white/90 px-3 py-2 shadow-sm transition hover:border-indigo-200 hover:bg-white dark:border-slate-800/60 dark:bg-slate-950/40 dark:hover:border-indigo-900/50">
                              <div class="flex items-start justify-between gap-3">
                                <div>
                                  <p class="font-semibold text-slate-700 dark:text-slate-200"><?= e($deliverable['original_name']); ?></p>
                                  <p class="text-[11px] text-slate-400">Autor: <?= e($deliverable['author_name']); ?></p>
                                  <p class="mt-1 text-[11px] text-slate-400"><?= e($deliverable['notes'] ? 'Incluye notas adicionales' : 'Solo archivo adjunto'); ?></p>
                                </div>
                                <?php if (!empty($deliverable['file_path'])): ?>
                                  <a class="inline-flex items-center gap-1 rounded-lg border border-indigo-200/70 bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-100 dark:border-indigo-900/60 dark:bg-indigo-950/40 dark:text-indigo-200" href="<?= e(url('/deliverables/download?id=' . (int) $deliverable['id'])); ?>">
                                    <i data-lucide="download" class="h-3 w-3"></i> Descargar
                                  </a>
                                <?php endif; ?>
                              </div>
                              <?php if (!empty($deliverable['notes'])): ?>
                                <p class="mt-2 rounded-lg bg-slate-100/70 px-3 py-2 text-slate-600 dark:bg-slate-800/70 dark:text-slate-200">"<?= e($deliverable['notes']); ?>"</p>
                              <?php endif; ?>
                            </div>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </div>

                      <div class="mt-4">
                        <?php if ($canUploadDeliverable): ?>
                          <form method="post" action="<?= e(url('/deliverables')); ?>" enctype="multipart/form-data" class="space-y-3">
                            <input type="hidden" name="milestone_id" value="<?= e((string) $milestone['id']); ?>" />
                            <input type="hidden" name="return_tab" value="<?= e((string) ($activeTab ?? 'dashboard')); ?>" />
                            <input type="hidden" name="return_project" value="<?= e((string) ($selectedProject['id'] ?? $milestone['project_id'] ?? '')); ?>" />
                            <input type="hidden" name="return_anchor" value="<?= e('milestone-' . (string) $milestone['id']); ?>" />
                            <label class="block text-[11px] font-semibold uppercase text-slate-500 dark:text-slate-300">Subir avance</label>
                            <input type="file" name="file" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600 shadow-sm transition focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:focus:border-indigo-500" />
                            <textarea name="notes" rows="2" placeholder="Notas complementarias (opcional)" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600 shadow-sm transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></textarea>
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-2 text-xs font-semibold text-white shadow-md shadow-indigo-200/70 transition hover:-translate-y-0.5 hover:from-indigo-500 hover:to-indigo-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 dark:shadow-none">
                              <i data-lucide="upload" class="h-3.5 w-3.5"></i> Registrar avance
                            </button>
                          </form>
                        <?php else: ?>
                          <p class="rounded-xl bg-amber-50/80 px-3 py-2 text-[11px] text-amber-700 shadow-sm dark:bg-amber-900/40 dark:text-amber-200">Solo el estudiante puede registrar avances antes de enviar a revision o despues de aprobacion.</p>
                        <?php endif; ?>
                      </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200/60 bg-white/80 p-5 text-xs shadow-inner shadow-slate-200/40 dark:border-slate-800/60 dark:bg-slate-900/60">
                      <div class="flex items-center justify-between">
                        <p class="font-semibold text-slate-700 dark:text-slate-200">Feedback</p>
                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200/70 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 shadow-sm dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
                          <i data-lucide="message-circle" class="h-3 w-3"></i>
                          <?= e((string) ($milestone['feedback_count'] ?? 0)); ?>
                        </span>
                      </div>
                      <div class="mt-3 max-h-48 space-y-3 overflow-y-auto pr-1 scrollbar-thin">
                        <?php if ($feedbackList === []): ?>
                          <p class="rounded-xl border border-dashed border-slate-300/80 bg-slate-50/70 px-3 py-2 text-center text-slate-500 dark:border-slate-700/60 dark:bg-slate-900/50">Sin comentarios aun.</p>
                        <?php else: ?>
                          <?php foreach ($feedbackList as $comment): ?>
                            <div class="rounded-xl border border-slate-200/70 bg-white/90 px-3 py-2 shadow-sm transition hover:border-emerald-200 hover:bg-white dark:border-slate-800/60 dark:bg-slate-950/40 dark:hover:border-emerald-900/50">
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
                          <input type="hidden" name="return_tab" value="<?= e((string) ($activeTab ?? 'dashboard')); ?>" />
                          <input type="hidden" name="return_project" value="<?= e((string) ($selectedProject['id'] ?? $milestone['project_id'] ?? '')); ?>" />
                          <input type="hidden" name="return_anchor" value="<?= e('milestone-' . (string) $milestone['id']); ?>" />
                          <textarea name="content" rows="3" placeholder="Escribe un comentario" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600 shadow-sm transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-emerald-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" required></textarea>
                          <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 px-4 py-2 text-xs font-semibold text-white shadow-md shadow-emerald-200/70 transition hover:-translate-y-0.5 hover:from-emerald-500 hover:to-emerald-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:shadow-none">
                            <i data-lucide="send" class="h-3.5 w-3.5"></i> Enviar feedback
                          </button>
                        </form>
                      </div>
                    </section>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </article>
  <?php endif; ?>
</section>


