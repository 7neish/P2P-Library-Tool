<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../controllers/auth/UserController.php';
Auth::require_login();

$u = Auth::user();
$userCtrl = new UserController();
$me = $userCtrl->findById($u->user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userCtrl->updateKycStatus($u->user_id, 'PENDING');
    set_flash('KYC document submitted. A librarian will review it.');
    header('Location: /views/members/kyc.php');
    exit;
}

$page_title = 'My KYC';
require __DIR__ . '/../shared/dashboard_shell_top.php';
?>
<h2><i class="bi bi-person-vcard"></i> Identity Verification (KYC)</h2>
<div class="card card-body">
  <p>Current status: <span class="badge bg-info"><?= e($me->kyc_status) ?></span></p>
  <p class="text-muted">Required to borrow tools with deposit > 1000 EGP.</p>
  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3"><label class="form-label">National ID document (placeholder)</label>
      <input type="file" class="form-control"></div>
    <button class="btn btn-primary">Submit for Review</button>
  </form>
</div>
<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>
