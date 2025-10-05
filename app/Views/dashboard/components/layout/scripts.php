<script>
  (function () {
    const ACTIVE_CLASSES = ['bg-indigo-600', 'text-white', 'shadow-sm'];
    const INACTIVE_CLASSES = ['text-slate-600', 'hover:bg-slate-100', 'dark:text-slate-300', 'dark:hover:bg-slate-800'];
    const INITIAL_NOTIFICATIONS = <?= json_encode($notifications ?? [], JSON_UNESCAPED_UNICODE); ?>;
    const INITIAL_UNREAD_COUNT = <?= (int) ($unreadNotificationCount ?? 0); ?>;
    const NOTIFICATIONS_ENDPOINT = <?= json_encode(url('/notifications')); ?>;
    const NOTIFICATIONS_MARK_ENDPOINT = <?= json_encode(url('/notifications/read')); ?>;
    const NOTIFICATIONS_LIMIT = 20;
    const INITIAL_MODAL = <?= json_encode($modalTarget ?? null); ?>;
    let active = <?= json_encode($activeTab ?? 'dashboard'); ?>;

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
        notifications = items;
        notificationsLoaded = true;
        if (typeof payload?.unread_count === 'number') {
          unreadNotifications = Math.max(0, Number(payload.unread_count));
        } else {
          unreadNotifications = items.reduce((count, item) => count + (isNotificationUnread(item) ? 1 : 0), 0);
        }
        renderNotifications(true);
        updateNotificationsBadge();
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
