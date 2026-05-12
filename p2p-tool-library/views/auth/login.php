<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../controllers/auth/UserController.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) {
        $error = 'Please enter email and password.';
    } else {
        $userCtrl = new UserController();
        $user = $userCtrl->findByEmail($email);
        if ($user && password_verify($password, $user->password_hash)) {
            if ($user->is_blacklisted) {
                $error = 'Your account is blacklisted.';
            } else {
                Auth::login((array)$user);
                set_flash('Welcome back, ' . $user->full_name . '!');
                header('Location: /views/tools/list.php');
                exit;
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
$page_title = 'Login';
require __DIR__ . '/../shared/dashboard_shell_top.php';
?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h3 class="mb-3"><i class="bi bi-box-arrow-in-right"></i> Login</h3>
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button class="btn btn-primary w-100" type="submit">Login</button>
        </form>
        <hr>
        <small class="text-muted">No account? <a href="/views/auth/register.php">Register</a></small>
        <hr>
        <!-- <div class="bg-light p-2 small rounded">
          <strong>Demo accounts</strong> (password: <code>password123</code>):<br>
          admin@library.com<br>
          librarian@library.com<br>
          tech@library.com<br>
          ahmed@example.com
        </div> -->
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>
