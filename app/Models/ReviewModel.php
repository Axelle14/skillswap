<?php
// app/Models/ReviewModel.php
declare(strict_types=1);

namespace App\Models;

class ReviewModel extends BaseModel
{
    protected string $table = 'reviews';

    public function create(int $swapId, int $reviewerId, int $revieweeId, int $rating, string $comment): int|false
    {
        // Prevent duplicate reviews
        if ($this->hasReviewed($swapId, $reviewerId)) return false;

        return $this->insert([
            'swap_id'     => $swapId,
            'reviewer_id' => $reviewerId,
            'reviewee_id' => $revieweeId,
            'rating'      => max(1, min(5, $rating)), // clamp 1-5
            'comment'     => $comment,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function hasReviewed(int $swapId, int $reviewerId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM reviews WHERE swap_id=? AND reviewer_id=?'
        );
        $stmt->execute([$swapId, $reviewerId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function getForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, u.full_name as reviewer_name, s.title as service_title
             FROM reviews r
             JOIN users u ON u.id = r.reviewer_id
             JOIN swap_requests sr ON sr.id = r.swap_id
             JOIN services s ON s.id = sr.service_id
             WHERE r.reviewee_id = ?
             ORDER BY r.created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
