<?php
session_start();
require "../../backend/config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../frontend/pages/index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">  
        <title>Accomplishment Reports</title>
        <link rel="stylesheet" href="../../frontend/assets/css/reports.css">
    </head>

    <body>
        <?php include __DIR__ . '/navbar.php'; ?>
        <h1>Accomplishment Reports</h1>
    </body>
</html>