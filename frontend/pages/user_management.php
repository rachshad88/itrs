<?php
session_start();
require_once "../../backend/config/db.php";

// Protect Page para di masearch
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: dashboard.php");
    exit;
}


$message = "";

// ADD USER
if (isset($_POST['add_user'])) {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'] ?? null;
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $password = md5($_POST['password']); // MD5 hash
    $role = $_POST['role'];

    try {
        $stmt = $pdo->prepare("
            INSERT INTO users(username, password, first_name, middle_name, last_name, role)
            VALUES (:username, :password, :first_name, :middle_name, :last_name, :role)
        ");
        $stmt->execute([
            ':username' => $username,
            ':password' => $password,
            ':first_name' => $first_name,
            ':middle_name' => $middle_name,
            ':last_name' => $last_name,
            ':role' => $role,
        ]);
        $message = "User added successfully!";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// UPDATE USER
if (isset($_POST['update_user'])) {
    $id = $_POST['id'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'] ?? null;
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $role = $_POST['role'];

    try {
        $stmt = $pdo->prepare("
            UPDATE users SET 
                username=:username,
                first_name=:first_name,
                middle_name=:middle_name,
                last_name=:last_name,
                role=:role
            WHERE id=:id
        ");
        $stmt->execute([
            ':username' => $username,
            ':first_name' => $first_name,
            ':middle_name' => $middle_name,
            ':last_name' => $last_name,
            ':role' => $role,
            ':id' => $id
        ]);
        $message = "User updated successfully!";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// FETCH USERS
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
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
    <th>Action</th>
</tr>
</thead>
<tbody>
<?php foreach ($users as $u): 
    $fullName = $u['first_name'] . ' ' . ($u['middle_name'] ? $u['middle_name'] . ' ' : '') . $u['last_name'];
?>
<tr>
<td><?= $u['id'] ?></td>
<td><?= htmlspecialchars($fullName) ?></td>
<td><?= htmlspecialchars($u['username']) ?></td>
<td><?= $u['role'] ?></td>
<td>
    <button class="editBtn"
        data-id="<?= $u['id'] ?>"
        data-first_name="<?= htmlspecialchars($u['first_name']) ?>"
        data-middle_name="<?= htmlspecialchars($u['middle_name'] ?? '') ?>"
        data-last_name="<?= htmlspecialchars($u['last_name']) ?>"
        data-username="<?= htmlspecialchars($u['username']) ?>"
        data-role="<?= $u['role'] ?>"
    >Edit</button>
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
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="middle_name" placeholder="Middle Name (Optional)">
            <input type="text" name="last_name" placeholder="Last Name" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role" required>
                <option disabled selected>Select Role</option>
                <option value="ADMIN">ADMIN</option>
                <option value="TECHNICIAN">TECHNICIAN</option>
                <option value="CLIENT">CLIENT</option>
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
            <input type="text" name="first_name" id="edit_first_name" required>
            <input type="text" name="middle_name" id="edit_middle_name">
            <input type="text" name="last_name" id="edit_last_name" required>
            <input type="text" name="username" id="edit_username" required>
            <select name="role" id="edit_role">
                <option value="ADMIN">ADMIN</option>
                <option value="TECHNICIAN">TECHNICIAN</option>
                <option value="CLIENT">CLIENT</option>
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
        document.getElementById("edit_first_name").value = this.dataset.first_name;
        document.getElementById("edit_middle_name").value = this.dataset.middle_name;
        document.getElementById("edit_last_name").value = this.dataset.last_name;
        document.getElementById("edit_username").value = this.dataset.username;
        document.getElementById("edit_role").value = this.dataset.role;
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
