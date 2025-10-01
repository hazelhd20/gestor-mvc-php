<header class="sticky top-0 z-40 border-b border-transparent bg-white/90 backdrop-blur shadow-[0_1px_0_rgba(148,163,184,0.25)] dark:bg-slate-900/80 dark:shadow-[0_1px_0_rgba(30,41,59,0.65)]">
  <div class="flex h-14 items-center gap-3 px-4 sm:px-6">
    <button id="btnSidebar" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200/70 bg-white/70 text-slate-500 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300" aria-label="Mostrar u ocultar la navegacion">
      <i data-lucide="menu" class="h-5 w-5"></i>
    </button>

    <div class="flex items-center gap-2 font-semibold text-slate-700 dark:text-slate-200">
      <span class="rounded-lg bg-indigo-600/90 px-2.5 py-1 text-xs text-white">GT</span>
      <span class="hidden text-sm sm:inline">Gestor de Titulación</span>
    </div>

    <div class="mx-3 hidden flex-1 items-center sm:flex">
      <label class="relative w-full max-w-xl">
        <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
        <span class="sr-only">Buscar</span>
        <input type="text" placeholder="Buscar proyecto, hito, comentario..." class="w-full rounded-xl border border-slate-200/70 bg-white/70 pl-10 pr-3 py-2 text-sm text-slate-600 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200 dark:placeholder:text-slate-500" />
      </label>
    </div>

    <div class="ml-auto flex items-center gap-2">
      <button class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200/70 bg-white/70 text-slate-500 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300" aria-label="Notificaciones">
        <i data-lucide="bell" class="h-5 w-5"></i>
        <span class="absolute right-1 top-1 h-2 w-2 rounded-full bg-rose-500"></span>
      </button>

      <div class="inline-flex overflow-hidden rounded-xl border border-slate-200/70 bg-white/70 text-slate-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300" role="group" aria-label="Cambiar tema">
        <button id="btnLight" class="flex h-10 w-10 items-center justify-center" title="Modo claro">
          <i data-lucide="sun" class="h-5 w-5"></i>
          <span class="sr-only">Modo claro</span>
        </button>
        <button id="btnDark" class="flex h-10 w-10 items-center justify-center" title="Modo oscuro">
          <i data-lucide="moon" class="h-5 w-5"></i>
          <span class="sr-only">Modo oscuro</span>
        </button>
      </div>

      <div class="ml-1 flex items-center gap-2 rounded-xl border border-slate-200/70 bg-white/70 px-2 py-1.5 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200">
        <?php if (!empty($avatarUrl)): ?>
          <img src="<?= e($avatarUrl); ?>" alt="Foto de perfil" class="h-7 w-7 rounded-full object-cover" />
        <?php else: ?>
          <span class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-600 text-xs font-semibold text-white">
            <?= e($avatarInitials ?? 'U'); ?>
          </span>
        <?php endif; ?>
        <div class="hidden leading-tight sm:block">
          <p class="text-xs font-medium text-slate-700 dark:text-slate-200"><?= e($fullName); ?></p>
          <p class="text-[10px] text-slate-500 dark:text-slate-400"><?= e(ucfirst($role)); ?></p>
        </div>
      </div>
    </div>
  </div>
</header>
