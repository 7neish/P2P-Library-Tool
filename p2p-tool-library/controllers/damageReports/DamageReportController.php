
<?php
require_once __DIR__ . '/../../includes/DBController.php';
require_once __DIR__ . '/../../models/DamageReport.php';

class DamageReportController {
    public function create($data) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $b = (int)$data['booking_id'];
        $d = $db->escape($data['description']);
        $url = $db->escape($data['photo_evidence_url'] ?? '');
        $cost = (float)($data['estimated_repair_cost'] ?? 0);
        return mysqli_query($conn, "INSERT INTO damage_reports (booking_id, description, photo_evidence_url, estimated_repair_cost)
                                    VALUES ($b, '$d', '$url', $cost)");
    }

    public function findAllPending() {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $res = mysqli_query($conn, "SELECT d.*, b.tool_id, t.tool_name FROM damage_reports d
                                    JOIN bookings b ON d.booking_id=b.booking_id
                                    JOIN tools t ON b.tool_id=t.tool_id
                                    WHERE d.status='PENDING' ORDER BY d.created_at DESC");
        $reports = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $report = new DamageReport();
            foreach ($row as $k => $v) {
                if (property_exists($report, $k)) $report->$k = $v;
            }
            $reports[] = $report;
        }
        return $reports;
    }

    public function updateStatus($id, $status) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $id = (int)$id;
        $st = $db->escape($status);
        return mysqli_query($conn, "UPDATE damage_reports SET status='$st' WHERE report_id=$id");
    }
}