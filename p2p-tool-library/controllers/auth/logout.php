<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
Auth::logout();
set_flash('You have been logged out.');
header('Location: /views/auth/login.php');
exit;