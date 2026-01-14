<?php
session_start();
require __DIR__ . "/db.php"; // adjust if needed

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';

    // MD5 password check
    $hashed = md5($password);

    $stmt = $conn->prepare("
        SELECT *
        FROM users 
        WHERE username = ? AND password = ?
        LIMIT 1
    ");
    $stmt->execute([$username, $hashed]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["full_name"] = $user["full_name"];
        $_SESSION["role"] = $user["role"];

        // redirect to dashboard
        header("Location:../../frontend/pages/dashboard.php");
        exit;
    } else {
        echo "<script>
            alert('Invalid username or password');
            window.location.href='index.php';
        </script>";
    }
}
?>
