<?php
// navbar.php
// session_start(); // make sure session is started
$user_role = $_SESSION['role'] ?? ''; // get role from session
?>
<nav class="navbar">
    <img src="../assets/images/solano1.png" alt="MMO Logo" class="logo" />
    <ul class="nav-links">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="reports.php">Accomplishment Reports</a></li>
        
        <?php if ($user_role === 'TECHNICIAN'): ?>
            <li><a href="profile.php">Profile</a></li>
        <?php endif; ?>

        <?php if ($user_role === 'ADMIN'): ?>
            <li><a href="user_management.php">User Management</a></li>
        <?php endif; ?>

        <li><a href="../../backend/config/logout.php">Logout</a></li>
    </ul>
</nav>
