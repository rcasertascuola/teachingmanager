<?php
require_once '../src/Database.php';
require_once '../src/Disciplina.php';
include '../header.php';

// Auth check
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

// Configuration for the generic index handler
$db = Database::getInstance()->getConnection();
$manager = new Disciplina($db);

$page_title = 'Gestione Discipline';
$entity_name = 'Disciplina';
$table_name = 'discipline';
$columns = [
    'id' => 'ID',
    'nome' => 'Nome'
];

// Include the generic handler
require_once '../handlers/index_handler.php';
?>
