<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <div>
    <h1 id="pageTitle" class="text-xl font-semibold sm:text-2xl"><?= e($pageTitle ?? 'Panel'); ?></h1>
    <p id="pageSubtitle" class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"><?= e($pageSubtitle ?? 'Resumen general y acciones rápidas'); ?></p>
  </div>
  <form method="post" action="<?= e(url('/logout')); ?>" id="logoutForm" class="hidden"></form>
</div>
