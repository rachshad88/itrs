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
    <?php elseif ($r['status'] === 'IN_PROGRESS'): ?>
        <button type="button" class="mark-done-btn" data-request-code="<?= $r['request_code'] ?>" onclick="openFinishModal(this)">Mark Done</button>
    <?php else: ?>
        <span>-</span>
    <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody>

    </table>
</div>

<!-- Finish Request Modal -->
<div id="finishModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-btn" onclick="closeFinishModal()">&times;</span>
        <h2>Request Completion Status</h2>
        <p>What is the status of this repair?</p>
        <div class="modal-buttons">
            <button type="button" class="btn-repaired" onclick="submitFinishStatus('repaired')">Repaired</button>
            <button type="button" class="btn-beyond-repair" onclick="submitFinishStatus('beyond repair')">Beyond Repair</button>
        </div>
    </div>
</div>

<style>
    .modal {
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 400px;
        border-radius: 5px;
        text-align: center;
    }

    .close-btn {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close-btn:hover,
    .close-btn:focus {
        color: black;
    }

    .modal-buttons {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-top: 20px;
    }

    .btn-repaired,
    .btn-beyond-repair {
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
    }

    .btn-repaired {
        background-color: #4CAF50;
        color: white;
    }

    .btn-repaired:hover {
        background-color: #45a049;
    }

    .btn-beyond-repair {
        background-color: #f44336;
        color: white;
    }

    .btn-beyond-repair:hover {
        background-color: #da190b;
    }
</style>

<script>
    let currentRequestCode = null;

    function openFinishModal(button) {
        currentRequestCode = button.getAttribute('data-request-code');
        document.getElementById('finishModal').style.display = 'block';
    }

    function closeFinishModal() {
        document.getElementById('finishModal').style.display = 'none';
        currentRequestCode = null;
    }

    function submitFinishStatus(status) {
        if (!currentRequestCode) {
            alert('Error: Request code not found');
            return;
        }

        fetch('../../backend/requests/request_finish.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'request_code=' + encodeURIComponent(currentRequestCode) + '&finish_status=' + encodeURIComponent(status)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Request marked as ' + status + '!');
                closeFinishModal();
                // Reload the page to show updated status
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }

    // Close modal when clicking outside of it
    window.onclick = function(event) {
        const modal = document.getElementById('finishModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }
</script>

</body>
</html>
