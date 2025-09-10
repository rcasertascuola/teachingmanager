<?php
require_once '../src/Database.php';
require_once '../src/Verifica.php';

session_start();

// Auth check
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $conn = $db->getConnection();

    $verifica = new Verifica($conn);

    // Assign data from form to Verifica object
    $verifica->id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $verifica->titolo = trim($_POST['titolo']);
    $verifica->descrizione = trim($_POST['descrizione']);
    $verifica->tipo = $_POST['tipo'];
    $verifica->abilita_ids = isset($_POST['abilita']) ? $_POST['abilita'] : [];
    $verifica->competenza_ids = isset($_POST['competenze']) ? $_POST['competenze'] : [];

    // Assign griglia data
    $verifica->griglia = [
        'nome' => trim($_POST['griglia_nome']),
        'descrittori' => isset($_POST['descrittori']) ? $_POST['descrittori'] : []
    ];

    $success = $verifica->save();

    if ($success) {
        $message = "Verifica salvata con successo.";
    } else {
        $message = "Errore durante il salvataggio della verifica.";
    }

    // Set feedback message and redirect
    $_SESSION['feedback'] = [
        'type' => $success ? 'success' : 'danger',
        'message' => $message
    ];

    header('Location: index.php');
    exit;

} else {
    // Not a POST request
    header('Location: index.php');
    exit;
}
?>
