<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Item
{
    public static function categories(): array
    {
        return Database::connection()->query('SELECT * FROM categories ORDER BY name')->fetchAll();
    }

    public static function locations(): array
    {
        return Database::connection()->query('SELECT * FROM locations ORDER BY name')->fetchAll();
    }

    public static function search(array $filters): array
    {
        $where = [];
        $params = [];

        if (($filters['q'] ?? '') !== '') {
            $where[] = '(i.title LIKE ? OR i.description LIKE ? OR i.tracking_code LIKE ? OR l.name LIKE ?)';
            $needle = '%' . $filters['q'] . '%';
            array_push($params, $needle, $needle, $needle, $needle);
        }

        foreach (['type', 'status', 'category_id', 'location_id'] as $field) {
            if (($filters[$field] ?? '') !== '') {
                $where[] = 'i.' . $field . ' = ?';
                $params[] = $filters[$field];
            }
        }

        $sql = 'SELECT i.*, c.name AS category_name, l.name AS location_name, u.name AS reporter_name,
                COUNT(cl.id) AS claim_count
            FROM items i
            JOIN categories c ON c.id = i.category_id
            JOIN locations l ON l.id = i.location_id
            JOIN users u ON u.id = i.reporter_id
            LEFT JOIN claims cl ON cl.item_id = i.id'
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '') .
            ' GROUP BY i.id ORDER BY i.created_at DESC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT i.*, c.name AS category_name, l.name AS location_name, u.name AS reporter_name, u.email AS reporter_email
             FROM items i
             JOIN categories c ON c.id = i.category_id
             JOIN locations l ON l.id = i.location_id
             JOIN users u ON u.id = i.reporter_id
             WHERE i.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO items (
                tracking_code, reporter_id, category_id, location_id, type, title, description,
                date_seen, image_path, contact_email, contact_phone, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            self::nextTrackingCode($data['type']),
            $data['reporter_id'],
            $data['category_id'],
            $data['location_id'],
            $data['type'],
            $data['title'],
            $data['description'],
            $data['date_seen'],
            $data['image_path'],
            $data['contact_email'],
            $data['contact_phone'],
            'open',
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE items SET category_id = ?, location_id = ?, type = ?, title = ?, description = ?,
             date_seen = ?, image_path = COALESCE(?, image_path), contact_email = ?, contact_phone = ?,
             status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?'
        );
        $stmt->execute([
            $data['category_id'],
            $data['location_id'],
            $data['type'],
            $data['title'],
            $data['description'],
            $data['date_seen'],
            $data['image_path'] ?: null,
            $data['contact_email'],
            $data['contact_phone'],
            $data['status'],
            $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM items WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function findPotentialMatches(array $item): array
    {
        $opposite = $item['type'] === 'lost' ? 'found' : 'lost';
        $stmt = Database::connection()->prepare(
            'SELECT i.*, u.email AS reporter_email
             FROM items i
             JOIN users u ON u.id = i.reporter_id
             WHERE i.type = ?
               AND i.status IN (\'open\', \'matched\')
               AND i.id <> ?
               AND (i.category_id = ? OR i.location_id = ? OR i.title LIKE ?)
             ORDER BY i.created_at DESC
             LIMIT 5'
        );
        $keyword = '%' . strtok($item['title'], ' ') . '%';
        $stmt->execute([$opposite, $item['id'], $item['category_id'], $item['location_id'], $keyword]);
        return $stmt->fetchAll();
    }

    public static function stats(): array
    {
        return Database::connection()->query(
            'SELECT
                SUM(type = \'lost\') AS lost_total,
                SUM(type = \'found\') AS found_total,
                SUM(status = \'claimed\') AS claimed_total,
                SUM(status = \'open\') AS open_total
             FROM items'
        )->fetch() ?: [];
    }

    private static function nextTrackingCode(string $type): string
    {
        $prefix = $type === 'lost' ? 'LST' : 'FND';
        $stmt = Database::connection()->prepare('SELECT COUNT(*) + 1 AS next_id FROM items WHERE tracking_code LIKE ?');
        $stmt->execute([$prefix . '-' . date('Y') . '-%']);
        return sprintf('%s-%s-%04d', $prefix, date('Y'), (int) $stmt->fetch()['next_id']);
    }
}
