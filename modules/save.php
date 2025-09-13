<?php
require_once '../src/Database.php';
require_once '../src/Module.php';

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
    $manager = new Module($db);
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $entity = $manager->findById((int)$_POST['id']);
        if (!$entity) {
            die("Entity not found.");
        }
    } else {
        $entity = new Module($db);
    }

    $redirect_url = 'index.php';
    $post_data = $_POST;

    // Manually handle the many-to-many relationships
    $entity->conoscenze = $post_data['conoscenze'] ?? [];
    $entity->abilita = $post_data['abilita'] ?? [];
    $entity->competenze = $post_data['competenze'] ?? [];

    // Include the generic handler
    require_once '../handlers/save_handler.php';
} else {
    // Redirect if not a POST request
    header("location: index.php");
    exit;
}
?>
