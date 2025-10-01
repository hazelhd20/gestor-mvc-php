<?php $_dashboard = get_defined_vars(); ?>
<main class="min-h-[calc(100vh-3.5rem)] flex-1 px-4 py-6 sm:px-8 sm:py-8">
  <?php dashboard_layout('page-heading', $_dashboard); ?>
  <?php dashboard_component('shared/alerts', $_dashboard); ?>

  <?php dashboard_section('dashboard-overview', $_dashboard); ?>
  <?php dashboard_section('projects', $_dashboard); ?>
  <?php dashboard_section('milestones', $_dashboard); ?>
  <?php dashboard_section('comments', $_dashboard); ?>
  <?php dashboard_section('progress', $_dashboard); ?>
</main>
