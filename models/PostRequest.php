<?php

declare(strict_types=1);

class PostRequest
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::connection();
    }

    public function create(int $scoutId, array $data): int
    {
        $postData = json_encode($data, JSON_UNESCAPED_UNICODE);
        $originalPostId = $data['original_post_id'] ?: null;
        $imagePath = $data['image_path'] ?? null;
        
        $stmt = $this->db->prepare(
            "INSERT INTO post_requests (scout_id, original_post_id, post_data, image_path, requested_at, status)
             VALUES (?, ?, ?, ?, NOW(), 'pending')"
        );
        $stmt->bind_param('iiss', $scoutId, $originalPostId, $postData, $imagePath);
        
        if ($stmt->execute()) {
            $insertId = $this->db->insert_id;
            $stmt->close();
            return $insertId;
        }
        
        $stmt->close();
        return 0;
    }

    public function update(int $id, int $scoutId, array $data): bool
    {
        $postData = json_encode($data, JSON_UNESCAPED_UNICODE);
        $originalPostId = $data['original_post_id'] ?: null;
        $imagePath = $data['image_path'] ?? null;
        
        $stmt = $this->db->prepare(
            "UPDATE post_requests
             SET original_post_id = ?,
                 post_data = ?,
                 image_path = ?
             WHERE id = ? AND scout_id = ? AND status = 'pending'"
        );
        $stmt->bind_param('issii', $originalPostId, $postData, $imagePath, $id, $scoutId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function byScout(int $scoutId): array
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM post_requests
             WHERE scout_id = ?
             ORDER BY requested_at DESC, id DESC"
        );
        $stmt->bind_param('i', $scoutId);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC) ?: [];
        $stmt->close();
        
        return array_map([$this, 'hydrateRow'], $rows);
    }

    public function findOwned(int $id, int $scoutId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM post_requests
             WHERE id = ? AND scout_id = ?
             LIMIT 1"
        );
        $stmt->bind_param('ii', $id, $scoutId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = mysqli_fetch_assoc($result);
        $stmt->close();
        
        return $row ? $this->hydrateRow($row) : null;
    }

    public function deletePending(int $id, int $scoutId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM post_requests
             WHERE id = ? AND scout_id = ? AND status = 'pending'"
        );
        $stmt->bind_param('ii', $id, $scoutId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function pendingAll(): array
    {
        $result = $this->db->query(
            "SELECT pr.*, u.name AS scout_name
             FROM post_requests pr
             INNER JOIN users u ON u.id = pr.scout_id
             WHERE pr.status = 'pending'
             ORDER BY pr.requested_at ASC"
        );
        $rows = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
        
        return array_map([$this, 'hydrateRow'], $rows);
    }

    public function findPending(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT pr.*, u.name AS scout_name
             FROM post_requests pr
             INNER JOIN users u ON u.id = pr.scout_id
             WHERE pr.id = ? AND pr.status = 'pending'
             LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = mysqli_fetch_assoc($result);
        $stmt->close();
        
        return $row ? $this->hydrateRow($row) : null;
    }

    public function reject(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE post_requests SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM post_requests WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function countPending(): int
    {
        $result = $this->db->query("SELECT COUNT(*) as total FROM post_requests WHERE status = 'pending'");
        $row = mysqli_fetch_assoc($result);
        return $row ? (int) $row['total'] : 0;
    }

    private function hydrateRow(array $row): array
    {
        $postData = json_decode($row['post_data'] ?? '{}', true);
        if (!is_array($postData)) {
            $postData = [];
        }

        return array_merge($row, $postData, [
            'image_path' => $row['image_path'] ?? ($postData['image_path'] ?? null),
            'original_post_id' => $row['original_post_id'] ?? ($postData['original_post_id'] ?? null),
        ]);
    }
}
