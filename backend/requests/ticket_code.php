

<?php
//dito nang gagaling yung nagenerate na ticket code
function generateRequestCode(PDO $pdo) {
    $year = date("Y");

    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM requests 
        WHERE YEAR(created_at) = YEAR(CURDATE())
    ");
    $stmt->execute();

    $nextNumber = $stmt->fetchColumn() + 1;

    return "IT-$year-" . str_pad($nextNumber, 4, "0", STR_PAD_LEFT);
}

?>