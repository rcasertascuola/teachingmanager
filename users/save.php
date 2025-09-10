<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/User.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    // Assign post values
    $user->id = $_POST['id'] ?? null;
    $user->username = $_POST['username'];
    $user->password = $_POST['password'];
    $user->role = $_POST['role'];

    if ($user->role === 'student') {
        $user->classe = $_POST['classe'];
        $user->corso = $_POST['corso'];
        $user->anno_scolastico = $_POST['anno_scolastico'];
    } else {
        $user->classe = null;
        $user->corso = null;
        $user->anno_scolastico = null;
    }

    // Create or update the user
    if ($user->id) {
        // Update user
        $user->update();
    } else {
        // Create user
        $user->create();
    }
}

header("location: index.php");
exit;
?>
