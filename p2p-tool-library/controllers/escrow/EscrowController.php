
<?php
require_once __DIR__ . '/../../includes/DBController.php';
require_once __DIR__ . '/../../models/Escrow.php';

class EscrowController {
    public function hold($bookingId, $amount, $type = 'DEPOSIT') {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $bid = (int)$bookingId;
        $amt = (float)$amount;
        $t = $db->escape($type);
        return mysqli_query($conn, "INSERT INTO escrow_transactions (booking_id, amount, transaction_type, status)
                                    VALUES ($bid, $amt, '$t', 'HELD')");
    }

    public function release($transactionId, $to = 'BORROWER', $notes = '') {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $id = (int)$transactionId;
        $st = ($to === 'LENDER') ? 'RELEASED_TO_LENDER' : 'RELEASED_TO_BORROWER';
        $n = $db->escape($notes);
        return mysqli_query($conn, "UPDATE escrow_transactions SET status='$st', notes='$n' WHERE transaction_id=$id");
    }

    public function findByBooking($bookingId) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $id = (int)$bookingId;
        $res = mysqli_query($conn, "SELECT * FROM escrow_transactions WHERE booking_id=$id");
        $transactions = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $e = new Escrow();
            foreach ($row as $k => $v) {
                if (property_exists($e, $k)) $e->$k = $v;
            }
            $transactions[] = $e;
        }
        return $transactions;
    }

    public function findAllPending() {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $res = mysqli_query($conn, "SELECT e.*, b.tool_id, t.tool_name, u.full_name AS borrower_name
                                    FROM escrow_transactions e
                                    JOIN bookings b ON e.booking_id=b.booking_id
                                    JOIN tools t ON b.tool_id=t.tool_id
                                    JOIN users u ON b.borrower_id=u.user_id
                                    WHERE e.status='HELD' ORDER BY e.created_at DESC");
        $transactions = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $e = new Escrow();
            foreach ($row as $k => $v) {
                if (property_exists($e, $k)) $e->$k = $v;
            }
            $transactions[] = $e;
        }
        return $transactions;
    }

    public function totalHeld() {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $r = mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) AS total FROM escrow_transactions WHERE status='HELD'");
        return (float)mysqli_fetch_assoc($r)['total'];
    }
}