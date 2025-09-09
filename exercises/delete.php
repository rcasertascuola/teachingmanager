<?php
session_start();
// Auth check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/Exercise.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    if (Exercise::delete((int)$_GET['id'])) {
        $_SESSION['feedback'] = ['type' => 'success', 'message' => 'Esercizio cancellato con successo.'];
    } else {
        $_SESSION['feedback'] = ['type' => 'danger', 'message' => 'Errore durante la cancellazione dell\'esercizio.'];
    }
} else {
    $_SESSION['feedback'] = ['type' => 'danger', 'message' => 'ID esercizio non specificato.'];
}

header("location: index.php");
exit;
?>
