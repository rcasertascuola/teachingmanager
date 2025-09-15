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
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit;
        } else {
            echo 'File non trovato.';
        }
    } else {
        echo 'Documento non trovato.';
    }
} else {
    echo 'ID non specificato.';
}
?>
