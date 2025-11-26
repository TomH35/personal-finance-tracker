<?php
require_once __DIR__ . '/class-db.php';

class RateLimiter
{
    private $pdo;
    private $maxAttempts;
    private $windowSeconds;
    private $banSeconds;

    public function __construct($maxAttempts = 5, $windowSeconds = 60, $banSeconds = 60)
    {
        $db = new Db();
        $this->pdo = $db->getPdo();

        $this->maxAttempts = $maxAttempts;
        $this->windowSeconds = $windowSeconds;
        $this->banSeconds = $banSeconds;
    }

    // Check if currently blocked (blocked_until in future)
    public function isBlocked($ip, $endpoint, $userId = null)
    {
        $query = "
            SELECT blocked_until 
            FROM rate_limits
            WHERE ip = ? AND endpoint = ? AND (user_id = ? OR ? IS NULL)
            ORDER BY id DESC
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$ip, $endpoint, $userId, $userId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && $row['blocked_until'] !== null && strtotime($row['blocked_until']) > time();
    }

    // Return latest row (assoc) for ip/endpoint/user
    public function getLatestRecord($ip, $endpoint, $userId = null)
    {
        $query = "
            SELECT id, ip, endpoint, user_id, blocked_until, require_captcha, created_at
            FROM rate_limits
            WHERE ip = ? AND endpoint = ? AND (user_id = ? OR ? IS NULL)
            ORDER BY id DESC
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$ip, $endpoint, $userId, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Check if captcha required based on DB flag on latest record AND active block
    public function requiresCaptcha($ip, $endpoint, $userId = null, $identifier = null)
    {
        $row = $this->getLatestRecord($ip, $endpoint, $userId);

        if (!$row) return false;

        // require captcha only when require_captcha = 1 AND blocked_until is in future
        if (isset($row['require_captcha']) && intval($row['require_captcha']) === 1) {
            if (isset($row['blocked_until']) && $row['blocked_until'] !== null) {
                return strtotime($row['blocked_until']) > time();
            }
        }

        return false;
    }

    // Log attempt - support require_captcha flag and blocked_until
    public function registerAttempt($ip, $endpoint, $userId = null, $blockedUntil = null, $identifier = null, $requireCaptcha = 0)
    {
        // Auto-detect user ID if identifier provided
        if ($userId === null && $identifier !== null) {
            $stmt = $this->pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$identifier, $identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) $userId = $user['user_id'];
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO rate_limits (ip, endpoint, user_id, blocked_until, require_captcha)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$ip, $endpoint, $userId, $blockedUntil, $requireCaptcha]);
    }

    // Count attempts in sliding window; apply ban if needed
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
        $attempts = (int)$stmt->fetch(PDO::FETCH_ASSOC)['attempts'];

        if ($attempts >= $this->maxAttempts) {
            $this->applyBan($ip, $endpoint, $userId, $identifier);
            return false;
        }

        return true;
    }

    // Apply ban: create a record with blocked_until and require_captcha = 1
    private function applyBan($ip, $endpoint, $userId = null, $identifier = null)
    {
        if ($userId === null && $identifier !== null) {
            $stmt = $this->pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$identifier, $identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) $userId = $user['user_id'];
        }

        $banUntil = date('Y-m-d H:i:s', time() + $this->banSeconds);

        // Register a record marking block + require captcha
        $this->registerAttempt(
            $ip,
            $endpoint,
            $userId,
            $banUntil,
            null,
            1 // require_captcha = true
        );
    }

    // Optional: remove require_captcha flag by inserting a new record with require_captcha=0 (or reset DB)
    public function clearRequireCaptcha($ip, $endpoint, $userId = null)
    {
        // Insert a neutral record that resets the flag; no blocked_until
        $this->registerAttempt($ip, $endpoint, $userId, null, null, 0);
    }
}
