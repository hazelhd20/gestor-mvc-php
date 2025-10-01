<?php $isProjectsActive = ($activeTab ?? 'dashboard') === 'proyectos'; ?>
<section id="section-proyectos" data-section="proyectos" class="space-y-8<?= $isProjectsActive ? '' : ' hidden'; ?>">
  <header class="flex flex-wrap items-center justify-between gap-3">
    <div class="max-w-xl space-y-1">
      <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Gestión de proyectos</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400">Selecciona un proyecto para revisar sus hitos, comentarios y registrar avances.</p>
    </div>
    <div class="flex items-center gap-2">
      <?php if (!empty($isDirector)): ?>
        <button
          data-modal="modalProject"
          type="button"
          class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500"
        >
          <i data-lucide="plus" class="h-4 w-4"></i> Nuevo proyecto
        </button>
      <?php endif; ?>
    </div>
  </header>

  <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/70 dark:bg-slate-900 dark:ring-slate-800">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200 text-sm leading-6 dark:divide-slate-800">
        <thead class="bg-slate-50/80 dark:bg-slate-900/70">
          <tr class="text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">
            <th class="px-5 py-3">Proyecto</th>
            <th class="px-5 py-3">Estudiante</th>
            <th class="px-5 py-3">Estado</th>
            <th class="px-5 py-3">Periodo</th>
            <th class="px-5 py-3 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white/60 dark:divide-slate-800 dark:bg-transparent">
          <?php if ($projects === []): ?>
            <tr>
              <td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500 dark:text-slate-400">Aún no se registran proyectos.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($projects as $project): ?>
              <?php $isProjectDirector = !empty($isDirector) && (int) ($project['director_id'] ?? 0) === $userId; ?>
              <?php $isProjectSelected = !empty($selectedProject) && (int) ($selectedProject['id'] ?? 0) === (int) $project['id']; ?>
              <?php
                $rowClasses = 'transition-colors';
                $rowClasses .= $isProjectSelected
                    ? ' bg-indigo-50/70 dark:bg-indigo-500/10'
                    : ' hover:bg-slate-50/80 dark:hover:bg-slate-800/40';
              ?>
              <tr class="<?= e($rowClasses); ?>">
                <td class="px-5 py-4 align-top">
                  <div class="font-semibold text-slate-800 dark:text-slate-100"><?= e($project['title']); ?></div>
                  <p class="mt-1 text-xs text-slate-500">Director: <?= e($project['director_name']); ?></p>
                </td>
                <td class="px-5 py-4 align-top">
                  <div class="font-medium text-slate-700 dark:text-slate-200"><?= e($project['student_name']); ?></div>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"><?= e($project['student_email']); ?></p>
                </td>
                <td class="px-5 py-4 align-top">
                  <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold <?= e(status_badge_classes($project['status'])); ?>">
                    <?= e(humanize_status($project['status'])); ?>
                  </span>
                </td>
                <td class="px-5 py-4 align-top text-sm text-slate-600 dark:text-slate-300">
                  <?= e(format_dashboard_period($project['start_date'] ?? null, $project['end_date'] ?? ($project['due_date'] ?? null))); ?>
                </td>
                <td class="px-5 py-4 align-top text-right">
                  <div class="flex flex-wrap justify-end gap-2">
                    <?php if (!$isProjectSelected): ?>
                      <a
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-800"
                        href="<?= e(url('/dashboard?tab=proyectos&project=' . (int) $project['id'])); ?>"
                      >
                        <i data-lucide="eye" class="h-3.5 w-3.5"></i> Ver
                      </a>
                    <?php else: ?>
                      <span class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-medium text-slate-400 dark:border-slate-700 dark:text-slate-500" title="Proyecto en vista">En vista</span>
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
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-800"
                      >
                        <i data-lucide="pencil" class="h-3.5 w-3.5"></i> Editar
                      </button>
                      <form
                        method="post"
                        action="<?= e(url('/projects/delete')); ?>"
                        class="inline-flex items-center gap-1.5"
                        onsubmit="return confirm('¿Seguro que deseas eliminar este proyecto? Esta acción no se puede deshacer.');"
                      >
                        <input type="hidden" name="project_id" value="<?= e((string) $project['id']); ?>" />
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-rose-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-500">
                          <i data-lucide="trash" class="h-3.5 w-3.5"></i> Eliminar
                        </button>
                      </form>
                    <?php endif; ?>
                    <?php if ($isProjectDirector): ?>
                      <form method="post" action="<?= e(url('/projects/status')); ?>" class="inline-flex items-center gap-1.5">
                        <input type="hidden" name="project_id" value="<?= e((string) $project['id']); ?>" />
                        <label class="sr-only" for="project-status-<?= e((string) $project['id']); ?>">Estado</label>
                        <select id="project-status-<?= e((string) $project['id']); ?>" name="status" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium outline-none transition focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-400">
                          <?php foreach (['planificado','en_progreso','en_riesgo','completado'] as $statusOption): ?>
                            <option value="<?= e($statusOption); ?>" <?= $statusOption === $project['status'] ? 'selected' : ''; ?>><?= e(humanize_status($statusOption)); ?></option>
                          <?php endforeach; ?>
                        </select>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
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
</section>
