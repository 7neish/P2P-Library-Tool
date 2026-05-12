<?php
require_once __DIR__ . '/../../includes/DBController.php';
require_once __DIR__ . '/../../models/Zone.php';

class ZoneController {
    public function findAll() {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $res = mysqli_query($conn, "SELECT z.*, u.full_name AS librarian_name
                                     FROM zones z
                                     LEFT JOIN users u ON z.librarian_id=u.user_id
                                     ORDER BY name");
        $zones = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $zone = new Zone();
            foreach ($row as $k => $v) {
                if (property_exists($zone, $k)) $zone->$k = $v;
            }
            $zones[] = $zone;
        }
        return $zones;
    }
}