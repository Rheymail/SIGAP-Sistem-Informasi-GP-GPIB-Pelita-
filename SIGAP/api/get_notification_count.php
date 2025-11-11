<?php
require_once '../config.php';
header('Content-Type: application/json');

$count = getUnreadNotificationsCount($conn);
echo json_encode(['count' => $count]);
?>

