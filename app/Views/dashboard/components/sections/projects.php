<?php $isProjectsActive = ($activeTab ?? 'dashboard') === 'proyectos'; ?>
<section id="section-proyectos" data-section="proyectos" class="mt-8 space-y-6<?= $isProjectsActive ? '' : ' hidden'; ?>">
  <div class="flex flex-wrap items-center justify-end gap-3">
    <?php if (!empty($isDirector)): ?>
      <button data-modal="modalProject" type="button" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none">
        <i data-lucide="plus" class="h-4 w-4"></i> Nuevo proyecto
      </button>
    <?php endif; ?>
  </div>

  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
      <thead class="bg-slate-50 dark:bg-slate-900/70">
        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
          <th class="px-4 py-3">Proyecto</th>
          <th class="px-4 py-3">Estudiante</th>
          <th class="px-4 py-3">Estado</th>
          <th class="px-4 py-3">Entrega</th>
          <th class="px-4 py-3 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        <?php if ($projects === []): ?>
          <tr>
            <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">Aún no se registran proyectos.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($projects as $project): ?>
            <?php $isProjectDirector = !empty($isDirector) && (int) ($project['director_id'] ?? 0) === $userId; ?>
            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
              <td class="px-4 py-3">
                <div class="font-medium text-slate-800 dark:text-slate-100"><?= e($project['title']); ?></div>
                <div class="text-xs text-slate-500">Director: <?= e($project['director_name']); ?></div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-slate-700 dark:text-slate-200"><?= e($project['student_name']); ?></div>
                <div class="text-[11px] text-slate-400"><?= e($project['student_email']); ?></div>
              </td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center rounded-lg px-2 py-1 text-xs font-semibold <?= e(status_badge_classes($project['status'])); ?>">
                  <?= e(humanize_status($project['status'])); ?>
                </span>
              </td>
              <td class="px-4 py-3 text-sm">
                <?= e(format_dashboard_date($project['due_date'] ?? null)); ?>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex justify-end gap-2">
                  <a class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2 py-1 text-xs text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" href="<?= e(url('/dashboard?tab=proyectos&project=' . (int) $project['id'])); ?>">
                    <i data-lucide="eye" class="h-3.5 w-3.5"></i> Ver
                  </a>
                  <?php if ($isProjectDirector): ?>
                    <form method="post" action="<?= e(url('/projects/status')); ?>" class="inline-flex items-center gap-1">
                      <input type="hidden" name="project_id" value="<?= e((string) $project['id']); ?>" />
                      <label class="sr-only" for="project-status-<?= e((string) $project['id']); ?>">Estado</label>
                      <select id="project-status-<?= e((string) $project['id']); ?>" name="status" class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs outline-none dark:border-slate-700 dark:bg-slate-900">
                        <?php foreach (['planificado','en_progreso','en_riesgo','completado'] as $statusOption): ?>
                          <option value="<?= e($statusOption); ?>" <?= $statusOption === $project['status'] ? 'selected' : ''; ?>><?= e(humanize_status($statusOption)); ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-2 py-1 text-xs font-medium text-white hover:bg-indigo-700">
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
</section>
