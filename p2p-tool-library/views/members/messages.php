<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../controllers/message/MessageController.php';
require_once __DIR__ . '/../../controllers/auth/UserController.php';
Auth::require_login();

$u = Auth::user();
$msgCtrl = new MessageController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msgCtrl->send($u->user_id, (int)$_POST['receiver_id'], $_POST['content']);
    set_flash('Message sent.');
    header('Location: /views/members/messages.php');
    exit;
}

$inbox = $msgCtrl->inboxFor($u->user_id);
$userCtrl = new UserController();
$users = $userCtrl->findAll();

$page_title = 'Messages';
require __DIR__ . '/../shared/dashboard_shell_top.php';
?>
<h2><i class="bi bi-envelope"></i> Encrypted Messages</h2>
<div class="row">
  <div class="col-md-7">
    <h5>Inbox</h5>
    <?php if (empty($inbox)): ?>
      <p class="text-muted">No messages.</p>
    <?php else: ?>
      <?php foreach ($inbox as $m): ?>
        <div class="card mb-2"><div class="card-body p-2">
          <small class="text-muted"><?= e($m->sent_at) ?> — from <?= e($m->sender_name) ?></small>
          <p class="mb-0"><?= e($m->decrypted) ?></p>
        </div></div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <div class="col-md-5">
    <h5>Send a message</h5>
    <form method="POST" class="card card-body">
      <div class="mb-2"><label class="form-label">To</label>
        <select name="receiver_id" class="form-select">
          <?php foreach ($users as $usr): if ($usr->user_id == $u->user_id) continue; ?>
            <option value="<?= (int)$usr->user_id ?>"><?= e($usr->full_name) ?> (<?= e($usr->role) ?>)</option>
          <?php endforeach; ?>
        </select></div>
      <div class="mb-2"><label class="form-label">Message</label>
        <textarea name="content" class="form-control" rows="3" required></textarea></div>
      <button class="btn btn-primary">Send</button>
    </form>
  </div>
</div>
<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>
