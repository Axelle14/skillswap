<?php
// app/Models/ServiceModel.php
declare(strict_types=1);

namespace App\Models;

class ServiceModel extends BaseModel
{
    protected string $table = 'services';

    public function create(int $userId, array $data): int
    {
        return $this->insert([
            'user_id'     => $userId,
            'title'       => $data['title'],
            'description' => $data['description'],
            'category'    => $data['category'],
            'credits'     => (int)$data['credits'],
            'is_active'   => 1,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateService(int $serviceId, int $userId, array $data): bool
    {
        // Ensure owner can only edit their own listing
        $stmt = $this->db->prepare(
            'UPDATE services SET title=?, description=?, category=?, credits=?, updated_at=NOW()
             WHERE id=? AND user_id=?'
        );
        return $stmt->execute([
            $data['title'],
            $data['description'],
            $data['category'],
            (int)$data['credits'],
            $serviceId,
            $userId,
        ]);
    }

    public function deleteOwned(int $serviceId, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM services WHERE id=? AND user_id=?');
        return $stmt->execute([$serviceId, $userId]);
    }

    public function browse(string $search = '', string $category = '', int $limit = 12, int $offset = 0): array
    {
        $where   = ['s.is_active = 1'];
        $params  = [];

        if ($search) {
            $where[] = '(s.title LIKE ? OR s.description LIKE ?)';
            $term    = '%' . $search . '%';
            $params  = array_merge($params, [$term, $term]);
        }
        if ($category) {
            $where[]  = 's.category = ?';
            $params[] = $category;
        }

        $whereSQL = implode(' AND ', $where);

        $stmt = $this->db->prepare(
            "SELECT s.*, u.full_name as provider_name, u.subscription_plan,
                    COALESCE(ROUND(AVG(r.rating),1), 0) as avg_rating,
                    COUNT(r.id) as review_count
             FROM services s
             JOIN users u ON u.id = s.user_id
             LEFT JOIN reviews r ON r.reviewee_id = s.user_id
             WHERE {$whereSQL}
             GROUP BY s.id
             ORDER BY u.subscription_plan = 'pro' DESC,
                      u.subscription_plan = 'premium' DESC,
                      s.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countBrowse(string $search = '', string $category = ''): int
    {
        $where  = ['is_active = 1'];
        $params = [];
        if ($search) {
            $where[]  = '(title LIKE ? OR description LIKE ?)';
            $term     = '%' . $search . '%';
            $params   = array_merge($params, [$term, $term]);
        }
        if ($category) {
            $where[]  = 'category = ?';
            $params[] = $category;
        }
        $whereSQL = implode(' AND ', $where);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM services WHERE {$whereSQL}");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function findWithOwner(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, u.full_name as provider_name, u.bio as provider_bio,
                    u.skills as provider_skills, u.subscription_plan
             FROM services s JOIN users u ON u.id = s.user_id
             WHERE s.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM services WHERE user_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
