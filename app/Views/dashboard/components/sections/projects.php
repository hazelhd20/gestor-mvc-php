<?php
$isProjectsActive = ($activeTab ?? 'dashboard') === 'proyectos';
$projectsList = is_array($projects ?? null) ? $projects : [];
$projectsCount = count($projectsList);
$selectedProjectId = (int) ($selectedProject['id'] ?? 0);
$selectedProjectTitle = $selectedProject['title'] ?? null;
?>
<section id="section-proyectos" data-section="proyectos" class="space-y-8<?= $isProjectsActive ? '' : ' hidden'; ?>">
  <article class="rounded-3xl border border-slate-200/70 bg-gradient-to-br from-white via-white to-indigo-50/40 p-6 shadow-xl shadow-indigo-100/50 transition dark:border-slate-800/70 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950/70 dark:shadow-none">
    <div class="flex flex-col gap-6">
      <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs text-slate-500 dark:text-slate-300">
          <span class="inline-flex items-center gap-2 rounded-full border border-indigo-200/70 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 shadow-sm dark:border-indigo-900/60 dark:bg-indigo-950/50 dark:text-indigo-200">
            <i data-lucide="circle-check" class="h-3.5 w-3.5"></i>
            <?= e((string) $projectsCount); ?> en total
          </span>
          <?php if ($selectedProjectTitle): ?>
            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200/70 bg-white/80 px-3 py-1 text-xs font-semibold text-slate-600 shadow-sm dark:border-slate-700/60 dark:bg-slate-900/70 dark:text-slate-200">
              <i data-lucide="focus" class="h-3.5 w-3.5 text-indigo-400"></i>
              En vista: <?= e($selectedProjectTitle); ?>
            </span>
          <?php endif; ?>
        </div>
        <div class="flex w-full flex-wrap items-center justify-start gap-2 sm:gap-3 md:w-auto md:justify-end">
          <?php if (!empty($isDirector)): ?>
            <button
              data-modal="modalProject"
              type="button"
              class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-200/70 transition hover:-translate-y-0.5 hover:from-indigo-500 hover:to-indigo-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 dark:shadow-none sm:w-auto"
            >
              <i data-lucide="plus" class="h-4 w-4"></i> Nuevo proyecto
            </button>
          <?php endif; ?>
        </div>
      </header>

      <div class="rounded-2xl border border-slate-200/70 bg-white/80 shadow-md shadow-slate-200/60 dark:border-slate-800/70 dark:bg-slate-900/70">
        <div class="overflow-x-auto rounded-2xl">
          <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-gradient-to-r from-indigo-50 to-transparent text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:from-slate-900/60 dark:text-slate-300">
              <tr>
                <th scope="col" class="px-4 py-3">Proyecto</th>
                <th scope="col" class="px-4 py-3">Estudiante</th>
                <th scope="col" class="px-4 py-3">Estado</th>
                <th scope="col" class="px-4 py-3">Periodo</th>
                <th scope="col" class="px-4 py-3 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/70 dark:divide-slate-800/70">
            <?php if ($projectsList === []): ?>
              <tr>
                <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">
                  <div class="flex flex-col items-center gap-2">
                    <i data-lucide="sparkles" class="h-5 w-5 text-indigo-400"></i>
                    <p class="font-medium text-slate-600 dark:text-slate-300">Aun no se registran proyectos.</p>
                    <?php if (!empty($isDirector)): ?>
                      <p class="text-xs text-slate-400">Crea el primer proyecto para comenzar a asignar hitos y entregables.</p>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($projectsList as $project): ?>
                <?php
                  $isProjectDirector = !empty($isDirector) && (int) ($project['director_id'] ?? 0) === $userId;
                  $isProjectSelected = $selectedProjectId !== 0 && $selectedProjectId === (int) $project['id'];
                  $statusChipClasses = trim('inline-flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-wide shadow-sm ring-1 ring-inset ring-black/5 dark:ring-white/10 ' . (status_badge_classes($project['status']) ?? 'bg-slate-200 text-slate-600'));
                ?>
                <tr class="bg-white/60 transition hover:bg-indigo-50/50 dark:bg-transparent dark:hover:bg-slate-800/40">
                  <td class="px-4 py-4 align-top">
                    <div class="space-y-1">
                      <p class="font-semibold text-slate-800 dark:text-slate-100"><?= e($project['title']); ?></p>
                      <p class="text-xs text-slate-500">Director: <?= e($project['director_name']); ?></p>
                    </div>
                  </td>
                  <td class="px-4 py-4 align-top">
                    <div class="space-y-1 text-sm">
                      <p class="font-medium text-slate-700 dark:text-slate-200"><?= e($project['student_name']); ?></p>
                      <p class="text-[11px] text-slate-400"><?= e($project['student_email']); ?></p>
                    </div>
                  </td>
                  <td class="px-4 py-4 align-top">
                    <span class="<?= e($statusChipClasses); ?>">
                      <i data-lucide="circle-dot" class="h-3 w-3"></i>
                      <?= e(humanize_status($project['status'])); ?>
                    </span>
                  </td>
                  <td class="px-4 py-4 align-top text-sm text-slate-600 dark:text-slate-300">
                    <?= e(format_dashboard_period($project['start_date'] ?? null, $project['end_date'] ?? ($project['due_date'] ?? null))); ?>
                  </td>
                  <td class="px-4 py-4 align-top text-right">
                    <div class="flex flex-col items-stretch gap-3 text-xs sm:flex-row sm:flex-wrap sm:items-center sm:justify-end sm:gap-4">
                      <div class="flex flex-1 flex-col items-stretch gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end sm:gap-3">
                        <?php if (!$isProjectSelected): ?>
                          <a class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200/70 bg-white/70 px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 dark:border-slate-700/60 dark:bg-slate-900/70 dark:text-slate-200 sm:w-auto"
                             href="<?= e(url('/dashboard?tab=proyectos&project=' . (int) $project['id'])); ?>">
                            <i data-lucide="eye" class="h-3.5 w-3.5"></i> Ver
                          </a>
                        <?php else: ?>
                          <span class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200/70 bg-slate-100/70 px-3 py-1.5 text-xs font-semibold text-slate-400 dark:border-slate-700/60 dark:bg-slate-800/50 dark:text-slate-500 sm:w-auto" title="Proyecto en vista">
                            <i data-lucide="focus" class="h-3 w-3"></i> En vista
                          </span>
                        <?php endif; ?>

                        <?php if ($isProjectDirector): ?>
                          <button
                            type="button"
                            data-modal="modalProjectEdit"
                            data-project-edit
                            data-project-id="<?= e((string) $project['id']); ?>"
                            data-project-title="<?= e($project['title']); ?>"
                            data-project-description="<?= e($project['description'] ?? ''); ?>"
                            data-project-student="<?= e((string) $project['student_id']); ?>"
                            data-project-start="<?= e((string) ($project['start_date'] ?? '')); ?>"
                            data-project-end="<?= e((string) ($project['end_date'] ?? ($project['due_date'] ?? ''))); ?>"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-indigo-200/70 bg-white px-3 py-1.5 text-xs font-semibold text-indigo-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 dark:border-indigo-900/50 dark:bg-slate-900/60 dark:text-indigo-300 dark:hover:bg-indigo-900/40 sm:w-auto"
                          >
                            <i data-lucide="pencil" class="h-3.5 w-3.5"></i> Editar
                          </button>
                          <form method="post" action="<?= e(url('/projects/delete')); ?>" class="inline-flex items-center gap-2" onsubmit="return confirm('Seguro que deseas eliminar este proyecto? Esta accion no se puede deshacer.');">
                            <input type="hidden" name="project_id" value="<?= e((string) $project['id']); ?>" />
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-rose-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-400/60 dark:bg-rose-500 dark:hover:bg-rose-400 sm:w-auto">
                              <i data-lucide="trash" class="h-3.5 w-3.5"></i> Eliminar
                            </button>
                          </form>
                        <?php endif; ?>
                      </div>

                      <?php if ($isProjectDirector): ?>
                        <form method="post" action="<?= e(url('/projects/status')); ?>" class="inline-flex w-full flex-col items-stretch gap-2 sm:w-auto sm:flex-row sm:items-center sm:gap-3">
                          <input type="hidden" name="project_id" value="<?= e((string) $project['id']); ?>" />
                          <label class="sr-only" for="project-status-<?= e((string) $project['id']); ?>">Estado</label>
                          <select id="project-status-<?= e((string) $project['id']); ?>" name="status" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm transition hover:border-indigo-200 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:focus:border-indigo-500 sm:w-44">
                            <?php foreach (['planificado','en_progreso','en_riesgo','completado'] as $statusOption): ?>
                              <option value="<?= e($statusOption); ?>" <?= $statusOption === $project['status'] ? 'selected' : ''; ?>><?= e(humanize_status($statusOption)); ?></option>
                            <?php endforeach; ?>
                          </select>
                          <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 px-3 py-1.5 text-xs font-semibold text-white shadow-md shadow-indigo-200/70 transition hover:-translate-y-0.5 hover:from-indigo-500 hover:to-indigo-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 dark:shadow-none sm:w-auto">
                            <i data-lucide="save" class="h-3.5 w-3.5"></i>
                          </button>
                        </form>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </article>
</section>



