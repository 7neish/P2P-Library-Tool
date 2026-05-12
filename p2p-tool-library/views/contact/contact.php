<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../controllers/roleApplication/RoleApplicationController.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName  = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $role      = $_POST['desired_role'] ?? '';
    $reason    = trim($_POST['reason'] ?? '');
    $cvPath    = null;

    // Handle file upload (PDF/DOCX)
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (in_array($_FILES['cv']['type'], $allowed) && $_FILES['cv']['size'] < 2 * 1024 * 1024) {
            $ext = pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION);
            $cvPath = 'assets/uploads/' . uniqid('cv_') . '.' . $ext;
            move_uploaded_file($_FILES['cv']['tmp_name'], __DIR__ . '/../../' . $cvPath);
        }
    }

    $appCtrl = new RoleApplicationController();
    if ($appCtrl->create([
        'full_name' => $fullName,
        'email'     => $email,
        'phone'     => $phone,
        'desired_role' => $role,
        'reason'    => $reason,
        'cv_path'   => $cvPath
    ])) {
        $success = 'Your application has been submitted! An admin will review it shortly.';
    } else {
        $error = 'Failed to submit. Please try again.';
    }
}

$page_title = 'Contact & Apply';
require __DIR__ . '/../../views/shared/dashboard_shell_top.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-lg border-0">
        <div class="card-body p-5">
          <h2 class="fw-bold mb-3"><i class="bi bi-send-fill"></i> Apply for a Staff Role</h2>
          <p class="text-muted mb-4">Fill out the form below to apply as a Librarian, Technician, or Administrator. After submission, an admin will review and approve your account with the requested role.</p>
          
          <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
          <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
          <?php endif; ?>
          
          <form method="POST" enctype="multipart/form-data">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label">Desired Role *</label>
                <select name="desired_role" class="form-select" required>
                  <option value="">-- Select --</option>
                  <option value="LIBRARIAN">Librarian</option>
                  <option value="TECHNICIAN">Technician</option>
                  <option value="ADMIN">Administrator</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Why do you want this role? *</label>
                <textarea name="reason" class="form-control" rows="4" required></textarea>
              </div>
              <div class="col-12">
                <label class="form-label">Attach CV (PDF/DOCX, max 2 MB)</label>
                <input type="file" name="cv" class="form-control" accept=".pdf,.doc,.docx">
              </div>
              <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary btn-lg px-5">Submit Application</button>
              </div>
            </div>
          </form>
          
          <hr class="my-5">
          <h5 class="fw-bold">Other Inquiries</h5>
          <p class="text-muted">For general questions, email us at <a href="mailto:support@p2ptools.com">support@p2ptools.com</a></p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../../views/shared/dashboard_shell_bottom.php'; ?>