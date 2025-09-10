<?php
require_once '../src/Database.php';
require_once '../src/Competenza.php';

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id' => $_POST['id'] ?? null,
        'nome' => $_POST['nome'],
        'descrizione' => $_POST['descrizione'],
        'tipologia_id' => $_POST['tipologia_id'] ?: null, // Store NULL if empty
        'conoscenze' => $_POST['conoscenze'] ?? [],
        'abilita' => $_POST['abilita'] ?? [],
        'discipline' => $_POST['discipline'] ?? [],
        'anni_corso' => $_POST['anni_corso'] ?? []
    ];

    $competenza = new Competenza($data);

    if ($competenza->save()) {
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
