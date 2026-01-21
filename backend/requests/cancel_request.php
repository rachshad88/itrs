<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized");
}

$requestId = $_POST['request_id'];

// Only allow cancellation if status is PENDING and not assigned to a technician
$sql = "
UPDATE requests
SET status = 'CANCELLED'
WHERE id = ? AND status = 'PENDING' AND assigned_to IS NULL
";

$stmt = $pdo->prepare($sql);
$result = $stmt->execute([$requestId]);

header("Location: ../../frontend/pages/requested.php");
exit;
?>
