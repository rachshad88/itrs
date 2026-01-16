<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");

require_once __DIR__ . "/../config/db.php";

try {
    $stmt = $pdo->prepare("
        SELECT id, request_code, client_name, office, issue, status, created_at
        FROM requests
        ORDER BY created_at DESC
    ");
    $stmt->execute(); 

    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($requests);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Database query failed",
        "message" => $e->getMessage()
    ]);
}
?>
