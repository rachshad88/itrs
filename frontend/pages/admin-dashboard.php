<?php
session_start();
require "../../backend/config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../frontend/pages/index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$filterType = $_GET['filter'] ?? 'all';
$statusFilter = $_GET['status'] ?? '';

// Build query
$sql = "
SELECT *
FROM requests
WHERE status != 'CANCELLED'
";

$params = [];

// Filter by date range
switch($filterType) {
    case 'daily':
        $sql .= " AND DATE(created_at) = CURDATE()";
        break;
    case 'weekly':
        $sql .= " AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        break;
    case 'monthly':
        $sql .= " AND YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())";
        break;
    case 'all':
    default:
        // No date filter
        break;
}

// Filter by status if provided
if ($statusFilter && $statusFilter !== '') {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get overall statistics
$statsSql = "
SELECT 
    SUM(status = 'PENDING' AND assigned_to IS NULL) AS pending_count,
    SUM(status = 'IN_PROGRESS') AS progress_count,
    SUM(status = 'DONE') AS done_count,
    SUM(finished = 'repaired') AS repaired_count,
    SUM(finished = 'beyond repair') AS beyond_repair_count
FROM requests
WHERE status != 'CANCELLED'
";

$statsStmt = $pdo->prepare($statsSql);
$statsStmt->execute();
$counts = $statsStmt->fetch();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - All Requests</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .filter-section {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-section label {
            font-weight: bold;
            margin-right: 5px;
        }
        
        .filter-section select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .filter-section button {
            padding: 8px 15px;
            background-color: #0e639c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .filter-section button:hover {
            background-color: #1177bb;
        }
    </style>
</head>
<?php include __DIR__ . '/navbar.php'; ?>
<body>

    <div class="main-content">
        <h1>Admin Dashboard - All Requests</h1>
        <p style="color: #666; font-style: italic;">View all requests system-wide (Read-only)</p>

        <div class="filter-section">
            <form method="GET" action="" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <div>
                    <label for="filter">Time Period:</label>
                    <select name="filter" id="filter" onchange="this.form.submit()">
                        <option value="all" <?= $filterType === 'all' ? 'selected' : '' ?>>All Time</option>
                        <option value="daily" <?= $filterType === 'daily' ? 'selected' : '' ?>>Today</option>
                        <option value="weekly" <?= $filterType === 'weekly' ? 'selected' : '' ?>>This Week</option>
                        <option value="monthly" <?= $filterType === 'monthly' ? 'selected' : '' ?>>This Month</option>
                    </select>
                </div>

                <div>
                    <label for="status">Status:</label>
                    <select name="status" id="status" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="PENDING" <?= $statusFilter === 'PENDING' ? 'selected' : '' ?>>Pending</option>
                        <option value="IN_PROGRESS" <?= $statusFilter === 'IN_PROGRESS' ? 'selected' : '' ?>>In Progress</option>
                        <option value="DONE" <?= $statusFilter === 'DONE' ? 'selected' : '' ?>>Done</option>
                    </select>
                </div>
            </form>
        </div>

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

            <div class="stat-card">
                <h3>Repaired</h3>
                <p><?= $counts['repaired_count'] ?? 0 ?></p>
            </div>

            <div class="stat-card">
                <h3>Beyond Repair</h3>
                <p><?= $counts['beyond_repair_count'] ?? 0 ?></p>
            </div>
        </div>

        <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>Request Code</th>
                    <th>Office</th>
                    <th>Issue</th>
                    <th>Status</th>
                    <th>Assigned To</th>
                    <th>Created</th>
                    <th>Completed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                <tr>
                    <td><?= $r['request_code'] ?></td>
                    <td><?= htmlspecialchars($r['office']) ?></td>
                    <td><?= htmlspecialchars($r['issue']) ?></td>
                    <td>
                        <span style="
                            padding: 5px 10px;
                            border-radius: 3px;
                            font-weight: bold;
                            <?php
                            if ($r['status'] === 'PENDING') echo 'background-color: #fff3cd; color: #333;';
                            elseif ($r['status'] === 'IN_PROGRESS') echo 'background-color: #cfe2ff; color: #084298;';
                            elseif ($r['status'] === 'DONE') echo 'background-color: #d1e7dd; color: #0f5132;';
                            ?>
                        ">
                            <?= $r['status'] ?>
                        </span>
                    </td>
                    <td><?= $r['assigned_to'] ? 'Assigned' : 'Unassigned' ?></td>
                    <td><?= htmlspecialchars($r['created_at']) ?></td>
                    <td><?= htmlspecialchars($r['completed_at'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.socket.io/4.8.3/socket.io.min.js"></script>
    <script src="../../frontend/assets/js/realtime.js"></script>
    <script>
        window.addEventListener('load', function() {
            realtimeClient.init(<?php echo $_SESSION['user_id']; ?>);
            
            // Admin should refresh page when status changes
            document.addEventListener('request-updated', function(event) {
                console.log('Admin dashboard received request update');
                location.reload();
            });
        });
    </script>

</body>
</html>
