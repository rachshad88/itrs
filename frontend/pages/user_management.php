<?php
session_start();
require_once "../../backend/config/db.php";

// Protect Page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../frontend/pages/index.php");
    exit;
}

$message = "";

// ADD USER
if (isset($_POST['add_user'])) {
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $password = md5($_POST['password']); // MD5 hash
    $role = $_POST['role'];
    $status = $_POST['status'];

    try {
        $stmt = $conn->prepare("
            INSERT INTO users(username, password, full_name, role, status)
            VALUES (:username, :password, :full_name, :role, :status)
        ");
        $stmt->execute([
            ':username' => $username,
            ':password' => $password,
            ':full_name' => $full_name,
            ':role' => $role,
            ':status' => $status
        ]);
        $message = "User added successfully!";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// UPDATE USER
if (isset($_POST['update_user'])) {
    $id = $_POST['id'];
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    try {
        $stmt = $conn->prepare("
            UPDATE users SET 
                username=:username,
                full_name=:full_name,
                role=:role,
                status=:status
            WHERE id=:id
        ");
        $stmt->execute([
            ':username' => $username,
            ':full_name' => $full_name,
            ':role' => $role,
            ':status' => $status,
            ':id' => $id
        ]);
        $message = "User updated successfully!";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// FETCH USERS
$users = $conn->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Management</title>
<link rel="stylesheet" href="../../frontend/assets/css/dashboard.css">
<style>
/* Modal Styles */
.modal { display: none; position: fixed; z-index: 1000; padding-top: 100px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); }
.modal-content { background-color: #fefefe; margin: auto; padding: 20px; border: 1px solid #888; width: 400px; border-radius: 8px; }
.close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
.close:hover { color: black; }
button { cursor: pointer; }
</style>
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>

<h1>User Management</h1>
<?php if ($message) echo "<p style='color:green;'>$message</p>"; ?>

<!-- Button to open Add User Modal -->
<button id="openAddModal">Add Employee</button>

<hr>

<!-- Employee List -->
<table border="1" cellpadding="10">
<thead>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Username</th>
    <th>Role</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>
<tbody>
<?php foreach ($users as $u): ?>
<tr>
<td><?= $u['id'] ?></td>
<td><?= $u['full_name'] ?></td>
<td><?= $u['username'] ?></td>
<td><?= $u['role'] ?></td>
<td><?= $u['status'] ?></td>
<td>
    <button class="editBtn"
        data-id="<?= $u['id'] ?>"
        data-full_name="<?= htmlspecialchars($u['full_name']) ?>"
        data-username="<?= htmlspecialchars($u['username']) ?>"
        data-role="<?= $u['role'] ?>"
        data-status="<?= $u['status'] ?>">Edit</button>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<!-- Add User Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeAdd">&times;</span>
        <h2>Add Employee</h2>
        <form method="POST">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role" required>
                <option disabled selected>Select Role</option>
                <option value="ADMIN">ADMIN</option>
                <option value="TECHNICIAN">TECHNICIAN</option>
            </select>
            <select name="status" required>
                <option disabled selected>Select Status</option>
                <option value="AVAILABLE">AVAILABLE</option>
                <option value="BUSY">BUSY</option>
            </select>
            <button type="submit" name="add_user">Add Employee</button>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeEdit">&times;</span>
        <h2>Edit Employee</h2>
        <form method="POST">
            <input type="hidden" name="id" id="edit_id">
            <input type="text" name="full_name" id="edit_full_name" required>
            <input type="text" name="username" id="edit_username" required>
            <select name="role" id="edit_role">
                <option value="ADMIN">ADMIN</option>
                <option value="TECHNICIAN">TECHNICIAN</option>
            </select>
            <select name="status" id="edit_status">
                <option value="AVAILABLE">AVAILABLE</option>
                <option value="BUSY">BUSY</option>
            </select>
            <button type="submit" name="update_user">Update Employee</button>
        </form>
    </div>
</div>

<script>
// Add Modal
var addModal = document.getElementById("addModal");
var addBtn = document.getElementById("openAddModal");
var closeAdd = document.getElementById("closeAdd");
addBtn.onclick = () => addModal.style.display = "block";
closeAdd.onclick = () => addModal.style.display = "none";

// Edit Modal
var editModal = document.getElementById("editModal");
var closeEdit = document.getElementById("closeEdit");
closeEdit.onclick = () => editModal.style.display = "none";

// Open Edit Modal with prefilled data
var editButtons = document.getElementsByClassName("editBtn");
Array.from(editButtons).forEach(btn => {
    btn.onclick = function() {
        document.getElementById("edit_id").value = this.dataset.id;
        document.getElementById("edit_full_name").value = this.dataset.full_name;
        document.getElementById("edit_username").value = this.dataset.username;
        document.getElementById("edit_role").value = this.dataset.role;
        document.getElementById("edit_status").value = this.dataset.status;
        editModal.style.display = "block";
    }
});

// Close modals if clicking outside
window.onclick = function(event) {
    if (event.target == addModal) addModal.style.display = "none";
    if (event.target == editModal) editModal.style.display = "none";
}
</script>

</body>
</html>
