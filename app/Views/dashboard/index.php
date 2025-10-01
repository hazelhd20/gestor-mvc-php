<?php
header('Content-Type: text/html; charset=UTF-8');

$fullName = $user['full_name'] ?? 'Usuario';
$role = $user['role'] ?? 'Invitado';
$errors = $errors ?? [];
$success = $success ?? null;
$old = $old ?? [];
$projects = $projects ?? [];
$activeTab = $activeTab ?? 'dashboard';
$selectedProject = $selectedProject ?? null;
$projectMilestones = $projectMilestones ?? [];
$deliverablesByMilestone = $deliverablesByMilestone ?? [];
$feedbackByMilestone = $feedbackByMilestone ?? [];
$stats = $stats ?? ['total' => 0, 'active' => 0, 'completed' => 0, 'due_soon' => 0];
$upcomingMilestones = $upcomingMilestones ?? [];
$recentFeedback = $recentFeedback ?? [];
$boardColumns = $boardColumns ?? [];
$students = $students ?? [];
$userId = (int) ($user['id'] ?? 0);

$projectOld = $old['project'] ?? [];
$milestoneOld = $old['milestone'] ?? [];

if (!function_exists('format_dashboard_date')) {
    function format_dashboard_date(?string $date): string
    {
        if (!$date) {
            return 'Sin fecha';
        }
        try {
            $dt = new DateTimeImmutable($date);
            return $dt->format('d/m/Y');
        } catch (Throwable) {
            return $date;
        }
    }
}

if (!function_exists('humanize_status')) {
    function humanize_status(string $status): string
    {
        return match ($status) {
            'planificado' => 'Planificado',
            'en_progreso' => 'En progreso',
            'en_riesgo' => 'En riesgo',
            'completado' => 'Completado',
            'pendiente' => 'Pendiente',
            'en_revision' => 'En revisión',
            'aprobado' => 'Aprobado',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}

if (!function_exists('status_badge_classes')) {
    function status_badge_classes(string $status): string
    {
        return match ($status) {
            'planificado', 'pendiente' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
            'en_progreso' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-200',
            'en_riesgo' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/60 dark:text-amber-200',
            'en_revision' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-200',
            'aprobado', 'completado' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-200',
            default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
        };
    }
}
?>
<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gestor de Titulación - Panel</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: { sans: ["Inter", "system-ui", "-apple-system", "Segoe UI", "Roboto", "Helvetica", "Arial", "sans-serif"] },
        },
      },
    };
  </script>
  <style>
    .transition-width { transition: width .3s ease; }
    .scrollbar-thin { scrollbar-width: thin; }
    .scrollbar-thin::-webkit-scrollbar { width: 6px; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background-color: rgba(148, 163, 184, .6); border-radius: 9999px; }
  </style>
</head>
<body class="h-full bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
  <header class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-900/80">
    <div class="flex h-14 items-center gap-3 px-3 sm:px-4">
      <button id="btnSidebar" class="inline-flex h-9 w-9 items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Mostrar/ocultar navegación">
        <i data-lucide="menu" class="h-5 w-5"></i>
      </button>

      <div class="flex items-center gap-2 font-semibold">
        <span class="rounded-lg bg-indigo-600 px-2 py-0.5 text-white">GT</span>
        <span class="hidden text-sm sm:inline">Gestor de Titulación</span>
      </div>

      <div class="mx-3 hidden flex-1 items-center sm:flex">
        <div class="relative w-full max-w-xl">
          <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
          <input type="text" placeholder="Buscar proyecto, hito, comentario..." class="w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 py-2 text-sm outline-none placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white dark:border-slate-700 dark:bg-slate-800 dark:focus:border-indigo-400" />
        </div>
      </div>

      <div class="ml-auto flex items-center gap-1 sm:gap-2">
        <button class="relative inline-flex h-9 w-9 items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Notificaciones">
          <i data-lucide="bell" class="h-5 w-5"></i>
          <span class="absolute right-1 top-1 h-2 w-2 rounded-full bg-rose-500"></span>
        </button>

        <div class="inline-flex overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800" role="group" aria-label="Cambiar tema">
          <button id="btnLight" class="flex h-9 w-9 items-center justify-center" title="Claro">
            <i data-lucide="sun" class="h-5 w-5"></i>
          </button>
          <button id="btnDark" class="flex h-9 w-9 items-center justify-center" title="Oscuro">
            <i data-lucide="moon" class="h-5 w-5"></i>
          </button>
        </div>

        <div class="ml-1 flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-2 py-1.5 text-sm dark:border-slate-800 dark:bg-slate-900">
          <img src="https://i.pravatar.cc/100?img=5" alt="avatar" class="h-7 w-7 rounded-full" />
          <div class="hidden leading-tight sm:block">
            <p class="text-xs font-medium"><?= e($fullName); ?></p>
            <p class="text-[10px] text-slate-500 dark:text-slate-400"><?= e(ucfirst($role)); ?></p>
          </div>
        </div>
      </div>
    </div>
  </header>

  <div class="flex w-full gap-0">
    <aside id="sidebar" class="sticky top-14 hidden h-[calc(100vh-3.5rem)] shrink-0 border-r border-slate-200 bg-white p-2 transition-width duration-300 dark:border-slate-800 dark:bg-slate-900 md:block w-64" aria-label="Navegación principal">
      <nav class="flex h-full flex-col">
        <ul id="navList" class="flex-1 space-y-1"></ul>
        <div class="space-y-1 pt-1">
          <button class="side-item"><i data-lucide="settings" class="icon"></i><span class="label">Configuración</span></button>
          <button class="side-item"><i data-lucide="help-circle" class="icon"></i><span class="label">Ayuda</span></button>
          <button class="side-item" data-action="logout"><i data-lucide="log-out" class="icon"></i><span class="label">Salir</span></button>
        </div>
      </nav>
    </aside>

    <main class="min-h-[calc(100vh-3.5rem)] flex-1 p-3 sm:p-6">
      <!-- ENCABEZADO PRINCIPAL (se conserva este y se eliminan encabezados duplicados en secciones) -->
      <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 id="pageTitle" class="text-xl font-semibold sm:text-2xl">Panel</h1>
          <p id="pageSubtitle" class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Resumen general y acciones rápidas</p>
        </div>
        <form method="post" action="<?= e(url('/logout')); ?>" id="logoutForm" class="hidden"></form>
      </div>

      <?php if ($success): ?>
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-100">
          <?= e($success); ?>
        </div>
      <?php endif; ?>

      <?php if ($errors): ?>
        <div class="mb-4 space-y-2">
          <?php foreach ($errors as $error): ?>
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-800 dark:bg-rose-900/40 dark:text-rose-100">
              <?= e($error); ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- DASHBOARD -->
      <section id="section-dashboard" data-section="dashboard" class="space-y-6">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
              <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Proyectos totales</p>
              <i data-lucide="layers" class="h-5 w-5 text-indigo-500"></i>
            </div>
            <p class="mt-4 text-3xl font-semibold"><?= e((string) ($stats['total'] ?? 0)); ?></p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
              <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Activos</p>
              <i data-lucide="activity" class="h-5 w-5 text-emerald-500"></i>
            </div>
            <p class="mt-4 text-3xl font-semibold"><?= e((string) ($stats['active'] ?? 0)); ?></p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
              <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Completados</p>
              <i data-lucide="check-circle" class="h-5 w-5 text-emerald-500"></i>
            </div>
            <p class="mt-4 text-3xl font-semibold"><?= e((string) ($stats['completed'] ?? 0)); ?></p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
              <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Vencen pronto (7 días)</p>
              <i data-lucide="clock" class="h-5 w-5 text-amber-500"></i>
            </div>
            <p class="mt-4 text-3xl font-semibold"><?= e((string) ($stats['due_soon'] ?? 0)); ?></p>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
          <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
              <h2 class="text-base font-semibold">Próximos hitos</h2>
              <span class="text-xs text-slate-500"><?= count($upcomingMilestones); ?> pendientes</span>
            </div>
            <div class="mt-4 space-y-3">
              <?php if ($upcomingMilestones === []): ?>
                <p class="text-sm text-slate-500">Sin hitos programados en los próximos días.</p>
              <?php else: ?>
                <?php foreach ($upcomingMilestones as $item): ?>
                  <div class="rounded-xl border border-slate-200/70 bg-slate-50 px-3 py-3 text-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between gap-3">
                      <div>
                        <p class="font-medium text-slate-800 dark:text-slate-100"><?= e($item['title']); ?></p>
                        <p class="text-xs text-slate-500">Proyecto: <?= e($item['project_title']); ?></p>
                      </div>
                      <span class="rounded-lg bg-indigo-100 px-2 py-1 text-[11px] font-medium text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-200">
                        <?= e(format_dashboard_date($item['due_date'] ?? null)); ?>
                      </span>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
              <h2 class="text-base font-semibold">Feedback reciente</h2>
              <span class="text-xs text-slate-500"><?= count($recentFeedback); ?> registros</span>
            </div>
            <div class="mt-4 space-y-3">
              <?php if ($recentFeedback === []): ?>
                <p class="text-sm text-slate-500">Aún no hay comentarios registrados.</p>
              <?php else: ?>
                <?php foreach ($recentFeedback as $comment): ?>
                  <div class="rounded-xl border border-slate-200/70 px-3 py-3 text-sm dark:border-slate-800">
                    <div class="flex items-center justify-between gap-2">
                      <p class="font-medium text-slate-800 dark:text-slate-100">
                        <?= e($comment['author_name']); ?>
                      </p>
                      <span class="text-[11px] uppercase tracking-wide text-slate-400">Hito: <?= e($comment['milestone_title']); ?></span>
                    </div>
                    <p class="mt-2 text-slate-600 dark:text-slate-300">"<?= e($comment['content']); ?>"</p>
                    <p class="mt-1 text-xs text-slate-400">Proyecto: <?= e($comment['project_title']); ?></p>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </section>

      <!-- PROYECTOS (se elimina encabezado duplicado, se conserva botón) -->
      <section id="section-proyectos" data-section="proyectos" class="mt-8 space-y-6">
        <div class="flex flex-wrap items-center justify-end gap-3">
          <?php if ($role === 'director'): ?>
            <button data-modal="modalProject" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none" type="button">
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
                        <?php $isProjectDirector = $role === 'director' && (int) ($project['director_id'] ?? 0) === $userId; ?>
                        <?php if ($isProjectDirector): ?>
                        <form method="post" action="<?= e(url('/projects/status')); ?>" class="inline-flex items-center gap-1">
                          <input type="hidden" name="project_id" value="<?= e((string) $project['id']); ?>" />
                          <select name="status" class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs outline-none dark:border-slate-700 dark:bg-slate-900">
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

      <!-- HITOS (sin encabezado duplicado) -->
      <section id="section-hitos" data-section="hitos" class="mt-8 space-y-6">
        <?php if (!$selectedProject): ?>
          <div class="rounded-2xl border border-slate-200 bg-white px-5 py-10 text-center text-sm text-slate-500 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            Selecciona un proyecto para gestionar sus hitos.
          </div>
        <?php else: ?>
          <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <div>
                <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100"><?= e($selectedProject['title']); ?></h3>
                <p class="text-xs text-slate-500">Estudiante: <?= e($selectedProject['student_name']); ?> · Director: <?= e($selectedProject['director_name']); ?></p>
              </div>
              <span class="rounded-lg px-3 py-1 text-xs font-semibold <?= e(status_badge_classes($selectedProject['status'])); ?>">Estado: <?= e(humanize_status($selectedProject['status'])); ?></span>
            </div>

            <div class="mt-6 space-y-4">
              <?php if ($projectMilestones === []): ?>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900/40">Aún no hay hitos registrados.</div>
              <?php else: ?>
                <?php foreach ($projectMilestones as $milestone): ?>
                  <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/60">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                      <div>
                        <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100"><?= e($milestone['title']); ?></h4>
                        <p class="mt-1 text-xs text-slate-500"><?= e($milestone['description'] ?: 'Sin descripción.'); ?></p>
                        <p class="mt-2 text-xs text-slate-400">Entrega: <?= e(format_dashboard_date($milestone['due_date'] ?? null)); ?></p>
                      </div>
                      <div class="flex flex-wrap items-center gap-2">
                        <?php
                          $isDirectorOwner = $role === 'director' && (int) ($selectedProject['director_id'] ?? 0) === $userId;
                          $isStudentOwner = $role === 'estudiante' && (int) ($selectedProject['student_id'] ?? 0) === $userId;
                          $milestoneStatusOptions = [];
                          if ($isDirectorOwner) {
                              $milestoneStatusOptions = ['pendiente','en_progreso','en_revision','aprobado'];
                          } elseif ($isStudentOwner && $milestone['status'] !== 'aprobado') {
                              $milestoneStatusOptions = ['pendiente','en_progreso','en_revision'];
                          }
                        ?>
                        <span class="rounded-lg px-2 py-1 text-[11px] font-semibold <?= e(status_badge_classes($milestone['status'])); ?>"><?= e(humanize_status($milestone['status'])); ?></span>
                        <?php if ($milestoneStatusOptions !== []): ?>
                        <form method="post" action="<?= e(url('/milestones/status')); ?>" class="flex items-center gap-1">
                          <input type="hidden" name="milestone_id" value="<?= e((string) $milestone['id']); ?>" />
                          <select name="status" class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs outline-none dark:border-slate-700 dark:bg-slate-900">
                            <?php foreach ($milestoneStatusOptions as $statusOption): ?>
                              <option value="<?= e($statusOption); ?>" <?= $statusOption === $milestone['status'] ? 'selected' : ''; ?>><?= e(humanize_status($statusOption)); ?></option>
                            <?php endforeach; ?>
                          </select>
                          <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-2 py-1 text-xs font-medium text-white hover:bg-indigo-700">
                            <i data-lucide="save" class="h-3.5 w-3.5"></i>
                          </button>
                        </form>
                        <?php endif; ?>
                      </div>
                    </div>

                    <div class="mt-4 grid gap-4 lg:grid-cols-2">
                      <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-xs dark:border-slate-800 dark:bg-slate-900/40">
                        <div class="flex items-center justify-between">
                          <p class="font-semibold text-slate-700 dark:text-slate-200">Entregables</p>
                          <span class="rounded-lg bg-indigo-100 px-2 py-0.5 text-[11px] font-semibold text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-200">
                            <?= e((string) ($milestone['deliverables_count'] ?? 0)); ?>
                          </span>
                        </div>
                        <div class="mt-3 space-y-3 max-h-40 overflow-y-auto pr-1 scrollbar-thin">
                          <?php $deliverables = $deliverablesByMilestone[$milestone['id']] ?? []; ?>
                          <?php if ($deliverables === []): ?>
                            <p class="text-slate-500">Sin entregas aún.</p>
                          <?php else: ?>
                            <?php foreach ($deliverables as $deliverable): ?>
                              <div class="rounded-lg bg-white/80 px-3 py-2 shadow-sm ring-1 ring-slate-200 dark:bg-slate-950/30 dark:ring-slate-800">
                                <p class="text-slate-700 dark:text-slate-200"><?= e($deliverable['original_name']); ?></p>
                                <p class="text-[11px] text-slate-400">Autor: <?= e($deliverable['author_name']); ?></p>
                                <div class="mt-1 flex items-center justify-between text-[11px] text-slate-400">
                                  <span><?= e($deliverable['notes'] ? 'Notas incluidas' : 'Archivo'); ?></span>
                                  <?php if ($deliverable['file_path']): ?>
                                    <a class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2 py-0.5 text-[11px] text-indigo-600 hover:bg-indigo-50 dark:border-slate-700 dark:text-indigo-300 dark:hover:bg-indigo-900/40" href="<?= e(url('/deliverables/download?id=' . (int) $deliverable['id'])); ?>">
                                      <i data-lucide="download" class="h-3 w-3"></i> Descargar
                                    </a>
                                  <?php endif; ?>
                                </div>
                                <?php if ($deliverable['notes']): ?>
                                  <p class="mt-2 rounded-lg bg-slate-100 px-2 py-1 text-slate-600 dark:bg-slate-800 dark:text-slate-200">"<?= e($deliverable['notes']); ?>"</p>
                                <?php endif; ?>
                              </div>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </div>
                        <div class="mt-3">
                          <?php
                            $canUploadDeliverable = $role === 'estudiante'
                                && (int) ($selectedProject['student_id'] ?? 0) === $userId
                                && !in_array($milestone['status'], ['en_revision', 'aprobado'], true);
                          ?>
                          <?php if ($canUploadDeliverable): ?>
                          <form method="post" action="<?= e(url('/deliverables')); ?>" enctype="multipart/form-data" class="space-y-2">
                            <input type="hidden" name="milestone_id" value="<?= e((string) $milestone['id']); ?>" />
                            <label class="block text-[11px] font-semibold uppercase text-slate-500">Subir avance</label>
                            <input type="file" name="file" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs dark:border-slate-700 dark:bg-slate-900" />
                            <textarea name="notes" rows="2" placeholder="Notas complementarias (opcional)" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900"></textarea>
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-xs font-medium text-white hover:bg-indigo-700">
                              <i data-lucide="upload" class="h-3.5 w-3.5"></i> Registrar avance
                            </button>
                          </form>
                          <?php else: ?>
                          <p class="text-[11px] text-slate-500">Solo el estudiante puede registrar avances antes de enviar a revisión o después de aprobación.</p>
                          <?php endif; ?>
                        </div>
                      </div>

                      <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-xs dark:border-slate-800 dark:bg-slate-900/40">
                        <div class="flex items-center justify-between">
                          <p class="font-semibold text-slate-700 dark:text-slate-200">Feedback</p>
                          <span class="rounded-lg bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-200">
                            <?= e((string) ($milestone['feedback_count'] ?? 0)); ?>
                          </span>
                        </div>
                        <div class="mt-3 space-y-3 max-h-40 overflow-y-auto pr-1 scrollbar-thin">
                          <?php $feedbackList = $feedbackByMilestone[$milestone['id']] ?? []; ?>
                          <?php if ($feedbackList === []): ?>
                            <p class="text-slate-500">Sin comentarios aún.</p>
                          <?php else: ?>
                            <?php foreach ($feedbackList as $comment): ?>
                              <div class="rounded-lg bg-white/80 px-3 py-2 shadow-sm ring-1 ring-slate-200 dark:bg-slate-950/30 dark:ring-slate-800">
                                <p class="font-semibold text-slate-700 dark:text-slate-200"><?= e($comment['author_name']); ?></p>
                                <p class="mt-1 text-slate-600 dark:text-slate-300">"<?= e($comment['content']); ?>"</p>
                              </div>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </div>
                        <div class="mt-3">
                          <form method="post" action="<?= e(url('/feedback')); ?>" class="space-y-2">
                            <input type="hidden" name="milestone_id" value="<?= e((string) $milestone['id']); ?>" />
                            <textarea name="content" rows="3" placeholder="Escribe un comentario" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900" required></textarea>
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-medium text-white hover:bg-emerald-700">
                              <i data-lucide="message-circle" class="h-3.5 w-3.5"></i> Enviar feedback
                            </button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </section>

      <!-- COMENTARIOS (sin encabezado duplicado) -->
      <section id="section-comentarios" data-section="comentarios" class="mt-8 space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
          <div class="space-y-4">
            <?php if ($projectMilestones === []): ?>
              <p class="text-sm text-slate-500">Selecciona un proyecto con hitos para ver los comentarios.</p>
            <?php else: ?>
              <?php foreach ($projectMilestones as $milestone): ?>
                <?php $feedbackList = $feedbackByMilestone[$milestone['id']] ?? []; ?>
                <div class="rounded-xl border border-slate-200 bg-white/80 p-4 dark:border-slate-800 dark:bg-slate-900/40">
                  <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Hito: <?= e($milestone['title']); ?></h3>
                    <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-[11px] text-slate-500 dark:bg-slate-800">Comentarios: <?= e((string) count($feedbackList)); ?></span>
                  </div>
                  <div class="mt-3 space-y-3">
                    <?php if ($feedbackList === []): ?>
                      <p class="text-xs text-slate-500">Sin comentarios registrados.</p>
                    <?php else: ?>
                      <?php foreach ($feedbackList as $comment): ?>
                        <div class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900">
                          <div class="flex items-center justify-between text-xs text-slate-400">
                            <span class="font-semibold text-slate-600 dark:text-slate-200"><?= e($comment['author_name']); ?></span>
                            <span><?= e(format_dashboard_date($comment['created_at'] ?? null)); ?></span>
                          </div>
                          <p class="mt-1 text-slate-700 dark:text-slate-200"><?= e($comment['content']); ?></p>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <!-- PROGRESO (sin encabezado duplicado) -->
      <section id="section-progreso" data-section="progreso" class="mt-8 space-y-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
          <?php foreach ([
            'pendiente' => 'Pendiente',
            'en_progreso' => 'En progreso',
            'en_revision' => 'En revisión',
            'aprobado' => 'Aprobado',
          ] as $columnKey => $columnLabel): ?>
            <div class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200"><?= e($columnLabel); ?></h3>
                <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-[11px] text-slate-500 dark:bg-slate-800">
                  <?= e((string) count($boardColumns[$columnKey] ?? [])); ?>
                </span>
              </div>
              <div class="mt-3 flex-1 space-y-3 overflow-y-auto pr-1 scrollbar-thin">
                <?php $items = $boardColumns[$columnKey] ?? []; ?>
                <?php if ($items === []): ?>
                  <p class="text-xs text-slate-500">Sin elementos.</p>
                <?php else: ?>
                  <?php foreach ($items as $item): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs dark:border-slate-800 dark:bg-slate-900/50">
                      <p class="font-semibold text-slate-700 dark:text-slate-100"><?= e($item['title']); ?></p>
                      <p class="mt-1 text-[11px] text-slate-500">Proyecto: <?= e($item['project_title']); ?></p>
                      <p class="mt-2 text-[11px] text-slate-400">Entrega: <?= e(format_dashboard_date($item['due_date'] ?? null)); ?></p>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    </main>
  </div>

  <?php if ($role === 'director'): ?>
    <div id="modalProject" class="modal hidden">
      <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-3 py-6">
        <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-700 dark:bg-slate-900">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">Nuevo proyecto</h2>
            <button data-modal-close class="rounded-full p-1 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
              <i data-lucide="x" class="h-5 w-5"></i>
            </button>
          </div>
          <form method="post" action="<?= e(url('/projects')); ?>" class="mt-5 space-y-3">
            <div>
              <label class="text-xs font-semibold uppercase text-slate-500">Título</label>
              <input type="text" name="title" value="<?= e((string) ($projectOld['title'] ?? '')); ?>" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900" required />
            </div>
            <div>
              <label class="text-xs font-semibold uppercase text-slate-500">Descripción</label>
              <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900" placeholder="Breve resumen del proyecto"><?= e((string) ($projectOld['description'] ?? '')); ?></textarea>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
              <div>
                <label class="text-xs font-semibold uppercase text-slate-500">Estudiante</label>
                <select name="student_id" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900" required>
                  <option value="">Selecciona una opción</option>
                  <?php foreach ($students as $student): ?>
                    <option value="<?= e((string) $student['id']); ?>" <?= (($projectOld['student_id'] ?? '') == $student['id']) ? 'selected' : ''; ?>><?= e($student['full_name']); ?> (<?= e($student['email']); ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label class="text-xs font-semibold uppercase text-slate-500">Fecha estimada de entrega</label>
                <input type="date" name="due_date" value="<?= e((string) ($projectOld['due_date'] ?? '')); ?>" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900" />
              </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
              <button type="button" data-modal-close class="rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Cancelar</button>
              <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Crear proyecto</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($role === 'director' && $selectedProject): ?>
    <div id="modalMilestone" class="modal hidden">
      <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-3 py-6">
        <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-700 dark:bg-slate-900">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">Nuevo hito</h2>
            <button data-modal-close class="rounded-full p-1 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
              <i data-lucide="x" class="h-5 w-5"></i>
            </button>
          </div>
          <form method="post" action="<?= e(url('/milestones')); ?>" class="mt-5 space-y-3">
            <input type="hidden" name="project_id" value="<?= e((string) $selectedProject['id']); ?>" />
            <div>
              <label class="text-xs font-semibold uppercase text-slate-500">Título</label>
              <input type="text" name="title" value="<?= e((string) ($milestoneOld['title'] ?? '')); ?>" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900" required />
            </div>
            <div>
              <label class="text-xs font-semibold uppercase text-slate-500">Descripción</label>
              <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900" placeholder="Detalles del entregable"><?= e((string) ($milestoneOld['description'] ?? '')); ?></textarea>
            </div>
            <div>
              <label class="text-xs font-semibold uppercase text-slate-500">Fecha límite</label>
              <input type="date" name="due_date" value="<?= e((string) ($milestoneOld['due_date'] ?? '')); ?>" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900" />
            </div>
            <div class="flex justify-end gap-2 pt-2">
              <button type="button" data-modal-close class="rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Cancelar</button>
              <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Crear hito</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <script>
    const NAV_ITEMS = [
      { id: 'dashboard', label: 'Dashboard', icon: 'layout-dashboard' },
      { id: 'proyectos', label: 'Gestión de proyectos', icon: 'folder-kanban' },
      { id: 'hitos', label: 'Hitos y entregables', icon: 'flag' },
      { id: 'comentarios', label: 'Comentarios / Feedback', icon: 'message-square' },
      { id: 'progreso', label: 'Visualización de progreso', icon: 'trending-up' },
    ];

    const TITLES = {
      dashboard: ['Resumen', 'Estado general, próximos vencimientos y actividad reciente'],
      proyectos: ['Gestión de proyectos', 'Crear, editar y asignar proyectos de titulación'],
      hitos: ['Hitos y entregables', 'Define fechas límite, sube avances y valida entregables'],
      comentarios: ['Comentarios y feedback', 'Comunicación entre estudiante y director'],
      progreso: ['Visualización de progreso', 'Kanban simplificado para seguimiento visual'],
    };

    const navList = document.getElementById('navList');
    const sections = document.querySelectorAll('[data-section]');
    let active = <?= json_encode($activeTab); ?>;

    function updateSections() {
      sections.forEach(section => {
        section.classList.toggle('hidden', section.dataset.section !== active);
      });
      const [title, subtitle] = TITLES[active] || ['', ''];
      document.getElementById('pageTitle').textContent = title;
      document.getElementById('pageSubtitle').textContent = subtitle;
    }

    function renderNav() {
      navList.innerHTML = '';
      NAV_ITEMS.forEach(item => {
        const li = document.createElement('li');
        const btn = document.createElement('button');
        btn.className = 'group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-[13px] font-medium tracking-tight transition';
        if (item.id === active) {
          btn.classList.add('bg-indigo-600', 'text-white', 'shadow-sm');
        } else {
          btn.classList.add('text-slate-600', 'hover:bg-slate-100', 'dark:text-slate-300', 'dark:hover:bg-slate-800');
        }
        btn.addEventListener('click', () => {
          active = item.id;
          renderNav();
          updateSections();
        });

        const icon = document.createElement('i');
        icon.setAttribute('data-lucide', item.icon);
        icon.className = 'h-5 w-5 flex-none';

        const label = document.createElement('span');
        label.textContent = item.label;
        label.className = 'label';

        btn.append(icon, label);
        li.append(btn);
        navList.append(li);
      });
      lucide.createIcons();
    }

    const sidebar = document.getElementById('sidebar');
    const btnSidebar = document.getElementById('btnSidebar');
    let sidebarOpen = true;

    btnSidebar.addEventListener('click', () => {
      sidebarOpen = !sidebarOpen;
      sidebar.style.width = sidebarOpen ? '16rem' : '5rem';
      sidebar.querySelectorAll('.label').forEach(el => {
        el.style.display = sidebarOpen ? 'inline' : 'none';
      });
    });

    const root = document.documentElement;
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      root.classList.add('dark');
    }

    document.getElementById('btnLight').addEventListener('click', () => {
      root.classList.remove('dark');
      localStorage.setItem('theme', 'light');
      lucide.createIcons();
    });
    document.getElementById('btnDark').addEventListener('click', () => {
      root.classList.add('dark');
      localStorage.setItem('theme', 'dark');
      lucide.createIcons();
    });

    const logoutBtn = document.querySelector('[data-action="logout"]');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', () => {
        document.getElementById('logoutForm').submit();
      });
    }

    document.querySelectorAll('.side-item').forEach(btn => {
      btn.classList.add('flex', 'w-full', 'items-center', 'gap-3', 'rounded-xl', 'px-3', 'py-2.5', 'text-left', 'text-[13px]', 'text-slate-600', 'hover:bg-slate-100', 'dark:text-slate-300', 'dark:hover:bg-slate-800');
      const icon = btn.querySelector('.icon');
      if (icon) {
        icon.classList.add('h-5', 'w-5', 'flex-none');
      }
    });

    document.querySelectorAll('[data-modal]').forEach(trigger => {
      trigger.addEventListener('click', () => {
        const target = document.getElementById(trigger.dataset.modal);
        if (target) target.classList.remove('hidden');
      });
    });

    document.querySelectorAll('[data-modal-close]').forEach(btn => {
      btn.addEventListener('click', () => {
        btn.closest('.modal').classList.add('hidden');
      });
    });

    document.querySelectorAll('.modal').forEach(modal => {
      modal.addEventListener('click', event => {
        if (event.target === modal.firstElementChild) {
          return;
        }
        if (event.target === modal) {
          modal.classList.add('hidden');
        }
      });
    });

    renderNav();
    updateSections();
    lucide.createIcons();
  </script>
</body>
</html>
