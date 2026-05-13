<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Activity
{
    public static function recent(int $limit = 10): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT a.*, u.name AS user_name
             FROM activity_logs a
             LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.created_at DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function smsLogs(int $limit = 10): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM sms_logs ORDER BY created_at DESC LIMIT ?');
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
