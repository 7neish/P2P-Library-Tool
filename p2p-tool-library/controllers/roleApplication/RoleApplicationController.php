<?php
require_once __DIR__ . '/../../includes/DBController.php';
require_once __DIR__ . '/../../models/RoleApplication.php';
require_once __DIR__ . '/../auth/UserController.php'; 

class RoleApplicationController {
    public function create($data) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $name = $db->escape($data['full_name']);
        $email = $db->escape($data['email']);
        $phone = $db->escape($data['phone'] ?? '');
        $role = $db->escape($data['desired_role']);
        $reason = $db->escape($data['reason'] ?? '');
        $cv = $data['cv_path'] ? $db->escape($data['cv_path']) : 'NULL';
        return mysqli_query($conn, "INSERT INTO role_applications (full_name, email, phone, desired_role, reason, cv_path)
                VALUES ('$name','$email','$phone','$role','$reason', '$cv')");
    }

    public function getAllPending() {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $res = mysqli_query($conn, "SELECT * FROM role_applications WHERE status='PENDING' ORDER BY submitted_at DESC");
        $apps = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $app = new RoleApplication();
            foreach ($row as $k => $v) if (property_exists($app, $k)) $app->$k = $v;
            $apps[] = $app;
        }
        return $apps;
    }

    public function approve($appId) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $app = $this->findById($appId);
        if (!$app) return false;
        $userCtrl = new UserController();
        $ok = $userCtrl->create([
            'email' => $app->email,
            'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
            'full_name' => $app->full_name,
            'phone' => $app->phone,
            'role' => $app->desired_role
        ]);
        if ($ok) {
            return mysqli_query($conn, "UPDATE role_applications SET status='APPROVED' WHERE id=$appId");
        }
        return false;
    }

    public function reject($appId) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        return mysqli_query($conn, "UPDATE role_applications SET status='REJECTED' WHERE id=$appId");
    }

    public function findById($id) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $id = (int)$id;
        $res = mysqli_query($conn, "SELECT * FROM role_applications WHERE id=$id");
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $app = new RoleApplication();
            foreach ($row as $k => $v) if (property_exists($app, $k)) $app->$k = $v;
            return $app;
        }
        return null;
    }
}