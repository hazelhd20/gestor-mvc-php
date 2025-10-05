<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\Notification;

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
}
