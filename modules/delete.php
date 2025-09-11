<?php
require_once '../src/Database.php';
require_once '../src/Module.php';

session_start();

// Auth check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

if (isset($_GET['id'])) {
    // Configuration for the generic delete handler
    $db = Database::getInstance()->getConnection();
    $manager = new Module($db);
    $id = (int)$_GET['id'];
    $redirect_url = 'index.php';

    // Include the generic handler
    require_once '../handlers/delete_handler.php';
} else {
    // No ID provided
    $_SESSION['feedback'] = [
        'type' => 'warning',
        'message' => 'Nessun ID specificato per la cancellazione.'
    ];
    header('Location: index.php');
    exit;
}
?>
