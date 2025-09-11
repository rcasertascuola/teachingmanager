<?php
require_once '../src/Database.php';
require_once '../src/Uda.php';
include '../header.php';

// Auth check
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

// Configuration for the generic index handler
$db = Database::getInstance()->getConnection();
$manager = new Uda($db);

$page_title = 'Gestione UDA';
$entity_name = 'UDA';
$columns = [
    'name' => 'Nome',
    'description' => 'Descrizione'
];
$items = $manager->findAll();

// Include the generic handler
require_once '../handlers/index_handler.php';
?>
