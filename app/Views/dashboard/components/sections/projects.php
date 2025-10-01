<?php $isProjectsActive = ($activeTab ?? 'dashboard') === 'proyectos'; ?>
<section id="section-proyectos" data-section="proyectos" class="flex flex-col gap-5<?= $isProjectsActive ? '' : ' hidden'; ?>">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div class="max-w-2xl">
      <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Gestión de proyectos</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400">Administra los equipos, estados y fechas de entrega desde un panel más claro y moderno.</p>
    </div>
    <?php if (!empty($isDirector)): ?>
      <button data-modal="modalProject" type="button" class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-indigo-500 via-indigo-600 to-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-lg transition duration-200 hover:-translate-y-0.5 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-indigo-400/60">
        <i data-lucide="plus" class="h-4 w-4"></i>
        Nuevo proyecto
      </button>
    <?php endif; ?>
  </div>

  <div class="overflow-hidden rounded-3xl glass-card shadow-xl">
    <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-800/60">
      <thead class="bg-white/40 backdrop-blur dark:bg-slate-900/40">
        <tr class="text-left text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
          <th class="px-5 py-3">Proyecto</th>
          <th class="px-5 py-3">Estudiante</th>
          <th class="px-5 py-3">Estado</th>
          <th class="px-5 py-3">Entrega</th>
          <th class="px-5 py-3 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100/60 dark:divide-slate-800/60">
        <?php if ($projects === []): ?>
          <tr>
            <td colspan="5" class="px-5 py-7 text-center text-sm text-slate-500">Aún no se registran proyectos.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($projects as $project): ?>
            <?php $isProjectDirector = !empty($isDirector) && (int) ($project['director_id'] ?? 0) === $userId; ?>
            <tr class="transition-colors duration-200 hover:bg-indigo-500/5 dark:hover:bg-indigo-500/10">
              <td class="px-5 py-4">
                <div class="font-medium text-slate-800 dark:text-slate-100"><?= e($project['title']); ?></div>
                <div class="text-xs text-slate-500">Director: <?= e($project['director_name']); ?></div>
              </td>
              <td class="px-5 py-4">
                <div class="text-sm font-medium text-slate-700 dark:text-slate-200"><?= e($project['student_name']); ?></div>
                <div class="text-[11px] text-slate-400"><?= e($project['student_email']); ?></div>
              </td>
              <td class="px-5 py-4">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?= e(status_badge_classes($project['status'])); ?>">
                  <?= e(humanize_status($project['status'])); ?>
                </span>
              </td>
              <td class="px-5 py-4 text-sm">
                <?= e(format_dashboard_date($project['due_date'] ?? null)); ?>
              </td>
              <td class="px-5 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <a class="inline-flex items-center gap-1 rounded-full border border-slate-200/80 px-3 py-1 text-xs font-medium text-slate-600 transition duration-200 hover:-translate-y-0.5 hover:bg-white dark:border-slate-700/60 dark:text-slate-300 dark:hover:bg-slate-900" href="<?= e(url('/dashboard?tab=proyectos&project=' . (int) $project['id'])); ?>">
                    <i data-lucide="eye" class="h-3.5 w-3.5"></i>
                    Ver
                  </a>
                  <?php if ($isProjectDirector): ?>
                    <form method="post" action="<?= e(url('/projects/status')); ?>" class="inline-flex items-center gap-1">
                      <input type="hidden" name="project_id" value="<?= e((string) $project['id']); ?>" />
                      <label class="sr-only" for="project-status-<?= e((string) $project['id']); ?>">Estado</label>
                      <select id="project-status-<?= e((string) $project['id']); ?>" name="status" class="rounded-full border border-slate-200/80 bg-white px-3 py-1 text-xs outline-none transition focus:border-indigo-500 dark:border-slate-700/60 dark:bg-slate-900">
                        <?php foreach (['planificado','en_progreso','en_riesgo','completado'] as $statusOption): ?>
                          <option value="<?= e($statusOption); ?>" <?= $statusOption === $project['status'] ? 'selected' : ''; ?>><?= e(humanize_status($statusOption)); ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button type="submit" class="inline-flex items-center gap-1 rounded-full bg-indigo-600 px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-700">
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
