<?php
// app/Middleware/RateLimiter.php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Database;

class RateLimiter
{
    /**
     * Check if an action is rate-limited.
     * Uses the rate_limits DB table so it works across requests.
     *
     * @param string $key     Unique key e.g. 'login:127.0.0.1'
     * @param int    $maxHits Max allowed hits in the window
     * @param int    $window  Window in seconds
     * @throws \RuntimeException if limit exceeded
     */
    public static function check(string $key, int $maxHits, int $window): void
    {
        $pdo = Database::getInstance();
        $now = time();
        $windowStart = $now - $window;

        // Clean old entries
        $pdo->prepare('DELETE FROM rate_limits WHERE key_name = ? AND created_at < ?')
            ->execute([$key, $windowStart]);

        // Count recent hits
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) as hits FROM rate_limits WHERE key_name = ? AND created_at >= ?'
        );
        $stmt->execute([$key, $windowStart]);
        $hits = (int)$stmt->fetchColumn();

        if ($hits >= $maxHits) {
            $retryAfter = $window - ($now - $windowStart);
            header("Retry-After: {$retryAfter}");
            http_response_code(429);
            throw new \RuntimeException("Too many attempts. Please try again in " . ceil($retryAfter / 60) . " minute(s).");
        }

        // Record this hit
        $pdo->prepare('INSERT INTO rate_limits (key_name, created_at) VALUES (?, ?)')
            ->execute([$key, $now]);
    }

    public static function getClientIP(): string
    {
        // Prefer forwarded IP only if behind a trusted proxy
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }
}
