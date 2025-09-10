<?php
session_start();
// Auth check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/Uda.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic validation
    if (isset($_POST['name'])) {

        $data = [
            'id' => isset($_POST['id']) && !empty($_POST['id']) ? (int)$_POST['id'] : null,
            'name' => trim($_POST['name']),
            'description' => isset($_POST['description']) ? trim($_POST['description']) : ''
        ];

        $uda = new Uda($data);
        $uda->save();
    }
}

// Redirect back to the UDA list
header("location: index.php");
exit;
?>
