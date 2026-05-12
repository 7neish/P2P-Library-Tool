<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../controllers/maintenance/MaintenanceController.php';
Auth::require_role('TECHNICIAN');

$maintCtrl = new MaintenanceController();
$logs = $maintCtrl->findAll();
$page_title = 'Maintenance Logs';
require __DIR__ . '/../shared/dashboard_shell_top.php';
?>
<div class="d-flex justify-content-between mb-3">
  <h2><i class="bi bi-wrench"></i> Maintenance Logs</h2>
  <a href="/views/maintenance/create.php" class="btn btn-success">New Log</a>
</div>
<?php if (empty($logs)): ?>
  <div class="alert alert-info">No maintenance logs yet.</div>
<?php else: ?>
<table class="table table-striped">
  <thead><tr><th>#</th><th>Tool</th><th>Technician</th><th>Task</th><th>Cost</th><th>Date</th></tr></thead>
  <tbody>
  <?php foreach ($logs as $m): ?>
    <tr>
      <td><?= (int)$m->maintenance_id ?></td>
      <td><?= e($m->tool_name) ?></td>
      <td><?= e($m->technician_name) ?></td>
      <td><?= e($m->task_description) ?></td>
      <td><?= number_format($m->cost, 2) ?></td>
      <td><?= e($m->service_date) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>
