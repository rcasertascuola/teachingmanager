<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    exit("Accesso non autorizzato.");
}

require_once '../src/Database.php';
require_once '../src/Documento.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance()->getConnection();
    $documento = new Documento($db);

    $documento->id = $_POST['id'];
    $documento->topic = $_POST['topic'];
    $documento->description = $_POST['description'];

    if ($documento->update()) {
        header('Location: admin.php');
        exit();
    } else {
        echo 'Errore nell\'aggiornamento del documento.';
    }
}
?>
