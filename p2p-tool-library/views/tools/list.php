<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../controllers/tools/ToolController.php';
require_once __DIR__ . '/../../controllers/category/CategoryController.php';
Auth::start();
$toolCtrl = new ToolController();
$catCtrl = new CategoryController();
$filters = [
    'category_id' => $_GET['category'] ?? '',
    'search'      => $_GET['q'] ?? ''
];
$tools = $toolCtrl->findAll($filters);
$categories = $catCtrl->findAll();
$page_title = 'Browse Tools';
require __DIR__ . '/../shared/dashboard_shell_top.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2><i class="bi bi-grid"></i> Browse Tools</h2>
  <a href="/views/tools/create.php" class="btn btn-success"><i class="bi bi-plus-lg"></i> List a Tool</a>
</div>

<form method="GET" class="card card-body mb-3">
  <div class="row g-2">
    <div class="col-md-6"><input type="text" name="q" class="form-control" placeholder="Search tool name..." value="<?= e($_GET['q'] ?? '') ?>"></div>
    <div class="col-md-4">
      <select name="category" class="form-select">
        <option value="">All categories</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= (int)$c->category_id ?>" <?= (($_GET['category'] ?? '') == $c->category_id) ? 'selected':'' ?>>
            <?= str_repeat('— ', $c->level-1) . e($c->name) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
  </div>
</form>

<?php if (empty($tools)): ?>
  <div class="alert alert-info">No tools available right now.</div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($tools as $t): ?>
      <div class="col-md-4">
        <div class="card tool-card h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <h5 class="card-title"><?= e($t->tool_name) ?></h5>
              <span class="badge bg-success"><?= e($t->status) ?></span>
            </div>
            <p class="text-muted small mb-1"><i class="bi bi-tag"></i> <?= e($t->category_name) ?></p>
            <p class="text-muted small mb-2"><i class="bi bi-person"></i> <?= e($t->owner_name) ?></p>
            <p class="card-text small"><?= e(mb_substr($t->description ?? '', 0, 100)) ?></p>
            <div class="d-flex justify-content-between align-items-center">
              <strong><?= number_format($t->daily_rate, 0) ?> EGP/day</strong>
              <a href="/views/tools/show.php?id=<?= (int)$t->tool_id ?>" class="btn btn-sm btn-outline-primary">View</a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>
