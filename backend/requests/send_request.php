<?php
session_start();
require __DIR__ . "/../config/db.php";
require __DIR__ . "/ticket_code.php";

header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "User not logged in"
    ]);
    exit;
}

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid request method"
        ]);
        exit;
    }

    $client_name     = trim($_POST['client_name'] ?? '');
    $office          = trim($_POST['office'] ?? '');
    $unit            = trim($_POST['unit'] ?? '');
    $semester        = trim($_POST['semester'] ?? '');
    $issue           = trim($_POST['issue'] ?? '');
    $remarks         = trim($_POST['remarks'] ?? '');
    $recommendation  = trim($_POST['recommendation'] ?? '');
    $created_by      = $_SESSION['user_id'];

    if ($client_name === '' || $office === '' || $issue === '') {
        echo json_encode([
            "status" => "error",
            "message" => "Required fields missing"
        ]);
        exit;
    }

    // Generate unique request code with retry mechanism
    $request_code = null;
    $maxAttempts = 5;
    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
        $code = generateRequestCode($pdo);
        
        // Check if code already exists
        $checkStmt = $pdo->prepare("SELECT id FROM requests WHERE request_code = ?");
        $checkStmt->execute([$code]);
        
        if (!$checkStmt->fetch()) {
            $request_code = $code;
            break;
        }
        // If exists, add attempt number to make it unique
        $request_code = $code . "-" . ($attempt + 1);
    }
    
    if (!$request_code) {
        throw new Exception("Failed to generate unique request code after multiple attempts");
    }

    $sql = "
        INSERT INTO requests
        (request_code, created_by, client_name, office, unit, semester, issue, remarks, recommendation, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING')
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $request_code,
        $created_by,
        $client_name,
        $office,
        $unit,
        $semester,
        $issue,
        $remarks,
        $recommendation
    ]);

    echo json_encode([
        "status" => "success",
        "request_code" => $request_code
    ]);
    exit;

} catch (Throwable $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
    exit;
}
