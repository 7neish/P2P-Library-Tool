<?php
require_once __DIR__ . '/includes/Auth.php';
require_once __DIR__ . '/controllers/logout.php';
if (Auth::check()) {
    header('Location: /views/tools/list.php');
} else {
    header('Location:  /views/auth/login.php');
}
exit;