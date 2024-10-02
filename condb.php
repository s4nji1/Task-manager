<?php
$host = 'localhost';
$db = 'task_manager';  // Change this to match your actual database name
$user = 'root';  // Change if needed
$pass = '';  // Add your MySQL password if needed

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
