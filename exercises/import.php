<?php
session_start();
// Auth check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/Exercise.php';
require_once '../src/Lesson.php'; // Needed to find lessons by title

$_SESSION['feedback'] = ['type' => 'danger', 'message' => 'Si è verificato un errore sconosciuto durante l\'importazione.'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["jsonFile"])) {
    $file = $_FILES["jsonFile"];

    if ($file["error"] !== UPLOAD_ERR_OK) {
        $_SESSION['feedback'] = ['type' => 'danger', 'message' => 'Errore durante il caricamento del file. Codice: ' . $file["error"]];
        header("location: index.php");
        exit;
    }

    $content = file_get_contents($file["tmp_name"]);
    $exercisesData = json_decode($content, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $_SESSION['feedback'] = ['type' => 'danger', 'message' => 'Errore di parsing del JSON: ' . json_last_error_msg()];
        header("location: index.php");
        exit;
    }

    if (is_array($exercisesData)) {
        $success_count = 0;
        $failures = [];

        // Get the database connection
        $db = Database::getInstance()->getConnection();
        $lesson_manager = new Lesson($db);

        foreach ($exercisesData as $exData) {
            if (empty($exData['title']) || empty($exData['type']) || !isset($exData['content'])) {
                $failures[] = "Un esercizio è stato saltato per mancanza di 'title', 'type', or 'content'. Dati: " . json_encode($exData);
                continue;
            }

            $exercise = new Exercise([
                'title' => $exData['title'],
                'type' => $exData['type'],
                'content' => $exData['content'],
                'options' => isset($exData['options']) ? json_encode($exData['options']) : '{}',
                'enabled' => $exData['enabled'] ?? 0
            ]);

            $result = $exercise->save(); // TODO: Refactor Exercise class

            if ($result === true) {
                $success_count++;
                // Handle lesson links
                if (!empty($exData['lesson_links']) && is_array($exData['lesson_links'])) {
                    $lessonIds = [];
                    foreach ($exData['lesson_links'] as $lessonTitle) {
                        $lesson = $lesson_manager->findByTitle($lessonTitle);
                        if ($lesson) {
                            $lessonIds[] = $lesson->id;
                        } else {
                            $failures[] = "Lezione non trovata per il titolo: '" . htmlspecialchars($lessonTitle) . "' per l'esercizio '" . htmlspecialchars($exData['title']) . "'.";
                        }
                    }
                    if (!empty($lessonIds)) {
                        $exercise->updateLinkedLessons($lessonIds); // TODO: Refactor Exercise class
                    }
                }
            } else {
                $failures[] = "Esercizio '" . htmlspecialchars($exData['title']) . "': " . htmlspecialchars($result);
            }
        }

        $message = "Importazione completata. Esercizi aggiunti con successo: $success_count.";
        if (!empty($failures)) {
            $message .= " Errori o avvisi: " . count($failures) . ".<br><ul>";
            foreach($failures as $failure) {
                $message .= "<li>$failure</li>";
            }
            $message .= "</ul>";
            $_SESSION['feedback'] = ['type' => 'warning', 'message' => $message];
        } else {
            $_SESSION['feedback'] = ['type' => 'success', 'message' => $message];
        }

    } else {
        $_SESSION['feedback'] = ['type' => 'danger', 'message' => 'Il file JSON non contiene un array di esercizi valido.'];
    }

}

header("location: index.php");
exit;
?>
