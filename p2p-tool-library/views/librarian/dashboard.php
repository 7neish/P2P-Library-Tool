<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/DBController.php'; 
require_once __DIR__ . '/../../controllers/damageReports/DamageReportController.php';
require_once __DIR__ . '/../../controllers/escrow/EscrowController.php';
Auth::require_role('LIBRARIAN');
// state pattern
$db = DBController::getInstance();
$conn = $db->getConnection();
$active = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM bookings WHERE status='ACTIVE'"))['c'];
$overdue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM bookings WHERE status='ACTIVE' AND end_time < NOW()"))['c'];
$damageCtrl = new DamageReportController();
$disputes = count($damageCtrl->findAllPending());
$escrowCtrl = new EscrowController();
$held = $escrowCtrl->totalHeld();

$page_title = 'Librarian Dashboard';
require __DIR__ . '/../shared/dashboard_shell_top.php';
?>
<h2><i class="bi bi-shield-check"></i> Librarian Dashboard</h2>
<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="dashboard-stat"><h3><?= (int)$active ?></h3><small>Active rentals</small></div></div>
  <div class="col-md-3"><div class="dashboard-stat" style="border-left-color:#dc3545"><h3 class="text-danger"><?= (int)$overdue ?></h3><small>Overdue returns</small></div></div>
  <div class="col-md-3"><div class="dashboard-stat" style="border-left-color:#fd7e14"><h3><?= (int)$disputes ?></h3><small>Pending disputes</small></div></div>
  <div class="col-md-3"><div class="dashboard-stat" style="border-left-color:#198754"><h3><?= number_format($held, 0) ?></h3><small>EGP in escrow</small></div></div>
</div>

<div class="row g-3">
  <div class="col-md-4"><a class="btn btn-outline-primary w-100 p-3" href="/views/librarian/disputes.php"><i class="bi bi-flag"></i> Review Disputes</a></div>
  <div class="col-md-4"><a class="btn btn-outline-primary w-100 p-3" href="/views/librarian/kyc.php"><i class="bi bi-person-check"></i> Approve KYC</a></div>
  <div class="col-md-4"><a class="btn btn-outline-primary w-100 p-3" href="/views/escrow/pending.php"><i class="bi bi-bank"></i> Release Escrow</a></div>
  <div class="col-md-4"><a class="btn btn-outline-primary w-100 p-3" href="/views/librarian/members.php"><i class="bi bi-people"></i> Manage Members</a></div>
  <div class="col-md-4"><a class="btn btn-outline-primary w-100 p-3" href="/views/librarian/bookings.php"><i class="bi bi-people"></i> Manage Bookings</a></div>
</div>
<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>
