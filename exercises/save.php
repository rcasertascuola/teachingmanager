<?php
require_once '../src/Database.php';
require_once '../src/Exercise.php';

session_start();

// Auth check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'teacher') {
    header("location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Configuration for the generic save handler
    $db = Database::getInstance()->getConnection();

    // The generic handler needs an entity to populate.
    $manager = new Exercise($db);
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $entity = $manager->findById((int)$_POST['id']);
        if (!$entity) {
            die("Entity not found.");
        }
    } else {
        $entity = new Exercise($db);
    }

    // Specific logic for this entity
    $_POST['enabled'] = isset($_POST['enabled']) ? 1 : 0;

    $redirect_url = 'index.php';
    $post_data = $_POST;

    // We need to handle the lesson links after saving
    $lessonIds = $_POST['lessons'] ?? [];
    unset($post_data['lessons']); // Don't try to save this to the exercise table

    // Save the main entity first
    foreach ($post_data as $key => $value) {
        if (property_exists($entity, $key)) {
            $entity->$key = $value;
        }
    }
    $result = $entity->save();

    if ($result === true) {
        // Now handle the linked lessons
        if ($entity->id) {
            $linkResult = $entity->updateLinkedLessons($lessonIds);
            if ($linkResult) {
                $_SESSION['feedback'] = ['type' => 'success', 'message' => 'Esercizio salvato con successo.'];
            } else {
                 $_SESSION['feedback'] = ['type' => 'warning', 'message' => 'Esercizio salvato, ma si è verificato un errore nel collegamento con le lezioni.'];
            }
        }
        $action = isset($post_data['id']) && !empty($post_data['id']) ? 'update' : 'create';
        header("Location: " . $redirect_url . "?success=" . $action);
    } else {
        $_SESSION['feedback'] = ['type' => 'danger', 'message' => 'Errore nel salvataggio: ' . htmlspecialchars($result)];
        // Redirect back to the edit form to show the error
        $id_param = isset($post_data['id']) ? '?id=' . $post_data['id'] : '';
        header("Location: edit.php" . $id_param);
    }
    exit;

} else {
    // Redirect if not a POST request
    header("location: index.php");
    exit;
}
?>
