<?php
require_once '../src/Database.php';
require_once '../src/Abilita.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    if (Abilita::delete($id)) {
        header('Location: index.php?success=delete');
        exit;
    } else {
        header('Location: index.php?error=delete');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
