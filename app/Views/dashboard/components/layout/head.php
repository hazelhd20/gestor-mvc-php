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
    body {
      background-color: #f8fafc;
      background-image:
        radial-gradient(120% 120% at -10% -10%, rgba(129, 140, 248, 0.18), transparent 55%),
        radial-gradient(70% 100% at 110% -10%, rgba(45, 212, 191, 0.12), transparent 55%),
        radial-gradient(90% 140% at 50% 120%, rgba(59, 130, 246, 0.1), transparent 65%);
      background-attachment: fixed;
    }

    .dark body {
      background-color: rgb(2 6 23);
      background-image:
        radial-gradient(120% 120% at -10% -10%, rgba(59, 130, 246, 0.2), transparent 55%),
        radial-gradient(80% 120% at 110% -10%, rgba(8, 145, 178, 0.24), transparent 60%),
        radial-gradient(100% 140% at 50% 120%, rgba(129, 140, 248, 0.2), transparent 70%);
    }

    .transition-width { transition: width .3s ease; }
    .scrollbar-thin { scrollbar-width: thin; }
    .scrollbar-thin::-webkit-scrollbar { width: 6px; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background-color: rgba(148, 163, 184, .6); border-radius: 9999px; }

    .glass-card {
      background-color: rgba(255, 255, 255, 0.82);
      border: 1px solid rgba(148, 163, 184, 0.25);
      box-shadow: 0 25px 40px -30px rgba(15, 23, 42, 0.55);
      backdrop-filter: blur(18px);
    }

    .dark .glass-card {
      background-color: rgba(15, 23, 42, 0.7);
      border-color: rgba(71, 85, 105, 0.45);
      box-shadow: 0 25px 40px -25px rgba(15, 23, 42, 0.85);
    }

    .glow-accent::before {
      content: "";
      position: absolute;
      inset-inline-end: -40px;
      inset-block-start: -40px;
      width: 180px;
      height: 180px;
      border-radius: 9999px;
      background: radial-gradient(circle, rgba(255, 255, 255, 0.55), transparent 65%);
      opacity: .75;
      filter: blur(0);
    }

    .dark .glow-accent::before {
      background: radial-gradient(circle, rgba(99, 102, 241, 0.55), transparent 65%);
      opacity: .65;
    }
  </style>
</head>
