<?php
require_once "../config/db.php";

$stmt = $pdo->query("
    SELECT id, CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) AS full_name
    FROM users
    WHERE role = 'TECHNICIAN'
");

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>