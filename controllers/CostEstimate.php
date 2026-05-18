<?php

declare(strict_types=1);

class CostEstimate
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::connection();
    }

    public function findByPostId(int $postId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM cost_estimates WHERE post_id = ? LIMIT 1'
        );
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $result = $stmt->get_result();
        $estimate = mysqli_fetch_assoc($result);
        $stmt->close();
        return $estimate ?: null;
    }

    public function resolveForPost(int $postId, string $costLevel): array
    {
        $estimate = $this->findByPostId($postId);
        if ($estimate) {
            return [
                'base_cost' => (float) $estimate['base_cost'],
                'currency' => $estimate['currency'],
            ];
        }

        return [
            'base_cost' => $this->defaultCostForLevel($costLevel),
            'currency' => 'USD',
        ];
    }

    public function upsert(int $postId, float $baseCost, string $currency = 'USD'): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO cost_estimates (post_id, base_cost, currency, last_updated)
             VALUES (?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE
                 base_cost = VALUES(base_cost),
                 currency = VALUES(currency),
                 last_updated = NOW()"
        );
        $stmt->bind_param('ids', $postId, $baseCost, $currency);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function defaultCostForLevel(string $costLevel): float
    {
        return match ($costLevel) {
            'low' => 500.0,
            'medium' => 1500.0,
            'high' => 3000.0,
            default => 1000.0,
        };
    }
}
