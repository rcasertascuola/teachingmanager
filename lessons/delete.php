<?php
session_start();
// Auth check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/Lesson.php';

// Check if an ID was provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    Lesson::delete((int)$_GET['id']);
}

// Redirect back to the lesson list
header("location: index.php");
exit;
?>
