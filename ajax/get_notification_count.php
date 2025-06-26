<?php
require_once '../config/session.php';
requireLogin();
require_once '../config/database.php';
require_once '../classes/Notification.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();
$notification = new Notification($db);

$count = $notification->getUnreadCount($_SESSION['user_id']);

echo json_encode(['count' => $count]);
?>
