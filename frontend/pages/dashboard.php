<?php
session_start();

// Prevent accessing dashboard without login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../frontend/pages/index.php");
    exit;
} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MMO IT Service Request Dashboard</title>

    <!-- Adjust if your dashboard.php is inside frontend/pages -->
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>

<body>
<?php include __DIR__ . '/navbar.php'; ?>

    <div class="main-content">
        <h1>Technician Dashboard</h1>

        <p>Welcome to your dashboard,
            <?php echo $_SESSION['username']; ?>
        </p>

        <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Client Name</th>
                    <th>Office</th>
                    <th>Issue</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <!-- Populate later -->
            </tbody>
        </table>
    </div>
</body>
</html>
