<?php if ($success): ?>
  <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-100">
    <?= e($success); ?>
  </div>
<?php endif; ?>

<?php if ($errors): ?>
  <div class="mb-4 space-y-2">
    <?php foreach ($errors as $error): ?>
      <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-800 dark:bg-rose-900/40 dark:text-rose-100">
        <?= e($error); ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
