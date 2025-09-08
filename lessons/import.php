<?php
session_start();
// Auth check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/Lesson.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["jsonFile"])) {
    $file = $_FILES["jsonFile"];

    // Check for errors and validate file type
    if ($file["error"] == 0 && $file["type"] == "application/json") {
        $content = file_get_contents($file["tmp_name"]);
        $lessonsData = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($lessonsData)) {
            foreach ($lessonsData as $lessonData) {
                // Basic validation for each lesson object
                if (isset($lessonData['title']) && isset($lessonData['content'])) {
                    $lesson = new Lesson([
                        'title' => $lessonData['title'],
                        'content' => $lessonData['content'],
                        'tags' => $lessonData['tags'] ?? ''
                    ]);
                    $lesson->save();
                }
            }
        }
    }
}

// Redirect back to the lesson list
header("location: index.php");
exit;
?>
