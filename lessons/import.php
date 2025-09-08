<?php
session_start();
// Auth check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/Lesson.php';

// Default feedback
$_SESSION['import_feedback'] = ['type' => 'danger', 'message' => 'Si è verificato un errore sconosciuto.'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["jsonFile"])) {
    $file = $_FILES["jsonFile"];

    // Check for upload errors
    if ($file["error"] !== UPLOAD_ERR_OK) {
        $_SESSION['import_feedback'] = ['type' => 'danger', 'message' => 'Errore durante il caricamento del file. Codice: ' . $file["error"]];
        header("location: index.php");
        exit;
    }

    $content = file_get_contents($file["tmp_name"]);
    $lessonsData = json_decode($content, true);

    // Check for JSON errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        $_SESSION['import_feedback'] = ['type' => 'danger', 'message' => 'Errore di parsing del JSON: ' . json_last_error_msg()];
        header("location: index.php");
        exit;
    }

    if (is_array($lessonsData)) {
        $success_count = 0;
        $failure_count = 0;

        foreach ($lessonsData as $lessonData) {
            if (isset($lessonData['title']) && isset($lessonData['content'])) {
                $lesson = new Lesson([
                    'title' => $lessonData['title'],
                    'content' => $lessonData['content'],
                    'tags' => $lessonData['tags'] ?? ''
                ]);

                if ($lesson->save()) {
                    $success_count++;
                } else {
                    $failure_count++;
                }
            } else {
                $failure_count++;
            }
        }

        $message = "Importazione completata. Lezioni aggiunte con successo: $success_count.";
        if ($failure_count > 0) {
            $message .= " Lezioni non riuscite: $failure_count.";
            $_SESSION['import_feedback'] = ['type' => 'warning', 'message' => $message];
        } else {
            $_SESSION['import_feedback'] = ['type' => 'success', 'message' => $message];
        }

    } else {
        $_SESSION['import_feedback'] = ['type' => 'danger', 'message' => 'Il file JSON non contiene un array di lezioni valido.'];
    }

}

// Redirect back to the lesson list
header("location: index.php");
exit;
?>
