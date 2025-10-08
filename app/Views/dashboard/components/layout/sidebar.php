<?php
$active = $activeTab ?? 'dashboard';
?>
<aside
  id="sidebar"
  class="sidebar-animatable fixed inset-y-0 left-0 z-40 w-64 -translate-x-full transform border-r border-slate-200 bg-white p-4 shadow-lg transition-all duration-500 ease-out dark:border-slate-800 dark:bg-slate-900 md:sticky md:top-14 md:h-[calc(100vh-3.5rem)] md:translate-x-0 md:p-2 md:shadow-none md:transition-none"
  aria-label="Navegacion principal"
  aria-hidden="true"
>
  <nav class="flex h-full flex-col">
    <ul class="flex-1 space-y-1" id="navList">
      <?php foreach ($navItems as $item): ?>
        <?php $isActive = $item['id'] === $active; ?>
        <li>
          <button
            type="button"
            class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-[13px] font-medium tracking-tight transition <?= $isActive ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'; ?>"
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
      <button class="side-item flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-[13px] text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" type="button" data-modal="modalProfile">
        <i data-lucide="settings" class="icon h-5 w-5 flex-none"></i>
        <span class="label">Configuracion</span>
      </button>
      <button class="side-item flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-[13px] text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" type="button">
        <i data-lucide="help-circle" class="icon h-5 w-5 flex-none"></i>
        <span class="label">Ayuda</span>
      </button>
      <button class="side-item flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-[13px] text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" type="button" data-action="logout">
        <i data-lucide="log-out" class="icon h-5 w-5 flex-none"></i>
        <span class="label">Salir</span>
      </button>
    </div>
  </nav>
</aside>
