<?php
//para to sa status ng request pare
require __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
        exit;
    }

    $request_code = trim($_POST['request_code'] ?? '');

    if ($request_code === '') {
        echo json_encode(['status' => 'error', 'message' => 'Missing request code']);
        exit;
    }

    // Fetch the current status of the request
    $stmt = $pdo->prepare("SELECT status FROM requests WHERE request_code = ?");
    $stmt->execute([$request_code]);
    $result = $stmt->fetch();

    if (!$result) {
        echo json_encode(['status' => 'error', 'message' => 'Request not found']);
        exit;
    }

    echo json_encode(['status' => $result['status']]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

?>
