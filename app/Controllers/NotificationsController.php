<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\Notification;
use DateTimeImmutable;
use DateTimeInterface;

class NotificationsController extends Controller
{
    private Notification $notifications;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->notifications = new Notification();
    }

    public function index(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->json(['error' => 'No autorizado'], 401);
            return;
        }

        $userId = (int) ($user['id'] ?? 0);
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 15;
        $limit = max(1, min($limit, 50));

        $items = $this->notifications->allForUser($userId, $limit, false);
        $unread = $this->notifications->countUnreadForUser($userId);

        $this->json([
            'notifications' => $items,
            'unread_count' => $unread,
        ]);
    }

    public function stream(): void
    {
        $user = Session::user();
        if (!$user) {
            http_response_code(401);
            header('Content-Type: text/plain; charset=UTF-8');
            echo 'No autorizado';
            return;
        }

        $userId = (int) ($user['id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(400);
            header('Content-Type: text/plain; charset=UTF-8');
            echo 'Usuario inválido';
            return;
        }

        $this->prepareStreamHeaders();

        $lastEventId = $this->resolveLastEventId();
        $connectionStart = time();
        $connectionTimeout = 55;
        $sleepInterval = 2;

        $this->emitRetry(5000);

        if ($lastEventId === 0) {
            $snapshotLimit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
            $snapshotLimit = max(1, min($snapshotLimit, 50));

            $this->emitEvent('init', [
                'notifications' => $this->notifications->allForUser($userId, $snapshotLimit, false),
                'unread_count' => $this->notifications->countUnreadForUser($userId),
            ]);
        }

        $this->flushOutput();
        session_write_close();
        ignore_user_abort(true);
        set_time_limit(0);

        while (!connection_aborted() && (time() - $connectionStart) < $connectionTimeout) {
            $items = $this->notifications->streamForUser($userId, $lastEventId);

            if ($items !== []) {
                foreach ($items as $notification) {
                    $lastEventId = max($lastEventId, (int) ($notification['id'] ?? 0));
                    $this->emitEvent('notification', [
                        'notification' => $notification,
                        'unread_count' => $this->notifications->countUnreadForUser($userId),
                    ], $lastEventId);
                    $this->flushOutput();
                }
            }

            $this->emitEvent('heartbeat', [
                'time' => (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM),
            ]);
            $this->flushOutput();

            if (connection_aborted()) {
                break;
            }

            sleep($sleepInterval);
        }
    }

    public function markAsRead(): void
    {
        $user = Session::user();
        if (!$user) {
            $this->json(['error' => 'No autorizado'], 401);
            return;
        }

        $payload = $this->resolvePayload();
        $userId = (int) ($user['id'] ?? 0);

        if (!empty($payload['mark_all'])) {
            $this->notifications->markAllForUser($userId);
        } else {
            $ids = [];
            if (isset($payload['ids']) && is_array($payload['ids'])) {
                $ids = $payload['ids'];
            } elseif (isset($payload['notification_id'])) {
                $ids = [$payload['notification_id']];
            }

            if ($ids !== []) {
                $this->notifications->markManyAsRead($ids, $userId);
            }
        }

        $this->json([
            'status' => 'ok',
            'unread_count' => $this->notifications->countUnreadForUser($userId),
        ]);
    }

    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolvePayload(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        if ($contentType !== '' && stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            if ($raw !== false && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }

            return [];
        }

        return $_POST ?? [];
    }

    private function prepareStreamHeaders(): void
    {
        header('Content-Type: text/event-stream; charset=UTF-8');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_implicit_flush(true);
    }

    private function emitEvent(string $event, array $payload, ?int $id = null): void
    {
        if ($id !== null) {
            echo 'id: ' . $id . "\n";
        }

        echo 'event: ' . $event . "\n";
        echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";
    }

    private function emitRetry(int $milliseconds): void
    {
        echo 'retry: ' . max(0, $milliseconds) . "\n\n";
    }

    private function flushOutput(): void
    {
        @ob_flush();
        @flush();
    }

    private function resolveLastEventId(): int
    {
        $headers = [
            $_SERVER['HTTP_LAST_EVENT_ID'] ?? null,
            $_GET['lastEventId'] ?? null,
        ];

        foreach ($headers as $value) {
            if ($value === null) {
                continue;
            }

            $id = (int) $value;
            if ($id > 0) {
                return $id;
            }
        }

        return 0;
    }
}
