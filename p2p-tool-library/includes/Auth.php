<?php

require_once __DIR__ . '/../models/User.php'; 


class Auth {
    
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    
    public static function check() {
        self::start();
        return isset($_SESSION['user_id']);
    }
    
    
    public static function user() {
        self::start();
        if (!self::check()) return null;
        
        $user = new User(); 
    
        
        $user->user_id   = $_SESSION['user_id'];
        $user->full_name = $_SESSION['full_name'] ?? '';
        $user->email     = $_SESSION['email'] ?? '';
        $user->role      = $_SESSION['role'] ?? 'MEMBER';
    
        return $user; 
    }

    public static function require_login() {
        if (!self::check()) {
            header('Location: /views/auth/login.php');
            exit;
        }
    }

    
    public static function require_role($role) {
        self::require_login();
        if ($_SESSION['role'] !== $role && $_SESSION['role'] !== 'ADMIN') {
            die('Access Denied');
        }
    }

    
    public static function login($user) {
        self::start();
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['role']      = $user['role'];
    }

    
    public static function logout() {
        self::start();
        $_SESSION = [];
        session_destroy();
    }
}
