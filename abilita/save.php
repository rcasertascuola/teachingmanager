<?php
require_once '../src/Database.php';
require_once '../src/Abilita.php';

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
    // If we are editing, we find the existing one. Otherwise, we create a new one.
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $manager = new Abilita($db);
        $entity = $manager->findById((int)$_POST['id']);
        if (!$entity) {
            die("Entity not found.");
        }
    } else {
        $entity = new Abilita($db);
    }

    $redirect_url = 'index.php';
    $post_data = $_POST;

    // Include the generic handler
    require_once '../handlers/save_handler.php';
} else {
    // Redirect if not a POST request
    header("location: index.php");
    exit;
}
?>
