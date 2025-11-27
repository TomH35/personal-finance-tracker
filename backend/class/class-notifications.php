<?php
require_once __DIR__ . '/class-db.php';

class Notifications
{
    private $db;

    public function __construct()
    {
        $database = new Db();
        $this->db = $database->getPdo();
    }

    /**
     * Create a new notification for a user
     */
    public function createNotification($user_id, $type, $message, $limit_id = null)
    {
        try {
            // Validate type
            $validTypes = ['warning', 'critical', 'info'];
            if (!in_array($type, $validTypes)) {
                return [
                    'success' => false,
                    'message' => 'Invalid notification type'
                ];
            }

            $stmt = $this->db->prepare("
                INSERT INTO notifications (user_id, limit_id, type, message, created_at, is_read)
                VALUES (:user_id, :limit_id, :type, :message, NOW(), 0)
            ");
            
            $stmt->execute([
                'user_id' => $user_id,
                'limit_id' => $limit_id,
                'type' => $type,
                'message' => $message
            ]);

            $notification_id = $this->db->lastInsertId();

            return [
                'success' => true,
                'message' => 'Notification created successfully',
                'notification_id' => $notification_id
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to create notification: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get all notifications for a user
     */
    public function getUserNotifications($user_id, $unread_only = false)
    {
        try {
            $sql = "SELECT notification_id, type, message, created_at, is_read 
                    FROM notifications 
                    WHERE user_id = :user_id";
            
            if ($unread_only) {
                $sql .= " AND is_read = 0";
            }
            
            $sql .= " ORDER BY created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'notifications' => $notifications
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve notifications: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($notification_id, $user_id)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE notifications 
                SET is_read = 1 
                WHERE notification_id = :notification_id 
                AND user_id = :user_id
            ");
            
            $stmt->execute([
                'notification_id' => $notification_id,
                'user_id' => $user_id
            ]);

            if ($stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'message' => 'Notification not found'
                ];
            }

            return [
                'success' => true,
                'message' => 'Notification marked as read'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to mark notification as read: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($user_id)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE notifications 
                SET is_read = 1 
                WHERE user_id = :user_id AND is_read = 0
            ");
            
            $stmt->execute(['user_id' => $user_id]);

            return [
                'success' => true,
                'message' => 'All notifications marked as read',
                'count' => $stmt->rowCount()
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to mark all notifications as read: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete a notification
     */
    public function deleteNotification($notification_id, $user_id)
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM notifications 
                WHERE notification_id = :notification_id 
                AND user_id = :user_id
            ");
            
            $stmt->execute([
                'notification_id' => $notification_id,
                'user_id' => $user_id
            ]);

            if ($stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'message' => 'Notification not found'
                ];
            }

            return [
                'success' => true,
                'message' => 'Notification deleted successfully'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete notification: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete all notifications for a user
     */
    public function deleteAllNotifications($user_id)
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM notifications 
                WHERE user_id = :user_id
            ");
            
            $stmt->execute(['user_id' => $user_id]);

            return [
                'success' => true,
                'message' => 'All notifications deleted successfully',
                'count' => $stmt->rowCount()
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete all notifications: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get unread notification count for a user
     */
    public function getUnreadCount($user_id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM notifications 
                WHERE user_id = :user_id AND is_read = 0
            ");
            
            $stmt->execute(['user_id' => $user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'count' => (int)$result['count']
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to get unread count: ' . $e->getMessage()
            ];
        }
    }
}
