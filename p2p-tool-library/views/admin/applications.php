<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../controllers/roleApplication/RoleApplicationController.php';
Auth::require_role('ADMIN'); 

$appCtrl = new RoleApplicationController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['app_id'];
    if ($_POST['decision'] === 'approve') {
        $appCtrl->approve($id);
        set_flash('Application approved. User created.');
    } else {
        $appCtrl->reject($id);
        set_flash('Application rejected.');
    }
    header('Location: /views/admin/applications.php');
    exit;
}

$applications = $appCtrl->getAllPending();
$page_title = 'Role Applications';
require __DIR__ . '/../shared/dashboard_shell_top.php';
?>

<div class="container py-4">
  <h2 class="mb-4"><i class="bi bi-file-earmark-person"></i> Pending Role Applications</h2>
  <?php if (empty($applications)): ?>
    <div class="alert alert-info">No pending applications.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-dark">
          <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Reason</th><th>CV</th><th>Date</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($applications as $app): ?>
            <tr>
              <td><?= $app->id ?></td>
              <td><?= e($app->full_name) ?></td>
              <td><?= e($app->email) ?></td>
              <td><span class="badge bg-primary"><?= e($app->desired_role) ?></span></td>
              <td><small><?= e($app->reason) ?></small></td>
              <td><?= $app->cv_path ? "<a href='/../{$app->cv_path}' target='_blank'>View</a>" : '—' ?></td>
              <td><small><?= $app->submitted_at ?></small></td>
              <td>
                <form method="POST" class="d-flex gap-1">
                  <input type="hidden" name="app_id" value="<?= $app->id ?>">
                  <button name="decision" value="approve" class="btn btn-sm btn-success">Approve</button>
                  <button name="decision" value="reject" class="btn btn-sm btn-danger">Reject</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>