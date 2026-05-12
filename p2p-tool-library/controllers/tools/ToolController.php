<?php
require_once __DIR__ . '/../../includes/DBController.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../models/Tool.php';

class ToolController {
    public function findAll($filters = []) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        
        $where = ["t.status = 'AVAILABLE'"];
        if (!empty($filters['category_id'])) {
            $cid = (int)$filters['category_id'];
            $where[] = "t.category_id = $cid";
        }
        
        $whereSql = implode(" AND ", $where);
        
        $sql = "SELECT t.*, c.name AS category_name, u.full_name AS owner_name 
                FROM tools t
                LEFT JOIN categories c ON t.category_id = c.category_id
                LEFT JOIN users u ON t.owner_id = u.user_id
                WHERE $whereSql";
                
        $res = mysqli_query($conn, $sql);
        if (!$res) die("SQL Error: " . mysqli_error($conn));
    
        $tools = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $tool = new Tool();
            foreach ($row as $k => $v) {
                if (property_exists($tool, $k)) $tool->$k = $v;
            }
            $tools[] = $tool;
        }
        return $tools;
    }

    public function findById($id) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $id = (int)$id;
        $res = mysqli_query($conn, "SELECT t.*, c.name AS category_name, u.full_name AS owner_name, u.phone AS owner_phone
                                     FROM tools t
                                     LEFT JOIN categories c ON t.category_id = c.category_id
                                     LEFT JOIN users u ON t.owner_id = u.user_id
                                     WHERE t.tool_id = $id LIMIT 1");
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $tool = new Tool();
            foreach ($row as $k => $v) {
                if (property_exists($tool, $k)) $tool->$k = $v;
            }
            return $tool;
        }
        return null;
    }

    public function findByOwner($ownerId) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $oid = (int)$ownerId;
        $res = mysqli_query($conn, "SELECT t.*, c.name AS category_name FROM tools t
                                     LEFT JOIN categories c ON t.category_id = c.category_id
                                     WHERE owner_id=$oid ORDER BY created_at DESC");
        $tools = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $tool = new Tool();
            foreach ($row as $k => $v) {
                if (property_exists($tool, $k)) $tool->$k = $v;
            }
            $tools[] = $tool;
        }
        return $tools;
    }


public function create(Tool $tool) {
    $db = DBController::getInstance();
    $conn = $db->getConnection();
    $oid  = (int)$tool->owner_id;
    $cid  = (int)$tool->category_id;
    $zid  = isset($tool->zone_id) && $tool->zone_id !== 0 ? (int)$tool->zone_id : 'NULL';
    $name = $db->escape($tool->tool_name);
    $desc = $db->escape($tool->description ?? '');
    $sn   = $db->escape($tool->serial_number ?? '');
    $hr   = (float)($tool->hourly_rate ?? 0);
    $dr   = (float)($tool->daily_rate ?? 0);
    $wr   = (float)($tool->weekly_rate ?? 0);
    $dep  = (float)($tool->deposit_amount ?? 0);
    $buf  = (int)($tool->buffer_hours ?? 10);
    $lat  = (float)($tool->latitude ?? 0);
    $lng  = (float)($tool->longitude ?? 0);
    $img  = $db->escape($tool->image_url ?? '');

    $sql = "INSERT INTO `tools` (
        `owner_id`, `category_id`, `zone_id`, `tool_name`, `image_url` , `description`, `serial_number`,
        `hourly_rate`, `daily_rate`, `weekly_rate`, `deposit_amount`, `buffer_hours`, `latitude`, `longitude`
    ) VALUES (
        $oid, $cid, $zid, '$name', '$img' , '$desc', '$sn',
        $hr, $dr, $wr, $dep, $buf, $lat, $lng
    )";

    if (mysqli_query($conn, $sql)) {
        return mysqli_insert_id($conn);
    }
    return false;
}

    public function updateStatus($toolId, $status) {
        $db = DBController::getInstance();
        $id = (int)$toolId;
        $st = $db->escape($status);
        $conn = $db->getConnection();
        return mysqli_query($conn, "UPDATE `tools` SET status='$st' WHERE tool_id=$id");
    }

    public function findNearby($lat, $lng, $radiusKm = 5) {
        $allTools = $this->findAll([]);
        $nearby = [];
        foreach ($allTools as $tool) {
            if (!$tool->latitude || !$tool->longitude) continue;
            $d = haversine_distance($lat, $lng, $tool->latitude, $tool->longitude);
            if ($d <= $radiusKm) {
                $tool->distance_km = round($d, 2);
                $nearby[] = $tool;
            }
        }
        usort($nearby, function($a, $b) { return $a->distance_km <=> $b->distance_km; });
        return $nearby;
    }
    // pricing strategy pattern
    public static function calculatePrice($tool, $hours, $discount_rate = 0) { //rename to rentalcost
        $base = 0;
        $remaining = $hours;
        if ($remaining >= 168) {
            $weeks = floor($remaining / 168);
            $base += $weeks * $tool->weekly_rate;
            $remaining -= $weeks * 168;
        }
        if ($remaining >= 24) {
            $days = floor($remaining / 24);
            $base += $days * $tool->daily_rate;
            $remaining -= $days * 24;
        }
        $base += $remaining * $tool->hourly_rate;
        $discounted = $base - ($base * ($discount_rate / 100));
        return round($discounted, 2);
    }
}