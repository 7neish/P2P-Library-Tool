<?php
require_once __DIR__ . '/../../includes/DBController.php';
require_once __DIR__ . '/../../models/Message.php';
require_once __DIR__ . '/../notification/NotificationController.php';   // ← أضفناه هنا

class MessageController {
    public function send($senderId, $receiverId, $content, $bookingId = null) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $s = (int)$senderId;
        $r = (int)$receiverId;
        $b = $bookingId ? (int)$bookingId : 'NULL';
        $enc = $db->escape(base64_encode($content));
        $sql = "INSERT INTO messages (sender_id, receiver_id, booking_id, encrypted_content)
                VALUES ($s, $r, $b, '$enc')";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $messageId = mysqli_insert_id($conn);
            $notifCtrl = new NotificationController();
            $notifCtrl->create($r, 'new_message', 'you have a new message', $messageId);
        }
        return $result;
    }

    public function findConversation($user1, $user2) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $u1 = (int)$user1; $u2 = (int)$user2;
        $res = mysqli_query($conn, "SELECT * FROM messages
                                    WHERE (sender_id=$u1 AND receiver_id=$u2)
                                       OR (sender_id=$u2 AND receiver_id=$u1)
                                    ORDER BY sent_at ASC");
        $messages = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $msg = new Message();
            foreach ($row as $k => $v) {
                if (property_exists($msg, $k)) $msg->$k = $v;
            }
            $msg->decrypted = base64_decode($msg->encrypted_content);
            $messages[] = $msg;
        }
        return $messages;
    }

    public function inboxFor($userId) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $id = (int)$userId;
        $res = mysqli_query($conn, "SELECT m.*, u.full_name AS sender_name FROM messages m
                                    JOIN users u ON m.sender_id=u.user_id
                                    WHERE receiver_id=$id ORDER BY sent_at DESC LIMIT 50");
        $messages = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $msg = new Message();
            foreach ($row as $k => $v) {
                if (property_exists($msg, $k)) $msg->$k = $v;
            }
            $msg->decrypted = base64_decode($msg->encrypted_content);
            $messages[] = $msg;
        }
        return $messages;
    }
}