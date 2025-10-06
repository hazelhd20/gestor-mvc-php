<script>
  (function () {
    const ACTIVE_CLASSES = ['bg-indigo-600', 'text-white', 'shadow-sm'];
    const INACTIVE_CLASSES = ['text-slate-600', 'hover:bg-slate-100', 'dark:text-slate-300', 'dark:hover:bg-slate-800'];
    const INITIAL_NOTIFICATIONS = <?= json_encode($notifications ?? [], JSON_UNESCAPED_UNICODE); ?>;
    const INITIAL_UNREAD_COUNT = <?= (int) ($unreadNotificationCount ?? 0); ?>;
    const NOTIFICATIONS_ENDPOINT = <?= json_encode(url('/notifications')); ?>;
    const NOTIFICATIONS_STREAM_ENDPOINT = <?= json_encode(url('/notifications/stream')); ?>;
    const NOTIFICATIONS_MARK_ENDPOINT = <?= json_encode(url('/notifications/read')); ?>;
    const NOTIFICATIONS_LIMIT = 20;
    const INITIAL_MODAL = <?= json_encode($modalTarget ?? null); ?>;
    let active = <?= json_encode($activeTab ?? 'dashboard'); ?>;
    const SEARCH_ENDPOINT = <?= json_encode(url('/search')); ?>;
    const SEARCH_LIMIT = 5;
    const MIN_SEARCH_LENGTH = 2;

    const searchContainer = document.querySelector('[data-global-search]');
    const searchInput = document.querySelector('[data-global-search-input]');
    const searchPanel = document.querySelector('[data-global-search-panel]');
    const searchList = searchPanel ? searchPanel.querySelector('[data-global-search-list]') : null;
    const searchLoading = searchPanel ? searchPanel.querySelector('[data-global-search-loading]') : null;
    const searchError = searchPanel ? searchPanel.querySelector('[data-global-search-error]') : null;
    const searchEmpty = searchPanel ? searchPanel.querySelector('[data-global-search-empty]') : null;
    const searchGroups = [
      { key: 'projects', label: 'Proyectos', icon: 'briefcase' },
      { key: 'milestones', label: 'Hitos', icon: 'flag' },
      { key: 'feedback', label: 'Comentarios', icon: 'message-circle' },
    ];
    let searchDebounceTimer = null;
    let searchAbortController = null;
    let searchItemsFlat = [];
    let searchActiveIndex = -1;
    let searchTerm = '';

    function isSearchPanelOpen() {
      return Boolean(searchPanel && !searchPanel.classList.contains('hidden'));
    }

    function openSearchPanel() {
      if (!searchPanel) {
        return;
      }
      searchPanel.classList.remove('hidden');
    }

    function closeSearchPanel(shouldBlur = false) {
      if (!searchPanel || searchPanel.classList.contains('hidden')) {
        return;
      }
      searchPanel.classList.add('hidden');
      if (shouldBlur) {
        searchInput?.blur();
      }
      setActiveSearchIndex(-1);
    }

    function setSearchLoading(state) {
      if (!searchLoading) {
        return;
      }
      searchLoading.classList.toggle('hidden', !state);
    }

    function setSearchError(state) {
      if (!searchError) {
        return;
      }
      searchError.classList.toggle('hidden', !state);
    }

    function setSearchEmpty(state) {
      if (!searchEmpty) {
        return;
      }
      if (state) {
        const term = searchTerm.trim();
        searchEmpty.textContent = term !== ''
          ? `No se encontraron coincidencias para “${term}”.`
          : 'No se encontraron coincidencias.';
      }
      searchEmpty.classList.toggle('hidden', !state);
    }

    function clearSearchResults() {
      if (searchList) {
        searchList.innerHTML = '';
      }
      searchItemsFlat = [];
      searchActiveIndex = -1;
    }

    function setActiveSearchIndex(index) {
      searchActiveIndex = index;
      if (!Array.isArray(searchItemsFlat) || searchItemsFlat.length === 0) {
        return;
      }
      searchItemsFlat.forEach((entry, position) => {
        if (!entry || !entry.element) {
          return;
        }
        if (position === searchActiveIndex) {
          entry.element.classList.add('bg-indigo-50', 'dark:bg-slate-800/60');
          entry.element.setAttribute('aria-selected', 'true');
        } else {
          entry.element.classList.remove('bg-indigo-50', 'dark:bg-slate-800/60');
          entry.element.setAttribute('aria-selected', 'false');
        }
      });
    }

    function renderSearchResults(data) {
      if (!searchList) {
        return;
      }
      clearSearchResults();

      let total = 0;
      searchGroups.forEach(group => {
        const items = Array.isArray(data?.[group.key]) ? data[group.key] : [];
        if (items.length === 0) {
          return;
        }
        total += items.length;
        const section = document.createElement('div');
        section.dataset.searchSection = 'true';
        section.className = 'border-b border-slate-100 dark:border-slate-800';

        const heading = document.createElement('p');
        heading.className = 'px-4 pt-3 text-[11px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500';
        heading.textContent = group.label;
        section.appendChild(heading);

        items.forEach(item => {
          const index = searchItemsFlat.length;
          const link = document.createElement('a');
          link.className = 'group flex items-start gap-3 px-4 py-3 text-left text-sm transition hover:bg-indigo-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 dark:hover:bg-slate-800/60';
          link.href = typeof item?.url === 'string' && item.url !== '' ? item.url : '#';
          link.setAttribute('role', 'option');
          link.setAttribute('aria-selected', 'false');

          const iconWrapper = document.createElement('div');
          iconWrapper.className = 'mt-0.5 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-200';
          const icon = document.createElement('i');
          icon.setAttribute('data-lucide', typeof item?.icon === 'string' && item.icon !== '' ? item.icon : group.icon);
          icon.className = 'h-4 w-4';
          iconWrapper.appendChild(icon);
          link.appendChild(iconWrapper);

          const textWrapper = document.createElement('div');
          textWrapper.className = 'min-w-0 flex-1';

          const title = document.createElement('p');
          title.className = 'truncate text-sm font-semibold text-slate-800 dark:text-slate-100';
          title.textContent = typeof item?.title === 'string' && item.title.trim() !== '' ? item.title : 'Sin título';
          textWrapper.appendChild(title);

          if (typeof item?.description === 'string' && item.description.trim() !== '') {
            const description = document.createElement('p');
            description.className = 'mt-0.5 line-clamp-2 text-xs text-slate-500 dark:text-slate-300';
            description.textContent = item.description.trim();
            textWrapper.appendChild(description);
          }

          if (typeof item?.meta === 'string' && item.meta.trim() !== '') {
            const meta = document.createElement('p');
            meta.className = 'mt-1 text-[11px] uppercase tracking-wide text-slate-400 dark:text-slate-500';
            meta.textContent = item.meta.trim();
            textWrapper.appendChild(meta);
          }

          link.appendChild(textWrapper);

          const arrowWrapper = document.createElement('div');
          arrowWrapper.className = 'flex h-9 w-5 flex-shrink-0 items-center justify-center';
          const arrow = document.createElement('i');
          arrow.setAttribute('data-lucide', 'chevron-right');
          arrow.className = 'h-4 w-4 text-slate-300 transition group-hover:text-indigo-500 dark:text-slate-600';
          arrowWrapper.appendChild(arrow);
          link.appendChild(arrowWrapper);

          link.addEventListener('mouseenter', () => {
            setActiveSearchIndex(index);
          });
          link.addEventListener('focus', () => {
            setActiveSearchIndex(index);
          });
          link.addEventListener('click', () => {
            closeSearchPanel();
          });

          section.appendChild(link);
          searchItemsFlat.push({ element: link, item });
        });

        searchList.appendChild(section);
      });

      const sectionElements = searchList ? Array.from(searchList.querySelectorAll('[data-search-section]')) : [];
      sectionElements.forEach((section, index) => {
        if (index === sectionElements.length - 1) {
          section.classList.remove('border-b');
          section.classList.remove('border-slate-100');
          section.classList.remove('dark:border-slate-800');
        }
      });

      setSearchEmpty(total === 0);
      if (total > 0) {
        setActiveSearchIndex(-1);
        lucide.createIcons();
      }
    }

    async function performSearch(term) {
      if (!searchInput) {
        return;
      }

      const controller = new AbortController();
      if (searchAbortController) {
        searchAbortController.abort();
      }
      searchAbortController = controller;

      try {
        const params = new URLSearchParams({ q: term, limit: String(SEARCH_LIMIT) });
        const response = await fetch(`${SEARCH_ENDPOINT}?${params.toString()}`, {
          headers: { Accept: 'application/json' },
          credentials: 'same-origin',
          signal: controller.signal,
        });
        if (!response.ok) {
          throw new Error('Request failed');
        }
        const payload = await response.json();
        if (term !== searchTerm) {
          return;
        }
        const results = {
          projects: Array.isArray(payload?.projects) ? payload.projects : [],
          milestones: Array.isArray(payload?.milestones) ? payload.milestones : [],
          feedback: Array.isArray(payload?.feedback) ? payload.feedback : [],
        };
        setSearchLoading(false);
        setSearchError(false);
        renderSearchResults(results);
        const total = results.projects.length + results.milestones.length + results.feedback.length;
        setSearchEmpty(total === 0);
        openSearchPanel();
      } catch (error) {
        if (error && typeof error === 'object' && 'name' in error && error.name === 'AbortError') {
          return;
        }
        console.error('No fue posible completar la búsqueda', error);
        if (term !== searchTerm) {
          return;
        }
        setSearchLoading(false);
        clearSearchResults();
        setSearchEmpty(false);
        setSearchError(true);
        openSearchPanel();
      } finally {
        if (searchAbortController === controller) {
          searchAbortController = null;
        }
      }
    }

    function handleSearchInput() {
      if (!searchInput) {
        return;
      }
      const value = searchInput.value.trim();
      searchTerm = value;
      if (searchDebounceTimer !== null) {
        window.clearTimeout(searchDebounceTimer);
        searchDebounceTimer = null;
      }

      if (value.length < MIN_SEARCH_LENGTH) {
        if (searchAbortController) {
          searchAbortController.abort();
        }
        setSearchLoading(false);
        setSearchError(false);
        setSearchEmpty(false);
        clearSearchResults();
        closeSearchPanel();
        return;
      }

      setSearchLoading(true);
      setSearchError(false);
      setSearchEmpty(false);
      openSearchPanel();

      searchDebounceTimer = window.setTimeout(() => {
        searchDebounceTimer = null;
        performSearch(value);
      }, 250);
    }

    function handleSearchKeyDown(event) {
      if (!searchInput) {
        return;
      }
      if (event.key === 'ArrowDown') {
        if (searchItemsFlat.length === 0) {
          return;
        }
        event.preventDefault();
        if (!isSearchPanelOpen()) {
          openSearchPanel();
        }
        const nextIndex = searchActiveIndex + 1 >= searchItemsFlat.length ? 0 : searchActiveIndex + 1;
        setActiveSearchIndex(nextIndex);
      } else if (event.key === 'ArrowUp') {
        if (searchItemsFlat.length === 0) {
          return;
        }
        event.preventDefault();
        if (!isSearchPanelOpen()) {
          openSearchPanel();
        }
        const prevIndex = searchActiveIndex - 1 < 0 ? searchItemsFlat.length - 1 : searchActiveIndex - 1;
        setActiveSearchIndex(prevIndex);
      } else if (event.key === 'Enter') {
        if (searchItemsFlat.length === 0) {
          return;
        }
        event.preventDefault();
        const index = searchActiveIndex >= 0 ? searchActiveIndex : 0;
        const entry = searchItemsFlat[index];
        if (entry && entry.item && typeof entry.item.url === 'string' && entry.item.url !== '') {
          closeSearchPanel();
          window.location.href = entry.item.url;
        }
      }
    }

    if (searchInput) {
      searchInput.addEventListener('input', handleSearchInput);
      searchInput.addEventListener('keydown', handleSearchKeyDown);
      searchInput.addEventListener('focus', () => {
        if (searchItemsFlat.length > 0 && searchTerm.length >= MIN_SEARCH_LENGTH) {
          openSearchPanel();
        }
      });
    }

    document.addEventListener('click', event => {
      if (!searchContainer || !searchPanel || searchPanel.classList.contains('hidden')) {
        return;
      }
      const target = event.target instanceof Element ? event.target : null;
      if (!target) {
        return;
      }
      if (searchContainer.contains(target)) {
        return;
      }
      closeSearchPanel();
    });

    const sections = Array.from(document.querySelectorAll('[data-section]'));
    const navButtons = Array.from(document.querySelectorAll('[data-nav-btn]'));
    const sidebar = document.getElementById('sidebar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    const btnSidebar = document.getElementById('btnSidebar');
    const body = document.body;
    const notificationsPanel = document.getElementById('notificationsPanel');
    const notificationsToggle = document.querySelector('[data-notifications-toggle]');
    const notificationsBadge = document.querySelector('[data-notifications-badge]');
    const notificationsList = notificationsPanel ? notificationsPanel.querySelector('[data-notifications-list]') : null;
    const notificationsEmpty = notificationsPanel ? notificationsPanel.querySelector('[data-notifications-empty]') : null;
    const notificationsLoading = notificationsPanel ? notificationsPanel.querySelector('[data-notifications-loading]') : null;
    const notificationsError = notificationsPanel ? notificationsPanel.querySelector('[data-notifications-error]') : null;
    const notificationsMarkAll = notificationsPanel ? notificationsPanel.querySelector('[data-notifications-mark-all]') : null;
    const notificationsClose = notificationsPanel ? notificationsPanel.querySelector('[data-notifications-close]') : null;
    let notifications = Array.isArray(INITIAL_NOTIFICATIONS) ? [...INITIAL_NOTIFICATIONS] : [];
    let unreadNotifications = Number.isFinite(Number(INITIAL_UNREAD_COUNT)) ? Number(INITIAL_UNREAD_COUNT) : 0;
    let notificationsLoaded = notifications.length > 0;
    let notificationsFetching = false;
    const notificationsStreamSupported = typeof window !== 'undefined' && 'EventSource' in window;
    const NOTIFICATIONS_STREAM_RETRY_BASE = 5000;
    const NOTIFICATIONS_STREAM_RETRY_MAX = 60000;
    let notificationsEventSource = null;
    let notificationsReconnectTimer = null;
    let notificationsStreamRetryDelay = NOTIFICATIONS_STREAM_RETRY_BASE;

    function setupAutoDismissAlerts(root = document) {
        const alerts = Array.from(root.querySelectorAll('[data-auto-dismiss]'));
        alerts.forEach(alert => {
            if (alert.dataset.autoDismissBound === 'true') {
          return;
        }
        alert.dataset.autoDismissBound = 'true';
        const delay = Number(alert.dataset.autoDismiss) || 5000;
        window.setTimeout(() => {
          alert.classList.add('transition-opacity', 'duration-500', 'ease-out');
          alert.style.opacity = '0';
          alert.addEventListener(
            'transitionend',
            () => {
              alert.remove();
            },
            { once: true }
          );
        }, delay);
      });
    }

    setupAutoDismissAlerts();
    const mediaQueryDesktop = window.matchMedia('(min-width: 768px)');
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

    function openMobileSidebar() {
      if (!sidebar) {
        return;
      }
      sidebar.classList.add('translate-x-0');
      sidebar.classList.remove('-translate-x-full');
      sidebar.setAttribute('aria-hidden', 'false');
      btnSidebar?.setAttribute('aria-expanded', 'true');
      sidebarBackdrop?.classList.remove('pointer-events-none');
      sidebarBackdrop?.classList.remove('opacity-0');
      sidebarBackdrop?.classList.add('opacity-100');
      body.classList.add('overflow-hidden');
    }

    function closeMobileSidebar() {
      if (!sidebar || mediaQueryDesktop.matches) {
        return;
      }
      sidebar.classList.add('-translate-x-full');
      sidebar.classList.remove('translate-x-0');
      sidebar.setAttribute('aria-hidden', mediaQueryDesktop.matches ? 'false' : 'true');
      btnSidebar?.setAttribute('aria-expanded', 'false');
      sidebarBackdrop?.classList.add('pointer-events-none');
      sidebarBackdrop?.classList.add('opacity-0');
      sidebarBackdrop?.classList.remove('opacity-100');
      body.classList.remove('overflow-hidden');
    }

    function toggleSidebar() {
      if (!sidebar) {
        return;
      }
      if (mediaQueryDesktop.matches) {
        sidebar.classList.toggle('sidebar-collapsed');
        sidebar.setAttribute('aria-hidden', 'false');
        btnSidebar?.setAttribute('aria-expanded', String(!sidebar.classList.contains('sidebar-collapsed')));
      } else if (sidebar.classList.contains('translate-x-0')) {
        closeMobileSidebar();
      } else {
        openMobileSidebar();
      }
    }

    btnSidebar?.addEventListener('click', toggleSidebar);
    sidebarBackdrop?.addEventListener('click', () => closeMobileSidebar());
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape') {
        closeMobileSidebar();
        closeNotificationsPanel();
        closeSearchPanel();
      }
    });

    mediaQueryDesktop.addEventListener('change', event => {
      if (event.matches) {
        sidebar?.classList.remove('sidebar-collapsed');
        sidebar?.classList.add('translate-x-0');
        sidebar?.classList.remove('-translate-x-full');
        sidebar?.setAttribute('aria-hidden', 'false');
        btnSidebar?.setAttribute('aria-expanded', 'true');
        sidebarBackdrop?.classList.add('pointer-events-none');
        sidebarBackdrop?.classList.add('opacity-0');
        sidebarBackdrop?.classList.remove('opacity-100');
        body.classList.remove('overflow-hidden');
      } else {
        sidebar?.classList.remove('translate-x-0');
        sidebar?.classList.add('-translate-x-full');
        sidebar?.setAttribute('aria-hidden', 'true');
        btnSidebar?.setAttribute('aria-expanded', 'false');
        sidebarBackdrop?.classList.add('pointer-events-none');
        sidebarBackdrop?.classList.add('opacity-0');
        sidebarBackdrop?.classList.remove('opacity-100');
      }
    });

    if (mediaQueryDesktop.matches) {
      sidebar?.setAttribute('aria-hidden', 'false');
      btnSidebar?.setAttribute('aria-expanded', 'true');
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

    const projectEditModal = document.getElementById('modalProjectEdit');
    const projectEditForm = projectEditModal?.querySelector('form[data-form="project-edit"]');

    if (projectEditModal && projectEditForm) {
      const projectIdInput = projectEditForm.querySelector('input[name="project_id"]');
      const projectTitleInput = projectEditForm.querySelector('input[name="title"]');
      const projectDescriptionInput = projectEditForm.querySelector('textarea[name="description"]');
      const projectStudentSelect = projectEditForm.querySelector('select[name="student_id"]');
      const projectStartInput = projectEditForm.querySelector('input[name="start_date"]');
      const projectEndInput = projectEditForm.querySelector('input[name="end_date"]');

      document.querySelectorAll('[data-project-edit]').forEach(button => {
        button.addEventListener('click', () => {
          projectIdInput && (projectIdInput.value = button.dataset.projectId || '');
          projectTitleInput && (projectTitleInput.value = button.dataset.projectTitle || '');
          projectDescriptionInput && (projectDescriptionInput.value = button.dataset.projectDescription || '');
          if (projectStudentSelect) {
            projectStudentSelect.value = button.dataset.projectStudent || '';
          }
          projectStartInput && (projectStartInput.value = button.dataset.projectStart || '');
          projectEndInput && (projectEndInput.value = button.dataset.projectEnd || '');
        });
      });
    }

    const milestoneEditModal = document.getElementById('modalMilestoneEdit');
    const milestoneEditForm = milestoneEditModal?.querySelector('form[data-form="milestone-edit"]');

    if (milestoneEditModal && milestoneEditForm) {
      const milestoneIdInput = milestoneEditForm.querySelector('input[name="milestone_id"]');
      const milestoneTitleInput = milestoneEditForm.querySelector('input[name="title"]');
      const milestoneDescriptionInput = milestoneEditForm.querySelector('textarea[name="description"]');
      const milestoneStartInput = milestoneEditForm.querySelector('input[name="start_date"]');
      const milestoneEndInput = milestoneEditForm.querySelector('input[name="end_date"]');

      document.querySelectorAll('[data-milestone-edit]').forEach(button => {
        button.addEventListener('click', () => {
          milestoneIdInput && (milestoneIdInput.value = button.dataset.milestoneId || '');
          milestoneTitleInput && (milestoneTitleInput.value = button.dataset.milestoneTitle || '');
          milestoneDescriptionInput && (milestoneDescriptionInput.value = button.dataset.milestoneDescription || '');
          milestoneStartInput && (milestoneStartInput.value = button.dataset.milestoneStart || '');
          milestoneEndInput && (milestoneEndInput.value = button.dataset.milestoneEnd || '');
        });
      });
    }

    function updateNotificationsBadge() {
      if (!notificationsBadge) {
        return;
      }
      if (unreadNotifications > 0) {
        notificationsBadge.textContent = unreadNotifications > 9 ? '9+' : String(unreadNotifications);
        notificationsBadge.classList.remove('hidden');
      } else {
        notificationsBadge.classList.add('hidden');
      }
      notificationsToggle?.setAttribute('data-notifications-count', String(unreadNotifications));
    }

    function updateNotificationsMarkAllState() {
      if (!notificationsMarkAll) {
        return;
      }
      if (unreadNotifications > 0) {
        notificationsMarkAll.removeAttribute('disabled');
      } else {
        notificationsMarkAll.setAttribute('disabled', 'disabled');
      }
    }

    function updateNotificationsEmptyState() {
      if (!notificationsEmpty) {
        return;
      }
      const hasItems = Array.isArray(notifications) && notifications.length > 0;
      notificationsEmpty.classList.toggle('hidden', hasItems);
    }

    function setNotificationsLoading(state) {
      if (!notificationsLoading) {
        return;
      }
      notificationsLoading.classList.toggle('hidden', !state);
    }

    function setNotificationsError(state) {
      if (!notificationsError) {
        return;
      }
      notificationsError.classList.toggle('hidden', !state);
    }

    function trimNotificationsLength() {
      if (!Array.isArray(notifications)) {
        notifications = [];
        return;
      }

      if (notifications.length > NOTIFICATIONS_LIMIT) {
        notifications = notifications.slice(0, NOTIFICATIONS_LIMIT);
      }
    }

    function applyNotificationsSnapshot(items, unreadCount) {
      if (!Array.isArray(items)) {
        return;
      }

      notifications = [...items];
      trimNotificationsLength();
      notificationsLoaded = notifications.length > 0;

      if (Number.isFinite(Number(unreadCount))) {
        unreadNotifications = Math.max(0, Number(unreadCount));
      } else {
        unreadNotifications = notifications.reduce(
          (count, notification) => count + (isNotificationUnread(notification) ? 1 : 0),
          0
        );
      }

      updateNotificationsBadge();
      updateNotificationsMarkAllState();
      updateNotificationsEmptyState();

      if (
        notificationsList &&
        (notificationsList.children.length === 0 || !notificationsPanel?.classList.contains('hidden'))
      ) {
        renderNotifications(true);
      }
    }

    function upsertNotification(notification) {
      if (!notification || typeof notification.id === 'undefined') {
        return;
      }

      const id = Number(notification.id);
      if (!Number.isFinite(id)) {
        return;
      }

      const existingIndex = Array.isArray(notifications)
        ? notifications.findIndex(item => Number(item?.id) === id)
        : -1;

      if (existingIndex >= 0) {
        notifications[existingIndex] = notification;
      } else {
        notifications.unshift(notification);
      }

      trimNotificationsLength();
      notificationsLoaded = true;
    }

    function isNotificationUnread(notification) {
      if (!notification) {
        return false;
      }
      if (notification.read_at) {
        return false;
      }
      if (typeof notification.is_unread === 'boolean') {
        return notification.is_unread;
      }
      return true;
    }

    function formatNotificationTime(value) {
      if (!value) {
        return 'Hace un momento';
      }
      const date = new Date(value);
      if (Number.isNaN(date.getTime())) {
        return value;
      }
      const now = new Date();
      const diffSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);
      if (diffSeconds < 60) {
        return 'Hace instantes';
      }
      const diffMinutes = Math.floor(diffSeconds / 60);
      if (diffMinutes < 60) {
        return diffMinutes === 1 ? 'Hace 1 minuto' : `Hace ${diffMinutes} minutos`;
      }
      const diffHours = Math.floor(diffMinutes / 60);
      if (diffHours < 24) {
        return diffHours === 1 ? 'Hace 1 hora' : `Hace ${diffHours} horas`;
      }
      const diffDays = Math.floor(diffHours / 24);
      if (diffDays < 7) {
        return diffDays === 1 ? 'Hace 1 dia' : `Hace ${diffDays} dias`;
      }
      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const hours = String(date.getHours()).padStart(2, '0');
      const minutes = String(date.getMinutes()).padStart(2, '0');
      return `${day}/${month}/${date.getFullYear()} ${hours}:${minutes}`;
    }

    function createNotificationElement(notification) {
      const element = document.createElement('li');
      const isUnread = isNotificationUnread(notification);
      const actionUrl = typeof notification?.action_url === 'string' ? notification.action_url : '';
      element.className = 'flex gap-3 px-4 py-3 transition hover:bg-slate-100 dark:hover:bg-slate-800/70' + (isUnread ? ' bg-slate-50 dark:bg-slate-800/60' : '');
      element.dataset.notificationId = String(notification?.id ?? '');
      element.dataset.notificationRead = isUnread ? 'false' : 'true';
      element.dataset.actionUrl = actionUrl;

      const markerWrapper = document.createElement('div');
      markerWrapper.className = 'mt-1 flex h-2 w-2 flex-shrink-0 items-center justify-center';
      const marker = document.createElement('span');
      marker.className = 'inline-block h-2 w-2 rounded-full ' + (isUnread ? 'bg-indigo-500' : 'bg-slate-300 dark:bg-slate-600');
      markerWrapper.appendChild(marker);

      const content = document.createElement('div');
      content.className = 'min-w-0 flex-1';

      const container = actionUrl ? document.createElement('a') : document.createElement('div');
      if (actionUrl) {
        container.href = actionUrl;
        container.className = 'block';
      }
      container.dataset.notificationLink = 'true';

      const title = document.createElement('p');
      title.className = 'truncate text-sm font-semibold text-slate-800 dark:text-slate-100';
      title.textContent = String(notification?.title ?? '');
      container.appendChild(title);

      if (typeof notification?.body === 'string' && notification.body.trim() !== '') {
        const bodyText = document.createElement('p');
        bodyText.className = 'mt-0.5 text-xs text-slate-600 line-clamp-2 dark:text-slate-400';
        bodyText.textContent = notification.body.trim();
        container.appendChild(bodyText);
      }

      const time = document.createElement('p');
      time.className = 'mt-1 text-[11px] uppercase tracking-wide text-slate-400 dark:text-slate-500';
      const formatted = typeof notification?.formatted_time === 'string' && notification.formatted_time !== ''
        ? notification.formatted_time
        : formatNotificationTime(notification?.created_at);
      time.textContent = formatted;
      container.appendChild(time);

      content.appendChild(container);
      element.appendChild(markerWrapper);
      element.appendChild(content);

      return element;
    }

    function renderNotifications(force = false) {
      if (!notificationsList) {
        return;
      }
      if (!force && notificationsList.children.length > 0) {
        updateNotificationsEmptyState();
        updateNotificationsMarkAllState();
        return;
      }
      notificationsList.innerHTML = '';
      const items = Array.isArray(notifications) ? notifications : [];
      items.forEach(notification => {
        notificationsList.appendChild(createNotificationElement(notification));
      });
      updateNotificationsEmptyState();
      updateNotificationsMarkAllState();
      lucide.createIcons();
    }

    function openNotificationsPanel() {
      if (!notificationsPanel) {
        return;
      }
      notificationsPanel.classList.remove('hidden');
      notificationsPanel.setAttribute('aria-hidden', 'false');
      notificationsToggle?.setAttribute('aria-expanded', 'true');
      setNotificationsError(false);
      updateNotificationsEmptyState();
      updateNotificationsMarkAllState();
      if (!notificationsLoaded) {
        fetchNotifications();
      }
    }

    function closeNotificationsPanel() {
      if (!notificationsPanel || notificationsPanel.classList.contains('hidden')) {
        return;
      }
      notificationsPanel.classList.add('hidden');
      notificationsPanel.setAttribute('aria-hidden', 'true');
      notificationsToggle?.setAttribute('aria-expanded', 'false');
    }

    function toggleNotificationsPanel() {
      if (!notificationsPanel) {
        return;
      }
      if (notificationsPanel.classList.contains('hidden')) {
        openNotificationsPanel();
      } else {
        closeNotificationsPanel();
      }
    }

    async function fetchNotifications(force = false) {
      if (notificationsFetching) {
        return;
      }
      if (!force && notificationsLoaded) {
        return;
      }
      notificationsFetching = true;
      setNotificationsError(false);
      setNotificationsLoading(true);

      try {
        const response = await fetch(`${NOTIFICATIONS_ENDPOINT}?limit=${NOTIFICATIONS_LIMIT}`, {
          headers: { Accept: 'application/json' },
          credentials: 'same-origin',
        });
        if (!response.ok) {
          throw new Error('Request failed');
        }
        const payload = await response.json();
        const items = Array.isArray(payload?.notifications) ? payload.notifications : [];
        const unreadCount = Number.isFinite(Number(payload?.unread_count)) ? Number(payload.unread_count) : null;
        applyNotificationsSnapshot(items, unreadCount);
        notificationsLoaded = true;
      } catch (error) {
        console.error('No se pudieron cargar las notificaciones', error);
        setNotificationsError(true);
      } finally {
        setNotificationsLoading(false);
        notificationsFetching = false;
        updateNotificationsEmptyState();
        updateNotificationsMarkAllState();
      }
    }

    function markLocalNotifications(ids) {
      if (!Array.isArray(ids) || ids.length === 0) {
        return;
      }
      const idSet = new Set(ids.map(id => Number(id)).filter(id => Number.isFinite(id) && id > 0));
      if (idSet.size === 0) {
        return;
      }
      const nowIso = new Date().toISOString();
      notifications = notifications.map(notification => {
        const notificationId = Number(notification?.id ?? 0);
        if (!idSet.has(notificationId)) {
          return notification;
        }
        return {
          ...notification,
          read_at: notification?.read_at ?? nowIso,
          is_unread: false,
        };
      });
      idSet.forEach(id => {
        const item = notificationsList?.querySelector(`[data-notification-id="${id}"]`);
        if (item) {
          item.dataset.notificationRead = 'true';
          item.classList.remove('bg-slate-50', 'dark:bg-slate-800/60');
          const marker = item.querySelector('span');
          if (marker) {
            marker.classList.remove('bg-indigo-500');
            marker.classList.add('bg-slate-300', 'dark:bg-slate-600');
          }
        }
      });
      unreadNotifications = notifications.reduce((count, notification) => count + (isNotificationUnread(notification) ? 1 : 0), 0);
      updateNotificationsBadge();
      updateNotificationsMarkAllState();
    }

    async function markNotifications(ids) {
      if (!Array.isArray(ids) || ids.length === 0) {
        return;
      }
      markLocalNotifications(ids);
      try {
        const response = await fetch(NOTIFICATIONS_MARK_ENDPOINT, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
          },
          credentials: 'same-origin',
          body: JSON.stringify({ ids }),
        });
        if (!response.ok) {
          throw new Error('Request failed');
        }
        const payload = await response.json();
        if (typeof payload?.unread_count === 'number') {
          unreadNotifications = Math.max(0, Number(payload.unread_count));
          updateNotificationsBadge();
          updateNotificationsMarkAllState();
        }
      } catch (error) {
        console.error('No fue posible marcar la notificacion como leida', error);
        notificationsLoaded = false;
      }
    }

    async function markAllNotifications() {
      if (!Array.isArray(notifications) || notifications.length === 0) {
        return;
      }
      const ids = notifications
        .map(notification => Number(notification?.id ?? 0))
        .filter(id => Number.isFinite(id) && id > 0);
      if (ids.length === 0) {
        return;
      }
      markLocalNotifications(ids);
      try {
        const response = await fetch(NOTIFICATIONS_MARK_ENDPOINT, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
          },
          credentials: 'same-origin',
          body: JSON.stringify({ mark_all: true }),
        });
        if (!response.ok) {
          throw new Error('Request failed');
        }
        const payload = await response.json();
        if (typeof payload?.unread_count === 'number') {
          unreadNotifications = Math.max(0, Number(payload.unread_count));
        } else {
          unreadNotifications = 0;
        }
        updateNotificationsBadge();
        updateNotificationsMarkAllState();
      } catch (error) {
        console.error('No fue posible marcar todas las notificaciones como leidas', error);
        notificationsLoaded = false;
      }
    }

    function closeNotificationsStream() {
      if (notificationsEventSource) {
        notificationsEventSource.close();
        notificationsEventSource = null;
      }
      if (notificationsReconnectTimer) {
        window.clearTimeout(notificationsReconnectTimer);
        notificationsReconnectTimer = null;
      }
    }

    function scheduleNotificationsStreamReconnect() {
      if (!notificationsStreamSupported) {
        return;
      }
      if (notificationsReconnectTimer) {
        return;
      }
      notificationsReconnectTimer = window.setTimeout(() => {
        notificationsReconnectTimer = null;
        connectNotificationsStream();
      }, notificationsStreamRetryDelay);
      notificationsStreamRetryDelay = Math.min(
        notificationsStreamRetryDelay * 2,
        NOTIFICATIONS_STREAM_RETRY_MAX
      );
    }

    function handleNotificationStreamPayload(payload) {
      if (!payload || typeof payload !== 'object') {
        return;
      }

      if (Array.isArray(payload.notifications)) {
        const unreadCount = Number.isFinite(Number(payload.unread_count))
          ? Number(payload.unread_count)
          : null;
        applyNotificationsSnapshot(payload.notifications, unreadCount);
        notificationsLoaded = true;
        return;
      }

      if (payload.notification) {
        upsertNotification(payload.notification);
        if (Number.isFinite(Number(payload.unread_count))) {
          unreadNotifications = Math.max(0, Number(payload.unread_count));
        } else if (isNotificationUnread(payload.notification)) {
          unreadNotifications += 1;
        }
        updateNotificationsBadge();
        updateNotificationsMarkAllState();
        updateNotificationsEmptyState();
        if (!notificationsPanel?.classList.contains('hidden') || notificationsList?.children.length === 0) {
          renderNotifications(true);
        }
      }
    }

    function connectNotificationsStream() {
      if (!notificationsStreamSupported) {
        return;
      }
      if (notificationsEventSource || typeof NOTIFICATIONS_STREAM_ENDPOINT !== 'string') {
        return;
      }

      try {
        notificationsEventSource = new EventSource(`${NOTIFICATIONS_STREAM_ENDPOINT}?limit=${NOTIFICATIONS_LIMIT}`, {
          withCredentials: true,
        });
      } catch (error) {
        console.error('No fue posible iniciar el stream de notificaciones', error);
        scheduleNotificationsStreamReconnect();
        return;
      }

      notificationsStreamRetryDelay = NOTIFICATIONS_STREAM_RETRY_BASE;

      notificationsEventSource.addEventListener('open', () => {
        notificationsStreamRetryDelay = NOTIFICATIONS_STREAM_RETRY_BASE;
      });

      notificationsEventSource.addEventListener('init', event => {
        try {
          const payload = JSON.parse(event.data);
          handleNotificationStreamPayload(payload);
        } catch (error) {
          console.error('No fue posible interpretar el estado inicial de notificaciones', error);
        }
      });

      notificationsEventSource.addEventListener('notification', event => {
        try {
          const payload = JSON.parse(event.data);
          handleNotificationStreamPayload(payload);
        } catch (error) {
          console.error('No fue posible interpretar una notificacion entrante', error);
        }
      });

      notificationsEventSource.addEventListener('error', event => {
        console.warn('La conexion del stream de notificaciones se interrumpio', event);
        if (notificationsEventSource?.readyState === EventSource.CLOSED) {
          closeNotificationsStream();
        }
        scheduleNotificationsStreamReconnect();
      });
    }

    notificationsToggle?.addEventListener('click', () => {
      toggleNotificationsPanel();
      if (!notificationsPanel?.classList.contains('hidden') && !notificationsLoaded) {
        fetchNotifications();
      }
    });

    notificationsClose?.addEventListener('click', () => {
      closeNotificationsPanel();
    });

    notificationsMarkAll?.addEventListener('click', event => {
      event.preventDefault();
      markAllNotifications();
    });

    notificationsList?.addEventListener('click', event => {
      const target = event.target instanceof Element ? event.target.closest('[data-notification-link]') : null;
      if (!target) {
        return;
      }
      const item = target.closest('[data-notification-id]');
      if (!item) {
        return;
      }
      const id = Number(item.dataset.notificationId || '0');
      const actionUrl = item.dataset.actionUrl || (target instanceof HTMLAnchorElement ? target.href : '');
      if (id > 0) {
        markNotifications([id]);
      }
      if (actionUrl) {
        event.preventDefault();
        closeNotificationsPanel();
        window.location.href = actionUrl;
      }
    });

    document.addEventListener('click', event => {
      if (!notificationsPanel || notificationsPanel.classList.contains('hidden')) {
        return;
      }
      const target = event.target instanceof Element ? event.target : null;
      if (!target) {
        return;
      }
      if (notificationsPanel.contains(target) || notificationsToggle?.contains(target)) {
        return;
      }
      closeNotificationsPanel();
    });

    if (notificationsStreamSupported) {
      connectNotificationsStream();
      window.addEventListener('beforeunload', () => {
        closeNotificationsStream();
      });
    }

    updateNotificationsBadge();
    updateNotificationsMarkAllState();
    updateNotificationsEmptyState();

    if (notificationsList && notificationsList.children.length === 0 && notifications.length > 0) {
      renderNotifications(true);
    }

    if (INITIAL_MODAL) {
      document.getElementById(INITIAL_MODAL)?.classList.remove('hidden');
    }

    lucide.createIcons();
  })();
</script>
