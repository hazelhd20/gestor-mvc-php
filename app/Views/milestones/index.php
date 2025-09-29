<?php
$pageTitle = 'Hitos y entregables';
$pageSubtitle = 'Define fechas limite, sube avances y valida entregables';
$activeNav = 'hitos';
$statusMessage = $statusMessage ?? null;
$errors = $errors ?? [];
$old = $old ?? [];
$projects = $projects ?? [];
$selectedProject = $selectedProject ?? null;
$milestones = $milestones ?? [];
$submissionsByMilestone = $submissionsByMilestone ?? [];
$usersMap = $usersMap ?? [];
$feedbackFlash = $feedbackFlash ?? [];

ob_start();
?>
<?php if ($statusMessage): ?>
  <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
    <?= e($statusMessage); ?>
  </div>
<?php endif; ?>

<?php if ($projects === []): ?>
  <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 p-10 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/40">
    Aun no tienes proyectos disponibles. Crea uno primero para poder definir hitos.
  </div>
<?php else: ?>
  <div class="grid grid-cols-1 gap-4 xl:grid-cols-[2fr,1fr]">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <header class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
          <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Hitos del proyecto</h2>
          <p class="text-xs text-slate-500 dark:text-slate-400">Gestiona entregables y estados.</p>
        </div>
        <form method="get" class="flex items-center gap-2">
          <label for="project" class="text-xs font-medium text-slate-500 dark:text-slate-400">Proyecto</label>
          <select id="project" name="project" class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-400" onchange="this.form.submit()">
            <?php foreach ($projects as $project): ?>
              <option value="<?= (int) $project['id']; ?>" <?= (int) ($selectedProject['id'] ?? 0) === (int) $project['id'] ? 'selected' : ''; ?>><?= e($project['title']); ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      </header>

      <?php if ($milestones === []): ?>
        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50">
          Aun no hay hitos registrados para este proyecto.
        </div>
      <?php else: ?>
        <div id="feedback"></div>
        <ul class="space-y-4">
          <?php foreach ($milestones as $milestone): ?>
            <?php
              $milestoneId = (int) $milestone['id'];
              $submissions = $submissionsByMilestone[$milestoneId] ?? [];
              $latestSubmission = $submissions[0] ?? null;
              $olderSubmissions = array_slice($submissions, 1);

              $isSubmissionError = ($feedbackFlash['type'] ?? '') === 'submission' && (int) ($feedbackFlash['target'] ?? 0) === $milestoneId;
              $submissionErrors = $isSubmissionError ? ($feedbackFlash['errors'] ?? []) : [];
              $submissionOld = $isSubmissionError ? ($feedbackFlash['old'] ?? []) : [];

              $isCommentError = ($feedbackFlash['type'] ?? '') === 'comment' && (int) ($feedbackFlash['target'] ?? 0) === $milestoneId;
              $commentErrors = $isCommentError ? ($feedbackFlash['errors'] ?? []) : [];
              $commentOld = $isCommentError ? ($feedbackFlash['old'] ?? []) : [];
            ?>
            <li id="milestone-<?= $milestoneId; ?>" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
              <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100"><?= e($milestone['title']); ?></h3>
                  <?php if (!empty($milestone['description'])): ?>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"><?= e($milestone['description']); ?></p>
                  <?php endif; ?>
                  <dl class="mt-2 flex flex-wrap gap-4 text-[11px] text-slate-500 dark:text-slate-400">
                    <div>
                      <dt class="uppercase tracking-wide text-[10px] text-slate-400">Estado</dt>
                      <dd class="mt-0.5 inline-flex items-center gap-2 rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-medium text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-200">
                        <?= e(ucwords(str_replace('_', ' ', $milestone['status']))); ?>
                      </dd>
                    </div>
                    <?php if (!empty($milestone['due_date'])): ?>
                      <div>
                        <dt class="uppercase tracking-wide text-[10px] text-slate-400">Entrega</dt>
                        <dd class="mt-0.5"><?= e($milestone['due_date']); ?></dd>
                      </div>
                    <?php endif; ?>
                  </dl>
                </div>

                <?php if ($user['role'] === 'director' && (int) ($selectedProject['director_id'] ?? 0) === (int) $user['id']): ?>
                  <form method="post" action="<?= e(url('/milestones/status')); ?>" class="flex items-center gap-2 text-xs">
                    <input type="hidden" name="milestone_id" value="<?= $milestoneId; ?>" />
                    <input type="hidden" name="project_id" value="<?= (int) ($selectedProject['id'] ?? 0); ?>" />
                    <select name="status" class="rounded-lg border border-slate-200 bg-white px-2 py-1 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-400">
                      <?php foreach (['pendiente' => 'Pendiente', 'en_progreso' => 'En progreso', 'en_revision' => 'En revision', 'completado' => 'Completado'] as $value => $label): ?>
                        <option value="<?= $value; ?>" <?= $milestone['status'] === $value ? 'selected' : ''; ?>><?= $label; ?></option>
                      <?php endforeach; ?>
                    </select>
                    <button type="submit" class="rounded-lg bg-slate-900 px-3 py-1.5 font-semibold text-white shadow-sm transition hover:bg-slate-700 dark:bg-indigo-600 dark:hover:bg-indigo-500">Actualizar</button>
                  </form>
                <?php endif; ?>
              </div>

              <div class="mt-4 space-y-3">
                <?php if ($latestSubmission): ?>
                  <?php $author = $usersMap[(int) $latestSubmission['user_id']] ?? null; ?>
                  <article class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-900/70">
                    <div class="flex items-start justify-between gap-3">
                      <div>
                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Entrega reciente</h4>
                        <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                          <?= e($author['full_name'] ?? 'Participante'); ?> &bull; <?= e(substr($latestSubmission['created_at'], 0, 16)); ?>
                        </p>
                        <?php if (!empty($latestSubmission['notes'])): ?>
                          <p class="mt-2 text-sm text-slate-600 dark:text-slate-300"><?= nl2br(e($latestSubmission['notes'])); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($latestSubmission['attachment_path'])): ?>
                          <a href="<?= e(url('/submissions/download?id=' . (int) $latestSubmission['id'])); ?>" class="mt-2 inline-flex items-center gap-2 text-xs font-semibold text-indigo-600 hover:text-indigo-500">
                            <i data-lucide="paperclip" class="h-4 w-4"></i> Descargar archivo
                          </a>
                        <?php endif; ?>
                      </div>
                    </div>

                    <?php $comments = $latestSubmission['comments'] ?? []; ?>
                    <?php if ($comments): ?>
                      <ul class="mt-3 space-y-2 rounded-xl border border-slate-200 bg-white p-3 text-sm dark:border-slate-800 dark:bg-slate-900">
                        <?php foreach ($comments as $comment): ?>
                          <?php $commentAuthor = $usersMap[(int) $comment['user_id']] ?? null; ?>
                          <li class="flex flex-col gap-1">
                            <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                              <span class="font-semibold text-slate-600 dark:text-slate-200"><?= e($commentAuthor['full_name'] ?? 'Usuario'); ?></span>
                              <span><?= e(substr($comment['created_at'], 0, 16)); ?></span>
                            </div>
                            <p class="text-sm text-slate-600 dark:text-slate-200"><?= nl2br(e($comment['message'])); ?></p>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>
                  </article>
                <?php else: ?>
                  <p class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/60">Sin entregas registradas.</p>
                <?php endif; ?>

                <?php if ($olderSubmissions): ?>
                  <details class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-900">
                    <summary class="cursor-pointer font-semibold text-slate-700 dark:text-slate-200">Historial de entregas (<?= count($submissions); ?>)</summary>
                    <ul class="mt-3 space-y-3">
                      <?php foreach ($olderSubmissions as $submission): ?>
                        <?php $submissionAuthor = $usersMap[(int) $submission['user_id']] ?? null; ?>
                        <li class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs dark:border-slate-800 dark:bg-slate-900/80">
                          <div class="flex items-center justify-between">
                            <span class="font-semibold text-slate-600 dark:text-slate-200"><?= e($submissionAuthor['full_name'] ?? 'Usuario'); ?></span>
                            <span><?= e(substr($submission['created_at'], 0, 16)); ?></span>
                          </div>
                          <?php if (!empty($submission['notes'])): ?>
                            <p class="mt-1 text-slate-500 dark:text-slate-400"><?= nl2br(e($submission['notes'])); ?></p>
                          <?php endif; ?>
                          <?php if (!empty($submission['attachment_path'])): ?>
                            <a href="<?= e(url('/submissions/download?id=' . (int) $submission['id'])); ?>" class="mt-1 inline-flex items-center gap-2 text-xs font-semibold text-indigo-600 hover:text-indigo-500">
                              <i data-lucide="paperclip" class="h-4 w-4"></i> Descargar archivo
                            </a>
                          <?php endif; ?>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  </details>
                <?php endif; ?>

                <?php if ($user['role'] === 'estudiante'): ?>
                  <form method="post" action="<?= e(url('/submissions')); ?>" enctype="multipart/form-data" class="rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <input type="hidden" name="milestone_id" value="<?= $milestoneId; ?>" />
                    <input type="hidden" name="project_id" value="<?= (int) ($selectedProject['id'] ?? 0); ?>" />
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Enviar avance</h4>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Comparte notas y adjunta tu entregable.</p>

                    <div class="mt-3 space-y-2">
                      <label for="notes-<?= $milestoneId; ?>" class="block text-xs font-semibold text-slate-600 dark:text-slate-300">Notas</label>
                      <textarea id="notes-<?= $milestoneId; ?>" name="notes" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-400" placeholder="Describe el entregable o inquietudes"><?= e($submissionOld['notes'] ?? ''); ?></textarea>
                      <?php if (!empty($submissionErrors['notes'])): ?>
                        <p class="text-[11px] text-rose-500"><?= e($submissionErrors['notes']); ?></p>
                      <?php endif; ?>
                    </div>

                    <div class="mt-3 space-y-2">
                      <label for="attachment-<?= $milestoneId; ?>" class="block text-xs font-semibold text-slate-600 dark:text-slate-300">Archivo (opcional)</label>
                      <input type="file" id="attachment-<?= $milestoneId; ?>" name="attachment" class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-indigo-500" />
                      <?php if (!empty($submissionErrors['attachment'])): ?>
                        <p class="text-[11px] text-rose-500"><?= e($submissionErrors['attachment']); ?></p>
                      <?php endif; ?>
                    </div>

                    <?php if (!empty($submissionErrors['submission'])): ?>
                      <p class="mt-2 text-[11px] text-rose-500"><?= e($submissionErrors['submission']); ?></p>
                    <?php endif; ?>

                    <div class="mt-4 text-right">
                      <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">Enviar avance</button>
                    </div>
                  </form>
                <?php endif; ?>

                <?php if ($latestSubmission): ?>
                  <form method="post" action="<?= e(url('/comments')); ?>" class="rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <input type="hidden" name="submission_id" value="<?= (int) $latestSubmission['id']; ?>" />
                    <input type="hidden" name="project_id" value="<?= (int) ($selectedProject['id'] ?? 0); ?>" />
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200"><?= $user['role'] === 'director' ? 'Dejar feedback' : 'Agregar comentario'; ?></h4>
                    <div class="mt-2">
                      <label for="comment-<?= $milestoneId; ?>" class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Comentario</label>
                      <textarea id="comment-<?= $milestoneId; ?>" name="message" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-400" placeholder="Comparte retroalimentacion o dudas"><?= e($commentOld['message'] ?? ''); ?></textarea>
                      <?php if (!empty($commentErrors['message'])): ?>
                        <p class="mt-1 text-[11px] text-rose-500"><?= e($commentErrors['message']); ?></p>
                      <?php endif; ?>
                      <?php if (!empty($commentErrors['comment'])): ?>
                        <p class="mt-1 text-[11px] text-rose-500"><?= e($commentErrors['comment']); ?></p>
                      <?php endif; ?>
                    </div>
                    <div class="mt-4 text-right">
                      <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700 dark:bg-indigo-600 dark:hover:bg-indigo-500">Publicar</button>
                    </div>
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
        <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Registrar nuevo hito</h2>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Define tareas y fechas clave para el proyecto actual.</p>

        <form method="post" action="<?= e(url('/milestones')); ?>" class="mt-4 space-y-3">
          <input type="hidden" name="project_id" value="<?= (int) ($selectedProject['id'] ?? 0); ?>" />

          <div>
            <label for="title" class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Titulo</label>
            <input type="text" id="title" name="title" value="<?= e($old['title'] ?? ''); ?>" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-400" required />
            <?php if (!empty($errors['title'])): ?>
              <p class="mt-1 text-[11px] text-rose-500"><?= e($errors['title']); ?></p>
            <?php endif; ?>
          </div>

          <div>
            <label for="description" class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Descripcion</label>
            <textarea id="description" name="description" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-400" placeholder="Describe el entregable o criterio de avance"><?= e($old['description'] ?? ''); ?></textarea>
          </div>

          <div>
            <label for="due_date" class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Fecha de entrega</label>
            <input type="date" id="due_date" name="due_date" value="<?= e($old['due_date'] ?? ''); ?>" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-400" />
            <?php if (!empty($errors['due_date'])): ?>
              <p class="mt-1 text-[11px] text-rose-500"><?= e($errors['due_date']); ?></p>
            <?php endif; ?>
          </div>

          <?php if (!empty($errors['project_id'])): ?>
            <p class="text-[11px] text-rose-500"><?= e($errors['project_id']); ?></p>
          <?php endif; ?>

          <div class="pt-2">
            <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">Agregar hito</button>
          </div>
        </form>
      </section>
    </aside>
  </div>
<?php endif; ?>
<?php
$pageContent = ob_get_clean();
require __DIR__ . '/../layouts/app-shell.php';