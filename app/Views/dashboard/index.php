<?php
$pageTitle = 'Resumen';
$pageSubtitle = 'Estado general, proximos vencimientos y actividad reciente';
$activeNav = 'dashboard';

ob_start();
?>
<?php if (!empty($statusMessage ?? null)): ?>
  <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
    <?= e($statusMessage); ?>
  </div>
<?php endif; ?>

<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
  <div class="card"></div>
  <div class="card"></div>
  <div class="card"></div>
  <div class="card"></div>
  <div class="card"></div>
  <div class="card"></div>
</div>

<script>
  document.querySelectorAll('.card').forEach(card => {
    card.className = 'rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900';
    card.innerHTML = `
      <div class="h-36 rounded-xl bg-slate-100 dark:bg-slate-800"></div>
      <div class="mt-3 h-3 w-2/3 rounded bg-slate-200 dark:bg-slate-700"></div>
      <div class="mt-2 h-3 w-1/2 rounded bg-slate-200 dark:bg-slate-700"></div>
    `;
  });
</script>
<?php
$pageContent = ob_get_clean();
require __DIR__ . '/../layouts/app-shell.php';