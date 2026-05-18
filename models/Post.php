<?php

declare(strict_types=1);

class Post
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::connection();
    }

    public function approvedLatest(int $limit = 6): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, u.name AS scout_name
             FROM posts p
             INNER JOIN users u ON u.id = p.scout_id
             WHERE p.status = 'approved'
             ORDER BY p.created_at DESC
             LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function approvedAll(): array
    {
        $result = $this->db->query(
            "SELECT p.*, u.name AS scout_name
             FROM posts p
             INNER JOIN users u ON u.id = p.scout_id
             WHERE p.status = 'approved'
             ORDER BY p.created_at DESC"
        );
        return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
    }

    public function getFilterOptions(): array
    {
        $countries = [];
        $result = $this->db->query(
            "SELECT DISTINCT country
             FROM posts
             WHERE status = 'approved'
             ORDER BY country ASC"
        );
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $countries[] = $row['country'];
            }
        }

        $genres = [];
        $result = $this->db->query(
            "SELECT DISTINCT genre
             FROM posts
             WHERE status = 'approved'
             ORDER BY genre ASC"
        );
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $genres[] = $row['genre'];
            }
        }

        return [
            'countries' => $countries,
            'genres' => $genres,
        ];
    }

    public function searchFilter(string $query, string $country, array $genres, string $costLevel): array
    {
        $sql = "SELECT p.*, u.name AS scout_name
                FROM posts p
                INNER JOIN users u ON u.id = p.scout_id
                WHERE p.status = 'approved'";
        
        $types = '';
        $params = [];

        if ($query !== '') {
            $sql .= ' AND (p.title LIKE ? OR p.country LIKE ?)';
            $queryParam = '%' . $query . '%';
            $params[] = $queryParam;
            $params[] = $queryParam;
            $types .= 'ss';
        }

        if ($country !== '') {
            $sql .= ' AND p.country = ?';
            $params[] = $country;
            $types .= 's';
        }

        if ($genres !== []) {
            $placeholders = array_fill(0, count($genres), '?');
            $sql .= ' AND p.genre IN (' . implode(', ', $placeholders) . ')';
            foreach ($genres as $genre) {
                $params[] = $genre;
                $types .= 's';
            }
        }

        if ($costLevel !== '') {
            $sql .= ' AND p.cost_level = ?';
            $params[] = $costLevel;
            $types .= 's';
        }

        $sql .= ' ORDER BY p.created_at DESC';

        $stmt = $this->db->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, u.name AS scout_name
             FROM posts p
             INNER JOIN users u ON u.id = p.scout_id
             WHERE p.id = ?
             LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $post = mysqli_fetch_assoc($result);
        $stmt->close();
        return $post ?: null;
    }

    public function approvedByScout(int $scoutId): array
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM posts
             WHERE scout_id = ? AND status = 'approved'
             ORDER BY created_at DESC"
        );
        $stmt->bind_param('i', $scoutId);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function allForAdmin(): array
    {
        $result = $this->db->query(
            "SELECT p.*, u.name AS scout_name
             FROM posts p
             INNER JOIN users u ON u.id = p.scout_id
             ORDER BY p.updated_at DESC, p.id DESC"
        );
        return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO posts (
                scout_id, title, short_history, country, genre, cost_level,
                travel_medium_info, country_representation, image_path, status, created_at, updated_at
             ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
             )"
        );
        
        $countryRep = $data['country_representation'] ?? null;
        $imagePath = $data['image_path'] ?? null;
        $status = $data['status'] ?? 'approved';
        
        $stmt->bind_param(
            'isssssssss',
            $data['scout_id'],
            $data['title'],
            $data['short_history'],
            $data['country'],
            $data['genre'],
            $data['cost_level'],
            $data['travel_medium_info'],
            $countryRep,
            $imagePath,
            $status
        );

        if ($stmt->execute()) {
            $insertId = $this->db->insert_id;
            $stmt->close();
            return $insertId;
        }
        
        $stmt->close();
        return 0;
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE posts
             SET title = ?,
                 short_history = ?,
                 country = ?,
                 genre = ?,
                 cost_level = ?,
                 travel_medium_info = ?,
                 country_representation = ?,
                 image_path = ?,
                 status = ?,
                 updated_at = NOW()
             WHERE id = ?"
        );

        $countryRep = $data['country_representation'] ?? null;
        $imagePath = $data['image_path'] ?? null;
        
        $stmt->bind_param(
            'sssssssssi',
            $data['title'],
            $data['short_history'],
            $data['country'],
            $data['genre'],
            $data['cost_level'],
            $data['travel_medium_info'],
            $countryRep,
            $imagePath,
            $data['status'],
            $id
        );

        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM posts WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function countAll(): int
    {
        $result = $this->db->query('SELECT COUNT(*) as total FROM posts');
        $row = mysqli_fetch_assoc($result);
        return $row ? (int) $row['total'] : 0;
    }
}
