<header class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-900/80">
  <div class="flex h-14 items-center gap-3 px-3 sm:px-4">
    <button id="btnSidebar" class="inline-flex h-9 w-9 items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Mostrar u ocultar la navegacion">
      <i data-lucide="menu" class="h-5 w-5"></i>
    </button>

    <div class="flex items-center gap-2 font-semibold">
      <span class="rounded-lg bg-indigo-600 px-2 py-0.5 text-white">GT</span>
      <span class="hidden text-sm sm:inline">Gestor de Titulacion</span>
    </div>

    <div class="mx-3 hidden flex-1 items-center sm:flex">
      <label class="relative w-full max-w-xl">
        <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
        <span class="sr-only">Buscar</span>
        <input type="text" placeholder="Buscar proyecto, hito, comentario..." class="w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 py-2 text-sm outline-none placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white dark:border-slate-700 dark:bg-slate-800 dark:focus:border-indigo-400" />
      </label>
    </div>

    <div class="ml-auto flex items-center gap-1 sm:gap-2">
      <button class="relative inline-flex h-9 w-9 items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Notificaciones">
        <i data-lucide="bell" class="h-5 w-5"></i>
        <span class="absolute right-1 top-1 h-2 w-2 rounded-full bg-rose-500"></span>
      </button>

      <div class="inline-flex overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800" role="group" aria-label="Cambiar tema">
        <button id="btnLight" class="flex h-9 w-9 items-center justify-center" title="Modo claro">
          <i data-lucide="sun" class="h-5 w-5"></i>
          <span class="sr-only">Modo claro</span>
        </button>
        <button id="btnDark" class="flex h-9 w-9 items-center justify-center" title="Modo oscuro">
          <i data-lucide="moon" class="h-5 w-5"></i>
          <span class="sr-only">Modo oscuro</span>
        </button>
      </div>

      <div class="ml-1 flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-2 py-1.5 text-sm dark:border-slate-800 dark:bg-slate-900">
        <?php if (!empty($avatarUrl)): ?>
          <img src="<?= e($avatarUrl); ?>" alt="Foto de perfil" class="h-7 w-7 rounded-full object-cover" />
        <?php else: ?>
          <span class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-600 text-xs font-semibold text-white">
            <?= e($avatarInitials ?? 'U'); ?>
          </span>
        <?php endif; ?>
        <div class="hidden leading-tight sm:block">
          <p class="text-xs font-medium"><?= e($fullName); ?></p>
          <p class="text-[10px] text-slate-500 dark:text-slate-400"><?= e(ucfirst($role)); ?></p>
        </div>
      </div>
    </div>
  </div>
</header>
