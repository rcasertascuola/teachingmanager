<?php
require_once '../src/Database.php';
require_once '../src/Conoscenza.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id' => $_POST['id'] ?? null,
        'nome' => $_POST['nome'],
        'descrizione' => $_POST['descrizione'],
        'discipline' => $_POST['discipline'] ?? [],
        'anni_corso' => $_POST['anni_corso'] ?? []
    ];

    $conoscenza = new Conoscenza($data);

    if ($conoscenza->save()) {
        $message = $data['id'] ? 'update' : 'create';
        header('Location: index.php?success=' . $message);
        exit;
    } else {
        // Error handling
        header('Location: edit.php?id=' . ($data['id'] ?? '') . '&error=1');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
