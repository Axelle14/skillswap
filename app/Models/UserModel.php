<?php
// app/Models/UserModel.php
declare(strict_types=1);

namespace App\Models;

class UserModel extends BaseModel
{
    protected string $table = 'users';

    public function findByEmail(string $email): array|false
    {
        return $this->findBy('email', strtolower(trim($email)));
    }

    public function findById(int $id): array|false
    {
        return $this->find($id);
    }

    public function create(array $data): int
    {
        return $this->insert([
            'full_name'         => $data['full_name'],
            'email'             => strtolower(trim($data['email'])),
            'password_hash'     => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'skills'            => $data['skills'] ?? '',
            'bio'               => $data['bio'] ?? '',
            'credits'           => 50,          // starter credits
            'role'              => 'member',
            'subscription_plan' => 'free',
            'availability'      => 'available',
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public function updateCredits(int $userId, int $delta): bool
    {
        // Use atomic SQL to prevent race conditions
        $stmt = $this->db->prepare(
            'UPDATE users SET credits = credits + ?, updated_at = NOW() WHERE id = ?'
        );
        return $stmt->execute([$delta, $userId]);
    }

    public function updateProfile(int $id, array $data): bool
    {
        return $this->update($id, [
            'full_name'    => $data['full_name'],
            'bio'          => $data['bio'],
            'skills'       => $data['skills'],
            'availability' => $data['availability'],
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateSubscription(int $id, string $plan): bool
    {
        return $this->update($id, [
            'subscription_plan' => $plan,
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $stmt->execute([strtolower(trim($email))]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function getPublicProfile(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT id, full_name, skills, bio, credits, subscription_plan,
                    availability, created_at,
                    (SELECT ROUND(AVG(rating),1) FROM reviews WHERE reviewee_id = u.id) as avg_rating,
                    (SELECT COUNT(*) FROM swap_requests WHERE (requester_id = u.id OR provider_id = u.id) AND status = "completed") as swaps_done
             FROM users u WHERE id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
