<?php

declare(strict_types=1);

class Wishlist
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::connection();
    }

    public function add(int $userId, int $postId): bool
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO wishlist (user_id, post_id, added_at) VALUES (?, ?, NOW())'
        );
        $stmt->bind_param('ii', $userId, $postId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function remove(int $userId, int $postId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM wishlist WHERE user_id = ? AND post_id = ?'
        );
        $stmt->bind_param('ii', $userId, $postId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function byUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT w.id, w.added_at, p.id AS post_id, p.title, p.country, p.genre, p.cost_level, p.image_path
             FROM wishlist w
             INNER JOIN posts p ON p.id = w.post_id
             WHERE w.user_id = ?
             ORDER BY w.added_at DESC"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function postIdsByUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT post_id FROM wishlist WHERE user_id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $postIds = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $postIds[] = (int) $row['post_id'];
        }
        $stmt->close();
        return $postIds;
    }

    public function exists(int $userId, int $postId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM wishlist WHERE user_id = ? AND post_id = ? LIMIT 1'
        );
        $stmt->bind_param('ii', $userId, $postId);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = (bool) mysqli_fetch_assoc($result);
        $stmt->close();
        return $exists;
    }
}
