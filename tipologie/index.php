<?php
require_once '../src/Database.php';
require_once '../src/TipologiaCompetenza.php';
include '../header.php';

// Auth check
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

// Configuration for the generic index handler
$db = Database::getInstance()->getConnection();
$manager = new TipologiaCompetenza($db);

$page_title = 'Gestione Tipologie di Competenze';
$entity_name = 'Tipologia di Competenza';
$columns = [
    'id' => 'ID',
    'nome' => 'Nome'
];
$items = $manager->findAll();

// Include the generic handler
require_once '../handlers/index_handler.php';
?>
