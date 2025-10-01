<?php if ($success): ?>
  <div class="mb-4 rounded-2xl border border-emerald-400/40 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-700 shadow-lg backdrop-blur-sm dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-100">
    <?= e($success); ?>
  </div>
<?php endif; ?>

<?php if ($errors): ?>
  <div class="mb-4 space-y-2">
    <?php foreach ($errors as $error): ?>
      <div class="rounded-2xl border border-rose-400/40 bg-rose-50/80 px-4 py-3 text-sm text-rose-700 shadow-lg backdrop-blur-sm dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-100">
        <?= e($error); ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
