<?php
$titleSuffix = isset($pageTitle) && $pageTitle ? 'Gestor de Titulación - ' . $pageTitle : 'Gestor de Titulación';
?>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= e($titleSuffix); ?></title>
  <link rel="stylesheet" href="<?= e(url('/assets/css/tailwind.min.css')); ?>" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .transition-width { transition: width .3s ease; }
    .scrollbar-thin { scrollbar-width: thin; }
    .scrollbar-thin::-webkit-scrollbar { width: 6px; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background-color: rgba(148, 163, 184, .6); border-radius: 9999px; }
    #sidebar.sidebar-collapsed .label { display: none; }
    #sidebar.sidebar-collapsed nav ul li button,
    #sidebar.sidebar-collapsed .side-item { justify-content: center; }
    #sidebar.sidebar-animatable.sidebar-animating { will-change: transform, opacity; }
    @media (max-width: 767px) {
      #sidebar.sidebar-animatable { opacity: 0; }
      #sidebar.sidebar-animatable.translate-x-0 { opacity: 1; }
    }
    @media (min-width: 768px) {
      #sidebar.sidebar-collapsed { width: 5rem; padding-left: .5rem; padding-right: .5rem; }
      #sidebar.sidebar-animatable { opacity: 1; }
    }
    @media (prefers-reduced-motion: reduce) {
      #sidebar.sidebar-animatable { transition: none !important; }
    }
  </style>
</head>
