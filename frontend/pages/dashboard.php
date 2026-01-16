<?php
session_start();
require "../../backend/config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../frontend/pages/index.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Show:
// 1. Unassigned requests
// 2. OR requests already accepted by this technician
$sql = "
SELECT *
FROM requests
WHERE assigned_to IS NULL
   OR assigned_to = ?
ORDER BY created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Status counters for this technician
$countSql = "
SELECT 
    SUM(status = 'PENDING' AND assigned_to IS NULL) AS pending_count,
    SUM(status = 'IN_PROGRESS' AND assigned_to = ?) AS progress_count,
    SUM(status = 'DONE' AND assigned_to = ?) AS done_count
FROM requests
";



$countStmt = $pdo->prepare($countSql);
$countStmt->execute([$userId, $userId]);
$counts = $countStmt->fetch();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MMO IT Service Request Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>

<body>

<?php include __DIR__ . '/navbar.php'; ?>

    <div class="main-content">
        <h1>Technician Dashboard</h1>

        <div class="stats-boxes">
        <div class="stat-card">
            <h3>Pending</h3>
            <p><?= $counts['pending_count'] ?? 0 ?></p>
        </div>

        <div class="stat-card">
            <h3>In Progress</h3>
            <p><?= $counts['progress_count'] ?? 0 ?></p>
        </div>

        <div class="stat-card">
            <h3>Completed</h3>
            <p><?= $counts['done_count'] ?? 0 ?></p>
        </div>
    </div>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Request Code</th>
                <th>Client Name</th>
                <th>Office</th>
                <th>Issue</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>
       <tbody>
<?php foreach ($requests as $r): ?>
<tr>
    <td><?= $r['request_code'] ?></td>
    <td><?= htmlspecialchars($r['client_name']) ?></td>
    <td><?= htmlspecialchars($r['office']) ?></td>
    <td><?= htmlspecialchars($r['issue']) ?></td>
    <td><?= $r['status'] ?></td>
    <td><?= htmlspecialchars($r['created_at']) ?></td>
    <td>
       <?php if ($r['assigned_to'] == null): ?>
        <form action="../../backend/requests/accept_request.php" method="POST">
            <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
            <button type="submit">Accept</button>
        </form>
    <?php else: ?>
        Assigned to you
    <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody>

    </table>
</div>
<script src="../assets/js/dashboard.js"></script>

</body>
</html>
