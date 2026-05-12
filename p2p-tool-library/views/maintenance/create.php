<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../controllers/maintenance/MaintenanceController.php';
require_once __DIR__ . '/../../controllers/tools/ToolController.php';
Auth::require_role('TECHNICIAN');

$u = Auth::user();
$maintCtrl = new MaintenanceController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maintCtrl->create([
        'tool_id' => (int)$_POST['tool_id'],
        'technician_id' => $u->user_id,
        'task_description' => $_POST['task_description'],
        'cost' => $_POST['cost'],
        'usage_hours_at_service' => $_POST['usage_hours_at_service'] ?? 0,
        'next_service_due' => $_POST['next_service_due'] ?? ''
    ]);
    set_flash('Maintenance logged.');
    header('Location: /views/maintenance/list.php');
    exit;
}

$toolCtrl = new ToolController();
$tools = $toolCtrl->findAll([]);
$page_title = 'New Maintenance Log';
require __DIR__ . '/../shared/dashboard_shell_top.php';
?>
<h2>New Maintenance Log</h2>
<form method="POST" class="card card-body">
  <div class="mb-3"><label class="form-label">Tool</label>
    <select name="tool_id" class="form-select" required>
      <?php foreach ($tools as $t): ?>
        <option value="<?= (int)$t->tool_id ?>"><?= e($t->tool_name) ?></option>
      <?php endforeach; ?>
    </select></div>
  <div class="mb-3"><label class="form-label">Task description</label>
    <textarea name="task_description" class="form-control" required></textarea></div>
  <div class="row g-2">
    <div class="col-md-4"><label class="form-label">Cost (EGP)</label>
      <input type="number" step="0.01" name="cost" class="form-control" value="0"></div>
    <div class="col-md-4"><label class="form-label">Usage hours</label>
      <input type="number" name="usage_hours_at_service" class="form-control" value="0"></div>
    <div class="col-md-4"><label class="form-label">Next service due</label>
      <input type="date" name="next_service_due" class="form-control"></div>
  </div>
  <div class="mt-3"><button class="btn btn-success">Save</button></div>
</form>
<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>
