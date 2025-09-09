<?php
session_start();
// Auth check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/Exercise.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Basic validation
    if (empty(trim($_POST['title']))) {
        $_SESSION['feedback'] = ['type' => 'danger', 'message' => 'Il titolo è obbligatorio.'];
        header("location: index.php");
        exit;
    }
    if (empty(trim($_POST['content']))) {
        $_SESSION['feedback'] = ['type' => 'danger', 'message' => 'Il contenuto è obbligatorio.'];
        header("location: index.php");
        exit;
    }

    // Validate options JSON
    $options = trim($_POST['options']);
    if (!empty($options)) {
        json_decode($options);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $_SESSION['feedback'] = ['type' => 'danger', 'message' => 'Il formato JSON delle opzioni non è valido.'];
            // In a real app, you'd redirect back to the edit form with the user's input
            header("location: index.php");
            exit;
        }
    }


    $data = [
        'id' => isset($_POST['id']) && !empty($_POST['id']) ? (int)$_POST['id'] : null,
        'title' => trim($_POST['title']),
        'type' => $_POST['type'],
        'content' => trim($_POST['content']),
        'options' => $options,
        'enabled' => isset($_POST['enabled']) ? 1 : 0
    ];

    $exercise = new Exercise($data);
    $result = $exercise->save();

    if ($result === true) {
        $lessonIds = $_POST['lessons'] ?? [];
        if ($exercise->id) {
            $linkResult = $exercise->updateLinkedLessons($lessonIds);
            if ($linkResult) {
                $_SESSION['feedback'] = ['type' => 'success', 'message' => 'Esercizio salvato con successo.'];
            } else {
                 $_SESSION['feedback'] = ['type' => 'warning', 'message' => 'Esercizio salvato, ma si è verificato un errore nel collegamento con le lezioni.'];
            }
        }
    } else {
        $_SESSION['feedback'] = ['type' => 'danger', 'message' => 'Errore nel salvataggio dell\'esercizio: ' . htmlspecialchars($result)];
    }

} else {
    $_SESSION['feedback'] = ['type' => 'danger', 'message' => 'Metodo di richiesta non valido.'];
}

header("location: index.php");
exit;
?>
