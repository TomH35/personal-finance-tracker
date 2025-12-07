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

    /**
     * Check spending limits for a user and create/update notifications if thresholds are exceeded
     * This method checks spending for each calendar month separately
     * @param bool $allow_popup - If false, popup won't be shown even when threshold is first crossed
     */
    public function checkAndNotifySpendingLimits($user_id, $transaction_date = null, $allow_popup = true)
    {
        try {
            // Get user's active spending limits
            $stmt = $this->db->prepare("
                SELECT limit_id, warning_limit, critical_limit, enabled 
                FROM spending_limits 
                WHERE user_id = :user_id AND enabled = 1
                ORDER BY limit_id DESC
                LIMIT 1
            ");
            $stmt->execute(['user_id' => $user_id]);
            $limit = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$limit) {
                // No active limits, nothing to check
                return ['success' => true, 'message' => 'No active limits to check'];
            }

            $warning_limit = (float)$limit['warning_limit'];
            $critical_limit = (float)$limit['critical_limit'];
            $limit_id = $limit['limit_id'];

            // Use provided transaction date or current date to determine which month to check
            $check_date = $transaction_date ? $transaction_date : date('Y-m-d');
            $date_obj = new DateTime($check_date);
            
            // Get month's date range based on the transaction date
            $month_start = $date_obj->format('Y-m-01');
            $month_end = $date_obj->format('Y-m-t');
            $year_month = $date_obj->format('Y-m');

            // Calculate total expenses for this specific month in USD
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(amount), 0) AS monthly_total
                FROM transactions
                WHERE user_id = :user_id 
                AND type = 'expense'
                AND date >= :start_date
                AND date <= :end_date
            ");
            $stmt->execute([
                'user_id' => $user_id,
                'start_date' => $month_start,
                'end_date' => $month_end
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $monthly_expenses = (float)$result['monthly_total'];

            // Check for existing notifications for THIS SPECIFIC month and limit
            // We search by message content since created_at is always current time
            $month_pattern = $date_obj->format('F Y'); // e.g., "January 2026"
            $stmt = $this->db->prepare("
                SELECT notification_id, type, message 
                FROM notifications 
                WHERE user_id = :user_id 
                AND limit_id = :limit_id
                AND (type = 'warning' OR type = 'critical')
                AND message LIKE :month_pattern
                ORDER BY created_at DESC
            ");
            $stmt->execute([
                'user_id' => $user_id,
                'limit_id' => $limit_id,
                'month_pattern' => '%' . $month_pattern . '%'
            ]);
            $existing_notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Find existing warning and critical notifications
            $existing_warning = null;
            $existing_critical = null;
            $had_any_notification = false;
            foreach ($existing_notifications as $notif) {
                $had_any_notification = true;
                if ($notif['type'] === 'warning' && !$existing_warning) {
                    $existing_warning = $notif;
                } elseif ($notif['type'] === 'critical' && !$existing_critical) {
                    $existing_critical = $notif;
                }
            }

            // Determine what should happen based on current spending
            $should_notify_critical = $monthly_expenses >= $critical_limit;
            $should_notify_warning = $monthly_expenses >= $warning_limit && $monthly_expenses < $critical_limit;
            
            $show_popup = false;
            $notification_type = null;

            // If critical limit is exceeded
            if ($should_notify_critical) {
                $overage = $monthly_expenses - $critical_limit;
                $message = sprintf(
                    "%s\nCritical spending limit exceeded! You have spent $%.2f (over by $%.2f)",
                    $date_obj->format('F Y'),
                    $monthly_expenses,
                    $overage
                );

                if ($existing_critical) {
                    // Update existing critical notification with new amount
                    $stmt = $this->db->prepare("
                        UPDATE notifications 
                        SET message = :message 
                        WHERE notification_id = :notification_id
                    ");
                    $stmt->execute([
                        'message' => $message,
                        'notification_id' => $existing_critical['notification_id']
                    ]);
                    $show_popup = false; // Don't show popup - already have critical notification
                } else {
                    // Create new critical notification
                    $this->createNotification($user_id, 'critical', $message, $limit_id);
                    // Only show popup if allowed and threshold was just crossed
                    $show_popup = $allow_popup;
                    $notification_type = 'critical';
                }
                
                // Remove warning notification if it exists (escalated to critical)
                if ($existing_warning) {
                    $stmt = $this->db->prepare("DELETE FROM notifications WHERE notification_id = :id");
                    $stmt->execute(['id' => $existing_warning['notification_id']]);
                }
            }
            // If warning limit is exceeded (but not critical)
            elseif ($should_notify_warning) {
                $overage = $monthly_expenses - $warning_limit;
                $message = sprintf(
                    "%s\nWarning - Spending limit threshold reached! You have spent $%.2f (over by $%.2f)",
                    $date_obj->format('F Y'),
                    $monthly_expenses,
                    $overage
                );

                if ($existing_warning) {
                    // Update existing warning notification with new amount
                    $stmt = $this->db->prepare("
                        UPDATE notifications 
                        SET message = :message 
                        WHERE notification_id = :notification_id
                    ");
                    $stmt->execute([
                        'message' => $message,
                        'notification_id' => $existing_warning['notification_id']
                    ]);
                    $show_popup = false; // Don't show popup - already have warning notification
                } else {
                    // Create new warning notification
                    $this->createNotification($user_id, 'warning', $message, $limit_id);
                    // Only show popup if allowed and threshold was just crossed
                    $show_popup = $allow_popup;
                    $notification_type = 'warning';
                }
                
                // Remove critical notification if it exists (went back under critical)
                if ($existing_critical) {
                    $stmt = $this->db->prepare("DELETE FROM notifications WHERE notification_id = :id");
                    $stmt->execute(['id' => $existing_critical['notification_id']]);
                }
            } else {
                // Under limits - remove existing notifications since spending is below threshold
                foreach ($existing_notifications as $notif) {
                    $stmt = $this->db->prepare("DELETE FROM notifications WHERE notification_id = :id");
                    $stmt->execute(['id' => $notif['notification_id']]);
                }
            }

            return [
                'success' => true,
                'show_popup' => $show_popup,
                'notification_type' => $notification_type,
                'monthly_expenses' => $monthly_expenses,
                'month' => $year_month
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to check spending limits: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Recalculate spending limits for ALL months that have notifications
     * This is called when user changes limit thresholds
     */
    public function recalculateLimitsForCurrentMonth($user_id, $limit_id)
    {
        try {
            // Get all unique months that have limit notifications for this user
            $stmt = $this->db->prepare("
                SELECT DISTINCT message 
                FROM notifications 
                WHERE user_id = :user_id 
                AND limit_id = :limit_id
                AND (type = 'warning' OR type = 'critical')
            ");
            $stmt->execute([
                'user_id' => $user_id,
                'limit_id' => $limit_id
            ]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Extract months from messages (e.g., "January 2026" from message)
            $months_to_recalculate = [];
            foreach ($notifications as $notif) {
                if (preg_match('/^([A-Za-z]+\s+\d{4})/', $notif['message'], $matches)) {
                    $month_year = $matches[1];
                    if (!in_array($month_year, $months_to_recalculate)) {
                        $months_to_recalculate[] = $month_year;
                    }
                }
            }
            
            // Delete all existing notifications for these months
            foreach ($months_to_recalculate as $month_pattern) {
                $stmt = $this->db->prepare("
                    DELETE FROM notifications 
                    WHERE user_id = :user_id 
                    AND limit_id = :limit_id
                    AND (type = 'warning' OR type = 'critical')
                    AND message LIKE :month_pattern
                ");
                $stmt->execute([
                    'user_id' => $user_id,
                    'limit_id' => $limit_id,
                    'month_pattern' => '%' . $month_pattern . '%'
                ]);
            }
            
            // Recalculate for each month
            $recalculated_months = [];
            foreach ($months_to_recalculate as $month_year) {
                // Parse month/year to get a date in that month
                $date_obj = DateTime::createFromFormat('F Y', $month_year);
                if ($date_obj) {
                    $transaction_date = $date_obj->format('Y-m-15'); // Use middle of month
                    // Don't show popup during recalculation (allow_popup = false)
                    $this->checkAndNotifySpendingLimits($user_id, $transaction_date, false);
                    $recalculated_months[] = $month_year;
                }
            }
            
            return [
                'success' => true,
                'message' => 'Limits recalculated for all months',
                'recalculated_months' => $recalculated_months
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to recalculate limits: ' . $e->getMessage()
            ];
        }
    }
}
