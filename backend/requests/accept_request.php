<?php
session_start();
require "../config/db.php";
require "../config/websocket.php";

if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized");
}

// Only TECHNICIAN can accept requests, not ADMIN
if ($_SESSION['role'] === 'ADMIN') {
    header("Location: ../../frontend/pages/dashboard.php");
    exit;
}

if ($_SESSION['role'] !== 'TECHNICIAN') {
    header("Location: ../../frontend/pages/index.php");
    exit;
}

$requestId = $_POST['request_id'];
$userId = $_SESSION['user_id'];

$sql = "
SELECT created_by FROM requests WHERE id = ?
";
$checkStmt = $pdo->prepare($sql);
$checkStmt->execute([$requestId]);
$result = $checkStmt->fetch();
$createdBy = $result ? $result['created_by'] : null;

$sql = "
UPDATE requests
SET assigned_to = ?, status = 'IN_PROGRESS'
WHERE id = ? AND assigned_to IS NULL
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId, $requestId]);

// Emit websocket event for real-time update
if ($createdBy) {
    $wsEmitter->requestAccepted($requestId, $userId, $createdBy);
}

header("Location: ../../frontend/pages/dashboard.php");
exit;
?>
