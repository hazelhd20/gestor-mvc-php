<?php
$pageTitle = 'Gestion de proyectos';
$pageSubtitle = 'Crear, asignar y monitorear proyectos de titulacion';
$activeNav = 'proyectos';
$statusMessage = $statusMessage ?? null;
$errors = $errors ?? [];
$old = $old ?? [];
$projects = $projects ?? [];
$students = $students ?? [];
$directors = $directors ?? [];
$peopleMap = $peopleMap ?? [];

$projectCount = count($projects);
$statusLabels = [
    'planeacion' => 'Planeacion',
    'en_progreso' => 'En progreso',
    'en_revision' => 'En revision',
    'finalizado' => 'Finalizado',
];

ob_start();
?>
<?php if ($statusMessage): ?>
  <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
    <?= e($statusMessage); ?>
  </div>
<?php endif; ?>

<?php if (!empty($errors['general'])): ?>
  <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
    <?= e($errors['general']); ?>
  </div>
<?php endif; ?>

<div class="grid grid-cols-1 gap-4 xl:grid-cols-[2fr,1fr]">
  <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <header class="mb-4 flex items-center justify-between">
      <div>
        <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Tus proyectos</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400">Listado filtrado de acuerdo a tu rol y asignaciones.</p>
      </div>
      <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200"><?= $projectCount; ?> registrados</span>
    </header>

    <?php if ($projectCount === 0): ?>
      <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50">
        Aun no tienes proyectos registrados.
      </div>
    <?php else: ?>
      <ul class="space-y-3">
        <?php foreach ($projects as $project): ?>
          <?php
            $student = $peopleMap[(int) $project['student_id']] ?? null;
            $director = $peopleMap[(int) $project['director_id']] ?? null;
          ?>
          <li class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 transition hover:border-indigo-200 hover:bg-white dark:border-slate-800 dark:bg-slate-900/60 dark:hover:border-indigo-500/40">
            <div class="flex flex-col gap-3">
              <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100"><?= e($project['title']); ?></h3>
                  <?php if (!empty($project['description'])): ?>
                    <p class="mt-1 max-w-xl text-sm text-slate-500 dark:text-slate-400"><?= e($project['description']); ?></p>
                  <?php endif; ?>
                </div>
                <div class="text-right text-xs text-slate-500 dark:text-slate-400">
                  <p class="uppercase tracking-wide text-[10px] text-slate-400">Creado</p>
                  <p><?= e(substr($project['created_at'], 0, 10)); ?></p>
                </div>
              </div>

              <div class="grid grid-cols-1 gap-2 text-xs text-slate-600 dark:text-slate-300 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-800 dark:bg-slate-900">
                  <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Estudiante</p>
                  <p class="mt-1 font-medium"><?= e($student['full_name'] ?? 'Sin asignar'); ?></p>
                  <?php if (!empty($student['email'])): ?>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400"><?= e($student['email']); ?></p>
                  <?php endif; ?>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-800 dark:bg-slate-900">
                  <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Director</p>
                  <p class="mt-1 font-medium"><?= e($director['full_name'] ?? 'Sin asignar'); ?></p>
                  <?php if (!empty($director['email'])): ?>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400"><?= e($director['email']); ?></p>
                  <?php endif; ?>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-800 dark:bg-slate-900">
                  <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Estado</p>
                  <p class="mt-1 inline-flex items-center gap-2 rounded-full bg-slate-200 px-2 py-0.5 text-[11px] font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <?= e($statusLabels[$project['status']] ?? ucfirst($project['status'])); ?>
                  </p>
                </div>
              </div>

              <?php if ($user['role'] === 'director' && (int) $project['director_id'] === (int) $user['id']): ?>
                <form method="post" action="<?= e(url('/projects/status')); ?>" class="flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs dark:border-slate-800 dark:bg-slate-900">
                  <input type="hidden" name="project_id" value="<?= (int) $project['id']; ?>" />
                  <label for="status-<?= (int) $project['id']; ?>" class="font-semibold text-slate-600 dark:text-slate-300">Actualizar estado</label>
                  <select id="status-<?= (int) $project['id']; ?>" name="status" class="rounded-lg border border-slate-200 bg-white px-2 py-1 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-400">
                    <?php foreach ($statusLabels as $value => $label): ?>
                      <option value="<?= $value; ?>" <?= $project['status'] === $value ? 'selected' : ''; ?>><?= $label; ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1 font-semibold text-white shadow-sm transition hover:bg-indigo-500">Guardar</button>
                </form>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>

  <aside class="space-y-4">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Nuevo proyecto</h2>
      <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"><?= $user['role'] === 'director' ? 'Asigna un nuevo proyecto a un estudiante.' : 'Propone tu proyecto y asigna un director.'; ?></p>

      <form method="post" action="<?= e(url('/projects')); ?>" class="mt-4 space-y-3">
        <div>
          <label for="title" class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Titulo</label>
          <input type="text" id="title" name="title" value="<?= e($old['title'] ?? ''); ?>" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-400" required />
          <?php if (!empty($errors['title'])): ?>
            <p class="mt-1 text-[11px] text-rose-500"><?= e($errors['title']); ?></p>
          <?php endif; ?>
        </div>

        <div>
          <label for="description" class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Descripcion</label>
          <textarea id="description" name="description" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-400" placeholder="Contexto breve del proyecto"><?= e($old['description'] ?? ''); ?></textarea>
        </div>

        <?php if ($user['role'] === 'director'): ?>
          <div>
            <label for="student_id" class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Estudiante</label>
            <select id="student_id" name="student_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-400">
              <option value="">Selecciona estudiante</option>
              <?php foreach ($students as $student): ?>
                <option value="<?= (int) $student['id']; ?>" <?= isset($old['student_id']) && (int) $old['student_id'] === (int) $student['id'] ? 'selected' : ''; ?>><?= e($student['full_name']); ?> (<?= e($student['email']); ?>)</option>
              <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['student_id'])): ?>
              <p class="mt-1 text-[11px] text-rose-500"><?= e($errors['student_id']); ?></p>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div>
            <label for="director_id" class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Director</label>
            <select id="director_id" name="director_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-400">
              <option value="">Selecciona director</option>
              <?php foreach ($directors as $director): ?>
                <option value="<?= (int) $director['id']; ?>" <?= isset($old['director_id']) && (int) $old['director_id'] === (int) $director['id'] ? 'selected' : ''; ?>><?= e($director['full_name']); ?> (<?= e($director['email']); ?>)</option>
              <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['director_id'])): ?>
              <p class="mt-1 text-[11px] text-rose-500"><?= e($errors['director_id']); ?></p>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <div class="pt-2">
          <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">Guardar proyecto</button>
        </div>
      </form>
    </section>
  </aside>
</div>
<?php
$pageContent = ob_get_clean();
require __DIR__ . '/../layouts/app-shell.php';