<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/DBController.php';
require_once __DIR__ . '/../../controllers/auth/UserController.php';
Auth::require_role('LIBRARIAN');

$userCtrl = new UserController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userCtrl->updateKycStatus((int)$_POST['user_id'], $_POST['decision']);
    set_flash('KYC status updated.');
    header('Location: /views/librarian/kyc.php');
    exit;
}

$db = DBController::getInstance();
$conn = $db->getConnection();
$res = mysqli_query($conn, "SELECT * FROM users WHERE kyc_status='PENDING' ORDER BY created_at DESC");
$pending = [];
while ($u = mysqli_fetch_assoc($res)) {
    $user = new User();
    foreach ($u as $k => $v) {
        if (property_exists($user, $k)) $user->$k = $v;
    }
    $pending[] = $user;
}

$page_title = 'KYC Approvals';
require __DIR__ . '/../shared/dashboard_shell_top.php';
?>
<h2><i class="bi bi-person-check"></i> KYC Approval Queue</h2>
<?php if (empty($pending)): ?>
  <div class="alert alert-success">No pending KYC requests.</div>
<?php else: ?>
<table class="table">
  <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Action</th></tr></thead>
  <tbody>
  <?php foreach ($pending as $u): ?>
    <tr>
      <td><?= (int)$u->user_id ?></td>
      <td><?= e($u->full_name) ?></td>
      <td><?= e($u->email) ?></td>
      <td><?= e($u->phone) ?></td>
      <td>
        <form method="POST" class="d-flex gap-1">
          <input type="hidden" name="user_id" value="<?= (int)$u->user_id ?>">
          <button name="decision" value="VERIFIED" class="btn btn-sm btn-success">Verify</button>
          <button name="decision" value="REJECTED" class="btn btn-sm btn-danger">Reject</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>
