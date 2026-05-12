<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../controllers/escrow/EscrowController.php';
Auth::require_role('LIBRARIAN');

$escrowCtrl = new EscrowController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tx = (int)$_POST['transaction_id'];
    $to = $_POST['release_to'] === 'LENDER' ? 'LENDER' : 'BORROWER';
    $escrowCtrl->release($tx, $to, $_POST['notes'] ?? '');
    set_flash("Escrow released to $to.");
    header('Location: /views/escrow/pending.php');
    exit;
}

$pending = $escrowCtrl->findAllPending();
$total   = $escrowCtrl->totalHeld();
$page_title = 'Escrow — Pending';
require __DIR__ . '/../shared/dashboard_shell_top.php';
?>
<h2><i class="bi bi-bank"></i> Escrow — Pending Releases</h2>
<div class="dashboard-stat mb-3">
  <h3><?= number_format($total, 2) ?> EGP</h3>
  <small>Total currently held in escrow</small>
</div>
<?php if (empty($pending)): ?>
  <div class="alert alert-success">No pending escrow transactions.</div>
<?php else: ?>
<table class="table table-bordered align-middle">
  <thead><tr><th>#</th><th>Tool</th><th>Borrower</th><th>Type</th><th>Amount</th><th>Action</th></tr></thead>
  <tbody>
  <?php foreach ($pending as $p): ?>
    <tr>
      <td><?= (int)$p->transaction_id ?></td>
      <td><?= e($p->tool_name) ?></td>
      <td><?= e($p->borrower_name) ?></td>
      <td><?= e($p->transaction_type) ?></td>
      <td><?= number_format($p->amount, 2) ?></td>
      <td>
        <form method="POST" class="d-flex gap-2">
          <input type="hidden" name="transaction_id" value="<?= (int)$p->transaction_id ?>">
          <select name="release_to" class="form-select form-select-sm" style="width:auto">
            <option value="BORROWER">Refund borrower</option>
            <option value="LENDER">Pay lender</option>
          </select>
          <input name="notes" class="form-control form-control-sm" placeholder="Notes">
          <button class="btn btn-sm btn-primary">Release</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>
