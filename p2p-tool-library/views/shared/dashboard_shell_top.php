<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
Auth::start();
$current_user = Auth::user();
$flash = get_flash();
$page_title = $page_title ?? 'P2P Tool Library';
$user = Auth::user();


if ($user && isset($_GET['mark_read'])) {
    require_once __DIR__ . '/../../controllers/notification/NotificationController.php';
    $notifCtrl = new NotificationController();
    $notifCtrl->markAsRead((int)$_GET['mark_read']);
    $params = $_GET;
    unset($params['mark_read']);
    $queryString = http_build_query($params);
    $redirect = strtok($_SERVER['REQUEST_URI'], '?');
    if (!empty($queryString)) $redirect .= '?' . $queryString;
    header('Location: ' . $redirect);
    exit;
}


$unreadCount = 0;
$notifications = [];
if ($user) {
    require_once __DIR__ . '/../../controllers/notification/NotificationController.php';
    $notifCtrl = new NotificationController();
    $unreadCount = $notifCtrl->getUnreadCount($user->user_id);
    $notifications = $notifCtrl->getAllForUser($user->user_id);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($page_title) ?> — P2P Tool Library</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/views/tools/list.php">
      <i class="bi bi-tools"></i> P2P Tool Library
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="/views/tools/list.php"><i class="bi bi-grid"></i> Browse Tools</a></li>
        <?php if ($user): ?>
          <li class="nav-item"><a class="nav-link" href="/views/tools/nearby.php"><i class="bi bi-geo-alt"></i> Nearby</a></li>
          <li class="nav-item"><a class="nav-link" href="/views/bookings/my_bookings.php"><i class="bi bi-calendar-check"></i> My Bookings</a></li>
          <li class="nav-item"><a class="nav-link" href="/views/tools/my_tools.php"><i class="bi bi-box"></i> My Tools</a></li>
          <li class="nav-item"><a class="nav-link" href="/views/members/messages.php"><i class="bi bi-envelope"></i> Messages</a></li>
          <?php if (in_array($user->role, ['LIBRARIAN','ADMIN'])): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-shield-check"></i> Librarian</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/views/librarian/dashboard.php">Dashboard</a></li>
                <li><a class="dropdown-item" href="/views/librarian/disputes.php">Disputes</a></li>
                <li><a class="dropdown-item" href="/views/librarian/kyc.php">KYC Approvals</a></li>
                <li><a class="dropdown-item" href="/views/escrow/pending.php">Escrow</a></li>
                <li><a class="dropdown-item" href="/views/librarian/members.php">Members</a></li>
                <li><a class="dropdown-item" href="/views/librarian/bookings.php">Bookings</a></li>
                <?php if ($user->role === 'ADMIN'): ?>
                <li><a class="dropdown-item" href="/views/admin/applications.php">Role Applications</a></li>
                <?php endif; ?>
              </ul>
            </li>
          <?php endif; ?>
          <?php if (in_array($user->role, ['TECHNICIAN','ADMIN'])): ?>
            <li class="nav-item"><a class="nav-link" href="/views/maintenance/list.php"><i class="bi bi-wrench"></i> Maintenance</a></li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav">
        <?php if ($user): ?>
          
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-bell-fill"></i>
              <?php if ($unreadCount > 0): ?>
                <span class="badge bg-danger rounded-pill"><?= $unreadCount ?></span>
              <?php endif; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notifDropdown" style="min-width:300px">
              <?php if (empty($notifications)): ?>
                <li><span class="dropdown-item text-muted">لا توجد إشعارات</span></li>
              <?php else: ?>
                <?php foreach ($notifications as $notif): ?>
                  <li>
                    <a class="dropdown-item small <?= $notif->is_read ? '' : 'fw-bold' ?>" 
                       href="?mark_read=<?= (int)$notif->id ?>">
                      <?= e($notif->message) ?>
                      <br><small class="text-muted"><?= $notif->created_at ?></small>
                    </a>
                  </li>
                <?php endforeach; ?>
              <?php endif; ?>
            </ul>
          </li>
          
          <li class="nav-item"><span class="navbar-text me-3"><i class="bi bi-person-circle"></i> <?= e($user->full_name) ?> <span class="badge bg-primary"><?= e($user->role) ?></span></span></li>
          <li class="nav-item"><a class="nav-link" href="/controllers/auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="/views/auth/login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="/views/auth/register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="container py-4">
<?php if ($flash): ?>
  <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show">
    <?= e($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>