<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../controllers/damageReports/DamageReportController.php';
Auth::require_role('LIBRARIAN');

$damageCtrl = new DamageReportController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $damageCtrl->updateStatus((int)$_POST['report_id'], $_POST['decision']);
    set_flash('Dispute decision recorded.');
    header('Location: /views/librarian/disputes.php');
    exit;
}

$reports = $damageCtrl->findAllPending();
$page_title = 'Disputes';

require __DIR__ . '/../shared/dashboard_shell_top.php';
?>
<h2><i class="bi bi-flag"></i> Dispute Mediation Hub</h2>
<?php if (empty($reports)): ?>
  <div class="alert alert-success">No pending disputes.</div>
<?php else: ?>
<table class="table">
  <thead><tr><th>#</th><th>Tool</th><th>Description</th><th>Estimated cost</th><th>Action</th></tr></thead>
  <tbody>
  <?php foreach ($reports as $r): ?>
    <tr>
      <td><?= (int)$r->report_id ?></td>
      <td><?= e($r->tool_name) ?></td>
      <td><?= e($r->description) ?></td>
      <td><?= number_format($r->estimated_repair_cost, 2) ?></td>
      <td>
        <form method="POST" class="d-flex gap-1">
          <input type="hidden" name="report_id" value="<?= (int)$r->report_id ?>">
          <button name="decision" value="APPROVED" class="btn btn-sm btn-success">Approve</button>
          <button name="decision" value="REJECTED" class="btn btn-sm btn-danger">Reject</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>
