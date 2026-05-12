<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class ActivityLogger
{
    public static function log(string $action, string $description, ?int $userId = null): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO activity_logs (user_id, action, description) VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId ?? (current_user()['id'] ?? null), $action, $description]);
    }
}
