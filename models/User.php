<?php

declare(strict_types=1);

class User
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::connection();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password_hash, role, is_verified, profile_picture, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );
        
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $isVerified = (int) ($data['is_verified'] ?? 0);
        $profilePicture = $data['profile_picture'] ?? null;
        
        $stmt->bind_param(
            'ssssss',
            $data['name'],
            $data['email'],
            $passwordHash,
            $data['role'],
            $isVerified,
            $profilePicture
        );
        
        if ($stmt->execute()) {
            $insertId = $this->db->insert_id;
            $stmt->close();
            return $insertId;
        }
        
        $stmt->close();
        return 0;
    }

    public function all(): array
    {
        $result = $this->db->query('SELECT * FROM users ORDER BY created_at DESC, id DESC');
        return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result ? mysqli_fetch_assoc($result) : null;
        $stmt->close();
        return $user;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result ? mysqli_fetch_assoc($result) : null;
        $stmt->close();
        return $user;
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
            $stmt->bind_param('si', $email, $excludeId);
        } else {
            $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = (bool) mysqli_fetch_assoc($result);
        $stmt->close();
        return $exists;
    }

    public function updateProfile(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET name = ?, email = ?, profile_picture = ? WHERE id = ?'
        );
        $stmt->bind_param('sssi', $data['name'], $data['email'], $data['profile_picture'], $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function updatePassword(int $id, string $newPassword): bool
    {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->bind_param('si', $passwordHash, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function updateName(int $id, string $name): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET name = ? WHERE id = ?');
        $stmt->bind_param('si', $name, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function updateRememberToken(int $id, string $hash): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
        $stmt->bind_param('si', $hash, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function clearRememberToken(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET remember_token = NULL WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function setVerified(int $id, int $isVerified): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET is_verified = ? WHERE id = ?');
        $stmt->bind_param('ii', $isVerified, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function countsByRole(): array
    {
        $result = $this->db->query(
            "SELECT role, COUNT(*) AS total
             FROM users
             GROUP BY role"
        );
        
        $counts = [
            'admin' => 0,
            'scout' => 0,
            'user' => 0,
        ];

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $counts[$row['role']] = (int) $row['total'];
            }
        }

        return $counts;
    }
}
