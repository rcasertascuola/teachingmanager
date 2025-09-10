<?php
session_start();
// Auth check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/Module.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic validation
    if (isset($_POST['name']) && isset($_POST['uda_id'])) {

        $data = [
            'id' => isset($_POST['id']) && !empty($_POST['id']) ? (int)$_POST['id'] : null,
            'name' => trim($_POST['name']),
            'description' => isset($_POST['description']) ? trim($_POST['description']) : '',
            'uda_id' => (int)$_POST['uda_id']
        ];

        $module = new Module($data);
        $module->save();
    }
}

// Redirect back to the module list
header("location: index.php");
exit;
?>
