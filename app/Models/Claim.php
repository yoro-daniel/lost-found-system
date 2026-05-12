<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Claim
{
    public static function all(): array
    {
        return Database::connection()->query(
            'SELECT cl.*, i.title AS item_title, i.tracking_code, u.name AS claimant_user
             FROM claims cl
             JOIN items i ON i.id = cl.item_id
             LEFT JOIN users u ON u.id = cl.user_id
             ORDER BY cl.created_at DESC'
        )->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO claims (item_id, user_id, claimant_name, claimant_email, claimant_phone, proof_text)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['item_id'],
            $data['user_id'],
            $data['claimant_name'],
            $data['claimant_email'],
            $data['claimant_phone'],
            $data['proof_text'],
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public static function review(int $id, string $status, int $reviewerId): ?array
    {
        $stmt = Database::connection()->prepare(
            'UPDATE claims SET status = ?, reviewed_by = ?, reviewed_at = CURRENT_TIMESTAMP WHERE id = ?'
        );
        $stmt->execute([$status, $reviewerId, $id]);

        if ($status === 'approved') {
            Database::connection()->prepare(
                'UPDATE items SET status = "claimed", updated_at = CURRENT_TIMESTAMP
                 WHERE id = (SELECT item_id FROM claims WHERE id = ?)'
            )->execute([$id]);
        }

        $stmt = Database::connection()->prepare(
            'SELECT cl.*, i.title AS item_title, i.tracking_code
             FROM claims cl JOIN items i ON i.id = cl.item_id WHERE cl.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function pendingCount(): int
    {
        return (int) Database::connection()->query('SELECT COUNT(*) FROM claims WHERE status = "pending"')->fetchColumn();
    }
}
