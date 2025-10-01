<?php $_dashboard = get_defined_vars(); ?>
<!doctype html>
<html lang="es" class="h-full">
<?php dashboard_layout('head', $_dashboard); ?>
<body class="layout-shell h-full text-slate-900 antialiased dark:text-slate-100">
  <?php dashboard_layout('header', $_dashboard); ?>

  <div class="flex w-full gap-0">
    <?php dashboard_layout('sidebar', $_dashboard); ?>
    <?php dashboard_layout('main', $_dashboard); ?>
  </div>

  <?php dashboard_modal('profile', $_dashboard); ?>

  <?php if (!empty($isDirector)): ?>
    <?php dashboard_modal('project', $_dashboard); ?>
    <?php if (!empty($selectedProject)): ?>
      <?php dashboard_modal('milestone', $_dashboard); ?>
    <?php endif; ?>
  <?php endif; ?>

  <?php dashboard_layout('scripts', $_dashboard); ?>
</body>
</html>
