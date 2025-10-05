<?php $_dashboard = get_defined_vars(); ?>
<!doctype html>
<html lang="es" class="h-full">
<?php dashboard_layout('head', $_dashboard); ?>
<body class="h-full bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
  <?php dashboard_layout('header', $_dashboard); ?>
  <?php dashboard_layout('notifications-panel', $_dashboard); ?>

  <div class="flex w-full gap-0">
    <?php dashboard_layout('sidebar', $_dashboard); ?>
    <div
      id="sidebarBackdrop"
      class="sidebar-overlay fixed inset-0 z-30 bg-slate-900/30 opacity-0 transition-opacity duration-300 pointer-events-none md:hidden"
      aria-hidden="true"
    ></div>
    <?php dashboard_layout('main', $_dashboard); ?>
  </div>

  <?php dashboard_modal('profile', $_dashboard); ?>

  <?php if (!empty($isDirector)): ?>
    <?php dashboard_modal('project', $_dashboard); ?>
    <?php dashboard_modal('project-edit', $_dashboard); ?>
    <?php if (!empty($selectedProject)): ?>
      <?php dashboard_modal('milestone', $_dashboard); ?>
      <?php dashboard_modal('milestone-edit', $_dashboard); ?>
    <?php endif; ?>
  <?php endif; ?>

  <?php dashboard_layout('scripts', $_dashboard); ?>
</body>
</html>
