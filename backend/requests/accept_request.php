<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized");
}

$requestId = $_POST['request_id'];
$userId = $_SESSION['user_id'];

$sql = "
UPDATE requests
SET assigned_to = ?, status = 'IN_PROGRESS'
WHERE id = ? AND assigned_to IS NULL
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId, $requestId]);

header("Location: ../../frontend/pages/dashboard.php");
exit;
