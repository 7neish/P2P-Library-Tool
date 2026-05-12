<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../../controllers/bookings/BookingController.php';
require_once __DIR__ . '/../../controllers/bookings/BookingActionController.php';

 

$bookingCtrl = new BookingController();
$actionCtrl = new BookingActionController();
$current_file = $_SERVER['PHP_SELF'];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $bid = (int)$_POST['booking_id'];
    $action = $_POST['action'];

    switch ($action) {
        case 'confirm':  $actionCtrl->confirm($bid); break;
        case 'cancel':   $actionCtrl->cancel($bid); break;
        case 'activate': $actionCtrl->activate($bid); break;
        case 'return':   $actionCtrl->markReturned($bid); break;
    }
    header("Location: $current_file?msg=updated");
    exit;
}


$status_filter = $_GET['status'] ?? '';
$bookings = $bookingCtrl->findAll(['status' => $status_filter]);

$page_title = "Admin - Manage Bookings";
require __DIR__ . '/../shared/dashboard_shell_top.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-shield-lock-fill"></i> Manage Bookings</h2>
        <a href="../tools/list.php" class="btn btn-outline-secondary">Dashboard</a>
    </div>

    
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Action completed successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    
    <div class="mb-4">
        <a href="<?= $current_file ?>" class="btn <?= !$status_filter ? 'btn-primary' : 'btn-outline-primary' ?>">All</a>
        <?php 
        $statuses = ['PENDING' => 'warning', 'CONFIRMED' => 'info', 'ACTIVE' => 'success', 'COMPLETED' => 'secondary'];
        foreach ($statuses as $status => $color): ?>
            <a href="?status=<?= $status ?>" class="btn <?= $status_filter === $status ? 'btn-'.$color : 'btn-outline-'.$color ?>">
                <?= ucfirst(strtolower($status)) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($bookings)): ?>
        <div class="alert alert-light border text-center py-5">
            <i class="bi bi-inbox h1 text-muted"></i><br>
            <p class="text-muted">No bookings found for this category.</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($bookings as $booking): ?>
                <div class="col-12 mb-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark">Booking #<?= (int)$booking->booking_id ?></span>
                            <span class="badge bg-<?= $statuses[$booking->status] ?? 'dark' ?>"><?= e($booking->status) ?></span>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Tool</small>
                                    <strong><?= e($booking->tool_name) ?></strong>
                                </div>
                                <div class="col-md-3 border-start">
                                    <small class="text-muted d-block">Borrower</small>
                                    <strong><?= e($booking->borrower_name) ?></strong>
                                </div>
                                <div class="col-md-3 border-start">
                                    <small class="text-muted d-block">Timeline</small>
                                    <small><?= date('M d', strtotime($booking->start_time)) ?> - <?= date('M d', strtotime($booking->end_time)) ?></small>
                                </div>
                                <div class="col-md-3 text-end">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="booking_id" value="<?= (int)$booking->booking_id ?>">
                                        
                                        <?php if ($booking->status === 'PENDING'): ?>
                                            <button name="action" value="confirm" class="btn btn-sm btn-success">Approve</button>
                                            <button name="action" value="cancel" class="btn btn-sm btn-outline-danger">Reject</button>
                                        <?php elseif ($booking->status === 'CONFIRMED'): ?>
                                            <button name="action" value="activate" class="btn btn-sm btn-primary">Handover</button>
                                        <?php elseif ($booking->status === 'ACTIVE'): ?>
                                            <button name="action" value="return" class="btn btn-sm btn-warning">Return</button>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>