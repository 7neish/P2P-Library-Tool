<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../controllers/auth/UserController.php';
Auth::require_role('LIBRARIAN');

$userCtrl = new UserController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_member') {
        $data = [
            'full_name'     => $_POST['full_name'],
            'email'         => $_POST['email'],
            'password_hash' => password_hash($_POST['password'], PASSWORD_BCRYPT),
            'phone'         => $_POST['phone'],
            'role'          => $_POST['role'] ?? 'MEMBER',
            // 'role'          => 'MEMBER'
        ];
        if ($userCtrl->create($data)) {
            set_flash('Member added successfully!');
        } else {
            set_flash('Error adding member.', 'danger');
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['user_id'];
        if ($userCtrl->deleteWithEverything($id)) {
            set_flash('Member and their data deleted.');
        } else {
            set_flash('Delete failed.', 'danger');
        }
    } else {
        $id = (int)$_POST['user_id'];
        if ($action === 'suspend') {
            $userCtrl->suspend($id, 30);
            set_flash('Member suspended.');
        } elseif ($action === 'blacklist') {
            $userCtrl->blacklist($id);
            set_flash('Member blacklisted.', 'danger');
        } elseif ($action === 'recalc') {
            $userCtrl->recalculateTrustScore($id);
            set_flash("Score recalculated.");
        }
    }
    header('Location: /views/librarian/members.php');
    exit;
}

if (isset($_GET['view']) && $_GET['view'] === 'add') {
    $page_title = 'Add Member';
    require __DIR__ . '/../../views/librarian/add_member.php';
    exit;
}

$search = $_GET['search'] ?? '';
if (!empty($search)) {
    $members = $userCtrl->search($search, 'MEMBER');
} else {
    $members = $userCtrl->findAll('MEMBER');
}

$page_title = 'Members';
require __DIR__ . '/../shared/dashboard_shell_top.php';

function trust_class($s) {
    if ($s >= 80) return 'trust-high';
    if ($s >= 60) return 'trust-mid';
    return 'trust-low';
}
?>
<h2><i class="bi bi-people"></i> Members</h2>
<div class="d-flex justify-content-between align-items-center mb-4">
    <form method="GET" class="d-flex gap-2 w-50">
        <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?= e($_GET['search'] ?? '') ?>">
        <button class="btn btn-outline-primary" type="submit">Search</button>
        <?php if(isset($_GET['search'])): ?>
            <a href="members.php" class="btn btn-outline-secondary">Reset</a>
        <?php endif; ?>
    </form>

    <a href="?view=add" class="btn btn-success">
        <i class="bi bi-person-plus"></i> Add New Member
    </a>
</div>
<table class="table table-striped">
  <thead><tr><th>#</th><th>Name</th><th>Email</th><th>KYC</th><th>Trust</th><th>Wallet</th><th>Action</th></tr></thead>
  <tbody>
  <?php foreach ($members as $u): ?>
    <tr>
      <td><?= (int)$u->user_id ?></td>
      <td><?= e($u->full_name) ?> <?php if ($u->is_blacklisted): ?><span class="badge bg-danger">Blacklisted</span><?php endif; ?></td>
      <td><?= e($u->email) ?></td>
      <td><span class="badge bg-secondary"><?= e($u->kyc_status) ?></span></td>
      <td><span class="trust-score <?= trust_class($u->current_trust_score) ?>"><?= number_format($u->current_trust_score, 1) ?></span></td>
      <td><?= number_format($u->wallet_balance, 2) ?> EGP</td>
      <td>
        <form method="POST" class="d-flex gap-1">
          <input type="hidden" name="user_id" value="<?= (int)$u->user_id ?>">
          <button name="action" value="recalc" class="btn btn-sm btn-outline-primary">Recalc</button>
          <button name="action" value="suspend" class="btn btn-sm btn-warning">Suspend</button>
          <button name="action" value="blacklist" class="btn btn-sm btn-danger">Blacklist</button>
          <button name="action" value="delete" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure? This will permanently delete the member.')">Delete</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>
