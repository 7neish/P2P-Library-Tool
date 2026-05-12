<?php
require_once __DIR__ . '/../includes/DBController.php';
require_once __DIR__ . '/../controllers/notification/NotificationController.php';

$ctrl = new NotificationController();


$ctrl->sendDueReminders();


$ctrl->sendEscalationReminders(24);

echo "Reminders and escalations processed at " . date('Y-m-d H:i:s');