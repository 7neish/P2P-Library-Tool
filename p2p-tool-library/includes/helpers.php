<?php

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}


function haversine_distance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius_km = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earth_radius_km * $c;
}


function set_flash($message, $type = 'success') {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}
function get_flash() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}


function url($path = '') {
    return '/' . ltrim($path, '/');
}
