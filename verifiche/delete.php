<?php
require_once '../src/Database.php';
require_once '../src/Verifica.php';

session_start();

// Auth check
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $success = Verifica::delete($id);

    if ($success) {
        $message = "Verifica cancellata con successo.";
    } else {
        $message = "Errore durante la cancellazione della verifica.";
    }

    $_SESSION['feedback'] = [
        'type' => $success ? 'success' : 'danger',
        'message' => $message
    ];

    header('Location: index.php');
    exit;

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
