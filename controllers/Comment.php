<?php

declare(strict_types=1);

class Comment
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::connection();
    }

    public function add(int $postId, int $userId, string $content): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO comments (post_id, user_id, content, created_at)
             VALUES (?, ?, ?, NOW())'
        );
        $stmt->bind_param('iis', $postId, $userId, $content);
        
        if ($stmt->execute()) {
            $insertId = $this->db->insert_id;
            $stmt->close();
            return $insertId;
        }
        
        $stmt->close();
        return 0;
    }

    public function byPost(int $postId): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, u.name AS commenter_name
             FROM comments c
             INNER JOIN users u ON u.id = c.user_id
             WHERE c.post_id = ?
             ORDER BY c.created_at DESC"
        );
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM comments WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $comment = mysqli_fetch_assoc($result);
        $stmt->close();
        return $comment ?: null;
    }

    public function deleteOwn(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM comments WHERE id = ? AND user_id = ?'
        );
        $stmt->bind_param('ii', $id, $userId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function deleteAny(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM comments WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function allWithContext(): array
    {
        $result = $this->db->query(
            "SELECT c.*, p.title AS post_title, u.name AS commenter_name
             FROM comments c
             INNER JOIN posts p ON p.id = c.post_id
             INNER JOIN users u ON u.id = c.user_id
             ORDER BY c.created_at DESC"
        );
        return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
    }

    public function countAll(): int
    {
        $result = $this->db->query('SELECT COUNT(*) as total FROM comments');
        $row = mysqli_fetch_assoc($result);
        return $row ? (int) $row['total'] : 0;
    }
}
