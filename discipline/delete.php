<?php
require_once '../src/Database.php';
require_once '../src/Disciplina.php';

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    if (Disciplina::delete($id)) {
        header('Location: index.php?success=delete');
        exit;
    } else {
        // Handle error, e.g., show a message
        header('Location: index.php?error=delete');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
