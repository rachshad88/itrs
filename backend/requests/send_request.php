<?php
require __DIR__ . "/../config/db.php";
require __DIR__ . "/ticket_code.php"; // if function is in another file

header("Content-Type: application/json; charset=UTF-8");

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

    if ($client_name === '' || $office === '' || $issue === '') {
        echo json_encode([
            "status" => "error",
            "message" => "Required fields missing"
        ]);
        exit;
    }

    // para macall yung code na ginawa ng ticket_code.php
    $request_code = generateRequestCode($pdo);

    $sql = "
        INSERT INTO requests
        (request_code, client_name, office, unit, semester, issue, remarks, recommendation)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $request_code,
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
