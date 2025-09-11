<?php
require_once '../src/Database.php';
require_once '../src/Verifica.php';

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
    $manager = new Verifica($db);
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $entity = $manager->findById((int)$_POST['id']);
        if (!$entity) {
            die("Entity not found.");
        }
    } else {
        $entity = new Verifica($db);
    }

    // Specific logic for this entity
    $_POST['abilita_ids'] = $_POST['abilita'] ?? [];
    $_POST['conoscenza_ids'] = $_POST['conoscenze'] ?? [];
    $_POST['griglia'] = [
        'nome' => trim($_POST['griglia_nome']),
        'descrittori' => isset($_POST['descrittori']) ? $_POST['descrittori'] : []
    ];

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
