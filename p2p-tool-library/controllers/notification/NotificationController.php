<?php
require_once __DIR__ . '/../../includes/DBController.php';
require_once __DIR__ . '/../../models/Notification.php';

class NotificationController {
    public function create($userId, $type, $message, $relatedId = null) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $uid = (int)$userId;
        $t = $db->escape($type);
        $m = $db->escape($message);
        $rid = $relatedId ? (int)$relatedId : 'NULL';
        $sql = "INSERT INTO notifications (user_id, type, message, related_id)
                VALUES ($uid, '$t', '$m', $rid)";
        return mysqli_query($conn, $sql);
    }

    public function getUnreadCount($userId) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $uid = (int)$userId;
        $res = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM notifications WHERE user_id=$uid AND is_read=0");
        $row = mysqli_fetch_assoc($res);
        return (int)$row['cnt'];
    }

    public function getAllForUser($userId) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $uid = (int)$userId;
        $res = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id=$uid ORDER BY created_at DESC LIMIT 50");
        $notifications = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $n = new Notification();
            foreach ($row as $k => $v) if (property_exists($n, $k)) $n->$k = $v;
            $notifications[] = $n;
        }
        return $notifications;
    }

    
    public function markAsRead($id) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $id = (int)$id;
        return mysqli_query($conn, "UPDATE notifications SET is_read=1 WHERE id=$id");
    }

    public function sendDueReminders()
    {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $res = mysqli_query($conn, "SELECT b.*, u.full_name AS borrower_name, t.tool_name
                                    FROM bookings b
                                    JOIN users u ON b.borrower_id = u.user_id
                                    JOIN tools t ON b.tool_id = t.tool_id
                                    WHERE b.status = 'ACTIVE' AND b.end_time < NOW()");
        while ($row = mysqli_fetch_assoc($res)) {
            $message = "you have to return the tool'{$row['tool_name']}' (booking #{$row['booking_id']})";
            
            if (!$this->notificationExists($row['borrower_id'], 'due_reminder', $row['booking_id'])) {
                $this->create($row['borrower_id'], 'due_reminder', $message, $row['booking_id']);
            }
        }
    }

    
    public function sendEscalationReminders($hoursAfterDue = 24)
    {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        
        $res = mysqli_query($conn, "SELECT b.*, u.full_name AS borrower_name, t.tool_name
                                    FROM bookings b
                                    JOIN users u ON b.borrower_id = u.user_id
                                    JOIN tools t ON b.tool_id = t.tool_id
                                    WHERE b.status = 'ACTIVE'
                                      AND b.end_time < NOW() - INTERVAL $hoursAfterDue HOUR");
        while ($row = mysqli_fetch_assoc($res)) {
            $message = "Escalation: You delayed returning the tool '{$row['tool_name']}' by more than {$hoursAfterDue} hours (Booking #{$row['booking_id']})";
            
            if (!$this->notificationExists($row['borrower_id'], 'due_escalation', $row['booking_id'])) {
                $this->create($row['borrower_id'], 'due_escalation', $message, $row['booking_id']);
            }
        }
    }

    
    private function notificationExists($userId, $type, $relatedId = null)
    {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $uid = (int)$userId;
        $t = $db->escape($type);
        $rid = $relatedId ? (int)$relatedId : 'NULL';
        $res = mysqli_query($conn, "SELECT id FROM notifications 
                                    WHERE user_id=$uid AND type='$t' AND related_id=$rid");
        return $res && mysqli_num_rows($res) > 0;
    }
}