<?php
session_start();
// Auth check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/Lesson.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic validation
    if (isset($_POST['title']) && isset($_POST['content'])) {

        $data = [
            'id' => isset($_POST['id']) && !empty($_POST['id']) ? (int)$_POST['id'] : null,
            'title' => trim($_POST['title']),
            'content' => trim($_POST['content']),
            'tags' => isset($_POST['tags']) ? trim($_POST['tags']) : ''
        ];

        $lesson = new Lesson($data);
        $lesson->save();
    }
}

// Redirect back to the lesson list
header("location: index.php");
exit;
?>
