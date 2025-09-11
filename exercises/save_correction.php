<?php
session_start();
// Auth check - only teachers can save corrections
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/Exercise.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['exercise_id'], $_POST['scores'])) {
    $exerciseId = (int)$_POST['exercise_id'];
    $scores = $_POST['scores'];
    $teacherId = $_SESSION['id'];

    $success_count = 0;
    $error_count = 0;

    // Get the database connection
    $db = Database::getInstance()->getConnection();
    $exercise_manager = new Exercise($db);

    foreach ($scores as $answerId => $score) {
        // Only update if a score is actually entered
        if ($score !== '') {
            if ($exercise_manager->saveCorrection((int)$answerId, (float)$score, $teacherId)) {
                $success_count++;
            } else {
                $error_count++;
            }
        }
    }

    if ($error_count > 0) {
        $_SESSION['feedback'] = ['type' => 'warning', 'message' => "Correzioni salvate con $success_count successi e $error_count errori."];
    } else {
        $_SESSION['feedback'] = ['type' => 'success', 'message' => "Tutte le $success_count correzioni sono state salvate con successo."];
    }

    header("location: correction.php?id=" . $exerciseId);
    exit;

} else {
    $_SESSION['feedback'] = ['type' => 'danger', 'message' => 'Dati di correzione non validi.'];
    header("location: index.php");
    exit;
}
?>
