<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../controllers/bookings/bookingController.php';

Auth::require_login();
$u = Auth::user(); 


$bookingCtrl = new BookingController();
$bookings = $bookingCtrl->findByUser($u->user_id); 

$page_title = 'My Bookings';
require __DIR__ . '/../shared/dashboard_shell_top.php';
?>

<h2><i class="bi bi-calendar-check"></i> My Bookings</h2>

<?php if (empty($bookings)): ?>
  <div class="alert alert-info">No bookings yet. <a href="/views/tools/list.php">Browse tools</a></div>
<?php else: ?>
<table class="table table-striped">
  <thead><tr><th>#</th><th>Tool</th><th>Start</th><th>End</th><th>Total</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($bookings as $b): ?>
    <tr>
      
      <td><?= (int)$b->booking_id ?></td>
      <td><?= e($b->tool_name) ?></td>
      <td><?= e($b->start_time) ?></td>
      <td><?= e($b->end_time) ?></td>
      <td><?= number_format($b->total_price, 2) ?> EGP</td>
      <td><span class="badge bg-secondary"><?= e($b->status) ?></span></td>
      <td>
        <a class="btn btn-sm btn-outline-primary" href="/views/bookings/show.php?id=<?= (int)$b->booking_id ?>">
            View
        </a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>