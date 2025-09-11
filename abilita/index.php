<?php
require_once '../src/Database.php';
require_once '../src/Abilita.php';
include '../header.php';

// Auth check
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

// Configuration for the generic index handler
$db = Database::getInstance()->getConnection();
$manager = new Abilita($db);

$page_title = 'Gestione Abilità';
$entity_name = 'Abilità';
$columns = [
    'id' => 'ID',
    'nome' => 'Nome',
    'tipo' => 'Tipo'
];
$items = $manager->findAll();

// Include the generic handler
require_once '../handlers/index_handler.php';
?>
