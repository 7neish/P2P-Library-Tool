<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../models/Tool.php';
require_once __DIR__ . '/../../models/Zone.php';
require_once __DIR__ . '/../../controllers/tools/ToolController.php';
Auth::require_login();
$page_title = 'Nearby Tools';
$u = Auth::user(); 


$radius = (float)($_GET['radius'] ?? 5);
$lat = (float)($u->latitude ?? 29.9602); 
$lng = (float)($u->longitude ?? 31.2569);

$toolCtrl = new ToolController();

$tools = $toolCtrl->findNearby($lat, $lng, $radius);
require __DIR__ . '/../shared/dashboard_shell_top.php';
?>
<h2><i class="bi bi-geo-alt"></i> Nearby Tools (Geospatial Discovery)</h2>
<form method="GET" class="card card-body my-3">
  <div class="row g-2 align-items-end">
    <div class="col-md-3"><label class="form-label">Radius (km)</label>
      <input type="number" step="0.5" name="radius" class="form-control" value="<?= e($radius) ?>"></div>
    <div class="col-md-3"><label class="form-label">Your latitude</label>
      <input class="form-control" value="<?= e($lat) ?>" disabled></div>
    <div class="col-md-3"><label class="form-label">Your longitude</label>
      <input class="form-control" value="<?= e($lng) ?>" disabled></div>
    <div class="col-md-3"><button class="btn btn-primary w-100">Search</button></div>
  </div>
</form>
<?php if (empty($tools)): ?>
  <div class="alert alert-warning">No tools within <?= e($radius) ?> km.</div>
<?php else: ?>
<table class="table">
  <thead><tr><th>Tool</th><th>Owner</th><th>Distance</th><th>Daily</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($tools as $t): ?>
    <tr>
      <td><?= e($t->tool_name) ?></td>
      <td><?= e($t->owner_name) ?></td>
      <td><span class="badge bg-info text-dark"><?= number_format($t->distance_km, 2) ?> km</span></td>
      <td><?= number_format($t->daily_rate, 0) ?> EGP</td>
      <td><a href="/views/tools/show.php?id=<?= (int)$t->tool_id ?>" class="btn btn-sm btn-outline-primary">View</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>
