<?php
session_start();
require_once '../src/Database.php';
require_once '../src/User.php';

// Check if user is a teacher
if ($_SESSION['role'] !== 'teacher') {
    header("Location: /dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && isset($_POST['status'])) {
    $db = Database::getInstance()->getConnection();
    $user = new User($db);

    $userId = $_POST['id'];
    $newStatus = $_POST['status'];

    // Basic validation for status
    $allowed_statuses = ['pending', 'active', 'disabled'];
    if (in_array($newStatus, $allowed_statuses)) {
        $user->updateStatus($userId, $newStatus);
    }
}

header("Location: index.php");
exit;
?>
