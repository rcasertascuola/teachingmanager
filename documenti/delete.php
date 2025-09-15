<?php
if (isset($_GET['id'])) {
    require_once '../src/Database.php';
    require_once '../src/Documento.php';

    $db = Database::getInstance()->getConnection();
    $documento = new Documento($db);
    $documento->id = $_GET['id'];
    $documento->readOne();

    if ($documento->filename) {
        $file_path = '../uploads/' . $documento->filename;

        if (file_exists($file_path)) {
            unlink($file_path);
        }

        if ($documento->delete()) {
            header('Location: index.php');
            exit();
        } else {
            echo 'Errore nella cancellazione del documento dal database.';
        }
    } else {
        echo 'Documento non trovato.';
    }
} else {
    echo 'ID non specificato.';
}
?>
