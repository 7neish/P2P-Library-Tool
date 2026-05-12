<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../controllers/auth/UserController.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $name     = trim($_POST['full_name'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $userCtrl = new UserController();

    if (!$email || !$name || !$password) {
        $error = 'All required fields must be filled.';
    } elseif ($userCtrl->findByEmail($email)) {
        $error = 'Email already in use.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $ok = $userCtrl->create([
            'email' => $email,
            'password_hash' => $hash,
            'full_name' => $name,
            'phone' => $phone,
            'address' => $address,
            'role' => 'MEMBER'
        ]);
        if ($ok) {
            set_flash('Account created. Please log in.');
            header('Location: /views/auth/login.php');
            exit;
        } else {
            $error = 'Failed to create account.';
        }
    }
}
$page_title = 'Register';
require __DIR__ . '/../shared/dashboard_shell_top.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h3 class="mb-3"><i class="bi bi-person-plus"></i> Create Account</h3>
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="POST">
          <div class="mb-3"><label class="form-label">Full Name *</label>
            <input type="text" name="full_name" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Email *</label>
            <input type="email" name="email" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Address</label>
            <input type="text" name="address" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Password *</label>
            <input type="password" name="password" class="form-control" required minlength="6"></div>
          <button class="btn btn-success w-100" type="submit">Register</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>
