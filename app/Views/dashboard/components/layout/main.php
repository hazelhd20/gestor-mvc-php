<?php $_dashboard = get_defined_vars(); ?>
<main class="min-h-[calc(100vh-3.5rem)] flex-1 p-3 sm:p-6">
  <?php dashboard_layout('page-heading', $_dashboard); ?>
  <?php dashboard_component('shared/alerts', $_dashboard); ?>

  <div class="space-y-8">
    <?php dashboard_section('dashboard-overview', $_dashboard); ?>
    <?php dashboard_section('projects', $_dashboard); ?>
    <?php dashboard_section('milestones', $_dashboard); ?>
    <?php dashboard_section('comments', $_dashboard); ?>
    <?php dashboard_section('progress', $_dashboard); ?>
  </div>
</main>
