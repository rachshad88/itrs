<?php 

require __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
        exit;
    }

    $finish_status = trim($_POST['finish_status'] ?? '');
    $request_code = trim($_POST['request_code'] ?? '');

    if ($finish_status === '' || $request_code === '') {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }

    // Convert status to lowercase for enum values
    $finish_value = strtolower(str_replace(' ', ' ', $finish_status));

    // Update the request status to DONE, set finished column, and record completed_at timestamp
    $stmt = $pdo->prepare("UPDATE requests SET status = 'DONE', finished = ?, completed_at = NOW() WHERE request_code = ?");
    $stmt->execute([$finish_value, $request_code]);

    echo json_encode(['status' => 'success', 'message' => 'Request marked as complete']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

?>