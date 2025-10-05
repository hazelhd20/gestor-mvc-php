<?php if (empty($isDirector) || empty($selectedProject)): ?>
  <?php return; ?>
<?php endif; ?>

<?php
  $milestoneEditValues = $milestoneEditOld ?? [];
  $milestoneEditId = $milestoneEditId ?? null;
  $milestoneEditProjectId = $milestoneEditValues['project_id'] ?? ($selectedProject['id'] ?? null);
?>

<div id="modalMilestoneEdit" class="modal hidden">
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-3 py-6">
    <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">Editar hito</h2>
        <button data-modal-close class="rounded-full p-1 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" type="button">
          <i data-lucide="x" class="h-5 w-5"></i>
        </button>
      </div>
      <form method="post" action="<?= e(url('/milestones/update')); ?>" class="mt-5 space-y-3" data-form="milestone-edit">
        <input type="hidden" name="milestone_id" value="<?= e((string) ($milestoneEditValues['milestone_id'] ?? $milestoneEditId ?? '')); ?>" />
        <input type="hidden" name="return_tab" value="<?= e((string) ($activeTab ?? 'dashboard')); ?>" />
        <input type="hidden" name="return_project" value="<?= e((string) ($milestoneEditProjectId ?? '')); ?>" />
        <?php if (($milestoneEditValues['milestone_id'] ?? $milestoneEditId ?? '') !== ''): ?>
          <input type="hidden" name="return_anchor" value="<?= e('milestone-' . (string) ($milestoneEditValues['milestone_id'] ?? $milestoneEditId)); ?>" />
        <?php endif; ?>
        <div>
          <label class="text-xs font-semibold uppercase text-slate-500" for="milestone-edit-title">Titulo <span class="text-rose-500" aria-hidden="true">*</span><span class="sr-only"> (obligatorio)</span></label>
          <input
            id="milestone-edit-title"
            type="text"
            name="title"
            value="<?= e((string) ($milestoneEditValues['title'] ?? '')); ?>"
            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900"
            required
          />
        </div>
        <div>
          <label class="text-xs font-semibold uppercase text-slate-500" for="milestone-edit-description">Descripcion</label>
          <textarea
            id="milestone-edit-description"
            name="description"
            rows="3"
            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900"
            placeholder="Actualiza los detalles del hito"
          ><?= e((string) ($milestoneEditValues['description'] ?? '')); ?></textarea>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
          <div>
            <label class="text-xs font-semibold uppercase text-slate-500" for="milestone-edit-start-date">Fecha de inicio <span class="text-rose-500" aria-hidden="true">*</span><span class="sr-only"> (obligatorio)</span></label>
            <input
              id="milestone-edit-start-date"
              type="date"
              name="start_date"
              value="<?= e((string) ($milestoneEditValues['start_date'] ?? '')); ?>"
              class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900"
              required
            />
          </div>
          <div>
            <label class="text-xs font-semibold uppercase text-slate-500" for="milestone-edit-end-date">Fecha de finalizacion <span class="text-rose-500" aria-hidden="true">*</span><span class="sr-only"> (obligatorio)</span></label>
            <input
              id="milestone-edit-end-date"
              type="date"
              name="end_date"
              value="<?= e((string) ($milestoneEditValues['end_date'] ?? '')); ?>"
              class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900"
              required
            />
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-2">
          <button
            type="button"
            data-modal-close
            class="rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
          >
            Cancelar
          </button>
          <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
            Guardar cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
