
<?php
require_once __DIR__ . '/../../includes/DBController.php';
require_once __DIR__ . '/../../models/Maintenance.php';

class MaintenanceController {
    public function findAll() {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $res = mysqli_query($conn, "SELECT m.*, t.tool_name, u.full_name AS technician_name
                                    FROM maintenance_logs m
                                    JOIN tools t ON m.tool_id=t.tool_id
                                    LEFT JOIN users u ON m.technician_id=u.user_id
                                    ORDER BY service_date DESC");
        $logs = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $log = new Maintenance();
            foreach ($row as $k => $v) {
                if (property_exists($log, $k)) $log->$k = $v;
            }
            $logs[] = $log;
        }
        return $logs;
    }

    public function findByTool($toolId) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $id = (int)$toolId;
        $res = mysqli_query($conn, "SELECT * FROM maintenance_logs WHERE tool_id=$id ORDER BY service_date DESC");
        $logs = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $log = new Maintenance();
            foreach ($row as $k => $v) {
                if (property_exists($log, $k)) $log->$k = $v;
            }
            $logs[] = $log;
        }
        return $logs;
    }

    public function create($data) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $t = (int)$data['tool_id'];
        $te = (int)$data['technician_id'];
        $desc = $db->escape($data['task_description']);
        $cost = (float)$data['cost'];
        $hrs  = (int)($data['usage_hours_at_service'] ?? 0);
        $next = !empty($data['next_service_due']) ? "'" . $db->escape($data['next_service_due']) . "'" : 'NULL';
        return mysqli_query($conn, "INSERT INTO maintenance_logs (tool_id, technician_id, task_description, cost, usage_hours_at_service, next_service_due)
                                    VALUES ($t, $te, '$desc', $cost, $hrs, $next)");
    }
}