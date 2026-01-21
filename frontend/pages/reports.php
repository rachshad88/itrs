<?php
session_start();
require "../../backend/config/db.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'TECHNICIAN' && $_SESSION['role'] !== 'ADMIN')) {
    header("Location: ../../frontend/pages/index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$filterType = $_GET['filter'] ?? 'daily';
$reportData = [];

// Determine date range based on filter
$endDate = date('Y-m-d');
switch($filterType) {
    case 'daily':
        $startDate = date('Y-m-d');
        break;
    case 'weekly':
        $startDate = date('Y-m-d', strtotime('-7 days'));
        break;
    case 'monthly':
        $startDate = date('Y-m-01');
        break;
    default:
        $startDate = date('Y-m-d');
        $filterType = 'daily';
}

// Get completed requests for the current technician or all technicians if admin
$sql = "
SELECT 
    r.id,
    r.request_code,
    r.office,
    r.unit,
    r.issue,
    r.finished,
    r.remarks,
    r.recommendation,
    r.completed_at,
    CONCAT(u.first_name, ' ', COALESCE(u.middle_name, ''), ' ', u.last_name) AS technician_name,
    CONCAT(c.first_name, ' ', COALESCE(c.middle_name, ''), ' ', c.last_name) AS client_name
FROM requests r
LEFT JOIN users u ON r.assigned_to = u.id
LEFT JOIN users c ON r.created_by = c.id
WHERE r.status = 'DONE'
    AND DATE(r.completed_at) BETWEEN ? AND ?
";

// Filter by technician if not admin
if ($_SESSION['role'] !== 'ADMIN') {
    $sql .= " AND r.assigned_to = ?";
    $params = [$startDate, $endDate, $userId];
} else {
    $params = [$startDate, $endDate];
}

$sql .= " ORDER BY r.completed_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$statsSql = "
SELECT 
    COUNT(*) AS total_completed,
    SUM(r.finished = 'repaired') AS repaired_count,
    SUM(r.finished = 'beyond repair') AS beyond_repair_count
FROM requests r
WHERE r.status = 'DONE'
    AND DATE(r.completed_at) BETWEEN ? AND ?
";

if ($_SESSION['role'] !== 'ADMIN') {
    $statsSql .= " AND r.assigned_to = ?";
    $statsParams = [$startDate, $endDate, $userId];
} else {
    $statsParams = [$startDate, $endDate];
}

$statsStmt = $pdo->prepare($statsSql);
$statsStmt->execute($statsParams);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accomplishment Reports</title>
    <link rel="stylesheet" href="../../frontend/assets/css/reports.css">
</head>
<body>
    <?php include __DIR__ . '/navbar.php'; ?>
    
    <div class="report-container">
        <h1>Accomplishment Reports</h1>
        <div class="date-range">
            From: <strong><?= date('M d, Y', strtotime($startDate)) ?></strong> 
            To: <strong><?= date('M d, Y', strtotime($endDate)) ?></strong>
        </div>
        
        <div class="filter-buttons">
            <a href="?filter=daily" class="filter-btn <?= $filterType === 'daily' ? 'active' : '' ?>">Daily</a>
            <a href="?filter=weekly" class="filter-btn <?= $filterType === 'weekly' ? 'active' : '' ?>">Weekly</a>
            <a href="?filter=monthly" class="filter-btn <?= $filterType === 'monthly' ? 'active' : '' ?>">Monthly</a>
        </div>
        
        <?php if (!empty($stats['total_completed']) && $stats['total_completed'] > 0): ?>
        <div class="stats-container">
            <div class="stat-box">
                <h3>Total Completed</h3>
                <div class="number"><?= $stats['total_completed'] ?></div>
            </div>
            <div class="stat-box">
                <h3>Repaired</h3>
                <div class="number"><?= $stats['repaired_count'] ?? 0 ?></div>
            </div>
            <div class="stat-box">
                <h3>Beyond Repair</h3>
                <div class="number"><?= $stats['beyond_repair_count'] ?? 0 ?></div>
            </div>
        </div>
        
        <table class="reports-table">
            <thead>
                <tr>
                    <th>Request Code</th>
                    <th>Office</th>
                    <th>Issue</th>
                    <th>Client</th>
                    <th>Technician</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th>Recommendation</th>
                    <th>Completed Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reportData as $report): ?>
                <tr>
                    <td><?= htmlspecialchars($report['request_code']) ?></td>
                    <td><?= htmlspecialchars($report['office']) ?></td>
                    <td><?= htmlspecialchars($report['issue']) ?></td>
                    <td><?= htmlspecialchars($report['client_name']) ?></td>
                    <td><?= htmlspecialchars($report['technician_name']) ?></td>
                    <td>
                        <span class="status-badge <?= $report['finished'] === 'repaired' ? 'badge-repaired' : 'badge-beyond-repair' ?>">
                            <?= ucfirst($report['finished']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($report['remarks'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($report['recommendation'] ?? '-') ?></td>
                    <td><?= date('M d, Y h:i A', strtotime($report['completed_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="no-data">
            <p>No completed requests found for the selected period.</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>