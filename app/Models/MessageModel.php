<?php
// app/Models/MessageModel.php
declare(strict_types=1);

namespace App\Models;

class MessageModel extends BaseModel
{
    protected string $table = 'messages';

    public function send(int $swapId, int $senderId, string $body): int
    {
        return $this->insert([
            'swap_id'    => $swapId,
            'sender_id'  => $senderId,
            'body'       => $body,
            'is_read'    => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getConversation(int $swapId): array
    {
        $stmt = $this->db->prepare(
            'SELECT m.*, u.full_name as sender_name
             FROM messages m JOIN users u ON u.id = m.sender_id
             WHERE m.swap_id = ?
             ORDER BY m.created_at ASC'
        );
        $stmt->execute([$swapId]);
        return $stmt->fetchAll();
    }

    public function markRead(int $swapId, int $userId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE messages SET is_read = 1 WHERE swap_id = ? AND sender_id != ?'
        );
        $stmt->execute([$swapId, $userId]);
    }

    public function getInboxSummary(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT sr.id as swap_id,
                    CASE WHEN sr.requester_id = ? THEN u_pro.full_name ELSE u_req.full_name END as other_name,
                    s.title as service_title,
                    (SELECT body FROM messages WHERE swap_id = sr.id ORDER BY created_at DESC LIMIT 1) as last_message,
                    (SELECT created_at FROM messages WHERE swap_id = sr.id ORDER BY created_at DESC LIMIT 1) as last_at,
                    (SELECT COUNT(*) FROM messages WHERE swap_id = sr.id AND sender_id != ? AND is_read = 0) as unread
             FROM swap_requests sr
             JOIN users u_req ON u_req.id = sr.requester_id
             JOIN users u_pro ON u_pro.id = sr.provider_id
             JOIN services s ON s.id = sr.service_id
             WHERE (sr.requester_id = ? OR sr.provider_id = ?)
               AND sr.status NOT IN ("declined")
             ORDER BY last_at DESC'
        );
        $stmt->execute([$userId, $userId, $userId, $userId]);
        return $stmt->fetchAll();
    }
}
