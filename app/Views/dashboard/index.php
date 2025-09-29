<?php
$fullName = $user['full_name'] ?? 'Usuario';
$role = $user['role'] ?? 'Invitado';
?>
<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gestor de Titulacion - Dashboard</title>
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
  </style>
</head>
<body class="h-full bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
  <header class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-900/80">
    <div class="flex h-14 items-center gap-3 px-3 sm:px-4">
      <button id="btnSidebar" class="inline-flex h-9 w-9 items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Mostrar/ocultar navegacion">
        <i data-lucide="menu" class="h-5 w-5"></i>
      </button>

      <div class="flex items-center gap-2 font-semibold">
        <span class="rounded-lg bg-indigo-600 px-2 py-0.5 text-white">GT</span>
        <span class="hidden text-sm sm:inline">Gestor de Titulacion</span>
      </div>

      <div class="mx-3 hidden flex-1 items-center sm:flex">
        <div class="relative w-full max-w-xl">
          <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
          <input type="text" placeholder="Buscar proyecto, hito, comentario..." class="w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 py-2 text-sm outline-none placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white dark:border-slate-700 dark:bg-slate-800 dark:focus:border-indigo-400" />
        </div>
      </div>

      <div class="ml-auto flex items-center gap-1 sm:gap-2">
        <button class="relative inline-flex h-9 w-9 items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Notificaciones">
          <i data-lucide="bell" class="h-5 w-5"></i>
          <span class="absolute right-1 top-1 h-2 w-2 rounded-full bg-rose-500"></span>
        </button>

        <div class="inline-flex overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800" role="group" aria-label="Cambiar tema">
          <button id="btnLight" class="flex h-9 w-9 items-center justify-center" title="Claro">
            <i data-lucide="sun" class="h-5 w-5"></i>
          </button>
          <button id="btnDark" class="flex h-9 w-9 items-center justify-center" title="Oscuro">
            <i data-lucide="moon" class="h-5 w-5"></i>
          </button>
        </div>

        <div class="ml-1 flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-2 py-1.5 text-sm dark:border-slate-800 dark:bg-slate-900">
          <img src="https://i.pravatar.cc/100?img=5" alt="avatar" class="h-7 w-7 rounded-full" />
          <div class="hidden leading-tight sm:block">
            <p class="text-xs font-medium"><?= e($fullName); ?></p>
            <p class="text-[10px] text-slate-500 dark:text-slate-400"><?= e(ucfirst($role)); ?></p>
          </div>
        </div>
      </div>
    </div>
  </header>

  <div class="flex w-full gap-0">
    <aside id="sidebar" class="sticky top-14 hidden h-[calc(100vh-3.5rem)] shrink-0 border-r border-slate-200 bg-white p-2 transition-width duration-300 dark:border-slate-800 dark:bg-slate-900 md:block w-64" aria-label="Navegacion principal">
      <nav class="flex h-full flex-col">
        <ul id="navList" class="flex-1 space-y-1"></ul>
        <div class="space-y-1 pt-1">
          <button class="side-item"><i data-lucide="settings" class="icon"></i><span class="label">Configuracion</span></button>
          <button class="side-item"><i data-lucide="help-circle" class="icon"></i><span class="label">Ayuda</span></button>
          <button class="side-item" data-action="logout"><i data-lucide="log-out" class="icon"></i><span class="label">Salir</span></button>
        </div>
      </nav>
    </aside>

    <main class="min-h-[calc(100vh-3.5rem)] flex-1 p-3 sm:p-6">
      <div class="mb-4 flex items-center justify-between">
        <div>
          <h1 id="pageTitle" class="text-xl font-semibold sm:text-2xl">Resumen</h1>
          <p id="pageSubtitle" class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Estado general, proximos vencimientos y actividad reciente</p>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <div class="card"></div>
        <div class="card"></div>
        <div class="card"></div>
        <div class="card"></div>
        <div class="card"></div>
        <div class="card"></div>
      </div>
    </main>
  </div>

  <form id="logoutForm" method="post" action="<?= e(url('/logout')); ?>" class="hidden"></form>

  <script>
    const addSideItemStyles = () => {
      document.querySelectorAll('.side-item').forEach(btn => {
        btn.classList.add('flex', 'w-full', 'items-center', 'gap-3', 'rounded-xl', 'px-3', 'py-2.5', 'text-left', 'text-[13px]', 'text-slate-600', 'hover:bg-slate-100', 'dark:text-slate-300', 'dark:hover:bg-slate-800');
        const icon = btn.querySelector('.icon');
        if (icon) {
          icon.classList.add('h-5', 'w-5', 'flex-none');
        }
      });
    };

    const NAV_ITEMS = [
      { id: 'dashboard', label: 'Dashboard', icon: 'layout-dashboard' },
      { id: 'proyectos', label: 'Gestion de proyectos', icon: 'folder-kanban' },
      { id: 'hitos', label: 'Hitos y entregables', icon: 'flag' },
      { id: 'comentarios', label: 'Comentarios / Feedback', icon: 'message-square' },
      { id: 'progreso', label: 'Visualizacion de progreso', icon: 'trending-up' },
    ];

    const TITLES = {
      dashboard: ['Resumen', 'Estado general, proximos vencimientos y actividad reciente'],
      proyectos: ['Gestion de proyectos', 'Crear, editar y asignar proyectos de titulacion'],
      hitos: ['Hitos y entregables', 'Define fechas limite, sube avances y valida entregables'],
      comentarios: ['Comentarios y feedback', 'Comunicacion entre estudiante y director, menciones y archivos'],
      progreso: ['Visualizacion de progreso', 'Kanban o Gantt simplificado para seguimiento visual'],
    };

    const navList = document.getElementById('navList');
    let active = 'dashboard';

    const renderNav = () => {
      navList.innerHTML = '';
      NAV_ITEMS.forEach(item => {
        const li = document.createElement('li');
        const btn = document.createElement('button');
        btn.className = 'group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-[13px] font-medium tracking-tight transition';
        if (item.id === active) {
          btn.classList.add('bg-indigo-600', 'text-white', 'shadow-sm');
        } else {
          btn.classList.add('text-slate-600', 'hover:bg-slate-100', 'dark:text-slate-300', 'dark:hover:bg-slate-800');
        }
        btn.addEventListener('click', () => setActive(item.id));

        const icon = document.createElement('i');
        icon.setAttribute('data-lucide', item.icon);
        icon.className = 'h-5 w-5 flex-none';

        const label = document.createElement('span');
        label.textContent = item.label;
        label.className = 'label';

        btn.append(icon, label);
        li.append(btn);
        navList.append(li);
      });
      lucide.createIcons();
    };

    function setActive(id) {
      active = id;
      const [title, subtitle] = TITLES[id] || ['', ''];
      document.getElementById('pageTitle').textContent = title;
      document.getElementById('pageSubtitle').textContent = subtitle;
      renderNav();
    }

    const sidebar = document.getElementById('sidebar');
    const btnSidebar = document.getElementById('btnSidebar');
    let sidebarOpen = true;

    btnSidebar.addEventListener('click', () => {
      sidebarOpen = !sidebarOpen;
      sidebar.style.width = sidebarOpen ? '16rem' : '5rem';
      sidebar.querySelectorAll('.label').forEach(el => {
        el.style.display = sidebarOpen ? 'inline' : 'none';
      });
    });

    const root = document.documentElement;
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      root.classList.add('dark');
    }

    document.getElementById('btnLight').addEventListener('click', () => {
      root.classList.remove('dark');
      localStorage.setItem('theme', 'light');
      lucide.createIcons();
    });
    document.getElementById('btnDark').addEventListener('click', () => {
      root.classList.add('dark');
      localStorage.setItem('theme', 'dark');
      lucide.createIcons();
    });

    document.querySelectorAll('.card').forEach(card => {
      card.className = 'rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900';
      card.innerHTML = `
        <div class="h-36 rounded-xl bg-slate-100 dark:bg-slate-800"></div>
        <div class="mt-3 h-3 w-2/3 rounded bg-slate-200 dark:bg-slate-700"></div>
        <div class="mt-2 h-3 w-1/2 rounded bg-slate-200 dark:bg-slate-700"></div>
      `;
    });

    const logoutBtn = document.querySelector('[data-action="logout"]');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', () => {
        document.getElementById('logoutForm').submit();
      });
    }

    renderNav();
    addSideItemStyles();
    lucide.createIcons();
  </script>
</body>
</html>
