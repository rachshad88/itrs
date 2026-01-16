<?php
session_start();
require_once "../../backend/config/db.php";

// Protect Page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'TECHNICIAN') {
    header("Location: dashboard.php");
    exit;
}

// Fetch current user info
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Prevent ADMIN from editing profile
if ($user['role'] === 'ADMIN') {
    $message = "Admins cannot edit their profile here.";
    $can_edit = false;
} else {
    $can_edit = true;
}

// Handle update form submission
$message = '';
if (isset($_POST['update_profile']) && $can_edit) {
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];

    // Optional: update password if provided
    $password_sql = '';
    $params = [
        ':full_name' => $full_name,
        ':username' => $username,
        ':id' => $user_id
    ];

    if (!empty($_POST['password'])) {
        $password = md5($_POST['password']); // MD5 hash
        $password_sql = ", password=:password";
        $params[':password'] = $password;
    }

    try {
        $sql = "UPDATE users SET full_name=:full_name, username=:username $password_sql WHERE id=:id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $message = "Profile updated successfully!";
        // Refresh session username
        $_SESSION['username'] = $username;
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }

    // Refresh user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile</title>
<link rel="stylesheet" href="../../frontend/assets/css/profile.css">
</head>
<body>

<?php include __DIR__ . '/navbar.php'; ?>

<div class="main-content">
    <h1>Your Profile</h1>

    <?php if (!empty($message)) echo "<p style='color:green;'>$message</p>"; ?>

    <?php if ($can_edit): ?>
    <form method="POST">
        <label>Full Name:</label><br>
        <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required><br><br>

        <label>Username:</label><br>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required><br><br>

        

        <label>New Password (leave blank to keep current):</label><br>
        <input type="password" name="password" placeholder="New Password"><br><br>

        <button type="submit" name="update_profile">Update Profile</button>
    </form>
    <?php else: ?>
        <p><?= $message ?></p>
    <?php endif; ?>
</div>

</body>
</html>
