<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../controllers/tools/ToolController.php';
require_once __DIR__ . '/../../controllers/bookings/BookingController.php';
require_once __DIR__ . '/../../controllers/auth/UserController.php';
require_once __DIR__ . '/../../controllers/escrow/EscrowController.php';
Auth::require_login();

$tool_id = (int)($_GET['tool_id'] ?? $_POST['tool_id'] ?? 0);
$toolCtrl = new ToolController();
$tool = $toolCtrl->findById($tool_id);
if (!$tool) die('Tool not found');

$u = Auth::user();
$userCtrl = new UserController();
$me = $userCtrl->findById($u->user_id);

if ($tool->deposit_amount > 1000 && $me->kyc_status !== 'VERIFIED') {
    set_flash('You must complete KYC verification to book high-value tools.', 'warning');
    header('Location: /views/members/kyc.php');
    exit;
}

$error = '';
$preview = null;
$bookingCtrl = new BookingController();
$escrowCtrl = new EscrowController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start = $_POST['start_time'];
    $end   = $_POST['end_time'];
    $hours = (strtotime($end) - strtotime($start)) / 3600;
    if ($hours <= 0) {
        $error = 'End must be after start.';
    } elseif ($bookingCtrl->hasConflict($tool_id, $start, $end)) {
        $error = 'Conflict: tool already booked (or buffer overlap) in this period.';
    } else {
        $rental = ToolController::calculatePrice($tool, $hours, (float)($me->discount_rate ?? 0));
        if (isset($_POST['confirm'])) {
            $bid = $bookingCtrl->create([
                'tool_id' => $tool_id,
                'borrower_id' => $u->user_id,
                'start_time' => $start,
                'end_time' => $end,
                'rental_cost' => $rental,
                'deposit_amount' => $tool->deposit_amount,
            ]);
            if ($bid) {
                $escrowCtrl->hold($bid, $tool->deposit_amount, 'DEPOSIT');
                $escrowCtrl->hold($bid, $rental, 'RENTAL');
                set_flash('Booking created! Awaiting confirmation.');
                $u->total_borrow_count++;
                header('Location: /views/bookings/show.php?id=' . $bid);
                exit;
            } else {
                $error = 'Failed to create booking.';
            }
        } else {
            $preview = ['hours' => $hours, 'rental' => $rental, 'deposit' => $tool->deposit_amount];
        }
    }
}
$page_title = 'Reserve ' . $tool->tool_name;
require __DIR__ . '/../shared/dashboard_shell_top.php';
?>
<h2><i class="bi bi-calendar-plus"></i> Reserve: <?= e($tool->tool_name) ?></h2>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<div class="row">
  <div class="col-md-7">
    <form method="POST" class="card card-body">
      <input type="hidden" name="tool_id" value="<?= (int)$tool->tool_id ?>">
      <div class="mb-3">
        <label class="form-label">Start time</label>
        <input type="datetime-local" name="start_time" class="form-control" required value="<?= e($_POST['start_time'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">End time</label>
        <input type="datetime-local" name="end_time" class="form-control" required value="<?= e($_POST['end_time'] ?? '') ?>">
      </div>
      <button class="btn btn-outline-primary" name="preview" value="1">Calculate Price</button>
      <?php if ($preview): ?>
        <hr>
        <table class="table table-sm">
          <tr><td>Hours</td><td><?= number_format($preview['hours'], 1) ?></td></tr>
          <tr><td>Rental cost</td><td><?= number_format($preview['rental'], 2) ?> EGP</td></tr>
          <tr><td>Refundable deposit (held in escrow)</td><td><?= number_format($preview['deposit'], 2) ?> EGP</td></tr>
          <tr class="table-success"><td><strong>Total to pay</strong></td><td><strong><?= number_format($preview['rental'] + $preview['deposit'], 2) ?> EGP</strong></td></tr>
        </table>
        <button class="btn btn-success w-100" type="submit" name="confirm" value="1">Confirm Booking</button>
      <?php endif; ?>
    </form>
  </div>
  <div class="col-md-5">
    <div class="card card-body">
      <h6>Tool details</h6>
      <p class="small mb-1">Daily rate: <strong><?= number_format($tool->daily_rate, 0) ?> EGP</strong></p>
      <p class="small mb-1">Buffer: <?= (int)$tool->buffer_hours ?> h between bookings</p>
      <p class="small mb-0 text-muted">Deposit is held until safe return is confirmed.</p>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>
