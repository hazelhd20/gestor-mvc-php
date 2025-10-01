<?php if ($success): ?>
  <div class="mb-6 rounded-2xl border border-emerald-200/70 bg-emerald-50/80 px-5 py-4 text-sm text-emerald-700 dark:border-emerald-800/70 dark:bg-emerald-900/40 dark:text-emerald-100">
    <?= e($success); ?>
  </div>
<?php endif; ?>

<?php if ($errors): ?>
  <div class="mb-6 space-y-2">
    <?php foreach ($errors as $error): ?>
      <div class="rounded-2xl border border-rose-200/70 bg-rose-50/80 px-5 py-4 text-sm text-rose-600 dark:border-rose-800/70 dark:bg-rose-900/40 dark:text-rose-100">
        <?= e($error); ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
