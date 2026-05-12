<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../controllers/bookings/BookingController.php';
require_once __DIR__ . '/../../controllers/bookings/BookingActionController.php';
require_once __DIR__ . '/../../controllers/escrow/EscrowController.php';
require_once __DIR__ . '/../../controllers/review/ReviewController.php';
require_once __DIR__ . '/../../controllers/auth/UserController.php';
require_once __DIR__ . '/../../models/Review.php';
Auth::require_login();

$id = (int)($_GET['id'] ?? 0);
$bc = new BookingController();
$booking = $bc->findById($id);
if (!$booking) die('Booking not found');


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $actionCtrl = new BookingActionController();
    $action = $_POST['action'];
    if ($action === 'confirm') {
        $actionCtrl->confirm($id);
        set_flash('Booking confirmed.');
    } elseif ($action === 'activate') {
        $actionCtrl->activate($id);
        set_flash('Booking activated.');
    } elseif ($action === 'return') {
        $actionCtrl->markReturned($id);
        set_flash('Tool returned.');
    } elseif ($action === 'cancel') {
        $actionCtrl->cancel($id);
        set_flash('Booking cancelled.');
    }
    header('Location: /views/bookings/show.php?id=' . $id);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    
    $rev = new Review(); 
    $rev->booking_id           = $id;
    $rev->reviewer_id          = Auth::user()->user_id; 
    $rev->rating               = (int)$_POST['rating'];
    $rev->comment              = $_POST['comment'] ?? '';
    $rev->tool_condition_rating = (int)($_POST['tool_condition_rating'] ?? 5);

   
    $reviewCtrl = new ReviewController();
    if ($reviewCtrl->create($rev)) {
        $userCtrl = new UserController();
        $userCtrl->recalculateTrustScore($booking->borrower_id);
        set_flash('Review submitted.');
    } else {
        set_flash('Failed to submit review.', 'danger');
    }

    header('Location: /views/bookings/show.php?id=' . $id);
    exit;
}

$escrowCtrl = new EscrowController();
$escrows = $escrowCtrl->findByBooking($id);
$reviewCtrl = new ReviewController();
$reviews = $reviewCtrl->findByBooking($id);

$page_title = 'Booking #' . $id;
require __DIR__ . '/../shared/dashboard_shell_top.php';
?>
<h2>Booking #<?= (int)$booking->booking_id ?></h2>
<div class="row">
  <div class="col-md-7">
    <div class="card card-body mb-3">
      <table class="table table-sm">
        <tr><td>Tool</td><td><?= e($booking->tool_name) ?></td></tr>
        <tr><td>Borrower</td><td><?= e($booking->borrower_name) ?></td></tr>
        <tr><td>Start</td><td><?= e($booking->start_time) ?></td></tr>
        <tr><td>End</td><td><?= e($booking->end_time) ?></td></tr>
        <tr><td>Returned</td><td><?= e($booking->actual_return_time ?? '—') ?></td></tr>
        <tr><td>Rental</td><td><?= number_format($booking->rental_cost, 2) ?> EGP</td></tr>
        <tr><td>Deposit</td><td><?= number_format($booking->deposit_amount, 2) ?> EGP</td></tr>
        <tr><td>Total</td><td><strong><?= number_format($booking->total_price, 2) ?> EGP</strong></td></tr>
        <tr><td>Status</td><td><span class="badge bg-primary"><?= e($booking->status) ?></span></td></tr>
      </table>
      <h6 class="mt-3">QR Handover Code</h6>
      <div class="qr-code"><?= e($booking->qr_handover_code) ?></div>
      <small class="text-muted">Borrower shows this code to lender at handover.</small>
    </div>

    

    <?php if ($booking->status === 'COMPLETED'): ?>
      <div class="card card-body">
        <h5>Leave a Review</h5>
        <form method="POST" action="/views/bookings/show.php?id=<?= $id ?>">
          <input type="hidden" name="booking_id" value="<?= (int)$booking->booking_id ?>">
          <div class="mb-2"><label class="form-label">Rating (1-5)</label>
            <select name="rating" class="form-select">
              <?php for ($i=5; $i>=1; $i--): ?><option value="<?= $i ?>"><?= $i ?> stars</option><?php endfor; ?>
            </select></div>
          <div class="mb-2"><label class="form-label">Tool condition (1-5)</label>
            <select name="tool_condition_rating" class="form-select">
              <?php for ($i=5; $i>=1; $i--): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
            </select></div>
          <div class="mb-2"><label class="form-label">Comment</label>
            <textarea name="comment" class="form-control" rows="2"></textarea></div>
          <button class="btn btn-primary btn-sm">Submit Review</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
  <div class="col-md-5">
    <div class="card card-body mb-3">
      <h5>Escrow</h5>
      <?php if (empty($escrows)): ?>
        <p class="text-muted small">No escrow transactions yet.</p>
      <?php else: ?>
        <table class="table table-sm">
          <thead><tr><th>Type</th><th>Amount</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($escrows as $tx): ?>
            <tr>
              <td><?= e($tx->transaction_type) ?></td>
              <td><?= number_format($tx->amount, 2) ?></td>
              <td><span class="badge bg-info text-dark"><?= e($tx->status) ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
    <?php if (!empty($reviews)): ?>
    <div class="card card-body">
      <h5>Reviews</h5>
      <?php foreach ($reviews as $r): ?>
        <p class="mb-1"><strong><?= e($r->full_name) ?></strong> — <?= str_repeat('★', (int)$r->rating) ?></p>
        <p class="small text-muted"><?= e($r->comment) ?></p>
        <hr>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>
