<?php

declare(strict_types=1);

$notificationItems = [];
if (isset($notifications) && is_array($notifications)) {
    foreach ($notifications as $item) {
        if (is_array($item)) {
            $notificationItems[] = $item;
        }
    }
}

$unreadCount = isset($unreadNotificationCount) ? max(0, (int) $unreadNotificationCount) : 0;
$hasNotifications = $notificationItems !== [];
?>

<div
  id="notificationsPanel"
  class="notifications-panel fixed right-4 top-16 z-50 hidden w-80 max-w-[95vw] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl transition-opacity duration-200 dark:border-slate-800 dark:bg-slate-900"
  role="dialog"
  aria-modal="false"
  aria-labelledby="notificationsPanelTitle"
  data-notifications-panel
>
  <div class="flex items-center justify-between border-b border-slate-200/70 bg-slate-50 px-4 py-3 dark:border-slate-800/70 dark:bg-slate-900/70">
    <div>
      <p id="notificationsPanelTitle" class="text-sm font-semibold text-slate-800 dark:text-slate-100">Notificaciones</p>
      <p class="text-xs text-slate-500 dark:text-slate-400">Actualizaciones de tus proyectos y avances</p>
    </div>
    <div class="flex items-center gap-1">
      <button
        type="button"
        class="rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:cursor-not-allowed disabled:opacity-60 dark:text-indigo-300 dark:hover:bg-indigo-500/10"
        data-notifications-mark-all
        <?= $unreadCount > 0 ? '' : 'disabled'; ?>
      >
        Marcar como leidas
      </button>
      <button
        type="button"
        class="rounded-lg p-1 text-slate-500 transition hover:bg-slate-200/60 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-400 dark:hover:bg-slate-700/80"
        data-notifications-close
        aria-label="Cerrar panel de notificaciones"
      >
        <i data-lucide="x" class="h-4 w-4"></i>
      </button>
    </div>
  </div>

  <div class="max-h-96 overflow-y-auto" data-notifications-container>
    <div data-notifications-loading class="hidden px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">
      Cargando notificaciones...
    </div>
    <div data-notifications-error class="hidden px-4 py-6 text-center text-sm text-rose-600 dark:text-rose-400">
      No pudimos cargar las notificaciones. Intenta de nuevo.
    </div>
    <p
      data-notifications-empty
      class="<?= $hasNotifications ? 'hidden' : ''; ?> px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400"
    >
      Aun no tienes notificaciones.
    </p>
    <ul data-notifications-list class="divide-y divide-slate-100 dark:divide-slate-800/80">
      <?php foreach ($notificationItems as $notification): ?>
        <?php
          $isUnread = ($notification['is_unread'] ?? false) || empty($notification['read_at'] ?? null);
          $actionUrl = isset($notification['action_url']) ? trim((string) $notification['action_url']) : '';
          $hasAction = $actionUrl !== '';
          $wrapperClasses = 'flex gap-3 px-4 py-3 transition hover:bg-slate-100 dark:hover:bg-slate-800/70';
          if ($isUnread) {
              $wrapperClasses .= ' bg-slate-50 dark:bg-slate-800/60';
          }
          $timeLabel = format_dashboard_notification_time($notification['created_at'] ?? null);
          $title = (string) ($notification['title'] ?? '');
          $body = trim((string) ($notification['body'] ?? ''));
        ?>
        <li
          class="<?= e($wrapperClasses); ?>"
          data-notification-id="<?= (int) ($notification['id'] ?? 0); ?>"
          data-notification-read="<?= $isUnread ? 'false' : 'true'; ?>"
          data-action-url="<?= e($actionUrl); ?>"
        >
          <div class="mt-1 flex h-2 w-2 flex-shrink-0 items-center justify-center">
            <?php if ($isUnread): ?>
              <span class="inline-block h-2 w-2 rounded-full bg-indigo-500"></span>
            <?php else: ?>
              <span class="inline-block h-2 w-2 rounded-full bg-slate-300 dark:bg-slate-600"></span>
            <?php endif; ?>
          </div>
          <div class="min-w-0 flex-1">
            <?php if ($hasAction): ?>
              <a href="<?= e($actionUrl); ?>" class="block" data-notification-link>
            <?php else: ?>
              <div data-notification-link>
            <?php endif; ?>
                <p class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">
                  <?= e($title); ?>
                </p>
                <?php if ($body !== ''): ?>
                  <p class="mt-0.5 text-xs text-slate-600 line-clamp-2 dark:text-slate-400">
                    <?= e($body); ?>
                  </p>
                <?php endif; ?>
                <p class="mt-1 text-[11px] uppercase tracking-wide text-slate-400 dark:text-slate-500">
                  <?= e($timeLabel); ?>
                </p>
            <?php if ($hasAction): ?>
              </a>
            <?php else: ?>
              </div>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
