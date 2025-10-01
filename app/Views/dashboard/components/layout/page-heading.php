<div class="mb-8 flex flex-wrap items-center justify-between gap-3">
  <div>
    <h1 id="pageTitle" class="text-2xl font-semibold text-slate-900 sm:text-3xl dark:text-slate-100"><?= e($pageTitle ?? 'Panel'); ?></h1>
    <p id="pageSubtitle" class="mt-1 text-sm text-slate-500 dark:text-slate-400"><?= e($pageSubtitle ?? 'Resumen general y acciones rápidas'); ?></p>
  </div>
  <form method="post" action="<?= e(url('/logout')); ?>" id="logoutForm" class="hidden"></form>
</div>
