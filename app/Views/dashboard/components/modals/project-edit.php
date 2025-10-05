<?php if (empty($isDirector)): ?>
  <?php return; ?>
<?php endif; ?>

<?php
  $projectEditValues = $projectEditOld ?? [];
  $editingProjectId = $projectEditId ?? null;
  $projectEditFormProjectId = $projectEditValues['project_id'] ?? $editingProjectId ?? null;
?>

<div id="modalProjectEdit" class="modal hidden">
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-3 py-6">
    <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-700 dark:bg-slate-900">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">Editar proyecto</h2>
        <button data-modal-close class="rounded-full p-1 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" type="button">
          <i data-lucide="x" class="h-5 w-5"></i>
        </button>
      </div>
      <form method="post" action="<?= e(url('/projects/update')); ?>" class="mt-5 space-y-3" data-form="project-edit">
        <input type="hidden" name="project_id" value="<?= e((string) ($projectEditValues['project_id'] ?? $editingProjectId ?? '')); ?>" />
        <input type="hidden" name="return_tab" value="<?= e((string) ($activeTab ?? 'dashboard')); ?>" />
        <input type="hidden" name="return_project" value="<?= e((string) ($projectEditFormProjectId ?? '')); ?>" />
        <?php if ($projectEditFormProjectId !== null && $projectEditFormProjectId !== ''): ?>
          <input type="hidden" name="return_anchor" value="<?= e('project-' . (string) $projectEditFormProjectId); ?>" />
        <?php endif; ?>
        <div>
          <label class="text-xs font-semibold uppercase text-slate-500" for="project-edit-title">Titulo</label>
          <input
            id="project-edit-title"
            type="text"
            name="title"
            value="<?= e((string) ($projectEditValues['title'] ?? '')); ?>"
            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900"
            required
          />
        </div>
        <div>
          <label class="text-xs font-semibold uppercase text-slate-500" for="project-edit-description">Descripcion</label>
          <textarea
            id="project-edit-description"
            name="description"
            rows="3"
            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900"
            placeholder="Actualiza el resumen del proyecto"
          ><?= e((string) ($projectEditValues['description'] ?? '')); ?></textarea>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
          <div>
            <label class="text-xs font-semibold uppercase text-slate-500" for="project-edit-student">Estudiante</label>
            <select
              id="project-edit-student"
              name="student_id"
              class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900"
              required
            >
              <option value="">Selecciona una opcion</option>
              <?php foreach ($students as $student): ?>
                <option
                  value="<?= e((string) $student['id']); ?>"
                  <?= ((string) ($projectEditValues['student_id'] ?? '') === (string) $student['id']) ? 'selected' : ''; ?>
                >
                  <?= e($student['full_name']); ?> (<?= e($student['email']); ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="text-xs font-semibold uppercase text-slate-500" for="project-edit-start-date">Fecha de inicio</label>
            <input
              id="project-edit-start-date"
              type="date"
              name="start_date"
              value="<?= e((string) ($projectEditValues['start_date'] ?? '')); ?>"
              class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900"
              required
            />
          </div>
          <div>
            <label class="text-xs font-semibold uppercase text-slate-500" for="project-edit-end-date">Fecha de finalizacion</label>
            <input
              id="project-edit-end-date"
              type="date"
              name="end_date"
              value="<?= e((string) ($projectEditValues['end_date'] ?? '')); ?>"
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
