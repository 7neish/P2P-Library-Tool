<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../models/Tool.php';
require_once __DIR__ . '/../../controllers/tools/ToolController.php';
$_id = (int)($_GET['id'] ?? 0);
$page_title = 'Tool Details';
require __DIR__ . '/../shared/dashboard_shell_top.php';
$toolCtrl = new ToolController();
$tool = $toolCtrl->findById($_id);
?>
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-body">
        <h2><?= e($tool->tool_name) ?> <span class="badge bg-success"><?= e($tool->status) ?></span></h2>
        <p class="text-muted"><i class="bi bi-tag"></i> <?= e($tool->category_name) ?> &middot; <i class="bi bi-person"></i> <?= e($tool->owner_name) ?></p>
        <hr>
        <p><?= nl2br(e($tool->description)) ?></p>
        <p class="small text-muted">Serial: <code><?= e($tool->serial_number) ?></code></p>

        <h5 class="mt-4">Pricing</h5>
        <table class="table table-bordered">
          <tr><td>Hourly</td><td><?= number_format($tool->hourly_rate, 2) ?> EGP</td></tr>
          <tr><td>Daily</td><td><?= number_format($tool->daily_rate, 2) ?> EGP</td></tr>
          <tr><td>Weekly</td><td><?= number_format($tool->weekly_rate, 2) ?> EGP</td></tr>
          <tr><td>Refundable Deposit</td><td><?= number_format($tool->deposit_amount, 2) ?> EGP</td></tr>
          <tr><td>Cleaning buffer</td><td><?= (int)$tool->buffer_hours ?> hours</td></tr>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card">
      <div class="card-body">
        <h5>Book this tool</h5>
        <?php if ($tool->status === 'AVAILABLE'): ?>
          <a href="/views/bookings/create.php?tool_id=<?= (int)$tool->tool_id ?>" class="btn btn-primary w-100">
            <i class="bi bi-calendar-plus"></i> Reserve
          </a>
        <?php else: ?>
          <button class="btn btn-secondary w-100" disabled>Not available</button>
        <?php endif; ?>
        <hr>
        <p class="small mb-1"><strong>Owner contact</strong> (after booking):</p>
        <p class="small text-muted"><?= e($tool->owner_name) ?></p>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>
