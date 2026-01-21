<?php
session_start();
require "../../backend/config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'TECHNICIAN' && $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../frontend/pages/index.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Show:
// 1. Unassigned PENDING requests (excluding CANCELLED)
// 2. OR requests already accepted by this technician
$sql = "
SELECT *
FROM requests
WHERE (assigned_to IS NULL AND status != 'CANCELLED')
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
    SUM(status = 'DONE' AND assigned_to = ?) AS done_count,
    SUM(finished = 'repaired' AND assigned_to = ?) AS repaired_count,
    SUM(finished = 'beyond repair' AND assigned_to = ?) AS beyond_repair_count
FROM requests
";


$countStmt = $pdo->prepare($countSql);
$countStmt->execute([$userId, $userId, $userId, $userId]);
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
                <th>Created</th>
                <th>Completed</th>
                <th>Action</th>
            </tr>
        </thead>
       <tbody>
<?php foreach ($requests as $r): ?>
<tr>
    <td><?= $r['request_code'] ?></td>
    <td><?= htmlspecialchars($r['office']) ?></td>
    <td><?= htmlspecialchars($r['issue']) ?></td>
    <td><?= $r['status'] ?></td>
    <td><?= htmlspecialchars($r['created_at']) ?></td>
    <td><?= htmlspecialchars($r['completed_at']) ?></td>
    <td>
       <?php if ($r['status'] === 'CANCELLED'): ?>
        Request Cancelled
    <?php elseif ($r['assigned_to'] == null): ?>
        <form action="../../backend/requests/accept_request.php" method="POST">
            <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
            <button type="submit">Accept</button>
        </form>
    <?php elseif ($r['status'] === 'IN_PROGRESS'): ?>
        <button type="button" class="mark-done-btn" data-request-code="<?= $r['request_code'] ?>" onclick="openFinishModal(this)">Mark Done</button>
    <?php else: ?>
        Completed
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
        <p>Please mark the status of this repair:</p>
        <div class="modal-buttons" style="margin-bottom: 20px;">
            <button type="button" class="btn-repaired" onclick="selectRepairStatus('repaired')" style="margin-right: 10px;">Repaired</button>
            <button type="button" class="btn-beyond-repair" onclick="selectRepairStatus('beyond repair')">Beyond Repair</button>
        </div>
        
        <div id="selectedStatus" style="font-weight: bold; margin-bottom: 15px;"></div>

        <label for="remarks">Remarks</label>
        <textarea id="remarks" name="remarks" style="width: 100%; height: 60px; margin-bottom: 10px;"></textarea>

        <label for="recommendation">Recommendation</label>
        <textarea id="recommendation" name="recommendation" style="width: 100%; height: 60px; margin-bottom: 15px;"></textarea>

        <button type="button" onclick="submitFinishRequest()" style="width: 100%; padding: 10px; background-color: #4CAF50; color: white; border: none; cursor: pointer; border-radius: 4px;">Submit</button>
    </div>
</div>

<style>
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 10% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 400px;
        border-radius: 8px;
    }

    .close-btn {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close-btn:hover {
        color: black;
    }

    .btn-repaired, .btn-beyond-repair {
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }

    .btn-repaired {
        background-color: #4CAF50;
        color: white;
    }

    .btn-beyond-repair {
        background-color: #f44336;
        color: white;
    }
</style>

<script>
let currentRequestCode = '';
let selectedRepairStatus = '';

function openFinishModal(button) {
    currentRequestCode = button.getAttribute('data-request-code');
    selectedRepairStatus = '';
    document.getElementById('remarks').value = '';
    document.getElementById('recommendation').value = '';
    document.getElementById('selectedStatus').textContent = '';
    document.getElementById('finishModal').style.display = 'block';
}

function closeFinishModal() {
    document.getElementById('finishModal').style.display = 'none';
    currentRequestCode = '';
    selectedRepairStatus = '';
}

function selectRepairStatus(status) {
    selectedRepairStatus = status;
    document.getElementById('selectedStatus').textContent = 'Status: ' + (status === 'repaired' ? 'Repaired' : 'Beyond Repair');
}

function submitFinishRequest() {
    if (!selectedRepairStatus) {
        alert('Please select a repair status');
        return;
    }

    const remarks = document.getElementById('remarks').value;
    const recommendation = document.getElementById('recommendation').value;

    const formData = new FormData();
    formData.append('request_code', currentRequestCode);
    formData.append('finish_status', selectedRepairStatus);
    formData.append('remarks', remarks);
    formData.append('recommendation', recommendation);

    fetch('../../backend/requests/request_finish.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Close modal if user clicks outside of it
window.onclick = function(event) {
    const modal = document.getElementById('finishModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<script src="../assets/js/dashboard.js"></script>

</body>
</html>
