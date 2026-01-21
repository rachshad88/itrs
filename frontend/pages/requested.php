<?php
session_start();
require "../../backend/config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'CLIENT') {
    header("Location: ../../frontend/pages/index.php");
    exit;
}

$userId = $_SESSION['user_id'];

//Request made by this client
$sql = "
SELECT *
FROM requests
WHERE created_by = ?
ORDER BY created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Status counters for this client
$countSql = "
SELECT 
    SUM(status = 'PENDING') AS pending_count,
    SUM(status = 'IN_PROGRESS') AS progress_count,
    SUM(status = 'DONE') AS done_count,
    SUM(finished = 'repaired') AS repaired_count,
    SUM(finished = 'beyond repair') AS beyond_repair_count
FROM requests
WHERE created_by = ?
";

$countStmt = $pdo->prepare($countSql);
$countStmt->execute([$userId]);
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
<?php include __DIR__ . '/navbar.php'; ?>
<body>



    <div class="main-content">
        <h1>Requested Services</h1>

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
                <th>Issue</th>
                <th>Status</th>
                <th>Created</th>
                <th>Completed</th>
                <th>Action</th>
            </tr>
        </thead>
       <tbody>
<?php foreach ($requests as $r): ?>
<tr>
    <td><?= $r['request_code'] ?></td>
    <td><?= htmlspecialchars($r['issue']) ?></td>
    <td><?= $r['status'] ?></td>
    <td><?= htmlspecialchars($r['created_at']) ?></td>
    <td><?= htmlspecialchars($r['completed_at']) ?></td>
    <td>
       <?php if ($r['status'] === 'PENDING' && $r['assigned_to'] == null): ?>
        <form action="../../backend/requests/cancel_request.php" method="POST">
            <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
            <button type="submit">Cancel</button>
        </form>
    <?php else: ?>
        <span>-</span>
    <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody>

    </table>
</div>

</body>
</html>
