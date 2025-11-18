<?php
require_once __DIR__ . '/class-db.php';

class RateLimiter
{
    private $pdo;
    private $maxAttempts;
    private $windowSeconds;
    private $banSeconds;

    public function __construct($maxAttempts = 5, $windowSeconds = 60, $banSeconds = 30)
    {
        $db = new Db();
        $this->pdo = $db->getPdo();

        $this->maxAttempts = $maxAttempts;
        $this->windowSeconds = $windowSeconds;
        $this->banSeconds = $banSeconds;
    }

    // Check if currently blocked
    public function isBlocked($ip, $endpoint, $userId = null)
    {
        $query = "
            SELECT blocked_until 
            FROM rate_limits
            WHERE ip = ? AND endpoint = ? AND (user_id = ? OR ? IS NULL)
            ORDER BY blocked_until DESC
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$ip, $endpoint, $userId, $userId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && $row['blocked_until'] !== null && strtotime($row['blocked_until']) > time();
    }

    // Log attempt
    public function registerAttempt($ip, $endpoint, $userId = null, $blockedUntil = null, $identifier = null)
    {
        // Auto-detect user ID if identifier provided
        if ($userId === null && $identifier !== null) {
            $stmt = $this->pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$identifier, $identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) $userId = $user['user_id'];
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO rate_limits (ip, endpoint, user_id, blocked_until)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$ip, $endpoint, $userId, $blockedUntil]);
    }

    // Check window + apply ban if needed
    public function checkLimit($ip, $endpoint, $userId = null, $identifier = null)
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) AS attempts
            FROM rate_limits
            WHERE ip = ? AND endpoint = ?
              AND blocked_until IS NULL
              AND created_at >= (NOW() - INTERVAL ? SECOND)
        ");
        $stmt->execute([$ip, $endpoint, $this->windowSeconds]);
        $attempts = $stmt->fetch(PDO::FETCH_ASSOC)['attempts'];

        if ($attempts >= $this->maxAttempts) {
            $this->applyBan($ip, $endpoint, $userId, $identifier);
            return false;
        }

        return true;
    }

    // Apply ban
    private function applyBan($ip, $endpoint, $userId = null, $identifier = null)
    {
        if ($userId === null && $identifier !== null) {
            $stmt = $this->pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$identifier, $identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) $userId = $user['user_id'];
        }

        $banUntil = date('Y-m-d H:i:s', time() + $this->banSeconds);
        $this->registerAttempt($ip, $endpoint, $userId, $banUntil);
    }
}
