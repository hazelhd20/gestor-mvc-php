<?php
$pageTitle = 'Comentarios y feedback';
$pageSubtitle = 'Centraliza la comunicación entre estudiantes y director';
$activeNav = 'comentarios';
$statusMessage = $statusMessage ?? null;
$projects = $projects ?? [];
$selectedProject = $selectedProject ?? null;
$milestones = $milestones ?? [];
$selectedMilestone = $selectedMilestone ?? null;
$latestSubmission = $latestSubmission ?? null;
$olderSubmissions = $olderSubmissions ?? [];
$milestoneComments = $milestoneComments ?? [];
$commentTotals = $commentTotals ?? [];
$usersMap = $usersMap ?? [];
$feedbackFlash = $feedbackFlash ?? [];

$selectedMilestoneId = $selectedMilestone ? (int) $selectedMilestone['id'] : 0;
$selectedProjectId = $selectedProject ? (int) $selectedProject['id'] : 0;
$currentRedirect = '/feedback?project=' . $selectedProjectId;
if ($selectedMilestoneId > 0) {
    $currentRedirect .= '&milestone=' . $selectedMilestoneId;
}
$currentRedirect .= '#comentarios';

$isCommentError = ($feedbackFlash['type'] ?? '') === 'comment' && (int) ($feedbackFlash['target'] ?? 0) === $selectedMilestoneId;
$commentErrors = $isCommentError ? ($feedbackFlash['errors'] ?? []) : [];
$commentOld = $isCommentError ? ($feedbackFlash['old'] ?? []) : [];
$commentScopeOld = $commentOld['thread_scope'] ?? 'milestone';
$generalOldMessage = $commentScopeOld === 'milestone' ? ($commentOld['message'] ?? '') : '';
$submissionOldMessage = $commentScopeOld === 'submission' ? ($commentOld['message'] ?? '') : '';
$redirectOld = $commentOld['redirect_to'] ?? $currentRedirect;

ob_start();
?>
<?php if ($statusMessage): ?>
  <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
    <?= e($statusMessage); ?>
  </div>
<?php endif; ?>

<?php if ($projects === []): ?>
  <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 p-10 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/40">
    Aun no tienes proyectos disponibles. Crea uno primero para poder compartir comentarios.
  </div>
<?php else: ?>
  <div class="grid grid-cols-1 gap-4 xl:grid-cols-[1fr,2fr]">
    <aside class="space-y-4">
      <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <header class="mb-4">
          <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Selecciona un proyecto</h2>
          <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Explora los hitos y conversaciones asociadas.</p>
        </header>
        <form method="get" class="space-y-3">
          <div>
            <label for="project" class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Proyecto</label>
            <select id="project" name="project" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-400" onchange="this.form.submit()">
              <?php foreach ($projects as $project): ?>
                <option value="<?= (int) $project['id']; ?>" <?= $selectedProjectId === (int) $project['id'] ? 'selected' : ''; ?>><?= e($project['title']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php if ($selectedMilestoneId): ?>
            <input type="hidden" name="milestone" value="<?= $selectedMilestoneId; ?>" />
          <?php endif; ?>
        </form>
      </section>

      <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <header class="mb-4 flex items-center justify-between">
          <div>
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Hitos del proyecto</h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Accede al hilo completo de cada etapa.</p>
          </div>
          <?php if ($selectedProject): ?>
            <a href="<?= e(url('/milestones?project=' . $selectedProjectId)); ?>" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
              <i data-lucide="flag" class="h-3.5 w-3.5"></i>
              Gestionar hitos
            </a>
          <?php endif; ?>
        </header>

        <?php if ($milestones === []): ?>
          <p class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/60">Todavía no hay hitos registrados para este proyecto.</p>
        <?php else: ?>
          <ul class="space-y-2">
            <?php foreach ($milestones as $milestone): ?>
              <?php
                $milestoneId = (int) $milestone['id'];
                $isActive = $milestoneId === $selectedMilestoneId;
                $totalComments = $commentTotals[$milestoneId] ?? 0;
              ?>
              <li>
                <a href="<?= e(url('/feedback?project=' . $selectedProjectId . '&milestone=' . $milestoneId . '#comentarios')); ?>" class="flex items-center justify-between rounded-xl border px-4 py-3 text-left text-sm transition <?= $isActive ? 'border-indigo-500 bg-indigo-50 text-indigo-700 dark:border-indigo-500 dark:bg-indigo-500/20 dark:text-indigo-200' : 'border-slate-200 bg-white text-slate-600 hover:border-indigo-200 hover:bg-indigo-50/70 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-indigo-500/60 dark:hover:bg-indigo-500/10'; ?>">
                  <div>
                    <p class="font-semibold text-slate-700 dark:text-slate-100"><?= e($milestone['title']); ?></p>
                    <?php if (!empty($milestone['due_date'])): ?>
                      <p class="text-[11px] text-slate-500 dark:text-slate-400">Entrega <?= e($milestone['due_date']); ?></p>
                    <?php endif; ?>
                  </div>
                  <div class="flex items-center gap-2 text-[11px] font-medium">
                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-slate-600 dark:bg-slate-800 dark:text-slate-200">
                      <i data-lucide="messages-square" class="h-3.5 w-3.5"></i>
                      <?= $totalComments; ?>
                    </span>
                    <span class="rounded-full bg-slate-900 px-2 py-0.5 text-white dark:bg-indigo-600 dark:text-indigo-100"><?= e(ucwords(str_replace('_', ' ', $milestone['status']))); ?></span>
                  </div>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>
    </aside>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <?php if (!$selectedMilestone): ?>
        <p class="text-sm text-slate-500 dark:text-slate-400">Selecciona un hito para ver sus conversaciones.</p>
      <?php else: ?>
        <header class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100"><?= e($selectedMilestone['title']); ?></h2>
            <?php if (!empty($selectedMilestone['description'])): ?>
              <p class="mt-1 text-sm text-slate-600 dark:text-slate-300"><?= e($selectedMilestone['description']); ?></p>
            <?php endif; ?>
            <dl class="mt-3 flex flex-wrap gap-4 text-[11px] text-slate-500 dark:text-slate-400">
              <div>
                <dt class="uppercase tracking-wide text-[10px] text-slate-400 dark:text-slate-500">Estado</dt>
                <dd class="mt-0.5 inline-flex items-center gap-2 rounded-full bg-indigo-50 px-2 py-0.5 font-medium text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-200">
                  <?= e(ucwords(str_replace('_', ' ', $selectedMilestone['status']))); ?>
                </dd>
              </div>
              <?php if (!empty($selectedMilestone['due_date'])): ?>
                <div>
                  <dt class="uppercase tracking-wide text-[10px] text-slate-400 dark:text-slate-500">Entrega</dt>
                  <dd class="mt-0.5"><?= e($selectedMilestone['due_date']); ?></dd>
                </div>
              <?php endif; ?>
            </dl>
          </div>
          <div class="text-right text-xs text-slate-500 dark:text-slate-400">
            <p class="font-medium"><?= $commentTotals[$selectedMilestoneId] ?? 0; ?> comentarios</p>
            <a href="<?= e(url('/milestones?project=' . $selectedProjectId . '#milestone-' . $selectedMilestoneId)); ?>" class="mt-1 inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-500 dark:text-indigo-300">Ver entregas</a>
          </div>
        </header>

        <?php if (!empty($commentErrors['comment'])): ?>
          <p class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs text-rose-600 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200"><?= e($commentErrors['comment']); ?></p>
        <?php endif; ?>

        <div id="comentarios" class="mt-6 space-y-5">
          <section class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 text-sm dark:border-slate-800 dark:bg-slate-900/70">
            <header class="flex flex-wrap items-center justify-between gap-2">
              <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">Conversación general</h3>
              <span class="text-[11px] text-slate-500 dark:text-slate-400"><?= count($milestoneComments[$selectedMilestoneId] ?? []); ?> mensajes</span>
            </header>

            <?php $generalComments = $milestoneComments[$selectedMilestoneId] ?? []; ?>
            <?php if ($generalComments): ?>
              <ul class="mt-3 space-y-3">
                <?php foreach ($generalComments as $comment): ?>
                  <?php $author = $usersMap[(int) $comment['user_id']] ?? null; ?>
                  <li class="rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-900">
                    <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                      <span class="font-semibold text-slate-600 dark:text-slate-200"><?= e($author['full_name'] ?? 'Usuario'); ?></span>
                      <span><?= e(substr($comment['created_at'], 0, 16)); ?></span>
                    </div>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-200"><?= nl2br(e($comment['message'])); ?></p>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <p class="mt-3 rounded-xl border border-dashed border-slate-200 bg-white px-3 py-3 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/60">Todavía no hay mensajes generales. Inicia la conversación a continuación.</p>
            <?php endif; ?>

            <form method="post" action="<?= e(url('/comments')); ?>" class="mt-4 space-y-3">
              <input type="hidden" name="milestone_id" value="<?= $selectedMilestoneId; ?>" />
              <input type="hidden" name="submission_id" value="0" />
              <input type="hidden" name="project_id" value="<?= $selectedProjectId; ?>" />
              <input type="hidden" name="thread_scope" value="milestone" />
              <input type="hidden" name="redirect_to" value="<?= e($redirectOld); ?>" />
              <div>
                <label for="general-comment" class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Nuevo comentario</label>
                <textarea id="general-comment" name="message" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-400" placeholder="Comparte retroalimentación o dudas"><?= e($generalOldMessage); ?></textarea>
                <?php if (!empty($commentErrors['message']) && $commentScopeOld === 'milestone'): ?>
                  <p class="mt-1 text-[11px] text-rose-500"><?= e($commentErrors['message']); ?></p>
                <?php endif; ?>
              </div>
              <div class="text-right">
                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700 dark:bg-indigo-600 dark:hover:bg-indigo-500">Publicar</button>
              </div>
            </form>
          </section>

          <section class="rounded-2xl border border-slate-200 bg-white p-4 text-sm dark:border-slate-800 dark:bg-slate-900">
            <header class="flex flex-wrap items-center justify-between gap-2">
              <div>
                <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">Entrega actual</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Comentarios y archivos del envío más reciente.</p>
              </div>
              <?php if ($latestSubmission): ?>
                <span class="text-[11px] text-slate-500 dark:text-slate-400">Actualizado <?= e(substr($latestSubmission['created_at'], 0, 16)); ?></span>
              <?php endif; ?>
            </header>

            <?php if ($latestSubmission): ?>
              <?php $author = $usersMap[(int) $latestSubmission['user_id']] ?? null; ?>
              <article class="mt-3 rounded-xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-900/70">
                <div class="flex flex-wrap items-start justify-between gap-3 text-[11px] text-slate-500 dark:text-slate-400">
                  <div>
                    <p class="font-semibold text-slate-700 dark:text-slate-200"><?= e($author['full_name'] ?? 'Usuario'); ?></p>
                    <p><?= e(substr($latestSubmission['created_at'], 0, 16)); ?></p>
                  </div>
                  <?php if (!empty($latestSubmission['attachment_path'])): ?>
                    <a href="<?= e(url('/submissions/download?id=' . (int) $latestSubmission['id'])); ?>" class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-500 dark:text-indigo-300">
                      <i data-lucide="paperclip" class="h-3.5 w-3.5"></i>
                      Descargar archivo
                    </a>
                  <?php endif; ?>
                </div>
                <?php if (!empty($latestSubmission['notes'])): ?>
                  <p class="mt-2 text-sm text-slate-600 dark:text-slate-200"><?= nl2br(e($latestSubmission['notes'])); ?></p>
                <?php endif; ?>
              </article>

              <?php $latestComments = $latestSubmission['comments'] ?? []; ?>
              <?php if ($latestComments): ?>
                <ul class="mt-3 space-y-3">
                  <?php foreach ($latestComments as $comment): ?>
                    <?php $commentAuthor = $usersMap[(int) $comment['user_id']] ?? null; ?>
                    <li class="rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-800 dark:bg-slate-900">
                      <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                        <span class="font-semibold text-slate-600 dark:text-slate-200"><?= e($commentAuthor['full_name'] ?? 'Usuario'); ?></span>
                        <span><?= e(substr($comment['created_at'], 0, 16)); ?></span>
                      </div>
                      <p class="mt-2 text-sm text-slate-600 dark:text-slate-200"><?= nl2br(e($comment['message'])); ?></p>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <p class="mt-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/60">Aún no hay comentarios sobre esta entrega.</p>
              <?php endif; ?>

              <form method="post" action="<?= e(url('/comments')); ?>" class="mt-4 space-y-3">
                <input type="hidden" name="milestone_id" value="<?= $selectedMilestoneId; ?>" />
                <input type="hidden" name="submission_id" value="<?= (int) $latestSubmission['id']; ?>" />
                <input type="hidden" name="project_id" value="<?= $selectedProjectId; ?>" />
                <input type="hidden" name="thread_scope" value="submission" />
                <input type="hidden" name="redirect_to" value="<?= e($redirectOld); ?>" />
                <div>
                  <label for="submission-comment" class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Responder a la entrega</label>
                  <textarea id="submission-comment" name="message" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-indigo-400" placeholder="Comparte retroalimentación puntual"><?= e($submissionOldMessage); ?></textarea>
                  <?php if (!empty($commentErrors['message']) && $commentScopeOld === 'submission'): ?>
                    <p class="mt-1 text-[11px] text-rose-500"><?= e($commentErrors['message']); ?></p>
                  <?php endif; ?>
                </div>
                <div class="text-right">
                  <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">Publicar comentario</button>
                </div>
              </form>
            <?php else: ?>
              <p class="mt-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/60">Aún no existen entregas para este hito. Utiliza la conversación general para coordinar el siguiente paso.</p>
            <?php endif; ?>
          </section>

          <?php if ($olderSubmissions): ?>
            <section class="rounded-2xl border border-slate-200 bg-white p-4 text-sm dark:border-slate-800 dark:bg-slate-900">
              <header class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">Historial de entregas</h3>
                <span class="text-[11px] text-slate-500 dark:text-slate-400"><?= count($olderSubmissions); ?> registro<?= count($olderSubmissions) === 1 ? '' : 's'; ?></span>
              </header>
              <ul class="mt-3 space-y-3">
                <?php foreach ($olderSubmissions as $submission): ?>
                  <?php
                    $submissionAuthor = $usersMap[(int) $submission['user_id']] ?? null;
                    $submissionComments = $submission['comments'] ?? [];
                  ?>
                  <li class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 dark:border-slate-800 dark:bg-slate-900/70">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                      <span class="font-semibold text-slate-600 dark:text-slate-200"><?= e($submissionAuthor['full_name'] ?? 'Usuario'); ?></span>
                      <span><?= e(substr($submission['created_at'], 0, 16)); ?></span>
                    </div>
                    <?php if (!empty($submission['notes'])): ?>
                      <p class="mt-2 text-sm text-slate-600 dark:text-slate-200"><?= nl2br(e($submission['notes'])); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($submission['attachment_path'])): ?>
                      <a href="<?= e(url('/submissions/download?id=' . (int) $submission['id'])); ?>" class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-300">
                        <i data-lucide="paperclip" class="h-3.5 w-3.5"></i>
                        Descargar archivo
                      </a>
                    <?php endif; ?>

                    <?php if ($submissionComments): ?>
                      <ul class="mt-3 space-y-2 rounded-xl border border-slate-200 bg-white px-3 py-3 text-xs dark:border-slate-700 dark:bg-slate-900">
                        <?php foreach ($submissionComments as $comment): ?>
                          <?php $historicAuthor = $usersMap[(int) $comment['user_id']] ?? null; ?>
                          <li class="flex flex-col gap-1">
                            <div class="flex items-center justify-between text-[10px] text-slate-500 dark:text-slate-400">
                              <span class="font-semibold text-slate-600 dark:text-slate-200"><?= e($historicAuthor['full_name'] ?? 'Usuario'); ?></span>
                              <span><?= e(substr($comment['created_at'], 0, 16)); ?></span>
                            </div>
                            <p class="text-[12px] text-slate-600 dark:text-slate-200"><?= nl2br(e($comment['message'])); ?></p>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </section>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </section>
  </div>
<?php endif; ?>
<?php
$pageContent = ob_get_clean();
require __DIR__ . '/../layouts/app-shell.php';
