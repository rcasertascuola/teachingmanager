<?php
require_once '../src/Database.php';
require_once '../src/Competenza.php';
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
$manager = new Competenza($db);

$page_title = 'Gestione Competenze';
$entity_name = 'Competenza';
$table_name = 'competenze';
$joins = [
    'LEFT JOIN tipologie_competenze ON competenze.tipologia_id = tipologie_competenze.id'
];
$selects = [
    'competenze.id',
    'competenze.nome',
    'tipologie_competenze.nome as tipologia'
];
$columns = [
    'id' => 'ID',
    'nome' => 'Nome',
    'tipologia' => 'Tipologia'
];

// Include the generic handler
require_once '../handlers/index_handler.php';
?>
