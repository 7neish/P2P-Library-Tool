<?php
require_once __DIR__ . '/../../includes/DBController.php';
require_once __DIR__ . '/../../models/User.php';

class UserController {
    public function findByEmail($email) {
        $db = DBController::getInstance();
        $email = $db->escape($email);
        $conn = $db->getConnection();
        $res = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' LIMIT 1");
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $user = new User();
            foreach ($row as $k => $v) $user->$k = $v;
            return $user;
        }
        return null;
    }

    public function findById($id) {
        $db = DBController::getInstance();
        $id = (int)$id;
        $conn = $db->getConnection();
        $res = mysqli_query($conn, "SELECT u.*, t.tier_name, t.discount_rate
                                    FROM users u
                                    LEFT JOIN membership_tiers t ON u.tier_id = t.tier_id
                                    WHERE u.user_id = $id LIMIT 1");
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $user = new User();
            foreach ($row as $k => $v) $user->$k = $v;
            return $user;
        }
        return null;
    }

    public function findAll($role = null) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $where = '';
        if ($role) {
            $role = $db->escape($role);
            $where = "WHERE role = '$role'";
        }
        $res = mysqli_query($conn, "SELECT * FROM users $where ORDER BY user_id DESC");
        $users = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $user = new User();
            foreach ($row as $k => $v) {
                if (property_exists($user, $k)) $user->$k = $v;
            }
            $users[] = $user;
        }
        return $users;
    }

    public function create($data) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $email     = $db->escape($data['email']);
        $hash      = $db->escape($data['password_hash']);
        $name      = $db->escape($data['full_name']);
        $phone     = $db->escape($data['phone'] ?? '');
        $address   = $db->escape($data['address'] ?? '');
        $lat       = isset($data['latitude'])  ? (float)$data['latitude']  : 0;
        $lng       = isset($data['longitude']) ? (float)$data['longitude'] : 0;
        $referral  = $db->escape($data['referral_code'] ?? strtoupper(substr(md5($email),0,6)));
        $role      = $db->escape($data['role'] ?? 'MEMBER');

        $sql = "INSERT INTO users (email, password_hash, full_name, phone, address, latitude, longitude, role, referral_code)
                VALUES ('$email','$hash','$name','$phone','$address',$lat,$lng,'$role','$referral')";
        return mysqli_query($conn, $sql);
    }

    public function updateKycStatus($userId, $status) {
        $db = DBController::getInstance();
        $id = (int)$userId;
        $st = $db->escape($status);
        $conn = $db->getConnection();
        return mysqli_query($conn, "UPDATE users SET kyc_status='$st' WHERE user_id=$id");
    }
     //strategy pattern for trust score calculation
    public function recalculateTrustScore($userId) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $id = (int)$userId;
        $u = $this->findById($id);
        if (!$u) return null;

        $onTime = (int)$u->on_time_return_count;
        $total  = max(1, (int)$u->total_borrow_count);
        $onTimeRate = ($onTime / $total) * 100;

        $r = mysqli_query($conn, "SELECT AVG(rating) avg_r FROM reviews WHERE reviewer_id=$id");
        $avgRating = mysqli_fetch_assoc($r)['avg_r'] ?? 4;
        $ratingScore = ($avgRating / 5) * 100;

        $score = round(($onTimeRate * 0.6) + ($ratingScore * 0.4), 2);
        mysqli_query($conn, "UPDATE users SET current_trust_score=$score WHERE user_id=$id");
        return $score;
    }

    public function suspend($userId, $days) {
        $db = DBController::getInstance();
        $id = (int)$userId;
        $until = date('Y-m-d', strtotime("+$days days"));
        $conn = $db->getConnection();
        return mysqli_query($conn, "UPDATE users SET suspension_end_date='$until' WHERE user_id=$id");
    }

    public function blacklist($userId) {
        $db = DBController::getInstance();
        $id = (int)$userId;
        $conn = $db->getConnection();
        return mysqli_query($conn, "UPDATE users SET is_blacklisted=1 WHERE user_id=$id");
    }

    public function deleteWithEverything($id) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $id = (int)$id;
        mysqli_query($conn, "DELETE FROM bookings WHERE borrower_id = $id OR tool_id IN (SELECT tool_id FROM tools WHERE owner_id = $id)");
        mysqli_query($conn, "DELETE FROM tools WHERE owner_id = $id");
        return mysqli_query($conn, "DELETE FROM users WHERE user_id = $id");
    }

    public function search($query, $role = 'MEMBER') {
        $db = DBController::getInstance();
        $q = $db->escape($query);
        $r = $db->escape($role);
        $conn = $db->getConnection();
        $res = mysqli_query($conn, "SELECT * FROM users 
                WHERE role = '$r' 
                AND (full_name LIKE '%$q%' OR email LIKE '%$q%' OR phone LIKE '%$q%')
                ORDER BY user_id DESC");
        $users = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $user = new User();
            foreach ($row as $k => $v) {
                if (property_exists($user, $k)) $user->$k = $v;
            }
            $users[] = $user;
        }
        return $users;
    }
}