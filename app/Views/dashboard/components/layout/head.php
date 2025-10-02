<?php
$titleSuffix = isset($pageTitle) && $pageTitle ? 'Gestor de Titulación - ' . $pageTitle : 'Gestor de Titulación';
?>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= e($titleSuffix); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: { sans: ["Inter", "system-ui", "-apple-system", "Segoe UI", "Roboto", "Helvetica", "Arial", "sans-serif"] },
        },
      },
    };
  </script>
  <style>
    .transition-width { transition: width .3s ease; }
    .scrollbar-thin { scrollbar-width: thin; }
    .scrollbar-thin::-webkit-scrollbar { width: 6px; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background-color: rgba(148, 163, 184, .6); border-radius: 9999px; }
    #sidebar.sidebar-collapsed .label { display: none; }
    #sidebar.sidebar-collapsed nav ul li button,
    #sidebar.sidebar-collapsed .side-item { justify-content: center; }
    @media (max-width: 767px) {
      #sidebar { width: min(90vw, 20rem); border-radius: 1.25rem; margin: 1rem; }
      #sidebar nav { padding-bottom: 1.5rem; }
    }
    @media (min-width: 768px) {
      #sidebar { margin: 0; border-radius: 1.5rem; }
      #sidebar.sidebar-collapsed { width: 5rem; padding-left: .5rem; padding-right: .5rem; }
    }
  </style>
</head>
