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
      <div class="relative w-full max-w-xl" data-global-search>
        <label for="globalSearchInput" class="sr-only">Buscar</label>
        <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
        <input
          id="globalSearchInput"
          type="search"
          placeholder="Buscar proyecto, hito, comentario..."
          autocomplete="off"
          spellcheck="false"
          class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm outline-none placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white dark:border-slate-700 dark:bg-slate-800 dark:focus:border-indigo-400"
          data-global-search-input
        />
        <div
          data-global-search-panel
          class="absolute left-0 right-0 top-full z-40 mt-2 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/70 dark:border-slate-700 dark:bg-slate-900"
        >
          <div data-global-search-loading class="border-b border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900/70 dark:text-slate-300 hidden">
            Buscando…
          </div>
          <div data-global-search-error class="border-b border-rose-100 bg-rose-50/80 px-4 py-3 text-sm text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-200 hidden">
            Ocurrió un error al buscar. Intenta nuevamente.
          </div>
          <div data-global-search-empty class="border-b border-slate-100 bg-slate-50/60 px-4 py-3 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-300 hidden">
            No se encontraron coincidencias.
          </div>
          <div data-global-search-list class="max-h-80 overflow-y-auto bg-white dark:bg-slate-900" role="listbox"></div>
        </div>
      </div>
    </div>

    <?php
      $unreadCount = isset($unreadNotificationCount) ? (int) $unreadNotificationCount : 0;
      $unreadLabel = $unreadCount > 9 ? '9+' : (string) $unreadCount;
    ?>

    <div class="ml-auto flex items-center gap-1 sm:gap-2">
      <div class="relative">
        <button
          type="button"
          class="relative inline-flex h-9 w-9 items-center justify-center rounded-xl hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:hover:bg-slate-800"
          data-notifications-toggle
          data-notifications-count="<?= (int) $unreadCount; ?>"
          aria-expanded="false"
          aria-haspopup="true"
          aria-controls="notificationsPanel"
        >
          <span class="sr-only">Abrir notificaciones</span>
          <i data-lucide="bell" class="h-5 w-5"></i>
          <span
            data-notifications-badge
            class="absolute -right-0.5 -top-0.5 min-h-[1.2rem] min-w-[1.2rem] rounded-full bg-rose-500 px-1 text-center text-[10px] font-semibold leading-4 text-white <?= $unreadCount > 0 ? '' : 'hidden'; ?>"
          >
            <?= e($unreadLabel); ?>
          </span>
        </button>
      </div>

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
