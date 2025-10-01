<?php
$active = $activeTab ?? 'dashboard';
?>
<aside id="sidebar" class="sticky top-14 hidden h-[calc(100vh-3.5rem)] w-64 shrink-0 transition-width duration-300 md:block" aria-label="Navegacion principal">
  <div class="surface h-full p-3">
    <nav class="flex h-full flex-col">
      <ul class="flex-1 space-y-1" id="navList">
      <?php foreach ($navItems as $item): ?>
        <?php $isActive = $item['id'] === $active; ?>
        <li>
          <button
            type="button"
            class="group flex w-full items-center gap-3 rounded-xl px-3.5 py-2.5 text-left text-[13px] font-medium tracking-tight transition <?= $isActive ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300/40' : 'text-slate-600 hover:bg-slate-100/80 dark:text-slate-300 dark:hover:bg-slate-800/60'; ?>"
            data-nav-btn
            data-nav-target="<?= e($item['id']); ?>"
            data-nav-title="<?= e($item['title']); ?>"
            data-nav-subtitle="<?= e($item['subtitle']); ?>"
          >
            <i data-lucide="<?= e($item['icon']); ?>" class="h-5 w-5 flex-none"></i>
            <span class="label"><?= e($item['label']); ?></span>
          </button>
        </li>
      <?php endforeach; ?>
      </ul>

      <div class="space-y-1 pt-1">
        <button class="flex w-full items-center gap-3 rounded-xl px-3.5 py-2.5 text-left text-[13px] text-slate-600 transition hover:bg-slate-100/80 dark:text-slate-300 dark:hover:bg-slate-800/60" type="button" data-modal="modalProfile">
          <i data-lucide="settings" class="h-5 w-5 flex-none"></i>
          <span class="label">Configuración</span>
        </button>
        <button class="flex w-full items-center gap-3 rounded-xl px-3.5 py-2.5 text-left text-[13px] text-slate-600 transition hover:bg-slate-100/80 dark:text-slate-300 dark:hover:bg-slate-800/60" type="button">
          <i data-lucide="help-circle" class="h-5 w-5 flex-none"></i>
          <span class="label">Ayuda</span>
        </button>
        <button class="flex w-full items-center gap-3 rounded-xl px-3.5 py-2.5 text-left text-[13px] text-slate-600 transition hover:bg-rose-50/80 hover:text-rose-600 dark:text-slate-300 dark:hover:bg-rose-900/30" type="button" data-action="logout">
          <i data-lucide="log-out" class="h-5 w-5 flex-none"></i>
          <span class="label">Salir</span>
        </button>
      </div>
    </nav>
  </div>
</aside>
