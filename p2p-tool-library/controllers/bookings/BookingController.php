<?php
require_once __DIR__ . '/../../includes/DBController.php';
require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../tools/ToolController.php';      

class BookingController
{
    
    public function findAll($filters = [])
    {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $where = '1=1';
        if (!empty($filters['status'])) {
            $s = $db->escape($filters['status']);
            $where .= " AND b.status='$s'";
        }
        if (!empty($filters['borrower_id'])) {
            $b = (int)$filters['borrower_id'];
            $where .= " AND b.borrower_id=$b";
        }
        $res = mysqli_query($conn, "SELECT b.*, t.tool_name, u.full_name AS borrower_name
                FROM bookings b
                JOIN tools t ON b.tool_id = t.tool_id
                JOIN users u ON b.borrower_id = u.user_id
                WHERE $where
                ORDER BY b.created_at DESC");
        $bookings = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $booking = new Booking();
            foreach ($row as $k => $v) {
                if (property_exists($booking, $k)) $booking->$k = $v;
            }
            $bookings[] = $booking;
        }
        return $bookings;
    }

    public function findById($id)
    {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $id = (int)$id;
        $res = mysqli_query($conn, "SELECT b.*, t.tool_name, t.owner_id, t.deposit_amount AS tool_deposit,
                                           u.full_name AS borrower_name, u.email AS borrower_email
                                    FROM bookings b
                                    JOIN tools t ON b.tool_id=t.tool_id
                                    JOIN users u ON b.borrower_id=u.user_id
                                    WHERE b.booking_id=$id LIMIT 1");
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $booking = new Booking();
            foreach ($row as $k => $v) {
                if (property_exists($booking, $k)) $booking->$k = $v;
            }
            return $booking;
        }
        return null;
    }

    public function hasConflict($toolId, $start, $end)
    {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $tid = (int)$toolId;
        $start = $db->escape($start);
        $end = $db->escape($end);
        $sql = "SELECT COUNT(*) AS c FROM bookings 
                WHERE tool_id = $tid 
                AND status IN ('PENDING', 'CONFIRMED', 'ACTIVE') 
                AND (
                    ('$start' < end_time) AND ('$end' > start_time)
                )";
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($res);
        return (int)$row['c'] > 0;
    }

    
    public function create($data)
    {
        $db = DBController::getInstance();
        $conn = $db->getConnection();

        $tid   = (int)$data['tool_id'];
        $bid   = (int)$data['borrower_id'];
        $start = $db->escape($data['start_time']);
        $end   = $db->escape($data['end_time']);
        $rc    = (float)$data['rental_cost'];
        $dep   = (float)$data['deposit_amount'];
        $tot   = $rc + $dep;                      
        $qr    = strtoupper(bin2hex(random_bytes(8)));

        
        $resUser = mysqli_query($conn, "SELECT wallet_balance FROM users WHERE user_id = $bid");
        if (!$resUser) return false;
        $userRow = mysqli_fetch_assoc($resUser);
        $currentBalance = (float)$userRow['wallet_balance'];

       
        if ($currentBalance < $tot) {
            
            return false;
        }

        
        $sqlDeduct = "UPDATE users SET wallet_balance = wallet_balance - $tot WHERE user_id = $bid";
        if (!mysqli_query($conn, $sqlDeduct)) {
            return false;
        }

        
        $sqlBooking = "INSERT INTO bookings (tool_id, borrower_id, start_time, end_time, rental_cost, deposit_amount, total_price, qr_handover_code, status)
                       VALUES ($tid, $bid, '$start', '$end', $rc, $dep, $tot, '$qr', 'PENDING')";

        if (mysqli_query($conn, $sqlBooking)) {
            $bookingId = mysqli_insert_id($conn);

            
            $sqlUserUpdate = "UPDATE users SET total_borrow_count = total_borrow_count + 1 WHERE user_id = $bid";
            mysqli_query($conn, $sqlUserUpdate);

            return $bookingId;
        }

        
        mysqli_query($conn, "UPDATE users SET wallet_balance = wallet_balance + $tot WHERE user_id = $bid");
        return false;
    }

    
    public function updateStatus($bookingId, $status)
    {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $id = (int)$bookingId;
        $st = $db->escape($status);
        return mysqli_query($conn, "UPDATE bookings SET status='$st' WHERE booking_id=$id");
    }

    
    public function calculatePenalty($bookingId)
    {
        $booking = $this->findById($bookingId);
        if (!$booking || $booking->status != 'COMPLETED' && $booking->status != 'ACTIVE') {
            return 0; 
        }

        
        if (empty($booking->actual_return_time)) {
            return 0;
        }

        
        $toolCtrl = new ToolController();
        $tool = $toolCtrl->findById($booking->tool_id);
        if (!$tool) return 0;

        $endTime = new DateTime($booking->end_time);
        $returnTime = new DateTime($booking->actual_return_time);

        if ($returnTime <= $endTime) {
            return 0; 
        }

        $diff = $endTime->diff($returnTime);
        $daysLate = $diff->days; 

        
        $penalty = $daysLate * $tool->daily_rate;

        
        return min($penalty, $booking->deposit_amount);
    }

    
    public function markReturned($bookingId, $actualReturnTime = null)
    {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $id = (int)$bookingId;

        
        $booking = $this->findById($id);
        if (!$booking || $booking->status != 'ACTIVE') {
            return false;
        }

        
        $returnTime = $actualReturnTime ?? date('Y-m-d H:i:s');
        $escapedReturn = $db->escape($returnTime);

    
        $sqlUpdate = "UPDATE bookings SET actual_return_time='$escapedReturn', status='COMPLETED' WHERE booking_id=$id";
        if (!mysqli_query($conn, $sqlUpdate)) {
            return false;
        }

        
        $updatedBooking = $this->findById($id);
        
        $penalty = $this->calculatePenalty($id); 

        
        $borrowerId = $updatedBooking->borrower_id;
        $ownerId = $updatedBooking->owner_id; 
        $rental = $updatedBooking->rental_cost;
        $deposit = $updatedBooking->deposit_amount;


        $refundToBorrower = $deposit - $penalty;
        $payoutToLender = $rental + $penalty;


        $sqlBorrower = "UPDATE users SET wallet_balance = wallet_balance + $refundToBorrower WHERE user_id = $borrowerId";
        mysqli_query($conn, $sqlBorrower);


        $sqlLender = "UPDATE users SET wallet_balance = wallet_balance + $payoutToLender WHERE user_id = $ownerId";
        mysqli_query($conn, $sqlLender);

        if ($penalty == 0) {
            mysqli_query($conn, "UPDATE users SET on_time_return_count = on_time_return_count + 1 WHERE user_id = $borrowerId");
        }

        return true;
    }

    public function findByUser($userId)
    {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $uid = (int)$userId;
        $sql = "SELECT b.*, t.tool_name 
                FROM bookings b
                JOIN tools t ON b.tool_id = t.tool_id
                WHERE b.borrower_id = $uid
                ORDER BY b.created_at DESC";
        $res = mysqli_query($conn, $sql);
        $bookings = [];
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $booking = new stdClass();
                foreach ($row as $k => $v) {
                    $booking->$k = $v;
                }
                $bookings[] = $booking;
            }
        }
        return $bookings;
    }
}