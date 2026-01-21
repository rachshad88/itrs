<?php
    session_start();
    require "../../backend/config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'CLIENT') {
    header("Location: dashboard.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MMO IT Service Request Page</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
   <?php include __DIR__ . '/navbar.php'; ?>
<div class="container">
        <h1>IT Service Request System</h1>

            <form action="" id="requestForm" method="POST">
                <div id="service-request-form">

                    <label for="office">Office:</label>
                    <input type="text" id="office" name="office" required />

                    <label for="semester">Semester:</label>
                    <select id="semester" name="semester" required>
                        <option value="" disabled selected>Select Semester</option>
                        <option value="1st Semester">1st Semester</option>
                        <option value="2nd Semester">2nd Semester</option>
                    </select>

                    <label for="unit">Unit:</label>
                    <select id="unit" name="unit" required>
                        <option value="desktop">Desktop</option>
                        <option value="laptop">Laptop</option>
                        <option value="printer">Printer</option>
                        <option value="network">Network</option>
                        <option value="others">Others</option>
                    </select>

                    <label for="issue">Issue Description:</label>
                    <input type="text" id="issue" name="issue" required />

                </div>
                <button type="submit">Submit Request</button>
            </form>
        </div>

    <script src="../assets/js/send_request.js"></script>
</body>
</html>