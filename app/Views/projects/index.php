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
$statusStyles = [
    'planeacion' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-200',
    'en_progreso' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200',
    'en_revision' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
    'finalizado' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200',
];

$statusTotals = [
    'planeacion' => 0,
    'en_progreso' => 0,
    'en_revision' => 0,
    'finalizado' => 0,
];

foreach ($projects as $project) {
    $status = $project['status'] ?? '';
    if (array_key_exists($status, $statusTotals)) {
        $statusTotals[$status] += 1;
    }
}

$projectHighlights = [
    [
        'label' => 'Proyectos en planeacion',
        'value' => $statusTotals['planeacion'],
        'icon' => 'compass',
        'accent' => 'from-sky-400/90 to-sky-500/70 text-sky-900',
    ],
    [
        'label' => 'En progreso activo',
        'value' => $statusTotals['en_progreso'],
        'icon' => 'loader-2',
        'accent' => 'from-indigo-400/90 to-indigo-500/70 text-indigo-900',
    ],
    [
        'label' => 'Pendientes de revision',
        'value' => $statusTotals['en_revision'],
        'icon' => 'clipboard-check',
        'accent' => 'from-amber-300/90 to-amber-400/70 text-amber-900',
    ],
    [
        'label' => 'Finalizados',
        'value' => $statusTotals['finalizado'],
        'icon' => 'medal',
        'accent' => 'from-emerald-300/90 to-emerald-400/70 text-emerald-900',
    ],
];

$roleTips = [
    'director' => 'Monitorea la carga de tus estudiantes, asigna nuevos proyectos y mantente al tanto de los estados.',
    'estudiante' => 'Visualiza el estatus general, identifica tareas pendientes y registra tus avances con tu director.',
    'admin' => 'Administra asignaciones y estados para dar visibilidad al progreso institucional.',
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

<?php if ($projectCount > 0): ?>
  <section class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
    <?php foreach ($projectHighlights as $highlight): ?>
      <article class="relative overflow-hidden rounded-2xl border border-transparent bg-gradient-to-br <?= $highlight['accent']; ?> p-5 shadow-sm">
        <div class="absolute inset-0 bg-white/10 mix-blend-soft-light"></div>
        <div class="relative flex items-start justify-between gap-3">
          <div>
            <p class="text-xs font-medium uppercase tracking-wide/loose text-slate-900/70">
              <?= e($highlight['label']); ?>
            </p>
            <p class="mt-3 text-2xl font-semibold"><?= (int) $highlight['value']; ?></p>
          </div>
          <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-white/80 text-slate-900/70">
            <i data-lucide="<?= e($highlight['icon']); ?>" class="h-5 w-5"></i>
          </span>
        </div>
      </article>
    <?php endforeach; ?>
  </section>
<?php endif; ?>

<section class="mb-5 grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:grid-cols-[1.2fr,1fr]">
  <div>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">Resumen operativo</h2>
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
      <?= e($roleTips[$user['role']] ?? 'Gestiona la cartera de proyectos y mantiene informados a los involucrados.'); ?>
    </p>
    <dl class="mt-4 grid grid-cols-2 gap-3 text-xs text-slate-600 dark:text-slate-300 sm:grid-cols-3">
      <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 dark:border-slate-800 dark:bg-slate-900/60">
        <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Total</dt>
        <dd class="mt-1 text-lg font-semibold text-slate-800 dark:text-slate-100"><?= $projectCount; ?></dd>
      </div>
      <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 dark:border-slate-800 dark:bg-slate-900/60">
        <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">En progreso</dt>
        <dd class="mt-1 text-lg font-semibold text-slate-800 dark:text-slate-100"><?= $statusTotals['en_progreso']; ?></dd>
      </div>
      <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 dark:border-slate-800 dark:bg-slate-900/60">
        <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Finalizados</dt>
        <dd class="mt-1 text-lg font-semibold text-slate-800 dark:text-slate-100"><?= $statusTotals['finalizado']; ?></dd>
      </div>
    </dl>
  </div>
  <div class="space-y-3">
    <div class="flex items-center justify-between text-[11px] font-medium text-slate-500 dark:text-slate-400">
      <span>Filtros rapidos</span>
      <a href="<?= e(url('/projects')); ?>" class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-500">
        <i data-lucide="rotate-ccw" class="h-3.5 w-3.5"></i> Reiniciar
      </a>
    </div>
    <div class="flex flex-wrap gap-2">
      <?php foreach ($statusLabels as $statusKey => $statusLabel): ?>
        <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-medium text-slate-600 shadow-sm dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300">
          <span class="h-2 w-2 rounded-full <?= [
            'planeacion' => 'bg-sky-500',
            'en_progreso' => 'bg-indigo-500',
            'en_revision' => 'bg-amber-500',
            'finalizado' => 'bg-emerald-500',
          ][$statusKey] ?? 'bg-slate-400'; ?>"></span>
          <?= e($statusLabel); ?> (<?= $statusTotals[$statusKey] ?? 0; ?>)
        </span>
      <?php endforeach; ?>
    </div>
  </div>
</section>

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
          <li class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/60 p-4 transition-all duration-300 hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-white hover:shadow-lg dark:border-slate-800 dark:bg-slate-900/60 dark:hover:border-indigo-500/40">
            <span class="absolute inset-y-0 left-0 w-1 bg-gradient-to-b from-indigo-500/80 via-indigo-400/70 to-indigo-300/60 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></span>
            <div class="flex flex-col gap-3 pl-1">
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
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                  <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Estudiante</p>
                  <p class="mt-1 font-medium"><?= e($student['full_name'] ?? 'Sin asignar'); ?></p>
                  <?php if (!empty($student['email'])): ?>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400"><?= e($student['email']); ?></p>
                  <?php endif; ?>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                  <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Director</p>
                  <p class="mt-1 font-medium"><?= e($director['full_name'] ?? 'Sin asignar'); ?></p>
                  <?php if (!empty($director['email'])): ?>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400"><?= e($director['email']); ?></p>
                  <?php endif; ?>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                  <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Estado</p>
                  <span class="mt-1 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold <?= $statusStyles[$project['status']] ?? 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-200'; ?>">
                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                    <?= e($statusLabels[$project['status']] ?? ucfirst($project['status'])); ?>
                  </span>
                </div>
              </div>

              <?php if ($user['role'] === 'director' && (int) $project['director_id'] === (int) $user['id']): ?>
                <form method="post" action="<?= e(url('/projects/status')); ?>" class="flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs shadow-sm dark:border-slate-800 dark:bg-slate-900">
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
      <div class="flex items-start justify-between gap-3">
        <div>
          <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Nuevo proyecto</h2>
          <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"><?= $user['role'] === 'director' ? 'Asigna un nuevo proyecto a un estudiante.' : 'Propone tu proyecto y asigna un director.'; ?></p>
        </div>
        <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-2.5 py-1 text-[11px] font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200">
          <i data-lucide="sparkles" class="h-3.5 w-3.5"></i>
          Paso a paso
        </span>
      </div>

      <form method="post" action="<?= e(url('/projects')); ?>" class="mt-4 space-y-4">
        <div class="space-y-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 p-4 dark:border-slate-700 dark:bg-slate-900/40">
          <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Datos generales</h3>
          <div class="space-y-2">
            <label for="title" class="block text-xs font-semibold text-slate-600 dark:text-slate-300">Titulo</label>
            <input type="text" id="title" name="title" value="<?= e($old['title'] ?? ''); ?>" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-400" required />
            <?php if (!empty($errors['title'])): ?>
              <p class="text-[11px] text-rose-500"><?= e($errors['title']); ?></p>
            <?php endif; ?>
          </div>
          <div class="space-y-2">
            <label for="description" class="block text-xs font-semibold text-slate-600 dark:text-slate-300">Descripcion</label>
            <textarea id="description" name="description" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-400" placeholder="Contexto breve del proyecto"><?= e($old['description'] ?? ''); ?></textarea>
          </div>
        </div>

        <div class="space-y-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 p-4 dark:border-slate-700 dark:bg-slate-900/40">
          <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Asignaciones</h3>
          <?php if ($user['role'] === 'director'): ?>
            <div class="space-y-2">
              <label for="student_id" class="block text-xs font-semibold text-slate-600 dark:text-slate-300">Estudiante</label>
              <select id="student_id" name="student_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-400">
                <option value="">Selecciona estudiante</option>
                <?php foreach ($students as $student): ?>
                  <option value="<?= (int) $student['id']; ?>" <?= isset($old['student_id']) && (int) $old['student_id'] === (int) $student['id'] ? 'selected' : ''; ?>><?= e($student['full_name']); ?> (<?= e($student['email']); ?>)</option>
                <?php endforeach; ?>
              </select>
              <?php if (!empty($errors['student_id'])): ?>
                <p class="text-[11px] text-rose-500"><?= e($errors['student_id']); ?></p>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="space-y-2">
              <label for="director_id" class="block text-xs font-semibold text-slate-600 dark:text-slate-300">Director</label>
              <select id="director_id" name="director_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-400">
                <option value="">Selecciona director</option>
                <?php foreach ($directors as $director): ?>
                  <option value="<?= (int) $director['id']; ?>" <?= isset($old['director_id']) && (int) $old['director_id'] === (int) $director['id'] ? 'selected' : ''; ?>><?= e($director['full_name']); ?> (<?= e($director['email']); ?>)</option>
                <?php endforeach; ?>
              </select>
              <?php if (!empty($errors['director_id'])): ?>
                <p class="text-[11px] text-rose-500"><?= e($errors['director_id']); ?></p>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="space-y-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 p-4 dark:border-slate-700 dark:bg-slate-900/40">
          <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Confirmacion</h3>
          <p class="text-[11px] text-slate-500 dark:text-slate-400">Revisa que la informacion sea correcta antes de enviar. Podras actualizarla desde la lista de proyectos.</p>
          <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">Guardar proyecto</button>
        </div>
      </form>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-900 via-slate-900 to-slate-700 p-5 text-slate-100 shadow-sm dark:border-slate-700">
      <h2 class="text-sm font-semibold">Buenas practicas</h2>
      <p class="mt-1 text-xs text-slate-300">Optimiza la gestion compartiendo contexto y definiendo expectativas claras.</p>
      <ul class="mt-4 space-y-3 text-xs">
        <li class="flex items-start gap-2"><i data-lucide="info" class="mt-0.5 h-4 w-4 flex-none"></i><span>Incluye objetivos medibles en la descripcion para alinear expectativas.</span></li>
        <li class="flex items-start gap-2"><i data-lucide="users" class="mt-0.5 h-4 w-4 flex-none"></i><span>Confirma la disponibilidad del director o estudiante antes de asignar.</span></li>
        <li class="flex items-start gap-2"><i data-lucide="calendar" class="mt-0.5 h-4 w-4 flex-none"></i><span>Define hitos tempranamente para facilitar el seguimiento posterior.</span></li>
      </ul>
    </section>
  </aside>
</div>
<?php
$pageContent = ob_get_clean();
require __DIR__ . '/../layouts/app-shell.php';