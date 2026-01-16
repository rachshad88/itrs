<?php
require_once "../config/db.php";

$stmt = $pdo->query("
    SELECT id, full_name
    FROM users
    WHERE role = 'TECHNICIAN'
      AND status = 'AVAILABLE'
");

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>