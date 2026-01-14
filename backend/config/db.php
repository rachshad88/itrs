<?php
// Database configuration
$host = "localhost";
$dbname = "itrs";           // CHANGE to your database name
$username = "root";         // XAMPP default
$password = "";             // leave empty unless you set one

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    // PDO Settings
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Throw exceptions
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Fetch assoc arrays

} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>
