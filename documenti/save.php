<?php
require_once '../src/Database.php';
require_once '../src/Documento.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance()->getConnection();
    $documento = new Documento($db);

    $upload_dir = '../uploads/';
    $file_name = uniqid() . '-' . basename($_FILES['file']['name']);
    $upload_file = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_file)) {
        $documento->filename = $file_name;
        $documento->file_type = $_FILES['file']['type'];
        $documento->size = $_FILES['file']['size'];
        $documento->topic = $_POST['topic'];
        $documento->description = $_POST['description'];

        if ($documento->create()) {
            header('Location: index.php');
            exit();
        } else {
            echo 'Errore nel salvataggio del documento nel database.';
        }
    } else {
        echo 'Errore nel caricamento del file.';
    }
}
?>
