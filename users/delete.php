<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/User.php';

if (isset($_GET['id'])) {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    $user->delete($_GET['id']);
}

header("location: index.php");
exit;
?>
