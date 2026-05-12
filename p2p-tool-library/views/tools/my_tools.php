<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../controllers/tools/ToolController.php';
Auth::require_login();
$page_title = 'My Tools';
require __DIR__ . '/../shared/dashboard_shell_top.php';
$user_id = Auth::user()->user_id;
$toolCtrl = new ToolController();
$tools = $toolCtrl->findByOwner($user_id);
?>
<div class="d-flex justify-content-between mb-3">
  <h2><i class="bi bi-box"></i> My Tools</h2>
  <a href="/views/tools/create.php" class="btn btn-success">List a Tool</a>
</div>
<?php if (empty($tools)): ?>
  <div class="alert alert-info">You haven't listed any tools yet.</div>
<?php else: ?>
<table class="table table-striped">
  <thead><tr><th>Name</th><th>Category</th><th>Daily</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($tools as $t): ?>
    <tr>
    <td><?= e($t->tool_name) ?></td>
    <td><?= e($t->category_name) ?></td>
    <td><?= number_format($t->daily_rate, 0) ?> EGP</td>
    <td><span class="badge bg-secondary"><?= e($t->status) ?></span></td>
    <td><a href="/views/tools/show.php?id=<?= (int)$t->tool_id ?>" class="btn btn-sm btn-outline-primary">View</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>
