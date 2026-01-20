<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized");
}

$requestId = $_POST['request_id'];

$sql = "
DELETE FROM requests
WHERE id = ? AND status = 'PENDING' AND assigned_to IS NULL
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$requestId]);

header("Location: ../../frontend/pages/requested.php");
exit;
?>
