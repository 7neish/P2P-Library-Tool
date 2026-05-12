<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../models/Tool.php';      
require_once __DIR__ . '/../../models/Category.php';
require_once __DIR__ . '/../../controllers/tools/ToolController.php';
require_once __DIR__ . '/../../controllers/category/CategoryController.php';
require_once __DIR__ . '/../../controllers/zone/ZoneController.php';
Auth::require_login();
$catCtrl = new CategoryController();
$zoneCtrl = new ZoneController();
$categories = $catCtrl->findAll(); 
$zones = $zoneCtrl->findAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = Auth::user();
    $toolCtrl = new ToolController();
    $tool = new Tool();
  
    $tool->owner_id     = $u->user_id;
    $tool->category_id  = (int)$_POST['category_id'];
    $tool->zone_id      = (int)($_POST['zone_id'] ?? 0);
    $tool->tool_name    = trim($_POST['tool_name']);
    $tool->description  = trim($_POST['description']);
    $tool->serial_number= trim($_POST['serial_number']);
    $tool->hourly_rate  = $_POST['hourly_rate'];
    $tool->daily_rate   = $_POST['daily_rate'];
    $tool->weekly_rate  = $_POST['weekly_rate'];
    $tool->deposit_amount = $_POST['deposit_amount'];
    $tool->buffer_hours = $_POST['buffer_hours'] ?? 10;
    $tool->latitude     = $_POST['latitude'] ?? 0;
    $tool->longitude    = $_POST['longitude'] ?? 0;
    $image_path = null;
    if (isset($_FILES['tool_image']) && $_FILES['tool_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../assets/uploads/'; 
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

        $extension = pathinfo($_FILES['tool_image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('tool_', true) . '.' . $extension; 
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['tool_image']['tmp_name'], $target_file)) {
            $image_path = 'assets/uploads/tools/' . $filename; 
        }
    }
    
    $tool->image_url = $image_path;
    if (empty($tool->tool_name) || empty($tool->category_id)) {
        $error = 'Name and category required.';
    } else {
        if ($toolCtrl->create($tool)) {
            set_flash('Tool listed successfully.');
            header('Location: /views/tools/my_tools.php');
            exit;
        } else {
            $error = 'Failed to create tool.';
        }
    }
}
$page_title = 'List a Tool';

require __DIR__ . '/../shared/dashboard_shell_top.php';
?>
<h2><i class="bi bi-plus-lg"></i> List a Tool</h2>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="POST" class="card card-body" enctype="multipart/form-data">
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Tool name *</label>
      <input type="text" name="tool_name" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Serial number</label>
      <input type="text" name="serial_number" class="form-control">
    </div>
    <div class="col-md-6">
      <label class="form-label">Category *</label>
      <select name="category_id" class="form-select" required>
        <option value="">Choose...</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= (int)$c->category_id ?>"><?= str_repeat('— ', $c->level-1) . e($c->name) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Zone</label>
      <select name="zone_id" class="form-select">
        <option value="">None</option>
        <?php foreach ($zones as $z): ?>
          <option value="<?= (int)$z->zone_id ?>"><?= e($z->name) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="3"></textarea>
    </div>
    <div class="col-md-3"><label class="form-label">Hourly (EGP)</label><input type="number" step="0.01" name="hourly_rate" class="form-control" value="0"></div>
    <div class="col-md-3"><label class="form-label">Daily</label><input type="number" step="0.01" name="daily_rate" class="form-control" value="0"></div>
    <div class="col-md-3"><label class="form-label">Weekly</label><input type="number" step="0.01" name="weekly_rate" class="form-control" value="0"></div>
    <div class="col-md-3"><label class="form-label">Deposit</label><input type="number" step="0.01" name="deposit_amount" class="form-control" value="0"></div>
    <div class="col-md-3"><label class="form-label">Buffer hours</label><input type="number" name="buffer_hours" class="form-control" value="10"></div>
    <div class="col-md-4"><label class="form-label">Latitude</label><input type="number" step="any" name="latitude" class="form-control" value="29.9602"></div>
    <div class="col-md-4"><label class="form-label">Longitude</label><input type="number" step="any" name="longitude" class="form-control" value="31.2569"></div>
    <div class="col-md-12"><label class="form-label">Tool Image</label><input type="file" name="tool_image" class="form-control" accept="image/*"></div>
  </div>
  <div class="mt-3"><button class="btn btn-success">Create</button></div>
</form>
<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>
