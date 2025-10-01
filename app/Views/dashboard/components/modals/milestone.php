<?php if (empty($isDirector) || empty($selectedProject)): ?>
  <?php return; ?>
<?php endif; ?>

<div id="modalMilestone" class="modal hidden">
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-3 py-6">
    <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-700 dark:bg-slate-900">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">Nuevo hito</h2>
        <button data-modal-close class="rounded-full p-1 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" type="button">
          <i data-lucide="x" class="h-5 w-5"></i>
        </button>
      </div>
      <form method="post" action="<?= e(url('/milestones')); ?>" class="mt-5 space-y-3">
        <input type="hidden" name="project_id" value="<?= e((string) $selectedProject['id']); ?>" />
        <div>
          <label class="text-xs font-semibold uppercase text-slate-500" for="milestone-title">Título</label>
          <input id="milestone-title" type="text" name="title" value="<?= e((string) ($milestoneOld['title'] ?? '')); ?>" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900" required />
        </div>
        <div>
          <label class="text-xs font-semibold uppercase text-slate-500" for="milestone-description">Descripción</label>
          <textarea id="milestone-description" name="description" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900" placeholder="Detalles del entregable"><?= e((string) ($milestoneOld['description'] ?? '')); ?></textarea>
        </div>
        <div>
          <label class="text-xs font-semibold uppercase text-slate-500" for="milestone-due-date">Fecha límite</label>
          <input id="milestone-due-date" type="date" name="due_date" value="<?= e((string) ($milestoneOld['due_date'] ?? '')); ?>" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900" />
        </div>
        <div class="flex justify-end gap-2 pt-2">
          <button type="button" data-modal-close class="rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Cancelar</button>
          <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Crear hito</button>
        </div>
      </form>
    </div>
  </div>
</div>
