<?php
require_once __DIR__ . '/../../includes/DBController.php';
require_once __DIR__ . '/../../models/User.php';

class AuthController {
    public function login($email, $password) {
        $db = DBController::getInstance();
        $email = $db->escape($email);
        $conn = $db->getConnection();
        $res = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' LIMIT 1");
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $user = new User();
            foreach ($row as $k => $v) $user->$k = $v;
            return $user;
        }
        return null;
    }

    public function register($data) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $email    = $db->escape($data['email']);
        $hash     = $db->escape($data['password_hash']);
        $name     = $db->escape($data['full_name']);
        $phone    = $db->escape($data['phone'] ?? '');
        $address  = $db->escape($data['address'] ?? '');
        $lat      = isset($data['latitude']) ? (float)$data['latitude'] : 0;
        $lng      = isset($data['longitude']) ? (float)$data['longitude'] : 0;
        $referral = $db->escape($data['referral_code'] ?? strtoupper(substr(md5($email),0,6)));
        $role     = $db->escape($data['role'] ?? 'MEMBER');
        $sql = "INSERT INTO users (email, password_hash, full_name, phone, address, latitude, longitude, role, referral_code)
                VALUES ('$email','$hash','$name','$phone','$address',$lat,$lng,'$role','$referral')";
        return mysqli_query($conn, $sql);
    }
}