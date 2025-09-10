<?php
require_once '../src/Database.php';
require_once '../src/TipologiaCompetenza.php';

session_start();


if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'teacher') {

    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id' => $_POST['id'] ?? null,
        'nome' => $_POST['nome']
    ];

    $tipologia = new TipologiaCompetenza($data);

    if ($tipologia->save()) {
        $message = $data['id'] ? 'update' : 'create';
        header('Location: index.php?success=' . $message);
        exit;
    } else {
        // In a real app, you might want to show a more specific error message
        header('Location: edit.php?id=' . ($data['id'] ?? '') . '&error=1');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
