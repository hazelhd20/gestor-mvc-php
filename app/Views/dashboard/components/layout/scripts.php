<script>
  (function () {
    const ACTIVE_CLASSES = ['bg-indigo-600', 'text-white', 'shadow-sm'];
    const INACTIVE_CLASSES = ['text-slate-600', 'hover:bg-slate-100', 'dark:text-slate-300', 'dark:hover:bg-slate-800'];
    let active = <?= json_encode($activeTab ?? 'dashboard'); ?>;

    const sections = Array.from(document.querySelectorAll('[data-section]'));
    const navButtons = Array.from(document.querySelectorAll('[data-nav-btn]'));
    const sidebar = document.getElementById('sidebar');
    const btnSidebar = document.getElementById('btnSidebar');
    const pageTitle = document.getElementById('pageTitle');
    const pageSubtitle = document.getElementById('pageSubtitle');

    function updateNavButton(button, isActive) {
      ACTIVE_CLASSES.forEach(cls => button.classList.remove(cls));
      INACTIVE_CLASSES.forEach(cls => button.classList.remove(cls));
      const targetClasses = isActive ? ACTIVE_CLASSES : INACTIVE_CLASSES;
      targetClasses.forEach(cls => button.classList.add(cls));
    }

    function updateSections(target) {
      sections.forEach(section => {
        section.classList.toggle('hidden', section.dataset.section !== target);
      });
    }

    function updateHeadings(target) {
      const button = navButtons.find(btn => btn.dataset.navTarget === target);
      if (!button) {
        return;
      }
      const title = button.dataset.navTitle || pageTitle?.textContent || '';
      const subtitle = button.dataset.navSubtitle || pageSubtitle?.textContent || '';
      if (pageTitle) {
        pageTitle.textContent = title;
      }
      if (pageSubtitle) {
        pageSubtitle.textContent = subtitle;
      }
    }

    function updateUrl(target) {
      try {
        const url = new URL(window.location.href);
        url.searchParams.set('tab', target);
        window.history.replaceState({}, '', url);
      } catch (error) {
        /* URL API no soportada */
      }
    }

    function setActive(target) {
      if (!target) {
        return;
      }
      active = target;
      updateSections(active);
      updateHeadings(active);
      updateUrl(active);
      navButtons.forEach(button => {
        const isActive = button.dataset.navTarget === active;
        updateNavButton(button, isActive);
      });
      lucide.createIcons();
    }

    navButtons.forEach(button => {
      button.addEventListener('click', () => {
        setActive(button.dataset.navTarget);
      });
    });

    updateSections(active);
    updateHeadings(active);
    navButtons.forEach(button => {
      const isActive = button.dataset.navTarget === active;
      updateNavButton(button, isActive);
    });

    if (btnSidebar && sidebar) {
      let sidebarOpen = true;
      btnSidebar.addEventListener('click', () => {
        sidebarOpen = !sidebarOpen;
        sidebar.style.width = sidebarOpen ? '16rem' : '5rem';
        sidebar.querySelectorAll('.label').forEach(label => {
          label.style.display = sidebarOpen ? 'inline' : 'none';
        });
      });
    }

    const root = document.documentElement;
    const btnLight = document.getElementById('btnLight');
    const btnDark = document.getElementById('btnDark');
    const savedTheme = localStorage.getItem('theme');

    function applyTheme(theme) {
      if (theme === 'dark') {
        root.classList.add('dark');
      } else {
        root.classList.remove('dark');
      }
      localStorage.setItem('theme', theme);
      lucide.createIcons();
    }

    if (savedTheme) {
      applyTheme(savedTheme);
    } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
      applyTheme('dark');
    }

    btnLight?.addEventListener('click', () => applyTheme('light'));
    btnDark?.addEventListener('click', () => applyTheme('dark'));

    const logoutBtn = document.querySelector('[data-action="logout"]');
    logoutBtn?.addEventListener('click', () => {
      document.getElementById('logoutForm')?.submit();
    });

    document.querySelectorAll('[data-modal]').forEach(trigger => {
      trigger.addEventListener('click', () => {
        const target = document.getElementById(trigger.dataset.modal || '');
        target?.classList.remove('hidden');
      });
    });

    document.querySelectorAll('[data-modal-close]').forEach(button => {
      button.addEventListener('click', () => {
        button.closest('.modal')?.classList.add('hidden');
      });
    });

    document.querySelectorAll('.modal').forEach(modal => {
      const overlay = modal.firstElementChild;
      overlay?.addEventListener('click', event => {
        if (event.target === overlay) {
          modal.classList.add('hidden');
        }
      });
    });

    lucide.createIcons();
  })();
</script>
