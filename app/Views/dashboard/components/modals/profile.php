<div id="modalProfile" class="modal hidden">
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-3 py-6">
    <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">Configuracion de perfil</h2>
        <button data-modal-close class="rounded-full p-1 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" type="button">
          <i data-lucide="x" class="h-5 w-5"></i>
        </button>
      </div>

      <form method="post" action="<?= e(url('/profile/avatar')); ?>" enctype="multipart/form-data" class="mt-5 space-y-4">
        <input type="hidden" name="return_tab" value="<?= e($activeTab ?? 'dashboard'); ?>" />
        <?php if (!empty($selectedProject['id'])): ?>
          <input type="hidden" name="return_project" value="<?= e((string) $selectedProject['id']); ?>" />
        <?php endif; ?>

        <div class="flex items-center gap-4">
          <?php if (!empty($avatarUrl)): ?>
            <img src="<?= e($avatarUrl); ?>" alt="Foto actual" class="h-16 w-16 rounded-full object-cover" />
          <?php else: ?>
            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-indigo-600 text-lg font-semibold text-white">
              <?= e($avatarInitials ?? 'U'); ?>
            </span>
          <?php endif; ?>
          <div>
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200"><?= e($fullName); ?></p>
            <p class="text-xs text-slate-500 dark:text-slate-400"><?= e($role === 'director' ? 'Director' : ($role === 'estudiante' ? 'Estudiante' : ucfirst($role))); ?></p>
          </div>
        </div>

        <div>
          <label for="profile-avatar" class="text-xs font-semibold uppercase text-slate-500">Selecciona una imagen <span class="text-rose-500" aria-hidden="true">*</span><span class="sr-only"> (obligatorio)</span></label>
          <input id="profile-avatar" name="avatar" type="file" accept="image/png,image/jpeg,image/webp,image/gif" class="mt-1 w-full cursor-pointer rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-indigo-700 dark:border-slate-700 dark:bg-slate-900" required />
          <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Formatos permitidos: JPG, PNG, GIF o WEBP. Peso maximo 5 MB.</p>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button type="button" data-modal-close class="rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Cancelar</button>
          <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
            <i data-lucide="upload" class="h-4 w-4"></i> Guardar foto
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
