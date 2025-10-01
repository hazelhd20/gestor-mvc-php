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
    .layout-shell { background-color: #f8fafc; }
    .dark .layout-shell { background-color: #020617; }
    .surface { border-radius: 22px; border: 1px solid rgba(15, 23, 42, 0.08); background: rgba(255, 255, 255, 0.92); box-shadow: 0 24px 60px -36px rgba(15, 23, 42, 0.4); backdrop-filter: blur(10px); }
    .dark .surface { border-color: rgba(71, 85, 105, 0.55); background: rgba(15, 23, 42, 0.7); box-shadow: 0 24px 60px -36px rgba(15, 23, 42, 0.65); }
    .surface-muted { border-radius: 18px; border: 1px solid rgba(148, 163, 184, 0.18); background: rgba(248, 250, 252, 0.85); }
    .dark .surface-muted { border-color: rgba(71, 85, 105, 0.55); background: rgba(15, 23, 42, 0.6); }
    .surface-table { border-radius: 22px; border: 1px solid rgba(15, 23, 42, 0.08); background: rgba(255, 255, 255, 0.92); box-shadow: 0 18px 50px -32px rgba(15, 23, 42, 0.45); overflow: hidden; }
    .dark .surface-table { border-color: rgba(71, 85, 105, 0.55); background: rgba(15, 23, 42, 0.75); box-shadow: 0 24px 50px -32px rgba(15, 23, 42, 0.75); }
    .section-title { font-size: 0.95rem; font-weight: 600; color: #0f172a; }
    .dark .section-title { color: #e2e8f0; }
    .section-subtitle { font-size: 0.75rem; color: #64748b; }
    .dark .section-subtitle { color: #94a3b8; }
    .badge-soft { display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; padding: 0.125rem 0.625rem; font-size: 0.7rem; font-weight: 600; letter-spacing: 0.02em; background-color: rgba(148, 163, 184, 0.15); color: #475569; }
    .dark .badge-soft { background-color: rgba(148, 163, 184, 0.22); color: #cbd5f5; }
  </style>
</head>
