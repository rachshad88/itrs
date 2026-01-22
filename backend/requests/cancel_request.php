<?php
session_start();
require "../config/db.php";
require "../config/websocket.php";

if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized");
}

$requestId = $_POST['request_id'];
$userId = $_SESSION['user_id'];

// Get created_by before updating
$getStmt = $pdo->prepare("SELECT created_by FROM requests WHERE id = ? AND created_by = ?");
$getStmt->execute([$requestId, $userId]);
$result = $getStmt->fetch();

if (!$result) {
    header("Location: ../../frontend/pages/requested.php");
    exit;
}

$createdBy = $result['created_by'];

// Only allow cancellation if status is PENDING and not assigned to a technician
$sql = "
UPDATE requests
SET status = 'CANCELLED'
WHERE id = ? AND status = 'PENDING' AND assigned_to IS NULL
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$requestId]);

// Emit websocket event for real-time update
$wsEmitter->requestCancelled($requestId, $createdBy);

header("Location: ../../frontend/pages/requested.php");
exit;
?>?>
